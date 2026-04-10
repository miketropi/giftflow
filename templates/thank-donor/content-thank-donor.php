<?php
/**
 * Thank donor page inner layout (PHP only — same UI as giftflow/thank-donor block).
 *
 * Variables: optional $page_id (int), $page (WP_Post).
 *
 * Filter: giftflow_thank_donor_classic_attributes — adjust heading, message, CTA, etc.
 *
 * Theme override: yourtheme/giftflow/thank-donor/content-thank-donor.php
 *
 * @package GiftFlow
 * @subpackage Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$page_id = isset( $page_id ) ? absint( $page_id ) : 0;
if ( $page_id <= 0 ) {
	$page_id = absint( giftflow_get_thank_donor_page() );
}

/**
 * Fires at the very start of the thank donor inner template.
 *
 * @param int $page_id Thank donor page ID.
 */
do_action( 'giftflow_thank_donor_inner_before', $page_id );

/**
 * Classic-only attribute overrides (camelCase, same as block JSON).
 *
 * @param array $attributes Partial attributes.
 * @param int   $page_id    Page ID.
 */
$attributes = apply_filters( 'giftflow_thank_donor_classic_attributes', array(), $page_id );

giftflow_render_thank_donor_content( is_array( $attributes ) ? $attributes : array() );

/**
 * Fires at the very end of the thank donor inner template.
 *
 * @param int $page_id Thank donor page ID.
 */
do_action( 'giftflow_thank_donor_inner_after', $page_id );
