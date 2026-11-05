<?php
/**
 * Environment-scoped persistence for PayPal connection state.
 *
 * Every mutation in this class targets a single environment. The opposite
 * profile is read, never rewritten: existing credential option keys keep their
 * historical names so payment behaviour is unchanged, while new per-environment
 * connection state lives under `paypal_connections[sandbox|live]`.
 *
 * @package GiftFlow
 * @subpackage PayPal
 */

namespace GiftFlow\PayPal;

use GiftFlow\Core\InstallationIdentity;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reads and writes PayPal payment options with explicit environment scope.
 */
final class PayPalConnectionRepository {

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
	public const CONNECTIONS_KEY = 'paypal_connections';

	/**
	 * Subtree key holding migration metadata.
	 *
	 * @var string
	 */
	public const MIGRATION_KEY = 'paypal_connection_migration';

	/**
	 * Option name prefix for environment locks.
	 *
	 * @var string
	 */
	public const LOCK_PREFIX = 'giftflow_paypal_lock_';

	/**
	 * Option name prefix for webhook event claims.
	 *
	 * @var string
	 */
	public const CLAIM_PREFIX = 'giftflow_paypal_claim_';

	/**
	 * Option holding the last claim-prune timestamp.
	 *
	 * @var string
	 */
	public const CLAIMS_PRUNED_OPTION = 'giftflow_paypal_claims_pruned_at';

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
			'webhook_id'               => '',
			'webhook_url'              => '',
			'webhook_owner'            => self::OWNER_UNKNOWN,
			'events'                   => array(),
			'paypal_app_id'            => '',
			'credential_fingerprint'   => '',
			'binding_fingerprint'      => '',
			'setup_status'             => self::STATUS_NOT_CONFIGURED,
			'last_checked_at'          => 0,
			'last_verified_webhook_at' => 0,
			'last_error_code'          => '',
			'last_error_at'            => 0,
			'recovery'                 => array(),
			'previous_webhook_ids'     => array(),
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
		$merged['last_error_code'] = PayPalErrorCodes::sanitize( $merged['last_error_code'] );

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
	 * PayPal subtree of the payment options.
	 *
	 * @return array<string, mixed>
	 */
	public function get_paypal_subtree(): array {
		$options = $this->get_payment_options();
		$paypal  = isset( $options[ PayPalEnvironment::OPTION_ROOT ] ) ? $options[ PayPalEnvironment::OPTION_ROOT ] : array();

		return is_array( $paypal ) ? $paypal : array();
	}

