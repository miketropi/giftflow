<?php
/**
 * Environment-safe PayPal webhook receiver.
 *
 * @package GiftFlow
 * @subpackage PayPal
 */

namespace GiftFlow\PayPal;

// phpcs:disable Generic.Commenting.DocComment.MissingShort,Squiz.Commenting.FunctionComment.MissingParamTag -- Existing concise method annotations accurately describe the receiver helpers.

use GiftFlow\Core\Logger as Giftflow_Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Verifies and routes incoming PayPal webhook deliveries. */
final class PayPalWebhookReceiver {

	/** @var PayPalConnectionRepository */
	private PayPalConnectionRepository $repository;

	/** @var PayPalWebhookDispatcher */
	private PayPalWebhookDispatcher $dispatcher;

	/**
	 * Constructor.
	 *
	 * @param PayPalConnectionRepository|null $repository Repository.
	 * @param PayPalWebhookDispatcher|null    $dispatcher Dispatcher.
	 */
	public function __construct( ?PayPalConnectionRepository $repository = null, ?PayPalWebhookDispatcher $dispatcher = null ) {
		$this->repository = $repository ?? new PayPalConnectionRepository();
		$this->dispatcher = $dispatcher ?? new PayPalWebhookDispatcher( $this->repository );
	}

	/** Handle the current HTTP request and terminate. */
	public function handle_request(): void {
		// PayPal signs the exact raw bytes, so the body must be read once and remain unchanged.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$payload = file_get_contents( 'php://input' );
		$headers = array(
			'transmission_id'   => $this->server_header( 'HTTP_PAYPAL_TRANSMISSION_ID' ),
			'transmission_time' => $this->server_header( 'HTTP_PAYPAL_TRANSMISSION_TIME' ),
			'transmission_sig'  => $this->server_header( 'HTTP_PAYPAL_TRANSMISSION_SIG' ),
			'cert_url'          => $this->server_header( 'HTTP_PAYPAL_CERT_URL' ),
			'auth_algo'         => $this->server_header( 'HTTP_PAYPAL_AUTH_ALGO' ),
		);
		$result = $this->receive( is_string( $payload ) ? $payload : '', $headers );
		status_header( (int) $result['status'] );
		echo $result['body']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- machine acknowledgement.
		exit;
	}

	/**
	 * Verify and process one delivery.
	 *
	 * @param string $payload Raw body.
	 * @param array  $headers PayPal transmission headers.
	 * @return array
	 */
	public function receive( string $payload, array $headers ): array {
		$result = array(
			'status' => 200,
			'body' => 'OK',
			'environment' => '',
			'code' => '',
			'event_type' => '',
			'event_id' => '',
			'handled' => false,
		);
		if ( ! $this->repository->is_webhook_enabled() ) {
			return $result;
		}
		$headers = $this->normalize_headers( $headers );
		if ( '' === trim( $payload ) || in_array( '', $headers, true ) ) {
			return $this->reject( $result, 400, PayPalErrorCodes::WEBHOOK_HEADERS_MISSING );
		}
		$event = json_decode( $payload, true );
		if ( ! is_array( $event ) ) {
			return $this->reject( $result, 400, PayPalErrorCodes::WEBHOOK_VERIFICATION_INVALID );
		}
		$matches = array();
		foreach ( PayPalEnvironment::all() as $environment ) {
			$profile     = $this->repository->get_profile( $environment );
			$credentials = $this->repository->get_credentials( $environment );
			$webhook_id  = isset( $profile['webhook_id'] ) ? trim( (string) $profile['webhook_id'] ) : '';
			if ( '' === $webhook_id || empty( $credentials['client_id'] ) || empty( $credentials['client_secret'] ) ) {
				continue;
			}
			$client   = new PayPalApiClient( $environment, $credentials['client_id'], $credentials['client_secret'], $this->repository );
			$response = $client->verify_webhook_signature( $headers, $webhook_id, $payload );
			if ( ! empty( $response['ok'] ) && 'SUCCESS' === ( isset( $response['body']['verification_status'] ) ? $response['body']['verification_status'] : '' ) ) {
				$matches[ $environment ] = $client->app_identity();
			}
		}
		if ( empty( $matches ) ) {
			return $this->reject( $result, 401, PayPalErrorCodes::WEBHOOK_VERIFICATION_INVALID );
		}
		if ( count( $matches ) > 1 ) {
			return $this->reject( $result, 400, PayPalErrorCodes::WEBHOOK_VERIFICATION_AMBIGUOUS );
		}
		$environment          = (string) key( $matches );
		$app_id               = (string) current( $matches );
		$result['environment'] = $environment;
		$result['event_type']  = isset( $event['event_type'] ) ? (string) $event['event_type'] : '';
		$result['event_id']    = isset( $event['id'] ) ? (string) $event['id'] : '';
		$resource             = isset( $event['resource'] ) && is_array( $event['resource'] ) ? $event['resource'] : array();
		foreach ( $this->dispatcher->resolve_target_donation_ids( $result['event_type'], $resource, $environment ) as $donation_id ) {
			if ( ! $this->dispatcher->bind_donation_environment( (int) $donation_id, $environment, $app_id ) ) {
				return $this->reject( $result, 400, PayPalErrorCodes::WEBHOOK_BINDING_MISMATCH );
			}
		}
		if ( ! PayPalWebhookDispatcher::is_supported( $result['event_type'] ) ) {
			return $result;
		}
		$claim = $this->repository->claim_webhook_event( $environment, $result['event_id'] );
		if ( 'duplicate' === $claim ) {
			return $result;
		}
		if ( 'claimed' !== $claim ) {
			return $this->reject( $result, 500, PayPalErrorCodes::WEBHOOK_CLAIM_STORAGE_FAILED );
		}
		$this->repository->mark_webhook_verified( $environment );
		$result['handled'] = $this->dispatcher->dispatch( $result['event_type'], $resource, $environment );
		return $result;
	}

	/** Read and sanitize one server header. */
	private function server_header( string $key ): string {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		return isset( $_SERVER[ $key ] ) ? sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) : '';
	}

	/** Normalize accepted header key spellings. */
	private function normalize_headers( array $headers ): array {
		$normalized = array();
		foreach ( array( 'transmission_id', 'transmission_time', 'transmission_sig', 'cert_url', 'auth_algo' ) as $key ) {
			$hyphen = str_replace( '_', '-', $key );
			$value  = isset( $headers[ $key ] ) ? $headers[ $key ] : ( isset( $headers[ $hyphen ] ) ? $headers[ $hyphen ] : '' );
			$normalized[ $key ] = is_scalar( $value ) ? trim( (string) $value ) : '';
		}
		return $normalized;
	}

	/** Build and log a rejected response. */
	private function reject( array $result, int $status, string $code ): array {
		$result['status'] = $status;
		$result['code']   = PayPalErrorCodes::sanitize( $code );
		$result['body']   = 401 === $status ? 'Unauthorized' : ( 400 === $status ? 'Bad Request' : 'Error' );
		Giftflow_Logger::error(
			'paypal.webhook.rejected',
			array(
				'code'        => $result['code'],
				'status'      => $status,
				'environment' => $result['environment'],
				'event_type'  => $result['event_type'],
				'gateway'     => 'paypal',
			),
			'paypal'
		);
		return $result;
	}
}
