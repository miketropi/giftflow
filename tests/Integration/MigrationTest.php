<?php
/**
 * Stripe migration integration tests.
 *
 * @package GiftFlow
 */

// phpcs:disable Squiz.Commenting.ClassComment.Missing,Squiz.Commenting.FunctionComment.Missing -- PHPUnit test names describe behavior.
// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- Test comparison only.

namespace GiftFlow\Tests\Integration;

use GiftFlow\Stripe\StripeConnectionMigrator;
use GiftFlow\Stripe\StripeConnectionRepository;
use GiftFlow\Stripe\StripeErrorCodes;

final class MigrationTest extends \WP_UnitTestCase {
	protected function setUp(): void {
		parent::setUp();
		delete_option( StripeConnectionRepository::PAYMENT_OPTION );
	}

	protected function tearDown(): void {
		delete_option( StripeConnectionRepository::PAYMENT_OPTION );
		parent::tearDown();
	}

	public function testLegacyProfilesMigrateIndependentlyAndPreserveAllLegacyValues(): void {
		$legacy = array(
			'stripe'      => array(
				'stripe_enabled'                 => '1',
				'stripe_sandbox_publishable_key' => 'pk_test_ABC',
				'stripe_sandbox_secret_key'      => 'sk_test_ABC',
				'stripe_sandbox_webhook_secret'  => 'whsec_abc',
				'stripe_live_publishable_key'    => 'pk_live_XYZ',
				'stripe_live_secret_key'         => 'sk_live_XYZ',
				'stripe_live_webhook_secret'     => 'whsec_xyz',
				'stripe_webhook_enabled'         => '1',
			),
			'stripe_mode' => 'live',
		);
		update_option( StripeConnectionRepository::PAYMENT_OPTION, $legacy );

		$repository = new StripeConnectionRepository();
		$result     = ( new StripeConnectionMigrator( $repository ) )->run();
		$stored     = get_option( StripeConnectionRepository::PAYMENT_OPTION );

		$this->assertSame( 'migrated', $result['status'] );
		$this->assertSame( 'legacy_manual', $repository->get_profile( 'sandbox' )['setup_status'] );
		$this->assertSame( 'legacy_manual', $repository->get_profile( 'live' )['setup_status'] );
		$this->assertNotSame( $repository->get_profile( 'sandbox' ), $repository->get_profile( 'live' ) );
		foreach ( $legacy['stripe'] as $key => $value ) {
			$this->assertSame( $value, $stored['stripe'][ $key ] );
		}
		$this->assertSame( 'live', $stored['stripe_mode'] );
		$this->assertSame( 'live', $stored['stripe']['stripe_mode'] );
		$this->assertSame( 'live', $repository->get_active_environment() );
	}

	public function testKeysOnlyProfileIsNotConfiguredWithoutAffectingOtherProfile(): void {
		update_option( StripeConnectionRepository::PAYMENT_OPTION, array( 'stripe' => array( 'stripe_sandbox_publishable_key' => 'pk_test_ABC' ) ) );
		$repository = new StripeConnectionRepository();
		$result     = ( new StripeConnectionMigrator( $repository ) )->run();

		$this->assertSame( 'migrated', $result['status'] );
		$this->assertSame( 'not_configured', $repository->get_profile( 'sandbox' )['setup_status'] );
		$this->assertSame( 'not_configured', $repository->get_profile( 'live' )['setup_status'] );
		$this->assertTrue( $repository->get_profile( 'live' )['livemode'] );
		$this->assertFalse( $repository->get_profile( 'sandbox' )['livemode'] );
	}

	public function testMigrationIsIdempotent(): void {
		update_option( StripeConnectionRepository::PAYMENT_OPTION, array( 'stripe' => array( 'stripe_sandbox_publishable_key' => 'pk_test_ABC' ) ) );
		$migrator = new StripeConnectionMigrator();
		$this->assertSame( 'migrated', $migrator->run()['status'] );
		$before = serialize( get_option( StripeConnectionRepository::PAYMENT_OPTION ) );

		$this->assertSame( 'noop', $migrator->run()['status'] );
		$this->assertSame( $before, serialize( get_option( StripeConnectionRepository::PAYMENT_OPTION ) ) );
		$this->assertFalse( $migrator->needs_migration() );
	}

	public function testMalformedStripeSubtreeIsPreservedByteForByte(): void {
		$this->assertMalformedValueIsPreserved( array( 'stripe' => 'broken' ) );
	}

	public function testMalformedWholeOptionIsPreservedByteForByte(): void {
		$this->assertMalformedValueIsPreserved( 'broken' );
	}

	private function assertMalformedValueIsPreserved( $value ): void {
		update_option( StripeConnectionRepository::PAYMENT_OPTION, $value );
		$before = serialize( get_option( StripeConnectionRepository::PAYMENT_OPTION ) );
		$result = ( new StripeConnectionMigrator() )->run();
		$this->assertSame( 'error', $result['status'] );
		$this->assertSame( StripeErrorCodes::MIGRATION_ERROR, $result['code'] );
		$this->assertSame( $before, serialize( get_option( StripeConnectionRepository::PAYMENT_OPTION ) ) );
	}
}
