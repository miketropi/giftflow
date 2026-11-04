<?php
/**
 * Additional PayPal repository contract tests.
 *
 * @package GiftFlow
 */

namespace GiftFlow\Tests\Unit;

use GiftFlow\PayPal\PayPalConnectionRepository;
use GiftFlow\PayPal\PayPalTokenCache;
use GiftFlow\Tests\Support\PayPalUnitTestCase;

/** Tests display-safe repository behavior and identity persistence. */
final class PayPalRepositoryExtrasTest extends PayPalUnitTestCase {

	/** Records the app identity and both associated fingerprints.
	 *
	 * @return void
	 */
	public function testRecordAppIdentityWritesAppAndBothFingerprints(): void {
		$this->assertTrue( $this->repository->record_app_identity( 'sandbox', 'APP-123', 'client-id' ) );

		$profile = $this->repository->get_profile( 'sandbox' );
		$this->assertSame( 'APP-123', $profile['paypal_app_id'] );
		$this->assertSame( PayPalConnectionRepository::credential_fingerprint( 'sandbox', 'client-id' ), $profile['credential_fingerprint'] );
		$this->assertSame( $this->repository->binding_fingerprint( 'sandbox', 'APP-123' ), $profile['binding_fingerprint'] );
	}

	/** Ensures masked profiles expose only the pinned display-safe keys.
	 *
	 * @return void
	 */
	public function testMaskedProfileHasPinnedKeysAndNeverExposesCredentials(): void {
		$this->seed_credentials( 'sandbox', 'raw-client-id', 'raw-client-secret' );
		$this->seed_profile(
			'sandbox',
			array(
				'webhook_id' => 'WH-1',
				'events' => array( 'A' ),
			)
		);

		$profile = $this->repository->get_masked_profile( 'sandbox' );
		$expected = array(
			'environment',
			'label',
			'setup_status',
			'configured',
			'verified',
			'state',
			'state_label',
			'state_tone',
			'recovery_pending',
			'client_id_masked',
			'client_secret_masked',
			'has_client_id',
			'has_client_secret',
			'webhook_id',
			'webhook_url',
			'webhook_owner',
			'events',
			'event_count',
			'paypal_app_id',
			'app_identity_fallback',
			'catches_attention',
			'last_checked_at',
			'last_verified_webhook_at',
			'last_checked_display',
			'last_verified_display',
			'last_error_code',
			'last_error_message',
			'previous_webhook_ids',
		);

		$this->assertSame( $expected, array_keys( $profile ) );
		$this->assertStringNotContainsString( 'raw-client-id', serialize( $profile ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- Test comparison only.
		$this->assertStringNotContainsString( 'raw-client-secret', serialize( $profile ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- Test comparison only.
	}

	/** Clears only the selected environment's token cache.
	 *
	 * @return void
	 */
	public function testForgetTokenCacheClearsOnlySelectedEnvironment(): void {
		PayPalTokenCache::put( 'sandbox', 'client-a', 'sandbox-token', 3600 );
		PayPalTokenCache::put( 'live', 'client-a', 'live-token', 3600 );

		$this->repository->forget_token_cache( 'sandbox' );

		$this->assertSame( '', PayPalTokenCache::get( 'sandbox', 'client-a' ) );
		$this->assertSame( 'live-token', PayPalTokenCache::get( 'live', 'client-a' ) );
	}
}
