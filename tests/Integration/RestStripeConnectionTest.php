<?php
/**
 * Stripe REST connection integration tests.
 *
 * @package GiftFlow
 */

// phpcs:disable Squiz.Commenting.ClassComment.Missing,Squiz.Commenting.FunctionComment.Missing,Squiz.Commenting.VariableComment.Missing -- PHPUnit test names and typed properties are self-documenting.
// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- Test comparison only.

namespace GiftFlow\Tests\Integration;

use GiftFlow\Stripe\StripeConnectionRepository;
use GiftFlow\Stripe\StripeErrorCodes;

final class RestStripeConnectionTest extends \WP_UnitTestCase {
	private int $administrator;

	protected function setUp(): void {
		parent::setUp();
		delete_option( StripeConnectionRepository::PAYMENT_OPTION );
		$this->administrator = self::factory()->user->create( array( 'role' => 'administrator' ) );
		do_action( 'rest_api_init' );
	}

	protected function tearDown(): void {
		wp_set_current_user( 0 );
		delete_option( StripeConnectionRepository::PAYMENT_OPTION );
		parent::tearDown();
	}

	public function testAllStripeConnectionRoutesAreRegistered(): void {
		$routes = rest_get_server()->get_routes();
		foreach ( array( 'configuration', 'connect', 'check', 'repair', 'rebind-preview', 'rebind', 'credentials' ) as $route ) {
			$this->assertArrayHasKey( '/giftflow/v2/stripe/' . $route, $routes );
		}
	}

	public function testAnonymousAndSubscriberRequestsAreForbidden(): void {
		foreach ( array( 0, self::factory()->user->create( array( 'role' => 'subscriber' ) ) ) as $user_id ) {
			wp_set_current_user( $user_id );
			$response = rest_do_request( new \WP_REST_Request( 'GET', '/giftflow/v2/stripe/configuration' ) );
			$this->assertSame( 401 === $response->get_status() || 403 === $response->get_status(), true );
			$this->assertSame( 'rest_forbidden', $response->get_data()['code'] );
		}
	}

	public function testAdministratorConfigurationIsCompleteAndDoesNotLeakSecrets(): void {
		update_option(
			StripeConnectionRepository::PAYMENT_OPTION,
			array(
				'stripe_mode' => 'live',
				'stripe' => array(
					'stripe_sandbox_publishable_key' => 'pk_test_visible_identifier',
					'stripe_sandbox_secret_key' => 'sk_test_DO_NOT_LEAK',
					'stripe_sandbox_webhook_secret' => 'whsec_DO_NOT_LEAK',
					'stripe_live_secret_key' => 'rk_live_DO_NOT_LEAK',
				),
			)
		);
		wp_set_current_user( $this->administrator );
		$response = rest_do_request( new \WP_REST_Request( 'GET', '/giftflow/v2/stripe/configuration' ) );
		$data = $response->get_data();
		$raw = wp_json_encode( $data );

		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( 'live', $data['active_environment'] );
		$this->assertArrayHasKey( 'sandbox', $data['profiles'] );
		$this->assertArrayHasKey( 'live', $data['profiles'] );
		$this->assertArrayHasKey( 'events_required', $data );
		$this->assertArrayHasKey( 'migration', $data );
		foreach ( array( 'sk_test_DO_NOT_LEAK', 'whsec_DO_NOT_LEAK', 'rk_live_DO_NOT_LEAK' ) as $forbidden ) {
			$this->assertStringNotContainsString( $forbidden, $raw );
		}
	}

	public function testInvalidEnvironmentDoesNotWriteOptions(): void {
		$before = $this->seedAndSnapshot();
		$response = $this->post( 'check', array( 'environment' => 'production' ) );
		$this->assertSame( 400, $response->get_status() );
		$this->assertSame( $before, serialize( get_option( StripeConnectionRepository::PAYMENT_OPTION ) ) );
	}

	public function testCheckRejectsUnknownIdentifierFields(): void {
		foreach ( array( 'webhook_id', 'account_id' ) as $field ) {
			$response = $this->post(
				'check',
				array(
					'environment' => 'sandbox',
					$field => 'forbidden',
				)
			);
			$this->assertSame( 400, $response->get_status() );
			$this->assertSame( StripeErrorCodes::UNEXPECTED_FIELD, $response->get_data()['data']['code'] );
		}
	}

