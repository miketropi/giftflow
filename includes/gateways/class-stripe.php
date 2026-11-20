<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Stripe Payment Gateway for GiftFlow
 *
 * This class implements Stripe payment processing using Stripe PHP SDK
 * with support for Payment Intents, 3D Secure (SCA), and webhooks.
 *
 * Every Stripe operation runs against an explicit environment resolved from the
 * stored donation (see GiftFlow\Stripe\StripeDonationBinding) rather than a
 * single mutable client.
 *
 * @package GiftFlow
 * @subpackage Gateways
 * @since 1.0.0
 * @version 3.0.0
 */

namespace GiftFlow\Gateways;

use GiftFlow\Vendor\Stripe\PaymentIntent;
use GiftFlow\Vendor\Stripe\StripeClient;
use GiftFlow\Vendor\Stripe\Exception\ApiErrorException;
use GiftFlow\Core\Donations;
use GiftFlow\Core\Logger as Giftflow_Logger;
use GiftFlow\Core\Donation_Event_History;
use GiftFlow\Stripe\StripeClientConfigurationException;
use GiftFlow\Stripe\StripeClientFactory;
use GiftFlow\Stripe\StripeConnectionPanel;
use GiftFlow\Stripe\StripeConnectionRepository;
use GiftFlow\Stripe\StripeDonationBinding;
use GiftFlow\Stripe\StripeEnvironment;
use GiftFlow\Stripe\StripeWebhookDispatcher;
use GiftFlow\Stripe\StripeWebhookEventRegistry;
use GiftFlow\Stripe\StripeWebhookProvisioner;
use GiftFlow\Stripe\StripeWebhookReceiver;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Stripe SDK service accessors are camelCase by design.

/**
 * Stripe Gateway Class
 */
class Stripe_Gateway extends Gateway_Base {

	/**
	 * Stripe client for the active payment environment.
	 *
	 * @var StripeClient|null
	 */
	private $stripe;

	/**
	 * Connection repository.
	 *
	 * @var StripeConnectionRepository|null
	 */
	private $repository;

	/**
	 * Donation binding helper.
	 *
	 * @var StripeDonationBinding|null
	 */
	private $binding;

	/**
	 * Shared webhook receiver.
	 *
	 * @var StripeWebhookReceiver|null
	 */
	private $webhook_receiver;

	/**
	 * Initialize gateway properties
	 */
	protected function init_gateway() {
		$this->id = 'stripe';
		$this->title = esc_html__( 'Credit Card (Stripe)', 'giftflow' );
		$this->description = esc_html__( 'Accept payments securely via Stripe using credit cards', 'giftflow' );

		// SVG icon.
		$this->icon = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-credit-card-icon lucide-credit-card"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>';

		$this->order = 10;
		$this->supports = apply_filters(
			'giftflow_stripe_gateway_supports',
			array(
				'webhooks',
				'3d_secure',
				'payment_intents',
			),
			$this
		);
	}

	/**
	 * Ready function
	 *
	 * @return void
	 */
	protected function ready() {
		$this->init_stripe_sdk();
		$this->add_stripe_assets();
	}

	/**
	 * Connection repository.
	 *
	 * @return StripeConnectionRepository
	 */
	public function get_repository(): StripeConnectionRepository {
		if ( null === $this->repository ) {
			$this->repository = new StripeConnectionRepository();
		}

		return $this->repository;
	}

	/**
	 * Donation binding helper.
	 *
	 * @return StripeDonationBinding
	 */
	private function binding(): StripeDonationBinding {
		if ( null === $this->binding ) {
			$this->binding = new StripeDonationBinding( $this->get_repository() );
		}

		return $this->binding;
	}

	/**
	 * Active payment environment.
	 *
	 * Only donor payments use this value; profile editing never writes it.
	 *
	 * @return string
	 */
	public function get_active_environment(): string {
		return $this->get_repository()->get_active_environment();
	}

	/**
	 * Initialize the Stripe client for the active payment environment.
	 *
	 * @return void
	 */
	private function init_stripe_sdk() {
		$environment = $this->get_active_environment();
		$credentials = $this->get_repository()->get_credentials( $environment );

		try {
			$this->stripe = StripeClientFactory::create( $environment, $credentials['secret_key'] );
		} catch ( StripeClientConfigurationException $e ) {
			$this->stripe = null;

			if ( '' !== $credentials['secret_key'] ) {
				Giftflow_Logger::error(
					'stripe.client.unavailable',
					array(
						'environment' => $environment,
						'message'     => $e->getMessage(),
						'gateway'     => 'stripe',
					),
					'stripe'
				);
			}
		}
	}

	/**
	 * Stripe client for one environment.
	 *
	 * @param string $environment Environment id.
	 * @return StripeClient|null Null when the environment is not configured.
	 */
	public function client_for_environment( string $environment ): ?StripeClient {
		$environment = StripeEnvironment::assert_valid( $environment );
		$credentials = $this->get_repository()->get_credentials( $environment );

		try {
			return StripeClientFactory::create( $environment, $credentials['secret_key'] );
		} catch ( StripeClientConfigurationException $e ) {
			return null;
		}
	}

	/**
	 * Resolve the environment a donation belongs to, recording legacy records.
	 *
	 * @param int $donation_id Donation post id.
	 * @return string
	 */
	public function resolve_donation_environment( int $donation_id ): string {
		return $this->binding()->resolve_environment( $donation_id );
	}

	/**
	 * Get publishable key for the active payment environment.
	 *
	 * @return string
	 */
	public function get_publishable_key() {
		$credentials = $this->get_repository()->get_credentials( $this->get_active_environment() );

		return $credentials['publishable_key'];
	}

