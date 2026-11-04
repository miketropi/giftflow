<?php
/**
 * Environment-scoped persistence for Stripe connection state.
 *
 * Every mutation in this class targets a single environment. The opposite
 * profile is read, never rewritten: existing credential option keys keep their
 * historical names so payment behaviour is unchanged, while new per-environment
 * connection state lives under `stripe_connections[sandbox|live]`.
 *
 * @package GiftFlow
 * @subpackage Stripe
 */

namespace GiftFlow\Stripe;

use GiftFlow\Core\InstallationIdentity;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reads and writes Stripe payment options with explicit environment scope.
 */
final class StripeConnectionRepository {

	/**
	 * Option holding every payment gateway setting.
	 *
	 * @var string
	 */
	public const PAYMENT_OPTION = 'giftflow_payment_options';

	/**
	 * Subtree key holding per-environment connection state.
	 *
	 * @var string
	 */
	public const CONNECTIONS_KEY = 'stripe_connections';

	/**
	 * Subtree key holding migration metadata.
	 *
	 * @var string
	 */
	public const MIGRATION_KEY = 'stripe_connection_migration';

	/**
	 * Option name prefix for environment locks.
	 *
	 * @var string
	 */
	public const LOCK_PREFIX = 'giftflow_stripe_lock_';

	/**
	 * Option name prefix for webhook event claims.
	 *
	 * @var string
	 */
	public const CLAIM_PREFIX = 'giftflow_stripe_claim_';

	/**
	 * Option holding the last claim-prune timestamp.
	 *
	 * @var string
	 */
	public const CLAIMS_PRUNED_OPTION = 'giftflow_stripe_claims_pruned_at';

	/**
	 * Seconds before a lock is considered abandoned.
	 *
	 * @var int
	 */
	public const LOCK_TTL = 180;

	/**
	 * Seconds a webhook event claim is retained.
	 *
	 * @var int
	 */
	public const CLAIM_TTL = 604800;

	/**
	 * Minimum seconds between claim prune passes.
	 *
	 * @var int
	 */
	public const CLAIM_PRUNE_INTERVAL = 3600;

	/**
	 * Setup status: nothing configured for the environment yet.
	 *
	 * @var string
	 */
	public const STATUS_NOT_CONFIGURED = 'not_configured';

	/**
	 * Setup status: credentials imported from a pre-1.2 manual setup.
	 *
	 * @var string
	 */
	public const STATUS_LEGACY_MANUAL = 'legacy_manual';

	/**
	 * Setup status: the environment is fully configured.
	 *
	 * @var string
	 */
	public const STATUS_CONFIGURED = 'configured';

	/**
	 * Setup status: a remote create needs reconciliation before retrying.
	 *
	 * @var string
	 */
	public const STATUS_RECOVERY_PENDING = 'recovery_pending';

	/**
	 * Setup status: the last operation failed and state is unchanged.
	 *
	 * @var string
	 */
	public const STATUS_ERROR = 'error';

	/**
	 * Webhook ownership: created by this plugin installation.
	 *
	 * @var string
	 */
	public const OWNER_PLUGIN = 'plugin';

	/**
	 * Webhook ownership: created outside GiftFlow.
	 *
	 * @var string
	 */
	public const OWNER_MANUAL = 'manual';

	/**
	 * Webhook ownership: ownership has not been established.
	 *
	 * @var string
	 */
	public const OWNER_UNKNOWN = 'unknown';

	/**
	 * Installation identity helper.
	 *
	 * @var InstallationIdentity
	 */
	private InstallationIdentity $identity;

	/**
	 * Constructor.
	 *
	 * @param InstallationIdentity|null $identity Optional identity helper.
	 */
	public function __construct( ?InstallationIdentity $identity = null ) {
		$this->identity = $identity ?? new InstallationIdentity();
	}

	/**
	 * Default state for a single environment profile.
	 *
	 * @return array<string, mixed>
	 */
	public static function default_profile(): array {
		return array(
			'webhook_id'                => '',
			'webhook_url'               => '',
			'webhook_owner'             => self::OWNER_UNKNOWN,
			'events'                    => array(),
			'stripe_account_id'         => '',
			'binding_fingerprint'       => '',
			'api_version'               => '',
			'setup_status'              => self::STATUS_NOT_CONFIGURED,
			'livemode'                  => false,
			'last_checked_at'           => 0,
			'last_verified_webhook_at'  => 0,
			'last_error_code'           => '',
			'last_error_at'             => 0,
			'recovery'                  => array(),
			'previous_webhook_ids'      => array(),
		);
	}

