<?php
/**
 * Webhook event dispatcher with explicit environment context.
 *
 * Every handler receives the verified environment and only ever touches
 * donations bound to that environment. The dispatcher never chooses a signing
 * secret, an environment or a client from request data.
 *
 * @package GiftFlow
 * @subpackage Stripe
 */

namespace GiftFlow\Stripe;

use GiftFlow\Core\InstallationIdentity;
use GiftFlow\Core\Donations;
use GiftFlow\Core\Logger as Giftflow_Logger;
use GiftFlow\Core\Donation_Event_History;
use GiftFlow\Vendor\Stripe\StripeClient;
use GiftFlow\Vendor\Stripe\Exception\ApiErrorException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Routes verified Stripe events to environment-filtered handlers.
 */
final class StripeWebhookDispatcher {

	/**
	 * Event types this dispatcher can process.
	 *
	 * @var string[]
	 */
	public const SUPPORTED_EVENTS = array(
		'payment_intent.succeeded',
		'payment_intent.payment_failed',
		'payment_intent.canceled',
		'charge.refunded',
		'invoice.paid',
		'invoice.payment_failed',
		'invoice_payment.paid',
		'customer.subscription.deleted',
		'customer.subscription.updated',
		'charge.dispute.created',
		'charge.dispute.updated',
		'charge.dispute.closed',
	);

	/**
	 * Repository.
	 *
	 * @var StripeConnectionRepository
	 */
	private StripeConnectionRepository $repository;

	/**
	 * Installation identity.
	 *
	 * @var InstallationIdentity
	 */
	private InstallationIdentity $identity;

	/**
	 * Donation binding helper.
	 *
	 * @var StripeDonationBinding
	 */
	private StripeDonationBinding $binding;

	/**
	 * Per-environment client cache.
	 *
	 * @var array<string, StripeClient|null>
	 */
	private array $clients = array();

	/**
	 * Constructor.
	 *
	 * @param StripeConnectionRepository $repository Repository.
	 * @param InstallationIdentity|null   $identity   Installation identity.
	 */
	public function __construct( StripeConnectionRepository $repository, ?InstallationIdentity $identity = null ) {
		$this->repository = $repository;
		$this->identity   = $identity ?? $repository->identity();
		$this->binding    = new StripeDonationBinding( $this->repository, $this->identity );
	}

	/**
	 * Whether an event type has a handler.
	 *
	 * @param string $event_type Stripe event type.
	 * @return bool
	 */
	public static function is_supported( string $event_type ): bool {
		return in_array( $event_type, self::SUPPORTED_EVENTS, true );
	}

	/**
	 * Dispatch a verified event.
	 *
	 * @param string $event_type  Stripe event type.
	 * @param object $event_object      Event data object.
	 * @param string $environment Verified environment.
	 * @return bool Whether a handler ran.
	 */
	public function dispatch( string $event_type, $event_object, string $environment ): bool {
		$environment = StripeEnvironment::assert_valid( $environment );

		switch ( $event_type ) {
			case 'payment_intent.succeeded':
				$this->handle_payment_intent_succeeded( $event_object, $environment );
				break;

			case 'payment_intent.payment_failed':
				$this->handle_payment_intent_failed( $event_object, $environment );
				break;

			case 'payment_intent.canceled':
				$this->handle_payment_intent_canceled( $event_object, $environment );
				break;

			case 'charge.refunded':
				$this->handle_payment_charge_refunded( $event_object, $environment );
				break;

			case 'invoice.paid':
				$this->handle_invoice_paid( $event_object, $environment );
				break;

			case 'invoice_payment.paid':
				$this->handle_invoice_payment_paid( $event_object, $environment );
				break;

			case 'invoice.payment_failed':
				$this->handle_invoice_payment_failed( $event_object, $environment );
				break;

			case 'customer.subscription.deleted':
				$this->handle_subscription_deleted( $event_object, $environment );
				break;

			case 'customer.subscription.updated':
				$this->handle_subscription_updated( $event_object, $environment );
				break;

			case 'charge.dispute.created':
			case 'charge.dispute.updated':
			case 'charge.dispute.closed':
				$this->handle_charge_dispute( $event_object, $environment, $event_type );
				break;

			default:
				return false;
		}

		return true;
	}

	/**
	 * Donation ids an event would mutate, using unfiltered lookups.
	 *
	 * @param string $event_type  Stripe event type.
	 * @param object $event_object      Event data object.
	 * @param string $environment Verified environment.
	 * @return int[]
	 */
	public function resolve_target_donation_ids( string $event_type, $event_object, string $environment ): array {
		switch ( $event_type ) {
			case 'payment_intent.succeeded':
			case 'payment_intent.payment_failed':
			case 'payment_intent.canceled':
			case 'charge.refunded':
				$donation_id = $this->metadata_donation_id( $event_object );

				return $donation_id > 0 ? array( $donation_id ) : array();

			case 'invoice.paid':
				return $this->subscription_donation_ids( $this->subscription_id_from_invoice( $event_object ) );

			case 'invoice.payment_failed':
				return $this->subscription_donation_ids( $this->subscription_id_from_invoice( $event_object ) );

			case 'invoice_payment.paid':
				$invoice = $this->normalize_invoice_payment( $event_object, $environment );

				return null === $invoice ? array() : $this->subscription_donation_ids( $this->subscription_id_from_invoice( $invoice ) );

			case 'customer.subscription.deleted':
			case 'customer.subscription.updated':
				$subscription_id = isset( $event_object->id ) ? (string) $event_object->id : '';

				return $this->subscription_donation_ids( $subscription_id );

			case 'charge.dispute.created':
			case 'charge.dispute.updated':
			case 'charge.dispute.closed':
				$donation_id = $this->donation_id_for_dispute( $event_object, $environment );

				return $donation_id > 0 ? array( $donation_id ) : array();

			default:
				return array();
		}
	}

