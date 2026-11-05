<?php
/**
 * Webhook provisioner tests.
 *
 * Covers the connect/check/repair/rebind matrix with ledger-backed Stripe
 * doubles: no network access and no real Stripe state is ever touched.
 *
 * @package GiftFlow
 */

namespace GiftFlow\Tests\Unit;

use GiftFlow\Stripe\StripeConnectionRepository;
use GiftFlow\Stripe\StripeErrorCodes;
use GiftFlow\Stripe\StripeWebhookEventRegistry;
use GiftFlow\Stripe\StripeWebhookProvisioner;
use GiftFlow\Tests\Support\UnitTestCase;
use GiftFlow\Vendor\Stripe\Exception\ApiConnectionException;
use GiftFlow\Vendor\Stripe\Exception\PermissionException;

/**
 * Provisioning is environment scoped, idempotent and fail-closed.
 */
class WebhookProvisionerTest extends UnitTestCase {

	/**
	 * Provisioner under test.
	 *
	 * @var StripeWebhookProvisioner
	 */
	private $provisioner;

	/**
	 * Build the provisioner with doubles.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->provisioner = new StripeWebhookProvisioner(
			$this->repository,
			$this->fake_validator(),
			$this->identity,
			function (): string {
				return $this->webhook_url;
			},
			null,
			$this->fake_client_factory()
		);
	}

	/**
	 * Stored sandbox credentials used by most tests.
	 *
	 * @param array<string, string> $extra Extra credentials.
	 * @return void
	 */
	private function seed_sandbox_credentials( array $extra = array() ): void {
		$this->repository->update_credentials(
			'sandbox',
			array_merge(
				array(
					'publishable_key' => 'pk_test_abc',
					'secret_key'      => 'sk_test_abc',
				),
				$extra
			)
		);
	}

	/**
	 * A sandbox connect creates exactly one endpoint and never touches live.
	 *
	 * @return void
	 */
	public function test_sandbox_connect_creates_one_endpoint_and_leaves_live_untouched(): void {
		$this->seed_sandbox_credentials();
		$this->repository->update_credentials(
			'live',
			array(
				'publishable_key' => 'pk_live_keep',
				'secret_key'      => 'sk_live_keep',
				'webhook_secret'  => 'whsec_live_keep',
			)
		);
		$this->repository->update_profile(
			'live',
			array(
				'webhook_id' => 'we_live_keep',
				'setup_status' => 'configured',
			)
		);

		$live_credentials_before = $this->snapshot( $this->repository->get_credentials( 'live' ) );
		$live_profile_before     = $this->snapshot( $this->repository->get_profile( 'live' ) );

		$result = $this->provisioner->connect( 'sandbox' );

		$this->assertTrue( $result['success'], (string) $result['code'] );
		$this->assertSame(
			array(
				StripeWebhookProvisioner::PHASE_CHECKING_CREDENTIALS,
				StripeWebhookProvisioner::PHASE_FINDING_WEBHOOK,
				StripeWebhookProvisioner::PHASE_CONFIGURING_EVENTS,
				StripeWebhookProvisioner::PHASE_SAVING_LOCAL,
				StripeWebhookProvisioner::PHASE_COMPLETE,
			),
			$result['phases']
		);
		$this->assertStringContainsString( 'Stripe is connected', $result['message'] );
		$this->assertStringContainsString( 'Verified after the first delivery', $result['message'] );

		$this->assertSame( 1, $this->ledger->count_calls( 'create' ) );
		$this->assertCount( 1, $this->ledger->endpoints );

		$profile = $this->repository->get_profile( 'sandbox' );
		$endpoint = $this->ledger->endpoints[ $profile['webhook_id'] ];

		$this->assertSame( StripeConnectionRepository::STATUS_CONFIGURED, $profile['setup_status'] );
		$this->assertSame( StripeConnectionRepository::OWNER_PLUGIN, $profile['webhook_owner'] );
		$this->assertSame( $this->webhook_url, $profile['webhook_url'] );
		$this->assertSame( 'acct_test_1', $profile['stripe_account_id'] );
		$this->assertSame(
			$this->identity->fingerprint( 'sandbox', 'acct_test_1' ),
			$profile['binding_fingerprint']
		);
		$this->assertSame( StripeWebhookEventRegistry::required_events( false, 'sandbox' ), $profile['events'] );
		$this->assertSame( $this->webhook_url, (string) $endpoint->url );
		$this->assertSame( $profile['events'], (array) $endpoint->enabled_events );
		$this->assertSame( '1', (string) $endpoint->metadata->giftflow_managed );
		$this->assertSame( 'sandbox', (string) $endpoint->metadata->giftflow_environment );

		$credentials = $this->repository->get_credentials( 'sandbox' );

		$this->assertSame( 'sk_test_abc', $credentials['secret_key'] );
		$this->assertNotSame( '', $credentials['webhook_secret'] );
		$this->assertSame( (string) $endpoint->secret, $credentials['webhook_secret'] );

		$this->assertSame( $live_credentials_before, $this->snapshot( $this->repository->get_credentials( 'live' ) ) );
		$this->assertSame( $live_profile_before, $this->snapshot( $this->repository->get_profile( 'live' ) ) );
	}

