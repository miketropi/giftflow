<?php

namespace GiftFlow\Vendor\Stripe\Events;

use GiftFlow\Vendor\Stripe\V2\Core\EventNotification;

/**
 * A class representing an EventNotification that the SDK doesn't have types for.
 *
 * @property null|\GiftFlow\Vendor\Stripe\RelatedObject $related_object Object containing the reference to API resource relevant to the event.
 */
class UnknownEventNotification extends EventNotification
{
    public $related_object;

    /**
     * Retrieve the event's related object from the Stripe API, if one exists. Returns null otherwise.
     *
     * @return null|\GiftFlow\Vendor\Stripe\StripeObject
     */
    public function fetchRelatedObject()
    {
        return parent::fetchRelatedObject();
    }
}
