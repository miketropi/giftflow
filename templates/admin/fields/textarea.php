<?php
/**
 * Textarea field template
 *
 * @package GiftFlow
 * @since 1.0.0
 *
 * @var string $id Field ID.
 * @var string $name Field name.
 * @var string $value Field value.
 * @var array $attributes Field attributes (key => value), includes rows/cols.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<textarea <?php echo giftflow_render_attributes( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_textarea( $value ); ?></textarea>
