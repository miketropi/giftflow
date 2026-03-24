# Stripe Recurring Payments — Feature Development Roadmap

---

## Table of Contents

1. [Current State Summary](#1-current-state-summary)
2. [Feature Scope](#2-feature-scope)
3. [Stripe API Strategy](#3-stripe-api-strategy)
4. [Data Schema](#4-data-schema)
5. [Phase 1 — Gateway: Add Recurring Support Flag](#phase-1--gateway-add-recurring-support-flag)
6. [Phase 2 — Admin Settings: Recurring Options](#phase-2--admin-settings-recurring-options)
7. [Phase 3 — PHP Backend: Customer & Subscription Creation](#phase-3--php-backend-customer--subscription-creation)
8. [Phase 4 — Webhook: Handle Subscription Events](#phase-4--webhook-handle-subscription-events)
9. [Phase 5 — Donations Class: Recurring Meta](#phase-5--donations-class-recurring-meta)
10. [Phase 6 — Frontend JS: Recurring UI](#phase-6--frontend-js-recurring-ui)
11. [Phase 7 — Admin UI: Display Subscription Details](#phase-7--admin-ui-display-subscription-details)
12. [Phase 8 — Cancellation & Management](#phase-8--cancellation--management)
13. [Hook Reference](#hook-reference)
14. [Testing Checklist](#testing-checklist)
15. [File Map](#file-map)

---

## 1. Current State Summary

### Key files

| File | Role |
|---|---|
| `includes/gateways/class-stripe.php` | `Stripe_Gateway` — one-time PaymentIntent flow |
| `includes/gateways/class-gateway-base.php` | `Gateway_Base` — abstract base all gateways extend |
| `includes/core/class-donations.php` | `Donations` — CRUD for `donation` CPT, meta storage |
| `includes/core/class-donation-event-history.php` | Audit trail per donation |
| `includes/core/class-logger.php` | `Giftflow_Logger` — structured error/info logging |
| `includes/frontend/class-forms.php` | AJAX handler `giftflow_donation_form` — creates donation post, calls gateway |
| `includes/frontend/class-shortcodes.php` | Renders donation form shortcode; builds `$donation_types` from campaign options |
| `admin/includes/meta-boxes/class-campaign-details-meta.php` | Campaign CPT meta box: recurring on/off, recurring_interval, recurring_number_of_times |
| `admin/includes/meta-boxes/class-donation-transaction-meta.php` | Admin meta box for donation detail |

### What `Stripe_Gateway` currently does

- Creates a Stripe **PaymentIntent** via `process_payment()`.
- Handles 3D Secure redirect via `handle_return_url()` (checks `?giftflow_stripe_return`).
- Listens to webhook events: `payment_intent.succeeded`, `payment_intent.payment_failed`, `charge.refunded`, `payment_intent.canceled`.
- Declares `$this->supports = ['webhooks', '3d_secure', 'payment_intents']`.

### Meta keys already stored on `donation` post

| Meta Key | Description |
|---|---|
| `_amount` | Donation amount |
| `_status` | `pending / completed / failed / refunded` |
| `_payment_method` | Gateway ID string (`stripe`) |
| `_donation_type` | `once` or `monthly` (field exists, not yet enforced by gateway) |
| `_recurring_interval` | Interval string (`monthly`, `yearly`, etc.) — stored but unused by Stripe |
| `_transaction_id` | PaymentIntent ID |
| `_stripe_payment_intent_id` | Duplicate store for lookup queries |
| `_stripe_charge_id` | Charge ID |
| `_transaction_raw_data` | JSON-encoded raw Stripe response |

### Existing PHP namespaces/uses

```php
namespace GiftFlow\Gateways;

use GiftFlow\Vendor\Stripe\Stripe;
use GiftFlow\Vendor\Stripe\PaymentIntent;
use GiftFlow\Vendor\Stripe\StripeClient;
use GiftFlow\Vendor\Stripe\Exception\ApiErrorException;
use GiftFlow\Vendor\Stripe\Webhook;
use GiftFlow\Core\Donations;
use GiftFlow\Core\Logger as Giftflow_Logger;
use GiftFlow\Core\Donation_Event_History;
```

The Stripe PHP SDK is already vendored under `GiftFlow\Vendor\Stripe\`.

---

## 2. Feature Scope

### In scope

- Donor can choose a **recurring interval** on the donation form: monthly or yearly.
- First charge: processed immediately as part of the subscription creation.
- Subsequent charges: handled automatically by Stripe, confirmed via webhook.
- Each Stripe `invoice.paid` event creates a **new child donation post** linked to the original (parent) subscription donation.
- Donor can cancel their subscription from a self-service link (Phase 8, can be deferred).
- Admin can cancel subscriptions from the donation meta box.
- All existing one-time payment behavior is **unchanged**.

### Out of scope (document separately if needed)

- Custom billing cycles beyond monthly/yearly.
- Proration or plan upgrades.
- Free trial periods.
- Donor portal (full account management).

---

## 3. Stripe API Strategy

Use **Stripe Subscriptions** (not PaymentIntents manually repeated):

```
Donor submits form
  → Backend creates/retrieves Stripe Customer
  → Backend attaches PaymentMethod to Customer
  → Backend creates Stripe Price (dynamic, amount × interval)
  → Backend creates Stripe Subscription (with payment_behavior: 'default_incomplete')
  → Subscription generates a draft Invoice + PaymentIntent
  → Backend confirms the PaymentIntent (handles SCA/3DS same as today)
  → On success: store subscription_id on the donation post
  → Future charges: Stripe fires `invoice.paid` → webhook creates child donation
```

### Stripe SDK classes required

Add these `use` statements to `class-stripe.php`:

```php
use GiftFlow\Vendor\Stripe\Customer;
use GiftFlow\Vendor\Stripe\Price;
use GiftFlow\Vendor\Stripe\Subscription;
```

---

## 4. Data Schema

All data is stored as WordPress post meta. **No new database tables are required.**

### New meta keys on the parent `donation` post

| Meta Key | Type | Description |
|---|---|---|
| `_stripe_customer_id` | string | Stripe Customer ID (`cus_xxx`) |
| `_stripe_subscription_id` | string | Stripe Subscription ID (`sub_xxx`) |
| `_stripe_price_id` | string | Stripe Price ID (`price_xxx`) used for this subscription |
| `_recurring_status` | string | `active / cancelled / past_due / unpaid` |
| `_recurring_next_payment_date` | string | ISO 8601 date of next scheduled charge |
| `_recurring_number_of_times` | int | Number of payments (from campaign at creation); 0 = ongoing. Webhook cancels subscription when renewal count reaches this. |
| `_is_subscription_parent` | bool (`1`) | Marks this donation as the originating subscription record |

### New meta keys on child `donation` posts (each renewal)

| Meta Key | Type | Description |
|---|---|---|
| `_parent_donation_id` | int | Post ID of the original subscription donation |
| `_stripe_invoice_id` | string | Stripe Invoice ID (`in_xxx`) |
| `_stripe_subscription_id` | string | Same subscription ID (allows lookup from child) |
| `_is_subscription_renewal` | bool (`1`) | Marks this as an auto-renewal charge |

### Updated `Donations::get()` return array

The agent must add these keys to the array inside `includes/core/class-donations.php` → `get()` method (around line 255):

```php
'stripe_customer_id'      => isset( $meta['_stripe_customer_id'][0] )      ? $meta['_stripe_customer_id'][0]      : '',
'stripe_subscription_id'  => isset( $meta['_stripe_subscription_id'][0] )  ? $meta['_stripe_subscription_id'][0]  : '',
'recurring_status'         => isset( $meta['_recurring_status'][0] )         ? $meta['_recurring_status'][0]         : '',
'recurring_next_payment'   => isset( $meta['_recurring_next_payment_date'][0] ) ? $meta['_recurring_next_payment_date'][0] : '',
'is_subscription_parent'   => isset( $meta['_is_subscription_parent'][0] )  ? (bool) $meta['_is_subscription_parent'][0] : false,
'parent_donation_id'       => isset( $meta['_parent_donation_id'][0] )      ? intval( $meta['_parent_donation_id'][0] ) : 0,
```

---

## Phase 1 — Gateway: Add Recurring Support Flag

**File:** `includes/gateways/class-stripe.php`
**Method:** `init_gateway()` (line 57)

### Task

Add `'recurring'` to the `$this->supports` array:

```php
$this->supports = array(
    'webhooks',
    '3d_secure',
    'payment_intents',
    'recurring',  // ADD THIS
);
```

This allows other parts of the plugin to detect recurring capability via:

```php
$gateway = Gateway_Base::get_gateway( 'stripe' );
if ( in_array( 'recurring', $gateway->get_supports(), true ) ) { ... }
```

---

## Phase 2 — Recurring Options: Per-Campaign (Meta Box) + Shortcode + Gateway

Recurring behaviour is controlled in two places: **per-campaign** in the Campaign Details meta box, and **globally** for the Stripe gateway. The donation form shortcode builds `$donation_types` from **campaign** options only.

---

### 2.A — Per-campaign options (Campaign CPT meta box)

**File:** `admin/includes/meta-boxes/class-campaign-details-meta.php`  
**Method:** `get_fields()` → `'regular'` array

These options are **per campaign**. Each campaign can enable one-time and/or recurring and set the recurring interval and number of payments.

| Field key | Type | Meta key | Description |
|-----------|------|----------|-------------|
| `recurring` | switch | `_recurring` | Recurring on/off for this campaign. |
| `recurring_interval` | select | `_recurring_interval` | Interval: `daily`, `weekly`, `monthly`, `quarterly`, `yearly`. Default `monthly`. |
| `recurring_number_of_times` | number | `_recurring_number_of_times` | Number of payments (e.g. 12 = monthly for 12 months). **0 = ongoing** until cancelled. |

- **Already present:** `recurring`, `recurring_interval` (see existing `get_fields()` in the file).
- **Added:** `recurring_number_of_times` — type `number`, `min` 0, `step` 1, default 0. Description: *"How many times to charge (e.g. 12 for monthly donations over 12 months). Leave 0 for ongoing until cancelled."*
- **Save:** In `save_meta_box()`, save `recurring_number_of_times` with `absint()`; all other regular fields stay as-is.

Stripe does not natively support “cancel after N payments”; the plugin must enforce this (e.g. in the `invoice.paid` webhook handler: count renewals for that subscription and cancel the subscription when count reaches `recurring_number_of_times` from the parent donation/campaign).

---

### 2.B — Donation form shortcode: `donation_types` from campaign options

**File:** `includes/frontend/class-shortcodes.php`  
**Method:** `render_donation_form()`

The form is rendered via the shortcode `[giftflow_donation_form campaign_id="…"]`. **Donation type choices (one-time vs recurring) come only from the campaign**, not from the gateway.

1. **Build `$donation_types`:**
   - If campaign meta `_one_time` is truthy → add a type with `'name' => 'one-time'` (label e.g. “One-time Donation”).
   - If campaign meta `_recurring` is truthy → add a type with `'name' => 'recurring'` (label e.g. “Recurring Donation”).
2. **Pass recurring options into the template** (so the form can show interval and “number of times” when recurring is selected):
   - `$recurring_interval` = `get_post_meta( $campaign_id, '_recurring_interval', true )` (default `'monthly'` if empty).
   - `$recurring_number_of_times` = `absint( get_post_meta( $campaign_id, '_recurring_number_of_times', true ) )`.
3. **Add to `$atts`** (and thus to the template):
   - `$atts['donation_types']` = result of the above (optionally filtered by `giftflow_form_donation_types`).
   - `$atts['recurring_interval']` = `$recurring_interval`.
   - `$atts['recurring_number_of_times']` = `$recurring_number_of_times`.

So: **recurring is shown on the form only when the campaign has recurring enabled**; interval and number of times are the campaign’s values.

---

### 2.C — Global Stripe gateway: “Enable recurring” (optional)

**File:** `includes/gateways/class-stripe.php`  
**Method:** `register_settings_fields()` (line ~205)

A **global** switch can gate whether the Stripe gateway accepts recurring at all. Inside the Stripe accordion `fields` array (e.g. after `stripe_apple_pay_google_pay_enabled`), add:

```php
'stripe_recurring_enabled' => array(
    'id'          => 'giftflow_stripe_recurring_enabled',
    'type'        => 'switch',
    'label'       => __( 'Enable Recurring Donations', 'giftflow' ),
    'value'       => isset( $payment_options['stripe']['stripe_recurring_enabled'] )
                     ? $payment_options['stripe']['stripe_recurring_enabled']
                     : false,
    'description' => __( 'Allow donors to set up recurring donations via Stripe Subscriptions. Per-campaign recurring options are in Campaign Details.', 'giftflow' ),
),
```

- When deciding whether to run the recurring flow in `process_payment()`, the gateway should consider **both** this global setting **and** the payment data (e.g. `donation_type` / `recurring_interval` from the form, which ultimately come from the campaign).
- Reading: `$this->get_setting( 'stripe_recurring_enabled', false );`

---

## Phase 3 — PHP Backend: Customer & Subscription Creation

**File:** `includes/gateways/class-stripe.php`

### 3.1 — Route inside `process_payment()`

The `process_payment()` method (line 348) must detect whether the donation is recurring and branch accordingly:

```php
public function process_payment( $data, $donation_id = 0 ) {
    if ( ! $this->stripe ) {
        return new \WP_Error( 'stripe_error', __( 'Stripe is not properly configured', 'giftflow' ) );
    }
    if ( ! $donation_id ) {
        return new \WP_Error( 'stripe_error', __( 'Donation ID is required', 'giftflow' ) );
    }

    $is_recurring = $this->is_recurring_donation( $data );

    try {
        if ( $is_recurring ) {
            return $this->process_recurring_payment( $data, $donation_id );
        }
        // Existing one-time flow.
        $payment_intent_data = $this->prepare_payment_intent_data( $data, $donation_id );
        $payment_intent      = $this->stripe->paymentIntents->create( $payment_intent_data );
        update_post_meta( $donation_id, '_stripe_payment_intent_id', $payment_intent->id );
        return $this->handle_payment_intent_response( $payment_intent, $donation_id );
    } catch ( ApiErrorException $e ) {
        $this->log_error( 'payment_exception', $e->getMessage(), $donation_id, $e->getStripeCode() );
        return new \WP_Error( 'stripe_error', $e->getMessage() );
    } catch ( \Exception $e ) {
        $this->log_error( 'payment_exception', $e->getMessage(), $donation_id );
        return new \WP_Error( 'stripe_error', $e->getMessage() );
    }
}
```

### 3.2 — Helper: `is_recurring_donation()`

Add as a **private method**:

```php
/**
 * Determine if the current donation should be processed as recurring.
 *
 * @param array $data Donation form data.
 * @return bool
 */
private function is_recurring_donation( $data ) {
    $recurring_enabled = $this->get_setting( 'stripe_recurring_enabled', false );
    if ( ! $recurring_enabled ) {
        return false;
    }

    $donation_type = isset( $data['donation_type'] ) ? sanitize_text_field( $data['donation_type'] ) : 'once';
    $interval      = isset( $data['recurring_interval'] ) ? sanitize_text_field( $data['recurring_interval'] ) : '';

    return ( 'once' !== $donation_type && ! empty( $interval ) );
}
```

### 3.3 — Core method: `process_recurring_payment()`

Add as a **private method**. This orchestrates the full subscription flow:

```php
/**
 * Process recurring (subscription) payment via Stripe.
 *
 * @param array $data        Donation form data.
 * @param int   $donation_id Donation post ID.
 * @return array|\WP_Error
 */
private function process_recurring_payment( $data, $donation_id ) {
    // 1. Get or create Stripe Customer.
    $customer_id = $this->get_or_create_stripe_customer( $data, $donation_id );
    if ( is_wp_error( $customer_id ) ) {
        return $customer_id;
    }

    // 2. Attach the PaymentMethod to the Customer.
    $this->stripe->paymentMethods->attach(
        $data['payment_method_id'],
        array( 'customer' => $customer_id )
    );

    // 3. Set as default payment method on Customer.
    $this->stripe->customers->update(
        $customer_id,
        array(
            'invoice_settings' => array(
                'default_payment_method' => $data['payment_method_id'],
            ),
        )
    );

    // 4. Create a dynamic Stripe Price.
    $interval    = sanitize_text_field( $data['recurring_interval'] ); // 'month' or 'year'
    $amount_cents = (int) ( (float) $data['donation_amount'] * 100 );

    $price = $this->stripe->prices->create(
        array(
            'unit_amount'  => $amount_cents,
            'currency'     => strtolower( $this->get_currency() ),
            'recurring'    => array( 'interval' => $interval ),
            'product_data' => array(
                'name' => sprintf(
                    // translators: 1: site name, 2: interval label.
                    __( '%1$s Recurring Donation (%2$s)', 'giftflow' ),
                    get_bloginfo( 'name' ),
                    $interval
                ),
            ),
        )
    );

    // 5. Create Subscription with default_incomplete so first invoice is created.
    $subscription = $this->stripe->subscriptions->create(
        array(
            'customer'               => $customer_id,
            'items'                  => array( array( 'price' => $price->id ) ),
            'payment_behavior'       => 'default_incomplete',
            'payment_settings'       => array(
                'payment_method_types'  => array( 'card' ),
                'save_default_payment_method' => 'on_subscription',
            ),
            'expand'                 => array( 'latest_invoice.payment_intent' ),
            'metadata'               => array(
                'donation_id'  => (string) $donation_id,
                'campaign_id'  => (string) $data['campaign_id'],
                'donor_email'  => sanitize_email( $data['donor_email'] ),
                'donor_name'   => sanitize_text_field( $data['donor_name'] ),
                'site_url'     => home_url(),
            ),
        )
    );

    // 6. Store subscription meta.
    update_post_meta( $donation_id, '_stripe_customer_id',     $customer_id );
    update_post_meta( $donation_id, '_stripe_subscription_id', $subscription->id );
    update_post_meta( $donation_id, '_stripe_price_id',        $price->id );
    update_post_meta( $donation_id, '_donation_type',          'recurring' );
    update_post_meta( $donation_id, '_recurring_interval',     $interval );
    update_post_meta( $donation_id, '_recurring_status',       'active' );
    update_post_meta( $donation_id, '_is_subscription_parent', '1' );

    if ( isset( $subscription->current_period_end ) ) {
        update_post_meta(
            $donation_id,
            '_recurring_next_payment_date',
            gmdate( 'Y-m-d\TH:i:s\Z', $subscription->current_period_end )
        );
    }

    // 7. Handle the first PaymentIntent (may require 3DS).
    $payment_intent = $subscription->latest_invoice->payment_intent;

    update_post_meta( $donation_id, '_stripe_payment_intent_id', $payment_intent->id );

    $result = $this->handle_payment_intent_response( $payment_intent, $donation_id );

    // 8. Fire action for extensibility.
    do_action( 'giftflow_stripe_subscription_created', $donation_id, $subscription->id, $subscription->toArray() );

    return $result;
}
```

### 3.4 — Helper: `get_or_create_stripe_customer()`

Add as a **private method**:

```php
/**
 * Get existing Stripe Customer for this donor or create a new one.
 *
 * Looks up `_stripe_customer_id` on any previous donation by the same email
 * before creating a new Customer object.
 *
 * @param array $data        Donation form data.
 * @param int   $donation_id Donation post ID.
 * @return string|\WP_Error Stripe Customer ID or WP_Error.
 */
private function get_or_create_stripe_customer( $data, $donation_id ) {
    $email = sanitize_email( $data['donor_email'] );

    // Check if this donor already has a Stripe customer on a previous donation.
    $existing = get_posts(
        array(
            'post_type'      => 'donation',
            'posts_per_page' => 1,
            'meta_query'     => array(    // phpcs:ignore WordPress.DB.SlowDBQuery
                array(
                    'key'     => '_stripe_customer_id',
                    'compare' => 'EXISTS',
                ),
                array(
                    // phpcs:ignore WordPress.DB.SlowDBQuery
                    'key'   => '_payment_method',
                    'value' => 'stripe',
                ),
            ),
            'meta_key'       => '_stripe_customer_id',  // phpcs:ignore WordPress.DB.SlowDBQuery
        )
    );

    // Verify this customer email matches by cross-checking donor meta.
    foreach ( $existing as $post ) {
        $donor_id = get_post_meta( $post->ID, '_donor_id', true );
        if ( $donor_id ) {
            $donor_email = get_post_meta( $donor_id, '_email', true );
            if ( $donor_email === $email ) {
                $cid = get_post_meta( $post->ID, '_stripe_customer_id', true );
                if ( ! empty( $cid ) ) {
                    return $cid;
                }
            }
        }
    }

    // Create a new Stripe Customer.
    try {
        $customer = $this->stripe->customers->create(
            array(
                'email'    => $email,
                'name'     => sanitize_text_field( $data['donor_name'] ),
                'metadata' => array(
                    'site_url'    => home_url(),
                    'donation_id' => (string) $donation_id,
                ),
            )
        );
        return $customer->id;
    } catch ( ApiErrorException $e ) {
        return new \WP_Error( 'stripe_customer_error', $e->getMessage() );
    }
}
```

---

## Phase 4 — Webhook: Handle Subscription Events

**File:** `includes/gateways/class-stripe.php`
**Method:** `handle_webhook()` (line 603)

### 4.1 — Register new Stripe webhook events

In the Stripe Dashboard (or via the Stripe CLI for local development), add these events to the webhook endpoint:

```
invoice.paid
invoice.payment_failed
customer.subscription.deleted
customer.subscription.updated
```

The webhook URL is already registered: `admin-ajax.php?action=giftflow_stripe_webhook`.

**Note in the admin settings description** for `stripe_webhook_enabled` (already at line 274 of `class-stripe.php`): update the displayed recommended events to include the four new events above.

### 4.2 — Extend the `switch` in `handle_webhook()`

Inside the existing `switch ( $event->type )` block, add four new cases **before** the closing brace:

```php
case 'invoice.paid':
    $this->handle_invoice_paid( $event->data->object );
    break;

case 'invoice.payment_failed':
    $this->handle_invoice_payment_failed( $event->data->object );
    break;

case 'customer.subscription.deleted':
    $this->handle_subscription_deleted( $event->data->object );
    break;

case 'customer.subscription.updated':
    $this->handle_subscription_updated( $event->data->object );
    break;
```

### 4.3 — Handler: `handle_invoice_paid()`

This is the most critical handler. It fires for **every successful charge** including the first one.

```php
/**
 * Handle Stripe invoice.paid webhook event.
 *
 * For the first invoice this confirms the initial donation.
 * For subsequent invoices this creates a child renewal donation post.
 *
 * @param object $invoice Stripe Invoice object from webhook.
 */
private function handle_invoice_paid( $invoice ) {
    $subscription_id = isset( $invoice->subscription ) ? $invoice->subscription : '';

    if ( empty( $subscription_id ) ) {
        return;
    }

    // Find the parent donation post by subscription ID.
    $parent_donations = get_posts(
        array(
            'post_type'      => 'donation',
            'posts_per_page' => 1,
            // phpcs:ignore WordPress.DB.SlowDBQuery
            'meta_key'       => '_stripe_subscription_id',
            // phpcs:ignore WordPress.DB.SlowDBQuery
            'meta_value'     => $subscription_id,
            'meta_query'     => array(   // phpcs:ignore WordPress.DB.SlowDBQuery
                array(
                    'key'   => '_is_subscription_parent',
                    'value' => '1',
                ),
            ),
        )
    );

    if ( empty( $parent_donations ) ) {
        $this->log_error( 'webhook_error', 'invoice.paid: parent donation not found for subscription ' . $subscription_id, 0 );
        return;
    }

    $parent_donation_id = $parent_donations[0]->ID;
    $invoice_id         = isset( $invoice->id ) ? $invoice->id : '';
    $payment_intent_id  = isset( $invoice->payment_intent ) ? $invoice->payment_intent : '';

    // Check if this invoice already has a donation (idempotency).
    $existing = get_posts(
        array(
            'post_type'      => 'donation',
            'posts_per_page' => 1,
            // phpcs:ignore WordPress.DB.SlowDBQuery
            'meta_key'       => '_stripe_invoice_id',
            // phpcs:ignore WordPress.DB.SlowDBQuery
            'meta_value'     => $invoice_id,
        )
    );

    if ( ! empty( $existing ) ) {
        // Already processed; update parent and return.
        $this->update_parent_after_renewal( $parent_donation_id, $invoice );
        return;
    }

    // Determine if this is the first invoice (billing_reason = 'subscription_create').
    $is_first_invoice = ( isset( $invoice->billing_reason ) && 'subscription_create' === $invoice->billing_reason );

    if ( $is_first_invoice ) {
        // Mark the parent donation as completed.
        update_post_meta( $parent_donation_id, '_transaction_id', $payment_intent_id );
        update_post_meta( $parent_donation_id, '_stripe_payment_intent_id', $payment_intent_id );
        update_post_meta( $parent_donation_id, '_stripe_invoice_id', $invoice_id );
        update_post_meta( $parent_donation_id, '_transaction_raw_data', wp_json_encode( (array) $invoice ) );

        $donations_class = new Donations();
        $donations_class->update_status( $parent_donation_id, 'completed' );

        Donation_Event_History::add(
            $parent_donation_id,
            'recurring_payment_first',
            'completed',
            __( 'Webhook: invoice.paid (first charge)', 'giftflow' ),
            array(
                'invoice_id'      => $invoice_id,
                'subscription_id' => $subscription_id,
                'gateway'         => 'stripe',
                'source'          => 'webhook',
            )
        );
    } else {
        // Create a child renewal donation post.
        $parent_data = get_post( $parent_donation_id );
        $meta        = get_post_meta( $parent_donation_id );

        $renewal_id = wp_insert_post(
            array(
                'post_title'  => sprintf(
                    // translators: %s: parent donation ID.
                    __( 'Recurring Donation (renewal of #%s)', 'giftflow' ),
                    $parent_donation_id
                ),
                'post_type'   => 'donation',
                'post_status' => 'publish',
            )
        );

        if ( is_wp_error( $renewal_id ) ) {
            $this->log_error( 'webhook_error', 'invoice.paid: failed to create renewal donation for ' . $subscription_id, $parent_donation_id );
            return;
        }

        // Copy core meta from parent.
        $copy_keys = array( '_amount', '_campaign_id', '_donor_id', '_payment_method', '_donation_type', '_recurring_interval' );
        foreach ( $copy_keys as $key ) {
            if ( isset( $meta[ $key ][0] ) ) {
                update_post_meta( $renewal_id, $key, $meta[ $key ][0] );
            }
        }

        // Set renewal-specific meta.
        update_post_meta( $renewal_id, '_status',                  'completed' );
        update_post_meta( $renewal_id, '_parent_donation_id',      $parent_donation_id );
        update_post_meta( $renewal_id, '_stripe_invoice_id',       $invoice_id );
        update_post_meta( $renewal_id, '_stripe_subscription_id',  $subscription_id );
        update_post_meta( $renewal_id, '_stripe_customer_id',      isset( $meta['_stripe_customer_id'][0] ) ? $meta['_stripe_customer_id'][0] : '' );
        update_post_meta( $renewal_id, '_transaction_id',          $payment_intent_id );
        update_post_meta( $renewal_id, '_stripe_payment_intent_id', $payment_intent_id );
        update_post_meta( $renewal_id, '_transaction_raw_data',    wp_json_encode( (array) $invoice ) );
        update_post_meta( $renewal_id, '_is_subscription_renewal', '1' );
        update_post_meta( $renewal_id, '_payment_status',          'completed' );

        Donation_Event_History::add(
            $renewal_id,
            'recurring_payment_renewal',
            'completed',
            __( 'Webhook: invoice.paid (renewal)', 'giftflow' ),
            array(
                'invoice_id'        => $invoice_id,
                'subscription_id'   => $subscription_id,
                'parent_donation_id'=> $parent_donation_id,
                'gateway'           => 'stripe',
                'source'            => 'webhook',
            )
        );

        do_action( 'giftflow_stripe_recurring_renewal_created', $renewal_id, $parent_donation_id, $subscription_id, (array) $invoice );
    }

    $this->update_parent_after_renewal( $parent_donation_id, $invoice );

    Giftflow_Logger::info(
        'stripe.webhook.invoice.paid',
        array(
            'parent_donation_id' => $parent_donation_id,
            'invoice_id'         => $invoice_id,
            'subscription_id'    => $subscription_id,
            'is_first'           => $is_first_invoice,
            'gateway'            => 'stripe',
        ),
        'stripe'
    );
}

/**
 * Update parent donation's recurring status and next payment date after a renewal.
 *
 * @param int    $parent_donation_id Parent donation post ID.
 * @param object $invoice            Stripe Invoice object.
 */
private function update_parent_after_renewal( $parent_donation_id, $invoice ) {
    update_post_meta( $parent_donation_id, '_recurring_status', 'active' );

    // The invoice has a subscription object if expanded; otherwise retrieve it.
    if ( ! empty( $invoice->lines->data ) ) {
        foreach ( $invoice->lines->data as $line ) {
            if ( isset( $line->period->end ) ) {
                update_post_meta(
                    $parent_donation_id,
                    '_recurring_next_payment_date',
                    gmdate( 'Y-m-d\TH:i:s\Z', $line->period->end )
                );
                break;
            }
        }
    }
}
```

### 4.4 — Handler: `handle_invoice_payment_failed()`

```php
/**
 * Handle Stripe invoice.payment_failed webhook.
 *
 * @param object $invoice Stripe Invoice object.
 */
private function handle_invoice_payment_failed( $invoice ) {
    $subscription_id = isset( $invoice->subscription ) ? $invoice->subscription : '';

    $parent_donations = get_posts(
        array(
            'post_type'      => 'donation',
            'posts_per_page' => 1,
            // phpcs:ignore WordPress.DB.SlowDBQuery
            'meta_key'       => '_stripe_subscription_id',
            // phpcs:ignore WordPress.DB.SlowDBQuery
            'meta_value'     => $subscription_id,
        )
    );

    if ( empty( $parent_donations ) ) {
        return;
    }

    $parent_donation_id = $parent_donations[0]->ID;

    update_post_meta( $parent_donation_id, '_recurring_status', 'past_due' );

    Donation_Event_History::add(
        $parent_donation_id,
        'recurring_payment_failed',
        'failed',
        __( 'Webhook: invoice.payment_failed', 'giftflow' ),
        array(
            'invoice_id'      => isset( $invoice->id ) ? $invoice->id : '',
            'subscription_id' => $subscription_id,
            'gateway'         => 'stripe',
            'source'          => 'webhook',
        )
    );

    Giftflow_Logger::error(
        'stripe.webhook.invoice.payment_failed',
        array(
            'parent_donation_id' => $parent_donation_id,
            'subscription_id'    => $subscription_id,
            'gateway'            => 'stripe',
        ),
        'stripe'
    );

    do_action( 'giftflow_stripe_recurring_payment_failed', $parent_donation_id, $subscription_id, (array) $invoice );
}
```

### 4.5 — Handler: `handle_subscription_deleted()`

```php
/**
 * Handle Stripe customer.subscription.deleted webhook.
 *
 * @param object $subscription Stripe Subscription object.
 */
private function handle_subscription_deleted( $subscription ) {
    $subscription_id = isset( $subscription->id ) ? $subscription->id : '';

    $parent_donations = get_posts(
        array(
            'post_type'      => 'donation',
            'posts_per_page' => 1,
            // phpcs:ignore WordPress.DB.SlowDBQuery
            'meta_key'       => '_stripe_subscription_id',
            // phpcs:ignore WordPress.DB.SlowDBQuery
            'meta_value'     => $subscription_id,
        )
    );

    if ( empty( $parent_donations ) ) {
        return;
    }

    $parent_donation_id = $parent_donations[0]->ID;

    update_post_meta( $parent_donation_id, '_recurring_status', 'cancelled' );

    Donation_Event_History::add(
        $parent_donation_id,
        'recurring_subscription_cancelled',
        'cancelled',
        __( 'Webhook: customer.subscription.deleted', 'giftflow' ),
        array(
            'subscription_id' => $subscription_id,
            'gateway'         => 'stripe',
            'source'          => 'webhook',
        )
    );

    do_action( 'giftflow_stripe_subscription_cancelled', $parent_donation_id, $subscription_id, (array) $subscription );
}
```

### 4.6 — Handler: `handle_subscription_updated()`

```php
/**
 * Handle Stripe customer.subscription.updated webhook.
 *
 * @param object $subscription Stripe Subscription object.
 */
private function handle_subscription_updated( $subscription ) {
    $subscription_id = isset( $subscription->id ) ? $subscription->id : '';

    $parent_donations = get_posts(
        array(
            'post_type'      => 'donation',
            'posts_per_page' => 1,
            // phpcs:ignore WordPress.DB.SlowDBQuery
            'meta_key'       => '_stripe_subscription_id',
            // phpcs:ignore WordPress.DB.SlowDBQuery
            'meta_value'     => $subscription_id,
        )
    );

    if ( empty( $parent_donations ) ) {
        return;
    }

    $parent_donation_id = $parent_donations[0]->ID;
    $new_status         = isset( $subscription->status ) ? $subscription->status : '';

    // Map Stripe subscription status → internal recurring_status.
    $status_map = array(
        'active'   => 'active',
        'past_due' => 'past_due',
        'unpaid'   => 'unpaid',
        'canceled' => 'cancelled',
        'trialing' => 'active',
    );

    if ( isset( $status_map[ $new_status ] ) ) {
        update_post_meta( $parent_donation_id, '_recurring_status', $status_map[ $new_status ] );
    }

    if ( isset( $subscription->current_period_end ) ) {
        update_post_meta(
            $parent_donation_id,
            '_recurring_next_payment_date',
            gmdate( 'Y-m-d\TH:i:s\Z', $subscription->current_period_end )
        );
    }

    do_action( 'giftflow_stripe_subscription_updated', $parent_donation_id, $subscription_id, (array) $subscription );
}
```

---

## Phase 5 — Donations Class: Recurring Meta

**File:** `includes/core/class-donations.php`

### Task A — Extend `get()` return array

In the `get()` method (around line 255, inside the `$donation_data` array), add the new recurring keys described in [Section 4 — Data Schema](#4-data-schema).

### Task B — Extend `save_donation_meta()`

In the `save_donation_meta()` method (around line 412), add handling for the new meta keys so they can be set programmatically:

```php
// Stripe Customer ID.
if ( isset( $data['stripe_customer_id'] ) ) {
    if ( ! empty( $data['stripe_customer_id'] ) ) {
        update_post_meta( $donation_id, '_stripe_customer_id', sanitize_text_field( $data['stripe_customer_id'] ) );
    }
}

// Stripe Subscription ID.
if ( isset( $data['stripe_subscription_id'] ) ) {
    if ( ! empty( $data['stripe_subscription_id'] ) ) {
        update_post_meta( $donation_id, '_stripe_subscription_id', sanitize_text_field( $data['stripe_subscription_id'] ) );
    }
}

// Recurring status.
if ( isset( $data['recurring_status'] ) ) {
    if ( ! empty( $data['recurring_status'] ) ) {
        update_post_meta( $donation_id, '_recurring_status', sanitize_text_field( $data['recurring_status'] ) );
    }
}
```

### Task C — Add `'cancelled'` to valid statuses

In `class-donations.php` at line 34, the `$valid_statuses` array is:

```php
private $valid_statuses = array(
    'pending',
    'completed',
    'failed',
    'refunded',
    // 'cancelled',
);
```

Uncomment `'cancelled'` (or add it) so that subscription-cancelled donations can be set to `cancelled` status:

```php
private $valid_statuses = array(
    'pending',
    'completed',
    'failed',
    'refunded',
    'cancelled',
);
```

---

## Phase 6 — Frontend JS: Recurring UI

**File:** `assets/src/stripe-donation.js` (or equivalent source file — check the build pipeline via `build-package.js`)

> **Note for AI Agent:** Run `cat build-package.js` to understand the JS build configuration and find the correct source file paths.

### 6.1 — Detect recurring from form data

The `forms.bundle.js` already collects `donation_type` and `recurring_interval` from the form and passes them in the AJAX payload (see `class-forms.php` line 66–74). Confirm these fields are present in the donation form template.

### 6.2 — Pass recurring fields to AJAX in `stripe-donation.js`

The Stripe-specific JS collects `payment_method_id` from Stripe Elements and submits it. Ensure that `donation_type` and `recurring_interval` are included in the payload sent to the `giftflow_donation_form` AJAX action. Example (pseudocode):

```js
const formData = {
    action: 'giftflow_donation_form',
    nonce: giftflowStripeDonation.nonce,
    payment_method_id: paymentMethod.id,
    donation_amount: form.querySelector('[name="donation_amount"]').value,
    donor_name: form.querySelector('[name="donor_name"]').value,
    donor_email: form.querySelector('[name="donor_email"]').value,
    campaign_id: form.querySelector('[name="campaign_id"]').value,
    donation_type: form.querySelector('[name="donation_type"]:checked')?.value || 'once',
    recurring_interval: form.querySelector('[name="recurring_interval"]')?.value || '',
};
```

### 6.3 — Localize recurring settings to JS

In `get_script_data()` (`class-stripe.php`, line 178), add:

```php
'recurring_enabled'   => (bool) $this->get_setting( 'stripe_recurring_enabled', false ),
'recurring_intervals' => $this->get_setting( 'stripe_recurring_intervals', array( 'month', 'year' ) ),
```

The JS can use `giftflowStripeDonation.recurring_enabled` to conditionally show/hide the recurring interval selector.

---

## Phase 7 — Admin UI: Display Subscription Details

**File:** `admin/includes/meta-boxes/class-donation-transaction-meta.php`

### Task

When rendering the donation transaction meta box, check for `_is_subscription_parent` and display a summary panel:

- Subscription ID (linked to Stripe Dashboard)
- Recurring interval
- Recurring status (badge)
- Next payment date
- Cancel subscription button (calls a new AJAX action — see Phase 8)

Also on **child renewal donations** (check `_is_subscription_renewal`), display a link back to the parent donation.

---

## Phase 8 — Cancellation & Management

### 8.1 — Admin cancellation AJAX action

**File:** `includes/gateways/class-stripe.php`
**Method to add:** `cancel_subscription()`

Register the AJAX action inside `init_additional_hooks()`:

```php
add_action( 'wp_ajax_giftflow_stripe_cancel_subscription', array( $this, 'ajax_cancel_subscription' ) );
```

Add the public handler:

```php
/**
 * AJAX handler: cancel a Stripe subscription from the admin.
 */
public function ajax_cancel_subscription() {
    check_ajax_referer( 'giftflow_stripe_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Unauthorized', 'giftflow' ) ) );
    }

    $donation_id     = intval( $_POST['donation_id'] ?? 0 );
    $subscription_id = get_post_meta( $donation_id, '_stripe_subscription_id', true );

    if ( empty( $subscription_id ) ) {
        wp_send_json_error( array( 'message' => __( 'No subscription found for this donation.', 'giftflow' ) ) );
    }

    try {
        $this->stripe->subscriptions->cancel( $subscription_id );

        update_post_meta( $donation_id, '_recurring_status', 'cancelled' );

        Donation_Event_History::add(
            $donation_id,
            'recurring_subscription_cancelled',
            'cancelled',
            __( 'Subscription cancelled by admin.', 'giftflow' ),
            array(
                'subscription_id' => $subscription_id,
                'gateway'         => 'stripe',
            )
        );

        wp_send_json_success( array( 'message' => __( 'Subscription cancelled successfully.', 'giftflow' ) ) );
    } catch ( ApiErrorException $e ) {
        wp_send_json_error( array( 'message' => $e->getMessage() ) );
    }
}
```

### 8.2 — Self-service cancellation (deferred)

This can be built as a front-end page using a secure token (e.g., signed URL with donation ID + nonce) to call the same cancellation flow. Document as a separate task when ready.

---

## Hook Reference

All new action hooks fired during recurring flows. Third-party plugins and theme code can hook into these.

| Hook | When fired | Arguments |
|---|---|---|
| `giftflow_stripe_subscription_created` | After Stripe subscription is created | `$donation_id, $subscription_id, $subscription_array` |
| `giftflow_stripe_recurring_renewal_created` | After a renewal child donation is created | `$renewal_id, $parent_id, $subscription_id, $invoice_array` |
| `giftflow_stripe_recurring_payment_failed` | On `invoice.payment_failed` webhook | `$parent_donation_id, $subscription_id, $invoice_array` |
| `giftflow_stripe_subscription_cancelled` | On `customer.subscription.deleted` webhook | `$parent_donation_id, $subscription_id, $subscription_array` |
| `giftflow_stripe_subscription_updated` | On `customer.subscription.updated` webhook | `$parent_donation_id, $subscription_id, $subscription_array` |

### Existing hooks used (unchanged)

- `giftflow_stripe_payment_completed` — still fires on first charge success via `handle_successful_payment_intent()`
- `giftflow_donation_status_updated` — fires when `Donations::update_status()` is called
- `giftflow_stripe_prepare_payment_intent_data` — still available for one-time flow

---

## Testing Checklist

Use Stripe test mode with the [Stripe CLI](https://stripe.com/docs/stripe-cli) for local webhook forwarding:

```bash
stripe listen --forward-to http://your-local-site.test/wp-admin/admin-ajax.php?action=giftflow_stripe_webhook
```

### One-time payment (regression — must still pass)

- [ ] Submit donation form with `donation_type = once`
- [ ] Donation status becomes `completed`
- [ ] `_transaction_id` is set to a PaymentIntent ID
- [ ] No `_stripe_subscription_id` meta created

### First recurring charge

- [ ] Submit form with `donation_type = recurring`, `recurring_interval = month`
- [ ] Stripe Customer created (`cus_xxx`)
- [ ] Stripe Subscription created (`sub_xxx`)
- [ ] Parent donation post created with `_is_subscription_parent = 1`
- [ ] Parent donation status becomes `completed` after `invoice.paid` (first)
- [ ] `_recurring_status = active` on parent

### Renewal charge (simulate with Stripe CLI)

```bash
stripe trigger invoice.paid
```

- [ ] New child donation post created with `_is_subscription_renewal = 1`
- [ ] Child has `_parent_donation_id` pointing to parent
- [ ] Child status is `completed`
- [ ] Parent `_recurring_next_payment_date` updated
- [ ] `giftflow_stripe_recurring_renewal_created` action fires

### Failed renewal

```bash
stripe trigger invoice.payment_failed
```

- [ ] No child donation created
- [ ] Parent `_recurring_status` updated to `past_due`
- [ ] `Donation_Event_History` entry logged on parent

### Subscription cancelled via webhook

```bash
stripe trigger customer.subscription.deleted
```

- [ ] Parent `_recurring_status = cancelled`
- [ ] Event history entry added

### Admin cancellation

- [ ] Open parent donation in admin
- [ ] Click "Cancel Subscription"
- [ ] Stripe subscription is cancelled via API
- [ ] `_recurring_status` updated to `cancelled`
- [ ] Confirmation message shown

### 3DS on first charge

- [ ] Use Stripe test card `4000002500003155` (requires authentication)
- [ ] Browser redirected to 3DS page
- [ ] After authentication, `handle_return_url()` processes result
- [ ] Donation completes normally

---

## File Map

Summary of every file that must be created or modified.

| Action | File | Description |
|---|---|---|
| **MODIFY** | `includes/gateways/class-stripe.php` | All backend recurring logic (Phases 1, 2C, 3, 4, 6, 8) |
| **MODIFY** | `includes/core/class-donations.php` | Add recurring meta to `get()`, `save_donation_meta()`, add `cancelled` status |
| **MODIFY** | `admin/includes/meta-boxes/class-campaign-details-meta.php` | Per-campaign: recurring, recurring_interval, recurring_number_of_times (Phase 2.A) |
| **MODIFY** | `includes/frontend/class-shortcodes.php` | Build donation_types from campaign _one_time / _recurring; pass recurring_interval, recurring_number_of_times (Phase 2.B) |
| **MODIFY** | `admin/includes/meta-boxes/class-donation-transaction-meta.php` | Display subscription details in admin (Phase 7) |
| **MODIFY** | `assets/src/stripe-donation.js` *(find exact path via build-package.js)* | Send recurring fields with AJAX payload (Phase 6) |
| **VERIFY** | `templates/payment-gateway/stripe-template.php` | Confirm `donation_type` and `recurring_interval` inputs exist; add if missing |

No new PHP class files are needed. All logic lives inside the existing `Stripe_Gateway` class.