	/**
	 * Bind a donation to an environment, adopting unbound legacy records once.
	 *
	 * @param int    $donation_id Donation post id.
	 * @param string $environment Verified environment.
	 * @param string $account_id  Account id from the event, may be empty.
	 * @return bool Whether the donation is bound to the environment.
	 */
	public function bind_donation_environment( int $donation_id, string $environment, string $account_id = '' ): bool {
		return $this->binding->bind( $donation_id, $environment, $account_id );
	}

	/**
	 * Handle `payment_intent.succeeded`.
	 *
	 * @param object $payment_intent Payment intent object.
	 * @param string $environment    Verified environment.
	 * @return void
	 */
	public function handle_payment_intent_succeeded( $payment_intent, string $environment ): void {
		$donation_id = $this->find_donation_id_by_metadata( $payment_intent, $environment );

		if ( ! $donation_id ) {
			return;
		}

		$transaction_id = isset( $payment_intent->id ) ? (string) $payment_intent->id : '';
		$charge_id      = ! empty( $payment_intent->charges->data ) ? (string) $payment_intent->charges->data[0]->id : '';

		update_post_meta( $donation_id, '_transaction_id', $transaction_id );
		update_post_meta( $donation_id, '_stripe_charge_id', $charge_id );
		update_post_meta( $donation_id, '_payment_status', 'completed' );

		$donations_class = new Donations();
		$donations_class->update_status( $donation_id, 'completed' );

		Donation_Event_History::add(
			$donation_id,
			'payment_succeeded',
			'completed',
			__( 'Webhook: payment_intent.succeeded', 'giftflow' ),
			array(
				'transaction_id' => $transaction_id,
				'charge_id'      => $charge_id,
				'gateway'        => 'stripe',
				'environment'    => $environment,
				'source'         => 'webhook',
			)
		);
		Giftflow_Logger::info(
			'stripe.webhook.payment_intent.succeeded',
			array(
				'donation_id'    => $donation_id,
				'transaction_id' => $transaction_id,
				'charge_id'      => $charge_id,
				'environment'    => $environment,
				'gateway'        => 'stripe',
			),
			'stripe'
		);

		$this->emit_observation( 'paid', $donation_id, $environment, $transaction_id, '' !== $charge_id ? $charge_id : $transaction_id, true );

		do_action( 'giftflow_stripe_webhook_payment_completed', $donation_id, $payment_intent, $environment );
	}

	/**
	 * Handle `payment_intent.payment_failed`.
	 *
	 * @param object $payment_intent Payment intent object.
	 * @param string $environment    Verified environment.
	 * @return void
	 */
	public function handle_payment_intent_failed( $payment_intent, string $environment ): void {
		$donation_id = $this->find_donation_id_by_metadata( $payment_intent, $environment );

		if ( ! $donation_id ) {
			return;
		}

		$error_message = isset( $payment_intent->last_payment_error->message )
			? (string) $payment_intent->last_payment_error->message
			: __( 'Payment failed', 'giftflow' );

		Donation_Event_History::add(
			$donation_id,
			'payment_failed',
			'failed',
			$error_message,
			array(
				'gateway'     => 'stripe',
				'environment' => $environment,
				'source'      => 'webhook',
			)
		);
		Giftflow_Logger::error(
			'stripe.webhook.payment_intent.failed',
			array(
				'donation_id'   => $donation_id,
				'error_message' => $error_message,
				'environment'   => $environment,
				'gateway'       => 'stripe',
			),
			'stripe'
		);

		$donations_class = new Donations();
		$donations_class->update_status( $donation_id, 'failed' );
		update_post_meta( $donation_id, '_payment_error', $error_message );
		update_post_meta( $donation_id, '_payment_status', 'failed' );

		$pi_id = isset( $payment_intent->id ) ? (string) $payment_intent->id : '';

		$this->emit_observation( 'failed', $donation_id, $environment, $pi_id, $pi_id, true );

		do_action( 'giftflow_stripe_webhook_payment_failed', $donation_id, $payment_intent, $environment );
	}