	public function testConnectReportsTheUnreachableUrlBeforeContactingStripe(): void {
		$before = serialize( get_option( StripeConnectionRepository::PAYMENT_OPTION ) );

		$data = $this->post( 'connect', array( 'environment' => 'sandbox' ) )->get_data();

		// The test site is served over http, so the URL blocks auto configuration
		// before any credential is validated or any Stripe request is made.
		$this->assertFalse( $data['success'] );
		$this->assertSame( StripeErrorCodes::NON_PUBLIC_URL, $data['code'] );
		$this->assertSame( 'scheme', $data['reason'] );
		$this->assertStringStartsWith( 'http://', $data['url'] );
		$this->assertStringContainsString( 'admin-ajax.php?action=giftflow_stripe_webhook', $data['url'] );
		$this->assertStringContainsString( $data['url'], $data['message'] );
		$this->assertSame( array(), $data['phases'] );
		$this->assertSame( 0, $data['writes'] );
		$this->assertSame( $before, serialize( get_option( StripeConnectionRepository::PAYMENT_OPTION ) ) );
	}

	public function testBogusRepairDiffIsRejectedWithoutWritesOrNetworkAccess(): void {
		$before = $this->seedAndSnapshot();
		$bogus = array(
			'endpoint_id' => 'we_x',
			'checksum'    => 'deadbeef',
			'fields'      => array(
				'status' => array(
					'from' => 'disabled',
					'to' => 'enabled',
				),
			),
		);

		// Not connected yet: repair reports that instead of touching anything.
		$data = $this->post(
			'repair',
			array(
				'environment' => 'sandbox',
				'diff' => $bogus,
			)
		)->get_data();
		$this->assertFalse( $data['success'] );
		$this->assertSame( StripeErrorCodes::NOT_CONFIGURED, $data['code'] );
		$this->assertSame( 0, $data['writes'] );
		$this->assertSame( $before, serialize( get_option( StripeConnectionRepository::PAYMENT_OPTION ) ) );

		// Stored but unusable credentials must still fail without network access or writes.
		$repository = new StripeConnectionRepository();
		$repository->update_credentials(
			'sandbox',
			array(
				'publishable_key' => 'pk_test_wrong',
				'secret_key'      => 'sk_live_wrong_profile',
			)
		);
		$seeded = serialize( get_option( StripeConnectionRepository::PAYMENT_OPTION ) );

		$data = $this->post(
			'repair',
			array(
				'environment' => 'sandbox',
				'diff' => $bogus,
			)
		)->get_data();
		$this->assertFalse( $data['success'] );
		$this->assertSame( StripeErrorCodes::INVALID_SECRET_KEY, $data['code'] );
		$this->assertSame( 0, $data['writes'] );
		$this->assertSame( $seeded, serialize( get_option( StripeConnectionRepository::PAYMENT_OPTION ) ) );
	}

	public function testRepairRejectsIdentifierFieldsInsideTheDiff(): void {
		$response = $this->post(
			'repair',
			array(
				'environment' => 'sandbox',
				'diff'        => array(
					'endpoint_id'  => 'we_x',
					'checksum'     => 'deadbeef',
					'webhook_url'  => 'https://attacker.example/x',
					'fields'       => array(),
				),
			)
		);

		$this->assertSame( 400, $response->get_status() );
		$this->assertSame( StripeErrorCodes::UNEXPECTED_FIELD, $response->get_data()['data']['code'] );
	}

