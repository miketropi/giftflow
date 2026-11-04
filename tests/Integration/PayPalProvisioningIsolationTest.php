<?php
/**
 * PayPal provisioning integration tests.
 *
 * @package GiftFlow
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort -- Compact property type documentation is intentional in tests.
// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- Test comparison only.
// phpcs:disable Squiz.Commenting.FunctionComment.MissingParamTag -- PHPUnit helper summaries document their focused test purpose.
// phpcs:disable Squiz.Operators.IncrementDecrementUsage.NoBrackets -- Post-increment intentionally supplies the current fake identifier.

namespace GiftFlow\Tests\Integration;

use GiftFlow\PayPal\PayPalConnectionRepository;
use GiftFlow\PayPal\PayPalWebhookEventRegistry;
use GiftFlow\PayPal\PayPalWebhookProvisioner;

/** Verifies provisioning never crosses the environment boundary. */
final class PayPalProvisioningIsolationTest extends \WP_UnitTestCase {
	/** @var PayPalConnectionRepository */
	private $repository;

	/** @var array<int,array<string,mixed>> */
	private $requests = array();

	/** @var array<string,array<string,mixed>> */
	private $webhooks = array();

	/** @var int */
	private $sequence = 0;

	/** Prepare two complete profiles and the fake transport. @return void */
	protected function setUp(): void {
		parent::setUp();
		delete_option( PayPalConnectionRepository::PAYMENT_OPTION );
		delete_option( 'giftflow_paypal_token_index' );
		update_option( 'home', 'https://merchant.example.org' );
		update_option( 'siteurl', 'https://merchant.example.org' );
		$this->repository = new PayPalConnectionRepository();
		$this->seed( 'sandbox' );
		$this->seed( 'live' );
		$options = $this->repository->get_payment_options();
		$options['paypal']['paypal_recurring_enabled'] = '1';
		update_option( PayPalConnectionRepository::PAYMENT_OPTION, $options );
		add_filter( 'pre_http_request', array( $this, 'transport' ), 10, 3 );
	}

	/** Remove transport and test state. @return void */
	protected function tearDown(): void {
		remove_filter( 'pre_http_request', array( $this, 'transport' ), 10 );
		delete_option( PayPalConnectionRepository::PAYMENT_OPTION );
		parent::tearDown();
	}

	/** Sandbox operations preserve every Live byte and vice versa. @return void */
	public function testProvisioningMatrixPreservesOppositeEnvironment(): void {
		$this->exercise_matrix( 'sandbox', 'live' );
		$this->webhooks = array();
		$this->exercise_matrix( 'live', 'sandbox' );
	}

	/** First connect creates one endpoint with the exact registry events. @return void */
	public function testFirstSandboxConnectCreatesExactlyOneExactWebhook(): void {
		$this->repository->update_profile( 'sandbox', PayPalConnectionRepository::default_profile() );
		$before_live = $this->snapshot( 'live' );
		$this->requests = array();

		$result = ( new PayPalWebhookProvisioner( $this->repository ) )->connect( 'sandbox' );

		$this->assertTrue( $result['success'] );
		$this->assertSame( 1, $this->count_requests( 'POST', '/v1/oauth2/token' ) );
		$this->assertSame( 1, $this->count_requests( 'POST', '/v1/notifications/webhooks' ) );
		$create = $this->find_request( 'POST', '/v1/notifications/webhooks' );
		$body = json_decode( $create['args']['body'], true );
		$this->assertSame(
			array_map(
				static function ( $event ) {
					return array( 'name' => $event );
				},
				PayPalWebhookEventRegistry::required_events( true )
			),
			$body['event_types']
		);
		$this->assertNotSame( '', $this->repository->get_profile( 'sandbox' )['webhook_id'] );
		$this->assertSame( $before_live, $this->snapshot( 'live' ) );
	}

	/** Check performs no mutating HTTP request. @return void */
	public function testCheckUsesGetRequestsOnly(): void {
		$provisioner = new PayPalWebhookProvisioner( $this->repository );
		$this->assertTrue( $provisioner->connect( 'sandbox' )['success'] );
		$this->requests = array();
		$this->assertTrue( $provisioner->check( 'sandbox' )['success'] );
		$this->assertSame( 0, $this->count_mutating_requests() );
	}

