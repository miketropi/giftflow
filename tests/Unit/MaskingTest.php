<?php
/**
 * Masking and error code tests.
 *
 * @package GiftFlow
 */

namespace GiftFlow\Tests\Unit;

use GiftFlow\Stripe\StripeErrorCodes;
use GiftFlow\Stripe\StripeMasking;
use GiftFlow\Tests\Support\UnitTestCase;

/**
 * Credential masking never reveals stored material.
 */
class MaskingTest extends UnitTestCase {

	/**
	 * Empty values stay empty.
	 *
	 * @return void
	 */
	public function test_empty_values_stay_empty(): void {
		$this->assertSame( '', StripeMasking::mask( '' ) );
		$this->assertSame( '', StripeMasking::mask( null ) );
		$this->assertSame( '', StripeMasking::mask( array() ) );
		$this->assertSame( '', StripeMasking::mask_webhook_secret( '' ) );
	}

	/**
	 * A masked secret never contains the full credential.
	 *
	 * @return void
	 */
	public function test_masked_secret_hides_material(): void {
		$secret = 'sk_test_51AbCdEfGhIjKlMnOpQrStUvWxYz0123456789';
		$masked = StripeMasking::mask( $secret );

		$this->assertNotSame( $secret, $masked );
		$this->assertStringNotContainsString( $secret, $masked );
		$this->assertStringStartsWith( 'sk_test_', $masked );
		$this->assertStringContainsString( '…', $masked );
		$this->assertStringEndsWith( substr( $secret, -4 ), $masked );
		$this->assertLessThan( strlen( $secret ), strlen( $masked ) );
	}

	/**
	 * Very short values are fully masked.
	 *
	 * @return void
	 */
	public function test_short_values_are_fully_masked(): void {
		$this->assertSame( '********', StripeMasking::mask( 'sk_test_' ) );
	}

	/**
	 * Webhook secrets are masked with their canonical prefix.
	 *
	 * @return void
	 */
	public function test_webhook_secret_masking(): void {
		$secret = 'whsec_abcdefghijklmnop';
		$masked = StripeMasking::mask_webhook_secret( $secret );

		$this->assertSame( 'whsec_…' . substr( $secret, -4 ), $masked );
		$this->assertStringNotContainsString( $secret, $masked );
	}

	/**
	 * Masked values are recognised so they can never be re-saved.
	 *
	 * @return void
	 */
	public function test_is_masked(): void {
		$this->assertTrue( StripeMasking::is_masked( 'sk_test_…3456' ) );
		$this->assertFalse( StripeMasking::is_masked( 'sk_test_3456' ) );
	}

	/**
	 * Redaction collapses whitespace and truncates after a prefix.
	 *
	 * @return void
	 */
	public function test_redact(): void {
		$redacted = StripeMasking::redact( "line one\nline two sk_test_secretvalue", 'sk_test_' );

		$this->assertStringNotContainsString( "\n", $redacted );
		$this->assertStringNotContainsString( 'secretvalue', $redacted );
		$this->assertStringContainsString( 'sk_test_', $redacted );
	}
}
