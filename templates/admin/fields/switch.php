<?php
/**
 * Switch field template
 *
 * @package GiftFlow
 * @since 1.0.0
 *
 * @var string $id Field ID.
 * @var string $name Field name.
 * @var mixed $value Checked value.
 * @var array $attributes Field attributes (key => value).
 * @var string $checked Checked attribute output.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="giftflow-switch">
	<input type="checkbox" <?php echo giftflow_render_attributes( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo $checked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> value="1" />
	<span class="giftflow-switch-slider"></span>
</div>
