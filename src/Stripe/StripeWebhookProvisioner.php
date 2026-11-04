<?php
/**
 * Safe, idempotent Stripe webhook endpoint provisioning.
 *
 * `connect()` is the only method that writes to Stripe. `check()` and
 * `rebind_preview()` are strictly read-only, and `repair()` applies exactly the
 * diff a previous `check()` reported for a plugin-owned endpoint.
 *
 * @package GiftFlow
 * @subpackage Stripe
 */

namespace GiftFlow\Stripe;

use GiftFlow\Core\InstallationIdentity;
use GiftFlow\Vendor\Stripe\StripeClient;
use GiftFlow\Vendor\Stripe\Exception\ApiErrorException;
use GiftFlow\Vendor\Stripe\Exception\ApiConnectionException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Stripe SDK service accessors are camelCase by design.

/**
 * Creates, verifies and repairs environment-scoped webhook endpoints.
 */
final class StripeWebhookProvisioner {

	/**
	 * Phase: credentials were validated against Stripe.
	 *
	 * @var string
	 */
	public const PHASE_CHECKING_CREDENTIALS = 'checking_credentials';

	/**
	 * Phase: remote endpoints were reconciled.
	 *
	 * @var string
	 */
	public const PHASE_FINDING_WEBHOOK = 'finding_webhook';

	/**
	 * Phase: events/URL were configured in Stripe.
	 *
	 * @var string
	 */
	public const PHASE_CONFIGURING_EVENTS = 'configuring_events';

	/**
	 * Phase: local configuration was committed.
	 *
	 * @var string
	 */
	public const PHASE_SAVING_LOCAL = 'saving_local_configuration';

	/**
	 * Phase: the operation finished.
	 *
	 * @var string
	 */
	public const PHASE_COMPLETE = 'complete';

	/**
	 * Maximum endpoint list pages read while reconciling.
	 *
	 * @var int
	 */
	public const MAX_LIST_PAGES = 10;

	/**
	 * Repository.
	 *
	 * @var StripeConnectionRepository
	 */
	private StripeConnectionRepository $repository;

	/**
	 * Credential validator.
	 *
	 * @var StripeCredentialsValidator
	 */
	private StripeCredentialsValidator $validator;

	/**
	 * Installation identity.
	 *
	 * @var InstallationIdentity
	 */
	private InstallationIdentity $identity;

	/**
	 * Webhook URL provider.
	 *
	 * @var callable
	 */
	private $url_provider;

	/**
	 * Recurring-feature flag provider.
	 *
	 * @var callable
	 */
	private $recurring_provider;

	/**
	 * Client factory callback.
	 *
	 * @var callable
	 */
	private $client_factory;

	/**
	 * Constructor.
	 *
	 * @param StripeConnectionRepository      $repository        Connection repository.
	 * @param StripeCredentialsValidator|null $validator         Credential validator.
	 * @param InstallationIdentity|null       $identity          Installation identity.
	 * @param callable|null                   $url_provider      Callback(): string canonical webhook URL.
	 * @param callable|null                   $recurring_provider Callback(): bool recurring feature flag.
	 * @param callable|null                   $client_factory    Callback( environment, secret_key ): StripeClient.
	 */
	public function __construct(
		StripeConnectionRepository $repository,
		?StripeCredentialsValidator $validator = null,
		?InstallationIdentity $identity = null,
		?callable $url_provider = null,
		?callable $recurring_provider = null,
		?callable $client_factory = null
	) {
		$this->repository         = $repository;
		$this->identity           = $identity ?? $repository->identity();
		$this->validator          = $validator ?? new StripeCredentialsValidator();
		$this->client_factory     = $client_factory ?? array( StripeClientFactory::class, 'create' );
		$this->url_provider       = $url_provider ?? static function (): string {
			$gateway = function_exists( 'giftflow_get_stripe_gateway' ) ? giftflow_get_stripe_gateway() : null;

			return $gateway && method_exists( $gateway, 'get_webhook_url' ) ? (string) $gateway->get_webhook_url() : '';
		};
		$this->recurring_provider = $recurring_provider ?? static function () use ( $repository ): bool {
			return $repository->is_recurring_enabled();
		};
	}

	/**
	 * Create a Stripe client through the configured factory.
	 *
	 * @param string $environment Environment id.
	 * @param string $secret_key  Secret key.
	 * @return StripeClient
	 * @throws StripeClientConfigurationException When the credentials are unusable.
	 */
	private function create_client( string $environment, string $secret_key ): StripeClient {
		$factory = $this->client_factory;

		return $factory( $environment, $secret_key );
	}

	/**
	 * Connect an environment: reconcile, configure and store.
	 *
	 * @param string                $environment        Environment id.
	 * @param array<string, string> $staged_credentials Optional publishable_key/secret_key typed by the admin.
	 * @return array<string, mixed>
	 */
	public function connect( string $environment, array $staged_credentials = array() ): array {
		$environment = StripeEnvironment::assert_valid( $environment );

		if ( ! $this->repository->acquire_lock( $environment ) ) {
			return $this->failure( $environment, StripeErrorCodes::LOCKED );
		}

		try {
			return $this->run_connect( $environment, $staged_credentials, false, array() );
		} finally {
			$this->repository->release_lock( $environment );
		}
	}

	/**
	 * Read-only configuration check.
	 *
	 * @param string $environment Environment id.
	 * @return array<string, mixed>
	 */
	public function check( string $environment ): array {
		$environment = StripeEnvironment::assert_valid( $environment );

		if ( ! $this->repository->acquire_lock( $environment ) ) {
			return $this->failure( $environment, StripeErrorCodes::LOCKED );
		}

		try {
			$result = $this->run_check( $environment );
			$this->repository->mark_checked( $environment );

			return $result;
		} finally {
			$this->repository->release_lock( $environment );
		}
	}

	/**
	 * Apply exactly the diff a previous check reported.
	 *
	 * @param string               $environment   Environment id.
	 * @param array<string, mixed> $approved_diff Diff returned by check().
	 * @return array<string, mixed>
	 */
	public function repair( string $environment, array $approved_diff ): array {
		$environment = StripeEnvironment::assert_valid( $environment );

		if ( ! $this->repository->acquire_lock( $environment ) ) {
			return $this->failure( $environment, StripeErrorCodes::LOCKED );
		}

		try {
			return $this->run_repair( $environment, $approved_diff );
		} finally {
			$this->repository->release_lock( $environment );
		}
	}