	/**
	 * Handle `payment_intent.canceled`.
	 *
	 * @param object $payment_intent Payment intent object.
	 * @param string $environment    Verified environment.
	 * @return void
	 */
	public function handle_payment_intent_canceled( $payment_intent, string $environment ): void {
		$donation_id = $this->find_donation_id_by_metadata( $payment_intent, $environment );

		if ( ! $donation_id ) {
			return;
		}

		Donation_Event_History::add(
			$donation_id,
			'payment_canceled',
			'cancelled',
			__( 'Webhook: payment_intent.canceled', 'giftflow' ),
			array(
				'gateway'     => 'stripe',
				'environment' => $environment,
				'source'      => 'webhook',
			)
		);
		Giftflow_Logger::info(
			'stripe.webhook.payment_intent.canceled',
			array(
				'donation_id' => $donation_id,
				'environment' => $environment,
				'gateway'     => 'stripe',
			),
			'stripe'
		);

		$donations_class = new Donations();
		$donations_class->update_status( $donation_id, 'cancelled' );
		update_post_meta( $donation_id, '_payment_status', 'canceled' );

		$pi_id = isset( $payment_intent->id ) ? (string) $payment_intent->id : '';

		$this->emit_observation( 'cancelled', $donation_id, $environment, $pi_id, $pi_id, true );

		do_action( 'giftflow_stripe_webhook_payment_canceled', $donation_id, $payment_intent, $environment );
	}

	/**
	 * Handle `charge.refunded`.
	 *
	 * @param object $charge      Charge object.
	 * @param string $environment Verified environment.
	 * @return void
	 */
	public function handle_payment_charge_refunded( $charge, string $environment ): void {
		$donation_id = $this->find_donation_id_by_metadata( $charge, $environment );

		if ( ! $donation_id ) {
			return;
		}

		$charge_id = isset( $charge->id ) ? (string) $charge->id : '';

		Donation_Event_History::add(
			$donation_id,
			'payment_refunded',
			'refunded',
			__( 'Webhook: charge.refunded', 'giftflow' ),
			array(
				'charge_id'   => $charge_id,
				'gateway'     => 'stripe',
				'environment' => $environment,
				'source'      => 'webhook',
			)
		);
		Giftflow_Logger::info(
			'stripe.webhook.charge.refunded',
			array(
				'donation_id' => $donation_id,
				'charge_id'   => $charge_id,
				'environment' => $environment,
				'gateway'     => 'stripe',
			),
			'stripe'
		);

		$donations_class = new Donations();
		$donations_class->update_status( $donation_id, 'refunded' );
		update_post_meta( $donation_id, '_payment_status', 'refunded' );

		$charged   = isset( $charge->amount ) ? (int) $charge->amount : 0;
		$refunded  = isset( $charge->amount_refunded ) ? (int) $charge->amount_refunded : 0;
		$refund_kind = ( $charged > 0 && $refunded >= $charged ) ? 'refund_full' : 'refund_partial';

		$this->emit_observation( $refund_kind, $donation_id, $environment, $charge_id, $charge_id, true );

		do_action( 'giftflow_stripe_webhook_charge_refunded', $donation_id, $charge, $environment );
	}

	/**
	 * Handle `invoice_payment.paid` by normalizing it into an invoice object.
	 *
	 * @param object $invoice_payment Stripe invoice_payment object.
	 * @param string $environment     Verified environment.
	 * @return void
	 */
	public function handle_invoice_payment_paid( $invoice_payment, string $environment ): void {
		$invoice = $this->normalize_invoice_payment( $invoice_payment, $environment );

		if ( null === $invoice ) {
			return;
		}

		$this->handle_invoice_paid( $invoice, $environment );
	}

	/**
	 * Handle `invoice.paid`.
	 *
	 * @param object $invoice     Stripe invoice object.
	 * @param string $environment Verified environment.
	 * @return void
	 */
	public function handle_invoice_paid( $invoice, string $environment ): void {
		$subscription_id = $this->subscription_id_from_invoice( $invoice );

		if ( '' === $subscription_id ) {
			return;
		}

		$parent_donation_id = $this->find_subscription_parent_id( $subscription_id, $environment );

		if ( ! $parent_donation_id ) {
			Giftflow_Logger::error(
				'stripe.webhook.invoice.paid.parent_missing',
				array(
					'subscription_id' => $subscription_id,
					'environment'     => $environment,
					'gateway'         => 'stripe',
				),
				'stripe'
			);

			return;
		}

		$target           = $this->resolve_invoice_target( $invoice, $parent_donation_id, $environment );
		$donation_id      = $target['donation_id'];
		$invoice_id       = isset( $invoice->id ) ? (string) $invoice->id : '';
		$payment_intent   = $this->payment_intent_id_from_invoice( $invoice, $environment );
		$is_first_invoice = 'first' === $target['state'];

		if ( 0 === $donation_id ) {
			$this->log_invoice_error( 'invoice.paid: failed to create renewal donation for ' . $subscription_id, $parent_donation_id, $environment );

			return;
		}

		if ( 'first' === $target['state'] ) {
			update_post_meta( $parent_donation_id, '_transaction_id', $payment_intent );
			update_post_meta( $parent_donation_id, '_stripe_payment_intent_id', $payment_intent );
			update_post_meta( $parent_donation_id, '_stripe_invoice_id', $invoice_id );
			update_post_meta( $parent_donation_id, '_transaction_raw_data', wp_json_encode( (array) $invoice ) );

			$donations_class = new Donations();
			$donations_class->update_status( $parent_donation_id, 'completed' );

			Donation_Event_History::add(
				$parent_donation_id,
				'recurring_payment_first',
				'completed',
				__( 'Webhook: invoice.paid (first charge)', 'giftflow' ),
				array(
					'invoice_id'      => $invoice_id,
					'subscription_id' => $subscription_id,
					'environment'     => $environment,
					'gateway'         => 'stripe',
					'source'          => 'webhook',
				)
			);
		} elseif ( 'renewal' === $target['state'] ) {
			Donation_Event_History::add(
				$donation_id,
				'recurring_payment_renewal',
				'completed',
				__( 'Webhook: invoice.paid (renewal)', 'giftflow' ),
				array(
					'invoice_id'         => $invoice_id,
					'subscription_id'    => $subscription_id,
					'parent_donation_id' => $parent_donation_id,
					'environment'        => $environment,
					'gateway'            => 'stripe',
					'source'             => 'webhook',
				)
			);

			do_action( 'giftflow_stripe_recurring_renewal_created', $donation_id, $parent_donation_id, $subscription_id, (array) $invoice, $environment );
		}

		$invoice_is_initial = 'first' === $target['state'];
		$invoice_target_id  = 'first' === $target['state'] ? $parent_donation_id : $donation_id;

		$this->emit_observation( 'paid', (int) $invoice_target_id, $environment, $invoice_id, $payment_intent, $invoice_is_initial );

		$this->update_parent_after_renewal( $parent_donation_id, $invoice );

		Giftflow_Logger::info(
			'stripe.webhook.invoice.paid',
			array(
				'parent_donation_id' => $parent_donation_id,
				'donation_id'        => $donation_id,
				'invoice_id'         => $invoice_id,
				'subscription_id'    => $subscription_id,
				'is_first'           => $is_first_invoice,
				'environment'        => $environment,
				'gateway'            => 'stripe',
			),
			'stripe'
		);
	}

