<?php
/**
 * Campaigns listing page template hooks (WooCommerce-style archive pattern).
 *
 * Used for the dedicated “Campaigns” page ({@see giftflow_get_campaigns_page()}).
 * Layout: templates/campaign-page/content-campaigns-page.php
 * Override: yourtheme/giftflow/campaign-page/content-campaigns-page.php
 *
 * Actions (first parameter: int $page_id — campaigns page post ID):
 * - giftflow_before_campaigns_page / giftflow_after_campaigns_page
 * - giftflow_before_campaigns_page_main / giftflow_after_campaigns_page_main
 * - giftflow_campaigns_page_header (defaults: title 10, optional page intro 20)
 * - giftflow_before_campaigns_page_loop / giftflow_after_campaigns_page_loop
 * - giftflow_campaigns_page_loop
 * - giftflow_before_campaigns_page_grid / giftflow_after_campaigns_page_grid ($page_id, $atts)
 *
 * Legacy: giftflow_campaigns_page_before_content / giftflow_campaigns_page_after_content
 *
 * @package GiftFlow
 * @subpackage Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register default callbacks.
 */
function giftflow_register_default_campaigns_page_template_hooks(): void {
	add_action( 'giftflow_campaigns_page_header', 'giftflow_template_campaigns_page_title', 10 );
	add_action( 'giftflow_campaigns_page_header', 'giftflow_template_campaigns_page_intro', 20 );
	add_action( 'giftflow_campaigns_page_loop', 'giftflow_template_campaigns_page_loop', 10 );

	add_filter( 'giftflow_campaign_grid_pagination_args', 'giftflow_campaigns_page_pagination_args', 10, 3 );
}
add_action( 'init', 'giftflow_register_default_campaigns_page_template_hooks', 30 );

/**
 * Pagination base on the campaigns static page.
 *
 * @param array $args    {@see paginate_links()} args.
 * @param int   $current Current page.
 * @param int   $pages   Total pages.
 * @return array
 */
function giftflow_campaigns_page_pagination_args( $args, $current, $pages ) {
	unset( $current, $pages );
	if ( ! is_campaigns_page() ) {
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
 * Page title (post title of campaigns page).
 *
 * @param int $page_id Page ID.
 */
function giftflow_template_campaigns_page_title( int $page_id ): void {
	if ( ! apply_filters( 'giftflow_campaigns_page_show_title', true, $page_id ) ) {
		return;
	}
	$title = get_the_title( $page_id );
	$title = apply_filters( 'giftflow_campaigns_page_title_text', $title, $page_id );
	if ( ! is_string( $title ) || '' === $title ) {
		return;
	}
	$tag = apply_filters( 'giftflow_campaigns_page_title_tag', 'h1', $page_id );
	$tag = is_string( $tag ) && preg_match( '/^h[1-6]$/', $tag ) ? $tag : 'h1';
	$classes = apply_filters( 'giftflow_campaigns_page_title_classes', 'giftflow-campaigns-page__title', $page_id );
	printf(
		'<%1$s class="%2$s">%3$s</%1$s>',
		esc_attr( $tag ),
		esc_attr( is_string( $classes ) ? $classes : 'giftflow-campaigns-page__title' ),
		esc_html( $title )
	);
}

/**
 * Optional intro from page content (disabled by default to avoid duplicate block layout).
 *
 * @param int $page_id Page ID.
 */
function giftflow_template_campaigns_page_intro( int $page_id ): void {
	if ( ! apply_filters( 'giftflow_campaigns_page_show_intro', false, $page_id ) ) {
		return;
	}
	$post = get_post( $page_id );
	if ( ! $post instanceof WP_Post || '' === trim( (string) $post->post_content ) ) {
		return;
	}
	$content = apply_filters( 'the_content', $post->post_content );
	$classes = apply_filters( 'giftflow_campaigns_page_intro_classes', 'giftflow-campaigns-page__intro entry-content', $page_id );
	echo '<div class="' . esc_attr( is_string( $classes ) ? $classes : 'giftflow-campaigns-page__intro' ) . '">';
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- same as core page content.
	echo $content;
	echo '</div>';
}

/**
 * Query all published campaigns and render {@see templates/campaign-grid.php}.
 *
 * @param int $page_id Page ID.
 */
function giftflow_template_campaigns_page_loop( int $page_id ): void {
	$paged = (int) get_query_var( 'paged' );
	if ( $paged < 1 ) {
		$paged = (int) get_query_var( 'page' );
	}
	$paged = max( 1, $paged );

	$per_page = (int) apply_filters( 'giftflow_campaigns_page_per_page', 9, $page_id );
	if ( $per_page < 1 ) {
		$per_page = 9;
	}

	$query_args = array(
		'posts_per_page' => $per_page,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'paged'          => $paged,
		'category'       => '',
	);

	/**
	 * Filter query args for {@see Campaigns::get_campaigns()} on the campaigns page.
	 *
	 * @param array $query_args WP_Query-compatible args (leave category empty for all campaigns).
	 * @param int   $page_id    Campaigns page ID.
	 */
	$query_args = apply_filters( 'giftflow_campaigns_page_query_args', $query_args, $page_id );
	if ( ! is_array( $query_args ) ) {
		$query_args = array(
			'posts_per_page' => $per_page,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'paged'          => $paged,
			'category'       => '',
		);
	}

	$campaigns_api = new \GiftFlow\Core\Campaigns();
	$result        = $campaigns_api->get_campaigns( $query_args );

	$atts = array(
		'per_page'     => $per_page,
		'orderby'      => isset( $query_args['orderby'] ) ? (string) $query_args['orderby'] : 'date',
		'order'        => isset( $query_args['order'] ) ? (string) $query_args['order'] : 'DESC',
		'category'     => '',
		'paged'        => $paged,
		'campaigns'    => $result['campaigns'],
		'total'        => $result['total'],
		'pages'        => $result['pages'],
		'current_page' => $result['current_page'],
		'custom_class' => 'giftflow-campaigns-page__grid',
		'post_type'    => 'campaign',
	);

	/**
	 * Attributes passed to campaign-grid.php on the campaigns page.
	 *
	 * @param array $atts    Template args.
	 * @param int   $page_id Page ID.
	 */
	$atts = apply_filters( 'giftflow_campaigns_page_grid_atts', $atts, $page_id );

	do_action( 'giftflow_before_campaigns_page_grid', $page_id, $atts );
	giftflow_load_template( 'campaign-grid.php', apply_filters( 'giftflow_form_campaign_grid_atts', $atts ) );
	do_action( 'giftflow_after_campaigns_page_grid', $page_id, $atts );
}

/**
 * Layout CSS for campaigns page.
 */
function giftflow_enqueue_campaigns_page_layout_styles(): void {
	if ( ! is_campaigns_page() ) {
		return;
	}
	$rel = 'assets/css/campaigns-page-layout.css';
	$dir = GIFTFLOW_PLUGIN_DIR . $rel;
	if ( ! is_readable( $dir ) ) {
		return;
	}
	wp_enqueue_style(
		'giftflow-campaigns-page-layout',
		GIFTFLOW_PLUGIN_URL . $rel,
		array(),
		(string) filemtime( $dir )
	);
}
add_action( 'wp_enqueue_scripts', 'giftflow_enqueue_campaigns_page_layout_styles', 25 );
