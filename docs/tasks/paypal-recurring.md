# PayPal Recurring Payments — Feature Development Roadmap

---

## Table of Contents

1. [Current State Summary](#1-current-state-summary)
2. [Feature Scope](#2-feature-scope)
3. [PayPal API Strategy](#3-paypal-api-strategy)
4. [Data Schema](#4-data-schema)
5. [Phase 1 — Gateway: Add Recurring Support Flag](#phase-1--gateway-add-recurring-support-flag)
6. [Phase 2 — Admin Settings: Recurring Options](#phase-2--admin-settings-recurring-options)
7. [Phase 3 — PHP Backend: Product, Plan & Subscription Services](#phase-3--php-backend-product-plan--subscription-services)
8. [Phase 4 — Frontend JS: Recurring UI & Subscription Flow](#phase-4--frontend-js-recurring-ui--subscription-flow)
9. [Phase 5 — Return URL Handler: Process Approved Subscriptions](#phase-5--return-url-handler-process-approved-subscriptions)
10. [Phase 6 — Webhook: Handle Subscription Events](#phase-6--webhook-handle-subscription-events)
11. [Phase 7 — Donations Class: PayPal Recurring Meta](#phase-7--donations-class-paypal-recurring-meta)
12. [Phase 8 — Admin UI: Display Subscription Details](#phase-8--admin-ui-display-subscription-details)
13. [Phase 9 — Cancellation & Management](#phase-9--cancellation--management)
14. [Hook Reference](#hook-reference)
15. [Testing Checklist](#testing-checklist)
16. [File Map](#file-map)

---

## 1. Current State Summary

### Key files

| File | Role |
|---|---|
| `includes/gateways/class-paypal.php` | `PayPal_Gateway` — one-time Orders API v2 flow (Smart Buttons) |
| `includes/gateways/class-gateway-base.php` | `Gateway_Base` — abstract base all gateways extend |
| `includes/core/class-donations.php` | `Donations` — CRUD for `donation` CPT, meta storage |
| `includes/core/class-donation-event-history.php` | Audit trail per donation |
| `includes/core/class-logger.php` | `Giftflow_Logger` — structured error/info logging |
| `includes/frontend/class-forms.php` | AJAX handler `giftflow_donation_form` — creates donation post, calls gateway |
| `includes/frontend/class-shortcodes.php` | Renders donation form shortcode; builds `$donation_types` from campaign options |
| `admin/includes/meta-boxes/class-campaign-details-meta.php` | Campaign CPT meta box: recurring on/off, recurring_interval, recurring_number_of_times |
| `admin/includes/meta-boxes/class-donation-transaction-meta.php` | Admin meta box for donation detail (already shows Stripe subscription info) |
| `assets/js/paypal-donation.js` | PayPal Smart Buttons integration (one-time flow) |

### What `PayPal_Gateway` currently does

- Renders PayPal Smart Buttons via JS SDK v6 (loaded in `add_paypal_sdk_script()`).
- Creates a PayPal **Order** (Orders API v2) via `ajax_create_order()` → `create_paypal_order()`.
- Stores donation data in a transient keyed by PayPal order ID.
- Captures the order via `ajax_capture_order()` → `capture_paypal_order()`.
- Creates the donation record **after** successful capture (not before).
- Handles webhooks for: `PAYMENT.SALE.COMPLETED`, `PAYMENT.CAPTURE.COMPLETED`, `PAYMENT.SALE.DENIED`, `PAYMENT.CAPTURE.DENIED`, `PAYMENT.SALE.REFUNDED`, `PAYMENT.CAPTURE.REFUNDED`.
- Declares `$this->supports = ['webhooks', 'refunds']`.
- Has a working OAuth token cache via transients (`get_paypal_access_token()`).
- Has working webhook signature verification (`verify_webhook_signature()`).

### PayPal SDK URL (current)

```php
$params = array(
    'client-id'  => $client_id,
    'currency'   => strtoupper( $this->get_currency() ),
    'intent'     => 'capture',
    'components' => 'buttons,marks',
);
```

The SDK is loaded with `intent=capture` which only supports `createOrder`. Subscriptions require a different approach (see Phase 4).

### Meta keys already stored on `donation` post

| Meta Key | Description |
|---|---|
| `_amount` | Donation amount |
| `_status` | `pending / completed / failed / refunded / cancelled` |
| `_payment_method` | Gateway ID string (`paypal`) |
| `_donation_type` | `once` or `monthly` (field exists) |
| `_recurring_interval` | Interval string (`monthly`, `yearly`, etc.) |
| `_transaction_id` | PayPal capture/sale ID |
| `_paypal_order_id` | PayPal Order ID |
| `_transaction_raw_data` | JSON-encoded raw PayPal response |

### Existing PHP namespaces/uses

```php
namespace GiftFlow\Gateways;

use GiftFlow\Core\Donations;
use GiftFlow\Core\Logger as Giftflow_Logger;
use GiftFlow\Core\Donation_Event_History;
```

No third-party PayPal SDK is used. All PayPal API calls use `wp_remote_post()` + `wp_remote_get()` with the REST API directly.

---

## 2. Feature Scope

### In scope

- Donor can choose a **recurring interval** on the donation form: monthly or yearly (per campaign settings).
- PayPal Subscriptions API is used (Product → Plan → Subscription model).
- Plans are cached in `wp_options` and reused across donors.
- First charge: handled by PayPal as part of subscription activation.
- Subsequent charges: handled automatically by PayPal, confirmed via webhook.
- Each `PAYMENT.SALE.COMPLETED` webhook event (after the first) creates a **new child donation post** linked to the original (parent) subscription donation.
- Admin can cancel subscriptions from the donation meta box.
- All existing one-time payment behavior is **unchanged**.

### Out of scope (document separately if needed)

- Custom billing cycles beyond what the campaign configures.
- Plan upgrades or amount changes mid-subscription.
- Free trial periods.
- Donor self-service cancellation portal.

---

## 3. PayPal API Strategy

### Three-Layer Model: Product → Plan → Subscription

Unlike Stripe (where prices are created dynamically per subscription), PayPal uses a three-layer billing model:

```
Product (one per plugin installation)
  └── Plan (one per unique amount/currency/interval combo)
        └── Subscription (one per donor)
```

**Critical rules:**
- Plans must be **reused** across multiple subscriptions.
- Creating a new plan per user is bad practice and may hit API rate limits.
- The plugin must cache plans in `wp_options`.

### Product Prerequisite

The plugin must guarantee that a PayPal Product exists **before** any recurring operations. The product is created manually by the admin, not automatically during the donation flow.

```
Admin enables PayPal recurring
  → Plugin checks wp_options → giftflow_paypal_product_id
  → Missing → show admin notice: "PayPal Donation Product has not been created yet."
  → Admin clicks "Create PayPal Product" button
  → Plugin calls POST /v1/catalogs/products
  → Stores product_id in wp_options
  → Product ready for all future plans and subscriptions
```

### Donation Flow

```
Ensure Product exists (admin check, must already be created)
  → Donor selects amount + interval
  → Generate plan_key (e.g. 10_month)
  → Check wp_options for existing plan
  → Plan exists → reuse plan_id
  → Plan missing → create plan via API, store in wp_options
  → Create PayPal Subscription (with return_url + cancel_url)
  → Create Donation post (status: pending)
  → Return approval_url to frontend
  → Frontend opens PayPal approval page in a new tab
  → User approves subscription on PayPal
  → PayPal redirects the new tab to return_url
  → Return handler verifies subscription status, updates _recurring_status (does NOT set donation to completed)
  → Webhook: BILLING.SUBSCRIPTION.ACTIVATED → marks donation active
  → Webhook: PAYMENT.SALE.COMPLETED → detects first payment via empty _paypal_sale_id, completes parent donation
  → Future charges: PAYMENT.SALE.COMPLETED → creates child donation
```

### PayPal API Endpoints Used

| API | Endpoint | Purpose |
|---|---|---|
| Create Product | `POST /v1/catalogs/products` | One-time, cached |
| Create Plan | `POST /v1/billing/plans` | Per unique amount/interval combo, cached |
| Create Subscription | `POST /v1/billing/subscriptions` | Per donor |
| Get Subscription | `GET /v1/billing/subscriptions/{id}` | Verify on return |
| Cancel Subscription | `POST /v1/billing/subscriptions/{id}/cancel` | Admin cancellation |
| Verify Webhook | `POST /v1/notifications/verify-webhook-signature` | Already exists |

### Interval Mapping

Campaign intervals must be mapped to PayPal billing cycle parameters:

| Campaign Interval | PayPal `interval_unit` | PayPal `interval_count` |
|---|---|---|
| `daily` | `DAY` | `1` |
| `weekly` | `WEEK` | `1` |
| `monthly` | `MONTH` | `1` |
| `quarterly` | `MONTH` | `3` |
| `yearly` | `YEAR` | `1` |

---

## 4. Data Schema

All data is stored as WordPress post meta or options. **No new database tables are required.**

### WordPress Options (Plan Cache)

| Option Key | Type | Description |
|---|---|---|
| `giftflow_paypal_product_id` | string | PayPal Product ID (`PROD-XXXXX`) |
| `giftflow_paypal_plan_{amount}_{interval}` | string | Cached PayPal Plan ID (`P-XXXXX`) |

**Plan option key format:**

```
giftflow_paypal_plan_{amount}_{interval}
```

**Examples:**

| Donation | Option Key | Value |
|---|---|---|
| $10 / month | `giftflow_paypal_plan_10_month` | `P-XXXXX` |
| $20 / month | `giftflow_paypal_plan_20_month` | `P-YYYYY` |
| $50 / year | `giftflow_paypal_plan_50_year` | `P-ZZZZZ` |

The option value is a simple string containing only the PayPal Plan ID.

**Plan key generation algorithm:**

```php
$amount_key   = intval( $amount );  // e.g. 10
$interval_key = strtolower( $interval );  // e.g. 'month'
$option_key   = 'giftflow_paypal_plan_' . $amount_key . '_' . $interval_key;
// Result: giftflow_paypal_plan_10_month
```

Plans are **not** tied to campaigns. Any campaign requesting the same amount + interval
will reuse the same plan:

```
Campaign A → $10/month → giftflow_paypal_plan_10_month (reuse)
Campaign B → $10/month → giftflow_paypal_plan_10_month (reuse)
Campaign C → $50/year  → giftflow_paypal_plan_50_year  (different plan)
```

### Transient Keys (Locks)

| Transient Key | TTL | Purpose |
|---|---|---|
| `giftflow_paypal_plan_lock_{hash}` | 30 seconds | Prevent duplicate plan creation |

### New meta keys on the parent `donation` post

| Meta Key | Type | Description |
|---|---|---|
| `_paypal_subscription_id` | string | PayPal Subscription ID (`I-XXXXX`) |
| `_paypal_plan_id` | string | PayPal Plan ID (`P-XXXXX`) used for this subscription |
| `_recurring_status` | string | `pending / active / cancelled / suspended / expired` |
| `_recurring_next_payment_date` | string | ISO 8601 date of next scheduled charge |
| `_recurring_number_of_times` | int | Number of payments (from campaign at creation); 0 = ongoing |
| `_is_subscription_parent` | bool (`1`) | Marks this donation as the originating subscription record |

### New meta keys on child `donation` posts (each renewal)

| Meta Key | Type | Description |
|---|---|---|
| `_parent_donation_id` | int | Post ID of the original subscription donation |
| `_paypal_sale_id` | string | PayPal Sale ID from `PAYMENT.SALE.COMPLETED` |
| `_paypal_subscription_id` | string | Same subscription ID (allows lookup from child) |
| `_is_subscription_renewal` | bool (`1`) | Marks this as an auto-renewal charge |

### Updated `Donations::get()` return array

The `get()` method in `includes/core/class-donations.php` (around line 255) must include these additional keys:

```php
'paypal_subscription_id'  => isset( $meta['_paypal_subscription_id'][0] )  ? $meta['_paypal_subscription_id'][0]  : '',
'paypal_plan_id'          => isset( $meta['_paypal_plan_id'][0] )          ? $meta['_paypal_plan_id'][0]           : '',
```

> **Note:** `recurring_status`, `recurring_next_payment`, `is_subscription_parent`, `parent_donation_id`, and `recurring_number_of_times` are already exposed by the `get()` method (added during Stripe recurring implementation).

---

## Phase 1 — Gateway: Add Recurring Support Flag

**File:** `includes/gateways/class-paypal.php`
**Method:** `init_gateway()` (line 45)

### Task

Add `'recurring'` to the `$this->supports` array:

```php
$this->supports = array(
    'webhooks',
    'refunds',
    'recurring',  // ADD THIS
);
```

This allows other parts of the plugin to detect recurring capability via:

```php
$gateway = Gateway_Base::get_gateway( 'paypal' );
if ( in_array( 'recurring', $gateway->get_supports(), true ) ) { ... }
```

---

## Phase 2 — Admin Settings: Recurring Options

**File:** `includes/gateways/class-paypal.php`
**Method:** `register_settings_fields()` (line 218)

### 2.A — Global PayPal gateway: "Enable recurring" switch

Inside the PayPal accordion `fields` array (after `paypal_webhook_id`), add:

```php
'paypal_recurring_enabled' => array(
    'id'          => 'giftflow_paypal_recurring_enabled',
    'type'        => 'switch',
    'label'       => __( 'Enable Recurring Donations', 'giftflow' ),
    'value'       => isset( $payment_options['paypal']['paypal_recurring_enabled'] )
                     ? $payment_options['paypal']['paypal_recurring_enabled']
                     : false,
    'description' => __( 'Allow donors to set up recurring donations via PayPal Subscriptions. Per-campaign recurring options are in Campaign Details.', 'giftflow' ),
),
```

### 2.B — Update webhook description

Update the `paypal_webhook_enabled` description (line ~283) to include the new subscription webhook events:

```php
'description' =>
    esc_html__( 'Enable webhooks for payment status updates.', 'giftflow' ) . '<br>' .
    esc_html__( 'Webhook URL:', 'giftflow' ) . ' <code>' . admin_url( 'admin-ajax.php?action=giftflow_paypal_webhook' ) . '</code><br>' .
    __(
        'Recommended PayPal events: <strong>Checkout order approved</strong>, <strong>Checkout order completed</strong>, <strong>Payment capture completed</strong>, <strong>Payment capture denied</strong>, <strong>Payment capture refunded</strong>, <strong>Billing subscription activated</strong>, <strong>Billing subscription cancelled</strong>, <strong>Payment sale completed</strong>, <strong>Payment sale denied</strong>.',
        'giftflow'
    ),
```

### 2.C — Per-campaign options (already done)

Campaign meta box fields `recurring`, `recurring_interval`, and `recurring_number_of_times` already exist in `class-campaign-details-meta.php`. The shortcode already builds `$donation_types` from campaign meta. **No changes needed here.**

---

## Phase 3 — PHP Backend: Product, Plan & Subscription Services

**File:** `includes/gateways/class-paypal.php`

All new methods are added as **private methods** inside `PayPal_Gateway`.

### 3.1 — Helper: `get_paypal_base_url()`

Add a reusable helper (the class currently duplicates this logic in several methods):

```php
/**
 * Get PayPal API base URL based on mode.
 *
 * @return string
 */
private function get_paypal_base_url() {
    $mode = $this->get_setting( 'paypal_mode', 'sandbox' );
    return 'sandbox' === $mode
        ? 'https://api.sandbox.paypal.com'
        : 'https://api.paypal.com';
}
```

### 3.2 — Helper: `is_recurring_donation()`

```php
/**
 * Determine if the current donation should be processed as recurring via PayPal.
 *
 * @param array $data Donation form data.
 * @return bool
 */
private function is_recurring_donation( $data ) {
    $recurring_enabled = $this->get_setting( 'paypal_recurring_enabled', false );
    if ( ! $recurring_enabled ) {
        return false;
    }

    $donation_type = isset( $data['donation_type'] ) ? sanitize_text_field( $data['donation_type'] ) : 'once';
    $interval      = isset( $data['recurring_interval'] ) ? sanitize_text_field( $data['recurring_interval'] ) : '';

    return ( 'once' !== $donation_type && 'one-time' !== $donation_type && ! empty( $interval ) );
}
```

### 3.3 — Interval mapping helper: `map_interval_to_paypal()`

```php
/**
 * Map plugin recurring interval to PayPal billing cycle parameters.
 *
 * @param string $interval Plugin interval (daily, weekly, monthly, quarterly, yearly).
 * @return array { interval_unit: string, interval_count: int }
 */
private function map_interval_to_paypal( $interval ) {
    $map = array(
        'daily'     => array( 'interval_unit' => 'DAY',   'interval_count' => 1 ),
        'weekly'    => array( 'interval_unit' => 'WEEK',  'interval_count' => 1 ),
        'monthly'   => array( 'interval_unit' => 'MONTH', 'interval_count' => 1 ),
        'quarterly' => array( 'interval_unit' => 'MONTH', 'interval_count' => 3 ),
        'yearly'    => array( 'interval_unit' => 'YEAR',  'interval_count' => 1 ),
    );

    return isset( $map[ $interval ] ) ? $map[ $interval ] : $map['monthly'];
}
```

### 3.4 — Product: Admin Notice + Manual Creation

The PayPal Product must exist before any plans or subscriptions can be created.
Instead of auto-creating the product during the donation flow, the plugin displays
an **admin notice** prompting the admin to create it manually via a button.

#### 3.4.A — Admin notice: `ensure_paypal_product_exists()`

Register in `init_additional_hooks()`:

```php
add_action( 'admin_notices', array( $this, 'maybe_show_product_notice' ) );
add_action( 'wp_ajax_giftflow_paypal_create_product', array( $this, 'ajax_create_product' ) );
```

Add the **public** notice method:

```php
/**
 * Show an admin notice if the PayPal Donation Product has not been created yet.
 *
 * Only shown when PayPal recurring is enabled and product_id is missing.
 */
public function maybe_show_product_notice() {
    if ( ! $this->enabled ) {
        return;
    }

    $recurring_enabled = $this->get_setting( 'paypal_recurring_enabled', false );
    if ( ! $recurring_enabled ) {
        return;
    }

    $product_id = get_option( 'giftflow_paypal_product_id', '' );
    if ( ! empty( $product_id ) ) {
        return;
    }

    $nonce = wp_create_nonce( 'giftflow_paypal_create_product' );
    ?>
    <div class="notice notice-warning is-dismissible giftflow-paypal-product-notice">
        <p>
            <strong><?php esc_html_e( 'GiftFlow — PayPal Recurring:', 'giftflow' ); ?></strong>
            <?php esc_html_e( 'PayPal Donation Product has not been created yet. You must create the product before recurring donations can work.', 'giftflow' ); ?>
        </p>
        <p>
            <button type="button" class="button button-primary giftflow-create-paypal-product">
                <?php esc_html_e( 'Create PayPal Product', 'giftflow' ); ?>
            </button>
            <span class="giftflow-product-result" style="margin-left:8px;"></span>
        </p>
        <script>
        jQuery( function( $ ) {
            $( '.giftflow-create-paypal-product' ).on( 'click', function() {
                var btn = $( this ), result = btn.siblings( '.giftflow-product-result' );
                btn.prop( 'disabled', true );
                result.text( '<?php echo esc_js( __( 'Creating product…', 'giftflow' ) ); ?>' );
                $.post( ajaxurl, {
                    action: 'giftflow_paypal_create_product',
                    nonce: '<?php echo esc_js( $nonce ); ?>'
                } ).done( function( r ) {
                    if ( r.success ) {
                        result.text( r.data.message || '<?php echo esc_js( __( 'Product created!', 'giftflow' ) ); ?>' );
                        setTimeout( function() { location.reload(); }, 1500 );
                    } else {
                        result.text( r.data.message || '<?php echo esc_js( __( 'Error creating product.', 'giftflow' ) ); ?>' );
                        btn.prop( 'disabled', false );
                    }
                } ).fail( function() {
                    result.text( '<?php echo esc_js( __( 'Request failed.', 'giftflow' ) ); ?>' );
                    btn.prop( 'disabled', false );
                } );
            } );
        } );
        </script>
    </div>
    <?php
}
```

#### 3.4.B — AJAX handler: `ajax_create_product()`

```php
/**
 * AJAX handler: create the PayPal Donation Product.
 */
public function ajax_create_product() {
    check_ajax_referer( 'giftflow_paypal_create_product', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Unauthorized', 'giftflow' ) ) );
    }

    // Check if already exists.
    $existing = get_option( 'giftflow_paypal_product_id', '' );
    if ( ! empty( $existing ) ) {
        wp_send_json_success( array( 'message' => __( 'Product already exists.', 'giftflow' ), 'product_id' => $existing ) );
    }

    $base_url     = $this->get_paypal_base_url();
    $access_token = $this->get_paypal_access_token( $base_url );

    if ( ! $access_token ) {
        wp_send_json_error( array( 'message' => __( 'Failed to get PayPal access token. Check your API credentials.', 'giftflow' ) ) );
    }

    $product_data = array(
        'name' => __( 'Giftflow Donation', 'giftflow' ),
        'type' => 'SERVICE',
    );

    $response = wp_remote_post(
        $base_url . '/v1/catalogs/products',
        array(
            'headers' => array(
                'Content-Type'      => 'application/json',
                'Authorization'     => 'Bearer ' . $access_token,
                'PayPal-Request-Id' => 'product-' . wp_generate_uuid4(),
            ),
            'body'    => wp_json_encode( $product_data ),
            'timeout' => 30,
        )
    );

    if ( is_wp_error( $response ) ) {
        $this->log_error( 'product_creation_failed', $response->get_error_message(), 0 );
        wp_send_json_error( array( 'message' => $response->get_error_message() ) );
    }

    $code = wp_remote_retrieve_response_code( $response );
    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( 201 !== $code || ! isset( $body['id'] ) ) {
        $error_msg = isset( $body['message'] ) ? $body['message'] : __( 'Failed to create PayPal product', 'giftflow' );
        $this->log_error( 'product_creation_failed', $error_msg, 0 );
        wp_send_json_error( array( 'message' => $error_msg ) );
    }

    update_option( 'giftflow_paypal_product_id', $body['id'], false );

    Giftflow_Logger::info(
        'paypal.product.created',
        array(
            'product_id' => $body['id'],
            'gateway'    => 'paypal',
        ),
        'paypal'
    );

    wp_send_json_success(
        array(
            'message'    => __( 'PayPal Donation Product created successfully!', 'giftflow' ),
            'product_id' => $body['id'],
        )
    );
}
```

#### 3.4.C — Product existence guard: `get_paypal_product_id()`

Used by `get_or_create_paypal_plan()` to verify the product exists before creating a plan.
If the product is missing, returns a `WP_Error` instead of auto-creating.

```php
/**
 * Get the stored PayPal Product ID.
 *
 * @return string|\WP_Error Product ID or WP_Error if not yet created.
 */
private function get_paypal_product_id() {
    $product_id = get_option( 'giftflow_paypal_product_id', '' );

    if ( empty( $product_id ) ) {
        return new \WP_Error(
            'paypal_product_missing',
            __( 'PayPal Donation Product has not been created yet. Please create it from the WordPress admin.', 'giftflow' )
        );
    }

    return $product_id;
}
```

### 3.5 — Plan key: `generate_plan_key()`

```php
/**
 * Generate a human-readable option key for a PayPal plan.
 *
 * Format: giftflow_paypal_plan_{amount}_{interval}
 * Examples: giftflow_paypal_plan_10_month, giftflow_paypal_plan_50_year
 *
 * @param string $amount   Donation amount (e.g. '10.00').
 * @param string $interval Plugin interval (daily, weekly, monthly, quarterly, yearly).
 * @return string Option key like 'giftflow_paypal_plan_10_month'.
 */
private function generate_plan_key( $amount, $interval ) {
    $amount_key   = intval( $amount );
    $interval_key = strtolower( sanitize_key( $interval ) );

    return 'giftflow_paypal_plan_' . $amount_key . '_' . $interval_key;
}
```

### 3.6 — Plan: `get_or_create_paypal_plan()`

```php
/**
 * Get or create a PayPal billing plan for the given parameters.
 *
 * Uses wp_options for caching with human-readable keys (e.g. giftflow_paypal_plan_10_month).
 * A transient-based lock prevents duplicate plan creation under concurrent requests.
 *
 * @param string $amount    Donation amount.
 * @param string $currency  Currency code.
 * @param string $interval  Plugin interval (daily, weekly, monthly, quarterly, yearly).
 * @param int    $number_of_times Number of billing cycles (0 = infinite).
 * @return string|\WP_Error PayPal Plan ID or WP_Error.
 */
private function get_or_create_paypal_plan( $amount, $currency, $interval, $number_of_times = 0 ) {
    $paypal_interval = $this->map_interval_to_paypal( $interval );
    $interval_unit   = $paypal_interval['interval_unit'];
    $interval_count  = $paypal_interval['interval_count'];

    $option_key = $this->generate_plan_key( $amount, $interval );

    // Check if plan already exists in cache.
    $cached_plan_id = get_option( $option_key, '' );

    if ( ! empty( $cached_plan_id ) ) {
        return $cached_plan_id;
    }

    // Acquire lock to prevent duplicate creation.
    $lock_key = 'giftflow_paypal_plan_lock_' . sanitize_key( $option_key );
    $lock     = get_transient( $lock_key );

    if ( false !== $lock ) {
        // Another request is creating this plan. Wait and retry.
        sleep( 2 );
        $cached_plan_id = get_option( $option_key, '' );
        if ( ! empty( $cached_plan_id ) ) {
            return $cached_plan_id;
        }
        return new \WP_Error( 'paypal_plan_locked', __( 'Plan creation in progress, please try again.', 'giftflow' ) );
    }

    set_transient( $lock_key, '1', 30 );

    // Require the product to already exist (created by admin).
    $product_id = $this->get_paypal_product_id();
    if ( is_wp_error( $product_id ) ) {
        delete_transient( $lock_key );
        return $product_id;
    }

    $base_url     = $this->get_paypal_base_url();
    $access_token = $this->get_paypal_access_token( $base_url );

    if ( ! $access_token ) {
        delete_transient( $lock_key );
        return new \WP_Error( 'paypal_token_error', __( 'Failed to get PayPal access token', 'giftflow' ) );
    }

    // Build billing cycles.
    $billing_cycles = array(
        array(
            'frequency'      => array(
                'interval_unit'  => $interval_unit,
                'interval_count' => $interval_count,
            ),
            'tenure_type'    => 'REGULAR',
            'sequence'       => 1,
            'total_cycles'   => ( $number_of_times > 0 ) ? $number_of_times : 0,
            'pricing_scheme' => array(
                'fixed_price' => array(
                    'value'         => number_format( (float) $amount, 2, '.', '' ),
                    'currency_code' => strtoupper( $currency ),
                ),
            ),
        ),
    );

    $plan_data = array(
        'product_id'          => $product_id,
        'name'                => sprintf(
            /* translators: 1: amount, 2: currency, 3: interval */
            __( 'Donation %1$s %2$s / %3$s', 'giftflow' ),
            number_format( (float) $amount, 2, '.', '' ),
            strtoupper( $currency ),
            strtolower( $interval_unit )
        ),
        'description'         => sprintf(
            /* translators: 1: amount, 2: currency, 3: interval */
            __( 'Recurring donation of %1$s %2$s every %3$s', 'giftflow' ),
            number_format( (float) $amount, 2, '.', '' ),
            strtoupper( $currency ),
            strtolower( $interval )
        ),
        'status'              => 'ACTIVE',
        'billing_cycles'      => $billing_cycles,
        'payment_preferences' => array(
            'auto_bill_outstanding'     => true,
            'payment_failure_threshold' => 3,
        ),
    );

    $response = wp_remote_post(
        $base_url . '/v1/billing/plans',
        array(
            'headers' => array(
                'Content-Type'      => 'application/json',
                'Authorization'     => 'Bearer ' . $access_token,
                'PayPal-Request-Id' => 'plan-' . wp_generate_uuid4(),
            ),
            'body'    => wp_json_encode( $plan_data ),
            'timeout' => 30,
        )
    );

    delete_transient( $lock_key );

    if ( is_wp_error( $response ) ) {
        $this->log_error( 'plan_creation_failed', $response->get_error_message(), 0 );
        return $response;
    }

    $code = wp_remote_retrieve_response_code( $response );
    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( 201 !== $code || ! isset( $body['id'] ) ) {
        $error_msg = isset( $body['message'] ) ? $body['message'] : __( 'Failed to create PayPal plan', 'giftflow' );
        $this->log_error( 'plan_creation_failed', $error_msg, 0 );
        return new \WP_Error( 'paypal_plan_error', $error_msg );
    }

    // Cache the plan_id in wp_options (simple string value).
    update_option( $option_key, $body['id'], false );

    Giftflow_Logger::info(
        'paypal.plan.created',
        array(
            'plan_id'    => $body['id'],
            'product_id' => $product_id,
            'option_key' => $option_key,
            'amount'     => $amount,
            'currency'   => $currency,
            'interval'   => $interval,
            'gateway'    => 'paypal',
        ),
        'paypal'
    );

    return $body['id'];
}
```

### 3.7 — Subscription: `create_paypal_subscription()`

```php
/**
 * Create a PayPal subscription for a donor.
 *
 * @param string $plan_id     PayPal Plan ID.
 * @param array  $data        Donation form data.
 * @param int    $donation_id Donation post ID.
 * @return array|\WP_Error Array with 'subscription_id' and 'approval_url', or WP_Error.
 */
private function create_paypal_subscription( $plan_id, $data, $donation_id ) {
    $base_url     = $this->get_paypal_base_url();
    $access_token = $this->get_paypal_access_token( $base_url );

    if ( ! $access_token ) {
        return new \WP_Error( 'paypal_token_error', __( 'Failed to get PayPal access token', 'giftflow' ) );
    }

    $return_url = add_query_arg(
        array(
            'giftflow_paypal_subscription_return' => '1',
            'donation_id' => $donation_id,
        ),
        home_url()
    );

    $cancel_url = add_query_arg(
        array(
            'giftflow_paypal_subscription_cancel' => '1',
            'donation_id' => $donation_id,
        ),
        home_url()
    );

    $subscriber = array(
        'name'          => array(
            'given_name' => sanitize_text_field( $data['donor_name'] ),
        ),
        'email_address' => sanitize_email( $data['donor_email'] ),
    );

    $subscription_data = array(
        'plan_id'             => $plan_id,
        'subscriber'          => $subscriber,
        'application_context' => array(
            'brand_name'          => get_bloginfo( 'name' ),
            'locale'              => 'en-US',
            'user_action'         => 'SUBSCRIBE_NOW',
            'payment_method'      => array(
                'payer_selected'  => 'PAYPAL',
                'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED',
            ),
            'return_url' => $return_url,
            'cancel_url' => $cancel_url,
        ),
        'custom_id' => (string) $donation_id,
    );

    $response = wp_remote_post(
        $base_url . '/v1/billing/subscriptions',
        array(
            'headers' => array(
                'Content-Type'      => 'application/json',
                'Authorization'     => 'Bearer ' . $access_token,
                'PayPal-Request-Id' => 'sub-' . wp_generate_uuid4(),
            ),
            'body'    => wp_json_encode( $subscription_data ),
            'timeout' => 30,
        )
    );

    if ( is_wp_error( $response ) ) {
        $this->log_error( 'subscription_creation_failed', $response->get_error_message(), $donation_id );
        return $response;
    }

    $code = wp_remote_retrieve_response_code( $response );
    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( 201 !== $code || ! isset( $body['id'] ) ) {
        $error_msg = isset( $body['message'] ) ? $body['message'] : __( 'Failed to create PayPal subscription', 'giftflow' );
        $this->log_error( 'subscription_creation_failed', $error_msg, $donation_id );
        return new \WP_Error( 'paypal_subscription_error', $error_msg );
    }

    // Extract approval URL from links.
    $approval_url = '';
    if ( isset( $body['links'] ) && is_array( $body['links'] ) ) {
        foreach ( $body['links'] as $link ) {
            if ( isset( $link['rel'] ) && 'approve' === $link['rel'] ) {
                $approval_url = $link['href'];
                break;
            }
        }
    }

    if ( empty( $approval_url ) ) {
        $this->log_error( 'subscription_no_approval_url', 'No approval URL in subscription response', $donation_id );
        return new \WP_Error( 'paypal_subscription_error', __( 'PayPal did not return an approval URL', 'giftflow' ) );
    }

    Giftflow_Logger::info(
        'paypal.subscription.created',
        array(
            'subscription_id' => $body['id'],
            'plan_id'         => $plan_id,
            'donation_id'     => $donation_id,
            'gateway'         => 'paypal',
        ),
        'paypal'
    );

    return array(
        'subscription_id' => $body['id'],
        'approval_url'    => $approval_url,
        'raw_response'    => $body,
    );
}
```

### 3.8 — AJAX handler: `ajax_create_subscription()`

Register in `init_additional_hooks()` (line 326):

```php
add_action( 'wp_ajax_giftflow_paypal_create_subscription', array( $this, 'ajax_create_subscription' ) );
add_action( 'wp_ajax_nopriv_giftflow_paypal_create_subscription', array( $this, 'ajax_create_subscription' ) );
```

Add the **public** handler method:

```php
/**
 * AJAX handler for creating a PayPal subscription.
 */
public function ajax_create_subscription() {
    check_ajax_referer( 'giftflow_paypal_nonce', 'nonce' );

    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $data = $_POST;
    $data = is_array( $data ) ? giftflow_sanitize_array( $data ) : sanitize_text_field( $data );

    do_action( 'giftflow_donation_form_before_process_donation', $data );

    // Validate amount.
    $amount = isset( $data['amount'] ) ? floatval( $data['amount'] ) : 0;
    if ( ! $amount || $amount <= 0 ) {
        wp_send_json_error( array( 'message' => __( 'Donation amount is required', 'giftflow' ) ) );
    }

    // Validate required fields.
    $donation_data = array(
        'donation_amount'    => $amount,
        'donor_name'         => isset( $data['donor_name'] ) ? sanitize_text_field( $data['donor_name'] ) : '',
        'donor_email'        => isset( $data['donor_email'] ) ? sanitize_email( $data['donor_email'] ) : '',
        'campaign_id'        => isset( $data['campaign_id'] ) ? sanitize_text_field( $data['campaign_id'] ) : '',
        'payment_method'     => 'paypal',
        'donation_type'      => isset( $data['donation_type'] ) ? sanitize_text_field( $data['donation_type'] ) : '',
        'recurring_interval' => isset( $data['recurring_interval'] ) ? sanitize_text_field( $data['recurring_interval'] ) : '',
        'donor_message'      => isset( $data['donor_message'] ) ? sanitize_textarea_field( $data['donor_message'] ) : '',
        'anonymous_donation' => isset( $data['anonymous_donation'] ) ? sanitize_text_field( $data['anonymous_donation'] ) : '',
    );

    if ( empty( $donation_data['donor_name'] ) || empty( $donation_data['donor_email'] ) ) {
        wp_send_json_error( array( 'message' => __( 'Donor name and email are required', 'giftflow' ) ) );
    }

    if ( ! $this->is_recurring_donation( $donation_data ) ) {
        wp_send_json_error( array( 'message' => __( 'Invalid recurring donation data', 'giftflow' ) ) );
    }

    try {
        $interval      = $donation_data['recurring_interval'];
        $currency      = strtoupper( $this->get_currency() );
        $campaign_id   = intval( $donation_data['campaign_id'] );
        $num_of_times  = 0;

        if ( $campaign_id ) {
            $num_of_times = absint( get_post_meta( $campaign_id, '_recurring_number_of_times', true ) );
        }

        // Get or create PayPal plan.
        $plan_id = $this->get_or_create_paypal_plan( $amount, $currency, $interval, $num_of_times );
        if ( is_wp_error( $plan_id ) ) {
            wp_send_json_error( array( 'message' => $plan_id->get_error_message() ) );
        }

        // Create donation record first (pending).
        $donation_data['recurring_number_of_times'] = $num_of_times;
        $donation_id = $this->create_donation_record( $donation_data );
        if ( is_wp_error( $donation_id ) ) {
            wp_send_json_error( array( 'message' => $donation_id->get_error_message() ) );
        }

        // Create PayPal subscription.
        $result = $this->create_paypal_subscription( $plan_id, $donation_data, $donation_id );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        // Store subscription meta on the donation.
        update_post_meta( $donation_id, '_paypal_subscription_id', $result['subscription_id'] );
        update_post_meta( $donation_id, '_paypal_plan_id', $plan_id );
        update_post_meta( $donation_id, '_donation_type', 'recurring' );
        update_post_meta( $donation_id, '_recurring_interval', $interval );
        update_post_meta( $donation_id, '_recurring_status', 'pending' );
        update_post_meta( $donation_id, '_is_subscription_parent', '1' );
        update_post_meta( $donation_id, '_recurring_number_of_times', $num_of_times );
        update_post_meta( $donation_id, '_transaction_raw_data', wp_json_encode( $result['raw_response'] ) );

        do_action( 'giftflow_paypal_subscription_created', $donation_id, $result['subscription_id'], $result['raw_response'] );

        wp_send_json_success(
            array(
                'subscription_id' => $result['subscription_id'],
                'approval_url'    => $result['approval_url'],
                'donation_id'     => $donation_id,
            )
        );

    } catch ( \Exception $e ) {
        $this->log_error( 'subscription_exception', $e->getMessage(), 0 );
        wp_send_json_error( array( 'message' => $e->getMessage() ) );
    }
}
```

### 3.9 — Helper: `get_paypal_subscription()`

Used by the return URL handler and admin to verify subscription status.

```php
/**
 * Get PayPal subscription details.
 *
 * @param string $subscription_id PayPal Subscription ID.
 * @return array|\WP_Error Subscription data or WP_Error.
 */
private function get_paypal_subscription( $subscription_id ) {
    $base_url     = $this->get_paypal_base_url();
    $access_token = $this->get_paypal_access_token( $base_url );

    if ( ! $access_token ) {
        return new \WP_Error( 'paypal_token_error', __( 'Failed to get PayPal access token', 'giftflow' ) );
    }

    $response = wp_remote_get(
        $base_url . '/v1/billing/subscriptions/' . $subscription_id,
        array(
            'headers' => array(
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $access_token,
            ),
            'timeout' => 30,
        )
    );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $code = wp_remote_retrieve_response_code( $response );
    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( 200 !== $code ) {
        $error_msg = isset( $body['message'] ) ? $body['message'] : __( 'Failed to retrieve subscription', 'giftflow' );
        return new \WP_Error( 'paypal_subscription_error', $error_msg );
    }

    return $body;
}
```

---

## Phase 4 — Frontend JS: Recurring UI & Subscription Flow

**File:** `assets/js/paypal-donation.js`

### 4.1 — Strategy: Hybrid Button Behavior

The existing PayPal Smart Buttons use `createOrder` for one-time payments. For recurring donations, we **cannot** use `createSubscription` with the same SDK instance loaded with `intent=capture`.

**Approach:** When the donor selects **recurring** as the donation type with PayPal:
1. Hide the PayPal Smart Buttons.
2. Show a "Complete Donation via PayPal" styled button.
3. On click: collect form data → AJAX to `giftflow_paypal_create_subscription` → receive approval_url → redirect user.

When the donor selects **one-time**: existing Smart Buttons flow is unchanged.

### 4.2 — JS Changes

Inside the `PayPalDonation` class, add detection for recurring and a redirect-based flow:

```js
// Inside the constructor, after this.init():
this.initRecurringHandler();

/**
 * Initialize recurring donation handler.
 */
initRecurringHandler() {
    const self = this;

    // Listen for donation type changes.
    const donationTypeInputs = this.form.querySelectorAll('input[name="donation_type"]');
    donationTypeInputs.forEach((input) => {
        input.addEventListener('change', () => {
            self.toggleRecurringMode(input.value);
        });
    });

    // Create the recurring PayPal button (hidden by default).
    this.createRecurringButton();
}

/**
 * Toggle between one-time (Smart Buttons) and recurring (redirect) mode.
 *
 * @param {string} donationType - 'one-time' or 'recurring' etc.
 */
toggleRecurringMode(donationType) {
    const smartButtonContainer = this.form.querySelector('#giftflow-paypal-button-container');
    const recurringButton = this.form.querySelector('.giftflow-paypal-recurring-button');

    const isRecurring = donationType !== 'one-time' && donationType !== 'once';

    if (smartButtonContainer) {
        smartButtonContainer.style.display = isRecurring ? 'none' : 'block';
    }
    if (recurringButton) {
        recurringButton.style.display = isRecurring ? 'block' : 'none';
    }
}

/**
 * Create a styled button for recurring PayPal donations.
 */
createRecurringButton() {
    const container = this.form.querySelector(
        '.donation-form__payment-method-description--paypal .donation-form__field'
    );
    if (!container) return;

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'giftflow-paypal-recurring-button';
    btn.textContent = giftflowPayPalDonation.messages?.subscribe_paypal || 'Subscribe with PayPal';
    btn.style.cssText = 'display:none; width:100%; padding:12px 24px; background:#0070ba; color:#fff; border:none; border-radius:4px; font-size:16px; cursor:pointer; max-width:350px; margin:0 auto;';
    btn.addEventListener('click', () => this.handleRecurringDonation());

    container.appendChild(btn);
}

/**
 * Handle recurring donation: create subscription and redirect to PayPal.
 */
async handleRecurringDonation() {
    const btn = this.form.querySelector('.giftflow-paypal-recurring-button');
    if (btn) {
        btn.disabled = true;
        btn.textContent = giftflowPayPalDonation.messages?.processing || 'Processing...';
    }

    try {
        const fields = this.formObject.getFields() || {};

        const requestData = {
            ...fields,
            action: 'giftflow_paypal_create_subscription',
            nonce: giftflowPayPalDonation.nonce,
            amount: fields?.donation_amount || '0',
        };

        const response = await fetch(giftflowPayPalDonation.ajaxurl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(requestData),
        });

        const result = await response.json();

        if (!result.success || !result.data?.approval_url) {
            throw new Error(result.data?.message || 'Failed to create subscription');
        }

        // Redirect to PayPal approval page.
        window.location.href = result.data.approval_url;

    } catch (error) {
        console.error('PayPal subscription error:', error);
        this.showErrorMessage(error.message || giftflowPayPalDonation.messages.error);
        if (btn) {
            btn.disabled = false;
            btn.textContent = giftflowPayPalDonation.messages?.subscribe_paypal || 'Subscribe with PayPal';
        }
    }
}
```

### 4.3 — Localize recurring messages to JS

In `get_script_data()` (`class-paypal.php`, line 197), add:

```php
'recurring_enabled' => (bool) $this->get_setting( 'paypal_recurring_enabled', false ),
'messages' => array(
    'processing'       => __( 'Processing payment...', 'giftflow' ),
    'error'            => __( 'Payment failed. Please try again.', 'giftflow' ),
    'canceled'         => __( 'Payment was canceled.', 'giftflow' ),
    'subscribe_paypal' => __( 'Subscribe with PayPal', 'giftflow' ),
    'subscribing'      => __( 'Creating subscription...', 'giftflow' ),
),
```

---

## Phase 5 — Return URL Handler: Process Approved Subscriptions

**File:** `includes/gateways/class-paypal.php`

### 5.1 — Register return URL hook

In `init_additional_hooks()`, add:

```php
add_action( 'template_redirect', array( $this, 'handle_subscription_return' ) );
```

### 5.2 — Return URL handler

```php
/**
 * Handle PayPal subscription return URL after donor approval.
 */
public function handle_subscription_return() {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if ( ! isset( $_GET['giftflow_paypal_subscription_return'] ) ) {
        return;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $donation_id     = isset( $_GET['donation_id'] ) ? absint( $_GET['donation_id'] ) : 0;
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $subscription_id = isset( $_GET['subscription_id'] ) ? sanitize_text_field( wp_unslash( $_GET['subscription_id'] ) ) : '';
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $ba_token        = isset( $_GET['ba_token'] ) ? sanitize_text_field( wp_unslash( $_GET['ba_token'] ) ) : '';
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $token           = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';

    if ( ! $donation_id ) {
        return;
    }

    // Get subscription ID from donation meta if not in URL.
    if ( empty( $subscription_id ) ) {
        $subscription_id = get_post_meta( $donation_id, '_paypal_subscription_id', true );
    }

    if ( empty( $subscription_id ) ) {
        $this->log_error( 'subscription_return_no_id', 'No subscription ID found on return', $donation_id );
        return;
    }

    // Verify the subscription status with PayPal.
    $subscription = $this->get_paypal_subscription( $subscription_id );

    if ( is_wp_error( $subscription ) ) {
        $this->log_error( 'subscription_return_verify_failed', $subscription->get_error_message(), $donation_id );
        return;
    }

    $status = isset( $subscription['status'] ) ? $subscription['status'] : '';

    // Update donation meta with latest subscription data.
    $status_map = array(
        'APPROVAL_PENDING' => 'pending',
        'APPROVED'         => 'pending',
        'ACTIVE'           => 'active',
        'SUSPENDED'        => 'suspended',
        'CANCELLED'        => 'cancelled',
        'EXPIRED'          => 'expired',
    );

    $recurring_status = isset( $status_map[ $status ] ) ? $status_map[ $status ] : 'pending';
    update_post_meta( $donation_id, '_recurring_status', $recurring_status );

    // If already active (fast activation), mark completed.
    if ( 'ACTIVE' === $status ) {
        $donations_class = new Donations();
        $donations_class->update_status( $donation_id, 'completed' );

        update_post_meta( $donation_id, '_recurring_status', 'active' );

        if ( isset( $subscription['billing_info']['next_billing_time'] ) ) {
            update_post_meta(
                $donation_id,
                '_recurring_next_payment_date',
                sanitize_text_field( $subscription['billing_info']['next_billing_time'] )
            );
        }

        Donation_Event_History::add(
            $donation_id,
            'recurring_subscription_activated',
            'completed',
            __( 'PayPal subscription activated on return', 'giftflow' ),
            array(
                'subscription_id' => $subscription_id,
                'gateway'         => 'paypal',
                'source'          => 'return_url',
            )
        );

        do_action( 'giftflow_donation_after_payment_processed', $donation_id, true );
    }

    Giftflow_Logger::info(
        'paypal.subscription.return',
        array(
            'donation_id'     => $donation_id,
            'subscription_id' => $subscription_id,
            'status'          => $status,
            'gateway'         => 'paypal',
        ),
        'paypal'
    );

    do_action( 'giftflow_paypal_subscription_return', $donation_id, $subscription_id, $subscription );

    // Redirect to thank-you page or home with success parameter.
    $redirect_url = apply_filters(
        'giftflow_paypal_subscription_return_url',
        add_query_arg(
            array(
                'giftflow_donation_success' => '1',
                'donation_id'               => $donation_id,
            ),
            home_url()
        ),
        $donation_id
    );

    wp_safe_redirect( $redirect_url );
    exit;
}
```

### 5.3 — Cancel URL handler (optional, for UX)

Also in `handle_subscription_return()` or as a separate check:

```php
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( isset( $_GET['giftflow_paypal_subscription_cancel'] ) ) {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $donation_id = isset( $_GET['donation_id'] ) ? absint( $_GET['donation_id'] ) : 0;
    if ( $donation_id ) {
        $donations_class = new Donations();
        $donations_class->update_status( $donation_id, 'failed' );
        update_post_meta( $donation_id, '_recurring_status', 'cancelled' );

        Donation_Event_History::add(
            $donation_id,
            'recurring_subscription_cancelled_by_donor',
            'failed',
            __( 'Donor cancelled PayPal subscription approval', 'giftflow' ),
            array( 'gateway' => 'paypal', 'source' => 'cancel_url' )
        );
    }
    wp_safe_redirect( home_url() );
    exit;
}
```

---

## Phase 6 — Webhook: Handle Subscription Events

**File:** `includes/gateways/class-paypal.php`
**Method:** `handle_webhook()` (line 1003)

### 6.1 — Register new PayPal webhook events

In the PayPal Developer Dashboard, add these events to the webhook endpoint:

```
BILLING.SUBSCRIPTION.CREATED
BILLING.SUBSCRIPTION.ACTIVATED
BILLING.SUBSCRIPTION.CANCELLED
BILLING.SUBSCRIPTION.SUSPENDED
BILLING.SUBSCRIPTION.EXPIRED
PAYMENT.SALE.COMPLETED
PAYMENT.SALE.DENIED
```

The webhook URL is already: `admin-ajax.php?action=giftflow_paypal_webhook`.

### 6.2 — Extend the `switch` in `handle_webhook()`

Inside the existing `switch ( $event['event_type'] )` block, add new cases **before** the `default`:

```php
case 'BILLING.SUBSCRIPTION.CREATED':
    $this->handle_subscription_created_webhook( $event['resource'] );
    break;

case 'BILLING.SUBSCRIPTION.ACTIVATED':
    $this->handle_subscription_activated( $event['resource'] );
    break;

case 'BILLING.SUBSCRIPTION.CANCELLED':
    $this->handle_subscription_cancelled( $event['resource'] );
    break;

case 'BILLING.SUBSCRIPTION.SUSPENDED':
    $this->handle_subscription_suspended( $event['resource'] );
    break;

case 'BILLING.SUBSCRIPTION.EXPIRED':
    $this->handle_subscription_expired( $event['resource'] );
    break;
```

Also update the existing `PAYMENT.SALE.COMPLETED` case to handle subscription-related sales:

```php
case 'PAYMENT.SALE.COMPLETED':
case 'PAYMENT.CAPTURE.COMPLETED':
    if ( $this->is_subscription_sale( $event['resource'] ) ) {
        $this->handle_subscription_payment_completed( $event['resource'] );
    } else {
        $this->handle_payment_completed( $event['resource'] );
    }
    break;

case 'PAYMENT.SALE.DENIED':
case 'PAYMENT.CAPTURE.DENIED':
    if ( $this->is_subscription_sale( $event['resource'] ) ) {
        $this->handle_subscription_payment_denied( $event['resource'] );
    } else {
        $this->handle_payment_denied( $event['resource'] );
    }
    break;
```

### 6.3 — Helper: `is_subscription_sale()`

```php
/**
 * Check if a PAYMENT.SALE resource is related to a subscription.
 *
 * @param array $_resource Resource data from webhook.
 * @return bool
 */
private function is_subscription_sale( $_resource ) {
    return ! empty( $_resource['billing_agreement_id'] );
}
```

### 6.4 — Helper: `find_donation_by_subscription_id()`

```php
/**
 * Find the parent donation post by PayPal subscription ID.
 *
 * @param string $subscription_id PayPal Subscription ID.
 * @return int|false Donation post ID or false.
 */
private function find_donation_by_subscription_id( $subscription_id ) {
    if ( empty( $subscription_id ) ) {
        return false;
    }

    $donations = get_posts(
        array(
            'post_type'      => 'donation',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            // phpcs:ignore WordPress.DB.SlowDBQuery
            'meta_key'       => '_paypal_subscription_id',
            // phpcs:ignore WordPress.DB.SlowDBQuery
            'meta_value'     => $subscription_id,
            'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
                array(
                    'key'   => '_is_subscription_parent',
                    'value' => '1',
                ),
            ),
        )
    );

    return ! empty( $donations ) ? $donations[0] : false;
}
```

### 6.5 — Handler: `handle_subscription_created_webhook()`

```php
/**
 * Handle BILLING.SUBSCRIPTION.CREATED webhook.
 *
 * PayPal fires this when a subscription is created but NOT yet approved by the donor.
 * At this point the donation post already exists (created during the AJAX flow),
 * so this handler is primarily used for logging/auditing.
 *
 * @param array $_resource Subscription resource from webhook.
 */
private function handle_subscription_created_webhook( $_resource ) {
    $subscription_id = isset( $_resource['id'] ) ? $_resource['id'] : '';
    $donation_id     = $this->find_donation_by_subscription_id( $subscription_id );

    if ( ! $donation_id ) {
        $donation_id = isset( $_resource['custom_id'] ) ? absint( $_resource['custom_id'] ) : 0;
        if ( $donation_id ) {
            update_post_meta( $donation_id, '_paypal_subscription_id', $subscription_id );
        }
    }

    if ( ! $donation_id ) {
        Giftflow_Logger::info(
            'paypal.webhook.subscription_created.no_donation',
            array(
                'subscription_id' => $subscription_id,
                'gateway'         => 'paypal',
            ),
            'paypal'
        );
        return;
    }

    Donation_Event_History::add_event(
        $donation_id,
        'recurring_subscription_created_webhook',
        'pending',
        __( 'Webhook: BILLING.SUBSCRIPTION.CREATED — subscription awaiting donor approval', 'giftflow' ),
        array(
            'subscription_id' => $subscription_id,
            'gateway'         => 'paypal',
        )
    );

    do_action( 'giftflow_paypal_subscription_created_webhook', $donation_id, $subscription_id, $_resource );
}
```

### 6.6 — Handler: `handle_subscription_activated()`

```php
/**
 * Handle BILLING.SUBSCRIPTION.ACTIVATED webhook.
 *
 * Marks the parent donation as completed and active.
 *
 * @param array $_resource Subscription resource from webhook.
 */
private function handle_subscription_activated( $_resource ) {
    $subscription_id = isset( $_resource['id'] ) ? $_resource['id'] : '';
    $donation_id     = $this->find_donation_by_subscription_id( $subscription_id );

    if ( ! $donation_id ) {
        // Try custom_id fallback.
        $donation_id = isset( $_resource['custom_id'] ) ? absint( $_resource['custom_id'] ) : 0;
        if ( $donation_id ) {
            update_post_meta( $donation_id, '_paypal_subscription_id', $subscription_id );
        }
    }

    if ( ! $donation_id ) {
        $this->log_error( 'webhook_subscription_activated_no_donation', 'Parent donation not found for subscription ' . $subscription_id, 0 );
        return;
    }

    $donations_class = new Donations();
    $donations_class->update_status( $donation_id, 'completed' );

    update_post_meta( $donation_id, '_recurring_status', 'active' );

    if ( isset( $_resource['billing_info']['next_billing_time'] ) ) {
        update_post_meta(
            $donation_id,
            '_recurring_next_payment_date',
            sanitize_text_field( $_resource['billing_info']['next_billing_time'] )
        );
    }

    Donation_Event_History::add(
        $donation_id,
        'recurring_subscription_activated',
        'completed',
        __( 'Webhook: BILLING.SUBSCRIPTION.ACTIVATED', 'giftflow' ),
        array(
            'subscription_id' => $subscription_id,
            'gateway'         => 'paypal',
            'source'          => 'webhook',
        )
    );

    Giftflow_Logger::info(
        'paypal.webhook.subscription.activated',
        array(
            'donation_id'     => $donation_id,
            'subscription_id' => $subscription_id,
            'gateway'         => 'paypal',
        ),
        'paypal'
    );

    do_action( 'giftflow_paypal_subscription_activated', $donation_id, $subscription_id, $_resource );
    do_action( 'giftflow_donation_after_payment_processed', $donation_id, true );
}
```

### 6.7 — Handler: `handle_subscription_cancelled()`

```php
/**
 * Handle BILLING.SUBSCRIPTION.CANCELLED webhook.
 *
 * @param array $_resource Subscription resource from webhook.
 */
private function handle_subscription_cancelled( $_resource ) {
    $subscription_id = isset( $_resource['id'] ) ? $_resource['id'] : '';
    $donation_id     = $this->find_donation_by_subscription_id( $subscription_id );

    if ( ! $donation_id ) {
        return;
    }

    update_post_meta( $donation_id, '_recurring_status', 'cancelled' );

    Donation_Event_History::add(
        $donation_id,
        'recurring_subscription_cancelled',
        'cancelled',
        __( 'Webhook: BILLING.SUBSCRIPTION.CANCELLED', 'giftflow' ),
        array(
            'subscription_id' => $subscription_id,
            'gateway'         => 'paypal',
            'source'          => 'webhook',
        )
    );

    Giftflow_Logger::info(
        'paypal.webhook.subscription.cancelled',
        array(
            'donation_id'     => $donation_id,
            'subscription_id' => $subscription_id,
            'gateway'         => 'paypal',
        ),
        'paypal'
    );

    do_action( 'giftflow_paypal_subscription_cancelled', $donation_id, $subscription_id, $_resource );
}
```

### 6.8 — Handler: `handle_subscription_suspended()`

```php
/**
 * Handle BILLING.SUBSCRIPTION.SUSPENDED webhook.
 *
 * @param array $_resource Subscription resource from webhook.
 */
private function handle_subscription_suspended( $_resource ) {
    $subscription_id = isset( $_resource['id'] ) ? $_resource['id'] : '';
    $donation_id     = $this->find_donation_by_subscription_id( $subscription_id );

    if ( ! $donation_id ) {
        return;
    }

    update_post_meta( $donation_id, '_recurring_status', 'suspended' );

    Donation_Event_History::add(
        $donation_id,
        'recurring_subscription_suspended',
        'suspended',
        __( 'Webhook: BILLING.SUBSCRIPTION.SUSPENDED', 'giftflow' ),
        array(
            'subscription_id' => $subscription_id,
            'gateway'         => 'paypal',
            'source'          => 'webhook',
        )
    );

    do_action( 'giftflow_paypal_subscription_suspended', $donation_id, $subscription_id, $_resource );
}
```

### 6.9 — Handler: `handle_subscription_expired()`

```php
/**
 * Handle BILLING.SUBSCRIPTION.EXPIRED webhook.
 *
 * @param array $_resource Subscription resource from webhook.
 */
private function handle_subscription_expired( $_resource ) {
    $subscription_id = isset( $_resource['id'] ) ? $_resource['id'] : '';
    $donation_id     = $this->find_donation_by_subscription_id( $subscription_id );

    if ( ! $donation_id ) {
        return;
    }

    update_post_meta( $donation_id, '_recurring_status', 'expired' );

    Donation_Event_History::add(
        $donation_id,
        'recurring_subscription_expired',
        'expired',
        __( 'Webhook: BILLING.SUBSCRIPTION.EXPIRED', 'giftflow' ),
        array(
            'subscription_id' => $subscription_id,
            'gateway'         => 'paypal',
            'source'          => 'webhook',
        )
    );

    do_action( 'giftflow_paypal_subscription_expired', $donation_id, $subscription_id, $_resource );
}
```

### 6.10 — Handler: `handle_subscription_payment_completed()`

This is the most critical handler. It fires for **every successful recurring charge**.

```php
/**
 * Handle PAYMENT.SALE.COMPLETED webhook for subscription payments.
 *
 * The first payment confirms the initial donation.
 * Subsequent payments create child renewal donation posts.
 *
 * @param array $_resource Sale resource from webhook.
 */
private function handle_subscription_payment_completed( $_resource ) {
    $subscription_id = isset( $_resource['billing_agreement_id'] ) ? $_resource['billing_agreement_id'] : '';
    $sale_id         = isset( $_resource['id'] ) ? $_resource['id'] : '';

    if ( empty( $subscription_id ) ) {
        return;
    }

    $parent_donation_id = $this->find_donation_by_subscription_id( $subscription_id );

    if ( ! $parent_donation_id ) {
        $this->log_error( 'webhook_sale_no_parent', 'Parent donation not found for subscription ' . $subscription_id, 0 );
        return;
    }

    // Idempotency: check if this sale already processed.
    $existing = get_posts(
        array(
            'post_type'      => 'donation',
            'posts_per_page' => 1,
            // phpcs:ignore WordPress.DB.SlowDBQuery
            'meta_key'       => '_paypal_sale_id',
            // phpcs:ignore WordPress.DB.SlowDBQuery
            'meta_value'     => $sale_id,
        )
    );

    // Also check if the parent has this sale_id as its transaction.
    $parent_txn = get_post_meta( $parent_donation_id, '_transaction_id', true );

    if ( ! empty( $existing ) || $parent_txn === $sale_id ) {
        return;
    }

    $current_status = get_post_meta( $parent_donation_id, '_status', true );

    // Determine if this is the first payment (parent is still pending).
    $is_first_payment = ( 'pending' === $current_status );

    $amount   = isset( $_resource['amount']['total'] ) ? $_resource['amount']['total'] : '';
    $currency = isset( $_resource['amount']['currency'] ) ? $_resource['amount']['currency'] : '';

    if ( $is_first_payment ) {
        // Mark the parent donation as completed.
        update_post_meta( $parent_donation_id, '_transaction_id', $sale_id );
        update_post_meta( $parent_donation_id, '_paypal_sale_id', $sale_id );
        update_post_meta( $parent_donation_id, '_transaction_raw_data', wp_json_encode( $_resource ) );

        $donations_class = new Donations();
        $donations_class->update_status( $parent_donation_id, 'completed' );
        update_post_meta( $parent_donation_id, '_recurring_status', 'active' );

        Donation_Event_History::add(
            $parent_donation_id,
            'recurring_payment_first',
            'completed',
            __( 'Webhook: PAYMENT.SALE.COMPLETED (first charge)', 'giftflow' ),
            array(
                'sale_id'         => $sale_id,
                'subscription_id' => $subscription_id,
                'amount'          => $amount,
                'gateway'         => 'paypal',
                'source'          => 'webhook',
            )
        );

        do_action( 'giftflow_donation_after_payment_processed', $parent_donation_id, true );

    } else {
        // Create a child renewal donation.
        $meta = get_post_meta( $parent_donation_id );

        $renewal_id = wp_insert_post(
            array(
                'post_title'  => sprintf(
                    /* translators: %s: parent donation ID */
                    __( 'Recurring Donation (renewal of #%s)', 'giftflow' ),
                    $parent_donation_id
                ),
                'post_type'   => 'donation',
                'post_status' => 'publish',
            )
        );

        if ( is_wp_error( $renewal_id ) ) {
            $this->log_error( 'webhook_renewal_creation_failed', 'Failed to create renewal for subscription ' . $subscription_id, $parent_donation_id );
            return;
        }

        // Copy core meta from parent.
        $copy_keys = array( '_amount', '_campaign_id', '_donor_id', '_payment_method', '_donation_type', '_recurring_interval' );
        foreach ( $copy_keys as $key ) {
            if ( isset( $meta[ $key ][0] ) ) {
                update_post_meta( $renewal_id, $key, $meta[ $key ][0] );
            }
        }

        // Override amount if different (future-proofing).
        if ( ! empty( $amount ) ) {
            update_post_meta( $renewal_id, '_amount', floatval( $amount ) );
        }

        // Set renewal-specific meta.
        update_post_meta( $renewal_id, '_status', 'completed' );
        update_post_meta( $renewal_id, '_parent_donation_id', $parent_donation_id );
        update_post_meta( $renewal_id, '_paypal_sale_id', $sale_id );
        update_post_meta( $renewal_id, '_paypal_subscription_id', $subscription_id );
        update_post_meta( $renewal_id, '_transaction_id', $sale_id );
        update_post_meta( $renewal_id, '_transaction_raw_data', wp_json_encode( $_resource ) );
        update_post_meta( $renewal_id, '_is_subscription_renewal', '1' );

        Donation_Event_History::add(
            $renewal_id,
            'recurring_payment_renewal',
            'completed',
            __( 'Webhook: PAYMENT.SALE.COMPLETED (renewal)', 'giftflow' ),
            array(
                'sale_id'            => $sale_id,
                'subscription_id'    => $subscription_id,
                'parent_donation_id' => $parent_donation_id,
                'amount'             => $amount,
                'gateway'            => 'paypal',
                'source'             => 'webhook',
            )
        );

        do_action( 'giftflow_paypal_recurring_renewal_created', $renewal_id, $parent_donation_id, $subscription_id, $_resource );
    }

    // Update parent's next payment date.
    $subscription = $this->get_paypal_subscription( $subscription_id );
    if ( ! is_wp_error( $subscription ) && isset( $subscription['billing_info']['next_billing_time'] ) ) {
        update_post_meta(
            $parent_donation_id,
            '_recurring_next_payment_date',
            sanitize_text_field( $subscription['billing_info']['next_billing_time'] )
        );
    }

    Giftflow_Logger::info(
        'paypal.webhook.sale.completed.subscription',
        array(
            'parent_donation_id' => $parent_donation_id,
            'sale_id'            => $sale_id,
            'subscription_id'    => $subscription_id,
            'is_first'           => $is_first_payment,
            'amount'             => $amount,
            'gateway'            => 'paypal',
        ),
        'paypal'
    );
}
```

### 6.11 — Handler: `handle_subscription_payment_denied()`

```php
/**
 * Handle PAYMENT.SALE.DENIED for subscription payments.
 *
 * @param array $_resource Sale resource from webhook.
 */
private function handle_subscription_payment_denied( $_resource ) {
    $subscription_id = isset( $_resource['billing_agreement_id'] ) ? $_resource['billing_agreement_id'] : '';
    $sale_id         = isset( $_resource['id'] ) ? $_resource['id'] : '';

    $parent_donation_id = $this->find_donation_by_subscription_id( $subscription_id );

    if ( ! $parent_donation_id ) {
        return;
    }

    update_post_meta( $parent_donation_id, '_recurring_status', 'suspended' );

    Donation_Event_History::add(
        $parent_donation_id,
        'recurring_payment_failed',
        'failed',
        __( 'Webhook: PAYMENT.SALE.DENIED (subscription payment failed)', 'giftflow' ),
        array(
            'sale_id'         => $sale_id,
            'subscription_id' => $subscription_id,
            'gateway'         => 'paypal',
            'source'          => 'webhook',
        )
    );

    Giftflow_Logger::error(
        'paypal.webhook.sale.denied.subscription',
        array(
            'parent_donation_id' => $parent_donation_id,
            'sale_id'            => $sale_id,
            'subscription_id'    => $subscription_id,
            'gateway'            => 'paypal',
        ),
        'paypal'
    );

    do_action( 'giftflow_paypal_recurring_payment_denied', $parent_donation_id, $subscription_id, $_resource );
}
```

---

## Phase 7 — Donations Class: PayPal Recurring Meta

**File:** `includes/core/class-donations.php`

### Task A — Extend `get()` return array

In the `get()` method (around line 255), add:

```php
'paypal_subscription_id' => isset( $meta['_paypal_subscription_id'][0] ) ? $meta['_paypal_subscription_id'][0] : '',
'paypal_plan_id'         => isset( $meta['_paypal_plan_id'][0] )         ? $meta['_paypal_plan_id'][0]         : '',
```

### Task B — Extend `save_donation_meta()`

In `save_donation_meta()` (around line 570, after existing Stripe meta handlers), add:

```php
// PayPal Subscription ID.
if ( isset( $data['paypal_subscription_id'] ) ) {
    if ( ! empty( $data['paypal_subscription_id'] ) ) {
        update_post_meta( $donation_id, '_paypal_subscription_id', sanitize_text_field( $data['paypal_subscription_id'] ) );
    } else {
        delete_post_meta( $donation_id, '_paypal_subscription_id' );
    }
}

// PayPal Plan ID.
if ( isset( $data['paypal_plan_id'] ) ) {
    if ( ! empty( $data['paypal_plan_id'] ) ) {
        update_post_meta( $donation_id, '_paypal_plan_id', sanitize_text_field( $data['paypal_plan_id'] ) );
    } else {
        delete_post_meta( $donation_id, '_paypal_plan_id' );
    }
}
```

---

## Phase 8 — Admin UI: Display Subscription Details

**File:** `admin/includes/meta-boxes/class-donation-transaction-meta.php`

### Task

After the existing Stripe recurring details block (line ~147), add a PayPal recurring details block:

```php
// PayPal recurring: show subscription details when this is a subscription parent.
$paypal_subscription_id = get_post_meta( $post->ID, '_paypal_subscription_id', true );
if ( $is_subscription_parent && ! empty( $paypal_subscription_id ) && 'paypal' === get_post_meta( $post->ID, '_payment_method', true ) ) {
    $recurring_status   = get_post_meta( $post->ID, '_recurring_status', true );
    $next_payment       = get_post_meta( $post->ID, '_recurring_next_payment_date', true );
    $recurring_interval = get_post_meta( $post->ID, '_recurring_interval', true );
    $paypal_plan_id     = get_post_meta( $post->ID, '_paypal_plan_id', true );
    ?>
    <div class="giftflow-recurring-details" style="margin-top:1em;padding:1em;background:#f0f0f1;border-left:4px solid #0070ba;">
        <p><strong><?php esc_html_e( 'Recurring (PayPal)', 'giftflow' ); ?></strong></p>
        <p>
            <?php esc_html_e( 'Subscription ID:', 'giftflow' ); ?>
            <code><?php echo esc_html( $paypal_subscription_id ); ?></code>
        </p>
        <?php if ( $paypal_plan_id ) : ?>
        <p>
            <?php esc_html_e( 'Plan ID:', 'giftflow' ); ?>
            <code><?php echo esc_html( $paypal_plan_id ); ?></code>
        </p>
        <?php endif; ?>
        <p><?php esc_html_e( 'Status:', 'giftflow' ); ?> <?php echo esc_html( $recurring_status ?? '—' ); ?></p>
        <p><?php esc_html_e( 'Interval:', 'giftflow' ); ?> <?php echo esc_html( $recurring_interval ?? '—' ); ?></p>
        <?php if ( $next_payment ) : ?>
            <p><?php esc_html_e( 'Next payment:', 'giftflow' ); ?> <?php echo esc_html( gmdate( get_option( 'date_format' ), strtotime( $next_payment ) ) ); ?></p>
        <?php endif; ?>
        <?php if ( $recurring_status && ! in_array( $recurring_status, array( 'cancelled', 'expired' ), true ) ) : ?>
            <p>
                <button type="button" class="button giftflow-cancel-paypal-subscription" data-donation-id="<?php echo esc_attr( (string) $post->ID ); ?>">
                    <?php esc_html_e( 'Cancel subscription', 'giftflow' ); ?>
                </button>
                <span class="giftflow-paypal-cancel-result" style="margin-left:8px;"></span>
            </p>
            <script>
            jQuery( function( $ ) {
                $( '.giftflow-cancel-paypal-subscription' ).on( 'click', function() {
                    var btn = $( this ), id = btn.data( 'donation-id' ), result = btn.siblings( '.giftflow-paypal-cancel-result' );
                    if ( ! confirm( '<?php echo esc_js( __( 'Are you sure you want to cancel this PayPal subscription?', 'giftflow' ) ); ?>' ) ) return;
                    btn.prop( 'disabled', true );
                    result.text( '<?php echo esc_js( __( 'Cancelling…', 'giftflow' ) ); ?>' );
                    $.post( ajaxurl, {
                        action: 'giftflow_paypal_cancel_subscription',
                        nonce: '<?php echo esc_js( wp_create_nonce( 'giftflow_paypal_nonce' ) ); ?>',
                        donation_id: id
                    } ).done( function( r ) {
                        if ( r.success ) {
                            result.text( r.data && r.data.message ? r.data.message : '<?php echo esc_js( __( 'Cancelled.', 'giftflow' ) ); ?>' );
                            location.reload();
                        } else {
                            result.text( r.data && r.data.message ? r.data.message : '<?php echo esc_js( __( 'Error.', 'giftflow' ) ); ?>' );
                            btn.prop( 'disabled', false );
                        }
                    } ).fail( function() {
                        result.text( '<?php echo esc_js( __( 'Request failed.', 'giftflow' ) ); ?>' );
                        btn.prop( 'disabled', false );
                    } );
                } );
            } );
            </script>
        <?php endif; ?>
    </div>
    <?php
}
```

---

## Phase 9 — Cancellation & Management

### 9.1 — Admin cancellation AJAX action

**File:** `includes/gateways/class-paypal.php`

Register in `init_additional_hooks()`:

```php
add_action( 'wp_ajax_giftflow_paypal_cancel_subscription', array( $this, 'ajax_cancel_subscription' ) );
```

Add the **public** handler:

```php
/**
 * AJAX handler: cancel a PayPal subscription from the admin.
 */
public function ajax_cancel_subscription() {
    check_ajax_referer( 'giftflow_paypal_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Unauthorized', 'giftflow' ) ) );
    }

    $donation_id     = isset( $_POST['donation_id'] ) ? absint( $_POST['donation_id'] ) : 0;
    $subscription_id = get_post_meta( $donation_id, '_paypal_subscription_id', true );

    if ( empty( $subscription_id ) ) {
        wp_send_json_error( array( 'message' => __( 'No PayPal subscription found for this donation.', 'giftflow' ) ) );
    }

    $base_url     = $this->get_paypal_base_url();
    $access_token = $this->get_paypal_access_token( $base_url );

    if ( ! $access_token ) {
        wp_send_json_error( array( 'message' => __( 'Failed to authenticate with PayPal', 'giftflow' ) ) );
    }

    $response = wp_remote_post(
        $base_url . '/v1/billing/subscriptions/' . $subscription_id . '/cancel',
        array(
            'headers' => array(
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $access_token,
            ),
            'body'    => wp_json_encode(
                array(
                    'reason' => __( 'Cancelled by admin via GiftFlow', 'giftflow' ),
                )
            ),
            'timeout' => 30,
        )
    );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ) );
    }

    $code = wp_remote_retrieve_response_code( $response );

    // PayPal returns 204 No Content on successful cancellation.
    if ( 204 !== $code ) {
        $body      = json_decode( wp_remote_retrieve_body( $response ), true );
        $error_msg = isset( $body['message'] ) ? $body['message'] : __( 'Failed to cancel subscription', 'giftflow' );
        wp_send_json_error( array( 'message' => $error_msg ) );
    }

    update_post_meta( $donation_id, '_recurring_status', 'cancelled' );

    Donation_Event_History::add(
        $donation_id,
        'recurring_subscription_cancelled',
        'cancelled',
        __( 'PayPal subscription cancelled by admin.', 'giftflow' ),
        array(
            'subscription_id' => $subscription_id,
            'gateway'         => 'paypal',
        )
    );

    Giftflow_Logger::info(
        'paypal.subscription.cancelled_by_admin',
        array(
            'donation_id'     => $donation_id,
            'subscription_id' => $subscription_id,
            'gateway'         => 'paypal',
        ),
        'paypal'
    );

    wp_send_json_success( array( 'message' => __( 'PayPal subscription cancelled successfully.', 'giftflow' ) ) );
}
```

### 9.2 — Self-service cancellation (deferred)

Document as a separate task when ready. Could use a signed URL approach similar to Stripe self-service cancellation.

---

## Hook Reference

All new action hooks fired during PayPal recurring flows.

| Hook | When fired | Arguments |
|---|---|---|
| `giftflow_paypal_subscription_created` | After PayPal subscription is created | `$donation_id, $subscription_id, $subscription_array` |
| `giftflow_paypal_subscription_return` | When donor returns from PayPal approval | `$donation_id, $subscription_id, $subscription_data` |
| `giftflow_paypal_subscription_created_webhook` | On `BILLING.SUBSCRIPTION.CREATED` webhook | `$donation_id, $subscription_id, $resource` |
| `giftflow_paypal_subscription_activated` | On `BILLING.SUBSCRIPTION.ACTIVATED` webhook | `$donation_id, $subscription_id, $resource` |
| `giftflow_paypal_subscription_cancelled` | On `BILLING.SUBSCRIPTION.CANCELLED` webhook | `$donation_id, $subscription_id, $resource` |
| `giftflow_paypal_subscription_suspended` | On `BILLING.SUBSCRIPTION.SUSPENDED` webhook | `$donation_id, $subscription_id, $resource` |
| `giftflow_paypal_subscription_expired` | On `BILLING.SUBSCRIPTION.EXPIRED` webhook | `$donation_id, $subscription_id, $resource` |
| `giftflow_paypal_recurring_renewal_created` | After a renewal child donation is created | `$renewal_id, $parent_id, $subscription_id, $resource` |
| `giftflow_paypal_recurring_payment_denied` | On `PAYMENT.SALE.DENIED` webhook (subscription) | `$parent_donation_id, $subscription_id, $resource` |

### Existing hooks used (unchanged)

- `giftflow_paypal_payment_completed` — still fires on one-time capture success
- `giftflow_donation_status_updated` — fires when `Donations::update_status()` is called
- `giftflow_donation_after_payment_processed` — fires after payment (one-time and first recurring)

---

## Testing Checklist

Use PayPal sandbox for all testing. Configure webhook forwarding or use a tool like ngrok for local development.

### Admin product creation

- [ ] Enable PayPal recurring in gateway settings
- [ ] Verify admin notice appears: "PayPal Donation Product has not been created yet."
- [ ] Click "Create PayPal Product" button
- [ ] Product ID stored in `giftflow_paypal_product_id` option
- [ ] Admin notice disappears after page reload
- [ ] Product name in PayPal is "Giftflow Donation"

### One-time payment (regression — must still pass)

- [ ] Submit donation form with `donation_type = one-time` and `payment_method = paypal`
- [ ] PayPal Smart Buttons appear and function normally
- [ ] Donation status becomes `completed`
- [ ] `_transaction_id` is set to a capture/sale ID
- [ ] No `_paypal_subscription_id` meta created

### First recurring charge

- [ ] Submit form with `donation_type = recurring`, `recurring_interval = monthly`, `payment_method = paypal`
- [ ] PayPal Smart Buttons are hidden; "Subscribe with PayPal" button appears
- [ ] Clicking the button sends AJAX to `giftflow_paypal_create_subscription`
- [ ] PayPal Product already created via admin notice button (stored in `giftflow_paypal_product_id` option)
- [ ] PayPal Plan created (if not cached) and stored in `giftflow_paypal_plan_{amount}_{interval}` option
- [ ] PayPal Subscription created (`I-XXXXX`)
- [ ] User redirected to PayPal approval page
- [ ] After approval, redirected back to return URL
- [ ] Parent donation post created with `_is_subscription_parent = 1`
- [ ] Webhook `BILLING.SUBSCRIPTION.CREATED` received — event history entry logged
- [ ] Webhook `BILLING.SUBSCRIPTION.ACTIVATED` received
- [ ] Parent donation status becomes `completed`
- [ ] `_recurring_status = active` on parent

### Plan reuse

- [ ] Submit another recurring donation with the same amount/interval
- [ ] Verify no new plan is created (same plan_id reused)
- [ ] Check `wp_options` — only one plan entry for that key (e.g. `giftflow_paypal_plan_10_month`)

### Renewal charge (PayPal sandbox may auto-trigger)

- [ ] Wait for PayPal to trigger next billing cycle (or use sandbox time manipulation)
- [ ] `PAYMENT.SALE.COMPLETED` webhook received with `billing_agreement_id`
- [ ] New child donation post created with `_is_subscription_renewal = 1`
- [ ] Child has `_parent_donation_id` pointing to parent
- [ ] Child status is `completed`
- [ ] Parent `_recurring_next_payment_date` updated
- [ ] `giftflow_paypal_recurring_renewal_created` action fires

### Failed renewal

- [ ] `PAYMENT.SALE.DENIED` webhook received
- [ ] No child donation created
- [ ] Parent `_recurring_status` updated to `suspended`
- [ ] `Donation_Event_History` entry logged on parent

### Subscription cancelled via webhook

- [ ] Cancel subscription from PayPal dashboard
- [ ] `BILLING.SUBSCRIPTION.CANCELLED` webhook received
- [ ] Parent `_recurring_status = cancelled`
- [ ] Event history entry added

### Admin cancellation

- [ ] Open parent donation in admin
- [ ] PayPal recurring details section visible (blue accent)
- [ ] Click "Cancel subscription"
- [ ] Confirmation dialog shown
- [ ] PayPal subscription cancelled via API (204 response)
- [ ] `_recurring_status` updated to `cancelled`
- [ ] Confirmation message shown; page reloads

### Cancel URL

- [ ] During PayPal approval, click "Cancel and return"
- [ ] Redirected back to cancel URL
- [ ] Donation marked as `failed`, recurring_status as `cancelled`

### Duplicate plan prevention

- [ ] Simulate two concurrent requests for the same plan
- [ ] Verify only one plan is created (transient lock works)

---

## File Map

Summary of every file that must be created or modified.

| Action | File | Description |
|---|---|---|
| **MODIFY** | `includes/gateways/class-paypal.php` | All backend recurring logic (Phases 1, 2, 3, 5, 6, 9) |
| **MODIFY** | `includes/core/class-donations.php` | Add PayPal recurring meta to `get()`, `save_donation_meta()` (Phase 7) |
| **MODIFY** | `admin/includes/meta-boxes/class-donation-transaction-meta.php` | Display PayPal subscription details in admin (Phase 8) |
| **MODIFY** | `assets/js/paypal-donation.js` | Add recurring flow: hide Smart Buttons, show subscribe button, redirect to PayPal (Phase 4) |
| **VERIFY** | `templates/payment-gateway/paypal-template.php` | Confirm no changes needed (button container is reused) |
| **VERIFY** | `includes/frontend/class-shortcodes.php` | Already passes `recurring_interval` and `recurring_number_of_times` — no changes |
| **VERIFY** | `admin/includes/meta-boxes/class-campaign-details-meta.php` | Already has `recurring`, `recurring_interval`, `recurring_number_of_times` fields — no changes |

No new PHP class files are needed. All logic lives inside the existing `PayPal_Gateway` class using `wp_remote_post()` / `wp_remote_get()` for PayPal REST API calls.

---

## Architecture Decisions & Rationale

### Why server-side subscription + redirect (not JS SDK `createSubscription`)?

PayPal JS SDK v6 loaded with `intent=capture` does not support `createSubscription`. Supporting both one-time and recurring would require either loading two SDK instances (unreliable) or dynamically reloading the SDK (complex). The server-side REST API approach:

1. Works alongside the existing Smart Buttons without conflict.
2. Is the standard approach used by major WordPress plugins (WooCommerce, Give, etc.).
3. Avoids SDK initialization complexity.
4. Keeps all subscription logic server-side where it can be properly logged and debugged.

### Why `wp_options` for plan caching (not a custom table)?

1. Plans are a small, bounded dataset (one option per unique amount/currency/interval).
2. `wp_options` with autoload=false is efficient for lookup operations.
3. No database migrations required on plugin install/update.
4. Clean uninstall: `delete_option()` during plugin uninstall.
5. Works in all WordPress hosting environments without custom table creation.

### Why transient locks for plan creation?

PayPal plans are API-created resources that must not be duplicated. Multiple concurrent form submissions with the same parameters could create duplicate plans without locking. A 30-second transient lock ensures only one request creates the plan while others wait and read the cached result.