	/**
	 * The active payment environment stored in options, or an empty string.
	 *
	 * The PayPal subtree is canonical; the former option-root `paypal_mode` is
	 * read only as a legacy fallback.
	 *
	 * @return string
	 */
	public function get_stored_active_environment(): string {
		$paypal = $this->get_paypal_subtree();

		if ( PayPalEnvironment::is_valid( $paypal[ PayPalEnvironment::MODE_OPTION ] ?? null ) ) {
			return (string) $paypal[ PayPalEnvironment::MODE_OPTION ];
		}

		$options = $this->get_payment_options();

		if ( PayPalEnvironment::is_valid( $options[ PayPalEnvironment::MODE_OPTION ] ?? null ) ) {
			return (string) $options[ PayPalEnvironment::MODE_OPTION ];
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

		return '' === $stored ? PayPalEnvironment::SANDBOX : $stored;
	}

	/**
	 * Persist the active payment environment.
	 *
	 * @param string $environment Environment id.
	 * @return bool
	 */
	public function set_active_environment( string $environment ): bool {
		$environment = PayPalEnvironment::assert_valid( $environment );

		return $this->commit_paypal_state( array( PayPalEnvironment::MODE_OPTION => $environment ) );
	}

	/**
	 * Connection state for one environment.
	 *
	 * @param string $environment Environment id.
	 * @return array<string, mixed>
	 */
	public function get_profile( string $environment ): array {
		$environment = PayPalEnvironment::assert_valid( $environment );
		$paypal      = $this->get_paypal_subtree();
		$connections = isset( $paypal[ self::CONNECTIONS_KEY ] ) ? $paypal[ self::CONNECTIONS_KEY ] : array();
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
			PayPalEnvironment::SANDBOX => $this->get_profile( PayPalEnvironment::SANDBOX ),
			PayPalEnvironment::LIVE    => $this->get_profile( PayPalEnvironment::LIVE ),
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
		$environment = PayPalEnvironment::assert_valid( $environment );
		$merged      = self::merge_profile_state( $this->get_profile( $environment ), $patch );
		$paypal      = $this->get_paypal_subtree();
		$connections = isset( $paypal[ self::CONNECTIONS_KEY ] ) && is_array( $paypal[ self::CONNECTIONS_KEY ] )
			? $paypal[ self::CONNECTIONS_KEY ]
			: array();

		$connections[ $environment ] = $merged;

		return $this->commit_paypal_state( array( self::CONNECTIONS_KEY => $connections ) );
	}

	/**
	 * Credential values stored for one environment.
	 *
	 * @param string $environment Environment id.
	 * @return array{client_id:string,client_secret:string}
	 */
	public function get_credentials( string $environment ): array {
		$keys   = PayPalEnvironment::credential_option_keys( $environment );
		$paypal = $this->get_paypal_subtree();
		$values = array();

		foreach ( $keys as $field => $option_key ) {
			$value            = isset( $paypal[ $option_key ] ) ? $paypal[ $option_key ] : '';
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
	 * @param array<string, string> $values      Map of client_id|client_secret.
	 * @return bool
	 */
	public function update_credentials( string $environment, array $values ): bool {
		$map   = PayPalEnvironment::credential_option_keys( $environment );
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

		return $this->commit_paypal_state( $patch );
	}

	/**
	 * Whether complete credentials are stored.
	 *
	 * @param string $environment Environment id.
	 * @return bool
	 */
	public function has_credentials( string $environment ): bool {
		$credentials = $this->get_credentials( $environment );
		return '' !== $credentials['client_id'] && '' !== $credentials['client_secret'];
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
		$paypal = $this->get_paypal_subtree();
		$state  = isset( $paypal[ self::MIGRATION_KEY ] ) ? $paypal[ self::MIGRATION_KEY ] : array();

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

		return $this->commit_paypal_state( array( self::MIGRATION_KEY => $state ) );
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
	 * @param string $environment Environment identifier.
	 * @return bool Whether the profile update succeeded.
	 */
	public function mark_webhook_verified( string $environment ): bool {
		$environment = PayPalEnvironment::assert_valid( $environment );
		$patch = array( 'last_verified_webhook_at' => time() );
		$profile = $this->get_profile( $environment );
		$credentials = $this->get_credentials( $environment );
		if ( '' === $profile['paypal_app_id'] && '' !== $credentials['client_id'] ) {
			$app_identity = PayPalEnvironment::app_identity( $environment, $credentials['client_id'] );
			$patch['paypal_app_id'] = $app_identity;
			$patch['credential_fingerprint'] = self::credential_fingerprint( $environment, $credentials['client_id'] );
			$patch['binding_fingerprint'] = $this->binding_fingerprint( $environment, $app_identity );
		}
		return $this->update_profile( $environment, $patch );
	}

	/**
	 * Record the authenticated PayPal app identity.
	 *
	 * @param string $environment Environment identifier.
	 * @param string $app_id      Authenticated PayPal app ID.
	 * @param string $client_id   PayPal client ID.
	 * @return bool Whether the profile update succeeded.
	 */
	public function record_app_identity( string $environment, string $app_id, string $client_id ): bool {
		$environment = PayPalEnvironment::assert_valid( $environment );
		$app_identity = PayPalEnvironment::app_identity( $environment, $client_id, $app_id );
		return $this->update_profile(
			$environment,
			array(
				'paypal_app_id' => $app_identity,
				'credential_fingerprint' => self::credential_fingerprint( $environment, $client_id ),
				'binding_fingerprint' => $this->binding_fingerprint( $environment, $app_identity ),
			)
		);
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
		$environment = PayPalEnvironment::assert_valid( $environment );
		$profile = $this->get_profile( $environment );
		$credentials = $this->get_credentials( $environment );
		$error_code = PayPalErrorCodes::sanitize( $profile['last_error_code'] );
		$state = self::display_state( $profile );
		return array(
			'environment' => $environment,
			'label' => PayPalEnvironment::label( $environment ),
			'setup_status' => $profile['setup_status'],
			'configured' => self::STATUS_CONFIGURED === $profile['setup_status'],
			'verified' => (int) $profile['last_verified_webhook_at'] > 0,
			'state' => $state,
			'state_label' => self::display_state_label( $state ),
			'state_tone' => self::display_state_tone( $state ),
			'recovery_pending' => array() !== $profile['recovery'],
			'client_id_masked' => PayPalMasking::mask( $credentials['client_id'] ),
			'client_secret_masked' => PayPalMasking::mask( $credentials['client_secret'] ),
			'has_client_id' => '' !== $credentials['client_id'],
			'has_client_secret' => '' !== $credentials['client_secret'],
			'webhook_id' => $profile['webhook_id'],
			'webhook_url' => $profile['webhook_url'],
			'webhook_owner' => $profile['webhook_owner'],
			'events' => $profile['events'],
			'event_count' => count( $profile['events'] ),
			'paypal_app_id' => $profile['paypal_app_id'],
			'app_identity_fallback' => PayPalEnvironment::is_fallback_app_identity( $profile['paypal_app_id'] ),
			'catches_attention' => 'needs_attention' === $state,
			'last_checked_at' => (int) $profile['last_checked_at'],
			'last_verified_webhook_at' => (int) $profile['last_verified_webhook_at'],
			'last_checked_display' => self::format_timestamp( (int) $profile['last_checked_at'] ),
			'last_verified_display' => self::format_timestamp( (int) $profile['last_verified_webhook_at'] ),
			'last_error_code' => $error_code,
			'last_error_message' => '' === $error_code ? '' : PayPalErrorCodes::message( $error_code ),
			'previous_webhook_ids' => $profile['previous_webhook_ids'],
		);
	}
	/**
	 * Derive a non-secret credential fingerprint.
	 *
	 * @param string $environment Environment identifier.
	 * @param string $client_id   PayPal client ID.
	 * @return string Truncated credential fingerprint.
	 */
	public static function credential_fingerprint( string $environment, string $client_id ): string {
		PayPalEnvironment::assert_valid( $environment );
		return substr( hash( 'sha256', 'paypal|' . $environment . '|' . trim( $client_id ) ), 0, 32 ); }
	/**
	 * Derive the installation-specific PayPal binding fingerprint.
	 *
	 * @param string $environment  Environment identifier.
	 * @param string $app_identity PayPal app identity.
	 * @return string Binding fingerprint.
	 */
	public function binding_fingerprint( string $environment, string $app_identity ): string {
		return $this->identity->fingerprint( PayPalEnvironment::assert_valid( $environment ), $app_identity, 'paypal' ); }
	/**
	 * Resolve the cache scope for an environment and account binding.
	 *
	 * @param string $environment Environment identifier.
	 * @return string Cache scope, or an empty string before binding.
	 */
	public function cache_scope( string $environment ): string {
		$environment = PayPalEnvironment::assert_valid( $environment );
		$profile = $this->get_profile( $environment );
		if ( '' === $profile['credential_fingerprint'] && '' === $profile['binding_fingerprint'] ) {
			return '';
		} return substr( hash( 'sha256', implode( '|', array( 'v1', $environment, $profile['credential_fingerprint'], $profile['binding_fingerprint'] ) ) ), 0, 16 ); }
	/**
	 * Build the environment-scoped product option key.
	 *
	 * @param string $environment Environment identifier.
	 * @return string Product option key.
	 */
	public function product_option_key( string $environment ): string {
		$environment = PayPalEnvironment::assert_valid( $environment );
		$scope = $this->cache_scope( $environment );
		return 'giftflow_paypal_product_' . $environment . ( '' === $scope ? '' : '_' . $scope ); }
	/**
	 * Build the environment-scoped billing plan option key.
	 *
	 * @param string $environment  Environment identifier.
	 * @param string $currency     Currency code.
	 * @param int    $amount_minor Amount in the currency's minor unit.
	 * @param string $interval     Billing interval unit.
	 * @param int    $count        Billing interval count.
	 * @return string Billing plan option key.
	 */
	public function plan_option_key( string $environment, string $currency, int $amount_minor, string $interval, int $count ): string {
		$environment = PayPalEnvironment::assert_valid( $environment );
		return 'giftflow_paypal_plan_' . $environment . '_' . $this->cache_scope( $environment ) . '_' . strtolower( $currency ) . '_' . $amount_minor . '_' . $interval . '_' . $count; }
	/**
	 * Get the legacy global product option key.
	 *
	 * @return string Legacy product option key.
	 */
	public function legacy_product_option(): string {
		return 'giftflow_paypal_product_id'; }
	/**
	 * Build a legacy global billing plan option key.
	 *
	 * @param string $currency     Currency code, unused by the legacy key format.
	 * @param int    $amount_minor Amount in the currency's minor unit.
	 * @param string $interval     Billing interval unit.
	 * @return string Legacy billing plan option key.
	 */
	public function legacy_plan_option( string $currency, int $amount_minor, string $interval ): string {
		unset( $currency );
		return 'giftflow_paypal_plan_' . $amount_minor . '_' . $interval; }
	/**
	 * Clear cached OAuth tokens for an environment.
	 *
	 * @param string $environment Environment identifier.
	 * @return void
	 */
	public function forget_token_cache( string $environment ): void {
		PayPalTokenCache::forget_environment( PayPalEnvironment::assert_valid( $environment ) ); }


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
			|| '' !== PayPalErrorCodes::sanitize( $profile['last_error_code'] ?? '' )
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
	 * Whether recurring PayPal donations are enabled in settings.
	 *
	 * @return bool
	 */
	public function is_recurring_enabled(): bool {
		$paypal = $this->get_paypal_subtree();
		$value  = $paypal['paypal_recurring_enabled'] ?? false;

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
		$paypal = $this->get_paypal_subtree();
		$value  = $paypal['paypal_webhook_enabled'] ?? true;

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
		$environment = PayPalEnvironment::assert_valid( $environment );
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
		delete_option( self::LOCK_PREFIX . PayPalEnvironment::assert_valid( $environment ) );
	}

	/**
	 * Atomically claim a PayPal webhook event ID.
	 *
	 * @param string $environment Environment identifier.
	 * @param string $event_id    PayPal webhook event ID.
	 * @return string Claim result: claimed, duplicate, or error.
	 */
	public function claim_webhook_event( string $environment, string $event_id ): string {
		$environment = PayPalEnvironment::assert_valid( $environment );
		$event_id = trim( $event_id );
		if ( '' === $event_id ) {
			return 'error';
		} $key = self::claim_option_key( $environment, $event_id );
		$expires = time() + self::CLAIM_TTL;
		if ( add_option( $key, $expires, '', false ) ) {
			$this->maybe_prune_claims();
			return 'claimed'; }
		$existing = get_option( $key, null );
		if ( null === $existing && add_option( $key, $expires, '', false ) ) {
			$this->maybe_prune_claims();
			return 'claimed'; }
		return null === get_option( $key, null ) ? 'error' : 'duplicate';
	}
	/**
	 * Build the option key for a webhook event claim.
	 *
	 * @param string $environment Environment identifier.
	 * @param string $event_id    PayPal webhook event ID.
	 * @return string Event claim option key.
	 */
	public static function claim_option_key( string $environment, string $event_id ): string {
		return self::CLAIM_PREFIX . md5( implode( '|', array( 'v1', (string) get_current_blog_id(), $environment, $event_id ) ) );
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
	 * Merge keys into the PayPal subtree and persist the payment option.
	 *
	 * Public so the migrator can commit profile state in a single write.
	 *
	 * @param array<string, mixed> $patch PayPal subtree patch.
	 * @return bool
	 */
	public function commit_paypal_state( array $patch ): bool {
		$options = $this->get_payment_options();
		$paypal  = $this->get_paypal_subtree();

		foreach ( $patch as $key => $value ) {
			$paypal[ (string) $key ] = $value;
		}

		$options[ PayPalEnvironment::OPTION_ROOT ] = $paypal;

		return (bool) update_option( self::PAYMENT_OPTION, $options );
	}
}
