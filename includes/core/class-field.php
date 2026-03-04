<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Field Class
 *
 * A flexible field builder for WordPress custom meta fields.
 *
 * @package GiftFlow
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Field Class
 *
 * @since 1.0.0
 */
class GiftFlow_Field {

	/**
	 * Field ID
	 *
	 * @var string
	 */
	private $id;

	/**
	 * Field name
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Field label
	 *
	 * @var string
	 */
	private $label;

	/**
	 * Field description
	 *
	 * @var string
	 */
	private $description;

	/**
	 * Field type
	 *
	 * @var string
	 */
	private $type;

	/**
	 * Field value
	 *
	 * @var mixed
	 */
	private $value;

	/**
	 * Field options (for select, multiple select, etc.)
	 *
	 * @var array
	 */
	private $options = array();

	/**
	 * Field attributes
	 *
	 * @var array
	 */
	private $attributes = array();

	/**
	 * Field classes
	 *
	 * @var array
	 */
	private $classes = array();

	/**
	 * Field wrapper classes
	 *
	 * @var array
	 */
	private $wrapper_classes = array();

	/**
	 * Field default value
	 *
	 * @var mixed
	 */
	private $default;

	/**
	 * Field required
	 *
	 * @var bool
	 */
	private $required = false;

	/**
	 * Field disabled
	 *
	 * @var bool
	 */
	private $disabled = false;

	/**
	 * Field placeholder
	 *
	 * @var string
	 */
	private $placeholder = '';

	/**
	 * Field min value (for number, currency)
	 *
	 * @var int|float
	 */
	private $min;

	/**
	 * Field max value (for number, currency)
	 *
	 * @var int|float
	 */
	private $max;

	/**
	 * Field step value (for number, currency)
	 *
	 * @var int|float
	 */
	private $step;

	/**
	 * Field currency symbol (for currency)
	 *
	 * @var string
	 */
	private $currency_symbol = '$';

	/**
	 * Field currency position (for currency)
	 *
	 * @var string
	 */
	private $currency_position = 'before';

	/**
	 * Field rows (for textarea)
	 *
	 * @var int
	 */
	private $rows = 5;

	/**
	 * Field columns (for textarea)
	 *
	 * @var int
	 */
	private $cols = 50;

	/**
	 * Field date format (for datetime)
	 *
	 * @var string
	 */
	private $date_format = 'Y-m-d H:i:s';

	/**
	 * Field time format (for datetime)
	 *
	 * @var string
	 */
	private $time_format = 'H:i:s';

	/**
	 * Field color format (for color)
	 *
	 * @var string
	 */
	private $color_format = 'hex';

	/**
	 * Field html (for html)
	 *
	 * @var string
	 */
	private $html = '';

	/**
	 * Field gallery settings (for gallery)
	 *
	 * @var array
	 */
	private $gallery_settings = array(
		'max_images'  => 0,
		'image_size'  => 'thumbnail',
		'button_text' => 'Select Images',
		'remove_text' => 'Remove All',
	);

	/**
	 * Field repeater settings (for repeater)
	 *
	 * @var array
	 */
	private $repeater_settings = array(
		'fields'      => array(),
		'button_text' => 'Add Row',
		'remove_text' => 'Remove Row',
		'min_rows'    => 0,
		'max_rows'    => 0,
		'row_label'   => 'Row',
	);

	/**
	 * Field accordion settings (for accordion)
	 *
	 * @var array
	 */
	private $accordion_settings = array(
		'fields'        => array(),
		'is_open'       => false,
		'icon'          => 'dashicons-arrow-down-alt2',
		'icon_position' => 'right',
	);

	/**
	 * Field pro only
	 *
	 * @var bool
	 */
	private $pro_only = false;

