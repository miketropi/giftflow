<?php
/**
 * Common functions for the plugin
 *
 * @package GiftFlow
 */

use GiftFlow\Frontend\Template;
use GiftFlow\Core\Role;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Assign donor role to user
 *
 * @param int $user_id User ID.
 * @return bool True if role was assigned, false otherwise.
 */
function giftflow_assign_donor_role( $user_id ) {
	return \GiftFlow\Core\Role::get_instance()->assign_donor_role( $user_id );
}

/**
 * Remove donor role from user
 *
 * @param int $user_id User ID.
 * @return bool True if role was removed, false otherwise.
 */
function giftflow_remove_donor_role( $user_id ) {
	return \GiftFlow\Core\Role::get_instance()->remove_donor_role( $user_id );
}

/**
 * Check if user has donor role
 *
 * @param int $user_id User ID.
 * @return bool True if user has donor role, false otherwise.
 */
function giftflow_user_has_donor_role( $user_id ) {
	return \GiftFlow\Core\Role::get_instance()->user_has_donor_role( $user_id );
}

/**
 * Get the Role instance (for advanced usage)
 *
 * @return \GiftFlow\Core\Role
 */
function giftflow_get_role_manager() {
	return \GiftFlow\Core\Role::get_instance();
}

/**
 * Get allowed SVG tags.
 *
 * @return array Allowed SVG tags.
 */
function giftflow_allowed_svg_tags() {
	return apply_filters(
		'giftflow_allowed_svg_tags',
		array(
			'svg' => array(
				'class' => true,
				'viewbox' => true,
				'aria-hidden' => true,
				'role' => true,
				'xmlns' => true,
				'width' => true,
				'height' => true,
				'fill' => true,
				'focusable' => true,
				'style' => true,
				'id' => true,
				'stroke' => true,
				'stroke-width' => true,
				'stroke-linejoin' => true,
				'stroke-linecap' => true,
			),
			'path' => array(
				'd' => true,
				'fill' => true,
				'stroke' => true,
				'stroke-width' => true,
				'stroke-linecap' => true,
				'stroke-linejoin' => true,
				'class' => true,
				'style' => true,
				'id' => true,
			),
			'g' => array(
				'class' => true,
				'fill' => true,
				'stroke' => true,
				'stroke-width' => true,
				'id' => true,
				'style' => true,
			),
			'circle' => array(
				'cx' => true,
				'cy' => true,
				'r' => true,
				'fill' => true,
				'stroke' => true,
				'stroke-width' => true,
				'class' => true,
				'id' => true,
				'style' => true,
			),
			'rect' => array(
				'x' => true,
				'y' => true,
				'width' => true,
				'height' => true,
				'rx' => true,
				'ry' => true,
				'fill' => true,
				'stroke' => true,
				'stroke-width' => true,
				'class' => true,
				'id' => true,
				'style' => true,
			),
			'title' => array(
				// text content supported.
			),
			'polygon' => array(
				'points' => true,
				'fill' => true,
				'stroke' => true,
				'stroke-width' => true,
				'class' => true,
				'id' => true,
				'style' => true,
			),
			'line' => array(
				'x1' => true,
				'y1' => true,
				'x2' => true,
				'y2' => true,
				'stroke' => true,
				'stroke-width' => true,
				'class' => true,
				'id' => true,
				'style' => true,
			),
			'ellipse' => array(
				'cx' => true,
				'cy' => true,
				'rx' => true,
				'ry' => true,
				'fill' => true,
				'stroke' => true,
				'stroke-width' => true,
				'class' => true,
				'id' => true,
				'style' => true,
			),
		)
	);
}

/**
 * Get SVG icon by name.
 *
 * @param string $name Icon name.
 * @return string SVG icon.
 */
function giftflow_svg_icon( $name ) {
	$icons = require __DIR__ . '/icons.php';
	return isset( $icons[ $name ] ) ? $icons[ $name ] : '';
}

/**
 * Get the raised amount for a campaign
 *
 * @param int $campaign_id The campaign ID.
 * @return float The raised amount.
 */
