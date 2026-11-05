<?php
/**
 * Stable, sanitized Stripe connection error codes and their messages.
 *
 * Codes are part of the REST contract and are safe to persist in local state:
 * they never contain credentials, account ids or raw Stripe exception text.
 *
 * @package GiftFlow
 * @subpackage Stripe
 */

namespace GiftFlow\Stripe;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Error code registry with actionable, translated messages.
 */
final class StripeErrorCodes {

	/**
	 * No credentials stored for the requested environment.
	 *
	 * @var string
	 */
	public const MISSING_CREDENTIALS = 'MISSING_CREDENTIALS';

	/**
	 * Publishable key is missing, malformed or belongs to another environment.
	 *
	 * @var string
	 */
	public const INVALID_PUBLISHABLE_KEY = 'INVALID_PUBLISHABLE_KEY';

	/**
	 * Secret key is missing, malformed or belongs to another environment.
	 *
	 * @var string
	 */
	public const INVALID_SECRET_KEY = 'INVALID_SECRET_KEY';

	/**
	 * A credential value is empty, masked or otherwise unusable.
	 *
	 * @var string
	 */
	public const INVALID_CREDENTIAL_VALUE = 'INVALID_CREDENTIAL_VALUE';

	/**
	 * Publishable and secret keys belong to different Stripe environments.
	 *
	 * @var string
	 */
	public const KEY_PAIR_MISMATCH = 'KEY_PAIR_MISMATCH';

	/**
	 * Stripe rejected the credentials.
	 *
	 * @var string
	 */
	public const AUTHENTICATION_FAILED = 'AUTHENTICATION_FAILED';

	/**
	 * The key authenticated but lacks the permissions auto-setup needs.
	 *
	 * @var string
	 */
	public const INSUFFICIENT_PERMISSIONS = 'INSUFFICIENT_PERMISSIONS';

	/**
	 * Stripe could not confirm which account the sandbox key belongs to.
	 *
	 * @var string
	 */
	public const SANDBOX_IDENTITY_UNCONFIRMED = 'SANDBOX_IDENTITY_UNCONFIRMED';

	/**
	 * The staged credentials belong to a different Stripe account.
	 *
	 * @var string
	 */
	public const ACCOUNT_CHANGED = 'ACCOUNT_CHANGED';

	/**
	 * An explicit rebind confirmation is required before writing anything.
	 *
	 * @var string
	 */
	public const REBIND_REQUIRED = 'REBIND_REQUIRED';

	/**
	 * The rebind plan token does not match the freshly computed plan.
	 *
	 * @var string
	 */
	public const REBIND_PLAN_STALE = 'REBIND_PLAN_STALE';

	/**
	 * A matching remote endpoint exists but its signing secret is unknown.
	 *
	 * @var string
	 */
	public const REMOTE_EXISTING_SECRET_UNKNOWN = 'REMOTE_EXISTING_SECRET_UNKNOWN';

	/**
	 * The remote endpoint is not owned by this installation.
	 *
	 * @var string
	 */
	public const ENDPOINT_NOT_OWNED = 'ENDPOINT_NOT_OWNED';

	/**
	 * Endpoint ownership metadata points at a cloned installation.
	 *
	 * @var string
	 */
	public const INSTALLATION_CLONE_DETECTED = 'INSTALLATION_CLONE_DETECTED';

	/**
	 * The webhook URL is not a public HTTPS URL.
	 *
	 * @var string
	 */
	public const NON_PUBLIC_URL = 'NON_PUBLIC_URL';

	/**
	 * No endpoint is stored or reachable for the environment.
	 *
	 * @var string
	 */
	public const ENDPOINT_MISSING = 'ENDPOINT_MISSING';

	/**
	 * The stored endpoint exists but is disabled in Stripe.
	 *
	 * @var string
	 */
	public const ENDPOINT_DISABLED = 'ENDPOINT_DISABLED';

