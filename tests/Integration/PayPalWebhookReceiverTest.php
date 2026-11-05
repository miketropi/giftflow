<?php
/**
 * PayPal webhook receiver integration tests.
 *
 * @package GiftFlow
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort -- Compact property type documentation is intentional in tests.
// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- Test comparison only.
// phpcs:disable Squiz.Commenting.FunctionComment.MissingParamTag -- PHPUnit helper summaries document their focused test purpose.
// phpcs:disable Universal.NamingConventions.NoReservedKeywordParameterNames.resourceFound -- Resource mirrors the PayPal webhook payload key.
namespace GiftFlow\Tests\Integration;

use GiftFlow\Core\Donation_Event_History;
use GiftFlow\PayPal\PayPalConnectionRepository;
use GiftFlow\PayPal\PayPalErrorCodes;
use GiftFlow\PayPal\PayPalWebhookReceiver;

/** Exercises receive() directly while intercepting all PayPal HTTP. */
final class PayPalWebhookReceiverTest extends \WP_UnitTestCase {
	/** @var PayPalConnectionRepository */ private $repository;
	/** @var array<string,bool> */ private $verification = array();

	/** Configure profiles and tables. @return void */
	protected function setUp(): void {
		parent::setUp();
		delete_option( PayPalConnectionRepository::PAYMENT_OPTION );
		Donation_Event_History::create_table();
		$this->repository = new PayPalConnectionRepository();
		$options = array(
			'paypal' => array(
				'paypal_mode' => 'sandbox',
				'paypal_webhook_enabled' => '1',
			),
		);
		update_option( PayPalConnectionRepository::PAYMENT_OPTION, $options );
		foreach ( array( 'sandbox', 'live' ) as $environment ) {
			$this->repository->update_credentials(
				$environment,
				array(
					'client_id' => $environment . '-client',
					'client_secret' => $environment . '-secret',
				)
			);
			$app = $environment . '-app';
			$this->repository->update_profile(
				$environment,
				array(
					'webhook_id'           => 'WH-' . $environment,
					'paypal_app_id'        => $app,
					'binding_fingerprint'  => $this->repository->binding_fingerprint( $environment, $app ),
				)
			);
		}
		add_filter( 'pre_http_request', array( $this, 'transport' ), 10, 3 );
	}

	/** Remove HTTP filter. @return void */
	protected function tearDown(): void {
		remove_filter( 'pre_http_request', array( $this, 'transport' ), 10 );
		delete_option( PayPalConnectionRepository::PAYMENT_OPTION );
		parent::tearDown(); }

	/** Live verification updates only the Live-bound duplicate and duplicate delivery is idempotent. @return void */
	public function testLiveDeliveryIsIsolatedAndDuplicateIsIdempotent(): void {
		$live = $this->donation( 'live', 'CAP-SAME' );
		$sandbox = $this->donation( 'sandbox', 'CAP-SAME' );
		$this->verification = array(
			'live' => true,
			'sandbox' => false,
		);
		$payload = $this->payload( 'PAYMENT.CAPTURE.COMPLETED', array( 'id' => 'CAP-SAME' ), 'EVT-LIVE' );
		$receiver = new PayPalWebhookReceiver( $this->repository );
		$first = $receiver->receive( $payload, $this->headers() );
		$history = count( Donation_Event_History::get_by_donation( $live ) );
		delete_post_meta( $live, '_paypal_binding_fingerprint' );
		$second = $receiver->receive( $payload, $this->headers() );
		$this->assertSame( 200, $first['status'] );
		$this->assertTrue( $first['handled'] );
		$this->assertSame( 'completed', get_post_meta( $live, '_status', true ) );
		$this->assertSame( '', get_post_meta( $sandbox, '_status', true ) );
		$this->assertSame( 'live', get_post_meta( $live, '_paypal_environment', true ) );
		$this->assertGreaterThan( 0, $this->repository->get_profile( 'live' )['last_verified_webhook_at'] );
		$this->assertSame( 0, $this->repository->get_profile( 'sandbox' )['last_verified_webhook_at'] );
		$this->assertSame( 200, $second['status'] );
		$this->assertFalse( $second['handled'] );
		$this->assertSame( $history, count( Donation_Event_History::get_by_donation( $live ) ) );
	}