function giftflow_get_campaign_raised_amount( $campaign_id ) {
	// Get all donations for this campaign.
	$donations = get_posts(
		array(
			'post_type' => 'donation',
			'posts_per_page' => -1,
			'fields' => 'ids',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query' => array(
				array(
					'key' => '_campaign_id',
					'value' => $campaign_id,
					'compare' => '=',
				),
				array(
					'key' => '_status',
					'value' => 'completed',
					'compare' => '=',
				),
			),
		)
	);

	$total_amount = 0;

	// Sum up all completed donations.
	foreach ( $donations as $id ) {
		$amount = get_post_meta( $id, '_amount', true );
		if ( $amount ) {
			$total_amount += floatval( $amount );
		}
	}

	return $total_amount;
}

/**
 * Get the goal amount for a campaign.
 *
 * @param int $campaign_id The campaign ID.
 * @return string The goal amount.
 */
function giftflow_get_campaign_goal_amount( $campaign_id ) {
	return get_post_meta( $campaign_id, '_goal_amount', true );
}

/**
 * Get the percentage of raised amount compared to goal amount
 *
 * @param int $campaign_id The campaign ID.
 * @return float The percentage (0-100).
 */
function giftflow_get_campaign_progress_percentage( $campaign_id ) {
	$raised_amount = giftflow_get_campaign_raised_amount( $campaign_id );
	$goal_amount = get_post_meta( $campaign_id, '_goal_amount', true );

	if ( ! $goal_amount || floatval( $goal_amount ) <= 0 ) {
		return 0;
	}

	$percentage = ( $raised_amount / floatval( $goal_amount ) ) * 100;

	// Ensure percentage is between 0 and 100.
	return min( 100, max( 0, round( $percentage, 2 ) ) );
}

/**
 * Display the campaign progress percentage
 *
 * @param int $campaign_id The campaign ID.
 * @return string HTML formatted progress percentage.
 */
function giftflow_display_campaign_progress( $campaign_id ) {
	$percentage = giftflow_get_campaign_progress_percentage( $campaign_id );
	$raised_amount = giftflow_get_campaign_raised_amount( $campaign_id );
	$goal_amount = get_post_meta( $campaign_id, '_goal_amount', true );

	$html = sprintf(
		'<div class="campaign-progress">
			<div class="progress-bar">
				<div class="progress" style="width: %s%%"></div>
			</div>
			<div class="progress-stats">
				<span class="raised">%s</span> / <span class="goal">%s</span> (%s%%)
			</div>
		</div>',
		esc_attr( $percentage ),
		esc_html( number_format( $raised_amount, 2 ) ),
		esc_html( number_format( $goal_amount, 2 ) ),
		esc_html( $percentage )
	);

	return $html;
}

/**
 * Get common currencies.
 *
 * @return array Common currencies.
 */
function giftflow_get_common_currency() {
	$currencies = require __DIR__ . '/currency.php';

	// apply filter to the currencies.
	$currencies = apply_filters( 'giftflow_common_currencies', $currencies );

	return $currencies;
}

/**
 * Get current currency.
 *
 * @return string Current currency.
 */
function giftflow_get_current_currency() {
	$options = get_option( 'giftflow_general_options' );
	$currency = isset( $options['currency'] ) ? $options['currency'] : 'USD';
	return $currency;
}

/**
 * Get options by group and option.
 *
 * @param string $option Option name.
 * @param string $group Option group.
 * @param string $value_default Default value.
 * @return string Option value.
 */
function giftflow_get_options( $option, $group = 'giftflow_general_options', $value_default = '' ) {
	$options = get_option( $group );
	return isset( $options[ $option ] ) ? $options[ $option ] : $value_default;
}

/**
 * Get symbol of currency.
 *
 * @param string $currency Currency code.
 * @return string Currency symbol.
 */
function giftflow_get_currency_symbol( $currency ) {
	$currencies = giftflow_get_common_currency();
	$_currency = array_filter(
		$currencies,
		function ( $c ) use ( $currency ) {
			return $c['code'] === $currency;
		}
	);
	$_currency = array_values( $_currency );
	return $_currency[0]['symbol'] ?? '';
}

/**
 * Get name of currency.
 *
 * @param string $currency Currency code.
 * @return string Currency name.
 */
function giftflow_get_currency_name( $currency ) {
	$currencies = giftflow_get_common_currency();
	$_currency = array_filter(
		$currencies,
		function ( $c ) use ( $currency ) {
			return $c['code'] === $currency;
		}
	);
	$_currency = array_values( $_currency );
	return $_currency[0]['name'] ?? '';
}

