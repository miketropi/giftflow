<?php
/**
 * Loader class for GiftFlow
 *
 * @package GiftFlow
 * @subpackage Core
 */

namespace GiftFlow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loader class that handles file loading and initialization
 */
class Loader extends Base {
	/**
	 * Initialize the loader
	 */
	public function __construct() {
		parent::__construct();
		$this->init_hooks();
	}

	/**
	 * Enqueue styles
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_script( 'giftflow-admin', $this->get_plugin_url() . 'assets/js/admin.bundle.js', array( 'jquery', 'wp-element', 'react-jsx-runtime' ), $this->get_version(), true );
		wp_enqueue_style( 'giftflow-admin', $this->get_plugin_url() . 'assets/css/admin.bundle.css', array(), $this->get_version() );

		wp_localize_script(
			'giftflow-admin',
			'giftflow_admin',
			array(
				'ajax_url'        => admin_url( 'admin-ajax.php' ),
				'nonce'           => wp_create_nonce( 'giftflow_admin_nonce' ),
				'rest_nonce'      => wp_create_nonce( 'wp_rest' ),
				'admin_url'       => admin_url(),
				'currency_symbol' => giftflow_get_global_currency_symbol(),
			)
		);
	}

	/**
	 * Enqueue scripts
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'giftflow-block-campaign-status-bar', $this->get_plugin_url() . 'assets/css/block-campaign-status-bar.bundle.css', array(), $this->get_version() );
	}

	/**
	 * Enqueue blocks
	 */
	public function enqueue_blocks() {
		wp_enqueue_style( 'giftflow-common', $this->get_plugin_url() . 'assets/css/common.bundle.css', array(), $this->get_version() );

		$args = require $this->get_plugin_dir() . '/blocks-build/index.asset.php';
		wp_enqueue_script( 'giftflow-blocks', $this->get_plugin_url() . '/blocks-build/index.js', $args['dependencies'], $args['version'], true );
		wp_enqueue_style( 'giftflow-block-campaign-status-bar', $this->get_plugin_url() . 'assets/css/block-campaign-status-bar.bundle.css', array(), $this->get_version() );
		wp_enqueue_style( 'giftflow-block-campaign-single-content', $this->get_plugin_url() . 'assets/css/block-campaign-single-content.bundle.css', array(), $this->get_version() );

		// load common js.
		$args_common = require $this->get_plugin_dir() . '/assets/js/common.bundle.asset.php';
		wp_enqueue_script( 'giftflow-common', $this->get_plugin_url() . 'assets/js/common.bundle.js', array( 'jquery' ), $args_common['version'], true );

		// localize script.
		wp_localize_script(
			'giftflow-common',
			'giftflow_common',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'giftflow_common_nonce' ),
			)
		);
	}

	/**
	 * Creating a new (custom) block category.

	 * @param array $categories The block categories.
	 * @return array The block categories.
	 */
	public function register_block_category( $categories ) {
		$categories[] = array(
			'slug'  => 'giftflow',
			'title' => 'GiftFlow',
			'icon'  => 'megaphone',
		);

		return $categories;
	}

	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'enqueue_block_assets', array( $this, 'enqueue_blocks' ) );
		add_filter( 'block_categories_all', array( $this, 'register_block_category' ) );
		add_action( 'giftflow_cleanup_logs', array( $this, 'run_logs_cleanup' ) );
	}

	/**
	 * Load plugin textdomain
	 */
	public function load_textdomain() {
		// load plugin textdomain.
	}

	/**
	 * Initialize plugin components
	 */
	public function init() {
		// core.
		new \GiftFlow\Core\Block_Template();
		\GiftFlow\Core\Role::get_instance();

		// Initialize post types.
		new \GiftFlow\Admin\PostTypes\Donation();
		new \GiftFlow\Admin\PostTypes\Donor();
		new \GiftFlow\Admin\PostTypes\Campaign();

		// Initialize meta boxes.
		new \GiftFlow\Admin\MetaBoxes\Donation_Transaction_Meta();
		new \GiftFlow\Admin\MetaBoxes\Donor_Contact_Meta();
		new \GiftFlow\Admin\MetaBoxes\Campaign_Details_Meta();
		\GiftFlow\Core\Donation_Event_History::register_meta_box();

		// Initialize frontend components.
		new \GiftFlow\Frontend\Shortcodes();
		new \GiftFlow\Frontend\Forms();

		\GiftFlow\Gateways\Gateway_Base::init_gateways();

		add_filter( 'display_post_states', array( $this, 'display_post_states' ), 10, 2 );

		if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
			// Block Theme.
			$this->is_block_theme_init();
		} else {
			// Classic Theme.
			$this->is_classic_theme_init();
		}
	}

	/**
	 * Display post states
	 *
	 * @param array $states The post states.
	 * @param WP_Post $post The post object.
	 * @return array The post states.
	 */
	public function display_post_states( $states, $post ) {
		$campaigns_page = get_page_by_path( 'campaigns' );
		if ( $post->ID === $campaigns_page->ID ) {
			$states[] = __( 'Campaigns Page', 'giftflow' );
		}

		return $states;
	}

	/**
	 * Initialize block theme.
	 */
	public function is_block_theme_init() {
	}

	/**
	 * Initialize classic theme.
	 */
	public function is_classic_theme_init() {

		// override template of campaign details page.
		add_action( 'template_include', array( $this, 'override_campaign_details_page_template' ), 10, 1 );
	}

	/**
	 * Override the content of campaign details page
	 *
	 * @param string $template The template file.
	 */
	public function override_campaign_details_page_template( $template ) {
		// check is current page is campaign details page.
		if ( is_singular( 'campaign' ) ) {

			// use get_template_path of class Template.
			$template = new \GiftFlow\Frontend\Template();
			$template_path = $template->get_template_path( 'single-campaign.php' );

			if ( $template_path ) {
				return $template_path;
			}
		}

		return $template;
	}

	/**
	 * Activate the plugin
	 */
	public function activate() {
		$this->create_pages_init();
		\GiftFlow\Core\Logger::create_table();
		\GiftFlow\Core\Donation_Event_History::create_table();
		$this->schedule_logs_cleanup();

		// reset permalinks.
		flush_rewrite_rules();
	}

	/**
	 * Create 2 pages donor-account and thank-donor & set template for there.
	 */
	public function create_pages_init() {

		// create page campaigns.
		$campaigns_page = get_page_by_path( 'campaigns' );
		if ( ! $campaigns_page ) {

			$campaigns_page_block_content = '<!-- wp:group {"align":"wide","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide"><!-- wp:shortcode -->
[giftflow_campaign_grid per_page="9" orderby="date" order="DESC" paged="1"]
<!-- /wp:shortcode --></div>
<!-- /wp:group -->';

			$campaigns_page = wp_insert_post(
				array(
					'post_title'   => esc_html__( 'Campaigns', 'giftflow' ),
					'post_content' => $campaigns_page_block_content,
					'post_status'  => 'publish',
					'post_type'    => 'page',
				)
			);

			update_post_meta(
				$campaigns_page,
				'_wp_page_template',
				'campaigns-page'
			);
		}

		// create 2 pages donor-account and thank-donor & set template for there.
		$donor_account_page = get_page_by_path( 'donor-account' );
		if ( ! $donor_account_page ) {

			$donor_account_page = wp_insert_post(
				array(
					'post_title'   => esc_html__( 'Donor Account', 'giftflow' ),
					'post_content' => '',
					'post_status'  => 'publish',
					'post_type'    => 'page',
				)
			);

			update_post_meta(
				$donor_account_page,
				'_wp_page_template',
				'donor-account'
			);
		}

		$thank_donor_page = get_page_by_path( 'thank-donor' );
		if ( ! $thank_donor_page ) {

			$thank_donor_page = wp_insert_post(
				array(
					'post_title'   => esc_html__( 'Thank Donor', 'giftflow' ),
					'post_content' => '',
					'post_status'  => 'publish',
					'post_type'    => 'page',
				)
			);

			update_post_meta(
				$thank_donor_page,
				'_wp_page_template',
				'thank-donor'
			);
		}
	}

	/**
	 * Deactivate the plugin
	 */
	public function deactivate() {
		$this->unschedule_logs_cleanup();
		// Clean up roles and capabilities.
		$role_manager = \GiftFlow\Core\Role::get_instance();
		$role_manager->remove_roles();
		$role_manager->remove_capabilities();
	}

	/**
	 * Schedule daily logs cleanup cron.
	 */
	private function schedule_logs_cleanup() {
		if ( ! wp_next_scheduled( 'giftflow_cleanup_logs' ) ) {
			wp_schedule_event( time(), 'daily', 'giftflow_cleanup_logs' );
		}
	}

	/**
	 * Unschedule logs cleanup cron.
	 */
	private function unschedule_logs_cleanup() {
		$timestamp = wp_next_scheduled( 'giftflow_cleanup_logs' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'giftflow_cleanup_logs' );
		}
	}

	/**
	 * Run logs cleanup (called by cron). Deletes old entries by retention rules.
	 */
	public function run_logs_cleanup() {
		\GiftFlow\Core\Logger::cleanup();
	}
}
