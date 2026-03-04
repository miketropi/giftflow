# GiftFlow Field Builder

The GiftFlow Field Builder provides a flexible and easy-to-use way to create and render WordPress custom meta fields. This documentation will guide you through using the `GiftFlow_Field` class to create various types of fields for your WordPress site.

## Table of Contents

- [Installation](#installation)
- [Basic Usage](#basic-usage)
- [Field Types](#field-types)
- [Field Properties](#field-properties)
- [Examples](#examples)
- [Integration with Meta Boxes](#integration-with-meta-boxes)
- [Styling](#styling)
- [Advanced Usage](#advanced-usage)

## Installation

The Field Builder is included in the GiftFlow plugin. No additional installation is required.

## Basic Usage

To create a field, instantiate the `GiftFlow_Field` class with the required parameters:

```php
$field = new GiftFlow_Field(
    'field_id',      // Unique ID for the field
    'field_name',    // Name attribute for the field
    'field_type',    // Type of field (see Field Types below)
    $args            // Additional arguments (see Field Properties below)
);

// Render the field
$field->render();
```

## Field Types

The Field Builder supports the following field types:

| Type | Description |
|------|-------------|
| `textfield` | Standard text input field |
| `number` | Numeric input field with min/max/step support |
| `currency` | Currency input field with symbol and position options |
| `select` | Dropdown select field |
| `multiple_select` | Multi-select dropdown field |
| `textarea` | Multi-line text input field |
| `checkbox` | Checkbox input field |
| `switch` | Toggle switch (on/off) |
| `datetime` | Date and time picker |
| `color` | Color picker with hex value display |
| `gallery` | Multiple image selector using WordPress media library |
| `googlemap` | Interactive Google Maps field for location selection |

## Field Properties

The following properties can be set when creating a field:

| Property | Type | Description | Default |
|----------|------|-------------|---------|
| `label` | string | Field label | '' |
| `description` | string | Field description | '' |
| `default` | mixed | Default value | '' |
| `value` | mixed | Current value | default value |
| `options` | array | Options for select/multiple_select fields | [] |
| `attributes` | array | Custom HTML attributes | [] |
| `classes` | array | CSS classes for the field | [] |
| `wrapper_classes` | array | CSS classes for the field wrapper | [] |
| `required` | bool | Whether the field is required | false |
| `disabled` | bool | Whether the field is disabled | false |
| `placeholder` | string | Placeholder text | '' |
| `min` | int/float | Minimum value (for number/currency) | null |
| `max` | int/float | Maximum value (for number/currency) | null |
| `step` | int/float | Step value (for number/currency) | null |
| `currency_symbol` | string | Currency symbol (for currency) | '$' |
| `currency_position` | string | Position of currency symbol (before/after) | 'before' |
| `rows` | int | Number of rows (for textarea) | 5 |
| `cols` | int | Number of columns (for textarea) | 50 |
| `date_format` | string | Date format (for datetime) | 'Y-m-d H:i:s' |
| `time_format` | string | Time format (for datetime) | 'H:i:s' |
| `color_format` | string | Color format (for color) | 'hex' |
| `gallery_settings` | array | Settings for gallery field | See below |
| `googlemap_settings` | array | Settings for Google Maps field | See below |

### Gallery Settings

The `gallery_settings` property is an array with the following options:

| Option | Type | Description | Default |
|--------|------|-------------|---------|
| `max_images` | int | Maximum number of images allowed (0 for unlimited) | 0 |
| `image_size` | string | Size of preview images (thumbnail, medium, large, full) | 'thumbnail' |
| `button_text` | string | Text for the add images button | 'Select Images' |
| `remove_text` | string | Text for the remove all button | 'Remove All' |

### Google Maps Settings

The `googlemap_settings` property is an array with the following options:

| Option | Type | Description | Default |
|--------|------|-------------|---------|
| `default_lat` | float | Default latitude | 0 |
| `default_lng` | float | Default longitude | 0 |
| `default_zoom` | int | Default zoom level (0-20) | 12 |
| `map_height` | string | Height of the map container | '400px' |
| `search_button_text` | string | Text for the search button | 'Search' |

## Examples

### Text Field

```php
$text_field = new GiftFlow_Field(
    'my_text_field',
    'my_text_field',
    'textfield',
    array(
        'label' => 'My Text Field',
        'description' => 'Enter some text',
        'placeholder' => 'Enter text here',
        'required' => true,
    )
);
$text_field->render();
```

### Select Field

```php
$select_field = new GiftFlow_Field(
    'my_select_field',
    'my_select_field',
    'select',
    array(
        'label' => 'My Select Field',
        'options' => array(
            'option1' => 'Option 1',
            'option2' => 'Option 2',
            'option3' => 'Option 3',
        ),
        'default' => 'option1',
    )
);
$select_field->render();
```

### Currency Field

```php
$currency_field = new GiftFlow_Field(
    'my_currency_field',
    'my_currency_field',
    'currency',
    array(
        'label' => 'My Currency Field',
        'currency_symbol' => '€',
        'currency_position' => 'after',
        'min' => 0,
        'max' => 1000,
        'step' => 0.01,
    )
);
$currency_field->render();
```

### Multiple Select Field

```php
$multiple_select_field = new GiftFlow_Field(
    'my_multiple_select_field',
    'my_multiple_select_field[]', // Note the [] for multiple values
    'multiple_select',
    array(
        'label' => 'My Multiple Select Field',
        'options' => array(
            'option1' => 'Option 1',
            'option2' => 'Option 2',
            'option3' => 'Option 3',
        ),
        'default' => array('option1', 'option2'),
    )
);
$multiple_select_field->render();
```

### Textarea Field

```php
$textarea_field = new GiftFlow_Field(
    'my_textarea_field',
    'my_textarea_field',
    'textarea',
    array(
        'label' => 'My Textarea Field',
        'description' => 'Enter a longer text',
        'rows' => 10,
        'cols' => 50,
    )
);
$textarea_field->render();
```

### Checkbox Field

```php
$checkbox_field = new GiftFlow_Field(
    'my_checkbox_field',
    'my_checkbox_field',
    'checkbox',
    array(
        'label' => 'My Checkbox Field',
        'description' => 'Check this box',
        'default' => true,
    )
);
$checkbox_field->render();
```

### Switch Field

```php
$switch_field = new GiftFlow_Field(
    'my_switch_field',
    'my_switch_field',
    'switch',
    array(
        'label' => 'My Switch Field',
        'description' => 'Toggle this switch',
        'default' => false,
    )
);
$switch_field->render();
```

### Datetime Field

```php
$datetime_field = new GiftFlow_Field(
    'my_datetime_field',
    'my_datetime_field',
    'datetime',
    array(
        'label' => 'My Datetime Field',
        'description' => 'Select date and time',
        'date_format' => 'Y-m-d',
        'time_format' => 'H:i',
    )
);
$datetime_field->render();
```

### Color Field

```php
$color_field = new GiftFlow_Field(
    'my_color_field',
    'my_color_field',
    'color',
    array(
        'label' => 'My Color Field',
        'description' => 'Select a color',
        'default' => '#000000',
    )
);
$color_field->render();
```

### Gallery Field

```php
$gallery_field = new GiftFlow_Field(
    'my_gallery_field',
    'my_gallery_field',
    'gallery',
    array(
        'label' => 'My Gallery Field',
        'description' => 'Select multiple images for your gallery',
        'gallery_settings' => array(
            'max_images' => 10, // Set to 0 for unlimited
            'image_size' => 'medium', // thumbnail, medium, large, full
            'button_text' => 'Select Images',
            'remove_text' => 'Remove All',
        ),
    )
);
$gallery_field->render();
```

### Google Maps Field

```php
$googlemap_field = new GiftFlow_Field(
    'my_googlemap_field',
    'my_googlemap_field',
    'googlemap',
    array(
        'label' => 'My Google Maps Field',
        'description' => 'Enter an address or select a location on the map',
        'googlemap_settings' => array(
            'default_lat' => 40.7128,
            'default_lng' => -74.0060,
            'default_zoom' => 12,
            'map_height' => '400px',
            'search_button_text' => 'Search',
        ),
    )
);
$googlemap_field->render();
```

## Integration with Meta Boxes

Here's how to integrate the Field Builder with WordPress meta boxes:

```php
// Add meta box
function add_custom_meta_box() {
    add_meta_box(
        'my_custom_meta_box',
        'My Custom Meta Box',
        'render_custom_meta_box',
        'post', // Post type
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_custom_meta_box');

// Render meta box
function render_custom_meta_box($post) {
    // Add nonce for security
    wp_nonce_field('my_custom_meta_box', 'my_custom_meta_box_nonce');
    
    // Get saved values
    $text_value = get_post_meta($post->ID, 'my_text_field', true);
    $select_value = get_post_meta($post->ID, 'my_select_field', true);
    
    // Create fields
    $text_field = new GiftFlow_Field(
        'my_text_field',
        'my_text_field',
        'textfield',
        array(
            'label' => 'My Text Field',
            'value' => $text_value,
        )
    );
    
    $select_field = new GiftFlow_Field(
        'my_select_field',
        'my_select_field',
        'select',
        array(
            'label' => 'My Select Field',
            'options' => array(
                'option1' => 'Option 1',
                'option2' => 'Option 2',
                'option3' => 'Option 3',
            ),
            'value' => $select_value,
        )
    );
    
    // Render fields
    $text_field->render();
    $select_field->render();
}

// Save meta box data
function save_custom_meta_box($post_id) {
    // Check if nonce is set
    if (!isset($_POST['my_custom_meta_box_nonce'])) {
        return;
    }
    
    // Verify nonce
    if (!wp_verify_nonce($_POST['my_custom_meta_box_nonce'], 'my_custom_meta_box')) {
        return;
    }
    
    // If this is an autosave, don't do anything
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Save text field
    if (isset($_POST['my_text_field'])) {
        update_post_meta($post_id, 'my_text_field', sanitize_text_field($_POST['my_text_field']));
    }
    
    // Save select field
    if (isset($_POST['my_select_field'])) {
        update_post_meta($post_id, 'my_select_field', sanitize_text_field($_POST['my_select_field']));
    }
}
add_action('save_post', 'save_custom_meta_box');
```

## Styling

The Field Builder adds CSS classes to help with styling:

- Each field is wrapped in a div with classes `giftflow-field` and `giftflow-field-{type}`
- The input element has the class `giftflow-field-input`
- Currency fields have additional classes for the currency symbol
- Switch fields have additional classes for the switch slider

You can add custom classes using the `classes` and `wrapper_classes` properties.

## Advanced Usage

### Getting and Setting Field Values

```php
// Get field value
$value = $field->get_value();

// Set field value
$field->set_value('new value');
```

### Getting Field Properties

```php
// Get field ID
$id = $field->get_id();

// Get field name
$name = $field->get_name();

// Get field type
$type = $field->get_type();
```

### Custom Attributes

You can add custom HTML attributes to fields:

```php
$field = new GiftFlow_Field(
    'my_field',
    'my_field',
    'textfield',
    array(
        'attributes' => array(
            'data-custom' => 'value',
            'onchange' => 'myFunction()',
        ),
    )
);
```

### Custom CSS Classes

You can add custom CSS classes to fields:

```php
$field = new GiftFlow_Field(
    'my_field',
    'my_field',
    'textfield',
    array(
        'classes' => array('my-custom-class', 'another-class'),
        'wrapper_classes' => array('my-custom-wrapper-class'),
    )
);
``` 