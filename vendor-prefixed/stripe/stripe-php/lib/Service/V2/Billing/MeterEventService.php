<?php

// File generated from our OpenAPI spec

namespace GiftFlow\Vendor\Stripe\Service\V2\Billing;

/**
 * @phpstan-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 *
 * @psalm-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 */
class MeterEventService extends \GiftFlow\Vendor\Stripe\Service\AbstractService
{
    /**
     * Creates a meter event. Events are validated synchronously, but are processed
     * asynchronously. Supports up to 1,000 events per second in livemode. For higher
     * rate-limits, please use meter event streams instead.
     *
     * @param null|array{event_name: string, identifier?: string, payload: array<string, string>, timestamp?: string} $params
     * @param null|RequestOptionsArray|\GiftFlow\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @return \GiftFlow\Vendor\Stripe\V2\Billing\MeterEvent
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v2/billing/meter_events', $params, $opts);
    }
}
