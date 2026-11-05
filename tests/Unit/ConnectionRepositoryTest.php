<?php
/**
 * Connection repository tests.
 *
 * @package GiftFlow
 */

namespace GiftFlow\Tests\Unit;

use GiftFlow\Stripe\StripeConnectionRepository;
use GiftFlow\Stripe\StripeErrorCodes;
use GiftFlow\Tests\Support\UnitTestCase;

/**
 * Environment-scoped persistence, locks, claims and masking.
 */
class ConnectionRepositoryTest extends UnitTestCase {

	/**
	 * Legacy credential option keys keep their values while profiles are added.
	 *
	 * @return void
	 */
	public function test_legacy_shape_is_read_without_mutation(): void {
		$this->set_payment_options(
			array(
				'stripe' => array(
					'stripe_mode'                     => 'live',
					'stripe_sandbox_secret_key'       => 'sk_test_sandbox',
					'stripe_live_secret_key'          => 'sk_live_live',
					'stripe_live_publishable_key'     => 'pk_live_live',
					'stripe_live_webhook_secret'      => 'whsec_live',
				),
				'paypal' => array( 'paypal_enabled' => '1' ),
			)
		);

		$before = $this->snapshot( $this->payment_options() );

		$this->assertSame( 'sk_test_sandbox', $this->repository->get_credentials( 'sandbox' )['secret_key'] );
		$this->assertSame( 'sk_live_live', $this->repository->get_credentials( 'live' )['secret_key'] );
		$this->assertSame( 'pk_live_live', $this->repository->get_credentials( 'live' )['publishable_key'] );
		$this->assertSame( 'whsec_live', $this->repository->get_credentials( 'live' )['webhook_secret'] );
		$this->assertTrue( $this->repository->has_secret_key( 'live' ) );
		$this->assertTrue( $this->repository->has_secret_key( 'sandbox' ) );

		$this->assertSame( $before, $this->snapshot( $this->payment_options() ), 'Reads must not write options.' );
	}

	/**
	 * The Stripe subtree is canonical for the active environment.
	 *
	 * @return void
	 */
	public function test_active_environment_resolution(): void {
		$this->set_payment_options( array( 'stripe' => array( 'stripe_mode' => 'live' ) ) );
		$this->assertSame( 'live', $this->repository->get_active_environment() );
		$this->assertSame( 'live', $this->repository->get_stored_active_environment() );

		// Legacy option-root value is only a fallback.
		$this->set_payment_options(
			array(
				'stripe_mode' => 'live',
				'stripe' => array(),
			)
		);
		$this->assertSame( 'live', $this->repository->get_stored_active_environment() );

		// The subtree wins over the legacy root value.
		$this->set_payment_options(
			array(
				'stripe_mode' => 'live',
				'stripe' => array( 'stripe_mode' => 'sandbox' ),
			)
		);
		$this->assertSame( 'sandbox', $this->repository->get_active_environment() );

		// Invalid values never select an environment.
		$this->set_payment_options(
			array(
				'stripe_mode' => 'production',
				'stripe' => array( 'stripe_mode' => 'prod' ),
			)
		);
		$this->assertSame( '', $this->repository->get_stored_active_environment() );
		$this->assertSame( 'sandbox', $this->repository->get_active_environment() );
	}

	/**
	 * Setting the active environment writes only that option key.
	 *
	 * @return void
	 */
	public function test_set_active_environment(): void {
		$this->set_payment_options(
			array(
				'stripe' => array(
					'stripe_live_secret_key' => 'sk_live_live',
				),
			)
		);

		$this->assertTrue( $this->repository->set_active_environment( 'live' ) );

		$stripe = $this->stripe_subtree();

		$this->assertSame( 'live', $stripe['stripe_mode'] );
		$this->assertSame( 'sk_live_live', $stripe['stripe_live_secret_key'] );
	}

