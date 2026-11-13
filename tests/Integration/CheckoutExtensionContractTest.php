<?php
/**
 * Checkout extension contract tests.
 *
 * These pin the two guarantees extensions rely on: a refusal is never mistaken
 * for an absent extension, and a binding requirement can veto the payment before
 * the provider is called.
 *
 * @package GiftFlow
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort -- Compact property documentation is intentional in tests.

namespace GiftFlow\Tests\Integration;

use GiftFlow\Services\CheckoutExtension;

/**
 * Class CheckoutExtensionContractTest
 */
final class CheckoutExtensionContractTest extends \WP_UnitTestCase {

	/**
	 * Bound action calls captured during the test.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	private $bound_calls = array();

	/**
	 * Filter callbacks registered by a test.
	 *
	 * @var array<int,array{0:string,1:callable}>
	 */
	private $registered = array();

	/**
	 * Reset captured state.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->bound_calls = array();
		$this->registered  = array();

		add_action( CheckoutExtension::ACTION_BOUND, $this->capture_bound( ... ), 10, 2 );
	}

	/**
	 * Remove test hooks.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		foreach ( $this->registered as $entry ) {
			remove_filter( $entry[0], $entry[1], 10 );
		}

		remove_all_actions( CheckoutExtension::ACTION_BOUND );

		$this->registered = array();

		parent::tearDown();
	}

	/**
	 * Capture bound notifications.
	 *
	 * @param array $extension Extension payload.
	 * @param array $binding   Binding facts.
	 * @return void
	 */
	public function capture_bound( $extension, $binding ): void {
		$this->bound_calls[] = array(
			'extension' => $extension,
			'binding'   => $binding,
		);
	}

	/**
	 * Register a prepare filter for the test.
	 *
	 * @param callable $callback Callback.
	 * @return void
	 */
	private function on_prepare( callable $callback ): void {
		add_filter( CheckoutExtension::FILTER_PREPARE, $callback, 10, 3 );
		$this->registered[] = array( CheckoutExtension::FILTER_PREPARE, $callback );
	}

	/**
	 * Register a binding validation filter for the test.
	 *
	 * @param callable $callback Callback.
	 * @return void
	 */
	private function on_bind( callable $callback ): void {
		add_filter( CheckoutExtension::FILTER_VALIDATE_BINDING, $callback, 10, 3 );
		$this->registered[] = array( CheckoutExtension::FILTER_VALIDATE_BINDING, $callback );
	}

	/**
	 * A valid extension payload for binding tests.
	 *
	 * @param string $attempt_key Attempt key.
	 * @return array
	 */
	private function extension( $attempt_key = 'attempt-key-0001' ): array {
		return array(
			'attempt_key'  => $attempt_key,
			'amount_minor' => 5000,
			'currency'     => 'USD',
			'environment'  => 'live',
			'reward_id'    => 0,
		);
	}

	/**
	 * A refusal from an extension stops the checkout instead of disappearing.
	 */
	public function test_prepare_returns_upstream_error_unchanged(): void {
		$this->on_prepare(
			static function () {
				return new \WP_Error( 'p2p_refused', 'This fundraiser is closed.' );
			}
		);

		$result = CheckoutExtension::prepare( array(), array( 'gateway' => 'stripe' ) );

		$this->assertWPError( $result );
		$this->assertSame( 'p2p_refused', $result->get_error_code() );
		$this->assertSame( 'This fundraiser is closed.', $result->get_error_message() );
	}

	/**
	 * No registered extension still means "ordinary checkout".
	 */
	public function test_prepare_returns_empty_array_without_extension(): void {
		$this->assertSame( array(), CheckoutExtension::prepare( array(), array() ) );

		$this->on_prepare(
			static function () {
				return null;
			}
		);

		$this->assertSame( array(), CheckoutExtension::prepare( array(), array() ) );
	}

	/**
	 * A non-array result that is not a refusal is a contract violation.
	 */
	public function test_prepare_rejects_invalid_extension_result(): void {
		$this->on_prepare(
			static function () {
				return 'not-an-extension';
			}
		);

		$result = CheckoutExtension::prepare( array(), array() );

		$this->assertWPError( $result );
		$this->assertSame( 'giftflow_checkout_extension_invalid', $result->get_error_code() );
	}

