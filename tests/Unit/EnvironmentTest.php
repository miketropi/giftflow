<?php
/**
 * Environment validation tests.
 *
 * @package GiftFlow
 */

namespace GiftFlow\Tests\Unit;

use GiftFlow\Stripe\StripeEnvironment;
use GiftFlow\Tests\Support\UnitTestCase;

/**
 * StripeEnvironment behaves as an immutable environment context.
 */
class EnvironmentTest extends UnitTestCase {

	/**
	 * Both environments are supported, sandbox first.
	 *
	 * @return void
	 */
	public function test_all_environments_are_sandbox_then_live(): void {
		$this->assertSame( array( 'sandbox', 'live' ), StripeEnvironment::all() );
	}

	/**
	 * Invalid input is rejected without falling back to a default.
	 *
	 * @dataProvider invalid_environments
	 *
	 * @param mixed $environment Candidate value.
	 * @return void
	 */
	public function test_invalid_environments_are_rejected( $environment ): void {
		$this->assertFalse( StripeEnvironment::is_valid( $environment ) );
		$this->expectException( \InvalidArgumentException::class );
		StripeEnvironment::assert_valid( $environment );
	}

	/**
	 * Candidate invalid environments.
	 *
	 * @return array<string, array<int, mixed>>
	 */
	public function invalid_environments(): array {
		return array(
			'empty string'     => array( '' ),
			'production'       => array( 'production' ),
			'test'             => array( 'test' ),
			'trailing space'   => array( 'sandbox ' ),
			'uppercase'        => array( 'SANDBOX' ),
			'null'             => array( null ),
			'integer'          => array( 1 ),
			'array'            => array( array( 'sandbox' ) ),
		);
	}

	/**
	 * Valid environments are returned unchanged.
	 *
	 * @return void
	 */
	public function test_assert_valid_returns_the_environment(): void {
		$this->assertSame( 'sandbox', StripeEnvironment::assert_valid( 'sandbox' ) );
		$this->assertSame( 'live', StripeEnvironment::assert_valid( 'live' ) );
	}

	/**
	 * Livemode maps one-to-one with the environment.
	 *
	 * @return void
	 */
	public function test_livemode_mapping(): void {
		$this->assertFalse( StripeEnvironment::livemode( 'sandbox' ) );
		$this->assertTrue( StripeEnvironment::livemode( 'live' ) );
		$this->assertSame( 'live', StripeEnvironment::from_livemode( true ) );
		$this->assertSame( 'sandbox', StripeEnvironment::from_livemode( false ) );
	}

	/**
	 * Secret key prefixes are environment specific.
	 *
	 * @return void
	 */
	public function test_secret_key_prefixes_are_environment_scoped(): void {
		$this->assertTrue( StripeEnvironment::secret_key_matches( 'sandbox', 'sk_test_abc' ) );
		$this->assertTrue( StripeEnvironment::secret_key_matches( 'sandbox', 'rk_test_abc' ) );
		$this->assertTrue( StripeEnvironment::secret_key_matches( 'live', 'sk_live_abc' ) );
		$this->assertTrue( StripeEnvironment::secret_key_matches( 'live', 'rk_live_abc' ) );

		$this->assertFalse( StripeEnvironment::secret_key_matches( 'sandbox', 'sk_live_abc' ) );
		$this->assertFalse( StripeEnvironment::secret_key_matches( 'live', 'sk_test_abc' ) );
		$this->assertFalse( StripeEnvironment::secret_key_matches( 'live', 'sk_abc' ) );
		$this->assertFalse( StripeEnvironment::secret_key_matches( 'sandbox', '' ) );
	}

	/**
	 * Publishable key prefixes are environment specific.
	 *
	 * @return void
	 */
	public function test_publishable_key_prefixes_are_environment_scoped(): void {
		$this->assertTrue( StripeEnvironment::publishable_key_matches( 'sandbox', 'pk_test_abc' ) );
		$this->assertTrue( StripeEnvironment::publishable_key_matches( 'live', 'pk_live_abc' ) );
		$this->assertFalse( StripeEnvironment::publishable_key_matches( 'sandbox', 'pk_live_abc' ) );
		$this->assertFalse( StripeEnvironment::publishable_key_matches( 'live', 'pk_test_abc' ) );
	}

	/**
	 * Credential option keys keep their historical names.
	 *
	 * Existing installations store credentials under these option keys, so a
	 * rename here would orphan every saved key.
	 *
	 * @return void
	 */
	public function test_credential_option_keys_are_stable(): void {
		$this->assertSame(
			array(
				'publishable_key' => 'stripe_sandbox_publishable_key',
				'secret_key'      => 'stripe_sandbox_secret_key',
				'webhook_secret'  => 'stripe_sandbox_webhook_secret',
			),
			StripeEnvironment::credential_option_keys( 'sandbox' )
		);

		$this->assertSame(
			array(
				'publishable_key' => 'stripe_live_publishable_key',
				'secret_key'      => 'stripe_live_secret_key',
				'webhook_secret'  => 'stripe_live_webhook_secret',
			),
			StripeEnvironment::credential_option_keys( 'live' )
		);
	}

	/**
	 * Labels are human readable.
	 *
	 * @return void
	 */
	public function test_labels(): void {
		$this->assertSame( 'Sandbox', StripeEnvironment::label( 'sandbox' ) );
		$this->assertSame( 'Live', StripeEnvironment::label( 'live' ) );
	}
}
