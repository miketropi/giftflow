<?php
/**
 * Idempotent migration of legacy PayPal settings.
 *
 * @package GiftFlow
 * @subpackage PayPal
 */

namespace GiftFlow\PayPal;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates profile state from legacy PayPal settings.
 */
final class PayPalConnectionMigrator {
	/** Current migration version. @var int */
	public const VERSION = 1;

	/** Migrated status. @var string */
	public const STATUS_MIGRATED = 'migrated';

	/** No-op status. @var string */
	public const STATUS_NOOP = 'noop';

	/** Error status. @var string */
	public const STATUS_ERROR = 'error';

	/**
	 * Repository.
	 *
	 * @var PayPalConnectionRepository
	 */
	private PayPalConnectionRepository $repository;

	/**
	 * Constructor.
	 *
	 * @param PayPalConnectionRepository|null $repository Repository.
	 */
	public function __construct( ?PayPalConnectionRepository $repository = null ) {
		$this->repository = $repository ?? new PayPalConnectionRepository();
	}

	/**
	 * Whether migration is required.
	 *
	 * @return bool
	 */
	public function needs_migration(): bool {
		$options = get_option( PayPalConnectionRepository::PAYMENT_OPTION, null );
		if ( null === $options ) {
			return false;
		}
		if ( ! is_array( $options ) ) {
			return true;
		}
		$state = $this->repository->get_migration_state();
		return (int) ( $state['version'] ?? 0 ) < self::VERSION;
	}

	/**
	 * Run the migration.
	 *
	 * @return array{status:string,code:string,profiles:array}
	 */
	public function run(): array {
		$options = get_option( PayPalConnectionRepository::PAYMENT_OPTION, null );
		if ( null === $options ) {
			return $this->result( self::STATUS_NOOP );
		}
		if ( ! is_array( $options ) ) {
			return $this->error();
		}

		$paypal = $options[ PayPalEnvironment::OPTION_ROOT ] ?? array();
		if ( ! is_array( $paypal ) ) {
			return $this->error();
		}

		$state = $this->repository->get_migration_state();
		if ( (int) ( $state['version'] ?? 0 ) >= self::VERSION && $this->has_complete_profiles( $paypal ) ) {
			return $this->result( self::STATUS_NOOP );
		}

		$connections = isset( $paypal[ PayPalConnectionRepository::CONNECTIONS_KEY ] ) && is_array( $paypal[ PayPalConnectionRepository::CONNECTIONS_KEY ] )
			? $paypal[ PayPalConnectionRepository::CONNECTIONS_KEY ]
			: array();
		$mode        = $paypal[ PayPalEnvironment::MODE_OPTION ] ?? ( $options[ PayPalEnvironment::MODE_OPTION ] ?? '' );
		$active      = PayPalEnvironment::is_valid( $mode ) ? $mode : PayPalEnvironment::SANDBOX;
		$webhook_id  = isset( $paypal['paypal_webhook_id'] ) && is_string( $paypal['paypal_webhook_id'] )
			? trim( $paypal['paypal_webhook_id'] )
			: '';

		foreach ( PayPalEnvironment::all() as $environment ) {
			$existing                    = isset( $connections[ $environment ] ) && is_array( $connections[ $environment ] ) ? $connections[ $environment ] : array();
			$connections[ $environment ] = $this->build_profile( $environment, $existing, $paypal, $active, $webhook_id );
		}

		$patch = array(
			PayPalConnectionRepository::CONNECTIONS_KEY => $connections,
			PayPalConnectionRepository::MIGRATION_KEY   => array(
				'version'     => self::VERSION,
				'migrated_at' => time(),
				'error'       => '',
			),
		);
		if ( ! PayPalEnvironment::is_valid( $paypal[ PayPalEnvironment::MODE_OPTION ] ?? null ) ) {
			$patch[ PayPalEnvironment::MODE_OPTION ] = $active;
		}

		if ( ! $this->repository->commit_paypal_state( $patch ) ) {
			return $this->error();
		}

		return array(
			'status'   => self::STATUS_MIGRATED,
			'code'     => '',
			'profiles' => $this->repository->get_profiles(),
		);
	}

	/**
	 * Build an environment profile.
	 *
	 * @param string $environment Environment.
	 * @param array  $existing    Existing profile.
	 * @param array  $paypal      PayPal subtree.
	 * @param string $active      Active environment.
	 * @param string $webhook_id  Legacy webhook id.
	 * @return array
	 */
	private function build_profile( string $environment, array $existing, array $paypal, string $active, string $webhook_id ): array {
		$profile = empty( $existing )
			? PayPalConnectionRepository::default_profile()
			: PayPalConnectionRepository::merge_profile_state( $existing, array() );
		$keys    = PayPalEnvironment::credential_option_keys( $environment );
		$id      = isset( $paypal[ $keys['client_id'] ] ) && is_string( $paypal[ $keys['client_id'] ] )
			? trim( $paypal[ $keys['client_id'] ] )
			: '';
		$secret  = isset( $paypal[ $keys['client_secret'] ] ) && is_string( $paypal[ $keys['client_secret'] ] )
			? trim( $paypal[ $keys['client_secret'] ] )
			: '';

		if ( empty( $existing ) ) {
			$profile['setup_status'] = '' !== $id && '' !== $secret ? PayPalConnectionRepository::STATUS_LEGACY_MANUAL : PayPalConnectionRepository::STATUS_NOT_CONFIGURED;
			if ( $environment === $active && '' !== $webhook_id ) {
				$profile['webhook_id']    = $webhook_id;
				$profile['webhook_owner'] = PayPalConnectionRepository::OWNER_MANUAL;
			}
		}
		return $profile;
	}

	/**
	 * Whether both profiles exist.
	 *
	 * @param array $paypal PayPal subtree.
	 * @return bool
	 */
	private function has_complete_profiles( array $paypal ): bool {
		$connections = $paypal[ PayPalConnectionRepository::CONNECTIONS_KEY ] ?? null;
		if ( ! is_array( $connections ) ) {
			return false;
		}
		foreach ( PayPalEnvironment::all() as $environment ) {
			if ( ! isset( $connections[ $environment ] ) || ! is_array( $connections[ $environment ] ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Build a result.
	 *
	 * @param string $status Status.
	 * @return array
	 */
	private function result( string $status ): array {
		return array(
			'status'   => $status,
			'code'     => '',
			'profiles' => $this->repository->get_profiles(),
		);
	}

	/**
	 * Build an error result.
	 *
	 * @return array
	 */
	private function error(): array {
		return array(
			'status'   => self::STATUS_ERROR,
			'code'     => PayPalErrorCodes::MIGRATION_ERROR,
			'profiles' => array(),
		);
	}
}
