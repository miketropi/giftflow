<?php
/**
 * Idempotent, environment-scoped migration of legacy Stripe settings.
 *
 * The migration creates per-environment profile state from the existing manual
 * keys without provisioning anything or writing remote data. It never clears an
 * existing profile and it leaves malformed options untouched.
 *
 * @package GiftFlow
 * @subpackage Stripe
 */

namespace GiftFlow\Stripe;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates profile state from pre-1.2 manual Stripe settings.
 */
final class StripeConnectionMigrator {

	/**
	 * Current migration version.
	 *
	 * @var int
	 */
	public const VERSION = 1;

	/**
	 * Migration status: state was created.
	 *
	 * @var string
	 */
	public const STATUS_MIGRATED = 'migrated';

	/**
	 * Migration status: nothing to do.
	 *
	 * @var string
	 */
	public const STATUS_NOOP = 'noop';

	/**
	 * Migration status: stored options are unusable and were left untouched.
	 *
	 * @var string
	 */
	public const STATUS_ERROR = 'error';

	/**
	 * Repository.
	 *
	 * @var StripeConnectionRepository
	 */
	private StripeConnectionRepository $repository;

	/**
	 * Constructor.
	 *
	 * @param StripeConnectionRepository|null $repository Optional repository.
	 */
	public function __construct( ?StripeConnectionRepository $repository = null ) {
		$this->repository = $repository ?? new StripeConnectionRepository();
	}

	/**
	 * Whether the migration still has work to do.
	 *
	 * @return bool
	 */
	public function needs_migration(): bool {
		$options = get_option( StripeConnectionRepository::PAYMENT_OPTION, null );

		if ( null === $options ) {
			return false;
		}

		if ( ! is_array( $options ) ) {
			return true;
		}

		$state = $this->repository->get_migration_state();

		if ( (int) ( $state['version'] ?? 0 ) >= self::VERSION ) {
			return false;
		}

		return true;
	}

	/**
	 * Run the migration.
	 *
	 * @return array{status:string,code:string,profiles:array<string,array<string,mixed>>}
	 */
	public function run(): array {
		$options = get_option( StripeConnectionRepository::PAYMENT_OPTION, null );

		if ( null === $options ) {
			return $this->result( self::STATUS_NOOP );
		}

		if ( ! is_array( $options ) ) {
			return $this->error();
		}

		$stripe = $options[ StripeEnvironment::OPTION_ROOT ] ?? array();

		if ( ! is_array( $stripe ) ) {
			return $this->error();
		}

		$state = $this->repository->get_migration_state();

		if ( (int) ( $state['version'] ?? 0 ) >= self::VERSION && $this->has_complete_profiles( $stripe ) ) {
			return $this->result( self::STATUS_NOOP );
		}

		$connections = isset( $stripe[ StripeConnectionRepository::CONNECTIONS_KEY ] ) && is_array( $stripe[ StripeConnectionRepository::CONNECTIONS_KEY ] )
			? $stripe[ StripeConnectionRepository::CONNECTIONS_KEY ]
			: array();

		$patch = array();

		foreach ( StripeEnvironment::all() as $environment ) {
			$existing = isset( $connections[ $environment ] ) && is_array( $connections[ $environment ] )
				? $connections[ $environment ]
				: array();

			$connections[ $environment ] = $this->build_profile( $environment, $existing, $stripe );
		}

		$patch[ StripeConnectionRepository::CONNECTIONS_KEY ] = $connections;

		if ( ! StripeEnvironment::is_valid( $stripe[ StripeEnvironment::MODE_OPTION ] ?? null ) ) {
			$legacy_mode = $options[ StripeEnvironment::MODE_OPTION ] ?? '';

			$patch[ StripeEnvironment::MODE_OPTION ] = StripeEnvironment::is_valid( $legacy_mode )
				? $legacy_mode
				: StripeEnvironment::SANDBOX;
		}

		$patch[ StripeConnectionRepository::MIGRATION_KEY ] = array(
			'version'     => self::VERSION,
			'migrated_at' => time(),
			'error'       => '',
		);

		if ( ! $this->repository->commit_stripe_state( $patch ) ) {
			return $this->error();
		}

		return array(
			'status'   => self::STATUS_MIGRATED,
			'code'     => '',
			'profiles' => array(
				StripeEnvironment::SANDBOX => $this->repository->get_profile( StripeEnvironment::SANDBOX ),
				StripeEnvironment::LIVE    => $this->repository->get_profile( StripeEnvironment::LIVE ),
			),
		);
	}