	/** Receiver rejects missing headers and failed or ambiguous verification. @return void */
	public function testVerificationFailuresHaveSpecifiedStatuses(): void {
		$receiver = new PayPalWebhookReceiver( $this->repository );
		$payload = $this->payload( 'CHECKOUT.ORDER.APPROVED', array(), 'EVT-V' );
		$this->assertSame( 400, $receiver->receive( $payload, array() )['status'] );
		$this->verification = array(
			'live' => false,
			'sandbox' => false,
		);
		$this->assertSame( 401, $receiver->receive( $payload, $this->headers() )['status'] );
		$this->verification = array(
			'live' => true,
			'sandbox' => true,
		);
		$this->assertSame( 400, $receiver->receive( $payload, $this->headers() )['status'] );
	}

	/** Mismatch, unknown and checkout events never mutate donations. @return void */
	public function testNoMutationEventCases(): void {
		$donation = $this->donation( 'sandbox', 'CAP-X' );
		$before = serialize( get_post_meta( $donation ) );
		$this->verification = array(
			'live' => true,
			'sandbox' => false,
		);
		$result = ( new PayPalWebhookReceiver( $this->repository ) )->receive( $this->payload( 'PAYMENT.CAPTURE.COMPLETED', array( 'id' => 'CAP-X' ), 'EVT-M' ), $this->headers() );
		$this->assertSame( 200, $result['status'] );
		$this->assertSame( $before, serialize( get_post_meta( $donation ) ) );
		foreach ( array( 'SOME.UNKNOWN.EVENT', 'CHECKOUT.ORDER.APPROVED', 'CHECKOUT.ORDER.COMPLETED' ) as $type ) {
			$result = ( new PayPalWebhookReceiver( $this->repository ) )->receive( $this->payload( $type, array(), 'EVT-' . md5( $type ) ), $this->headers() );
			$this->assertSame( 200, $result['status'] );
			$this->assertSame( 'SOME.UNKNOWN.EVENT' === $type ? false : true, $result['handled'] );
		}
	}

	/** An unstoreable empty event claim returns 500 without mutation. @return void */
	public function testClaimStorageFailureReturns500(): void {
		$donation = $this->donation( 'live', 'CAP-FAIL' );
		$before = serialize( get_post_meta( $donation ) );
		$this->verification = array(
			'live' => true,
			'sandbox' => false,
		);
		$result = ( new PayPalWebhookReceiver( $this->repository ) )->receive( $this->payload( 'PAYMENT.CAPTURE.COMPLETED', array( 'id' => 'CAP-FAIL' ), '' ), $this->headers() );
		$this->assertSame( 500, $result['status'] );
		$this->assertSame( PayPalErrorCodes::WEBHOOK_CLAIM_STORAGE_FAILED, $result['code'] );
		$this->assertSame( $before, serialize( get_post_meta( $donation ) ) );
	}

	/** Create bound donation. @param string $environment Environment. @param string $capture Capture. @return int */
	private function donation( string $environment, string $capture ): int {
		$id = self::factory()->post->create(
			array(
				'post_type' => 'donation',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $id, '_payment_status', 'pending' );
		update_post_meta( $id, '_transaction_id', $capture );
		update_post_meta( $id, '_paypal_environment', $environment );
		update_post_meta( $id, '_paypal_binding_fingerprint', $this->repository->get_profile( $environment )['binding_fingerprint'] );
		return $id;
	}

	/** Build payload. @param string $type Type. @param array $resource Resource. @param string $id Event id. @return string */
	private function payload( string $type, array $resource, string $id ): string {
		return wp_json_encode(
			array(
				'id' => $id,
				'event_type' => $type,
				'resource' => $resource,
			)
		); }
	/** Complete transmission headers. @return array */
	private function headers(): array {
		return array(
			'transmission_id'   => 'T',
			'transmission_time' => gmdate( 'c' ),
			'transmission_sig'  => 'S',
			'cert_url'          => 'https://paypal.test/cert',
			'auth_algo'         => 'SHA256withRSA',
		);
	}
	/** Fake OAuth and verification. @param mixed $pre Preempt. @param array $args Args. @param string $url URL. @return array */
	public function transport( $pre, array $args, string $url ): array {
		$environment = false !== strpos( $url, 'sandbox' ) ? 'sandbox' : 'live';
		if ( false !== strpos( $url, '/oauth2/token' ) ) {
			return array(
				'response' => array( 'code' => 200 ),
				'body'     => wp_json_encode(
					array(
						'access_token' => 'token',
						'expires_in' => 3600,
						'app_id' => $environment . '-app',
					)
				),
			);
		}
		$status = ! empty( $this->verification[ $environment ] ) ? 'SUCCESS' : 'FAILURE';
		return array(
			'response' => array( 'code' => 200 ),
			'body' => wp_json_encode( array( 'verification_status' => $status ) ),
		);
	}
}
