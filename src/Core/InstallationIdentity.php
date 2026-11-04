<?php
/**
 * Gateway-neutral installation identity for remote resource ownership.
 *
 * A remote resource is only considered plugin-owned when its installation
 * metadata agrees. A copied installation UUID combined with a different
 * canonical host is reported as a clone warning instead of ownership proof.
 *
 * @package GiftFlow
 * @subpackage Core
 */

namespace GiftFlow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stable per-installation identity and derived binding fingerprints.
 */
final class InstallationIdentity {

	/**
	 * Option holding the installation UUID.
	 *
	 * @var string
	 */
	public const UUID_OPTION = 'giftflow_installation_uuid';

	/**
	 * Metadata key marking an endpoint as GiftFlow managed.
	 *
	 * @var string
	 */
	public const META_MANAGED = 'giftflow_managed';

	/**
	 * Metadata key holding the installation UUID.
	 *
	 * @var string
	 */
	public const META_UUID = 'giftflow_installation_uuid';

	/**
	 * Metadata key holding the blog id.
	 *
	 * @var string
	 */
	public const META_BLOG_ID = 'giftflow_blog_id';

	/**
	 * Metadata key holding the Meta environment.
	 *
	 * @var string
	 */
	public const META_ENVIRONMENT = 'giftflow_environment';

	/**
	 * Metadata key holding the canonical site host.
	 *
	 * @var string
	 */
	public const META_SITE_HOST = 'giftflow_site_host';

	/**
	 * Metadata key holding the technical binding fingerprint.
	 *
	 * @var string
	 */
	public const META_BINDING = 'giftflow_binding';

	/**
	 * Ownership classification: the endpoint belongs to this installation.
	 *
	 * @var string
	 */
	public const OWNERSHIP_OWNED = 'owned';

	/**
	 * Ownership classification: metadata comes from a copy of this installation.
	 *
	 * @var string
	 */
	public const OWNERSHIP_CLONE = 'clone';

	/**
	 * Ownership classification: metadata is unknown or belongs to someone else.
	 *
	 * @var string
	 */
	public const OWNERSHIP_FOREIGN = 'foreign';

	/**
	 * Process-wide UUID cache.
	 *
	 * @var string
	 */
	private static $uuid_cache = '';

	/**
	 * Installation UUID, generated once and stored without autoloading.
	 *
	 * @return string
	 */
	public function uuid(): string {
		if ( '' !== self::$uuid_cache ) {
			return self::$uuid_cache;
		}

		$stored = get_option( self::UUID_OPTION, '' );

		if ( is_string( $stored ) && '' !== trim( $stored ) ) {
			self::$uuid_cache = trim( $stored );
			return self::$uuid_cache;
		}

		$generated = function_exists( 'wp_generate_uuid4' ) ? wp_generate_uuid4() : $this->fallback_uuid();

		update_option( self::UUID_OPTION, $generated, false );
		self::$uuid_cache = $generated;

		return self::$uuid_cache;
	}

	/**
	 * Current blog id.
	 *
	 * @return int
	 */
	public function blog_id(): int {
		return (int) get_current_blog_id();
	}

	/**
	 * Canonical site host used for clone detection.
	 *
	 * @return string
	 */
	public function site_host(): string {
		$url  = function_exists( 'home_url' ) ? home_url() : '';
		$host = is_string( $url ) ? wp_parse_url( $url, PHP_URL_HOST ) : '';

		return is_string( $host ) ? strtolower( $host ) : '';
	}

	/**
	 * Deterministic binding fingerprint for an environment/subject pair.
	 *
	 * @param string $environment Environment id.
	 * @param string $subject_id  Gateway subject id, or an empty string when unknown.
	 * @param string $gateway     Optional gateway namespace.
	 * @return string
	 */
	public function fingerprint( string $environment, string $subject_id, string $gateway = '' ): string {
		$environment = $this->assert_valid_environment( $environment );

		$parts = array( 'v1' );
		if ( '' !== $gateway ) {
			$parts[] = $gateway;
		}
		$parts[] = (string) $this->blog_id();
		$parts[] = $this->uuid();
		$parts[] = $environment;
		$parts[] = (string) $subject_id;

		return hash( 'sha256', implode( '|', $parts ) );
	}

