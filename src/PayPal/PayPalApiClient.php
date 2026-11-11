<?php
/**
 * Environment-bound PayPal REST API client.
 *
 * @package GiftFlow
 * @subpackage PayPal
 */

namespace GiftFlow\PayPal;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Performs authenticated PayPal API requests for one explicit profile.
 */
final class PayPalApiClient {

	/**
	 * PayPal environment.
	 *
	 * @var string
	 */
	private $environment;

	/**
	 * PayPal client ID.
	 *
	 * @var string
	 */
	private $client_id;

	/**
	 * PayPal client secret.
	 *
	 * @var string
	 */
	private $client_secret;

	/**
	 * Connection repository.
	 *
	 * @var PayPalConnectionRepository
	 */
	private $repository;

	/**
	 * HTTP transport.
	 *
	 * @var callable
	 */
	private $transport;

	/**
	 * App ID learned during authentication.
	 *
	 * @var string
	 */
	private $app_id = '';

	/**
	 * Sanitized token request failure.
	 *
	 * @var array
	 */
	private $token_failure = array();

	/**
	 * Constructor.
	 *
	 * @param string                     $environment   PayPal environment.
	 * @param string                     $client_id     PayPal client ID.
	 * @param string                     $client_secret PayPal client secret.
	 * @param PayPalConnectionRepository $repository    Connection repository.
	 * @param callable|null              $transport     WordPress HTTP transport.
	 * @throws \InvalidArgumentException When credentials are empty or masked.
	 */
	public function __construct( string $environment, string $client_id, string $client_secret, PayPalConnectionRepository $repository, ?callable $transport = null ) {
		$this->environment   = PayPalEnvironment::assert_valid( $environment );
		$this->client_id     = trim( $client_id );
		$this->client_secret = trim( $client_secret );
		$this->repository    = $repository;

		if ( '' === $this->client_id || PayPalMasking::is_masked( $this->client_id ) ) {
			throw new \InvalidArgumentException( esc_html__( 'A complete, unmasked PayPal client ID is required.', 'giftflow' ) );
		}
		if ( '' === $this->client_secret || PayPalMasking::is_masked( $this->client_secret ) ) {
			throw new \InvalidArgumentException( esc_html__( 'A complete, unmasked PayPal client secret is required.', 'giftflow' ) );
		}

		$this->transport = $transport ?? static function ( string $method, string $url, array $args ) {
			return wp_remote_request( $url, array_merge( $args, array( 'method' => $method ) ) );
		};
	}

	/**
	 * Get the client's PayPal environment.
	 *
	 * @return string PayPal environment identifier.
	 */
	public function environment(): string {
		return $this->environment;
	}

	/**
	 * Get the API base URL for the configured environment.
	 *
	 * @return string PayPal API base URL.
	 */
	public function api_base(): string {
		return PayPalEnvironment::api_base( $this->environment );
	}

	/**
	 * Get the client ID used by this client.
	 *
	 * @return string PayPal client ID.
	 */
	public function client_id(): string {
		return $this->client_id;
	}

	/**
	 * Resolve and cache an OAuth access token.
	 *
	 * @return string Empty when authentication is unavailable.
	 */
	public function access_token(): string {
		$cached = PayPalTokenCache::get( $this->environment, $this->client_id );
		if ( '' !== $cached ) {
			return $cached;
		}

		$headers = array(
			'Accept'          => 'application/json',
			'Accept-Language' => 'en_US',
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			'Authorization'   => 'Basic ' . base64_encode( $this->client_id . ':' . $this->client_secret ),
			'Content-Type'    => 'application/x-www-form-urlencoded',
		);
		$response = call_user_func(
			$this->transport,
			'POST',
			$this->api_base() . '/v1/oauth2/token',
			array(
				'headers' => $headers,
				'body' => 'grant_type=client_credentials',
				'timeout' => 30,
			)
		);
		$this->token_failure = array();
		if ( is_wp_error( $response ) ) {
			$this->token_failure = $this->error_envelope( 0, array(), '', true );
			return '';
		}

		$status = (int) wp_remote_retrieve_response_code( $response );
		$body   = json_decode( wp_remote_retrieve_body( $response ), true );
		$body   = is_array( $body ) ? $body : array();
		if ( 200 !== $status || empty( $body['access_token'] ) || ! is_string( $body['access_token'] ) ) {
			$this->token_failure = $this->error_envelope( $status, $body );
			return '';
		}

		$token      = $body['access_token'];
		$expires_in = isset( $body['expires_in'] ) ? max( 60, (int) $body['expires_in'] ) : 32400;
		$this->app_id = isset( $body['app_id'] ) && is_string( $body['app_id'] ) ? trim( $body['app_id'] ) : '';
		PayPalTokenCache::put( $this->environment, $this->client_id, $token, $expires_in );
		return $token;
	}

