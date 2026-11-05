<?php
/**
 * Payment observation contract.
 *
 * Gateways already update donation status and transaction metadata. Rewards
 * additionally needs a *normalized* description of what the provider told us,
 * emitted only from verified branches (server-side success responses and
 * signature-verified webhooks). This class never confirms a payment itself: it
 * records the sanitized observation on the donation and fires
 * `giftflow_payment_observed`, which extension owners consume idempotently.
 *
 * @package GiftFlow
 * @subpackage Services
 */

namespace GiftFlow\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalized payment observation emitter.
 */
final class PaymentObservation {

	/**
	 * Action fired for every verified payment observation.
	 *
	 * @var string
	 */
	const ACTION_OBSERVED = 'giftflow_payment_observed';

	/**
	 * Donation meta: most recent observation (JSON).
	 *
	 * @var string
	 */
	const META_LATEST = '_giftflow_payment_observation';

	/**
	 * Donation meta: bounded observation history (JSON array).
	 *
	 * @var string
	 */
	const META_HISTORY = '_giftflow_payment_observations';

	/**
	 * Number of observations retained per donation.
	 *
	 * @var int
	 */
	const HISTORY_LIMIT = 10;

	/**
	 * Supported observation kinds.
	 *
	 * @var string[]
	 */
	const KINDS = array(
		'paid',
		'failed',
		'cancelled',
		'refund_full',
		'refund_partial',
		'dispute_open',
		'dispute_won',
		'dispute_lost',
		'reversed',
	);

	/**
	 * Supported sources.
	 *
	 * @var string[]
	 */
	const SOURCES = array(
		'sync',
		'webhook',
		'donation_management',
	);

	/**
	 * Normalize, persist and dispatch an observation.
	 *
	 * Duplicate emission is expected and safe: consumers dedupe on `event_key`.
	 *
	 * @param array $event Observation payload.
	 * @return array|\WP_Error Normalized observation, or error when invalid.
	 */
	public static function emit( array $event ) {
		$normalized = self::normalize( $event );

		if ( is_wp_error( $normalized ) ) {
			return $normalized;
		}

		if ( $normalized['donation_id'] > 0 && function_exists( 'update_post_meta' ) ) {
			self::persist( $normalized );
		}

		/**
		 * Fires after a verified payment observation is recorded.
		 *
		 * @param array $observation Normalized observation.
		 */
		do_action( self::ACTION_OBSERVED, $normalized );

		return $normalized;
	}

