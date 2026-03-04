<?php

namespace GiftFlow\Vendor\Stripe\Tax;

/**
 * @internal
 *
 * @covers \GiftFlow\Vendor\Stripe\Terminal\ConnectionToken
 */
final class SettingsTest extends \GiftFlow\Vendor\Stripe\TestCase
{
    use \GiftFlow\Vendor\Stripe\TestHelper;
    public function testIsUpdateable()
    {
        $this->expectsRequest('post', '/v1/tax/settings');
        $resource = Settings::update(['defaults' => ['tax_behavior' => 'exclusive']]);
        self::assertInstanceOf(Settings::class, $resource);
    }
}