	/**
	 * Build one profile state from legacy settings.
	 *
	 * @param string               $environment Environment id.
	 * @param array<string, mixed> $existing    Existing profile state, preserved when present.
	 * @param array<string, mixed> $stripe      Stripe subtree.
	 * @return array<string, mixed>
	 */
	private function build_profile( string $environment, array $existing, array $stripe ): array {
		$credentials = array(
			'publishable_key' => isset( $stripe[ StripeEnvironment::publishable_key_option( $environment ) ] ) && is_string( $stripe[ StripeEnvironment::publishable_key_option( $environment ) ] )
				? trim( $stripe[ StripeEnvironment::publishable_key_option( $environment ) ] )
				: '',
			'secret_key'      => isset( $stripe[ StripeEnvironment::secret_key_option( $environment ) ] ) && is_string( $stripe[ StripeEnvironment::secret_key_option( $environment ) ] )
				? trim( $stripe[ StripeEnvironment::secret_key_option( $environment ) ] )
				: '',
			'webhook_secret'  => isset( $stripe[ StripeEnvironment::webhook_secret_option( $environment ) ] ) && is_string( $stripe[ StripeEnvironment::webhook_secret_option( $environment ) ] )
				? trim( $stripe[ StripeEnvironment::webhook_secret_option( $environment ) ] )
				: '',
		);

		$defaults = StripeConnectionRepository::default_profile();

		if ( ! empty( $existing ) ) {
			return StripeConnectionRepository::merge_profile_state( $existing, array() );
		}

		$has_secret      = '' !== $credentials['secret_key'];
		$has_publishable = '' !== $credentials['publishable_key'];

		$defaults['livemode']     = StripeEnvironment::livemode( $environment );
		$defaults['setup_status'] = ( $has_secret && $has_publishable )
			? StripeConnectionRepository::STATUS_LEGACY_MANUAL
			: StripeConnectionRepository::STATUS_NOT_CONFIGURED;

		if ( $has_secret && '' !== $credentials['webhook_secret'] ) {
			$defaults['webhook_owner'] = StripeConnectionRepository::OWNER_MANUAL;
		}

		return $defaults;
	}

	/**
	 * Whether both environments already have profile state.
	 *
	 * @param array<string, mixed> $stripe Stripe subtree.
	 * @return bool
	 */
	private function has_complete_profiles( array $stripe ): bool {
		$connections = $stripe[ StripeConnectionRepository::CONNECTIONS_KEY ] ?? null;

		if ( ! is_array( $connections ) ) {
			return false;
		}

		foreach ( StripeEnvironment::all() as $environment ) {
			if ( ! isset( $connections[ $environment ] ) || ! is_array( $connections[ $environment ] ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Build a result payload.
	 *
	 * @param string $status Result status.
	 * @return array{status:string,code:string,profiles:array<string,array<string,mixed>>}
	 */
	private function result( string $status ): array {
		return array(
			'status'   => $status,
			'code'     => '',
			'profiles' => $this->repository->get_profiles(),
		);
	}

	/**
	 * Build a sanitized error result without writing anything.
	 *
	 * @return array{status:string,code:string,profiles:array<string,array<string,mixed>>}
	 */
	private function error(): array {
		return array(
			'status'   => self::STATUS_ERROR,
			'code'     => StripeErrorCodes::MIGRATION_ERROR,
			'profiles' => array(),
		);
	}
}