/**
 * Render currency formatted amount
 *
 * @param float $amount Amount.
 * @param float $decimals Decimals.
 * @param string $currency Currency code.
 * @param string $template Template, default: {{currency_symbol}} {{amount}}.
 * @return string
 */
function giftflow_render_currency_formatted_amount( $amount, $decimals = 2, $currency = null, $template = '' ) {
	if ( ! $currency ) {
		$currency = giftflow_get_current_currency();
	}
	$currency_symbol = giftflow_get_currency_symbol( $currency );
	$amount = number_format( $amount, $decimals );

	// replace array map with currency symbol and amount.
	$replace = array(
		'{{currency_symbol}}' => $currency_symbol,
		'{{amount}}' => $amount,
	);

	if ( ! $template ) {
		$template = giftflow_get_currency_template();
	}

	$amount = '<span class="giftflow-currency-formatted-amount gfw-monofont">' . str_replace( array_keys( $replace ), array_values( $replace ), $template ) . '</span>';
	$amount = apply_filters( 'giftflow_render_currency_formatted_amount', $amount, $currency, $decimals );
	return $amount;
}

/**
 * Get global currency symbol.
 *
 * @return string Global currency symbol.
 */
function giftflow_get_global_currency_symbol() {
	$currency = giftflow_get_current_currency();
	$currencies = giftflow_get_common_currency();
	$_currency = array_filter(
		$currencies,
		function ( $c ) use ( $currency ) {
			return $c['code'] === $currency;
		}
	);
	$_currency = array_values( $_currency );
	return $_currency[0]['symbol'] ?? '';
}

/**
 * Get currency template.
 *
 * @return string Currency template.
 */
function giftflow_get_currency_template() {
	$options = get_option( 'giftflow_general_options' );
	$currency_template = isset( $options['currency_template'] ) ? $options['currency_template'] : '{{currency_symbol}}{{amount}}';
	return $currency_template;
}

/**
 * Get currency JS format template.
 *
 * @return string Currency JS format template.
 */
function giftflow_get_currency_js_format_template() {
	$temp = giftflow_get_currency_template();
	$symbol = giftflow_get_global_currency_symbol();
	$template = str_replace( '{{currency_symbol}}', $symbol, $temp );
	$template = str_replace( '{{amount}}', '{{value}}', $template );
	return $template;
}

/**
 * Get preset donation amounts.
 *
 * @return array Preset donation amounts.
 */
function giftflow_get_preset_donation_amounts() {
	$options = get_option( 'giftflow_general_options' );
	$preset_donation_amounts = isset( $options['preset_donation_amounts'] ) ? $options['preset_donation_amounts'] : '10, 25, 35';
	return $preset_donation_amounts;
}

/**
 * Get preset donation amounts by campaign id
 *
 * @param int $campaign_id Campaign ID.
 * @return array
 */
function giftflow_get_preset_donation_amounts_by_campaign( $campaign_id ) {
	$preset_donation_amounts = get_post_meta( $campaign_id, '_preset_donation_amounts', true );

	// unserialize if exists.
	if ( is_serialized( $preset_donation_amounts ) ) {
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
		$preset_donation_amounts = unserialize( $preset_donation_amounts );
	}

	return array_map(
		function ( $item ) {
			return array(
				'amount' => (float) trim( $item['amount'] ),
			);
		},
		$preset_donation_amounts
	);
}

/**
 * Get campaign days left.
 *
 * @param int $campaign_id Campaign ID.
 * @return int Campaign days left.
 */
function giftflow_get_campaign_days_left( $campaign_id ) {
	$start_date = get_post_meta( $campaign_id, '_start_date', true );
	$end_date = get_post_meta( $campaign_id, '_end_date', true );

	if ( ! $start_date ) {
		return 0;
	}

	// current date.
	// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
	$current_date = current_time( 'timestamp' );
	$start_date = strtotime( $start_date );
	$end_date = strtotime( $end_date );

	// if start date is in the future, return false.
	if ( $start_date > $current_date ) {
		return false;
	}

	// if end date empty, return ''.
	if ( ! $end_date ) {
		return '';
	}

	// if end date is in the past, return true.
	if ( $end_date < $current_date ) {
		return true;
	}

	$days_left = ceil( ( $end_date - $current_date ) / 86400 );

	// apply filter.
	$days_left = apply_filters( 'giftflow_get_campaign_days_left', $days_left, $campaign_id );

	return $days_left;
}

