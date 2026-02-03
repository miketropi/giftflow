# Campaign Grid Template Hooks

This document provides a comprehensive reference for all hooks available in the `campaign-grid.php` template. These hooks allow developers to customize the campaign grid display, add custom content, and modify template behavior.

## Overview

The campaign grid template (`templates/campaign-grid.php`) displays a grid of campaign cards with featured images, titles, excerpts, progress bars, and pagination. The template includes multiple action and filter hooks at strategic points to enable customization without modifying the core template file.

## Action Hooks

Action hooks allow you to inject custom HTML, execute functions, or modify output at specific points in the template rendering process.

### `giftflow_campaign_grid_before`

Fires before the main grid wrapper when campaigns are found.

**Parameters:**
- `$campaigns` (array): Array of campaign data
- `$total` (int): Total number of campaigns
- `$pages` (int): Total number of pages
- `$current_page` (int): Current page number

**Example:**
```php
add_action( 'giftflow_campaign_grid_before', function( $campaigns, $total, $pages, $current_page ) {
    echo '<div class="campaign-grid-header">';
    echo '<p>Showing ' . count( $campaigns ) . ' of ' . $total . ' campaigns</p>';
    echo '</div>';
}, 10, 4 );
```

### `giftflow_campaign_grid_before_container`

Fires inside the wrapper, before the grid container div.

**Parameters:**
- `$campaigns` (array): Array of campaign data
- `$total` (int): Total number of campaigns
- `$pages` (int): Total number of pages
- `$current_page` (int): Current page number

**Example:**
```php
add_action( 'giftflow_campaign_grid_before_container', function( $campaigns, $total, $pages, $current_page ) {
    // Add custom filters or sorting controls
    echo '<div class="campaign-filters">Filter controls here</div>';
}, 10, 4 );
```

### `giftflow_campaign_grid_before_item`

Fires before each campaign article element in the loop.

**Parameters:**
- `$campaign` (array): Single campaign data array
- `$campaign_id` (int): Campaign post ID

**Example:**
```php
add_action( 'giftflow_campaign_grid_before_item', function( $campaign, $campaign_id ) {
    // Add custom wrapper or tracking code
    echo '<div data-campaign-id="' . esc_attr( $campaign_id ) . '">';
}, 10, 2 );
```

### `giftflow_campaign_grid_before_item_content`

Fires after the campaign image, before the content div.

**Parameters:**
- `$campaign` (array): Single campaign data array
- `$campaign_id` (int): Campaign post ID

**Example:**
```php
add_action( 'giftflow_campaign_grid_before_item_content', function( $campaign, $campaign_id ) {
    // Add custom badge or featured indicator
    if ( isset( $campaign['featured'] ) && $campaign['featured'] ) {
        echo '<span class="featured-badge">Featured</span>';
    }
}, 10, 2 );
```

### `giftflow_campaign_grid_after_item_content`

Fires after the footer div, before the closing article tag.

**Parameters:**
- `$campaign` (array): Single campaign data array
- `$campaign_id` (int): Campaign post ID

**Example:**
```php
add_action( 'giftflow_campaign_grid_after_item_content', function( $campaign, $campaign_id ) {
    // Add custom CTA or additional information
    echo '<div class="custom-cta">';
    echo '<a href="' . esc_url( $campaign['permalink'] ) . '?ref=special">Special Offer</a>';
    echo '</div>';
}, 10, 2 );
```

### `giftflow_campaign_grid_after_item`

Fires after each campaign article element closes.

**Parameters:**
- `$campaign` (array): Single campaign data array
- `$campaign_id` (int): Campaign post ID

**Example:**
```php
add_action( 'giftflow_campaign_grid_after_item', function( $campaign, $campaign_id ) {
    // Close custom wrapper or add tracking
    echo '</div>';
}, 10, 2 );
```

### `giftflow_campaign_grid_after_container`

Fires after the grid container closes, before pagination.

**Parameters:**
- `$campaigns` (array): Array of campaign data
- `$total` (int): Total number of campaigns
- `$pages` (int): Total number of pages
- `$current_page` (int): Current page number

**Example:**
```php
add_action( 'giftflow_campaign_grid_after_container', function( $campaigns, $total, $pages, $current_page ) {
    // Add custom content after grid
    echo '<div class="campaign-grid-footer">';
    echo '<p>Total raised across all campaigns: $' . number_format( $total_raised, 2 ) . '</p>';
    echo '</div>';
}, 10, 4 );
```

### `giftflow_campaign_grid_before_pagination`

Fires before the pagination navigation (only when `$pages > 1`).

**Parameters:**
- `$current_page` (int): Current page number
- `$pages` (int): Total number of pages

