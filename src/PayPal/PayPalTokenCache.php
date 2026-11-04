<?php
/**
 * Environment-scoped PayPal OAuth token cache.
 *
 * @package GiftFlow
 * @subpackage PayPal
 */

namespace GiftFlow\PayPal;

// phpcs:disable Generic.Commenting.DocComment.MissingShort,Squiz.Commenting.FunctionComment.MissingParamTag,Squiz.Commenting.FunctionComment.ParamCommentFullStop -- Existing concise API annotations accurately describe these cache operations.

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/** Caches access tokens without exposing their values. */
final class PayPalTokenCache {
	public const PREFIX = 'giftflow_paypal_token_';
	public const INDEX_OPTION = 'giftflow_paypal_token_index';
	/** @param string $environment Environment. @param string $client_id Client id. @return string */
	public static function key( string $environment, string $client_id ): string {
		return self::PREFIX . PayPalEnvironment::assert_valid( $environment ) . '_' . md5( trim( $client_id ) ); }
	/** @param string $environment Environment. @param string $client_id Client id. @return string */
	public static function get( string $environment, string $client_id ): string {
		$value = get_transient( self::key( $environment, $client_id ) );
		if ( ! is_array( $value ) || ! isset( $value['access_token'], $value['expires_at'] ) ) {
			return '';
		}
		if ( (int) $value['expires_at'] <= time() + 60 ) {
			return '';
		} return is_string( $value['access_token'] ) ? $value['access_token'] : '';
	}
	/** @param string $environment Environment. @param string $client_id Client id. @param string $token Token. @param int $expires_in Lifetime. @return void */
	public static function put( string $environment, string $client_id, string $token, int $expires_in ): void {
		$key = self::key( $environment, $client_id );
		$expires_at = time() + max( 0, $expires_in );
		$ttl = max( 60, $expires_in - 60 );
		set_transient(
			$key,
			array(
				'access_token' => $token,
				'expires_at' => $expires_at,
				'expires_in' => $expires_in,
			),
			$ttl
		);
		$index = get_option( self::INDEX_OPTION, array() );
		$index = is_array( $index ) ? $index : array();
		foreach ( $index as $indexed_key => $expiry ) {
			if ( (int) $expiry <= time() ) {
				unset( $index[ $indexed_key ] );
			}
		} $index[ $key ] = $expires_at;
		update_option( self::INDEX_OPTION, $index, false );
	}
	/** @param string $environment Environment. @param string $client_id Client id. @return void */
	public static function forget( string $environment, string $client_id ): void {
		$key = self::key( $environment, $client_id );
		delete_transient( $key );
		$index = get_option( self::INDEX_OPTION, array() );
		if ( is_array( $index ) && isset( $index[ $key ] ) ) {
			unset( $index[ $key ] );
			update_option( self::INDEX_OPTION, $index, false );} }
	/** @param string $environment Environment. @return void */
	public static function forget_environment( string $environment ): void {
		$prefix = self::PREFIX . PayPalEnvironment::assert_valid( $environment ) . '_';
		$index = get_option( self::INDEX_OPTION, array() );
		$index = is_array( $index ) ? $index : array();
		foreach ( array_keys( $index ) as $key ) {
			if ( 0 === strpos( (string) $key, $prefix ) ) {
				delete_transient( $key );
					unset( $index[ $key ] );
			}
		} update_option( self::INDEX_OPTION, $index, false );
	}
}
