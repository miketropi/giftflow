<?php

namespace GiftFlow\Vendor\Stripe\Service\Issuing;

/**
 * @internal
 *
 * @covers \GiftFlow\Vendor\Stripe\Service\Issuing\DisputeService
 */
final class DisputeServiceTest extends \GiftFlow\Vendor\Stripe\TestCase
{
    use \GiftFlow\Vendor\Stripe\TestHelper;
    const TEST_RESOURCE_ID = 'idp_123';
    /** @var \GiftFlow\Vendor\Stripe\StripeClient */
    private $client;
    /** @var DisputeService */
    private $service;
    /**
     * @before
     */
    protected function setUpService()
    {
        $this->client = new \GiftFlow\Vendor\Stripe\StripeClient(['api_key' => 'sk_test_123', 'api_base' => MOCK_URL]);
        $this->service = new DisputeService($this->client);
    }
    public function testAll()
    {
        $this->expectsRequest('get', '/v1/issuing/disputes');
        $resources = $this->service->all();
        self::compatAssertIsArray($resources->data);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Issuing\Dispute::class, $resources->data[0]);
    }
    public function testCreate()
    {
        $params = ['transaction' => 'ipi_123'];
        $this->expectsRequest('post', '/v1/issuing/disputes', $params);
        $resource = $this->service->create($params);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Issuing\Dispute::class, $resource);
    }
    public function testRetrieve()
    {
        $this->expectsRequest('get', '/v1/issuing/disputes/' . self::TEST_RESOURCE_ID);
        $resource = $this->service->retrieve(self::TEST_RESOURCE_ID);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Issuing\Dispute::class, $resource);
    }
    public function testUpdate()
    {
        $this->expectsRequest('post', '/v1/issuing/disputes/' . self::TEST_RESOURCE_ID, ['metadata' => ['key' => 'value']]);
        $resource = $this->service->update(self::TEST_RESOURCE_ID, ['metadata' => ['key' => 'value']]);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Issuing\Dispute::class, $resource);
    }
    public function testSubmit()
    {
        $this->expectsRequest('post', '/v1/issuing/disputes/' . self::TEST_RESOURCE_ID . '/submit', ['metadata' => ['key' => 'value']]);
        $resource = $this->service->submit(self::TEST_RESOURCE_ID, ['metadata' => ['key' => 'value']]);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Issuing\Dispute::class, $resource);
    }
}