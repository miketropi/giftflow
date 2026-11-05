<?php
/**
 * Masking helpers for PayPal credentials.
 *
 * @package GiftFlow
 * @subpackage PayPal
 */

namespace GiftFlow\PayPal;

// phpcs:disable Generic.Commenting.DocComment.MissingShort,Squiz.Commenting.FunctionComment.MissingParamTag,Squiz.Commenting.FunctionComment.ParamCommentFullStop -- Existing concise API annotations accurately describe these masking helpers.

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Produces non-reversible display strings. */
final class PayPalMasking {
	/** @param mixed $value Raw value. @return string */
	public static function mask( $value ): string {
		$value = is_string( $value ) ? trim( $value ) : '';
		if ( '' === $value ) {
			return ''; }
		$length = strlen( $value );
		if ( $length <= 8 ) {
			return str_repeat( '*', $length ); }
		$prefix_length = 8;
		$suffix_length = 4;
		if ( $length <= $prefix_length + $suffix_length ) {
			$prefix_length = 4;
			$suffix_length = 2; }
		return substr( $value, 0, $prefix_length ) . '…' . substr( $value, -$suffix_length );
	}
	/** @param string $value Candidate value. @return bool */
	public static function is_masked( string $value ): bool {
		return false !== strpos( $value, '…' ); }
	/** @param mixed $value Raw value. @param string $prefix Prefix. @return string */
	public static function redact( $value, string $prefix = '' ): string {
		$value = is_string( $value ) ? $value : '';
		if ( '' === $value ) {
			return ''; }
		$value = preg_replace( '/\s+/', ' ', $value );
		$value = is_string( $value ) ? $value : '';
		if ( '' === $prefix ) {
			return self::mask( $value ); }
		$position = strpos( $value, $prefix );
		if ( false === $position ) {
			return self::mask( $value ); }
		return substr( $value, 0, $position + strlen( $prefix ) ) . '…[' . ( strlen( $value ) - $position - strlen( $prefix ) ) . ' chars]';
	}
}
