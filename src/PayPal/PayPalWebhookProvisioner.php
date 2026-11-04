<?php
/**
 * Safe, idempotent PayPal webhook provisioning.
 *
 * @package GiftFlow
 * @subpackage PayPal
 */

namespace GiftFlow\PayPal;

// phpcs:disable Generic.Commenting.DocComment.MissingShort,Squiz.Commenting.FunctionComment.MissingParamTag -- Existing concise method annotations accurately describe the internal provisioning operations.

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates, verifies and repairs environment-scoped PayPal webhooks.
 */
final class PayPalWebhookProvisioner {

	public const PHASE_CHECKING_CREDENTIALS = 'checking_credentials';
	public const PHASE_FINDING_WEBHOOK = 'finding_webhook';
	public const PHASE_CONFIGURING_EVENTS = 'configuring_events';
	public const PHASE_SAVING_LOCAL = 'saving_local_configuration';
	public const PHASE_COMPLETE = 'complete';
	public const PLAN_OPTION = 'giftflow_paypal_rebind_plan';
	public const PLAN_TTL = 600;

	/** @var PayPalConnectionRepository */
	private PayPalConnectionRepository $repository;

	/** @var PayPalCredentialsValidator */
	private PayPalCredentialsValidator $validator;

	/** @var callable */
	private $client_factory;

	/**
	 * Constructor.
	 *
	 * @param PayPalConnectionRepository|null $repository    Connection repository.
	 * @param callable|null                   $client_factory Client factory.
	 */
	public function __construct( ?PayPalConnectionRepository $repository = null, ?callable $client_factory = null ) {
		$this->repository     = $repository ?? new PayPalConnectionRepository();
		$this->validator      = new PayPalCredentialsValidator();
		$this->client_factory = $client_factory ?? static function ( string $environment, string $client_id, string $client_secret, PayPalConnectionRepository $repository ): PayPalApiClient {
			return new PayPalApiClient( $environment, $client_id, $client_secret, $repository );
		};
	}

