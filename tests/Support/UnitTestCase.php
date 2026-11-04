<?php
/**
 * Base test case for the unit suite.
 *
 * @package GiftFlow
 */

namespace GiftFlow\Tests\Support;

use GiftFlow\Core\InstallationIdentity;
use GiftFlow\Stripe\StripeConnectionRepository;
use GiftFlow\Stripe\StripeCredentialsValidator;
use PHPUnit\Framework\TestCase;

/**
 * Resets the option store and provides Stripe doubles.
 */
abstract class UnitTestCase extends TestCase {

	/**
	 * Shared ledger.
	 *
	 * @var FakeStripeLedger
	 */
	protected $ledger;

	/**
	 * Repository under test.
	 *
	 * @var StripeConnectionRepository
	 */
	protected $repository;

	/**
	 * Installation identity.
	 *
	 * @var InstallationIdentity
	 */
	protected $identity;

	/**
	 * Canonical webhook URL used by the provisioner.
	 *
	 * @var string
	 */
	protected $webhook_url = 'https://donations.giftflow-demo.org/wp-admin/admin-ajax.php?action=giftflow_stripe_webhook';

	/**
	 * Prepare the environment.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		\GiftFlow_Test_Option_Store::reset();

		$this->ledger     = new FakeStripeLedger();
		$this->identity   = new InstallationIdentity();
		$this->repository = new StripeConnectionRepository( $this->identity );
	}

	/**
	 * Client factory that hands out ledger-backed doubles.
	 *
	 * @return callable
	 */
	protected function fake_client_factory(): callable {
		$ledger = $this->ledger;

		return static function ( string $environment, string $secret_key ) use ( $ledger ) {
			return new FakeStripeClient( $ledger, $environment, $secret_key );
		};
	}

	/**
	 * Credential validator bound to the doubles.
	 *
	 * @return StripeCredentialsValidator
	 */
	protected function fake_validator(): StripeCredentialsValidator {
		return new StripeCredentialsValidator( $this->fake_client_factory() );
	}

	/**
	 * Write the paid options array directly.
	 *
	 * @param array<string, mixed> $options Payment options.
	 * @return void
	 */
	protected function set_payment_options( array $options ): void {
		\GiftFlow_Test_Option_Store::$options[ StripeConnectionRepository::PAYMENT_OPTION ] = $options;
	}

	/**
	 * Current payment options.
	 *
	 * @return array<string, mixed>
	 */
	protected function payment_options(): array {
		return $this->repository->get_payment_options();
	}

	/**
	 * Stripe subtree of the payment options.
	 *
	 * @return array<string, mixed>
	 */
	protected function stripe_subtree(): array {
		return $this->repository->get_stripe_subtree();
	}

	/**
	 * Deep snapshot of a value for semantic before/after comparisons.
	 *
	 * @param mixed $value Value.
	 * @return string
	 */
	protected function snapshot( $value ): string {
		return serialize( $value ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- test comparison only.
	}
}
