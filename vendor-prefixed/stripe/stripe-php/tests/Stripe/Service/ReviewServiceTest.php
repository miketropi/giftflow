<?php

namespace GiftFlow\Vendor\Stripe\Service;

/**
 * @internal
 *
 * @covers \GiftFlow\Vendor\Stripe\Service\ReviewService
 */
final class ReviewServiceTest extends \GiftFlow\Vendor\Stripe\TestCase
{
    use \GiftFlow\Vendor\Stripe\TestHelper;
    const TEST_RESOURCE_ID = 'prv_123';
    /** @var \GiftFlow\Vendor\Stripe\StripeClient */
    private $client;
    /** @var ReviewService */
    private $service;
    /**
     * @before
     */
    protected function setUpService()
    {
        $this->client = new \GiftFlow\Vendor\Stripe\StripeClient(['api_key' => 'sk_test_123', 'api_base' => MOCK_URL]);
        $this->service = new ReviewService($this->client);
    }
    public function testAll()
    {
        $this->expectsRequest('get', '/v1/reviews');
        $resources = $this->service->all();
        self::compatAssertIsArray($resources->data);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Review::class, $resources->data[0]);
    }
    public function testApprove()
    {
        $this->expectsRequest('post', '/v1/reviews/' . self::TEST_RESOURCE_ID . '/approve');
        $resource = $this->service->approve(self::TEST_RESOURCE_ID);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Review::class, $resource);
    }
    public function testRetrieve()
    {
        $this->expectsRequest('get', '/v1/reviews/' . self::TEST_RESOURCE_ID);
        $resource = $this->service->retrieve(self::TEST_RESOURCE_ID);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Review::class, $resource);
    }
}