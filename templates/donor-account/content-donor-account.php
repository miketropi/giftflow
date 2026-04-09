<?php
/**
 * Donor account inner layout (PHP only — same UI as giftflow/donor-account block).
 *
 * Variables: optional $page_id (int), $page (WP_Post).
 *
 * Theme override: yourtheme/giftflow/donor-account/content-donor-account.php
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
	$page_id = absint( giftflow_get_donor_account_page() );
}

/**
 * Before donor account UI (tabs or login).
 *
 * @param int $page_id Donor account page ID.
 */
do_action( 'giftflow_donor_account_inner_before', $page_id );

giftflow_render_donor_account_interface( $page_id, array() );

/**
 * After donor account UI.
 *
 * @param int $page_id Donor account page ID.
 */
do_action( 'giftflow_donor_account_inner_after', $page_id );