	/**
	 * Merge a patch into a profile state, keeping only known keys.
	 *
	 * Pure function: no option access, used directly by unit tests.
	 *
	 * @param array<string, mixed> $existing Existing profile state.
	 * @param array<string, mixed> $patch    Patch to apply.
	 * @return array<string, mixed>
	 */
	public static function merge_profile_state( array $existing, array $patch ): array {
		$defaults = self::default_profile();
		$merged   = array_merge( $defaults, array_intersect_key( $existing, $defaults ) );

		foreach ( $patch as $key => $value ) {
			if ( ! array_key_exists( $key, $defaults ) ) {
				continue;
			}

			switch ( $key ) {
				case 'events':
					$merged['events'] = self::normalize_events( $value );
					break;
				case 'previous_webhook_ids':
					$merged['previous_webhook_ids'] = self::normalize_string_list( $value );
					break;
				case 'recovery':
					$merged['recovery'] = is_array( $value ) ? $value : array();
					break;
				case 'livemode':
					$merged['livemode'] = (bool) $value;
					break;
				case 'last_checked_at':
				case 'last_verified_webhook_at':
				case 'last_error_at':
					$merged[ $key ] = (int) $value;
					break;
				default:
					$merged[ $key ] = (string) $value;
					break;
			}
		}

		$merged['webhook_owner']  = self::normalize_owner( $merged['webhook_owner'] );
		$merged['setup_status']   = self::normalize_status( $merged['setup_status'] );
		$merged['last_error_code'] = StripeErrorCodes::sanitize( $merged['last_error_code'] );

		return $merged;
	}

	/**
	 * Normalize an event list to unique, non-empty strings.
	 *
	 * @param mixed $events Candidate events.
	 * @return string[]
	 */
	public static function normalize_events( $events ): array {
		return self::normalize_string_list( $events );
	}

	/**
	 * Normalize a list to unique, non-empty strings preserving order.
	 *
	 * @param mixed $values Candidate list.
	 * @return string[]
	 */
	public static function normalize_string_list( $values ): array {
		if ( ! is_array( $values ) ) {
			return array();
		}

		$normalized = array();

		foreach ( $values as $value ) {
			if ( ! is_string( $value ) ) {
				continue;
			}

			$value = trim( $value );

			if ( '' === $value || in_array( $value, $normalized, true ) ) {
				continue;
			}

			$normalized[] = $value;
		}

		return $normalized;
	}

	/**
	 * Normalize a webhook owner value.
	 *
	 * @param mixed $owner Candidate owner.
	 * @return string
	 */
	private static function normalize_owner( $owner ): string {
		$allowed = array( self::OWNER_PLUGIN, self::OWNER_MANUAL, self::OWNER_UNKNOWN );

		return is_string( $owner ) && in_array( $owner, $allowed, true ) ? $owner : self::OWNER_UNKNOWN;
	}

	/**
	 * Normalize a setup status value.
	 *
	 * @param mixed $status Candidate status.
	 * @return string
	 */
	private static function normalize_status( $status ): string {
		$allowed = array(
			self::STATUS_NOT_CONFIGURED,
			self::STATUS_LEGACY_MANUAL,
			self::STATUS_CONFIGURED,
			self::STATUS_RECOVERY_PENDING,
			self::STATUS_ERROR,
		);

		return is_string( $status ) && in_array( $status, $allowed, true ) ? $status : self::STATUS_NOT_CONFIGURED;
	}

	/**
	 * Full payment options array.
	 *
	 * @return array<string, mixed>
	 */
	public function get_payment_options(): array {
		$options = get_option( self::PAYMENT_OPTION, array() );

		return is_array( $options ) ? $options : array();
	}

	/**
	 * Stripe subtree of the payment options.
	 *
	 * @return array<string, mixed>
	 */
	public function get_stripe_subtree(): array {
		$options = $this->get_payment_options();
		$stripe  = isset( $options[ StripeEnvironment::OPTION_ROOT ] ) ? $options[ StripeEnvironment::OPTION_ROOT ] : array();

		return is_array( $stripe ) ? $stripe : array();
	}

