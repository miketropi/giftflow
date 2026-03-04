<?php
/**
 * Currency field template
 *
 * @package GiftFlow
 * @since 1.0.0
 *
 * @var string $id Field ID.
 * @var string $name Field name.
 * @var string $value Field value.
 * @var array $attributes Field attributes (key => value), includes min/max/step.
 * @var string $currency_symbol Currency symbol.
 * @var string $currency_position Position: before|after.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="giftflow-currency-field">
	<?php if ( 'before' === $currency_position ) : ?>
		<span class="giftflow-currency-symbol"><?php echo esc_html( $currency_symbol ); ?></span>
	<?php endif; ?>

	<input type="number" <?php echo giftflow_render_attributes( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> value="<?php echo esc_attr( $value ); ?>" />

	<?php if ( 'after' === $currency_position ) : ?>
		<span class="giftflow-currency-symbol"><?php echo esc_html( $currency_symbol ); ?></span>
	<?php endif; ?>
</div>
