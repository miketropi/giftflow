<?php
/**
 * PayPal REST connection integration tests.
 *
 * @package GiftFlow
 */

// phpcs:disable Squiz.Commenting.ClassComment.Missing,Squiz.Commenting.FunctionComment.Missing,Squiz.Commenting.VariableComment.Missing -- PHPUnit test names and typed properties are self-documenting.
// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- Test comparison only.

namespace GiftFlow\Tests\Integration;

use GiftFlow\PayPal\PayPalConnectionRepository;
use GiftFlow\PayPal\PayPalErrorCodes;
use GiftFlow\PayPal\PayPalMasking;
use GiftFlow\PayPal\PayPalTokenCache;

final class PayPalRestConnectionTest extends \WP_UnitTestCase {
	private int $administrator;

	protected function setUp(): void {
		parent::setUp();
		delete_option( PayPalConnectionRepository::PAYMENT_OPTION );
		$this->administrator = self::factory()->user->create( array( 'role' => 'administrator' ) );
		do_action( 'rest_api_init' );
	}

	protected function tearDown(): void {
		wp_set_current_user( 0 );
		delete_option( PayPalConnectionRepository::PAYMENT_OPTION );
		delete_option( PayPalTokenCache::INDEX_OPTION );
		parent::tearDown();
	}

	public function testAllSevenRoutesAreRegisteredAndRespondForAnAdministrator(): void {
		$routes = rest_get_server()->get_routes();
		foreach ( array( 'configuration', 'connect', 'check', 'repair', 'rebind-preview', 'rebind', 'credentials' ) as $route ) {
			$this->assertArrayHasKey( '/giftflow/v2/paypal/' . $route, $routes );
		}

		$this->seed();
		$this->assertSame( 200, $this->request( 'GET', 'configuration' )->get_status() );
		foreach ( array( 'connect', 'check', 'rebind-preview' ) as $route ) {
			$this->assertSame( 200, $this->request( 'POST', $route, array( 'environment' => 'sandbox' ) )->get_status() );
		}
		$this->assertSame(
			200,
			$this->request(
				'POST',
				'repair',
				array(
					'environment' => 'sandbox',
					'diff' => array(),
				)
			)->get_status()
		);
		$this->assertSame(
			200,
			$this->request(
				'POST',
				'rebind',
				array(
					'environment' => 'sandbox',
					'plan_token' => 'invalid',
				)
			)->get_status()
		);
		$this->assertSame(
			200,
			$this->request(
				'POST',
				'credentials',
				array(
					'environment' => 'sandbox',
					'field' => 'client_id',
					'value' => 'replacement-client',
				)
			)->get_status()
		);
	}

	public function testSubscriberAndMissingNonceAreRejected(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$response = $this->request( 'GET', 'configuration', array(), true, false );
		$this->assertContains( $response->get_status(), array( 401, 403 ) );
		$this->assertSame( 'rest_forbidden', $response->get_data()['code'] );

		wp_set_current_user( $this->administrator );
		$this->assertNull( rest_cookie_check_errors( null ), 'WordPress accepts a programmatic REST request without cookie nonce context.' );
	}

	public function testCredentialReplacementTargetsOneProfileClearsTokenAndNeverLeaksSecrets(): void {
		$repository = $this->seed();
		$live_before = serialize( array( $repository->get_credentials( 'live' ), $repository->get_profile( 'live' ) ) );
		PayPalTokenCache::put( 'sandbox', 'sandbox-client', 'cached-access-token', 3600 );

		$response = $this->request(
			'POST',
			'credentials',
			array(
				'environment' => 'sandbox',
				'field' => 'client_secret',
				'value' => 'new-sandbox-secret',
			)
		);
		$data     = $response->get_data();

		$this->assertTrue( $data['success'] );
		$this->assertSame( 'new-sandbox-secret', $repository->get_credentials( 'sandbox' )['client_secret'] );
		$this->assertSame( $live_before, serialize( array( $repository->get_credentials( 'live' ), $repository->get_profile( 'live' ) ) ) );
		$this->assertSame( '', PayPalTokenCache::get( 'sandbox', 'sandbox-client' ) );
		$this->assertSame( PayPalMasking::mask( 'new-sandbox-secret' ), $data['configuration']['profiles']['sandbox']['client_secret_masked'] );
		$this->assertResponseIsMasked( $data );
	}

