<?php

// File generated from our OpenAPI spec

namespace GiftFlow\Vendor\Stripe\Service\Issuing;

/**
 * @phpstan-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 *
 * @psalm-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 */
class PhysicalBundleService extends \GiftFlow\Vendor\Stripe\Service\AbstractService
{
    /**
     * Returns a list of physical bundle objects. The objects are sorted in descending
     * order by creation date, with the most recently created object appearing first.
     *
     * @param null|array{ending_before?: string, expand?: string[], limit?: int, starting_after?: string, status?: string, type?: string} $params
     * @param null|RequestOptionsArray|\GiftFlow\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @return \GiftFlow\Vendor\Stripe\Collection<\GiftFlow\Vendor\Stripe\Issuing\PhysicalBundle>
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/issuing/physical_bundles', $params, $opts);
    }

    /**
     * Retrieves a physical bundle object.
     *
     * @param string $id
     * @param null|array{expand?: string[]} $params
     * @param null|RequestOptionsArray|\GiftFlow\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @return \GiftFlow\Vendor\Stripe\Issuing\PhysicalBundle
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/issuing/physical_bundles/%s', $id), $params, $opts);
    }
}