	/**
	 * Repeated connects reuse the stored endpoint.
	 *
	 * @return void
	 */
	public function test_repeat_connects_do_not_create_duplicate_endpoints(): void {
		$this->seed_sandbox_credentials();

		$first = $this->provisioner->connect( 'sandbox' );

		$this->assertTrue( $first['success'] );
		$this->assertSame( 1, $this->ledger->count_calls( 'create' ) );

		$endpoint_id = $this->repository->get_profile( 'sandbox' )['webhook_id'];
		$secret      = $this->repository->get_credentials( 'sandbox' )['webhook_secret'];

		for ( $attempt = 0; $attempt < 3; $attempt++ ) {
			$result = $this->provisioner->connect( 'sandbox' );

			$this->assertTrue( $result['success'], (string) $result['code'] );
		}

		$this->assertSame( 1, $this->ledger->count_calls( 'create' ) );
		$this->assertCount( 1, $this->ledger->endpoints );
		$this->assertSame( $endpoint_id, $this->repository->get_profile( 'sandbox' )['webhook_id'] );
		$this->assertSame( $secret, $this->repository->get_credentials( 'sandbox' )['webhook_secret'] );
	}

	/**
	 * A concurrent submit is rejected by the environment lock.
	 *
	 * @return void
	 */
	public function test_concurrent_connect_is_locked_out(): void {
		$this->seed_sandbox_credentials();

		// Another request holds the lock while it provisions.
		$this->assertTrue( $this->repository->acquire_lock( 'sandbox' ) );

		$result = $this->provisioner->connect( 'sandbox' );

		$this->assertFalse( $result['success'] );
		$this->assertSame( StripeErrorCodes::LOCKED, $result['code'] );
		$this->assertSame( 0, $this->ledger->write_calls() );
		$this->assertCount( 0, $this->ledger->endpoints );

		$this->repository->release_lock( 'sandbox' );

		$after = $this->provisioner->connect( 'sandbox' );

		$this->assertTrue( $after['success'] );
		$this->assertSame( 1, $this->ledger->count_calls( 'create' ) );
	}

	/**
	 * The full matrix works in the opposite direction.
	 *
	 * @return void
	 */
	public function test_live_connect_leaves_sandbox_untouched(): void {
		$this->seed_sandbox_credentials( array( 'webhook_secret' => 'whsec_sandbox_keep' ) );
		$this->repository->update_profile(
			'sandbox',
			array(
				'webhook_id' => 'we_sandbox_keep',
				'setup_status' => 'configured',
			)
		);
		$this->repository->update_credentials(
			'live',
			array(
				'publishable_key' => 'pk_live_abc',
				'secret_key'      => 'sk_live_abc',
			)
		);

		$sandbox_credentials_before = $this->snapshot( $this->repository->get_credentials( 'sandbox' ) );
		$sandbox_profile_before     = $this->snapshot( $this->repository->get_profile( 'sandbox' ) );

		$result = $this->provisioner->connect( 'live' );

		$this->assertTrue( $result['success'], (string) $result['code'] );
		$this->assertSame( 1, $this->ledger->count_calls( 'create' ) );
		$this->assertSame(
			StripeConnectionRepository::STATUS_CONFIGURED,
			$this->repository->get_profile( 'live' )['setup_status']
		);
		$this->assertSame( 'acct_test_1', $this->repository->get_profile( 'live' )['stripe_account_id'] );

		$this->assertSame( $sandbox_credentials_before, $this->snapshot( $this->repository->get_credentials( 'sandbox' ) ) );
		$this->assertSame( $sandbox_profile_before, $this->snapshot( $this->repository->get_profile( 'sandbox' ) ) );
	}

