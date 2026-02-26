<?php

// File generated from our OpenAPI spec

namespace GiftFlow\Vendor\Stripe\Service\V2\Core;

/**
 * @phpstan-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 *
 * @psalm-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 */
class EventService extends \GiftFlow\Vendor\Stripe\Service\AbstractService
{
    /**
     * List events, going back up to 30 days.
     *
     * @param null|array{created?: array{gt?: string, gte?: string, lt?: string, lte?: string}, limit?: int, object_id?: string, types?: string[]} $params
     * @param null|RequestOptionsArray|\GiftFlow\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @return \GiftFlow\Vendor\Stripe\V2\Collection<\GiftFlow\Vendor\Stripe\V2\Core\Event>
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v2/core/events', $params, $opts);
    }

    /**
     * Retrieves the details of an event.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\GiftFlow\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @return \GiftFlow\Vendor\Stripe\V2\Core\Event
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v2/core/events/%s', $id), $params, $opts);
    }
}