	/**
	 * Validate and normalize an observation payload.
	 *
	 * @param array $event Raw payload.
	 * @return array|\WP_Error
	 */
	public static function normalize( array $event ) {
		$event_key = isset( $event['event_key'] ) && is_scalar( $event['event_key'] ) ? (string) $event['event_key'] : '';

		if ( ! preg_match( '/^[A-Za-z0-9_.:|\-]{8,191}$/', $event_key ) ) {
			return new \WP_Error( 'giftflow_invalid_observation', __( 'Payment observation is missing a stable event key.', 'giftflow' ) );
		}

		$gateway = isset( $event['gateway'] ) ? sanitize_key( (string) $event['gateway'] ) : '';

		if ( '' === $gateway || strlen( $gateway ) > 32 ) {
			return new \WP_Error( 'giftflow_invalid_observation', __( 'Payment observation is missing a gateway.', 'giftflow' ) );
		}

		$environment = isset( $event['environment'] ) ? strtolower( (string) $event['environment'] ) : '';

		if ( ! in_array( $environment, array( 'live', 'sandbox' ), true ) ) {
			return new \WP_Error( 'giftflow_invalid_observation', __( 'Payment observation has an invalid environment.', 'giftflow' ) );
		}

		$kind = isset( $event['kind'] ) ? (string) $event['kind'] : '';

		if ( ! in_array( $kind, self::KINDS, true ) ) {
			return new \WP_Error( 'giftflow_invalid_observation', __( 'Payment observation has an unknown kind.', 'giftflow' ) );
		}

		$donation_id = 0;

		if ( isset( $event['donation_id'] ) && null !== $event['donation_id'] && '' !== $event['donation_id'] ) {
			$donation_id = (int) $event['donation_id'];

			if ( $donation_id <= 0 ) {
				return new \WP_Error( 'giftflow_invalid_observation', __( 'Payment observation has an invalid donation reference.', 'giftflow' ) );
			}
		}

		$amount_minor = null;

		if ( isset( $event['amount_minor'] ) && '' !== $event['amount_minor'] && null !== $event['amount_minor'] ) {
			if ( ! is_int( $event['amount_minor'] ) && ! ctype_digit( (string) $event['amount_minor'] ) ) {
				return new \WP_Error( 'giftflow_invalid_observation', __( 'Payment observation has an invalid amount.', 'giftflow' ) );
			}

			$amount_minor = (int) $event['amount_minor'];

			if ( $amount_minor < 0 ) {
				return new \WP_Error( 'giftflow_invalid_observation', __( 'Payment observation has an invalid amount.', 'giftflow' ) );
			}
		}

		$currency = null;

		if ( ! empty( $event['currency'] ) && is_scalar( $event['currency'] ) ) {
			$candidate = CheckoutMoney::normalize_currency( $event['currency'] );

			if ( preg_match( '/^[A-Z]{3}$/', $candidate ) ) {
				$currency = $candidate;
			}
		}

		$source = isset( $event['source'] ) ? (string) $event['source'] : 'sync';

		if ( ! in_array( $source, self::SOURCES, true ) ) {
			$source = 'sync';
		}

		return array(
			'event_key'       => $event_key,
			'gateway'         => $gateway,
			'environment'     => $environment,
			'kind'            => $kind,
			'donation_id'     => $donation_id,
			'attempt_key'     => self::scalar_string( $event, 'attempt_key', 191 ),
			'provider_id'     => self::scalar_string( $event, 'provider_id', 191 ),
			'transaction_id'  => self::scalar_string( $event, 'transaction_id', 191 ),
			'provider_account' => self::scalar_string( $event, 'provider_account', 191 ),
			'amount_minor'    => $amount_minor,
			'currency'        => $currency,
			'is_initial'      => ! empty( $event['is_initial'] ),
			'source'          => $source,
			'actor_id'        => isset( $event['actor_id'] ) ? (int) $event['actor_id'] : 0,
			'observed_at'     => isset( $event['observed_at'] ) && is_scalar( $event['observed_at'] ) ? (string) $event['observed_at'] : gmdate( 'Y-m-d H:i:s' ),
		);
	}

	/**
	 * Persist an observation on the donation for later reconciliation.
	 *
	 * @param array $observation Normalized observation.
	 * @return void
	 */
	private static function persist( array $observation ) {
		$donation_id = (int) $observation['donation_id'];

		update_post_meta( $donation_id, self::META_LATEST, wp_json_encode( $observation ) );

		$history = get_post_meta( $donation_id, self::META_HISTORY, true );

		if ( ! is_array( $history ) ) {
			$history = array();
		}

		$history[] = $observation;

		if ( count( $history ) > self::HISTORY_LIMIT ) {
			$history = array_slice( $history, - self::HISTORY_LIMIT );
		}

		update_post_meta( $donation_id, self::META_HISTORY, wp_json_encode( $history ) );
	}

	/**
	 * Read the latest observation for a donation.
	 *
	 * @param int $donation_id Donation ID.
	 * @return array Empty array when none recorded.
	 */
	public static function latest( $donation_id ) {
		$donation_id = (int) $donation_id;

		if ( $donation_id <= 0 || ! function_exists( 'get_post_meta' ) ) {
			return array();
		}

		$raw = get_post_meta( $donation_id, self::META_LATEST, true );

		if ( ! is_string( $raw ) || '' === $raw ) {
			return array();
		}

		$decoded = json_decode( $raw, true );

		return is_array( $decoded ) ? $decoded : array();
	}

	/**
	 * Read one scalar field as a bounded string.
	 *
	 * @param array  $event Source event.
	 * @param string $key   Field name.
	 * @param int    $max   Maximum length.
	 * @return string|null
	 */
	private static function scalar_string( array $event, $key, $max ) {
		if ( ! isset( $event[ $key ] ) || ! is_scalar( $event[ $key ] ) ) {
			return null;
		}

		$value = trim( (string) $event[ $key ] );

		if ( '' === $value ) {
			return null;
		}

		return substr( $value, 0, $max );
	}
}