	/**
	 * Resolve the authenticated PayPal app identity.
	 *
	 * @return string PayPal app identity or deterministic fallback.
	 */
	public function app_identity(): string {
		if ( '' === $this->app_id ) {
			$this->access_token();
		}
		return PayPalEnvironment::app_identity( $this->environment, $this->client_id, $this->app_id );
	}

	/**
	 * Perform one authenticated JSON request.
	 *
	 * @param string $method HTTP method.
	 * @param string $path   API path.
	 * @param array  $body   JSON body.
	 * @param array  $query      Query parameters.
	 * @param string $request_id Optional caller-owned PayPal-Request-Id (idempotency key).
	 * @return array Sanitized response envelope.
	 */
	public function request( string $method, string $path, array $body = array(), array $query = array(), string $request_id = '' ): array {
		$method = strtoupper( trim( $method ) );
		$token  = $this->access_token();
		if ( '' === $token ) {
			return ! empty( $this->token_failure ) ? $this->token_failure : $this->error_envelope( 0, array(), '', true );
		}

		$result = $this->send_business_request( $method, $path, $body, $query, $token, $request_id );
		if ( 401 === $result['status'] ) {
			PayPalTokenCache::forget( $this->environment, $this->client_id );
			$token = $this->access_token();
			if ( '' === $token ) {
				return ! empty( $this->token_failure ) ? $this->token_failure : $this->error_envelope( 401, array() );
			}
			// Same request id on the auth retry: the provider must dedupe the write.
			$result = $this->send_business_request( $method, $path, $body, $query, $token, $request_id );
		}
		return $result;
	}

	/**
	 * Create an order.
	 *
	 * @param array  $order_data Order payload.
	 * @param string $request_id Optional caller-owned PayPal-Request-Id.
	 * @return array
	 */
	public function create_order( array $order_data, string $request_id = '' ): array {
		return $this->request( 'POST', '/v2/checkout/orders', $order_data, array(), $request_id );
	}

	/**
	 * Capture an order.
	 *
	 * @param string $order_id   Order ID.
	 * @param string $request_id Optional caller-owned PayPal-Request-Id.
	 * @return array
	 */
	public function capture_order( string $order_id, string $request_id = '' ): array {
		return $this->request( 'POST', '/v2/checkout/orders/' . rawurlencode( $order_id ) . '/capture', array(), array(), $request_id );
	}

	/**
	 * Retrieve an order.
	 *
	 * @param string $order_id Order ID.
	 * @return array
	 */
	public function get_order( string $order_id ): array {
		return $this->request( 'GET', '/v2/checkout/orders/' . rawurlencode( $order_id ) );
	}

	/**
	 * Create a product.
	 *
	 * @param array $product Product payload.
	 * @return array
	 */
	public function create_product( array $product ): array {
		return $this->request( 'POST', '/v1/catalogs/products', $product );
	}

	/**
	 * Retrieve a product.
	 *
	 * @param string $product_id Product ID.
	 * @return array
	 */
	public function get_product( string $product_id ): array {
		return $this->request( 'GET', '/v1/catalogs/products/' . rawurlencode( $product_id ) );
	}

	/**
	 * Create a billing plan.
	 *
	 * @param array  $plan       Plan payload.
	 * @param string $request_id Optional caller-owned PayPal-Request-Id.
	 * @return array
	 */
	public function create_plan( array $plan, string $request_id = '' ): array {
		return $this->request( 'POST', '/v1/billing/plans', $plan, array(), $request_id );
	}

