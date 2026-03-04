<?php
/**
 * Repeater field template
 *
 * @package GiftFlow
 * @since 1.0.0
 *
 * @var string $id Field ID.
 * @var string $name Field name.
 * @var string $repeater_id Unique repeater ID.
 * @var array $values Array of row values.
 * @var array $fields Fields configuration.
 * @var string $button_text Add row button text.
 * @var string $remove_text Remove row button text.
 * @var int $min_rows Minimum rows.
 * @var int $max_rows Maximum rows.
 * @var string $row_label Row label.
 * @var GiftFlow_Field $field_instance The field instance for creating nested fields.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div 
	class="giftflow-repeater-field"
	id="<?php echo esc_attr( $repeater_id ); ?>"
	data-id="<?php echo esc_attr( $repeater_id ); ?>"
	data-row-label="<?php echo esc_attr( $row_label ); ?>"
	data-max-rows="<?php echo esc_attr( $max_rows ); ?>"
	data-min-rows="<?php echo esc_attr( $min_rows ); ?>"
	data-button-text="<?php echo esc_attr( $button_text ); ?>"
	data-remove-text="<?php echo esc_attr( $remove_text ); ?>">
	<div class="giftflow-repeater-row-template" style="display: none;">
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $row_template;
		?>
	</div>

	<!-- Hidden input to store all values -->
	<input type="hidden" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( wp_json_encode( $values ) ); ?>" />
	
	<!-- Repeater rows container -->
	<div class="giftflow-repeater-rows">
		<?php
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		foreach ( $values as $row_index => $row_values ) :
			?>
			<div class="giftflow-repeater-row" data-index="<?php echo esc_attr( $row_index ); ?>">
				<div class="giftflow-repeater-row-header">
					<span class="giftflow-repeater-row-title"><?php echo esc_html( $row_label . ' ' . ( $row_index + 1 ) ); ?></span>
					<button type="button" class="button giftflow-repeater-remove-row"><?php echo esc_html( $remove_text ); ?></button>
				</div>
				<div class="giftflow-repeater-row-content">
					<?php
					// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
					foreach ( $fields as $field_id => $field_args ) :
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
						$field_value = isset( $row_values[ $field_id ] ) ? $row_values[ $field_id ] : '';
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
						$field_name  = $name . '[' . $row_index . '][' . $field_id . ']';
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
						$field_id_attr = $id . '_' . $row_index . '_' . $field_id;

						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
						$nested_field = new GiftFlow_Field(
							// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
							$field_id_attr,
							// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
							$field_name,
							// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
							$field_args['type'],
							array_merge(
								// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
								$field_args,
								array(
									// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
									'value'           => $field_value,
									'wrapper_classes'  => array( 'giftflow-repeater-field' ),
								)
							)
						);

						// render field.
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
						$nested_field->render();
					endforeach;
					?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	
	<!-- Add row button -->
	<button type="button" class="button giftflow-repeater-add-row" <?php echo ( $max_rows > 0 && count( $values ) >= $max_rows ) ? 'disabled' : ''; ?>>
		<?php echo esc_html( $button_text ); ?>
	</button>
</div>
