<?php
/**
 * Credential validator tests.
 *
 * @package GiftFlow
 */

namespace GiftFlow\Tests\Unit;

use GiftFlow\Stripe\StripeCredentialsValidator;
use GiftFlow\Stripe\StripeErrorCodes;
use GiftFlow\Tests\Support\UnitTestCase;
use GiftFlow\Vendor\Stripe\Exception\ApiConnectionException;
use GiftFlow\Vendor\Stripe\Exception\AuthenticationException;
use GiftFlow\Vendor\Stripe\Exception\InvalidRequestException;
use GiftFlow\Vendor\Stripe\Exception\PermissionException;
use GiftFlow\Vendor\Stripe\Exception\RateLimitException;

/**
 * Key shape, profile consistency and permission diagnostics.
 */
class CredentialsValidatorTest extends UnitTestCase {

	/**
	 * Missing credentials are reported before any network access.
	 *
	 * @return void
	 */
	public function test_missing_credentials(): void {
		$result = $this->fake_validator()->validate( 'sandbox', array() );

		$this->assertFalse( $result['valid'] );
		$this->assertSame( StripeErrorCodes::MISSING_CREDENTIALS, $result['errors'][0]['code'] );
		$this->assertSame( 0, $this->ledger->count_calls( 'retrieve' ), 'Shape errors must not reach Stripe.' );
	}

	/**
	 * A blank field is reported as missing rather than malformed.
	 *
	 * @return void
	 */
	public function test_missing_publishable_key(): void {
		$result = $this->fake_validator()->validate(
			'sandbox',
			array(
				'publishable_key' => '',
				'secret_key'      => 'sk_test_abc',
			)
		);

		$this->assertFalse( $result['valid'] );
		$this->assertSame( StripeErrorCodes::MISSING_CREDENTIALS, $result['errors'][0]['code'] );
	}

	/**
	 * A blank secret key is reported as missing rather than malformed.
	 *
	 * @return void
	 */
	public function test_missing_secret_key(): void {
		$result = $this->fake_validator()->validate(
			'sandbox',
			array(
				'publishable_key' => 'pk_test_abc',
				'secret_key'      => '',
			)
		);

		$this->assertFalse( $result['valid'] );
		$this->assertSame( StripeErrorCodes::MISSING_CREDENTIALS, $result['errors'][0]['code'] );
	}

	/**
	 * A partial credential set is accepted when completeness is not required.
	 *
	 * @return void
	 */
	public function test_publishable_key_is_optional_when_not_required(): void {
		$result = $this->fake_validator()->validate_prefixes(
			'sandbox',
			array(
				'publishable_key' => '',
				'secret_key'      => 'sk_test_abc',
			),
			false
		);

		$this->assertTrue( $result['valid'] );
	}

	/**
	 * A single supplied credential is validated without demanding its pair.
	 *
	 * This is the shape the panel's credential route uses.
	 *
	 * @return void
	 */
	public function test_single_field_prefix_is_validated_when_not_required(): void {
		$validator = $this->fake_validator();

		$wrong = $validator->validate_prefixes( 'sandbox', array( 'publishable_key' => 'pk_live_wrong' ), false );

		$this->assertFalse( $wrong['valid'] );
		$this->assertSame( StripeErrorCodes::INVALID_PUBLISHABLE_KEY, $wrong['errors'][0]['code'] );

		$right = $validator->validate_prefixes( 'sandbox', array( 'publishable_key' => 'pk_test_right' ), false );

		$this->assertTrue( $right['valid'] );
		$this->assertSame( array(), $right['errors'] );

		$empty = $validator->validate_prefixes( 'sandbox', array( 'secret_key' => '' ), false );

		$this->assertFalse( $empty['valid'] );
		$this->assertSame( StripeErrorCodes::MISSING_CREDENTIALS, $empty['errors'][0]['code'] );
	}

	/**
	 * Prefixes must match the profile being edited.
	 *
	 * @return void
	 */
	public function test_prefix_mismatch_is_rejected_without_network_access(): void {
		$validator = $this->fake_validator();

		$live_publishable = $validator->validate(
			'sandbox',
			array(
				'publishable_key' => 'pk_live_abc',
				'secret_key'      => 'sk_test_abc',
			)
		);

		$this->assertFalse( $live_publishable['valid'] );
		$this->assertSame( StripeErrorCodes::INVALID_PUBLISHABLE_KEY, $live_publishable['errors'][0]['code'] );

		$live_secret = $validator->validate(
			'sandbox',
			array(
				'publishable_key' => 'pk_test_abc',
				'secret_key'      => 'sk_live_abc',
			)
		);

		$this->assertFalse( $live_secret['valid'] );
		$this->assertSame( StripeErrorCodes::INVALID_SECRET_KEY, $live_secret['errors'][0]['code'] );

		$this->assertSame( 0, $this->ledger->count_calls( 'retrieve' ), 'Shape errors must not reach Stripe.' );
	}

