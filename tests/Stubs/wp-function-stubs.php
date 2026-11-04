<?php
/**
 * Minimal WordPress function stubs for unit tests.
 *
 * The unit suite runs without WordPress. Only the functions the tested code
 * actually calls are stubbed, and the option store emulates core behaviour:
 * `add_option()` is atomic (it fails when the option already exists) and
 * `update_option()` reports success or failure.
 *
 * @package GiftFlow
 */

// phpcs:disable Universal.Files.SeparateFunctionsFromOO.Mixed -- Test stubs intentionally combine minimal WordPress functions and value objects.
// phpcs:disable Generic.Files.OneObjectStructurePerFile.MultipleFound -- Test stubs intentionally keep their related structures together.
// phpcs:disable Generic.Commenting.DocComment.MissingShort -- Compact stub property type documentation is intentional.
// phpcs:disable Universal.NamingConventions.NoReservedKeywordParameterNames.defaultFound -- Stub signature mirrors WordPress core.
// phpcs:disable WordPress.WP.AlternativeFunctions.parse_url_parse_url,WordPress.WP.AlternativeFunctions.json_encode_json_encode -- These stubs implement the WordPress alternatives themselves.
// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Compatibility stub signatures intentionally retain unused WordPress arguments.

/**
 * In-memory WordPress option store used by the unit suite.
 */
final class GiftFlow_Test_Option_Store {

	/**
	 * Stored option values.
	 *
	 * @var array<string, mixed>
	 */
	public static $options = array();

	/**
	 * Autoload flags per option.
	 *
	 * @var array<string, bool>
	 */
	public static $autoload = array();

	/**
	 * When true every write fails, simulating an unwritable database.
	 *
	 * @var bool
	 */
	public static $fail_writes = false;

	/**
	 * Write counters per option name.
	 *
	 * @var array<string, int>
	 */
	public static $writes = array();

	/**
	 * Stored transients as name => array( value, expires_at ).
	 *
	 * @var array<string, array{value: mixed, expires_at: int}>
	 */
	public static $transients = array();

	/** @var callable|null HTTP transport callback. */
	public static $http_callback = null;

	/**
	 * Reset the store between tests.
	 *
	 * @return void
	 */
	public static function reset(): void {
		self::$options     = array();
		self::$autoload    = array();
		self::$fail_writes = false;
		self::$writes      = array();
		self::$transients  = array();
		self::$http_callback = null;
	}