	/**
	 * Connect an environment.
	 *
	 * @param string $environment Environment id.
	 * @return array<string, mixed>
	 */
	public function connect( string $environment ): array {
		$environment = PayPalEnvironment::assert_valid( $environment );
		if ( ! $this->repository->acquire_lock( $environment ) ) {
			return $this->failure( $environment, PayPalErrorCodes::LOCKED );
		}
		try {
			return $this->run_connect( $environment, false );
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
		$environment = PayPalEnvironment::assert_valid( $environment );
		if ( ! $this->repository->acquire_lock( $environment ) ) {
			return $this->failure( $environment, PayPalErrorCodes::LOCKED );
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
	 * Apply an approved check diff.
	 *
	 * @param string               $environment   Environment id.
	 * @param array<string, mixed> $approved_diff Approved diff.
	 * @return array<string, mixed>
	 */
	public function repair( string $environment, array $approved_diff ): array {
		$environment = PayPalEnvironment::assert_valid( $environment );
		if ( ! $this->repository->acquire_lock( $environment ) ) {
			return $this->failure( $environment, PayPalErrorCodes::LOCKED );
		}
		try {
			return $this->run_repair( $environment, $approved_diff );
		} finally {
			$this->repository->release_lock( $environment );
		}
	}

	/**
	 * Preview an account rebind.
	 *
	 * @param string $environment Environment id.
	 * @return array<string, mixed>
	 */
	public function rebind_preview( string $environment ): array {
		$environment = PayPalEnvironment::assert_valid( $environment );
		if ( ! $this->repository->acquire_lock( $environment ) ) {
			return $this->failure( $environment, PayPalErrorCodes::LOCKED );
		}
		try {
			return $this->run_rebind_preview( $environment, true );
		} finally {
			$this->repository->release_lock( $environment );
		}
	}

	/**
	 * Execute a confirmed rebind.
	 *
	 * @param string $environment Environment id.
	 * @param string $plan_token Plan token.
	 * @return array<string, mixed>
	 */
	public function rebind( string $environment, string $plan_token ): array {
		$environment = PayPalEnvironment::assert_valid( $environment );
		if ( ! $this->repository->acquire_lock( $environment ) ) {
			return $this->failure( $environment, PayPalErrorCodes::LOCKED );
		}
		try {
			$preview = $this->run_rebind_preview( $environment, false );
			$stored  = get_option( self::PLAN_OPTION, array() );
			if (
				empty( $preview['success'] )
				|| ! is_array( $stored )
				|| (int) ( $stored['expires_at'] ?? 0 ) < time()
				|| (string) ( $stored['environment'] ?? '' ) !== $environment
				|| ! hash_equals( (string) ( $stored['token'] ?? '' ), $plan_token )
				|| ! hash_equals( (string) ( $preview['plan_token'] ?? '' ), $plan_token )
			) {
				return $this->failure( $environment, PayPalErrorCodes::REBIND_PLAN_STALE );
			}
			delete_option( self::PLAN_OPTION );
			$profile = $this->repository->get_profile( $environment );
			$old_id  = (string) $profile['webhook_id'];
			$previous = isset( $profile['previous_webhook_ids'] ) && is_array( $profile['previous_webhook_ids'] ) ? $profile['previous_webhook_ids'] : array();
			if ( '' !== $old_id ) {
				$previous[] = $old_id;
			}
			$target = $preview['plan']['target'];
			if (
				! $this->repository->update_profile(
					$environment,
					array(
						'webhook_id'              => '',
						'webhook_url'             => '',
						'webhook_owner'           => PayPalConnectionRepository::OWNER_UNKNOWN,
						'events'                  => array(),
						'paypal_app_id'           => (string) $target['paypal_app_id'],
						'credential_fingerprint'  => (string) $target['credential_fingerprint'],
						'binding_fingerprint'     => (string) $target['binding_fingerprint'],
						'previous_webhook_ids'    => array_values( array_unique( $previous ) ),
					)
				)
			) {
				return $this->failure( $environment, PayPalErrorCodes::PERSISTENCE_FAILED );
			}
			$result = $this->run_connect( $environment, true );
			if ( ! empty( $result['success'] ) ) {
				$result['plan'] = $preview['plan'];
			}
			return $result;
		} finally {
			$this->repository->release_lock( $environment );
		}
	}

	/**
	 * Canonical webhook URL.
	 *
	 * @return string
	 */
	public function webhook_url(): string {
		$gateway = function_exists( 'giftflow_get_paypal_gateway' ) ? giftflow_get_paypal_gateway() : null;
		$url = $gateway && method_exists( $gateway, 'get_webhook_url' ) ? (string) $gateway->get_webhook_url() : (string) admin_url( 'admin-ajax.php?action=giftflow_paypal_webhook' );
		return self::normalize_url( $url );
	}

	/**
	 * Normalize a webhook URL.
	 *
	 * @param string $url URL.
	 * @return string
	 */
	public static function normalize_url( string $url ): string {
		$url = trim( $url );
		$parts = wp_parse_url( $url );
		if ( '' === $url || ! is_array( $parts ) || empty( $parts['host'] ) || 'http' !== strtolower( (string) ( $parts['scheme'] ?? '' ) ) ) {
			return $url;
		}
		$home_scheme = strtolower( (string) wp_parse_url( (string) home_url(), PHP_URL_SCHEME ) );
		if ( 'https' !== $home_scheme && ( ! function_exists( 'is_ssl' ) || ! is_ssl() ) ) {
			return $url;
		}
		return function_exists( 'set_url_scheme' ) ? (string) set_url_scheme( $url, 'https' ) : (string) preg_replace( '#^http://#i', 'https://', $url );
	}

	/**
	 * Whether a URL is public HTTPS.
	 *
	 * @param string $url URL.
	 * @return bool
	 */
	public static function is_public_url( string $url ): bool {
		$parts = wp_parse_url( trim( $url ) );
		$valid = is_array( $parts ) && ! empty( $parts['host'] ) && 'https' === strtolower( (string) ( $parts['scheme'] ?? '' ) );
		$host  = $valid ? strtolower( rtrim( (string) $parts['host'], '.' ) ) : '';
		if ( in_array( $host, array( 'localhost', '127.0.0.1', '::1', '[::1]' ), true ) || false === strpos( $host, '.' ) ) {
			$valid = false;
		}
		foreach ( array( '.local', '.localhost', '.test', '.invalid', '.example', '.internal' ) as $suffix ) {
			if ( '' !== $host && substr( $host, -strlen( $suffix ) ) === $suffix ) {
				$valid = false;
			}
		}
		if ( $valid && filter_var( $host, FILTER_VALIDATE_IP ) ) {
			$valid = (bool) filter_var( $host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE );
		}
		return (bool) apply_filters( 'giftflow_paypal_webhook_url_is_public', $valid, $url );
	}

	/** Perform connect. */
	private function run_connect( string $environment, bool $allow_rebind ): array {
		$url = $this->webhook_url();
		if ( ! self::is_public_url( $url ) ) {
			return $this->failure( $environment, PayPalErrorCodes::NON_PUBLIC_URL, array( 'url' => $url ) );
		}
		$credentials = $this->resolve_credentials( $environment );
		$validation  = $this->validator->validate( $environment, $credentials );
		if ( empty( $validation['valid'] ) ) {
			return $this->first_error( $environment, (array) $validation['errors'] );
		}
		$phases  = array( self::PHASE_CHECKING_CREDENTIALS, self::PHASE_FINDING_WEBHOOK );
		$profile = $this->repository->get_profile( $environment );
		$app_id  = PayPalEnvironment::app_identity( $environment, $credentials['client_id'], (string) ( $validation['app_id'] ?? '' ) );
		$binding = $this->repository->binding_fingerprint( $environment, $app_id );
		if ( '' !== (string) $profile['binding_fingerprint'] && (string) $profile['binding_fingerprint'] !== $binding && ! $allow_rebind ) {
			return $this->failure(
				$environment,
				PayPalErrorCodes::REBIND_REQUIRED,
				array(
					'phases' => $phases,
					'requires_rebind' => true,
				)
			);
		}
		$client = $this->create_client( $environment, $credentials );
		$found  = $this->reconcile_endpoint( $client, $environment, $url, $profile, $app_id, $binding );
		if ( isset( $found['error'] ) ) {
			return $this->failure(
				$environment,
				(string) $found['error'],
				array(
					'phases'          => $phases,
					'recovery'        => ! empty( $found['recovery'] ),
					'requires_rebind' => PayPalErrorCodes::ENDPOINT_OWNERSHIP_UNKNOWN === $found['error'],
				)
			);
		}
		$endpoint = $found['endpoint'];
		$writes   = 0;
		$events   = $this->required_events( $environment );
		if ( null === $endpoint ) {
			$created = $this->create_endpoint( $client, $url, $events );
			if ( isset( $created['error'] ) ) {
				$recovery = array(
					'operation' => 'create',
					'started_at' => time(),
					'url' => $url,
					'events' => $events,
				);
				$this->repository->update_profile(
					$environment,
					array(
						'recovery'        => $recovery,
						'setup_status'    => PayPalConnectionRepository::STATUS_RECOVERY_PENDING,
						'last_error_code' => (string) $created['error'],
						'last_error_at'   => time(),
					)
				);
				return $this->failure(
					$environment,
					(string) $created['error'],
					array(
						'phases' => $phases,
						'recovery' => true,
					)
				);
			}
			$endpoint = $created['endpoint'];
			$writes = 1;
		} elseif ( ! $this->endpoint_is_synced( $endpoint, $url, $events ) ) {
			$response = $client->update_webhook( (string) $endpoint['id'], $url, $events );
			if ( empty( $response['ok'] ) ) {
				return $this->failure( $environment, (string) $response['code'], array( 'phases' => $phases ) );
			}
			$endpoint = (array) $response['body'];
			++$writes;
		}
		$phases[] = self::PHASE_CONFIGURING_EVENTS;
		$phases[] = self::PHASE_SAVING_LOCAL;
		$previous = isset( $profile['previous_webhook_ids'] ) ? (array) $profile['previous_webhook_ids'] : array();
		if ( '' !== (string) $profile['webhook_id'] && (string) $profile['webhook_id'] !== (string) $endpoint['id'] ) {
			$previous[] = (string) $profile['webhook_id'];
		}
		$saved = $this->repository->update_profile(
			$environment,
			array(
				'webhook_id'              => (string) $endpoint['id'],
				'webhook_url'             => $url,
				'webhook_owner'           => PayPalConnectionRepository::OWNER_PLUGIN,
				'events'                  => $events,
				'paypal_app_id'           => $app_id,
				'credential_fingerprint'  => PayPalConnectionRepository::credential_fingerprint( $environment, $credentials['client_id'] ),
				'binding_fingerprint'     => $binding,
				'setup_status'            => PayPalConnectionRepository::STATUS_CONFIGURED,
				'last_checked_at'         => time(),
				'last_error_code'         => '',
				'last_error_at'           => 0,
				'recovery'                => array(),
				'previous_webhook_ids'    => array_values( array_unique( $previous ) ),
			)
		);
		if ( ! $saved ) {
			$this->repository->update_profile(
				$environment,
				array(
					'recovery'        => array(
						'operation'  => 'persist',
						'started_at' => time(),
						'url'        => $url,
						'webhook_id' => (string) $endpoint['id'],
					),
					'setup_status'    => PayPalConnectionRepository::STATUS_RECOVERY_PENDING,
					'last_error_code' => PayPalErrorCodes::PERSISTENCE_FAILED,
					'last_error_at'   => time(),
				)
			);
			return $this->failure(
				$environment,
				PayPalErrorCodes::PERSISTENCE_FAILED,
				array(
					'phases' => $phases,
					'recovery' => true,
					'writes' => $writes,
				)
			);
		}
		$phases[] = self::PHASE_COMPLETE;
		return $this->success(
			$environment,
			array(
				'phases' => $phases,
				'writes' => $writes,
				'message' => __( 'PayPal is connected and the webhook endpoint is active.', 'giftflow' ),
			)
		);
	}

	/** Perform read-only check. */
	private function run_check( string $environment ): array {
		$profile = $this->repository->get_profile( $environment );
		$credentials = $this->resolve_credentials( $environment );
		if ( '' === $credentials['client_id'] || '' === $credentials['client_secret'] || '' === (string) $profile['webhook_id'] ) {
			return $this->failure( $environment, PayPalErrorCodes::NOT_CONFIGURED, array( 'status' => 'not_configured' ) );
		}
		$client = $this->create_client( $environment, $credentials );
		$response = $client->get_webhook( (string) $profile['webhook_id'] );
		if ( empty( $response['ok'] ) ) {
			$code = 404 === (int) $response['status'] ? PayPalErrorCodes::ENDPOINT_MISSING : (string) $response['code'];
			return $this->failure( $environment, $code, array( 'status' => 'missing' ) );
		}
		$endpoint = (array) $response['body'];
		if ( ! $this->endpoint_matches_environment( $endpoint, $profile ) ) {
			return $this->failure( $environment, PayPalErrorCodes::ENDPOINT_NOT_OWNED );
		}
		$url = $this->webhook_url();
		$required = $this->required_events( $environment );
		$current = $this->endpoint_events( $endpoint );
		$fields = array();
		$findings = array();
		if ( (string) ( $endpoint['url'] ?? '' ) !== $url ) {
			$fields['url'] = array(
				'from' => (string) ( $endpoint['url'] ?? '' ),
				'to' => $url,
			);
			$findings[] = PayPalErrorCodes::URL_DRIFT;
		}
		if ( $current !== $required ) {
			$fields['events'] = array(
				'from' => $current,
				'to' => $required,
			);
			$findings[] = PayPalErrorCodes::EVENT_DRIFT;
		}
		$diff = array(
			'webhook_id' => (string) $endpoint['id'],
			'fields'     => $fields,
			'findings'   => $findings,
			'ownership'  => PayPalConnectionRepository::OWNER_PLUGIN,
			'checksum'   => self::endpoint_checksum( (string) $endpoint['id'], (string) ( $endpoint['url'] ?? '' ), $current ),
		);
		if ( empty( $findings ) ) {
			return $this->success(
				$environment,
				array(
					'status' => 'in_sync',
					'diff' => $diff,
					'repairable' => false,
				)
			);
		}
		return $this->failure(
			$environment,
			(string) $findings[0],
			array(
				'status' => 'drift',
				'diff' => $diff,
				'findings' => $findings,
				'repairable' => true,
			)
		);
	}

	/** Perform repair. */
	private function run_repair( string $environment, array $approved_diff ): array {
		$fresh = $this->run_check( $environment );
		$diff = isset( $fresh['diff'] ) ? (array) $fresh['diff'] : array();
		$approved_id = (string) ( $approved_diff['webhook_id'] ?? '' );
		$approved_sum = (string) ( $approved_diff['checksum'] ?? '' );
		$approved_fields = isset( $approved_diff['fields'] ) && is_array( $approved_diff['fields'] ) ? array_keys( $approved_diff['fields'] ) : array();
		$fresh_fields = isset( $diff['fields'] ) && is_array( $diff['fields'] ) ? array_keys( $diff['fields'] ) : array();
		sort( $approved_fields );
		sort( $fresh_fields );
		if (
			'' === $approved_id
			|| (string) ( $diff['webhook_id'] ?? '' ) !== $approved_id
			|| $fresh_fields !== $approved_fields
			|| '' === $approved_sum
			|| ! hash_equals( (string) ( $diff['checksum'] ?? '' ), $approved_sum )
		) {
			return $this->failure( $environment, PayPalErrorCodes::INVALID_DIFF );
		}
		if ( PayPalConnectionRepository::OWNER_PLUGIN !== (string) ( $diff['ownership'] ?? '' ) ) {
			return $this->failure( $environment, PayPalErrorCodes::ENDPOINT_NOT_OWNED );
		}
		foreach ( $approved_fields as $field ) {
			if ( ! in_array( $field, array( 'status', 'url', 'events' ), true ) ) {
				return $this->failure( $environment, PayPalErrorCodes::INVALID_DIFF );
			}
		}
		if ( empty( $approved_fields ) ) {
			return $this->failure( $environment, PayPalErrorCodes::INVALID_DIFF );
		}
		$credentials = $this->resolve_credentials( $environment );
		$client = $this->create_client( $environment, $credentials );
		$response = $client->update_webhook( $approved_id, $this->webhook_url(), $this->required_events( $environment ) );
		if ( empty( $response['ok'] ) ) {
			return $this->failure( $environment, (string) $response['code'] );
		}
		if (
			! $this->repository->update_profile(
				$environment,
				array(
					'webhook_url'     => $this->webhook_url(),
					'events'          => $this->required_events( $environment ),
					'last_checked_at' => time(),
					'last_error_code' => '',
					'last_error_at'   => 0,
				)
			)
		) {
			return $this->failure( $environment, PayPalErrorCodes::PERSISTENCE_FAILED, array( 'writes' => 1 ) );
		}
		return $this->success(
			$environment,
			array(
				'status' => 'repaired',
				'writes' => 1,
				'diff' => $diff,
				'message' => __( 'PayPal webhook endpoint updated.', 'giftflow' ),
			)
		);
	}

	/** Build rebind preview. */
	private function run_rebind_preview( string $environment, bool $store ): array {
		$url = $this->webhook_url();
		if ( ! self::is_public_url( $url ) ) {
			return $this->failure( $environment, PayPalErrorCodes::NON_PUBLIC_URL, array( 'url' => $url ) );
		}
		$credentials = $this->resolve_credentials( $environment );
		$validation = $this->validator->validate( $environment, $credentials );
		if ( empty( $validation['valid'] ) ) {
			return $this->first_error( $environment, (array) $validation['errors'] );
		}
		$profile = $this->repository->get_profile( $environment );
		$app_id = PayPalEnvironment::app_identity( $environment, $credentials['client_id'], (string) ( $validation['app_id'] ?? '' ) );
		$credential_fp = PayPalConnectionRepository::credential_fingerprint( $environment, $credentials['client_id'] );
		$binding = $this->repository->binding_fingerprint( $environment, $app_id );
		$plan = array(
			'environment'                => $environment,
			'url'                        => $url,
			'previous'                   => array(
				'paypal_app_id'       => (string) $profile['paypal_app_id'],
				'binding_fingerprint' => (string) $profile['binding_fingerprint'],
				'webhook_id'          => (string) $profile['webhook_id'],
			),
			'target'                     => array(
				'paypal_app_id'          => $app_id,
				'credential_fingerprint' => $credential_fp,
				'binding_fingerprint'    => $binding,
				'events'                 => $this->required_events( $environment ),
			),
			'abandoned_options'          => $this->abandoned_options( $environment, $profile ),
			'previous_endpoint_retained' => (string) $profile['webhook_id'],
		);
		$token = self::plan_token( $plan );
		if ( $store ) {
			update_option(
				self::PLAN_OPTION,
				array(
					'environment' => $environment,
					'token'       => $token,
					'expires_at'  => time() + self::PLAN_TTL,
				),
				false
			);
		}
		return $this->success(
			$environment,
			array(
				'status'     => 'rebind_preview',
				'plan'       => $plan,
				'plan_token' => $token,
				'diff'       => array(
					'webhook_id' => (string) $profile['webhook_id'],
					'fields'     => array(
						'paypal_app_id'       => array(
							'from' => (string) $profile['paypal_app_id'],
							'to' => $app_id,
						),
						'binding_fingerprint' => array(
							'from' => (string) $profile['binding_fingerprint'],
							'to' => $binding,
						),
					),
					'checksum'   => self::endpoint_checksum( (string) $profile['webhook_id'], $url, $this->required_events( $environment ) ),
				),
			)
		);
	}

	/** Reconcile stored endpoint and ambiguous-create recovery. */
	private function reconcile_endpoint( PayPalApiClient $client, string $environment, string $url, array $profile, string $app_id, string $binding ): array {
		$stored_id = (string) $profile['webhook_id'];
		if ( '' !== $stored_id ) {
			$endpoint = $this->retrieve_endpoint( $client, $stored_id );
			if ( null !== $endpoint ) {
				if ( (string) $profile['paypal_app_id'] !== $app_id || (string) $profile['binding_fingerprint'] !== $binding || (string) ( $endpoint['url'] ?? '' ) !== $url ) {
					return array( 'error' => PayPalErrorCodes::ENDPOINT_NOT_OWNED );
				}
				return array( 'endpoint' => $endpoint );
			}
		}
		$matches = $this->find_endpoints_by_url( $client, $url );
		if ( ! empty( $matches ) ) {
			return array( 'error' => PayPalErrorCodes::ENDPOINT_OWNERSHIP_UNKNOWN );
		}
		if ( ! empty( $profile['recovery'] ) ) {
			return array(
				'error' => PayPalErrorCodes::AMBIGUOUS_CREATE,
				'recovery' => true,
			);
		}
		return array( 'endpoint' => null );
	}

	/** Create endpoint. */
	private function create_endpoint( PayPalApiClient $client, string $url, array $events ): array {
		$response = $client->create_webhook( $url, $events );
		if ( empty( $response['ok'] ) ) {
			return array( 'error' => ! empty( $response['temporary'] ) ? PayPalErrorCodes::AMBIGUOUS_CREATE : (string) $response['code'] );
		}
		$endpoint = (array) $response['body'];
		if ( empty( $endpoint['id'] ) ) {
			return array( 'error' => PayPalErrorCodes::AMBIGUOUS_CREATE );
		}
		return array( 'endpoint' => $endpoint );
	}

	/** Retrieve endpoint or null. */
	private function retrieve_endpoint( PayPalApiClient $client, string $id ): ?array {
		$response = $client->get_webhook( $id );
		return ! empty( $response['ok'] ) ? (array) $response['body'] : null;
	}

	/** Find URL matches. */
	private function find_endpoints_by_url( PayPalApiClient $client, string $url ): array {
		$response = $client->list_webhooks();
		if ( empty( $response['ok'] ) ) {
			return array();
		}
		$matches = array();
		foreach ( (array) ( $response['body']['webhooks'] ?? array() ) as $endpoint ) {
			$endpoint = (array) $endpoint;
			if ( (string) ( $endpoint['url'] ?? '' ) === $url ) {
				$matches[] = $endpoint;
			}
		}
		return $matches;
	}

	/** Pick endpoint. */
	private function pick_endpoint( array $matches ): ?array {
		return empty( $matches ) ? null : (array) $matches[0];
	}

	/** Verify locally stored ownership context. */
	private function endpoint_matches_environment( array $endpoint, array $profile ): bool {
		return (string) ( $endpoint['id'] ?? '' ) === (string) $profile['webhook_id']
			&& PayPalConnectionRepository::OWNER_PLUGIN === (string) $profile['webhook_owner']
			&& '' !== (string) $profile['paypal_app_id']
			&& '' !== (string) $profile['binding_fingerprint'];
	}

	/** Whether endpoint is synchronized. */
	private function endpoint_is_synced( array $endpoint, string $url, array $events ): bool {
		return (string) ( $endpoint['url'] ?? '' ) === $url && $this->endpoint_events( $endpoint ) === $events;
	}

	/** Update parameter description. */
	private function update_params( array $endpoint, string $url, array $events ): array {
		$params = array();
		if ( (string) ( $endpoint['url'] ?? '' ) !== $url ) {
			$params['url'] = $url;
		}
		if ( $this->endpoint_events( $endpoint ) !== $events ) {
			$params['events'] = $events;
		}
		return $params;
	}

	/** Resolve stored credentials. */
	private function resolve_credentials( string $environment ): array {
		$credentials = $this->repository->get_credentials( $environment );
		return array(
			'client_id' => trim( (string) ( $credentials['client_id'] ?? '' ) ),
			'client_secret' => trim( (string) ( $credentials['client_secret'] ?? '' ) ),
		);
	}

	/** Required event names. */
	private function required_events( string $environment ): array {
		$paypal = $this->repository->get_paypal_subtree();
		$recurring = ! empty( $paypal['paypal_recurring_enabled'] );
		return PayPalWebhookEventRegistry::required_events( $recurring, $environment );
	}

	/** Create API client. */
	private function create_client( string $environment, array $credentials ): PayPalApiClient {
		$factory = $this->client_factory;
		return $factory( $environment, $credentials['client_id'], $credentials['client_secret'], $this->repository );
	}

	/** Normalize endpoint events. */
	private function endpoint_events( array $endpoint ): array {
		$events = array();
		foreach ( (array) ( $endpoint['event_types'] ?? array() ) as $event ) {
			$name = is_array( $event ) ? (string) ( $event['name'] ?? '' ) : '';
			if ( '' !== $name ) {
				$events[] = $name;
			}
		}
		$events = array_values( array_unique( $events ) );
		sort( $events, SORT_STRING );
		return $events;
	}

	/** Find resource cache options abandoned by rebind. */
	private function abandoned_options( string $environment, array $profile ): array {
		global $wpdb;
		$scope = substr( hash( 'sha256', implode( '|', array( 'v1', $environment, (string) $profile['credential_fingerprint'], (string) $profile['binding_fingerprint'] ) ) ), 0, 16 );
		$names = array( $this->repository->product_option_key( $environment ) );
		if ( isset( $wpdb ) ) {
			$like = $wpdb->esc_like( 'giftflow_paypal_plan_' . $environment . '_' . $scope . '_' ) . '%';
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Option names must be enumerated for an explicit rebind plan.
			$found = $wpdb->get_col( $wpdb->prepare( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s", $like ) );
			$names = array_merge( $names, is_array( $found ) ? $found : array() );
		}
		$names = array_values( array_unique( array_filter( array_map( 'strval', $names ) ) ) );
		sort( $names, SORT_STRING );
		return $names;
	}

	/** Endpoint checksum. */
	private static function endpoint_checksum( string $id, string $url, array $events ): string {
		$events = array_values( array_unique( array_map( 'strval', $events ) ) );
		sort( $events, SORT_STRING );
		return hash( 'sha256', $id . '|' . $url . '|' . implode( ',', $events ) );
	}

	/** Rebind plan token. */
	private static function plan_token( array $plan ): string {
		$material = wp_json_encode( self::canonicalize( $plan ) );
		return hash_hmac( 'sha256', (string) $material, defined( 'AUTH_SALT' ) ? AUTH_SALT : '' );
	}

	/** Canonicalize nested arrays. */
	private static function canonicalize( $value ) {
		if ( ! is_array( $value ) ) {
			return $value;
		}
		foreach ( $value as $key => $item ) {
			$value[ $key ] = self::canonicalize( $item );
		}
		if ( array_keys( $value ) !== range( 0, count( $value ) - 1 ) ) {
			ksort( $value );
		}
		return $value;
	}

	/** Failure envelope. */
	private function failure( string $environment, string $code, array $extra = array() ): array {
		$code = PayPalErrorCodes::sanitize( $code );
		return array_merge(
			array(
				'success'     => false,
				'environment' => $environment,
				'code'        => $code,
				'message'     => PayPalErrorCodes::message( $code ),
				'phases'      => array(),
				'status'      => 'error',
				'writes'      => 0,
				'recovery'    => false,
				'diff'        => array(),
			),
			$extra
		);
	}

	/** Success envelope. */
	private function success( string $environment, array $extra = array() ): array {
		return array_merge(
			array(
				'success'     => true,
				'environment' => $environment,
				'code'        => '',
				'message'     => '',
				'phases'      => array(),
				'status'      => 'configured',
				'writes'      => 0,
				'recovery'    => false,
				'diff'        => array(),
			),
			$extra
		);
	}

	/** Convert validator errors to an envelope. */
	private function first_error( string $environment, array $errors ): array {
		$code = empty( $errors ) ? PayPalErrorCodes::MISSING_CREDENTIALS : (string) ( $errors[0]['code'] ?? PayPalErrorCodes::MISSING_CREDENTIALS );
		return $this->failure( $environment, $code, array( 'errors' => $errors ) );
	}
}