	/**
	 * Read-only preview of an account rebind.
	 *
	 * @param string                $environment        Environment id.
	 * @param array<string, string> $staged_credentials Optional publishable_key/secret_key typed by the admin.
	 * @return array<string, mixed>
	 */
	public function rebind_preview( string $environment, array $staged_credentials = array() ): array {
		$environment = StripeEnvironment::assert_valid( $environment );

		if ( ! $this->repository->acquire_lock( $environment ) ) {
			return $this->failure( $environment, StripeErrorCodes::LOCKED );
		}

		try {
			return $this->run_rebind_preview( $environment, $staged_credentials );
		} finally {
			$this->repository->release_lock( $environment );
		}
	}

	/**
	 * Execute a confirmed rebind.
	 *
	 * @param string                $environment        Environment id.
	 * @param array<string, string> $staged_credentials Publishable/secret key typed by the admin.
	 * @param string                $plan_token         Token returned by rebind_preview().
	 * @return array<string, mixed>
	 */
	public function rebind( string $environment, array $staged_credentials, string $plan_token ): array {
		$environment = StripeEnvironment::assert_valid( $environment );

		if ( ! $this->repository->acquire_lock( $environment ) ) {
			return $this->failure( $environment, StripeErrorCodes::LOCKED );
		}

		try {
			$preview = $this->run_rebind_preview( $environment, $staged_credentials );

			if ( empty( $preview['success'] ) ) {
				return $preview;
			}

			if ( ! hash_equals( (string) $preview['plan_token'], $plan_token ) ) {
				return $this->failure( $environment, StripeErrorCodes::REBIND_PLAN_STALE );
			}

			$result = $this->run_connect( $environment, $staged_credentials, true, array( self::PHASE_CHECKING_CREDENTIALS ) );

			if ( ! empty( $result['success'] ) ) {
				$result['plan'] = $preview['plan'];
			}

			return $result;
		} finally {
			$this->repository->release_lock( $environment );
		}
	}

	/**
	 * Canonical webhook URL for this installation.
	 *
	 * Falls back to the shared AJAX route when no gateway instance is available
	 * yet, and upgrades the scheme to HTTPS whenever the site itself is served
	 * over HTTPS (site and admin URLs can disagree behind a proxy or after an
	 * HTTPS migration).
	 *
	 * @return string
	 */
	public function webhook_url(): string {
		$provider = $this->url_provider;
		$url      = $provider();
		$url      = is_string( $url ) ? trim( $url ) : '';

		if ( '' === $url ) {
			$url = trim( (string) admin_url( 'admin-ajax.php?action=giftflow_stripe_webhook' ) );
		}

		return self::normalize_url( $url );
	}

	/**
	 * Upgrade an http URL to https when the site is served over HTTPS.
	 *
	 * @param string $url Candidate URL.
	 * @return string
	 */
	public static function normalize_url( string $url ): string {
		$url = trim( $url );

		if ( '' === $url ) {
			return '';
		}

		$parts = wp_parse_url( $url );

		if ( ! is_array( $parts ) || empty( $parts['host'] ) ) {
			return $url;
		}

		$scheme = isset( $parts['scheme'] ) ? strtolower( (string) $parts['scheme'] ) : '';

		if ( 'http' !== $scheme || ! self::site_uses_https() ) {
			return $url;
		}

		if ( function_exists( 'set_url_scheme' ) ) {
			return (string) set_url_scheme( $url, 'https' );
		}

		return (string) preg_replace( '#^http://#i', 'https://', $url );
	}

	/**
	 * Whether this WordPress site is served over HTTPS.
	 *
	 * @return bool
	 */
	private static function site_uses_https(): bool {
		$home        = function_exists( 'home_url' ) ? (string) home_url() : '';
		$home_scheme = '' !== $home ? strtolower( (string) wp_parse_url( $home, PHP_URL_SCHEME ) ) : '';

		return 'https' === $home_scheme || ( function_exists( 'is_ssl' ) && is_ssl() );
	}

	/**
	 * Why a URL cannot be reached from Stripe, or an empty string when it can.
	 *
	 * The reason is part of the failure payload so the admin sees which part of
	 * their configuration has to change instead of guessing.
	 *
	 * @param string $url Candidate URL.
	 * @return string One of empty|unparseable|scheme|loopback|reserved_tld|single_label|private_ip, or ''.
	 */
	public static function public_url_problem( string $url ): string {
		$url = trim( $url );

		if ( '' === $url ) {
			return 'empty';
		}

		$parts = wp_parse_url( $url );

		if ( ! is_array( $parts ) || empty( $parts['host'] ) ) {
			return 'unparseable';
		}

		$scheme = isset( $parts['scheme'] ) ? strtolower( (string) $parts['scheme'] ) : '';

		if ( 'https' !== $scheme ) {
			return 'scheme';
		}

		$host = strtolower( rtrim( (string) $parts['host'], '.' ) );

		if ( in_array( $host, array( 'localhost', '127.0.0.1', '::1', '[::1]' ), true ) ) {
			return 'loopback';
		}

		$reserved_suffixes = array( '.local', '.localhost', '.test', '.invalid', '.example', '.internal' );

		foreach ( $reserved_suffixes as $suffix ) {
			if ( substr( $host, -strlen( $suffix ) ) === $suffix ) {
				return 'reserved_tld';
			}
		}

		if ( false === strpos( $host, '.' ) ) {
			// Single-label hostnames are never publicly resolvable.
			return 'single_label';
		}

		if ( filter_var( $host, FILTER_VALIDATE_IP ) ) {
			$is_public = (bool) filter_var( $host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE );

			return $is_public ? '' : 'private_ip';
		}

		return '';
	}

	/**
	 * Human readable label for a URL problem reason.
	 *
	 * @param string $reason Reason from public_url_problem().
	 * @return string
	 */
	public static function url_problem_label( string $reason ): string {
		switch ( $reason ) {
			case 'empty':
				return __( 'the webhook URL is empty', 'giftflow' );
			case 'unparseable':
				return __( 'the webhook URL cannot be parsed', 'giftflow' );
			case 'scheme':
				return __( 'the webhook URL is not HTTPS', 'giftflow' );
			case 'loopback':
				return __( 'the host is a loopback address', 'giftflow' );
			case 'reserved_tld':
				return __( 'the host uses a reserved development domain', 'giftflow' );
			case 'single_label':
				return __( 'the host is not a public domain name', 'giftflow' );
			case 'private_ip':
				return __( 'the host is a private IP address', 'giftflow' );
			default:
				return '';
		}
	}

	/**
	 * Whether a URL is reachable from Stripe.
	 *
	 * @param string $url Candidate URL.
	 * @return bool
	 */
	public static function is_public_url( string $url ): bool {
		$problem = self::public_url_problem( $url );

		/**
		 * Filter whether a webhook URL is considered publicly reachable.
		 *
		 * @since 1.2.0
		 *
		 * @param bool   $is_public Whether the URL passed the built-in checks.
		 * @param string $url       Evaluated URL.
		 * @param string $problem   Detected problem, empty when none.
		 */
		return (bool) apply_filters( 'giftflow_stripe_webhook_url_is_public', '' === $problem, $url, $problem );
	}

