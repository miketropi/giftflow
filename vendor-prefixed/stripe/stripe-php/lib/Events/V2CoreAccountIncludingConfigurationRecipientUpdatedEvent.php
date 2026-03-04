<?php

// File generated from our OpenAPI spec

namespace GiftFlow\Vendor\Stripe\Events;

/**
 * @property \GiftFlow\Vendor\Stripe\RelatedObject $related_object Object containing the reference to API resource relevant to the event
 */
class V2CoreAccountIncludingConfigurationRecipientUpdatedEvent extends \GiftFlow\Vendor\Stripe\V2\Core\Event
{
    const LOOKUP_TYPE = 'v2.core.account[configuration.recipient].updated';

    /**
     * Retrieves the related object from the API. Make an API request on every call.
     *
     * @return \GiftFlow\Vendor\Stripe\V2\Core\Account
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function fetchRelatedObject()
    {
        $apiMode = \GiftFlow\Vendor\Stripe\Util\Util::getApiMode($this->related_object->url);
        list($object, $options) = $this->_request('get', $this->related_object->url, [], [
            'stripe_context' => $this->context,
        ], [], $apiMode);

        return \GiftFlow\Vendor\Stripe\Util\Util::convertToStripeObject($object, $options, $apiMode);
    }
}
