<?php
/**
 * Donation ↔ Stripe environment binding.
 *
 * A donation stores the Stripe environment, account id and binding fingerprint
 * it was created in. Records created before environments were recorded adopt the
 * active environment at their next authenticated operation; a record whose stored
 * environment contradicts the operation is rejected instead of being retried
 * against the other profile.
 *
 * @package GiftFlow
 * @subpackage Stripe
 */

namespace GiftFlow\Stripe;

use GiftFlow\Core\InstallationIdentity;
use GiftFlow\Core\Logger as Giftflow_Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reads, validates and writes Stripe environment identity on donations.
 */
final class StripeDonationBinding {

	/**
	 * Donation meta key holding the Stripe environment.
	 *
	 * @var string
	 */
	public const META_ENVIRONMENT = '_stripe_environment';

	/**
	 * Donation meta key holding the Stripe account id.
	 *
	 * @var string
	 */
	public const META_ACCOUNT = '_stripe_account_id';

	/**
	 * Donation meta key holding the environment binding fingerprint.
	 *
	 * @var string
	 */
	public const META_BINDING = '_stripe_binding_fingerprint';

	/**
	 * Repository.
	 *
	 * @var StripeConnectionRepository
	 */
	private StripeConnectionRepository $repository;

	/**
	 * Installation identity.
	 *
	 * @var InstallationIdentity
	 */
	private InstallationIdentity $identity;

	/**
	 * Constructor.
	 *
	 * @param StripeConnectionRepository|null $repository Repository.
	 * @param InstallationIdentity|null       $identity   Installation identity.
	 */
	public function __construct( ?StripeConnectionRepository $repository = null, ?InstallationIdentity $identity = null ) {
		$this->repository = $repository ?? new StripeConnectionRepository();
		$this->identity   = $identity ?? $this->repository->identity();
	}

	/**
	 * Environment stored on a donation, or an empty string.
	 *
	 * @param int $donation_id Donation post id.
	 * @return string
	 */
	public function stored_environment( int $donation_id ): string {
		$stored = get_post_meta( $donation_id, self::META_ENVIRONMENT, true );

		return is_string( $stored ) && StripeEnvironment::is_valid( $stored ) ? $stored : '';
	}

	/**
	 * Environment to use for a donation's next operation.
	 *
	 * Legacy records without a marker adopt the active environment here and keep
	 * it, so a later mode change cannot move them.
	 *
	 * @param int $donation_id Donation post id.
	 * @return string
	 */
	public function resolve_environment( int $donation_id ): string {
		$stored = $this->stored_environment( $donation_id );

		if ( '' !== $stored ) {
			return $stored;
		}

		$environment = $this->repository->get_active_environment();
		$this->persist( $donation_id, $environment );

		Giftflow_Logger::info(
			'stripe.donation.environment_resolved',
			array(
				'donation_id' => $donation_id,
				'environment' => $environment,
				'gateway'     => 'stripe',
			),
			'stripe'
		);

		return $environment;
	}

	/**
	 * Whether a donation may be operated on in the given environment.
	 *
	 * @param int    $donation_id Donation post id.
	 * @param string $environment Environment id.
	 * @return bool
	 */
	public function matches( int $donation_id, string $environment ): bool {
		$stored = $this->stored_environment( $donation_id );

		return '' === $stored || $stored === $environment;
	}

	/**
	 * Environment binding fingerprint of a donation.
	 *
	 * @param int $donation_id Donation post id.
	 * @return string
	 */
	public function stored_fingerprint( int $donation_id ): string {
		$stored = get_post_meta( $donation_id, self::META_BINDING, true );

		return is_string( $stored ) ? $stored : '';
	}

	/**
	 * Write environment identity markers to a donation.
	 *
	 * @param int    $donation_id Donation post id.
	 * @param string $environment Environment id.
	 * @param string $account_id  Account id, or '' to use the profile account.
	 * @return void
	 */
	public function persist( int $donation_id, string $environment, string $account_id = '' ): void {
		if ( $donation_id <= 0 ) {
			return;
		}

		$environment = StripeEnvironment::assert_valid( $environment );

		if ( '' === $account_id ) {
			$account_id = $this->expected_account( $environment );
		}

		update_post_meta( $donation_id, self::META_ENVIRONMENT, $environment );

		if ( '' !== $account_id ) {
			update_post_meta( $donation_id, self::META_ACCOUNT, $account_id );
		}

		update_post_meta( $donation_id, self::META_BINDING, $this->identity->fingerprint( $environment, $account_id ) );
	}

	/**
	 * Bind a donation to an environment, adopting unbound legacy records.
	 *
	 * @param int    $donation_id Donation post id.
	 * @param string $environment Environment id.
	 * @param string $account_id  Account id from a verified event, may be empty.
	 * @return bool Whether the donation is bound to the environment.
	 */
	public function bind( int $donation_id, string $environment, string $account_id = '' ): bool {
		$environment = StripeEnvironment::assert_valid( $environment );

		if ( $donation_id <= 0 || 'donation' !== get_post_type( $donation_id ) ) {
			return false;
		}

		$stored = $this->stored_environment( $donation_id );

		if ( '' !== $stored ) {
			if ( $stored !== $environment ) {
				return false;
			}

			$fingerprint = $this->stored_fingerprint( $donation_id );

			if ( '' !== $account_id && '' !== $fingerprint ) {
				return $fingerprint === $this->identity->fingerprint( $environment, $account_id );
			}

			return true;
		}

		// A verified signature uniquely identifies the environment, so an unbound
		// legacy donation adopts it rather than being inferred from an object id.
		$this->persist( $donation_id, $environment, $account_id );

		return true;
	}

	/**
	 * Account id configured for an environment.
	 *
	 * @param string $environment Environment id.
	 * @return string
	 */
	public function expected_account( string $environment ): string {
		$profile = $this->repository->get_profile( $environment );

		return is_string( $profile['stripe_account_id'] ) ? $profile['stripe_account_id'] : '';
	}

	/**
	 * Metadata attached to Stripe resources for an environment.
	 *
	 * @param string $environment Environment id.
	 * @return array<string, string>
	 */
	public function resource_metadata( string $environment ): array {
		$environment = StripeEnvironment::assert_valid( $environment );
		$account_id  = $this->expected_account( $environment );

		return array(
			'giftflow_environment' => $environment,
			'giftflow_account_id'  => $account_id,
			'giftflow_binding'     => $this->identity->fingerprint( $environment, $account_id ),
		);
	}
}
