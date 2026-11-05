<?php
/**
 * Builds environment-bound Stripe clients.
 *
 * A client is always created for one explicit environment and one explicit
 * secret key. Prefix/profile mismatches are rejected before a client exists,
 * and the SDK's global static API key is never populated.
 *
 * @package GiftFlow
 * @subpackage Stripe
 */

namespace GiftFlow\Stripe;

use GiftFlow\Vendor\Stripe\Stripe;
use GiftFlow\Vendor\Stripe\StripeClient;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Factory for environment-scoped Stripe clients.
 */
final class StripeClientFactory {

	/**
	 * Create a Stripe client bound to an environment and secret key.
	 *
	 * @param string $environment Environment id.
	 * @param string $secret_key  Stripe secret key (sk_* or rk_*).
	 * @return StripeClient
	 * @throws StripeClientConfigurationException When the environment or key is unusable.
	 */
	public static function create( string $environment, string $secret_key ): StripeClient {
		$environment = StripeEnvironment::assert_valid( $environment );
		$secret_key  = trim( $secret_key );

		if ( '' === $secret_key ) {
			throw new StripeClientConfigurationException(
				esc_html__( 'No Stripe secret key is stored for this profile.', 'giftflow' )
			);
		}

		if ( StripeMasking::is_masked( $secret_key ) ) {
			throw new StripeClientConfigurationException(
				esc_html__( 'A masked value cannot be used as a Stripe secret key.', 'giftflow' )
			);
		}

		if ( ! StripeEnvironment::secret_key_matches( $environment, $secret_key ) ) {
			throw new StripeClientConfigurationException(
				sprintf(
					/* translators: 1: environment label, 2: comma separated accepted prefixes */
					esc_html__( 'The stored secret key does not belong to the %1$s profile. Accepted prefixes: %2$s.', 'giftflow' ),
					esc_html( StripeEnvironment::label( $environment ) ),
					esc_html( implode( ', ', StripeEnvironment::secret_key_prefixes( $environment ) ) )
				)
			);
		}

		return new StripeClient( $secret_key );
	}

	/**
	 * Version of the bundled Stripe SDK.
	 *
	 * @return string
	 */
	public static function sdk_version(): string {
		return Stripe::VERSION;
	}
}