	/**
	 * Non-public URLs block auto configuration before any Stripe call.
	 *
	 * @dataProvider non_public_urls
	 *
	 * @param string $url Candidate URL.
	 * @return void
	 */
	public function test_non_public_urls_are_rejected( string $url ): void {
		// A site that is itself served over http cannot have its URL upgraded.
		\GiftFlow_Test_Option_Store::$options['home_url'] = 'http://example.test';

		$this->seed_sandbox_credentials();

		$provisioner = new StripeWebhookProvisioner(
			$this->repository,
			$this->fake_validator(),
			$this->identity,
			static function () use ( $url ): string {
				return $url;
			},
			static function (): bool {
				return false;
			},
			$this->fake_client_factory()
		);

		$result = $provisioner->connect( 'sandbox' );

		$this->assertFalse( $result['success'] );
		$this->assertSame( StripeErrorCodes::NON_PUBLIC_URL, $result['code'] );
		$this->assertSame( 0, $this->ledger->write_calls(), 'Non-public URLs must never mutate Stripe.' );
	}

	/**
	 * Non-public URL fixtures.
	 *
	 * @return array<string, array<int, string>>
	 */
	public function non_public_urls(): array {
		return array(
			'plain http'      => array( 'http://example.com/wp-admin/admin-ajax.php' ),
			'dot local'       => array( 'https://site.local/wp-admin/admin-ajax.php' ),
			'dot test'        => array( 'https://site.test/wp-admin/admin-ajax.php' ),
			'localhost'       => array( 'https://localhost/wp-admin/admin-ajax.php' ),
			'loopback ip'     => array( 'https://127.0.0.1/wp-admin/admin-ajax.php' ),
			'private ip'      => array( 'https://192.168.1.10/wp-admin/admin-ajax.php' ),
			'single label'    => array( 'https://intranet/wp-admin/admin-ajax.php' ),
		);
	}

	/**
	 * An empty provider falls back to the shared AJAX route.
	 *
	 * @return void
	 */
	public function test_empty_provider_falls_back_to_the_shared_route(): void {
		\GiftFlow_Test_Option_Store::$options['home_url']  = 'https://example.org';
		\GiftFlow_Test_Option_Store::$options['admin_url'] = 'https://example.org/wp-admin/';

		$provisioner = new StripeWebhookProvisioner(
			$this->repository,
			$this->fake_validator(),
			$this->identity,
			static function (): string {
				return '';
			},
			null,
			$this->fake_client_factory()
		);

		$this->assertSame(
			'https://example.org/wp-admin/admin-ajax.php?action=giftflow_stripe_webhook',
			$provisioner->webhook_url()
		);
	}

	/**
	 * Public URLs are recognised.
	 *
	 * @return void
	 */
	public function test_public_url_detection(): void {
		$this->assertTrue( StripeWebhookProvisioner::is_public_url( 'https://example.com/x' ) );
		$this->assertTrue( StripeWebhookProvisioner::is_public_url( 'https://donations.example.org/wp-admin/admin-ajax.php?action=x' ) );
		$this->assertFalse( StripeWebhookProvisioner::is_public_url( 'ftp://example.com/x' ) );
	}

	/**
	 * Missing credentials block the connect.
	 *
	 * @return void
	 */
	public function test_missing_credentials_block_connect(): void {
		$result = $this->provisioner->connect( 'sandbox' );

		$this->assertFalse( $result['success'] );
		$this->assertSame( StripeErrorCodes::MISSING_CREDENTIALS, $result['code'] );
		$this->assertSame( 0, $this->ledger->count_calls( 'create' ) );
		$this->assertSame( array(), $this->repository->get_profile( 'sandbox' )['events'] );
	}

	/**
	 * Insufficient permissions are reported without local writes.
	 *
	 * @return void
	 */
	public function test_insufficient_permissions_block_connect(): void {
		$this->seed_sandbox_credentials();

		$this->ledger->account_exception = PermissionException::factory( 'No permission', 403, null, null, null, 'permission_denied' );

		$result = $this->provisioner->connect( 'sandbox' );

		$this->assertFalse( $result['success'] );
		$this->assertSame( StripeErrorCodes::INSUFFICIENT_PERMISSIONS, $result['code'] );
		$this->assertSame( 0, $this->ledger->count_calls( 'create' ) );
		$this->assertSame( StripeConnectionRepository::STATUS_NOT_CONFIGURED, $this->repository->get_profile( 'sandbox' )['setup_status'] );
	}

