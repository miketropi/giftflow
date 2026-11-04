<?php
/**
 * Stable PayPal connection error codes.
 *
 * @package GiftFlow
 * @subpackage PayPal
 */

namespace GiftFlow\PayPal;

// phpcs:disable Generic.Commenting.DocComment.MissingShort,Squiz.Commenting.FunctionComment.MissingParamTag,Squiz.Commenting.FunctionComment.ParamCommentFullStop -- Existing concise API annotations accurately describe these small value helpers.

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/** Error code registry. */
final class PayPalErrorCodes {
	public const MISSING_CREDENTIALS = 'MISSING_CREDENTIALS';
	public const INVALID_CLIENT_ID = 'INVALID_CLIENT_ID';
	public const INVALID_CLIENT_SECRET = 'INVALID_CLIENT_SECRET';
	public const INVALID_CREDENTIAL_VALUE = 'INVALID_CREDENTIAL_VALUE';
	public const ENVIRONMENT_MISMATCH = 'ENVIRONMENT_MISMATCH';
	public const AUTHENTICATION_FAILED = 'AUTHENTICATION_FAILED';
	public const INSUFFICIENT_PERMISSIONS = 'INSUFFICIENT_PERMISSIONS';
	public const APP_IDENTITY_UNCONFIRMED = 'APP_IDENTITY_UNCONFIRMED';
	public const ACCOUNT_CHANGED = 'ACCOUNT_CHANGED';
	public const REBIND_REQUIRED = 'REBIND_REQUIRED';
	public const REBIND_PLAN_STALE = 'REBIND_PLAN_STALE';
	public const ENDPOINT_OWNERSHIP_UNKNOWN = 'ENDPOINT_OWNERSHIP_UNKNOWN';
	public const ENDPOINT_NOT_OWNED = 'ENDPOINT_NOT_OWNED';
	public const INSTALLATION_CLONE_DETECTED = 'INSTALLATION_CLONE_DETECTED';
	public const NON_PUBLIC_URL = 'NON_PUBLIC_URL';
	public const ENDPOINT_MISSING = 'ENDPOINT_MISSING';
	public const ENDPOINT_DISABLED = 'ENDPOINT_DISABLED';
	public const URL_DRIFT = 'URL_DRIFT';
	public const EVENT_DRIFT = 'EVENT_DRIFT';
	public const WEBHOOK_ID_UNKNOWN = 'WEBHOOK_ID_UNKNOWN';
	public const AMBIGUOUS_CREATE = 'AMBIGUOUS_CREATE';
	public const LOCKED = 'LOCKED';
	public const TEMPORARY_PAYPAL_ERROR = 'TEMPORARY_PAYPAL_ERROR';
	public const PERSISTENCE_FAILED = 'PERSISTENCE_FAILED';
	public const NOT_CONFIGURED = 'NOT_CONFIGURED';
	public const MIGRATION_ERROR = 'MIGRATION_ERROR';
	public const LEGACY_BINDING_UNKNOWN = 'LEGACY_BINDING_UNKNOWN';
	public const INVALID_DIFF = 'INVALID_DIFF';
	public const WEBHOOK_ID_MISSING = 'WEBHOOK_ID_MISSING';
	public const WEBHOOK_HEADERS_MISSING = 'WEBHOOK_HEADERS_MISSING';
	public const WEBHOOK_VERIFICATION_INVALID = 'WEBHOOK_VERIFICATION_INVALID';
	public const WEBHOOK_VERIFICATION_AMBIGUOUS = 'WEBHOOK_VERIFICATION_AMBIGUOUS';
	public const WEBHOOK_BINDING_MISMATCH = 'WEBHOOK_BINDING_MISMATCH';
	public const WEBHOOK_CLAIM_STORAGE_FAILED = 'WEBHOOK_CLAIM_STORAGE_FAILED';
	public const UNEXPECTED_FIELD = 'UNEXPECTED_FIELD';
	/** @param mixed $code Code. @return string */
	public static function sanitize( $code ): string {
		return is_string( $code ) && in_array( $code, self::all(), true ) ? $code : ''; }
	/** @return string[] */
	public static function all(): array {
		return array(
			self::MISSING_CREDENTIALS,
			self::INVALID_CLIENT_ID,
			self::INVALID_CLIENT_SECRET,
			self::INVALID_CREDENTIAL_VALUE,
			self::ENVIRONMENT_MISMATCH,
			self::AUTHENTICATION_FAILED,
			self::INSUFFICIENT_PERMISSIONS,
			self::APP_IDENTITY_UNCONFIRMED,
			self::ACCOUNT_CHANGED,
			self::REBIND_REQUIRED,
			self::REBIND_PLAN_STALE,
			self::ENDPOINT_OWNERSHIP_UNKNOWN,
			self::ENDPOINT_NOT_OWNED,
			self::INSTALLATION_CLONE_DETECTED,
			self::NON_PUBLIC_URL,
			self::ENDPOINT_MISSING,
			self::ENDPOINT_DISABLED,
			self::URL_DRIFT,
			self::EVENT_DRIFT,
			self::WEBHOOK_ID_UNKNOWN,
			self::AMBIGUOUS_CREATE,
			self::LOCKED,
			self::TEMPORARY_PAYPAL_ERROR,
			self::PERSISTENCE_FAILED,
			self::NOT_CONFIGURED,
			self::MIGRATION_ERROR,
			self::LEGACY_BINDING_UNKNOWN,
			self::INVALID_DIFF,
			self::WEBHOOK_ID_MISSING,
			self::WEBHOOK_HEADERS_MISSING,
			self::WEBHOOK_VERIFICATION_INVALID,
			self::WEBHOOK_VERIFICATION_AMBIGUOUS,
			self::WEBHOOK_BINDING_MISMATCH,
			self::WEBHOOK_CLAIM_STORAGE_FAILED,
			self::UNEXPECTED_FIELD,
		);
	}
	/** @param string $issue PayPal issue. @return string */
	public static function from_issue( string $issue ): string {
		$issue = strtoupper( trim( $issue ) );
		if ( in_array( $issue, array( 'AUTHENTICATION_FAILURE', 'INVALID_CLIENT' ), true ) ) {
			return self::AUTHENTICATION_FAILED;
		}
		if ( in_array( $issue, array( 'PERMISSION_DENIED', 'NOT_AUTHORIZED' ), true ) ) {
			return self::INSUFFICIENT_PERMISSIONS;
		}
		if ( in_array( $issue, array( 'RESOURCE_NOT_FOUND', 'INVALID_RESOURCE_ID' ), true ) ) {
			return self::ENDPOINT_MISSING;
		}
		if ( in_array( $issue, array( 'INVALID_REQUEST', 'MALFORMED_REQUEST_JSON' ), true ) ) {
			return self::INVALID_DIFF;
		}
		return '';
	}
	/** @param int $status HTTP status. @param array<string,mixed> $body Body. @return string */
	public static function from_http( int $status, array $body ): string {
		if ( isset( $body['details'] ) && is_array( $body['details'] ) ) {
			foreach ( $body['details'] as $detail ) {
				if ( is_array( $detail ) && isset( $detail['issue'] ) ) {
					$code = self::from_issue( (string) $detail['issue'] );
					if ( '' !== $code ) {
						return $code;
					}
				}
			}
		}
		$name = isset( $body['name'] ) ? (string) $body['name'] : '';
		$mapped = self::from_issue( $name );
		if ( '' !== $mapped ) {
			return $mapped;
		}
		if ( 401 === $status ) {
			return self::AUTHENTICATION_FAILED;
		} if ( 403 === $status ) {
			return self::INSUFFICIENT_PERMISSIONS;
		} if ( 404 === $status ) {
			return self::ENDPOINT_MISSING;
		} if ( $status >= 500 || 0 === $status ) {
			return self::TEMPORARY_PAYPAL_ERROR;
		} return self::TEMPORARY_PAYPAL_ERROR;
	}
	/** @param string $code Code. @return string */
	public static function message( string $code ): string {
		$messages = array(
			self::MISSING_CREDENTIALS => __( 'Add the client ID and client secret for this profile, then try again.', 'giftflow' ),
			self::INVALID_CLIENT_ID => __( 'Enter a valid PayPal client ID.', 'giftflow' ),
			self::INVALID_CLIENT_SECRET => __( 'Enter a valid PayPal client secret.', 'giftflow' ),
			self::INVALID_CREDENTIAL_VALUE => __( 'That value looks masked or incomplete. Paste the full value from PayPal.', 'giftflow' ),
			self::ENVIRONMENT_MISMATCH => __( 'These credentials do not belong to this PayPal environment.', 'giftflow' ),
			self::AUTHENTICATION_FAILED => __( 'PayPal did not accept these credentials.', 'giftflow' ),
			self::INSUFFICIENT_PERMISSIONS => __( 'These credentials cannot manage PayPal webhooks.', 'giftflow' ),
			self::NON_PUBLIC_URL => __( 'PayPal needs a public HTTPS webhook URL.', 'giftflow' ),
			self::LOCKED => __( 'Another PayPal configuration request is running. Try again shortly.', 'giftflow' ),
			self::TEMPORARY_PAYPAL_ERROR => __( 'PayPal is temporarily unavailable. Try again shortly.', 'giftflow' ),
			self::NOT_CONFIGURED => __( 'This PayPal profile is not connected yet.', 'giftflow' ),
			self::WEBHOOK_HEADERS_MISSING => __( 'Required PayPal webhook headers are missing.', 'giftflow' ),
			self::WEBHOOK_VERIFICATION_INVALID => __( 'PayPal webhook verification failed.', 'giftflow' ),
			self::WEBHOOK_VERIFICATION_AMBIGUOUS => __( 'The webhook matched more than one PayPal profile.', 'giftflow' ),
			self::WEBHOOK_BINDING_MISMATCH => __( 'The webhook does not belong to the donation environment.', 'giftflow' ),
			self::WEBHOOK_CLAIM_STORAGE_FAILED => __( 'The webhook event could not be recorded.', 'giftflow' ),
			self::UNEXPECTED_FIELD => __( 'The request contained a field GiftFlow does not accept.', 'giftflow' ),
		);
		return isset( $messages[ $code ] ) ? $messages[ $code ] : __( 'The PayPal configuration request could not be completed.', 'giftflow' );
	}
}
