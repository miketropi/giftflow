<?php
/**
 * Shortcodes class for GiftFlow
 *
 * @package GiftFlow
 * @subpackage Frontend
 */

namespace GiftFlow\Frontend;

use GiftFlow\Core\Base;

/**
 * Handles all shortcode functionality
 */
class Shortcodes extends Base {
	/**
	 * Initialize shortcodes
	 */
	public function __construct() {
		parent::__construct();
		$this->init_shortcodes();
	}

	/**
	 * Register shortcodes
	 */
	private function init_shortcodes() {
		// donation form shortcode.
		add_shortcode( 'giftflow_donation_form', array( $this, 'render_donation_form' ) );

		// campaign grid shortcode.
		add_shortcode( 'giftflow_campaign_grid', array( $this, 'render_campaign_grid' ) );

		// campaign status bar shortcode.
		add_shortcode( 'giftflow_campaign_status_bar', array( $this, 'render_campaign_status_bar' ) );
	}

	/**
	 * Render donation form shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_donation_form( $atts ) {
		$atts = shortcode_atts(
			array(
				'campaign_id' => 0,
			),
			$atts
		);

		$campaign_id = intval( $atts['campaign_id'] );
		if ( ! $campaign_id ) {
			return;
		}

		// gateways.
		$gateways = \GiftFlow\Gateways\Gateway_Base::get_registered_gateways();

		// preset donation amounts.
		$preset_donation_amounts = giftflow_get_preset_donation_amounts_by_campaign( $campaign_id );

		// allow_custom_donation_amounts.
		$allow_custom_donation_amounts = filter_var( get_post_meta( $campaign_id, '_allow_custom_donation_amounts', true ), FILTER_VALIDATE_BOOLEAN );

		// raised amount.
		$raised_amount = giftflow_get_campaign_raised_amount( $campaign_id );

		// goal amount.
		$goal_amount = giftflow_get_campaign_goal_amount( $campaign_id );

		// Get default donation amount (first preset amount or 10).
		$default_amount = ! empty( $preset_donation_amounts ) ? $preset_donation_amounts[0]['amount'] : 10;

		// Get campaign title.
		$campaign_title = get_the_title( $campaign_id );

		// Get currency symbol.
		$currency_symbol = giftflow_get_global_currency_symbol();

		// Get currency format template.
		$currency_format_template = giftflow_get_currency_js_format_template();

		// array of donation types.
		$donation_types = array();

		// get one-time donation.
		$one_time_donation = get_post_meta( $campaign_id, '_one_time', true );

		// if one-time donation is on, add it to the array.
		if ( $one_time_donation ) {
			$donation_types[] = array(
				'name'        => 'one-time',
				'icon'        => '',
				'label'       => __( 'One-time Donation', 'giftflow' ),
				'description' => __( 'Make a single donation', 'giftflow' ),
			);
		}

		$user_fullname      = '';
		$user_email         = '';
		$user_info_readonly = false;
		if ( is_user_logged_in() ) {
			$current_user       = wp_get_current_user();
			$user_fullname      = $current_user->display_name;
			$user_email         = $current_user->user_email;
			$user_info_readonly = true;
		}

		// localtion.
		$location = get_post_meta( $campaign_id, '_location', true );

		// gallery.
		$gallery = get_post_meta( $campaign_id, '_gallery', true );

		ob_start();

		// filter gateways by is_enabled().
		$gateways = array_filter(
			$gateways,
			function ( $gateway ) {
				return $gateway->is_enabled();
			}
		);

		$atts['gateways']                 = $gateways;
		$atts['preset_donation_amounts']  = $preset_donation_amounts;
		$atts['allow_custom_donation_amounts'] = $allow_custom_donation_amounts;
		$atts['raised_amount']            = $raised_amount;
		$atts['goal_amount']              = $goal_amount;
		$atts['default_amount']           = $default_amount;
		$atts['campaign_title']           = $campaign_title;
		$atts['currency_symbol']          = $currency_symbol;
		$atts['currency_format_template'] = $currency_format_template;
		$atts['recurring_interval']       = $recurring_interval;
		$atts['location']                 = $location;
		$atts['gallery']                  = $gallery;

		/**
		 * Filter the donation types for the donation form.
		 *
		 * @since 1.0.0
		 *
		 * @param array $donation_types The available donation types.
		 * @param int   $campaign_id    The campaign post ID.
		 */
		$atts['donation_types'] = apply_filters( 'giftflow_form_donation_types', $donation_types, $campaign_id );

