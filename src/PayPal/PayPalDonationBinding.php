<?php
/**
 * Donation to PayPal environment binding.
 *
 * @package GiftFlow
 * @subpackage PayPal
 */

namespace GiftFlow\PayPal;

use GiftFlow\Core\InstallationIdentity;
use GiftFlow\Core\Logger as Giftflow_Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Reads, validates and writes PayPal identity on donations. */
final class PayPalDonationBinding {
	public const META_ENVIRONMENT = '_paypal_environment';
	public const META_APP_ID = '_paypal_app_id';
	public const META_BINDING = '_paypal_binding_fingerprint';

	/**
	 * PayPal connection state repository.
	 *
	 * @var PayPalConnectionRepository
	 */
	private PayPalConnectionRepository $repository;

	/**
	 * Installation identity used to derive binding fingerprints.
	 *
	 * @var InstallationIdentity
	 */
	private InstallationIdentity $identity;

	/**
	 * Construct the donation binding service.
	 *
	 * @param PayPalConnectionRepository|null $repository Connection repository, or null for the default.
	 * @param InstallationIdentity|null        $identity   Installation identity, or null to use the repository identity.
	 */
	public function __construct( ?PayPalConnectionRepository $repository = null, ?InstallationIdentity $identity = null ) {
		$this->repository = $repository ?? new PayPalConnectionRepository();
		$this->identity   = $identity ?? $this->repository->identity();
	}

	/**
	 * Read a donation's stored PayPal environment.
	 *
	 * @param int $donation_id Donation post ID.
	 * @return string Stored environment, or an empty string when absent or invalid.
	 */
	public function stored_environment( int $donation_id ): string {
		$stored = get_post_meta( $donation_id, self::META_ENVIRONMENT, true );
		return is_string( $stored ) && PayPalEnvironment::is_valid( $stored ) ? $stored : '';
	}

	/**
	 * Resolve and persist the environment for a legacy donation.
	 *
	 * @param int $donation_id Donation post ID.
	 * @return string Stored or adopted environment.
	 */
	public function resolve_environment( int $donation_id ): string {
		$stored = $this->stored_environment( $donation_id );
		if ( '' !== $stored ) {
			return $stored;
		}
		$environment = $this->repository->get_active_environment();
		$this->persist( $donation_id, $environment );
		Giftflow_Logger::info(
			'paypal.donation.environment_resolved',
			array(
				'donation_id' => $donation_id,
				'environment' => $environment,
				'gateway' => 'paypal',
			),
			'paypal'
		);
		return $environment;
	}

	/**
	 * Determine whether a donation may be used in an environment.
	 *
	 * @param int    $donation_id Donation post ID.
	 * @param string $environment Environment identifier.
	 * @return bool Whether the donation is unbound or matches the environment.
	 */
	public function matches( int $donation_id, string $environment ): bool {
		$stored = $this->stored_environment( $donation_id );
		return '' === $stored || $stored === $environment;
	}

	/**
	 * Read a donation's stored binding fingerprint.
	 *
	 * @param int $donation_id Donation post ID.
	 * @return string Stored fingerprint, or an empty string when absent.
	 */
	public function stored_fingerprint( int $donation_id ): string {
		$stored = get_post_meta( $donation_id, self::META_BINDING, true );
		return is_string( $stored ) ? $stored : '';
	}

	/**
	 * Persist PayPal environment and account binding metadata.
	 *
	 * @param int    $donation_id Donation post ID.
	 * @param string $environment Environment identifier.
	 * @param string $app_id      PayPal app ID, or an empty string to resolve it locally.
	 * @return void
	 */
	public function persist( int $donation_id, string $environment, string $app_id = '' ): void {
		if ( $donation_id <= 0 ) {
			return;
		}
		$environment  = PayPalEnvironment::assert_valid( $environment );
		$app_identity = '' !== trim( $app_id ) ? trim( $app_id ) : $this->expected_app_id( $environment );
		update_post_meta( $donation_id, self::META_ENVIRONMENT, $environment );
		if ( '' !== $app_identity ) {
			update_post_meta( $donation_id, self::META_APP_ID, $app_identity );
		}
		update_post_meta( $donation_id, self::META_BINDING, $this->identity->fingerprint( $environment, $app_identity, 'paypal' ) );
	}

	/**
	 * Bind an unbound donation or validate its existing binding.
	 *
	 * @param int    $donation_id Donation post ID.
	 * @param string $environment Environment identifier.
	 * @param string $app_id      PayPal app ID, when known.
	 * @return bool Whether the donation is bound consistently.
	 */
	public function bind( int $donation_id, string $environment, string $app_id = '' ): bool {
		$environment = PayPalEnvironment::assert_valid( $environment );
		if ( $donation_id <= 0 || 'donation' !== get_post_type( $donation_id ) ) {
			return false;
		}
		$stored = $this->stored_environment( $donation_id );
		if ( '' !== $stored ) {
			if ( $stored !== $environment ) {
				return false;
			}
			$fingerprint = $this->stored_fingerprint( $donation_id );
			if ( '' !== trim( $app_id ) && '' !== $fingerprint ) {
				$app_identity = PayPalEnvironment::app_identity( $environment, '', $app_id );
				return $fingerprint === $this->identity->fingerprint( $environment, $app_identity, 'paypal' );
			}
			return true;
		}
		$this->persist( $donation_id, $environment, $app_id );
		return true;
	}

	/**
	 * Resolve the expected PayPal app identity for an environment.
	 *
	 * @param string $environment Environment identifier.
	 * @return string Stored app identity or deterministic fallback.
	 */
	public function expected_app_id( string $environment ): string {
		$environment = PayPalEnvironment::assert_valid( $environment );
		$profile     = $this->repository->get_profile( $environment );
		$app_id      = isset( $profile['paypal_app_id'] ) && is_string( $profile['paypal_app_id'] ) ? trim( $profile['paypal_app_id'] ) : '';
		$credentials = $this->repository->get_credentials( $environment );
		$client_id   = isset( $credentials['client_id'] ) && is_string( $credentials['client_id'] ) ? $credentials['client_id'] : '';
		return PayPalEnvironment::app_identity( $environment, $client_id, $app_id );
	}

	/**
	 * Build metadata for an environment-bound PayPal resource.
	 *
	 * @param string $environment Environment identifier.
	 * @return array<string,string> Environment, app identity, and binding fingerprint.
	 */
	public function resource_metadata( string $environment ): array {
		$environment = PayPalEnvironment::assert_valid( $environment );
		$app_id      = $this->expected_app_id( $environment );
		return array(
			'giftflow_environment' => $environment,
			'giftflow_app_id'      => $app_id,
			'giftflow_binding'     => $this->identity->fingerprint( $environment, $app_id, 'paypal' ),
		);
	}
}
