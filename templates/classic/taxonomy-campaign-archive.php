<?php
/**
 * Campaign taxonomy archive — full page (header/footer).
 *
 * Inner layout is hook-driven (WooCommerce-style); see
 * templates/campaign-archive/content-taxonomy-campaign-archive.php
 * and includes/frontend/campaign-taxonomy-archive-template-hooks.php
 *
 * @package GiftFlow
 * @subpackage Templates
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$term = get_queried_object();
if ( ! $term instanceof WP_Term || 'campaign-tax' !== $term->taxonomy ) {
	return;
}

get_header( 'giftflow' );

/**
 * Legacy hook before inner archive markup.
 *
 * @param WP_Term $term Current term.
 */
do_action( 'giftflow_campaign_taxonomy_archive_before_content', $term );

$template_rel = apply_filters(
	'giftflow_campaign_taxonomy_archive_content_template',
	'campaign-archive/content-taxonomy-campaign-archive.php',
	$term
);
$template_rel = is_string( $template_rel ) && '' !== $template_rel
	? $template_rel
	: 'campaign-archive/content-taxonomy-campaign-archive.php';

?>
<main class="giftflow-campaign-tax-archive-main" role="main">
	<?php
	giftflow_load_template(
		$template_rel,
		array(
			'term' => $term,
		)
	);
	?>
</main>
<?php

/**
 * Legacy hook after inner archive markup.
 *
 * @param WP_Term $term Current term.
 */
do_action( 'giftflow_campaign_taxonomy_archive_after_content', $term );

get_footer( 'giftflow' );
