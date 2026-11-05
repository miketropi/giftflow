<?php
/**
 * Checkout extension contract.
 *
 * GiftFlow Pro (Rewards) needs a server-owned hook between "the donation form
 * data is valid" and "a payment is created with the gateway". The contract is
 * deliberately narrow:
 *
 * - `prepare()` runs after the request nonce/recaptcha/required-field checks and
 *   before any provider call. Pro validates the whole request, creates a
 *   durable intent and returns the exact money/environment it wants charged.
 * - {@see \GiftFlow\Services\CheckoutExtension::bind()} runs once the Core
 *   donation record exists, so Pro can attach the attempt to a real donation.
 * - {@see \GiftFlow\Services\CheckoutExtension::failed()} reports a payment
 *   failure so Pro can release the exact hold once.
 *
 * An empty result means "no extension" and Core behaves exactly as before.
 *
 * @package GiftFlow
 * @subpackage Services
 */

namespace GiftFlow\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filter/action bridge for third-party checkout extensions.
 */
final class CheckoutExtension {

	/**
	 * Filter: `apply_filters( 'giftflow_checkout_extension_prepare', array(), $fields, $context )`.
	 *
	 * @var string
	 */
	const FILTER_PREPARE = 'giftflow_checkout_extension_prepare';

	/**
	 * Action: `do_action( 'giftflow_checkout_extension_bound', $extension, $binding )`.
	 *
	 * @var string
	 */
	const ACTION_BOUND = 'giftflow_checkout_extension_bound';

	/**
	 * Action: `do_action( 'giftflow_checkout_extension_failed', $extension, $reason, $definitive )`.
	 *
	 * @var string
	 */
	const ACTION_FAILED = 'giftflow_checkout_extension_failed';

	/**
	 * Maximum stored provider reference length (Stripe/PayPal ids fit easily).
	 *
	 * @var int
	 */
	const MAX_PROVIDER_ID_LENGTH = 191;

	/**
	 * Donation meta keys written for an extended checkout.
	 *
	 * @var string
	 */
	const META_ATTEMPT   = '_giftflow_checkout_attempt';
	const META_AMOUNT    = '_giftflow_payment_amount_minor';
	const META_CURRENCY  = '_giftflow_payment_currency';
	const META_ENVIRONMENT = '_giftflow_payment_environment';
	const META_REWARD    = '_giftflow_reward_id';

	/**
	 * Whether the extension result means "no extension".
	 *
	 * @param mixed $extension Value returned by {@see CheckoutExtension::prepare()}.
	 * @return bool
	 */
	public static function is_empty( $extension ) {
		return empty( $extension ) || ! is_array( $extension );
	}

	/**
	 * Run the extension preparation filter and validate the result.
	 *
	 * @param array $fields  Sanitized donation form fields (already validated by Core).
	 * @param array $context Server-resolved checkout context.
	 * @return array|\WP_Error Validated extension data, or empty array when unused.
	 */
	public static function prepare( array $fields, array $context ) {
		if ( ! function_exists( 'apply_filters' ) ) {
			return array();
		}

		/**
		 * Filter the checkout extension data for the current submission.
		 *
		 * @param array $extension Empty extension data.
		 * @param array $fields    Sanitized donation form fields.
		 * @param array $context   Server-resolved context: gateway, environment, currency, raw_amount, donation_type, campaign_id, provider_account, is_recurring.
		 */
		$extension = apply_filters( self::FILTER_PREPARE, array(), $fields, $context );

		if ( self::is_empty( $extension ) ) {
			return array();
		}

		if ( ! is_array( $extension ) ) {
			return new \WP_Error( 'giftflow_checkout_extension_invalid', __( 'Checkout extension returned invalid data.', 'giftflow' ) );
		}

		return self::sanitize( $extension );
	}

