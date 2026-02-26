<?php

namespace GiftFlow\Vendor\Stripe\Service;

/**
 * @internal
 *
 * @covers \GiftFlow\Vendor\Stripe\Service\WebhookEndpointService
 */
final class WebhookEndpointServiceTest extends \GiftFlow\Vendor\Stripe\TestCase
{
    use \GiftFlow\Vendor\Stripe\TestHelper;
    const TEST_RESOURCE_ID = 'we_123';
    /** @var \GiftFlow\Vendor\Stripe\StripeClient */
    private $client;
    /** @var WebhookEndpointService */
    private $service;
    /**
     * @before
     */
    protected function setUpService()
    {
        $this->client = new \GiftFlow\Vendor\Stripe\StripeClient(['api_key' => 'sk_test_123', 'api_base' => MOCK_URL]);
        $this->service = new WebhookEndpointService($this->client);
    }
    public function testAll()
    {
        $this->expectsRequest('get', '/v1/webhook_endpoints');
        $resources = $this->service->all();
        self::compatAssertIsArray($resources->data);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\WebhookEndpoint::class, $resources->data[0]);
    }
    public function testCreate()
    {
        $this->expectsRequest('post', '/v1/webhook_endpoints');
        $resource = $this->service->create(['enabled_events' => ['charge.succeeded'], 'url' => 'https://stripe.com']);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\WebhookEndpoint::class, $resource);
    }
    public function testDelete()
    {
        $this->expectsRequest('delete', '/v1/webhook_endpoints/' . self::TEST_RESOURCE_ID);
        $resource = $this->service->delete(self::TEST_RESOURCE_ID);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\WebhookEndpoint::class, $resource);
    }
    public function testRetrieve()
    {
        $this->expectsRequest('get', '/v1/webhook_endpoints/' . self::TEST_RESOURCE_ID);
        $resource = $this->service->retrieve(self::TEST_RESOURCE_ID);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\WebhookEndpoint::class, $resource);
    }
    public function testUpdate()
    {
        $this->expectsRequest('post', '/v1/webhook_endpoints/' . self::TEST_RESOURCE_ID);
        $resource = $this->service->update(self::TEST_RESOURCE_ID, ['enabled_events' => ['charge.succeeded']]);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\WebhookEndpoint::class, $resource);
    }
}