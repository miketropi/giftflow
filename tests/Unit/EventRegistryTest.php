<?php
/**
 * Webhook event registry tests.
 *
 * @package GiftFlow
 */

namespace GiftFlow\Tests\Unit;

use GiftFlow\Stripe\StripeWebhookEventRegistry;
use GiftFlow\Tests\Support\UnitTestCase;

/**
 * The registry is the single source of truth for subscribed events.
 */
class EventRegistryTest extends UnitTestCase {

	/**
	 * One-time donations need the base events only.
	 *
	 * @return void
	 */
	public function test_base_events_for_one_time_donations(): void {
		$events = StripeWebhookEventRegistry::required_events( false );

		$this->assertSame(
			array(
				'charge.refunded',
				'payment_intent.canceled',
				'payment_intent.payment_failed',
				'payment_intent.succeeded',
			),
			$events
		);
	}

	/**
	 * Recurring donations add the subscription and invoice events.
	 *
	 * @return void
	 */
	public function test_recurring_events_are_added(): void {
		$events = StripeWebhookEventRegistry::required_events( true );

		foreach ( StripeWebhookEventRegistry::BASE_EVENTS as $event ) {
			$this->assertContains( $event, $events );
		}

		foreach ( StripeWebhookEventRegistry::RECURRING_EVENTS as $event ) {
			$this->assertContains( $event, $events );
		}

		$this->assertSame( StripeWebhookEventRegistry::sorted( $events ), $events );
		$this->assertSame( count( array_unique( $events ) ), count( $events ) );
	}

	/**
	 * The advertised events are exactly the events with handlers.
	 *
	 * `invoice_payment.paid` is advertised only because its handler is wired in
	 * the dispatcher; disharmony here would advertise an ignored event.
	 *
	 * @return void
	 */
	public function test_advertised_events_all_have_handlers(): void {
		$events = StripeWebhookEventRegistry::required_events( true );

		foreach ( $events as $event ) {
			$this->assertTrue(
				\GiftFlow\Stripe\StripeWebhookDispatcher::is_supported( $event ),
				'No dispatcher handler for advertised event ' . $event
			);
		}

		$this->assertContains( 'invoice_payment.paid', $events );
	}

	/**
	 * Sorting and de-duplication is deterministic.
	 *
	 * @return void
	 */
	public function test_sorted_normalizes_the_list(): void {
		$this->assertSame(
			array( 'a.b', 'c.d' ),
			StripeWebhookEventRegistry::sorted( array( 'c.d', 'a.b', 'c.d', '  ', 5, null ) )
		);
	}

	/**
	 * Diffs report additions and removals separately.
	 *
	 * @return void
	 */
	public function test_diff(): void {
		$diff = StripeWebhookEventRegistry::diff(
			array( 'payment_intent.succeeded', 'legacy.event' ),
			array( 'payment_intent.succeeded', 'charge.refunded' )
		);

		$this->assertSame( array( 'charge.refunded' ), $diff['add'] );
		$this->assertSame( array( 'legacy.event' ), $diff['remove'] );

		$empty = StripeWebhookEventRegistry::diff( array( 'a' ), array( 'a' ) );

		$this->assertSame( array(), $empty['add'] );
		$this->assertSame( array(), $empty['remove'] );
	}

	/**
	 * Is_required follows the feature flag.
	 *
	 * @return void
	 */
	public function test_is_required(): void {
		$this->assertTrue( StripeWebhookEventRegistry::is_required( 'charge.refunded', false ) );
		$this->assertFalse( StripeWebhookEventRegistry::is_required( 'invoice.paid', false ) );
		$this->assertTrue( StripeWebhookEventRegistry::is_required( 'invoice.paid', true ) );
	}
}