	/**
	 * Handle `invoice.payment_failed`.
	 *
	 * @param object $invoice     Stripe invoice object.
	 * @param string $environment Verified environment.
	 * @return void
	 */
	public function handle_invoice_payment_failed( $invoice, string $environment ): void {
		$subscription_id    = $this->subscription_id_from_invoice( $invoice );
		$parent_donation_id = $this->find_subscription_parent_id( $subscription_id, $environment );

		if ( ! $parent_donation_id ) {
			return;
		}

		update_post_meta( $parent_donation_id, '_recurring_status', 'past_due' );
		Donation_Event_History::add(
			$parent_donation_id,
			'recurring_payment_failed',
			'failed',
			__( 'Webhook: invoice.payment_failed', 'giftflow' ),
			array(
				'invoice_id'      => isset( $invoice->id ) ? (string) $invoice->id : '',
				'subscription_id' => $subscription_id,
				'environment'     => $environment,
				'gateway'         => 'stripe',
				'source'          => 'webhook',
			)
		);
		Giftflow_Logger::error(
			'stripe.webhook.invoice.payment_failed',
			array(
				'parent_donation_id' => $parent_donation_id,
				'subscription_id'    => $subscription_id,
				'environment'        => $environment,
				'gateway'            => 'stripe',
			),
			'stripe'
		);

		do_action( 'giftflow_stripe_recurring_payment_failed', $parent_donation_id, $subscription_id, (array) $invoice, $environment );
	}

	/**
	 * Handle `customer.subscription.deleted`.
	 *
	 * @param object $subscription Stripe subscription object.
	 * @param string $environment  Verified environment.
	 * @return void
	 */
	public function handle_subscription_deleted( $subscription, string $environment ): void {
		$subscription_id    = isset( $subscription->id ) ? (string) $subscription->id : '';
		$parent_donation_id = $this->find_subscription_parent_id( $subscription_id, $environment );

		if ( ! $parent_donation_id ) {
			return;
		}

		update_post_meta( $parent_donation_id, '_recurring_status', 'cancelled' );
		Donation_Event_History::add(
			$parent_donation_id,
			'recurring_subscription_cancelled',
			'cancelled',
			__( 'Webhook: customer.subscription.deleted', 'giftflow' ),
			array(
				'subscription_id' => $subscription_id,
				'environment'     => $environment,
				'gateway'         => 'stripe',
				'source'          => 'webhook',
			)
		);

		do_action( 'giftflow_stripe_subscription_cancelled', $parent_donation_id, $subscription_id, (array) $subscription, $environment );
	}

	/**
	 * Handle `customer.subscription.updated`.
	 *
	 * @param object $subscription Stripe subscription object.
	 * @param string $environment  Verified environment.
	 * @return void
	 */
	public function handle_subscription_updated( $subscription, string $environment ): void {
		$subscription_id    = isset( $subscription->id ) ? (string) $subscription->id : '';
		$parent_donation_id = $this->find_subscription_parent_id( $subscription_id, $environment );

		if ( ! $parent_donation_id ) {
			return;
		}

		$new_status = isset( $subscription->status ) ? (string) $subscription->status : '';
		$status_map = array(
			'active'   => 'active',
			'past_due' => 'past_due',
			'unpaid'   => 'unpaid',
			'canceled' => 'cancelled',
			'trialing' => 'active',
		);

		if ( isset( $status_map[ $new_status ] ) ) {
			update_post_meta( $parent_donation_id, '_recurring_status', $status_map[ $new_status ] );
		}

		if ( isset( $subscription->current_period_end ) ) {
			update_post_meta( $parent_donation_id, '_recurring_next_payment_date', gmdate( 'Y-m-d\TH:i:s\Z', (int) $subscription->current_period_end ) );
		}

		do_action( 'giftflow_stripe_subscription_updated', $parent_donation_id, $subscription_id, (array) $subscription, $environment );
	}

