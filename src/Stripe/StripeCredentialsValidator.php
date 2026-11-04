<?php
/**
 * Stripe credential validation and permission diagnostics.
 *
 * Produces stable, sanitized error codes and never echoes raw Stripe text or
 * credential material back to the caller.
 *
 * @package GiftFlow
 * @subpackage Stripe
 */

namespace GiftFlow\Stripe;

use GiftFlow\Vendor\Stripe\StripeClient;
use GiftFlow\Vendor\Stripe\Exception\ApiErrorException;
use GiftFlow\Vendor\Stripe\Exception\ApiConnectionException;
use GiftFlow\Vendor\Stripe\Exception\AuthenticationException;
use GiftFlow\Vendor\Stripe\Exception\PermissionException;
use GiftFlow\Vendor\Stripe\Exception\RateLimitException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validates key shape, profile consistency and server-side authentication.
 */
final class StripeCredentialsValidator {

	/**
	 * Client factory callback.
	 *
	 * @var callable
	 */
	private $client_factory;

	/**
	 * Constructor.
	 *
	 * @param callable|null $client_factory Callback( environment, secret_key ): StripeClient.
	 */
	public function __construct( ?callable $client_factory = null ) {
		$this->client_factory = $client_factory ?? array( StripeClientFactory::class, 'create' );
	}

	/**
	 * Validate a credential set for one environment and authenticate it.
	 *
	 * @param string                $environment      Environment id.
	 * @param array<string, string> $credentials      Keys: publishable_key, secret_key.
	 * @param bool                  $require_complete Whether the publishable key is mandatory.
	 * @return array{valid:bool,errors:array<int,array{code:string,message:string}>,account_id:string,api_version:string}
	 */
	public function validate( string $environment, array $credentials, bool $require_complete = true ): array {
		$environment = StripeEnvironment::assert_valid( $environment );
		$prefix      = $this->validate_prefixes( $environment, $credentials, $require_complete );

		if ( ! $prefix['valid'] ) {
			return $prefix;
		}

		$auth = $this->authenticate( $environment, (string) $credentials['secret_key'] );

		if ( ! $auth['valid'] ) {
			return array(
				'valid'       => false,
				'errors'      => array(
					array(
						'code'    => $auth['code'],
						'message' => StripeErrorCodes::message( $auth['code'] ),
					),
				),
				'account_id'  => '',
				'api_version' => '',
			);
		}

		return array(
			'valid'       => true,
			'errors'      => array(),
			'account_id'  => $auth['account_id'],
			'api_version' => $auth['api_version'],
		);
	}

	/**
	 * Validate key shape and profile consistency without contacting Stripe.
	 *
	 * @param string                $environment      Environment id.
	 * @param array<string, string> $credentials      Keys: publishable_key, secret_key.
	 * @param bool                  $require_complete Whether the publishable key is mandatory.
	 * @return array{valid:bool,errors:array<int,array{code:string,message:string}>,account_id:string,api_version:string}
	 */
	public function validate_prefixes( string $environment, array $credentials, bool $require_complete = true ): array {
		$environment = StripeEnvironment::assert_valid( $environment );
		$publishable = isset( $credentials['publishable_key'] ) && is_string( $credentials['publishable_key'] )
			? trim( $credentials['publishable_key'] )
			: '';
		$secret      = isset( $credentials['secret_key'] ) && is_string( $credentials['secret_key'] )
			? trim( $credentials['secret_key'] )
			: '';
		$errors      = array();
		$provided    = ( '' !== $publishable ) || ( '' !== $secret );
		$incomplete  = ! $provided || ( $require_complete && ( '' === $publishable || '' === $secret ) );

		if ( $incomplete ) {
			// A blank field is missing, not malformed: report one actionable error.
			$errors[] = self::error( StripeErrorCodes::MISSING_CREDENTIALS );
		} else {
			if ( '' !== $publishable && ! StripeEnvironment::publishable_key_matches( $environment, $publishable ) ) {
				$errors[] = self::error( StripeErrorCodes::INVALID_PUBLISHABLE_KEY );
			}

			if ( '' !== $secret && ! StripeEnvironment::secret_key_matches( $environment, $secret ) ) {
				$errors[] = self::error( StripeErrorCodes::INVALID_SECRET_KEY );
			}
		}

		return array(
			'valid'       => empty( $errors ),
			'errors'      => $errors,
			'account_id'  => '',
			'api_version' => '',
		);
	}

