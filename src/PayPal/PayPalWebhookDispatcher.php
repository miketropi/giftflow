<?php
/**
 * PayPal webhook dispatcher with explicit environment context.
 *
 * @package GiftFlow
 * @subpackage PayPal
 */

namespace GiftFlow\PayPal;

// phpcs:disable Generic.Commenting.DocComment.MissingShort,Squiz.Commenting.FunctionComment.MissingParamTag -- Existing concise method annotations accurately describe the dispatcher helpers.

// phpcs:disable Universal.NamingConventions.NoReservedKeywordParameterNames.resourceFound,Universal.NamingConventions.NoReservedKeywordParameterNames.parentFound -- Pinned webhook API and domain terminology use these parameter names.

use GiftFlow\Core\Donation_Event_History;
use GiftFlow\Core\Donations;
use GiftFlow\Core\Logger as Giftflow_Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Routes verified PayPal events to environment-bound donations. */
final class PayPalWebhookDispatcher {

	/** @var PayPalConnectionRepository */
	private PayPalConnectionRepository $repository;

	/** @var PayPalDonationBinding */
	private PayPalDonationBinding $binding;

	/**
	 * Constructor.
	 *
	 * @param PayPalConnectionRepository|null $repository Repository.
	 */
	public function __construct( ?PayPalConnectionRepository $repository = null ) {
		$this->repository = $repository ?? new PayPalConnectionRepository();
		$this->binding    = new PayPalDonationBinding( $this->repository );
	}

	/**
	 * Whether an event is supported.
	 *
	 * @param string $event_type Event type.
	 * @return bool
	 */
	public static function is_supported( string $event_type ): bool {
		return in_array( $event_type, array_merge( PayPalWebhookEventRegistry::BASE_EVENTS, PayPalWebhookEventRegistry::RECURRING_EVENTS ), true );
	}

	/**
	 * Resolve donations targeted by an event without mutating them.
	 *
	 * @param string $event_type  Event type.
	 * @param array  $resource    PayPal resource.
	 * @param string $environment Verified environment.
	 * @return int[]
	 */
	public function resolve_target_donation_ids( string $event_type, array $resource, string $environment ): array {
		if ( 0 === strpos( $event_type, 'CHECKOUT.ORDER.' ) ) {
			return array();
		}
		if ( false !== strpos( $event_type, 'BILLING.SUBSCRIPTION.' ) ) {
			$id       = isset( $resource['id'] ) ? (string) $resource['id'] : '';
			$donation = $this->find_donation_by_subscription_id( $id, $environment );
			if ( ! $donation && isset( $resource['custom_id'] ) ) {
				$custom = absint( $resource['custom_id'] );
				$donation = $custom && $this->binding->matches( $custom, $environment ) ? $custom : false;
			}
			return $donation ? array( (int) $donation ) : array();
		}
		if ( ! empty( $resource['billing_agreement_id'] ) ) {
			$donation = $this->find_donation_by_subscription_id( (string) $resource['billing_agreement_id'], $environment );
			return $donation ? array( (int) $donation ) : array();
		}
		$transaction = $this->transaction_id( $resource, false !== strpos( $event_type, 'REFUNDED' ) ); // phpcs:ignore WordPress.PHP.YodaConditions.NotYoda -- Comparison already places the constant on the left.
		$donation    = $this->find_donation_by_transaction_id( $transaction, $environment );
		return $donation ? array( (int) $donation ) : array();
	}

	/**
	 * Bind or validate a donation against the verified profile.
	 *
	 * @param int    $donation_id Donation ID.
	 * @param string $environment Verified environment.
	 * @param string $app_id      Verified app identity.
	 * @return bool
	 */
	public function bind_donation_environment( int $donation_id, string $environment, string $app_id ): bool {
		return $this->binding->bind( $donation_id, $environment, $app_id );
	}

