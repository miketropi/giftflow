<?php
/**
 * Shared Stripe webhook receiver.
 *
 * The receiver keeps the historical admin-ajax URL so already-deployed manual
 * webhooks keep working. It reads the raw body exactly once, verifies the
 * signature independently against every stored environment secret, requires a
 * single match, enforces `livemode` and donation binding, takes an atomic event
 * claim and only then dispatches side effects.
 *
 * @package GiftFlow
 * @subpackage Stripe
 */

namespace GiftFlow\Stripe;

use GiftFlow\Core\Logger as Giftflow_Logger;
use GiftFlow\Vendor\Stripe\Webhook;
use GiftFlow\Vendor\Stripe\Exception\SignatureVerificationException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Verifies and routes incoming Stripe webhook deliveries.
 */
final class StripeWebhookReceiver {

	/**
	 * Repository.
	 *
	 * @var StripeConnectionRepository
	 */
	private StripeConnectionRepository $repository;

	/**
	 * Dispatcher.
	 *
	 * @var StripeWebhookDispatcher
	 */
	private StripeWebhookDispatcher $dispatcher;

	/**
	 * Constructor.
	 *
	 * @param StripeConnectionRepository|null $repository Repository.
	 * @param StripeWebhookDispatcher|null    $dispatcher Dispatcher.
	 */
	public function __construct( ?StripeConnectionRepository $repository = null, ?StripeWebhookDispatcher $dispatcher = null ) {
		$this->repository = $repository ?? new StripeConnectionRepository();
		$this->dispatcher = $dispatcher ?? new StripeWebhookDispatcher( $this->repository );
	}

	/**
	 * Handle the current HTTP request and terminate.
	 *
	 * @return void
	 */
	public function handle_request(): void {
		// Reviewer Note: Stripe's signature covers the exact raw body, so the payload
		// must not be sanitized or re-encoded before verification.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$payload = file_get_contents( 'php://input' );
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$signature = isset( $_SERVER['HTTP_STRIPE_SIGNATURE'] )
			? sanitize_text_field( wp_unslash( $_SERVER['HTTP_STRIPE_SIGNATURE'] ) )
			: '';

		$result = $this->receive( is_string( $payload ) ? $payload : '', $signature );

		status_header( (int) $result['status'] );
		echo $result['body']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- machine readable acknowledgement.

		exit;
	}

