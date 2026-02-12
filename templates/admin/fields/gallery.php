<?php
/**
 * Gallery field template
 *
 * @package GiftFlow
 * @since 1.0.0
 *
 * @var string $id Field ID.
 * @var string $name Field name.
 * @var string $gallery_id Unique gallery ID.
 * @var array $image_ids Array of image attachment IDs.
 * @var array $gallery_settings Gallery settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div 
	class="giftflow-gallery-field" 
	id="<?php echo esc_attr( $gallery_id ); ?>"
	data-max-images="<?php echo esc_attr( $gallery_settings['max_images'] ); ?>"
	data-image-size="<?php echo esc_attr( $gallery_settings['image_size'] ); ?>"
	data-button-text="<?php echo esc_attr( $gallery_settings['button_text'] ); ?>"
	data-remove-text="<?php echo esc_attr( $gallery_settings['remove_text'] ); ?>"
	data-nonce="<?php echo esc_attr( wp_create_nonce( 'giftflow_gallery_nonce' ) ); ?>">
	<!-- Hidden input to store image IDs -->
	<input type="hidden" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( implode( ',', $image_ids ) ); ?>" />
	
	<!-- Gallery preview container -->
	<div class="giftflow-gallery-preview">
		<?php
		if ( ! empty( $image_ids ) ) {
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
			foreach ( $image_ids as $image_id ) {
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
				$image_url = wp_get_attachment_image_url( $image_id, $gallery_settings['image_size'] );
				if ( $image_url ) {
					?>
					<div class="giftflow-gallery-image" data-id="<?php echo esc_attr( $image_id ); ?>">
						<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( get_post_meta( $image_id, '_wp_attachment_image_alt', true ) ); ?>" />
						<span class="giftflow-gallery-remove" title="<?php esc_attr_e( 'Remove Image', 'giftflow' ); ?>">&times;</span>
					</div>
					<?php
				}
			}
		}
		?>
	</div><!-- End gallery preview -->
	
	<!-- Gallery controls -->
	<div class="giftflow-gallery-controls">
		<button type="button" class="button giftflow-gallery-add"><?php echo esc_html( $gallery_settings['button_text'] ); ?></button>
		
		<?php if ( ! empty( $image_ids ) ) : ?>
			<button type="button" class="button giftflow-gallery-remove-all"><?php echo esc_html( $gallery_settings['remove_text'] ); ?></button>
		<?php endif; ?>
	</div><!-- End gallery controls -->
</div><!-- End gallery field -->
