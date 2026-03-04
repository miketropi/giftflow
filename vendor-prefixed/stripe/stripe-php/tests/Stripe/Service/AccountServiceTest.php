<?php

namespace GiftFlow\Vendor\Stripe\Service;

/**
 * @internal
 *
 * @covers \GiftFlow\Vendor\Stripe\Service\AccountService
 */
final class AccountServiceTest extends \GiftFlow\Vendor\Stripe\TestCase
{
    use \GiftFlow\Vendor\Stripe\TestHelper;
    const TEST_RESOURCE_ID = 'acct_123';
    const TEST_CAPABILITY_ID = 'acap_123';
    const TEST_EXTERNALACCOUNT_ID = 'ba_123';
    const TEST_PERSON_ID = 'person_123';
    /** @var \GiftFlow\Vendor\Stripe\StripeClient */
    private $client;
    /** @var AccountService */
    private $service;
    /**
     * @before
     */
    protected function setUpService()
    {
        $this->client = new \GiftFlow\Vendor\Stripe\StripeClient(['api_key' => 'sk_test_123', 'api_base' => MOCK_URL]);
        $this->service = new AccountService($this->client);
    }
    public function testAll()
    {
        $this->expectsRequest('get', '/v1/accounts');
        $resources = $this->service->all();
        self::compatAssertIsArray($resources->data);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Account::class, $resources->data[0]);
    }
    public function testAllCapabilities()
    {
        $this->expectsRequest('get', '/v1/accounts/' . self::TEST_RESOURCE_ID . '/capabilities');
        $resources = $this->service->allCapabilities(self::TEST_RESOURCE_ID);
        self::compatAssertIsArray($resources->data);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Capability::class, $resources->data[0]);
    }
    public function testAllExternalAccounts()
    {
        $this->expectsRequest('get', '/v1/accounts/' . self::TEST_RESOURCE_ID . '/external_accounts');
        $resources = $this->service->allExternalAccounts(self::TEST_RESOURCE_ID);
        self::compatAssertIsArray($resources->data);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\BankAccount::class, $resources->data[0]);
    }
    public function testAllPersons()
    {
        $this->expectsRequest('get', '/v1/accounts/' . self::TEST_RESOURCE_ID . '/persons');
        $resources = $this->service->allPersons(self::TEST_RESOURCE_ID);
        self::compatAssertIsArray($resources->data);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Person::class, $resources->data[0]);
    }
    public function testCreate()
    {
        $this->expectsRequest('post', '/v1/accounts');
        $resource = $this->service->create(['type' => 'custom']);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Account::class, $resource);
    }
    public function testCreateExternalAccount()
    {
        $this->expectsRequest('post', '/v1/accounts/' . self::TEST_RESOURCE_ID . '/external_accounts');
        $resource = $this->service->createExternalAccount(self::TEST_RESOURCE_ID, ['external_account' => 'btok_123']);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\BankAccount::class, $resource);
    }
    public function testCreateLoginLink()
    {
        $this->expectsRequest('post', '/v1/accounts/' . self::TEST_RESOURCE_ID . '/login_links');
        $resource = $this->service->createLoginLink(self::TEST_RESOURCE_ID);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\LoginLink::class, $resource);
    }
    public function testCreatePerson()
    {
        $this->expectsRequest('post', '/v1/accounts/' . self::TEST_RESOURCE_ID . '/persons');
        $resource = $this->service->createPerson(self::TEST_RESOURCE_ID, ['dob' => ['day' => 1, 'month' => 1, 'year' => 1980]]);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Person::class, $resource);
    }
    public function testDelete()
    {
        $this->expectsRequest('delete', '/v1/accounts/' . self::TEST_RESOURCE_ID);
        $resource = $this->service->delete(self::TEST_RESOURCE_ID);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Account::class, $resource);
        self::assertTrue($resource->isDeleted());
    }
    public function testDeleteExternalAccount()
    {
        $this->expectsRequest('delete', '/v1/accounts/' . self::TEST_RESOURCE_ID . '/external_accounts/' . self::TEST_EXTERNALACCOUNT_ID);
        $resource = $this->service->deleteExternalAccount(self::TEST_RESOURCE_ID, self::TEST_EXTERNALACCOUNT_ID);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\BankAccount::class, $resource);
        self::assertTrue($resource->isDeleted());
    }
    public function testDeletePerson()
    {
        $this->expectsRequest('delete', '/v1/accounts/' . self::TEST_RESOURCE_ID . '/persons/' . self::TEST_PERSON_ID);
        $resource = $this->service->deletePerson(self::TEST_RESOURCE_ID, self::TEST_PERSON_ID);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Person::class, $resource);
        self::assertTrue($resource->isDeleted());
    }
    public function testReject()
    {
        $this->expectsRequest('post', '/v1/accounts/' . self::TEST_RESOURCE_ID . '/reject');
        $resource = $this->service->reject(self::TEST_RESOURCE_ID, ['reason' => 'fraud']);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Account::class, $resource);
    }
    public function testRetrieveCapability()
    {
        $this->expectsRequest('get', '/v1/accounts/' . self::TEST_RESOURCE_ID . '/capabilities/' . self::TEST_CAPABILITY_ID);
        $resource = $this->service->retrieveCapability(self::TEST_RESOURCE_ID, self::TEST_CAPABILITY_ID);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Capability::class, $resource);
    }
    public function testRetrieveExternalAccount()
    {
        $this->expectsRequest('get', '/v1/accounts/' . self::TEST_RESOURCE_ID . '/external_accounts/' . self::TEST_EXTERNALACCOUNT_ID);
        $resource = $this->service->retrieveExternalAccount(self::TEST_RESOURCE_ID, self::TEST_EXTERNALACCOUNT_ID);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\BankAccount::class, $resource);
    }
    public function testRetrievePerson()
    {
        $this->expectsRequest('get', '/v1/accounts/' . self::TEST_RESOURCE_ID . '/persons/' . self::TEST_PERSON_ID);
        $resource = $this->service->retrievePerson(self::TEST_RESOURCE_ID, self::TEST_PERSON_ID);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Person::class, $resource);
    }
    public function testUpdate()
    {
        $this->expectsRequest('post', '/v1/accounts/' . self::TEST_RESOURCE_ID);
        $resource = $this->service->update(self::TEST_RESOURCE_ID, ['metadata' => ['key' => 'value']]);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Account::class, $resource);
    }
    public function testUpdateCapability()
    {
        $this->expectsRequest('post', '/v1/accounts/' . self::TEST_RESOURCE_ID . '/capabilities/' . self::TEST_CAPABILITY_ID);
        $resource = $this->service->updateCapability(self::TEST_RESOURCE_ID, self::TEST_CAPABILITY_ID, ['requested' => true]);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Capability::class, $resource);
    }
    public function testUpdateExternalAccount()
    {
        $this->expectsRequest('post', '/v1/accounts/' . self::TEST_RESOURCE_ID . '/external_accounts/' . self::TEST_EXTERNALACCOUNT_ID);
        $resource = $this->service->updateExternalAccount(self::TEST_RESOURCE_ID, self::TEST_EXTERNALACCOUNT_ID, ['name' => 'name']);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\BankAccount::class, $resource);
    }
    public function testUpdatePerson()
    {
        $this->expectsRequest('post', '/v1/accounts/' . self::TEST_RESOURCE_ID . '/persons/' . self::TEST_PERSON_ID);
        $resource = $this->service->updatePerson(self::TEST_RESOURCE_ID, self::TEST_PERSON_ID, ['first_name' => 'First name']);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Person::class, $resource);
    }
    public function testRetrieveWithId()
    {
        $this->expectsRequest('get', '/v1/accounts/' . self::TEST_RESOURCE_ID);
        $resource = $this->service->retrieve(self::TEST_RESOURCE_ID);
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Account::class, $resource);
    }
    public function testRetrieveWithoutId()
    {
        $this->expectsRequest('get', '/v1/account');
        $resource = $this->service->retrieve();
        self::assertInstanceOf(\GiftFlow\Vendor\Stripe\Account::class, $resource);
    }
}