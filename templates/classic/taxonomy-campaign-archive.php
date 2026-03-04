<?php
/**
 * Campaign taxonomy archive template
 *
 * @package GiftFlow
 * @subpackage Templates
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header( 'giftflow' );

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$current_term = get_queried_object();
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$current_term_id = $current_term->term_id;

// do_blocks the block content.
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$block_content = '
<!-- wp:group {"tagName":"main","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<main class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)">

<!-- wp:query {"queryId":22,"query":{"perPage":9,"pages":0,"offset":0,"postType":"campaign","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false,"parents":[],"format":[],"taxQuery":{"campaign-tax":[' . $current_term_id . ']}},"metadata":{"categories":["posts"],"patternName":"core/query-grid-posts","name":"Grid"}} -->
<div class="wp-block-query"><!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|40"}},"layout":{"type":"grid","columnCount":3,"minimumColumnWidth":null}} -->
<!-- wp:group {"style":{"spacing":{"padding":{"top":"20px","right":"20px","bottom":"20px","left":"20px"}},"border":{"color":"#e0e0e0","width":"1px","radius":"1px"}},"backgroundColor":"base","layout":{"type":"default"}} -->
<div class="wp-block-group has-border-color has-base-background-color has-background" style="border-color:#e0e0e0;border-width:1px;border-radius:1px;padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px"><!-- wp:post-featured-image {"isLink":true,"aspectRatio":"4/3"} /-->

<!-- wp:post-terms {"term":"campaign-tax","prefix":"in ","fontSize":"small"} /-->

<!-- wp:post-title {"level":4,"isLink":true,"style":{"typography":{"fontStyle":"normal","fontWeight":"600"}},"fontSize":"medium"} /-->

<!-- wp:post-excerpt {"excerptLength":15,"fontSize":"medium"} /-->

<!-- wp:giftflow/campaign-status-bar {"__editorPostId":5} /--></div>
<!-- /wp:group -->
<!-- /wp:post-template -->

<!-- wp:query-pagination {"paginationArrow":"arrow","layout":{"type":"flex","justifyContent":"center","orientation":"horizontal","flexWrap":"wrap"}} -->
<!-- wp:query-pagination-previous /-->

<!-- wp:query-pagination-numbers /-->

<!-- wp:query-pagination-next /-->
<!-- /wp:query-pagination --></div>
<!-- /wp:query --></main>
<!-- /wp:group -->';

do_action( 'giftflow_campaign_taxonomy_archive_before_content' );

// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
echo '<h1 class="m-0 text-center">' . esc_html__( 'Category', 'giftflow' ) . ': ' . esc_html( $current_term->name ) . '</h1>';

// print the block content.
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo do_blocks( apply_filters( 'giftflow_campaign_taxonomy_archive_block_content', $block_content ) );

do_action( 'giftflow_campaign_taxonomy_archive_after_content' );

get_footer( 'giftflow' );