	/**
	 * The active payment environment stored in options, or an empty string.
	 *
	 * The Stripe subtree is canonical; the former option-root `stripe_mode` is
	 * read only as a legacy fallback.
	 *
	 * @return string
	 */
	public function get_stored_active_environment(): string {
		$stripe = $this->get_stripe_subtree();

		if ( StripeEnvironment::is_valid( $stripe[ StripeEnvironment::MODE_OPTION ] ?? null ) ) {
			return (string) $stripe[ StripeEnvironment::MODE_OPTION ];
		}

		$options = $this->get_payment_options();

		if ( StripeEnvironment::is_valid( $options[ StripeEnvironment::MODE_OPTION ] ?? null ) ) {
			return (string) $options[ StripeEnvironment::MODE_OPTION ];
		}

		return '';
	}

	/**
	 * Active payment environment, defaulting to sandbox.
	 *
	 * @return string
	 */
	public function get_active_environment(): string {
		$stored = $this->get_stored_active_environment();

		return '' === $stored ? StripeEnvironment::SANDBOX : $stored;
	}

	/**
	 * Persist the active payment environment.
	 *
	 * @param string $environment Environment id.
	 * @return bool
	 */
	public function set_active_environment( string $environment ): bool {
		$environment = StripeEnvironment::assert_valid( $environment );

		return $this->commit_stripe_state( array( StripeEnvironment::MODE_OPTION => $environment ) );
	}

	/**
	 * Connection state for one environment.
	 *
	 * @param string $environment Environment id.
	 * @return array<string, mixed>
	 */
	public function get_profile( string $environment ): array {
		$environment = StripeEnvironment::assert_valid( $environment );
		$stripe      = $this->get_stripe_subtree();
		$connections = isset( $stripe[ self::CONNECTIONS_KEY ] ) ? $stripe[ self::CONNECTIONS_KEY ] : array();
		$profile     = is_array( $connections ) && isset( $connections[ $environment ] ) && is_array( $connections[ $environment ] )
			? $connections[ $environment ]
			: array();

		return self::merge_profile_state( $profile, array() );
	}

	/**
	 * Connection state for both environments.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function get_profiles(): array {
		return array(
			StripeEnvironment::SANDBOX => $this->get_profile( StripeEnvironment::SANDBOX ),
			StripeEnvironment::LIVE    => $this->get_profile( StripeEnvironment::LIVE ),
		);
	}

	/**
	 * Merge state into one environment profile.
	 *
	 * @param string               $environment Environment id.
	 * @param array<string, mixed> $patch       Profile patch.
	 * @return bool Whether the option write reported success.
	 */
	public function update_profile( string $environment, array $patch ): bool {
		$environment = StripeEnvironment::assert_valid( $environment );
		$merged      = self::merge_profile_state( $this->get_profile( $environment ), $patch );
		$stripe      = $this->get_stripe_subtree();
		$connections = isset( $stripe[ self::CONNECTIONS_KEY ] ) && is_array( $stripe[ self::CONNECTIONS_KEY ] )
			? $stripe[ self::CONNECTIONS_KEY ]
			: array();

		$connections[ $environment ] = $merged;

		return $this->commit_stripe_state( array( self::CONNECTIONS_KEY => $connections ) );
	}

	/**
	 * Credential values stored for one environment.
	 *
	 * @param string $environment Environment id.
	 * @return array{publishable_key:string,secret_key:string,webhook_secret:string}
	 */
	public function get_credentials( string $environment ): array {
		$keys   = StripeEnvironment::credential_option_keys( $environment );
		$stripe = $this->get_stripe_subtree();
		$values = array();

		foreach ( $keys as $field => $option_key ) {
			$value            = isset( $stripe[ $option_key ] ) ? $stripe[ $option_key ] : '';
			$values[ $field ] = is_string( $value ) ? trim( $value ) : '';
		}

		return $values;
	}

	/**
	 * Write credential values for one environment.
	 *
	 * Keys absent from `$values` are left untouched; an explicit empty string
	 * clears the stored value. Only the targeted environment's option keys are
	 * written.
	 *
	 * @param string                $environment Environment id.
	 * @param array<string, string> $values      Map of publishable_key|secret_key|webhook_secret.
	 * @return bool
	 */
	public function update_credentials( string $environment, array $values ): bool {
		$map   = StripeEnvironment::credential_option_keys( $environment );
		$patch = array();

		foreach ( $map as $field => $option_key ) {
			if ( ! array_key_exists( $field, $values ) ) {
				continue;
			}

			$patch[ $option_key ] = is_string( $values[ $field ] ) ? trim( $values[ $field ] ) : '';
		}

		if ( empty( $patch ) ) {
			return true;
		}

		return $this->commit_stripe_state( $patch );
	}

