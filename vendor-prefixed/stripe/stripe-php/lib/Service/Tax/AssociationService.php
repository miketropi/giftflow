<?php

// File generated from our OpenAPI spec

namespace GiftFlow\Vendor\Stripe\Service\Tax;

/**
 * @phpstan-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 *
 * @psalm-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 */
class AssociationService extends \GiftFlow\Vendor\Stripe\Service\AbstractService
{
    /**
     * Finds a tax association object by PaymentIntent id.
     *
     * @param null|array{expand?: string[], payment_intent: string} $params
     * @param null|RequestOptionsArray|\GiftFlow\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @return \GiftFlow\Vendor\Stripe\Tax\Association
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function find($params = null, $opts = null)
    {
        return $this->request('get', '/v1/tax/associations/find', $params, $opts);
    }
}