	/**
	 * Dispatch an event.
	 *
	 * @param string $event_type  Event type.
	 * @param array  $resource    Resource.
	 * @param string $environment Verified environment.
	 * @return bool
	 */
	public function dispatch( string $event_type, array $resource, string $environment ): bool {
		switch ( $event_type ) {
			case 'CHECKOUT.ORDER.APPROVED':
			case 'CHECKOUT.ORDER.COMPLETED':
				return true;
			case 'PAYMENT.CAPTURE.COMPLETED':
			case 'PAYMENT.SALE.COMPLETED':
				if ( ! empty( $resource['billing_agreement_id'] ) ) {
					return $this->subscription_payment_completed( $resource, $environment );
				}
				return $this->payment_completed( $resource, $environment );
			case 'PAYMENT.CAPTURE.DENIED':
			case 'PAYMENT.SALE.DENIED':
				if ( ! empty( $resource['billing_agreement_id'] ) ) {
					return $this->subscription_payment_denied( $resource, $environment );
				}
				return $this->payment_denied( $resource, $environment );
			case 'PAYMENT.CAPTURE.REFUNDED':
				return $this->payment_refunded( $resource, $environment );
			case 'BILLING.SUBSCRIPTION.CREATED':
				return $this->subscription_created( $resource, $environment );
			case 'BILLING.SUBSCRIPTION.ACTIVATED':
				return $this->subscription_status(
					$resource,
					$environment,
					'active',
					'recurring_subscription_activated',
					'completed',
					'Webhook: BILLING.SUBSCRIPTION.ACTIVATED',
					'giftflow_paypal_subscription_activated'
				);
			case 'BILLING.SUBSCRIPTION.CANCELLED':
				return $this->subscription_status(
					$resource,
					$environment,
					'cancelled',
					'recurring_subscription_cancelled',
					'cancelled',
					'Webhook: BILLING.SUBSCRIPTION.CANCELLED',
					'giftflow_paypal_subscription_cancelled'
				);
			case 'BILLING.SUBSCRIPTION.SUSPENDED':
				return $this->subscription_status(
					$resource,
					$environment,
					'suspended',
					'recurring_subscription_suspended',
					'suspended',
					'Webhook: BILLING.SUBSCRIPTION.SUSPENDED',
					'giftflow_paypal_subscription_suspended'
				);
			case 'BILLING.SUBSCRIPTION.EXPIRED':
				return $this->subscription_status(
					$resource,
					$environment,
					'expired',
					'recurring_subscription_expired',
					'expired',
					'Webhook: BILLING.SUBSCRIPTION.EXPIRED',
					'giftflow_paypal_subscription_expired'
				);
			case 'CUSTOMER.DISPUTE.CREATED':
			case 'CUSTOMER.DISPUTE.UPDATED':
			case 'CUSTOMER.DISPUTE.RESOLVED':
				return $this->customer_dispute( $resource, $environment );
			case 'PAYMENT.CAPTURE.REVERSED':
			case 'PAYMENT.SALE.REVERSED':
				return $this->payment_reversed( $resource, $environment );
		}
		return false;
	}

	/** Extract transaction ID. */
	private function transaction_id( array $resource, bool $refund ): string {
		if ( ! $refund && ! empty( $resource['id'] ) ) {
			return (string) $resource['id'];
		}
		$links = isset( $resource['links'] ) && is_array( $resource['links'] ) ? $resource['links'] : array();
		foreach ( $links as $link ) {
			$is_link = isset( $link['rel'], $link['href'] );
			$is_rel  = 'up' === $link['rel'] || ( ! $refund && 'self' === $link['rel'] );
			$is_path = false !== strpos( $link['href'], '/captures/' ) || false !== strpos( $link['href'], '/payments/sale/' );
			if ( $is_link && $is_rel && $is_path ) {
				return basename( $link['href'] );
			}
		}
		return '';
	}

	/** Find an environment-bound donation by a meta value. */
	private function find_bound_donation( string $key, string $value, string $environment, bool $parent = false ) {
		if ( '' === $value ) {
			return false;
		}
		$profile = $this->repository->get_profile( $environment );
		$query   = array(
			array(
				'key' => $key,
				'value' => $value,
			),
			array(
				'key' => '_paypal_environment',
				'value' => $environment,
			),
		);
		if ( ! empty( $profile['binding_fingerprint'] ) ) {
			$query[] = array(
				'key' => '_paypal_binding_fingerprint',
				'value' => $profile['binding_fingerprint'],
			);
		}
		if ( $parent ) {
			$query[] = array(
				'key' => '_is_subscription_parent',
				'value' => '1',
			);
		}
		$posts = get_posts(
			array(
				'post_type' => 'donation',
				'posts_per_page' => 1,
				'fields' => 'ids',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Environment and binding metadata are required to isolate PayPal resources.
				'meta_query' => $query,
			)
		); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		return ! empty( $posts ) ? (int) $posts[0] : false;
	}