	/**
	 * The stored endpoint URL differs from the canonical plugin URL.
	 *
	 * @var string
	 */
	public const URL_DRIFT = 'URL_DRIFT';

	/**
	 * The stored endpoint subscribes to a different event set.
	 *
	 * @var string
	 */
	public const EVENT_DRIFT = 'EVENT_DRIFT';

	/**
	 * The local signing secret does not match the remote endpoint.
	 *
	 * @var string
	 */
	public const SECRET_UNKNOWN = 'SECRET_UNKNOWN';

	/**
	 * A create call timed out; state must be reconciled before retrying.
	 *
	 * @var string
	 */
	public const AMBIGUOUS_CREATE = 'AMBIGUOUS_CREATE';

	/**
	 * Another Stripe operation holds the environment lock.
	 *
	 * @var string
	 */
	public const LOCKED = 'LOCKED';

	/**
	 * Stripe returned a temporary failure; retrying may succeed.
	 *
	 * @var string
	 */
	public const TEMPORARY_STRIPE_ERROR = 'TEMPORARY_STRIPE_ERROR';

	/**
	 * Local configuration could not be persisted.
	 *
	 * @var string
	 */
	public const PERSISTENCE_FAILED = 'PERSISTENCE_FAILED';

	/**
	 * The environment is not configured yet.
	 *
	 * @var string
	 */
	public const NOT_CONFIGURED = 'NOT_CONFIGURED';

	/**
	 * Legacy state could not be migrated and was left untouched.
	 *
	 * @var string
	 */
	public const MIGRATION_ERROR = 'MIGRATION_ERROR';

	/**
	 * A legacy donation cannot be bound to exactly one environment.
	 *
	 * @var string
	 */
	public const LEGACY_BINDING_UNKNOWN = 'LEGACY_BINDING_UNKNOWN';

	/**
	 * The supplied repair/rebind diff was rejected.
	 *
	 * @var string
	 */
	public const INVALID_DIFF = 'INVALID_DIFF';

	/**
	 * Webhook signing secret is not configured for the environment.
	 *
	 * @var string
	 */
	public const WEBHOOK_SECRET_MISSING = 'WEBHOOK_SECRET_MISSING';

	/**
	 * The request signature did not verify against any stored secret.
	 *
	 * @var string
	 */
	public const WEBHOOK_SIGNATURE_INVALID = 'WEBHOOK_SIGNATURE_INVALID';

	/**
	 * The signature verified against more than one stored secret.
	 *
	 * @var string
	 */
	public const WEBHOOK_SIGNATURE_AMBIGUOUS = 'WEBHOOK_SIGNATURE_AMBIGUOUS';

	/**
	 * The event `livemode` flag does not match the verified environment.
	 *
	 * @var string
	 */
	public const WEBHOOK_LIVEMODE_MISMATCH = 'WEBHOOK_LIVEMODE_MISMATCH';

	/**
	 * The event does not bind to the environment of the target donation.
	 *
	 * @var string
	 */
	public const WEBHOOK_BINDING_MISMATCH = 'WEBHOOK_BINDING_MISMATCH';

	/**
	 * The event claim could not be written to storage.
	 *
	 * @var string
	 */
	public const WEBHOOK_CLAIM_STORAGE_FAILED = 'WEBHOOK_CLAIM_STORAGE_FAILED';

	/**
	 * A field that must never be supplied by a client was present.
	 *
	 * @var string
	 */
	public const UNEXPECTED_FIELD = 'UNEXPECTED_FIELD';

	/**
	 * Sanitize an arbitrary error code, keeping only registered codes.
	 *
	 * @param mixed $code Candidate code.
	 * @return string Registered code or an empty string.
	 */
	public static function sanitize( $code ): string {
		return is_string( $code ) && in_array( $code, self::all(), true ) ? $code : '';
	}

