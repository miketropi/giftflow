<?php

namespace GiftFlow\Vendor\Stripe\Service;

/**
 * @internal
 *
 * @covers \GiftFlow\Vendor\Stripe\Service\SetupAttemptService
 */
final class SetupAttemptServiceTest extends \GiftFlow\Vendor\Stripe\TestCase
{
    use \GiftFlow\Vendor\Stripe\TestHelper;
    /** @var \GiftFlow\Vendor\Stripe\StripeClient */
    private $client;
    /** @var SetupAttemptService */
    private $service;
    /**
     * @before
     */
    protected function setUpService()
    {
        $this->client = new \GiftFlow\Vendor\Stripe\StripeClient(['api_key' => 'sk_test_123', 'api_base' => MOCK_URL]);
        $this->service = new SetupAttemptService($this->client);
    }
    public function testAll()
    {
        $this->expectsRequest('get', '/v1/setup_attempts');
        $resources = $this->service->all(['setup_intent' => 'si_123']);
        self::compatAssertIsArray($resources->data);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\SetupAttempt::class, $resources->data[0]);
    }
}