<?php
/**
 * Exact money parsing for checkout extensions.
 *
 * The donation form historically converts the submitted amount with
 * `floatval()` before any payment code runs. Rewards (and any other checkout
 * extension) require exact integer minor units, so the original decimal lexeme
 * is preserved and parsed here instead.
 *
 * Only the currencies listed in {@see CheckoutMoney::SUPPORTED_CURRENCIES} are
 * accepted. They are all two-decimal ISO 4217 codes whose settlement rules do
 * not require provider-specific minor unit overrides. Unknown currencies are
 * rejected rather than silently assumed to have two decimals.
 *
 * @package GiftFlow
 * @subpackage Services
 */

namespace GiftFlow\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Integer minor-unit arithmetic for checkout extensions.
 */
final class CheckoutMoney {

	/**
	 * Maximum accepted integer part length (13 digits ≈ 10 trillion major units).
	 *
	 * @var int
	 */
	const MAX_INTEGER_DIGITS = 13;

	/**
	 * Currencies supported by checkout extensions in this release.
	 *
	 * All entries are two-decimal currencies. Codes that require provider
	 * specific precision handling (HUF, TWD and other special-rule codes) and
	 * zero/three-decimal currencies are intentionally excluded.
	 *
	 * @var string[]
	 */
	const SUPPORTED_CURRENCIES = array(
		'USD',
		'EUR',
		'GBP',
		'AUD',
		'CAD',
		'NZD',
		'SGD',
		'HKD',
		'CHF',
		'SEK',
		'NOK',
		'DKK',
		'PLN',
		'CZK',
		'ILS',
		'MXN',
		'BRL',
		'PHP',
		'THB',
		'MYR',
	);

	/**
	 * Normalize a currency code.
	 *
	 * @param string $currency Raw currency code.
	 * @return string Upper-case three letter code, or '' when not a string.
	 */
	public static function normalize_currency( $currency ) {
		if ( ! is_string( $currency ) && ! is_numeric( $currency ) ) {
			return '';
		}

		return strtoupper( trim( (string) $currency ) );
	}

	/**
	 * Whether a currency can be used by a checkout extension.
	 *
	 * @param string $currency Currency code.
	 * @return bool
	 */
	public static function is_supported_currency( $currency ) {
		$currency = self::normalize_currency( $currency );

		return in_array( $currency, self::SUPPORTED_CURRENCIES, true );
	}

	/**
	 * Parse a decimal amount into integer minor units.
	 *
	 * Rejects exponent notation, signs, group separators, internal whitespace,
	 * more than two fractional digits and integer overflow. Never rounds.
	 *
	 * @param string $decimal  Decimal amount as submitted, e.g. "60", "60.5", "60.50".
	 * @param string $currency Currency the amount is denominated in.
	 * @return int|\WP_Error Integer minor units on success.
	 */
	public static function parse( $decimal, $currency ) {
		$currency = self::normalize_currency( $currency );

		if ( ! self::is_supported_currency( $currency ) ) {
			return new \WP_Error(
				'giftflow_unsupported_currency',
				__( 'This currency is not supported for reward checkouts.', 'giftflow' )
			);
		}

		if ( is_int( $decimal ) ) {
			$decimal = (string) $decimal;
		}

		if ( ! is_string( $decimal ) ) {
			return new \WP_Error( 'giftflow_invalid_amount', __( 'Invalid donation amount.', 'giftflow' ) );
		}

		$decimal = trim( $decimal );

		if ( '' === $decimal ) {
			return new \WP_Error( 'giftflow_invalid_amount', __( 'Invalid donation amount.', 'giftflow' ) );
		}

		if ( ! preg_match( '/^([0-9]+)(?:\.([0-9]{1,2})?)?$/', $decimal, $matches ) ) {
			return new \WP_Error(
				'giftflow_invalid_amount',
				__( 'Donation amount must be a plain number with at most two decimal places.', 'giftflow' )
			);
		}

		$integer_part = ltrim( $matches[1], '0' );
		if ( '' === $integer_part ) {
			$integer_part = '0';
		}

		if ( self::MAX_INTEGER_DIGITS < strlen( $integer_part ) ) {
			return new \WP_Error( 'giftflow_invalid_amount', __( 'Donation amount is too large.', 'giftflow' ) );
		}

		$fraction_part = isset( $matches[2] ) ? str_pad( $matches[2], 2, '0' ) : '00';

		$minor = ( (int) $integer_part ) * 100 + (int) $fraction_part;

		// Defensive overflow guard: the recomputed digits must round-trip.
		if ( 0 > $minor || self::trim_leading_zeros( $integer_part . $fraction_part ) !== (string) $minor ) {
			return new \WP_Error( 'giftflow_invalid_amount', __( 'Donation amount is too large.', 'giftflow' ) );
		}

		return $minor;
	}

	/**
	 * Format integer minor units back into a plain decimal string.
	 *
	 * @param int $minor Integer minor units.
	 * @return string Decimal string such as "60.50".
	 */
	public static function format( $minor ) {
		$minor = (int) $minor;
		$sign  = $minor < 0 ? '-' : '';
		$minor = abs( $minor );

		$major      = (int) floor( $minor / 100 );
		$fraction   = $minor % 100;

		return $sign . $major . '.' . str_pad( (string) $fraction, 2, '0', STR_PAD_LEFT );
	}

	/**
	 * Compare two amounts expressed in minor units.
	 *
	 * @param int $left  Left amount.
	 * @param int $right Right amount.
	 * @return int -1, 0 or 1.
	 */
	public static function compare( $left, $right ) {
		$left  = (int) $left;
		$right = (int) $right;

		if ( $left === $right ) {
			return 0;
		}

		return $left < $right ? -1 : 1;
	}

	/**
	 * Remove leading zeros from a digit string, keeping one zero.
	 *
	 * @param string $digits Digits only.
	 * @return string
	 */
	private static function trim_leading_zeros( $digits ) {
		$trimmed = ltrim( (string) $digits, '0' );

		return '' === $trimmed ? '0' : $trimmed;
	}
}
