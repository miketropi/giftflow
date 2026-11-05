<?php
/**
 * PayPal credential validation and permission diagnostics.
 *
 * @package GiftFlow
 * @subpackage PayPal
 */

namespace GiftFlow\PayPal;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validates credential shape and server-side authentication.
 */
final class PayPalCredentialsValidator {

	/**
	 * HTTP transport.
	 *
	 * @var callable
	 */
	private $transport;

	/**
	 * Constructor.
	 *
	 * @param callable|null $transport HTTP transport callback.
	 */
	public function __construct( ?callable $transport = null ) {
		$this->transport = $transport ?? static function ( string $method, string $url, array $args ) {
			$args['method'] = $method;
			return wp_remote_request( $url, $args );
		};
	}

	/**
	 * Validate credentials and authenticate them.
	 *
	 * @param string                $environment      Environment id.
	 * @param array<string, string> $credentials      Client credentials.
	 * @param bool                  $require_complete Whether both values are required.
	 * @return array{valid:bool,errors:array,app_id:string,http_status:int}
	 */
	public function validate( string $environment, array $credentials, bool $require_complete = true ): array {
		$shape = $this->validate_shape( $environment, $credentials, $require_complete );
		if ( ! $shape['valid'] ) {
			return $shape;
		}

		$auth = $this->authenticate( $environment, trim( (string) $credentials['client_id'] ), trim( (string) $credentials['client_secret'] ) );
		if ( ! $auth['valid'] ) {
			return array(
				'valid'       => false,
				'errors'      => array( self::error( $auth['code'] ) ),
				'app_id'      => '',
				'http_status' => $auth['http_status'],
			);
		}

		return array(
			'valid'       => true,
			'errors'      => array(),
			'app_id'      => $auth['app_id'],
			'http_status' => $auth['http_status'],
		);
	}

	/**
	 * Validate credential shape without a network call.
	 *
	 * @param string                $environment      Environment id.
	 * @param array<string, string> $credentials      Client credentials.
	 * @param bool                  $require_complete Whether both values are required.
	 * @return array{valid:bool,errors:array,app_id:string,http_status:int}
	 */
	public function validate_shape( string $environment, array $credentials, bool $require_complete = true ): array {
		PayPalEnvironment::assert_valid( $environment );
		$client_id     = isset( $credentials['client_id'] ) && is_string( $credentials['client_id'] ) ? trim( $credentials['client_id'] ) : '';
		$client_secret = isset( $credentials['client_secret'] ) && is_string( $credentials['client_secret'] ) ? trim( $credentials['client_secret'] ) : '';
		$provided      = '' !== $client_id || '' !== $client_secret;
		$errors        = array();

		if ( ! $provided || ( $require_complete && ( '' === $client_id || '' === $client_secret ) ) ) {
			$errors[] = self::error( PayPalErrorCodes::MISSING_CREDENTIALS );
		} elseif ( PayPalMasking::is_masked( $client_id ) || PayPalMasking::is_masked( $client_secret ) ) {
			$errors[] = self::error( PayPalErrorCodes::INVALID_CREDENTIAL_VALUE );
		}

		return array(
			'valid'       => empty( $errors ),
			'errors'      => $errors,
			'app_id'      => '',
			'http_status' => 0,
		);
	}

	/**
	 * Authenticate credentials and resolve the PayPal app id.
	 *
	 * @param string $environment   Environment id.
	 * @param string $client_id     Client id.
	 * @param string $client_secret Client secret.
	 * @return array{valid:bool,code:string,app_id:string,http_status:int}
	 */
	public function authenticate( string $environment, string $client_id, string $client_secret ): array {
		$environment   = PayPalEnvironment::assert_valid( $environment );
		$client_id     = trim( $client_id );
		$client_secret = trim( $client_secret );
		$transport     = $this->transport;
		$response      = $transport(
			'POST',
			PayPalEnvironment::api_base( $environment ) . '/v1/oauth2/token',
			array(
				'headers' => array(
					'Accept'        => 'application/json',
					'Authorization' => 'Basic ' . base64_encode( $client_id . ':' . $client_secret ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
					'Content-Type'  => 'application/x-www-form-urlencoded',
				),
				'body'    => 'grant_type=client_credentials',
				'timeout' => 20,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $this->auth_result( false, PayPalErrorCodes::TEMPORARY_PAYPAL_ERROR, '', 0 );
		}

		$status = isset( $response['response']['code'] ) ? (int) $response['response']['code'] : 0;
		$body   = json_decode( isset( $response['body'] ) && is_string( $response['body'] ) ? $response['body'] : '', true );
		$body   = is_array( $body ) ? $body : array();
		$token  = isset( $body['access_token'] ) && is_string( $body['access_token'] ) ? trim( $body['access_token'] ) : '';

		if ( 200 === $status && '' !== $token ) {
			$app_id = isset( $body['app_id'] ) && is_string( $body['app_id'] ) ? trim( $body['app_id'] ) : '';

			// Hand the freshly issued token to the shared cache so the API client
			// that runs next reuses it instead of authenticating a second time.
			$expires_in = isset( $body['expires_in'] ) ? max( 60, (int) $body['expires_in'] ) : 32400;
			PayPalTokenCache::put( $environment, $client_id, $token, $expires_in );

			return $this->auth_result( true, '', $app_id, $status );
		}

		$paypal_error = isset( $body['error'] ) && is_string( $body['error'] ) ? strtolower( trim( $body['error'] ) ) : '';
		if ( 400 === $status && 'invalid_client' === $paypal_error ) {
			$code = PayPalErrorCodes::ENVIRONMENT_MISMATCH;
		} elseif ( 401 === $status ) {
			$code = PayPalErrorCodes::AUTHENTICATION_FAILED;
		} elseif ( 403 === $status ) {
			$code = PayPalErrorCodes::INSUFFICIENT_PERMISSIONS;
		} else {
			$code = PayPalErrorCodes::TEMPORARY_PAYPAL_ERROR;
		}

		return $this->auth_result( false, $code, '', $status );
	}

	/**
	 * Build a display-safe validation error entry.
	 *
	 * @param string $code Stable PayPal error code.
	 * @return array{code:string,message:string} Error code and translated message.
	 */
	public static function error( string $code ): array {
		return array(
			'code' => $code,
			'message' => PayPalErrorCodes::message( $code ),
		);
	}

	/**
	 * Build an authentication result envelope.
	 *
	 * @param bool   $valid  Whether authentication succeeded.
	 * @param string $code   Stable error code, or an empty string on success.
	 * @param string $app_id PayPal app ID, when returned.
	 * @param int    $status HTTP response status.
	 * @return array Authentication result.
	 */
	private function auth_result( bool $valid, string $code, string $app_id, int $status ): array {
		return array(
			'valid' => $valid,
			'code' => $code,
			'app_id' => $app_id,
			'http_status' => $status,
		);
	}
}