	/**
	 * Failure envelope for a webhook URL Stripe cannot reach.
	 *
	 * Reports the exact URL and the failing part so the admin can fix the site
	 * configuration instead of guessing why auto configuration is unavailable.
	 *
	 * @param string   $environment Environment id.
	 * @param string   $url         Evaluated webhook URL.
	 * @param string[] $phases      Phases already confirmed.
	 * @return array<string, mixed>
	 */
	private function url_failure( string $environment, string $url, array $phases = array() ): array {
		$reason = self::public_url_problem( $url );

		return $this->failure(
			$environment,
			StripeErrorCodes::NON_PUBLIC_URL,
			array(
				'phases'  => $phases,
				'url'     => $url,
				'reason'  => $reason,
				'message' => sprintf(
					/* translators: 1: webhook URL, 2: reason the URL cannot be reached */
					__(
						'Auto configuration needs a public HTTPS webhook URL. This site reports %1$s (%2$s). Fix the WordPress site URL or serve wp-admin over HTTPS, then try again — or keep using the manual signing secret.',
						'giftflow'
					),
					'' !== $url ? $url : '—',
					self::url_problem_label( $reason )
				),
			)
		);
	}

	/**
	 * Perform connect/rebind.
	 *
	 * @param string                $environment        Environment id.
	 * @param array<string, string> $staged_credentials Staged credentials.
	 * @param bool                  $allow_rebind       Whether an account change is permitted.
	 * @param string[]              $phases             Phases already confirmed.
	 * @return array<string, mixed>
	 */
	private function run_connect( string $environment, array $staged_credentials, bool $allow_rebind, array $phases ): array {
		// Fail fast without contacting Stripe when the endpoint URL cannot work.
		$url = $this->webhook_url();

		if ( ! self::is_public_url( $url ) ) {
			return $this->url_failure( $environment, $url, $phases );
		}

		$credentials = $this->resolve_credentials( $environment, $staged_credentials );
		$validation  = $this->validator->validate( $environment, $credentials );

		if ( empty( $validation['valid'] ) ) {
			return $this->first_error( $environment, $validation['errors'] );
		}

		$phases[] = self::PHASE_CHECKING_CREDENTIALS;

		$profile     = $this->repository->get_profile( $environment );
		$account_id  = (string) $validation['account_id'];
		$api_version = (string) $validation['api_version'];
		$events      = $this->required_events( $environment );
		$livemode    = StripeEnvironment::livemode( $environment );

		try {
			$client = $this->create_client( $environment, (string) $credentials['secret_key'] );
		} catch ( StripeClientConfigurationException $e ) {
			return $this->failure( $environment, StripeErrorCodes::INVALID_SECRET_KEY, array( 'phases' => $phases ) );
		}

		if ( '' !== (string) $profile['stripe_account_id'] && (string) $profile['stripe_account_id'] !== $account_id && ! $allow_rebind ) {
			return $this->failure(
				$environment,
				StripeErrorCodes::REBIND_REQUIRED,
				array(
					'phases'      => $phases,
					'requires_rebind' => true,
				)
			);
		}

		$phases[] = self::PHASE_FINDING_WEBHOOK;

		try {
			$endpoint = $this->reconcile_endpoint( $client, $environment, $url, $livemode, $profile );
		} catch ( ApiErrorException $e ) {
			return $this->failure(
				$environment,
				StripeCredentialsValidator::classify_api_error( $e ),
				array( 'phases' => $phases )
			);
		}

		if ( isset( $endpoint['error'] ) ) {
			return $this->failure(
				$environment,
				(string) $endpoint['error'],
				array(
					'phases'          => $phases,
					'requires_rebind' => true,
					'diff'            => isset( $endpoint['diff'] ) ? $endpoint['diff'] : array(),
				)
			);
		}

		$existing   = isset( $endpoint['endpoint'] ) ? $endpoint['endpoint'] : null;
		$endpoints  = isset( $endpoint['matches'] ) ? (array) $endpoint['matches'] : array();
		$replaced   = array();
		$stale_ids  = array();
		$writes     = 0;
		$secret     = '';
		$endpoint_id = '';

		if ( null !== $existing ) {
			$endpoint_id = (string) $existing->id;

			if ( ! $this->endpoint_is_synced( $existing, $environment, $url, $events, $livemode ) ) {
				$params = $this->update_params( $existing, $url, $events );

				if ( ! empty( $params ) ) {
					$existing = $client->webhookEndpoints->update( $endpoint_id, $params );
					++$writes;
				}
			}

			foreach ( $endpoints as $duplicate ) {
				if ( (string) $duplicate->id === $endpoint_id ) {
					continue;
				}

				$stale_ids[] = (string) $duplicate->id;
			}

			$stored = $this->repository->get_credentials( $environment );

			if ( '' === $stored['webhook_secret'] ) {
				// The endpoint is ours but its signing secret was never stored: create a
				// replacement and retire the previous endpoint so events are not doubled.
				$created = $this->create_endpoint( $client, $environment, $url, $events, $account_id );

				$stale_ids[] = $endpoint_id;
				$replaced[]  = $endpoint_id;
				$endpoint_id = (string) $created['endpoint']->id;
				$secret      = (string) $created['secret'];
				$api_version = (string) $created['api_version'];
				$writes     += $created['writes'];
			}
		} else {
			$created = $this->create_endpoint( $client, $environment, $url, $events, $account_id );

			if ( isset( $created['error'] ) ) {
				$this->repository->update_profile(
					$environment,
					array(
						'recovery'        => array(
							'operation'  => 'create',
							'started_at' => time(),
							'url'        => $url,
						),
						'setup_status'    => StripeConnectionRepository::STATUS_RECOVERY_PENDING,
						'last_error_code' => (string) $created['error'],
						'last_error_at'   => time(),
					)
				);

				return $this->failure(
					$environment,
					(string) $created['error'],
					array(
						'phases'   => $phases,
						'recovery' => true,
					)
				);
			}

			$endpoint_id = (string) $created['endpoint']->id;
			$secret      = (string) $created['secret'];
			$api_version = (string) $created['api_version'];
			$writes     += $created['writes'];
		}

		$phases[] = self::PHASE_CONFIGURING_EVENTS;
		$phases[] = self::PHASE_SAVING_LOCAL;

		$previous_ids = $profile['previous_webhook_ids'];

		if ( '' !== (string) $profile['webhook_id'] && (string) $profile['webhook_id'] !== $endpoint_id ) {
			$previous_ids[] = (string) $profile['webhook_id'];
		}

		$remembered = StripeConnectionRepository::normalize_string_list( array_merge( $previous_ids, $replaced ) );

		$credential_update = array(
			'publishable_key' => (string) $credentials['publishable_key'],
			'secret_key'      => (string) $credentials['secret_key'],
		);

		if ( '' !== $secret ) {
			$credential_update['webhook_secret'] = $secret;
		}

		$credentials_saved = $this->repository->update_credentials( $environment, $credential_update );

		$profile_saved = $this->repository->update_profile(
			$environment,
			array(
				'webhook_id'          => $endpoint_id,
				'webhook_url'         => $url,
				'webhook_owner'       => StripeConnectionRepository::OWNER_PLUGIN,
				'events'              => $events,
				'stripe_account_id'   => $account_id,
				'binding_fingerprint' => $this->identity->fingerprint( $environment, $account_id ),
				'api_version'         => $api_version,
				'setup_status'        => StripeConnectionRepository::STATUS_CONFIGURED,
				'livemode'            => $livemode,
				'last_checked_at'     => time(),
				'last_error_code'     => '',
				'last_error_at'       => 0,
				'recovery'            => array(),
				'previous_webhook_ids' => $remembered,
			)
		);

		if ( ! $credentials_saved || ! $profile_saved ) {
			$this->repository->update_profile(
				$environment,
				array(
					'recovery'        => array(
						'operation'    => 'persist',
						'started_at'   => time(),
						'url'          => $url,
						'webhook_id'   => $endpoint_id,
					),
					'setup_status'    => StripeConnectionRepository::STATUS_RECOVERY_PENDING,
					'last_error_code' => StripeErrorCodes::PERSISTENCE_FAILED,
					'last_error_at'   => time(),
				)
			);

			return $this->failure(
				$environment,
				StripeErrorCodes::PERSISTENCE_FAILED,
				array(
					'phases'   => $phases,
					'recovery' => true,
					'writes'   => $writes,
				)
			);
		}

		$retired = $this->retire_endpoints( $client, $stale_ids, $endpoint_id, $environment );

		$phases[] = self::PHASE_COMPLETE;

		return $this->success(
			$environment,
			array(
				'phases'  => $phases,
				'writes'  => $writes + $retired,
				'message' => __( 'Stripe is connected and the webhook endpoint is active. The profile switches to Verified after the first delivery.', 'giftflow' ),
			)
		);
	}

