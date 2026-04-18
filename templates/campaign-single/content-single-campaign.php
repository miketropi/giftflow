<?php
/**
 * Single campaign layout (PHP + hooks; no block serialization).
 *
 * Override in theme: yourtheme/giftflow/campaign-single/content-single-campaign.php
 *
 * Hooks (argument: int $campaign_id):
 * - giftflow_before_single_campaign
 * - giftflow_before_single_campaign_grid
 * - giftflow_before_single_campaign_images
 * - giftflow_single_campaign_images
 * - giftflow_after_single_campaign_images
 * - giftflow_before_single_campaign_summary
 * - giftflow_single_campaign_summary  (nested: terms, title, status, CTA row…)
 * - giftflow_after_single_campaign_summary
 * - giftflow_after_single_campaign_grid
 * - giftflow_single_campaign_tabs
 * - giftflow_after_single_campaign
 *
 * @package GiftFlow
 * @subpackage Templates
 * @var int $campaign_id Campaign post ID (passed via giftflow_load_template $args).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$campaign_id = isset( $campaign_id ) ? (int) $campaign_id : 0;
if ( $campaign_id <= 0 && is_singular( 'campaign' ) ) {
	$campaign_id = (int) get_the_ID();
}

$campaign_id = (int) apply_filters( 'giftflow_single_campaign_id', $campaign_id );
if ( $campaign_id <= 0 ) {
	return;
}

do_action( 'giftflow_before_single_campaign', $campaign_id );

$wrapper_classes = apply_filters( 'giftflow_single_campaign_inner_wrapper_classes', 'giftflow-single-campaign__inner', $campaign_id );
?>
<div class="<?php echo esc_attr( is_string( $wrapper_classes ) ? $wrapper_classes : 'giftflow-single-campaign__inner' ); ?>">
	<?php
	do_action( 'giftflow_before_single_campaign_grid', $campaign_id );

	$grid_classes = apply_filters( 'giftflow_single_campaign_grid_classes', 'giftflow-single-campaign__grid', $campaign_id );
	?>
	<div class="<?php echo esc_attr( is_string( $grid_classes ) ? $grid_classes : 'giftflow-single-campaign__grid' ); ?>">
		<?php
		do_action( 'giftflow_before_single_campaign_images', $campaign_id );

		$img_col = apply_filters( 'giftflow_single_campaign_images_column_classes', 'giftflow-single-campaign__images-col', $campaign_id );
		?>
		<div class="<?php echo esc_attr( is_string( $img_col ) ? $img_col : 'giftflow-single-campaign__images-col' ); ?>">
			<?php do_action( 'giftflow_single_campaign_images', $campaign_id ); ?>
		</div>
		<?php
		do_action( 'giftflow_after_single_campaign_images', $campaign_id );

		$sum_col = apply_filters( 'giftflow_single_campaign_summary_column_classes', 'giftflow-single-campaign__summary-col', $campaign_id );
		?>
		<div class="<?php echo esc_attr( is_string( $sum_col ) ? $sum_col : 'giftflow-single-campaign__summary-col' ); ?>">
			<?php
			do_action( 'giftflow_before_single_campaign_summary', $campaign_id );
			do_action( 'giftflow_single_campaign_summary', $campaign_id );
			do_action( 'giftflow_after_single_campaign_summary', $campaign_id );
			?>
		</div>
	</div>
	<?php
	do_action( 'giftflow_after_single_campaign_grid', $campaign_id );

	do_action( 'giftflow_single_campaign_tabs', $campaign_id );
	?>
</div>
<?php
do_action( 'giftflow_after_single_campaign', $campaign_id );