	/**
	 * Profile patches merge into a single environment and normalise values.
	 *
	 * @return void
	 */
	public function test_update_profile_is_environment_scoped(): void {
		$this->repository->update_profile( 'live', array( 'webhook_id' => 'we_live' ) );

		$live_before    = $this->snapshot( $this->repository->get_profile( 'live' ) );
		$sandbox_before = $this->snapshot( $this->repository->get_profile( 'sandbox' ) );

		$this->repository->update_profile(
			'sandbox',
			array(
				'webhook_id'  => 'we_sandbox',
				'events'      => array( 'payment_intent.succeeded', 'payment_intent.succeeded', '', 5 ),
				'livemode'    => false,
				'unknown_key' => 'ignored',
			)
		);

		$sandbox = $this->repository->get_profile( 'sandbox' );

		$this->assertSame( 'we_sandbox', $sandbox['webhook_id'] );
		$this->assertSame( array( 'payment_intent.succeeded' ), $sandbox['events'] );
		$this->assertArrayNotHasKey( 'unknown_key', $sandbox );
		$this->assertNotSame( $sandbox_before, $this->snapshot( $sandbox ) );
		$this->assertSame( $live_before, $this->snapshot( $this->repository->get_profile( 'live' ) ) );
		$this->assertSame( 'we_live', $this->repository->get_profile( 'live' )['webhook_id'] );
	}

	/**
	 * Credential writes only touch the targeted environment.
	 *
	 * @return void
	 */
	public function test_update_credentials_is_environment_scoped(): void {
		$this->set_payment_options(
			array(
				'stripe' => array(
					'stripe_live_secret_key'          => 'sk_live_keep',
					'stripe_live_publishable_key'     => 'pk_live_keep',
					'stripe_live_webhook_secret'      => 'whsec_keep',
					'stripe_sandbox_secret_key'       => 'sk_test_old',
				),
			)
		);

		$live_before   = $this->snapshot( $this->repository->get_credentials( 'live' ) );
		$stripe_before = $this->stripe_subtree();

		$this->assertTrue(
			$this->repository->update_credentials(
				'sandbox',
				array(
					'publishable_key' => 'pk_test_new',
					'secret_key'      => 'sk_test_new',
				)
			)
		);

		$this->assertSame(
			array(
				'publishable_key' => 'pk_test_new',
				'secret_key'      => 'sk_test_new',
				'webhook_secret'  => '',
			),
			$this->repository->get_credentials( 'sandbox' )
		);

		$this->assertSame( $live_before, $this->snapshot( $this->repository->get_credentials( 'live' ) ) );

		$stripe_after = $this->stripe_subtree();

		$live_option_keys = \GiftFlow\Stripe\StripeEnvironment::credential_option_keys( 'live' );

		foreach ( $live_option_keys as $option_key ) {
			$this->assertSame(
				isset( $stripe_before[ $option_key ] ) ? $stripe_before[ $option_key ] : '',
				isset( $stripe_after[ $option_key ] ) ? $stripe_after[ $option_key ] : '',
				'Live option ' . $option_key . ' must not change.'
			);
		}

		$this->assertSame( 'sk_live_keep', isset( $stripe_after[ $live_option_keys['secret_key'] ] ) ? $stripe_after[ $live_option_keys['secret_key'] ] : '' );
		$this->assertSame( 'pk_live_keep', isset( $stripe_after[ $live_option_keys['publishable_key'] ] ) ? $stripe_after[ $live_option_keys['publishable_key'] ] : '' );
		$this->assertSame( 'whsec_keep', isset( $stripe_after[ $live_option_keys['webhook_secret'] ] ) ? $stripe_after[ $live_option_keys['webhook_secret'] ] : '' );
		$this->assertSame( 'sk_test_new', isset( $stripe_after['stripe_sandbox_secret_key'] ) ? $stripe_after['stripe_sandbox_secret_key'] : '' );
	}

	/**
	 * Absent credential keys are untouched; explicit empty values clear.
	 *
	 * @return void
	 */
	public function test_update_credentials_semantics(): void {
		$this->repository->update_credentials(
			'live',
			array(
				'publishable_key' => 'pk_live_a',
				'secret_key'      => 'sk_live_a',
				'webhook_secret'  => 'whsec_a',
			)
		);

		$this->repository->update_credentials( 'live', array( 'publishable_key' => 'pk_live_b' ) );

		$this->assertSame( 'sk_live_a', $this->repository->get_credentials( 'live' )['secret_key'] );
		$this->assertSame( 'pk_live_b', $this->repository->get_credentials( 'live' )['publishable_key'] );

		$this->repository->update_credentials( 'live', array( 'webhook_secret' => '' ) );

		$this->assertSame( '', $this->repository->get_credentials( 'live' )['webhook_secret'] );
		$this->assertSame( 'sk_live_a', $this->repository->get_credentials( 'live' )['secret_key'] );
	}