		$atts['user_fullname']            = $user_fullname;
		$atts['user_email']               = $user_email;
		$atts['user_info_readonly']       = $user_info_readonly;

		// get global options.
		$atts['min_amount'] = giftflow_get_options( 'min_amount', 'giftflow_general_options', 1 );
		$atts['max_amount'] = giftflow_get_options( 'max_amount', 'giftflow_general_options', 1000 );

		// load the donation form template use class-template.php.
		$template = new Template();
		$template->load_template( 'donation-form.php', apply_filters( 'giftflow_form_donation_form_atts', $atts, $campaign_id ) );
		return ob_get_clean();
	}

	/**
	 * Render campaign grid shortcode
	 * [giftflow_campaign_grid per_page="10" orderby="date" order="DESC" category="1" search="test" paged="1"]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_campaign_grid( $atts ) {
		$atts = shortcode_atts(
			array(
				'per_page' => 10,
				'orderby' => 'date',
				'order' => 'DESC',
				'category' => '',
				'search' => '',
				'paged' => 1,
				'post_type' => 'campaign',
				'custom_class' => '',
			),
			$atts
		);

		// paged maybe override by get parameter.
		$paged = get_query_var( 'paged' );
		if ( ! empty( $paged ) ) {
			$atts['paged'] = intval( $paged );
		}

		// Get campaigns using the Campaigns class.
		$campaigns_class = new \GiftFlow\Core\Campaigns();
		$query_args = array(
			'posts_per_page' => intval( $atts['per_page'] ),
			'orderby' => sanitize_text_field( $atts['orderby'] ),
			'order' => strtoupper( sanitize_text_field( $atts['order'] ) ),
			'paged' => intval( $atts['paged'] ),
		);

		// Add category filter if provided.
		if ( ! empty( $atts['category'] ) ) {
			$query_args['category'] = $atts['category'];
		}

		// Add search if provided.
		if ( ! empty( $atts['search'] ) ) {
			$query_args['search'] = $atts['search'];
		}

		$campaigns_result = $campaigns_class->get_campaigns( $query_args );

		// Add campaigns data to atts for template.
		$atts['campaigns'] = $campaigns_result['campaigns'];
		$atts['total'] = $campaigns_result['total'];
		$atts['pages'] = $campaigns_result['pages'];
		$atts['current_page'] = $campaigns_result['current_page'];

		ob_start();

		// load the campaign grid template use class-template.php.
		giftflow_load_template( 'campaign-grid.php', apply_filters( 'giftflow_form_campaign_grid_atts', $atts ) );
		return ob_get_clean();
	}

	/**
	 * Render campaign status bar shortcode
	 * [giftflow_campaign_status_bar campaign_id="1"]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_campaign_status_bar( $atts ) {
		$atts = shortcode_atts(
			array(
				'campaign_id' => 0,
			),
			$atts
		);

		$campaign_id = intval( $atts['campaign_id'] );
		if ( ! $campaign_id ) {
			return;
		}

		// Prepare template data using helper function.
		$template_data = giftflow_prepare_campaign_status_bar_data( $campaign_id );

		ob_start();

		// load the campaign status bar template use class-template.php.
		giftflow_load_template( 'block/campaign-status-bar.php', $template_data );
		return ob_get_clean();
	}
}
