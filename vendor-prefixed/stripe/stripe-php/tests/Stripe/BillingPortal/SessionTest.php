<?php

namespace GiftFlow\Vendor\Stripe\BillingPortal;

/**
 * @internal
 *
 * @covers \GiftFlow\Vendor\Stripe\BillingPortal\Session
 */
final class SessionTest extends \GiftFlow\Vendor\Stripe\TestCase
{
    use \GiftFlow\Vendor\Stripe\TestHelper;
    const TEST_RESOURCE_ID = 'pts_123';
    public function testIsCreatable()
    {
        $this->expectsRequest('post', '/v1/billing_portal/sessions');
        $resource = Session::create(['customer' => 'cus_123', 'return_url' => 'https://stripe.com/return']);
        self::assertInstanceOf(Session::class, $resource);
    }
}