	/**
	 * Masked profiles expose no stored credential material.
	 *
	 * @return void
	 */
	public function test_masked_profile_contains_no_secrets(): void {
		$this->repository->update_credentials(
			'sandbox',
			array(
				'publishable_key' => 'pk_test_51AbCdEfGhIjKlMnOp',
				'secret_key'      => 'sk_test_51AbCdEfGhIjKlMnOp',
				'webhook_secret'  => 'whsec_abcdefghijklmnop',
			)
		);
		$this->repository->update_profile(
			'sandbox',
			array(
				'webhook_id' => 'we_1',
				'setup_status' => 'configured',
			)
		);

		$masked = $this->repository->get_masked_profile( 'sandbox' );
		$json   = (string) wp_json_encode( $masked );

		$this->assertStringNotContainsString( 'sk_test_51AbCdEfGhIjKlMnOp', $json );
		$this->assertStringNotContainsString( 'pk_test_51AbCdEfGhIjKlMnOp', $json );
		$this->assertStringNotContainsString( 'whsec_abcdefghijklmnop', $json );
		$this->assertTrue( $masked['configured'] );
		$this->assertTrue( $masked['has_secret_key'] );
		$this->assertSame( 'we_1', $masked['webhook_id'] );
	}

	/**
	 * Masked profiles carry server-formatted timestamps for the panel.
	 *
	 * @return void
	 */
	public function test_masked_profile_formats_timestamps(): void {
		\GiftFlow_Test_Option_Store::$options['date_format'] = 'Y-m-d';
		\GiftFlow_Test_Option_Store::$options['time_format'] = 'H:i';

		$masked = $this->repository->get_masked_profile( 'sandbox' );

		$this->assertSame( 0, $masked['last_checked_at'] );
		$this->assertSame( '', $masked['last_checked_display'] );
		$this->assertSame( '', $masked['last_verified_display'] );

		$this->repository->update_profile(
			'sandbox',
			array(
				'last_checked_at'          => 1793759999,
				'last_verified_webhook_at' => 1793759999,
			)
		);

		$masked = $this->repository->get_masked_profile( 'sandbox' );

		$this->assertSame( '2026-11-04 02:39', $masked['last_checked_display'] );
		$this->assertSame( $masked['last_checked_display'], $masked['last_verified_display'] );
	}

	/**
	 * Merge semantics keep only known keys and normalise values.
	 *
	 * @return void
	 */
	public function test_merge_profile_state_normalisation(): void {
		$merged = StripeConnectionRepository::merge_profile_state(
			array(
				'webhook_id' => 'we_1',
				'events' => array( 'a' ),
			),
			array(
				'webhook_owner'   => 'not-a-valid-owner',
				'setup_status'    => 'nonsense',
				'last_error_code' => 'NOT_A_CODE',
				'last_checked_at' => '1234',
				'recovery'        => array( 'operation' => 'create' ),
				'livemode'        => 1,
				'events'          => array( 'b', 'b', '' ),
				'unknown'         => 'dropped',
			)
		);

		$this->assertSame( 'we_1', $merged['webhook_id'] );
		$this->assertSame( StripeConnectionRepository::OWNER_UNKNOWN, $merged['webhook_owner'] );
		$this->assertSame( StripeConnectionRepository::STATUS_NOT_CONFIGURED, $merged['setup_status'] );
		$this->assertSame( '', $merged['last_error_code'] );
		$this->assertSame( 1234, $merged['last_checked_at'] );
		$this->assertSame( array( 'operation' => 'create' ), $merged['recovery'] );
		$this->assertTrue( $merged['livemode'] );
		$this->assertSame( array( 'b' ), $merged['events'] );
		$this->assertArrayNotHasKey( 'unknown', $merged );
	}