	/**
	 * Metadata attached to every remote resource this installation creates.
	 *
	 * @param string $environment Environment id.
	 * @param string $subject_id  Gateway subject id, or an empty string when unknown.
	 * @param string $gateway     Optional gateway namespace.
	 * @return array<string, string>
	 */
	public function ownership_metadata( string $environment, string $subject_id, string $gateway = '' ): array {
		$environment = $this->assert_valid_environment( $environment );

		return array(
			self::META_MANAGED     => '1',
			self::META_UUID        => $this->uuid(),
			self::META_BLOG_ID     => (string) $this->blog_id(),
			self::META_ENVIRONMENT => $environment,
			self::META_SITE_HOST   => $this->site_host(),
			self::META_BINDING     => $this->fingerprint( $environment, $subject_id, $gateway ),
		);
	}

	/**
	 * Classify remote endpoint metadata.
	 *
	 * @param mixed  $metadata    Remote endpoint metadata (array or Stripe object).
	 * @param string $environment Environment the endpoint is expected to serve.
	 * @return string One of the OWNERSHIP_* constants.
	 */
	public function classify( $metadata, string $environment ): string {
		$environment = $this->assert_valid_environment( $environment );
		$metadata    = $this->normalize_metadata( $metadata );

		if ( '1' !== ( $metadata[ self::META_MANAGED ] ?? '' ) ) {
			return self::OWNERSHIP_FOREIGN;
		}

		$uuid = isset( $metadata[ self::META_UUID ] ) ? (string) $metadata[ self::META_UUID ] : '';

		if ( '' === $uuid || $uuid !== $this->uuid() ) {
			return self::OWNERSHIP_FOREIGN;
		}

		$host          = isset( $metadata[ self::META_SITE_HOST ] ) ? strtolower( (string) $metadata[ self::META_SITE_HOST ] ) : '';
		$stored_env    = isset( $metadata[ self::META_ENVIRONMENT ] ) ? (string) $metadata[ self::META_ENVIRONMENT ] : '';
		$current_host  = $this->site_host();

		if ( '' !== $stored_env && $stored_env !== $environment ) {
			return self::OWNERSHIP_FOREIGN;
		}

		if ( '' === $host || ( '' !== $current_host && $host !== $current_host ) ) {
			return self::OWNERSHIP_CLONE;
		}

		return self::OWNERSHIP_OWNED;
	}

	/**
	 * Whether remote metadata proves ownership by this installation.
	 *
	 * @param mixed  $metadata    Remote endpoint metadata.
	 * @param string $environment Environment the endpoint is expected to serve.
	 * @return bool
	 */
	public function is_owned( $metadata, string $environment ): bool {
		return self::OWNERSHIP_OWNED === $this->classify( $metadata, $environment );
	}

	/**
	 * Normalize Stripe metadata (object or array) to a string map.
	 *
	 * @param mixed $metadata Remote metadata.
	 * @return array<string, string>
	 */
	private function normalize_metadata( $metadata ): array {
		if ( is_object( $metadata ) ) {
			$metadata = method_exists( $metadata, 'toArray' ) ? $metadata->toArray() : (array) $metadata;
		}

		if ( ! is_array( $metadata ) ) {
			return array();
		}

		$normalized = array();

		foreach ( $metadata as $key => $value ) {
			if ( is_array( $value ) || is_object( $value ) ) {
				continue;
			}

			$normalized[ (string) $key ] = (string) $value;
		}

		return $normalized;
	}

	/**
	 * Validate and normalize an environment identifier.
	 *
	 * @param string $environment Environment id.
	 * @return string
	 * @throws \InvalidArgumentException When the environment is empty.
	 */
	private function assert_valid_environment( string $environment ): string {
		$environment = trim( $environment );

		if ( '' === $environment ) {
			throw new \InvalidArgumentException( 'Invalid environment.' );
		}

		return $environment;
	}

	/**
	 * Fallback UUID generator for non-WordPress contexts.
	 *
	 * @return string
	 */
	private function fallback_uuid(): string {
		$bytes    = function_exists( 'random_bytes' ) ? random_bytes( 16 ) : pack( 'N4', wp_rand(), wp_rand(), wp_rand(), wp_rand() );
		$bytes[6] = chr( ( ord( $bytes[6] ) & 0x0f ) | 0x40 );
		$bytes[8] = chr( ( ord( $bytes[8] ) & 0x3f ) | 0x80 );

		return vsprintf( '%s%s-%s-%s-%s-%s%s%s', str_split( bin2hex( $bytes ), 4 ) );
	}
}
