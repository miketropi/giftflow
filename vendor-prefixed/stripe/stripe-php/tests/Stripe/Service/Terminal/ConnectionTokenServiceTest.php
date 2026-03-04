<?php

namespace GiftFlow\Vendor\Stripe\Service\Terminal;

/**
 * @internal
 *
 * @covers \GiftFlow\Vendor\Stripe\Service\Terminal\ConnectionTokenService
 */
final class ConnectionTokenServiceTest extends \GiftFlow\Vendor\Stripe\TestCase
{
    use \GiftFlow\Vendor\Stripe\TestHelper;
    /** @var \GiftFlow\Vendor\Stripe\StripeClient */
    private $client;
    /** @var ConnectionTokenService */
    private $service;
    /**
     * @before
     */
    protected function setUpService()
    {
        $this->client = new \GiftFlow\Vendor\Stripe\StripeClient(['api_key' => 'sk_test_123', 'api_base' => MOCK_URL]);
        $this->service = new ConnectionTokenService($this->client);
    }
    public function testCreate()
    {
        $this->expectsRequest('post', '/v1/terminal/connection_tokens');
        $resource = $this->service->create();
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Terminal\ConnectionToken::class, $resource);
    }
}