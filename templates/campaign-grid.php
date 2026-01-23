<?php
/**
 * Campaign grid template
 *
 * @package GiftFlow
 * @subpackage Templates
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Extract variables from shortcode attributes.
$campaigns = isset( $campaigns ) ? $campaigns : array();
$total = isset( $total ) ? intval( $total ) : 0;
// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$pages = isset( $pages ) ? intval( $pages ) : 0;
$current_page = isset( $current_page ) ? intval( $current_page ) : 1;
$custom_class = isset( $custom_class ) ? sanitize_html_class( $custom_class ) : '';

// Custom class wrapper.
$wrapper_class = 'giftflow-campaign-grid';
if ( ! empty( $custom_class ) ) {
	$wrapper_class .= ' ' . esc_attr( $custom_class );
}
$wrapper_class = apply_filters( 'giftflow_campaign_grid_wrapper_class', $wrapper_class, $campaigns, $total, $pages, $current_page );

// If no campaigns found.
if ( empty( $campaigns ) ) {
	$empty_message = apply_filters( 'giftflow_campaign_grid_empty_message', __( 'No campaigns found.', 'giftflow' ) );
	?>
	<div class="<?php echo esc_attr( $wrapper_class ); ?>">
		<?php do_action( 'giftflow_campaign_grid_before_empty' ); ?>
		<div class="giftflow-campaign-grid__empty">
			<p class="giftflow-campaign-grid__empty-message">
				<?php echo esc_html( $empty_message ); ?>
			</p>
		</div>
		<?php do_action( 'giftflow_campaign_grid_after_empty' ); ?>
	</div>
	<?php
	return;
}
?>

<?php do_action( 'giftflow_campaign_grid_before', $campaigns, $total, $pages, $current_page ); ?>

<div class="<?php echo esc_attr( $wrapper_class ); ?>">
	<?php do_action( 'giftflow_campaign_grid_before_container', $campaigns, $total, $pages, $current_page ); ?>
	<div class="giftflow-campaign-grid__container">
		<?php foreach ( $campaigns as $campaign ) : ?>
			<?php
			$campaign_id = isset( $campaign['id'] ) ? intval( $campaign['id'] ) : 0;
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$title = isset( $campaign['title'] ) ? $campaign['title'] : '';
			$excerpt = isset( $campaign['excerpt'] ) ? $campaign['excerpt'] : '';
			$permalink = isset( $campaign['permalink'] ) ? $campaign['permalink'] : '';
			$featured_image_url = isset( $campaign['featured_image_url'] ) ? $campaign['featured_image_url'] : '';
			$goal_amount = isset( $campaign['goal_amount'] ) ? floatval( $campaign['goal_amount'] ) : 0;
			$raised_amount = isset( $campaign['raised_amount'] ) ? floatval( $campaign['raised_amount'] ) : 0;
			$progress_percentage = isset( $campaign['progress_percentage'] ) ? floatval( $campaign['progress_percentage'] ) : 0;
			$location = isset( $campaign['location'] ) ? $campaign['location'] : '';
			$categories = isset( $campaign['categories'] ) ? $campaign['categories'] : array();
			?>
			<?php do_action( 'giftflow_campaign_grid_before_item', $campaign, $campaign_id ); ?>
			<article class="giftflow-campaign-grid__item">
				<?php if ( ! empty( $featured_image_url ) ) : ?>
					<div class="giftflow-campaign-grid__image">
						<a href="<?php echo esc_url( $permalink ); ?>" class="giftflow-campaign-grid__image-link">
							<img 
								src="<?php echo esc_url( $featured_image_url ); ?>" 
								alt="<?php echo esc_attr( $title ); ?>"
								loading="lazy"
							/>
						</a>
					</div>
				<?php else : ?>
					<div class="giftflow-campaign-grid__image giftflow-campaign-grid__image--placeholder">
						<a href="<?php echo esc_url( $permalink ); ?>" class="giftflow-campaign-grid__image-link">
							<span class="giftflow-campaign-grid__placeholder-icon">
								<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M4 16L8.586 11.414C8.961 11.039 9.469 10.828 10 10.828C10.531 10.828 11.039 11.039 11.414 11.414L16 16M14 14L15.586 12.414C15.961 12.039 16.469 11.828 17 11.828C17.531 11.828 18.039 12.039 18.414 12.414L20 14M14 8H14.01M6 20H18C19.105 20 20 19.105 20 18V6C20 4.895 19.105 4 18 4H6C4.895 4 4 4.895 4 6V18C4 19.105 4.895 20 6 20Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</span>
						</a>
					</div>
				<?php endif; ?>

				<?php do_action( 'giftflow_campaign_grid_before_item_content', $campaign, $campaign_id ); ?>
				<div class="giftflow-campaign-grid__content">
					<?php if ( ! empty( $categories ) && is_array( $categories ) ) : ?>
						<div class="giftflow-campaign-grid__categories">
							<?php foreach ( array_slice( $categories, 0, 2 ) as $category ) : ?>
								<?php if ( isset( $category->name ) ) : ?>
									<span class="giftflow-campaign-grid__category">
										<?php echo esc_html( $category->name ); ?>
									</span>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<h3 class="giftflow-campaign-grid__title">
						<a href="<?php echo esc_url( $permalink ); ?>">
							<?php echo esc_html( $title ); ?>
						</a>
					</h3>

					<?php if ( ! empty( $excerpt ) ) : ?>
						<div class="giftflow-campaign-grid__excerpt">
							<?php
							$excerpt_length = apply_filters( 'giftflow_campaign_grid_excerpt_length', 20, $campaign_id );
							echo wp_kses_post( wp_trim_words( $excerpt, $excerpt_length, '...' ) );
							?>
						</div>
					<?php endif; ?>

					<?php if ( $goal_amount > 0 ) : ?>
						<div class="giftflow-campaign-grid__progress">
							<?php echo do_shortcode( '[giftflow_campaign_status_bar campaign_id="' . $campaign_id . '"]' ); ?>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $location ) ) : ?>
						<div class="giftflow-campaign-grid__location">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M21 10C21 17 12 23 12 23C12 23 3 17 3 10C3 7.61305 3.94821 5.32387 5.63604 3.63604C7.32387 1.94821 9.61305 1 12 1C14.3869 1 16.6761 1.94821 18.364 3.63604C20.0518 5.32387 21 7.61305 21 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M12 13C13.6569 13 15 11.6569 15 10C15 8.34315 13.6569 7 12 7C10.3431 7 9 8.34315 9 10C9 11.6569 10.3431 13 12 13Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
							<span><?php echo esc_html( $location ); ?></span>
						</div>
					<?php endif; ?>

					<div class="giftflow-campaign-grid__footer">
						<a href="<?php echo esc_url( $permalink ); ?>" class="giftflow-campaign-grid__button">
							<?php esc_html_e( 'View Campaign', 'giftflow' ); ?>
						</a>
					</div>
				</div>
				<?php do_action( 'giftflow_campaign_grid_after_item_content', $campaign, $campaign_id ); ?>
			</article>
			<?php do_action( 'giftflow_campaign_grid_after_item', $campaign, $campaign_id ); ?>
		<?php endforeach; ?>
	</div>
	<?php do_action( 'giftflow_campaign_grid_after_container', $campaigns, $total, $pages, $current_page ); ?>

	<?php if ( $pages > 1 ) : ?>
		<?php do_action( 'giftflow_campaign_grid_before_pagination', $current_page, $pages ); ?>
		<?php
		$pagination_args = apply_filters(
			'giftflow_campaign_grid_pagination_args',
			array(
				'base'      => add_query_arg( 'paged', '%#%' ),
				'format'    => '',
				'current'   => $current_page,
				'total'     => $pages,
				'prev_text' => __( '&laquo; Previous', 'giftflow' ),
				'next_text' => __( 'Next &raquo;', 'giftflow' ),
				'type'      => 'list',
			),
			$current_page,
			$pages
		);
		?>
		<nav class="giftflow-campaign-grid__pagination" aria-label="<?php esc_attr_e( 'Campaign pagination', 'giftflow' ); ?>">
			<?php echo wp_kses_post( paginate_links( $pagination_args ) ); ?>
		</nav>
		<?php do_action( 'giftflow_campaign_grid_after_pagination', $current_page, $pages ); ?>
	<?php endif; ?>
</div>

<?php do_action( 'giftflow_campaign_grid_after', $campaigns, $total, $pages, $current_page ); ?>
