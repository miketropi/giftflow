# GiftFlow Plugin — Agent Instructions

## Project Overview

GiftFlow is a WordPress donation & fundraising plugin (v1.0.16) built with OOP PHP and React-based Gutenberg blocks. It manages three custom post types — **Campaign**, **Donation**, **Donor** — with Stripe, PayPal, and direct bank transfer payment gateways.

## Architecture

### Namespace & Autoloading
- Root namespace: `GiftFlow\*`
- Vendor-scoped dependencies: `GiftFlow\Vendor\*` (via Strauss prefixing)
- Manual file loading in `giftflow.php` → `giftflow_load_files()` (NOT PSR-4 autoloading)
- All new classes must be explicitly required in `giftflow_load_files()` or hooked via `giftflow_load_files` filter

### Key Architectural Patterns
1. **Base class** (`GiftFlow\Core\Base`): Provides `$version`, `$plugin_dir`, `$plugin_url` — extend for shared access
2. **Loader** (`GiftFlow\Core\Loader`): Single init point hooked to `plugins_loaded`, orchestrates all component init
3. **Abstract Base Post Type** (`GiftFlow\Admin\PostTypes\Base_Post_Type`): Template Method pattern — override `init_post_type()` to set `$this->post_type`, `$this->labels`, `$this->args`, `$this->taxonomies`
4. **Abstract Base Meta Box** (`GiftFlow\Admin\MetaBoxes\Base_Meta_Box`): Override constructor to set `$this->id`, `$this->title`, `$this->post_type`, then implement `get_fields()` returning field arrays
5. **Abstract Gateway Base** (`GiftFlow\Gateways\Gateway_Base`): Self-registering registry pattern — override `init_gateway()`, `register_settings_fields()`, `template_html()`, `process_payment()`
6. **Block Loader** (`GiftFlow_Block_Loader`): Auto-loads `block.php` from each `blocks/*/` subdirectory. Register with `register_block_type()` on `init` hook
7. **Template routing**: Classic themes: `template_include` filter overrides. Block themes: FSE templates. Fallback: `the_content` filter

### Directory Structure Conventions
```
giftflow.php                  # Plugin header, constants, load_files(), activation/deactivation
includes/core/                # Business logic (Loader, Base, Donations, Campaigns, Role, Logger, Ajax, Field)
includes/frontend/            # Frontend classes (Shortcodes, Forms, Template, template-hooks)
includes/gateways/            # Payment gateways (Gateway_Base, Stripe, PayPal, Direct_Bank_Transfer)
includes/common.php           # 1700+ line helper functions (all giftflow_* prefixed globals)
includes/hooks.php            # Action/filter registrations (glue between components)
includes/currency.php         # 160+ currency data array
includes/icons.php            # SVG icon definitions
includes/mail.php             # Email sending utilities
admin/includes/post-types/    # CPT registration (Base_Post_Type, Campaign, Donation, Donor)
admin/includes/meta-boxes/    # Meta box registration (Base_Meta_Box, *_Meta classes)
admin/includes/settings.php   # Admin settings pages
admin/includes/dashboard.php  # Dashboard page
admin/includes/api.php        # REST API endpoints
admin/includes/class-export.php # CSV/PDF export
blocks/*/block.php            # Gutenberg block PHP registration + render callback
blocks-build/                 # Compiled block JS/CSS (from webpack.mix.js)
templates/                    # Template files (block/, classic/, admin/, email/, payment-gateway/)
assets/                       # Compiled frontend/admin JS/CSS bundles
vendor-prefixed/              # Scoped Composer deps (Stripe SDK via Strauss)
```

## Coding Standards

- **PHP**: WordPress Coding Standards via `phpcs.xml.dist` (WordPress, WordPress-Core, WordPress-Extra, WordPress-Docs)
- **PHP Version**: 7.4 minimum, 8.3 tested
- **WordPress Version**: 6.0+ minimum
- **Lint**: `composer lint` (phpcs), `composer lint:fix` (phpcbf)
- **Build**: `npm run build` (Laravel Mix), `composer run build` (install + Strauss prefix)
- **Dev**: `npm run dev` (Mix watch)

