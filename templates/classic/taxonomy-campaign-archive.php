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

$__term = get_queried_object();
if ( ! $__term instanceof WP_Term || 'campaign-tax' !== $__term->taxonomy ) {
	return;
}

get_header( 'giftflow' );

/**
 * Legacy hook before inner archive markup.
 *
 * @param WP_Term $term Current term.
 */
do_action( 'giftflow_campaign_taxonomy_archive_before_content', $__term );

$template_rel = apply_filters(
	'giftflow_campaign_taxonomy_archive_content_template',
	'campaign-archive/content-taxonomy-campaign-archive.php',
	$__term
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
			'term' => $__term,
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
do_action( 'giftflow_campaign_taxonomy_archive_after_content', $__term );

get_footer( 'giftflow' );
