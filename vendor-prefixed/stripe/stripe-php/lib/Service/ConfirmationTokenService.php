<?php

// File generated from our OpenAPI spec

namespace GiftFlow\Vendor\Stripe\Service;

/**
 * @phpstan-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 *
 * @psalm-import-type RequestOptionsArray from \Stripe\Util\RequestOptions
 */
class ConfirmationTokenService extends AbstractService
{
    /**
     * Retrieves an existing ConfirmationToken object.
     *
     * @param string $id
     * @param null|array{expand?: string[]} $params
     * @param null|RequestOptionsArray|\GiftFlow\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @return \GiftFlow\Vendor\Stripe\ConfirmationToken
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/confirmation_tokens/%s', $id), $params, $opts);
    }
}
