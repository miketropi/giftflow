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
	 * @return string
	 */
	public function render() {
		$output = '';

		// Start field wrapper.
		$output .= $this->get_field_wrapper_start();

		// Render field label.
		$output .= $this->get_field_label();

		// Add pro only indicator.
		$classes = array( 'giftflow-field-wrapper' );

		$output .= '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">';

		// Render field based on type.
		switch ( $this->type ) {
			case 'textfield':
				$output .= $this->render_textfield();
				break;
			case 'number':
				$output .= $this->render_number();
				break;
			case 'currency':
				$output .= $this->render_currency();
				break;
			case 'select':
				$output .= $this->render_select();
				break;
			case 'multiple_select':
				$output .= $this->render_multiple_select();
				break;
			case 'textarea':
				$output .= $this->render_textarea();
				break;
			case 'checkbox':
				$output .= $this->render_checkbox();
				break;
			case 'switch':
				$output .= $this->render_switch();
				break;
			case 'datetime':
				$output .= $this->render_datetime();
				break;
			case 'color':
				$output .= $this->render_color();
				break;
			case 'gallery':
				$output .= $this->render_gallery();
				break;
			case 'googlemap':
				$output .= $this->render_googlemap();
				break;
			case 'repeater':
				$output .= $this->render_repeater();
				break;
			case 'accordion':
				$output .= $this->render_accordion();
				break;
			case 'html':
				$output .= $this->render_html();
				break;
			default:
				$output .= $this->render_textfield();
				break;
		}

		// Render field description.
		$output .= $this->get_field_description();

		$output .= '</div>';

		// End field wrapper.
		$output .= $this->get_field_wrapper_end();

		return $output;
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
	 * Get field attributes
	 *
	 * @return string
	 */
	private function get_field_attributes() {
		$attributes = array();

		// Add ID.
		$attributes[] = 'id="' . esc_attr( $this->id ) . '"';

		// Add name.
		$attributes[] = 'name="' . esc_attr( $this->name ) . '"';

		// Add placeholder.
		if ( ! empty( $this->placeholder ) ) {
			$attributes[] = 'placeholder="' . esc_attr( $this->placeholder ) . '"';
		}

		// Add required.
		if ( $this->required ) {
			$attributes[] = 'required';
		}

		// Add disabled.
		if ( $this->disabled ) {
			$attributes[] = 'disabled';
		}

		// Add classes.
		$classes      = array_merge( array( 'giftflow-field-input' ), $this->classes );
		$classes      = array_filter( $classes );
		$class        = implode( ' ', $classes );
		$attributes[] = 'class="' . esc_attr( $class ) . '"';

		// Add custom attributes.
		foreach ( $this->attributes as $key => $value ) {
			$attributes[] = esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
		}

		return implode( ' ', $attributes );
	}

	/**
	 * Render HTML.
	 *
	 * @return string
	 */
	private function render_html() {
		return '<div class="giftflow-html-field">' . $this->html . '</div>';
	}

	/**
	 * Render textfield
	 *
	 * @return string
	 */
	private function render_textfield() {
		$attributes = $this->get_field_attributes();
		return '<input type="text" ' . $attributes . ' value="' . esc_attr( $this->value ) . '" />';
	}

	/**
	 * Render number
	 *
	 * @return string
	 */
	private function render_number() {
		$attributes = $this->get_field_attributes();

		// Add min.
		if ( null !== $this->min ) {
			$attributes .= ' min="' . esc_attr( $this->min ) . '"';
		}

		// Add max.
		if ( null !== $this->max ) {
			$attributes .= ' max="' . esc_attr( $this->max ) . '"';
		}

		// Add step.
		if ( null !== $this->step ) {
			$attributes .= ' step="' . esc_attr( $this->step ) . '"';
		}

		return '<input type="number" ' . $attributes . ' value="' . esc_attr( $this->value ) . '" />';
	}

	/**
	 * Render currency
	 *
	 * @return string
	 */
	private function render_currency() {
		$attributes = $this->get_field_attributes();

		// Add min.
		if ( null !== $this->min ) {
			$attributes .= ' min="' . esc_attr( $this->min ) . '"';
		}

		// Add max.
		if ( null !== $this->max ) {
			$attributes .= ' max="' . esc_attr( $this->max ) . '"';
		}

		// Add step.
		if ( null !== $this->step ) {
			$attributes .= ' step="' . esc_attr( $this->step ) . '"';
		}

		$output = '<div class="giftflow-currency-field">';

		if ( 'before' === $this->currency_position ) {
			$output .= '<span class="giftflow-currency-symbol">' . esc_html( $this->currency_symbol ) . '</span>';
		}

		$output .= '<input type="number" ' . $attributes . ' value="' . esc_attr( $this->value ) . '" />';

		if ( 'after' === $this->currency_position ) {
			$output .= '<span class="giftflow-currency-symbol">' . esc_html( $this->currency_symbol ) . '</span>';
		}

		$output .= '</div>';

		return $output;
	}

	/**
	 * Render select
	 *
	 * @return string
	 */
	private function render_select() {
		$attributes = $this->get_field_attributes();
		$output     = '<select ' . $attributes . '>';

		// Add empty option if no value is selected.
		if ( empty( $this->value ) ) {
			$output .= '<option value="">' . esc_html__( 'Select an option', 'giftflow' ) . '</option>';
		}

		// Add options.
		foreach ( $this->options as $option_value => $option_label ) {
			$selected = selected( $this->value, $option_value, false );
			$output  .= '<option value="' . esc_attr( $option_value ) . '" ' . $selected . '>' . esc_html( $option_label ) . '</option>';
		}

		$output .= '</select>';

		return $output;
	}

	/**
	 * Render multiple select
	 *
	 * @return string
	 */
	private function render_multiple_select() {
		$attributes  = $this->get_field_attributes();
		$attributes .= ' multiple';

		// Convert value to array if it's not already.
		$values = is_array( $this->value ) ? $this->value : array( $this->value );

		$output = '<select ' . $attributes . '>';

		// Add options.
		foreach ( $this->options as $option_value => $option_label ) {
			$selected = in_array( $option_value, $values, true ) ? ' selected' : '';
			$output  .= '<option value="' . esc_attr( $option_value ) . '"' . $selected . '>' . esc_html( $option_label ) . '</option>';
		}

		$output .= '</select>';

		return $output;
	}

	/**
	 * Render textarea
	 *
	 * @return string
	 */
	private function render_textarea() {
		$attributes  = $this->get_field_attributes();
		$attributes .= ' rows="' . esc_attr( $this->rows ) . '"';
		$attributes .= ' cols="' . esc_attr( $this->cols ) . '"';

		return '<textarea ' . $attributes . '>' . esc_textarea( $this->value ) . '</textarea>';
	}

	/**
	 * Render checkbox
	 *
	 * @return string
	 */
	private function render_checkbox() {
		$attributes = $this->get_field_attributes();
		$checked    = checked( $this->value, true, false );

		return '<input type="checkbox" ' . $attributes . ' ' . $checked . ' />';
	}

	/**
	 * Render switch
	 *
	 * @return string
	 */
	private function render_switch() {
		$attributes = $this->get_field_attributes();
		$checked    = checked( $this->value, true, false );

		$output  = '<div class="giftflow-switch">';
		$output .= '<input type="checkbox" ' . $attributes . ' ' . $checked . ' value="1" />';
		$output .= '<span class="giftflow-switch-slider"></span>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Render datetime
	 *
	 * @return string
	 */
	private function render_datetime() {
		$attributes = $this->get_field_attributes();
		$value      = ! empty( $this->value ) ? gmdate( $this->date_format, strtotime( $this->value ) ) : '';

		return '<input type="datetime-local" ' . $attributes . ' value="' . esc_attr( $value ) . '" onfocus="this.showPicker()" />';
	}

	/**
	 * Render color
	 *
	 * @return string
	 */
	private function render_color() {
		$attributes = $this->get_field_attributes();

		$output  = '<div class="giftflow-color-field">';
		$output .= '<input type="color" ' . $attributes . ' value="' . esc_attr( $this->value ) . '" />';
		$output .= '<input type="text" class="giftflow-color-text" value="' . esc_attr( $this->value ) . '" />';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Render gallery
	 *
	 * @return string
	 */
	private function render_gallery() {
		// Start output buffering.
		ob_start();

		// Ensure value is an array.
		$image_ids = $this->value ? explode( ',', $this->value ) : array();

		// Generate a unique ID for the gallery.
		$gallery_id = 'giftflow-gallery-' . $this->id;
		?>
		<div class="giftflow-gallery-field" id="<?php echo esc_attr( $gallery_id ); ?>">
			<!-- Hidden input to store image IDs -->
			<input type="hidden" name="<?php echo esc_attr( $this->name ); ?>" id="<?php echo esc_attr( $this->id ); ?>" value="<?php echo esc_attr( implode( ',', $image_ids ) ); ?>" />
			
			<!-- Gallery preview container -->
			<div class="giftflow-gallery-preview">
				<?php
				// Display selected images.
				if ( ! empty( $image_ids ) ) {
					foreach ( $image_ids as $image_id ) {
						$image_url = wp_get_attachment_image_url( $image_id, $this->gallery_settings['image_size'] );
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
				<button type="button" class="button giftflow-gallery-add"><?php echo esc_html( $this->gallery_settings['button_text'] ); ?></button>
				
				<?php if ( ! empty( $image_ids ) ) : ?>
					<button type="button" class="button giftflow-gallery-remove-all"><?php echo esc_html( $this->gallery_settings['remove_text'] ); ?></button>
				<?php endif; ?>
			</div><!-- End gallery controls -->
			
			<!-- JavaScript for gallery functionality -->
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					var galleryFrame;
					var $gallery = $("#<?php echo esc_js( $gallery_id ); ?>");
					var $input = $gallery.find("input[type=hidden]");
					var $preview = $gallery.find(".giftflow-gallery-preview");
					var $addButton = $gallery.find(".giftflow-gallery-add");
					var $removeAllButton = $gallery.find(".giftflow-gallery-remove-all");
					var maxImages = <?php echo intval( $this->gallery_settings['max_images'] ); ?>;
					
					// Open media frame
					$addButton.on("click", function(e) {
						e.preventDefault();
						
						// If the media frame already exists, reopen it
						if (galleryFrame) {
							galleryFrame.open();
							return;
						}
						
						// Create the media frame
						galleryFrame = wp.media({
							title: "<?php esc_attr_e( 'Select Images', 'giftflow' ); ?>",
							button: {
								text: "<?php esc_attr_e( 'Add to Gallery', 'giftflow' ); ?>"
							},
							multiple: true
						});
						
						// When an image is selected in the media frame
						galleryFrame.on("select", function() {
							var selection = galleryFrame.state().get("selection");
							var currentIds = $input.val() ? $input.val().split(",") : [];
							
							selection.map(function(attachment) {
								attachment = attachment.toJSON();
								
								// Check if max images limit is reached
								if (maxImages > 0 && currentIds.length >= maxImages) {
									return;
								}
								
								// Add image ID to the array if not already present
								if (currentIds.indexOf(attachment.id.toString()) === -1) {
									currentIds.push(attachment.id);
								}
							});
							
							// Update hidden input
							$input.val(currentIds.join(","));
							
							// Update preview
							updateGalleryPreview();
						});
						
						// Open the media frame
						galleryFrame.open();
					});
					
					// Remove single image
					$gallery.on("click", ".giftflow-gallery-remove", function(e) {
						e.preventDefault();
						
						var $image = $(this).parent();
						var imageId = $image.data("id");
						var currentIds = $input.val() ? $input.val().split(",") : [];
						
						// Remove image ID from array
						currentIds = currentIds.filter(function(id) {
							return id != imageId;
						});
						
						// Update hidden input
						$input.val(currentIds.join(","));
						
						// Remove image from preview
						$image.remove();
						
						// Show/hide remove all button
						if (currentIds.length === 0) {
							$removeAllButton.hide();
						}
					});
					
					// Remove all images
					$removeAllButton.on("click", function(e) {
						e.preventDefault();
						
						// Clear hidden input
						$input.val("");
						
						// Clear preview
						$preview.empty();
						
						// Hide remove all button
						$(this).hide();
					});
					
					// Update gallery preview
					function updateGalleryPreview() {
						var currentIds = $input.val() ? $input.val().split(",") : [];
						
						// Clear preview
						$preview.empty();
						
						// Add images to preview
						if (currentIds.length > 0) {
							$.ajax({
								url: ajaxurl,
								type: "POST",
								data: {
									action: "giftflow_get_gallery_images",
									ids: currentIds,
									size: "<?php echo esc_js( $this->gallery_settings['image_size'] ); ?>",
									nonce: "<?php echo esc_js( wp_create_nonce( 'giftflow_gallery_nonce' ) ); ?>"
								},
								success: function(response) {
									if (response.success && response.data) {
										$.each(response.data, function(id, image) {
											var $image = $("<div class='giftflow-gallery-image' data-id='" + id + "'>");
											$image.append("<img src='" + image.url + "' alt='" + image.alt + "' />");
											$image.append("<span class='giftflow-gallery-remove' title='<?php esc_attr_e( 'Remove Image', 'giftflow' ); ?>'>&times;</span>");
											$preview.append($image);
										});
										
										// Show remove all button
										$removeAllButton.show();
									}
								}
							});
						} else {
							// Hide remove all button
							$removeAllButton.hide();
						}
					}
				});
			</script>
		</div><!-- End gallery field -->
		<?php

		// Return the buffered content.
		return ob_get_clean();
	}

	/**
	 * Render Google Maps field
	 *
	 * @return string
	 */
	private function render_googlemap() {
		// Start output buffering.
		ob_start();

		// Generate a unique ID for the map.
		$map_id = 'giftflow-map-' . $this->id;

		// Get the current value.
		$value   = $this->value;
		$address = '';
		$lat     = '';
		$lng     = '';

		// Parse the value if it exists.
		if ( ! empty( $value ) ) {
			$value_data = json_decode( $value, true );
			if ( is_array( $value_data ) ) {
				$address = isset( $value_data['address'] ) ? $value_data['address'] : '';
				$lat     = isset( $value_data['lat'] ) ? $value_data['lat'] : '';
				$lng     = isset( $value_data['lng'] ) ? $value_data['lng'] : '';
			}
		}

		// Get Google Maps API key from settings or use a default.
		$api_key = defined( 'GIFTFLOW_GOOGLE_MAPS_API_KEY' ) ? GIFTFLOW_GOOGLE_MAPS_API_KEY : '';
		?>
		<div class="giftflow-googlemap-field" id="<?php echo esc_attr( $map_id ); ?>">
			<!-- Hidden input to store location data -->
			<input type="hidden" name="<?php echo esc_attr( $this->name ); ?>" id="<?php echo esc_attr( $this->id ); ?>" value="<?php echo esc_attr( $value ); ?>" />
			
			<!-- Address input field -->
			<div class="giftflow-googlemap-address">
				<input type="text" class="giftflow-googlemap-address-input" placeholder="<?php esc_attr_e( 'Enter an address', 'giftflow' ); ?>" value="<?php echo esc_attr( $address ); ?>" />
				<button type="button" class="button giftflow-googlemap-search"><?php esc_html_e( 'Search', 'giftflow' ); ?></button>
			</div>
			
			<!-- Map container -->
			<div class="giftflow-googlemap-container" style="height: 300px; margin-top: 10px;"></div>
			
			<!-- Coordinates display -->
			<div class="giftflow-googlemap-coordinates">
				<p>
					<strong><?php esc_html_e( 'Latitude:', 'giftflow' ); ?></strong> <span class="giftflow-googlemap-lat"><?php echo esc_html( $lat ); ?></span>
					<strong><?php esc_html_e( 'Longitude:', 'giftflow' ); ?></strong> <span class="giftflow-googlemap-lng"><?php echo esc_html( $lng ); ?></span>
				</p>
			</div>
			
			<!-- JavaScript for Google Maps functionality -->
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					var $mapField = $("#<?php echo esc_js( $map_id ); ?>");
					var $input = $mapField.find("input[type=hidden]");
					var $addressInput = $mapField.find(".giftflow-googlemap-address-input");
					var $searchButton = $mapField.find(".giftflow-googlemap-search");
					var $mapContainer = $mapField.find(".giftflow-googlemap-container");
					var $latDisplay = $mapField.find(".giftflow-googlemap-lat");
					var $lngDisplay = $mapField.find(".giftflow-googlemap-lng");
					
					var map, marker, geocoder;
					var defaultLat = <?php echo ! empty( $lat ) ? esc_js( $lat ) : esc_js( '40.7128' ); ?>;
					var defaultLng = <?php echo ! empty( $lng ) ? esc_js( $lng ) : esc_js( '-74.0060' ); ?>;
					var defaultZoom = <?php echo ! empty( $lat ) && ! empty( $lng ) ? esc_js( '15' ) : esc_js( '2' ); ?>;
					
					// Initialize the map
					function initMap() {
						// Create map
						map = new google.maps.Map($mapContainer[0], {
							center: { lat: parseFloat(defaultLat), lng: parseFloat(defaultLng) },
							zoom: defaultZoom,
							mapTypeControl: true,
							streetViewControl: true,
							fullscreenControl: true
						});
						
						// Create geocoder
						geocoder = new google.maps.Geocoder();
						
						// Create marker
						marker = new google.maps.Marker({
							map: map,
							draggable: true,
							position: { lat: parseFloat(defaultLat), lng: parseFloat(defaultLng) }
						});
						
						// Add marker drag event
						marker.addListener('dragend', function() {
							updateLocationFromMarker();
						});
						
						// Add map click event
						map.addListener('click', function(event) {
							marker.setPosition(event.latLng);
							updateLocationFromMarker();
						});
						
						// If we have coordinates, reverse geocode to get the address
						if (defaultLat && defaultLng) {
							reverseGeocode({ lat: parseFloat(defaultLat), lng: parseFloat(defaultLng) });
						}
					}
					
					// Update location from marker position
					function updateLocationFromMarker() {
						var position = marker.getPosition();
						reverseGeocode(position);
					}
					
					// Reverse geocode to get address from coordinates
					function reverseGeocode(position) {
						geocoder.geocode({ location: position }, function(results, status) {
							if (status === 'OK') {
								if (results[0]) {
									$addressInput.val(results[0].formatted_address);
									updateLocationData(position.lat(), position.lng(), results[0].formatted_address);
								}
							}
						});
					}
					
					// Update location data
					function updateLocationData(lat, lng, address) {
						$latDisplay.text(lat);
						$lngDisplay.text(lng);
						
						var locationData = {
							lat: lat,
							lng: lng,
							address: address
						};
						
						$input.val(JSON.stringify(locationData));
					}
					
					// Search for address
					$searchButton.on('click', function() {
						var address = $addressInput.val();
						if (address) {
							geocoder.geocode({ address: address }, function(results, status) {
								if (status === 'OK') {
									var position = results[0].geometry.location;
									map.setCenter(position);
									map.setZoom(15);
									marker.setPosition(position);
									updateLocationData(position.lat(), position.lng(), results[0].formatted_address);
								} else {
									alert('<?php esc_html_e( 'Geocode was not successful for the following reason: ', 'giftflow' ); ?>' + status);
								}
							});
						}
					});
					
					// Allow pressing Enter in the address field to search
					$addressInput.on('keypress', function(e) {
						if (e.which === 13) {
							e.preventDefault();
							$searchButton.click();
						}
					});
					
					// Load Google Maps API and initialize the map
					if (typeof google === 'undefined') {
						var script = document.createElement('script');
						script.src = 'https://maps.googleapis.com/maps/api/js?key=<?php echo esc_js( $api_key ); ?>&callback=initMap';
						script.async = true;
						script.defer = true;
						document.head.appendChild(script);
						
						// Define the callback function
						window.initMap = initMap;
					} else {
						initMap();
					}
				});
			</script>
		</div><!-- End Google Maps field -->
		<?php

		// Return the buffered content.
		return ob_get_clean();
	}

	/**
	 * Render repeater field
	 *
	 * @return string
	 */
	private function render_repeater() {
		// Start output buffering.
		ob_start();

		// Get the repeater settings.
		$repeater_settings = $this->repeater_settings;
		$fields            = $repeater_settings['fields'];
		$button_text       = $repeater_settings['button_text'];
		$remove_text       = $repeater_settings['remove_text'];
		$min_rows          = $repeater_settings['min_rows'];
		$max_rows          = $repeater_settings['max_rows'];
		$row_label         = $repeater_settings['row_label'];

		// Ensure value is an array.
		$values = is_array( $this->value ) ? $this->value : array();
		if ( empty( $values ) && $min_rows > 0 ) {
			// Initialize with empty rows if minimum rows required.
			$values = array_fill( 0, $min_rows, array() );
		}

		// Generate a unique ID for the repeater.
		$repeater_id = 'giftflow-repeater-' . $this->id;
		?>
		<div class="giftflow-repeater-field" id="<?php echo esc_attr( $repeater_id ); ?>">
			<!-- Hidden input to store all values -->
			<input type="hidden" name="<?php echo esc_attr( $this->name ); ?>" id="<?php echo esc_attr( $this->id ); ?>" value="<?php echo esc_attr( wp_json_encode( $values ) ); ?>" />
			
			<!-- Repeater rows container -->
			<div class="giftflow-repeater-rows">
				<?php foreach ( $values as $row_index => $row_values ) : ?>
					<div class="giftflow-repeater-row" data-index="<?php echo esc_attr( $row_index ); ?>">
						<div class="giftflow-repeater-row-header">
							<span class="giftflow-repeater-row-title"><?php echo esc_html( $row_label . ' ' . ( $row_index + 1 ) ); ?></span>
							<button type="button" class="button giftflow-repeater-remove-row"><?php echo esc_html( $remove_text ); ?></button>
						</div>
						<div class="giftflow-repeater-row-content">
							<?php
							foreach ( $fields as $field_id => $field_args ) :
								$field_value = isset( $row_values[ $field_id ] ) ? $row_values[ $field_id ] : '';
								$field_name  = $this->name . '[' . $row_index . '][' . $field_id . ']';
								$field_id    = $this->id . '_' . $row_index . '_' . $field_id;

								// Create field instance.
								$field = new GiftFlow_Field(
									$field_id,
									$field_name,
									$field_args['type'],
									array_merge(
										$field_args,
										array(
											'value' => $field_value,
											'wrapper_classes' => array( 'giftflow-repeater-field' ),
										)
									)
								);

								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $field->render();
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
			
			<!-- JavaScript for repeater functionality -->
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					var $repeater = $("#<?php echo esc_js( $repeater_id ); ?>");
					var $rows = $repeater.find(".giftflow-repeater-rows");
					var $addButton = $repeater.find(".giftflow-repeater-add-row");
					var $hiddenInput = $repeater.find("input[type=hidden]");
					var maxRows = <?php echo intval( $max_rows ); ?>;
					var rowTemplate = <?php echo wp_json_encode( $this->get_repeater_row_template( $fields ) ); ?>;
					
					// Add new row
					$addButton.on("click", function() {
						if (maxRows > 0 && $rows.children().length >= maxRows) {
							return;
						}
						
						var newIndex = $rows.children().length;
						var newRow = $(rowTemplate.replace(/__INDEX__/g, newIndex));
						$rows.append(newRow);
						
						// Update hidden input
						updateHiddenInput();
						
						// Disable add button if max rows reached
						if (maxRows > 0 && $rows.children().length >= maxRows) {
							$addButton.prop("disabled", true);
						}
					});
					
					// Remove row
					$repeater.on("click", ".giftflow-repeater-remove-row", function(e) {
						e.preventDefault();
						var $row = $(this).closest(".giftflow-repeater-row");
						$row.remove();
						
						// Update row indices
						$rows.find(".giftflow-repeater-row").each(function(index) {
							$(this).attr("data-index", index);
							$(this).find(".giftflow-repeater-row-title").text("<?php echo esc_js( $row_label ); ?> " + (index + 1));
							$(this).find("input, select, textarea").each(function() {
								var name = $(this).attr("name");
								name = name.replace(/\[\d+\]/, "[" + index + "]");
								$(this).attr("name", name);
							});
						});
						
						// Update hidden input
						updateHiddenInput();
						
						// Enable add button
						$addButton.prop("disabled", false);
					});
					
					// Update values when fields change
					$repeater.on("change", "input, select, textarea", function() {
						updateHiddenInput();
					});
					
					// Function to update hidden input with all values
					function updateHiddenInput() {
						var values = [];
						$rows.find(".giftflow-repeater-row").each(function() {
							var rowValues = {};
							$(this).find("input, select, textarea").each(function() {
								var name = $(this).attr("name");
								var matches = name.match(/\[(\d+)\]\[([^\]]+)\]/);
								if (matches) {
									var fieldId = matches[2];
									if ($(this).attr("type") === "checkbox" || $(this).attr("type") === "switch") {
										rowValues[fieldId] = $(this).is(":checked");
									} else {
										rowValues[fieldId] = $(this).val();
									}
								}
							});
							values.push(rowValues);
						});
						$hiddenInput.val(JSON.stringify(values));
					}
				});
			</script>
		</div>
		<?php

		// Return the buffered content.
		return ob_get_clean();
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
					<!-- __INDEX_PLUS_1__ -->
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

					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $field->render();
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
		// Start output buffering.
		ob_start();

		// Get accordion settings.
		$settings      = $this->accordion_settings;
		$is_open       = $settings['is_open'] ? 'open' : '';
		$icon          = $settings['icon'];
		$icon_position = $settings['icon_position'];
		$label         = $settings['label'];

		// Generate unique ID for the accordion.
		$accordion_id = 'giftflow-accordion-' . $this->id;
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
				<?php if ( ! empty( $this->description ) ) : ?>
					<p class="description"><?php echo esc_html( $this->description ); ?></p>
				<?php endif; ?>
				
				<?php if ( ! empty( $settings['fields'] ) ) : ?>
					<div class="giftflow-accordion-fields">
						<?php
						foreach ( $settings['fields'] as $field_id => $field_args ) :
							$field_value = isset( $field_args['value'] ) ? $field_args['value'] : '';
							$field_name  = $this->name . '[' . $field_id . ']';
							$field_id    = $this->id . '_' . $field_id;

							// Create field instance.
							$field = new GiftFlow_Field(
								$field_id,
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

							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo $field->render();
						endforeach;
						?>
					</div>
				<?php endif; ?>
				
				<?php
				if ( isset( $this->content ) && is_callable( $this->content ) ) :
					call_user_func( $this->content );
				endif;
				?>
			</div>
		</div>
		
		<!-- JavaScript for accordion functionality -->
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				var $accordion = $("#<?php echo esc_js( $accordion_id ); ?>");
				var $header = $accordion.find(".giftflow-accordion-header");
				var $content = $accordion.find(".giftflow-accordion-content");
				var $icon = $header.find(".dashicons");
				
				// Toggle accordion
				$header.on("click", function() {
					$accordion.toggleClass("open");
					$content.slideToggle(200);
					
					// Rotate icon
					if ($accordion.hasClass("open")) {
						$icon.css("transform", "rotate(180deg)");
					} else {
						$icon.css("transform", "rotate(0deg)");
					}
				});
				
				// Initialize state
				if ($accordion.hasClass("open")) {
					$content.show();
					$icon.css("transform", "rotate(180deg)");
				} else {
					$content.hide();
					$icon.css("transform", "rotate(0deg)");
				}
			});
		</script>
		
		<!-- Styles for accordion -->
		<style type="text/css">
			{
				border: 1px solid #ddd;
				margin-bottom: 10px;
				border-radius: 4px;
			}
			
			.giftflow-accordion-header {
				background: #f5f5f5;
				padding: 10px 15px;
				cursor: pointer;
				display: flex;
				align-items: center;
				justify-content: space-between;
			}
			
			.giftflow-accordion-header h3 {
				margin: 0;
				font-size: 14px;
				font-weight: 600;
			}
			
			.giftflow-accordion-header .dashicons {
				transition: transform 0.2s ease;
			}
			
			.giftflow-accordion-content {
				padding: 15px;
				display: none;
				background: #fafafa;
			}
			
			.giftflow-accordion-content .description {
				margin-top: 0;
			}
			
			.giftflow-accordion-fields {
				margin-top: 15px;
			}
			
			.giftflow-accordion-field {
				margin-bottom: 15px;
			}
			
			.giftflow-accordion-field:last-child {
				margin-bottom: 0;
			}
		</style>
		<?php

		// Return the buffered content.
		return ob_get_clean();
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
