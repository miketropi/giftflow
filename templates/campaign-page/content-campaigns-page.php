<?php
/**
 * Campaigns listing page — inner layout (PHP + hooks).
 *
 * Variables from {@see giftflow_load_template()}: $page_id (int), optional $page (WP_Post).
 *
 * @package GiftFlow
 * @subpackage Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$page_id = isset( $page_id ) ? absint( $page_id ) : 0;
if ( $page_id <= 0 ) {
	$page_id = absint( giftflow_get_campaigns_page() );
}
if ( $page_id <= 0 ) {
	return;
}

// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
$__page = isset( $page ) && $page instanceof WP_Post ? $page : get_post( $page_id );
if ( ! $__page instanceof WP_Post || 'publish' !== $__page->post_status ) {
	return;
}

/**
 * Fires at the very start of the campaigns page inner template.
 *
 * @param int $page_id Campaigns page ID.
 */
do_action( 'giftflow_before_campaigns_page', $page_id );

$outer = apply_filters( 'giftflow_campaigns_page_outer_classes', 'giftflow campaigns-page', $page_id );
?>
<div class="<?php echo esc_attr( is_string( $outer ) ? $outer : 'giftflow campaigns-page' ); ?>" data-page-id="<?php echo esc_attr( (string) $page_id ); ?>">
	<?php
	do_action( 'giftflow_before_campaigns_page_main', $page_id );

	$inner = apply_filters( 'giftflow_campaigns_page_inner_classes', 'giftflow-campaigns-page__inner', $page_id );
	?>
	<div class="<?php echo esc_attr( is_string( $inner ) ? $inner : 'giftflow-campaigns-page__inner' ); ?>">
		<?php
		$header_class = apply_filters( 'giftflow_campaigns_page_header_classes', 'giftflow-campaigns-page__header', $page_id );
		?>
		<header class="<?php echo esc_attr( is_string( $header_class ) ? $header_class : 'giftflow-campaigns-page__header' ); ?>">
			<?php do_action( 'giftflow_campaigns_page_header', $page_id ); ?>
		</header>

		<?php
		do_action( 'giftflow_before_campaigns_page_loop', $page_id );

		$loop_wrap = apply_filters( 'giftflow_campaigns_page_loop_wrap_classes', 'giftflow-campaigns-page__loop-wrap', $page_id );
		?>
		<div class="<?php echo esc_attr( is_string( $loop_wrap ) ? $loop_wrap : 'giftflow-campaigns-page__loop-wrap' ); ?>">
			<?php do_action( 'giftflow_campaigns_page_loop', $page_id ); ?>
		</div>

		<?php do_action( 'giftflow_after_campaigns_page_loop', $page_id ); ?>
	</div>
	<?php
	do_action( 'giftflow_after_campaigns_page_main', $page_id );
	?>
</div>
<?php
/**
 * Fires at the very end of the campaigns page inner template.
 *
 * @param int $page_id Campaigns page ID.
 */
do_action( 'giftflow_after_campaigns_page', $page_id );