	/**
	 * An unowned endpoint on the same URL is never adopted silently.
	 *
	 * @return void
	 */
	public function test_foreign_endpoint_requires_manual_secret(): void {
		$this->seed_sandbox_credentials();

		$this->ledger->add_endpoint(
			array(
				'url'            => $this->webhook_url,
				'enabled_events' => StripeWebhookEventRegistry::required_events( false ),
				'status'         => 'enabled',
				'livemode'       => false,
				'metadata'       => array( 'managed_by' => 'someone-else' ),
			)
		);

		$result = $this->provisioner->connect( 'sandbox' );

		$this->assertFalse( $result['success'] );
		$this->assertSame( StripeErrorCodes::REMOTE_EXISTING_SECRET_UNKNOWN, $result['code'] );
		$this->assertSame( 0, $this->ledger->count_calls( 'create' ) );
	}

	/**
	 * A copied installation is reported as a clone, not as ownership.
	 *
	 * @return void
	 */
	public function test_cloned_installation_is_detected(): void {
		$this->seed_sandbox_credentials();

		$metadata = $this->identity->ownership_metadata( 'sandbox', 'acct_test_1' );
		$metadata[ \GiftFlow\Core\InstallationIdentity::META_SITE_HOST ] = 'copied.example.com';

		$this->ledger->add_endpoint(
			array(
				'url'            => $this->webhook_url,
				'enabled_events' => StripeWebhookEventRegistry::required_events( false ),
				'status'         => 'enabled',
				'livemode'       => false,
				'metadata'       => $metadata,
			)
		);

		$result = $this->provisioner->connect( 'sandbox' );

		$this->assertFalse( $result['success'] );
		$this->assertSame( StripeErrorCodes::INSTALLATION_CLONE_DETECTED, $result['code'] );
		$this->assertSame( 0, $this->ledger->count_calls( 'create' ) );
	}

	/**
	 * An ambiguous create is recorded for reconciliation, never retried blindly.
	 *
	 * @return void
	 */
	public function test_ambiguous_create_is_reconciled_on_retry(): void {
		$this->seed_sandbox_credentials();

		// The request reaches Stripe, the endpoint is created, the response is lost.
		$this->ledger->create_exception = ApiConnectionException::factory( 'Timed out', null, null, null, null, null );

		$first = $this->provisioner->connect( 'sandbox' );

		$this->assertFalse( $first['success'] );
		$this->assertSame( StripeErrorCodes::AMBIGUOUS_CREATE, $first['code'] );
		$this->assertTrue( $first['recovery'] );
		$this->assertCount( 1, $this->ledger->endpoints );

		$profile = $this->repository->get_profile( 'sandbox' );

		$this->assertSame( StripeConnectionRepository::STATUS_RECOVERY_PENDING, $profile['setup_status'] );
		$this->assertSame( 'create', $profile['recovery']['operation'] );
		$this->assertSame( '', $profile['webhook_id'] );

		// Retry: the recorded endpoint is reconciled and replaced, leaving one endpoint.
		$this->ledger->create_exception = null;

		$second = $this->provisioner->connect( 'sandbox' );

		$this->assertTrue( $second['success'], (string) $second['code'] );
		$this->assertCount( 1, $this->ledger->endpoints, 'Reconciliation must not leave duplicates behind.' );
		$this->assertCount( 1, $this->ledger->deleted );
		$this->assertSame(
			StripeConnectionRepository::STATUS_CONFIGURED,
			$this->repository->get_profile( 'sandbox' )['setup_status']
		);
		$this->assertContains( $this->ledger->deleted[0], $this->repository->get_profile( 'sandbox' )['previous_webhook_ids'] );
	}

	/**
	 * A local write failure is reported and never marks the profile configured.
	 *
	 * @return void
	 */
	public function test_local_persistence_failure_is_reported(): void {
		$this->seed_sandbox_credentials();

		$this->ledger->fail_local_writes_on_create = true;

		$result = $this->provisioner->connect( 'sandbox' );

		\GiftFlow_Test_Option_Store::$fail_writes = false;

		$this->assertFalse( $result['success'] );
		$this->assertSame( StripeErrorCodes::PERSISTENCE_FAILED, $result['code'] );
		$this->assertTrue( $result['recovery'] );
		$this->assertNotSame(
			StripeConnectionRepository::STATUS_CONFIGURED,
			$this->repository->get_profile( 'sandbox' )['setup_status']
		);
	}