	/**
	 * Read-only check implementation.
	 *
	 * @param string $environment Environment id.
	 * @return array<string, mixed>
	 */
	private function run_check( string $environment ): array {
		$profile     = $this->repository->get_profile( $environment );
		$credentials = $this->repository->get_credentials( $environment );
		$url         = $this->webhook_url();
		$events      = $this->required_events( $environment );
		$livemode    = StripeEnvironment::livemode( $environment );
		$stored_id   = (string) $profile['webhook_id'];

		if ( '' === $credentials['secret_key'] ) {
			return $this->failure( $environment, StripeErrorCodes::NOT_CONFIGURED, array( 'status' => 'not_configured' ) );
		}

		try {
			$client = $this->create_client( $environment, $credentials['secret_key'] );
		} catch ( StripeClientConfigurationException $e ) {
			return $this->failure( $environment, StripeErrorCodes::INVALID_SECRET_KEY, array( 'status' => 'blocked' ) );
		}

		$findings  = array();
		$fields    = array();
		$endpoint  = null;
		$ownership = InstallationIdentity::OWNERSHIP_FOREIGN;

		try {
			if ( '' === $stored_id ) {
				$matches = $this->find_endpoints_by_url( $client, $url );

				if ( empty( $matches ) ) {
					return $this->failure(
						$environment,
						StripeErrorCodes::ENDPOINT_MISSING,
						array(
							'status'     => 'missing',
							'repairable' => false,
							'diff'       => array(
								'endpoint_id' => '',
								'fields'      => array(),
								'findings'    => array( StripeErrorCodes::ENDPOINT_MISSING ),
								'ownership'   => '',
								'checksum'    => '',
							),
						)
					);
				}

				$endpoint  = $this->pick_endpoint( $matches, $environment );
				$ownership = null === $endpoint ? InstallationIdentity::OWNERSHIP_FOREIGN : $this->identity->classify( $endpoint->metadata, $environment );
				$findings[] = InstallationIdentity::OWNERSHIP_OWNED === $ownership
					? StripeErrorCodes::NOT_CONFIGURED
					: StripeErrorCodes::ENDPOINT_NOT_OWNED;
			} else {
				$endpoint = $this->retrieve_endpoint( $client, $stored_id );

				if ( null === $endpoint ) {
					return $this->failure(
						$environment,
						StripeErrorCodes::ENDPOINT_MISSING,
						array(
							'status'     => 'missing',
							'repairable' => false,
							'diff'       => array(
								'endpoint_id' => $stored_id,
								'fields'      => array(),
								'findings'    => array( StripeErrorCodes::ENDPOINT_MISSING ),
								'ownership'   => '',
								'checksum'    => '',
							),
						)
					);
				}

				$ownership = $this->identity->classify( $endpoint->metadata, $environment );
			}
		} catch ( ApiErrorException $e ) {
			return $this->failure(
				$environment,
				StripeCredentialsValidator::classify_api_error( $e ),
				array( 'status' => 'blocked' )
			);
		}

		if ( InstallationIdentity::OWNERSHIP_OWNED !== $ownership ) {
			$findings[] = InstallationIdentity::OWNERSHIP_CLONE === $ownership
				? StripeErrorCodes::INSTALLATION_CLONE_DETECTED
				: StripeErrorCodes::ENDPOINT_NOT_OWNED;
		} elseif ( '' === $stored_id ) {
			$findings[] = StripeErrorCodes::NOT_CONFIGURED;
		}

		if ( isset( $endpoint->livemode ) && (bool) $endpoint->livemode !== $livemode ) {
			$findings[] = StripeErrorCodes::ENDPOINT_NOT_OWNED;
		} elseif ( isset( $endpoint->status ) && 'enabled' !== (string) $endpoint->status ) {
			$findings[]       = StripeErrorCodes::ENDPOINT_DISABLED;
			$fields['status'] = array(
				'from' => (string) $endpoint->status,
				'to'   => 'enabled',
			);
		}

		$endpoint_url = isset( $endpoint->url ) ? (string) $endpoint->url : '';

		if ( $endpoint_url !== $url ) {
			$findings[]    = StripeErrorCodes::URL_DRIFT;
			$fields['url'] = array(
				'from' => $endpoint_url,
				'to'   => $url,
			);
		}

		$endpoint_events = isset( $endpoint->enabled_events ) && is_array( $endpoint->enabled_events )
			? $endpoint->enabled_events
			: array();
		$event_diff      = StripeWebhookEventRegistry::diff( $endpoint_events, $events );

		if ( array() !== $event_diff['add'] || array() !== $event_diff['remove'] ) {
			$findings[]       = StripeErrorCodes::EVENT_DRIFT;
			$fields['events'] = array(
				'from' => StripeWebhookEventRegistry::sorted( $endpoint_events ),
				'to'   => $events,
			);
		}

		if ( '' === $credentials['webhook_secret'] ) {
			$findings[] = StripeErrorCodes::SECRET_UNKNOWN;
		}

		$findings = array_values( array_unique( $findings ) );

		$diff = array(
			'endpoint_id' => (string) $endpoint->id,
			'fields'      => $fields,
			'findings'    => $findings,
			'ownership'   => $ownership,
		);

		$diff['checksum'] = self::diff_checksum( $diff );

		$repairable = array() !== $fields && InstallationIdentity::OWNERSHIP_OWNED === $ownership;

		$profile_verified = (int) $this->repository->get_profile( $environment )['last_verified_webhook_at'] > 0;

		if ( empty( $findings ) ) {
			return $this->success(
				$environment,
				array(
					'status'     => 'in_sync',
					'diff'       => $diff,
					'repairable' => false,
					'writes'     => 0,
					'message'    => $profile_verified
						? __( 'Everything matches and deliveries are verified.', 'giftflow' )
						: __( 'Everything matches: credentials, endpoint URL and events are configured correctly.', 'giftflow' ),
				)
			);
		}

		$code = (string) $findings[0];

		return array(
			'success'     => false,
			'code'        => $code,
			'message'     => StripeErrorCodes::message( $code ),
			'environment' => $environment,
			'status'      => 'drift',
			'phases'      => array(),
			'writes'      => 0,
			'recovery'    => false,
			'diff'        => $diff,
			'findings'    => $findings,
			'repairable'  => $repairable,
			'connection'  => $this->repository->get_masked_profile( $environment ),
		);
	}