	/**
	 * Whether a secret key is stored for the environment.
	 *
	 * @param string $environment Environment id.
	 * @return bool
	 */
	public function has_secret_key( string $environment ): bool {
		$credentials = $this->get_credentials( $environment );

		return '' !== $credentials['secret_key'];
	}

	/**
	 * Whether the environment completed auto configuration.
	 *
	 * @param string $environment Environment id.
	 * @return bool
	 */
	public function is_configured( string $environment ): bool {
		return self::STATUS_CONFIGURED === $this->get_profile( $environment )['setup_status'];
	}

	/**
	 * Migration metadata stored beside the profiles.
	 *
	 * @return array<string, mixed>
	 */
	public function get_migration_state(): array {
		$stripe = $this->get_stripe_subtree();
		$state  = isset( $stripe[ self::MIGRATION_KEY ] ) ? $stripe[ self::MIGRATION_KEY ] : array();

		return is_array( $state ) ? $state : array();
	}

	/**
	 * Merge migration metadata.
	 *
	 * @param array<string, mixed> $patch Migration patch.
	 * @return bool
	 */
	public function update_migration_state( array $patch ): bool {
		$state = array_merge( $this->get_migration_state(), $patch );

		return $this->commit_stripe_state( array( self::MIGRATION_KEY => $state ) );
	}

	/**
	 * Record that the environment was checked.
	 *
	 * @param string $environment Environment id.
	 * @return bool
	 */
	public function mark_checked( string $environment ): bool {
		return $this->update_profile( $environment, array( 'last_checked_at' => time() ) );
	}

	/**
	 * Record a verified webhook delivery timestamp.
	 *
	 * @param string $environment Environment id.
	 * @param string $account_id  Account id learned from the event, if any.
	 * @return bool
	 */
	public function mark_webhook_verified( string $environment, string $account_id = '' ): bool {
		$patch = array( 'last_verified_webhook_at' => time() );

		if ( '' !== $account_id && '' === $this->get_profile( $environment )['stripe_account_id'] ) {
			$patch['stripe_account_id']   = $account_id;
			$patch['binding_fingerprint'] = $this->identity->fingerprint( $environment, $account_id );
		}

		return $this->update_profile( $environment, $patch );
	}

	/**
	 * Installation identity helper.
	 *
	 * @return InstallationIdentity
	 */
	public function identity(): InstallationIdentity {
		return $this->identity;
	}

	/**
	 * Display-safe snapshot of an environment profile.
	 *
	 * @param string $environment Environment id.
	 * @return array<string, mixed>
	 */
	public function get_masked_profile( string $environment ): array {
		$environment = StripeEnvironment::assert_valid( $environment );
		$profile     = $this->get_profile( $environment );
		$credentials = $this->get_credentials( $environment );
		$error_code  = StripeErrorCodes::sanitize( $profile['last_error_code'] );

		return array(
			'environment'              => $environment,
			'label'                    => StripeEnvironment::label( $environment ),
			'livemode'                 => (bool) $profile['livemode'],
			'setup_status'             => $profile['setup_status'],
			'configured'               => self::STATUS_CONFIGURED === $profile['setup_status'],
			'verified'                 => (int) $profile['last_verified_webhook_at'] > 0,
			'state'                    => self::display_state( $profile ),
			'state_label'              => self::display_state_label( self::display_state( $profile ) ),
			'state_tone'               => self::display_state_tone( self::display_state( $profile ) ),
			'recovery_pending'         => array() !== $profile['recovery'],
			'publishable_key_masked'   => StripeMasking::mask( $credentials['publishable_key'] ),
			'secret_key_masked'        => StripeMasking::mask( $credentials['secret_key'] ),
			'webhook_secret_masked'    => StripeMasking::mask_webhook_secret( $credentials['webhook_secret'] ),
			'has_publishable_key'      => '' !== $credentials['publishable_key'],
			'has_secret_key'           => '' !== $credentials['secret_key'],
			'has_webhook_secret'       => '' !== $credentials['webhook_secret'],
			'webhook_id'               => $profile['webhook_id'],
			'webhook_url'              => $profile['webhook_url'],
			'webhook_owner'            => $profile['webhook_owner'],
			'events'                   => $profile['events'],
			'event_count'              => count( $profile['events'] ),
			'stripe_account_id'        => $profile['stripe_account_id'],
			'api_version'              => $profile['api_version'],
			'last_checked_at'          => (int) $profile['last_checked_at'],
			'last_verified_webhook_at' => (int) $profile['last_verified_webhook_at'],
			'last_checked_display'     => self::format_timestamp( (int) $profile['last_checked_at'] ),
			'last_verified_display'    => self::format_timestamp( (int) $profile['last_verified_webhook_at'] ),
			'last_error_code'          => $error_code,
			'last_error_message'       => '' === $error_code ? '' : StripeErrorCodes::message( $error_code ),
			'previous_webhook_ids'     => $profile['previous_webhook_ids'],
		);
	}

