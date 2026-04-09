<?php
/**
 * Campaign taxonomy archive template hooks (WooCommerce-style shop archive pattern).
 *
 * Layout: templates/campaign-archive/content-taxonomy-campaign-archive.php
 * Override: yourtheme/giftflow/campaign-archive/content-taxonomy-campaign-archive.php
 *
 * Actions (first parameter is WP_Term $term unless noted):
 * - giftflow_before_campaign_taxonomy_archive / giftflow_after_campaign_taxonomy_archive
 * - giftflow_before_campaign_taxonomy_main / giftflow_after_campaign_taxonomy_main
 * - giftflow_campaign_taxonomy_archive_header (defaults: title 10, description 20)
 * - giftflow_before_campaign_taxonomy_loop / giftflow_after_campaign_taxonomy_loop
 * - giftflow_campaign_taxonomy_loop (grid)
 * - giftflow_before_campaign_taxonomy_archive_grid / giftflow_after_campaign_taxonomy_archive_grid ($term, $atts)
 *
 * Legacy (taxonomy-campaign-archive.php): giftflow_campaign_taxonomy_archive_before_content / _after_content
 *
 * @package GiftFlow
 * @subpackage Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register default archive callbacks.
 */
function giftflow_register_default_campaign_taxonomy_archive_hooks(): void {
	add_action( 'giftflow_campaign_taxonomy_archive_header', 'giftflow_template_campaign_taxonomy_archive_title', 10 );
	add_action( 'giftflow_campaign_taxonomy_archive_header', 'giftflow_template_campaign_taxonomy_archive_description', 20 );
	add_action( 'giftflow_campaign_taxonomy_loop', 'giftflow_template_campaign_taxonomy_archive_loop', 10 );

	add_filter( 'giftflow_campaign_grid_pagination_args', 'giftflow_campaign_taxonomy_archive_pagination_args', 10, 3 );
}
add_action( 'init', 'giftflow_register_default_campaign_taxonomy_archive_hooks', 30 );

/**
 * Fix pagination base on campaign taxonomy archives (campaign-grid default is generic).
 *
 * @param array $args     {@see paginate_links()} args.
 * @param int   $current  Current page.
 * @param int   $pages    Total pages.
 * @return array
 */
function giftflow_campaign_taxonomy_archive_pagination_args( $args, $current, $pages ) {
	unset( $current, $pages );
	if ( ! is_tax( 'campaign-tax' ) ) {
		return $args;
	}
	if ( ! is_array( $args ) ) {
		$args = array();
	}
	$big  = 999999999;
	$link = get_pagenum_link( $big, false );
	if ( ! is_string( $link ) || '' === $link ) {
		return $args;
	}
	$args['base']   = str_replace( (string) $big, '%#%', esc_url( $link ) );
	$args['format'] = '';
	return $args;
}

/**
 * Archive title.
 *
 * @param WP_Term $term Term.
 */
function giftflow_template_campaign_taxonomy_archive_title( WP_Term $term ): void {
	if ( ! apply_filters( 'giftflow_campaign_taxonomy_archive_show_title', true, $term ) ) {
		return;
	}
	$tag = apply_filters( 'giftflow_campaign_taxonomy_archive_title_tag', 'h1', $term );
	$tag = is_string( $tag ) && preg_match( '/^h[1-6]$/', $tag ) ? $tag : 'h1';
	$classes = apply_filters(
		'giftflow_campaign_taxonomy_archive_title_classes',
		'giftflow-campaign-tax-archive__title',
		$term
	);
	$label = apply_filters(
		'giftflow_campaign_taxonomy_archive_title_prefix',
		esc_html__( 'Category', 'giftflow' ),
		$term
	);
	$name = apply_filters( 'giftflow_campaign_taxonomy_archive_title_text', $term->name, $term );
	$prefix_html = '' !== $label
		? '<span class="giftflow-campaign-tax-archive__title-prefix">' . esc_html( $label ) . ': </span>'
		: '';
	printf(
		'<%1$s class="%2$s">%3$s%4$s</%1$s>',
		esc_attr( $tag ),
		esc_attr( is_string( $classes ) ? $classes : 'giftflow-campaign-tax-archive__title' ),
		$prefix_html,
		esc_html( is_string( $name ) ? $name : '' )
	);
}

/**
 * Term description (if any).
 *
 * @param WP_Term $term Term.
 */
