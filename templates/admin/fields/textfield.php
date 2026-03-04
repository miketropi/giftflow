<?php
/**
 * Textfield field template
 *
 * @package GiftFlow
 * @since 1.0.0
 *
 * @var string $id Field ID.
 * @var string $name Field name.
 * @var string $value Field value.
 * @var array $attributes Field attributes (key => value).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<input type="text" <?php echo giftflow_render_attributes( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> value="<?php echo esc_attr( $value ); ?>" />
