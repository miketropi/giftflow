<?php
/**
 * PayPal HTTP client and credential tests.
 *
 * @package GiftFlow
 */

namespace GiftFlow\Tests\Unit;

use GiftFlow\PayPal\PayPalCredentialsValidator;
use GiftFlow\PayPal\PayPalErrorCodes;
use GiftFlow\PayPal\PayPalTokenCache;
use GiftFlow\Tests\Support\PayPalUnitTestCase;

/** Tests real client behavior through the in-memory transport. */
final class PayPalHttpTest extends PayPalUnitTestCase {

	/**
	 * Client reuses token and targets its environment host.
	 *
	 * @return void
	 */
	public function testClientReusesTokenAndTargetsItsEnvironmentHost(): void {
		$client = $this->client( 'sandbox', 'client-a' );
		$this->assertTrue( $client->create_order( array( 'intent' => 'CAPTURE' ) )['ok'] );
		$this->assertTrue( $client->create_product( array( 'name' => 'Donation' ) )['ok'] );
		$this->assertSame( 1, $this->ledger->oauth_calls() );
		foreach ( $this->http->urls() as $url ) {
			$this->assertStringStartsWith( 'https://api.sandbox.paypal.com/', $url );
		}
	}

	/**
	 * Token cache separates environment and client id.
	 *
	 * @return void
	 */
	public function testTokenCacheSeparatesEnvironmentAndClientId(): void {
		$this->client( 'sandbox', 'client-a' )->access_token();
		$this->client( 'sandbox', 'client-b' )->access_token();
		$this->client( 'live', 'client-a' )->access_token();
		$this->assertSame( 3, $this->ledger->oauth_calls() );
		$this->assertNotSame( PayPalTokenCache::key( 'sandbox', 'client-a' ), PayPalTokenCache::key( 'live', 'client-a' ) );
		$this->assertNotSame( PayPalTokenCache::key( 'sandbox', 'client-a' ), PayPalTokenCache::key( 'sandbox', 'client-b' ) );
	}

	/**
	 * Webhook patch uses only exact replace operations.
	 *
	 * @return void
	 */
	public function testWebhookPatchUsesOnlyExactReplaceOperations(): void {
		$client = $this->client();
		$created = $client->create_webhook( 'https://example.test/hook', array( 'B', 'A' ) );
		$this->assertTrue( $client->update_webhook( $created['body']['id'], 'https://example.test/new', array( 'A' ) )['ok'] );
		$calls = $this->http->calls();
		$patch = $calls[ count( $calls ) - 1 ];
		$this->assertSame( 'PATCH', $patch['method'] );
		$this->assertSame( array( '/url', '/event_types' ), array_column( $patch['body'], 'path' ) );
		$this->assertSame( array( 'replace', 'replace' ), array_column( $patch['body'], 'op' ) );
	}

	/**
	 * Validator rejects incomplete and masked credentials without http.
	 *
	 * @return void
	 */
	public function testValidatorRejectsIncompleteAndMaskedCredentialsWithoutHttp(): void {
		$validator = new PayPalCredentialsValidator( $this->http );
		$missing = $validator->validate(
			'sandbox',
			array(
				'client_id' => '',
				'client_secret' => '',
			)
		);
		$masked = $validator->validate(
			'sandbox',
			array(
				'client_id' => 'abc…xyz',
				'client_secret' => 'secret',
			)
		);
		$this->assertFalse( $missing['valid'] );
		$this->assertSame( PayPalErrorCodes::MISSING_CREDENTIALS, $missing['errors'][0]['code'] );
		$this->assertFalse( $masked['valid'] );
		$this->assertSame( PayPalErrorCodes::INVALID_CREDENTIAL_VALUE, $masked['errors'][0]['code'] );
		$this->assertSame( 0, count( $this->http->calls() ) );
	}

	/**
	 * Validator returns app id without returning token.
	 *
	 * @return void
	 */
	public function testValidatorReturnsAppIdWithoutReturningToken(): void {
		$validator = new PayPalCredentialsValidator( $this->http );
		$result = $validator->validate(
			'sandbox',
			array(
				'client_id' => 'client',
				'client_secret' => 'secret',
			)
		);
		$this->assertTrue( $result['valid'] );
		$this->assertSame( 'sandbox', $result['app_id'] );
		$this->assertArrayNotHasKey( 'access_token', $result );
		$this->assertStringNotContainsString( 'token-', wp_json_encode( $result ) );
	}

	/**
	 * Validator maps authentication and temporary failures.
	 *
	 * @return void
	 */
	public function testValidatorMapsAuthenticationAndTemporaryFailures(): void {
		$this->http->queue(
			'POST',
			'/v1/oauth2/token',
			array(
				'response' => array( 'code' => 401 ),
				'body' => '{}',
			)
		);
		$validator = new PayPalCredentialsValidator( $this->http );
		$unauthorized = $validator->validate(
			'sandbox',
			array(
				'client_id' => 'client',
				'client_secret' => 'secret',
			)
		);
		$this->assertSame( PayPalErrorCodes::AUTHENTICATION_FAILED, $unauthorized['errors'][0]['code'] );
		$this->http->queue(
			'POST',
			'/v1/oauth2/token',
			array(
				'response' => array( 'code' => 503 ),
				'body' => '{}',
			)
		);
		$temporary = $validator->validate(
			'sandbox',
			array(
				'client_id' => 'client',
				'client_secret' => 'secret',
			)
		);
		$this->assertSame( PayPalErrorCodes::TEMPORARY_PAYPAL_ERROR, $temporary['errors'][0]['code'] );
	}
}
