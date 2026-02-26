<?php

namespace GiftFlow\Vendor\Stripe\Service;

/**
 * @internal
 *
 * @covers \GiftFlow\Vendor\Stripe\Service\TokenService
 */
final class TokenServiceTest extends \GiftFlow\Vendor\Stripe\TestCase
{
    use \GiftFlow\Vendor\Stripe\TestHelper;
    const TEST_RESOURCE_ID = 'tok_123';
    /** @var \GiftFlow\Vendor\Stripe\StripeClient */
    private $client;
    /** @var TokenService */
    private $service;
    /**
     * @before
     */
    protected function setUpService()
    {
        $this->client = new \GiftFlow\Vendor\Stripe\StripeClient(['api_key' => 'sk_test_123', 'api_base' => MOCK_URL]);
        $this->service = new TokenService($this->client);
    }
    public function testCreate()
    {
        $this->expectsRequest('post', '/v1/tokens');
        $resource = $this->service->create(['card' => 'tok_visa']);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Token::class, $resource);
    }
    public function testRetrieve()
    {
        $this->expectsRequest('get', '/v1/tokens/' . self::TEST_RESOURCE_ID);
        $resource = $this->service->retrieve(self::TEST_RESOURCE_ID);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Token::class, $resource);
    }
}