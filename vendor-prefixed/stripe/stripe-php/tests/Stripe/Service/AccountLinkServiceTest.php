<?php

namespace GiftFlow\Vendor\Stripe\Service;

/**
 * @internal
 *
 * @covers \GiftFlow\Vendor\Stripe\Service\AccountLinkService
 */
final class AccountLinkServiceTest extends \GiftFlow\Vendor\Stripe\TestCase
{
    use \GiftFlow\Vendor\Stripe\TestHelper;
    /** @var \GiftFlow\Vendor\Stripe\StripeClient */
    private $client;
    /** @var AccountLinkService */
    private $service;
    /**
     * @before
     */
    protected function setUpService()
    {
        $this->client = new \GiftFlow\Vendor\Stripe\StripeClient(['api_key' => 'sk_test_123', 'api_base' => MOCK_URL]);
        $this->service = new AccountLinkService($this->client);
    }
    public function testCreate()
    {
        $this->expectsRequest('post', '/v1/account_links');
        $resource = $this->service->create(['account' => 'acct_123', 'refresh_url' => 'https://stripe.com/refresh_url', 'return_url' => 'https://stripe.com/return_url', 'type' => 'account_onboarding']);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\AccountLink::class, $resource);
    }
}