	/**
	 * Count a write attempt.
	 *
	 * @param string $name Option name.
	 * @return void
	 */
	public static function count_write( string $name ): void {
		self::$writes[ $name ] = ( self::$writes[ $name ] ?? 0 ) + 1;
	}
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- WordPress core function stubs.

/**
 * Get an option value.
 *
 * @param string $name    Option name.
 * @param mixed  $default Default value.
 * @return mixed
 */
function get_option( $name, $default = false ) {
	return array_key_exists( $name, GiftFlow_Test_Option_Store::$options )
		? GiftFlow_Test_Option_Store::$options[ $name ]
		: $default;
}

/**
 * Update an option value.
 *
 * @param string $name     Option name.
 * @param mixed  $value    New value.
 * @param mixed  $autoload Autoload flag.
 * @return bool
 */
function update_option( $name, $value, $autoload = null ) {
	GiftFlow_Test_Option_Store::count_write( $name );

	if ( GiftFlow_Test_Option_Store::$fail_writes ) {
		return false;
	}

	GiftFlow_Test_Option_Store::$options[ $name ]  = $value;
	GiftFlow_Test_Option_Store::$autoload[ $name ] = null === $autoload ? true : (bool) $autoload;

	// Core returns false only when the stored value is unchanged; tests always
	// need to know whether the write was accepted.
	return true;
}

/**
 * Add an option atomically.
 *
 * @param string $name     Option name.
 * @param mixed  $value    Option value.
 * @param mixed  $deprecated Deprecated argument.
 * @param mixed  $autoload Autoload flag.
 * @return bool
 */
function add_option( $name, $value = '', $deprecated = '', $autoload = 'yes' ) {
	GiftFlow_Test_Option_Store::count_write( $name );

	if ( GiftFlow_Test_Option_Store::$fail_writes ) {
		return false;
	}

	if ( '' === $value || null === $value ) {
		return delete_option( $name );
	}

	if ( array_key_exists( $name, GiftFlow_Test_Option_Store::$options ) ) {
		return false;
	}

	GiftFlow_Test_Option_Store::$options[ $name ]  = $value;
	GiftFlow_Test_Option_Store::$autoload[ $name ] = 'no' === $autoload ? false : (bool) $autoload;

	return true;
}

/**
 * Delete an option.
 *
 * @param string $name Option name.
 * @return bool
 */
function delete_option( $name ) {
	$existed = array_key_exists( $name, GiftFlow_Test_Option_Store::$options );

	unset( GiftFlow_Test_Option_Store::$options[ $name ], GiftFlow_Test_Option_Store::$autoload[ $name ] );

	return $existed;
}

/**
 * Current blog id.
 *
 * @return int
 */
function get_current_blog_id() {
	return 1;
}

/**
 * Read a transient, honouring its expiry.
 *
 * @param string $name Transient name.
 * @return mixed Stored value, or false when missing or expired.
 */
function get_transient( $name ) {
	if ( ! array_key_exists( $name, GiftFlow_Test_Option_Store::$transients ) ) {
		return false;
	}

	$entry = GiftFlow_Test_Option_Store::$transients[ $name ];

	if ( $entry['expires_at'] > 0 && $entry['expires_at'] <= time() ) {
		unset( GiftFlow_Test_Option_Store::$transients[ $name ] );

		return false;
	}

	return $entry['value'];
}

/**
 * Store a transient.
 *
 * @param string $name       Transient name.
 * @param mixed  $value      Value.
 * @param int    $expiration Lifetime in seconds; 0 means no expiry.
 * @return bool
 */
function set_transient( $name, $value, $expiration = 0 ) {
	$expiration = (int) $expiration;

	GiftFlow_Test_Option_Store::$transients[ $name ] = array(
		'value'      => $value,
		'expires_at' => $expiration > 0 ? time() + $expiration : 0,
	);

	return true;
}

/**
 * Delete a transient.
 *
 * @param string $name Transient name.
 * @return bool
 */
function delete_transient( $name ) {
	$existed = array_key_exists( $name, GiftFlow_Test_Option_Store::$transients );

	unset( GiftFlow_Test_Option_Store::$transients[ $name ] );

	return $existed;
}

/**
 * Site home URL, overridable through the option store for URL tests.
 *
 * @param string $path Optional path.
 * @return string
 */
function home_url( $path = '' ) {
	$base = isset( GiftFlow_Test_Option_Store::$options['home_url'] )
		? (string) GiftFlow_Test_Option_Store::$options['home_url']
		: 'https://example.test';

	if ( '' === $path ) {
		return $base;
	}

	return rtrim( $base, '/' ) . '/' . ltrim( (string) $path, '/' );
}

/**
 * Admin URL, overridable through the option store for URL tests.
 *
 * @param string $path Optional path.
 * @return string
 */
function admin_url( $path = '' ) {
	$base = isset( GiftFlow_Test_Option_Store::$options['admin_url'] )
		? (string) GiftFlow_Test_Option_Store::$options['admin_url']
		: 'https://example.test/wp-admin/';

	if ( '' === $path ) {
		return $base;
	}

	return rtrim( $base, '/' ) . '/' . ltrim( (string) $path, '/' );
}

/**
 * Whether the current request is served over HTTPS.
 *
 * @return bool
 */
function is_ssl() {
	return 0 === strpos( (string) home_url(), 'https://' );
}

/**
 * Swap the scheme of a URL.
 *
 * @param string      $url    URL.
 * @param string|null $scheme Target scheme.
 * @return string
 */
function set_url_scheme( $url, $scheme = null ) {
	$url = (string) $url;

	if ( null === $scheme || '' === $scheme ) {
		return $url;
	}

	return (string) preg_replace( '#^https?://#i', strtolower( (string) $scheme ) . '://', $url );
}

/**
 * Parse a URL with the given component.
 *
 * @param string $url   URL.
 * @param int    $component Component constant.
 * @return mixed
 */
function wp_parse_url( $url, $component = -1 ) {
	return parse_url( $url, $component );
}

/**
 * Encode a value as JSON.
 *
 * @param mixed $value Value.
 * @param int   $flags Encode flags.
 * @param int   $depth Depth.
 * @return string|false
 */
function wp_json_encode( $value, $flags = 0, $depth = 512 ) {
	return json_encode( $value, $flags, $depth );
}

/**
 * Generate a UUID v4.
 *
 * @return string
 */
function wp_generate_uuid4() {
	return sprintf(
		'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		wp_rand( 0, 0xffff ),
		wp_rand( 0, 0xffff ),
		wp_rand( 0, 0xffff ),
		wp_rand( 0, 0x0fff ) | 0x4000,
		wp_rand( 0, 0x3fff ) | 0x8000,
		wp_rand( 0, 0xffff ),
		wp_rand( 0, 0xffff ),
		wp_rand( 0, 0xffff )
	);
}

/**
 * Deterministic pseudo random integer.
 *
 * @param int $min Minimum.
 * @param int $max Maximum.
 * @return int
 */
function wp_rand( $min = 0, $max = 0 ) {
	static $seed = 424242;

	$seed = ( $seed * 1103515245 + 12345 ) % 2147483647;

	return 0 === $max ? $seed : $min + ( $seed % max( 1, $max - $min + 1 ) );
}

/**
 * Apply a filter, returning the first value unless a callback is attached.
 *
 * @param string $hook      Filter name.
 * @param mixed  $value     Value.
 * @param mixed  ...$args   Extra arguments.
 * @return mixed
 */
function apply_filters( $hook, $value, ...$args ) {
	return $value;
}

/**
 * Register an action (no-op in unit tests).
 *
 * @param string $hook     Hook name.
 * @param mixed  $callback Callback.
 * @param int    $priority Priority.
 * @param int    $accepted Accepted args.
 * @return bool
 */
function add_action( $hook, $callback, $priority = 10, $accepted = 1 ) {
	return true;
}

/**
 * Translate a string.
 *
 * @param string $text   Text.
 * @param string $domain Text domain.
 * @return string
 */
function __( $text, $domain = 'default' ) {
	return $text;
}

/**
 * Translate and escape a string.
 *
 * @param string $text   Text.
 * @param string $domain Text domain.
 * @return string
 */
function esc_html__( $text, $domain = 'default' ) {
	return $text;
}

/**
 * Escape HTML.
 *
 * @param string $text Text.
 * @return string
 */
function esc_html( $text ) {
	return $text;
}

/**
 * Escape an attribute.
 *
 * @param string $text Text.
 * @return string
 */
function esc_attr( $text ) {
	return $text;
}

/**
 * Format a local date, using UTC in the stub environment.
 *
 * @param string $format    Date format.
 * @param int    $timestamp Timestamp.
 * @return string
 */
function date_i18n( $format, $timestamp = false ) {
	$timestamp = false === $timestamp ? time() : (int) $timestamp;

	return gmdate( $format, $timestamp );
}

/**
 * Sanitize a text field value.
 *
 * @param string $value Value.
 * @return string
 */
function sanitize_text_field( $value ) {
	return is_string( $value ) ? trim( preg_replace( '/[\r\n\t]+/', ' ', $value ) ) : '';
}

/**
 * Sanitize a key.
 *
 * @param string $value Value.
 * @return string
 */
function sanitize_key( $value ) {
	return strtolower( preg_replace( '/[^a-z0-9_\-]/i', '', (string) $value ) );
}

/**
 * Dispatch an HTTP request through the configured unit-test transport.
 *
 * @param string $url  URL.
 * @param array  $args Request arguments.
 * @return array|WP_Error
 */
function wp_remote_request( $url, $args = array() ) {
	$callback = GiftFlow_Test_Option_Store::$http_callback;
	if ( is_callable( $callback ) ) {
		return $callback( (string) ( $args['method'] ?? 'GET' ), (string) $url, (array) $args );
	}
	return new WP_Error( 'http_not_configured', 'HTTP transport is not configured.' );
}

/**
 * Retrieve an HTTP response status code.
 *
 * @param array $response Response array.
 * @return int
 */
function wp_remote_retrieve_response_code( $response ) {
	return isset( $response['response']['code'] ) ? (int) $response['response']['code'] : 0;
}

/**
 * Retrieve an HTTP response body.
 *
 * @param array $response Response array.
 * @return string
 */
function wp_remote_retrieve_body( $response ) {
	return isset( $response['body'] ) ? (string) $response['body'] : '';
}

/**
 * Whether a value is a WP_Error.
 *
 * @param mixed $thing Value.
 * @return bool
 */
function is_wp_error( $thing ) {
	return $thing instanceof WP_Error;
}

/**
 * Minimal WP_Error stand-in.
 */
class WP_Error {

	/**
	 * Error code.
	 *
	 * @var string
	 */
	public $code;

	/**
	 * Error message.
	 *
	 * @var string
	 */
	public $message;

	/**
	 * Constructor.
	 *
	 * @param string $code    Code.
	 * @param string $message Message.
	 */
	public function __construct( $code = '', $message = '' ) {
		$this->code    = $code;
		$this->message = $message;
	}
}

// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound.
