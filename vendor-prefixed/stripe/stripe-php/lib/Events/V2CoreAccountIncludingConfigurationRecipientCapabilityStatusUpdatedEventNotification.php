<?php

// File generated from our OpenAPI spec

namespace GiftFlow\Vendor\Stripe\Events;

/**
 * @property \GiftFlow\Vendor\Stripe\RelatedObject $related_object Object containing the reference to API resource relevant to the event
 */
class V2CoreAccountIncludingConfigurationRecipientCapabilityStatusUpdatedEventNotification extends \GiftFlow\Vendor\Stripe\V2\Core\EventNotification
{
    const LOOKUP_TYPE = 'v2.core.account[configuration.recipient].capability_status_updated';
    public $related_object;

    /**
     * Retrieves the full event object from the API. Make an API request on every call.
     *
     * @return V2CoreAccountIncludingConfigurationRecipientCapabilityStatusUpdatedEvent
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function fetchEvent()
    {
        return parent::fetchEvent();
    }

    /**
     * Retrieves the related object from the API. Make an API request on every call.
     *
     * @return \GiftFlow\Vendor\Stripe\V2\Core\Account
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function fetchRelatedObject()
    {
        return parent::fetchRelatedObject();
    }
}