	/**
	 * Update parent donation recurring state after a renewal.
	 *
	 * @param int    $parent_donation_id Parent donation post id.
	 * @param object $invoice            Stripe invoice object.
	 * @return void
	 */
	/**
	 * Handle `charge.dispute.*` events.
	 *
	 * Disputes never change the Core donation status: the donation was paid and
	 * may still be won. The normalized observation lets extension owners (e.g.
	 * Rewards) put the claim into review. Unknown provider states stay "open"
	 * and are never interpreted as a win.
	 *
	 * @param object $dispute      Dispute object.
	 * @param string $environment  Verified environment.
	 * @param string $event_type   Stripe event type.
	 * @return void
	 */
	public function handle_charge_dispute( $dispute, string $environment, string $event_type = '' ): void {
		$dispute_id = isset( $dispute->id ) ? (string) $dispute->id : '';

		if ( '' === $dispute_id ) {
			return;
		}

		$donation_id = $this->donation_id_for_dispute( $dispute, $environment );
		$kind        = $this->dispute_kind( $dispute );
		$charge_id   = isset( $dispute->charge ) && is_string( $dispute->charge ) ? (string) $dispute->charge : $dispute_id;
		$status      = isset( $dispute->status ) ? (string) $dispute->status : '';

		if ( $donation_id > 0 ) {
			Donation_Event_History::add(
				$donation_id,
				'payment_dispute',
				'',
				__( 'Webhook: charge dispute', 'giftflow' ),
				array(
					'dispute_id'  => $dispute_id,
					'status'      => $status,
					'kind'        => $kind,
					'event_type'  => $event_type,
					'gateway'     => 'stripe',
					'environment' => $environment,
					'source'      => 'webhook',
				)
			);
		}

		Giftflow_Logger::info(
			'stripe.webhook.charge.dispute',
			array(
				'dispute_id'  => $dispute_id,
				'donation_id' => $donation_id,
				'status'      => $status,
				'kind'        => $kind,
				'environment' => $environment,
				'gateway'     => 'stripe',
			),
			'stripe'
		);

		$this->emit_observation( $kind, $donation_id, $environment, $dispute_id, $charge_id, true );

		do_action( 'giftflow_stripe_webhook_charge_dispute', $donation_id, $dispute, $environment, $kind );
	}

	/**
	 * Map a dispute status to an observation kind.
	 *
	 * @param object $dispute Dispute object.
	 * @return string
	 */
	private function dispute_kind( $dispute ): string {
		$status = isset( $dispute->status ) ? strtolower( (string) $dispute->status ) : '';

		if ( 'won' === $status ) {
			return 'dispute_won';
		}

		if ( 'lost' === $status ) {
			return 'dispute_lost';
		}

		return 'dispute_open';
	}

	/**
	 * Resolve the donation a dispute belongs to.
	 *
	 * Uses payment intent metadata first, then stored payment intent/charge ids.
	 * When the payload only carries a charge id, the authenticated charge is
	 * fetched outside any database transaction to recover the donation binding.
	 *
	 * @param object $dispute     Dispute object.
	 * @param string $environment Verified environment.
	 * @return int Donation id, or 0 when it cannot be resolved.
	 */
	private function donation_id_for_dispute( $dispute, string $environment ): int {
		$payment_intent = isset( $dispute->payment_intent ) ? $dispute->payment_intent : '';

		if ( is_object( $payment_intent ) ) {
			$found = $this->find_donation_id_by_metadata( $payment_intent, $environment );

			if ( $found > 0 ) {
				return $found;
			}

			$payment_intent = isset( $payment_intent->id ) ? (string) $payment_intent->id : '';
		}

		if ( is_string( $payment_intent ) && '' !== $payment_intent ) {
			foreach ( array( '_stripe_payment_intent_id', '_transaction_id' ) as $meta_key ) {
				$posts = $this->find_donations(
					$environment,
					array(
						array(
							'key'   => $meta_key,
							'value' => $payment_intent,
						),
					),
					array( 'fields' => 'ids' )
				);

				if ( ! empty( $posts ) ) {
					return (int) $posts[0];
				}
			}
		}

		$charge = isset( $dispute->charge ) ? $dispute->charge : '';

		if ( is_object( $charge ) ) {
			$found = $this->find_donation_id_by_metadata( $charge, $environment );

			if ( $found > 0 ) {
				return $found;
			}

			$charge = isset( $charge->id ) ? (string) $charge->id : '';
		}

		if ( ! is_string( $charge ) || '' === $charge ) {
			return 0;
		}

		$posts = $this->find_donations(
			$environment,
			array(
				array(
					'key'   => '_stripe_charge_id',
					'value' => $charge,
				),
			),
			array( 'fields' => 'ids' )
		);

		if ( ! empty( $posts ) ) {
			return (int) $posts[0];
		}

		$client = $this->client_for_environment( $environment );

		if ( null === $client ) {
			return 0;
		}

		try {
			$detail = $client->charges->retrieve( $charge );
		} catch ( ApiErrorException $e ) {
			Giftflow_Logger::error(
				'stripe.webhook.dispute.charge_lookup_failed',
				array(
					'charge_id'   => $charge,
					'environment' => $environment,
					'gateway'     => 'stripe',
				),
				'stripe'
			);

			return 0;
		}

		return $this->find_donation_id_by_metadata( $detail, $environment );
	}

