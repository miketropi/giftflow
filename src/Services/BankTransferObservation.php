<?php
/**
 * Bank transfer payment observations.
 *
 * Direct bank transfer is confirmed by an administrator through the existing
 * Donation Management meta box (or a trusted server-side `Donations::update_status()`
 * call). Core keeps those permission and nonce rules unchanged; this adapter only
 * translates the resulting status change into the normalized payment observation
 * contract so extension owners (Rewards) can act on it idempotently.
 *
 * The bank environment is a Pro-owned server setting; Core asks for it through
 * `giftflow_bank_transfer_environment` and defaults to live.
 *
 * @package GiftFlow
 * @subpackage Services
 */

namespace GiftFlow\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Emits normalized observations for bank transfer lifecycle changes.
 */
final class BankTransferObservation {

	/**
	 * Gateway id handled by this adapter.
	 *
	 * @var string
	 */
	const GATEWAY = 'direct_bank_transfer';

	/**
	 * Filter used by Pro to report the configured bank environment.
	 *
	 * @var string
	 */
	const FILTER_ENVIRONMENT = 'giftflow_bank_transfer_environment';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public static function register() {
		add_action( 'giftflow_donation_management_saved', array( __CLASS__, 'on_management_saved' ), 10, 4 );
		add_action( 'giftflow_donation_status_updated', array( __CLASS__, 'on_status_updated' ), 10, 3 );
	}

	/**
	 * Handle a completed Donation Management save.
	 *
	 * @param int    $donation_id Donation post id.
	 * @param string $old_status  Status before the save.
	 * @param string $new_status  Status after the save.
	 * @param int    $actor_id    Acting user id.
	 * @return void
	 */
	public static function on_management_saved( $donation_id, $old_status, $new_status, $actor_id ) {
		self::maybe_emit( (int) $donation_id, (string) $old_status, (string) $new_status, (int) $actor_id );
	}

	/**
	 * Handle a trusted server-side status update.
	 *
	 * @param int    $donation_id Donation post id.
	 * @param string $new_status  New status.
	 * @param string $old_status  Previous status.
	 * @return void
	 */
	public static function on_status_updated( $donation_id, $new_status, $old_status ) {
		$actor_id = function_exists( 'get_current_user_id' ) ? (int) get_current_user_id() : 0;

		self::maybe_emit( (int) $donation_id, (string) $old_status, (string) $new_status, $actor_id );
	}

	/**
	 * Emit an observation for a bank donation status transition.
	 *
	 * @param int    $donation_id Donation post id.
	 * @param string $old_status  Previous status.
	 * @param string $new_status  New status.
	 * @param int    $actor_id    Acting user id.
	 * @return void
	 */
	private static function maybe_emit( $donation_id, $old_status, $new_status, $actor_id ) {
		if ( $donation_id <= 0 || $old_status === $new_status ) {
			return;
		}

		$kind = self::kind_for_status( $new_status );

		if ( '' === $kind ) {
			return;
		}

		if ( self::GATEWAY !== (string) get_post_meta( $donation_id, '_payment_method', true ) ) {
			return;
		}

		$environment = (string) apply_filters( self::FILTER_ENVIRONMENT, 'live', $donation_id );

		if ( ! in_array( $environment, array( 'live', 'sandbox' ), true ) ) {
			$environment = 'live';
		}

		$attempt  = (string) get_post_meta( $donation_id, CheckoutExtension::META_ATTEMPT, true );
		$amount   = (string) get_post_meta( $donation_id, CheckoutExtension::META_AMOUNT, true );
		$currency = (string) get_post_meta( $donation_id, CheckoutExtension::META_CURRENCY, true );

		PaymentObservation::emit(
			array(
				'event_key'      => 'bank:' . $environment . ':' . $donation_id . ':' . $kind,
				'gateway'        => self::GATEWAY,
				'environment'    => $environment,
				'kind'           => $kind,
				'donation_id'    => $donation_id,
				'attempt_key'    => '' === $attempt ? null : $attempt,
				'provider_id'    => (string) get_post_meta( $donation_id, '_reference_number', true ),
				'transaction_id' => (string) get_post_meta( $donation_id, '_transaction_id', true ),
				'amount_minor'   => '' === $amount ? null : (int) $amount,
				'currency'       => '' === $currency ? null : $currency,
				'is_initial'     => true,
				'source'         => 'donation_management',
				'actor_id'       => $actor_id,
			)
		);
	}

	/**
	 * Map a donation status to an observation kind.
	 *
	 * @param string $status Donation status.
	 * @return string Observation kind, or '' when the status is not a payment fact.
	 */
	private static function kind_for_status( $status ) {
		switch ( $status ) {
			case 'completed':
				return 'paid';
			case 'refunded':
				return 'refund_full';
			case 'failed':
				return 'failed';
			case 'cancelled':
				return 'cancelled';
			default:
				return '';
		}
	}
}