	/**
	 * Coarse connection state used by the admin badge.
	 *
	 * @param array<string, mixed> $profile Profile state.
	 * @return string One of verified|configured|manual|needs_attention|not_configured.
	 */
	public static function display_state( array $profile ): string {
		// A pending reconcile or recorded error outranks any success state: showing
		// "Verified" while an operation is still unsettled would be misleading.
		$needs_attention = array() !== ( $profile['recovery'] ?? array() )
			|| '' !== StripeErrorCodes::sanitize( $profile['last_error_code'] ?? '' )
			|| in_array( (string) ( $profile['setup_status'] ?? '' ), array( self::STATUS_ERROR, self::STATUS_RECOVERY_PENDING ), true );

		if ( $needs_attention ) {
			return 'needs_attention';
		}

		$is_configured = self::STATUS_CONFIGURED === ( $profile['setup_status'] ?? '' ) && '' !== (string) ( $profile['webhook_id'] ?? '' );

		if ( $is_configured ) {
			return (int) ( $profile['last_verified_webhook_at'] ?? 0 ) > 0 ? 'verified' : 'configured';
		}

		if ( self::STATUS_LEGACY_MANUAL === ( $profile['setup_status'] ?? '' ) ) {
			return 'manual';
		}

		return 'not_configured';
	}

	/**
	 * Translated badge label for a connection state.
	 *
	 * @param string $state State from display_state().
	 * @return string
	 */
	public static function display_state_label( string $state ): string {
		switch ( $state ) {
			case 'verified':
				return __( 'Verified', 'giftflow' );
			case 'configured':
				return __( 'Configured', 'giftflow' );
			case 'manual':
				return __( 'Manual keys', 'giftflow' );
			case 'needs_attention':
				return __( 'Needs attention', 'giftflow' );
			default:
				return __( 'Not configured', 'giftflow' );
		}
	}

	/**
	 * Badge tone for a connection state.
	 *
	 * @param string $state State from display_state().
	 * @return string One of success|warning|danger|neutral.
	 */
	public static function display_state_tone( string $state ): string {
		switch ( $state ) {
			case 'verified':
			case 'configured':
				return 'success';
			case 'manual':
				return 'warning';
			case 'needs_attention':
				return 'danger';
			default:
				return 'neutral';
		}
	}

	/**
	 * Whether recurring Stripe donations are enabled in settings.
	 *
	 * @return bool
	 */
	public function is_recurring_enabled(): bool {
		$stripe = $this->get_stripe_subtree();
		$value  = $stripe['stripe_recurring_enabled'] ?? false;

		if ( is_string( $value ) ) {
			return in_array( strtolower( $value ), array( '1', 'true', 'yes', 'on' ), true );
		}

		return (bool) $value;
	}

	/**
	 * Whether the webhook receiver is enabled for the site.
	 *
	 * @return bool
	 */
	public function is_webhook_enabled(): bool {
		$stripe = $this->get_stripe_subtree();
		$value  = $stripe['stripe_webhook_enabled'] ?? true;

		if ( is_string( $value ) ) {
			return ! in_array( strtolower( $value ), array( '0', 'false', 'no', 'off', '' ), true );
		}

		return (bool) $value;
	}

