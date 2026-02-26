<?php

namespace GiftFlow\Vendor\Stripe\Service;

/**
 * @internal
 *
 * @covers \GiftFlow\Vendor\Stripe\Service\EventService
 */
final class EventServiceTest extends \GiftFlow\Vendor\Stripe\TestCase
{
    use \GiftFlow\Vendor\Stripe\TestHelper;
    const TEST_RESOURCE_ID = 'evt_123';
    /** @var \GiftFlow\Vendor\Stripe\StripeClient */
    private $client;
    /** @var EventService */
    private $service;
    /**
     * @before
     */
    protected function setUpService()
    {
        $this->client = new \GiftFlow\Vendor\Stripe\StripeClient(['api_key' => 'sk_test_123', 'api_base' => MOCK_URL]);
        $this->service = new EventService($this->client);
    }
    public function testAll()
    {
        $this->expectsRequest('get', '/v1/events');
        $resources = $this->service->all();
        self::compatAssertIsArray($resources->data);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Event::class, $resources->data[0]);
    }
    public function testRetrieve()
    {
        $this->expectsRequest('get', '/v1/events/' . self::TEST_RESOURCE_ID);
        $resource = $this->service->retrieve(self::TEST_RESOURCE_ID);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Event::class, $resource);
    }
}