	/**
	 * A hole in the fixed wire format is refused rather than silently dropped.
	 */
	public function test_prepare_rejects_extension_without_attempt_key(): void {
		$this->on_prepare(
			static function () {
				return array(
					'amount_minor' => 100,
					'currency' => 'USD',
					'environment' => 'live',
				);
			}
		);

		$result = CheckoutExtension::prepare( array(), array() );

		$this->assertWPError( $result );
		$this->assertSame( 'giftflow_checkout_extension_invalid', $result->get_error_code() );
	}

	/**
	 * An empty extension is not a binding failure.
	 */
	public function test_bind_without_extension_succeeds(): void {
		$this->assertTrue( CheckoutExtension::bind( array(), array( 'donation_id' => 12 ) ) );
		$this->assertSame( array(), $this->bound_calls );
	}

	/**
	 * Without a veto the bound notification still fires exactly once.
	 */
	public function test_bind_dispatches_bound_action_when_accepted(): void {
		$donation_id = (int) self::factory()->post->create( array( 'post_type' => 'donation' ) );

		$result = CheckoutExtension::bind(
			$this->extension(),
			array(
				'donation_id' => $donation_id,
				'gateway' => 'stripe',
			)
		);

		$this->assertTrue( $result );
		$this->assertCount( 1, $this->bound_calls );
		$this->assertSame( $donation_id, $this->bound_calls[0]['binding']['donation_id'] );
	}

	/**
	 * A required extension can veto the payment and no other observer is told
	 * the attempt was bound.
	 */
	public function test_bind_veto_returns_error_and_skips_bound_action(): void {
		$donation_id = (int) self::factory()->post->create( array( 'post_type' => 'donation' ) );

		$this->on_bind(
			static function () {
				return new \WP_Error( 'giftflow_p2p_attribution_failed', 'This contribution could not be recorded.' );
			}
		);

		$result = CheckoutExtension::bind( $this->extension(), array( 'donation_id' => $donation_id ) );

		$this->assertWPError( $result );
		$this->assertSame( 'giftflow_p2p_attribution_failed', $result->get_error_code() );
		$this->assertSame( array(), $this->bound_calls );
	}

	/**
	 * The validation filter receives the extension and binding facts it needs to
	 * decide, and a non-true, non-error result is refused rather than trusted.
	 */
	public function test_bind_requires_true_from_validation(): void {
		$donation_id = (int) self::factory()->post->create( array( 'post_type' => 'donation' ) );
		$seen        = array();

		$this->on_bind(
			static function ( $result, $extension, $binding ) use ( &$seen ) {
				$seen = array(
					'result'    => $result,
					'extension' => $extension,
					'binding'   => $binding,
				);

				return 'yes';
			}
		);

		$outcome = CheckoutExtension::bind(
			$this->extension(),
			array(
				'donation_id' => $donation_id,
				'gateway' => 'paypal',
			)
		);

		$this->assertWPError( $outcome );
		$this->assertSame( 'giftflow_checkout_extension_binding_invalid', $outcome->get_error_code() );
		$this->assertTrue( $seen['result'] );
		$this->assertSame( 'attempt-key-0001', $seen['extension']['attempt_key'] );
		$this->assertSame( 'paypal', $seen['binding']['gateway'] );
		$this->assertSame( array(), $this->bound_calls );
	}

	/**
	 * A binding without a donation cannot be recorded, so it fails closed.
	 */
	public function test_bind_rejects_missing_donation(): void {
		$result = CheckoutExtension::bind( $this->extension(), array( 'donation_id' => 0 ) );

		$this->assertWPError( $result );
		$this->assertSame( 'giftflow_checkout_extension_binding_invalid', $result->get_error_code() );
	}

	/**
	 * Consumers compile against this constant, so it must stay a positive integer.
	 */
	public function test_contract_version_is_published(): void {
		$this->assertSame( 1, CheckoutExtension::P2P_CONTRACT_VERSION );
	}
}