	/**
	 * Emit a normalized payment observation for extension consumers.
	 *
	 * @param string $kind           Observation kind.
	 * @param int    $donation_id    Donation id (0 when unresolved).
	 * @param string $environment    Verified environment.
	 * @param string $subject        Stable provider resource id.
	 * @param string $transaction_id Provider transaction reference.
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
				'event_key'      => 'stripe:' . $environment . ':' . $subject . ':' . $kind,
				'gateway'        => 'stripe',
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
	 * Update the parent donation after a renewal invoice.
	 *
	 * @param int   $parent_donation_id Parent donation post id.
	 * @param mixed $invoice            Stripe invoice object.
	 * @return void
	 */
	private function update_parent_after_renewal( int $parent_donation_id, $invoice ): void {
		update_post_meta( $parent_donation_id, '_recurring_status', 'active' );

		if ( ! empty( $invoice->lines->data ) ) {
			foreach ( $invoice->lines->data as $line ) {
				if ( isset( $line->period->end ) ) {
					update_post_meta( $parent_donation_id, '_recurring_next_payment_date', gmdate( 'Y-m-d\TH:i:s\Z', (int) $line->period->end ) );
					break;
				}
			}
		}
	}

	/**
	 * Resolve the donation an invoice belongs to, creating renewals when needed.
	 *
	 * @param object $invoice            Stripe invoice object.
	 * @param int    $parent_donation_id Parent donation post id.
	 * @param string $environment        Verified environment.
	 * @return array{donation_id:int,state:string} State is existing|first|renewal|failed.
	 */
	private function resolve_invoice_target( $invoice, int $parent_donation_id, string $environment ): array {
		$invoice_id = isset( $invoice->id ) ? (string) $invoice->id : '';
		$existing   = $this->find_donations(
			$environment,
			array(
				array(
					'key'   => '_stripe_invoice_id',
					'value' => $invoice_id,
				),
			),
			array( 'fields' => 'ids' )
		);

		if ( ! empty( $existing ) ) {
			return array(
				'donation_id' => (int) $existing[0],
				'state'       => 'existing',
			);
		}

		$is_first_invoice = isset( $invoice->billing_reason ) && 'subscription_create' === $invoice->billing_reason;

		if ( $is_first_invoice ) {
			return array(
				'donation_id' => $parent_donation_id,
				'state'       => 'first',
			);
		}

		$meta            = get_post_meta( $parent_donation_id );
		$subscription_id = $this->subscription_id_from_invoice( $invoice );
		$payment_intent  = $this->payment_intent_id_from_invoice( $invoice, $environment );

		$renewal_id = wp_insert_post(
			array(
				'post_title'  => sprintf(
					/* translators: %s: parent donation ID */
					__( 'Recurring Donation (renewal of #%s)', 'giftflow' ),
					$parent_donation_id
				),
				'post_type'   => 'donation',
				'post_status' => 'publish',
			)
		);

		if ( is_wp_error( $renewal_id ) ) {
			return array(
				'donation_id' => 0,
				'state'       => 'failed',
			);
		}

		$copy_keys = array(
			'_amount',
			'_campaign_id',
			'_donor_id',
			'_payment_method',
			'_donation_type',
			'_recurring_interval',
			StripeDonationBinding::META_ACCOUNT,
			StripeDonationBinding::META_BINDING,
		);

		foreach ( $copy_keys as $key ) {
			if ( isset( $meta[ $key ][0] ) ) {
				update_post_meta( $renewal_id, $key, $meta[ $key ][0] );
			}
		}

		update_post_meta( $renewal_id, StripeDonationBinding::META_ENVIRONMENT, $environment );
		update_post_meta( $renewal_id, '_status', 'completed' );
		update_post_meta( $renewal_id, '_parent_donation_id', $parent_donation_id );
		update_post_meta( $renewal_id, '_stripe_invoice_id', $invoice_id );
		update_post_meta( $renewal_id, '_stripe_subscription_id', $subscription_id );
		update_post_meta( $renewal_id, '_stripe_customer_id', isset( $meta['_stripe_customer_id'][0] ) ? $meta['_stripe_customer_id'][0] : '' );
		update_post_meta( $renewal_id, '_transaction_id', $payment_intent );
		update_post_meta( $renewal_id, '_stripe_payment_intent_id', $payment_intent );
		update_post_meta( $renewal_id, '_transaction_raw_data', wp_json_encode( (array) $invoice ) );
		update_post_meta( $renewal_id, '_is_subscription_renewal', '1' );
		update_post_meta( $renewal_id, '_payment_status', 'completed' );

		$this->maybe_cancel_after_n_payments( $parent_donation_id, $subscription_id, $environment );

		return array(
			'donation_id' => (int) $renewal_id,
			'state'       => 'renewal',
		);
	}

