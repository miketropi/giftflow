# GiftFlow

A comprehensive WordPress plugin for managing donations, donors, and campaigns with modern features and extensible architecture.

## Description

GiftFlow is a powerful WordPress plugin designed to help organizations manage donations, donors, and fundraising campaigns efficiently. Built with modern WordPress standards and an extensible architecture, GiftFlow provides everything you need to accept donations, track campaigns, and manage donor relationships.

## Features

### Donation Management
- Custom post types for donations, donors, and campaigns
- Comprehensive donor profiles with contact information
- Donation history tracking
- Transaction details and status management
- Support for one-time and recurring donations
- Centralized donation CRUD operations via `Donations` class
- Donation status management (pending, completed, failed, refunded)

### Payment Processing
- **Stripe** - Direct integration using Stripe PHP SDK
  - Secure credit/debit card processing
  - PCI compliant
  - Payment Intent API support
  - Webhook integration for real-time status updates
- **PayPal** - Direct integration using PayPal JS SDK v6
  - PayPal Orders API v2 integration
  - Smart Buttons for seamless payment experience
  - On-site payment processing (no redirect)
  - Access token caching for improved performance
  - Webhook integration for payment notifications
- **Direct Bank Transfer** - Manual payment option
  - Bank transfer instructions
  - Manual payment verification
- Secure payment processing with PCI compliance
- Real-time payment status updates
- Webhook integration for all payment gateways

### Campaign Management
- Create and manage unlimited donation campaigns
- Set campaign goals and track progress in real-time
- Campaign-specific donation forms
- Campaign analytics and reporting
- Visual progress indicators
- Campaign images and galleries
- Campaign location support

### Form Builder
- Flexible donation form system
- Multiple form templates
- Custom field support
- Anonymous donation option
- Donor message support
- Preset donation amounts
- Custom donation amount support
- Recurring donation options

### Gutenberg Blocks
- **Donation Button** - Customizable donation button with campaign integration
- **Campaign Single Content** - Display campaign content and details
- **Campaign Single Images** - Display campaign images with gallery support
- **Campaign Status Bar** - Visual progress indicator for campaigns
- **Donor Account** - Donor account management and dashboard
- **Share** - Social media sharing block for campaigns

### Reporting & Analytics
- Admin dashboard with statistics
- Custom reports
- Export functionality
- Donation trends and insights
- Campaign performance metrics

## Requirements

- **WordPress**: 6.0 or higher
- **PHP**: 8.2 or higher
- **MySQL**: 5.6 or higher
- **Composer**: For dependency management
- **SSL Certificate**: Required for secure payment processing

## Installation

### From WordPress Admin

1. Visit **Plugins > Add New**
2. Search for **GiftFlow**
3. Click **Install Now** and then **Activate**

### Manual Installation

1. Upload the `giftflow` folder to the `/wp-content/plugins/` directory
2. Install dependencies:
   ```bash
   cd wp-content/plugins/giftflow
   composer install
   npm install
   npm run build
   ```
3. Activate the plugin through the **Plugins** menu in WordPress
4. Navigate to **GiftFlow > Settings** to configure the plugin

### After Installation

1. Go to **GiftFlow > Settings** to configure your payment gateways
2. Set up Stripe or PayPal credentials in the Payment Methods section
   - See [PayPal Setup Guide](docs/paypal-method-setup.md) for detailed PayPal configuration
3. Configure your preferred currency and email settings
4. Create your first campaign under **Campaigns > Add New**
5. Use Gutenberg blocks or shortcodes to display donation forms

## Payment Gateway Setup

### Stripe

