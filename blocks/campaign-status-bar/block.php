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

	// comment: if $post_id is 0 or empty, return.
	if ( empty( $post_id ) ) {
		ob_start();
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
		return ob_get_clean();
	}

	// Get campaign data.
	$goal_amount = get_post_meta( $post_id, '_goal_amount', true );
	$raised_amount = giftflow_get_campaign_raised_amount( $post_id );
	$progress_percentage = giftflow_get_campaign_progress_percentage( $post_id );

	// days left.
	$days_left = giftflow_get_campaign_days_left( $post_id );

	// Get donation count.
	$donations = get_posts(
		array(
			'post_type' => 'donation',
			'posts_per_page' => -1,
			'fields' => 'ids',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query' => array(
				array(
					'key' => '_campaign_id',
					'value' => $post_id,
					'compare' => '=',
				),
				array(
					'key' => '_status',
					'value' => 'completed',
					'compare' => '=',
				),
			),
		)
	);
	$donation_count = count( $donations );
	$raised_amount_formatted = giftflow_render_currency_formatted_amount( $raised_amount );
	$goal_amount_formatted = giftflow_render_currency_formatted_amount( $goal_amount );
	ob_start();
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
	<?php
	return ob_get_clean();
}
