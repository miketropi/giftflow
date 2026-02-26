<?php

namespace GiftFlow\Vendor\Stripe\Service\Radar;

/**
 * @internal
 *
 * @covers \GiftFlow\Vendor\Stripe\Service\Radar\EarlyFraudWarningService
 */
final class EarlyFraudWarningServiceTest extends \GiftFlow\Vendor\Stripe\TestCase
{
    use \GiftFlow\Vendor\Stripe\TestHelper;
    const TEST_RESOURCE_ID = 'issfr_123';
    /** @var \GiftFlow\Vendor\Stripe\StripeClient */
    private $client;
    /** @var EarlyFraudWarningService */
    private $service;
    /**
     * @before
     */
    protected function setUpService()
    {
        $this->client = new \GiftFlow\Vendor\Stripe\StripeClient(['api_key' => 'sk_test_123', 'api_base' => MOCK_URL]);
        $this->service = new EarlyFraudWarningService($this->client);
    }
    public function testAll()
    {
        $this->expectsRequest('get', '/v1/radar/early_fraud_warnings');
        $resources = $this->service->all();
        self::compatAssertIsArray($resources->data);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Radar\EarlyFraudWarning::class, $resources->data[0]);
    }
    public function testRetrieve()
    {
        $this->expectsRequest('get', '/v1/radar/early_fraud_warnings/' . self::TEST_RESOURCE_ID);
        $resource = $this->service->retrieve(self::TEST_RESOURCE_ID);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Radar\EarlyFraudWarning::class, $resource);
    }
}