	/**
	 * Repair implementation.
	 *
	 * @param string               $environment   Environment id.
	 * @param array<string, mixed> $approved_diff Diff returned by check().
	 * @return array<string, mixed>
	 */
	private function run_repair( string $environment, array $approved_diff ): array {
		$fresh = $this->run_check( $environment );

		$fresh_diff = isset( $fresh['diff'] ) && is_array( $fresh['diff'] ) ? $fresh['diff'] : array();
		$fresh_id   = isset( $fresh_diff['endpoint_id'] ) ? (string) $fresh_diff['endpoint_id'] : '';
		$fresh_sum  = isset( $fresh_diff['checksum'] ) ? (string) $fresh_diff['checksum'] : '';

		if ( '' === $fresh_id || '' === $fresh_sum ) {
			// Nothing repairable right now: surface the newest finding instead of
			// applying a diff that no longer matches remote state.
			if ( empty( $fresh['success'] ) && ! empty( $fresh['code'] ) ) {
				return $fresh;
			}

			return $this->failure( $environment, StripeErrorCodes::INVALID_DIFF );
		}

		$approved_id   = isset( $approved_diff['endpoint_id'] ) ? (string) $approved_diff['endpoint_id'] : '';
		$approved_sum  = isset( $approved_diff['checksum'] ) ? (string) $approved_diff['checksum'] : '';
		$approved_keys = isset( $approved_diff['fields'] ) && is_array( $approved_diff['fields'] )
			? array_keys( $approved_diff['fields'] )
			: array();
		$fresh_keys    = isset( $fresh_diff['fields'] ) && is_array( $fresh_diff['fields'] )
			? array_keys( $fresh_diff['fields'] )
			: array();

		sort( $approved_keys );
		sort( $fresh_keys );

		if (
			'' === $approved_id
			|| '' === $approved_sum
			|| $approved_id !== $fresh_id
			|| $approved_keys !== $fresh_keys
			|| ! hash_equals( $fresh_sum, $approved_sum )
		) {
			return $this->failure( $environment, StripeErrorCodes::INVALID_DIFF );
		}

		if ( InstallationIdentity::OWNERSHIP_OWNED !== (string) ( $fresh_diff['ownership'] ?? '' ) ) {
			return $this->failure( $environment, StripeErrorCodes::ENDPOINT_NOT_OWNED );
		}

		$allowed = array( 'status', 'url', 'events' );

		foreach ( $approved_keys as $key ) {
			if ( ! in_array( $key, $allowed, true ) ) {
				return $this->failure( $environment, StripeErrorCodes::INVALID_DIFF );
			}
		}

		if ( empty( $approved_keys ) ) {
			return $this->failure( $environment, StripeErrorCodes::ENDPOINT_MISSING );
		}

		$credentials = $this->repository->get_credentials( $environment );

		try {
			$client = $this->create_client( $environment, $credentials['secret_key'] );
		} catch ( StripeClientConfigurationException $e ) {
			return $this->failure( $environment, StripeErrorCodes::INVALID_SECRET_KEY );
		}

		$params = array();

		if ( in_array( 'status', $approved_keys, true ) ) {
			$params['status'] = 'enabled';
		}

		if ( in_array( 'url', $approved_keys, true ) ) {
			$params['url'] = (string) $fresh_diff['fields']['url']['to'];
		}

		if ( in_array( 'events', $approved_keys, true ) ) {
			$params['enabled_events'] = $this->required_events( $environment );
		}

		try {
			$endpoint = $client->webhookEndpoints->update( (string) $fresh_diff['endpoint_id'], $params );
		} catch ( ApiErrorException $e ) {
			return $this->failure( $environment, StripeCredentialsValidator::classify_api_error( $e ) );
		}

		$profile = $this->repository->get_profile( $environment );

		$saved = $this->repository->update_profile(
			$environment,
			array(
				'webhook_url'     => isset( $endpoint->url ) ? (string) $endpoint->url : (string) $profile['webhook_url'],
				'events'          => isset( $endpoint->enabled_events ) && is_array( $endpoint->enabled_events )
					? StripeWebhookEventRegistry::sorted( $endpoint->enabled_events )
					: $profile['events'],
				'last_checked_at' => time(),
				'last_error_code' => '',
				'last_error_at'   => 0,
			)
		);

		if ( ! $saved ) {
			return $this->failure( $environment, StripeErrorCodes::PERSISTENCE_FAILED, array( 'writes' => 1 ) );
		}

		return $this->success(
			$environment,
			array(
				'status'  => 'repaired',
				'writes'  => 1,
				'diff'    => $fresh_diff,
				'message' => __( 'Webhook endpoint updated. The signing secret was not changed.', 'giftflow' ),
			)
		);
	}

