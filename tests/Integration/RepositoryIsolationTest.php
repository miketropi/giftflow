<?php
/**
 * Stripe repository isolation integration tests.
 *
 * @package GiftFlow
 */

// phpcs:disable Squiz.Commenting.ClassComment.Missing,Squiz.Commenting.FunctionComment.Missing,Squiz.Commenting.VariableComment.Missing -- PHPUnit test names and typed properties are self-documenting.
// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- Test comparison only.

namespace GiftFlow\Tests\Integration;

use GiftFlow\Stripe\StripeConnectionRepository;

final class RepositoryIsolationTest extends \WP_UnitTestCase {
	private StripeConnectionRepository $repository;

	protected function setUp(): void {
		parent::setUp();
		delete_option( StripeConnectionRepository::PAYMENT_OPTION );
		foreach ( array( 'sandbox', 'live' ) as $environment ) {
			delete_option( StripeConnectionRepository::LOCK_PREFIX . $environment );
		}
		$this->repository = new StripeConnectionRepository();
		update_option(
			StripeConnectionRepository::PAYMENT_OPTION,
			array(
				'stripe' => array(
					'stripe_sandbox_publishable_key' => 'sandbox-pk-original',
					'stripe_sandbox_secret_key' => 'sandbox-sk-original',
					'stripe_sandbox_webhook_secret' => 'sandbox-wh-original',
					'stripe_live_publishable_key' => 'live-pk-original',
					'stripe_live_secret_key' => 'live-sk-original',
					'stripe_live_webhook_secret' => 'live-wh-original',
					'stripe_connections' => array(
						'sandbox' => array_merge( StripeConnectionRepository::default_profile(), array( 'webhook_id' => 'we_sandbox' ) ),
						'live' => array_merge(
							StripeConnectionRepository::default_profile(),
							array(
								'webhook_id' => 'we_live',
								'livemode' => true,
							)
						),
					),
				),
			)
		);
	}

	protected function tearDown(): void {
		foreach ( array( 'sandbox', 'live' ) as $environment ) {
			$this->repository->release_lock( $environment );
		}
		delete_option( StripeConnectionRepository::PAYMENT_OPTION );
		parent::tearDown();
	}

	public function testSandboxMutationsLeaveLiveStateByteIdentical(): void {
		$before = $this->environmentSnapshot( 'live' );
		$this->assertTrue(
			$this->repository->update_credentials(
				'sandbox',
				array(
					'publishable_key' => 'sandbox-pk-new',
					'secret_key' => 'sandbox-sk-new',
				)
			)
		);
		$this->assertTrue(
			$this->repository->update_profile(
				'sandbox',
				array(
					'setup_status' => 'configured',
					'webhook_id' => 'we_new',
				)
			)
		);
		$this->assertTrue( $this->repository->acquire_lock( 'sandbox' ) );
		$this->repository->release_lock( 'sandbox' );
		$this->assertSame( $before, $this->environmentSnapshot( 'live' ) );
	}

	public function testLiveMutationsLeaveSandboxStateByteIdentical(): void {
		$before = $this->environmentSnapshot( 'sandbox' );
		$this->assertTrue(
			$this->repository->update_credentials(
				'live',
				array(
					'publishable_key' => 'live-pk-new',
					'secret_key' => 'live-sk-new',
				)
			)
		);
		$this->assertTrue(
			$this->repository->update_profile(
				'live',
				array(
					'setup_status' => 'configured',
					'webhook_id' => 'we_new',
				)
			)
		);
		$this->assertTrue( $this->repository->acquire_lock( 'live' ) );
		$this->repository->release_lock( 'live' );
		$this->assertSame( $before, $this->environmentSnapshot( 'sandbox' ) );
	}

	public function testClaimsAndLocksAreEnvironmentScoped(): void {
		$this->assertSame( 'claimed', $this->repository->claim_webhook_event( 'sandbox', 'acct', 'evt_1' ) );
		$this->assertSame( 'duplicate', $this->repository->claim_webhook_event( 'sandbox', 'acct', 'evt_1' ) );
		$this->assertSame( 'claimed', $this->repository->claim_webhook_event( 'live', 'acct', 'evt_1' ) );
		$this->assertTrue( $this->repository->acquire_lock( 'sandbox' ) );
		$this->assertFalse( $this->repository->acquire_lock( 'sandbox' ) );
		$this->repository->release_lock( 'sandbox' );
		$this->assertTrue( $this->repository->acquire_lock( 'sandbox' ) );
	}

	private function environmentSnapshot( string $environment ): string {
		$stripe = get_option( StripeConnectionRepository::PAYMENT_OPTION )['stripe'];
		$prefix = 'sandbox' === $environment ? 'stripe_sandbox_' : 'stripe_live_';
		return serialize(
			array(
				'profile' => $stripe['stripe_connections'][ $environment ],
				'publishable' => $stripe[ $prefix . 'publishable_key' ],
				'secret' => $stripe[ $prefix . 'secret_key' ],
				'webhook' => $stripe[ $prefix . 'webhook_secret' ],
			)
		);
	}
}