	/**
	 * Retrieve a billing plan.
	 *
	 * @param string $plan_id Plan ID.
	 * @return array
	 */
	public function get_plan( string $plan_id ): array {
		return $this->request( 'GET', '/v1/billing/plans/' . rawurlencode( $plan_id ) );
	}

	/**
	 * Create a subscription.
	 *
	 * @param array  $subscription Subscription payload.
	 * @param string $request_id   Optional caller-owned PayPal-Request-Id.
	 * @return array
	 */
	public function create_subscription( array $subscription, string $request_id = '' ): array {
		return $this->request( 'POST', '/v1/billing/subscriptions', $subscription, array(), $request_id );
	}

	/**
	 * Retrieve a subscription.
	 *
	 * @param string $subscription_id Subscription ID.
	 * @return array
	 */
	public function get_subscription( string $subscription_id ): array {
		return $this->request( 'GET', '/v1/billing/subscriptions/' . rawurlencode( $subscription_id ) );
	}

	/**
	 * Cancel a subscription.
	 *
	 * @param string $subscription_id Subscription ID.
	 * @param string $reason          Cancellation reason.
	 * @return array
	 */
	public function cancel_subscription( string $subscription_id, string $reason ): array {
		return $this->request(
			'POST',
			'/v1/billing/subscriptions/' . rawurlencode( $subscription_id ) . '/cancel',
			array( 'reason' => $reason )
		);
	}

	/**
	 * List webhooks.
	 *
	 * @return array
	 */
	public function list_webhooks(): array {
		return $this->request( 'GET', '/v1/notifications/webhooks' );
	}

	/**
	 * Retrieve a webhook.
	 *
	 * @param string $webhook_id Webhook ID.
	 * @return array
	 */
	public function get_webhook( string $webhook_id ): array {
		return $this->request( 'GET', '/v1/notifications/webhooks/' . rawurlencode( $webhook_id ) );
	}

	/**
	 * Create a webhook.
	 *
	 * @param string $url    Webhook URL.
	 * @param array  $events Event names.
	 * @return array
	 */
	public function create_webhook( string $url, array $events ): array {
		return $this->request(
			'POST',
			'/v1/notifications/webhooks',
			array(
				'url'         => $url,
				'event_types' => $this->event_types( $events ),
			)
		);
	}

	/**
	 * Update a webhook.
	 *
	 * @param string $webhook_id Webhook ID.
	 * @param string $url        Webhook URL.
	 * @param array  $events     Event names.
	 * @return array
	 */
	public function update_webhook( string $webhook_id, string $url, array $events ): array {
		$body = array(
			array(
				'op' => 'replace',
				'path' => '/url',
				'value' => $url,
			),
			array(
				'op' => 'replace',
				'path' => '/event_types',
				'value' => $this->event_types( $events ),
			),
		);
		return $this->request( 'PATCH', '/v1/notifications/webhooks/' . rawurlencode( $webhook_id ), $body );
	}

	/**
	 * Verify a webhook transmission.
	 *
	 * @param array  $transmission_headers PayPal transmission headers.
	 * @param string $webhook_id          Webhook ID.
	 * @param string $raw_body            Raw webhook JSON.
	 * @return array
	 */
	public function verify_webhook_signature( array $transmission_headers, string $webhook_id, string $raw_body ): array {
		$event = json_decode( $raw_body, true );
		$event = is_array( $event ) ? $event : array();
		$headers = array_change_key_case( $transmission_headers, CASE_LOWER );
		$body = array(
			'webhook_id'       => $webhook_id,
			'transmission_id'  => $this->header( $headers, 'paypal-transmission-id' ),
			'transmission_time' => $this->header( $headers, 'paypal-transmission-time' ),
			'cert_url'         => $this->header( $headers, 'paypal-cert-url' ),
			'auth_algo'        => $this->header( $headers, 'paypal-auth-algo' ),
			'transmission_sig' => $this->header( $headers, 'paypal-transmission-sig' ),
			'webhook_event'    => $event,
		);
		return $this->request( 'POST', '/v1/notifications/verify-webhook-signature', $body );
	}

