<?php
/**
 * In-memory PayPal REST backend for unit tests.
 *
 * @package GiftFlow
 */

namespace GiftFlow\Tests\Support;

/**
 * Provides deterministic OAuth and resource responses without network I/O.
 */
final class FakePayPalLedger {

	/**
	 * Number of OAuth token requests.
	 *
	 * @var int
	 */
	private $oauth_calls = 0;

	/**
	 * Webhooks keyed by identifier.
	 *
	 * @var array<string, array<string, mixed>>
	 */
	private $webhooks = array();

	/**
	 * PayPal resources keyed by API path.
	 *
	 * @var array<string, array<string, mixed>>
	 */
	private $resources = array();

	/**
	 * Verification results keyed by profile.
	 *
	 * @var array<string, string>
	 */
	private $verification = array();

	/**
	 * Resource identifier sequence.
	 *
	 * @var int
	 */
	private $sequence = 0;

	/**
	 * Respond to one recorded HTTP call.
	 *
	 * @param string $method  HTTP method.
	 * @param string $url     Full URL.
	 * @param array  $args    Request arguments.
	 * @param array  $decoded Decoded JSON body.
	 * @return array
	 */
	public function respond( string $method, string $url, array $args, array $decoded ): array {
		$path = (string) wp_parse_url( $url, PHP_URL_PATH );
		if ( '/v1/oauth2/token' === $path ) {
			++$this->oauth_calls;
			return $this->response(
				200,
				array(
					'access_token' => 'token-' . $this->oauth_calls,
					'expires_in' => 3600,
					'app_id' => $this->profile( $url ),
				)
			);
		}
		if ( '/v1/notifications/verify-webhook-signature' === $path ) {
			$status = $this->verification[ $this->profile( $url ) ] ?? 'FAILURE';
			return $this->response( 200, array( 'verification_status' => $status ) );
		}
		if ( '/v1/notifications/webhooks' === $path && 'GET' === $method ) {
			return $this->response( 200, array( 'webhooks' => array_values( $this->webhooks ) ) );
		}
		if ( '/v1/notifications/webhooks' === $path && 'POST' === $method ) {
			$id = 'WH-' . ( ++$this->sequence );
			$this->webhooks[ $id ] = array_merge( array( 'id' => $id ), $decoded );
			return $this->response( 201, $this->webhooks[ $id ] );
		}
		if ( preg_match( '#^/v1/notifications/webhooks/([^/]+)$#', $path, $matches ) ) {
			$id = rawurldecode( $matches[1] );
			if ( ! isset( $this->webhooks[ $id ] ) ) {
				return $this->response( 404, array( 'name' => 'RESOURCE_NOT_FOUND' ) );
			}
			if ( 'PATCH' === $method ) {
				foreach ( $decoded as $operation ) {
					if ( '/url' === ( $operation['path'] ?? '' ) ) {
						$this->webhooks[ $id ]['url'] = $operation['value'];
					} elseif ( '/event_types' === ( $operation['path'] ?? '' ) ) {
						$this->webhooks[ $id ]['event_types'] = $operation['value'];
					}
				}
				return $this->response( 204, array() );
			}
			return $this->response( 200, $this->webhooks[ $id ] );
		}
		return $this->resource_response( $method, $path, $decoded );
	}

	/**
	 * Configure webhook signature verification for a profile.
	 *
	 * @param string $profile Profile or app identifier.
	 * @param bool   $success Whether verification succeeds.
	 * @return void
	 */
	public function verify_for( string $profile, bool $success ): void {
		$this->verification[ $profile ] = $success ? 'SUCCESS' : 'FAILURE';
	}

	/**
	 * Return the OAuth request count.
	 *
	 * @return int
	 */
	public function oauth_calls(): int {
		return $this->oauth_calls;
	}

	/**
	 * Return all stored webhooks.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function webhooks(): array {
		return $this->webhooks;
	}

	/**
	 * Reset all in-memory PayPal state.
	 *
	 * @return void
	 */
	public function reset(): void {
		$this->oauth_calls = 0;
		$this->webhooks = array();
		$this->resources = array();
		$this->verification = array();
		$this->sequence = 0;
	}

	/**
	 * Respond to a resource API request.
	 *
	 * @param string $method HTTP method.
	 * @param string $path   API path.
	 * @param array  $body   Decoded request body.
	 * @return array
	 */
	private function resource_response( string $method, string $path, array $body ): array {
		if ( 'POST' === $method && preg_match( '#/(orders|products|plans|subscriptions)$#', $path, $matches ) ) {
			$id = strtoupper( substr( $matches[1], 0, 3 ) ) . '-' . ( ++$this->sequence );
			$this->resources[ $path . '/' . $id ] = array_merge( array( 'id' => $id ), $body );
			return $this->response( 201, $this->resources[ $path . '/' . $id ] );
		}
		if ( 'POST' === $method && preg_match( '#/orders/([^/]+)/capture$#', $path, $matches ) ) {
			return $this->response(
				201,
				array(
					'id' => rawurldecode( $matches[1] ),
					'status' => 'COMPLETED',
				)
			);
		}
		if ( 'POST' === $method && preg_match( '#/subscriptions/([^/]+)/cancel$#', $path ) ) {
			return $this->response( 204, array() );
		}
		if ( 'GET' === $method && isset( $this->resources[ $path ] ) ) {
			return $this->response( 200, $this->resources[ $path ] );
		}
		return $this->response( 404, array( 'name' => 'RESOURCE_NOT_FOUND' ) );
	}

	/**
	 * Build a WordPress-style HTTP response.
	 *
	 * @param int   $status HTTP status.
	 * @param array $body   Response body data.
	 * @return array
	 */
	private function response( int $status, array $body ): array {
		return array(
			'response' => array( 'code' => $status ),
			'body' => wp_json_encode( $body ),
		);
	}

	/**
	 * Resolve the PayPal profile from an API URL.
	 *
	 * @param string $url PayPal API URL.
	 * @return string
	 */
	private function profile( string $url ): string {
		return false !== strpos( $url, 'sandbox' ) ? 'sandbox' : 'live';
	}
}
