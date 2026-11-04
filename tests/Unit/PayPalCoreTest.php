<?php
/**
 * PayPal connection primitive behavior tests.
 *
 * @package GiftFlow
 */

namespace GiftFlow\Tests\Unit;

use GiftFlow\PayPal\PayPalConnectionMigrator;
use GiftFlow\PayPal\PayPalConnectionRepository;
use GiftFlow\PayPal\PayPalEnvironment;
use GiftFlow\PayPal\PayPalErrorCodes;
use GiftFlow\PayPal\PayPalMasking;
use GiftFlow\PayPal\PayPalTokenCache;
use GiftFlow\PayPal\PayPalWebhookEventRegistry;
use GiftFlow\Tests\Support\PayPalUnitTestCase;

/** Tests environment, persistence, cache, migration and registry contracts. */
final class PayPalCoreTest extends PayPalUnitTestCase {

	/**
	 * Environment selects exact hosts and credential keys.
	 *
	 * @return void
	 */
	public function testEnvironmentSelectsExactHostsAndCredentialKeys(): void {
		$this->assertSame( 'https://api.sandbox.paypal.com', PayPalEnvironment::api_base( 'sandbox' ) );
		$this->assertSame( 'https://api.paypal.com', PayPalEnvironment::api_base( 'live' ) );
		$this->assertSame( 'https://www.sandbox.paypal.com/sdk/js', PayPalEnvironment::sdk_base( 'sandbox' ) );
		$this->assertSame(
			array(
				'client_id' => 'paypal_live_client_id',
				'client_secret' => 'paypal_live_client_secret',
			),
			PayPalEnvironment::credential_option_keys( 'live' )
		);
		$this->assertSame( 'Live', PayPalEnvironment::label( 'live' ) );
	}

	/**
	 * Environment rejects invalid values and builds fallback identity.
	 *
	 * @return void
	 */
	public function testEnvironmentRejectsInvalidValuesAndBuildsFallbackIdentity(): void {
		$fallback = PayPalEnvironment::app_identity( 'sandbox', 'client' );
		$this->assertStringStartsWith( 'fp_', $fallback );
		$this->assertTrue( PayPalEnvironment::is_fallback_app_identity( $fallback ) );
		$this->assertSame( 'APP-1', PayPalEnvironment::app_identity( 'sandbox', 'client', ' APP-1 ' ) );
		$this->expectException( \InvalidArgumentException::class );
		PayPalEnvironment::api_base( 'staging' );
	}

	/**
	 * Profile update preserves opposite profile and normalizes input.
	 *
	 * @return void
	 */
	public function testProfileUpdatePreservesOppositeProfileAndNormalizesInput(): void {
		$this->seed_profile(
			'live',
			array(
				'webhook_id' => 'LIVE',
				'events' => array( 'B', 'A' ),
			)
		);
		$before = $this->snapshot( $this->repository->get_profile( 'live' ) );
		$this->repository->update_profile(
			'sandbox',
			array(
				'unknown' => 'drop',
				'events' => array( 'A', 'A', '' ),
				'webhook_owner' => 'bad',
				'setup_status' => 'bad',
				'last_error_code' => 'bad',
			)
		);
		$this->assertSame( $before, $this->snapshot( $this->repository->get_profile( 'live' ) ) );
		$profile = $this->repository->get_profile( 'sandbox' );
		$this->assertSame( array( 'A' ), $profile['events'] );
		$this->assertSame( PayPalConnectionRepository::OWNER_UNKNOWN, $profile['webhook_owner'] );
		$this->assertSame( PayPalConnectionRepository::STATUS_NOT_CONFIGURED, $profile['setup_status'] );
		$this->assertArrayNotHasKey( 'unknown', $profile );
	}

	/**
	 * Scoped keys change with environment and binding.
	 *
	 * @return void
	 */
	public function testScopedKeysChangeWithEnvironmentAndBinding(): void {
		$this->seed_profile(
			'sandbox',
			array(
				'credential_fingerprint' => 'cred-a',
				'binding_fingerprint' => 'bind-a',
			)
		);
		$this->seed_profile(
			'live',
			array(
				'credential_fingerprint' => 'cred-a',
				'binding_fingerprint' => 'bind-a',
			)
		);
		$this->assertNotSame( $this->repository->cache_scope( 'sandbox' ), $this->repository->cache_scope( 'live' ) );
		$this->assertNotSame( $this->repository->product_option_key( 'sandbox' ), $this->repository->product_option_key( 'live' ) );
		$this->assertNotSame( $this->repository->plan_option_key( 'sandbox', 'USD', 1000, 'MONTH', 1 ), $this->repository->plan_option_key( 'live', 'USD', 1000, 'MONTH', 1 ) );
		$this->assertSame( 'giftflow_paypal_product_id', $this->repository->legacy_product_option() );
		$this->assertSame( 'giftflow_paypal_plan_1000_MONTH', $this->repository->legacy_plan_option( 'USD', 1000, 'MONTH' ) );
	}