	public function testCredentialRouteReplacesOneValueForOneEnvironment(): void {
		$repository = new StripeConnectionRepository();
		$repository->update_credentials(
			'live',
			array(
				'publishable_key' => 'pk_live_keep_me',
				'secret_key'      => 'sk_live_keep_me',
				'webhook_secret'  => 'whsec_live_keep_me',
			)
		);

		$live_before = serialize( $repository->get_credentials( 'live' ) );
		$new_secret  = 'sk_test_updated_value_1234';

		$data = $this->post(
			'credentials',
			array(
				'environment' => 'sandbox',
				'field'       => 'secret_key',
				'value'       => $new_secret,
			)
		)->get_data();

		$this->assertTrue( $data['success'], (string) $data['code'] );
		$this->assertSame( 'secret_key', $data['credential'] );
		$this->assertSame( $new_secret, $repository->get_credentials( 'sandbox' )['secret_key'] );
		$this->assertSame( $live_before, serialize( $repository->get_credentials( 'live' ) ) );

		// The response carries the new value masked only.
		$this->assertStringNotContainsString( $new_secret, (string) wp_json_encode( $data ) );
		$this->assertSame(
			\GiftFlow\Stripe\StripeMasking::mask( $new_secret ),
			$data['configuration']['profiles']['sandbox']['secret_key_masked']
		);

		// A stored value re-submitted in masked form is rejected and nothing changes.
		$rejected = $this->post(
			'credentials',
			array(
				'environment' => 'sandbox',
				'field'       => 'secret_key',
				'value'       => (string) $data['configuration']['profiles']['sandbox']['secret_key_masked'],
			)
		)->get_data();

		$this->assertFalse( $rejected['success'] );
		$this->assertSame( StripeErrorCodes::INVALID_CREDENTIAL_VALUE, $rejected['code'] );
		$this->assertSame( $new_secret, $repository->get_credentials( 'sandbox' )['secret_key'] );
	}

	public function testCredentialRouteRejectsWrongPrefixBlankAndUnknownFields(): void {
		$option_before = serialize( get_option( StripeConnectionRepository::PAYMENT_OPTION ) );

		// Wrong profile for the key.
		$wrong = $this->post(
			'credentials',
			array(
				'environment' => 'sandbox',
				'field'       => 'secret_key',
				'value'       => 'sk_live_wrong_profile',
			)
		)->get_data();

		$this->assertFalse( $wrong['success'] );
		$this->assertSame( StripeErrorCodes::INVALID_SECRET_KEY, $wrong['code'] );
		$this->assertSame( 'secret_key', $wrong['credential'] );

		// Blank value.
		$blank = $this->post(
			'credentials',
			array(
				'environment' => 'sandbox',
				'field'       => 'publishable_key',
				'value'       => '   ',
			)
		)->get_data();

		$this->assertFalse( $blank['success'] );
		$this->assertSame( StripeErrorCodes::MISSING_CREDENTIALS, $blank['code'] );

		// Undeclared top-level field.
		$unknown = $this->post(
			'credentials',
			array(
				'environment' => 'sandbox',
				'field'       => 'secret_key',
				'value'       => 'sk_test_abc',
				'webhook_id'  => 'we_forbidden',
			)
		);

		$this->assertSame( 400, $unknown->get_status() );
		$this->assertSame( StripeErrorCodes::UNEXPECTED_FIELD, $unknown->get_data()['data']['code'] );

		// Field outside the allowlist never reaches the handler.
		$bad_field = $this->post(
			'credentials',
			array(
				'environment' => 'sandbox',
				'field'       => 'account_id',
				'value'       => 'acct_forbidden',
			)
		);

		$this->assertSame( 400, $bad_field->get_status() );

		$this->assertSame( $option_before, serialize( get_option( StripeConnectionRepository::PAYMENT_OPTION ) ) );
	}

	public function testConnectRouteRejectsStagedCredentialFields(): void {
		$rejected = $this->post(
			'connect',
			array(
				'environment'     => 'sandbox',
				'publishable_key' => 'pk_test_staged',
			)
		);

		$this->assertSame( 400, $rejected->get_status() );
		$this->assertSame( StripeErrorCodes::UNEXPECTED_FIELD, $rejected->get_data()['data']['code'] );
	}

	public function testRebindRequiresPlanToken(): void {
		$response = $this->post( 'rebind', array( 'environment' => 'sandbox' ) );
		$this->assertSame( 400, $response->get_status() );
	}

	private function post( string $route, array $body ): \WP_REST_Response {
		wp_set_current_user( $this->administrator );
		$request = new \WP_REST_Request( 'POST', '/giftflow/v2/stripe/' . $route );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( wp_json_encode( $body ) );
		return rest_do_request( $request );
	}

	private function seedAndSnapshot(): string {
		update_option( StripeConnectionRepository::PAYMENT_OPTION, array( 'stripe' => array( 'stripe_enabled' => '1' ) ) );
		return serialize( get_option( StripeConnectionRepository::PAYMENT_OPTION ) );
	}
}