	/**
	 * Read-only rebind preview.
	 *
	 * @param string                $environment        Environment id.
	 * @param array<string, string> $staged_credentials Staged credentials.
	 * @return array<string, mixed>
	 */
	private function run_rebind_preview( string $environment, array $staged_credentials ): array {
		// Fail fast without contacting Stripe when the endpoint URL cannot work.
		$url = $this->webhook_url();

		if ( ! self::is_public_url( $url ) ) {
			return $this->url_failure( $environment, $url );
		}

		$credentials = $this->resolve_credentials( $environment, $staged_credentials );
		$validation  = $this->validator->validate( $environment, $credentials );

		if ( empty( $validation['valid'] ) ) {
			return $this->first_error( $environment, $validation['errors'] );
		}

		$profile = $this->repository->get_profile( $environment );

		try {
			$client  = $this->create_client( $environment, (string) $credentials['secret_key'] );
			$matches = $this->find_endpoints_by_url( $client, $url );
		} catch ( StripeClientConfigurationException $e ) {
			return $this->failure( $environment, StripeErrorCodes::INVALID_SECRET_KEY );
		} catch ( ApiErrorException $e ) {
			return $this->failure( $environment, StripeCredentialsValidator::classify_api_error( $e ) );
		}

		$candidate  = $this->pick_endpoint( $matches, $environment );
		$ownership  = null === $candidate
			? ''
			: $this->identity->classify( $candidate->metadata, $environment );
		$stored     = $this->repository->get_credentials( $environment );
		$previous   = StripeConnectionRepository::normalize_string_list(
			array_merge(
				'' === (string) $profile['webhook_id'] ? array() : array( (string) $profile['webhook_id'] ),
				$profile['previous_webhook_ids']
			)
		);

		$plan = array(
			'environment'         => $environment,
			'url'                 => $url,
			'previous'            => array(
				'account_id'    => (string) $profile['stripe_account_id'],
				'webhook_id'    => (string) $profile['webhook_id'],
				'webhook_ids'   => $previous,
			),
			'target'              => array(
				'account_id'    => (string) $validation['account_id'],
				'livemode'      => StripeEnvironment::livemode( $environment ),
				'events'        => $this->required_events( $environment ),
				'endpoint_id'   => null === $candidate ? '' : (string) $candidate->id,
				'endpoint_owner' => $ownership,
			),
			'actions'             => array(
				InstallationIdentity::OWNERSHIP_OWNED === $ownership ? 'reuse_owned_endpoint' : 'create_endpoint',
				'retain_local_secret' => '' !== $stored['webhook_secret'] && InstallationIdentity::OWNERSHIP_OWNED === $ownership,
			),
			'previous_endpoints_must_be_removed_manually' => $previous,
		);

		return $this->success(
			$environment,
			array(
				'status'     => 'rebind_preview',
				'writes'     => 0,
				'plan'       => $plan,
				'plan_token' => self::plan_token( $plan ),
				'diff'       => array(
					'endpoint_id' => (string) $plan['target']['endpoint_id'],
					'fields'      => array(
						'account_id' => array(
							'from' => (string) $profile['stripe_account_id'],
							'to'   => (string) $validation['account_id'],
						),
					),
					'findings'    => array(),
					'ownership'   => $ownership,
					'checksum'    => self::diff_checksum( $plan ),
				),
				'message'    => __( 'Review the change, then confirm to configure the new Stripe account.', 'giftflow' ),
			)
		);
	}

	/**
	 * Reconcile a stored or recoverable endpoint.
	 *
	 * @param StripeClient          $client     Stripe client.
	 * @param string                $environment Environment id.
	 * @param string                $url         Canonical URL.
	 * @param bool                  $livemode    Expected livemode flag.
	 * @param array<string, mixed>  $profile     Stored profile.
	 * @return array<string, mixed> Either `endpoint` (+`matches`) or `error`.
	 */
	private function reconcile_endpoint( StripeClient $client, string $environment, string $url, bool $livemode, array $profile ): array {
		$stored_id = (string) $profile['webhook_id'];
		$endpoint  = '' === $stored_id ? null : $this->retrieve_endpoint( $client, $stored_id );
		$matches   = array();

		if ( null !== $endpoint ) {
			if ( ! $this->endpoint_matches_environment( $endpoint, $environment, $livemode ) ) {
				return array( 'error' => StripeErrorCodes::ENDPOINT_NOT_OWNED );
			}

			$ownership = $this->identity->classify( $endpoint->metadata, $environment );

			if ( InstallationIdentity::OWNERSHIP_OWNED !== $ownership ) {
				return array(
					'error' => InstallationIdentity::OWNERSHIP_CLONE === $ownership
						? StripeErrorCodes::INSTALLATION_CLONE_DETECTED
						: StripeErrorCodes::ENDPOINT_NOT_OWNED,
					'diff'  => array( 'endpoint_id' => $stored_id ),
				);
			}

			$matches = $this->find_endpoints_by_url( $client, $url );

			return array(
				'endpoint' => $endpoint,
				'matches'  => $matches,
			);
		}

		// No stored endpoint (or it disappeared): look for a recoverable match.
		$matches = $this->find_endpoints_by_url( $client, $url );
		$owned   = array();

		foreach ( $matches as $candidate ) {
			if ( ! $this->endpoint_matches_environment( $candidate, $environment, $livemode ) ) {
				continue;
			}

			if ( $this->identity->is_owned( $candidate->metadata, $environment ) ) {
				$owned[] = $candidate;
			}
		}

		if ( ! empty( $owned ) ) {
			$credentials = $this->repository->get_credentials( $environment );

			if ( '' === $credentials['webhook_secret'] ) {
				// An owned endpoint exists but its secret was never persisted: it cannot be
				// adopted safely, so it is replaced after the new endpoint is stored.
				return array(
					'endpoint' => $owned[0],
					'matches'  => $matches,
				);
			}

			return array(
				'endpoint' => $owned[0],
				'matches'  => $matches,
			);
		}

		if ( ! empty( $matches ) ) {
			$candidate = $this->pick_endpoint( $matches, $environment );
			$ownership = null === $candidate ? InstallationIdentity::OWNERSHIP_FOREIGN : $this->identity->classify( $candidate->metadata, $environment );

			return array(
				'error' => InstallationIdentity::OWNERSHIP_CLONE === $ownership
					? StripeErrorCodes::INSTALLATION_CLONE_DETECTED
					: StripeErrorCodes::REMOTE_EXISTING_SECRET_UNKNOWN,
				'diff'  => array(
					'endpoint_id' => null === $candidate ? '' : (string) $candidate->id,
					'fields'      => array(),
					'findings'    => array( StripeErrorCodes::REMOTE_EXISTING_SECRET_UNKNOWN ),
					'ownership'   => $ownership,
					'checksum'    => '',
				),
			);
		}

		return array(
			'endpoint' => null,
			'matches'  => array(),
		);
	}

