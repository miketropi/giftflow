<?php
/**
 * GiftFlow Dashboard
 *
 * @package GiftFlow
 * @subpackage Admin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Include dashboard functions.
require_once GIFTFLOW_PLUGIN_DIR . 'admin/includes/dashboard-functions.php';

/**
 * Register the GiftFlow dashboard page.

 * @return void
 */
function giftflow_register_dashboard_page() {
	add_menu_page(
		__( 'GiftFlow Dashboard', 'giftflow' ),
		__( 'GiftFlow', 'giftflow' ),
		'manage_options',
		'giftflow-dashboard',
		'giftflow_dashboard_page',
		'dashicons-heart',
		30
	);

	add_submenu_page(
		'giftflow-dashboard',
		__( 'Dashboard', 'giftflow' ),
		__( 'Dashboard', 'giftflow' ),
		'manage_options',
		'giftflow-dashboard',
		'giftflow_dashboard_page',
		0
	);
}
add_action( 'admin_menu', 'giftflow_register_dashboard_page' );

/**
 * Display the GiftFlow dashboard page content.

 * @return void
 */
function giftflow_dashboard_page() {
	// Check user capabilities.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Get the current tab.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'overview';

	// array of tabs.
	$tabs = array(
		'overview' => array(
			'label' => __( 'Overview', 'giftflow' ),
			'url' => admin_url( 'admin.php?page=giftflow-dashboard&tab=overview' ),
			'active_class' => 'overview' === $current_tab ? 'nav-tab-active' : '',
			'callback' => 'giftflow_dashboard_overview_tab',
		),
		'help' => array(
			'label' => __( 'Help', 'giftflow' ),
			'url' => admin_url( 'admin.php?page=giftflow-dashboard&tab=help' ),
			'active_class' => 'help' === $current_tab ? 'nav-tab-active' : '',
			'callback' => 'giftflow_dashboard_help_tab',
		),
	);

	// filter the tabs.
	$tabs = apply_filters( 'giftflow_dashboard_tabs', $tabs, $current_tab );

	// Include the header.
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		
		<nav class="nav-tab-wrapper">
			<?php foreach ( $tabs as $tab ) : ?>
				<a href="<?php echo esc_url( $tab['url'] ); ?>" class="nav-tab <?php echo esc_attr( $tab['active_class'] ); ?>">
					<?php echo esc_html( $tab['label'] ); ?>
				</a>
			<?php endforeach; ?>
		</nav>
		
		<div class="tab-content">
			<?php
			// Include the appropriate tab content.
			foreach ( $tabs as $key => $tab ) {
				if ( $key === $current_tab ) {
					call_user_func( $tab['callback'] );
				}
			}
			?>
		</div>
	</div>
	<?php
}

/**
 * Display the overview tab content.

 * @return void
 */
function giftflow_dashboard_overview_tab() {
	giftflow_load_template( 'admin/dashboard-view.php' );
}

/**
 * Display the help tab content.

 * @return void
 */
function giftflow_dashboard_help_tab() {
	giftflow_load_template( 'admin/dashboard-helps.php' );
}