1. Get your Stripe API keys from [Stripe Dashboard](https://dashboard.stripe.com/apikeys)
2. Navigate to **GiftFlow > Settings > Payment Methods**
3. Enable Stripe and enter your API keys
4. Configure webhook endpoint in Stripe Dashboard
5. Add webhook events: `payment_intent.succeeded`, `payment_intent.payment_failed`, `charge.refunded`

### PayPal

1. Create a PayPal app in [PayPal Developer Dashboard](https://developer.paypal.com/)
2. Get your Client ID and Secret
3. Navigate to **GiftFlow > Settings > Payment Methods**
4. Enable PayPal and enter your credentials
5. Configure webhook URL in PayPal Dashboard
6. Add recommended webhook events (see [PayPal Setup Guide](docs/paypal-method-setup.md))

### Direct Bank Transfer

1. Navigate to **GiftFlow > Settings > Payment Methods**
2. Enable Direct Bank Transfer
3. Configure bank account details that will be shown to donors

## Usage

### Gutenberg Blocks

GiftFlow includes several Gutenberg blocks for building donation campaigns:

- **Donation Button** - `giftflow/donation-button`
  - Add donation buttons with customizable styling
  - Campaign integration with auto-detect support
  - Full-width option and custom colors

- **Campaign Single Content** - `giftflow/campaign-single-content`
  - Display campaign content and details
  - Automatically uses current post context

- **Campaign Single Images** - `giftflow/campaign-single-images`
  - Display campaign images with gallery support
  - Lightbox integration
  - Thumbnail navigation with expandable view

- **Campaign Status Bar** - `giftflow/campaign-status-bar`
  - Visual progress indicator for campaigns
  - Goal tracking display

- **Donor Account** - `giftflow/donor-account`
  - Donor account dashboard
  - Donation history and account management

- **Share** - `giftflow/share`
  - Social media sharing (Facebook, X/Twitter, LinkedIn)
  - Email sharing and copy URL functionality

### Shortcodes

#### Donation Form

Display a donation form for a specific campaign:

```
[giftflow_donation_form campaign_id="123"]
```

**Parameters:**
- `campaign_id` (required) - The ID of the campaign for which to display the donation form

**Example:**
```php
echo do_shortcode('[giftflow_donation_form campaign_id="123"]');
```

### Custom Post Types

#### Donations
- Transaction details meta box
- Recurring settings meta box
- Payment status tracking
- Donor and campaign relationships

#### Donors
- Contact information meta box
- Donation history meta box
- Preferences meta box
- Tax information storage

#### Campaigns
- Campaign details meta box
- Campaign settings meta box
- Goal tracking
- Progress monitoring
- Image galleries
- Location support

### Templates

The plugin includes the following templates that can be overridden in your theme:

- `single-donation.php` - Single donation view
- `single-donor.php` - Single donor view
- `single-campaign.php` - Single campaign view
- `archive-donation.php` - Donation archive
- `donation-form.php` - Donation form template

## Development

This plugin uses [Laravel Mix](https://laravel-mix.com/) (a wrapper around Webpack) to compile and bundle JavaScript and CSS files for production.

### Source Code

The original source code for all compiled JavaScript and CSS files can be found at:
[https://github.com/miketropi/giftflow](https://github.com/miketropi/giftflow)

### JavaScript File Structure

In the `assets/js/` directory, the plugin follows this naming convention:

| File Type | Description |
|-----------|-------------|
| `*.js` | Original, uncompiled source files (human-readable) |
| `*.bundle.js` | Compiled/minified production files (generated by build process) |

**Example:**
- `forms.js` → Source file
- `forms.bundle.js` → Compiled production file

**All JavaScript file pairs:**

| Source File | Compiled File |
|-------------|---------------|
| `forms.js` | `forms.bundle.js` |
| `common.js` | `common.bundle.js` |
| `stripe-donation.js` | `stripe-donation.bundle.js` |
| `paypal-donation.js` | `paypal-donation.bundle.js` |
| `grecaptcha.js` | `grecaptcha.bundle.js` |

> **Note:** Only the `*.bundle.js` files are enqueued in production. The source `*.js` files are included in the repository for transparency and to comply with WordPress.org guidelines requiring human-readable source code.

### Build Commands

To rebuild assets locally:

```bash
# Install dependencies
npm install

# Watch for changes and rebuild automatically during development
npm run dev

# Build for production (minified)
npm run build
```

### Directory Structure

```
giftflow/
├── admin/                      # Admin interface
│   ├── assets/                # Admin assets (CSS, JS, images)
│   ├── includes/              # Admin classes
│   │   ├── post-types/       # Custom post type classes
│   │   ├── meta-boxes/       # Meta box classes
│   │   └── settings.php      # Settings page
│   └── css/                   # Admin styles
├── assets/                     # Frontend assets
│   ├── css/                   # Frontend styles
│   └── js/                    # Frontend scripts
├── blocks/                     # Gutenberg blocks
│   ├── campaign-single-content/
│   ├── campaign-single-images/
│   ├── campaign-status-bar/
│   ├── donation-button/
│   ├── donor-account/
│   ├── share/
│   └── index.php              # Block loader
├── docs/                       # Documentation
├── includes/                   # Core plugin files
│   ├── core/                  # Core classes
│   │   ├── class-donations.php
│   │   ├── class-ajax.php
│   │   └── ...
│   ├── gateways/              # Payment gateway classes
│   │   ├── class-stripe.php
│   │   ├── class-paypal.php
│   │   └── class-direct-bank-transfer.php
│   ├── frontend/              # Frontend classes
│   │   ├── class-shortcodes.php
│   │   ├── class-forms.php
│   │   └── class-template.php
│   └── common.php             # Common functions
├── templates/                  # PHP templates
│   ├── donation-form.php
│   └── admin/                 # Admin templates
├── languages/                  # Translation files
└── giftflow.php               # Main plugin file
```

### Hooks

GiftFlow provides extensive hooks for customization and extension.

#### Action Hooks

```php
// Donation lifecycle
do_action('giftflow_donation_created', $donation_id, $data);
do_action('giftflow_donation_updated', $donation_id, $data);
do_action('giftflow_donation_before_create', $data);
do_action('giftflow_donation_create_failed', $error, $data);

// Payment processing
do_action('giftflow_payment_success', $donation_id, $transaction_id);
do_action('giftflow_payment_failed', $donation_id, $error);

// Campaign events
do_action('giftflow_campaign_created', $campaign_id);
do_action('giftflow_campaign_updated', $campaign_id);
```

#### Filter Hooks

```php
// Donation data
apply_filters('giftflow_donation_before_create', $data);
apply_filters('giftflow_donation_post_data', $post_data, $data);

// Payment gateways
apply_filters('giftflow_payment_gateways', $gateways);

// Form customization
apply_filters('giftflow_form_donation_types', $donation_types, $campaign_id);
apply_filters('giftflow_form_donation_form_atts', $atts, $campaign_id);
```

### Extending GiftFlow

#### Adding a Custom Payment Gateway

1. Create a new gateway class extending `Gateway_Base`:

```php
class My_Custom_Gateway extends \GiftFlow\Gateways\Gateway_Base {
    // Implement required methods
}
```

2. Register your gateway:

```php
add_filter('giftflow_payment_gateways', function($gateways) {
    $gateways[] = new My_Custom_Gateway();
    return $gateways;
});
```

See the [Register New Payment Method](docs/register-new-paymen-method.md) guide for detailed instructions.

## External Services

This plugin relies on the following third-party services to provide its functionality. These services are only used when explicitly enabled by the site administrator. By using these features, data may be transmitted to external servers. Please review the terms and privacy policies of each service before enabling them.

### Stripe (stripe.com)

This plugin uses the Stripe payment gateway to process credit and debit card donations securely.

**Service Provider:** Stripe, Inc.  
**Service URL:** [https://stripe.com](https://stripe.com)

**Data Transmitted:**
- Payment card information (processed directly by Stripe, not stored on your server)
- Donation amount and currency
- Donor name and email address
- Billing address (if collected)
- Transaction metadata (donation ID, campaign ID)

**When Data is Sent:**
- When a donor submits a donation form using Stripe as the payment method
- When processing refunds through the admin dashboard
- When verifying webhook signatures for payment status updates

**Legal:**
- [Terms of Service](https://stripe.com/legal)
- [Privacy Policy](https://stripe.com/privacy)

---

### PayPal (paypal.com)

This plugin uses PayPal to process donations through PayPal accounts and PayPal Smart Payment Buttons.

**Service Provider:** PayPal Holdings, Inc.  
**Service URL:** [https://www.paypal.com](https://www.paypal.com)

**Data Transmitted:**
- Donation amount and currency
- Donor name and email address
- Order description and transaction metadata
- Return URLs for payment completion

**When Data is Sent:**
- When a donor initiates a donation using PayPal as the payment method
- When creating and capturing PayPal orders via the REST API
- When processing refunds through the admin dashboard

**Legal:**
- [Terms of Service](https://www.paypal.com/webapps/mpp/ua/useragreement-full)
- [Privacy Policy](https://www.paypal.com/webapps/mpp/ua/privacy-full)

---

### Google reCAPTCHA (google.com/recaptcha)

This plugin optionally uses Google reCAPTCHA to protect donation forms from spam submissions and automated abuse.

**Service Provider:** Google LLC  
**Service URL:** [https://www.google.com/recaptcha](https://www.google.com/recaptcha)

**Data Transmitted:**
- User's IP address
- Browser user agent and plugins
- Mouse movements and keystrokes on the page
- JavaScript environment information
- reCAPTCHA site key

**When Data is Sent:**
- When a donation form with reCAPTCHA protection is loaded
- When a donor interacts with or submits a protected donation form

**Legal:**
- [Terms of Service](https://policies.google.com/terms)
- [Privacy Policy](https://policies.google.com/privacy)

---

### Google Maps Platform (developers.google.com/maps)

This plugin optionally uses Google Maps to display campaign locations and geographic information.

**Service Provider:** Google LLC  
**Service URL:** [https://developers.google.com/maps](https://developers.google.com/maps)

**Data Transmitted:**
- Map coordinates and location queries
- User's IP address (by Google when loading map tiles)
- Google Maps API key

**When Data is Sent:**
- When a page containing a Google Maps embed is loaded
- When geocoding addresses for campaign locations

**Legal:**
- [Terms of Service](https://cloud.google.com/maps-platform/terms)
- [Privacy Policy](https://policies.google.com/privacy)

---

### Important Privacy Notice

Site administrators are responsible for ensuring compliance with applicable privacy laws (such as GDPR) when using these third-party services. We recommend:

- Disclosing the use of these services in your site's privacy policy
- Obtaining appropriate consent from users before processing their data
- Reviewing each service's data processing agreements

## Support

For support, please visit our [support forum](https://giftflow.com/support) or email support@giftflow.com.

## Changelog

### 1.0.1
- Initial public release
- Core donation management features
- Stripe and PayPal payment gateway integration
- PayPal JS SDK v6 integration with Orders API v2
- Gutenberg blocks support (6 blocks)
- Campaign management system
- Admin dashboard with statistics
- Export functionality
- Email notification system
- Custom post types and taxonomies
- Comprehensive hooks and filters for extensibility
- Centralized donation management via Donations class
- Access token caching for PayPal
- Direct bank transfer gateway

## License

GPL v2 or later. See [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html) for details.

---

**Developed by BePlus**