	/**
	 * Constructor
	 *
	 * @param string $id Field ID.
	 * @param string $name Field name.
	 * @param string $type Field type.
	 * @param array  $args Field arguments.
	 */
	public function __construct( $id, $name, $type, $args = array() ) {
		$this->id   = $id;
		$this->name = $name;
		$this->type = $type;

		// Set default value.
		$this->default = isset( $args['default'] ) ? $args['default'] : '';

		// Set field value.
		$this->value = isset( $args['value'] ) ? $args['value'] : $this->default;

		// Set field label.
		$this->label = isset( $args['label'] ) ? $args['label'] : '';

		// Set field description.
		$this->description = isset( $args['description'] ) ? $args['description'] : '';

		// Set field options.
		$this->options = isset( $args['options'] ) ? $args['options'] : array();

		// Set field attributes.
		$this->attributes = isset( $args['attributes'] ) ? $args['attributes'] : array();

		// Set field classes.
		$this->classes = isset( $args['classes'] ) ? $args['classes'] : array();

		// Set field wrapper classes.
		$this->wrapper_classes = isset( $args['wrapper_classes'] ) ? $args['wrapper_classes'] : array();

		// Set field required.
		$this->required = isset( $args['required'] ) ? $args['required'] : false;

		// Set field disabled.
		$this->disabled = isset( $args['disabled'] ) ? $args['disabled'] : false;

		// Set field placeholder.
		$this->placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';

		// Set field min value.
		$this->min = isset( $args['min'] ) ? $args['min'] : null;

		// Set field max value.
		$this->max = isset( $args['max'] ) ? $args['max'] : null;

		// Set field step value.
		$this->step = isset( $args['step'] ) ? $args['step'] : null;

		// set field html.
		if ( 'html' === $type ) {
			$this->html = $args['html'];
		}

		// Set field pro only.
		$this->pro_only = isset( $args['pro_only'] ) ? $args['pro_only'] : false;

		// default currency symbol.
		$default_currency_symbol = giftflow_get_currency_symbol( giftflow_get_current_currency() );

		// Set field currency symbol.
		$this->currency_symbol = isset( $args['currency_symbol'] ) ? $args['currency_symbol'] : $default_currency_symbol;

		// Set field currency position.
		$this->currency_position = isset( $args['currency_position'] ) ? $args['currency_position'] : 'before';

		// Set field rows.
		$this->rows = isset( $args['rows'] ) ? $args['rows'] : 5;

		// Set field columns.
		$this->cols = isset( $args['cols'] ) ? $args['cols'] : 50;

		// Set field date format.
		$this->date_format = isset( $args['date_format'] ) ? $args['date_format'] : 'Y-m-d H:i:s';

		// Set field time format.
		$this->time_format = isset( $args['time_format'] ) ? $args['time_format'] : 'H:i:s';

		// Set field color format.
		$this->color_format = isset( $args['color_format'] ) ? $args['color_format'] : 'hex';

		// Set gallery settings.
		if ( 'gallery' === $type && isset( $args['gallery_settings'] ) ) {
			$this->gallery_settings = wp_parse_args( $args['gallery_settings'], $this->gallery_settings );
		}

		// Set repeater settings.
		if ( 'repeater' === $type && isset( $args['repeater_settings'] ) ) {
			$this->repeater_settings = wp_parse_args( $args['repeater_settings'], $this->repeater_settings );
		}

		// Set accordion settings.
		if ( 'accordion' === $type && isset( $args['accordion_settings'] ) ) {
			$this->accordion_settings = wp_parse_args( $args['accordion_settings'], $this->accordion_settings );
		}
	}

