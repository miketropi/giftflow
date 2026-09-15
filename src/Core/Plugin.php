<?php
/**
 * Main Plugin bootstrap for GiftFlow.
 *
 * Replaces the procedural giftflow_load_files() + Loader pattern
 * with a container-based architecture. All services are registered
 * through the container and booted on plugins_loaded.
 *
 * @package GiftFlow
 * @subpackage Core
 */

namespace GiftFlow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The core plugin class — single entry point for boot, activate, deactivate.
 */
class Plugin {

	/**
	 * Service container.
	 *
	 * @var Container
	 */
	private Container $container;

	/**
	 * Whether the legacy file loader has run.
	 *
	 * @var bool
	 */
	private bool $legacy_loaded = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->container = new Container();
	}

	/**
	 * Boot the plugin on plugins_loaded.
	 *
	 * @return void
	 */
	public function boot(): void {
		$this->load_legacy_files();
		$this->register_core_services();
		$this->register_services_from_filter();
		$this->boot_registered_modules();

		add_action( 'init', array( $this, 'on_init' ) );
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_filter( 'block_categories_all', array( $this, 'register_block_category' ) );

		$this->register_admin_menus();
		$this->schedule_cron_jobs();
	}

	/**
	 * Run on init hook.
	 *
	 * @return void
	 */
	public function on_init(): void {
		$this->init_post_types();
		$this->init_meta_boxes();
		$this->init_gateways();
		$this->init_frontend();
		$this->init_block_templates();
		$this->register_campaign_meta_for_rest();

		add_filter( 'display_post_states', array( $this, 'display_post_states' ), 10, 2 );

		if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
			$this->init_block_theme();
		} else {
			$this->init_classic_theme();
		}
	}

	/**
	 * Register campaign post meta for REST API exposure.
	 *
	 * Makes all campaign meta fields available via the WordPress
	 * REST API so the block editor can fetch real campaign data.
	 *
	 * @return void
	 */
	public function register_campaign_meta_for_rest(): void {
		$campaign_meta_fields = array(
			'_goal_amount'                  => array(
				'type' => 'number',
				'description' => 'Fundraising goal amount.',
			),
			'_start_date'                   => array(
				'type' => 'string',
				'description' => 'Campaign start date.',
			),
			'_end_date'                     => array(
				'type' => 'string',
				'description' => 'Campaign end date.',
			),
			'_status'                       => array(
				'type' => 'string',
				'description' => 'Campaign custom status.',
			),
			'_one_time'                     => array(
				'type' => 'boolean',
				'description' => 'Allow one-time donations.',
			),
			'_recurring'                    => array(
				'type' => 'boolean',
				'description' => 'Allow recurring donations.',
			),
			'_recurring_interval'           => array(
				'type' => 'string',
				'description' => 'Recurring donation interval.',
			),
			'_recurring_number_of_times'    => array(
				'type' => 'number',
				'description' => 'Max recurring donation cycles.',
			),
			'_preset_donation_amounts'      => array(
				'type'        => 'string',
				'description' => 'Preset donation amount choices.',
				'single'      => true,
			),
			'_allow_custom_donation_amounts' => array(
				'type' => 'boolean',
				'description' => 'Allow custom donation amounts.',
			),
			'_location'                     => array(
				'type' => 'string',
				'description' => 'Campaign physical location.',
			),
			'_gallery'                      => array(
				'type'        => 'string',
				'description' => 'Campaign gallery image IDs.',
				'single'      => true,
			),
		);

		foreach ( $campaign_meta_fields as $meta_key => $config ) {
			$type        = $config['type'];
			$description = $config['description'];
			$single      = $config['single'] ?? true;

			$args = array(
				'type'         => $type,
				'description'  => $description,
				'single'       => $single,
				'show_in_rest' => 'array' === $type
					? array(
						'schema' => array(
							'items' => array(
								'type' => 'string',
							),
						),
					)
					: true,
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			);

			// For array types, add schema for sanitisation.
			if ( 'array' === $type ) {
				$args['sanitize_callback'] = function ( $value ) {
					if ( ! is_array( $value ) ) {
						return array();
					}
					return array_map( 'sanitize_text_field', $value );
				};
			}

			register_post_meta( 'campaign', $meta_key, $args );
		}
	}

	/**
	 * Load textdomain.
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		// Translations loaded via WordPress standard mechanism.
	}

	/**
	 * Activate the plugin.
	 *
	 * @return void
	 */
	public function activate(): void {
		$this->load_legacy_files();
		$this->create_default_pages();
		Logger::create_table();
		Donation_Event_History::create_table();
		$this->schedule_logs_cleanup();
		flush_rewrite_rules();
	}

	/**
	 * Deactivate the plugin.
	 *
	 * @return void
	 */
	public function deactivate(): void {
		$this->load_legacy_files();
		$this->unschedule_logs_cleanup();

		$role = \GiftFlow\Core\Role::get_instance();
		$role->remove_roles();
		$role->remove_capabilities();
	}

	/**
	 * Get the container.
	 *
	 * @return Container
	 */
	public function container(): Container {
		return $this->container;
	}

	/**
	 * Load legacy PHP files for backward compatibility.
	 *
	 * @return void
	 */
	private function load_legacy_files(): void {
		if ( $this->legacy_loaded ) {
			return;
		}

		giftflow_load_files();
		$this->legacy_loaded = true;
	}

	/**
	 * Register core infrastructure services in the container.
	 *
	 * @return void
	 */
	private function register_core_services(): void {
		$this->container->register(
			array(
				\GiftFlow\Core\AssetLoader::class             => function ( Container $c ) {
					return new \GiftFlow\Core\AssetLoader( $c );
				},
				\GiftFlow\Blocks\BlockRegistry::class          => function ( Container $c ) {
					return new \GiftFlow\Blocks\BlockRegistry( $c );
				},
				\GiftFlow\Settings\SettingsRegistry::class     => function ( Container $c ) {
					return new \GiftFlow\Settings\SettingsRegistry( $c );
				},
				\GiftFlow\Gateways\GatewayRegistry::class      => function ( Container $c ) {
					return new \GiftFlow\Gateways\GatewayRegistry( $c );
				},
				\GiftFlow\Patterns\PatternRegistry::class    => function ( Container $c ) {
					return new \GiftFlow\Patterns\PatternRegistry( $c );
				},
			)
		);

		// Register REST controllers.
		add_action(
			'rest_api_init',
			function () {
				$settings_ctrl = new \GiftFlow\REST\SettingsController(
					$this->container->get( \GiftFlow\Settings\SettingsRegistry::class )
				);
				$settings_ctrl->register_routes();

				$dashboard_ctrl = new \GiftFlow\REST\DashboardController();
				$dashboard_ctrl->register_routes();

				$campaign_ctrl = new \GiftFlow\REST\CampaignController();
				$campaign_ctrl->register_routes();
			}
		);

		// Register currency service.
		$this->container->set(
			\GiftFlow\Services\CurrencyService::class,
			function ( Container $c ) {
				return new \GiftFlow\Services\CurrencyService( $c );
			}
		);
	}

	/**
	 * Apply the giftflow.services filter for third-party registration.
	 *
	 * @return void
	 */
	private function register_services_from_filter(): void {
		$services = apply_filters( HookManager::FILTER_SERVICES, array() );

		if ( ! empty( $services ) && is_array( $services ) ) {
			$this->container->register( $services );
		}
	}

	/**
	 * Boot all registered modules by calling their register() method.
	 *
	 * @return void
	 */
	private function boot_registered_modules(): void {
		$modules = array(
			\GiftFlow\Core\AssetLoader::class,
			\GiftFlow\Core\Compat::class,
			\GiftFlow\Blocks\BlockRegistry::class,
			\GiftFlow\Settings\SettingsRegistry::class,
			\GiftFlow\Gateways\GatewayRegistry::class,
			\GiftFlow\Patterns\PatternRegistry::class,
		);

		foreach ( $modules as $class ) {
			$instance = $this->container->get( $class );
			if ( $instance instanceof AbstractModule ) {
				$instance->register();
			}
		}
	}

	/**
	 * Initialize post types.
	 *
	 * @return void
	 */
	private function init_post_types(): void {
		new \GiftFlow\Admin\PostTypes\Donation();
		new \GiftFlow\Admin\PostTypes\Donor();
		new \GiftFlow\Admin\PostTypes\Campaign();
	}

	/**
	 * Initialize meta boxes.
	 *
	 * @return void
	 */
	private function init_meta_boxes(): void {
		new \GiftFlow\Admin\MetaBoxes\Donation_Transaction_Meta();
		new \GiftFlow\Admin\MetaBoxes\Donor_Contact_Meta();
		new \GiftFlow\Admin\MetaBoxes\Campaign_Details_Meta();
		\GiftFlow\Core\Donation_Event_History::register_meta_box();
	}

	/**
	 * Initialize payment gateways.
	 *
	 * @return void
	 */
	private function init_gateways(): void {
		\GiftFlow\Gateways\Gateway_Base::init_gateways();
	}

	/**
	 * Initialize frontend components.
	 *
	 * @return void
	 */
	private function init_frontend(): void {
		new \GiftFlow\Frontend\Shortcodes();
		new \GiftFlow\Frontend\Forms();
	}

	/**
	 * Initialize block template injection for FSE themes.
	 *
	 * @return void
	 */
	private function init_block_templates(): void {
		\GiftFlow\Core\Role::get_instance();
		new \GiftFlow\Core\Block_Template();
	}

	/**
	 * Initialize block theme hooks.
	 *
	 * @return void
	 */
	private function init_block_theme(): void {
		// Block themes use FSE templates injected via Block_Template.
		// Additional block-theme-only hooks can be added here.
	}

	/**
	 * Initialize classic theme hooks (template_include overrides).
	 *
	 * @return void
	 */
	private function init_classic_theme(): void {
		if ( current_theme_supports( 'giftflow' ) ) {
			add_action( 'template_include', array( $this, 'override_campaign_single_template' ) );
		} else {
			add_filter( 'the_content', array( $this, 'filter_campaign_single_content' ), 10, 1 );
		}

		add_action( 'template_include', array( $this, 'override_campaign_taxonomy_archive_template' ) );
		add_action( 'template_include', array( $this, 'override_campaigns_page_template' ) );
		add_action( 'template_include', array( $this, 'override_my_account_page_template' ) );
		add_action( 'template_include', array( $this, 'override_thank_donor_page_template' ) );
	}

	/**
	 * Register the GiftFlow block category.
	 *
	 * @param array $categories Existing block categories.
	 * @return array
	 */
	public function register_block_category( array $categories ): array {
		$categories[] = array(
			'slug'  => 'giftflow',
			'title' => 'GiftFlow',
			'icon'  => 'megaphone',
		);

		return $categories;
	}

	/**
	 * Display custom post states for GiftFlow pages.
	 *
	 * @param array    $states Post states.
	 * @param \WP_Post $post   Post object.
	 * @return array
	 */
	public function display_post_states( array $states, \WP_Post $post ): array {
		$campaigns_page = get_page_by_path( 'campaigns' );
		if ( $campaigns_page && $post->ID === $campaigns_page->ID ) {
			$states[] = __( 'Campaigns Page', 'giftflow' );
		}

		$privacy_page = get_page_by_path( 'donation-privacy-policy' );
		if ( $privacy_page && $post->ID === $privacy_page->ID ) {
			$states[] = __( 'Donation Privacy Policy', 'giftflow' );
		}

		$terms_page = get_page_by_path( 'donation-terms-conditions' );
		if ( $terms_page && $post->ID === $terms_page->ID ) {
			$states[] = __( 'Donation Terms & Conditions', 'giftflow' );
		}

		return $states;
	}

	/**
	 * Override singular campaign template for classic themes.
	 *
	 * @param string $template Current template path.
	 * @return string
	 */
	public function override_campaign_single_template( string $template ): string {
		if ( ! is_singular( 'campaign' ) ) {
			return $template;
		}

		$theme_template = locate_template( 'giftflow.php' );
		if ( ! empty( $theme_template ) && file_exists( $theme_template ) ) {
			return $theme_template;
		}

		$gf_template   = new \GiftFlow\Frontend\Template();
		$template_path = $gf_template->get_template_path( 'classic/single-campaign.php' );

		return ( $template_path && is_readable( $template_path ) )
			? $template_path
			: $template;
	}

	/**
	 * Filter the_content for singular campaign (fallback for unsupported themes).
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function filter_campaign_single_content( string $content ): string {
		if ( ! is_singular( 'campaign' ) ) {
			return $content;
		}

		if ( is_feed() || is_admin() || wp_doing_ajax() ) {
			return $content;
		}

		if ( ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$template  = new \GiftFlow\Frontend\Template();
		$inner_tpl = $template->get_template_path( 'classic/single-campaign-inner.php' );

		if ( ! $inner_tpl || ! is_readable( $inner_tpl ) ) {
			return $content;
		}

		ob_start();
		$template->load_template( 'classic/single-campaign-inner.php' );
		$html = ob_get_clean();

		if ( '' === trim( (string) $html ) ) {
			return $content;
		}

		return apply_filters( 'giftflow_campaign_single_the_content', $html, $content );
	}

	/**
	 * Override campaign taxonomy archive template.
	 *
	 * @param string $template Current template path.
	 * @return string
	 */
	public function override_campaign_taxonomy_archive_template( string $template ): string {
		if ( ! is_tax( 'campaign-tax' ) ) {
			return $template;
		}

		$theme_template = locate_template( 'giftflow.php' );
		if ( ! empty( $theme_template ) && file_exists( $theme_template ) ) {
			return $theme_template;
		}

		$gf_template   = new \GiftFlow\Frontend\Template();
		$template_path = $gf_template->get_template_path( 'classic/taxonomy-campaign-archive.php' );

		return ( $template_path && is_readable( $template_path ) )
			? $template_path
			: $template;
	}

	/**
	 * Override campaigns page template.
	 *
	 * @param string $template Current template path.
	 * @return string
	 */
	public function override_campaigns_page_template( string $template ): string {
		if ( ! is_campaigns_page() ) {
			return $template;
		}

		$theme_template = locate_template( 'giftflow.php' );
		if ( ! empty( $theme_template ) && file_exists( $theme_template ) ) {
			return $theme_template;
		}

		$gf_template   = new \GiftFlow\Frontend\Template();
		$template_path = $gf_template->get_template_path( 'classic/campaigns-page.php' );

		return ( $template_path && is_readable( $template_path ) )
			? $template_path
			: $template;
	}

	/**
	 * Override donor account page template.
	 *
	 * @param string $template Current template path.
	 * @return string
	 */
	public function override_my_account_page_template( string $template ): string {
		if ( ! is_my_account_page() ) {
			return $template;
		}

		$theme_template = locate_template( 'giftflow.php' );
		if ( ! empty( $theme_template ) && file_exists( $theme_template ) ) {
			return $theme_template;
		}

		$gf_template   = new \GiftFlow\Frontend\Template();
		$template_path = $gf_template->get_template_path( 'classic/donor-account.php' );

		return ( $template_path && is_readable( $template_path ) )
			? $template_path
			: $template;
	}

	/**
	 * Override thank donor page template.
	 *
	 * @param string $template Current template path.
	 * @return string
	 */
	public function override_thank_donor_page_template( string $template ): string {
		if ( ! is_thank_donor_page() ) {
			return $template;
		}

		$theme_template = locate_template( 'giftflow.php' );
		if ( ! empty( $theme_template ) && file_exists( $theme_template ) ) {
			return $theme_template;
		}

		$gf_template   = new \GiftFlow\Frontend\Template();
		$template_path = $gf_template->get_template_path( 'classic/thank-donor.php' );

		return ( $template_path && is_readable( $template_path ) )
			? $template_path
			: $template;
	}

	/**
	 * Register admin menus and submenus.
	 *
	 * @return void
	 */
	private function register_admin_menus(): void {
		add_action(
			'admin_menu',
			function () {
				if ( defined( 'GIFTFLOW_PRO_PLUGIN_DIR' ) ) {
					return;
				}

				add_submenu_page(
					'giftflow-dashboard',
					esc_html__( 'Get Pro ✦', 'giftflow' ),
					esc_html__( 'Get Pro ✦', 'giftflow' ),
					'manage_options',
					'giftflow-get-pro',
					function () {
						echo '<script>window.location.href = \'https://giftflow.beplus-agency.cloud/pro\';</script>';
					},
					99
				);
			},
			99
		);
	}

	/**
	 * Create default pages on activation.
	 *
	 * @return void
	 */
	private function create_default_pages(): void {
		$pages = array(
			'campaigns'                  => esc_html__( 'Campaigns', 'giftflow' ),
			'donor-account'              => esc_html__( 'Donor Account', 'giftflow' ),
			'thank-donor'                => esc_html__( 'Thank Donor', 'giftflow' ),
			'donation-privacy-policy'    => esc_html__( 'Donation Privacy Policy', 'giftflow' ),
			'donation-terms-conditions'  => esc_html__( 'Donation Terms & Conditions', 'giftflow' ),
		);

		foreach ( $pages as $slug => $title ) {
			$existing = get_page_by_path( $slug );
			if ( ! $existing ) {
				$content = '';

				if ( 'campaigns' === $slug ) {
					$content = apply_filters( 'giftflow_campaigns_page_content_on_create', '' );
				}

				if ( in_array( $slug, array( 'donation-privacy-policy', 'donation-terms-conditions' ), true ) ) {
					$file_path = GIFTFLOW_PLUGIN_DIR . 'block-templates/page-content/' . $slug . '.html';
					if ( file_exists( $file_path ) ) {
						$content = file_get_contents( $file_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local file read, not remote
					}
				}

				wp_insert_post(
					array(
						'post_title'   => $title,
						'post_content' => $content,
						'post_status'  => 'publish',
						'post_type'    => 'page',
					)
				);
			}
		}
	}

	/**
	 * Schedule cron jobs.
	 *
	 * @return void
	 */
	private function schedule_cron_jobs(): void {
		add_action( HookManager::CRON_LOG_CLEANUP, array( $this, 'run_logs_cleanup' ) );
	}

	/**
	 * Schedule daily log cleanup.
	 *
	 * @return void
	 */
	private function schedule_logs_cleanup(): void {
		if ( ! wp_next_scheduled( HookManager::CRON_LOG_CLEANUP ) ) {
			wp_schedule_event( time(), 'daily', HookManager::CRON_LOG_CLEANUP );
		}
	}

	/**
	 * Unschedule log cleanup.
	 *
	 * @return void
	 */
	private function unschedule_logs_cleanup(): void {
		$timestamp = wp_next_scheduled( HookManager::CRON_LOG_CLEANUP );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, HookManager::CRON_LOG_CLEANUP );
		}
	}

	/**
	 * Run log cleanup (cron callback).
	 *
	 * @return void
	 */
	public function run_logs_cleanup(): void {
		Logger::cleanup();
	}
}
