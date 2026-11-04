<?php
/**
 * Stripe connection REST controller.
 *
 * Exposes authenticated, narrowly scoped endpoints for the Stripe connection
 * panel. Requests carry only an environment plus allowlisted fields; every
 * client-supplied identifier (webhook id, URL, account id, opposite profile
 * data) is rejected. Responses never contain credentials or raw Stripe errors.
 *
 * @package GiftFlow
 * @subpackage REST
 */

namespace GiftFlow\REST;

use GiftFlow\Stripe\StripeConnectionMigrator;
use GiftFlow\Stripe\StripeConnectionRepository;
use GiftFlow\Stripe\StripeCredentialsValidator;
use GiftFlow\Stripe\StripeEnvironment;
use GiftFlow\Stripe\StripeErrorCodes;
use GiftFlow\Stripe\StripeMasking;
use GiftFlow\Stripe\StripeWebhookEventRegistry;
use GiftFlow\Stripe\StripeWebhookProvisioner;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles REST API routes for Stripe connection management.
 */
class StripeConnectionController extends \WP_REST_Controller {

	/**
	 * Keys a repair diff may contain.
	 *
	 * @var string[]
	 */
	public const DIFF_KEYS = array( 'endpoint_id', 'checksum', 'fields' );

	/**
	 * Fields a repair diff may change.
	 *
	 * @var string[]
	 */
	public const DIFF_FIELDS = array( 'status', 'url', 'events' );

	/**
	 * Credentials the panel may replace one at a time.
	 *
	 * @var string[]
	 */
	public const CREDENTIAL_FIELDS = array( 'publishable_key', 'secret_key', 'webhook_secret' );

	/**
	 * Repository.
	 *
	 * @var StripeConnectionRepository
	 */
	private StripeConnectionRepository $repository;

	/**
	 * Credential shape validator.
	 *
	 * @var StripeCredentialsValidator
	 */
	private StripeCredentialsValidator $validator;

	/**
	 * Provisioner.
	 *
	 * @var StripeWebhookProvisioner
	 */
	private StripeWebhookProvisioner $provisioner;