	/**
	 * Add Stripe-specific assets
	 *
	 * @return void
	 */
	private function add_stripe_assets() {
		// Custom Stripe donation script.
		$this->add_script(
			'giftflow-stripe-donation',
			array(
				'src' => GIFTFLOW_PLUGIN_URL . 'assets/js/stripe-donation.bundle.js',
				'deps' => array( 'jquery', 'giftflow-donation-forms' ),
				'version' => GIFTFLOW_VERSION,
				'frontend' => true,
				'admin' => false,
				'in_footer' => true,
				'localize' => array(
					'name' => 'giftflowStripeDonation',
					'data' => $this->get_script_data(),
				),
			)
		);
	}

	/**
	 * Get script localization data
	 *
	 * @return array
	 */
	private function get_script_data() {

			return array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'stripe_publishable_key' => $this->get_publishable_key(),
				'mode' => $this->get_active_environment(),
				'nonce' => wp_create_nonce( 'giftflow_stripe_nonce' ),
				'return_url' => add_query_arg( 'giftflow_stripe_return', '1', home_url() ),
				'currency' => $this->get_currency(),
				'country' => $this->get_country_code(),
				'site_name' => get_bloginfo( 'name' ),
				'apple_pay_google_pay_enabled' => $this->get_setting( 'stripe_apple_pay_google_pay_enabled', false ),
				'recurring_enabled' => (bool) $this->get_setting( 'stripe_recurring_enabled', false ),
				'messages' => array(
					'processing' => __( 'Processing payment...', 'giftflow' ),
					'error' => __( 'Payment failed. Please try again.', 'giftflow' ),
					'invalid_card' => __( 'Please enter valid card details.', 'giftflow' ),
					'authentication_required' => __( 'Additional authentication required.', 'giftflow' ),
				),
			);
	}

	/**
	 * Get gateway settings fields
	 *
	 * Credential and environment fields live in the Stripe connection panel
	 * rendered by {@see self::render_connection_settings()}; the accordion keeps
	 * only the shared feature toggles.
	 *
	 * @param array $payment_fields Existing payment fields.
	 * @return array
	 */
	public function register_settings_fields( $payment_fields = array() ) {
		$payment_options = get_option( 'giftflow_payment_options' );
		$stripe_options  = isset( $payment_options['stripe'] ) && is_array( $payment_options['stripe'] )
			? $payment_options['stripe']
			: array();
		$repository      = $this->get_repository();
		$events          = StripeWebhookEventRegistry::required_events( $repository->is_recurring_enabled() );

		$payment_fields['stripe'] = array(
			'id' => 'giftflow_stripe',
			'name' => 'giftflow_payment_options[stripe]',
			'type' => 'accordion',
			'label' => __( 'Stripe (Credit Card)', 'giftflow' ),
			'description' => __( 'Configure Stripe payment settings', 'giftflow' ),
			'content' => array( $this, 'render_connection_settings' ),
			'accordion_settings' => array(
				'label' => __( 'Stripe Settings', 'giftflow' ),
				'is_open' => true,
				'fields' => array(
					'stripe_enabled' => array(
						'id' => 'giftflow_stripe_enabled',
						'type' => 'switch',
						'label' => __( 'Enable Stripe', 'giftflow' ),
						'value' => isset( $stripe_options['stripe_enabled'] ) ? $stripe_options['stripe_enabled'] : false,
						'description' => __( 'Enable Stripe as a payment method', 'giftflow' ),
					),
					'stripe_webhook_enabled' => array(
						'id' => 'giftflow_stripe_webhook_enabled',
						'type' => 'switch',
						'label' => __( 'Enable Webhook', 'giftflow' ),
						'value' => isset( $stripe_options['stripe_webhook_enabled'] ) ? $stripe_options['stripe_webhook_enabled'] : false,
						'description' => sprintf(
							// translators: 1: webhook URL, 2: comma separated event names.
							__( 'Enable webhooks for payment status updates. Webhook URL: %1$s<br>Recommended Stripe events: %2$s', 'giftflow' ),
							'<code>' . esc_html( $this->get_webhook_url() ) . '</code>',
							esc_html( implode( ', ', $events ) )
						),
					),
					'stripe_apple_pay_google_pay_enabled' => array(
						'id' => 'giftflow_stripe_apple_pay_google_pay_enabled',
						'type' => 'switch',
						'label' => __( 'Enable Apple Pay + Google Pay', 'giftflow' ),
						'value' => isset( $stripe_options['stripe_apple_pay_google_pay_enabled'] ) ? $stripe_options['stripe_apple_pay_google_pay_enabled'] : false,
						'description' => __( 'Enable Apple Pay + Google Pay as a payment method (Stripe automatically detects your device and browser to display the most suitable payment method, ensuring a smooth checkout experience)', 'giftflow' ) . ' <a href="https://stripe.com/docs/testing/wallets" target="_blank">' . __( 'read more Documentation', 'giftflow' ) . '</a>',
						'pro_only'    => true,
					),
					'stripe_recurring_enabled' => array(
						'id' => 'giftflow_stripe_recurring_enabled',
						'type' => 'switch',
						'label' => __( 'Enable Recurring Donations', 'giftflow' ),
						'value' => isset( $stripe_options['stripe_recurring_enabled'] ) ? $stripe_options['stripe_recurring_enabled'] : false,
						'description' => __( 'Allow donors to set up recurring donations via Stripe Subscriptions. Per-campaign recurring options are in Campaign Details.', 'giftflow' ),
						'pro_only'    => true,
					),
				),
			),
		);

		return $payment_fields;
	}

	/**
	 * Render the environment-scoped connection panel inside the accordion.
	 *
	 * @return void
	 */
	public function render_connection_settings(): void {
		$panel = new StripeConnectionPanel( $this->get_repository(), $this->get_active_environment(), $this->get_webhook_url() );
		$panel->render();
	}

	/**
	 * Template HTML
	 *
	 * @return void
	 */
	public function template_html() {

			giftflow_load_template(
				'payment-gateway/stripe-template.php',
				array(
					'id' => $this->id,
					'title' => $this->title,
					'icon' => $this->icon,
					'mode' => $this->get_active_environment(),
				)
			);
	}

	/**
	 * Additional hooks for Stripe gateway
	 */
	protected function init_additional_hooks() {
			// Webhook handler.
			add_action( 'wp_ajax_giftflow_stripe_webhook', array( $this, 'handle_webhook' ) );
			add_action( 'wp_ajax_nopriv_giftflow_stripe_webhook', array( $this, 'handle_webhook' ) );

			// Return URL handler.
			add_action( 'init', array( $this, 'handle_return_url' ) );

			// Admin: cancel subscription.
			add_action( 'wp_ajax_giftflow_stripe_cancel_subscription', array( $this, 'ajax_cancel_subscription' ) );
	}

	/**
	 * Process payment
	 *
	 * @param array $data Payment data.
	 * @param int $donation_id Donation ID.
	 * @return mixed
	 */
	public function process_payment( $data, $donation_id = 0 ) {
		if ( ! $donation_id ) {
			return new \WP_Error( 'stripe_error', __( 'Donation ID is required', 'giftflow' ) );
		}

		$environment = $this->resolve_donation_environment( (int) $donation_id );
		$client      = $this->client_for_environment( $environment );

		if ( ! $client ) {
			return new \WP_Error( 'stripe_error', __( 'Stripe is not properly configured', 'giftflow' ) );
		}

		$is_recurring = $this->is_recurring_donation( $data );
		$extension    = $this->checkout_extension( $data );

		try {
			if ( $is_recurring ) {
				return $this->process_recurring_payment( $data, $donation_id, $environment, $client );
			}

			// One-time flow.
			$payment_intent_data = $this->prepare_payment_intent_data( $data, $donation_id, $environment );

			if ( is_wp_error( $payment_intent_data ) ) {
				return $payment_intent_data;
			}

			$payment_intent = $client->paymentIntents->create( $payment_intent_data, $this->request_options( $extension, 'intent' ) );

			update_post_meta( $donation_id, '_stripe_payment_intent_id', $payment_intent->id );
			$this->binding()->persist( (int) $donation_id, $environment );

			if ( ! empty( $extension ) ) {
				$bound = \GiftFlow\Services\CheckoutExtension::bind(
					$extension,
					array(
						'donation_id' => (int) $donation_id,
						'provider_id' => (string) $payment_intent->id,
						'gateway'     => 'stripe',
					)
				);

				if ( is_wp_error( $bound ) ) {
					// A required extension refused this donation. The intent cannot be
					// confirmed, so the checkout stops with its ordinary error shape and
					// the donation is left pending for administrator review.
					$this->log_error( 'extension_bind_failed', $bound->get_error_message(), $donation_id, $bound->get_error_code() );

					return $bound;
				}
			}

			return $this->handle_payment_intent_response( $payment_intent, $donation_id, $environment );
		} catch ( ApiErrorException $e ) {
			$this->log_error( 'payment_exception', $e->getMessage(), $donation_id, $e->getStripeCode() );

			// A 4xx API error is a definitive rejection; anything else may be uncertain.
			$status = (int) $e->getHttpStatus();
			if ( ! empty( $extension ) && $status >= 400 && $status < 500 ) {
				\GiftFlow\Services\CheckoutExtension::failed( $extension, 'stripe_api_rejected', true );
			}

			return new \WP_Error( 'stripe_error', $e->getMessage() );
		} catch ( \Exception $e ) {
			$this->log_error( 'payment_exception', $e->getMessage(), $donation_id );
			return new \WP_Error( 'stripe_error', $e->getMessage() );
		}
	}

	/**
	 * Validated checkout extension payload for this submission.
	 *
	 * @param array $data Payment data.
	 * @return array Empty array when the checkout is not extended.
	 */
	private function checkout_extension( $data ) {
		if ( empty( $data['_checkout_extension'] ) || ! is_array( $data['_checkout_extension'] ) ) {
			return array();
		}

		$extension = $data['_checkout_extension'];

		if ( empty( $extension['attempt_key'] ) || empty( $extension['amount_minor'] ) ) {
			return array();
		}

		return $extension;
	}

	/**
	 * Stripe request options, keeping a stable idempotency key per operation.
	 *
	 * @param array  $extension Validated extension payload.
	 * @param string $operation Operation suffix (intent, price, subscription…).
	 * @return array
	 */
	private function request_options( $extension, $operation ) {
		if ( empty( $extension['attempt_key'] ) ) {
			return array();
		}

		return array( 'idempotency_key' => $extension['attempt_key'] . ':' . $operation );
	}

	/**
	 * Prepare payment intent data for Stripe API
	 *
	 * @param array  $data Payment data.
	 * @param int    $donation_id Donation ID.
	 * @param string $environment Environment id.
	 * @return array|\WP_Error
	 */
	private function prepare_payment_intent_data( $data, $donation_id, $environment ) {
		$statement_descriptor = $this->get_setting( 'statement_descriptor', get_bloginfo( 'name' ) );
		$statement_descriptor = substr( $statement_descriptor, 0, 22 ); // Stripe limit.

		$extension = $this->checkout_extension( $data );

		if ( ! empty( $extension ) ) {
			// Extended checkouts charge the exact server-resolved integer amount.
			$amount_in_cents = (int) $extension['amount_minor'];
			$charge_currency = strtolower( (string) $extension['currency'] );
		} else {
			// Convert amount to cents (Stripe expects smallest currency unit).
			$amount_in_cents = (int) ( (float) $data['donation_amount'] * 100 );
			$charge_currency = strtolower( $this->get_currency() );
		}

		$metadata = array_merge(
			array(
				'donation_id' => (string) $donation_id,
				'campaign_id' => (string) $data['campaign_id'],
				'donor_email' => sanitize_email( $data['donor_email'] ),
				'donor_name' => sanitize_text_field( $data['donor_name'] ),
				'site_url' => home_url(),
			),
			$this->binding()->resource_metadata( $environment )
		);

		if ( ! empty( $extension ) ) {
			$metadata['giftflow_attempt'] = (string) $extension['attempt_key'];
		}

		$payment_intent_data = array(
			'amount' => $amount_in_cents,
			'currency' => $charge_currency,
			'payment_method' => $data['payment_method_id'], // Payment Method ID from frontend.
			'confirmation_method' => 'manual',
			'confirm' => true,
			'return_url' => $this->get_return_url( $donation_id ),
			// translators: 1: donor name, 2: campaign id or name.
			'description' => sprintf( __( 'Donation from %1$s for campaign %2$s', 'giftflow' ), sanitize_text_field( $data['donor_name'] ), $data['campaign_id'] ),
			'metadata' => $metadata,
		);

		// Add receipt email.
		if ( ! empty( $data['donor_email'] ) ) {
			$payment_intent_data['receipt_email'] = sanitize_email( $data['donor_email'] );
		}

		$payment_intent_data = apply_filters( 'giftflow_stripe_prepare_payment_intent_data', $payment_intent_data, $data, $donation_id );

		if ( ! empty( $extension ) ) {
			// Fail closed: a filter must not silently change a promised reward charge.
			$filtered_amount   = isset( $payment_intent_data['amount'] ) ? (int) $payment_intent_data['amount'] : 0;
			$filtered_currency = isset( $payment_intent_data['currency'] ) ? strtolower( (string) $payment_intent_data['currency'] ) : '';

			if ( $filtered_amount !== $amount_in_cents || $filtered_currency !== $charge_currency ) {
				return new \WP_Error(
					'stripe_error',
					__( 'The selected reward cannot be charged with this payment configuration.', 'giftflow' )
				);
			}
		}

		return $payment_intent_data;
	}

	/**
	 * Determine if the current donation should be processed as recurring.
	 *
	 * @param array $data Donation form data.
	 * @return bool
	 */
	private function is_recurring_donation( $data ) {
		if ( ! $this->get_setting( 'stripe_recurring_enabled', false ) ) {
			return false;
		}
		$donation_type = isset( $data['donation_type'] ) ? sanitize_text_field( $data['donation_type'] ) : 'one-time';
		$interval      = isset( $data['recurring_interval'] ) ? sanitize_text_field( $data['recurring_interval'] ) : '';
		return ( 'recurring' === $donation_type && ! empty( $interval ) );
	}

	/**
	 * Map campaign recurring interval to Stripe Price recurring format.
	 *
	 * @param string $interval Campaign interval: daily, weekly, monthly, quarterly, yearly.
	 * @return array Stripe recurring array (interval, optional interval_count).
	 */
	private function map_recurring_interval_to_stripe( $interval ) {
		$map = array(
			'daily'     => array( 'interval' => 'day' ),
			'weekly'    => array( 'interval' => 'week' ),
			'monthly'   => array( 'interval' => 'month' ),
			'yearly'    => array( 'interval' => 'year' ),
			'quarterly' => array(
				'interval' => 'month',
				'interval_count' => 3,
			),
		);
		return isset( $map[ $interval ] ) ? $map[ $interval ] : array( 'interval' => 'month' );
	}

	/**
	 * Process recurring (subscription) payment via Stripe.
	 *
	 * @param array        $data        Donation form data.
	 * @param int          $donation_id Donation post ID.
	 * @param string       $environment Environment id.
	 * @param StripeClient $client      Environment-bound Stripe client.
	 * @return array|\WP_Error
	 */
	private function process_recurring_payment( $data, $donation_id, $environment, $client ) {
		$customer_id = $this->get_or_create_stripe_customer( $data, $donation_id, $environment, $client );
		if ( is_wp_error( $customer_id ) ) {
			return $customer_id;
		}

		$pm_id = isset( $data['payment_method_id'] ) ? sanitize_text_field( $data['payment_method_id'] ) : '';
		if ( empty( $pm_id ) ) {
			return new \WP_Error( 'stripe_error', __( 'Payment method is required', 'giftflow' ) );
		}

		try {
			$client->paymentMethods->attach( $pm_id, array( 'customer' => $customer_id ) );
		} catch ( ApiErrorException $e ) {
			return new \WP_Error( 'stripe_error', $e->getMessage() );
		}

		$client->customers->update(
			$customer_id,
			array(
				'invoice_settings' => array(
					'default_payment_method' => $pm_id,
				),
			)
		);

		$interval_raw = isset( $data['recurring_interval'] ) ? sanitize_text_field( $data['recurring_interval'] ) : 'monthly';
		$stripe_recurring = $this->map_recurring_interval_to_stripe( $interval_raw );

		$extension = $this->checkout_extension( $data );

		if ( ! empty( $extension ) ) {
			// Extended checkouts price the exact server-resolved integer amount.
			$amount_cents      = (int) $extension['amount_minor'];
			$price_currency    = strtolower( (string) $extension['currency'] );
		} else {
			$amount_cents      = (int) ( (float) $data['donation_amount'] * 100 );
			$price_currency    = strtolower( $this->get_currency() );
		}

		$price_params = array(
			'unit_amount'   => $amount_cents,
			'currency'      => $price_currency,
			'recurring'     => $stripe_recurring,
			'product_data'  => array(
				'name' => sprintf(
					/* translators: 1: site name, 2: interval */
					__( '%1$s Recurring Donation (%2$s)', 'giftflow' ),
					get_bloginfo( 'name' ),
					$interval_raw
				),
			),
		);

		try {
			$price = $client->prices->create( $price_params, $this->request_options( $extension, 'price' ) );
		} catch ( ApiErrorException $e ) {
			return new \WP_Error( 'stripe_error', $e->getMessage() );
		}

		if ( ! empty( $extension ) ) {
			// Fail closed: the created price must match the promised reward charge.
			$price_amount   = isset( $price->unit_amount ) ? (int) $price->unit_amount : 0;
			$price_code     = isset( $price->currency ) ? strtolower( (string) $price->currency ) : '';

			if ( $price_amount !== $amount_cents || $price_code !== $price_currency ) {
				return new \WP_Error(
					'stripe_error',
					__( 'The selected reward cannot be charged with this payment configuration.', 'giftflow' )
				);
			}
		}

		$subscription_params = array(
			'customer'             => $customer_id,
			'items'                => array( array( 'price' => $price->id ) ),
			'expand'               => array( 'latest_invoice.payment_intent' ),
			'metadata'             => array_merge(
				array(
					'donation_id' => (string) $donation_id,
					'campaign_id' => (string) ( isset( $data['campaign_id'] ) ? $data['campaign_id'] : '' ),
					'donor_email' => sanitize_email( $data['donor_email'] ),
					'donor_name' => sanitize_text_field( $data['donor_name'] ),
					'site_url'   => home_url(),
				),
				$this->binding()->resource_metadata( $environment )
			),
		);

		/**
		 * Filter the Stripe subscription parameters before the subscription is created.
		 *
		 * @since 1.1.15
		 *
		 * @param array  $subscription_params Subscription payload.
		 * @param array  $data              Donation form data.
		 * @param int    $donation_id       Donation post ID.
		 * @param string $environment       Environment id.
		 */
		$subscription_params = apply_filters( 'giftflow_stripe_subscription_params', $subscription_params, $data, $donation_id, $environment );

		try {
			$subscription = $client->subscriptions->create( $subscription_params, $this->request_options( $extension, 'subscription' ) );
		} catch ( ApiErrorException $e ) {
			return new \WP_Error( 'stripe_error', $e->getMessage() );
		}

		$recurring_number_of_times = isset( $data['recurring_number_of_times'] ) ? absint( $data['recurring_number_of_times'] ) : 0;

		update_post_meta( $donation_id, '_stripe_customer_id', $customer_id );
		update_post_meta( $donation_id, '_stripe_subscription_id', $subscription->id );
		update_post_meta( $donation_id, '_stripe_price_id', $price->id );
		update_post_meta( $donation_id, '_donation_type', 'recurring' );
		update_post_meta( $donation_id, '_recurring_interval', $interval_raw );
		update_post_meta( $donation_id, '_recurring_status', 'active' );
		update_post_meta( $donation_id, '_recurring_number_of_times', $recurring_number_of_times );
		update_post_meta( $donation_id, '_is_subscription_parent', '1' );
		$this->binding()->persist( (int) $donation_id, $environment );

		if ( ! empty( $extension ) ) {
			$bound = \GiftFlow\Services\CheckoutExtension::bind(
				$extension,
				array(
					'donation_id' => (int) $donation_id,
					'provider_id' => (string) $subscription->id,
					'gateway'     => 'stripe',
				)
			);

			if ( is_wp_error( $bound ) ) {
				$this->log_error( 'extension_bind_failed', $bound->get_error_message(), $donation_id, $bound->get_error_code() );

				return $bound;
			}
		}

		if ( isset( $subscription->current_period_end ) ) {
			update_post_meta( $donation_id, '_recurring_next_payment_date', gmdate( 'Y-m-d\TH:i:s\Z', $subscription->current_period_end ) );
		}

		$latest_invoice = $subscription->latest_invoice;
		$invoice_paid   = is_object( $latest_invoice ) && isset( $latest_invoice->status ) && 'paid' === $latest_invoice->status;

		if ( ! $invoice_paid ) {
			if ( ! empty( $extension ) ) {
				\GiftFlow\Services\CheckoutExtension::failed( $extension, 'stripe_invoice_unpaid', false );
			}

			return new \WP_Error(
				'stripe_error',
				__( 'The payment for your donation was not completed. Please try again, and if the issue persists, don\'t hesitate to contact us for assistance. Thank you for your support!', 'giftflow' )
			);
		}

		// Fire action to notify other plugins that the subscription was created.
		do_action( 'giftflow_stripe_subscription_created', $donation_id, $subscription->id, $subscription->toArray() );

		return true;
	}

	/**
	 * Get existing Stripe Customer for this donor or create a new one.
	 *
	 * Customer lookups are filtered by environment so a Sandbox customer id can
	 * never be reused against Live (or the other way round).
	 *
	 * @param array        $data        Donation form data.
	 * @param int          $donation_id Donation post ID.
	 * @param string       $environment Environment id.
	 * @param StripeClient $client      Environment-bound Stripe client.
	 * @return string|\WP_Error Stripe Customer ID or WP_Error.
	 */
	private function get_or_create_stripe_customer( $data, $donation_id, $environment, $client ) {
		$email = sanitize_email( $data['donor_email'] );

		$existing = get_posts(
			array(
				'post_type'      => 'donation',
				'posts_per_page' => 1,
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key' => '_stripe_customer_id',
						'compare' => 'EXISTS',
					),
					array(
						'key' => '_payment_method',
						'value' => 'stripe',
					),
					array(
						'key' => StripeDonationBinding::META_ENVIRONMENT,
						'value' => $environment,
					),
				),
			)
		);

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

		try {

			$customer_data = array(
				'email'    => $email,
				'name'     => sanitize_text_field( $data['donor_name'] ),
				'metadata' => array_merge(
					array(
						'site_url'    => home_url(),
						'donation_id' => (string) $donation_id,
					),
					$this->binding()->resource_metadata( $environment )
				),
			);

			/**
			 * Filter the Stripe Customer data before creating a customer.
			 *
			 * @since 1.0.0
			 *
			 * @param array $customer_data     Data to be sent to Stripe when creating the customer.
			 * @param array $data             Original donation form data.
			 * @param int   $donation_id      Donation post ID.
			 */
			$customer_data = apply_filters( 'giftflow_stripe_customer_data', $customer_data, $data, $donation_id );

			$customer = $client->customers->create( $customer_data );
			return $customer->id;
		} catch ( ApiErrorException $e ) {
			return new \WP_Error( 'stripe_customer_error', $e->getMessage() );
		}
	}

	/**
	 * Handle payment intent response
	 *
	 * @param PaymentIntent $payment_intent Payment Intent from Stripe.
	 * @param int           $donation_id Donation ID.
	 * @param string        $environment Environment id.
	 * @return array|\WP_Error
	 */
	private function handle_payment_intent_response( $payment_intent, $donation_id, $environment ) {
		$status = $payment_intent->status;

		switch ( $status ) {
			case 'succeeded':
				return $this->handle_successful_payment_intent( $payment_intent, $donation_id, $environment );

			case 'requires_action':
			case 'requires_source_action':
				return $this->handle_action_required_intent( $payment_intent, $donation_id, $environment );

			case 'requires_payment_method':
			case 'requires_source':
				return $this->handle_failed_payment_intent( $payment_intent, $donation_id, __( 'Payment method was declined', 'giftflow' ), $environment );

			case 'processing':
				return $this->handle_processing_intent( $payment_intent, $donation_id, $environment );

			case 'canceled':
				return $this->handle_failed_payment_intent( $payment_intent, $donation_id, __( 'Payment was canceled', 'giftflow' ), $environment );

			default:
				return $this->handle_failed_payment_intent( $payment_intent, $donation_id, __( 'Payment could not be processed', 'giftflow' ), $environment );
		}
	}

	/**
	 * Handle successful payment intent
	 *
	 * @param PaymentIntent $payment_intent Payment Intent from Stripe.
	 * @param int           $donation_id Donation ID.
	 * @param string        $environment Environment id.
	 * @return array
	 */
	private function handle_successful_payment_intent( $payment_intent, $donation_id, $environment ) {
		$transaction_id = $payment_intent->id;
		$charge_id = ! empty( $payment_intent->charges->data ) ? $payment_intent->charges->data[0]->id : '';

		// Update donation meta.
		update_post_meta( $donation_id, '_transaction_id', $transaction_id );
		update_post_meta( $donation_id, '_stripe_charge_id', $charge_id );
		update_post_meta( $donation_id, '_transaction_raw_data', wp_json_encode( $payment_intent->toArray() ) );
		update_post_meta( $donation_id, '_payment_method', 'stripe' );
		$this->binding()->persist( (int) $donation_id, $environment );

		// Use centralized Donations class to update status.
		$donations_class = new Donations();
		$donations_class->update_status( $donation_id, 'completed' );

		Donation_Event_History::add(
			$donation_id,
			'payment_succeeded',
			'completed',
			'',
			array(
				'transaction_id' => $transaction_id,
				'charge_id' => $charge_id,
				'gateway' => 'stripe',
				'environment' => $environment,
			)
		);
		$this->log_success( $transaction_id, $donation_id );

		$this->emit_observation( 'paid', (int) $donation_id, $environment, (string) $payment_intent->id, $charge_id ? $charge_id : $transaction_id, true, 'sync' );

		do_action( 'giftflow_stripe_payment_completed', $donation_id, $transaction_id, $payment_intent->toArray() );

		// Return success response for AJAX.
		return array(
			'success' => true,
			'status' => 'succeeded',
			'payment_intent_id' => $transaction_id,
			'message' => __( 'Payment successful', 'giftflow' ),
		);
	}

	/**
	 * Handle action required payment intent (3D Secure / SCA)
	 *
	 * @param PaymentIntent $payment_intent Payment Intent from Stripe.
	 * @param int           $donation_id Donation ID.
	 * @param string        $environment Environment id.
	 * @return array
	 */
	private function handle_action_required_intent( $payment_intent, $donation_id, $environment ) {
		// Store payment intent for later verification.
		update_post_meta( $donation_id, '_payment_status', 'processing' );
		Donation_Event_History::add(
			$donation_id,
			'payment_requires_action',
			'processing',
			__( 'Additional authentication required', 'giftflow' ),
			array(
				'payment_intent_id' => $payment_intent->id,
				'gateway' => 'stripe',
				'environment' => $environment,
			)
		);

		return array(
			'success' => false,
			'requires_action' => true,
			'payment_intent_id' => $payment_intent->id,
			'client_secret' => $payment_intent->client_secret,
			'status' => $payment_intent->status,
			'message' => __( 'Payment requires additional authentication', 'giftflow' ),
		);
	}

	/**
	 * Handle processing payment intent
	 *
	 * @param PaymentIntent $payment_intent Payment Intent from Stripe.
	 * @param int           $donation_id Donation ID.
	 * @param string        $environment Environment id.
	 * @return array
	 */
	private function handle_processing_intent( $payment_intent, $donation_id, $environment ) {
		update_post_meta( $donation_id, '_payment_status', 'processing' );
		Donation_Event_History::add(
			$donation_id,
			'payment_processing',
			'processing',
			'',
			array(
				'payment_intent_id' => $payment_intent->id,
				'gateway' => 'stripe',
				'environment' => $environment,
			)
		);

		return array(
			'success' => false,
			'processing' => true,
			'payment_intent_id' => $payment_intent->id,
			'status' => 'processing',
			'message' => __( 'Payment is being processed', 'giftflow' ),
		);
	}

	/**
	 * Handle failed payment intent
	 *
	 * @param PaymentIntent $payment_intent Payment Intent from Stripe.
	 * @param int           $donation_id Donation ID.
	 * @param string        $default_message Default error message.
	 * @param string        $environment Environment id.
	 * @return \WP_Error
	 */
	private function handle_failed_payment_intent( $payment_intent, $donation_id, $default_message = '', $environment = '' ) {
		$error_message = $default_message;
		$error_code = '';

		// Get error details from payment intent.
		if ( ! empty( $payment_intent->last_payment_error ) ) {
			$error = $payment_intent->last_payment_error;
			$error_message = ! empty( $error->message ) ? $error->message : $default_message;
			$error_code = ! empty( $error->code ) ? $error->code : '';
		}

		if ( empty( $error_message ) ) {
			$error_message = __( 'Payment failed', 'giftflow' );
		}

		$this->log_error( 'payment_failed', $error_message, $donation_id, $error_code );
		Donation_Event_History::add(
			$donation_id,
			'payment_failed',
			'failed',
			$error_message,
			array(
				'error_code' => $error_code,
				'gateway' => 'stripe',
				'environment' => $environment,
			)
		);

		update_post_meta( $donation_id, '_payment_status', 'failed' );
		update_post_meta( $donation_id, '_payment_error', $error_message );

		// Use centralized Donations class to update status.
		$donations_class = new Donations();
		$donations_class->update_status( $donation_id, 'failed' );

		if ( 'canceled' === $payment_intent->status ) {
			$this->emit_observation( 'cancelled', (int) $donation_id, $environment, (string) $payment_intent->id, (string) $payment_intent->id, true, 'sync' );
		} else {
			$this->emit_observation( 'failed', (int) $donation_id, $environment, (string) $payment_intent->id, (string) $payment_intent->id, true, 'sync' );
		}

		$extension = \GiftFlow\Services\CheckoutExtension::from_donation( $donation_id );

		if ( ! empty( $extension ) ) {
			// A declined/canceled intent is a definitive provider rejection.
			\GiftFlow\Services\CheckoutExtension::failed( $extension, 'stripe_' . $payment_intent->status, true );
		}

		return new \WP_Error( 'stripe_error', $error_message );
	}

	/**
	 * Emit a normalized payment observation for Rewards-style consumers.
	 *
	 * @param string $kind            Observation kind.
	 * @param int    $donation_id     Donation ID.
	 * @param string $environment     Environment id.
	 * @param string $subject         Stable provider resource id (payment intent, charge, dispute).
	 * @param string $transaction_id  Provider transaction id.
	 * @param bool   $is_initial      Whether this is the initial successful charge.
	 * @param string $source          sync|webhook|donation_management.
	 * @return void
	 */
	private function emit_observation( $kind, $donation_id, $environment, $subject, $transaction_id, $is_initial = true, $source = 'sync' ) {
		if ( ! class_exists( '\GiftFlow\Services\PaymentObservation' ) ) {
			return;
		}

		$attempt = (string) get_post_meta( $donation_id, \GiftFlow\Services\CheckoutExtension::META_ATTEMPT, true );
		$amount  = get_post_meta( $donation_id, \GiftFlow\Services\CheckoutExtension::META_AMOUNT, true );
		$currency = (string) get_post_meta( $donation_id, \GiftFlow\Services\CheckoutExtension::META_CURRENCY, true );

		\GiftFlow\Services\PaymentObservation::emit(
			array(
				'event_key'      => 'stripe:' . $environment . ':' . $subject . ':' . $kind,
				'gateway'        => 'stripe',
				'environment'    => $environment,
				'kind'           => $kind,
				'donation_id'    => (int) $donation_id,
				'attempt_key'    => '' === $attempt ? null : $attempt,
				'provider_id'    => (string) $subject,
				'transaction_id' => (string) $transaction_id,
				'amount_minor'   => '' === (string) $amount ? null : (int) $amount,
				'currency'       => '' === $currency ? null : $currency,
				'is_initial'     => (bool) $is_initial,
				'source'         => $source,
			)
		);
	}

	/**
	 * Handle webhook notifications.
	 *
	 * The shared receiver verifies the signature against every stored
	 * environment secret, enforces livemode and donation binding, claims the
	 * event id atomically and only then dispatches side effects.
	 *
	 * @return void
	 */
	public function handle_webhook() {
		$this->webhook_receiver()->handle_request();
	}

	/**
	 * Shared webhook receiver instance.
	 *
	 * @return StripeWebhookReceiver
	 */
	private function webhook_receiver(): StripeWebhookReceiver {
		if ( null === $this->webhook_receiver ) {
			$repository             = $this->get_repository();
			$this->webhook_receiver = new StripeWebhookReceiver( $repository, new StripeWebhookDispatcher( $repository ) );
		}

		return $this->webhook_receiver;
	}

	/**
	 * Handle return URL from 3D Secure / SCA
	 *
	 * @return void
	 * @throws \Exception If Stripe is not configured.
	 */
	public function handle_return_url() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['giftflow_stripe_return'] ) || ! isset( $_GET['payment_intent'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$payment_intent_id = sanitize_text_field( wp_unslash( $_GET['payment_intent'] ) );

		// Find donation by payment intent ID.
		$donations = get_posts(
			array(
				'post_type' => 'donation',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_key' => '_stripe_payment_intent_id',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'meta_value' => $payment_intent_id,
				'posts_per_page' => 1,
			)
		);

		if ( ! empty( $donations ) ) {
			$donation_id = $donations[0]->ID;

			try {
				$environment = $this->resolve_donation_environment( (int) $donation_id );
				$client      = $this->client_for_environment( $environment );

				if ( ! $client ) {
					throw new \Exception( __( 'Stripe is not configured', 'giftflow' ) );
				}

				$payment_intent = $client->paymentIntents->retrieve( $payment_intent_id );

				if ( 'succeeded' === $payment_intent->status ) {
					$this->handle_successful_payment_intent( $payment_intent, $donation_id, $environment );
					wp_safe_redirect( esc_url( add_query_arg( 'payment_status', 'success', home_url() ) ) );
				} elseif ( in_array( $payment_intent->status, array( 'processing', 'requires_capture' ), true ) ) {
					// Payment is processing.
					update_post_meta( $donation_id, '_payment_status', 'processing' );
					wp_safe_redirect( esc_url( add_query_arg( 'payment_status', 'processing', home_url() ) ) );
				} else {
					$this->handle_failed_payment_intent( $payment_intent, $donation_id, '', $environment );
					wp_safe_redirect( esc_url( add_query_arg( 'payment_status', 'failed', home_url() ) ) );
				}
			} catch ( \Exception $e ) {
				$this->log_error( 'return_url_error', $e->getMessage(), $donation_id );
				wp_safe_redirect( esc_url( add_query_arg( 'payment_status', 'error', home_url() ) ) );
			}

			exit;
		}
	}

	/**
	 * AJAX handler: cancel a Stripe subscription from the admin.
	 *
	 * @throws \Exception If there is an error canceling the subscription.
	 */
	public function ajax_cancel_subscription() {
		check_ajax_referer( 'giftflow_stripe_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'giftflow' ) ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$donation_id     = isset( $_POST['donation_id'] ) ? absint( $_POST['donation_id'] ) : 0;
		$subscription_id = $donation_id ? get_post_meta( $donation_id, '_stripe_subscription_id', true ) : '';

		if ( empty( $subscription_id ) ) {
			wp_send_json_error( array( 'message' => __( 'No subscription found for this donation.', 'giftflow' ) ) );
		}

		try {
			$environment = $this->resolve_donation_environment( (int) $donation_id );
			$client      = $this->client_for_environment( $environment );

			if ( ! $client ) {
				throw new \Exception( __( 'Stripe is not configured', 'giftflow' ) );
			}

			$client->subscriptions->cancel( $subscription_id );
			update_post_meta( $donation_id, '_recurring_status', 'cancelled' );
			Donation_Event_History::add(
				$donation_id,
				'recurring_subscription_cancelled',
				'cancelled',
				__( 'Subscription cancelled by admin.', 'giftflow' ),
				array(
					'subscription_id' => $subscription_id,
					'gateway' => 'stripe',
					'environment' => $environment,
				)
			);
			wp_send_json_success( array( 'message' => __( 'Subscription cancelled successfully.', 'giftflow' ) ) );
		} catch ( ApiErrorException $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		} catch ( \Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}

	/**
	 * Get currency code
	 *
	 * @return string
	 */
	private function get_currency() {
			return apply_filters( 'giftflow_stripe_currency', 'USD' );
	}

	/**
	 * Get country code for Payment Request Button
	 *
	 * @return string
	 */
	private function get_country_code() {
			return apply_filters( 'giftflow_stripe_country_code', 'US' );
	}

	/**
	 * Get return URL
	 *
	 * @param int $donation_id Donation ID.
	 * @return string
	 */
	private function get_return_url( $donation_id ) {
		return add_query_arg(
			array(
				'giftflow_stripe_return' => '1',
				'donation_id' => $donation_id,
			),
			home_url()
		);
	}

	/**
	 * Get webhook URL
	 *
	 * Kept on the historical shared route, with the scheme normalized to HTTPS
	 * whenever the site itself is served over HTTPS.
	 *
	 * @return string
	 */
	public function get_webhook_url() {
		$url = admin_url( 'admin-ajax.php?action=giftflow_stripe_webhook' );

		return StripeWebhookProvisioner::normalize_url( $url );
	}

	/**
	 * Log successful payment
	 *
	 * @param string $transaction_id Transaction ID.
	 * @param int    $donation_id Donation ID.
	 */
	private function log_success( $transaction_id, $donation_id ) {
		Giftflow_Logger::info(
			'stripe.payment.succeeded',
			array(
				'donation_id'      => $donation_id,
				'transaction_id'   => $transaction_id,
				'gateway'          => 'stripe',
			),
			'stripe'
		);
	}

	/**
	 * Log error
	 *
	 * @param string $type Type of error.
	 * @param string $message Message of error.
	 * @param int    $donation_id Donation ID.
	 * @param string $code Code of error.
	 */
	private function log_error( $type, $message, $donation_id, $code = '' ) {
		Giftflow_Logger::error(
			'stripe.payment.failed',
			array(
				'type'          => $type,
				'donation_id'   => $donation_id,
				'error_message' => $message,
				'error_code'    => $code,
				'gateway'       => 'stripe',
			),
			'stripe'
		);
	}
}

add_action(
	'giftflow_register_gateways',
	function () {
		new \GiftFlow\Gateways\Stripe_Gateway();
	}
);

/**
 * Helper function to get Stripe Gateway instance
 *
 * @return Stripe_Gateway
 */
// phpcs:ignore Universal.Files.SeparateFunctionsFromOO.Mixed, Squiz.Commenting.FunctionComment.Missing
function giftflow_get_stripe_gateway() {
	return Gateway_Base::get_gateway( 'stripe' );
}

/**
 * Process Stripe payment (backward compatibility)
 *
 * @param array $data Payment data.
 * @param int $donation_id Donation ID.
 * @return mixed
 */
function giftflow_process_payment_stripe( $data = array(), $donation_id = 0 ) {
	$stripe_gateway = giftflow_get_stripe_gateway();

	if ( ! $stripe_gateway ) {
		return new \WP_Error( 'stripe_error', esc_html__( 'Stripe gateway not found', 'giftflow' ) );
	}

	return $stripe_gateway->process_payment( $data, $donation_id );
}
