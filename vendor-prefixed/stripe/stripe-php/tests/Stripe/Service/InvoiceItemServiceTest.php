<?php

namespace GiftFlow\Vendor\Stripe\Service;

/**
 * @internal
 *
 * @covers \GiftFlow\Vendor\Stripe\Service\InvoiceItemService
 */
final class InvoiceItemServiceTest extends \GiftFlow\Vendor\Stripe\TestCase
{
    use \GiftFlow\Vendor\Stripe\TestHelper;
    const TEST_RESOURCE_ID = 'ii_123';
    /** @var \GiftFlow\Vendor\Stripe\StripeClient */
    private $client;
    /** @var InvoiceItemService */
    private $service;
    /**
     * @before
     */
    protected function setUpService()
    {
        $this->client = new \GiftFlow\Vendor\Stripe\StripeClient(['api_key' => 'sk_test_123', 'api_base' => MOCK_URL]);
        $this->service = new InvoiceItemService($this->client);
    }
    public function testAll()
    {
        $this->expectsRequest('get', '/v1/invoiceitems');
        $resources = $this->service->all();
        self::compatAssertIsArray($resources->data);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\InvoiceItem::class, $resources->data[0]);
    }
    public function testCreate()
    {
        $this->expectsRequest('post', '/v1/invoiceitems');
        $resource = $this->service->create(['amount' => 100, 'currency' => 'usd', 'customer' => 'cus_123']);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\InvoiceItem::class, $resource);
    }
    public function testDelete()
    {
        $this->expectsRequest('delete', '/v1/invoiceitems/' . self::TEST_RESOURCE_ID);
        $resource = $this->service->delete(self::TEST_RESOURCE_ID);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\InvoiceItem::class, $resource);
    }
    public function testRetrieve()
    {
        $this->expectsRequest('get', '/v1/invoiceitems/' . self::TEST_RESOURCE_ID);
        $resource = $this->service->retrieve(self::TEST_RESOURCE_ID);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\InvoiceItem::class, $resource);
    }
    public function testUpdate()
    {
        $this->expectsRequest('post', '/v1/invoiceitems/' . self::TEST_RESOURCE_ID);
        $resource = $this->service->update(self::TEST_RESOURCE_ID, ['metadata' => ['key' => 'value']]);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\InvoiceItem::class, $resource);
    }
}