### PHP Conventions
- Always use `namespace GiftFlow\*`; place in correct sub-namespace
- Always check `if ( ! defined( 'ABSPATH' ) ) { exit; }` at top of PHP files
- Use `esc_html__()`, `esc_attr__()`, `__()`, `_x()` for all user-facing strings
- Text domain: `'giftflow'`
- Prefix global functions with `giftflow_`
- Prefix hooks/actions: `giftflow_*`
- Prefix post meta keys: `_*` (private meta, e.g., `_amount`, `_campaign_id`)
- Use `do_action()` / `apply_filters()` liberally for extensibility
- Nonce verification: Always verify `wp_verify_nonce()` in form handlers
- DB queries: Add `phpcs:ignore WordPress.DB.SlowDBQuery.*` comments for meta queries
- Use `wp_send_json_success()` / `wp_send_json_error()` for AJAX responses
- Use `giftflow_load_template()` / `GiftFlow\Frontend\Template::load_template()` for template rendering
- Use `giftflow_render_currency_formatted_amount()` for formatted currency output
- Use `giftflow_get_options()` to read plugin option values
- Use `giftflow_sanitize_array()` for recursive array sanitization

### Block Conventions
- Each block lives in `blocks/{block-name}/`
- PHP: `block.php` — registers block type, render callback
- JS: `blocks/{block-name}/index.js` or `blocks/{block-name}/edit.js`, `save.js`
- Block slug: `giftflow/{block-name}`, category: `giftflow`
- Use `giftflow_load_template()` inside render callbacks
- Pass data via template args array

### JS Conventions
- React 19 with `@wordpress/element` for Gutenberg blocks
- Zustand for state management
- Chart.js + react-chartjs-2 for charts
- Lucide React for icons
- Laravel Mix (webpack.mix.js) for bundling
- WordPress dependency extraction for wp-scripts deps

## Custom Post Types & Meta

| CPT | Post Type | Key Meta | Notes |
|-----|-----------|----------|-------|
| Campaign | `campaign` | `_goal_amount`, `_start_date`, `_end_date`, `_location`, `_gallery`, `_one_time`, `_recurring`, `_recurring_interval`, `_preset_donation_amounts`, `_allow_custom_donation_amounts` | Has `campaign-tax` taxonomy, admin menu under giftflow-dashboard |
| Donation | `donation` | `_amount`, `_campaign_id`, `_donor_id`, `_status`, `_payment_method`, `_donation_type`, `_transaction_id`, `_stripe_customer_id`, `_stripe_subscription_id`, `_paypal_order_id`, `_anonymous_donation` | Statuses: pending, completed, failed, refunded, cancelled. Donation types: one-time, recurring |
| Donor | `donor` | `_email`, `_first_name`, `_last_name`, `_phone`, `_address`, `_city`, `_state`, `_postal_code`, `_country` | Linked to WP users via `giftflow_donor` role |

## Settings Structure
- `giftflow_general_options` — currency, pages, preset amounts, templates
- `giftflow_payment_options` — per-gateway settings (enabled, API keys, etc.)
- `giftflow_options_with_api_keys_options` — Google reCAPTCHA settings
- Settings page: `admin.php?page=giftflow-settings`
- Dashboard: `admin.php?page=giftflow-dashboard`

## Payment Gateway Pattern
To add a gateway:
1. Create class in `includes/gateways/` extending `Gateway_Base`
2. Override `init_gateway()`: set `$this->id`, `$this->title`, `$this->description`, `$this->icon`, `$this->order`, `$this->supports`
3. Override `register_settings_fields()`: return array of field configs for admin settings
4. Override `template_html()`: return HTML for frontend payment form
5. Override `process_payment($data, $donation_id)`: handle payment processing
6. Instance gets auto-registered into `Gateway_Base::$gateway_registry`

## When Making Changes
- New classes → add require_once in `giftflow_load_files()` or use `giftflow_load_files` filter
- New blocks → create `blocks/{name}/block.php` with `register_block_type()` + render callback
- New templates → add to `templates/` directory, load via `giftflow_load_template()`
- New helper functions → add to `includes/common.php` with `giftflow_` prefix
- New hooks → add to `includes/hooks.php` or relevant component file
- New admin pages → add to `admin/includes/settings.php`
- New REST endpoints → add to `admin/includes/api.php`
- Never modify `vendor-prefixed/` or `blocks-build/` directly (they are build artifacts)
- Always run `composer lint` before committing PHP changes

## Commits
- Never bypass the pre-commit hook: no `git commit --no-verify` and no agent commit helper's `noVerify` option. lint-staged must run `vendor/bin/phpcbf` (auto-fix) followed by `vendor/bin/phpcs` on staged PHP files.
- `vendor/bin/phpcs` lints the whole repository through `composer lint`, so every file it reports must be clean before the commit lands; fix a pre-existing violation or report it, never skip it.
- Run `composer lint` and the specific `composer test` filter for the change before committing, and push only after those gates passed for the exact commits being pushed.
- lint-staged stashes the worktree while it runs and may leave a `lint-staged automatic backup` stash. Re-check `git status` and `git show --stat` after the commit: a staged hunk can silently drop when a file mixes your edits with pre-existing ones.
