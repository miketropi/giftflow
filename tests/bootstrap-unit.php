<?php
/**
 * Unit test bootstrap.
 *
 * Loads the plugin's PSR-4 autoloader, the Strauss-prefixed Stripe SDK and the
 * WordPress function stubs. WordPress is not loaded, so this suite runs on every
 * supported PHP version (7.4 – 8.3).
 *
 * Run with: composer test:unit
 *
 * @package GiftFlow
 */

$giftflow_plugin_dir = dirname( __DIR__ );

// Plugin classes bail out when ABSPATH is undefined; unit tests run without WordPress.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', $giftflow_plugin_dir . '/' );
}

require_once $giftflow_plugin_dir . '/vendor/autoload.php';

$giftflow_prefixed_autoload = $giftflow_plugin_dir . '/vendor-prefixed/autoload.php';

if ( file_exists( $giftflow_prefixed_autoload ) ) {
	require_once $giftflow_prefixed_autoload;
}

require_once __DIR__ . '/Stubs/wp-function-stubs.php';
