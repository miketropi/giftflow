<?php

// File generated from our OpenAPI spec

namespace GiftFlow\Vendor\Stripe\Events;

/**
 * @property \GiftFlow\Vendor\Stripe\RelatedObject $related_object Object containing the reference to API resource relevant to the event
 * @property \GiftFlow\Vendor\Stripe\EventData\V1BillingMeterErrorReportTriggeredEventData $data data associated with the event
 */
class V1BillingMeterErrorReportTriggeredEvent extends \GiftFlow\Vendor\Stripe\V2\Core\Event
{
    const LOOKUP_TYPE = 'v1.billing.meter.error_report_triggered';

    /**
     * Retrieves the related object from the API. Make an API request on every call.
     *
     * @return \GiftFlow\Vendor\Stripe\Billing\Meter
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
            $evt->data = \GiftFlow\Vendor\Stripe\EventData\V1BillingMeterErrorReportTriggeredEventData::constructFrom($evt->data, $opts, $apiMode);
        }

        return $evt;
    }
}
