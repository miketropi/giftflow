<?php

namespace GiftFlow\Vendor\Stripe\Service;

/**
 * @internal
 *
 * @covers \GiftFlow\Vendor\Stripe\Service\ApplePayDomainService
 */
final class ApplePayDomainServiceTest extends \GiftFlow\Vendor\Stripe\TestCase
{
    use \GiftFlow\Vendor\Stripe\TestHelper;
    const TEST_RESOURCE_ID = 'apwc_123';
    /** @var \GiftFlow\Vendor\Stripe\StripeClient */
    private $client;
    /** @var ApplePayDomainService */
    private $service;
    /**
     * @before
     */
    protected function setUpService()
    {
        $this->client = new \GiftFlow\Vendor\Stripe\StripeClient(['api_key' => 'sk_test_123', 'api_base' => MOCK_URL]);
        $this->service = new ApplePayDomainService($this->client);
    }
    public function testAll()
    {
        $this->expectsRequest('get', '/v1/apple_pay/domains');
        $resources = $this->service->all();
        self::compatAssertIsArray($resources->data);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\ApplePayDomain::class, $resources->data[0]);
    }
    public function testCreate()
    {
        $this->expectsRequest('post', '/v1/apple_pay/domains');
        $resource = $this->service->create(['domain_name' => 'domain']);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\ApplePayDomain::class, $resource);
    }
    public function testDelete()
    {
        $this->expectsRequest('delete', '/v1/apple_pay/domains/' . self::TEST_RESOURCE_ID);
        $resource = $this->service->delete(self::TEST_RESOURCE_ID);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\ApplePayDomain::class, $resource);
    }
    public function testRetrieve()
    {
        $this->expectsRequest('get', '/v1/apple_pay/domains/' . self::TEST_RESOURCE_ID);
        $resource = $this->service->retrieve(self::TEST_RESOURCE_ID);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\ApplePayDomain::class, $resource);
    }
}