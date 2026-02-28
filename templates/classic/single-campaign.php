<?php
/**
 * Campaign details template
 *
 * @package GiftFlow
 * @subpackage Templates
 * @since 1.0.0
 */

get_header( 'giftflow' );

// do_blocks the block content.
$block_content = '<!-- wp:group {"tagName":"main","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<main class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)"><!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide"><!-- wp:columns {"verticalAlignment":"center","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|40"}}}} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:giftflow/campaign-single-images /--></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:post-terms {"term":"campaign-tax","prefix":"in "} /-->

<!-- wp:post-title {"style":{"typography":{"fontStyle":"normal","fontWeight":"600"}},"fontSize":"large"} /-->

<!-- wp:giftflow/campaign-status-bar {"__editorPostId":22} /-->

<!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","layout":{"type":"constrained","justifyContent":"left"}} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:giftflow/donation-button {"backgroundColor":"#ff7a00","fullWidth":true} /--></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","layout":{"type":"constrained","justifyContent":"right"}} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:giftflow/share {"title":"Share Campaign:"} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide"><!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:giftflow/campaign-single-content /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group --></main>
<!-- /wp:group -->';

do_action( 'giftflow_campaign_single_before_content' );

// print the block content.
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo do_blocks( apply_filters( 'giftflow_campaign_single_block_content', $block_content ) );

do_action( 'giftflow_campaign_single_after_content' );

get_footer( 'giftflow' );