	/**
	 * Create an endpoint, mapping ambiguous failures to recovery state.
	 *
	 * @param StripeClient $client      Stripe client.
	 * @param string       $environment Environment id.
	 * @param string       $url         Canonical URL.
	 * @param string[]     $events      Required events.
	 * @param string       $account_id  Account id.
	 * @return array<string, mixed>
	 */
	private function create_endpoint( StripeClient $client, string $environment, string $url, array $events, string $account_id ): array {
		try {
			$endpoint = $client->webhookEndpoints->create(
				array(
					'url'            => $url,
					'enabled_events' => $events,
					'description'    => sprintf(
						/* translators: 1: environment label, 2: site host */
						__( 'GiftFlow %1$s endpoint for %2$s', 'giftflow' ),
						StripeEnvironment::label( $environment ),
						$this->identity->site_host()
					),
					'metadata'       => $this->identity->ownership_metadata( $environment, $account_id ),
					'connect'        => false,
				)
			);
		} catch ( ApiConnectionException $e ) {
			// The request may have reached Stripe: recover by reconciling, never blind-create.
			return array( 'error' => StripeErrorCodes::AMBIGUOUS_CREATE );
		} catch ( ApiErrorException $e ) {
			return array( 'error' => StripeCredentialsValidator::classify_api_error( $e ) );
		} catch ( \Exception $e ) {
			return array( 'error' => StripeErrorCodes::AMBIGUOUS_CREATE );
		}

		$secret = isset( $endpoint->secret ) && is_string( $endpoint->secret ) ? $endpoint->secret : '';

		if ( '' === $secret ) {
			return array( 'error' => StripeErrorCodes::SECRET_UNKNOWN );
		}

		return array(
			'endpoint'    => $endpoint,
			'secret'      => $secret,
			'api_version' => isset( $endpoint->api_version ) ? (string) $endpoint->api_version : StripeClientFactory::sdk_version(),
			'writes'      => 1,
		);
	}

	/**
	 * Delete stale plugin-owned duplicate endpoints.
	 *
	 * @param StripeClient $client      Stripe client.
	 * @param string[]     $ids         Duplicate endpoint ids.
	 * @param string       $keep_id     Endpoint id to keep.
	 * @param string       $environment Environment id.
	 * @return int Number of delete calls issued.
	 */
	private function retire_endpoints( StripeClient $client, array $ids, string $keep_id, string $environment ): int {
		$deleted = 0;

		foreach ( $ids as $id ) {
			if ( '' === $id || $id === $keep_id ) {
				continue;
			}

			try {
				$endpoint = $this->retrieve_endpoint( $client, $id );

				if ( null === $endpoint ) {
					continue;
				}

				if ( ! $this->identity->is_owned( $endpoint->metadata, $environment ) ) {
					continue;
				}

				$client->webhookEndpoints->delete( $id );
				++$deleted;
			} catch ( ApiErrorException $e ) {
				// Leaving a duplicate endpoint in place is preferable to failing the connect.
				continue;
			}
		}

		return $deleted;
	}

	/**
	 * Retrieve an endpoint, treating a missing resource as null.
	 *
	 * @param StripeClient $client Stripe client.
	 * @param string       $id     Endpoint id.
	 * @return object|null
	 * @throws ApiErrorException When Stripe returns a non-404 failure.
	 */
	private function retrieve_endpoint( StripeClient $client, string $id ): ?object {
		try {
			return $client->webhookEndpoints->retrieve( $id );
		} catch ( ApiErrorException $e ) {
			if ( 404 === (int) $e->getHttpStatus() || 'resource_missing' === $e->getStripeCode() ) {
				return null;
			}

			throw $e;
		}
	}

	/**
	 * List every endpoint whose URL matches the canonical URL.
	 *
	 * @param StripeClient $client Stripe client.
	 * @param string       $url    Canonical URL.
	 * @return array<int, object>
	 */
	private function find_endpoints_by_url( StripeClient $client, string $url ): array {
		$matches    = array();
		$starting   = '';
		$page_count = 0;

		do {
			$params = array( 'limit' => 100 );

			if ( '' !== $starting ) {
				$params['starting_after'] = $starting;
			}

			$page        = $client->webhookEndpoints->all( $params );
			$data        = isset( $page->data ) && is_array( $page->data ) ? $page->data : array();
			$last_id     = '';
			++$page_count;

			foreach ( $data as $endpoint ) {
				$last_id = isset( $endpoint->id ) ? (string) $endpoint->id : $last_id;

				if ( isset( $endpoint->url ) && (string) $endpoint->url === $url ) {
					$matches[] = $endpoint;
				}
			}

			$has_more = isset( $page->has_more ) ? (bool) $page->has_more : false;
			$starting = '' === $last_id ? '' : $last_id;
		} while ( $has_more && '' !== $starting && $page_count < self::MAX_LIST_PAGES );

		return $matches;
	}

	/**
	 * Pick the most relevant matching endpoint.
	 *
	 * @param array<int, object> $matches     Matching endpoints.
	 * @param string             $environment Environment id.
	 * @return object|null
	 */
	private function pick_endpoint( array $matches, string $environment ): ?object {
		foreach ( $matches as $endpoint ) {
			if ( $this->identity->is_owned( $endpoint->metadata, $environment ) ) {
				return $endpoint;
			}
		}

		return empty( $matches ) ? null : $matches[0];
	}

	/**
	 * Whether an endpoint belongs to the expected environment and mode.
	 *
	 * @param object $endpoint    Stripe endpoint object.
	 * @param string $environment Environment id.
	 * @param bool   $livemode    Expected livemode flag.
	 * @return bool
	 */
	private function endpoint_matches_environment( $endpoint, string $environment, bool $livemode ): bool {
		if ( isset( $endpoint->livemode ) && (bool) $endpoint->livemode !== $livemode ) {
			return false;
		}

		$metadata = isset( $endpoint->metadata ) ? $endpoint->metadata : array();
		$metadata = is_object( $metadata ) && method_exists( $metadata, 'toArray' ) ? $metadata->toArray() : (array) $metadata;
		$stored   = isset( $metadata[ InstallationIdentity::META_ENVIRONMENT ] )
			? (string) $metadata[ InstallationIdentity::META_ENVIRONMENT ]
			: '';

		if ( '' !== $stored && $stored !== $environment ) {
			return false;
		}

		return true;
	}

