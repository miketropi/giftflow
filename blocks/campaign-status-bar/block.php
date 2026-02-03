<?php
/**
 * Campaign status bar block.
 *
 * @package GiftFlow
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register campaign status bar block.
 *
 * @return void
 */
function giftflow_campaign_status_bar_block() {
	register_block_type(
		'giftflow/campaign-status-bar',
		array(
			'api_version' => 3,
			'render_callback' => 'giftflow_campaign_status_bar_block_render',
			'attributes' => array(
				'__editorPostId' => array(
					'type' => 'number',
					'default' => 0,
				),
			),
		)
	);
}

/**
 * Add action to register campaign status bar block.
 */
add_action( 'init', 'giftflow_campaign_status_bar_block' );

/**
 * Render campaign status bar block.
 *
 * @param array $attributes Block attributes.
 * @param string $content Block content.
 * @param WP_Block $block Block object.
 * @return string Block output.
 */
function giftflow_campaign_status_bar_block_render( $attributes, $content, $block ) {
	unset( $content );
	unset( $block );
	$post_id = get_the_ID();

	// Check if it is a WP json api request.
	if ( wp_is_serving_rest_request() ) {
		// We can assume it is a server side render callback from Gutenberg.
		if ( isset( $attributes['__editorPostId'] ) ) {
			// Value from JS can be a float, we need integer.
			$attributes['__editorPostId'] = (int) $attributes['__editorPostId'];
		}
		$post_id = $attributes['__editorPostId'] ?? $post_id;
	}

	// Prepare template data using helper function.
	$template_data = giftflow_prepare_campaign_status_bar_data( $post_id );

	ob_start();
	giftflow_load_template( 'block/campaign-status-bar.php', $template_data );
	return ob_get_clean();
}
