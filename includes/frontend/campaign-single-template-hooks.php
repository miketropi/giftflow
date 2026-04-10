<?php
/**
 * Single campaign template hooks (WooCommerce-style).
 *
 * Layout: templates/campaign-single/content-single-campaign.php
 * Override: yourtheme/giftflow/campaign-single/content-single-campaign.php
 *
 * Actions (first arg: int $campaign_id):
 * - giftflow_before_single_campaign
 * - giftflow_before_single_campaign_grid
 * - giftflow_before_single_campaign_images
 * - giftflow_single_campaign_images
 * - giftflow_after_single_campaign_images
 * - giftflow_before_single_campaign_summary
 * - giftflow_single_campaign_summary (priorities: 5 terms, 10 title, 20 status, 28–48 CTA row + donate + share)
 * - giftflow_after_single_campaign_summary
 * - giftflow_after_single_campaign_grid
 * - giftflow_single_campaign_tabs
 * - giftflow_after_single_campaign
 *
 * Legacy (single-campaign-inner.php): giftflow_campaign_single_before_content, giftflow_campaign_single_after_content
 *
 * @package GiftFlow
 * @subpackage Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register default template hook callbacks (priorities mirror common WC patterns).
 */
function giftflow_register_default_campaign_single_template_hooks(): void {
	if ( ! function_exists( 'giftflow_campaign_single_images_block_render' ) ) {
		return;
	}

	// Outer shell (wraps everything from this template).
	add_action( 'giftflow_before_single_campaign', 'giftflow_single_campaign_open_wrapper', 5 );
	add_action( 'giftflow_after_single_campaign', 'giftflow_single_campaign_close_wrapper', 99 );

	add_action( 'giftflow_single_campaign_images', 'giftflow_template_single_campaign_images', 10 );
	add_action( 'giftflow_single_campaign_summary', 'giftflow_template_single_campaign_terms', 5 );
	add_action( 'giftflow_single_campaign_summary', 'giftflow_template_single_campaign_title', 10 );
	add_action( 'giftflow_single_campaign_summary', 'giftflow_template_single_campaign_status_bar', 20 );
	add_action( 'giftflow_single_campaign_summary', 'giftflow_template_single_campaign_open_cta_row', 28 );
	add_action( 'giftflow_single_campaign_summary', 'giftflow_template_single_campaign_donation_button', 30 );
	add_action( 'giftflow_single_campaign_summary', 'giftflow_template_single_campaign_share', 40 );
	add_action( 'giftflow_single_campaign_summary', 'giftflow_template_single_campaign_close_cta_row', 48 );

	// Tabs: story / donations / comments (reuses block render; no serialized blocks in template).
	add_action( 'giftflow_single_campaign_tabs', 'giftflow_template_single_campaign_tabs', 10 );
}
add_action( 'init', 'giftflow_register_default_campaign_single_template_hooks', 30 );

/**
 * Locate a campaign-single PHP template (child/theme override support).
 *
 * @param string $template Relative to templates/, e.g. campaign-single/content-single-campaign.php.
 * @return string Absolute path.
 */
function giftflow_locate_campaign_single_template( string $template ): string {
	$tpl     = new \GiftFlow\Frontend\Template();
	$path    = $tpl->get_template_path( $template );
	$path    = apply_filters( 'giftflow_locate_campaign_single_template', $path, $template );
	return is_string( $path ) ? $path : '';
}

/**
 * --- Wrapper callbacks ---
 *
 * @param int $campaign_id Campaign post ID.
 */
function giftflow_single_campaign_open_wrapper( int $campaign_id ): void {
	$classes = apply_filters( 'giftflow_single_campaign_wrapper_classes', 'giftflow single-campaign', $campaign_id );
	if ( ! is_string( $classes ) || '' === $classes ) {
		$classes = 'giftflow single-campaign';
	}
	echo '<div class="' . esc_attr( $classes ) . '" data-campaign-id="' . esc_attr( (string) $campaign_id ) . '">';
}

/**
 * Close the wrapper.
 *
 * @param int $campaign_id Campaign post ID.
 */
function giftflow_single_campaign_close_wrapper( int $campaign_id ): void {
	unset( $campaign_id );
	echo '</div>';
}

/**
 * Open the CTA row.
 *
 * @param int $campaign_id Campaign post ID.
 */
function giftflow_template_single_campaign_open_cta_row( int $campaign_id ): void {
	$classes = apply_filters( 'giftflow_single_campaign_cta_row_classes', 'giftflow-single-campaign__cta', $campaign_id );
	if ( ! is_string( $classes ) || '' === $classes ) {
		$classes = 'giftflow-single-campaign__cta';
	}
	echo '<div class="' . esc_attr( $classes ) . '">';
}

/**
 * Close the CTA row.
 *
 * @param int $campaign_id Campaign post ID.
 */
function giftflow_template_single_campaign_close_cta_row( int $campaign_id ): void {
	unset( $campaign_id );
	echo '</div>';
}

/**
 * Gallery / featured + lightbox markup.
 *
 * @param int $campaign_id Campaign post ID.
 */
function giftflow_template_single_campaign_images( int $campaign_id ): void {
	if ( ! apply_filters( 'giftflow_single_campaign_show_images', true, $campaign_id ) ) {
		return;
	}
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- block render returns HTML.
	echo giftflow_campaign_single_images_block_render( array(), '', null );
}

