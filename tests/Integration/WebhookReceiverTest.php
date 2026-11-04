<?php
/**
 * Stripe webhook receiver integration tests.
 *
 * @package GiftFlow
 */

// phpcs:disable Squiz.Commenting.ClassComment.Missing,Squiz.Commenting.FunctionComment.Missing,Squiz.Commenting.VariableComment.Missing -- PHPUnit test names and typed properties are self-documenting.
// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- Test comparison only.
// phpcs:disable WordPress.NamingConventions.ValidVariableName -- Existing fixture identifiers are retained to keep the test contract readable.
// phpcs:disable Universal.Operators.DisallowShortTernary.Found -- Test fixture intentionally defaults only falsey event identifiers.

namespace GiftFlow\Tests\Integration;

use GiftFlow\Core\Donation_Event_History;
use GiftFlow\Core\Logger;
use GiftFlow\Stripe\StripeConnectionRepository;
use GiftFlow\Stripe\StripeErrorCodes;
use GiftFlow\Stripe\StripeWebhookReceiver;

final class WebhookReceiverTest extends \WP_UnitTestCase {
	private StripeConnectionRepository $repository;
	private StripeWebhookReceiver $receiver;
	private string $sandboxSecret = 'sandbox-signing-secret';
	private string $liveSecret = 'live-signing-secret';

	protected function setUp(): void {
		parent::setUp();
		delete_option( StripeConnectionRepository::PAYMENT_OPTION );
		Donation_Event_History::create_table();
		Logger::create_table();
		$this->repository = new StripeConnectionRepository();
		$this->receiver = new StripeWebhookReceiver( $this->repository );
		update_option(
			StripeConnectionRepository::PAYMENT_OPTION,
			array(
				'stripe_mode' => 'sandbox',
				'stripe' => array( 'stripe_webhook_enabled' => '1' ),
			)
		);
	}

	protected function tearDown(): void {
		delete_option( StripeConnectionRepository::PAYMENT_OPTION );
		parent::tearDown();
	}

	public function testLiveDeliveryProcessesLiveDonationWhileActiveModeIsSandbox(): void {
		$donation = $this->donation( 'live' );
		$payload = $this->payload( $donation, true );
		$this->storeSecret( 'live', $this->liveSecret );
		$result = $this->receiver->receive( $payload, $this->signature( $payload, $this->liveSecret ) );

		$this->assertSame( 200, $result['status'] );
		$this->assertTrue( $result['handled'] );
		$this->assertSame( 'completed', get_post_meta( $donation, '_payment_status', true ) );
		$this->assertSame( 'pi_' . $donation, get_post_meta( $donation, '_transaction_id', true ) );
		$this->assertGreaterThan( 0, $this->repository->get_profile( 'live' )['last_verified_webhook_at'] );
		$this->assertSame( 0, $this->repository->get_profile( 'sandbox' )['last_verified_webhook_at'] );
	}

	public function testLiveModeEventSignedBySandboxIsRejectedWithoutMutation(): void {
		$donation = $this->donation( 'live' );
		$payload = $this->payload( $donation, true );
		$this->storeSecret( 'sandbox', $this->sandboxSecret );
		$before = $this->metaSnapshot( $donation );
		$result = $this->receiver->receive( $payload, $this->signature( $payload, $this->sandboxSecret ) );
		$this->assertSame( 400, $result['status'] );
		$this->assertSame( StripeErrorCodes::WEBHOOK_LIVEMODE_MISMATCH, $result['code'] );
		$this->assertSame( $before, $this->metaSnapshot( $donation ) );
	}

	public function testSandboxEventCannotMutateLiveBoundDonation(): void {
		$donation = $this->donation( 'live' );
		$payload = $this->payload( $donation, false );
		$this->storeSecret( 'sandbox', $this->sandboxSecret );
		$before = $this->metaSnapshot( $donation );
		$result = $this->receiver->receive( $payload, $this->signature( $payload, $this->sandboxSecret ) );
		$this->assertSame( 400, $result['status'] );
		$this->assertSame( StripeErrorCodes::WEBHOOK_BINDING_MISMATCH, $result['code'] );
		$this->assertSame( $before, $this->metaSnapshot( $donation ) );
	}

	public function testInvalidSignatureIsRejectedWithoutMutation(): void {
		$donation = $this->donation( 'sandbox' );
		$this->storeSecret( 'sandbox', $this->sandboxSecret );
		$payload = $this->payload( $donation, false );
		$before = $this->metaSnapshot( $donation );

		$result = $this->receiver->receive( $payload, 't=' . time() . ',v1=invalid' );

		$this->assertSame( 400, $result['status'] );
		$this->assertSame( StripeErrorCodes::WEBHOOK_SIGNATURE_INVALID, $result['code'] );
		$this->assertSame( $before, $this->metaSnapshot( $donation ) );
		$this->assertSame( 0, $this->repository->get_profile( 'sandbox' )['last_verified_webhook_at'] );
	}

	public function testMalformedPayloadIsRejected(): void {
		$this->storeSecret( 'sandbox', $this->sandboxSecret );

		$result = $this->receiver->receive( 'not-json', $this->signature( 'not-json', $this->sandboxSecret ) );

		$this->assertSame( 400, $result['status'] );
		$this->assertSame( StripeErrorCodes::WEBHOOK_SIGNATURE_INVALID, $result['code'] );
	}