/**
 * Get all donations for the campaign id.
 *
 * @param int $campaign_id Campaign ID.
 * @param array $args Arguments.
 * @param int $paged Page number.
 * @return array Donations.
 */
function giftflow_get_campaign_donations( $campaign_id, $args = array(), $paged = 1 ) {
	$args = wp_parse_args(
		$args,
		array(
			'posts_per_page' => apply_filters( 'giftflow_campaign_donations_per_page', 20 ),
			'paged' => $paged,
			'orderby' => 'date',
			'order' => 'DESC',
			'post_status' => 'publish',
			'post_type' => 'donation',
		)
	);

	// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
	$args['meta_key'] = '_campaign_id';
	// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
	$args['meta_value'] = $campaign_id;

	$donations = new WP_Query( $args );

	// return donation posts, total, and pagination.
	return array(
		'posts' => array_map(
			function ( $post ) {
				$is_anonymous = get_post_meta( $post->ID, '_anonymous_donation', true );
				$donor_meta = array();
				$donor_meta['id'] = get_post_meta( $post->ID, '_donor_id', true );
				$donor_meta['name'] = get_the_title( $donor_meta['id'] );
				$donor_meta['email'] = get_post_meta( $donor_meta['id'], '_email', true );
				$donor_meta['phone'] = get_post_meta( $donor_meta['id'], '_phone', true );
				$donor_meta['address'] = get_post_meta( $donor_meta['id'], '_address', true );
				$donor_meta['city'] = get_post_meta( $donor_meta['id'], '_city', true );
				$donor_meta['state'] = get_post_meta( $donor_meta['id'], '_state', true );
				$donor_meta['postal_code'] = get_post_meta( $donor_meta['id'], '_postal_code', true );
				$donor_meta['country'] = get_post_meta( $donor_meta['id'], '_country', true );

				if ( 'yes' === $is_anonymous ) {
					$donor_meta['name'] = esc_html__( 'Anonymous 🍀', 'giftflow' );
					$donor_meta['email'] = '';
					$donor_meta['phone'] = '';
					$donor_meta['address'] = '';
					$donor_meta['city'] = '';
					$donor_meta['state'] = '';
					$donor_meta['postal_code'] = '';
					$donor_meta['country'] = '';
				}

				return array(
					'id' => $post->ID,
					'amount' => get_post_meta( $post->ID, '_amount', true ),
					'amount_formatted' => giftflow_render_currency_formatted_amount( get_post_meta( $post->ID, '_amount', true ) ),
					'payment_method' => get_post_meta( $post->ID, '_payment_method', true ),
					'status' => get_post_meta( $post->ID, '_status', true ),
					'transaction_id' => get_post_meta( $post->ID, '_transaction_id', true ),
					'donor_id' => get_post_meta( $post->ID, '_donor_id', true ),
					'donor_meta' => $donor_meta,
					'campaign_id' => get_post_meta( $post->ID, '_campaign_id', true ),
					'message' => get_post_meta( $post->ID, '_donor_message', true ),
					// anonymous.
					'is_anonymous' => get_post_meta( $post->ID, '_anonymous_donation', true ),
					'date' => get_the_date( '', $post->ID ),
					'date_gmt' => get_gmt_from_date( get_the_date( 'Y-m-d H:i:s', $post->ID ) ),
				);
			},
			$donations->posts
		),
		'total' => $donations->found_posts,
		'pagination' => $donations->max_num_pages,
	);
}

/**
 * Donation form thank you template
 *
 * @param array $args Template arguments.
 * @return void
 */
function giftflow_donation_form_thank_you_section_html( $args = array() ) {
	// load template thank you.
	$template = new Template();
	$template->load_template( 'donation-form-thank-you.php', $args );
}

/**
 * Donation form error section
 *
 * @return void
 */
function giftflow_donation_form_error_section_html() {
	// load template error.
	$template = new Template();
	$template->load_template( 'donation-form-error.php' );
}

/**
 * Load template
 *
 * @param string $template_name Template name.
 * @param array $args Template arguments.
 * @return void
 */
function giftflow_load_template( $template_name, $args = array() ) {
	$template = new Template();
	$template->load_template( $template_name, $args );
}