	/**
	 * Constructor.
	 *
	 * @param StripeConnectionRepository|null $repository Optional repository.
	 */
	public function __construct( ?StripeConnectionRepository $repository = null ) {
		$this->namespace   = 'giftflow/v2';
		$this->rest_base   = 'stripe';
		$this->repository  = $repository ?? new StripeConnectionRepository();
		$this->provisioner = new StripeWebhookProvisioner( $this->repository );
		$this->validator   = new StripeCredentialsValidator();
	}

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/configuration',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_configuration' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/connect',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'connect' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => $this->mutation_args(),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/check',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'check' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => $this->mutation_args(),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/repair',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'repair' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => $this->mutation_args( array( 'diff' ) ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/rebind-preview',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'rebind_preview' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => $this->mutation_args(),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/rebind',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'rebind' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => $this->mutation_args( array( 'plan_token' ), array( 'plan_token' ) ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/credentials',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'update_credential' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => $this->credential_args(),
				),
			)
		);
	}

	/**
	 * Permission check for every route.
	 *
	 * @return bool|\WP_Error
	 */
	public function permissions_check() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to configure Stripe.', 'giftflow' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Read-only configuration snapshot.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_configuration( \WP_REST_Request $request ) {
		$rejected = $this->reject_unknown_fields( $request, array() );

		if ( is_wp_error( $rejected ) ) {
			return $rejected;
		}

		return rest_ensure_response( $this->configuration_payload() );
	}

	/**
	 * Connect and auto configure an environment.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function connect( \WP_REST_Request $request ) {
		$rejected = $this->reject_unknown_fields( $request, array( 'environment' ) );

		if ( is_wp_error( $rejected ) ) {
			return $rejected;
		}

		$environment = $this->environment_param( $request );

		if ( is_wp_error( $environment ) ) {
			return $environment;
		}

		$result = $this->provisioner->connect( $environment );

		return rest_ensure_response( $this->with_configuration( $result, $environment ) );
	}

	/**
	 * Read-only configuration check.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function check( \WP_REST_Request $request ) {
		$rejected = $this->reject_unknown_fields( $request, array( 'environment' ) );

		if ( is_wp_error( $rejected ) ) {
			return $rejected;
		}

		$environment = $this->environment_param( $request );

		if ( is_wp_error( $environment ) ) {
			return $environment;
		}

		$result = $this->provisioner->check( $environment );

		return rest_ensure_response( $this->with_configuration( $result, $environment ) );
	}

	/**
	 * Apply the exact diff returned by a previous check.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function repair( \WP_REST_Request $request ) {
		$rejected = $this->reject_unknown_fields( $request, array( 'environment', 'diff' ) );

		if ( is_wp_error( $rejected ) ) {
			return $rejected;
		}

		$environment = $this->environment_param( $request );

		if ( is_wp_error( $environment ) ) {
			return $environment;
		}

		$diff = $request->get_param( 'diff' );

		if ( ! is_array( $diff ) ) {
			return new \WP_Error(
				'giftflow_stripe_invalid_diff',
				StripeErrorCodes::message( StripeErrorCodes::INVALID_DIFF ),
				array( 'status' => 400 )
			);
		}

		$diff_rejected = $this->reject_unknown_fields( $request, array( 'environment', 'diff' ), self::DIFF_KEYS );

		if ( is_wp_error( $diff_rejected ) ) {
			return $diff_rejected;
		}

		foreach ( array_keys( $diff ) as $diff_key ) {
			if ( ! in_array( (string) $diff_key, self::DIFF_KEYS, true ) ) {
				return $this->unexpected_field_error();
			}
		}

		if ( isset( $diff['fields'] ) && is_array( $diff['fields'] ) ) {
			foreach ( array_keys( $diff['fields'] ) as $field_key ) {
				if ( ! in_array( (string) $field_key, self::DIFF_FIELDS, true ) ) {
					return $this->unexpected_field_error();
				}
			}
		}

		$result = $this->provisioner->repair( $environment, $this->sanitize_diff( $diff ) );

		return rest_ensure_response( $this->with_configuration( $result, $environment ) );
	}

	/**
	 * Read-only preview of an account rebind.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function rebind_preview( \WP_REST_Request $request ) {
		$rejected = $this->reject_unknown_fields( $request, array( 'environment' ) );

		if ( is_wp_error( $rejected ) ) {
			return $rejected;
		}

		$environment = $this->environment_param( $request );

		if ( is_wp_error( $environment ) ) {
			return $environment;
		}

		$result = $this->provisioner->rebind_preview( $environment );

		return rest_ensure_response( $this->with_configuration( $result, $environment ) );
	}

	/**
	 * Execute a confirmed rebind.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function rebind( \WP_REST_Request $request ) {
		$rejected = $this->reject_unknown_fields( $request, array( 'environment', 'plan_token' ) );

		if ( is_wp_error( $rejected ) ) {
			return $rejected;
		}

		$environment = $this->environment_param( $request );

		if ( is_wp_error( $environment ) ) {
			return $environment;
		}

		$plan_token = $request->get_param( 'plan_token' );

		if ( ! is_string( $plan_token ) || '' === trim( $plan_token ) ) {
			return new \WP_Error(
				'giftflow_stripe_invalid_plan',
				StripeErrorCodes::message( StripeErrorCodes::REBIND_PLAN_STALE ),
				array( 'status' => 400 )
			);
		}

		$result = $this->provisioner->rebind( $environment, array(), sanitize_text_field( $plan_token ) );

		return rest_ensure_response( $this->with_configuration( $result, $environment ) );
	}

	/**
	 * Replace a single stored credential for one environment.
	 *
	 * The panel edits credentials inline, so this route writes exactly one
	 * allowlisted field for exactly one environment and validates its shape
	 * before anything is stored.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function update_credential( \WP_REST_Request $request ) {
		$rejected = $this->reject_unknown_fields( $request, array( 'environment', 'field', 'value' ) );

		if ( is_wp_error( $rejected ) ) {
			return $rejected;
		}

		$environment = $this->environment_param( $request );

		if ( is_wp_error( $environment ) ) {
			return $environment;
		}

		$field = $request->get_param( 'field' );

		if ( ! is_string( $field ) || ! in_array( $field, self::CREDENTIAL_FIELDS, true ) ) {
			return $this->unexpected_field_error();
		}

		$value = $request->get_param( 'value' );
		$value = is_string( $value ) ? trim( sanitize_text_field( $value ) ) : '';

		if ( '' === $value ) {
			return rest_ensure_response(
				$this->with_configuration(
					$this->result_failure( $environment, StripeErrorCodes::MISSING_CREDENTIALS, array( 'credential' => $field ) ),
					$environment
				)
			);
		}

		if ( StripeMasking::is_masked( $value ) ) {
			return rest_ensure_response(
				$this->with_configuration(
					$this->result_failure( $environment, StripeErrorCodes::INVALID_CREDENTIAL_VALUE, array( 'credential' => $field ) ),
					$environment
				)
			);
		}

		if ( 'webhook_secret' !== $field ) {
			$validation = $this->validator->validate_prefixes( $environment, array( $field => $value ), false );

			if ( empty( $validation['valid'] ) ) {
				return rest_ensure_response(
					$this->with_configuration(
						$this->result_failure(
							$environment,
							(string) $validation['errors'][0]['code'],
							array(
								'credential' => $field,
								'errors'     => $validation['errors'],
							)
						),
						$environment
					)
				);
			}
		}

		if ( ! $this->repository->acquire_lock( $environment ) ) {
			return rest_ensure_response(
				$this->with_configuration(
					$this->result_failure( $environment, StripeErrorCodes::LOCKED, array( 'credential' => $field ) ),
					$environment
				)
			);
		}

		try {
			$saved = $this->repository->update_credentials( $environment, array( $field => $value ) );

			if ( ! $saved ) {
				return rest_ensure_response(
					$this->with_configuration(
						$this->result_failure( $environment, StripeErrorCodes::PERSISTENCE_FAILED, array( 'credential' => $field ) ),
						$environment
					)
				);
			}

			// A replaced credential invalidates no stored state, but the previous
			// error no longer describes the current configuration.
			$this->repository->update_profile(
				$environment,
				array(
					'last_error_code' => '',
					'last_error_at' => 0,
				)
			);
		} finally {
			$this->repository->release_lock( $environment );
		}

		return rest_ensure_response(
			$this->with_configuration(
				$this->result_success(
					$environment,
					sprintf(
						/* translators: %s: credential label */
						__( '%s saved. Run Check Configuration to verify this profile.', 'giftflow' ),
						self::credential_label( $field )
					),
					array( 'credential' => $field )
				),
				$environment
			)
		);
	}

	/**
	 * Human readable label for a credential field.
	 *
	 * @param string $field Credential field.
	 * @return string
	 */
	private static function credential_label( string $field ): string {
		switch ( $field ) {
			case 'publishable_key':
				return __( 'Publishable key', 'giftflow' );
			case 'secret_key':
				return __( 'Secret key', 'giftflow' );
			default:
				return __( 'Signing secret', 'giftflow' );
		}
	}

	/**
	 * Operation result envelope for a failed credential write.
	 *
	 * @param string               $environment Environment id.
	 * @param string               $code        Registered error code.
	 * @param array<string, mixed> $extra       Extra fields.
	 * @return array<string, mixed>
	 */
	private function result_failure( string $environment, string $code, array $extra = array() ): array {
		$code = StripeErrorCodes::sanitize( $code );

		return array_merge(
			array(
				'success'     => false,
				'code'        => $code,
				'message'     => StripeErrorCodes::message( $code ),
				'environment' => $environment,
				'status'      => 'error',
				'phases'      => array(),
				'writes'      => 0,
				'recovery'    => false,
				'diff'        => array(),
				'connection'  => $this->repository->get_masked_profile( $environment ),
			),
			$extra
		);
	}

	/**
	 * Operation result envelope for a successful credential write.
	 *
	 * @param string               $environment Environment id.
	 * @param string               $message     Result message.
	 * @param array<string, mixed> $extra       Extra fields.
	 * @return array<string, mixed>
	 */
	private function result_success( string $environment, string $message, array $extra = array() ): array {
		return array_merge(
			array(
				'success'     => true,
				'code'        => '',
				'message'     => $message,
				'environment' => $environment,
				'status'      => 'updated',
				'phases'      => array(),
				'writes'      => 1,
				'recovery'    => false,
				'diff'        => array(),
				'connection'  => $this->repository->get_masked_profile( $environment ),
			),
			$extra
		);
	}

	/**
	 * Route argument definitions.
	 *
	 * @param array<int, string> $optional Additional optional fields.
	 * @param array<int, string> $required Fields that must be present.
	 * @return array<string, mixed>
	 */
	private function mutation_args( array $optional = array(), array $required = array() ): array {
		$args = array(
			'environment' => array(
				'type'        => 'string',
				'required'    => true,
				'enum'        => StripeEnvironment::all(),
				'description' => __( 'Stripe environment profile.', 'giftflow' ),
			),
		);

		if ( in_array( 'plan_token', $optional, true ) ) {
			$args['plan_token'] = array(
				'type'     => 'string',
				'required' => in_array( 'plan_token', $required, true ),
			);
		}

		if ( in_array( 'diff', $optional, true ) ) {
			$args['diff'] = array(
				'type'     => 'object',
				'required' => true,
			);
		}

		return $args;
	}

	/**
	 * Route argument definitions for the credential route.
	 *
	 * @return array<string, mixed>
	 */
	private function credential_args(): array {
		return array(
			'environment' => array(
				'type'        => 'string',
				'required'    => true,
				'enum'        => StripeEnvironment::all(),
				'description' => __( 'Stripe environment profile.', 'giftflow' ),
			),
			'field'       => array(
				'type'        => 'string',
				'required'    => true,
				'enum'        => self::CREDENTIAL_FIELDS,
				'description' => __( 'Credential to replace.', 'giftflow' ),
			),
			'value'       => array(
				'type'        => 'string',
				'required'    => true,
				'description' => __( 'New credential value.', 'giftflow' ),
			),
		);
	}

	/**
	 * Validate and normalize the environment parameter.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return string|\WP_Error
	 */
	private function environment_param( \WP_REST_Request $request ) {
		$environment = $request->get_param( 'environment' );

		if ( ! StripeEnvironment::is_valid( $environment ) ) {
			return new \WP_Error(
				'giftflow_stripe_invalid_environment',
				__( 'Select either the Sandbox or Live profile.', 'giftflow' ),
				array( 'status' => 400 )
			);
		}

		return (string) $environment;
	}

	/**
	 * Shared error for fields outside a route's allowlist.
	 *
	 * @return \WP_Error
	 */
	private function unexpected_field_error(): \WP_Error {
		return new \WP_Error(
			'giftflow_stripe_unexpected_field',
			StripeErrorCodes::message( StripeErrorCodes::UNEXPECTED_FIELD ),
			array(
				'status' => 400,
				'code'   => StripeErrorCodes::UNEXPECTED_FIELD,
			)
		);
	}

	/**
	 * Reject fields outside the allowlist for a route.
	 *
	 * @param \WP_REST_Request       $request   Request object.
	 * @param array<int, string>     $allowed   Allowed top-level fields.
	 * @param array<int, string>|null $diff_keys Allowed keys inside a diff payload.
	 * @return \WP_Error|null
	 */
	private function reject_unknown_fields( \WP_REST_Request $request, array $allowed, ?array $diff_keys = null ) {
		$params = $request->get_json_params();

		if ( ! is_array( $params ) ) {
			$params = $request->get_params();
		}

		if ( ! is_array( $params ) ) {
			return null;
		}

		foreach ( array_keys( $params ) as $key ) {
			if ( ! in_array( (string) $key, $allowed, true ) ) {
				return $this->unexpected_field_error();
			}
		}

		if ( null === $diff_keys || ! isset( $params['diff'] ) || ! is_array( $params['diff'] ) ) {
			return null;
		}

		foreach ( array_keys( $params['diff'] ) as $key ) {
			if ( ! in_array( (string) $key, $diff_keys, true ) ) {
				return $this->unexpected_field_error();
			}
		}

		return null;
	}

	/**
	 * Sanitize an approved diff payload.
	 *
	 * @param array<string, mixed> $diff Raw diff.
	 * @return array<string, mixed>
	 */
	private function sanitize_diff( array $diff ): array {
		$sanitized = array(
			'endpoint_id' => '',
			'checksum'    => '',
			'fields'      => array(),
		);

		if ( isset( $diff['endpoint_id'] ) && is_string( $diff['endpoint_id'] ) ) {
			$sanitized['endpoint_id'] = sanitize_text_field( $diff['endpoint_id'] );
		}

		if ( isset( $diff['checksum'] ) && is_string( $diff['checksum'] ) ) {
			$sanitized['checksum'] = sanitize_text_field( $diff['checksum'] );
		}

		if ( isset( $diff['fields'] ) && is_array( $diff['fields'] ) ) {
			foreach ( $diff['fields'] as $key => $field ) {
				if ( ! in_array( (string) $key, array( 'status', 'url', 'events' ), true ) || ! is_array( $field ) ) {
					continue;
				}

				$sanitized['fields'][ (string) $key ] = $field;
			}
		}

		return $sanitized;
	}

	/**
	 * Add the refreshed configuration snapshot to an operation result.
	 *
	 * @param array<string, mixed> $result      Provisioner result.
	 * @param string               $environment Environment id.
	 * @return array<string, mixed>
	 */
	private function with_configuration( array $result, string $environment ): array {
		$result['configuration'] = $this->configuration_payload();

		return $result;
	}

	/**
	 * Read-only configuration payload shared by every route.
	 *
	 * @return array<string, mixed>
	 */
	private function configuration_payload(): array {
		$recurring = $this->repository->is_recurring_enabled();
		$profiles  = array();

		foreach ( StripeEnvironment::all() as $environment ) {
			$profiles[ $environment ] = $this->repository->get_masked_profile( $environment );
			$profiles[ $environment ]['events_required'] = StripeWebhookEventRegistry::required_events( $recurring, $environment );
		}

		$migration = $this->repository->get_migration_state();

		return array(
			'active_environment'   => $this->repository->get_active_environment(),
			'environments'         => StripeEnvironment::all(),
			'profiles'             => $profiles,
			'events_required'      => StripeWebhookEventRegistry::required_events( $recurring ),
			'recurring_enabled'    => $recurring,
			'webhook_enabled'      => $this->repository->is_webhook_enabled(),
			'migration'            => array(
				'version'     => (int) ( $migration['version'] ?? 0 ),
				'migrated_at' => (int) ( $migration['migrated_at'] ?? 0 ),
				'status'      => (int) ( $migration['version'] ?? 0 ) >= StripeConnectionMigrator::VERSION
					? StripeConnectionMigrator::STATUS_MIGRATED
					: 'pending',
				'code'        => StripeErrorCodes::sanitize( $migration['error'] ?? '' ),
			),
		);
	}
}
