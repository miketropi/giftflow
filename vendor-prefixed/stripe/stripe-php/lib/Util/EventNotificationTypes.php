<?php

namespace GiftFlow\Vendor\Stripe\Util;

class EventNotificationTypes
{
    const v2EventMapping = [
        // The beginning of the section generated from our OpenAPI spec
        \GiftFlow\Vendor\Stripe\Events\V1BillingMeterErrorReportTriggeredEventNotification::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V1BillingMeterErrorReportTriggeredEventNotification::class,
        \GiftFlow\Vendor\Stripe\Events\V1BillingMeterNoMeterFoundEventNotification::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V1BillingMeterNoMeterFoundEventNotification::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountClosedEventNotification::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountClosedEventNotification::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountCreatedEventNotification::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountCreatedEventNotification::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountUpdatedEventNotification::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountUpdatedEventNotification::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationCustomerCapabilityStatusUpdatedEventNotification::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationCustomerCapabilityStatusUpdatedEventNotification::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationCustomerUpdatedEventNotification::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationCustomerUpdatedEventNotification::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationMerchantCapabilityStatusUpdatedEventNotification::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationMerchantCapabilityStatusUpdatedEventNotification::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationMerchantUpdatedEventNotification::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationMerchantUpdatedEventNotification::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationRecipientCapabilityStatusUpdatedEventNotification::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationRecipientCapabilityStatusUpdatedEventNotification::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationRecipientUpdatedEventNotification::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingConfigurationRecipientUpdatedEventNotification::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingDefaultsUpdatedEventNotification::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingDefaultsUpdatedEventNotification::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingFutureRequirementsUpdatedEventNotification::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingFutureRequirementsUpdatedEventNotification::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingIdentityUpdatedEventNotification::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingIdentityUpdatedEventNotification::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingRequirementsUpdatedEventNotification::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountIncludingRequirementsUpdatedEventNotification::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountLinkReturnedEventNotification::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountLinkReturnedEventNotification::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountPersonCreatedEventNotification::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountPersonCreatedEventNotification::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountPersonDeletedEventNotification::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountPersonDeletedEventNotification::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreAccountPersonUpdatedEventNotification::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreAccountPersonUpdatedEventNotification::class,
        \GiftFlow\Vendor\Stripe\Events\V2CoreEventDestinationPingEventNotification::LOOKUP_TYPE => \GiftFlow\Vendor\Stripe\Events\V2CoreEventDestinationPingEventNotification::class,
        // The end of the section generated from our OpenAPI spec
    ];
}