/**
 * Get donation data by donation id
 *
 * @param int $donation_id Donation ID.
 * @return object Donation data.
 */
function giftflow_get_donation_data_by_id( $donation_id ) {
	$donation_data = get_post( $donation_id );

	if ( ! $donation_data ) {
		return false;
	}

	$donation_data->donation_edit_url = get_edit_post_link( $donation_id );
	$campaign_id = get_post_meta( $donation_id, '_campaign_id', true );
	$donation_data->campaign_name = $campaign_id ? get_the_title( $campaign_id ) : esc_html__( '???', 'giftflow' );
	$donation_data->campaign_url = $campaign_id ? get_the_permalink( $campaign_id ) : '#';

	$donor_id = get_post_meta( $donation_id, '_donor_id', true );
	// donor_name = first name + last name.
	$donation_data->donor_name = $donor_id ? get_post_meta( $donor_id, '_first_name', true ) . ' ' . get_post_meta( $donor_id, '_last_name', true ) : esc_html__( '???', 'giftflow' );
	$donation_data->donor_email = $donor_id ? get_post_meta( $donor_id, '_email', true ) : '';

	// message.
	$donation_data->message = get_post_meta( $donation_id, '_donor_message', true );
	// anonymous.
	$donation_data->anonymous = get_post_meta( $donation_id, '_anonymous_donation', true );

	$donation_data->amount = get_post_meta( $donation_id, '_amount', true );
	$donation_data->__amount_formatted = giftflow_render_currency_formatted_amount( $donation_data->amount );

	$donation_data->status = get_post_meta( $donation_id, '_status', true );
	$donation_data->payment_method = get_post_meta( $donation_id, '_payment_method', true );
	$donation_data->__date = get_the_date( '', $donation_id );
	$donation_data->__date_gmt = get_gmt_from_date( get_the_date( 'Y-m-d H:i:s', $donation_id ) );

	return $donation_data;
}

/**
 * Get donor account page
 *
 * @return string Donor account page.
 */
function giftflow_get_donor_account_page() {
	$options = get_option( 'giftflow_general_options' );
	$donor_account_page = isset( $options['donor_account_page'] ) ? $options['donor_account_page'] : '';

	// if empty please search by path 'donor-account'.
	if ( ! $donor_account_page ) {
		$donor_account_page = get_page_by_path( 'donor-account' );
		// Validate that $donor_account_page is a valid WP_Post object before accessing its ID.
		if ( $donor_account_page && is_a( $donor_account_page, 'WP_Post' ) ) {
			$donor_account_page = $donor_account_page->ID;
		} else {
			$donor_account_page = '';
		}
	}

	return $donor_account_page;
}

/**
 * Get thank donor page
 *
 * @return string Thank donor page.
 */
function giftflow_get_thank_donor_page() {
	$options = get_option( 'giftflow_general_options' );
	$thank_donor_page = isset( $options['thank_donor_page'] ) ? $options['thank_donor_page'] : '';

	// if empty please search by path 'thank-donor'.
	if ( ! $thank_donor_page ) {
		$thank_donor_page = get_page_by_path( 'thank-donor' );
		// Validate that $thank_donor_page is a valid WP_Post object before accessing its ID.
		if ( $thank_donor_page && is_a( $thank_donor_page, 'WP_Post' ) ) {
			$thank_donor_page = $thank_donor_page->ID;
		} else {
			$thank_donor_page = '';
		}
	}

	return $thank_donor_page;
}

/**
 * Auto create user on donation
 *
 * @param int $donation_id Donation ID.
 * @param mixed $payment_result Payment result.
 * @return void
 */