**Example:**
```php
add_action( 'giftflow_campaign_grid_before_pagination', function( $current_page, $pages ) {
    echo '<div class="pagination-info">';
    echo 'Page ' . $current_page . ' of ' . $pages;
    echo '</div>';
}, 10, 2 );
```

### `giftflow_campaign_grid_after_pagination`

Fires after the pagination navigation.

**Parameters:**
- `$current_page` (int): Current page number
- `$pages` (int): Total number of pages

**Example:**
```php
add_action( 'giftflow_campaign_grid_after_pagination', function( $current_page, $pages ) {
    // Add custom navigation or additional controls
    echo '<div class="pagination-helper">Jump to page controls</div>';
}, 10, 2 );
```

### `giftflow_campaign_grid_after`

Fires after the main grid wrapper closes.

**Parameters:**
- `$campaigns` (array): Array of campaign data
- `$total` (int): Total number of campaigns
- `$pages` (int): Total number of pages
- `$current_page` (int): Current page number

**Example:**
```php
add_action( 'giftflow_campaign_grid_after', function( $campaigns, $total, $pages, $current_page ) {
    // Add custom scripts or tracking code
    echo '<script>trackCampaignGridView(' . count( $campaigns ) . ');</script>';
}, 10, 4 );
```

### `giftflow_campaign_grid_before_empty`

Fires inside the empty state wrapper, before the empty message (when no campaigns found).

**Parameters:** None

**Example:**
```php
add_action( 'giftflow_campaign_grid_before_empty', function() {
    echo '<div class="empty-state-icon">';
    echo '<svg>...</svg>';
    echo '</div>';
} );
```

### `giftflow_campaign_grid_after_empty`

Fires inside the empty state wrapper, after the empty message (when no campaigns found).

**Parameters:** None

**Example:**
```php
add_action( 'giftflow_campaign_grid_after_empty', function() {
    echo '<div class="empty-state-cta">';
    echo '<a href="/create-campaign">Create Your First Campaign</a>';
    echo '</div>';
} );
```

## Filter Hooks

Filter hooks allow you to modify data, classes, text, and configuration values before they are used in the template.

### `giftflow_campaign_grid_wrapper_class`

Filter the CSS classes applied to the main grid wrapper.

**Parameters:**
- `$wrapper_class` (string): Current wrapper class string
- `$campaigns` (array): Array of campaign data
- `$total` (int): Total number of campaigns
- `$pages` (int): Total number of pages
- `$current_page` (int): Current page number

**Returns:** (string) Modified wrapper class string

**Example:**
```php
add_filter( 'giftflow_campaign_grid_wrapper_class', function( $class, $campaigns, $total, $pages, $current_page ) {
    // Add custom class based on conditions
    if ( $pages > 1 ) {
        $class .= ' has-pagination';
    }
    if ( count( $campaigns ) < 3 ) {
        $class .= ' few-campaigns';
    }
    return $class;
}, 10, 5 );
```

### `giftflow_campaign_grid_empty_message`

Filter the empty state message text.

**Parameters:**
- `$message` (string): Current empty message text

**Returns:** (string) Modified message text

**Example:**
```php
add_filter( 'giftflow_campaign_grid_empty_message', function( $message ) {
    return 'No campaigns are currently available. Please check back soon!';
} );
```

### `giftflow_campaign_grid_excerpt_length`

Filter the number of words in the campaign excerpt.

**Parameters:**
- `$length` (int): Current excerpt length (default: 20)
- `$campaign_id` (int): Campaign post ID

**Returns:** (int) Modified excerpt length

**Example:**
```php
add_filter( 'giftflow_campaign_grid_excerpt_length', function( $length, $campaign_id ) {
    // Show longer excerpts for featured campaigns
    if ( get_post_meta( $campaign_id, '_featured', true ) ) {
        return 40;
    }
    return $length;
}, 10, 2 );
```

### `giftflow_campaign_grid_pagination_args`

Filter the arguments passed to `paginate_links()` function.

**Parameters:**
- `$pagination_args` (array): Current pagination arguments
- `$current_page` (int): Current page number
- `$pages` (int): Total number of pages

**Returns:** (array) Modified pagination arguments

**Example:**
```php
add_filter( 'giftflow_campaign_grid_pagination_args', function( $args, $current_page, $pages ) {
    // Customize pagination text
    $args['prev_text'] = __( '← Previous', 'your-textdomain' );
    $args['next_text'] = __( 'Next →', 'your-textdomain' );
    
    // Change pagination type
    $args['type'] = 'plain';
    
    return $args;
}, 10, 3 );
```

## Hook Execution Order

Understanding the hook execution order helps you know where to place your customizations:

