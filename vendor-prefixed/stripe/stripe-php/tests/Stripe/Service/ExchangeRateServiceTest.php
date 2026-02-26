<?php

namespace GiftFlow\Vendor\Stripe\Service;

/**
 * @internal
 *
 * @covers \GiftFlow\Vendor\Stripe\Service\ExchangeRateService
 */
final class ExchangeRateServiceTest extends \GiftFlow\Vendor\Stripe\TestCase
{
    use \GiftFlow\Vendor\Stripe\TestHelper;
    /** @var \GiftFlow\Vendor\Stripe\StripeClient */
    private $client;
    /** @var ExchangeRateService */
    private $service;
    /**
     * @before
     */
    protected function setUpService()
    {
        $this->client = new \GiftFlow\Vendor\Stripe\StripeClient(['api_key' => 'sk_test_123', 'api_base' => MOCK_URL]);
        $this->service = new ExchangeRateService($this->client);
    }
    public function testAll()
    {
        $resources = $this->service->all();
        self::compatAssertIsArray($resources->data);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\ExchangeRate::class, $resources->data[0]);
    }
    public function testRetrieve()
    {
        $resource = $this->service->retrieve('usd');
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\ExchangeRate::class, $resource);
    }
}