function giftflow_auto_create_user_on_donation( $donation_id, $payment_result ) {
	// get donor id.
	$donor_id = get_post_meta( $donation_id, '_donor_id', true );

	// get donor data.
	$donor_data = giftflow_get_donor_data_by_id( $donor_id );

	// check if user exists by email.
	$user = get_user_by( 'email', $donor_data->email );

	if ( $user ) {
		return;
	}

	// create new user, update first name, last name, and set role is subscriber.
	$password = wp_generate_password();
	$user_id = wp_create_user( $donor_data->email, $password, $donor_data->email );
	if ( is_wp_error( $user_id ) ) {
		return;
	}

	// update user data.
	wp_update_user(
		array(
			'ID' => $user_id,
			'first_name' => $donor_data->first_name,
			'last_name' => $donor_data->last_name,
		)
	);

	// assign donor role.
	giftflow_assign_donor_role( $user_id );

	// add hook after create new user.
	do_action( 'giftflow_new_user_on_first_time_donation', $user_id, $payment_result );

	// get donor account url.
	$donor_account_url = get_permalink( giftflow_get_donor_account_page() );

	// load content mail template.
	ob_start();
	$new_user_email_data = array(
		'name' => $donor_data->first_name . ' ' . $donor_data->last_name,
		'username' => $donor_data->email,
		'password' => $password,
		'login_url' => $donor_account_url,
	);

	// filter the data passed to the new user email template.
	$new_user_email_data = apply_filters(
		'giftflow_new_user_email_data',
		$new_user_email_data,
		$donor_data,
		$user_id,
		$donor_id
	);

	giftflow_load_template( 'email/new-user.php', $new_user_email_data );
	$content = ob_get_clean();

	// send mail to new user.
	giftflow_send_mail_template(
		array(
			'to' => $donor_data->email,
			// translators: %s: Site name for new donor welcome email subject.
			'subject' => sprintf( esc_html__( 'Welcome to %s', 'giftflow' ), get_bloginfo( 'name' ) ),
			'header' => esc_html__( '🍀 Your donor account has been created.', 'giftflow' ),
			'content' => $content,
		)
	);
}

/**
 * Get donor data by id.
 *
 * @param int $donor_id Donor ID.
 * @return object|null
 */
function giftflow_get_donor_data_by_id( $donor_id = 0 ) {
	$donor_data = get_post( $donor_id );

	if ( ! $donor_data || is_wp_error( $donor_data ) ) {
		return null;
	}

	// get meta fields.
	$donor_data->email = get_post_meta( $donor_id, '_email', true );
	$donor_data->first_name = get_post_meta( $donor_id, '_first_name', true );
	$donor_data->last_name = get_post_meta( $donor_id, '_last_name', true );
	$donor_data->phone = get_post_meta( $donor_id, '_phone', true );
	$donor_data->address = get_post_meta( $donor_id, '_address', true );
	$donor_data->city = get_post_meta( $donor_id, '_city', true );
	$donor_data->state = get_post_meta( $donor_id, '_state', true );
	$donor_data->postal_code = get_post_meta( $donor_id, '_postal_code', true );
	$donor_data->country = get_post_meta( $donor_id, '_country', true );

	return $donor_data;
}

/**
 * Query donations by donor id, use wp_query
 *
 * @param string $donor_id Donor ID.
 * @param int $page Page number.
 * @param int $per_page Per page.
 * @return WP_Query Donations.
 */
function giftflow_query_donation_by_donor_id( $donor_id, $page = 1, $per_page = 20 ) {
	$donations = new WP_Query(
		array(
			'post_type' => 'donation',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_key' => '_donor_id',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'meta_value' => $donor_id,
			'posts_per_page' => $per_page,
			'paged' => $page,
			'orderby' => 'date',
			'order' => 'DESC',
			'post_status' => 'publish',
		)
	);

	return $donations;
}

/**
 * Get donor id by email.
 *
 * @param string $email Email.
 * @return int Donor ID.
 */
function giftflow_get_donor_id_by_email( $email ) {
	$donor = get_posts(
		array(
			'post_type' => 'donor',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_key' => '_email',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'meta_value' => $email,
			'posts_per_page' => 1,
		)
	);

	if ( $donor ) {
		return $donor[0]->ID;
	}

	return 0;
}

/**
 * Get donations by user id.
 *
 * @param int $user_id User ID.
 * @param int $page Page number.
 * @param int $per_page Per page.
 * @return WP_Query Donations.
 */
function giftflow_get_donations_by_user( $user_id, $page = 1, $per_page = 20 ) {
	$user_email = get_user_by( 'id', $user_id )->user_email;
	$donor_id = giftflow_get_donor_id_by_email( $user_email );
	$donations = giftflow_query_donation_by_donor_id( $donor_id, $page, $per_page );
	return $donations;
}

/**
 * Process bar of campaign donations.
 *
 * @param int $campaign_id Campaign ID.
 * @return void
 */