	/**
	 * Claims and locks are environment scoped and exclusive.
	 *
	 * @return void
	 */
	public function testClaimsAndLocksAreEnvironmentScopedAndExclusive(): void {
		$this->assertSame( 'claimed', $this->repository->claim_webhook_event( 'sandbox', 'event-1' ) );
		$this->assertSame( 'duplicate', $this->repository->claim_webhook_event( 'sandbox', 'event-1' ) );
		$this->assertSame( 'claimed', $this->repository->claim_webhook_event( 'live', 'event-1' ) );
		$this->assertTrue( $this->repository->acquire_lock( 'sandbox' ) );
		$this->assertFalse( $this->repository->acquire_lock( 'sandbox' ) );
		$this->repository->release_lock( 'sandbox' );
		$this->assertTrue( $this->repository->acquire_lock( 'sandbox' ) );
	}

	/**
	 * Masking and error mapping never expose raw values.
	 *
	 * @return void
	 */
	public function testMaskingAndErrorMappingNeverExposeRawValues(): void {
		$masked = PayPalMasking::mask( 'client-secret-value' );
		$this->assertTrue( PayPalMasking::is_masked( $masked ) );
		$this->assertStringNotContainsString( 'client-secret-value', $masked );
		$this->assertSame( '******', PayPalMasking::redact( 'secret' ) );
		$this->assertSame( '', PayPalErrorCodes::sanitize( 'UNKNOWN' ) );
		$this->assertSame( PayPalErrorCodes::INSUFFICIENT_PERMISSIONS, PayPalErrorCodes::from_http( 403, array() ) );
		$this->assertSame( PayPalErrorCodes::AUTHENTICATION_FAILED, PayPalErrorCodes::from_issue( 'INVALID_CLIENT' ) );
	}

	/**
	 * Token cache is scoped and environment forget is selective.
	 *
	 * @return void
	 */
	public function testTokenCacheIsScopedAndEnvironmentForgetIsSelective(): void {
		PayPalTokenCache::put( 'sandbox', 'client-a', 'sandbox-token', 3600 );
		PayPalTokenCache::put( 'live', 'client-a', 'live-token', 3600 );
		PayPalTokenCache::put( 'sandbox', 'client-b', 'other-token', 3600 );
		$this->assertSame( 'sandbox-token', PayPalTokenCache::get( 'sandbox', 'client-a' ) );
		PayPalTokenCache::forget_environment( 'sandbox' );
		$this->assertSame( '', PayPalTokenCache::get( 'sandbox', 'client-a' ) );
		$this->assertSame( '', PayPalTokenCache::get( 'sandbox', 'client-b' ) );
		$this->assertSame( 'live-token', PayPalTokenCache::get( 'live', 'client-a' ) );
	}

	/**
	 * Migration preserves credentials and moves legacy webhook once.
	 *
	 * @return void
	 */
	public function testMigrationPreservesCredentialsAndMovesLegacyWebhookOnce(): void {
		$paypal = array(
			'paypal_mode'                  => 'live',
			'paypal_sandbox_client_id'     => ' sid ',
			'paypal_sandbox_client_secret' => ' ss ',
			'paypal_live_client_id'        => ' lid ',
			'paypal_live_client_secret'    => ' ls ',
			'paypal_webhook_id'            => 'WH-OLD',
		);
		\GiftFlow_Test_Option_Store::$options[ PayPalConnectionRepository::PAYMENT_OPTION ] = array( 'paypal' => $paypal );
		$migrator = new PayPalConnectionMigrator( $this->repository );
		$this->assertSame( PayPalConnectionMigrator::STATUS_MIGRATED, $migrator->run()['status'] );
		$after = $this->paypal_subtree();
		foreach ( $paypal as $key => $value ) {
			$this->assertSame( $value, $after[ $key ] );
		}
		$this->assertSame( '', $this->repository->get_profile( 'sandbox' )['webhook_id'] );
		$this->assertSame( 'WH-OLD', $this->repository->get_profile( 'live' )['webhook_id'] );
		$this->assertSame( PayPalConnectionRepository::OWNER_MANUAL, $this->repository->get_profile( 'live' )['webhook_owner'] );
		$this->assertSame( PayPalConnectionMigrator::STATUS_NOOP, $migrator->run()['status'] );
	}

	/**
	 * Registry returns exact sorted sets and diff.
	 *
	 * @return void
	 */
	public function testRegistryReturnsExactSortedSetsAndDiff(): void {
		$base = PayPalWebhookEventRegistry::required_events( false );
		$all = PayPalWebhookEventRegistry::required_events( true );
		$this->assertSame( PayPalWebhookEventRegistry::sorted( PayPalWebhookEventRegistry::BASE_EVENTS ), $base );
		$this->assertSame( PayPalWebhookEventRegistry::sorted( array_merge( PayPalWebhookEventRegistry::BASE_EVENTS, PayPalWebhookEventRegistry::RECURRING_EVENTS ) ), $all );
		$this->assertSame(
			array(
				'add' => array( 'B' ),
				'remove' => array( 'C' ),
			),
			PayPalWebhookEventRegistry::diff( array( 'A', 'C' ), array( 'A', 'B' ) )
		);
		$this->assertTrue( PayPalWebhookEventRegistry::is_dispatched( 'PAYMENT.CAPTURE.COMPLETED', false ) );
		$this->assertFalse( PayPalWebhookEventRegistry::is_required( 'PAYMENT.SALE.COMPLETED', false ) );
	}
}
