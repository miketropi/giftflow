=== GiftFlow ===
Contributors: bearsthemes
Author: Beplus
Author URI: https://beplusthemes.com
Tags: donations, fundraising, campaigns, charity, nonprofit
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A comprehensive WordPress plugin for managing donations, donors, and campaigns with modern features and extensible architecture.

== Description ==

GiftFlow is a powerful WordPress plugin designed to help organizations manage donations, donors, and fundraising campaigns efficiently. Built with modern WordPress standards and an extensible architecture, GiftFlow provides everything you need to accept donations, track campaigns, and manage donor relationships.

= Key Features =

* **Donation Management**
  * Custom post types for donations, donors, and campaigns
  * Comprehensive donor profiles with contact information
  * Donation history tracking
  * Transaction details and status management
  * Support for one-time and recurring donations

* **Payment Processing**
  * Multiple payment gateway support (Stripe, PayPal)
  * Secure payment processing with PCI compliance
  * Direct bank transfer option
  * Real-time payment status updates
  * Webhook integration for payment notifications

* **Campaign Management**
  * Create and manage unlimited donation campaigns
  * Set campaign goals and track progress in real-time
  * Campaign-specific donation forms
  * Campaign analytics and reporting
  * Visual progress indicators

* **Form Builder**
  * coming soon 

* **Reporting & Analytics**
  * Admin dashboard with statistics
  * Export functionality

* **Gutenberg Blocks**
  * Campaign display blocks
  * Donation form blocks
  * Campaign content blocks
  * Full block editor support

= Payment Gateways =

GiftFlow supports multiple payment gateways through official SDKs:

* **Stripe** - Direct integration using Stripe PHP SDK for secure credit/debit card processing
* **PayPal** - Direct integration using PayPal JS SDK v6 and REST API v2 with Smart Buttons for seamless payment experience
* **Direct Bank Transfer** - Manual payment option for bank transfers

Additional payment gateways can be added through the plugin's extensible gateway architecture.

= Developer-Friendly =

GiftFlow is built with developers in mind, featuring:

* coming soon

== Installation ==

= Installation from WordPress Admin =

1. Visit **Plugins > Add New**
2. Search for **GiftFlow**
3. Click **Install Now** and then **Activate**

= Manual Installation =

1. Upload the `giftflow` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the **Plugins** menu in WordPress
3. Navigate to **GiftFlow > Settings** to configure the plugin

= After Installation =

1. Go to **GiftFlow > Settings** to configure your payment gateways
2. Set up Stripe or PayPal credentials in the Payment Methods section
3. Configure your preferred currency and email settings
4. Create your first campaign under **Campaigns > Add New**

== Frequently Asked Questions ==

= Does GiftFlow support recurring donations? =

Yes, GiftFlow supports both one-time and recurring (coming soon) donations through Stripe and PayPal.

= Which payment gateways are supported? =

GiftFlow currently supports Stripe, PayPal, and Direct Bank Transfer. Additional payment gateways can be added through the extensible gateway system.

= Can I customize the donation form? =

Yes, form customization is possible via hooks and custom fields, but currently requires developer implementation. We plan to release a visual form builder for custom fields in a future update.

= Is GiftFlow compatible with page builders? =

Yes, GiftFlow includes Gutenberg blocks and shortcodes that work with most page builders.

= Can I export donation data? =

Yes, GiftFlow includes export functionality in the admin dashboard to export donation data in various formats.

= Does GiftFlow send email notifications? =

Yes, GiftFlow can send email notifications for donations, campaign updates, and other events. Email templates can be customized.

= What are the system requirements? =

GiftFlow requires WordPress 6.0 or higher, PHP 8.2 or higher, and an SSL certificate for secure payment processing.

== Screenshots ==

1. Admin Dashboard - Overview of donations, campaigns, and statistics
2. Campaign Management - Create and manage fundraising campaigns
3. Donation Form - Customizable donation forms with multiple payment options
4. Payment Settings - Configure Stripe, PayPal, and other payment gateways
5. Donor Management - Comprehensive donor profiles and donation history
6. Reports & Analytics - Track donations and campaign performance

== External services ==

This plugin connects to external third-party services under certain circumstances:

= Stripe =

When the Stripe payment gateway is enabled, the plugin connects to Stripe's payment processing service to handle credit and debit card donations.

* **What data is sent:** Donor payment information (card details), donation amount, currency, and transaction metadata.
* **When it's used:** During the checkout process when a donor selects Stripe as their payment method.
* **Service website:** [https://stripe.com](https://stripe.com)
* **Terms of service:** [https://stripe.com/legal](https://stripe.com/legal)
* **Privacy policy:** [https://stripe.com/privacy](https://stripe.com/privacy)

= PayPal =

When the PayPal payment gateway is enabled, the plugin connects to PayPal's payment processing service to handle PayPal and card donations via PayPal Smart Buttons.

* **What data is sent:** Donor payment information, donation amount, currency, and transaction metadata.
* **When it's used:** During the checkout process when a donor selects PayPal as their payment method.
* **Service website:** [https://www.paypal.com](https://www.paypal.com)
* **Terms of service:** [https://www.paypal.com/webapps/mpp/ua/useragreement-full](https://www.paypal.com/webapps/mpp/ua/useragreement-full)
* **Privacy policy:** [https://www.paypal.com/webapps/mpp/ua/privacy-full](https://www.paypal.com/webapps/mpp/ua/privacy-full)

= Google reCAPTCHA =

When reCAPTCHA is enabled in the plugin settings, the plugin connects to Google's reCAPTCHA service to protect donation forms from spam and abuse.

* **What data is sent:** User interaction data, IP address, browser information, and site key for verification.
* **When it's used:** When a donor submits a donation form with reCAPTCHA protection enabled.
* **Service website:** [https://www.google.com/recaptcha](https://www.google.com/recaptcha)
* **Terms of service:** [https://policies.google.com/terms](https://policies.google.com/terms)
* **Privacy policy:** [https://policies.google.com/privacy](https://policies.google.com/privacy)

= Google Maps API =

When Google Maps integration is enabled, the plugin connects to Google Maps API to display maps on campaign pages or donor location features.

* **What data is sent:** Location data, map coordinates, and API key for authentication.
* **When it's used:** When displaying campaign locations or address-related features on the frontend.
* **Service website:** [https://developers.google.com/maps](https://developers.google.com/maps)
* **Terms of service:** [https://cloud.google.com/maps-platform/terms](https://cloud.google.com/maps-platform/terms)
* **Privacy policy:** [https://policies.google.com/privacy](https://policies.google.com/privacy)

== Changelog ==

= 1.0.1 =
* Initial public release
* Core donation management features
* Stripe and PayPal payment gateway integration
* Campaign management system
* Gutenberg blocks support
* Admin dashboard with statistics
* Export functionality
* Email notification system
* Custom post types and taxonomies
* Comprehensive hooks and filters for extensibility

== Upgrade Notice ==

= 1.0.1 =
Initial release of GiftFlow. Please configure your payment gateway settings after installation.
