<?php
/**
 * Multiple select field template
 *
 * @package GiftFlow
 * @since 1.0.0
 *
 * @var string $id Field ID.
 * @var string $name Field name.
 * @var array $values Selected values.
 * @var array $attributes Field attributes (key => value), includes multiple.
 * @var array $options Options as value => label.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<select <?php echo giftflow_render_attributes( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	foreach ( $options as $option_value => $option_label ) :
		?>
		<option value="<?php echo esc_attr( $option_value ); ?>"<?php echo in_array( $option_value, $values, true ) ? ' selected' : ''; ?>><?php echo esc_html( $option_label ); ?></option>
	<?php endforeach; ?>
</select>