	/** Find donation by transaction ID. */
	private function find_donation_by_transaction_id( string $id, string $environment ) {
		return $this->find_bound_donation( '_transaction_id', $id, $environment );
	}

	/** Find parent donation by subscription ID. */
	private function find_donation_by_subscription_id( string $id, string $environment ) {
		return $this->find_bound_donation( '_paypal_subscription_id', $id, $environment, true );
	}

	/** Handle completed one-time payment. */
	private function payment_completed( array $resource, string $environment ): bool {
		$id       = $this->transaction_id( $resource, false );
		$donation = $this->find_donation_by_transaction_id( $id, $environment );
		if ( ! $donation ) {
			// Payment confirmed before the Core donation exists (or the attempt
			// was never bound): keep it visible instead of silently dropping it.
			$this->observe_unbound( 'paid', $resource, $environment, $id );
			return false;
		}
		Donation_Event_History::add(
			$donation,
			'payment_succeeded',
			'completed',
			__( 'Webhook: payment completed', 'giftflow' ),
			array(
				'transaction_id' => $id,
				'gateway' => 'paypal',
				'source' => 'webhook',
			)
		);
		Giftflow_Logger::info(
			'paypal.webhook.payment.completed',
			array(
				'donation_id' => $donation,
				'transaction_id' => $id,
				'gateway' => 'paypal',
			),
			'paypal'
		);
		( new Donations() )->update_status( $donation, 'completed' );
		update_post_meta( $donation, '_webhook_completed_data', wp_json_encode( $resource ) );
		$this->emit_observation( 'paid', (int) $donation, $environment, $id, $id, true );
		do_action( 'giftflow_paypal_webhook_payment_completed', $donation, $resource );
		return true;
	}

	/** Handle denied one-time payment. */
	private function payment_denied( array $resource, string $environment ): bool {
		$id       = $this->transaction_id( $resource, false );
		$donation = $this->find_donation_by_transaction_id( $id, $environment );
		if ( ! $donation ) {
			return false; }
		$message = __( 'Payment was denied', 'giftflow' );
		if ( isset( $resource['status_details']['reason'] ) ) {
			$message .= ': ' . sanitize_text_field( $resource['status_details']['reason'] ); }
		Donation_Event_History::add(
			$donation,
			'payment_failed',
			'failed',
			$message,
			array(
				'transaction_id' => $id,
				'gateway' => 'paypal',
				'source' => 'webhook',
			)
		);
		Giftflow_Logger::error(
			'paypal.webhook.payment.denied',
			array(
				'donation_id' => $donation,
				'transaction_id' => $id,
				'error_message' => $message,
				'gateway' => 'paypal',
			),
			'paypal'
		);
		( new Donations() )->update_status( $donation, 'failed' );
		update_post_meta( $donation, '_payment_error', $message );
		update_post_meta( $donation, '_webhook_denied_data', wp_json_encode( $resource ) );
		$this->emit_observation( 'failed', (int) $donation, $environment, $id, $id, true );
		do_action( 'giftflow_paypal_webhook_payment_denied', $donation, $resource );
		return true;
	}

