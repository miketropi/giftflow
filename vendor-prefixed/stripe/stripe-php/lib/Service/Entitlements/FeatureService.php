<?php

// File generated from our OpenAPI spec

namespace GiftFlow\Vendor\Stripe\Service\Entitlements;

/**
 * @phpstan-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 *
 * @psalm-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 */
class FeatureService extends \GiftFlow\Vendor\Stripe\Service\AbstractService
{
    /**
     * Retrieve a list of features.
     *
     * @param null|array{archived?: bool, ending_before?: string, expand?: string[], limit?: int, lookup_key?: string, starting_after?: string} $params
     * @param null|RequestOptionsArray|\GiftFlow\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @return \GiftFlow\Vendor\Stripe\Collection<\GiftFlow\Vendor\Stripe\Entitlements\Feature>
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/entitlements/features', $params, $opts);
    }

    /**
     * Creates a feature.
     *
     * @param null|array{expand?: string[], lookup_key: string, metadata?: array<string, string>, name: string} $params
     * @param null|RequestOptionsArray|\GiftFlow\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @return \GiftFlow\Vendor\Stripe\Entitlements\Feature
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/entitlements/features', $params, $opts);
    }

    /**
     * Retrieves a feature.
     *
     * @param string $id
     * @param null|array{expand?: string[]} $params
     * @param null|RequestOptionsArray|\GiftFlow\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @return \GiftFlow\Vendor\Stripe\Entitlements\Feature
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/entitlements/features/%s', $id), $params, $opts);
    }

    /**
     * Update a feature’s metadata or permanently deactivate it.
     *
     * @param string $id
     * @param null|array{active?: bool, expand?: string[], metadata?: null|array<string, string>, name?: string} $params
     * @param null|RequestOptionsArray|\GiftFlow\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @return \GiftFlow\Vendor\Stripe\Entitlements\Feature
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function update($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/entitlements/features/%s', $id), $params, $opts);
    }
}
