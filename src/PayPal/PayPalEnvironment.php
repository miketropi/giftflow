<?php
/**
 * Immutable PayPal environment context.
 *
 * @package GiftFlow
 * @subpackage PayPal
 */

namespace GiftFlow\PayPal;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** PayPal environment and credential contract. */
final class PayPalEnvironment {
	public const SANDBOX = 'sandbox';
	public const LIVE = 'live';
	public const OPTION_ROOT = 'paypal';
	public const MODE_OPTION = 'paypal_mode';

	/**
	 * List supported PayPal environments.
	 *
	 * @return string[] Environment identifiers.
	 */
	public static function all(): array {
		return array( self::SANDBOX, self::LIVE );
	}

	/**
	 * Determine whether a value is a supported environment.
	 *
	 * @param mixed $environment Value to validate.
	 * @return bool Whether the value is valid.
	 */
	public static function is_valid( $environment ): bool {
		return is_string( $environment ) && in_array( $environment, self::all(), true );
	}

	/**
	 * Validate and return an environment identifier.
	 *
	 * @param mixed  $environment Value to validate.
	 * @param string $context     Context named in the exception message.
	 * @return string Validated environment identifier.
	 * @throws \InvalidArgumentException When the environment is invalid.
	 */
	public static function assert_valid( $environment, string $context = 'environment' ): string {
		if ( ! self::is_valid( $environment ) ) {
			throw new \InvalidArgumentException(
				sprintf(
					/* translators: 1: context, 2: environments */
					esc_html__( 'Invalid %1$s. Expected one of: %2$s.', 'giftflow' ),
					esc_html( $context ),
					esc_html( implode( ', ', self::all() ) )
				)
			);
		}
		return (string) $environment;
	}

	/**
	 * Determine whether an environment is live.
	 *
	 * @param string $environment Environment identifier.
	 * @return bool Whether the environment is live.
	 */
	public static function is_live( string $environment ): bool {
		return self::LIVE === self::assert_valid( $environment ); }
	/**
	 * Get the PayPal API base URL.
	 *
	 * @param string $environment Environment identifier.
	 * @return string API base URL.
	 */
	public static function api_base( string $environment ): string {
		return self::is_live( $environment ) ? 'https://api.paypal.com' : 'https://api.sandbox.paypal.com'; }
	/**
	 * Get the PayPal JavaScript SDK base URL.
	 *
	 * @param string $environment Environment identifier.
	 * @return string SDK base URL.
	 */
	public static function sdk_base( string $environment ): string {
		return self::is_live( $environment ) ? 'https://www.paypal.com/sdk/js' : 'https://www.sandbox.paypal.com/sdk/js'; }
	/**
	 * Get the client ID option key.
	 *
	 * @param string $environment Environment identifier.
	 * @return string Client ID option key.
	 */
	public static function client_id_option( string $environment ): string {
		return 'paypal_' . self::assert_valid( $environment ) . '_client_id'; }
	/**
	 * Get the client secret option key.
	 *
	 * @param string $environment Environment identifier.
	 * @return string Client secret option key.
	 */
	public static function client_secret_option( string $environment ): string {
		return 'paypal_' . self::assert_valid( $environment ) . '_client_secret'; }
	/**
	 * Get credential option keys for an environment.
	 *
	 * @param string $environment Environment identifier.
	 * @return array<string,string> Credential field-to-option mapping.
	 */
	public static function credential_option_keys( string $environment ): array {
		return array(
			'client_id' => self::client_id_option( $environment ),
			'client_secret' => self::client_secret_option( $environment ),
		); }
	/**
	 * Get the display label for an environment.
	 *
	 * @param string $environment Environment identifier.
	 * @return string Translated environment label.
	 */
	public static function label( string $environment ): string {
		return self::is_live( $environment ) ? esc_html__( 'Live', 'giftflow' ) : esc_html__( 'Sandbox', 'giftflow' ); }
	/**
	 * Resolve an app ID or deterministic fallback identity.
	 *
	 * @param string $environment Environment identifier.
	 * @param string $client_id   PayPal client ID.
	 * @param string $app_id      Authenticated PayPal app ID, when available.
	 * @return string PayPal app identity.
	 */
	public static function app_identity( string $environment, string $client_id, string $app_id = '' ): string {
		self::assert_valid( $environment );
		$app_id = trim( $app_id );
		return '' !== $app_id ? $app_id : 'fp_' . substr( hash( 'sha256', 'paypal|' . $environment . '|' . trim( $client_id ) ), 0, 24 );
	}
	/**
	 * Determine whether an app identity is a local fallback.
	 *
	 * @param string $app_identity PayPal app identity.
	 * @return bool Whether the identity is a generated fallback.
	 */
	public static function is_fallback_app_identity( string $app_identity ): bool {
		return 0 === strpos( $app_identity, 'fp_' ); }
}
