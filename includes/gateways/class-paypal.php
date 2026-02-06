<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * PayPal Payment Gateway for GiftFlow
 *
 * This class implements PayPal payment processing using PayPal JS SDK v6
 * with support for PayPal Smart Buttons and webhooks.
 *
 * @package GiftFlow
 * @subpackage Gateways
 * @since 1.0.0
 * @version 1.0.0
 */

namespace GiftFlow\Gateways;

use GiftFlow\Core\Donations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PayPal Gateway Class
 */
class PayPal_Gateway extends Gateway_Base {
	/**
	 * Client ID.
	 *
	 * @var string
	 */
	private $client_id;

	/**
	 * Client Secret.
	 *
	 * @var string
	 */
	private $client_secret;

	/**
	 * Initialize gateway properties
	 */
	protected function init_gateway() {
		$this->id = 'paypal';
		$this->title = esc_html__( 'PayPal', 'giftflow' );
		$this->description = esc_html__( 'Pay securely with PayPal', 'giftflow' );

		// SVG icon.
		$this->icon = giftflow_svg_icon( 'paypal' );

		$this->order = 15;
		$this->supports = array(
			'webhooks',
			'refunds',
		);
	}

	/**
	 * Ready function
	 *
	 * @return void
	 */
	protected function ready() {
		// Initialize PayPal credentials.
		$this->client_id = $this->get_client_id();
		$this->client_secret = $this->get_client_secret();

		// Add PayPal-specific assets.
		$this->add_paypal_assets();
	}

	/**
	 * Get Client ID based on mode
	 *
	 * @return string
	 */
	private function get_client_id() {
		$mode = $this->get_setting( 'paypal_mode', 'sandbox' );

		if ( 'live' === $mode ) {
			return $this->get_setting( 'paypal_live_client_id' );
		}

		return $this->get_setting( 'paypal_sandbox_client_id' );
	}

	/**
	 * Get Client Secret based on mode
	 *
	 * @return string
	 */
	private function get_client_secret() {
		$mode = $this->get_setting( 'paypal_mode', 'sandbox' );

		if ( 'live' === $mode ) {
			return $this->get_setting( 'paypal_live_client_secret' );
		}

		return $this->get_setting( 'paypal_sandbox_client_secret' );
	}

	/**
	 * Add PayPal-specific assets
	 *
	 * @return void
	 */
	private function add_paypal_assets() {
		// Add PayPal JS SDK v6 script dynamically.
		$this->add_paypal_sdk_script();

		// Custom PayPal donation script.
		$this->add_script(
			'giftflow-paypal-donation',
			array(
				'src' => GIFTFLOW_PLUGIN_URL . 'assets/js/paypal-donation.bundle.js',
				'deps' => array( 'jquery', 'giftflow-donation-forms' ),
				'version' => GIFTFLOW_VERSION,
				'frontend' => true,
				'admin' => false,
				'in_footer' => true,
				'localize' => array(
					'name' => 'giftflowPayPalDonation',
					'data' => $this->get_script_data(),
				),
			)
		);

		$css_inline = '
			#giftflow-paypal-button-container {
				width: 350px;
				max-width: 100%;
				margin: 0 auto;
			}

