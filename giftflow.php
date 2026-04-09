<?php
/**
 * Plugin Name: GiftFlow – Donation & Fundraising
 * Plugin URI: https://giftflow.beplus-agency.cloud/
 * Description: The all-in-one fundraising and donation management solution for WordPress. GiftFlow empowers nonprofits and organizations to easily accept donations, run unlimited campaigns, and manage donor relationships—all with a modern interface, robust analytics, and seamless payment integrations (Stripe, PayPal, bank transfer). Designed for growth, security, and extensibility, GiftFlow offers powerful tools—even no coding required—to launch, track, and optimize your giving programs.
 * Version: 1.0.12
 * Author: Beplus
 * Author URI: https://beplusthemes.com/
 * Text Domain: giftflow
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
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
define( 'GIFTFLOW_VERSION', '1.0.12' );
define( 'GIFTFLOW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GIFTFLOW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'GIFTFLOW_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Include Composer autoloader.
if ( file_exists( GIFTFLOW_PLUGIN_DIR . 'vendor-prefixed/autoload.php' ) ) {
	require_once GIFTFLOW_PLUGIN_DIR . 'vendor-prefixed/autoload.php';
} else {
	add_action(
		'admin_notices',
		function () {
			?>
		<div class="notice notice-error">
			<p><?php esc_html_e( 'GiftFlow requires Composer dependencies to be installed. Please run "composer run build" in the plugin directory.', 'giftflow' ); ?></p>
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
	require_once GIFTFLOW_PLUGIN_DIR . 'includes/core/class-donations.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'includes/core/class-campaigns.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'includes/core/class-block-template.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'includes/core/class-logger.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'includes/core/class-donation-event-history.php';
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
	require_once GIFTFLOW_PLUGIN_DIR . 'includes/frontend/campaign-single-template-hooks.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'includes/frontend/campaign-taxonomy-archive-template-hooks.php';
	require_once GIFTFLOW_PLUGIN_DIR . 'includes/frontend/campaigns-page-template-hooks.php';

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
	if ( ! file_exists( GIFTFLOW_PLUGIN_DIR . 'vendor-prefixed/autoload.php' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die(
			esc_html__( 'GiftFlow requires Composer dependencies to be installed. Please run "composer run build" in the plugin directory.', 'giftflow' ),
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
	// giftflow_first_activation_notice_dismissed.
	delete_option( 'giftflow_first_activation_notice_dismissed' );

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

	if ( ! current_user_can( 'manage_options' ) ) {
		// Do not add admin bar items if user is not allowed.
		return;
	}

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

/**
 * Add admin notice on first activation to suggest viewing documentation.
 */
add_action( 'admin_init', 'giftflow_add_first_activation_notice' );

/**
 * Add admin notice on first activation to suggest viewing documentation.
 */
function giftflow_add_first_activation_notice() {
	$is_giftflow_help_page = false;
	if (
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		isset( $_GET['page'], $_GET['tab'] )
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.PHP.YodaConditions.NotYoda
		&& sanitize_text_field( wp_unslash( $_GET['page'] ) ) === 'giftflow-dashboard'
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.PHP.YodaConditions.NotYoda
		&& sanitize_text_field( wp_unslash( $_GET['tab'] ) ) === 'help'
	) {
		$is_giftflow_help_page = true;
	}

	// Show admin notice on first activation to suggest viewing documentation.
	if ( is_admin()
		&& ! get_option( 'giftflow_first_activation_notice_dismissed', false )
		&& ! $is_giftflow_help_page
	) {
		add_action(
			'admin_notices',
			function () {
				// Only show to users who can manage options.
				if ( ! current_user_can( 'manage_options' ) ) {
					return;
				}

				// Prepare URL to documentation. You may want to change this as needed.
				$docs_url = esc_url( admin_url( 'admin.php?page=giftflow-dashboard&tab=help' ) );
				?>
			<div
				class="notice notice-info is-dismissible giftflow-first-activation-notice"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'giftflow_dismiss_notice' ) ); ?>">
				<p>
					<?php esc_html_e( 'It looks like you\'re using GiftFlow for the first time. We highly recommend visiting the documentation page to quickly get started and make the most out of your donation campaigns.', 'giftflow' ); ?>
					<a 
						href="<?php echo esc_url( $docs_url ); ?>" 
						target="_blank" >
						<?php esc_html_e( 'View documentation here.', 'giftflow' ); ?>
					</a>
				</p>
			</div>
				<?php
			}
		);

		add_action(
			'wp_ajax_giftflow_dismiss_first_activation_notice',
			function () {
				check_ajax_referer( 'giftflow_dismiss_notice', '_giftflow_nonce' );
				update_option( 'giftflow_first_activation_notice_dismissed', true );
				wp_send_json_success();
			}
		);
	}
}

// add_filter('get_block_templates', function($query_result, $query, $template_type) {
// 	if ($template_type !== 'wp_template') return $query_result;

