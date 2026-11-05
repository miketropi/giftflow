<?php
/**
 * PayPal settings preservation integration tests.
 *
 * @package GiftFlow
 */

// phpcs:disable Squiz.Commenting.ClassComment.Missing,Squiz.Commenting.FunctionComment.Missing -- PHPUnit test names describe behavior.

namespace GiftFlow\Tests\Integration;

use GiftFlow\PayPal\PayPalConnectionRepository;

final class PayPalSettingsPreservationTest extends \WP_UnitTestCase {
	protected function setUp(): void {
		parent::setUp();
		delete_option( PayPalConnectionRepository::PAYMENT_OPTION );
	}

	protected function tearDown(): void {
		delete_option( PayPalConnectionRepository::PAYMENT_OPTION );
		parent::tearDown();
	}

	public function testBulkSavePreservesPanelOwnedStateAndStoredCredentials(): void {
		$connections = array(
			'sandbox' => array_replace( PayPalConnectionRepository::default_profile(), array( 'webhook_id' => 'WH-sandbox' ) ),
			'live'    => array_replace( PayPalConnectionRepository::default_profile(), array( 'webhook_id' => 'WH-live' ) ),
		);
		$migration = array(
			'version' => 1,
			'migrated_at' => 123,
			'error' => '',
		);
		$paypal    = array(
			'paypal_mode'                  => 'live',
			'paypal_sandbox_client_id'     => 'sandbox-client',
			'paypal_sandbox_client_secret' => 'sandbox-secret',
			'paypal_live_client_id'        => 'live-client',
			'paypal_live_client_secret'    => 'live-secret',
			'paypal_connections'           => $connections,
			'paypal_connection_migration'  => $migration,
		);
		update_option( PayPalConnectionRepository::PAYMENT_OPTION, array( 'paypal' => $paypal ) );

		$result = giftflow_sanitize_payment_options(
			array(
				'paypal' => array(
					'paypal_sandbox_client_id'     => '',
					'paypal_sandbox_client_secret' => '   ',
					'paypal_enabled'               => '1',
					'paypal_webhook_enabled'       => '0',
					'paypal_recurring_enabled'     => '1',
				),
			)
		);

		$this->assertSame( 'live', $result['paypal']['paypal_mode'] );
		$this->assertSame( $connections, $result['paypal']['paypal_connections'] );
		$this->assertSame( $migration, $result['paypal']['paypal_connection_migration'] );
		foreach ( array( 'paypal_sandbox_client_id', 'paypal_sandbox_client_secret', 'paypal_live_client_id', 'paypal_live_client_secret' ) as $key ) {
			$this->assertSame( $paypal[ $key ], $result['paypal'][ $key ] );
		}
		$this->assertSame( '1', $result['paypal']['paypal_enabled'] );
		$this->assertSame( '0', $result['paypal']['paypal_webhook_enabled'] );
		$this->assertSame( '1', $result['paypal']['paypal_recurring_enabled'] );
	}
}
