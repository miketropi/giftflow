<?php
/**
 * PayPal checkout flow isolation tests.
 *
 * @package GiftFlow
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort -- Compact property type documentation is intentional in tests.
// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- Test comparison only.
// phpcs:disable Squiz.Commenting.FunctionComment.MissingParamTag -- PHPUnit helper summaries document their focused test purpose.
// phpcs:disable WordPress.Security.NonceVerification.Missing -- AJAX test helper deliberately populates request globals before dispatch.
// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode,WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- Test transport encodes and decodes benign credentials.
// phpcs:disable WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Test transport returns encoded fixture data rather than rendering HTML.
namespace GiftFlow\Tests\Integration;

use GiftFlow\Gateways\PayPal_Gateway;
use GiftFlow\PayPal\PayPalConnectionRepository;

/** Verifies continuations remain bound after the active mode changes. */
final class PayPalFlowIsolationTest extends \WP_Ajax_UnitTestCase {
	/** @var PayPalConnectionRepository */ private $repository;
	/** @var PayPal_Gateway */ private $gateway;
	/** @var array<int,array<string,mixed>> */ private $requests = array();
	/** @var int */ private $order_sequence = 0;
	/** @var string */ private $ajax_response = '';

	/** Configure gateway and both profiles. @return void */
	protected function setUp(): void {
		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true ); }
		parent::setUp();
		delete_option( PayPalConnectionRepository::PAYMENT_OPTION );
		add_filter( 'wp_doing_ajax', '__return_true' );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		update_option( 'giftflow_options_with_api_keys_options', array() );
		update_option( 'giftflow_email_options', array( 'email_admin_address' => 'admin@example.test' ) );
		$this->repository = new PayPalConnectionRepository();
		foreach ( array( 'sandbox', 'live' ) as $environment ) {
			$this->repository->update_credentials(
				$environment,
				array(
					'client_id' => $environment . '-client',
					'client_secret' => $environment . '-secret',
				)
			);
			$app = $environment . '-app';
			$this->repository->update_profile(
				$environment,
				array(
					'paypal_app_id'          => $app,
					'credential_fingerprint' => PayPalConnectionRepository::credential_fingerprint( $environment, $environment . '-client' ),
					'binding_fingerprint'    => $this->repository->binding_fingerprint( $environment, $app ),
				)
			);
		}
		$this->repository->set_active_environment( 'sandbox' );
		$options = $this->repository->get_payment_options();
		$options['paypal']['paypal_recurring_enabled'] = '1';
		update_option( PayPalConnectionRepository::PAYMENT_OPTION, $options );
		if ( ! class_exists( PayPal_Gateway::class ) ) {
			require_once dirname( __DIR__, 2 ) . '/includes/gateways/class-paypal.php'; }
		$this->gateway = new PayPal_Gateway();
		add_filter( 'pre_http_request', array( $this, 'transport' ), 10, 3 );
	}

	/** Remove interception. @return void */
	protected function tearDown(): void {
		remove_filter( 'pre_http_request', array( $this, 'transport' ), 10 );
		remove_filter( 'wp_doing_ajax', '__return_true' );
		delete_option( 'giftflow_options_with_api_keys_options' );
		delete_option( 'giftflow_email_options' );
		delete_option( PayPalConnectionRepository::PAYMENT_OPTION );
		parent::tearDown();
	}


	/** Order capture remains Sandbox after switching active mode. @return void */
	public function testOneTimeCaptureUsesCreationEnvironment(): void {
		$create = new \ReflectionMethod( $this->gateway, 'create_paypal_order' );
		$create->setAccessible( true );
		$order = $create->invoke(
			$this->gateway,
			array(
				'donation_amount' => 10,
				'donor_name' => 'Ada',
				'donor_email' => 'ada@example.test',
				'campaign_id' => '1',
				'payment_method' => 'paypal',
			)
		);
		$this->repository->set_active_environment( 'live' );
		$this->requests = array();
		$capture_method = new \ReflectionMethod( $this->gateway, 'capture_paypal_order' );
		$capture_method->setAccessible( true );
		$capture = $capture_method->invoke( $this->gateway, $order );
		$donation = (int) $capture['donation_id'];
		$this->assertSandboxRequests();
		$this->assertSame( 'sandbox', get_post_meta( $donation, '_paypal_environment', true ) );
		$this->assertNotSame( '', get_post_meta( $donation, '_paypal_app_id', true ) );
		$this->assertNotSame( '', get_post_meta( $donation, '_paypal_binding_fingerprint', true ) );
	}

	/** Subscription return and cancellation use the parent's bound environment. @return void */
	public function testRecurringContinuationUsesCreationEnvironment(): void {
		$donation = self::factory()->post->create(
			array(
				'post_type' => 'donation',
				'post_status' => 'publish',
			)
		);
		$this->gateway->binding()->persist( $donation, 'sandbox', 'sandbox-app' );
		update_post_meta( $donation, '_paypal_subscription_id', 'SUB-1' );
		$this->repository->set_active_environment( 'live' );
		$this->requests = array();
		$client = $this->gateway->client_for_environment( $this->gateway->resolve_donation_environment( $donation ) );
		$client->get_subscription( 'SUB-1' );
		$client->cancel_subscription( 'SUB-1', 'test' );
		$this->assertSandboxRequests();
		$this->assertSame( 'sandbox', get_post_meta( $donation, '_paypal_environment', true ) );
	}

	/** Same resource identifiers in Live are not modified by Sandbox resolution. @return void */
	public function testSameIdentifiersDoNotCrossUpdate(): void {
		$live = self::factory()->post->create(
			array(
				'post_type' => 'donation',
				'post_status' => 'publish',
			)
		);
		foreach ( array(
			'_paypal_order_id' => 'SAME',
			'_paypal_subscription_id' => 'SAME',
			'_paypal_sale_id' => 'SAME',
			'_paypal_environment' => 'live',
		) as $key => $value ) {
			update_post_meta( $live, $key, $value );
		}
		$before = serialize( get_post_meta( $live ) );
		$sandbox = self::factory()->post->create(
			array(
				'post_type' => 'donation',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $sandbox, '_paypal_environment', 'sandbox' );
		update_post_meta( $sandbox, '_paypal_subscription_id', 'SAME' );
		$client = $this->gateway->client_for_environment( 'sandbox' );
		$client->cancel_subscription( 'SAME', 'test' );
		$this->assertSame( $before, serialize( get_post_meta( $live ) ) );
	}

	/** Public AJAX contracts preserve Sandbox bindings across active-mode changes. @return void */
	public function testPublicAjaxContractsPreserveSandboxContinuations(): void {
		$campaign_id = self::factory()->post->create(
			array(
				'post_type' => 'campaign',
				'post_status' => 'publish',
			)
		);
		$order_data = array(
			'amount'         => '10.00',
			'donor_name'     => 'Ada Lovelace',
			'donor_email'    => 'ada@example.test',
			'campaign_id'    => (string) $campaign_id,
			'payment_method' => 'paypal',
		);
		$first_create = $this->ajax( 'giftflow_paypal_create_order', $order_data );
		$this->assertTrue( $first_create['success'], wp_json_encode( $first_create ) );
		$this->assertNotSame( '', $first_create['data']['orderID'] );
		$this->assertSandboxRequests();

		$this->requests = array();
		$second_create = $this->ajax( 'giftflow_paypal_create_order', $order_data );
		$this->assertTrue( $second_create['success'] );
		$this->assertNotSame( '', $second_create['data']['orderID'] );
		$this->assertSandboxRequests();

		$this->requests = array();
		$first_capture = $this->ajax(
			'giftflow_paypal_capture_order',
			array( 'orderID' => $first_create['data']['orderID'] )
		);
		$this->assertTrue( $first_capture['success'], wp_json_encode( $first_capture ) );
		$this->assertSame( 'Payment completed successfully', $first_capture['data']['message'] );
		$this->assertTrue( $first_capture['data']['data']['success'] );
		$this->assertSame( $first_capture['data']['data']['donation_id'], $first_capture['data']['donation_id'] );
		$first_donation = (int) $first_capture['data']['donation_id'];
		$this->assertGreaterThan( 0, $first_donation );
		$this->assertSame( $first_create['data']['orderID'], get_post_meta( $first_donation, '_paypal_order_id', true ) );
		$this->assertSame( 'sandbox', get_post_meta( $first_donation, '_paypal_environment', true ) );
		$this->assertSame( 'CAPTURE-1', get_post_meta( $first_donation, '_transaction_id', true ) );
		$this->assertSame( 'completed', get_post_meta( $first_donation, '_status', true ) );
		$this->assertSandboxRequests();

		$this->repository->set_active_environment( 'live' );
		$this->requests = array();
		$second_capture = $this->ajax(
			'giftflow_paypal_capture_order',
			array( 'orderID' => $second_create['data']['orderID'] )
		);
		$this->assertTrue( $second_capture['success'] );
		$second_donation = (int) $second_capture['data']['donation_id'];
		$this->assertSame( 'sandbox', get_post_meta( $second_donation, '_paypal_environment', true ) );
		$this->assertSandboxRequests();

		$this->repository->set_active_environment( 'sandbox' );
		update_option( $this->repository->product_option_key( 'sandbox' ), 'PRODUCT-1' );
		$this->requests = array();
		$subscription = $this->ajax(
			'giftflow_paypal_create_subscription',
			array(
				'amount'             => '12.00',
				'donor_name'         => 'Grace Hopper',
				'donor_email'        => 'grace@example.test',
				'campaign_id'        => (string) $campaign_id,
				'donation_type'      => 'recurring',
				'recurring_interval' => 'month',
			)
		);
		$this->assertTrue( $subscription['success'], wp_json_encode( $subscription ) );
		$this->assertSame( 'SUB-1', $subscription['data']['subscription_id'] );
		$this->assertSame( 'https://paypal.test/approve', $subscription['data']['approval_url'] );
		$parent_donation = (int) $subscription['data']['donation_id'];
		$this->assertGreaterThan( 0, $parent_donation );
		$this->assertSame( 'sandbox', get_post_meta( $parent_donation, '_paypal_environment', true ) );
		$this->assertSame( '1', get_post_meta( $parent_donation, '_is_subscription_parent', true ) );
		$this->assertSandboxRequests();

		$this->repository->set_active_environment( 'live' );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$this->requests = array();
		$cancellation = $this->ajax(
			'giftflow_paypal_cancel_subscription',
			array( 'donation_id' => $parent_donation )
		);
		$this->assertTrue( $cancellation['success'] );
		$this->assertSame( 'PayPal subscription cancelled successfully.', $cancellation['data']['message'] );
		$this->assertSandboxRequests();
	}

	/** Invalid AJAX nonces fail before PayPal receives a request. @return void */
	public function testPublicAjaxRejectsInvalidNonceWithoutPayPalWork(): void {
		$response = $this->ajax(
			'giftflow_paypal_create_order',
			array(
				'amount'      => '10.00',
				'donor_name'  => 'Ada Lovelace',
				'donor_email' => 'ada@example.test',
			),
			'invalid-nonce'
		);

		$this->assertFalse( $response['success'] );
		$this->assertSame( array(), $this->requests );
	}

	/** Execute AJAX and decode response. @param string $action Action. @param array $data Data. @param string|null $nonce Nonce override. @return array */
	private function ajax( string $action, array $data, ?string $nonce = null ): array {
		$_GET = array();
		$_POST = array_merge(
			$data,
			array(
				'action' => $action,
				'nonce' => null === $nonce ? wp_create_nonce( 'giftflow_paypal_nonce' ) : $nonce,
			)
		);
		$_REQUEST = $_POST;
		$this->_last_response = '';
		$methods     = array(
			'giftflow_paypal_create_order'        => 'ajax_create_order',
			'giftflow_paypal_capture_order'       => 'ajax_capture_order',
			'giftflow_paypal_create_subscription' => 'ajax_create_subscription',
			'giftflow_paypal_cancel_subscription' => 'ajax_cancel_subscription',
		);
		if ( null !== $nonce ) {
			$die_handler = static function () {
				return static function () {
					throw new \Error( '-1' );
				};
			};
			add_filter( 'wp_die_ajax_handler', $die_handler, 100 );
			try {
				$this->gateway->{$methods[ $action ]}();
			} catch ( \Error $error ) {
				unset( $error ); }
			remove_filter( 'wp_die_ajax_handler', $die_handler, 100 );
			return array( 'success' => false );
		}
		$die_handler = static function () {
			return static function () {
				throw new \Error( 'ajax-complete:' . base64_encode( (string) ob_get_clean() ) );
			};
		};
		add_filter( 'wp_die_ajax_handler', $die_handler, 100 );
		ob_start();
		try {
			$this->gateway->{$methods[ $action ]}();
		} catch ( \Error $error ) {
			$runtime_error = $error->getMessage(); }
		remove_filter( 'wp_die_ajax_handler', $die_handler, 100 );
		$response_body = isset( $runtime_error ) ? base64_decode( substr( $runtime_error, strlen( 'ajax-complete:' ) ) ) : '';
		if ( '' === $response_body ) {
			$response_body = $this->_last_response; }
		$json_start = strpos( $response_body, '{"success"' );
		$response = json_decode( false === $json_start ? $response_body : substr( $response_body, $json_start ), true );
		return is_array( $response ) ? $response : array();
	}

	/** Assert all business requests use Sandbox. @return void */
	private function assertSandboxRequests(): void {
		$business = array_filter(
			$this->requests,
			static function ( $call ) {
				return false === strpos( $call['url'], '/oauth2/token' );
			}
		);

		$this->assertNotEmpty( $business );

		foreach ( $business as $call ) {
			$this->assertStringContainsString( 'api.sandbox.paypal.com', $call['url'] );
		}
	}

	/** Fake PayPal resources. @param mixed $pre Preempt. @param array $args Args. @param string $url URL. @return array */
	public function transport( $pre, array $args, string $url ): array {
		$this->requests[] = array(
			'url' => $url,
			'args' => $args,
		);
		$method = strtoupper( isset( $args['method'] ) ? $args['method'] : 'GET' );
		$path = (string) wp_parse_url( $url, PHP_URL_PATH );
		if ( false !== strpos( $path, '/oauth2/token' ) ) {
			$app_id = false !== strpos( $url, 'sandbox' ) ? 'sandbox-app' : 'live-app';

			return $this->response(
				200,
				array(
					'access_token' => 'token',
					'expires_in' => 3600,
					'app_id' => $app_id,
				)
			);
		}
		if ( '/v2/checkout/orders' === $path ) {
			++$this->order_sequence;
			return $this->response( 201, array( 'id' => 'ORDER-' . $this->order_sequence ) ); }
		if ( false !== strpos( $path, '/capture' ) ) {
			return $this->response(
				201,
				array(
					'id'             => 'ORDER-1',
					'purchase_units' => array(
						array(
							'payments' => array(
								'captures' => array( array( 'id' => 'CAPTURE-1' ) ),
							),
						),
					),
				)
			);
		}
		if ( '/v1/catalogs/products' === $path ) {
			return $this->response( 201, array( 'id' => 'PRODUCT-1' ) ); }
		if ( '/v1/billing/plans' === $path ) {
			return $this->response( 201, array( 'id' => 'PLAN-1' ) ); }
		if ( '/v1/billing/subscriptions' === $path ) {
			return $this->response(
				201,
				array(
					'id' => 'SUB-1',
					'links' => array(
						array(
							'rel' => 'approve',
							'href' => 'https://paypal.test/approve',
						),
					),
				)
			); }
		if ( 'POST' === $method && false !== strpos( $path, '/cancel' ) ) {
			return $this->response( 204, array() ); }
		return $this->response(
			200,
			array(
				'id' => basename( $path ),
				'status' => 'ACTIVE',
			)
		);
	}
	/** Response helper. @param int $status Status. @param array $body Body. @return array */
	private function response( int $status, array $body ): array {
		return array(
			'response' => array( 'code' => $status ),
			'body' => wp_json_encode( $body ),
		); }
}
