<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Stripe Payment Gateway for GiftFlow
 *
 * This class implements Stripe payment processing using Stripe PHP SDK
 * with support for Payment Intents, 3D Secure (SCA), and webhooks.
 *
 * @package GiftFlow
 * @subpackage Gateways
 * @since 1.0.0
 * @version 2.0.0
 */

namespace GiftFlow\Gateways;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use Stripe\Webhook;
use GiftFlow\Core\Donations;
use GiftFlow\Core\Logger as Giftflow_Logger;
use GiftFlow\Core\Donation_Event_History;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stripe Gateway Class
 */
class Stripe_Gateway extends Gateway_Base {
	/**
	 * Stripe Client instance.
	 *
	 * @var StripeClient
	 */
	private $stripe;

	/**
	 * API Secret Key.
	 *
	 * @var string
	 */
	private $api_key;

	/**
	 * Webhook Secret.
	 *
	 * @var string
	 */
	private $webhook_secret;

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
		$this->supports = array(
			'webhooks',
			'3d_secure',
			'payment_intents',
		);
	}

	/**
	 * Ready function
	 *
	 * @return void
	 */
	protected function ready() {
		// Initialize Stripe SDK.
		$this->init_stripe_sdk();

		// Add Stripe-specific assets.
		$this->add_stripe_assets();
	}

	/**
	 * Initialize Stripe SDK
	 *
	 * @return void
	 */
	private function init_stripe_sdk() {
		$this->api_key = $this->get_api_key();
		$this->webhook_secret = $this->get_webhook_secret();

		if ( ! empty( $this->api_key ) ) {
			// Set the API key globally.
			Stripe::setApiKey( $this->api_key );

			// Create Stripe client instance.
			$this->stripe = new StripeClient( $this->api_key );
		}
	}

	/**
	 * Get API key based on mode
	 *
	 * @return string
	 */
	private function get_api_key() {
		$mode = $this->get_setting( 'stripe_mode', 'sandbox' );

		if ( 'live' === $mode ) {
			return $this->get_setting( 'stripe_live_secret_key' );
		}

		return $this->get_setting( 'stripe_sandbox_secret_key' );
	}

	/**
	 * Get publishable key based on mode
	 *
	 * @return string
	 */
	public function get_publishable_key() {
		$mode = $this->get_setting( 'stripe_mode', 'sandbox' );

		if ( 'live' === $mode ) {
			return $this->get_setting( 'stripe_live_publishable_key' );
		}

		return $this->get_setting( 'stripe_sandbox_publishable_key' );
	}

	/**
	 * Get webhook secret based on mode
	 *
	 * @return string
	 */
	private function get_webhook_secret() {
		$mode = $this->get_setting( 'stripe_mode', 'sandbox' );

		if ( 'live' === $mode ) {
			return $this->get_setting( 'stripe_live_webhook_secret', '' );
		}

		return $this->get_setting( 'stripe_sandbox_webhook_secret', '' );
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
				'mode' => $this->get_setting( 'stripe_mode', 'sandbox' ),
				'nonce' => wp_create_nonce( 'giftflow_stripe_nonce' ),
				'return_url' => add_query_arg( 'giftflow_stripe_return', '1', home_url() ),
				'currency' => $this->get_currency(),
				'country' => $this->get_country_code(),
				'site_name' => get_bloginfo( 'name' ),
				'apple_pay_google_pay_enabled' => $this->get_setting( 'stripe_apple_pay_google_pay_enabled', false ),
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
	 * @param array $payment_fields Existing payment fields.
	 * @return array
	 */
	public function register_settings_fields( $payment_fields = array() ) {
		$payment_options = get_option( 'giftflow_payment_options' );
		$payment_fields['stripe'] = array(
			'id' => 'giftflow_stripe',
			'name' => 'giftflow_payment_options[stripe]',
			'type' => 'accordion',
			'label' => __( 'Stripe (Credit Card)', 'giftflow' ),
			'description' => __( 'Configure Stripe payment settings', 'giftflow' ),
			'accordion_settings' => array(
				'label' => __( 'Stripe Settings', 'giftflow' ),
				'is_open' => true,
				'fields' => array(
					'stripe_enabled' => array(
						'id' => 'giftflow_stripe_enabled',
						'type' => 'switch',
						'label' => __( 'Enable Stripe', 'giftflow' ),
						'value' => isset( $payment_options['stripe']['stripe_enabled'] ) ? $payment_options['stripe']['stripe_enabled'] : false,
						'description' => __( 'Enable Stripe as a payment method', 'giftflow' ),
					),
					'stripe_mode' => array(
						'id' => 'giftflow_stripe_mode',
						'type' => 'select',
						'label' => __( 'Stripe Mode', 'giftflow' ),
						'value' => isset( $payment_options['stripe_mode'] ) ? $payment_options['stripe_mode'] : 'sandbox',
						'options' => array(
							'sandbox' => __( 'Sandbox (Test Mode)', 'giftflow' ),
							'live' => __( 'Live (Production Mode)', 'giftflow' ),
						),
						'description' => __( 'Select Stripe environment mode', 'giftflow' ),
					),
					'stripe_sandbox_publishable_key' => array(
						'id' => 'giftflow_stripe_sandbox_publishable_key',
						'type' => 'textfield',
						'label' => __( 'Stripe Sandbox Publishable Key', 'giftflow' ),
						'value' => isset( $payment_options['stripe']['stripe_sandbox_publishable_key'] ) ? $payment_options['stripe']['stripe_sandbox_publishable_key'] : '',
						'description' => __( 'Enter your Stripe sandbox publishable key', 'giftflow' ),
					),
					'stripe_sandbox_secret_key' => array(
						'id' => 'giftflow_stripe_sandbox_secret_key',
						'type' => 'textfield',
						'label' => __( 'Stripe Sandbox Secret Key', 'giftflow' ),
						'value' => isset( $payment_options['stripe']['stripe_sandbox_secret_key'] ) ? $payment_options['stripe']['stripe_sandbox_secret_key'] : '',
						'input_type' => 'password',
						'description' => __( 'Enter your Stripe sandbox secret key', 'giftflow' ),
					),
					'stripe_live_publishable_key' => array(
						'id' => 'giftflow_stripe_live_publishable_key',
						'type' => 'textfield',
						'label' => __( 'Stripe Live Publishable Key', 'giftflow' ),
						'value' => isset( $payment_options['stripe']['stripe_live_publishable_key'] ) ? $payment_options['stripe']['stripe_live_publishable_key'] : '',
						'description' => __( 'Enter your Stripe live publishable key', 'giftflow' ),
					),
					'stripe_live_secret_key' => array(
						'id' => 'giftflow_stripe_live_secret_key',
						'type' => 'textfield',
						'label' => __( 'Stripe Live Secret Key', 'giftflow' ),
						'value' => isset( $payment_options['stripe']['stripe_live_secret_key'] ) ? $payment_options['stripe']['stripe_live_secret_key'] : '',
						'input_type' => 'password',
						'description' => __( 'Enter your Stripe live secret key', 'giftflow' ),
					),
					// stripe_webhook_enabled.
					'stripe_webhook_enabled' => array(
						'id' => 'giftflow_stripe_webhook_enabled',
						'type' => 'switch',
						'label' => __( 'Enable Webhook', 'giftflow' ),
						'value' => isset( $payment_options['stripe']['stripe_webhook_enabled'] ) ? $payment_options['stripe']['stripe_webhook_enabled'] : false,
						'description' => sprintf(
							// translators: This is the label for enabling the Stripe webhook option in the payment gateway settings.
							__( 'Enable webhooks for payment status updates. Webhook URL: %s', 'giftflow' ),
							'<code>' . admin_url( 'admin-ajax.php?action=giftflow_stripe_webhook' ) . '</code><br>' . __( 'Recommended Stripe events to send: <strong>payment_intent.succeeded</strong>, <strong>payment_intent.payment_failed</strong>, <strong>charge.refunded</strong>.', 'giftflow' )
						),
					),
					// stripe_sandbox_webhook_secret.
					'stripe_sandbox_webhook_secret' => array(
						'id' => 'giftflow_stripe_sandbox_webhook_secret',
						'type' => 'textfield',
						'label' => __( 'Stripe Sandbox Webhook Secret', 'giftflow' ),
						'value' => isset( $payment_options['stripe']['stripe_sandbox_webhook_secret'] ) ? $payment_options['stripe']['stripe_sandbox_webhook_secret'] : '',
						'input_type' => 'password',
						'description' => __( 'Enter your Stripe sandbox webhook secret', 'giftflow' ),
					),
					// stripe_live_webhook_secret.
					'stripe_live_webhook_secret' => array(
						'id' => 'giftflow_stripe_live_webhook_secret',
						'type' => 'textfield',
						'label' => __( 'Stripe Live Webhook Secret', 'giftflow' ),
						'value' => isset( $payment_options['stripe']['stripe_live_webhook_secret'] ) ? $payment_options['stripe']['stripe_live_webhook_secret'] : '',
						'input_type' => 'password',
						'description' => __( 'Enter your Stripe live webhook secret', 'giftflow' ),
					),
					// support Apple Pay + Google Pay.
					'stripe_apple_pay_google_pay_enabled' => array(
						'id' => 'giftflow_stripe_apple_pay_google_pay_enabled',
						'type' => 'switch',
						'label' => __( 'Enable Apple Pay + Google Pay', 'giftflow' ),
						'value' => isset( $payment_options['stripe']['stripe_apple_pay_google_pay_enabled'] ) ? $payment_options['stripe']['stripe_apple_pay_google_pay_enabled'] : false,
						'description' => __( 'Enable Apple Pay + Google Pay as a payment method (Stripe automatically detects your device and browser to display the most suitable payment method, ensuring a smooth checkout experience)', 'giftflow' ) . ' <a href="https://stripe.com/docs/testing/wallets" target="_blank">' . __( 'read more Documentation', 'giftflow' ) . '</a>',
						'pro_only'    => true,
					),
				),
			),
		);

		return $payment_fields;
	}

	/**
	 * Template HTML
	 *
	 * @return void
	 */
	public function template_html() {

			giftflow_load_template(
				'payment-gateway/stripe.php',
				array(
					'id' => $this->id,
					'title' => $this->title,
					'icon' => $this->icon,
					'mode' => $this->get_setting( 'stripe_mode' ),
				)
			);
	}

	/**
	 * Additional hooks for Stripe gateway
	 */
	protected function init_additional_hooks() {
			// AJAX handlers.
			add_action( 'wp_ajax_giftflow_process_stripe_payment', array( $this, 'ajax_process_payment' ) );
			add_action( 'wp_ajax_nopriv_giftflow_process_stripe_payment', array( $this, 'ajax_process_payment' ) );

			// Webhook handler.
			add_action( 'wp_ajax_giftflow_stripe_webhook', array( $this, 'handle_webhook' ) );
			add_action( 'wp_ajax_nopriv_giftflow_stripe_webhook', array( $this, 'handle_webhook' ) );

			// Return URL handler.
			add_action( 'init', array( $this, 'handle_return_url' ) );
	}

	/**
	 * Process payment
	 *
	 * @param array $data Payment data.
	 * @param int $donation_id Donation ID.
	 * @return mixed
	 */
	public function process_payment( $data, $donation_id = 0 ) {
		if ( ! $this->stripe ) {
			return new \WP_Error( 'stripe_error', __( 'Stripe is not properly configured', 'giftflow' ) );
		}

		if ( ! $donation_id ) {
			return new \WP_Error( 'stripe_error', __( 'Donation ID is required', 'giftflow' ) );
		}

		try {
			// Prepare payment intent data.
			$payment_intent_data = $this->prepare_payment_intent_data( $data, $donation_id );

			// Create Payment Intent.
			$payment_intent = $this->stripe->paymentIntents->create( $payment_intent_data );

			// Store payment intent ID.
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

	/**
	 * Prepare payment intent data for Stripe API
	 *
	 * @param array $data Payment data.
	 * @param int $donation_id Donation ID.
	 * @return array
	 */
	private function prepare_payment_intent_data( $data, $donation_id ) {
		$statement_descriptor = $this->get_setting( 'statement_descriptor', get_bloginfo( 'name' ) );
		$statement_descriptor = substr( $statement_descriptor, 0, 22 ); // Stripe limit.

		// Convert amount to cents (Stripe expects smallest currency unit).
		$amount_in_cents = (int) ( (float) $data['donation_amount'] * 100 );

		$payment_intent_data = array(
			'amount' => $amount_in_cents,
			'currency' => strtolower( $this->get_currency() ),
			'payment_method' => $data['payment_method_id'], // Payment Method ID from frontend.
			'confirmation_method' => 'manual',
			'confirm' => true,
			'return_url' => $this->get_return_url( $donation_id ),
			// translators: 1: donor name, 2: campaign id or name.
			'description' => sprintf( __( 'Donation from %1$s for campaign %2$s', 'giftflow' ), sanitize_text_field( $data['donor_name'] ), $data['campaign_id'] ),
			'metadata' => array(
				'donation_id' => (string) $donation_id,
				'campaign_id' => (string) $data['campaign_id'],
				'donor_email' => sanitize_email( $data['donor_email'] ),
				'donor_name' => sanitize_text_field( $data['donor_name'] ),
				'site_url' => home_url(),
			),
		);

		// Add receipt email.
		if ( ! empty( $data['donor_email'] ) ) {
			$payment_intent_data['receipt_email'] = sanitize_email( $data['donor_email'] );
		}

		return apply_filters( 'giftflow_stripe_prepare_payment_intent_data', $payment_intent_data, $data, $donation_id );
	}

	/**
	 * Handle payment intent response
	 *
	 * @param PaymentIntent $payment_intent Payment Intent from Stripe.
	 * @param int $donation_id Donation ID.
	 * @return array|\WP_Error
	 */
	private function handle_payment_intent_response( $payment_intent, $donation_id ) {
		$status = $payment_intent->status;

		switch ( $status ) {
			case 'succeeded':
				return $this->handle_successful_payment_intent( $payment_intent, $donation_id );

			case 'requires_action':
			case 'requires_source_action':
				return $this->handle_action_required_intent( $payment_intent, $donation_id );

			case 'requires_payment_method':
			case 'requires_source':
				return $this->handle_failed_payment_intent( $payment_intent, $donation_id, __( 'Payment method was declined', 'giftflow' ) );

			case 'processing':
				return $this->handle_processing_intent( $payment_intent, $donation_id );

			case 'canceled':
				return $this->handle_failed_payment_intent( $payment_intent, $donation_id, __( 'Payment was canceled', 'giftflow' ) );

			default:
				return $this->handle_failed_payment_intent( $payment_intent, $donation_id, __( 'Payment could not be processed', 'giftflow' ) );
		}
	}

	/**
	 * Handle successful payment intent
	 *
	 * @param PaymentIntent $payment_intent Payment Intent from Stripe.
	 * @param int $donation_id Donation ID.
	 * @return array
	 */
	private function handle_successful_payment_intent( $payment_intent, $donation_id ) {
		$transaction_id = $payment_intent->id;
		$charge_id = ! empty( $payment_intent->charges->data ) ? $payment_intent->charges->data[0]->id : '';

		// Update donation meta.
		update_post_meta( $donation_id, '_transaction_id', $transaction_id );
		update_post_meta( $donation_id, '_stripe_charge_id', $charge_id );
		update_post_meta( $donation_id, '_transaction_raw_data', wp_json_encode( $payment_intent->toArray() ) );
		update_post_meta( $donation_id, '_payment_method', 'stripe' );

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
			)
		);
		$this->log_success( $transaction_id, $donation_id );

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
	 * @param int $donation_id Donation ID.
	 * @return array
	 */
	private function handle_action_required_intent( $payment_intent, $donation_id ) {
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
	 * @param int $donation_id Donation ID.
	 * @return array
	 */
	private function handle_processing_intent( $payment_intent, $donation_id ) {
		update_post_meta( $donation_id, '_payment_status', 'processing' );
		Donation_Event_History::add(
			$donation_id,
			'payment_processing',
			'processing',
			'',
			array(
				'payment_intent_id' => $payment_intent->id,
				'gateway' => 'stripe',
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
	 * @param int $donation_id Donation ID.
	 * @param string $default_message Default error message.
	 * @return \WP_Error
	 */
	private function handle_failed_payment_intent( $payment_intent, $donation_id, $default_message = '' ) {
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
			)
		);

		update_post_meta( $donation_id, '_payment_status', 'failed' );
		update_post_meta( $donation_id, '_payment_error', $error_message );

		// Use centralized Donations class to update status.
		$donations_class = new Donations();
		$donations_class->update_status( $donation_id, 'failed' );

		return new \WP_Error( 'stripe_error', $error_message );
	}

	/**
	 * AJAX handler for processing payments
	 */
	public function ajax_process_payment() {
			check_ajax_referer( 'giftflow_stripe_nonce', 'nonce' );

			$data = $_POST;
			$donation_id = intval( $data['donation_id'] );

			$result = $this->process_payment( $data, $donation_id );

		if ( is_wp_error( $result ) ) {
				wp_send_json_error(
					array(
						'message' => $result->get_error_message(),
					)
				);
		} else {
				wp_send_json_success( $result );
		}
	}

	/**
	 * Handle webhook notifications
	 */
	public function handle_webhook() {
		if ( ! $this->get_setting( 'stripe_webhook_enabled', '1' ) ) {
			status_header( 200 );
			exit;
		}

		// Reviewer Note: Stripe webhooks POST raw JSON data and Stripe's signature verification requires access to this exact, unmodified request body.
		// As such, we intentionally do not sanitize or alter the raw input at this stage—this is critical for validating webhook authenticity.
		// The webhook_secret value will be verified in the following logic below .
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$payload = file_get_contents( 'php://input' );
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$sig_header = isset( $_SERVER['HTTP_STRIPE_SIGNATURE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_STRIPE_SIGNATURE'] ) ) : '';

		try {
			// Verify webhook signature if webhook secret is configured.
			if ( ! empty( $this->webhook_secret ) ) {
				$event = Webhook::constructEvent(
					$payload,
					$sig_header,
					$this->webhook_secret
				);
			} else {
				// Fallback: parse without verification (not recommended for production).
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$event = json_decode( $payload, false );
				if ( ! $event || ! isset( $event->type ) ) {
					status_header( 400 );
					exit;
				}
			}

			// Handle different event types.
			switch ( $event->type ) {
				case 'payment_intent.succeeded':
					$this->handle_payment_intent_succeeded( $event->data->object );
					break;

				case 'payment_intent.payment_failed':
					$this->handle_payment_intent_failed( $event->data->object );
					break;

				case 'charge.refunded':
					$this->handle_payment_charge_refunded( $event->data->object );
					break;

				case 'payment_intent.canceled':
					$this->handle_payment_intent_canceled( $event->data->object );
					break;
			}

			status_header( 200 );
			echo 'OK';

		} catch ( \UnexpectedValueException $e ) {
			// Invalid payload.
			$this->log_error( 'webhook_error', 'Invalid webhook payload: ' . $e->getMessage(), 0 );
			status_header( 400 );
		} catch ( \Stripe\Exception\SignatureVerificationException $e ) {
			// Invalid signature.
			$this->log_error( 'webhook_error', 'Invalid webhook signature: ' . $e->getMessage(), 0 );
			status_header( 400 );
		} catch ( \Exception $e ) {
			$this->log_error( 'webhook_error', $e->getMessage(), 0 );
			status_header( 500 );
		}

		exit;
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

			// Retrieve and verify payment intent status.
			try {
				if ( ! $this->stripe ) {
					throw new \Exception( __( 'Stripe is not configured', 'giftflow' ) );
				}

				$payment_intent = $this->stripe->paymentIntents->retrieve( $payment_intent_id );

				if ( 'succeeded' === $payment_intent->status ) {
					$this->handle_successful_payment_intent( $payment_intent, $donation_id );
					wp_safe_redirect( esc_url( add_query_arg( 'payment_status', 'success', home_url() ) ) );
				} elseif ( in_array( $payment_intent->status, array( 'processing', 'requires_capture' ), true ) ) {
					// Payment is processing.
					update_post_meta( $donation_id, '_payment_status', 'processing' );
					wp_safe_redirect( esc_url( add_query_arg( 'payment_status', 'processing', home_url() ) ) );
				} else {
					$this->handle_failed_payment_intent( $payment_intent, $donation_id );
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
	 * Handle successful payment intent webhook
	 *
	 * @param object $payment_intent Payment intent object from webhook.
	 */
	private function handle_payment_intent_succeeded( $payment_intent ) {
		$donation_id = isset( $payment_intent->metadata->donation_id )
			? intval( $payment_intent->metadata->donation_id )
			: 0;

		if ( $donation_id ) {
			$transaction_id = $payment_intent->id;
			$charge_id = ! empty( $payment_intent->charges->data ) ? $payment_intent->charges->data[0]->id : '';

			// Update donation meta.
			update_post_meta( $donation_id, '_transaction_id', $transaction_id );
			update_post_meta( $donation_id, '_stripe_charge_id', $charge_id );
			update_post_meta( $donation_id, '_payment_status', 'completed' );

			// Use centralized Donations class to update status.
			$donations_class = new Donations();
			$donations_class->update_status( $donation_id, 'completed' );

			Donation_Event_History::add(
				$donation_id,
				'payment_succeeded',
				'completed',
				__( 'Webhook: payment_intent.succeeded', 'giftflow' ),
				array(
					'transaction_id' => $transaction_id,
					'charge_id' => $charge_id,
					'gateway' => 'stripe',
					'source' => 'webhook',
				)
			);
			Giftflow_Logger::info(
				'stripe.webhook.payment_intent.succeeded',
				array(
					'donation_id'    => $donation_id,
					'transaction_id' => $transaction_id,
					'charge_id'     => $charge_id,
					'gateway'       => 'stripe',
				),
				'stripe'
			);

			do_action( 'giftflow_stripe_webhook_payment_completed', $donation_id, $payment_intent );
		}
	}

	/**
	 * Handle failed payment intent webhook
	 *
	 * @param object $payment_intent Payment intent object from webhook.
	 */
	private function handle_payment_intent_failed( $payment_intent ) {
		$donation_id = isset( $payment_intent->metadata->donation_id )
			? intval( $payment_intent->metadata->donation_id )
			: 0;

		if ( $donation_id ) {
			$error_message = isset( $payment_intent->last_payment_error->message )
				? $payment_intent->last_payment_error->message
				: __( 'Payment failed', 'giftflow' );

			Donation_Event_History::add(
				$donation_id,
				'payment_failed',
				'failed',
				$error_message,
				array(
					'gateway' => 'stripe',
					'source' => 'webhook',
				)
			);
			Giftflow_Logger::error(
				'stripe.webhook.payment_intent.failed',
				array(
					'donation_id'    => $donation_id,
					'error_message' => $error_message,
					'gateway'       => 'stripe',
				),
				'stripe'
			);

			// Use centralized Donations class to update status.
			$donations_class = new Donations();
			$donations_class->update_status( $donation_id, 'failed' );
			update_post_meta( $donation_id, '_payment_error', $error_message );
			update_post_meta( $donation_id, '_payment_status', 'failed' );

			do_action( 'giftflow_stripe_webhook_payment_failed', $donation_id, $payment_intent );
		}
	}

	/**
	 * Handle canceled payment intent webhook
	 *
	 * @param object $payment_intent Payment intent object from webhook.
	 */
	private function handle_payment_intent_canceled( $payment_intent ) {
		$donation_id = isset( $payment_intent->metadata->donation_id )
			? intval( $payment_intent->metadata->donation_id )
			: 0;

		if ( $donation_id ) {
			Donation_Event_History::add(
				$donation_id,
				'payment_canceled',
				'cancelled',
				__( 'Webhook: payment_intent.canceled', 'giftflow' ),
				array(
					'gateway' => 'stripe',
					'source' => 'webhook',
				)
			);
			Giftflow_Logger::info(
				'stripe.webhook.payment_intent.canceled',
				array(
					'donation_id' => $donation_id,
					'gateway'     => 'stripe',
				),
				'stripe'
			);

			// Use centralized Donations class to update status.
			$donations_class = new Donations();
			$donations_class->update_status( $donation_id, 'cancelled' );
			update_post_meta( $donation_id, '_payment_status', 'canceled' );

			do_action( 'giftflow_stripe_webhook_payment_canceled', $donation_id, $payment_intent );
		}
	}

	/**
	 * Handle payment charge refunded
	 *
	 * @param object $charge Charge object from webhook.
	 */
	private function handle_payment_charge_refunded( $charge ) {
		$donation_id = isset( $charge->metadata->donation_id )
			? intval( $charge->metadata->donation_id )
			: 0;

		if ( $donation_id ) {
			$charge_id = isset( $charge->id ) ? $charge->id : '';
			Donation_Event_History::add(
				$donation_id,
				'payment_refunded',
				'refunded',
				__( 'Webhook: charge.refunded', 'giftflow' ),
				array(
					'charge_id' => $charge_id,
					'gateway' => 'stripe',
					'source' => 'webhook',
				)
			);
			Giftflow_Logger::info(
				'stripe.webhook.charge.refunded',
				array(
					'donation_id' => $donation_id,
					'charge_id'   => $charge_id,
					'gateway'     => 'stripe',
				),
				'stripe'
			);

			// Use centralized Donations class to update status.
			$donations_class = new Donations();
			$donations_class->update_status( $donation_id, 'refunded' );
			update_post_meta( $donation_id, '_payment_status', 'refunded' );

			do_action( 'giftflow_stripe_webhook_charge_refunded', $donation_id, $charge );
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
	 * @return string
	 */
	public function get_webhook_url() {
		return admin_url( 'admin-ajax.php?action=giftflow_stripe_webhook' );
	}

	/**
	 * Log successful payment
	 *
	 * @param string $transaction_id Transaction ID.
	 * @param int $donation_id Donation ID.
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
	 * @param int $donation_id Donation ID.
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