	/**
	 * Changing the Stripe account requires an explicit rebind.
	 *
	 * @return void
	 */
	public function test_account_change_requires_confirmed_rebind(): void {
		$this->seed_sandbox_credentials();
		$this->assertTrue( $this->provisioner->connect( 'sandbox' )['success'] );

		$profile_before = $this->snapshot( $this->repository->get_profile( 'sandbox' ) );
		$writes_before  = $this->ledger->write_calls();

		// New credentials authenticate against a different account.
		$this->ledger->account = array(
			'id'     => 'acct_test_2',
			'object' => 'account',
		);

		$connect = $this->provisioner->connect(
			'sandbox',
			array(
				'publishable_key' => 'pk_test_new',
				'secret_key'      => 'sk_test_new',
			)
		);

		$this->assertFalse( $connect['success'] );
		$this->assertSame( StripeErrorCodes::REBIND_REQUIRED, $connect['code'] );
		$this->assertTrue( $connect['requires_rebind'] );
		$this->assertSame( $writes_before, $this->ledger->write_calls(), 'No endpoint may be touched before confirmation.' );
		$this->assertSame( $profile_before, $this->snapshot( $this->repository->get_profile( 'sandbox' ) ) );

		$preview = $this->provisioner->rebind_preview(
			'sandbox',
			array(
				'publishable_key' => 'pk_test_new',
				'secret_key'      => 'sk_test_new',
			)
		);

		$this->assertTrue( $preview['success'] );
		$this->assertSame( 0, $preview['writes'] );
		$this->assertSame( 'acct_test_1', $preview['plan']['previous']['account_id'] );
		$this->assertSame( 'acct_test_2', $preview['plan']['target']['account_id'] );
		$this->assertSame( $profile_before, $this->snapshot( $this->repository->get_profile( 'sandbox' ) ) );
		$this->assertSame( $writes_before, $this->ledger->write_calls() );

		$stale = $this->provisioner->rebind(
			'sandbox',
			array(
				'publishable_key' => 'pk_test_new',
				'secret_key'      => 'sk_test_new',
			),
			'not-the-plan-token'
		);

		$this->assertFalse( $stale['success'] );
		$this->assertSame( StripeErrorCodes::REBIND_PLAN_STALE, $stale['code'] );
		$this->assertSame( $profile_before, $this->snapshot( $this->repository->get_profile( 'sandbox' ) ) );

		$rebound = $this->provisioner->rebind(
			'sandbox',
			array(
				'publishable_key' => 'pk_test_new',
				'secret_key'      => 'sk_test_new',
			),
			(string) $preview['plan_token']
		);

		$this->assertTrue( $rebound['success'], (string) $rebound['code'] );
		$this->assertSame( 'acct_test_2', $this->repository->get_profile( 'sandbox' )['stripe_account_id'] );
		$this->assertSame( 'pk_test_new', $this->repository->get_credentials( 'sandbox' )['publishable_key'] );
	}

	/**
	 * Check is read-only.
	 *
	 * @return void
	 */
	public function test_check_is_read_only_when_in_sync(): void {
		$this->seed_sandbox_credentials();
		$this->assertTrue( $this->provisioner->connect( 'sandbox' )['success'] );

		$writes_before = $this->ledger->write_calls();

		$result = $this->provisioner->check( 'sandbox' );

		$this->assertTrue( $result['success'] );
		$this->assertSame( 'in_sync', $result['status'] );
		$this->assertSame( 0, $result['writes'] );
		$this->assertSame( $writes_before, $this->ledger->write_calls() );
		$this->assertGreaterThan( 0, $this->repository->get_profile( 'sandbox' )['last_checked_at'] );
	}