	/**
	 * Format a Unix timestamp for display, server side.
	 *
	 * The REST payload carries this preformatted value so the panel can refresh
	 * "last checked"/"last valid webhook" without reformatting dates in JavaScript.
	 *
	 * @param int $timestamp Timestamp.
	 * @return string Empty string when nothing was recorded yet.
	 */
	private static function format_timestamp( int $timestamp ): string {
		if ( $timestamp <= 0 || ! function_exists( 'date_i18n' ) ) {
			return '';
		}

		return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp );
	}

	/**
	 * Acquire the environment lock.
	 *
	 * Uses an atomic option insert. An expired lock is abandoned so a crashed
	 * request cannot block configuration forever.
	 *
	 * @param string $environment Environment id.
	 * @return bool Whether the lock is now held.
	 */
	public function acquire_lock( string $environment ): bool {
		$environment = StripeEnvironment::assert_valid( $environment );
		$key         = self::LOCK_PREFIX . $environment;
		$now         = time();

		if ( add_option( $key, $now, '', false ) ) {
			return true;
		}

		$held = (int) get_option( $key, 0 );

		if ( $held > 0 && $held < $now - self::LOCK_TTL ) {
			delete_option( $key );

			return (bool) add_option( $key, $now, '', false );
		}

		return false;
	}

	/**
	 * Release the environment lock.
	 *
	 * @param string $environment Environment id.
	 * @return void
	 */
	public function release_lock( string $environment ): void {
		delete_option( self::LOCK_PREFIX . StripeEnvironment::assert_valid( $environment ) );
	}

	/**
	 * Atomically claim a webhook event id.
	 *
	 * @param string $environment Environment id.
	 * @param string $account_id  Account id the event belongs to (may be empty).
	 * @param string $event_id    Stripe event id.
	 * @return string One of `claimed`, `duplicate`, `error`.
	 */
	public function claim_webhook_event( string $environment, string $account_id, string $event_id ): string {
		$environment = StripeEnvironment::assert_valid( $environment );
		$event_id    = trim( $event_id );

		if ( '' === $event_id ) {
			return 'error';
		}

		$key     = self::claim_option_key( $environment, $account_id, $event_id );
		$expires = time() + self::CLAIM_TTL;

		if ( add_option( $key, $expires, '', false ) ) {
			$this->maybe_prune_claims();

			return 'claimed';
		}

		$existing = get_option( $key, null );

		if ( null === $existing ) {
			// Storage hiccup or a concurrent delete: retry once before failing closed.
			if ( add_option( $key, $expires, '', false ) ) {
				$this->maybe_prune_claims();

				return 'claimed';
			}

			$existing = get_option( $key, null );
		}

		if ( null === $existing ) {
			return 'error';
		}

		return 'duplicate';
	}

	/**
	 * Claim option name for an event.
	 *
	 * @param string $environment Environment id.
	 * @param string $account_id  Account id.
	 * @param string $event_id    Event id.
	 * @return string
	 */
	public static function claim_option_key( string $environment, string $account_id, string $event_id ): string {
		return self::CLAIM_PREFIX . md5(
			implode(
				'|',
				array(
					'v1',
					(string) get_current_blog_id(),
					$environment,
					$account_id,
					$event_id,
				)
			)
		);
	}

	/**
	 * Drop expired event claims at most once per interval.
	 *
	 * @return void
	 */
	private function maybe_prune_claims(): void {
		$last = (int) get_option( self::CLAIMS_PRUNED_OPTION, 0 );

		if ( $last > time() - self::CLAIM_PRUNE_INTERVAL ) {
			return;
		}

		update_option( self::CLAIMS_PRUNED_OPTION, time(), false );

		global $wpdb;

		if ( ! isset( $wpdb ) || ! is_object( $wpdb ) || ! isset( $wpdb->options ) ) {
			return;
		}

		$like = $wpdb->esc_like( self::CLAIM_PREFIX ) . '%';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s AND CAST(option_value AS UNSIGNED) < %d",
				$like,
				time()
			)
		);
	}

	/**
	 * Merge keys into the Stripe subtree and persist the payment option.
	 *
	 * Public so the migrator can commit profile state in a single write.
	 *
	 * @param array<string, mixed> $patch Stripe subtree patch.
	 * @return bool
	 */
	public function commit_stripe_state( array $patch ): bool {
		$options = $this->get_payment_options();
		$stripe  = $this->get_stripe_subtree();

		foreach ( $patch as $key => $value ) {
			$stripe[ (string) $key ] = $value;
		}

		$options[ StripeEnvironment::OPTION_ROOT ] = $stripe;

		return (bool) update_option( self::PAYMENT_OPTION, $options );
	}
}
