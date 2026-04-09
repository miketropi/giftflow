<?php
/**
 * Campaign single: inner markup only (no header/footer).
 *
 * Renders the hook-driven PHP template (WooCommerce-style), not serialized blocks.
 * Used by single-campaign.php and the classic theme the_content filter.
 *
 * @package GiftFlow
 * @subpackage Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$campaign_id = (int) get_the_ID();
if ( $campaign_id <= 0 ) {
	return;
}

/**
 * Fires before the single campaign layout (legacy + parity with block template).
 *
 * @param int $campaign_id Campaign post ID.
 */
do_action( 'giftflow_campaign_single_before_content', $campaign_id );

$template_rel = apply_filters(
	'giftflow_campaign_single_content_template',
	'campaign-single/content-single-campaign.php',
	$campaign_id
);
$template_rel = is_string( $template_rel ) && '' !== $template_rel
	? $template_rel
	: 'campaign-single/content-single-campaign.php';

giftflow_load_template(
	$template_rel,
	array(
		'campaign_id' => $campaign_id,
	)
);

/**
 * Fires after the single campaign layout.
 *
 * @param int $campaign_id Campaign post ID.
 */
do_action( 'giftflow_campaign_single_after_content', $campaign_id );