	/**
	 * Verify and process one delivery.
	 *
	 * @param string $payload   Raw request body.
	 * @param string $signature Value of the Stripe-Signature header.
	 * @return array{status:int,body:string,environment:string,code:string,event_type:string,event_id:string,handled:bool}
	 */
	public function receive( string $payload, string $signature ): array {
		$result = array(
			'status'      => 200,
			'body'        => 'OK',
			'environment' => '',
			'code'        => '',
			'event_type'  => '',
			'event_id'    => '',
			'handled'     => false,
		);

		if ( ! $this->repository->is_webhook_enabled() ) {
			// Historical behaviour: a disabled webhook acknowledges without processing.
			return $result;
		}

		if ( '' === trim( $payload ) || '' === trim( $signature ) ) {
			return $this->reject( $result, 400, StripeErrorCodes::WEBHOOK_SIGNATURE_INVALID );
		}

		$candidates = $this->candidate_secrets();

		if ( empty( $candidates ) ) {
			return $this->reject( $result, 400, StripeErrorCodes::WEBHOOK_SECRET_MISSING );
		}

		$matches = array();

		foreach ( $candidates as $environment => $secret ) {
			try {
				$matches[ $environment ] = Webhook::constructEvent( $payload, $signature, $secret );
			} catch ( SignatureVerificationException $e ) {
				continue;
			} catch ( \UnexpectedValueException $e ) {
				// The payload is not valid JSON, so no candidate can ever match it.
				return $this->reject( $result, 400, StripeErrorCodes::WEBHOOK_SIGNATURE_INVALID );
			} catch ( \Throwable $e ) {
				if ( self::is_signature_failure( $e ) ) {
					continue;
				}

				// Unexpected failure: fail closed without processing the delivery.
				return $this->reject( $result, 500, '' );
			}
		}

		if ( empty( $matches ) ) {
			return $this->reject( $result, 400, StripeErrorCodes::WEBHOOK_SIGNATURE_INVALID );
		}

		if ( count( $matches ) > 1 ) {
			return $this->reject( $result, 400, StripeErrorCodes::WEBHOOK_SIGNATURE_AMBIGUOUS );
		}

		$environment = (string) key( $matches );
		$event       = $matches[ $environment ];

		$result['environment'] = $environment;
		$result['event_type']  = isset( $event->type ) ? (string) $event->type : '';
		$result['event_id']    = isset( $event->id ) ? (string) $event->id : '';

		$livemode = isset( $event->livemode ) ? (bool) $event->livemode : false;

		if ( StripeEnvironment::livemode( $environment ) !== $livemode ) {
			return $this->reject( $result, 400, StripeErrorCodes::WEBHOOK_LIVEMODE_MISMATCH );
		}

		$account = isset( $event->account ) && is_string( $event->account ) ? $event->account : '';
		$object  = isset( $event->data->object ) ? $event->data->object : null;
		$targets = null === $object
			? array()
			: $this->dispatcher->resolve_target_donation_ids( $result['event_type'], $object, $environment );

		foreach ( $targets as $donation_id ) {
			if ( ! $this->dispatcher->bind_donation_environment( (int) $donation_id, $environment, $account ) ) {
				return $this->reject( $result, 400, StripeErrorCodes::WEBHOOK_BINDING_MISMATCH );
			}
		}

		if ( ! StripeWebhookDispatcher::is_supported( $result['event_type'] ) ) {
			return $result;
		}

		$claim = $this->repository->claim_webhook_event( $environment, $account, $result['event_id'] );

		if ( 'duplicate' === $claim ) {
			return $result;
		}

		if ( 'claimed' !== $claim ) {
			return $this->reject( $result, 500, StripeErrorCodes::WEBHOOK_CLAIM_STORAGE_FAILED );
		}

		$this->repository->mark_webhook_verified( $environment, $account );

		if ( null !== $object ) {
			$result['handled'] = $this->dispatcher->dispatch( $result['event_type'], $object, $environment );
		}

		return $result;
	}

	/**
	 * Stored signing secrets per environment.
	 *
	 * @return array<string, string>
	 */
	private function candidate_secrets(): array {
		$candidates = array();

		foreach ( StripeEnvironment::all() as $environment ) {
			$credentials = $this->repository->get_credentials( $environment );

			if ( '' !== $credentials['webhook_secret'] ) {
				$candidates[ $environment ] = $credentials['webhook_secret'];
			}
		}

		return $candidates;
	}

	/**
	 * Whether a throwable from the SDK means "this signature did not match".
	 *
	 * The bundled SDK raises `SignatureVerificationException` for a failed
	 * verification, but its throw sites reference the unprefixed class name, so
	 * the class actually raised can be the unscoped one (or an `Error` when that
	 * class is unavailable in a production build). Matching on the class name and
	 * message keeps signature failures from escaping as a fatal error.
	 *
	 * @param \Throwable $error Raised throwable.
	 * @return bool
	 */
	private static function is_signature_failure( \Throwable $error ): bool {
		if ( false !== strpos( get_class( $error ), 'SignatureVerificationException' ) ) {
			return true;
		}

		return false !== strpos( $error->getMessage(), 'SignatureVerificationException' );
	}

	/**
	 * Build a rejection response and log it.
	 *
	 * @param array<string, mixed> $result Response being built.
	 * @param int                  $status HTTP status.
	 * @param string               $code   Registered error code.
	 * @return array<string, mixed>
	 */
	private function reject( array $result, int $status, string $code ): array {
		$result['status'] = $status;
		$result['code']   = StripeErrorCodes::sanitize( $code );
		$result['body']   = 400 === $status ? 'Bad Request' : 'Error';

		Giftflow_Logger::error(
			'stripe.webhook.rejected',
			array(
				'code'        => $result['code'],
				'status'      => $status,
				'environment' => $result['environment'],
				'event_type'  => $result['event_type'],
				'gateway'     => 'stripe',
			),
			'stripe'
		);

		return $result;
	}
}
