<?php
/**
 * Webhook URL resolution and diagnostics tests.
 *
 * Covers the reason a real HTTPS site can still be rejected: the admin URL and
 * the site URL disagreeing about the scheme, and an unreachable host.
 *
 * @package GiftFlow
 */

namespace GiftFlow\Tests\Unit;

use GiftFlow\Stripe\StripeErrorCodes;
use GiftFlow\Stripe\StripeWebhookProvisioner;
use GiftFlow\Tests\Support\UnitTestCase;

/**
 * Webhook URL normalization and public-reachability diagnostics.
 */
class WebhookUrlTest extends UnitTestCase {

	/**
	 * Provisioner under test.
	 *
	 * @var StripeWebhookProvisioner
	 */
	private $provisioner;

	/**
	 * URL returned by the gateway in tests.
	 *
	 * @var string
	 */
	private $admin_url = 'https://donations.giftflow-demo.org/wp-admin/admin-ajax.php?action=giftflow_stripe_webhook';

	/**
	 * Build the provisioner with doubles.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->provisioner = $this->make_provisioner();
	}

	/**
	 * Build a provisioner bound to the current ledger and admin URL.
	 *
	 * @return StripeWebhookProvisioner
	 */
	private function make_provisioner(): StripeWebhookProvisioner {
		$admin_url = $this->admin_url;

		return new StripeWebhookProvisioner(
			$this->repository,
			$this->fake_validator(),
			$this->identity,
			static function () use ( $admin_url ): string {
				return $admin_url;
			},
			static function (): bool {
				return false;
			},
			$this->fake_client_factory()
		);
	}

	/**
	 * An http admin URL is upgraded when the site is served over HTTPS.
	 *
	 * @return void
	 */
	public function test_http_admin_url_is_upgraded_on_an_https_site(): void {
		\GiftFlow_Test_Option_Store::$options['home_url'] = 'https://donations.giftflow-demo.org';

		$this->assertSame(
			'https://donations.giftflow-demo.org/wp-admin/admin-ajax.php?action=giftflow_stripe_webhook',
			StripeWebhookProvisioner::normalize_url( 'http://donations.giftflow-demo.org/wp-admin/admin-ajax.php?action=giftflow_stripe_webhook' )
		);

		// HTTPS URLs and paths are left untouched.
		$already_secure = 'https://donations.giftflow-demo.org/wp-admin/admin-ajax.php?action=x';
		$this->assertSame( $already_secure, StripeWebhookProvisioner::normalize_url( $already_secure ) );
	}

	/**
	 * An http site keeps its http URL, so the failure stays explicit.
	 *
	 * @return void
	 */
	public function test_http_site_keeps_http_url(): void {
		\GiftFlow_Test_Option_Store::$options['home_url'] = 'http://donations.giftflow-demo.org';

		$url = 'http://donations.giftflow-demo.org/wp-admin/admin-ajax.php';

		$this->assertSame( $url, StripeWebhookProvisioner::normalize_url( $url ) );
	}

	/**
	 * Empty and unparseable values pass through unchanged.
	 *
	 * @return void
	 */
	public function test_normalize_url_edge_cases(): void {
		$this->assertSame( '', StripeWebhookProvisioner::normalize_url( '' ) );
		$this->assertSame( 'not a url', StripeWebhookProvisioner::normalize_url( 'not a url' ) );
	}

	/**
	 * The evaluated URL explains why Stripe cannot reach it.
	 *
	 * @dataProvider url_problem_matrix
	 *
	 * @param string $url      Candidate URL.
	 * @param string $expected Expected reason.
	 * @return void
	 */
	public function test_public_url_problems( string $url, string $expected ): void {
		$this->assertSame( $expected, StripeWebhookProvisioner::public_url_problem( $url ) );
		$this->assertSame( '' === $expected, StripeWebhookProvisioner::is_public_url( $url ) );
	}

	/**
	 * URL fixtures.
	 *
	 * @return array<string, array<int, string>>
	 */
	public function url_problem_matrix(): array {
		return array(
			'empty'          => array( '', 'empty' ),
			'unparseable'    => array( 'not a url', 'unparseable' ),
			'plain http'     => array( 'http://donations.giftflow-demo.org/x', 'scheme' ),
			'localhost'      => array( 'https://localhost/x', 'loopback' ),
			'loopback ip'    => array( 'https://127.0.0.1/x', 'loopback' ),
			'reserved tld'   => array( 'https://shop.test/x', 'reserved_tld' ),
			'local tld'      => array( 'https://shop.local/x', 'reserved_tld' ),
			'single label'   => array( 'https://intranet/x', 'single_label' ),
			'private ip'     => array( 'https://192.168.1.20/x', 'private_ip' ),
			'public https'   => array( 'https://donations.giftflow-demo.org/x', '' ),
			'public sub'     => array( 'https://give.shop.example.org/wp-admin/admin-ajax.php?action=x', '' ),
		);
	}