	/**
	 * Every registered code.
	 *
	 * @return string[]
	 */
	public static function all(): array {
		return array(
			self::MISSING_CREDENTIALS,
			self::INVALID_PUBLISHABLE_KEY,
			self::INVALID_SECRET_KEY,
			self::INVALID_CREDENTIAL_VALUE,
			self::KEY_PAIR_MISMATCH,
			self::AUTHENTICATION_FAILED,
			self::INSUFFICIENT_PERMISSIONS,
			self::SANDBOX_IDENTITY_UNCONFIRMED,
			self::ACCOUNT_CHANGED,
			self::REBIND_REQUIRED,
			self::REBIND_PLAN_STALE,
			self::REMOTE_EXISTING_SECRET_UNKNOWN,
			self::ENDPOINT_NOT_OWNED,
			self::INSTALLATION_CLONE_DETECTED,
			self::NON_PUBLIC_URL,
			self::ENDPOINT_MISSING,
			self::ENDPOINT_DISABLED,
			self::URL_DRIFT,
			self::EVENT_DRIFT,
			self::SECRET_UNKNOWN,
			self::AMBIGUOUS_CREATE,
			self::LOCKED,
			self::TEMPORARY_STRIPE_ERROR,
			self::PERSISTENCE_FAILED,
			self::NOT_CONFIGURED,
			self::MIGRATION_ERROR,
			self::LEGACY_BINDING_UNKNOWN,
			self::INVALID_DIFF,
			self::WEBHOOK_SECRET_MISSING,
			self::WEBHOOK_SIGNATURE_INVALID,
			self::WEBHOOK_SIGNATURE_AMBIGUOUS,
			self::WEBHOOK_LIVEMODE_MISMATCH,
			self::WEBHOOK_BINDING_MISMATCH,
			self::WEBHOOK_CLAIM_STORAGE_FAILED,
			self::UNEXPECTED_FIELD,
		);
	}

