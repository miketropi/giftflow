<?php
/**
 * Immutable Stripe environment context.
 *
 * The plugin has exactly two Stripe environments: `sandbox` (test mode) and
 * `live` (production mode). Nothing in this class falls back to a default when
 * handed invalid input; callers must handle the invalid case explicitly.
 *
 * @package GiftFlow
 * @subpackage Stripe
 */

namespace GiftFlow\Stripe;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Value object describing a Stripe environment and its credential contract.
 */
final class StripeEnvironment {

	/**
	 * Sandbox (test mode) environment id.
	 *
	 * @var string
	 */
	public const SANDBOX = 'sandbox';

	/**
	 * Live (production mode) environment id.
	 *
	 * @var string
	 */
	public const LIVE = 'live';

	/**
	 * Option root that stores every Stripe setting.
	 *
	 * @var string
	 */
	public const OPTION_ROOT = 'stripe';

	/**
	 * Option key that stores the active payment environment.
	 *
	 * @var string
	 */
	public const MODE_OPTION = 'stripe_mode';

	/**
	 * Both supported environments, sandbox first.
	 *
	 * @return string[]
	 */
	public static function all(): array {
		return array( self::SANDBOX, self::LIVE );
	}

	/**
	 * Whether a raw value is one of the supported environments.
	 *
	 * @param mixed $environment Candidate environment.
	 * @return bool
	 */
	public static function is_valid( $environment ): bool {
		return is_string( $environment ) && in_array( $environment, self::all(), true );
	}

	/**
	 * Validate a raw environment, rejecting anything unsupported.
	 *
	 * @param mixed  $environment Candidate environment.
	 * @param string $context     Context used in the exception message.
	 * @return string The validated environment.
	 * @throws \InvalidArgumentException When the environment is unsupported.
	 */
	public static function assert_valid( $environment, string $context = 'environment' ): string {
		if ( ! self::is_valid( $environment ) ) {
			throw new \InvalidArgumentException(
				sprintf(
					/* translators: 1: context label, 2: comma separated environment list */
					esc_html__( 'Invalid %1$s. Expected one of: %2$s.', 'giftflow' ),
					esc_html( $context ),
					esc_html( implode( ', ', self::all() ) )
				)
			);
		}

		return (string) $environment;
	}

	/**
	 * Whether the environment is live.
	 *
	 * @param string $environment Environment id.
	 * @return bool
	 */
	public static function is_live( string $environment ): bool {
		return self::LIVE === self::assert_valid( $environment );
	}

	/**
	 * Expected `livemode` flag for webhook events in an environment.
	 *
	 * @param string $environment Environment id.
	 * @return bool
	 */
	public static function livemode( string $environment ): bool {
		return self::is_live( $environment );
	}

	/**
	 * Map a Stripe `livemode` flag to the matching environment.
	 *
	 * @param bool $livemode Event livemode flag.
	 * @return string
	 */
	public static function from_livemode( bool $livemode ): string {
		return $livemode ? self::LIVE : self::SANDBOX;
	}

	/**
	 * Option key holding an environment's secret key.
	 *
	 * @param string $environment Environment id.
	 * @return string
	 */
	public static function secret_key_option( string $environment ): string {
		return 'stripe_' . self::assert_valid( $environment ) . '_secret_key';
	}

	/**
	 * Option key holding an environment's publishable key.
	 *
	 * @param string $environment Environment id.
	 * @return string
	 */
	public static function publishable_key_option( string $environment ): string {
		return 'stripe_' . self::assert_valid( $environment ) . '_publishable_key';
	}

	/**
	 * Option key holding an environment's webhook signing secret.
	 *
	 * @param string $environment Environment id.
	 * @return string
	 */
	public static function webhook_secret_option( string $environment ): string {
		return 'stripe_' . self::assert_valid( $environment ) . '_webhook_secret';
	}

	/**
	 * Every credential option key owned by an environment.
	 *
	 * @param string $environment Environment id.
	 * @return array{publishable_key:string,secret_key:string,webhook_secret:string}
	 */
	public static function credential_option_keys( string $environment ): array {
		return array(
			'publishable_key' => self::publishable_key_option( $environment ),
			'secret_key'      => self::secret_key_option( $environment ),
			'webhook_secret'  => self::webhook_secret_option( $environment ),
		);
	}

	/**
	 * Accepted secret key prefixes for an environment.
	 *
	 * @param string $environment Environment id.
	 * @return string[]
	 */
	public static function secret_key_prefixes( string $environment ): array {
		return self::is_live( $environment )
			? array( 'sk_live_', 'rk_live_' )
			: array( 'sk_test_', 'rk_test_' );
	}

	/**
	 * Accepted publishable key prefixes for an environment.
	 *
	 * @param string $environment Environment id.
	 * @return string[]
	 */
	public static function publishable_key_prefixes( string $environment ): array {
		return self::is_live( $environment )
			? array( 'pk_live_' )
			: array( 'pk_test_' );
	}

	/**
	 * Whether a secret key prefix belongs to the environment.
	 *
	 * @param string $environment Environment id.
	 * @param string $key         Candidate secret key.
	 * @return bool
	 */
	public static function secret_key_matches( string $environment, string $key ): bool {
		return self::key_matches_any_prefix( $key, self::secret_key_prefixes( $environment ) );
	}

	/**
	 * Whether a publishable key prefix belongs to the environment.
	 *
	 * @param string $environment Environment id.
	 * @param string $key         Candidate publishable key.
	 * @return bool
	 */
	public static function publishable_key_matches( string $environment, string $key ): bool {
		return self::key_matches_any_prefix( $key, self::publishable_key_prefixes( $environment ) );
	}

	/**
	 * Whether a key uses one of the given prefixes.
	 *
	 * @param string   $key      Candidate key.
	 * @param string[] $prefixes Allowed prefixes.
	 * @return bool
	 */
	private static function key_matches_any_prefix( string $key, array $prefixes ): bool {
		foreach ( $prefixes as $prefix ) {
			if ( 0 === strpos( $key, $prefix ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Human readable environment label.
	 *
	 * @param string $environment Environment id.
	 * @return string
	 */
	public static function label( string $environment ): string {
		return self::is_live( $environment )
			? esc_html__( 'Live', 'giftflow' )
			: esc_html__( 'Sandbox', 'giftflow' );
	}
}
