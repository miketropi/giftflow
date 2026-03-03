<?php
/**
 * Donor account template
 *
 * @package GiftFlow
 * @subpackage Templates
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header( 'giftflow' );

// do_blocks the block content.
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$block_content = '
<!-- wp:group {"tagName":"main","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<main class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)"><!-- wp:post-title /-->

<!-- wp:spacer {"height":"25px"} -->
<div style="height:25px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:giftflow/donor-account /--></main>
<!-- /wp:group -->';

do_action( 'giftflow_donor_account_before_content' );

// print the block content.
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo do_blocks( apply_filters( 'giftflow_donor_account_block_content', $block_content ) );

do_action( 'giftflow_donor_account_after_content' );

get_footer( 'giftflow' );