	/**
	 * Actionable, translated message for a code.
	 *
	 * @param string $code Registered error code.
	 * @return string
	 */
	public static function message( string $code ): string {
		switch ( $code ) {
			case self::MISSING_CREDENTIALS:
				return __( 'Add the publishable and secret key for this profile, then try again.', 'giftflow' );
			case self::INVALID_PUBLISHABLE_KEY:
				return __( 'That publishable key is not valid for this profile. Sandbox profiles need pk_test_ keys; Live profiles need pk_live_ keys.', 'giftflow' );
			case self::INVALID_SECRET_KEY:
				return __( 'That secret key is not valid for this profile. Sandbox profiles need sk_test_ or rk_test_ keys; Live profiles need sk_live_ or rk_live_ keys.', 'giftflow' );
			case self::INVALID_CREDENTIAL_VALUE:
				return __( 'That value looks masked or incomplete. Paste the full value from Stripe.', 'giftflow' );
			case self::KEY_PAIR_MISMATCH:
				return __( 'The publishable key and secret key belong to different environments. Both keys must come from the same Stripe account.', 'giftflow' );
			case self::AUTHENTICATION_FAILED:
				return __( 'Stripe did not accept these credentials. Check for copied spaces and confirm the key belongs to this profile.', 'giftflow' );
			case self::INSUFFICIENT_PERMISSIONS:
				return __( 'These credentials cannot manage webhook endpoints. Use a secret key with write access to webhook endpoints, or paste the signing secret from Stripe into the Signing secret field.', 'giftflow' );
			case self::SANDBOX_IDENTITY_UNCONFIRMED:
				return __( 'Stripe did not confirm the sandbox account identity. Confirm the account change to continue, or paste the signing secret from Stripe.', 'giftflow' );
			case self::ACCOUNT_CHANGED:
				return __( 'These credentials belong to a different Stripe account than the one currently configured.', 'giftflow' );
			case self::REBIND_REQUIRED:
				return __( 'These credentials belong to a different Stripe account. Confirm the change to configure the new account.', 'giftflow' );
			case self::REBIND_PLAN_STALE:
				return __( 'The configuration changed since the change was previewed. Review the change again before confirming.', 'giftflow' );
			case self::REMOTE_EXISTING_SECRET_UNKNOWN:
				return __( 'A webhook endpoint for this URL already exists in Stripe and its signing secret is unknown. Paste that signing secret into the Signing secret field, or create a new endpoint.', 'giftflow' );
			case self::ENDPOINT_NOT_OWNED:
				return __( 'That webhook endpoint was not created by this installation, so GiftFlow will not change or delete it.', 'giftflow' );
			case self::INSTALLATION_CLONE_DETECTED:
				return __( 'That webhook endpoint carries metadata from a different site. Confirm the change to create a fresh endpoint for this site.', 'giftflow' );
			case self::NON_PUBLIC_URL:
				return __( 'Stripe cannot reach this site. Auto configuration needs a public HTTPS URL; add the keys and signing secret manually on a local or private site.', 'giftflow' );
			case self::ENDPOINT_MISSING:
				return __( 'The configured webhook endpoint no longer exists in Stripe.', 'giftflow' );
			case self::ENDPOINT_DISABLED:
				return __( 'The webhook endpoint is disabled in Stripe, so Stripe is not delivering events.', 'giftflow' );
			case self::URL_DRIFT:
				return __( 'The webhook endpoint URL no longer matches this site.', 'giftflow' );
			case self::EVENT_DRIFT:
				return __( 'The webhook endpoint is missing events GiftFlow needs, or subscribes to events it does not use.', 'giftflow' );
			case self::SECRET_UNKNOWN:
				return __( 'The signing secret stored here does not match the endpoint in Stripe. Paste the current signing secret from Stripe.', 'giftflow' );
			case self::AMBIGUOUS_CREATE:
				return __( 'Stripe did not confirm the last request. GiftFlow will reconcile the existing endpoint before creating another one.', 'giftflow' );
			case self::LOCKED:
				return __( 'Another Stripe configuration request is still running. Wait a moment and try again.', 'giftflow' );
			case self::TEMPORARY_STRIPE_ERROR:
				return __( 'Stripe is temporarily unavailable. Nothing was changed locally; try again shortly.', 'giftflow' );
			case self::PERSISTENCE_FAILED:
				return __( 'The configuration could not be saved on this server. Nothing was changed in Stripe or locally.', 'giftflow' );
			case self::NOT_CONFIGURED:
				return __( 'This profile is not connected yet. Run Connect & Auto Configure, or add the keys with the pencil buttons.', 'giftflow' );
			case self::MIGRATION_ERROR:
				return __( 'Existing Stripe settings could not be read, so they were left untouched. Review the stored payment options.', 'giftflow' );
			case self::LEGACY_BINDING_UNKNOWN:
				return __( 'A stored donation is missing its Stripe environment and cannot be bound safely.', 'giftflow' );
			case self::INVALID_DIFF:
				return __( 'The requested change no longer matches the reported difference. Run Check Configuration again.', 'giftflow' );
			case self::WEBHOOK_SECRET_MISSING:
				return __( 'No webhook signing secret is stored for this environment.', 'giftflow' );
			case self::WEBHOOK_SIGNATURE_INVALID:
				return __( 'The webhook signature did not match any stored signing secret.', 'giftflow' );
			case self::WEBHOOK_SIGNATURE_AMBIGUOUS:
				return __( 'The webhook signature matched more than one stored signing secret.', 'giftflow' );
			case self::WEBHOOK_LIVEMODE_MISMATCH:
				return __( 'The webhook event mode does not match the environment its signature belongs to.', 'giftflow' );
			case self::WEBHOOK_BINDING_MISMATCH:
				return __( 'The webhook event does not belong to the environment of the donation it targets.', 'giftflow' );
			case self::WEBHOOK_CLAIM_STORAGE_FAILED:
				return __( 'The webhook event could not be recorded, so it was not processed.', 'giftflow' );
			case self::UNEXPECTED_FIELD:
				return __( 'The request contained a field GiftFlow does not accept.', 'giftflow' );
			default:
				return __( 'The Stripe configuration request could not be completed.', 'giftflow' );
		}
	}
}
