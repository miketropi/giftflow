<?php

// File generated from our OpenAPI spec

namespace GiftFlow\Vendor\Stripe\Service\V2\Billing;

/**
 * @phpstan-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 *
 * @psalm-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 */
class MeterEventAdjustmentService extends \GiftFlow\Vendor\Stripe\Service\AbstractService
{
    /**
     * Creates a meter event adjustment to cancel a previously sent meter event.
     *
     * @param null|array{cancel: array{identifier: string}, event_name: string, type: string} $params
     * @param null|RequestOptionsArray|\GiftFlow\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @return \GiftFlow\Vendor\Stripe\V2\Billing\MeterEventAdjustment
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v2/billing/meter_event_adjustments', $params, $opts);
    }
}