			@media(max-width: 512px) {
				#giftflow-paypal-button-container {
					width: 100%;
				}
			}
		';

		// Custom inline styles for PayPal buttons container.
		$this->add_style_inline(
			'giftflow-donation-form',
			$css_inline
		);
	}

	/**
	 * Add PayPal JS SDK v6 script
	 *
	 * @return void
	 */
	private function add_paypal_sdk_script() {
		$client_id = $this->get_client_id();
		$mode = $this->get_setting( 'paypal_mode', 'sandbox' );

		if ( empty( $client_id ) ) {
			return;
		}

		// PayPal JS SDK v6 URL.
		$sdk_url = 'sandbox' === $mode
			? 'https://www.sandbox.paypal.com/sdk/js'
			: 'https://www.paypal.com/sdk/js';

		// Build query parameters.
		$params = array(
			'client-id' => $client_id,
			'currency' => strtoupper( $this->get_currency() ),
			'intent' => 'capture',
			'components' => 'buttons,marks',
		);

		$sdk_url = add_query_arg( $params, $sdk_url );

		// Register and enqueue PayPal SDK.
		add_action(
			'wp_enqueue_scripts',
			function () use ( $sdk_url ) {
				if ( $this->enabled ) {
					// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
					wp_enqueue_script( 'paypal-js-sdk', $sdk_url, array(), null, false );
				}
			},
			5
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
			'client_id' => $this->get_client_id(),
			'mode' => $this->get_setting( 'paypal_mode', 'sandbox' ),
			'currency' => $this->get_currency(),
			'nonce' => wp_create_nonce( 'giftflow_paypal_nonce' ),
			'messages' => array(
				'processing' => __( 'Processing payment...', 'giftflow' ),
				'error' => __( 'Payment failed. Please try again.', 'giftflow' ),
				'canceled' => __( 'Payment was canceled.', 'giftflow' ),
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
		$payment_fields['paypal'] = array(
			'id' => 'giftflow_paypal',
			'name' => 'giftflow_payment_options[paypal]',
			'type' => 'accordion',
			'label' => __( 'PayPal', 'giftflow' ),
			'description' => __( 'Configure PayPal payment settings', 'giftflow' ),
			'accordion_settings' => array(
				'label' => __( 'PayPal Settings', 'giftflow' ),
				'is_open' => true,
				'fields' => array(
					'paypal_enabled' => array(
						'id' => 'giftflow_paypal_enabled',
						'type' => 'switch',
						'label' => __( 'Enable PayPal', 'giftflow' ),
						'value' => isset( $payment_options['paypal']['paypal_enabled'] ) ? $payment_options['paypal']['paypal_enabled'] : false,
						'description' => __( 'Enable PayPal as a payment method', 'giftflow' ),
					),
					'paypal_mode' => array(
						'id' => 'giftflow_paypal_mode',
						'type' => 'select',
						'label' => __( 'PayPal Mode', 'giftflow' ),
						'value' => isset( $payment_options['paypal']['paypal_mode'] ) ? $payment_options['paypal']['paypal_mode'] : 'sandbox',
						'options' => array(
							'sandbox' => __( 'Sandbox (Test Mode)', 'giftflow' ),
							'live' => __( 'Live (Production Mode)', 'giftflow' ),
						),
						'description' => __( 'Select PayPal environment mode', 'giftflow' ),
					),
					'paypal_sandbox_client_id' => array(
						'id' => 'giftflow_paypal_sandbox_client_id',
						'type' => 'textfield',
						'label' => __( 'PayPal Sandbox Client ID', 'giftflow' ),
						'value' => isset( $payment_options['paypal']['paypal_sandbox_client_id'] ) ? $payment_options['paypal']['paypal_sandbox_client_id'] : '',
						'description' => __( 'Enter your PayPal sandbox client ID', 'giftflow' ),
					),
					'paypal_sandbox_client_secret' => array(
						'id' => 'giftflow_paypal_sandbox_client_secret',
						'type' => 'textfield',
						'label' => __( 'PayPal Sandbox Client Secret', 'giftflow' ),
						'value' => isset( $payment_options['paypal']['paypal_sandbox_client_secret'] ) ? $payment_options['paypal']['paypal_sandbox_client_secret'] : '',
						'input_type' => 'password',
						'description' => __( 'Enter your PayPal sandbox client secret', 'giftflow' ),
					),
					'paypal_live_client_id' => array(
						'id' => 'giftflow_paypal_live_client_id',
						'type' => 'textfield',
						'label' => __( 'PayPal Live Client ID', 'giftflow' ),
						'value' => isset( $payment_options['paypal']['paypal_live_client_id'] ) ? $payment_options['paypal']['paypal_live_client_id'] : '',
						'description' => __( 'Enter your PayPal live client ID', 'giftflow' ),
					),
					'paypal_live_client_secret' => array(
						'id' => 'giftflow_paypal_live_client_secret',
						'type' => 'textfield',
						'label' => __( 'PayPal Live Client Secret', 'giftflow' ),
						'value' => isset( $payment_options['paypal']['paypal_live_client_secret'] ) ? $payment_options['paypal']['paypal_live_client_secret'] : '',
						'input_type' => 'password',
						'description' => __( 'Enter your PayPal live client secret', 'giftflow' ),
					),
					'paypal_webhook_enabled' => array(
						'id' => 'giftflow_paypal_webhook_enabled',
						'type' => 'switch',
						'label' => __( 'Enable Webhook', 'giftflow' ),
						'value' => isset( $payment_options['paypal']['paypal_webhook_enabled'] ) ? $payment_options['paypal']['paypal_webhook_enabled'] : false,
						'description' =>
							esc_html__( 'Enable webhooks for payment status updates.', 'giftflow' ) . '<br>' .
							esc_html__( 'Webhook URL:', 'giftflow' ) . ' <code>' . admin_url( 'admin-ajax.php?action=giftflow_paypal_webhook' ) . '</code><br>' .
							__(
								'Recommended PayPal events: <strong>Checkout order approved</strong>, <strong>Checkout order completed</strong>, <strong>Payment capture completed</strong>, <strong>Payment capture denied</strong>, <strong>Payment capture refunded</strong>.',
								'giftflow'
							),
					),
					'paypal_webhook_id' => array(
						'id' => 'giftflow_paypal_webhook_id',
						'type' => 'textfield',
						'label' => __( 'Webhook ID', 'giftflow' ),
						'value' => isset( $payment_options['paypal']['paypal_webhook_id'] ) ? $payment_options['paypal']['paypal_webhook_id'] : '',
						'description' => __( 'Enter the Webhook ID from your PayPal Developer Dashboard. Required for webhook signature verification.', 'giftflow' ),
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
			'payment-gateway/paypal.php',
			array(
				'id' => $this->id,
				'title' => $this->title,
				'icon' => $this->icon,
				'mode' => $this->get_setting( 'paypal_mode' ),
			)
		);
	}

	/**
	 * Additional hooks for PayPal gateway
	 */
	protected function init_additional_hooks() {
		// AJAX handlers for PayPal JS SDK v6.
		add_action( 'wp_ajax_giftflow_paypal_create_order', array( $this, 'ajax_create_order' ) );
		add_action( 'wp_ajax_nopriv_giftflow_paypal_create_order', array( $this, 'ajax_create_order' ) );

		add_action( 'wp_ajax_giftflow_paypal_capture_order', array( $this, 'ajax_capture_order' ) );
		add_action( 'wp_ajax_nopriv_giftflow_paypal_capture_order', array( $this, 'ajax_capture_order' ) );

		// Legacy AJAX handlers (for backward compatibility).
		add_action( 'wp_ajax_giftflow_process_paypal_payment', array( $this, 'ajax_process_payment' ) );
		add_action( 'wp_ajax_nopriv_giftflow_process_paypal_payment', array( $this, 'ajax_process_payment' ) );

		// Webhook handler.
		add_action( 'wp_ajax_giftflow_paypal_webhook', array( $this, 'handle_webhook' ) );
		add_action( 'wp_ajax_nopriv_giftflow_paypal_webhook', array( $this, 'handle_webhook' ) );
	}

	/**
	 * Process payment
	 *
	 * @param array $data Payment data.
	 * @param int   $donation_id Donation ID.
	 * @return mixed
	 */
	public function process_payment( $data, $donation_id = 0 ) {
	}

	/**
	 * Prepare payment data (legacy method - not used with JS SDK v6)
	 *
	 * @param array $data Payment data.
	 * @param int   $donation_id Donation ID.
	 * @return array
	 */
	private function prepare_payment_data( $data, $donation_id ) {
		$return_url = add_query_arg(
			array(
				'giftflow_paypal_return' => '1',
				'donation_id' => $donation_id,
			),
			home_url()
		);

		$cancel_url = add_query_arg(
			array(
				'giftflow_paypal_cancel' => '1',
				'donation_id' => $donation_id,
			),
			home_url()
		);

		$paypal_data = array(
			'amount' => number_format( (float) $data['donation_amount'], 2, '.', '' ),
			'currency' => strtolower( $this->get_currency() ),
			'description' => sprintf(
				// translators: 1: donor name, 2: campaign id or name.
				__( 'Donation from %1$s for campaign %2$s', 'giftflow' ),
				sanitize_text_field( $data['donor_name'] ),
				$data['campaign_id']
			),
			'returnUrl' => $return_url,
			'cancelUrl' => $cancel_url,
			'notifyUrl' => $this->get_webhook_url(),
		);

		// Add metadata.
		$paypal_data['metadata'] = array(
			'donation_id' => $donation_id,
			'campaign_id' => $data['campaign_id'],
			'donor_email' => sanitize_email( $data['donor_email'] ),
			'donor_name' => sanitize_text_field( $data['donor_name'] ),
			'site_url' => home_url(),
		);

		return apply_filters( 'giftflow_paypal_prepare_payment_data', $paypal_data, $data, $donation_id );
	}

	/**
	 * Handle payment response
	 *
	 * @param mixed $response Response from PayPal.
	 * @param int   $donation_id Donation ID.
	 * @return array|\WP_Error
	 */
	private function handle_payment_response( $response, $donation_id ) {
		if ( $response->isSuccessful() ) {
			return $this->handle_successful_payment( $response, $donation_id );
		} elseif ( $response->isRedirect() ) {
			return $this->handle_redirect_payment( $response, $donation_id );
		} else {
			return $this->handle_failed_payment( $response, $donation_id );
		}
	}

	/**
	 * Handle successful payment
	 *
	 * @param mixed $response Response from PayPal.
	 * @param int   $donation_id Donation ID.
	 * @return array
	 */
	private function handle_successful_payment( $response, $donation_id ) {
		$transaction_id = $response->getTransactionReference();
		$all_data = $response->getData();

		// Update donation meta.
		update_post_meta( $donation_id, '_transaction_id', $transaction_id );
		update_post_meta( $donation_id, '_transaction_raw_data', wp_json_encode( $all_data ) );
		update_post_meta( $donation_id, '_payment_method', 'paypal' );

		// Use centralized Donations class to update status.
		$donations_class = new Donations();
		$donations_class->update_status( $donation_id, 'completed' );

		$this->log_success( $transaction_id, $donation_id );

		do_action( 'giftflow_paypal_payment_completed', $donation_id, $transaction_id, $all_data );

		return true;
	}

	/**
	 * Handle redirect payment (PayPal Express Checkout)
	 *
	 * @param mixed $response Response from PayPal.
	 * @param int   $donation_id Donation ID.
	 * @return array
	 */
	private function handle_redirect_payment( $response, $donation_id ) {
		$transaction_id = $response->getTransactionReference();

		// Store transaction reference for later verification.
		update_post_meta( $donation_id, '_paypal_transaction_id', $transaction_id );
		update_post_meta( $donation_id, '_payment_status', 'processing' );
		update_post_meta( $donation_id, '_payment_method', 'paypal' );

		return array(
			'success' => false,
			'redirect' => true,
			'redirect_url' => $response->getRedirectUrl(),
			'message' => __( 'Redirecting to PayPal...', 'giftflow' ),
		);
	}

	/**
	 * Handle failed payment
	 *
	 * @param mixed $response Response from PayPal.
	 * @param int   $donation_id Donation ID.
	 * @return \WP_Error
	 */
	private function handle_failed_payment( $response, $donation_id ) {
		$error_message = $response->getMessage() ? $response->getMessage() : esc_html__( 'Payment failed', 'giftflow' );
		$error_code = method_exists( $response, 'getCode' ) ? $response->getCode() : '';

		$this->log_error( 'payment_failed', $error_message, $donation_id, $error_code );

		update_post_meta( $donation_id, '_payment_status', 'failed' );
		update_post_meta( $donation_id, '_payment_error', $error_message );

		return new \WP_Error( 'paypal_error', $error_message );
	}

	/**
	 * AJAX handler for creating PayPal order (PayPal JS SDK v6)
	 */
	public function ajax_create_order() {
		check_ajax_referer( 'giftflow_paypal_nonce', 'nonce' );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$data = $_POST;

		/**
		 * Hooks do_action before process donation.
		 *
		 * @see giftflow_donation_form_validate_recaptcha - 10
		 */
		do_action( 'giftflow_donation_form_before_process_donation', $data );

		// Get donation amount - required.
		$amount = isset( $data['amount'] ) ? floatval( $data['amount'] ) : 0;
		if ( ! $amount || $amount <= 0 ) {
			wp_send_json_error(
				array(
					'message' => __( 'Donation amount is required', 'giftflow' ),
				)
			);
		}

		// Validate required donation data.
		$donation_data = array(
			'donation_amount' => $amount,
			'donor_name' => isset( $data['donor_name'] ) ? sanitize_text_field( $data['donor_name'] ) : '',
			'donor_email' => isset( $data['donor_email'] ) ? sanitize_email( $data['donor_email'] ) : '',
			'campaign_id' => isset( $data['campaign_id'] ) ? sanitize_text_field( $data['campaign_id'] ) : '',
			'payment_method' => 'paypal',
			'donation_type' => isset( $data['donation_type'] ) ? sanitize_text_field( $data['donation_type'] ) : '',
			'recurring_interval' => isset( $data['recurring_interval'] ) ? sanitize_text_field( $data['recurring_interval'] ) : '',
			'donor_message' => isset( $data['donor_message'] ) ? sanitize_textarea_field( $data['donor_message'] ) : '',
			'anonymous_donation' => isset( $data['anonymous_donation'] ) ? sanitize_text_field( $data['anonymous_donation'] ) : '',
		);

		// Validate required fields.
		if ( empty( $donation_data['donor_name'] ) || empty( $donation_data['donor_email'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Donor name and email are required', 'giftflow' ),
				)
			);
		}

		try {
			$order_id = $this->create_paypal_order( $donation_data );
			wp_send_json_success(
				array(
					'orderID' => $order_id,
				)
			);
		} catch ( \Exception $e ) {
			$this->log_error( 'create_order_exception', $e->getMessage(), 0 );
			wp_send_json_error(
				array(
					'message' => $e->getMessage(),
				)
			);
		}
	}

	/**
	 * AJAX handler for capturing PayPal order (PayPal JS SDK v6)
	 */
	public function ajax_capture_order() {
		check_ajax_referer( 'giftflow_paypal_nonce', 'nonce' );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$data = $_POST;
		$order_id = isset( $data['orderID'] ) ? sanitize_text_field( $data['orderID'] ) : '';

		if ( empty( $order_id ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Order ID is required', 'giftflow' ),
				)
			);
		}

		try {
			$result = $this->capture_paypal_order( $order_id );

			if ( is_wp_error( $result ) ) {
				wp_send_json_error(
					array(
						'message' => $result->get_error_message(),
					)
				);
			} else {
				wp_send_json_success(
					array(
						'message' => __( 'Payment completed successfully', 'giftflow' ),
						'data' => $result,
						'donation_id' => $result['donation_id'],
					)
				);
			}
		} catch ( \Exception $e ) {
			$this->log_error( 'capture_order_exception', $e->getMessage(), 0 );
			wp_send_json_error(
				array(
					'message' => $e->getMessage(),
				)
			);
		}
	}

	/**
	 * Create PayPal order using PayPal Orders API v2
	 *
	 * @param array $donation_data Donation data (without donation_id).
	 * @return string PayPal order ID.
	 * @throws \Exception If order creation fails.
	 */
	private function create_paypal_order( $donation_data ) {
		if ( empty( $this->client_id ) || empty( $this->client_secret ) ) {
			throw new \Exception( esc_html__( 'PayPal is not properly configured', 'giftflow' ) );
		}

		$mode = $this->get_setting( 'paypal_mode', 'sandbox' );
		$base_url = 'sandbox' === $mode
			? 'https://api.sandbox.paypal.com'
			: 'https://api.paypal.com';

		// Get access token.
		$access_token = $this->get_paypal_access_token( $base_url );

		if ( ! $access_token ) {
			throw new \Exception( esc_html__( 'Failed to get PayPal access token', 'giftflow' ) );
		}

		$amount = floatval( $donation_data['donation_amount'] );

		// Prepare order data for Orders API v2.
		$order_data = array(
			'intent' => 'CAPTURE',
			'purchase_units' => array(
				array(
					'amount' => array(
						'currency_code' => strtoupper( $this->get_currency() ),
						'value' => number_format( $amount, 2, '.', '' ),
					),
					'description' => sprintf(
						// translators: 1: donor name, 2: campaign id or name.
						__( 'Donation from %1$s for campaign %2$s', 'giftflow' ),
						$donation_data['donor_name'],
						$donation_data['campaign_id']
					),
				),
			),
		);

		// Add application context.
		$order_data['application_context'] = array(
			'brand_name' => get_bloginfo( 'name' ),
			'landing_page' => 'NO_PREFERENCE',
			'user_action' => 'PAY_NOW',
			'return_url' => home_url(),
			'cancel_url' => home_url(),
		);

		// Make API request.
		$response = wp_remote_post(
			$base_url . '/v2/checkout/orders',
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
					'Authorization' => 'Bearer ' . $access_token,
					'PayPal-Request-Id' => wp_generate_uuid4(),
				),
				'body' => wp_json_encode( $order_data ),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
      // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw new \Exception( $response->get_error_message() );
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 201 !== $response_code || ! isset( $response_body['id'] ) ) {
			$error_message = isset( $response_body['message'] ) ? $response_body['message'] : esc_html__( 'Failed to create PayPal order', 'giftflow' );
      // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw new \Exception( $error_message );
		}

		$order_id = $response_body['id'];

		// Store donation data temporarily using transient (expires in 1 hour).
		// Key format: giftflow_paypal_order_{order_id}.
		$transient_key = 'giftflow_paypal_order_' . $order_id;
		set_transient( $transient_key, $donation_data, HOUR_IN_SECONDS );

		return $order_id;
	}

	/**
	 * Get PayPal access token
	 *
	 * @param string $base_url PayPal API base URL.
	 * @return string|false Access token or false on failure.
	 */
	private function get_paypal_access_token( $base_url ) {
		// Create a unique cache key based on mode and client_id.
		$mode = $this->get_setting( 'paypal_mode', 'sandbox' );
		$cache_key = 'giftflow_paypal_token_' . $mode . '_' . md5( $this->client_id );

		// Try to get cached token.
		$cached_token_data = get_transient( $cache_key );

		if ( false !== $cached_token_data && is_array( $cached_token_data ) ) {
			$token = $cached_token_data['access_token'] ?? '';
			$expires_at = $cached_token_data['expires_at'] ?? 0;

			// Check if token is still valid (with 60 second buffer to account for clock differences).
			if ( ! empty( $token ) && $expires_at > ( time() + 60 ) ) {
				return $token;
			}
		}

		// Token expired or doesn't exist, fetch a new one.
		$response = wp_remote_post(
			trailingslashit( $base_url ) . 'v1/oauth2/token',
			array(
				'headers' => array(
					'Accept' => 'application/json',
					'Accept-Language' => 'en_US',
          // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode, WordPress.Arrays.ArrayIndentation.MultiLineArrayItemNotAligned
					'Authorization' => 'Basic ' . base64_encode(
						$this->client_id . ':' . $this->client_secret
					),
				),
				'body' => array(
					'grant_type' => 'client_credentials',
				),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $code ) {
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		$access_token = $body['access_token'] ?? false;

		if ( ! $access_token ) {
			return false;
		}

		// Get expires_in value (usually in seconds, default to 32400 / 9 hours if not provided).
		$expires_in = isset( $body['expires_in'] ) ? intval( $body['expires_in'] ) : 32400;

		// Calculate expiration timestamp.
		$expires_at = time() + $expires_in;

		// Cache the token data.
		$token_data = array(
			'access_token' => $access_token,
			'expires_at' => $expires_at,
			'expires_in' => $expires_in,
		);

		// Store in transient with expiration time (add 60 seconds buffer).
		set_transient( $cache_key, $token_data, $expires_in - 60 );

		return $access_token;
	}

	/**
	 * Capture PayPal order using PayPal Orders API v2
	 *
	 * @param string $order_id PayPal order ID.
	 * @return array|\WP_Error
	 */
	private function capture_paypal_order( $order_id ) {
		if ( empty( $this->client_id ) || empty( $this->client_secret ) ) {
			return new \WP_Error( 'paypal_error', esc_html__( 'PayPal is not properly configured', 'giftflow' ) );
		}

		// Retrieve temporary donation data.
		$transient_key = 'giftflow_paypal_order_' . $order_id;
		$donation_data = get_transient( $transient_key );

		if ( false === $donation_data ) {
			return new \WP_Error( 'paypal_error', esc_html__( 'Donation data not found. Please try again.', 'giftflow' ) );
		}

		$mode = $this->get_setting( 'paypal_mode', 'sandbox' );
		$base_url = 'sandbox' === $mode
			? 'https://api.sandbox.paypal.com'
			: 'https://api.paypal.com';

		// Get access token.
		$access_token = $this->get_paypal_access_token( $base_url );

		if ( ! $access_token ) {
			return new \WP_Error( 'paypal_error', esc_html__( 'Failed to get PayPal access token', 'giftflow' ) );
		}

		// Capture the order.
		$response = wp_remote_post(
			$base_url . '/v2/checkout/orders/' . $order_id . '/capture',
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
					'Authorization' => 'Bearer ' . $access_token,
					'PayPal-Request-Id' => wp_generate_uuid4(),
				),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			$this->log_error( 'capture_exception', $response->get_error_message(), 0 );
			return new \WP_Error( 'paypal_error', $response->get_error_message() );
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 201 !== $response_code ) {
			$error_message = isset( $response_body['message'] ) ? $response_body['message'] : esc_html__( 'Failed to capture PayPal order', 'giftflow' );
			$this->log_error( 'capture_failed', $error_message, 0 );
			// Clean up transient on failure.
			delete_transient( $transient_key );
			return new \WP_Error( 'paypal_error', $error_message );
		}

		// Payment successful - now create the donation.
		$donation_id = $this->create_donation_record( $donation_data );

		if ( is_wp_error( $donation_id ) ) {
			$this->log_error( 'donation_creation_failed', $donation_id->get_error_message(), 0 );
			delete_transient( $transient_key );
			return new \WP_Error( 'paypal_error', esc_html__( 'Payment successful but failed to create donation record', 'giftflow' ) );
		}

		// Handle successful capture.
		$transaction_id = '';
		if ( isset( $response_body['purchase_units'][0]['payments']['captures'][0]['id'] ) ) {
			$transaction_id = $response_body['purchase_units'][0]['payments']['captures'][0]['id'];
		}

		// Update donation meta with payment information.
		update_post_meta( $donation_id, '_transaction_id', $transaction_id );
		update_post_meta( $donation_id, '_transaction_raw_data', wp_json_encode( $response_body ) );
		update_post_meta( $donation_id, '_paypal_order_id', $order_id );
		update_post_meta( $donation_id, '_payment_method', 'paypal' );

		// Use centralized Donations class to update status.
		$donations_class = new Donations();
		$donations_class->update_status( $donation_id, 'completed' );

		// Clean up transient.
		delete_transient( $transient_key );

		$this->log_success( $transaction_id, $donation_id );

		do_action( 'giftflow_paypal_payment_completed', $donation_id, $transaction_id, $response_body );

		/**
		 * Add hook after payment processed

		 * @see giftflow_send_mail_notification_donation_to_admin - 10
		 * @see giftflow_auto_create_user_on_donation - 10
		 */
		do_action( 'giftflow_donation_after_payment_processed', $donation_id, true );

		return array(
			'success' => true,
			'transaction_id' => $transaction_id,
			'donation_id' => $donation_id,
		);
	}

	/**
	 * Create donation record using centralized Donations class
	 *
	 * @param array $donation_data Donation data.
	 * @return int|\WP_Error Donation ID or error.
	 */
	private function create_donation_record( $donation_data ) {
		// Prepare donation data for Donations class.
		$data = array(
			'donation_amount' => isset( $donation_data['donation_amount'] ) ? floatval( $donation_data['donation_amount'] ) : 0,
			'donor_name' => isset( $donation_data['donor_name'] ) ? sanitize_text_field( $donation_data['donor_name'] ) : '',
			'donor_email' => isset( $donation_data['donor_email'] ) ? sanitize_email( $donation_data['donor_email'] ) : '',
			'payment_method' => isset( $donation_data['payment_method'] ) ? sanitize_text_field( $donation_data['payment_method'] ) : 'paypal',
			'status' => 'pending', // Initial status, will be updated to completed after payment capture.
		);

		// Optional fields.
		if ( ! empty( $donation_data['campaign_id'] ) ) {
			$data['campaign_id'] = sanitize_text_field( $donation_data['campaign_id'] );
		}

		if ( ! empty( $donation_data['donation_type'] ) ) {
			$data['donation_type'] = sanitize_text_field( $donation_data['donation_type'] );
		}

		if ( ! empty( $donation_data['recurring_interval'] ) ) {
			$data['recurring_interval'] = sanitize_text_field( $donation_data['recurring_interval'] );
		}

		if ( ! empty( $donation_data['donor_message'] ) ) {
			$data['donor_message'] = sanitize_textarea_field( $donation_data['donor_message'] );
		}

		if ( isset( $donation_data['anonymous_donation'] ) ) {
			$data['anonymous_donation'] = ( 'yes' === $donation_data['anonymous_donation'] || true === $donation_data['anonymous_donation'] || '1' === $donation_data['anonymous_donation'] ) ? 'yes' : 'no';
		}

		// Use centralized Donations class to create donation.
		$donations = new Donations();
		$donation_id = $donations->create( $data );

		return $donation_id;
	}

	/**
	 * AJAX handler for processing payments (legacy/backward compatibility)
	 */
	public function ajax_process_payment() {
		check_ajax_referer( 'giftflow_donation_nonce', 'nonce' );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
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
		if ( ! $this->get_setting( 'paypal_webhook_enabled', '1' ) ) {
			status_header( 200 );
			exit;
		}

		// Reviewer Note: PayPal webhooks deliver payloads as raw JSON, and the signature verification process requires access to the unmodified request body.
		// For this reason, input sanitization is deliberately omitted at this point.
		// If there are concerns about this approach or required adjustments for compliance, please advise.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$payload = file_get_contents( 'php://input' );
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$event = json_decode( $payload, true );

		if ( ! $event || ! isset( $event['event_type'] ) ) {
			status_header( 400 );
			exit;
		}

		// Verify webhook signature.
		$webhook_id = $this->get_setting( 'paypal_webhook_id', '' );

		if ( ! empty( $webhook_id ) ) {
			$is_valid = $this->verify_webhook_signature( $payload, $webhook_id );

			if ( ! $is_valid ) {
				$this->log_error( 'webhook_verification_failed', 'Webhook signature verification failed', 0 );
				status_header( 401 );
				echo 'Unauthorized';
				exit;
			}
		} else {
			// Log warning if webhook ID is not configured.
			$this->log_error( 'webhook_id_missing', 'Webhook ID not configured - signature verification skipped', 0 );
		}

		try {
			switch ( $event['event_type'] ) {
				// Payment completed events.
				case 'PAYMENT.SALE.COMPLETED':
				case 'PAYMENT.CAPTURE.COMPLETED':
					$this->handle_payment_completed( $event['resource'] );
					break;

				// Payment denied/failed events.
				case 'PAYMENT.SALE.DENIED':
				case 'PAYMENT.CAPTURE.DENIED':
					$this->handle_payment_denied( $event['resource'] );
					break;

				// Payment refunded events.
				case 'PAYMENT.SALE.REFUNDED':
				case 'PAYMENT.CAPTURE.REFUNDED':
					$this->handle_payment_refunded( $event['resource'] );
					break;

				default:
					// Log unhandled event types for debugging.
					$this->log_error( 'webhook_unhandled_event', 'Unhandled webhook event type: ' . $event['event_type'], 0 );
					break;
			}

			status_header( 200 );
			echo 'OK';
		} catch ( \Exception $e ) {
			$this->log_error( 'webhook_error', $e->getMessage(), 0 );
			status_header( 500 );
		}

		exit;
	}

	/**
	 * Verify PayPal webhook signature using PayPal API
	 *
	 * @param string $payload Raw webhook payload.
	 * @param string $webhook_id PayPal Webhook ID.
	 * @return bool True if signature is valid, false otherwise.
	 */
	private function verify_webhook_signature( $payload, $webhook_id ) {
		$mode = $this->get_setting( 'paypal_mode', 'sandbox' );
		$base_url = 'sandbox' === $mode
			? 'https://api.sandbox.paypal.com'
			: 'https://api.paypal.com';

		// Get access token.
		$access_token = $this->get_paypal_access_token( $base_url );

		if ( ! $access_token ) {
			$this->log_error( 'webhook_verify_token_failed', 'Failed to get access token for webhook verification', 0 );
			return false;
		}

		// Get webhook headers.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$transmission_id = isset( $_SERVER['HTTP_PAYPAL_TRANSMISSION_ID'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_PAYPAL_TRANSMISSION_ID'] ) ) : '';
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$transmission_time = isset( $_SERVER['HTTP_PAYPAL_TRANSMISSION_TIME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_PAYPAL_TRANSMISSION_TIME'] ) ) : '';
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$cert_url = isset( $_SERVER['HTTP_PAYPAL_CERT_URL'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_PAYPAL_CERT_URL'] ) ) : '';
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$auth_algo = isset( $_SERVER['HTTP_PAYPAL_AUTH_ALGO'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_PAYPAL_AUTH_ALGO'] ) ) : '';
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$transmission_sig = isset( $_SERVER['HTTP_PAYPAL_TRANSMISSION_SIG'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_PAYPAL_TRANSMISSION_SIG'] ) ) : '';

		// Check if all required headers are present.
		if ( empty( $transmission_id ) || empty( $transmission_time ) || empty( $cert_url ) || empty( $auth_algo ) || empty( $transmission_sig ) ) {
			$this->log_error( 'webhook_missing_headers', 'Missing required PayPal webhook headers', 0 );
			return false;
		}

		// Validate cert_url domain (must be from PayPal).
		$cert_host = wp_parse_url( $cert_url, PHP_URL_HOST );
		if ( ! $cert_host || ! preg_match( '/\.paypal\.com$/i', $cert_host ) ) {
			$this->log_error( 'webhook_invalid_cert_url', 'Invalid PayPal certificate URL: ' . $cert_url, 0 );
			return false;
		}

		// Prepare verification request data.
		$verify_data = array(
			'auth_algo'         => $auth_algo,
			'cert_url'          => $cert_url,
			'transmission_id'   => $transmission_id,
			'transmission_sig'  => $transmission_sig,
			'transmission_time' => $transmission_time,
			'webhook_id'        => $webhook_id,
			'webhook_event'     => json_decode( $payload, true ),
		);

		// Make API request to verify signature.
		$response = wp_remote_post(
			$base_url . '/v1/notifications/verify-webhook-signature',
			array(
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $access_token,
				),
				'body'    => wp_json_encode( $verify_data ),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			$this->log_error( 'webhook_verify_request_failed', $response->get_error_message(), 0 );
			return false;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 !== $response_code ) {
			$error_message = isset( $response_body['message'] ) ? $response_body['message'] : 'Unknown error';
			$this->log_error( 'webhook_verify_api_error', $error_message, 0 );
			return false;
		}

		// Check verification status.
		$verification_status = isset( $response_body['verification_status'] ) ? $response_body['verification_status'] : '';

		if ( 'SUCCESS' === $verification_status ) {
			return true;
		}

		$this->log_error( 'webhook_verify_failed', 'Verification status: ' . $verification_status, 0 );
		return false;
	}

	/**
	 * Extract transaction ID from PayPal webhook resource
	 *
	 * Attempts to extract transaction ID from multiple locations:
	 * 1. Direct resource ID (for capture/sale events)
	 * 2. Links array with rel="up" pointing to captures (for refund events)
	 * 3. Links array with rel="self" (fallback)
	 *
	 * @param array  $_resource Resource data from webhook.
	 * @param string $link_pattern Pattern to match in link href (e.g., '/captures/', '/payments/sale/').
	 * @return string Transaction ID or empty string if not found.
	 */
	private function extract_transaction_id( $_resource, $link_pattern = '/captures/' ) {
		// First, try to get ID directly from resource (works for capture/sale completed/denied events).
		if ( isset( $_resource['id'] ) && ! empty( $_resource['id'] ) ) {
			return $_resource['id'];
		}

		// For refund events, extract from links with rel="up".
		if ( isset( $_resource['links'] ) && is_array( $_resource['links'] ) ) {
			foreach ( $_resource['links'] as $link ) {
				if ( isset( $link['rel'], $link['href'] ) && 'up' === $link['rel'] && false !== strpos( $link['href'], $link_pattern ) ) {
					return basename( $link['href'] );
				}
			}

			// Fallback: try self link.
			foreach ( $_resource['links'] as $link ) {
				if ( isset( $link['rel'], $link['href'] ) && 'self' === $link['rel'] ) {
					return basename( $link['href'] );
				}
			}
		}

		return '';
	}

	/**
	 * Find donation by transaction ID
	 *
	 * @param string $transaction_id Transaction ID to search for.
	 * @return int|false Donation ID or false if not found.
	 */
	private function find_donation_by_transaction_id( $transaction_id ) {
		if ( empty( $transaction_id ) ) {
			return false;
		}

		$donations = get_posts(
			array(
				'post_type'      => 'donation',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_key'       => '_transaction_id',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'meta_value'     => $transaction_id,
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		return ! empty( $donations ) ? $donations[0] : false;
	}

	/**
	 * Handle completed payment webhook
	 *
	 * @param array $_resource Resource data.
	 */
	private function handle_payment_completed( $_resource ) {
		$transaction_id = $this->extract_transaction_id( $_resource, '/captures/' );

		// Also try sale pattern for PAYMENT.SALE.* events.
		if ( empty( $transaction_id ) ) {
			$transaction_id = $this->extract_transaction_id( $_resource, '/payments/sale/' );
		}

		if ( empty( $transaction_id ) ) {
			$this->log_error( 'completed_webhook_no_transaction', 'Could not extract transaction ID from completed webhook', 0 );
			return;
		}

		$donation_id = $this->find_donation_by_transaction_id( $transaction_id );

		if ( $donation_id ) {
			// Use centralized Donations class to update status.
			$donations_class = new Donations();
			$donations_class->update_status( $donation_id, 'completed' );

			// Store webhook event data.
			update_post_meta( $donation_id, '_webhook_completed_data', wp_json_encode( $_resource ) );

			do_action( 'giftflow_paypal_webhook_payment_completed', $donation_id, $_resource );
		}
	}

	/**
	 * Handle denied payment webhook
	 *
	 * @param array $_resource Resource data.
	 */
	private function handle_payment_denied( $_resource ) {
		$transaction_id = $this->extract_transaction_id( $_resource, '/captures/' );

		// Also try sale pattern for PAYMENT.SALE.* events.
		if ( empty( $transaction_id ) ) {
			$transaction_id = $this->extract_transaction_id( $_resource, '/payments/sale/' );
		}

		if ( empty( $transaction_id ) ) {
			$this->log_error( 'denied_webhook_no_transaction', 'Could not extract transaction ID from denied webhook', 0 );
			return;
		}

		$donation_id = $this->find_donation_by_transaction_id( $transaction_id );

		if ( $donation_id ) {
			// Use centralized Donations class to update status.
			$donations_class = new Donations();
			$donations_class->update_status( $donation_id, 'failed' );

			// Store error details.
			$error_message = __( 'Payment was denied', 'giftflow' );
			if ( isset( $_resource['status_details']['reason'] ) ) {
				$error_message .= ': ' . sanitize_text_field( $_resource['status_details']['reason'] );
			}
			update_post_meta( $donation_id, '_payment_error', $error_message );
			update_post_meta( $donation_id, '_webhook_denied_data', wp_json_encode( $_resource ) );

			do_action( 'giftflow_paypal_webhook_payment_denied', $donation_id, $_resource );
		}
	}

	/**
	 * Handle refunded payment webhook
	 *
	 * @param array $_resource Resource data.
	 */
	private function handle_payment_refunded( $_resource ) {
		// For refund events, we need to extract the original capture/sale ID from links.
		// The refund resource ID is the refund ID, not the original transaction.
		$transaction_id = '';

		// Extract transaction ID (capture ID) from links with rel="up".
		if ( isset( $_resource['links'] ) && is_array( $_resource['links'] ) ) {
			foreach ( $_resource['links'] as $link ) {
				if ( isset( $link['rel'], $link['href'] ) && 'up' === $link['rel'] ) {
					// Check for capture link.
					if ( false !== strpos( $link['href'], '/captures/' ) ) {
						$transaction_id = basename( $link['href'] );
						break;
					}
					// Check for sale link.
					if ( false !== strpos( $link['href'], '/payments/sale/' ) ) {
						$transaction_id = basename( $link['href'] );
						break;
					}
				}
			}
		}

		if ( empty( $transaction_id ) ) {
			$this->log_error( 'refund_webhook_no_transaction', 'Could not extract transaction ID from refund webhook', 0 );
			return;
		}

		$donation_id = $this->find_donation_by_transaction_id( $transaction_id );

		if ( $donation_id ) {
			// Use centralized Donations class to update status.
			$donations_class = new Donations();
			$donations_class->update_status( $donation_id, 'refunded' );

			// Store refund details.
			$refund_id = isset( $_resource['id'] ) ? $_resource['id'] : '';
			if ( ! empty( $refund_id ) ) {
				update_post_meta( $donation_id, '_refund_id', $refund_id );
			}

			// Store refund amount if available.
			if ( isset( $_resource['amount']['value'] ) ) {
				update_post_meta( $donation_id, '_refund_amount', sanitize_text_field( $_resource['amount']['value'] ) );
			}

			update_post_meta( $donation_id, '_refund_raw_data', wp_json_encode( $_resource ) );

			do_action( 'giftflow_paypal_webhook_payment_refunded', $donation_id, $_resource );
		}
	}

	/**
	 * Get currency code
	 *
	 * @return string
	 */
	private function get_currency() {
		return apply_filters( 'giftflow_paypal_currency', 'USD' );
	}

	/**
	 * Get webhook URL
	 *
	 * @return string
	 */
	public function get_webhook_url() {
		return admin_url( 'admin-ajax.php?action=giftflow_paypal_webhook' );
	}

	/**
	 * Log successful payment
	 *
	 * @param string $transaction_id Transaction ID.
	 * @param int    $donation_id Donation ID.
	 */
	private function log_success( $transaction_id, $donation_id ) {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}

		$log_data = array(
			'action' => 'paypal_payment_success',
			'donation_id' => $donation_id,
			'transaction_id' => $transaction_id,
			'timestamp' => current_time( 'mysql' ),
		);

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( '[GiftFlow PayPal Success] ' . wp_json_encode( $log_data ) );
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
		$log_data = array(
			'action' => 'paypal_payment_error',
			'type' => $type,
			'donation_id' => $donation_id,
			'error_message' => $message,
			'error_code' => $code,
			'timestamp' => current_time( 'mysql' ),
		);

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( '[GiftFlow PayPal Error] ' . wp_json_encode( $log_data ) );
	}
}

// Register PayPal gateway.
add_action(
	'giftflow_register_gateways',
	function () {
		new \GiftFlow\Gateways\PayPal_Gateway();
	}
);

/**
 * Helper function to get PayPal Gateway instance
 *
 * @return PayPal_Gateway|null
 */
// phpcs:ignore Universal.Files.SeparateFunctionsFromOO.Mixed, Squiz.Commenting.FunctionComment.Missing
function giftflow_get_paypal_gateway() {
	return Gateway_Base::get_gateway( 'paypal' );
}

/**
 * Process PayPal payment (backward compatibility)
 *
 * @param array $data Payment data.
 * @param int   $donation_id Donation ID.
 * @return mixed
 */
function giftflow_process_payment_paypal( $data = array(), $donation_id = 0 ) {
	$paypal_gateway = giftflow_get_paypal_gateway();

	if ( ! $paypal_gateway ) {
		return new \WP_Error( 'paypal_error', esc_html__( 'PayPal gateway not found', 'giftflow' ) );
	}

	return $paypal_gateway->process_payment( $data, $donation_id );
}
