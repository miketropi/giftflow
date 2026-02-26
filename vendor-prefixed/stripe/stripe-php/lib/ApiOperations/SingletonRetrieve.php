<?php

namespace GiftFlow\Vendor\Stripe\ApiOperations;

/**
 * Trait for retrievable singleton resources. Adds a `retrieve()` static method to the
 * class.
 *
 * This trait should only be applied to classes that derive from SingletonApiResource.
 */
trait SingletonRetrieve
{
    /**
     * @param null|array|string $opts the ID of the API resource to retrieve,
     *     or an options array containing an `id` key
     *
     * @return static
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public static function retrieve($opts = null)
    {
        $opts = \GiftFlow\Vendor\Stripe\Util\RequestOptions::parse($opts);
        $instance = new static(null, $opts);
        $instance->refresh();

        return $instance;
    }
}
