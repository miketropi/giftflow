<?php

// File generated from our OpenAPI spec

namespace GiftFlow\Vendor\Stripe\Events;

class V2CoreAccountLinkReturnedEventNotification extends \GiftFlow\Vendor\Stripe\V2\Core\EventNotification
{
    const LOOKUP_TYPE = 'v2.core.account_link.returned';

    /**
     * Retrieves the full event object from the API. Make an API request on every call.
     *
     * @return V2CoreAccountLinkReturnedEvent
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function fetchEvent()
    {
        return parent::fetchEvent();
    }
}