/**
 * Taxonomy terms (campaign-tax).
 *
 * @param int $campaign_id Campaign post ID.
 */
function giftflow_template_single_campaign_terms( int $campaign_id ): void {
	if ( ! apply_filters( 'giftflow_single_campaign_show_terms', true, $campaign_id ) ) {
		return;
	}
	$list = get_the_term_list( $campaign_id, 'campaign-tax', esc_html__( 'In Category: ', 'giftflow' ), esc_html__( ', ', 'giftflow' ), '. ' );
	if ( is_wp_error( $list ) || empty( $list ) ) {
		return;
	}
	$classes = apply_filters( 'giftflow_single_campaign_terms_classes', 'giftflow-single-campaign__terms', $campaign_id );
	echo '<div class="' . esc_attr( is_string( $classes ) ? $classes : 'giftflow-single-campaign__terms' ) . '">';
	$list = apply_filters( 'giftflow_single_campaign_terms_html', $list, $campaign_id );
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- term list HTML from core.
	echo wp_kses_post( $list );
	echo '</div>';
}

/**
 * Title.
 *
 * @param int $campaign_id Campaign post ID.
 */
function giftflow_template_single_campaign_title( int $campaign_id ): void {
	if ( ! apply_filters( 'giftflow_single_campaign_show_title', true, $campaign_id ) ) {
		return;
	}
	$tag = apply_filters( 'giftflow_single_campaign_title_tag', 'h1', $campaign_id );
	$tag = is_string( $tag ) && preg_match( '/^h[1-6]$/', $tag ) ? $tag : 'h1';
	$classes = apply_filters( 'giftflow_single_campaign_title_classes', 'giftflow-single-campaign__title entry-title', $campaign_id );
	$title   = get_the_title( $campaign_id );
	$title   = apply_filters( 'giftflow_single_campaign_title_text', $title, $campaign_id );
	printf(
		'<%1$s class="%2$s">%3$s</%1$s>',
		esc_attr( $tag ),
		esc_attr( is_string( $classes ) ? $classes : 'giftflow-single-campaign__title entry-title' ),
		esc_html( is_string( $title ) ? $title : '' )
	);
}

/**
 * Progress / goal status bar.
 *
 * @param int $campaign_id Campaign post ID.
 */
function giftflow_template_single_campaign_status_bar( int $campaign_id ): void {
	if ( ! apply_filters( 'giftflow_single_campaign_show_status_bar', true, $campaign_id ) ) {
		return;
	}
	$data = giftflow_prepare_campaign_status_bar_data( $campaign_id );
	$data = apply_filters( 'giftflow_single_campaign_status_bar_data', $data, $campaign_id );
	echo '<div class="giftflow-single-campaign__status">';
	giftflow_load_template( 'block/campaign-status-bar.php', $data );
	echo '</div>';
}

/**
 * Donate CTA.
 *
 * @param int $campaign_id Campaign post ID.
 */
function giftflow_template_single_campaign_donation_button( int $campaign_id ): void {
	if ( ! apply_filters( 'giftflow_single_campaign_show_donation_button', true, $campaign_id ) ) {
		return;
	}
	$attrs = array(
		'campaignId'      => $campaign_id,
		'fullWidth'       => true,
		'backgroundColor' => '#ff7a00',
		'textColor'       => '#ffffff',
		'buttonText'      => esc_html__( 'Donate Now', 'giftflow' ),
	);
	$attrs = apply_filters( 'giftflow_single_campaign_donation_button_attributes', $attrs, $campaign_id );
	echo '<div class="giftflow-single-campaign__donate">';
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo giftflow_donation_button_block_render( $attrs, '', null );
	echo '</div>';
}

/**
 * Share row.
 *
 * @param int $campaign_id Campaign post ID.
 */
function giftflow_template_single_campaign_share( int $campaign_id ): void {
	if ( ! apply_filters( 'giftflow_single_campaign_show_share', true, $campaign_id ) ) {
		return;
	}
	$attrs = array(
		'title'         => esc_html__( 'Share Campaign:', 'giftflow' ),
		'showSocials'   => true,
		'showEmail'     => true,
		'showCopyUrl'   => true,
		'customUrl'     => get_permalink( $campaign_id ),
	);
	$attrs = apply_filters( 'giftflow_single_campaign_share_attributes', $attrs, $campaign_id );
	echo '<div class="giftflow-single-campaign__share">';
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo giftflow_share_block_render( $attrs, '', null );
	echo '</div>';
}

/**
 * Tabbed content (campaign copy, donations, comments).
 *
 * @param int $campaign_id Campaign post ID.
 */
function giftflow_template_single_campaign_tabs( int $campaign_id ): void {
	if ( ! apply_filters( 'giftflow_single_campaign_show_tabs', true, $campaign_id ) ) {
		return;
	}
	$classes = apply_filters( 'giftflow_single_campaign_tabs_classes', 'giftflow-single-campaign__tabs', $campaign_id );
	echo '<div class="' . esc_attr( is_string( $classes ) ? $classes : 'giftflow-single-campaign__tabs' ) . '">';
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo giftflow_campaign_single_content_block_render( array(), '', null );
	echo '</div>';
}