	/**
	 * Drift is reported with a precise, repairable diff and zero writes.
	 *
	 * @return void
	 */
	public function test_check_reports_drift_without_writes(): void {
		$this->seed_sandbox_credentials();
		$this->assertTrue( $this->provisioner->connect( 'sandbox' )['success'] );

		$endpoint_id = $this->repository->get_profile( 'sandbox' )['webhook_id'];
		$writes_before = $this->ledger->write_calls();

		// Simulate remote drift: disabled endpoint, changed URL and extra events.
		$this->ledger->endpoints[ $endpoint_id ] = \GiftFlow\Vendor\Stripe\StripeObject::constructFrom(
			array(
				'id'             => $endpoint_id,
				'object'         => 'webhook_endpoint',
				'status'         => 'disabled',
				'url'            => 'https://example.test/old-endpoint',
				'enabled_events' => array( 'payment_intent.succeeded', 'legacy.event' ),
				'metadata'       => $this->identity->ownership_metadata( 'sandbox', 'acct_test_1' ),
				'livemode'       => false,
			),
			null
		);

		$result = $this->provisioner->check( 'sandbox' );

		$this->assertFalse( $result['success'] );
		$this->assertSame( 'drift', $result['status'] );
		$this->assertSame( 0, $result['writes'] );
		$this->assertSame( $writes_before, $this->ledger->write_calls() );

		$this->assertContains( StripeErrorCodes::ENDPOINT_DISABLED, $result['findings'] );
		$this->assertContains( StripeErrorCodes::URL_DRIFT, $result['findings'] );
		$this->assertContains( StripeErrorCodes::EVENT_DRIFT, $result['findings'] );

		$this->assertSame( $endpoint_id, $result['diff']['endpoint_id'] );
		$this->assertSame( 'disabled', $result['diff']['fields']['status']['from'] );
		$this->assertSame( 'enabled', $result['diff']['fields']['status']['to'] );
		$this->assertSame( $this->webhook_url, $result['diff']['fields']['url']['to'] );
		$this->assertSame( StripeWebhookEventRegistry::required_events( false ), $result['diff']['fields']['events']['to'] );
		$this->assertTrue( $result['repairable'] );
	}

	/**
	 * A missing endpoint is reported without writes.
	 *
	 * @return void
	 */
	public function test_check_reports_missing_endpoint(): void {
		$this->seed_sandbox_credentials();
		$this->repository->update_profile( 'sandbox', array( 'webhook_id' => 'we_vanished' ) );

		$writes_before = $this->ledger->write_calls();

		$result = $this->provisioner->check( 'sandbox' );

		$this->assertFalse( $result['success'] );
		$this->assertSame( StripeErrorCodes::ENDPOINT_MISSING, $result['code'] );
		$this->assertSame( 'missing', $result['status'] );
		$this->assertSame( $writes_before, $this->ledger->write_calls() );
		$this->assertFalse( $result['repairable'] );
	}

	/**
	 * An unrecorded endpoint on the canonical URL is reported as foreign.
	 *
	 * @return void
	 */
	public function test_check_reports_unowned_remote_endpoint(): void {
		$this->seed_sandbox_credentials();

		$this->ledger->add_endpoint(
			array(
				'url'            => $this->webhook_url,
				'enabled_events' => StripeWebhookEventRegistry::required_events( false ),
				'status'         => 'enabled',
				'livemode'       => false,
				'metadata'       => array(),
			)
		);

		$result = $this->provisioner->check( 'sandbox' );

		$this->assertFalse( $result['success'] );
		$this->assertContains( StripeErrorCodes::ENDPOINT_NOT_OWNED, $result['findings'] );
		$this->assertFalse( $result['repairable'] );
		$this->assertSame( 0, $result['writes'] );
	}

	/**
	 * A missing signing secret is reported so it can be imported manually.
	 *
	 * @return void
	 */
	public function test_check_reports_unknown_signing_secret(): void {
		$this->seed_sandbox_credentials();
		$this->assertTrue( $this->provisioner->connect( 'sandbox' )['success'] );

		$this->repository->update_credentials( 'sandbox', array( 'webhook_secret' => '' ) );

		$result = $this->provisioner->check( 'sandbox' );

		$this->assertFalse( $result['success'] );
		$this->assertContains( StripeErrorCodes::SECRET_UNKNOWN, $result['findings'] );
	}