function giftflow_template_campaign_taxonomy_archive_description( WP_Term $term ): void {
	if ( ! apply_filters( 'giftflow_campaign_taxonomy_archive_show_description', true, $term ) ) {
		return;
	}
	$desc = term_description( $term->term_id, $term->taxonomy );
	if ( ! is_string( $desc ) || '' === trim( $desc ) ) {
		return;
	}
	$classes = apply_filters(
		'giftflow_campaign_taxonomy_archive_description_classes',
		'giftflow-campaign-tax-archive__description term-description',
		$term
	);
	echo '<div class="' . esc_attr( is_string( $classes ) ? $classes : 'giftflow-campaign-tax-archive__description' ) . '">';
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core term description HTML.
	echo apply_filters( 'giftflow_campaign_taxonomy_archive_description_html', $desc, $term );
	echo '</div>';
}

/**
 * Query campaigns for this term and render {@see templates/campaign-grid.php}.
 *
 * @param WP_Term $term Term.
 */
function giftflow_template_campaign_taxonomy_archive_loop( WP_Term $term ): void {
	$paged = (int) get_query_var( 'paged' );
	if ( $paged < 1 ) {
		$paged = (int) get_query_var( 'page' );
	}
	$paged = max( 1, $paged );

	$per_page = (int) apply_filters( 'giftflow_campaign_taxonomy_archive_per_page', 9, $term );
	if ( $per_page < 1 ) {
		$per_page = 9;
	}

	$query_args = array(
		'posts_per_page' => $per_page,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'paged'          => $paged,
		'category'       => (int) $term->term_id,
	);

	/**
	 * Filter query args passed to {@see Campaigns::get_campaigns()} on taxonomy archive.
	 *
	 * @param array   $query_args WP_Query-compatible args (incl. category => term ID).
	 * @param WP_Term $term       Current term.
	 */
	$query_args = apply_filters( 'giftflow_campaign_taxonomy_archive_query_args', $query_args, $term );
	if ( ! is_array( $query_args ) ) {
		$query_args = array(
			'posts_per_page' => $per_page,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'paged'          => $paged,
			'category'       => (int) $term->term_id,
		);
	}

	$campaigns_api = new \GiftFlow\Core\Campaigns();
	$result        = $campaigns_api->get_campaigns( $query_args );

	$atts = array(
		'per_page'      => $per_page,
		'orderby'       => isset( $query_args['orderby'] ) ? (string) $query_args['orderby'] : 'date',
		'order'         => isset( $query_args['order'] ) ? (string) $query_args['order'] : 'DESC',
		'category'      => (string) $term->term_id,
		'paged'         => $paged,
		'campaigns'     => $result['campaigns'],
		'total'         => $result['total'],
		'pages'         => $result['pages'],
		'current_page'  => $result['current_page'],
		'custom_class'  => 'giftflow-campaign-tax-archive__grid',
		'post_type'     => 'campaign',
	);

	/**
	 * Attributes passed to campaign-grid.php on taxonomy archive.
	 *
	 * @param array   $atts Template args.
	 * @param WP_Term $term Term.
	 */
	$atts = apply_filters( 'giftflow_campaign_taxonomy_archive_grid_atts', $atts, $term );

	do_action( 'giftflow_before_campaign_taxonomy_archive_grid', $term, $atts );
	giftflow_load_template( 'campaign-grid.php', apply_filters( 'giftflow_form_campaign_grid_atts', $atts ) );
	do_action( 'giftflow_after_campaign_taxonomy_archive_grid', $term, $atts );
}

/**
 * Enqueue layout helpers for taxonomy archive.
 */
function giftflow_enqueue_campaign_taxonomy_archive_layout_styles(): void {
	if ( ! is_tax( 'campaign-tax' ) ) {
		return;
	}
	$rel = 'assets/css/campaign-taxonomy-archive-layout.css';
	$dir = GIFTFLOW_PLUGIN_DIR . $rel;
	if ( ! is_readable( $dir ) ) {
		return;
	}
	wp_enqueue_style(
		'giftflow-campaign-taxonomy-archive-layout',
		GIFTFLOW_PLUGIN_URL . $rel,
		array(),
		(string) filemtime( $dir )
	);
}
// add_action( 'wp_enqueue_scripts', 'giftflow_enqueue_campaign_taxonomy_archive_layout_styles', 25 );

/**
 * Locate a campaign-archive PHP template.
 *
 * @param string $template Relative to templates/, e.g. campaign-archive/content-taxonomy-campaign-archive.php.
 * @return string
 */
function giftflow_locate_campaign_archive_template( string $template ): string {
	$tpl  = new \GiftFlow\Frontend\Template();
	$path = $tpl->get_template_path( $template );
	return apply_filters( 'giftflow_locate_campaign_archive_template', is_string( $path ) ? $path : '', $template );
}
