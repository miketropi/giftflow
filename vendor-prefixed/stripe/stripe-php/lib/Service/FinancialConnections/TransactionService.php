<?php

// File generated from our OpenAPI spec

namespace GiftFlow\Vendor\Stripe\Service\FinancialConnections;

/**
 * @phpstan-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 *
 * @psalm-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 */
class TransactionService extends \GiftFlow\Vendor\Stripe\Service\AbstractService
{
    /**
     * Returns a list of Financial Connections <code>Transaction</code> objects.
     *
     * @param null|array{account: string, ending_before?: string, expand?: string[], limit?: int, starting_after?: string, transacted_at?: array|int, transaction_refresh?: array{after: string}} $params
     * @param null|RequestOptionsArray|\GiftFlow\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @return \GiftFlow\Vendor\Stripe\Collection<\GiftFlow\Vendor\Stripe\FinancialConnections\Transaction>
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/financial_connections/transactions', $params, $opts);
    }

    /**
     * Retrieves the details of a Financial Connections <code>Transaction</code>.
     *
     * @param string $id
     * @param null|array{expand?: string[]} $params
     * @param null|RequestOptionsArray|\GiftFlow\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @return \GiftFlow\Vendor\Stripe\FinancialConnections\Transaction
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/financial_connections/transactions/%s', $id), $params, $opts);
    }
}