	/** Handle refund. */
	private function payment_refunded( array $resource, string $environment ): bool {
		$id       = $this->transaction_id( $resource, true );
		$donation = $this->find_donation_by_transaction_id( $id, $environment );
		if ( ! $donation ) {
			return false;
		}
		$refund = isset( $resource['id'] ) ? (string) $resource['id'] : '';
		Donation_Event_History::add(
			$donation,
			'payment_refunded',
			'refunded',
			__( 'Webhook: payment refunded', 'giftflow' ),
			array(
				'refund_id' => $refund,
				'gateway' => 'paypal',
				'source' => 'webhook',
			)
		);
		Giftflow_Logger::info(
			'paypal.webhook.payment.refunded',
			array(
				'donation_id' => $donation,
				'refund_id' => $refund,
				'gateway' => 'paypal',
			),
			'paypal'
		);
		( new Donations() )->update_status( $donation, 'refunded' );
		if ( '' !== $refund ) {
			update_post_meta( $donation, '_refund_id', $refund );
		}
		if ( isset( $resource['amount']['value'] ) ) {
			update_post_meta( $donation, '_refund_amount', sanitize_text_field( $resource['amount']['value'] ) );
		}
		update_post_meta( $donation, '_refund_raw_data', wp_json_encode( $resource ) );

		$refund_kind = $this->refund_kind( $resource, (int) $donation );

		$this->emit_observation( $refund_kind, (int) $donation, $environment, $refund, $id, true );
		do_action( 'giftflow_paypal_webhook_payment_refunded', $donation, $resource );
		return true;
	}

	/** Handle subscription created. */
	private function subscription_created( array $resource, string $environment ): bool {
		$id       = isset( $resource['id'] ) ? (string) $resource['id'] : '';
		$donation = $this->find_donation_by_subscription_id( $id, $environment );
		if ( ! $donation && isset( $resource['custom_id'] ) ) {
			$candidate = absint( $resource['custom_id'] );
			if ( $candidate && $this->binding->matches( $candidate, $environment ) ) {
				$donation = $candidate;
				update_post_meta( $donation, '_paypal_subscription_id', $id );
			}
		}
		if ( ! $donation ) {
			return false;
		}
		Donation_Event_History::add(
			$donation,
			'recurring_subscription_created_webhook',
			'pending',
			__( 'Webhook: BILLING.SUBSCRIPTION.CREATED — subscription awaiting donor approval', 'giftflow' ),
			array(
				'subscription_id' => $id,
				'gateway' => 'paypal',
			)
		);
		do_action( 'giftflow_paypal_subscription_created_webhook', $donation, $id, $resource );
		return true;
	}

	/** Handle a subscription status transition. */
	private function subscription_status( array $resource, string $environment, string $recurring_status, string $history_event, string $history_status, string $message, string $action ): bool {
		$id       = isset( $resource['id'] ) ? (string) $resource['id'] : '';
		$donation = $this->find_donation_by_subscription_id( $id, $environment );
		if ( ! $donation && 'active' === $recurring_status && isset( $resource['custom_id'] ) ) {
			$candidate = absint( $resource['custom_id'] );
			if ( $candidate && $this->binding->matches( $candidate, $environment ) ) {
				$donation = $candidate;
				update_post_meta( $donation, '_paypal_subscription_id', $id );
			}
		}
		if ( ! $donation ) {
			return false;
		}
		update_post_meta( $donation, '_recurring_status', $recurring_status );
		if ( 'active' === $recurring_status && isset( $resource['billing_info']['next_billing_time'] ) ) {
			update_post_meta(
				$donation,
				'_recurring_next_payment_date',
				sanitize_text_field( $resource['billing_info']['next_billing_time'] )
			);
		}
		switch ( $history_event ) {
			case 'recurring_subscription_activated':
				$translated_message = __( 'Webhook: BILLING.SUBSCRIPTION.ACTIVATED', 'giftflow' );
				break;
			case 'recurring_subscription_cancelled':
				$translated_message = __( 'Webhook: BILLING.SUBSCRIPTION.CANCELLED', 'giftflow' );
				break;
			case 'recurring_subscription_suspended':
				$translated_message = __( 'Webhook: BILLING.SUBSCRIPTION.SUSPENDED', 'giftflow' );
				break;
			case 'recurring_subscription_expired':
				$translated_message = __( 'Webhook: BILLING.SUBSCRIPTION.EXPIRED', 'giftflow' );
				break;
			default:
				$translated_message = $message;
		}
		Donation_Event_History::add(
			$donation,
			$history_event,
			$history_status,
			$translated_message,
			array(
				'subscription_id' => $id,
				'gateway' => 'paypal',
				'source' => 'webhook',
			)
		);
		switch ( $action ) {
			case 'giftflow_paypal_subscription_activated':
				do_action( 'giftflow_paypal_subscription_activated', $donation, $id, $resource );
				break;
			case 'giftflow_paypal_subscription_cancelled':
				do_action( 'giftflow_paypal_subscription_cancelled', $donation, $id, $resource );
				break;
			case 'giftflow_paypal_subscription_suspended':
				do_action( 'giftflow_paypal_subscription_suspended', $donation, $id, $resource );
				break;
			case 'giftflow_paypal_subscription_expired':
				do_action( 'giftflow_paypal_subscription_expired', $donation, $id, $resource );
				break;
		}
		return true;
	}