function giftflow_process_bar_of_campaign_donations( $campaign_id ) {
	$progress_percentage = giftflow_get_campaign_progress_percentage( $campaign_id );

	?>
	<div class="giftflow-campaign-progress-bar" title="<?php echo esc_attr( $progress_percentage ); ?>%" style="max-wdith: 100%; margin: 0 0 .5em;"> 
		<div class="progress-bar" style="height: 3px; background-color: #f1f5f9; overflow: hidden; width: 100%; border-radius: 1px;">
			<div class="progress" style="width: <?php echo esc_attr( $progress_percentage ); ?>%; height: 100%; background: linear-gradient(90deg, #22c55e, #4ade80);"></div>
		</div>
	</div>
	<?php
}

/**
 * Get donor user information.
 *
 * @param int $user_id User ID.
 * @return array Donor user information.
 */
function giftflow_get_donor_user_information( $user_id ) {
	// get user data.
	$user_data = get_user_by( 'id', $user_id );

	// giftflow_get_donor_id_by_email.
	$donor_id = giftflow_get_donor_id_by_email( $user_data->user_email );

	// get donor data.
	$donor_data = giftflow_get_donor_data_by_id( $donor_id );

	$donor_information = array(
		// wp user.
		'user_id' => $user_data->ID,
		'first_name' => $user_data->first_name,
		'last_name' => $user_data->last_name,
		'email' => $user_data->user_email,

		// donor.
		'donor_id' => $donor_id,
		'phone' => $donor_data->phone,
		'address' => $donor_data->address,
		'city' => $donor_data->city,
		'state' => $donor_data->state,
		'postal_code' => $donor_data->postal_code,
		'country' => $donor_data->country,
	);

	return $donor_information;
}

/**
 * Get payment methods options
 *
 * @return array
 */
function giftflow_get_payment_methods_options() {
	/**
	 * Get registered gateways.
	 */
	$gateways = \GiftFlow\Gateways\Gateway_Base::get_registered_gateways();
	$options = array();

	foreach ( $gateways as $gateway ) {
		$options[ $gateway->get_id() ] = $gateway->get_title();
	}

	/**
	 * Allow developers to customize the payment methods options.
	 */
	return apply_filters( 'giftflow_payment_methods_options', $options );
}

/**
 * Add recaptcha field to donation form
 */
function giftflow_donation_form_add_recaptcha_field() {
	?>
	<input type="hidden" name="recaptcha_token" id="recaptcha_token" />
	<?php
}

/**
 * Enqueue custom scripts for donation form
 *
 * @return void
 */
function giftflow_donation_form_enqueue_custom_scripts() {
	$api_options = get_option( 'giftflow_options_with_api_keys_options' );

	// google recaptcha.
	$google_recaptcha_enabled = isset( $api_options['google_recaptcha']['google_recaptcha_enabled'] ) ? $api_options['google_recaptcha']['google_recaptcha_enabled'] : '';

	if ( '1' === $google_recaptcha_enabled ) {
		$google_recaptcha_site_key = isset( $api_options['google_recaptcha']['google_recaptcha_site_key'] ) ? $api_options['google_recaptcha']['google_recaptcha_site_key'] : '';

		if ( empty( $google_recaptcha_site_key ) ) {
			return;
		}

		// enqueue google recaptcha script.
		wp_enqueue_script( 'giftflow-google-recaptcha', 'https://www.google.com/recaptcha/api.js?render=' . $google_recaptcha_site_key, array(), '1.0.0', true );

		// enqueue grecaptcha script.
		wp_enqueue_script( 'giftflow-grecaptcha', GIFTFLOW_PLUGIN_URL . 'assets/js/grecaptcha.bundle.js', array( 'giftflow-google-recaptcha', 'giftflow-donation-forms' ), GIFTFLOW_VERSION, true );

		// localize script.
		wp_localize_script(
			'giftflow-grecaptcha',
			'giftflowGoogleRecaptcha',
			array(
				'site_key' => $google_recaptcha_site_key,
			)
		);
	}
}

/**
 * Validate recaptcha
 *
 * @param array $fields Fields.
 * @return void
 */
