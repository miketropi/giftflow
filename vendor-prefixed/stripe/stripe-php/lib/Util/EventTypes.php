<?php

namespace GiftFlow\Vendor\Stripe\Util;

class EventTypes
{
    const v2EventMapping = [
        // The beginning of the section generated from our OpenAPI spec
        \GiftFlow\Vendor\Stripe\Events\V1BillingMeterErrorReportTriggeredEvent::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V1BillingMeterErrorReportTriggeredEvent::class,
        \GiftFlow\Vendor\Stripe\Events\V1BillingMeterNoMeterFoundEvent::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V1BillingMeterNoMeterFoundEvent::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountClosedEvent::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountClosedEvent::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountCreatedEvent::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountCreatedEvent::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountUpdatedEvent::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountUpdatedEvent::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationCustomerCapabilityStatusUpdatedEvent::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationCustomerCapabilityStatusUpdatedEvent::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationCustomerUpdatedEvent::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationCustomerUpdatedEvent::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationMerchantCapabilityStatusUpdatedEvent::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationMerchantCapabilityStatusUpdatedEvent::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationMerchantUpdatedEvent::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationMerchantUpdatedEvent::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationRecipientCapabilityStatusUpdatedEvent::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationRecipientCapabilityStatusUpdatedEvent::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationRecipientUpdatedEvent::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationRecipientUpdatedEvent::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingDefaultsUpdatedEvent::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingDefaultsUpdatedEvent::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingFutureRequirementsUpdatedEvent::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingFutureRequirementsUpdatedEvent::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingIdentityUpdatedEvent::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingIdentityUpdatedEvent::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingRequirementsUpdatedEvent::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingRequirementsUpdatedEvent::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountLinkReturnedEvent::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountLinkReturnedEvent::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountPersonCreatedEvent::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountPersonCreatedEvent::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountPersonDeletedEvent::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountPersonDeletedEvent::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountPersonUpdatedEvent::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountPersonUpdatedEvent::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreEventDestinationPingEvent::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreEventDestinationPingEvent::class,
        // The end of the section generated from our OpenAPI spec
    ];
}