	/**
	 * Cancel a subscription once the configured number of payments was collected.
	 *
	 * @param int    $parent_donation_id Parent donation post id.
	 * @param string $subscription_id    Stripe subscription id.
	 * @param string $environment        Verified environment.
	 * @return void
	 */
	private function maybe_cancel_after_n_payments( int $parent_donation_id, string $subscription_id, string $environment ): void {
		$number_of_times = (int) get_post_meta( $parent_donation_id, '_recurring_number_of_times', true );

		if ( $number_of_times <= 0 ) {
			return;
		}

		$renewals = $this->find_donations(
			$environment,
			array(
				array(
					'key'   => '_stripe_subscription_id',
					'value' => $subscription_id,
				),
				array(
					'key'   => '_is_subscription_renewal',
					'value' => '1',
				),
			),
			array( 'posts_per_page' => -1 )
		);

		if ( count( $renewals ) < $number_of_times ) {
			return;
		}

		$client = $this->client_for_environment( $environment );

		if ( ! $client ) {
			return;
		}

		try {
			$client->subscriptions->cancel( $subscription_id );
		} catch ( ApiErrorException $e ) {
			$this->log_invoice_error( 'Failed to cancel subscription after N payments.', $parent_donation_id, $environment );
		}
	}

	/**
	 * Normalize an invoice_payment object into a retrievable invoice object.
	 *
	 * @param object $invoice_payment Stripe invoice_payment object.
	 * @param string $environment     Verified environment.
	 * @return object|null
	 */
	private function normalize_invoice_payment( $invoice_payment, string $environment ): ?object {
		$invoice_id = isset( $invoice_payment->invoice ) ? (string) $invoice_payment->invoice : '';
		$client     = $this->client_for_environment( $environment );

		if ( '' === $invoice_id || ! $client ) {
			return null;
		}

		try {
			$invoice = $client->invoices->retrieve(
				$invoice_id,
				array( 'expand' => array( 'subscription', 'lines.data' ) )
			);
		} catch ( ApiErrorException $e ) {
			$this->log_invoice_error( 'invoice_payment.paid: failed to retrieve invoice.', 0, $environment );

			return null;
		}

		$payment_intent = isset( $invoice_payment->payment->payment_intent ) ? (string) $invoice_payment->payment->payment_intent : '';

		if ( '' !== $payment_intent && ( ! isset( $invoice->payment_intent ) || empty( $invoice->payment_intent ) ) ) {
			$invoice->payment_intent = $payment_intent;
		}

		if ( is_object( $invoice->subscription ) && isset( $invoice->subscription->id ) ) {
			$invoice->subscription = $invoice->subscription->id;
		}

		return $invoice;
	}

	/**
	 * Subscription id from an invoice in either API shape.
	 *
	 * @param object $invoice Stripe invoice object.
	 * @return string
	 */
	private function subscription_id_from_invoice( $invoice ): string {
		if ( ! is_object( $invoice ) ) {
			return '';
		}

		$subscription_id = isset( $invoice->subscription ) ? $invoice->subscription : '';

		if ( is_object( $subscription_id ) && isset( $subscription_id->id ) ) {
			$subscription_id = $subscription_id->id;
		}

		if ( '' === $subscription_id && isset( $invoice->parent->subscription_details->subscription ) ) {
			$subscription_id = $invoice->parent->subscription_details->subscription;
		}

		return is_string( $subscription_id ) ? $subscription_id : '';
	}

	/**
	 * Payment intent id for an invoice, with read-only fallbacks.
	 *
	 * @param object $invoice     Stripe invoice object.
	 * @param string $environment Verified environment.
	 * @return string
	 */
	private function payment_intent_id_from_invoice( $invoice, string $environment ): string {
		$payment_intent = isset( $invoice->payment_intent ) ? $invoice->payment_intent : '';

		if ( is_object( $payment_intent ) && isset( $payment_intent->id ) ) {
			$payment_intent = $payment_intent->id;
		}

		if ( is_string( $payment_intent ) && '' !== $payment_intent ) {
			return $payment_intent;
		}

		$invoice_id = isset( $invoice->id ) ? (string) $invoice->id : '';
		$client     = $this->client_for_environment( $environment );

		if ( '' === $invoice_id || ! $client ) {
			return '';
		}

		try {
			$invoice_full = $client->invoices->retrieve( $invoice_id, array( 'expand' => array( 'payment_intent' ) ) );
			$payment_intent = isset( $invoice_full->payment_intent ) ? $invoice_full->payment_intent : '';

			if ( is_object( $payment_intent ) && isset( $payment_intent->id ) ) {
				$payment_intent = $payment_intent->id;
			}

			if ( is_string( $payment_intent ) && '' !== $payment_intent ) {
				return $payment_intent;
			}

			$charges = $client->charges->all(
				array(
					'invoice' => $invoice_id,
					'limit'   => 1,
				)
			);

			if ( ! empty( $charges->data[0]->payment_intent ) ) {
				$charge_intent = $charges->data[0]->payment_intent;

				return is_object( $charge_intent ) && isset( $charge_intent->id )
					? (string) $charge_intent->id
					: (string) $charge_intent;
			}
		} catch ( ApiErrorException $e ) {
			return '';
		}

		return '';
	}

