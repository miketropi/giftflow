<?php
/**
 * Base test case for PayPal unit tests.
 *
 * @package GiftFlow
 */

namespace GiftFlow\Tests\Support;

use GiftFlow\Core\InstallationIdentity;
use GiftFlow\PayPal\PayPalApiClient;
use GiftFlow\PayPal\PayPalConnectionRepository;
use GiftFlow\PayPal\PayPalEnvironment;
use GiftFlow\PayPal\PayPalWebhookProvisioner;
use PHPUnit\Framework\TestCase;

/**
 * Resets WordPress stores and provides PayPal HTTP doubles.
 */
abstract class PayPalUnitTestCase extends TestCase {

	/**
	 * PayPal profile repository.
	 *
	 * @var PayPalConnectionRepository
	 */
	protected $repository;

	/**
	 * Installation identity service.
	 *
	 * @var InstallationIdentity
	 */
	protected $identity;

	/**
	 * In-memory HTTP transport.
	 *
	 * @var FakePayPalHttp
	 */
	protected $http;

	/**
	 * In-memory PayPal backend.
	 *
	 * @var FakePayPalLedger
	 */
	protected $ledger;

	/**
	 * Reset stores and create fresh PayPal test doubles.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		\GiftFlow_Test_Option_Store::reset();
		$this->identity = new InstallationIdentity();
		$this->repository = new PayPalConnectionRepository( $this->identity );
		$this->ledger = new FakePayPalLedger();
		$this->http = new FakePayPalHttp( $this->ledger );
		\GiftFlow_Test_Option_Store::$http_callback = $this->http;
	}

	/**
	 * Store credentials for one PayPal environment.
	 *
	 * @param string $environment PayPal environment.
	 * @param string $client_id   PayPal client identifier.
	 * @param string $secret      PayPal client secret.
	 * @return void
	 */
	protected function seed_credentials( string $environment, string $client_id = 'client-id', string $secret = 'client-secret' ): void {
		$this->repository->update_credentials(
			$environment,
			array(
				'client_id' => $client_id,
				'client_secret' => $secret,
			)
		);
	}

	/**
	 * Patch one PayPal connection profile.
	 *
	 * @param string $environment PayPal environment.
	 * @param array  $patch       Profile fields to update.
	 * @return void
	 */
	protected function seed_profile( string $environment, array $patch = array() ): void {
		$this->repository->update_profile( $environment, $patch );
	}

	/**
	 * Return the complete payment options.
	 *
	 * @return array<string, mixed>
	 */
	protected function payment_options(): array {
		return $this->repository->get_payment_options();
	}

	/**
	 * Return the PayPal payment option subtree.
	 *
	 * @return array<string, mixed>
	 */
	protected function paypal_subtree(): array {
		return $this->repository->get_paypal_subtree();
	}

	/**
	 * Create an API client backed by the fake transport.
	 *
	 * @param string $environment PayPal environment.
	 * @param string $client_id   PayPal client identifier.
	 * @param string $secret      PayPal client secret.
	 * @return PayPalApiClient
	 */
	protected function client( string $environment = PayPalEnvironment::SANDBOX, string $client_id = 'client-id', string $secret = 'client-secret' ): PayPalApiClient {
		return new PayPalApiClient( $environment, $client_id, $secret, $this->repository, $this->http );
	}

	/**
	 * Create a provisioner-compatible API client factory.
	 *
	 * @return callable
	 */
	protected function client_factory(): callable {
		$transport = $this->http;
		return static function ( string $environment, string $client_id, string $secret, PayPalConnectionRepository $repository ) use ( $transport ): PayPalApiClient {
			return new PayPalApiClient( $environment, $client_id, $secret, $repository, $transport );
		};
	}

	/**
	 * Create a webhook provisioner using the fake transport.
	 *
	 * @return PayPalWebhookProvisioner
	 */
	protected function provisioner(): PayPalWebhookProvisioner {
		return new PayPalWebhookProvisioner( $this->repository, $this->client_factory() );
	}

	/**
	 * Serialize a value for exact state comparisons.
	 *
	 * @param mixed $value Value to snapshot.
	 * @return string
	 */
	protected function snapshot( $value ): string {
		return serialize( $value ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- Test comparison only.
	}
}