	/**
	 * Whether a secret key is a restricted key.
	 *
	 * @param string $secret_key Candidate secret key.
	 * @return bool
	 */
	public static function is_restricted_key( string $secret_key ): bool {
		return 0 === strpos( trim( $secret_key ), 'rk_' );
	}

	/**
	 * Authenticate a secret key against Stripe and resolve the account context.
	 *
	 * @param string $environment Environment id.
	 * @param string $secret_key  Secret key value.
	 * @return array{valid:bool,code:string,account_id:string,api_version:string}
	 */
	public function authenticate( string $environment, string $secret_key ): array {
		$environment = StripeEnvironment::assert_valid( $environment );
		$secret_key  = trim( $secret_key );

		if ( '' === $secret_key || ! StripeEnvironment::secret_key_matches( $environment, $secret_key ) ) {
			return array(
				'valid'       => false,
				'code'        => StripeErrorCodes::INVALID_SECRET_KEY,
				'account_id'  => '',
				'api_version' => '',
			);
		}

		try {
			$client = $this->create_client( $environment, $secret_key );
		} catch ( StripeClientConfigurationException $e ) {
			return array(
				'valid'       => false,
				'code'        => StripeErrorCodes::INVALID_SECRET_KEY,
				'account_id'  => '',
				'api_version' => '',
			);
		}

		try {
			$account = $client->accounts->retrieve();
		} catch ( ApiErrorException $e ) {
			return array(
				'valid'       => false,
				'code'        => self::classify_api_error( $e ),
				'account_id'  => '',
				'api_version' => '',
			);
		} catch ( \Exception $e ) {
			return array(
				'valid'       => false,
				'code'        => StripeErrorCodes::TEMPORARY_STRIPE_ERROR,
				'account_id'  => '',
				'api_version' => '',
			);
		}

		$account_id = isset( $account->id ) && is_string( $account->id ) ? trim( $account->id ) : '';

		if ( '' === $account_id ) {
			return array(
				'valid'       => false,
				'code'        => StripeEnvironment::is_live( $environment )
					? StripeErrorCodes::AUTHENTICATION_FAILED
					: StripeErrorCodes::SANDBOX_IDENTITY_UNCONFIRMED,
				'account_id'  => '',
				'api_version' => '',
			);
		}

		return array(
			'valid'       => true,
			'code'        => '',
			'account_id'  => $account_id,
			'api_version' => StripeClientFactory::sdk_version(),
		);
	}

	/**
	 * Map a Stripe API exception to a stable error code.
	 *
	 * @param ApiErrorException $exception Stripe exception.
	 * @return string
	 */
	public static function classify_api_error( ApiErrorException $exception ): string {
		$stripe_code = $exception->getStripeCode();
		$stripe_code = is_string( $stripe_code ) ? $stripe_code : '';
		$status      = (int) $exception->getHttpStatus();

		if ( in_array( $stripe_code, array( 'api_key_invalid', 'api_key_expired', 'api_key_revoked' ), true ) ) {
			return StripeErrorCodes::AUTHENTICATION_FAILED;
		}

		if ( $exception instanceof AuthenticationException ) {
			return StripeErrorCodes::AUTHENTICATION_FAILED;
		}

		if ( $exception instanceof PermissionException ) {
			return StripeErrorCodes::INSUFFICIENT_PERMISSIONS;
		}

		if ( $exception instanceof RateLimitException || $exception instanceof ApiConnectionException ) {
			return StripeErrorCodes::TEMPORARY_STRIPE_ERROR;
		}

		if ( 401 === $status ) {
			return StripeErrorCodes::AUTHENTICATION_FAILED;
		}

		if ( 403 === $status ) {
			return StripeErrorCodes::INSUFFICIENT_PERMISSIONS;
		}

		return StripeErrorCodes::TEMPORARY_STRIPE_ERROR;
	}

	/**
	 * Build an error entry.
	 *
	 * @param string $code Registered error code.
	 * @return array{code:string,message:string}
	 */
	public static function error( string $code ): array {
		return array(
			'code'    => $code,
			'message' => StripeErrorCodes::message( $code ),
		);
	}

	/**
	 * Create a client through the injected factory.
	 *
	 * @param string $environment Environment id.
	 * @param string $secret_key  Secret key.
	 * @return StripeClient
	 */
	private function create_client( string $environment, string $secret_key ): StripeClient {
		$factory = $this->client_factory;

		return $factory( $environment, $secret_key );
	}
}
