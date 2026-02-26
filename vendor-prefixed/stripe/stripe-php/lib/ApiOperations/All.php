<?php

namespace GiftFlow\Vendor\Stripe\ApiOperations;

/**
 * Trait for listable resources. Adds a `all()` static method to the class.
 *
 * This trait should only be applied to classes that derive from StripeObject.
 */
trait All
{
    /**
     * @param null|array $params
     * @param null|array|string $opts
     *
     * @return \GiftFlow\Vendor\Stripe\Collection of ApiResources
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public static function all($params = null, $opts = null)
    {
        $url = static::classUrl();

        return static::_requestPage($url, \GiftFlow\Vendor\Stripe\Collection::class, $params, $opts);
    }
}