	/**
	 * Donation ids holding a subscription id.
	 *
	 * @param string $subscription_id Stripe subscription id.
	 * @return int[]
	 */
	private function subscription_donation_ids( string $subscription_id ): array {
		if ( '' === $subscription_id ) {
			return array();
		}

		$donations = $this->find_donations(
			'',
			array(
				array(
					'key'   => '_stripe_subscription_id',
					'value' => $subscription_id,
				),
			),
			array( 'fields' => 'ids' )
		);

		return array_map( 'intval', $donations );
	}

	/**
	 * Parent donation id for a subscription.
	 *
	 * @param string $subscription_id Stripe subscription id.
	 * @param string $environment     Verified environment.
	 * @return int
	 */
	private function find_subscription_parent_id( string $subscription_id, string $environment ): int {
		if ( '' === $subscription_id ) {
			return 0;
		}

		$parents = $this->find_donations(
			$environment,
			array(
				array(
					'key'   => '_stripe_subscription_id',
					'value' => $subscription_id,
				),
				array(
					'key'   => '_is_subscription_parent',
					'value' => '1',
				),
			),
			array( 'fields' => 'ids' )
		);

		if ( ! empty( $parents ) ) {
			return (int) $parents[0];
		}

		$any = $this->find_donations(
			$environment,
			array(
				array(
					'key'   => '_stripe_subscription_id',
					'value' => $subscription_id,
				),
			),
			array( 'fields' => 'ids' )
		);

		return empty( $any ) ? 0 : (int) $any[0];
	}

	/**
	 * Donation id from an event object's donation_id metadata.
	 *
	 * @param object $event_object Stripe event data object.
	 * @return int
	 */
	private function metadata_donation_id( $event_object ): int {
		$donation_id = isset( $event_object->metadata->donation_id ) ? (int) $event_object->metadata->donation_id : 0;

		if ( $donation_id <= 0 ) {
			return 0;
		}

		if ( 'donation' !== get_post_type( $donation_id ) ) {
			return 0;
		}

		return $donation_id;
	}

	/**
	 * Environment-filtered donation lookup for a payment object.
	 *
	 * @param object $event_object      Stripe event data object.
	 * @param string $environment Verified environment.
	 * @return int
	 */
	private function find_donation_id_by_metadata( $event_object, string $environment ): int {
		$donation_id = $this->metadata_donation_id( $event_object );

		if ( 0 === $donation_id ) {
			return 0;
		}

		$found = $this->find_donations(
			$environment,
			array(),
			array(
				'post__in' => array( $donation_id ),
				'fields'   => 'ids',
			)
		);

		return empty( $found ) ? 0 : (int) $found[0];
	}

	/**
	 * Find donations for an environment with additional meta clauses.
	 *
	 * @param string                  $environment Environment id, or '' for no filter.
	 * @param array<int, array<mixed>> $meta_query Additional meta clauses.
	 * @param array<string, mixed>     $args       Additional query args.
	 * @return array<int, mixed>
	 */
	private function find_donations( string $environment, array $meta_query = array(), array $args = array() ): array {
		$clauses = $meta_query;

		if ( '' !== $environment ) {
			array_unshift(
				$clauses,
				array(
					'key'   => StripeDonationBinding::META_ENVIRONMENT,
					'value' => $environment,
				)
			);
		}

		$query_args = array_merge(
			array(
				'post_type'      => 'donation',
				'posts_per_page' => 1,
				'post_status'    => 'any',
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'meta_query'     => $clauses, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			),
			$args
		);

		$posts = get_posts( $query_args );

		return is_array( $posts ) ? $posts : array();
	}

	/**
	 * Client for an environment, or null when it cannot be built.
	 *
	 * @param string $environment Environment id.
	 * @return StripeClient|null
	 */
	private function client_for_environment( string $environment ): ?StripeClient {
		$environment = StripeEnvironment::assert_valid( $environment );

		if ( array_key_exists( $environment, $this->clients ) ) {
			return $this->clients[ $environment ];
		}

		$credentials = $this->repository->get_credentials( $environment );

		try {
			$this->clients[ $environment ] = StripeClientFactory::create( $environment, $credentials['secret_key'] );
		} catch ( StripeClientConfigurationException $e ) {
			$this->clients[ $environment ] = null;
		}

		return $this->clients[ $environment ];
	}

	/**
	 * Log a redacted invoice handling error.
	 *
	 * @param string $message     Redacted message.
	 * @param int    $donation_id Donation post id.
	 * @param string $environment Environment id.
	 * @return void
	 */
	private function log_invoice_error( string $message, int $donation_id, string $environment ): void {
		Giftflow_Logger::error(
			'stripe.webhook.invoice',
			array(
				'message'     => $message,
				'donation_id' => $donation_id,
				'environment' => $environment,
				'gateway'     => 'stripe',
			),
			'stripe'
		);
	}
}
