<?php

namespace GiftFlow\Vendor\Stripe\Service;

/**
 * @internal
 *
 * @covers \GiftFlow\Vendor\Stripe\Service\CoreServiceFactory
 */
final class CoreServiceFactoryTest extends \GiftFlow\Vendor\Stripe\TestCase
{
    /** @var \GiftFlow\Vendor\Stripe\StripeClient */
    private $client;
    /** @var CoreServiceFactory */
    private $serviceFactory;
    /**
     * @before
     */
    protected function setUpService()
    {
        $this->client = new \GiftFlow\Vendor\Stripe\StripeClient(['api_key' => 'sk_test_123', 'api_base' => MOCK_URL]);
        $this->serviceFactory = new CoreServiceFactory($this->client);
    }
    public function testExposesPropertiesForServices()
    {
        self::assertInstanceOf(CouponService::class, $this->serviceFactory->coupons);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Service\Issuing\IssuingServiceFactory::class, $this->serviceFactory->issuing);
    }
    public function testMultipleCallsReturnSameInstance()
    {
        $service = $this->serviceFactory->coupons;
        self::assertSame($service, $this->serviceFactory->coupons);
    }
}