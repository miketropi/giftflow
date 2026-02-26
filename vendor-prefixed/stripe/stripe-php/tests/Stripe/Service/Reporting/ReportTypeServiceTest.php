<?php

namespace GiftFlow\Vendor\Stripe\Service\Reporting;

/**
 * @internal
 *
 * @covers \GiftFlow\Vendor\Stripe\Service\Reporting\ReportTypeService
 */
final class ReportTypeServiceTest extends \GiftFlow\Vendor\Stripe\TestCase
{
    use \GiftFlow\Vendor\Stripe\TestHelper;
    const TEST_RESOURCE_ID = 'activity.summary.1';
    /** @var \GiftFlow\Vendor\Stripe\StripeClient */
    private $client;
    /** @var ReportTypeService */
    private $service;
    /**
     * @before
     */
    protected function setUpService()
    {
        $this->client = new \GiftFlow\Vendor\Stripe\StripeClient(['api_key' => 'sk_test_123', 'api_base' => MOCK_URL]);
        $this->service = new ReportTypeService($this->client);
    }
    public function testAll()
    {
        $this->expectsRequest('get', '/v1/reporting/report_types');
        $resources = $this->service->all();
        self::compatAssertIsArray($resources->data);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Reporting\ReportType::class, $resources->data[0]);
    }
    public function testRetrieve()
    {
        $this->expectsRequest('get', '/v1/reporting/report_types/' . self::TEST_RESOURCE_ID);
        $resource = $this->service->retrieve(self::TEST_RESOURCE_ID);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Reporting\ReportType::class, $resource);
    }
}