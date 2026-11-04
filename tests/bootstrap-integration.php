<?php
/**
 * WordPress integration test bootstrap.
 *
 * Requires the WordPress PHPUnit test library (tests/phpunit of
 * wordpress-develop) plus a wp-tests-config.php:
 *
 *   export WP_TESTS_DIR=/path/to/.giftflow-wp-tests
 *   # WP_TESTS_DIR must contain includes/, data/ and wp-tests-config.php
 *   composer test:integration
 *
 * The database named by wp-tests-config.php is dropped and recreated on every
 * run; never point it at a production or shared database.
 *
 * @package GiftFlow
 */

$giftflow_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $giftflow_tests_dir || ! file_exists( $giftflow_tests_dir . '/includes/functions.php' ) ) {
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- CLI bootstrap message before WordPress loads.
	fwrite(
		STDERR,
		"WordPress test library not found.\n"
		. "Set WP_TESTS_DIR to a directory containing includes/, data/ and wp-tests-config.php.\n"
	);
	exit( 1 );
}

$giftflow_config_path = getenv( 'WP_TESTS_CONFIG_FILE_PATH' );

if ( ! $giftflow_config_path ) {
	$giftflow_config_path = $giftflow_tests_dir . '/wp-tests-config.php';
}

if ( ! is_readable( $giftflow_config_path ) ) {
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- CLI bootstrap message before WordPress loads.
	fwrite( STDERR, "Test configuration not readable: {$giftflow_config_path}\n" );
	exit( 1 );
}

define( 'WP_TESTS_CONFIG_FILE_PATH', $giftflow_config_path );

require_once $giftflow_tests_dir . '/includes/functions.php';

/**
 * Load the plugin under test before WordPress finishes booting.
 *
 * @return void
 */
function giftflow_tests_load_plugin(): void {
	$autoload = dirname( __DIR__ ) . '/vendor/autoload.php';

	if ( file_exists( $autoload ) ) {
		require_once $autoload;
	}

	require_once dirname( __DIR__ ) . '/giftflow.php';
}

tests_add_filter( 'muplugins_loaded', 'giftflow_tests_load_plugin' );

/**
 * Create the plugin's custom tables.
 *
 * Activation normally creates them, but the test suite loads the plugin
 * directly. Without this the donation event history and log writers hit
 * missing tables and mask real failures behind database errors.
 *
 * @return void
 */
function giftflow_tests_create_tables() {
	if ( ! class_exists( '\GiftFlow\Core\Logger' ) || ! class_exists( '\GiftFlow\Core\Donation_Event_History' ) ) {
		return;
	}

	\GiftFlow\Core\Logger::create_table();
	\GiftFlow\Core\Donation_Event_History::create_table();
}

tests_add_filter( 'init', 'giftflow_tests_create_tables', 5 );

require_once $giftflow_tests_dir . '/includes/bootstrap.php';