	/**
	 * A profile that was never written equals the documented defaults.
	 *
	 * @return void
	 */
	public function test_default_profile(): void {
		$defaults = StripeConnectionRepository::default_profile();
		$stored   = $this->repository->get_profile( 'sandbox' );

		$this->assertSame( $defaults, $stored );
		$this->assertSame( StripeConnectionRepository::STATUS_NOT_CONFIGURED, $stored['setup_status'] );
		$this->assertSame( '', $stored['webhook_id'] );
		$this->assertFalse( $this->repository->is_configured( 'sandbox' ) );
	}

	/**
	 * The installation UUID is generated once and kept.
	 *
	 * @return void
	 */
	public function test_installation_uuid_is_stable(): void {
		$first = $this->repository->identity()->uuid();

		$this->assertNotSame( '', $first );
		$this->assertSame( $first, ( new \GiftFlow\Core\InstallationIdentity() )->uuid() );
		$this->assertFalse( (bool) \GiftFlow_Test_Option_Store::$autoload[ \GiftFlow\Core\InstallationIdentity::UUID_OPTION ] );
	}

	/**
	 * Fingerprints are environment and account scoped.
	 *
	 * @return void
	 */
	public function test_fingerprints_are_scoped(): void {
		$identity = $this->repository->identity();

		$sandbox = $identity->fingerprint( 'sandbox', 'acct_1' );

		$this->assertSame( $sandbox, $identity->fingerprint( 'sandbox', 'acct_1' ) );
		$this->assertNotSame( $sandbox, $identity->fingerprint( 'live', 'acct_1' ) );
		$this->assertNotSame( $sandbox, $identity->fingerprint( 'sandbox', 'acct_2' ) );
		$this->assertSame( 64, strlen( $sandbox ) );
	}

	/**
	 * Locks are exclusive, recoverable after expiry and released explicitly.
	 *
	 * @return void
	 */
	public function test_locks(): void {
		$this->assertTrue( $this->repository->acquire_lock( 'sandbox' ) );
		$this->assertFalse( $this->repository->acquire_lock( 'sandbox' ) );

		// A different environment has its own lock.
		$this->assertTrue( $this->repository->acquire_lock( 'live' ) );

		$key = StripeConnectionRepository::LOCK_PREFIX . 'sandbox';
		\GiftFlow_Test_Option_Store::$options[ $key ] = time() - ( StripeConnectionRepository::LOCK_TTL + 10 );

		$this->assertTrue( $this->repository->acquire_lock( 'sandbox' ), 'Expired lock must be reclaimed.' );

		$this->repository->release_lock( 'sandbox' );

		$this->assertTrue( $this->repository->acquire_lock( 'sandbox' ) );
	}

	/**
	 * Event claims are idempotent and environment scoped.
	 *
	 * @return void
	 */
	public function test_event_claims(): void {
		$this->assertSame( 'claimed', $this->repository->claim_webhook_event( 'sandbox', 'acct_1', 'evt_1' ) );
		$this->assertSame( 'duplicate', $this->repository->claim_webhook_event( 'sandbox', 'acct_1', 'evt_1' ) );
		$this->assertSame( 'claimed', $this->repository->claim_webhook_event( 'sandbox', 'acct_1', 'evt_2' ) );
		$this->assertSame( 'claimed', $this->repository->claim_webhook_event( 'live', 'acct_1', 'evt_1' ) );
		$this->assertSame( 'claimed', $this->repository->claim_webhook_event( 'sandbox', 'acct_2', 'evt_1' ) );
		$this->assertSame( 'error', $this->repository->claim_webhook_event( 'sandbox', 'acct_1', '  ' ) );
	}

	/**
	 * Claim storage failure is reported distinctly from a duplicate.
	 *
	 * @return void
	 */
	public function test_claim_storage_failure(): void {
		\GiftFlow_Test_Option_Store::$fail_writes = true;

		$this->assertSame( 'error', $this->repository->claim_webhook_event( 'sandbox', 'acct_1', 'evt_1' ) );
	}