	/**
	 * Send an authenticated request to a PayPal business endpoint.
	 *
	 * @param string $method HTTP method.
	 * @param string $path   API path relative to the environment base URL.
	 * @param array  $body   Request body fields.
	 * @param array  $query      Query parameters.
	 * @param string $token      OAuth access token.
	 * @param string $request_id Optional caller-owned PayPal-Request-Id.
	 * @return array Sanitized response envelope.
	 */
	private function send_business_request( string $method, string $path, array $body, array $query, string $token, string $request_id = '' ): array {
		$url = $this->api_base() . '/' . ltrim( $path, '/' );
		if ( ! empty( $query ) ) {
			$url = add_query_arg( $query, $url );
		}
		$headers = array(
			'Accept' => 'application/json',
			'Authorization' => 'Bearer ' . $token,
		);
		$args    = array(
			'headers' => $headers,
			'timeout' => 30,
		);
		$is_write = in_array( $method, array( 'POST', 'PUT', 'PATCH' ), true );

		if ( $is_write ) {
			// PayPal requires a JSON content type on every write request, even
			// when the endpoint takes no fields (order capture, for example), so
			// an empty body is sent as an empty JSON object.
			$args['headers']['Content-Type'] = 'application/json';
			$args['body'] = empty( $body ) ? '{}' : (string) wp_json_encode( $body );
		} elseif ( ! empty( $body ) ) {
			$args['headers']['Content-Type'] = 'application/json';
			$args['body'] = wp_json_encode( $body );
		}

		if ( in_array( $method, array( 'POST', 'PUT', 'PATCH', 'DELETE' ), true ) ) {
			$args['headers']['PayPal-Request-Id'] = '' !== $request_id ? $request_id : wp_generate_uuid4();
		}
		$response = call_user_func( $this->transport, $method, $url, $args );
		if ( is_wp_error( $response ) ) {
			return $this->error_envelope( 0, array(), '', true );
		}
		$status = (int) wp_remote_retrieve_response_code( $response );
		$decoded = json_decode( wp_remote_retrieve_body( $response ), true );
		$decoded = is_array( $decoded ) ? $decoded : array();
		if ( $status >= 200 && $status < 300 ) {
			return array(
				'ok' => true,
				'status' => $status,
				'body' => $decoded,
				'code' => '',
				'issue' => '',
				'temporary' => false,
			);
		}
		return $this->error_envelope( $status, $decoded );
	}

	/**
	 * Build a redacted error response envelope.
	 *
	 * @param int    $status    HTTP response status.
	 * @param array  $body      Decoded PayPal response body.
	 * @param string $issue     Optional PayPal issue code.
	 * @param bool   $temporary Whether to force temporary-error classification.
	 * @return array Sanitized error response envelope.
	 */
	private function error_envelope( int $status, array $body, string $issue = '', bool $temporary = false ): array {
		if ( '' === $issue && ! empty( $body['details'][0]['issue'] ) && is_string( $body['details'][0]['issue'] ) ) {
			$issue = $body['details'][0]['issue'];
		}
		$code = '' !== $issue ? PayPalErrorCodes::from_issue( $issue ) : PayPalErrorCodes::from_http( $status, $body );
		return array(
			'ok' => false,
			'status' => $status,
			'body' => array(),
			'code' => PayPalErrorCodes::sanitize( $code ),
			'issue' => $issue,
			'temporary' => $temporary || 0 === $status || $status >= 500,
		);
	}

	/**
	 * Convert event names to PayPal event type objects.
	 *
	 * @param array $events Event names.
	 * @return array PayPal event type objects.
	 */
	private function event_types( array $events ): array {
		return array_map(
			static function ( $event ) {
				return array( 'name' => (string) $event );
			},
			array_values( $events )
		);
	}

	/**
	 * Read a PayPal transmission header.
	 *
	 * @param array  $headers Normalized request headers.
	 * @param string $name    Full PayPal header name.
	 * @return string Header value, or an empty string when absent.
	 */
	private function header( array $headers, string $name ): string {
		if ( isset( $headers[ $name ] ) ) {
			return trim( (string) $headers[ $name ] );
		}
		$short = str_replace( 'paypal-', '', $name );
		return isset( $headers[ $short ] ) ? trim( (string) $headers[ $short ] ) : '';
	}
}
