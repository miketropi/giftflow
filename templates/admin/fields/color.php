<?php
/**
 * Color field template
 *
 * @package GiftFlow
 * @since 1.0.0
 *
 * @var string $id Field ID.
 * @var string $name Field name.
 * @var string $value Color value.
 * @var array $attributes Field attributes (key => value).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="giftflow-color-field">
	<input type="color" <?php echo giftflow_render_attributes( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> value="<?php echo esc_attr( $value ); ?>" />
	<input type="text" class="giftflow-color-text" value="<?php echo esc_attr( $value ); ?>" />
</div>
