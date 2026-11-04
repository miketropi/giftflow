<?php
/**
 * Error code registry tests.
 *
 * @package GiftFlow
 */

namespace GiftFlow\Tests\Unit;

use GiftFlow\Stripe\StripeErrorCodes;
use GiftFlow\Tests\Support\UnitTestCase;

/**
 * Error codes stay stable, sanitized and redacted.
 */
class ErrorCodesTest extends UnitTestCase {

	/**
	 * Registered codes are unique and non-empty.
	 *
	 * @return void
	 */
	public function test_codes_are_unique(): void {
		$codes = StripeErrorCodes::all();

		$this->assertNotEmpty( $codes );
		$this->assertSame( array_values( array_unique( $codes ) ), array_values( $codes ) );

		foreach ( $codes as $code ) {
			$this->assertNotSame( '', trim( $code ) );
		}
	}

	/**
	 * Unknown or injected codes are discarded.
	 *
	 * @return void
	 */
	public function test_sanitize_rejects_unknown_codes(): void {
		$this->assertSame( '', StripeErrorCodes::sanitize( 'NOT_A_CODE' ) );
		$this->assertSame( '', StripeErrorCodes::sanitize( 'EVENT_DRIFT; DROP TABLE' ) );
		$this->assertSame( '', StripeErrorCodes::sanitize( array( 'EVENT_DRIFT' ) ) );
		$this->assertSame( '', StripeErrorCodes::sanitize( null ) );
		$this->assertSame( StripeErrorCodes::EVENT_DRIFT, StripeErrorCodes::sanitize( StripeErrorCodes::EVENT_DRIFT ) );
	}

	/**
	 * Every code has an actionable message that does not leak the code or payloads.
	 *
	 * @return void
	 */
	public function test_messages_are_actionable_and_redacted(): void {
		foreach ( StripeErrorCodes::all() as $code ) {
			$message = StripeErrorCodes::message( $code );

			$this->assertNotSame( '', $message );
			$this->assertStringNotContainsString( $code, $message );
			$this->assertStringNotContainsString( 'Exception', $message );
			// No credential material: key prefixes are explained, full keys never are.
			$this->assertSame( 0, preg_match( '/\b(?:sk|rk|pk)_(?:test|live)_[A-Za-z0-9]{6,}/', $message ) );
			$this->assertSame( 0, preg_match( '/whsec_[A-Za-z0-9]{6,}/', $message ) );
		}
	}

	/**
	 * An unknown code still yields a safe message.
	 *
	 * @return void
	 */
	public function test_unknown_code_message_is_generic(): void {
		$message = StripeErrorCodes::message( 'TOTALLY_UNKNOWN_CODE' );

		$this->assertNotSame( '', $message );
		$this->assertStringNotContainsString( 'TOTALLY_UNKNOWN_CODE', $message );
	}
}