	/** Handle completed subscription payment. */
	private function subscription_payment_completed( array $resource, string $environment ): bool {
		$subscription = isset( $resource['billing_agreement_id'] ) ? (string) $resource['billing_agreement_id'] : '';
		$sale         = isset( $resource['id'] ) ? (string) $resource['id'] : '';
		$parent       = $this->find_donation_by_subscription_id( $subscription, $environment );
		if ( ! $parent || '' === $sale ) {
			return false; }
		$existing = $this->find_bound_donation( '_paypal_sale_id', $sale, $environment );
		if ( $existing || get_post_meta( $parent, '_transaction_id', true ) === $sale ) {
			return false; }
		$first    = '' === (string) get_post_meta( $parent, '_paypal_sale_id', true );
		$amount   = isset( $resource['amount']['total'] ) ? $resource['amount']['total'] : '';
		if ( $first ) {
			update_post_meta( $parent, '_transaction_id', $sale );
			update_post_meta( $parent, '_paypal_sale_id', $sale );
			update_post_meta( $parent, '_transaction_raw_data', wp_json_encode( $resource ) );
			( new Donations() )->update_status( $parent, 'completed' );
			update_post_meta( $parent, '_recurring_status', 'active' );
			Donation_Event_History::add(
				$parent,
				'recurring_payment_first',
				'completed',
				__( 'Webhook: PAYMENT.SALE.COMPLETED (first charge)', 'giftflow' ),
				array(
					'sale_id'         => $sale,
					'subscription_id' => $subscription,
					'amount'          => $amount,
					'gateway'         => 'paypal',
					'source'          => 'webhook',
				)
			);
			$this->emit_observation( 'paid', (int) $parent, $environment, $sale, $sale, true );
			do_action( 'giftflow_donation_after_payment_processed', $parent, true );
		} else {
			$renewal = wp_insert_post(
				array(
					'post_title'  => sprintf(
						/* translators: %s: parent donation ID */
						__( 'Recurring Donation (renewal of #%s)', 'giftflow' ),
						$parent
					),
					'post_type'   => 'donation',
					'post_status' => 'publish',
				)
			);

			if ( is_wp_error( $renewal ) ) {
				return false;
			}

			$meta      = get_post_meta( $parent );
			$copy_keys = array(
				'_amount',
				'_campaign_id',
				'_donor_id',
				'_payment_method',
				'_donation_type',
				'_recurring_interval',
				PayPalDonationBinding::META_ENVIRONMENT,
				PayPalDonationBinding::META_APP_ID,
				PayPalDonationBinding::META_BINDING,
			);

			foreach ( $copy_keys as $key ) {
				if ( isset( $meta[ $key ][0] ) ) {
					update_post_meta( $renewal, $key, $meta[ $key ][0] );
				}
			}

			if ( '' !== $amount ) {
				update_post_meta( $renewal, '_amount', floatval( $amount ) );
			}

			foreach ( array(
				'_status'               => 'completed',
				'_parent_donation_id'   => $parent,
				'_paypal_sale_id'       => $sale,
				'_paypal_subscription_id' => $subscription,
				'_transaction_id'       => $sale,
				'_transaction_raw_data' => wp_json_encode( $resource ),
				'_is_subscription_renewal' => '1',
			) as $key => $value ) {
				update_post_meta( $renewal, $key, $value );
			}

			Donation_Event_History::add(
				$renewal,
				'recurring_payment_renewal',
				'completed',
				__( 'Webhook: PAYMENT.SALE.COMPLETED (renewal)', 'giftflow' ),
				array(
					'sale_id'            => $sale,
					'subscription_id'    => $subscription,
					'parent_donation_id' => $parent,
					'amount'             => $amount,
					'gateway'            => 'paypal',
					'source'             => 'webhook',
				)
			);

			$this->emit_observation( 'paid', (int) $renewal, $environment, $sale, $sale, false );
			do_action( 'giftflow_paypal_recurring_renewal_created', $renewal, $parent, $subscription, $resource );
		}

		$this->refresh_next_payment_date( $parent, $subscription, $environment );

		return true;
	}

