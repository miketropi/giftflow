<?php

namespace GiftFlow\Vendor\Stripe\Service;

/**
 * @internal
 *
 * @covers \GiftFlow\Vendor\Stripe\Service\PriceService
 */
final class PriceServiceTest extends \GiftFlow\Vendor\Stripe\TestCase
{
    use \GiftFlow\Vendor\Stripe\TestHelper;
    const TEST_RESOURCE_ID = 'prod_123';
    /** @var \GiftFlow\Vendor\Stripe\StripeClient */
    private $client;
    /** @var PriceService */
    private $service;
    /**
     * @before
     */
    protected function setUpService()
    {
        $this->client = new \GiftFlow\Vendor\Stripe\StripeClient(['api_key' => 'sk_test_123', 'api_base' => MOCK_URL]);
        $this->service = new PriceService($this->client);
    }
    public function testAll()
    {
        $this->expectsRequest('get', '/v1/prices');
        $resources = $this->service->all();
        self::compatAssertIsArray($resources->data);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Price::class, $resources->data[0]);
    }
    public function testCreate()
    {
        $this->expectsRequest('post', '/v1/prices');
        $resource = $this->service->create(['unit_amount' => 2000, 'currency' => 'usd', 'recurring' => ['interval' => 'month'], 'product_data' => ['name' => 'Product Name']]);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Price::class, $resource);
    }
    public function testRetrieve()
    {
        $this->expectsRequest('get', '/v1/prices/' . self::TEST_RESOURCE_ID);
        $resource = $this->service->retrieve(self::TEST_RESOURCE_ID);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Price::class, $resource);
    }
    public function testUpdate()
    {
        $this->expectsRequest('post', '/v1/prices/' . self::TEST_RESOURCE_ID);
        $resource = $this->service->update(self::TEST_RESOURCE_ID, ['metadata' => ['key' => 'value']]);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Price::class, $resource);
    }
}