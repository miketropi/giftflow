<?php
/**
 * Campaign taxonomy archive inner layout (PHP + hooks; no block serialization).
 *
 * Variables: $term (WP_Term) from {@see giftflow_load_template()}.
 *
 * Actions (first parameter: WP_Term $term):
 * - giftflow_before_campaign_taxonomy_archive
 * - giftflow_before_campaign_taxonomy_main
 * - giftflow_campaign_taxonomy_archive_header  (title, description — default priorities 10, 20)
 * - giftflow_before_campaign_taxonomy_loop
 * - giftflow_campaign_taxonomy_loop            (grid)
 * - giftflow_after_campaign_taxonomy_loop
 * - giftflow_after_campaign_taxonomy_main
 * - giftflow_after_campaign_taxonomy_archive
 *
 * @package GiftFlow
 * @subpackage Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$term = isset( $term ) ? $term : get_queried_object();
if ( ! $term instanceof WP_Term || 'campaign-tax' !== $term->taxonomy ) {
	return;
}

/**
 * Fires at the very start of the archive inner template.
 *
 * @param WP_Term $term Current term.
 */
do_action( 'giftflow_before_campaign_taxonomy_archive', $term );

$outer_classes = apply_filters( 'giftflow_campaign_taxonomy_archive_outer_classes', 'giftflow campaign-tax-archive', $term );
?>
<div class="<?php echo esc_attr( is_string( $outer_classes ) ? $outer_classes : 'giftflow campaign-tax-archive' ); ?>" data-term-id="<?php echo esc_attr( (string) $term->term_id ); ?>">
	<?php
	do_action( 'giftflow_before_campaign_taxonomy_main', $term );

	$inner_classes = apply_filters( 'giftflow_campaign_taxonomy_archive_inner_classes', 'giftflow-campaign-tax-archive__inner', $term );
	?>
	<div class="<?php echo esc_attr( is_string( $inner_classes ) ? $inner_classes : 'giftflow-campaign-tax-archive__inner' ); ?>">
		<?php
		$header_classes = apply_filters( 'giftflow_campaign_taxonomy_archive_header_classes', 'giftflow-campaign-tax-archive__header', $term );
		?>
		<header class="<?php echo esc_attr( is_string( $header_classes ) ? $header_classes : 'giftflow-campaign-tax-archive__header' ); ?>">
			<?php do_action( 'giftflow_campaign_taxonomy_archive_header', $term ); ?>
		</header>

		<?php
		do_action( 'giftflow_before_campaign_taxonomy_loop', $term );

		$loop_classes = apply_filters( 'giftflow_campaign_taxonomy_archive_loop_wrap_classes', 'giftflow-campaign-tax-archive__loop-wrap', $term );
		?>
		<div class="<?php echo esc_attr( is_string( $loop_classes ) ? $loop_classes : 'giftflow-campaign-tax-archive__loop-wrap' ); ?>">
			<?php do_action( 'giftflow_campaign_taxonomy_loop', $term ); ?>
		</div>

		<?php do_action( 'giftflow_after_campaign_taxonomy_loop', $term ); ?>
	</div>
	<?php
	do_action( 'giftflow_after_campaign_taxonomy_main', $term );
	?>
</div>
<?php
/**
 * Fires after the archive outer wrapper closes.
 *
 * @param WP_Term $term Current term.
 */
do_action( 'giftflow_after_campaign_taxonomy_archive', $term );