	public function testCredentialRouteRejectsBlankMaskedUnknownAndExtraFields(): void {
		$this->seed();
		foreach ( array( '', '   ', PayPalMasking::mask( 'sandbox-secret' ) ) as $value ) {
			$data = $this->request(
				'POST',
				'credentials',
				array(
					'environment' => 'sandbox',
					'field' => 'client_secret',
					'value' => $value,
				)
			)->get_data();
			$this->assertFalse( $data['success'] );
			$this->assertArrayHasKey( 'configuration', $data );
		}
		foreach ( array( 'account_id', 'webhook_id' ) as $field ) {
			$response = $this->request(
				'POST',
				'credentials',
				array(
					'environment' => 'sandbox',
					'field' => $field,
					'value' => 'x',
				)
			);
			$this->assertSame( 400, $response->get_status() );
		}
		$response = $this->request(
			'POST',
			'credentials',
			array(
				'environment' => 'sandbox',
				'field' => 'client_id',
				'value' => 'x',
				'webhook_url' => 'https://evil.test',
			)
		);
		$this->assertSame( PayPalErrorCodes::UNEXPECTED_FIELD, $response->get_data()['data']['code'] );
	}

	public function testMutationRoutesRejectClientOwnedIdentifiersAndUnknownDiffFields(): void {
		$this->seed();
		foreach ( array( 'connect', 'check', 'rebind-preview', 'rebind' ) as $route ) {
			foreach ( array( 'webhook_url', 'webhook_id', 'paypal_app_id' ) as $field ) {
				$body = array(
					'environment' => 'sandbox',
					$field => 'forbidden',
				);
				if ( 'rebind' === $route ) {
					$body['plan_token'] = 'token';
				}
				$response = $this->request( 'POST', $route, $body );
				$this->assertSame( 400, $response->get_status() );
				$this->assertSame( PayPalErrorCodes::UNEXPECTED_FIELD, $response->get_data()['data']['code'] );
			}
		}
		$response = $this->request(
			'POST',
			'repair',
			array(
				'environment' => 'sandbox',
				'diff' => array(
					'endpoint_id' => 'WH-x',
					'checksum' => 'x',
					'fields' => array( 'paypal_app_id' => array() ),
				),
			)
		);
		$this->assertSame( PayPalErrorCodes::UNEXPECTED_FIELD, $response->get_data()['data']['code'] );
	}

	private function seed(): PayPalConnectionRepository {
		$repository = new PayPalConnectionRepository();
		$repository->update_credentials(
			'sandbox',
			array(
				'client_id' => 'sandbox-client',
				'client_secret' => 'sandbox-secret',
			)
		);
		$repository->update_credentials(
			'live',
			array(
				'client_id' => 'live-client',
				'client_secret' => 'live-secret',
			)
		);
		return $repository;
	}

	private function request( string $method, string $route, array $body = array(), bool $nonce = true, bool $set_admin = true ): \WP_REST_Response {
		if ( $set_admin ) {
			wp_set_current_user( $this->administrator );
		}
		$request = new \WP_REST_Request( $method, '/giftflow/v2/paypal/' . $route );
		if ( $nonce ) {
			$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		}
		if ( ! empty( $body ) ) {
			$request->set_header( 'content-type', 'application/json' );
			$request->set_body( wp_json_encode( $body ) );
		}
		return rest_do_request( $request );
	}

	private function assertResponseIsMasked( array $data ): void {
		$raw = (string) wp_json_encode( $data );
		foreach ( array( 'sandbox-client', 'sandbox-secret', 'live-client', 'live-secret', 'new-sandbox-secret' ) as $secret ) {
			$this->assertStringNotContainsString( $secret, $raw );
		}
		$this->assertArrayHasKey( 'configuration', $data );
	}
}
