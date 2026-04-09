<?php
/**
 * Campaigns listing page (full document — header/footer).
 *
 * Lists all published campaigns via hook-driven layout + {@see templates/campaign-grid.php}.
 * See includes/frontend/campaigns-page-template-hooks.php
 *
 * @package GiftFlow
 * @subpackage Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$page_id = giftflow_get_campaigns_page();
$page_id = is_scalar( $page_id ) ? absint( $page_id ) : 0;
if ( $page_id <= 0 ) {
	return;
}

get_header( 'giftflow' );

$page = get_post( $page_id );
if ( ! $page instanceof WP_Post ) {
	return;
}

/**
 * Legacy hook before inner layout.
 *
 * @param int $page_id Campaigns page ID.
 */
do_action( 'giftflow_campaigns_page_before_content', $page_id );

$template_rel = apply_filters(
	'giftflow_campaigns_page_content_template',
	'campaign-page/content-campaigns-page.php',
	$page_id
);
$template_rel = is_string( $template_rel ) && '' !== $template_rel
	? $template_rel
	: 'campaign-page/content-campaigns-page.php';

?>
<main id="primary" class="giftflow-campaigns-page-main" role="main">
	<?php
	giftflow_load_template(
		$template_rel,
		array(
			'page_id' => $page_id,
			'page'    => $page,
		)
	);
	?>
</main>
<?php

/**
 * Legacy hook after inner layout.
 *
 * @param int $page_id Campaigns page ID.
 */
do_action( 'giftflow_campaigns_page_after_content', $page_id );

get_footer( 'giftflow' );