	/** Run all operations against one environment. @param string $target Target. @param string $opposite Opposite. @return void */
	private function exercise_matrix( string $target, string $opposite ): void {
		$provisioner = new PayPalWebhookProvisioner( $this->repository );
		foreach ( array( 'connect', 'check' ) as $method ) {
			$before = $this->snapshot( $opposite );
			$result = $provisioner->$method( $target );
			$this->assertTrue( $result['success'], $method . ' failed for ' . $target );
			$this->assertSame( $before, $this->snapshot( $opposite ) );
		}
		$check = $provisioner->check( $target );
		$before = $this->snapshot( $opposite );
		$provisioner->repair( $target, $check['diff'] );
		$this->assertSame( $before, $this->snapshot( $opposite ) );
		$this->repository->update_credentials( $target, array( 'client_id' => $target . '-changed-client' ) );
		$preview = $provisioner->rebind_preview( $target );
		$this->assertTrue( $preview['success'] );
		$before = $this->snapshot( $opposite );
		$provisioner->rebind( $target, $preview['plan_token'] );
		$this->assertSame( $before, $this->snapshot( $opposite ) );
	}

	/** Seed one complete profile and scoped caches. @param string $environment Environment. @return void */
	private function seed( string $environment ): void {
		$client = $environment . '-client';
		$app = $environment . '-app';
		$this->repository->update_credentials(
			$environment,
			array(
				'client_id' => $client,
				'client_secret' => $environment . '-secret',
			)
		);
		$this->repository->update_profile(
			$environment,
			array(
				'webhook_id' => '',
				'webhook_url' => '',
				'webhook_owner' => 'plugin',
				'events' => PayPalWebhookEventRegistry::required_events( true ),
				'paypal_app_id' => $app,
				'credential_fingerprint' => PayPalConnectionRepository::credential_fingerprint( $environment, $client ),
				'binding_fingerprint' => $this->repository->binding_fingerprint( $environment, $app ),
				'setup_status' => 'configured',
			)
		);
		update_option( $this->repository->product_option_key( $environment ), strtoupper( $environment ) . '-PRODUCT' );
		update_option( $this->repository->plan_option_key( $environment, 'USD', 1000, 'monthly', 1 ), strtoupper( $environment ) . '-PLAN' );
	}

	/** Serialize all environment-owned state. @param string $environment Environment. @return string */
	private function snapshot( string $environment ): string {
		return serialize(
			array(
				$this->repository->get_credentials( $environment ),
				$this->repository->get_profile( $environment ),
				get_option( $this->repository->product_option_key( $environment ) ),
				get_option( $this->repository->plan_option_key( $environment, 'USD', 1000, 'monthly', 1 ) ),
			)
		);
	}

	/** Intercept PayPal HTTP. @param mixed $pre Preempt. @param array $args Arguments. @param string $url URL. @return array */
	public function transport( $pre, array $args, string $url ): array {
		$method = strtoupper( isset( $args['method'] ) ? $args['method'] : 'GET' );
		$this->requests[] = compact( 'method', 'url', 'args' );
		$path = (string) wp_parse_url( $url, PHP_URL_PATH );
		if ( '/v1/oauth2/token' === $path ) {
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
		if ( '/v1/notifications/webhooks' === $path && 'POST' === $method ) {
			$body = json_decode( $args['body'], true );
			$id = 'WH-' . ++$this->sequence;
			$this->webhooks[ $id ] = array_merge( array( 'id' => $id ), $body );
			return $this->response( 201, $this->webhooks[ $id ] );
		}
		if ( '/v1/notifications/webhooks' === $path ) {
			return $this->response( 200, array( 'webhooks' => array_values( $this->webhooks ) ) ); }
		$id = basename( $path );
		if ( isset( $this->webhooks[ $id ] ) ) {
			return 'PATCH' === $method ? $this->response( 204, array() ) : $this->response( 200, $this->webhooks[ $id ] ); }
		return $this->response( 404, array() );
	}

	/** WordPress HTTP response. @param int $status Status. @param array $body Body. @return array */
	private function response( int $status, array $body ): array {
		return array(
			'response' => array( 'code' => $status ),
			'body' => wp_json_encode( $body ),
		); }

	/** Count matching calls. @param string $method Method. @param string $path Path. @return int */
	private function count_requests( string $method, string $path ): int {
		return count(
			array_filter(
				$this->requests,
				static function ( $call ) use ( $method, $path ) {
					return $method === $call['method'] && false !== strpos( $call['url'], $path );
				}
			)
		);
	}

	/** Count all mutating calls. @return int */
	private function count_mutating_requests(): int {
		return count(
			array_filter(
				$this->requests,
				static function ( $call ) {
					return in_array( $call['method'], array( 'POST', 'PATCH', 'DELETE' ), true );
				}
			)
		);
	}

	/** Find one request. @param string $method Method. @param string $path Path. @return array */
	private function find_request( string $method, string $path ): array {
		foreach ( $this->requests as $call ) {
			if ( $method === $call['method'] && false !== strpos( $call['url'], $path ) ) {
				return $call;
			}
		}

		return array();
	}
}
