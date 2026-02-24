<?php

// File generated from our OpenAPI spec

namespace GiftFlow\Vendor\Stripe\Billing;

/**
 * A credit balance transaction is a resource representing a transaction (either a credit or a debit) against an existing credit grant.
 *
 * @property string $id Unique identifier for the object.
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property int $created Time at which the object was created. Measured in seconds since the Unix epoch.
 * @property null|(object{amount: (object{monetary: null|(object{currency: string, value: int}&\GiftFlow\Vendor\Stripe\StripeObject), type: string}&\GiftFlow\Vendor\Stripe\StripeObject), credits_application_invoice_voided: null|(object{invoice: string|\GiftFlow\Vendor\Stripe\Invoice, invoice_line_item: string}&\GiftFlow\Vendor\Stripe\StripeObject), type: string}&\GiftFlow\Vendor\Stripe\StripeObject) $credit Credit details for this credit balance transaction. Only present if type is <code>credit</code>.
 * @property CreditGrant|string $credit_grant The credit grant associated with this credit balance transaction.
 * @property null|(object{amount: (object{monetary: null|(object{currency: string, value: int}&\GiftFlow\Vendor\Stripe\StripeObject), type: string}&\GiftFlow\Vendor\Stripe\StripeObject), credits_applied: null|(object{invoice: string|\GiftFlow\Vendor\Stripe\Invoice, invoice_line_item: string}&\GiftFlow\Vendor\Stripe\StripeObject), type: string}&\GiftFlow\Vendor\Stripe\StripeObject) $debit Debit details for this credit balance transaction. Only present if type is <code>debit</code>.
 * @property int $effective_at The effective time of this credit balance transaction.
 * @property bool $livemode Has the value <code>true</code> if the object exists in live mode or the value <code>false</code> if the object exists in test mode.
 * @property null|string|\GiftFlow\Vendor\Stripe\TestHelpers\TestClock $test_clock ID of the test clock this credit balance transaction belongs to.
 * @property null|string $type The type of credit balance transaction (credit or debit).
 */
class CreditBalanceTransaction extends \GiftFlow\Vendor\Stripe\ApiResource
{
    const OBJECT_NAME = 'billing.credit_balance_transaction';

    const TYPE_CREDIT = 'credit';
    const TYPE_DEBIT = 'debit';

    /**
     * Retrieve a list of credit balance transactions.
     *
     * @param null|array{credit_grant?: string, customer?: string, customer_account?: string, ending_before?: string, expand?: string[], limit?: int, starting_after?: string} $params
     * @param null|array|string $opts
     *
     * @return \GiftFlow\Vendor\Stripe\Collection<CreditBalanceTransaction> of ApiResources
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public static function all($params = null, $opts = null)
    {
        $url = static::classUrl();

        return static::_requestPage($url, \GiftFlow\Vendor\Stripe\Collection::class, $params, $opts);
    }

    /**
     * Retrieves a credit balance transaction.
     *
     * @param array|string $id the ID of the API resource to retrieve, or an options array containing an `id` key
     * @param null|array|string $opts
     *
     * @return CreditBalanceTransaction
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public static function retrieve($id, $opts = null)
    {
        $opts = \GiftFlow\Vendor\Stripe\Util\RequestOptions::parse($opts);
        $instance = new static($id, $opts);
        $instance->refresh();

        return $instance;
    }
}