	public function testMissingSecretIsRejected(): void {
		$payload = $this->payload( $this->donation( 'sandbox' ), false );
		$result = $this->receiver->receive( $payload, $this->signature( $payload, $this->sandboxSecret ) );
		$this->assertSame( StripeErrorCodes::WEBHOOK_SECRET_MISSING, $result['code'] );
	}

	public function testDisabledWebhookAcknowledgesWithoutMutation(): void {
		$donation = $this->donation( 'sandbox' );
		$stripe = $this->repository->get_stripe_subtree();
		$stripe['stripe_webhook_enabled'] = '0';
		update_option( StripeConnectionRepository::PAYMENT_OPTION, array( 'stripe' => $stripe ) );
		$payload = $this->payload( $donation, false );
		$before = $this->metaSnapshot( $donation );
		$result = $this->receiver->receive( $payload, $this->signature( $payload, $this->sandboxSecret ) );
		$this->assertSame( 200, $result['status'] );
		$this->assertFalse( $result['handled'] );
		$this->assertSame( $before, $this->metaSnapshot( $donation ) );
	}

	public function testDuplicateEventReturnsBeforeSideEffectsAndTimestampUpdate(): void {
		$donation = $this->donation( 'sandbox' );
		$this->storeSecret( 'sandbox', $this->sandboxSecret );
		$payload = $this->payload( $donation, false, 'payment_intent.succeeded', 'evt_duplicate_' . $donation );
		$signature = $this->signature( $payload, $this->sandboxSecret );
		$this->receiver->receive( $payload, $signature );
		$verified = $this->repository->get_profile( 'sandbox' )['last_verified_webhook_at'];
		update_post_meta( $donation, '_payment_status', 'failed' );
		$result = $this->receiver->receive( $payload, $signature );
		$this->assertSame( 200, $result['status'] );
		$this->assertFalse( $result['handled'] );
		$this->assertSame( 'failed', get_post_meta( $donation, '_payment_status', true ) );
		$this->assertSame( $verified, $this->repository->get_profile( 'sandbox' )['last_verified_webhook_at'] );
	}

	public function testUnknownSignedEventIsAcknowledgedWithoutMutationOrVerificationTimestamp(): void {
		$donation = $this->donation( 'sandbox' );
		$this->storeSecret( 'sandbox', $this->sandboxSecret );
		$payload = $this->payload( $donation, false, 'charge.dispute.created' );
		$before = $this->metaSnapshot( $donation );
		$result = $this->receiver->receive( $payload, $this->signature( $payload, $this->sandboxSecret ) );
		$this->assertSame( 200, $result['status'] );
		$this->assertFalse( $result['handled'] );
		$this->assertSame( $before, $this->metaSnapshot( $donation ) );
		$this->assertSame( 0, $this->repository->get_profile( 'sandbox' )['last_verified_webhook_at'] );
	}

	public function testLegacyDonationAdoptsVerifiedEnvironment(): void {
		$donation = $this->donation();
		$this->storeSecret( 'live', $this->liveSecret );
		$payload = $this->payload( $donation, true );
		$result = $this->receiver->receive( $payload, $this->signature( $payload, $this->liveSecret ) );
		$this->assertSame( 200, $result['status'] );
		$this->assertSame( 'live', get_post_meta( $donation, '_stripe_environment', true ) );
	}

	public function testSameSecretForBothEnvironmentsIsAmbiguousAndCannotMutate(): void {
		$this->storeSecret( 'sandbox', $this->sandboxSecret );
		$this->repository->update_credentials( 'live', array( 'webhook_secret' => $this->sandboxSecret ) );
		$donation = $this->donation( 'sandbox' );
		$payload = $this->payload( $donation, false );
		$before = $this->metaSnapshot( $donation );
		$result = $this->receiver->receive( $payload, $this->signature( $payload, $this->sandboxSecret ) );
		$this->assertSame( 400, $result['status'] );
		$this->assertSame( StripeErrorCodes::WEBHOOK_SIGNATURE_AMBIGUOUS, $result['code'] );
		$this->assertSame( $before, $this->metaSnapshot( $donation ) );
	}

	private function donation( string $environment = '' ): int {
		$id = self::factory()->post->create(
			array(
				'post_type' => 'donation',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $id, '_payment_status', 'pending' );
		if ( '' !== $environment ) {
			update_post_meta( $id, '_stripe_environment', $environment );
		}
		return $id;
	}

	private function storeSecret( string $environment, string $secret ): void {
		$this->repository->update_credentials( $environment, array( 'webhook_secret' => $secret ) );
	}

	private function payload( int $donation, bool $livemode, string $type = 'payment_intent.succeeded', string $eventId = '' ): string {
		return wp_json_encode(
			array(
				'id' => $eventId ?: 'evt_' . $donation . '_' . wp_generate_password( 8, false ),
				'object' => 'event',
				'type' => $type,
				'livemode' => $livemode,
				'account' => null,
				'data' => array(
					'object' => array(
						'id' => 'pi_' . $donation,
						'object' => 'payment_intent',
						'metadata' => array( 'donation_id' => (string) $donation ),
						'charges' => array( 'data' => array( array( 'id' => 'ch_' . $donation ) ) ),
					),
				),
			)
		);
	}

	private function signature( string $payload, string $secret ): string {
		$timestamp = time();
		return 't=' . $timestamp . ',v1=' . hash_hmac( 'sha256', $timestamp . '.' . $payload, $secret );
	}

	private function metaSnapshot( int $donation ): string {
		return serialize( get_post_meta( $donation ) );
	}
}
