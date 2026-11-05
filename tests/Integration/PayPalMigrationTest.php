<?php
/**
 * PayPal migration integration tests.
 *
 * @package GiftFlow
 */

// phpcs:disable Squiz.Commenting.ClassComment.Missing,Squiz.Commenting.FunctionComment.Missing -- PHPUnit test names describe behavior.
// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- Test comparison only.

namespace GiftFlow\Tests\Integration;

use GiftFlow\PayPal\PayPalConnectionMigrator;
use GiftFlow\PayPal\PayPalConnectionRepository;
use GiftFlow\PayPal\PayPalErrorCodes;

final class PayPalMigrationTest extends \WP_UnitTestCase {
	protected function setUp(): void {
		parent::setUp();
		delete_option( PayPalConnectionRepository::PAYMENT_OPTION );
	}

	protected function tearDown(): void {
		delete_option( PayPalConnectionRepository::PAYMENT_OPTION );
		parent::tearDown();
	}

	public function testLegacySettingsMigrateWithoutChangingCredentialsOrMode(): void {
		$paypal = array(
			'paypal_mode'                  => 'live',
			'paypal_sandbox_client_id'     => 'sandbox-client',
			'paypal_sandbox_client_secret' => 'sandbox-secret',
			'paypal_live_client_id'        => 'live-client',
			'paypal_live_client_secret'    => 'live-secret',
			'paypal_webhook_id'            => 'WH-legacy',
		);
		update_option( PayPalConnectionRepository::PAYMENT_OPTION, array( 'paypal' => $paypal ) );

		$repository = new PayPalConnectionRepository();
		$result     = ( new PayPalConnectionMigrator( $repository ) )->run();
		$stored     = get_option( PayPalConnectionRepository::PAYMENT_OPTION )['paypal'];

		$this->assertSame( PayPalConnectionMigrator::STATUS_MIGRATED, $result['status'] );
		foreach ( $paypal as $key => $value ) {
			$this->assertSame( $value, $stored[ $key ] );
		}
		$this->assertSame( 'legacy_manual', $repository->get_profile( 'sandbox' )['setup_status'] );
		$this->assertSame( 'legacy_manual', $repository->get_profile( 'live' )['setup_status'] );
		$this->assertSame( '', $repository->get_profile( 'sandbox' )['webhook_id'] );
		$this->assertSame( 'WH-legacy', $repository->get_profile( 'live' )['webhook_id'] );
		$this->assertSame( 'manual', $repository->get_profile( 'live' )['webhook_owner'] );
		$this->assertSame( PayPalConnectionRepository::default_profile(), array_replace( $repository->get_profile( 'sandbox' ), array( 'setup_status' => 'not_configured' ) ) );
	}

	public function testSecondMigrationIsAnExactNoOp(): void {
		update_option( PayPalConnectionRepository::PAYMENT_OPTION, array( 'paypal' => array( 'paypal_mode' => 'sandbox' ) ) );
		$migrator = new PayPalConnectionMigrator();
		$this->assertSame( PayPalConnectionMigrator::STATUS_MIGRATED, $migrator->run()['status'] );
		$before = serialize( get_option( PayPalConnectionRepository::PAYMENT_OPTION ) );
		$writes = 0;
		$filter = static function ( $value, $old_value, $option ) use ( &$writes ) {
			if ( PayPalConnectionRepository::PAYMENT_OPTION === $option ) {
				++$writes;
			}
			return $value;
		};
		add_filter( 'pre_update_option', $filter, 10, 3 );
		$result = $migrator->run();
		remove_filter( 'pre_update_option', $filter, 10 );

		$this->assertSame( PayPalConnectionMigrator::STATUS_NOOP, $result['status'] );
		$this->assertSame( 0, $writes );
		$this->assertSame( $before, serialize( get_option( PayPalConnectionRepository::PAYMENT_OPTION ) ) );
	}

	public function testMalformedPaypalSubtreeIsUntouchedAndReportsMigrationError(): void {
		$value = array( 'paypal' => 'malformed' );
		update_option( PayPalConnectionRepository::PAYMENT_OPTION, $value );
		$before = serialize( get_option( PayPalConnectionRepository::PAYMENT_OPTION ) );
		$result = ( new PayPalConnectionMigrator() )->run();

		$this->assertSame( PayPalConnectionMigrator::STATUS_ERROR, $result['status'] );
		$this->assertSame( PayPalErrorCodes::MIGRATION_ERROR, $result['code'] );
		$this->assertSame( $before, serialize( get_option( PayPalConnectionRepository::PAYMENT_OPTION ) ) );
	}
}