	/**
	 * Migration state round-trips.
	 *
	 * @return void
	 */
	public function test_migration_state(): void {
		$this->assertSame( array(), $this->repository->get_migration_state() );

		$this->assertTrue( $this->repository->update_migration_state( array( 'version' => 1 ) ) );
		$this->assertSame( 1, $this->repository->get_migration_state()['version'] );

		$this->assertTrue( $this->repository->update_migration_state( array( 'error' => StripeErrorCodes::MIGRATION_ERROR ) ) );
		$this->assertSame( 1, $this->repository->get_migration_state()['version'] );
		$this->assertSame( StripeErrorCodes::MIGRATION_ERROR, $this->repository->get_migration_state()['error'] );
	}

	/**
	 * Masked profiles carry sanitized error codes only.
	 *
	 * @return void
	 */
	public function test_masked_profile_sanitizes_error_codes(): void {
		$this->repository->update_profile( 'sandbox', array( 'last_error_code' => 'Raw Stripe exception text' ) );

		$masked = $this->repository->get_masked_profile( 'sandbox' );

		$this->assertSame( '', $masked['last_error_code'] );
		$this->assertSame( '', $masked['last_error_message'] );
	}

	/**
	 * Masked profiles expose a coarse state for the admin badge.
	 *
	 * @return void
	 */
	public function test_masked_profile_states(): void {
		$masked = $this->repository->get_masked_profile( 'sandbox' );

		$this->assertSame( 'not_configured', $masked['state'] );
		$this->assertSame( 'neutral', $masked['state_tone'] );
		$this->assertSame( 'Not configured', $masked['state_label'] );
		$this->assertFalse( $masked['verified'] );

		$this->repository->update_profile(
			'sandbox',
			array(
				'webhook_id'     => 'we_1',
				'setup_status'   => StripeConnectionRepository::STATUS_CONFIGURED,
				'last_error_code' => '',
				'recovery'       => array(),
			)
		);

		$masked = $this->repository->get_masked_profile( 'sandbox' );

		$this->assertSame( 'configured', $masked['state'] );
		$this->assertSame( 'success', $masked['state_tone'] );
		$this->assertSame( 'Configured', $masked['state_label'] );

		// One verified delivery promotes the badge.
		$this->repository->mark_webhook_verified( 'sandbox' );

		$masked = $this->repository->get_masked_profile( 'sandbox' );

		$this->assertSame( 'verified', $masked['state'] );
		$this->assertSame( 'success', $masked['state_tone'] );
		$this->assertSame( 'Verified', $masked['state_label'] );
		$this->assertTrue( $masked['verified'] );

		// A reconcile-needed profile outranks the configured state.
		$this->repository->update_profile( 'sandbox', array( 'recovery' => array( 'operation' => 'create' ) ) );

		$masked = $this->repository->get_masked_profile( 'sandbox' );

		$this->assertSame( 'needs_attention', $masked['state'] );
		$this->assertSame( 'danger', $masked['state_tone'] );
		$this->assertSame( 'Needs attention', $masked['state_label'] );

		// Legacy manual keys are reported separately.
		$this->repository->update_profile(
			'sandbox',
			array(
				'webhook_id'   => '',
				'recovery'     => array(),
				'setup_status' => StripeConnectionRepository::STATUS_LEGACY_MANUAL,
			)
		);

		$this->assertSame( 'manual', $this->repository->get_masked_profile( 'sandbox' )['state'] );
		$this->assertSame( 'warning', $this->repository->get_masked_profile( 'sandbox' )['state_tone'] );
	}

	/**
	 * Feature flags reflect stored values.
	 *
	 * @return void
	 */
	public function test_feature_flags(): void {
		$this->assertFalse( $this->repository->is_recurring_enabled() );
		$this->assertTrue( $this->repository->is_webhook_enabled() );

		$this->set_payment_options(
			array(
				'stripe' => array(
					'stripe_recurring_enabled' => '1',
					'stripe_webhook_enabled'   => '0',
				),
			)
		);

		$this->assertTrue( $this->repository->is_recurring_enabled() );
		$this->assertFalse( $this->repository->is_webhook_enabled() );

		$this->set_payment_options(
			array(
				'stripe' => array(
					'stripe_recurring_enabled' => false,
					'stripe_webhook_enabled'   => 'off',
				),
			)
		);

		$this->assertFalse( $this->repository->is_recurring_enabled() );
		$this->assertFalse( $this->repository->is_webhook_enabled() );
	}
}
