<?php
/**
 * Plugin Name: GiftFlow
 * Plugin URI: https://giftflow.com
 * Description: A comprehensive WordPress plugin for managing donations, donors, and campaigns with modern features and extensible architecture.
 * Version: 1.0.1
 * Author: GiftFlow Team
 * Author URI: https://giftflow.com
 * Text Domain: giftflow
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.2
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package GiftFlow
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define plugin constants.
define( 'GIFTFLOW_VERSION', '1.0.1' );
define( 'GIFTFLOW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GIFTFLOW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'GIFTFLOW_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Include Composer autoloader.
if ( file_exists( GIFTFLOW_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
	require_once GIFTFLOW_PLUGIN_DIR . 'vendor/autoload.php';
} else {
	add_action(
		'admin_notices',
		function () {
			?>
		<div class="notice notice-error">
			<p><?php esc_html_e( 'GiftFlow requires Composer dependencies to be installed. Please run "composer install" in the plugin directory.', 'giftflow' ); ?></p>
		</div>
			<?php
		}
	);
	return;
}

/**
 * Load plugin files
 *
 * A safer approach to loading plugin files using direct includes
 * rather than relying on autoloading which can be error-prone
 */
function giftflow_load_files() {
	// Core files.
	require_once GIFTFLOW_PLUGIN_DIR . 'includes/core/class-base.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'includes/core/class-loader.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'includes/core/class-field.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'includes/core/class-role.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'includes/core/class-ajax.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'includes/core/class-block-template.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'includes/core/class-wp-block-custom-hooks.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'blocks/index.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'includes/common.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'includes/hooks.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'includes/mail.php';

	// Payment gateways.
	require_once GIFTFLOW_PLUGIN_DIR . 'includes/gateways/class-gateway-base.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'includes/gateways/class-stripe.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'includes/gateways/class-paypal.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'includes/gateways/class-direct-bank-transfer.php';

	// Admin files.
	require_once GIFTFLOW_PLUGIN_DIR . 'admin/includes/dashboard.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'admin/includes/class-export.php';

	require_once GIFTFLOW_PLUGIN_DIR . 'admin/includes/post-types/class-base-post-type.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'admin/includes/post-types/class-donation.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'admin/includes/post-types/class-donor.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'admin/includes/post-types/class-campaign.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'admin/includes/settings.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'admin/includes/api.php';

	// Meta boxes.
	require_once GIFTFLOW_PLUGIN_DIR . 'admin/includes/meta-boxes/class-base-meta-box.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'admin/includes/meta-boxes/class-donation-transaction-meta.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'admin/includes/meta-boxes/class-donor-contact-meta.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'admin/includes/meta-boxes/class-campaign-details-meta.php';

	// Frontend files.
	require_once GIFTFLOW_PLUGIN_DIR . 'includes/frontend/class-shortcodes.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'includes/frontend/class-forms.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'includes/frontend/class-template.php';

	// Blocks.

	// Apply filters to allow extensions to load additional files.
	$additional_files = apply_filters( 'giftflow_load_files', array() );

	if ( ! empty( $additional_files ) && is_array( $additional_files ) ) {
		foreach ( $additional_files as $file ) {
			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}
	}
}

// Load all required files.
giftflow_load_files();

// Initialize plugin.
add_action( 'plugins_loaded', 'giftflow_init' );

/**
 * Initialize the plugin.
 */
function giftflow_init() {
	// Initialize plugin.
	$plugin = new \GiftFlow\Core\Loader();
}

// Activation hook.
register_activation_hook( __FILE__, 'giftflow_activate' );

/**
 * Plugin activation
 */
function giftflow_activate() {
	// Check PHP version.
	if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die(
			esc_html__( 'GiftFlow requires PHP 7.4 or higher.', 'giftflow' ),
			'Plugin Activation Error',
			array( 'back_link' => true )
		);
	}

	// Check if Composer dependencies are installed.
	if ( ! file_exists( GIFTFLOW_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die(
			esc_html__( 'GiftFlow requires Composer dependencies to be installed. Please run "composer install" in the plugin directory.', 'giftflow' ),
			'Plugin Activation Error',
			array( 'back_link' => true )
		);
	}

	// Initialize plugin.
	$plugin = new \GiftFlow\Core\Loader();
	$plugin->activate();
}

// Deactivation hook.
register_deactivation_hook( __FILE__, 'giftflow_deactivate' );

/**
 * Plugin deactivation
 */
function giftflow_deactivate() {
	// Flush rewrite rules.
	flush_rewrite_rules();

	// Deactivate plugin.
	$plugin = new \GiftFlow\Core\Loader();
	$plugin->deactivate();
}

/**
 * Add admin bar item.
 */
add_action( 'admin_bar_menu', 'giftflow_admin_bar_item', 100 );

/**
 * Add admin bar items.
 *
 * @param \WP_Admin_Bar $wp_admin_bar The admin bar object.
 * @return void
 */
function giftflow_admin_bar_item( $wp_admin_bar ) {
	// Add parent item.
	$args = array(
		'id'    => 'giftflow_admin_bar_item',
		'title' => esc_html__( 'Gift Flow Dashboard', 'giftflow' ),
		'href'  => admin_url( 'admin.php?page=giftflow-dashboard' ), // or any URL.
		'meta'  => array(
			'class' => 'giftflow_admin_bar_item',
			'title' => esc_html__( 'Go to Gift Flow Dashboard', 'giftflow' ), // Tooltip.
		),
	);
	$wp_admin_bar->add_node( $args );

	$wp_admin_bar->add_node(
		array(
			'id'     => 'giftflow_admin_bar_item_donations',
			'title'  => esc_html__( 'Donations', 'giftflow' ),
			'href'   => admin_url( 'edit.php?post_type=donation' ),
			'parent' => 'giftflow_admin_bar_item',
		)
	);

	$wp_admin_bar->add_node(
		array(
			'id'     => 'giftflow_admin_bar_item_donors',
			'title'  => esc_html__( 'Donors', 'giftflow' ),
			'href'   => admin_url( 'edit.php?post_type=donor' ),
			'parent' => 'giftflow_admin_bar_item',
		)
	);

	$wp_admin_bar->add_node(
		array(
			'id'     => 'giftflow_admin_bar_item_campaigns',
			'title'  => esc_html__( 'Campaigns', 'giftflow' ),
			'href'   => admin_url( 'edit.php?post_type=campaign' ),
			'parent' => 'giftflow_admin_bar_item',
		)
	);

	// Add child item.
	$wp_admin_bar->add_node(
		array(
			'id'     => 'giftflow_admin_bar_item_settings',
			'title'  => esc_html__( 'Settings', 'giftflow' ),
			'href'   => admin_url( 'admin.php?page=giftflow-settings' ),
			'parent' => 'giftflow_admin_bar_item',
		)
	);
}