	/**
	 * Repair applies only the approved diff and never rotates the secret.
	 *
	 * @return void
	 */
	public function test_repair_applies_only_the_approved_diff(): void {
		$this->seed_sandbox_credentials();
		$this->assertTrue( $this->provisioner->connect( 'sandbox' )['success'] );

		$endpoint_id = $this->repository->get_profile( 'sandbox' )['webhook_id'];
		$secret      = $this->repository->get_credentials( 'sandbox' )['webhook_secret'];

		$this->ledger->endpoints[ $endpoint_id ] = \GiftFlow\Vendor\Stripe\StripeObject::constructFrom(
			array(
				'id'             => $endpoint_id,
				'object'         => 'webhook_endpoint',
				'status'         => 'disabled',
				'url'            => 'https://example.test/old-endpoint',
				'enabled_events' => array( 'payment_intent.succeeded' ),
				'metadata'       => $this->identity->ownership_metadata( 'sandbox', 'acct_test_1' ),
				'livemode'       => false,
			),
			null
		);

		$check = $this->provisioner->check( 'sandbox' );

		$repair = $this->provisioner->repair( 'sandbox', $check['diff'] );

		$this->assertTrue( $repair['success'], (string) $repair['code'] );
		$this->assertSame( 1, $this->ledger->count_calls( 'update' ) );

		$params = $this->ledger->update_params[ $endpoint_id ][0];

		$this->assertSame( 'enabled', $params['status'] );
		$this->assertSame( $this->webhook_url, $params['url'] );
		$this->assertSame( StripeWebhookEventRegistry::required_events( false ), $params['enabled_events'] );
		$this->assertArrayNotHasKey( 'secret', $params );
		$this->assertSame( $secret, $this->repository->get_credentials( 'sandbox' )['webhook_secret'] );
		$this->assertSame( StripeWebhookEventRegistry::required_events( false ), $this->repository->get_profile( 'sandbox' )['events'] );
	}

	/**
	 * A stale or foreign diff is rejected.
	 *
	 * @return void
	 */
	public function test_repair_rejects_stale_and_foreign_diffs(): void {
		$this->seed_sandbox_credentials();
		$this->assertTrue( $this->provisioner->connect( 'sandbox' )['success'] );

		$endpoint_id = $this->repository->get_profile( 'sandbox' )['webhook_id'];

		$stale = $this->provisioner->repair(
			'sandbox',
			array(
				'endpoint_id' => $endpoint_id,
				'checksum'    => 'stale-checksum',
				'fields'      => array(
					'status' => array(
						'from' => 'disabled',
						'to' => 'enabled',
					),
				),
			)
		);

		$this->assertFalse( $stale['success'] );
		$this->assertSame( StripeErrorCodes::INVALID_DIFF, $stale['code'] );
		$this->assertSame( 0, $this->ledger->count_calls( 'update' ) );

		// Drift on a foreign endpoint is not repairable even with a fresh checksum.
		unset( $this->ledger->endpoints[ $endpoint_id ] );
		$this->repository->update_profile( 'sandbox', array( 'webhook_id' => '' ) );

		$foreign_id = $this->ledger->add_endpoint(
			array(
				'url'            => $this->webhook_url,
				'enabled_events' => array( 'payment_intent.succeeded' ),
				'status'         => 'disabled',
				'livemode'       => false,
				'metadata'       => array(),
			)
		);

		$check = $this->provisioner->check( 'sandbox' );

		$this->assertFalse( $check['repairable'] );

		$repair = $this->provisioner->repair( 'sandbox', $check['diff'] );

		$this->assertFalse( $repair['success'] );
		$this->assertSame( StripeErrorCodes::ENDPOINT_NOT_OWNED, $repair['code'] );
		$this->assertSame( 0, $this->ledger->count_calls( 'update' ) );
		$this->assertNotSame( '', $foreign_id );
	}

	/**
	 * Check without stored credentials reports not-configured.
	 *
	 * @return void
	 */
	public function test_check_without_credentials(): void {
		$result = $this->provisioner->check( 'sandbox' );

		$this->assertFalse( $result['success'] );
		$this->assertSame( StripeErrorCodes::NOT_CONFIGURED, $result['code'] );
		$this->assertSame( 'not_configured', $result['status'] );
		$this->assertSame( 0, $this->ledger->write_calls() );
	}

	/**
	 * Recurring donations widen the configured event list.
	 *
	 * @return void
	 */
	public function test_recurring_feature_adds_events(): void {
		$this->seed_sandbox_credentials();
		$this->repository->commit_stripe_state( array( 'stripe_recurring_enabled' => '1' ) );

		$this->assertTrue( $this->provisioner->connect( 'sandbox' )['success'] );

		$endpoint = $this->ledger->endpoints[ $this->repository->get_profile( 'sandbox' )['webhook_id'] ];

		$this->assertSame( StripeWebhookEventRegistry::required_events( true ), (array) $endpoint->enabled_events );
		$this->assertContains( 'invoice_payment.paid', (array) $endpoint->enabled_events );
	}
}