	/**
	 * Render field.
	 *
	 * Outputs the field HTML directly.
	 *
	 * @return void
	 */
	public function render() {
		// Start field wrapper.
		echo $this->get_field_wrapper_start(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		// Render field label.
		echo $this->get_field_label(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		// Add pro only indicator.
		$classes = array( 'giftflow-field-wrapper' );

		echo '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">';

		// Render field based on type.
		switch ( $this->type ) {
			case 'textfield':
				echo $this->render_textfield(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;
			case 'number':
				echo $this->render_number(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;
			case 'currency':
				echo $this->render_currency(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;
			case 'select':
				echo $this->render_select(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;
			case 'multiple_select':
				echo $this->render_multiple_select(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;
			case 'textarea':
				echo $this->render_textarea(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;
			case 'checkbox':
				echo $this->render_checkbox(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;
			case 'switch':
				echo $this->render_switch(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;
			case 'datetime':
				echo $this->render_datetime(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;
			case 'color':
				echo $this->render_color(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;
			case 'gallery':
				echo $this->render_gallery(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;
			case 'googlemap':
				echo $this->render_googlemap(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;
			case 'repeater':
				echo $this->render_repeater(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;
			case 'accordion':
				echo $this->render_accordion(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;
			case 'html':
				echo $this->render_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;
			default:
				echo $this->render_textfield(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;
		}

		// Render field description.
		echo $this->get_field_description(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		echo '</div>';

		// End field wrapper.
		echo $this->get_field_wrapper_end(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Get field wrapper start
	 *
	 * @return string
	 */
	private function get_field_wrapper_start() {
		// base classes.
		$classes = array( 'giftflow-field', 'giftflow-field-' . $this->type );

		// add pro only class.
		if ( true === $this->pro_only ) {
			$classes[] = 'giftflow-pro-only-field';

			// if defined GIFTFLOW_PRO_VERSION add class disabled.
			if ( ! defined( 'GIFTFLOW_PRO_VERSION' ) ) {
				$classes[] = 'giftflow-pro-only-field__disabled';
			}
		}

		$wrapper_classes = array_merge( $classes, $this->wrapper_classes );
		$wrapper_classes = array_filter( $wrapper_classes );
		$wrapper_class   = implode( ' ', $wrapper_classes );

		return '<div class="' . esc_attr( $wrapper_class ) . '">';
	}

	/**
	 * Get field wrapper end
	 *
	 * @return string
	 */
	private function get_field_wrapper_end() {
		return '</div>';
	}

	/**
	 * Get field label
	 *
	 * @return string
	 */
	private function get_field_label() {
		if ( empty( $this->label ) ) {
			return '';
		}

		// add pro only indicator.
		$pro_tag = '';
		if ( true === $this->pro_only ) {
			$pro_tag = ' <sup class="giftflow-pro-only-indicator">(Pro)</sup>';
		}

		$required = $this->required ? ' <span class="required">*</span>' : '';
		return '<label for="' . esc_attr( $this->id ) . '">' . esc_html( $this->label ) . $required . $pro_tag . '</label>';
	}

	/**
	 * Get field description
	 *
	 * @return string
	 */
	private function get_field_description() {
		if ( empty( $this->description ) ) {
			return '';
		}

		return '<p class="description">' . wp_kses_post( $this->description ) . '</p>';
	}

	/**
	 * Get field attributes as key => value array.
	 *
	 * @param array $extra Optional. Extra attributes to merge (e.g. min, max, multiple).
	 * @return array Associative array of attribute => value.
	 */
	private function get_field_attributes( $extra = array() ) {
		$classes      = array_merge( array( 'giftflow-field-input' ), $this->classes );
		$classes      = array_filter( $classes );
		$class        = implode( ' ', $classes );

		$attributes = array(
			'id'    => $this->id,
			'name'  => $this->name,
			'class' => $class,
		);

		if ( ! empty( $this->placeholder ) ) {
			$attributes['placeholder'] = $this->placeholder;
		}

		if ( $this->required ) {
			$attributes['required'] = true;
		}

		if ( $this->disabled ) {
			$attributes['disabled'] = true;
		}

		$attributes = array_merge( $attributes, $this->attributes, $extra );

		return $attributes;
	}

	/**
	 * Load a field template and return its output.
	 *
	 * @param string $template_name Template file name (e.g. 'textfield.php').
	 * @param array  $args Template arguments.
	 * @return string
	 */
	private function load_field_template( $template_name, $args = array() ) {
		ob_start();
		giftflow_load_template( 'admin/fields/' . $template_name, $args );
		return ob_get_clean();
	}

	/**
	 * Render HTML.
	 *
	 * @return string
	 */
	private function render_html() {
		return $this->load_field_template( 'html.php', array( 'html' => $this->html ) );
	}

	/**
	 * Render textfield
	 *
	 * @return string
	 */
	private function render_textfield() {
		return $this->load_field_template(
			'textfield.php',
			array(
				'id'         => $this->id,
				'name'       => $this->name,
				'value'      => $this->value,
				'attributes' => $this->get_field_attributes(),
			)
		);
	}

	/**
	 * Render number
	 *
	 * @return string
	 */
	private function render_number() {
		$extra = array();
		if ( null !== $this->min ) {
			$extra['min'] = $this->min;
		}
		if ( null !== $this->max ) {
			$extra['max'] = $this->max;
		}
		if ( null !== $this->step ) {
			$extra['step'] = $this->step;
		}
		return $this->load_field_template(
			'number.php',
			array(
				'id'         => $this->id,
				'name'       => $this->name,
				'value'      => $this->value,
				'attributes' => $this->get_field_attributes( $extra ),
			)
		);
	}

	/**
	 * Render currency
	 *
	 * @return string
	 */
	private function render_currency() {
		$extra = array();
		if ( null !== $this->min ) {
			$extra['min'] = $this->min;
		}
		if ( null !== $this->max ) {
			$extra['max'] = $this->max;
		}
		if ( null !== $this->step ) {
			$extra['step'] = $this->step;
		}
		return $this->load_field_template(
			'currency.php',
			array(
				'id'                => $this->id,
				'name'              => $this->name,
				'value'             => $this->value,
				'attributes'        => $this->get_field_attributes( $extra ),
				'currency_symbol'   => $this->currency_symbol,
				'currency_position' => $this->currency_position,
			)
		);
	}

	/**
	 * Render select
	 *
	 * @return string
	 */
	private function render_select() {
		return $this->load_field_template(
			'select.php',
			array(
				'id'         => $this->id,
				'name'       => $this->name,
				'value'      => $this->value,
				'attributes' => $this->get_field_attributes(),
				'options'    => $this->options,
			)
		);
	}

	/**
	 * Render multiple select
	 *
	 * @return string
	 */
	private function render_multiple_select() {
		$values = is_array( $this->value ) ? $this->value : array( $this->value );

		return $this->load_field_template(
			'multiple-select.php',
			array(
				'id'         => $this->id,
				'name'       => $this->name,
				'values'     => $values,
				'attributes' => $this->get_field_attributes( array( 'multiple' => true ) ),
				'options'    => $this->options,
			)
		);
	}

	/**
	 * Render textarea
	 *
	 * @return string
	 */
	private function render_textarea() {
		return $this->load_field_template(
			'textarea.php',
			array(
				'id'         => $this->id,
				'name'       => $this->name,
				'value'      => $this->value,
				'attributes' => $this->get_field_attributes(
					array(
						'rows' => $this->rows,
						'cols' => $this->cols,
					)
				),
			)
		);
	}

	/**
	 * Render checkbox
	 *
	 * @return string
	 */
	private function render_checkbox() {
		return $this->load_field_template(
			'checkbox.php',
			array(
				'id'         => $this->id,
				'name'       => $this->name,
				'value'      => $this->value,
				'attributes' => $this->get_field_attributes(),
				'checked'    => checked( $this->value, true, false ),
			)
		);
	}

	/**
	 * Render switch
	 *
	 * @return string
	 */
	private function render_switch() {
		return $this->load_field_template(
			'switch.php',
			array(
				'id'         => $this->id,
				'name'       => $this->name,
				'value'      => $this->value,
				'attributes' => $this->get_field_attributes(),
				'checked'    => checked( $this->value, true, false ),
			)
		);
	}

	/**
	 * Render datetime
	 *
	 * @return string
	 */
	private function render_datetime() {
		$value = ! empty( $this->value ) ? gmdate( $this->date_format, strtotime( $this->value ) ) : '';

		return $this->load_field_template(
			'datetime.php',
			array(
				'id'         => $this->id,
				'name'       => $this->name,
				'value'     => $value,
				'attributes' => $this->get_field_attributes(),
			)
		);
	}

	/**
	 * Render color
	 *
	 * @return string
	 */
	private function render_color() {
		return $this->load_field_template(
			'color.php',
			array(
				'id'         => $this->id,
				'name'       => $this->name,
				'value'      => $this->value,
				'attributes' => $this->get_field_attributes(),
			)
		);
	}

	/**
	 * Render gallery
	 *
	 * @return string
	 */
	private function render_gallery() {
		$image_ids = $this->value ? explode( ',', $this->value ) : array();

		return $this->load_field_template(
			'gallery.php',
			array(
				'id'               => $this->id,
				'name'             => $this->name,
				'gallery_id'        => 'giftflow-gallery-' . $this->id,
				'image_ids'        => $image_ids,
				'gallery_settings'  => $this->gallery_settings,
			)
		);
	}

	/**
	 * Render Google Maps field
	 *
	 * @return string
	 */
	private function render_googlemap() {
		$value   = $this->value;
		$address = '';
		$lat     = '';
		$lng     = '';

		if ( ! empty( $value ) ) {
			$value_data = json_decode( $value, true );
			if ( is_array( $value_data ) ) {
				$address = isset( $value_data['address'] ) ? $value_data['address'] : '';
				$lat     = isset( $value_data['lat'] ) ? $value_data['lat'] : '';
				$lng     = isset( $value_data['lng'] ) ? $value_data['lng'] : '';
			}
		}

		$api_key = defined( 'GIFTFLOW_GOOGLE_MAPS_API_KEY' ) ? GIFTFLOW_GOOGLE_MAPS_API_KEY : '';

		return $this->load_field_template(
			'googlemap.php',
			array(
				'id'      => $this->id,
				'name'    => $this->name,
				'map_id'  => 'giftflow-map-' . $this->id,
				'value'   => $value,
				'address' => $address,
				'lat'     => $lat,
				'lng'     => $lng,
				'api_key' => $api_key,
			)
		);
	}

	/**
	 * Render repeater field
	 *
	 * @return string
	 */
	private function render_repeater() {
		$repeater_settings = $this->repeater_settings;
		$fields            = $repeater_settings['fields'];
		$button_text       = $repeater_settings['button_text'];
		$remove_text       = $repeater_settings['remove_text'];
		$min_rows          = $repeater_settings['min_rows'];
		$max_rows          = $repeater_settings['max_rows'];
		$row_label         = $repeater_settings['row_label'];

		$values = is_array( $this->value ) ? $this->value : array();
		if ( empty( $values ) && $min_rows > 0 ) {
			$values = array_fill( 0, $min_rows, array() );
		}

		return $this->load_field_template(
			'repeater.php',
			array(
				'id'           => $this->id,
				'name'         => $this->name,
				'repeater_id'  => 'giftflow-repeater-' . $this->id,
				'values'       => $values,
				'fields'       => $fields,
				'button_text'  => $button_text,
				'remove_text'  => $remove_text,
				'min_rows'     => $min_rows,
				'max_rows'     => $max_rows,
				'row_label'    => $row_label,
				'row_template' => $this->get_repeater_row_template( $fields ),
			)
		);
	}

	/**
	 * Get repeater row template
	 *
	 * @param array $fields Fields configuration.
	 * @return string
	 */
	private function get_repeater_row_template( $fields ) {
		ob_start();
		?>
		<div class="giftflow-repeater-row" data-index="__INDEX__">
			<div class="giftflow-repeater-row-header">
				<span class="giftflow-repeater-row-title">
					<?php echo esc_html( $this->repeater_settings['row_label'] ); ?>
				</span>
				<button type="button" class="button giftflow-repeater-remove-row"><?php echo esc_html( $this->repeater_settings['remove_text'] ); ?></button>
			</div>
			<div class="giftflow-repeater-row-content">
				<?php
				foreach ( $fields as $field_id => $field_args ) :
					$field_name = $this->name . '[__INDEX__][' . $field_id . ']';
					$field_id   = $this->id . '___INDEX__' . $field_id;

					// Create field instance.
					$field = new GiftFlow_Field(
						$field_id,
						$field_name,
						$field_args['type'],
						array_merge(
							$field_args,
							array(
								'value'           => '',
								'wrapper_classes' => array( 'giftflow-repeater-field' ),
							)
						)
					);

					// render field.
					$field->render();
				endforeach;
				?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render accordion field
	 *
	 * @return string
	 */
	private function render_accordion() {
		$settings = $this->accordion_settings;
		$content_callback = null;
		if ( isset( $this->content ) && is_callable( $this->content ) ) {
			$content_callback = $this->content;
		}

		return $this->load_field_template(
			'accordion.php',
			array(
				'id'               => $this->id,
				'name'             => $this->name,
				'accordion_id'     => 'giftflow-accordion-' . $this->id,
				'description'     => $this->description,
				'settings'        => $settings,
				'content_callback' => $content_callback,
			)
		);
	}

	/**
	 * Get field value
	 *
	 * @return mixed
	 */
	public function get_value() {
		return $this->value;
	}

	/**
	 * Set field value
	 *
	 * @param mixed $value Field value.
	 */
	public function set_value( $value ) {
		$this->value = $value;
	}

	/**
	 * Get field ID
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get field name
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get field type
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}
}