// 	// Chỉ inject khi đang query slug cụ thể
// 	if (empty($query['slug__in'])) return $query_result;

// 	$slugs = $query['slug__in'];

// 	$plugin_templates = [
// 			'page-campaigns' => plugin_dir_path(__FILE__) . 'templates/page-campaigns.html',
// 			'single-campaign' => plugin_dir_path(__FILE__) . 'templates/single-campaign.html',
// 	];

// 	foreach ($plugin_templates as $slug => $file) {
// 			// Chỉ inject nếu WP đang hỏi slug này
// 			if (!in_array($slug, $slugs)) continue;
// 			if (!file_exists($file)) continue;

// 			// Skip nếu đã có
// 			foreach ($query_result as $t) {
// 					if ($t->slug === $slug) continue 2;
// 			}

// 			$template                 = new WP_Block_Template();
// 			$template->id             = get_stylesheet() . '//' . $slug;
// 			$template->theme          = get_stylesheet();
// 			$template->slug           = $slug;
// 			$template->source         = 'plugin';
// 			$template->origin         = 'plugin';
// 			$template->type           = 'wp_template';
// 			$template->title          = ucwords(str_replace('-', ' ', $slug));
// 			$template->status         = 'publish';
// 			$template->has_theme_file = false;
// 			$template->is_custom      = false;
// 			$template->content        = file_get_contents($file);

// 			$query_result[] = $template;
// 	}

// 	return $query_result;

// }, 10, 3);

/**
 * Block Theme Template Loader
 * Handles template loading for block themes from plugin
 */
class GiftFlow_Block_Templates {

    /**
     * Map slug => điều kiện context
     * Thêm template mới vào đây
     */
    private static function get_template_map(): array {
        return [
            'page-campaigns'  => plugin_dir_path( __FILE__ ) . 'templates/page-campaigns.html',
            'single-campaign' => plugin_dir_path( __FILE__ ) . 'templates/single-campaign.html',
        ];
    }

    /**
     * Khởi tạo hooks
     */
    public static function init(): void {
        if ( ! wp_is_block_theme() ) return;

        // 1. Cung cấp template cho renderer (slug__in query)
        add_filter( 'get_block_templates', [ self::class, 'provide_templates' ], 10, 3 );

        // 2. Cung cấp template cho FSE editor (lookup theo ID)
        add_filter( 'get_block_template', [ self::class, 'provide_template_by_id' ], 10, 3 );
    }

    /**
     * Hook 1: Inject template khi WordPress query theo slug__in
     * Chạy khi: render trang + FSE editor list templates
     */
    public static function provide_templates( array $query_result, array $query, string $template_type ): array {
        if ( $template_type !== 'wp_template' ) return $query_result;
        if ( empty( $query['slug__in'] ) ) return $query_result;

        $requested_slugs = $query['slug__in'];
        $template_map    = self::get_template_map();

        foreach ( $template_map as $slug => $file ) {
            // Chỉ inject nếu WP đang hỏi slug này
            if ( ! in_array( $slug, $requested_slugs, true ) ) continue;
            if ( ! file_exists( $file ) ) continue;

            // Tránh duplicate
            foreach ( $query_result as $existing ) {
                if ( $existing->slug === $slug ) continue 2;
            }

            $query_result[] = self::build_template(
                get_stylesheet() . '//' . $slug,
                $slug,
                $file
            );
        }

        return $query_result;
    }

    /**
     * Hook 2: Inject template khi FSE editor lookup theo ID
     * Chạy khi: save template trong FSE editor
     */
    public static function provide_template_by_id( $block_template, string $id, string $template_type ) {
        if ( $template_type !== 'wp_template' ) return $block_template;

        // Đã tìm thấy rồi → bỏ qua
        if ( $block_template instanceof WP_Block_Template ) return $block_template;

        $parts = explode( '//', $id );
        if ( count( $parts ) !== 2 ) return $block_template;

        $slug         = $parts[1];
        $template_map = self::get_template_map();

        if ( ! isset( $template_map[ $slug ] ) ) return $block_template;
        if ( ! file_exists( $template_map[ $slug ] ) ) return $block_template;

        return self::build_template( $id, $slug, $template_map[ $slug ] );
    }

    /**
     * Build WP_Block_Template object
     */
    private static function build_template( string $id, string $slug, string $file ): WP_Block_Template {
        $template                 = new WP_Block_Template();
        $template->id             = $id;
        $template->theme          = get_stylesheet();
        $template->slug           = $slug;
        $template->source         = 'plugin';
        $template->origin         = 'plugin';
        $template->type           = 'wp_template';
        $template->title          = ucwords( str_replace( '-', ' ', $slug ) );
        $template->status         = 'publish';
        $template->has_theme_file = false;
        $template->is_custom      = false;
        $template->content        = file_get_contents( $file );
        return $template;
    }
}

GiftFlow_Block_Templates::init();