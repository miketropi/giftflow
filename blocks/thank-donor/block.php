<?php
/**
 * Thank donor block.
 *
 * @package GiftFlow
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default URL for “view account” style CTAs (matches thank-donor page pattern).
 *
 * @return string
 */
function giftflow_thank_donor_block_default_button_url() {
	return add_query_arg( 'gf-direct-to', 'donor-account', home_url( '/' ) );
}

/**
 * Default attribute values (block editor + classic template).
 *
 * @return array
 */
function giftflow_thank_donor_default_attributes() {
	return array(
		'heading'           => __( '🎉 Thank You!', 'giftflow' ),
		'message'           => __( 'Your donation has been received. We appreciate your support!', 'giftflow' ),
		'accountNotice'     => __( 'We\'ve created an account for you using the email from your donation. Your login details have been sent to your inbox.', 'giftflow' ),
		'showAccountNotice' => true,
		'buttonText'        => __( 'View My Donations', 'giftflow' ),
		'buttonUrl'         => '',
		'showButton'        => true,
	);
}

/**
 * Block attribute schema (defaults match {@see giftflow_thank_donor_default_attributes()}).
 *
 * @return array
 */
function giftflow_thank_donor_block_attributes_schema() {
	$d = giftflow_thank_donor_default_attributes();

	return array(
		'heading'           => array(
			'type'    => 'string',
			'default' => $d['heading'],
		),
		'message'           => array(
			'type'    => 'string',
			'default' => $d['message'],
		),
		'accountNotice'     => array(
			'type'    => 'string',
			'default' => $d['accountNotice'],
		),
		'showAccountNotice' => array(
			'type'    => 'boolean',
			'default' => $d['showAccountNotice'],
		),
		'buttonText'        => array(
			'type'    => 'string',
			'default' => $d['buttonText'],
		),
		'buttonUrl'         => array(
			'type'    => 'string',
			'default' => $d['buttonUrl'],
		),
		'showButton'        => array(
			'type'    => 'boolean',
			'default' => $d['showButton'],
		),
	);
}

/**
 * Build sanitized template variables for {@see templates/block/thank-donor.php}.
 *
 * @param array $attributes Block-style keys (camelCase).
 * @return array
 */
function giftflow_thank_donor_build_template_data( $attributes = array() ) {
	$attributes = wp_parse_args( is_array( $attributes ) ? $attributes : array(), giftflow_thank_donor_default_attributes() );

	$heading = isset( $attributes['heading'] ) ? $attributes['heading'] : '';
	$message = isset( $attributes['message'] ) ? trim( (string) $attributes['message'] ) : '';
	if ( '' !== $message ) {
		$message = wp_kses_post( wpautop( $message ) );
	}

	$account_notice = isset( $attributes['accountNotice'] ) ? trim( (string) $attributes['accountNotice'] ) : '';
	if ( '' !== $account_notice ) {
		$account_notice = wp_kses_post( wpautop( $account_notice ) );
	}
	$show_notice = ! empty( $attributes['showAccountNotice'] );

	$button_text = isset( $attributes['buttonText'] ) ? $attributes['buttonText'] : '';
	$button_url  = isset( $attributes['buttonUrl'] ) ? trim( (string) $attributes['buttonUrl'] ) : '';
	$show_button = ! empty( $attributes['showButton'] );

	if ( $show_button && '' === $button_url ) {
		$button_url = giftflow_thank_donor_block_default_button_url();
	} else {
		$button_url = esc_url_raw( $button_url );
	}

	$heading_allowed = array(
		'span'   => array(
			'class' => true,
			'style' => true,
		),
		'em'     => array(),
		'strong' => array(),
	);
	$heading = is_string( $heading ) ? wp_kses( $heading, $heading_allowed ) : '';

	$data = array(
		'heading'             => $heading,
		'message'             => $message,
		'account_notice'      => $account_notice,
		'show_account_notice' => $show_notice,
		'button_text'         => $button_text,
		'button_url'          => $button_url,
		'show_button'         => $show_button,
	);

	/**
	 * Filter template variables for the Thank Donor block / classic page.
	 *
	 * @param array $data       Variables passed to the template.
	 * @param array $attributes Merged attributes (after defaults).
	 */
	return apply_filters( 'giftflow_thank_donor_block_data', $data, $attributes );
}

/**
 * Output thank donor UI (same markup as giftflow/thank-donor block).
 *
 * @param array $attributes Optional overrides (same keys as block attributes).
 * @return void
 */
function giftflow_render_thank_donor_content( $attributes = array() ) {
	$data = giftflow_thank_donor_build_template_data( $attributes );
	giftflow_load_template( 'block/thank-donor.php', $data );
}

/**
 * Register thank donor block.
 *
 * @return void
 */
function giftflow_thank_donor_block() {
	register_block_type(
		'giftflow/thank-donor',
		array(
			'api_version'     => 3,
			'render_callback' => 'giftflow_thank_donor_block_render',
			'attributes'      => giftflow_thank_donor_block_attributes_schema(),
			'supports'        => array(
				'align' => array( 'wide', 'full' ),
				'html'  => false,
			),
		)
	);
}

add_action( 'init', 'giftflow_thank_donor_block' );

/**
 * Render thank donor block.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content.
 * @param WP_Block $block      Block instance.
 * @return string
 */
function giftflow_thank_donor_block_render( $attributes, $content, $block ) {
	unset( $content, $block );

	ob_start();
	giftflow_render_thank_donor_content( is_array( $attributes ) ? $attributes : array() );
	return ob_get_clean();
}
