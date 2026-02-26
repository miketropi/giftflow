<?php

namespace GiftFlow\Vendor\Stripe\Util;

/**
 * @internal
 *
 * @covers \GiftFlow\Vendor\Stripe\Util\ObjectTypes
 */
final class ObjectTypesTest extends \GiftFlow\Vendor\Stripe\TestCase
{
    use \GiftFlow\Vendor\Stripe\TestHelper;
    public function testMapping()
    {
        self::assertSame(ObjectTypes::mapping['charge'], \GiftFlow\Vendor\Stripe\Charge::class);
        self::assertSame(ObjectTypes::mapping['checkout.session'], \GiftFlow\Vendor\Stripe\Checkout\Session::class);
    }
}