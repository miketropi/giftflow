<?php

namespace GiftFlow\Vendor\Stripe\Sigma;

/**
 * @internal
 *
 * @covers \GiftFlow\Vendor\Stripe\Sigma\ScheduledQueryRun
 */
final class ScheduledQueryRunTest extends \GiftFlow\Vendor\Stripe\TestCase
{
    use \GiftFlow\Vendor\Stripe\TestHelper;
    const TEST_RESOURCE_ID = 'sqr_123';
    public function testIsListable()
    {
        $resources = ScheduledQueryRun::all();
        self::compatAssertIsArray($resources->data);
        self::assertInstanceOf(ScheduledQueryRun::class, $resources->data[0]);
    }
    public function testIsRetrievable()
    {
        $resource = ScheduledQueryRun::retrieve(self::TEST_RESOURCE_ID);
        self::assertInstanceOf(ScheduledQueryRun::class, $resource);
    }
}