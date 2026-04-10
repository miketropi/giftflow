<?php
/**
 * Campaigns grid block.
 *
 * @package GiftFlow
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register campaigns grid block.
 *
 * @return void
 */
function giftflow_campaigns_grid_block() {
	register_block_type(
		'giftflow/campaigns-grid',
		array(
			'api_version'     => 3,
			'render_callback' => 'giftflow_campaigns_grid_block_render',
			'attributes'      => array(
				'perPage'     => array(
					'type'    => 'number',
					'default' => 9,
				),
				'orderby'     => array(
					'type'    => 'string',
					'default' => 'date',
				),
				'order'       => array(
					'type'    => 'string',
					'default' => 'DESC',
				),
				'category'    => array(
					'type'    => 'string',
					'default' => '',
				),
				'search'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'customClass' => array(
					'type'    => 'string',
					'default' => '',
				),
				'inheritCampaignTaxonomy' => array(
					'type'    => 'boolean',
					'default' => true,
				),
			),
			'supports'        => array(
				'align' => array( 'wide', 'full' ),
				'html'  => false,
			),
		)
	);
}

add_action( 'init', 'giftflow_campaigns_grid_block' );

/**
 * Render campaigns grid block (same data path as [giftflow_campaign_grid]).
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content.
 * @param WP_Block $block      Block instance.
 * @return string
 */
function giftflow_campaigns_grid_block_render( $attributes, $content, $block ) {
	unset( $content );

	$per_page = isset( $attributes['perPage'] ) ? max( 1, (int) $attributes['perPage'] ) : 9;
	$orderby  = isset( $attributes['orderby'] ) ? sanitize_text_field( $attributes['orderby'] ) : 'date';
	$order    = isset( $attributes['order'] ) ? strtoupper( sanitize_text_field( $attributes['order'] ) ) : 'DESC';
	if ( ! in_array( $order, array( 'ASC', 'DESC' ), true ) ) {
		$order = 'DESC';
	}

	$inherit_tax = (bool) ( $attributes['inheritCampaignTaxonomy'] ?? true );
	$category    = isset( $attributes['category'] ) ? sanitize_text_field( (string) $attributes['category'] ) : '';
	$search      = isset( $attributes['search'] ) ? sanitize_text_field( $attributes['search'] ) : '';
	$custom_raw   = isset( $attributes['customClass'] ) ? $attributes['customClass'] : '';
	$custom_class = is_string( $custom_raw ) ? sanitize_html_class( $custom_raw ) : '';

	// On campaign-tax term archives, use the viewed term when no category is set in the block.
	if ( '' === $category && $inherit_tax && ! wp_is_serving_rest_request() && is_tax( 'campaign-tax' ) ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term && 'campaign-tax' === $term->taxonomy ) {
			$category = $term->slug;
		}
	}

	// Optional: resolve from block template context (e.g. FSE) when main query is not the archive.
	if ( '' === $category && $inherit_tax && $block instanceof WP_Block ) {
		$ctx_tax = isset( $block->context['taxonomy'] ) ? (string) $block->context['taxonomy'] : '';
		$ctx_id  = isset( $block->context['termId'] ) ? absint( $block->context['termId'] ) : 0;
		if ( 'campaign-tax' === $ctx_tax && $ctx_id > 0 ) {
			$t = get_term( $ctx_id, 'campaign-tax' );
			if ( $t instanceof WP_Term && ! is_wp_error( $t ) ) {
				$category = $t->slug;
			}
		}
	}

	/**
	 * Final campaign category for the grid (slug, term ID string, or empty = all).
	 *
	 * @param string   $category   Resolved value.
	 * @param array    $attributes Block attributes.
	 * @param WP_Block $block      Block instance.
	 */
	$category = apply_filters( 'giftflow_campaigns_grid_resolved_category', $category, $attributes, $block );

	$atts = array(
		'per_page'     => $per_page,
		'orderby'      => $orderby,
		'order'        => $order,
		'category'     => $category,
		'search'       => $search,
		'paged'        => 1,
		'post_type'    => 'campaign',
		'custom_class' => $custom_class,
	);

	if ( ! wp_is_serving_rest_request() ) {
		$paged = get_query_var( 'paged' );
		if ( ! empty( $paged ) ) {
			$atts['paged'] = (int) $paged;
		}
	}

	$query_args = array(
		'posts_per_page' => $atts['per_page'],
		'orderby'        => $atts['orderby'],
		'order'          => $atts['order'],
		'paged'          => (int) $atts['paged'],
	);

	if ( ! empty( $atts['category'] ) ) {
		$query_args['category'] = $atts['category'];
	}

	if ( ! empty( $atts['search'] ) ) {
		$query_args['search'] = $atts['search'];
	}

	/**
	 * Filter the campaign grid query arguments.
	 *
	 * @param array $query_args The query arguments for the campaign grid.
	 * @param array $atts       Block-derived attributes (shortcode-shaped).
	 */
	$query_args = apply_filters( 'giftflow_campaign_grid_query_args', $query_args, $atts );

	$campaigns_class   = new \GiftFlow\Core\Campaigns();
	$campaigns_result  = $campaigns_class->get_campaigns( $query_args );
	$atts['campaigns'] = $campaigns_result['campaigns'];
	$atts['total']     = $campaigns_result['total'];
	$atts['pages']     = $campaigns_result['pages'];
	$atts['current_page'] = $campaigns_result['current_page'];

	ob_start();
	giftflow_load_template( 'campaign-grid.php', apply_filters( 'giftflow_form_campaign_grid_atts', $atts ) );
	return ob_get_clean();
}
