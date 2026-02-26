<?php

// File generated from our OpenAPI spec

namespace GiftFlow\Vendor\Stripe\Service\Billing;

/**
 * @phpstan-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 *
 * @psalm-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 */
class CreditBalanceSummaryService extends \GiftFlow\Vendor\Stripe\Service\AbstractService
{
    /**
     * Retrieves the credit balance summary for a customer.
     *
     * @param null|array{customer?: string, customer_account?: string, expand?: string[], filter: array{applicability_scope?: array{price_type?: string, prices?: array{id: string}[]}, credit_grant?: string, type: string}} $params
     * @param null|RequestOptionsArray|\GiftFlow\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @return \GiftFlow\Vendor\Stripe\Billing\CreditBalanceSummary
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function retrieve($params = null, $opts = null)
    {
        return $this->request('get', '/v1/billing/credit_balance_summary', $params, $opts);
    }
}
