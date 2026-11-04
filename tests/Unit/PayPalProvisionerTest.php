<?php
/**
 * PayPal webhook provisioner behavior tests.
 *
 * @package GiftFlow
 */

namespace GiftFlow\Tests\Unit;

use GiftFlow\PayPal\PayPalConnectionRepository;
use GiftFlow\PayPal\PayPalErrorCodes;
use GiftFlow\PayPal\PayPalWebhookEventRegistry;
use GiftFlow\PayPal\PayPalWebhookProvisioner;
use GiftFlow\Tests\Support\PayPalUnitTestCase;

/** Tests safe webhook provisioning and reconciliation. */
final class PayPalProvisionerTest extends PayPalUnitTestCase {

	/**
	 * Set up.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		\GiftFlow_Test_Option_Store::$options['admin_url'] = 'https://merchant.example.org/wp-admin/';
		\GiftFlow_Test_Option_Store::$options['home_url'] = 'https://merchant.example.org';
		$this->seed_credentials( 'sandbox' );
	}

	/**
	 * Connect creates one exact endpoint and preserves opposite profile.
	 *
	 * @return void
	 */
	public function testConnectCreatesOneExactEndpointAndPreservesOppositeProfile(): void {
		$live = $this->snapshot( $this->repository->get_profile( 'live' ) );
		$result = $this->provisioner()->connect( 'sandbox' );

		$this->assertTrue( $result['success'] );
		$this->assertSame( 1, $this->http->count( 'POST', '/v1/notifications/webhooks' ) );
		$this->assertSame( 1, $this->ledger->oauth_calls(), 'Connect must authenticate only once.' );
		$this->assertSame( $live, $this->snapshot( $this->repository->get_profile( 'live' ) ) );
		$endpoint = array_values( $this->ledger->webhooks() )[0];
		$this->assertSame( 'https://merchant.example.org/wp-admin/admin-ajax.php?action=giftflow_paypal_webhook', $endpoint['url'] );
		$this->assertSame( array_map( static fn( string $name ): array => array( 'name' => $name ), PayPalWebhookEventRegistry::required_events( false ) ), $endpoint['event_types'] );
	}

	/**
	 * Retries and held lock never create second endpoint.
	 *
	 * @return void
	 */
	public function testRetriesAndHeldLockNeverCreateSecondEndpoint(): void {
		$provisioner = $this->provisioner();
		$this->assertTrue( $provisioner->connect( 'sandbox' )['success'] );
		$this->assertTrue( $provisioner->connect( 'sandbox' )['success'] );
		$this->assertTrue( $provisioner->connect( 'sandbox' )['success'] );
		$this->assertTrue( $provisioner->connect( 'sandbox' )['success'] );
		$this->assertTrue( $this->repository->acquire_lock( 'sandbox' ) );
		$locked = $provisioner->connect( 'sandbox' );
		$this->repository->release_lock( 'sandbox' );

		$this->assertFalse( $locked['success'] );
		$this->assertSame( PayPalErrorCodes::LOCKED, $locked['code'] );
		$this->assertCount( 1, $this->ledger->webhooks() );
		$this->assertSame( 1, $this->http->count( 'POST', '/v1/notifications/webhooks' ) );
	}

	/**
	 * Check is read only and returns deterministic exact diff.
	 *
	 * @return void
	 */
	public function testCheckIsReadOnlyAndReturnsDeterministicExactDiff(): void {
		$provisioner = $this->provisioner();
		$this->assertTrue( $provisioner->connect( 'sandbox' )['success'] );
		$this->http->reset();

		$first = $provisioner->check( 'sandbox' );
		$second = $provisioner->check( 'sandbox' );

		$this->assertTrue( $first['success'] );
		$this->assertSame( 'in_sync', $first['status'] );
		$this->assertSame( array(), $first['diff']['fields'] );
		$this->assertSame( $first['diff']['checksum'], $second['diff']['checksum'] );
		$this->assertSame( 0, $this->http->count( 'POST', '/v1/notifications/webhooks' ) );
		$this->assertSame( 0, $this->http->count( 'PATCH', '/v1/notifications/webhooks/' ) );
	}

	/**
	 * Repair rejects stale diff without mutation.
	 *
	 * @return void
	 */
	public function testRepairRejectsStaleDiffWithoutMutation(): void {
		$provisioner = $this->provisioner();
		$this->assertTrue( $provisioner->connect( 'sandbox' )['success'] );
		$this->http->reset();
		$result = $provisioner->repair(
			'sandbox',
			array(
				'webhook_id' => 'WH-1',
				'checksum' => 'stale',
				'fields' => array( 'url' => array() ),
			)
		);

		$this->assertFalse( $result['success'] );
		$this->assertSame( PayPalErrorCodes::INVALID_DIFF, $result['code'] );
		$this->assertSame( 0, $this->http->count( 'PATCH', '/v1/notifications/webhooks/' ) );
	}

	/**
	 * Matching url without ownership is never patched.
	 *
	 * @return void
	 */
	public function testMatchingUrlWithoutOwnershipIsNeverPatched(): void {
		$url = 'https://merchant.example.org/wp-admin/admin-ajax.php?action=giftflow_paypal_webhook';
		$response = array(
			'response' => array( 'code' => 200 ),
			'body'     => wp_json_encode(
				array(
					'webhooks' => array(
						array(
							'id' => 'WH-FOREIGN',
							'url' => $url,
							'event_types' => array(),
						),
					),
				)
			),
		);
		$this->http->queue( 'GET', '/v1/notifications/webhooks', $response );
		$result = $this->provisioner()->connect( 'sandbox' );

		$this->assertFalse( $result['success'] );
		$this->assertSame( PayPalErrorCodes::ENDPOINT_OWNERSHIP_UNKNOWN, $result['code'] );
		$this->assertSame( 0, $this->http->count( 'PATCH', '/v1/notifications/webhooks/' ) );
	}

	/**
	 * Non public url stops before remote call.
	 *
	 * @return void
	 */
	public function testNonPublicUrlStopsBeforeRemoteCall(): void {
		\GiftFlow_Test_Option_Store::$options['admin_url'] = 'http://localhost/wp-admin/';
		$result = $this->provisioner()->connect( 'sandbox' );

		$this->assertFalse( $result['success'] );
		$this->assertSame( PayPalErrorCodes::NON_PUBLIC_URL, $result['code'] );
		$this->assertSame( array(), $this->http->calls() );
	}

	/**
	 * Rebind rejects foreign token without remote request.
	 *
	 * @return void
	 */
	public function testRebindRejectsForeignTokenWithoutRemoteRequest(): void {
		$this->seed_profile(
			'sandbox',
			array(
				'paypal_app_id' => 'OLD',
				'credential_fingerprint' => 'old-credential',
				'binding_fingerprint' => 'old-binding',
			)
		);
		$preview = $this->provisioner()->rebind_preview( 'sandbox' );
		$this->assertTrue( $preview['success'] );
		$this->assertContains( $this->repository->product_option_key( 'sandbox' ), $preview['plan']['abandoned_options'] );
		$this->http->reset();

		$result = $this->provisioner()->rebind( 'sandbox', 'foreign-token' );
		$this->assertFalse( $result['success'] );
		$this->assertSame( PayPalErrorCodes::REBIND_PLAN_STALE, $result['code'] );
		$this->assertSame( 0, $this->http->count( 'DELETE', '/v1/notifications/webhooks' ) );
	}
}
