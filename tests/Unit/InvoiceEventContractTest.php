<?php
/**
 * Invoice event contract tests.
 *
 * `invoice_payment.paid` is advertised in the webhook registry, so the property
 * paths its handler reads must exist on the bundled Stripe SDK 19.4.1 objects.
 * These fixtures are the offline proof of that contract.
 *
 * @package GiftFlow
 */

namespace GiftFlow\Tests\Unit;

use GiftFlow\Stripe\StripeWebhookDispatcher;
use GiftFlow\Tests\Support\UnitTestCase;
use GiftFlow\Vendor\Stripe\Event;
use GiftFlow\Vendor\Stripe\Invoice;
use GiftFlow\Vendor\Stripe\InvoicePayment;

/**
 * The invoice payment payload normalizes into the invoice handler.
 */
class InvoiceEventContractTest extends UnitTestCase {

	/**
	 * Realistic 2025+ `invoice.paid` payload (subscription under `parent`).
	 *
	 * @var string
	 */
	private const INVOICE_PAID_JSON = '{
		"id": "in_2025",
		"object": "invoice",
		"livemode": false,
		"billing_reason": "subscription_cycle",
		"parent": {
			"type": "subscription_details",
			"subscription_details": {
				"subscription": "sub_2025"
			}
		},
		"lines": {
			"object": "list",
			"data": [
				{ "id": "il_1", "object": "line_item", "period": { "end": 1793759999 } }
			]
		}
	}';

	/**
	 * Realistic `invoice_payment.paid` payload.
	 *
	 * @var string
	 */
	private const INVOICE_PAYMENT_JSON = '{
		"id": "inpay_1",
		"object": "invoice_payment",
		"livemode": false,
		"status": "paid",
		"invoice": "in_2025",
		"payment": {
			"type": "payment_intent",
			"payment_intent": "pi_invoice"
		}
	}';

	/**
	 * `invoice_payment.paid` carries the invoice and payment intent paths.
	 *
	 * @return void
	 */
	public function test_invoice_payment_exposes_invoice_and_payment_intent(): void {
		$invoice_payment = InvoicePayment::constructFrom(
			json_decode( self::INVOICE_PAYMENT_JSON, true ),
			null
		);

		$this->assertSame( 'in_2025', (string) $invoice_payment->invoice );
		$this->assertSame( 'pi_invoice', (string) $invoice_payment->payment->payment_intent );

		$connection = $this->repository;
		$dispatcher = new StripeWebhookDispatcher( $connection );

		$this->assertTrue( $dispatcher->dispatch( 'invoice_payment.paid', $invoice_payment, 'sandbox' ) );
	}

	/**
	 * The subscription id is readable in both advertised invoice shapes.
	 *
	 * @return void
	 */
	public function test_invoice_subscription_paths(): void {
		$invoice = Invoice::constructFrom( json_decode( self::INVOICE_PAID_JSON, true ), null );

		$this->assertSame( 'sub_2025', (string) $invoice->parent->subscription_details->subscription );
		$this->assertSame( 1793759999, (int) $invoice->lines->data[0]->period->end );

		$legacy = Invoice::constructFrom(
			array(
				'id'           => 'in_legacy',
				'object'       => 'invoice',
				'subscription' => 'sub_legacy',
				'livemode'     => false,
			),
			null
		);

		$this->assertSame( 'sub_legacy', (string) $legacy->subscription );
	}

	/**
	 * A subscription event payload exposes the fields the handler maps.
	 *
	 * @return void
	 */
	public function test_subscription_payload_paths(): void {
		$event = Event::constructFrom(
			json_decode(
				'{
					"id": "evt_1",
					"object": "event",
					"type": "customer.subscription.updated",
					"livemode": false,
					"data": {
						"object": {
							"id": "sub_2025",
							"object": "subscription",
							"status": "past_due",
							"current_period_end": 1793759999
						}
					}
				}',
				true
			),
			null
		);

		$this->assertSame( 'customer.subscription.updated', (string) $event->type );
		$this->assertSame( 'sub_2025', (string) $event->data->object->id );
		$this->assertSame( 'past_due', (string) $event->data->object->status );
		$this->assertSame( 1793759999, (int) $event->data->object->current_period_end );
		$this->assertSame( '', (string) ( $event->account ?? '' ) );
	}
}
