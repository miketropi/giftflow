<?php
/**
 * Base Gateway class for GiftFlow
 *
 * This class provides a comprehensive foundation for creating payment gateways
 * with automatic registration, settings management, and asset enqueuing.
 *
 * @package GiftFlow
 * @subpackage Gateways
 * @since 1.0.0
 * @version 1.0.0
 */

namespace GiftFlow\Gateways;

use GiftFlow\Core\Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base class for payment gateways
 */
abstract class Gateway_Base extends Base {
	/**
	 * Gateway ID
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Gateway title
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Gateway description
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * Gateway icon URL
	 *
	 * @var string
	 */
	protected $icon;

	/**
	 * Gateway enabled status
	 *
	 * @var bool
	 */
	protected $enabled = false;

	/**
	 * Gateway settings
	 *
	 * @var array
	 */
	protected $settings = array();

	/**
	 * Gateway supports
	 *
	 * @var array
	 */
	protected $supports = array();

	/**
	 * Order of gateway display
	 *
	 * @var int
	 */
	protected $order = 10;

	/**
	 * Gateway scripts
	 *
	 * @var array
	 */
	protected $scripts = array();

	/**
	 * Gateway styles
	 *
	 * @var array
	 */
	protected $styles = array();

	/**
	 * Template HTML
	 *
	 * @var string
	 */
	protected $template_html = '';

	/**
	 * Static registry for all gateways
	 *
	 * @var array
	 */
	private static $gateway_registry = array();

	/**
	 * Initialize gateway
	 */
	public function __construct() {
		parent::__construct();
		$this->init_gateway();
		$this->init_settings();
		$this->ready(); // Allow child classes to do additional initialization.
		$this->init_hooks();
		$this->register_gateway();
	}

	/**
	 * Initialize gateway properties
	 * Child classes should override this method
	 */
	protected function init_gateway() {
		// Override in child classes.
	}

	/**
	 * Ready function
	 */
	protected function ready() {
	}

	/**
	 * Initialize gateway settings
	 */
	protected function init_settings() {
		$opts           = get_option( 'giftflow_payment_options', array() ); // all options of payment gateways.
		$this->settings = isset( $opts[ $this->id ] ) ? $opts[ $this->id ] : array();
		$enabled_field_name    = $this->id . '_enabled';
		$enabled             = isset( $this->settings[ $enabled_field_name ] ) ? '1' === $this->settings[ $enabled_field_name ] : false;
		$this->enabled       = $enabled;
		$this->template_html = $this->template_html();
	}

	/**
	 * Initialize WordPress hooks
	 */
	protected function init_hooks() {
		// Core gateway hooks.
		add_filter( 'giftflow_payment_gateways', array( $this, 'add_gateway_to_list' ) );
		add_action( 'giftflow_payment_methods_settings', array( $this, 'register_settings_fields' ) );

		// Asset hooks.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// Additional hooks for child classes.
		$this->init_additional_hooks();

		// Allow third parties to add hooks.
		do_action( 'giftflow_gateway_init_hooks', $this );
	}

	/**
	 * Additional hooks for child classes
	 */
	protected function init_additional_hooks() {
		// Override in child classes.
	}

	/**
	 * Register this gateway in the global registry
	 */
	protected function register_gateway() {
		if ( ! empty( $this->id ) ) {
			self::$gateway_registry[ $this->id ] = $this;

			// Fire action after gateway registration.
			do_action( 'giftflow_gateway_registered', $this->id, $this );
		}
	}

	/**
	 * Add gateway to the gateways list
	 *
	 * @param array $gateways List of gateways.
	 * @return array
	 */
	public function add_gateway_to_list( $gateways ) {
		$gateways[ $this->id ] = array(
			'id'          => $this->id,
			'title'       => $this->title,
			'description' => $this->description,
			'icon'        => $this->icon,
			'enabled'     => $this->enabled,
			'order'       => $this->order,
			'supports'    => $this->supports,
			'instance'    => $this,
		);

		return $gateways;
	}


	/**
	 * Enqueue frontend assets
	 */
	public function enqueue_frontend_assets() {
		if ( ! $this->enabled ) {
			return;
		}

		// Enqueue scripts.
		foreach ( $this->scripts as $handle => $script ) {
			if ( isset( $script['frontend'] ) && $script['frontend'] ) {
				wp_enqueue_script(
					$handle,
					$script['src'],
					$script['deps'] ?? array(),
					$script['version'] ?? $this->version,
					$script['in_footer'] ?? true
				);

				// Localize script if data provided.
				if ( isset( $script['localize'] ) ) {
					wp_localize_script( $handle, $script['localize']['name'], $script['localize']['data'] );
				}
			}
		}

		// Enqueue styles.
		foreach ( $this->styles as $handle => $style ) {
			if ( isset( $style['frontend'] ) && $style['frontend'] ) {
				wp_enqueue_style(
					$handle,
					$style['src'],
					$style['deps'] ?? array(),
					$style['version'] ?? $this->version,
					$style['media'] ?? 'all'
				);
			}
		}

		// Allow additional frontend assets.
		do_action( 'giftflow_gateway_enqueue_frontend_assets', $this->id );
	}

