<?php
/**
 * Accordion field template
 *
 * @package GiftFlow
 * @since 1.0.0
 *
 * @var string $id Field ID.
 * @var string $name Field name.
 * @var string $accordion_id Unique accordion ID.
 * @var string $description Field description.
 * @var array $settings Accordion settings (label, icon, icon_position, is_open, fields).
 * @var GiftFlow_Field $field_instance The field instance for creating nested fields.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_open       = ! empty( $settings['is_open'] ) ? 'open' : '';
$icon          = isset( $settings['icon'] ) ? $settings['icon'] : 'dashicons-arrow-down-alt2';
$icon_position = isset( $settings['icon_position'] ) ? $settings['icon_position'] : 'right';
$label         = isset( $settings['label'] ) ? $settings['label'] : '';
?>
<div class="giftflow-accordion-section <?php echo esc_attr( $is_open ); ?>" id="<?php echo esc_attr( $accordion_id ); ?>">
	<!-- Accordion header -->
	<div class="giftflow-accordion-header">
		<?php if ( 'left' === $icon_position ) : ?>
			<span class="dashicons <?php echo esc_attr( $icon ); ?>"></span>
		<?php endif; ?>
		
		<h3><?php echo esc_html( $label ); ?></h3>
		
		<?php if ( 'right' === $icon_position ) : ?>
			<span class="dashicons <?php echo esc_attr( $icon ); ?>"></span>
		<?php endif; ?>
	</div>
	
	<!-- Accordion content -->
	<div class="giftflow-accordion-content">
		<?php if ( ! empty( $description ) ) : ?>
			<p class="description"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
		
		<?php if ( ! empty( $settings['fields'] ) ) : ?>
			<div class="giftflow-accordion-fields">
				<?php
				foreach ( $settings['fields'] as $field_id => $field_args ) :
					$field_value   = isset( $field_args['value'] ) ? $field_args['value'] : '';
					$field_name   = $name . '[' . $field_id . ']';
					$field_id_attr = $id . '_' . $field_id;

					$nested_field = new GiftFlow_Field(
						$field_id_attr,
						$field_name,
						$field_args['type'],
						array_merge(
							$field_args,
							array(
								'value'           => $field_value,
								'wrapper_classes' => array( 'giftflow-accordion-field' ),
							)
						)
					);

					// render field.
					$nested_field->render();
				endforeach;
				?>
			</div>
		<?php endif; ?>
		
		<?php
		if ( isset( $content_callback ) && is_callable( $content_callback ) ) {
			call_user_func( $content_callback );
		}
		?>
	</div>
</div>