	/**
	 * Whether an endpoint already matches the desired configuration.
	 *
	 * @param object   $endpoint    Stripe endpoint object.
	 * @param string   $environment Environment id.
	 * @param string   $url         Canonical URL.
	 * @param string[] $events      Required events.
	 * @param bool     $livemode    Expected livemode flag.
	 * @return bool
	 */
	private function endpoint_is_synced( $endpoint, string $environment, string $url, array $events, bool $livemode ): bool {
		if ( ! $this->endpoint_matches_environment( $endpoint, $environment, $livemode ) ) {
			return false;
		}

		if ( isset( $endpoint->status ) && 'enabled' !== (string) $endpoint->status ) {
			return false;
		}

		if ( isset( $endpoint->url ) && (string) $endpoint->url !== $url ) {
			return false;
		}

		$current = isset( $endpoint->enabled_events ) && is_array( $endpoint->enabled_events ) ? $endpoint->enabled_events : array();
		$diff    = StripeWebhookEventRegistry::diff( $current, $events );

		return array() === $diff['add'] && array() === $diff['remove'];
	}

	/**
	 * Parameters needed to bring an owned endpoint in line.
	 *
	 * @param object   $endpoint Stripe endpoint object.
	 * @param string   $url      Canonical URL.
	 * @param string[] $events   Required events.
	 * @return array<string, mixed>
	 */
	private function update_params( $endpoint, string $url, array $events ): array {
		$params = array();

		if ( isset( $endpoint->status ) && 'enabled' !== (string) $endpoint->status ) {
			$params['status'] = 'enabled';
		}

		if ( isset( $endpoint->url ) && (string) $endpoint->url !== $url ) {
			$params['url'] = $url;
		}

		$current = isset( $endpoint->enabled_events ) && is_array( $endpoint->enabled_events ) ? $endpoint->enabled_events : array();
		$diff    = StripeWebhookEventRegistry::diff( $current, $events );

		if ( array() !== $diff['add'] || array() !== $diff['remove'] ) {
			$params['enabled_events'] = $events;
		}

		return $params;
	}

	/**
	 * Merge staged credentials over stored ones.
	 *
	 * @param string                $environment Environment id.
	 * @param array<string, string> $staged      Staged credentials.
	 * @return array<string, string>
	 */
	private function resolve_credentials( string $environment, array $staged ): array {
		$stored = $this->repository->get_credentials( $environment );
		$result = array(
			'publishable_key' => $stored['publishable_key'],
			'secret_key'      => $stored['secret_key'],
		);

		foreach ( array( 'publishable_key', 'secret_key' ) as $field ) {
			if ( ! isset( $staged[ $field ] ) || ! is_string( $staged[ $field ] ) ) {
				continue;
			}

			$value = trim( $staged[ $field ] );

			if ( '' === $value || StripeMasking::is_masked( $value ) ) {
				continue;
			}

			$result[ $field ] = $value;
		}

		return $result;
	}

	/**
	 * Required events for the environment.
	 *
	 * @param string $environment Environment id.
	 * @return string[]
	 */
	private function required_events( string $environment ): array {
		$provider = $this->recurring_provider;

		return StripeWebhookEventRegistry::required_events( (bool) $provider(), $environment );
	}

	/**
	 * Checksum for a diff payload.
	 *
	 * @param array<string, mixed> $diff Diff payload.
	 * @return string
	 */
	public static function diff_checksum( array $diff ): string {
		return hash( 'sha256', wp_json_encode( self::canonicalize( $diff ) ) );
	}

	/**
	 * Token binding a rebind plan to its exact content.
	 *
	 * @param array<string, mixed> $plan Plan payload.
	 * @return string
	 */
	public static function plan_token( array $plan ): string {
		return hash( 'sha256', 'rebind|' . wp_json_encode( self::canonicalize( $plan ) ) );
	}

	/**
	 * Recursively sort arrays so JSON encodes deterministically.
	 *
	 * @param mixed $value Arbitrary value.
	 * @return mixed
	 */
	private static function canonicalize( $value ) {
		if ( ! is_array( $value ) ) {
			return $value;
		}

		foreach ( $value as $key => $item ) {
			$value[ $key ] = self::canonicalize( $item );
		}

		if ( ! self::is_list( $value ) ) {
			ksort( $value );
		}

		return $value;
	}

	/**
	 * Whether an array uses sequential integer keys from zero.
	 *
	 * @param array<mixed> $value Candidate array.
	 * @return bool
	 */
	private static function is_list( array $value ): bool {
		$index = 0;

		foreach ( $value as $key => $item ) {
			if ( $key !== $index ) {
				return false;
			}

			++$index;
		}

		return true;
	}

	/**
	 * Failure envelope.
	 *
	 * @param string               $environment Environment id.
	 * @param string               $code        Error code.
	 * @param array<string, mixed> $extra       Extra response fields.
	 * @return array<string, mixed>
	 */
	private function failure( string $environment, string $code, array $extra = array() ): array {
		$payload = array(
			'success'     => false,
			'code'        => StripeErrorCodes::sanitize( $code ),
			'message'     => StripeErrorCodes::message( StripeErrorCodes::sanitize( $code ) ),
			'environment' => $environment,
			'status'      => 'error',
			'phases'      => array(),
			'writes'      => 0,
			'recovery'    => false,
			'diff'        => array(),
			'connection'  => $this->repository->get_masked_profile( $environment ),
		);

		return array_merge( $payload, $extra );
	}

	/**
	 * Success envelope.
	 *
	 * @param string               $environment Environment id.
	 * @param array<string, mixed> $extra       Extra response fields.
	 * @return array<string, mixed>
	 */
	private function success( string $environment, array $extra = array() ): array {
		$payload = array(
			'success'     => true,
			'code'        => '',
			'message'     => '',
			'environment' => $environment,
			'status'      => 'configured',
			'phases'      => array(),
			'writes'      => 0,
			'recovery'    => false,
			'diff'        => array(),
			'connection'  => $this->repository->get_masked_profile( $environment ),
		);

		return array_merge( $payload, $extra );
	}

	/**
	 * Failure envelope built from validator errors.
	 *
	 * @param string                                $environment Environment id.
	 * @param array<int, array{code:string,message:string}> $errors Validation errors.
	 * @return array<string, mixed>
	 */
	private function first_error( string $environment, array $errors ): array {
		$first = empty( $errors ) ? StripeErrorCodes::MISSING_CREDENTIALS : (string) $errors[0]['code'];

		return $this->failure( $environment, $first, array( 'errors' => $errors ) );
	}
}