	/**
	 * Refresh the parent donation's next payment date from the resolved environment.
	 *
	 * The sale resource carries no billing schedule, so the next billing time is
	 * read from the subscription that owns it, through the environment-bound
	 * client. A failure leaves the stored value untouched.
	 *
	 * @param int    $parent       Parent donation post id.
	 * @param string $subscription Subscription id.
	 * @param string $environment  Verified environment id.
	 * @return void
	 */
	/**
	 * Handle customer dispute events.
	 *
	 * The dispute never changes the Core donation status. Unknown states are
	 * reported as an open dispute and never interpreted as a win.
	 *
	 * @param array  $resource    Dispute resource.
	 * @param string $environment Verified environment.
	 * @return bool
	 */
	private function customer_dispute( array $resource, string $environment ): bool {
		$dispute_id = isset( $resource['dispute_id'] ) ? (string) $resource['dispute_id'] : ( isset( $resource['id'] ) ? (string) $resource['id'] : '' );

		if ( '' === $dispute_id ) {
			return false;
		}

		$donation   = 0;
		$seller_txn = '';

		if ( isset( $resource['disputed_transactions'][0] ) && is_array( $resource['disputed_transactions'][0] ) ) {
			$first      = $resource['disputed_transactions'][0];
			$seller_txn = isset( $first['seller_transaction_id'] ) ? (string) $first['seller_transaction_id'] : '';

			if ( '' !== $seller_txn ) {
				$found = $this->find_donation_by_transaction_id( $seller_txn, $environment );
				$donation = $found ? (int) $found : 0;
			}
		}

		$kind = $this->dispute_kind( $resource );

		if ( $donation > 0 ) {
			Donation_Event_History::add(
				$donation,
				'payment_dispute',
				'',
				__( 'Webhook: customer dispute', 'giftflow' ),
				array(
					'dispute_id'  => $dispute_id,
					'kind'        => $kind,
					'gateway'     => 'paypal',
					'source'      => 'webhook',
				)
			);
		}

		Giftflow_Logger::info(
			'paypal.webhook.customer.dispute',
			array(
				'dispute_id'  => $dispute_id,
				'donation_id' => $donation,
				'kind'        => $kind,
				'gateway'     => 'paypal',
			),
			'paypal'
		);

		$this->emit_observation( $kind, $donation, $environment, $dispute_id, $seller_txn, true );

		do_action( 'giftflow_paypal_webhook_customer_dispute', $donation, $resource, $kind );

		return true;
	}

	/**
	 * Map a PayPal dispute resource to an observation kind.
	 *
	 * @param array $resource Dispute resource.
	 * @return string
	 */
	private function dispute_kind( array $resource ): string {
		$outcome = isset( $resource['dispute_outcome']['outcome_code'] ) ? strtoupper( (string) $resource['dispute_outcome']['outcome_code'] ) : '';

		if ( 0 === strpos( $outcome, 'RESOLVED_SELLER_FAVOUR' ) ) {
			return 'dispute_won';
		}

		if ( 0 === strpos( $outcome, 'RESOLVED_BUYER_FAVOUR' ) ) {
			return 'dispute_lost';
		}

		return 'dispute_open';
	}

	/**
	 * Handle capture/sale reversal events.
	 *
	 * @param array  $resource    Reversal resource.
	 * @param string $environment Verified environment.
	 * @return bool
	 */
	private function payment_reversed( array $resource, string $environment ): bool {
		$id       = $this->transaction_id( $resource, true );
		$donation = $this->find_donation_by_transaction_id( $id, $environment );

		if ( ! $donation ) {
			$this->observe_unbound( 'reversed', $resource, $environment, $id );

			return false;
		}

		Donation_Event_History::add(
			$donation,
			'payment_reversed',
			'',
			__( 'Webhook: payment reversed', 'giftflow' ),
			array(
				'transaction_id' => $id,
				'gateway'        => 'paypal',
				'source'         => 'webhook',
			)
		);

		Giftflow_Logger::info(
			'paypal.webhook.payment.reversed',
			array(
				'donation_id'    => $donation,
				'transaction_id' => $id,
				'gateway'        => 'paypal',
			),
			'paypal'
		);

		$this->emit_observation( 'reversed', (int) $donation, $environment, $id, $id, true );

		do_action( 'giftflow_paypal_webhook_payment_reversed', $donation, $resource );

		return true;
	}