function giftflow_donation_form_validate_recaptcha( $fields ) {
	// get recaptcha token.
	$api_options = get_option( 'giftflow_options_with_api_keys_options' );
	$google_recaptcha_enabled = isset( $api_options['google_recaptcha']['google_recaptcha_enabled'] ) ? $api_options['google_recaptcha']['google_recaptcha_enabled'] : '';

	if ( '1' !== $google_recaptcha_enabled ) {
		return;
	}

	$google_recaptcha_secret_key = isset( $api_options['google_recaptcha']['google_recaptcha_secret_key'] ) ? $api_options['google_recaptcha']['google_recaptcha_secret_key'] : '';

	if ( empty( $google_recaptcha_secret_key ) ) {
		return;
	}

	$recaptcha_token = isset( $fields['recaptcha_token'] ) ? $fields['recaptcha_token'] : '';

	if ( empty( $recaptcha_token ) ) {
		wp_send_json_error( array( 'message' => __( 'Internal error: reCAPTCHA Token is empty. Please try again!', 'giftflow' ) ) );
	}

	// verify recaptcha token.
	$response = wp_remote_post(
		'https://www.google.com/recaptcha/api/siteverify',
		array(
			'body' => array(
				'secret' => $google_recaptcha_secret_key,
				'response' => $recaptcha_token,
			),
		)
	);

	// decode response body.
	$result = json_decode( $response['body'], true );

	// if success and score is greater than 0.5, return true.
	if ( ! $result['success'] || $result['score'] < 0.5 ) {
		wp_send_json_error( array( 'message' => __( 'Internal error: reCAPTCHA verification failed. Please try again!', 'giftflow' ) ) );
	}
}

/**
 * Prepare campaign status bar data
 *
 * @param int $post_id Campaign post ID.
 * @return array Campaign status bar template data.
 */
function giftflow_prepare_campaign_status_bar_data( $post_id ) {
	$post_id = intval( $post_id );

	// Prepare template data.
	$template_data = array(
		'post_id' => $post_id,
	);

	// If post_id is valid, get campaign data.
	if ( ! empty( $post_id ) ) {
		$goal_amount = get_post_meta( $post_id, '_goal_amount', true );
		$raised_amount = giftflow_get_campaign_raised_amount( $post_id );
		$progress_percentage = giftflow_get_campaign_progress_percentage( $post_id );
		$days_left = giftflow_get_campaign_days_left( $post_id );

		// Get donation count.
		$donations = get_posts(
			array(
				'post_type' => 'donation',
				'posts_per_page' => -1,
				'fields' => 'ids',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'meta_query' => array(
					array(
						'key' => '_campaign_id',
						'value' => $post_id,
						'compare' => '=',
					),
					array(
						'key' => '_status',
						'value' => 'completed',
						'compare' => '=',
					),
				),
			)
		);
		$donation_count = count( $donations );

		$template_data['goal_amount'] = $goal_amount;
		$template_data['raised_amount'] = $raised_amount;
		$template_data['progress_percentage'] = $progress_percentage;
		$template_data['days_left'] = $days_left;
		$template_data['donation_count'] = $donation_count;
		$template_data['raised_amount_formatted'] = giftflow_render_currency_formatted_amount( $raised_amount );
		$template_data['goal_amount_formatted'] = giftflow_render_currency_formatted_amount( $goal_amount );
	}

	/**
	 * Filter campaign status bar template data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $template_data Template data array.
	 * @param int   $post_id Campaign post ID.
	 */
	return apply_filters( 'giftflow_campaign_status_bar_data', $template_data, $post_id );
}

/**
 * Sanitize array data recursively
 *
 * @param array $data Data to sanitize.
 * @return array Sanitized data.
 */
function giftflow_sanitize_array( array $data ) {
	$sanitized = array();

	foreach ( $data as $key => $value ) {
		// Sanitize key.
		$clean_key = sanitize_key( $key );

		if ( is_array( $value ) ) {
			$sanitized[ $clean_key ] = giftflow_sanitize_array( $value );
		} elseif ( is_bool( $value ) ) {
			$sanitized[ $clean_key ] = (bool) $value;
		} elseif ( is_int( $value ) ) {
			$sanitized[ $clean_key ] = absint( $value );
		} elseif ( is_float( $value ) ) {
			$sanitized[ $clean_key ] = (float) $value;
		} else {
			// Default: text field.
			$sanitized[ $clean_key ] = sanitize_text_field( $value );
		}
	}

	return $sanitized;
}