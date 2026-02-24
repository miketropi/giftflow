<?php

// File generated from our OpenAPI spec

namespace GiftFlow\Vendor\Stripe\Tax;

/**
 * A Tax Association exposes the Tax Transactions that Stripe attempted to create on your behalf based on the PaymentIntent input.
 *
 * @property string $id Unique identifier for the object.
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property string $calculation The <a href="https://docs.stripe.com/api/tax/calculations/object">Tax Calculation</a> that was included in PaymentIntent.
 * @property string $payment_intent The <a href="https://docs.stripe.com/api/payment_intents/object">PaymentIntent</a> that this Tax Association is tracking.
 * @property null|(object{committed?: (object{transaction: string}&\GiftFlow\Vendor\Stripe\StripeObject), errored?: (object{reason: string}&\GiftFlow\Vendor\Stripe\StripeObject), source: string, status: string}&\GiftFlow\Vendor\Stripe\StripeObject)[] $tax_transaction_attempts Information about the tax transactions linked to this payment intent
 */
class Association extends \GiftFlow\Vendor\Stripe\ApiResource
{
    const OBJECT_NAME = 'tax.association';
}
