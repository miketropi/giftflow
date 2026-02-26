<?php

namespace GiftFlow\Vendor\Stripe\Service;

/**
 * @internal
 *
 * @covers \GiftFlow\Vendor\Stripe\Service\MandateService
 */
final class MandateServiceTest extends \GiftFlow\Vendor\Stripe\TestCase
{
    use \GiftFlow\Vendor\Stripe\TestHelper;
    const TEST_RESOURCE_ID = 'mandate_123';
    /** @var \GiftFlow\Vendor\Stripe\StripeClient */
    private $client;
    /** @var MandateService */
    private $service;
    /**
     * @before
     */
    protected function setUpService()
    {
        $this->client = new \GiftFlow\Vendor\Stripe\StripeClient(['api_key' => 'sk_test_123', 'api_base' => MOCK_URL]);
        $this->service = new MandateService($this->client);
    }
    public function testRetrieve()
    {
        $this->expectsRequest('get', '/v1/mandates/' . self::TEST_RESOURCE_ID);
        $resource = $this->service->retrieve(self::TEST_RESOURCE_ID);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Mandate::class, $resource);
    }
}