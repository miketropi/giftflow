<?php

namespace GiftFlow\Vendor\Stripe\Service;

/**
 * @internal
 *
 * @covers \GiftFlow\Vendor\Stripe\Service\CouponService
 */
final class CouponServiceTest extends \GiftFlow\Vendor\Stripe\TestCase
{
    use \GiftFlow\Vendor\Stripe\TestHelper;
    const TEST_RESOURCE_ID = 'COUPON_ID';
    /** @var \GiftFlow\Vendor\Stripe\StripeClient */
    private $client;
    /** @var CouponService */
    private $service;
    /**
     * @before
     */
    protected function setUpService()
    {
        $this->client = new \GiftFlow\Vendor\Stripe\StripeClient(['api_key' => 'sk_test_123', 'api_base' => MOCK_URL]);
        $this->service = new CouponService($this->client);
    }
    public function testAll()
    {
        $this->expectsRequest('get', '/v1/coupons');
        $resources = $this->service->all();
        self::compatAssertIsArray($resources->data);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Coupon::class, $resources->data[0]);
    }
    public function testCreate()
    {
        $this->expectsRequest('post', '/v1/coupons');
        $resource = $this->service->create(['percent_off' => 25, 'duration' => 'repeating', 'duration_in_months' => 3]);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Coupon::class, $resource);
    }
    public function testDelete()
    {
        $this->expectsRequest('delete', '/v1/coupons/' . self::TEST_RESOURCE_ID);
        $resource = $this->service->delete(self::TEST_RESOURCE_ID);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Coupon::class, $resource);
    }
    public function testRetrieve()
    {
        $this->expectsRequest('get', '/v1/coupons/' . self::TEST_RESOURCE_ID);
        $resource = $this->service->retrieve(self::TEST_RESOURCE_ID);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Coupon::class, $resource);
    }
    public function testUpdate()
    {
        $this->expectsRequest('post', '/v1/coupons/' . self::TEST_RESOURCE_ID);
        $resource = $this->service->update(self::TEST_RESOURCE_ID, ['metadata' => ['key' => 'value']]);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Coupon::class, $resource);
    }
}