	/**
	 * Enqueue admin assets
	 */
	public function enqueue_admin_assets() {
		if ( ! $this->enabled ) {
			return;
		}

		// Enqueue admin scripts.
		foreach ( $this->scripts as $handle => $script ) {
			if ( isset( $script['admin'] ) && $script['admin'] ) {
				wp_enqueue_script(
					$handle,
					$script['src'],
					$script['deps'] ?? array(),
					$script['version'] ?? $this->version,
					$script['in_footer'] ?? true
				);

				// Localize script if data provided.
				if ( isset( $script['localize'] ) ) {
					wp_localize_script( $handle, $script['localize']['name'], $script['localize']['data'] );
				}
			}
		}

		// Enqueue admin styles.
		foreach ( $this->styles as $handle => $style ) {
			if ( isset( $style['admin'] ) && $style['admin'] ) {
				wp_enqueue_style(
					$handle,
					$style['src'],
					$style['deps'] ?? array(),
					$style['version'] ?? $this->version,
					$style['media'] ?? 'all'
				);
			}
		}

		// Allow additional admin assets.
		do_action( 'giftflow_gateway_enqueue_admin_assets', $this->id );
	}

	/**
	 * Add script to be enqueued
	 *
	 * @param string $handle Script handle.
	 * @param array  $script_args Script arguments.
	 */
	protected function add_script( $handle, $script_args ) {
		$this->scripts[ $handle ] = $script_args;
	}

	/**
	 * Add style to be enqueued
	 *
	 * @param string $handle Style handle.
	 * @param array  $style_args Style arguments.
	 */
	protected function add_style( $handle, $style_args ) {
		$this->styles[ $handle ] = $style_args;
	}

	/**
	 * Get all registered gateways.
	 *
	 * @return array
	 */
	public static function get_registered_gateways() {
		return self::$gateway_registry;
	}

	/**
	 * Get gateway by ID
	 *
	 * @param string $gateway_id Gateway ID.
	 * @return Gateway_Base|null
	 */
	public static function get_gateway( $gateway_id ) {
		return isset( self::$gateway_registry[ $gateway_id ] ) ? self::$gateway_registry[ $gateway_id ] : null;
	}

	/**
	 * Initialize all gateways
	 */
	public static function init_gateways() {
		// Allow plugins to register gateways.
		do_action( 'giftflow_register_gateways' );

		// Sort gateways by order.
		uasort(
			self::$gateway_registry,
			function ( $a, $b ) {
				return $a->get_order() - $b->get_order();
			}
		);

		// Fire action after all gateways initialized.
		do_action( 'giftflow_gateways_initialized', self::$gateway_registry );
	}

	/**
	 * Get gateway settings fields.
	 *
	 * @return array
	 */
	abstract protected function register_settings_fields();

	/**
	 * Get gateway template HTML.
	 *
	 * @return string
	 */
	abstract public function template_html();

	/**
	 * Process payment
	 *
	 * @param array $data Payment data.
	 * @param int   $donation_id Donation ID.
	 * @return mixed
	 */
	abstract public function process_payment( $data, $donation_id = 0 );

	// Getters.
	/**
	 * Get gateway ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id; }

	/**
	 * Get gateway title.
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title; }

	/**
	 * Get gateway description.
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->description; }

	/**
	 * Get gateway icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return $this->icon; }

	/**
	 * Get gateway enabled status.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return $this->enabled; }

	/**
	 * Get gateway order.
	 *
	 * @return int
	 */
	public function get_order() {
		return $this->order; }

	/**
	 * Get gateway supports.
	 *
	 * @return array
	 */
	public function get_supports() {
		return $this->supports; }

	/**
	 * Get gateway settings.
	 *
	 * @return array
	 */
	public function get_settings() {
		return $this->settings; }

	/**
	 * Get gateway setting.
	 *
	 * @param string $key Setting key.
	 * @param string $value_default Default value.
	 * @return string
	 */
	public function get_setting( $key, $value_default = '' ) {
		return isset( $this->settings[ $key ] ) ? $this->settings[ $key ] : $value_default;
	}
}
