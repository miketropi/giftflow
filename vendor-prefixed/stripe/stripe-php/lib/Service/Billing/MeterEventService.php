<?php

// File generated from our OpenAPI spec

namespace GiftFlow\Vendor\Stripe\Service\Billing;

/**
 * @phpstan-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 *
 * @psalm-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 */
class MeterEventService extends \GiftFlow\Vendor\Stripe\Service\AbstractService
{
    /**
     * Creates a billing meter event.
     *
     * @param null|array{event_name: string, expand?: string[], identifier?: string, payload: array<string, string>, timestamp?: int} $params
     * @param null|RequestOptionsArray|\GiftFlow\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @return \GiftFlow\Vendor\Stripe\Billing\MeterEvent
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/billing/meter_events', $params, $opts);
    }
}
