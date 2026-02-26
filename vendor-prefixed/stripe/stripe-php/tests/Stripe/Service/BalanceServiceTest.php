<?php

namespace GiftFlow\Vendor\Stripe\Service;

/**
 * @internal
 *
 * @covers \GiftFlow\Vendor\Stripe\Service\BalanceService
 */
final class BalanceServiceTest extends \GiftFlow\Vendor\Stripe\TestCase
{
    use \GiftFlow\Vendor\Stripe\TestHelper;
    /** @var \GiftFlow\Vendor\Stripe\StripeClient */
    private $client;
    /** @var BalanceService */
    private $service;
    /**
     * @before
     */
    protected function setUpService()
    {
        $this->client = new \GiftFlow\Vendor\Stripe\StripeClient(['api_key' => 'sk_test_123', 'api_base' => MOCK_URL]);
        $this->service = new BalanceService($this->client);
    }
    public function testRetrieve()
    {
        $this->expectsRequest('get', '/v1/balance');
        $resource = $this->service->retrieve();
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Balance::class, $resource);
    }
}