	/**
	 * Every reason has a human readable label.
	 *
	 * @return void
	 */
	public function test_url_problem_labels(): void {
		foreach ( array( 'empty', 'unparseable', 'scheme', 'loopback', 'reserved_tld', 'single_label', 'private_ip' ) as $reason ) {
			$label = StripeWebhookProvisioner::url_problem_label( $reason );

			$this->assertNotSame( '', $label );
			$this->assertStringNotContainsString( 'Exception', $label );
		}
	}

	/**
	 * The reported site URL is used for provisioning, not the raw admin URL.
	 *
	 * This is the scenario behind "Stripe cannot reach this site" on a working
	 * HTTPS install: WordPress reports an http admin URL.
	 *
	 * @return void
	 */
	public function test_connect_uses_the_upgraded_url(): void {
		\GiftFlow_Test_Option_Store::$options['home_url'] = 'https://donations.giftflow-demo.org';

		$this->admin_url = 'http://donations.giftflow-demo.org/wp-admin/admin-ajax.php?action=giftflow_stripe_webhook';
		$this->provisioner = $this->make_provisioner();

		$this->repository->update_credentials(
			'sandbox',
			array(
				'publishable_key' => 'pk_test_abc',
				'secret_key'      => 'sk_test_abc',
			)
		);

		$result = $this->provisioner->connect( 'sandbox' );

		$this->assertTrue( $result['success'], (string) $result['code'] );
		$this->assertSame( 1, $this->ledger->count_calls( 'create' ) );

		$profile  = $this->repository->get_profile( 'sandbox' );
		$endpoint = $this->ledger->endpoints[ $profile['webhook_id'] ];

		$this->assertSame( 'https://', substr( (string) $endpoint->url, 0, 8 ) );
		$this->assertSame( 'https://donations.giftflow-demo.org/wp-admin/admin-ajax.php?action=giftflow_stripe_webhook', (string) $endpoint->url );
		$this->assertSame( (string) $endpoint->url, $profile['webhook_url'] );
	}

	/**
	 * A genuinely unreachable URL reports the exact URL and reason.
	 *
	 * Nothing is sent to Stripe in this case.
	 *
	 * @return void
	 */
	public function test_connect_reports_the_rejected_url_without_contacting_stripe(): void {
		\GiftFlow_Test_Option_Store::$options['home_url'] = 'http://donations.giftflow-demo.org';

		$this->admin_url = 'http://donations.giftflow-demo.org/wp-admin/admin-ajax.php?action=giftflow_stripe_webhook';
		$this->provisioner = $this->make_provisioner();

		$this->repository->update_credentials(
			'sandbox',
			array(
				'publishable_key' => 'pk_test_abc',
				'secret_key'      => 'sk_test_abc',
			)
		);

		$result = $this->provisioner->connect( 'sandbox' );

		$this->assertFalse( $result['success'] );
		$this->assertSame( StripeErrorCodes::NON_PUBLIC_URL, $result['code'] );
		$this->assertSame( $this->admin_url, $result['url'] );
		$this->assertSame( 'scheme', $result['reason'] );
		$this->assertStringContainsString( $this->admin_url, (string) $result['message'] );
		$this->assertStringContainsString( 'not HTTPS', (string) $result['message'] );

		// No Stripe call happens before the URL is known to work.
		$this->assertSame( array(), $result['phases'] );
		$this->assertSame( 0, $this->ledger->count_calls( 'retrieve' ) );
		$this->assertSame( 0, $this->ledger->write_calls() );

		$profile = $this->repository->get_profile( 'sandbox' );

		$this->assertSame( '', $profile['webhook_id'] );
		$this->assertSame( array(), $profile['events'] );
	}

	/**
	 * Rebind preview reports the same URL diagnostics.
	 *
	 * @return void
	 */
	public function test_rebind_preview_reports_url_problem(): void {
		\GiftFlow_Test_Option_Store::$options['options'] = null;
		\GiftFlow_Test_Option_Store::$options['home_url'] = 'https://shop.test';

		$this->admin_url = 'https://shop.test/wp-admin/admin-ajax.php?action=giftflow_stripe_webhook';
		$this->provisioner = $this->make_provisioner();

		$result = $this->provisioner->rebind_preview( 'sandbox' );

		$this->assertFalse( $result['success'] );
		$this->assertSame( StripeErrorCodes::NON_PUBLIC_URL, $result['code'] );
		$this->assertSame( 'reserved_tld', $result['reason'] );
		$this->assertSame( 0, $this->ledger->count_calls( 'retrieve' ) );
	}
}