### When Campaigns Are Found:
1. `giftflow_campaign_grid_before`
2. Grid wrapper opens
3. `giftflow_campaign_grid_before_container`
4. Container opens
5. **For each campaign:**
   - `giftflow_campaign_grid_before_item`
   - Article opens
   - Image section
   - `giftflow_campaign_grid_before_item_content`
   - Content section (categories, title, excerpt, progress, location, footer)
   - `giftflow_campaign_grid_after_item_content`
   - Article closes
   - `giftflow_campaign_grid_after_item`
6. Container closes
7. `giftflow_campaign_grid_after_container`
8. **If pagination exists:**
   - `giftflow_campaign_grid_before_pagination`
   - Pagination nav
   - `giftflow_campaign_grid_after_pagination`
9. Grid wrapper closes
10. `giftflow_campaign_grid_after`

### When No Campaigns Found:
1. Empty wrapper opens
2. `giftflow_campaign_grid_before_empty`
3. Empty message (filtered by `giftflow_campaign_grid_empty_message`)
4. `giftflow_campaign_grid_after_empty`
5. Empty wrapper closes

## Common Use Cases

### Adding Custom Badges or Labels

```php
add_action( 'giftflow_campaign_grid_before_item_content', function( $campaign, $campaign_id ) {
    $status = get_post_meta( $campaign_id, '_campaign_status', true );
    if ( 'urgent' === $status ) {
        echo '<span class="campaign-badge urgent">Urgent</span>';
    }
}, 10, 2 );
```

### Modifying Grid Layout Based on Campaign Count

```php
add_filter( 'giftflow_campaign_grid_wrapper_class', function( $class, $campaigns ) {
    $count = count( $campaigns );
    if ( $count === 1 ) {
        $class .= ' single-campaign';
    } elseif ( $count < 4 ) {
        $class .= ' few-campaigns';
    } else {
        $class .= ' many-campaigns';
    }
    return $class;
}, 10, 2 );
```

### Adding Analytics Tracking

```php
add_action( 'giftflow_campaign_grid_after', function( $campaigns, $total, $pages, $current_page ) {
    ?>
    <script>
        if ( typeof gtag !== 'undefined' ) {
            gtag( 'event', 'campaign_grid_view', {
                'campaign_count': <?php echo count( $campaigns ); ?>,
                'total_campaigns': <?php echo $total; ?>,
                'page': <?php echo $current_page; ?>
            } );
        }
    </script>
    <?php
}, 10, 4 );
```

### Customizing Empty State

```php
add_action( 'giftflow_campaign_grid_before_empty', function() {
    echo '<div class="empty-state-illustration">';
    echo '<img src="' . get_template_directory_uri() . '/images/no-campaigns.svg" alt="">';
    echo '</div>';
} );

add_filter( 'giftflow_campaign_grid_empty_message', function( $message ) {
    return 'We\'re currently setting up new campaigns. Check back soon!';
} );

add_action( 'giftflow_campaign_grid_after_empty', function() {
    echo '<div class="empty-state-actions">';
    echo '<a href="/subscribe" class="button">Get Notified</a>';
    echo '</div>';
} );
```

### Adding Custom Campaign Data

```php
add_action( 'giftflow_campaign_grid_after_item_content', function( $campaign, $campaign_id ) {
    $donor_count = get_post_meta( $campaign_id, '_donor_count', true );
    if ( $donor_count ) {
        echo '<div class="campaign-donor-count">';
        echo '<span class="icon">👥</span>';
        echo '<span>' . sprintf( _n( '%d donor', '%d donors', $donor_count, 'giftflow' ), $donor_count ) . '</span>';
        echo '</div>';
    }
}, 10, 2 );
```

## Best Practices

1. **Use Appropriate Priorities**: Use priority 10 as default, adjust if you need to run before or after other hooks.

2. **Always Escape Output**: When outputting HTML, use WordPress escaping functions:
   - `esc_html()` for text
   - `esc_attr()` for attributes
   - `esc_url()` for URLs
   - `wp_kses_post()` for HTML content

3. **Check Data Availability**: Always check if data exists before using it:
   ```php
   if ( isset( $campaign['custom_field'] ) && ! empty( $campaign['custom_field'] ) ) {
       // Use the field
   }
   ```

4. **Performance Considerations**: 
   - Avoid heavy database queries in hooks that fire for each campaign item
   - Cache expensive operations
   - Use transients for frequently accessed data

5. **Maintain Template Structure**: When adding custom HTML, maintain semantic structure and accessibility standards.

6. **Document Your Hooks**: If creating custom hooks within your hook callbacks, document them clearly.

## Related Hooks

The campaign grid template is loaded via the shortcode system, which also provides hooks:

- `giftflow_form_campaign_grid_atts` - Filter shortcode attributes before template load
- `giftflow_campaigns_query_args` - Filter query arguments for fetching campaigns
- `giftflow_campaigns_data` - Filter campaign data array before template render

See the main GiftFlow documentation for more information on these hooks.
