<?php
/**
 * Campaign status bar block template
 *
 * @package GiftFlow
 * @subpackage Templates
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Extract variables.
// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$post_id = isset( $post_id ) ? intval( $post_id ) : 0;
$goal_amount = isset( $goal_amount ) ? $goal_amount : '';
$raised_amount = isset( $raised_amount ) ? floatval( $raised_amount ) : 0;
$progress_percentage = isset( $progress_percentage ) ? floatval( $progress_percentage ) : 0;
$days_left = isset( $days_left ) ? $days_left : '';
$donation_count = isset( $donation_count ) ? intval( $donation_count ) : 0;
$raised_amount_formatted = isset( $raised_amount_formatted ) ? $raised_amount_formatted : giftflow_render_currency_formatted_amount( $raised_amount );
$goal_amount_formatted = isset( $goal_amount_formatted ) ? $goal_amount_formatted : giftflow_render_currency_formatted_amount( $goal_amount );

// If post_id is 0 or empty, show empty state.
if ( empty( $post_id ) ) {
	?>
	<div class="giftflow-campaign-status-bar">
		<div class="campaign-progress">
			<div class="progress-stats">
				<?php echo esc_html__( 'Campaign not found or no data available', 'giftflow' ); ?>
			</div>
			<div class="progress-bar" style="height: 0.5rem; background-color: #f1f5f9; overflow: hidden; width: 100%;">
				<div class="progress" style="width: 0%; height: 100%; background: linear-gradient(90deg, #0ea5e9, #38bdf8);"></div>
			</div>
			<div class="progress-meta">
				<div class="progress-meta-item">
					<span class="__icon">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users-icon lucide-users"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
					</span>
					<span class="__text">
						<?php esc_html_e( 'No donations yet', 'giftflow' ); ?>
					</span>
				</div>
				<div class="progress-meta-item">
					<span class="__icon">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock2-icon lucide-clock-2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 10"/></svg>
					</span>
					<span class="__text">
						<?php esc_html_e( 'Not available', 'giftflow' ); ?>
					</span>
				</div>
			</div>
		</div>
	</div>
	<?php
	return;
}
?>

<div class="giftflow-campaign-status-bar">
	<div class="campaign-progress">
		<div class="progress-stats">
			<!-- template example: $100 raised from $1000 total -->
			<?php
				// translators: 1: is the raised amount, 2: is the goal amount.
				echo wp_kses_post( sprintf( __( '%1$s raised from %2$s total', 'giftflow' ), $raised_amount_formatted, $goal_amount_formatted ) );
			?>
		</div>
		<div class="progress-bar" style="height: 0.5rem; background-color: #f1f5f9; overflow: hidden; width: 100%;">
			<div class="progress" style="width: <?php echo esc_attr( $progress_percentage ); ?>%; height: 100%; background: linear-gradient(90deg, #0ea5e9, #38bdf8);"></div>
		</div>
		<div class="progress-meta">
			<div class="progress-meta-item">
				<span class="__icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users-icon lucide-users"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
				</span>
				<span class="__text">
					<!-- if donation count is 0, show "No donations yet" else show donation count -->
					<?php if ( 0 === $donation_count ) : ?>
						<?php esc_html_e( 'No donations yet', 'giftflow' ); ?>
					<?php else : ?>
						<?php echo wp_kses_post( $donation_count ); ?> <?php echo wp_kses_post( _n( 'donation', 'donations', $donation_count, 'giftflow' ) ); ?>
					<?php endif; ?>
				</span>
			</div>
			<!-- days left -->
			<div class="progress-meta-item">
				<span class="__icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock2-icon lucide-clock-2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 10"/></svg>
				</span>
				<span class="__text">
					<!-- if days left is false, show "Not started", is true, show "Ended" else show days left -->
					<?php if ( false === $days_left ) : ?>
						<?php esc_html_e( 'Not started', 'giftflow' ); ?>
					<?php elseif ( true === $days_left ) : ?>
						<?php esc_html_e( 'Ended', 'giftflow' ); ?>
					<?php elseif ( '' === $days_left ) : ?>
						<?php
							// return message not limited time for campaign.
							esc_html_e( 'Not limited time', 'giftflow' );
						?>
					<?php else : ?>
						<?php echo wp_kses_post( $days_left ); ?> <?php echo wp_kses_post( _n( 'day left', 'days left', $days_left, 'giftflow' ) ); ?>
					<?php endif; ?>
				</span>
			</div>
		</div>
	</div>
</div>
