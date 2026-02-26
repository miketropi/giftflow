<?php

namespace GiftFlow\Vendor\Stripe\Exception\OAuth;

/**
 * Implements properties and methods common to all (non-SPL) Stripe OAuth
 * exceptions.
 */
abstract class OAuthErrorException extends \GiftFlow\Vendor\Stripe\Exception\ApiErrorException
{
    protected function constructErrorObject()
    {
        if (null === $this->jsonBody) {
            return null;
        }

        return \GiftFlow\Vendor\Stripe\OAuthErrorObject::constructFrom($this->jsonBody);
    }
}
