<?php
/**
 * Campaign Single Images Block.
 *
 * @package GiftFlow
 */

/**
 * Register campaign single images block.
 *
 * @return void
 */
function giftflow_campaign_single_images_block() {
	register_block_type(
		'giftflow/campaign-single-images',
		array(
			'api_version' => 3,
			'render_callback' => 'giftflow_campaign_single_images_block_render',
		)
	);
}

/**
 * Add action to register campaign single images block.
 */
add_action( 'init', 'giftflow_campaign_single_images_block' );

/**
 * Render campaign single images block.
 *
 * @param array $attributes Block attributes.
 * @param string $content Block content.
 * @param WP_Block $block Block object.
 * @return string Block output.
 */
function giftflow_campaign_single_images_block_render( $attributes, $content, $block ) {
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

	// Get featured image.
	$featured_image_id = get_post_thumbnail_id( $post_id );
	$featured_image_url = $featured_image_id ? wp_get_attachment_image_url( $featured_image_id, 'large' ) : '';
	$featured_image_alt = $featured_image_id ? get_post_meta( $featured_image_id, '_wp_attachment_image_alt', true ) : '';

	// Get gallery images.
	$gallery_ids = get_post_meta( $post_id, '_gallery', true );
	$gallery_ids = ! empty( $gallery_ids ) ? explode( ',', $gallery_ids ) : array();
	$gallery_ids = array_map( 'intval', $gallery_ids );
	$gallery_ids = array_filter( $gallery_ids );

	// Combine featured image and gallery images, removing duplicates.
	$all_image_ids = array();
	if ( $featured_image_id ) {
		$all_image_ids[] = (int) $featured_image_id;
	}
	foreach ( $gallery_ids as $gallery_id ) {
		if ( ! in_array( $gallery_id, $all_image_ids, true ) ) {
			$all_image_ids[] = $gallery_id;
		}
	}

	// Allow filtering of images.
	$all_image_ids = apply_filters( 'giftflow_campaign_single_images', $all_image_ids, $post_id );

	// If no images, return empty.
	if ( empty( $all_image_ids ) ) {
		// Render a semantic HTML placeholder when there are no images.
		?>
		<div class="giftflow-campaign-single-images giftflow-campaign-single-images--placeholder"
			aria-label="<?php esc_attr_e( 'No images available', 'giftflow' ); ?>">
			<div class="giftflow-campaign-single-images-placeholder"
				style="display: flex; align-items: center; justify-content: center; background: #e8e8e8; border-radius: 1px; color: #aaaaaa; font-size: .8em; text-align: center; user-select: none; aspect-ratio: 3 / 2;">
				<?php esc_html_e( 'No images available', 'giftflow' ); ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	ob_start();
	?>
	<div class="giftflow-campaign-single-images">
		<?php if ( count( $all_image_ids ) === 1 ) : ?>
			<!-- Single Image -->
			<div class="giftflow-campaign-single-images-single">
				<?php
				$image_id = $all_image_ids[0];
				$image_url = wp_get_attachment_image_url( $image_id, 'large' );
				$image_full_url = wp_get_attachment_image_url( $image_id, 'full' );
				$image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
				$image_alt = ! empty( $image_alt ) ? $image_alt : get_the_title( $post_id );
				?>
				<img 
					src="<?php echo esc_url( $image_url ); ?>" 
					alt="<?php echo esc_attr( $image_alt ); ?>"
					class="giftflow-campaign-single-images-main"
					data-full-url="<?php echo esc_url( $image_full_url ); ?>"
				/>
			</div>
		<?php else : ?>
			<!-- Gallery with Main Image and Thumbnails -->
			<div class="giftflow-campaign-single-images-gallery">
				<!-- Main Image -->
				<div class="giftflow-campaign-single-images-gallery-main-container">
					<?php
					$main_image_id = $all_image_ids[0];
					$main_image_url = wp_get_attachment_image_url( $main_image_id, 'large' );
					$main_image_full_url = wp_get_attachment_image_url( $main_image_id, 'full' );
					$main_image_alt = get_post_meta( $main_image_id, '_wp_attachment_image_alt', true );
					$main_image_alt = ! empty( $main_image_alt ) ? $main_image_alt : get_the_title( $post_id );
					?>
					<img 
						src="<?php echo esc_url( $main_image_url ); ?>" 
						alt="<?php echo esc_attr( $main_image_alt ); ?>"
						class="giftflow-campaign-single-images-main"
						data-full-url="<?php echo esc_url( $main_image_full_url ); ?>"
						data-image-id="<?php echo esc_attr( $main_image_id ); ?>"
					/>
				</div>

				<!-- Thumbnails -->
				<?php if ( count( $all_image_ids ) > 1 ) : ?>
					<?php
					$total_images = count( $all_image_ids );
					$max_visible = 3;
					$has_more = $total_images > $max_visible;
					?>
					<div class="giftflow-campaign-single-images-gallery-thumbnails">
						<?php foreach ( $all_image_ids as $index => $image_id ) : ?>
							<?php
							$thumb_url = wp_get_attachment_image_url( $image_id, 'thumbnail' );
							$image_url = wp_get_attachment_image_url( $image_id, 'large' );
							$image_full_url = wp_get_attachment_image_url( $image_id, 'full' );
							$image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
							$image_alt = ! empty( $image_alt ) ? $image_alt : get_the_title( $post_id );
							$is_active = 0 === $index ? 'active' : '';
							// Hide thumbnails beyond the first 3 initially.
							$is_hidden = $has_more && $index >= $max_visible ? 'giftflow-thumbnail-hidden' : '';
							?>
							<div 
								class="giftflow-campaign-single-images-gallery-thumbnail <?php echo esc_attr( $is_active ); ?> <?php echo esc_attr( $is_hidden ); ?>"
								data-image-id="<?php echo esc_attr( $image_id ); ?>"
								data-image-url="<?php echo esc_url( $image_url ); ?>"
								data-image-full-url="<?php echo esc_url( $image_full_url ); ?>"
								data-image-alt="<?php echo esc_attr( $image_alt ); ?>"
							>
								<img 
									src="<?php echo esc_url( $thumb_url ); ?>" 
									alt="<?php echo esc_attr( $image_alt ); ?>"
								/>
							</div>
						<?php endforeach; ?>
						<?php
						if ( $has_more ) :
							// translators: 1 is the number of more images to show.
							$more_images_label = sprintf( esc_html__( 'Show %d more images', 'giftflow' ), $total_images - $max_visible );

							// translators: 1 is the number of more images to show.
							$more_images_count_text = sprintf( esc_html__( '+%d', 'giftflow' ), $total_images - $max_visible );
							?>
							<!-- Expand Button -->
							<div 
								class="giftflow-campaign-single-images-gallery-expand"
								data-total-images="<?php echo esc_attr( $total_images ); ?>"
								data-visible-count="<?php echo esc_attr( $max_visible ); ?>"
								role="button"
								tabindex="0"
								aria-label="<?php echo esc_attr( $more_images_label ); ?>"
							>
								<span class="giftflow-campaign-single-images-gallery-expand-text gfw-monofont">
									<?php echo esc_html( $more_images_count_text ); ?>
								</span>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	<script>
		// Gallery thumbnail click handler.
		document.addEventListener('DOMContentLoaded', function() {
			const gallery = document.querySelector('.giftflow-campaign-single-images-gallery');
			if (!gallery) {
				return;
			}

			const thumbnails = gallery.querySelectorAll('.giftflow-campaign-single-images-gallery-thumbnail');
			const mainImage = gallery.querySelector('.giftflow-campaign-single-images-main');
			const expandButton = gallery.querySelector('.giftflow-campaign-single-images-gallery-expand');

			// Handle thumbnail clicks.
			thumbnails.forEach(function(thumbnail) {
				thumbnail.addEventListener('click', function() {
					// Remove active class from all thumbnails.
					thumbnails.forEach(function(thumb) {
						thumb.classList.remove('active');
					});

					// Add active class to clicked thumbnail.
					thumbnail.classList.add('active');

					// Update main image.
					if (mainImage) {
						mainImage.src = thumbnail.dataset.imageUrl;
						mainImage.alt = thumbnail.dataset.imageAlt;
						mainImage.dataset.fullUrl = thumbnail.dataset.imageFullUrl;
						mainImage.dataset.imageId = thumbnail.dataset.imageId;
					}
				});
			});

			// Handle expand button click.
			if (expandButton) {
				const handleExpand = function() {
					const hiddenThumbnails = gallery.querySelectorAll('.giftflow-thumbnail-hidden');
					const isExpanded = expandButton.classList.contains('expanded');

					if (isExpanded) {
						// Collapse: hide thumbnails beyond first 3.
						hiddenThumbnails.forEach(function(thumb) {
							thumb.classList.add('giftflow-thumbnail-hidden');
						});
						expandButton.classList.remove('expanded');
						expandButton.setAttribute('aria-label', expandButton.dataset.expandLabel || '<?php echo esc_js( __( 'Show more images', 'giftflow' ) ); ?>');
					} else {
						// Expand: show all thumbnails.
						hiddenThumbnails.forEach(function(thumb) {
							thumb.classList.remove('giftflow-thumbnail-hidden');
						});
						expandButton.classList.add('expanded');
						expandButton.setAttribute('aria-label', expandButton.dataset.collapseLabel || '<?php echo esc_js( __( 'Show fewer images', 'giftflow' ) ); ?>');
					}
				};

				// Store labels for accessibility.
				expandButton.dataset.expandLabel = expandButton.getAttribute('aria-label');
				expandButton.dataset.collapseLabel = '<?php echo esc_js( __( 'Show fewer images', 'giftflow' ) ); ?>';

				// Click handler.
				expandButton.addEventListener('click', handleExpand);

				// Keyboard handler for accessibility.
				expandButton.addEventListener('keydown', function(e) {
					if (e.key === 'Enter' || e.key === ' ') {
						e.preventDefault();
						handleExpand();
					}
				});
			}
		});
	</script>
	<?php
	return ob_get_clean();
}