	/**
	 * Validate and normalize extension data.
	 *
	 * @param array $extension Extension data from the filter.
	 * @return array|\WP_Error
	 */
	public static function sanitize( array $extension ) {
		$attempt_key = isset( $extension['attempt_key'] ) ? $extension['attempt_key'] : '';

		if ( ! is_string( $attempt_key ) || ! preg_match( '/^[A-Za-z0-9_.:\-]{8,191}$/', $attempt_key ) ) {
			return new \WP_Error( 'giftflow_checkout_extension_invalid', __( 'Checkout extension is missing a valid attempt key.', 'giftflow' ) );
		}

		$amount_minor = isset( $extension['amount_minor'] ) ? $extension['amount_minor'] : null;

		if ( ! is_int( $amount_minor ) || $amount_minor <= 0 ) {
			return new \WP_Error( 'giftflow_checkout_extension_invalid', __( 'Checkout extension is missing a valid amount.', 'giftflow' ) );
		}

		$currency = CheckoutMoney::normalize_currency( isset( $extension['currency'] ) ? $extension['currency'] : '' );

		if ( ! CheckoutMoney::is_supported_currency( $currency ) ) {
			return new \WP_Error( 'giftflow_checkout_extension_invalid', __( 'Checkout extension returned an unsupported currency.', 'giftflow' ) );
		}

		$environment = isset( $extension['environment'] ) ? strtolower( (string) $extension['environment'] ) : '';

		if ( ! in_array( $environment, array( 'live', 'sandbox' ), true ) ) {
			return new \WP_Error( 'giftflow_checkout_extension_invalid', __( 'Checkout extension returned an invalid payment environment.', 'giftflow' ) );
		}

		$donation_id = null;

		if ( isset( $extension['donation_id'] ) && null !== $extension['donation_id'] && '' !== $extension['donation_id'] ) {
			$donation_id = (int) $extension['donation_id'];

			if ( $donation_id <= 0 ) {
				return new \WP_Error( 'giftflow_checkout_extension_invalid', __( 'Checkout extension returned an invalid donation reference.', 'giftflow' ) );
			}
		}

		$provider_id = null;

		if ( isset( $extension['provider_id'] ) && null !== $extension['provider_id'] && '' !== $extension['provider_id'] ) {
			if ( ! is_scalar( $extension['provider_id'] ) ) {
				return new \WP_Error( 'giftflow_checkout_extension_invalid', __( 'Checkout extension returned an invalid provider reference.', 'giftflow' ) );
			}

			$provider_id = substr( sanitize_text_field( (string) $extension['provider_id'] ), 0, self::MAX_PROVIDER_ID_LENGTH );

			if ( '' === $provider_id ) {
				$provider_id = null;
			}
		}

		$replay_response = null;

		if ( isset( $extension['replay_response'] ) && is_array( $extension['replay_response'] ) ) {
			$replay_response = $extension['replay_response'];
		}

		return array(
			'attempt_key'     => $attempt_key,
			'amount_minor'    => $amount_minor,
			'currency'        => $currency,
			'environment'     => $environment,
			'donation_id'     => $donation_id,
			'provider_id'     => $provider_id,
			'replay_response' => $replay_response,
			'reward_id'       => isset( $extension['reward_id'] ) ? (int) $extension['reward_id'] : 0,
		);
	}

	/**
	 * Read a stored extension attempt from a donation.
	 *
	 * @param int $donation_id Donation post ID.
	 * @return array Empty array when the donation has no extension attempt.
	 */
	public static function from_donation( $donation_id ) {
		$donation_id = (int) $donation_id;

		if ( ! $donation_id || ! function_exists( 'get_post_meta' ) ) {
			return array();
		}

		$attempt = (string) get_post_meta( $donation_id, self::META_ATTEMPT, true );

		if ( '' === $attempt ) {
			return array();
		}

		return array(
			'attempt_key'  => $attempt,
			'amount_minor' => (int) get_post_meta( $donation_id, self::META_AMOUNT, true ),
			'currency'     => (string) get_post_meta( $donation_id, self::META_CURRENCY, true ),
			'environment'  => (string) get_post_meta( $donation_id, self::META_ENVIRONMENT, true ),
			'reward_id'    => (int) get_post_meta( $donation_id, self::META_REWARD, true ),
		);
	}

	/**
	 * Donation meta values to persist for an extended checkout.
	 *
	 * @param array $extension Validated extension data.
	 * @return array<string,string|int>
	 */
	public static function donation_meta( array $extension ) {
		if ( self::is_empty( $extension ) || empty( $extension['attempt_key'] ) ) {
			return array();
		}

		$meta = array(
			self::META_ATTEMPT     => (string) $extension['attempt_key'],
			self::META_AMOUNT      => (int) $extension['amount_minor'],
			self::META_CURRENCY    => (string) $extension['currency'],
			self::META_ENVIRONMENT => (string) $extension['environment'],
		);

		if ( ! empty( $extension['reward_id'] ) ) {
			$meta[ self::META_REWARD ] = (int) $extension['reward_id'];
		}

		return $meta;
	}

	/**
	 * Notify extension owners that the attempt is bound to a donation.
	 *
	 * @param array $extension Validated extension data.
	 * @param array $binding   Binding facts: donation_id, provider_id (optional), gateway (optional).
	 * @return void
	 */
	public static function bind( array $extension, array $binding ) {
		if ( self::is_empty( $extension ) ) {
			return;
		}

		$binding['donation_id'] = isset( $binding['donation_id'] ) ? (int) $binding['donation_id'] : 0;

		if ( $binding['donation_id'] <= 0 ) {
			return;
		}

		if ( isset( $binding['provider_id'] ) ) {
			$binding['provider_id'] = null === $binding['provider_id'] ? null : substr( (string) $binding['provider_id'], 0, self::MAX_PROVIDER_ID_LENGTH );
		}

		/**
		 * Fires when an extended checkout attempt is bound to a Core donation.
		 *
		 * @param array $extension Validated extension data.
		 * @param array $binding   Binding facts.
		 */
		do_action( self::ACTION_BOUND, $extension, $binding );
	}

	/**
	 * Notify extension owners that the payment attempt failed.
	 *
	 * @param array  $extension  Validated extension data.
	 * @param string $reason     Machine readable reason code.
	 * @param bool   $definitive True when the provider definitively rejected the attempt.
	 * @return void
	 */
	public static function failed( array $extension, $reason, $definitive = true ) {
		if ( self::is_empty( $extension ) ) {
			return;
		}

		/**
		 * Fires when an extended checkout attempt failed.
		 *
		 * @param array  $extension  Validated extension data.
		 * @param string $reason     Machine readable reason code.
		 * @param bool   $definitive Whether the failure is definitive.
		 */
		do_action( self::ACTION_FAILED, $extension, (string) $reason, (bool) $definitive );
	}
}