	/**
	 * Classify a refund as full or partial.
	 *
	 * @param array $resource    Refund resource.
	 * @param int   $donation_id Donation id.
	 * @return string
	 */
	private function refund_kind( array $resource, int $donation_id ): string {
		$refund_value    = isset( $resource['amount']['value'] ) ? (string) $resource['amount']['value'] : '';
		$refund_currency = isset( $resource['amount']['currency_code'] ) ? strtoupper( (string) $resource['amount']['currency_code'] ) : '';

		$refund_minor = class_exists( '\GiftFlow\Services\CheckoutMoney' )
			? \GiftFlow\Services\CheckoutMoney::parse( $refund_value, $refund_currency )
			: new \WP_Error( 'unsupported', 'unsupported' );

		if ( is_wp_error( $refund_minor ) ) {
			return 'refund_partial';
		}

		$charged_minor = 0;

		if ( class_exists( '\GiftFlow\Services\CheckoutExtension' ) ) {
			$attempt_amount = get_post_meta( $donation_id, \GiftFlow\Services\CheckoutExtension::META_AMOUNT, true );

			if ( '' !== (string) $attempt_amount ) {
				$charged_minor = (int) $attempt_amount;
			}
		}

		if ( 0 === $charged_minor ) {
			$stored_amount = (float) get_post_meta( $donation_id, '_amount', true );
			$charged_minor = (int) round( $stored_amount * 100 );
		}

		if ( $charged_minor > 0 && (int) $refund_minor >= $charged_minor ) {
			return 'refund_full';
		}

		return 'refund_partial';
	}

	/**
	 * Emit a normalized observation for a bound donation.
	 *
	 * @param string $kind           Observation kind.
	 * @param int    $donation_id    Donation id (0 when unresolved).
	 * @param string $environment    Verified environment.
	 * @param string $subject        Stable provider resource id.
	 * @param string $transaction_id Provider transaction id.
	 * @param bool   $is_initial     Whether this is the initial charge.
	 * @return void
	 */
	private function emit_observation( string $kind, int $donation_id, string $environment, string $subject, string $transaction_id, bool $is_initial ): void {
		if ( ! class_exists( '\GiftFlow\Services\PaymentObservation' ) ) {
			return;
		}

		$attempt  = '';
		$amount   = '';
		$currency = '';

		if ( $donation_id > 0 ) {
			$attempt  = (string) get_post_meta( $donation_id, \GiftFlow\Services\CheckoutExtension::META_ATTEMPT, true );
			$amount   = (string) get_post_meta( $donation_id, \GiftFlow\Services\CheckoutExtension::META_AMOUNT, true );
			$currency = (string) get_post_meta( $donation_id, \GiftFlow\Services\CheckoutExtension::META_CURRENCY, true );
		}

		\GiftFlow\Services\PaymentObservation::emit(
			array(
				'event_key'      => 'paypal:' . $environment . ':' . $subject . ':' . $kind,
				'gateway'        => 'paypal',
				'environment'    => $environment,
				'kind'           => $kind,
				'donation_id'    => $donation_id,
				'attempt_key'    => '' === $attempt ? null : $attempt,
				'provider_id'    => $subject,
				'transaction_id' => $transaction_id,
				'amount_minor'   => '' === $amount ? null : (int) $amount,
				'currency'       => '' === $currency ? null : $currency,
				'is_initial'     => $is_initial,
				'source'         => 'webhook',
			)
		);
	}

