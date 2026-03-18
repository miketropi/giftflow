<?php

// File generated from our OpenAPI spec

namespace GiftFlow\Vendor\Stripe\Events;

/**
 * @property \GiftFlow\Vendor\Stripe\RelatedObject $related_object Object containing the reference to API resource relevant to the event
 * @property \GiftFlow\Vendor\Stripe\EventData\V2CoreAccountPersonDeletedEventData $data data associated with the event
 */
class V2CoreAccountPersonDeletedEvent extends \GiftFlow\Vendor\Stripe\V2\Core\Event
{
    const LOOKUP_TYPE = 'v2.core.account_person.deleted';

    /**
     * Retrieves the related object from the API. Make an API request on every call.
     *
     * @return \GiftFlow\Vendor\Stripe\V2\Core\AccountPerson
     *
     * @throws \GiftFlow\Vendor\Stripe\Exception\ApiErrorException if the request fails
     */
    public function fetchRelatedObject()
    {
        $apiMode = \GiftFlow\Vendor\Stripe\Util\Util::getApiMode($this->related_object->url);
        list($object, $options) = $this->_request('get', $this->related_object->url, [], [
            'stripe_context' => $this->context,
            'headers' => ['Stripe-Request-Trigger' => 'event=' . $this->id],
        ], [], $apiMode);

        return \GiftFlow\Vendor\Stripe\Util\Util::convertToStripeObject($object, $options, $apiMode);
    }

    public static function constructFrom($values, $opts = null, $apiMode = 'v2')
    {
        $evt = parent::constructFrom($values, $opts, $apiMode);
        if (null !== $evt->data) {
            $evt->data = \GiftFlow\Vendor\Stripe\EventData\V2CoreAccountPersonDeletedEventData::constructFrom($evt->data, $opts, $apiMode);
        }

        return $evt;
    }
}