	/**
	 * Restricted keys are recognised.
	 *
	 * @return void
	 */
	public function test_restricted_key_detection(): void {
		$this->assertTrue( StripeCredentialsValidator::is_restricted_key( 'rk_test_abc' ) );
		$this->assertTrue( StripeCredentialsValidator::is_restricted_key( 'rk_live_abc' ) );
		$this->assertFalse( StripeCredentialsValidator::is_restricted_key( 'sk_test_abc' ) );
	}

	/**
	 * A valid pair authenticates and reports the account context.
	 *
	 * @return void
	 */
	public function test_valid_pair_authenticates(): void {
		$result = $this->fake_validator()->validate(
			'live',
			array(
				'publishable_key' => 'pk_live_abc',
				'secret_key'      => 'sk_live_abc',
			)
		);

		$this->assertTrue( $result['valid'] );
		$this->assertSame( 'acct_test_1', $result['account_id'] );
		$this->assertSame( \GiftFlow\Stripe\StripeClientFactory::sdk_version(), $result['api_version'] );
		$this->assertSame( array(), $result['errors'] );
	}

	/**
	 * A missing account identity fails closed.
	 *
	 * @return void
	 */
	public function test_unconfirmed_identity_fails_closed(): void {
		$this->ledger->account = array( 'object' => 'account' );

		$validator = $this->fake_validator();

		$sandbox = $validator->validate(
			'sandbox',
			array(
				'publishable_key' => 'pk_test_abc',
				'secret_key'      => 'sk_test_abc',
			)
		);

		$this->assertFalse( $sandbox['valid'] );
		$this->assertSame( StripeErrorCodes::SANDBOX_IDENTITY_UNCONFIRMED, $sandbox['errors'][0]['code'] );

		$live = $validator->validate(
			'live',
			array(
				'publishable_key' => 'pk_live_abc',
				'secret_key'      => 'sk_live_abc',
			)
		);

		$this->assertFalse( $live['valid'] );
		$this->assertSame( StripeErrorCodes::AUTHENTICATION_FAILED, $live['errors'][0]['code'] );
	}

	/**
	 * API failures map to stable diagnostic codes.
	 *
	 * @dataProvider api_failure_matrix
	 *
	 * @param \Exception $exception Raised by the account lookup.
	 * @param string     $expected  Expected error code.
	 * @return void
	 */
	public function test_api_failures_are_classified( \Exception $exception, string $expected ): void {
		$this->ledger->account_exception = $exception;

		$result = $this->fake_validator()->validate(
			'sandbox',
			array(
				'publishable_key' => 'pk_test_abc',
				'secret_key'      => 'sk_test_abc',
			)
		);

		$this->assertFalse( $result['valid'] );
		$this->assertSame( $expected, $result['errors'][0]['code'] );
	}

	/**
	 * Exception fixtures.
	 *
	 * @return array<string, array<int, mixed>>
	 */
	public function api_failure_matrix(): array {
		return array(
			'invalid api key'        => array(
				AuthenticationException::factory( 'Invalid API Key provided', 401, null, null, null, 'api_key_invalid' ),
				StripeErrorCodes::AUTHENTICATION_FAILED,
			),
			'expired api key'        => array(
				AuthenticationException::factory( 'Expired API Key', 401, null, null, null, 'api_key_expired' ),
				StripeErrorCodes::AUTHENTICATION_FAILED,
			),
			'permission denied'      => array(
				PermissionException::factory( 'You do not have permission', 403, null, null, null, 'permission_denied' ),
				StripeErrorCodes::INSUFFICIENT_PERMISSIONS,
			),
			'rate limited'           => array(
				RateLimitException::factory( 'Too many requests', 429, null, null, null, 'rate_limit' ),
				StripeErrorCodes::TEMPORARY_STRIPE_ERROR,
			),
			'connection failure'     => array(
				ApiConnectionException::factory( 'Could not connect', null, null, null, null, null ),
				StripeErrorCodes::TEMPORARY_STRIPE_ERROR,
			),
			'server error'           => array(
				InvalidRequestException::factory( 'Server error', 500, null, null, null, 'server_error' ),
				StripeErrorCodes::TEMPORARY_STRIPE_ERROR,
			),
			'unknown client error'   => array(
				InvalidRequestException::factory( 'Bad request', 400, null, null, null, 'invalid_request' ),
				StripeErrorCodes::TEMPORARY_STRIPE_ERROR,
			),
		);
	}

	/**
	 * Classify_api_error prefers the Stripe error code over the HTTP status.
	 *
	 * @return void
	 */
	public function test_classify_prefers_stripe_code(): void {
		$exception = InvalidRequestException::factory( 'Invalid API Key provided', 401, null, null, null, 'api_key_invalid' );

		$this->assertSame(
			StripeErrorCodes::AUTHENTICATION_FAILED,
			StripeCredentialsValidator::classify_api_error( $exception )
		);
	}

	/**
	 * An unusable secret key never reaches Stripe.
	 *
	 * @return void
	 */
	public function test_authenticate_rejects_unusable_keys(): void {
		$result = $this->fake_validator()->authenticate( 'sandbox', 'masked…1234' );

		$this->assertFalse( $result['valid'] );
		$this->assertSame( StripeErrorCodes::INVALID_SECRET_KEY, $result['code'] );
		$this->assertSame( 0, $this->ledger->count_calls( 'retrieve' ) );
	}
}