	/**
	 * Emit an observation for a payment that could not be bound to a donation.
	 *
	 * @param string $kind           Observation kind.
	 * @param array  $resource       Provider resource.
	 * @param string $environment    Verified environment.
	 * @param string $subject        Stable provider resource id.
	 * @return void
	 */
	private function observe_unbound( string $kind, array $resource, string $environment, string $subject ): void {
		if ( ! class_exists( '\GiftFlow\Services\PaymentObservation' ) ) {
			return;
		}

		$attempt = isset( $resource['custom_id'] ) && is_string( $resource['custom_id'] ) && preg_match( '/^[A-Za-z0-9_.:\-]{8,191}$/', $resource['custom_id'] )
			? $resource['custom_id']
			: null;

		$amount   = isset( $resource['amount']['value'] ) ? (string) $resource['amount']['value'] : '';
		$currency = isset( $resource['amount']['currency_code'] ) ? strtoupper( (string) $resource['amount']['currency_code'] ) : '';

		$amount_minor = null;

		if ( '' !== $amount && class_exists( '\GiftFlow\Services\CheckoutMoney' ) ) {
			$parsed = \GiftFlow\Services\CheckoutMoney::parse( $amount, $currency );

			if ( ! is_wp_error( $parsed ) ) {
				$amount_minor = (int) $parsed;
			}
		}

		\GiftFlow\Services\PaymentObservation::emit(
			array(
				'event_key'      => 'paypal:' . $environment . ':' . $subject . ':' . $kind,
				'gateway'        => 'paypal',
				'environment'    => $environment,
				'kind'           => $kind,
				'donation_id'    => 0,
				'attempt_key'    => $attempt,
				'provider_id'    => $subject,
				'transaction_id' => $subject,
				'amount_minor'   => $amount_minor,
				'currency'       => '' === $currency ? null : $currency,
				'is_initial'     => true,
				'source'         => 'webhook',
			)
		);
	}

	/**
	 * Refresh the parent donation's next payment date from the resolved environment.
	 *
	 * @param int    $parent       Parent donation post id.
	 * @param string $subscription Subscription id.
	 * @param string $environment  Verified environment id.
	 * @return void
	 */
	private function refresh_next_payment_date( int $parent, string $subscription, string $environment ): void {
		$gateway = function_exists( 'giftflow_get_paypal_gateway' ) ? giftflow_get_paypal_gateway() : null;

		if ( ! $gateway instanceof \GiftFlow\Gateways\PayPal_Gateway ) {
			return;
		}

		$client = $gateway->client_for_environment( $environment );

		if ( ! $client ) {
			return;
		}

		$response = $client->get_subscription( $subscription );

		if ( ! $response['ok'] || ! isset( $response['body']['billing_info']['next_billing_time'] ) ) {
			return;
		}

		update_post_meta(
			$parent,
			'_recurring_next_payment_date',
			sanitize_text_field( $response['body']['billing_info']['next_billing_time'] )
		);
	}

	/**
	 * Handle a denied subscription payment.
	 *
	 * @param array  $resource    Verified sale resource.
	 * @param string $environment Verified environment id.
	 * @return bool Whether the event mutated a donation.
	 */
	private function subscription_payment_denied( array $resource, string $environment ): bool {
		$subscription = isset( $resource['billing_agreement_id'] ) ? (string) $resource['billing_agreement_id'] : '';
		$sale         = isset( $resource['id'] ) ? (string) $resource['id'] : '';
		$parent       = $this->find_donation_by_subscription_id( $subscription, $environment );

		if ( ! $parent ) {
			return false;
		}

		update_post_meta( $parent, '_recurring_status', 'suspended' );

		Donation_Event_History::add(
			$parent,
			'recurring_payment_failed',
			'failed',
			__( 'Webhook: PAYMENT.SALE.DENIED (subscription payment failed)', 'giftflow' ),
			array(
				'sale_id'         => $sale,
				'subscription_id' => $subscription,
				'gateway'         => 'paypal',
				'source'          => 'webhook',
			)
		);

		Giftflow_Logger::error(
			'paypal.webhook.sale.denied.subscription',
			array(
				'parent_donation_id' => $parent,
				'sale_id'            => $sale,
				'subscription_id'    => $subscription,
				'gateway'            => 'paypal',
			),
			'paypal'
		);

		do_action( 'giftflow_paypal_recurring_payment_denied', $parent, $subscription, $resource );

		return true;
	}
}
