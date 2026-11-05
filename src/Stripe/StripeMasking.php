<?php
/**
 * Masking helpers for Stripe identifiers and secrets.
 *
 * Every value that leaves the server (REST responses, admin markup, logs) MUST
 * pass through this class. Raw credentials never leave the option store.
 *
 * @package GiftFlow
 * @subpackage Stripe
 */

namespace GiftFlow\Stripe;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Produces non-reversible display strings for credentials.
 */
final class StripeMasking {

	/**
	 * Mask a credential, keeping its environment prefix and last four characters.
	 *
	 * @param mixed $value Raw credential value.
	 * @return string Masked value, or an empty string when nothing is stored.
	 */
	public static function mask( $value ): string {
		$value = is_string( $value ) ? trim( $value ) : '';

		if ( '' === $value ) {
			return '';
		}

		$length = strlen( $value );

		if ( $length <= 8 ) {
			return str_repeat( '*', $length );
		}

		$prefix_length = 8;
		$suffix_length = 4;

		if ( $length <= $prefix_length + $suffix_length ) {
			$prefix_length = 4;
			$suffix_length = 2;
		}

		return substr( $value, 0, $prefix_length ) . '…' . substr( $value, -$suffix_length );
	}

	/**
	 * Mask a signing secret with its canonical prefix.
	 *
	 * @param mixed $value Raw webhook signing secret.
	 * @return string Masked value, or an empty string when nothing is stored.
	 */
	public static function mask_webhook_secret( $value ): string {
		$value = is_string( $value ) ? trim( $value ) : '';

		if ( '' === $value ) {
			return '';
		}

		return 'whsec_…' . substr( $value, -4 );
	}

	/**
	 * Whether a value is already masked, so it must never be re-saved as a credential.
	 *
	 * @param string $value Candidate value.
	 * @return bool
	 */
	public static function is_masked( string $value ): bool {
		return false !== strpos( $value, '…' );
	}

	/**
	 * Reduce text to a redacted, single-line string safe for logs and messages.
	 *
	 * @param mixed  $value  Raw value.
	 * @param string $prefix Mask prefix to search for.
	 * @return string
	 */
	public static function redact( $value, string $prefix = '' ): string {
		$value = is_string( $value ) ? $value : '';

		if ( '' === $value ) {
			return '';
		}

		$value = preg_replace( '/\s+/', ' ', $value );
		$value = is_string( $value ) ? $value : '';

		if ( '' === $prefix ) {
			return self::mask( $value );
		}

		$position = strpos( $value, $prefix );

		if ( false === $position ) {
			return self::mask( $value );
		}

		return substr( $value, 0, $position + strlen( $prefix ) ) . '…[' . ( strlen( $value ) - $position - strlen( $prefix ) ) . ' chars]';
	}
}
