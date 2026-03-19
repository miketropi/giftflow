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
use GiftFlow\Core\Logger as Giftflow_Logger;
use GiftFlow\Core\Donation_Event_History;

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
		$this->supports = apply_filters(
			'giftflow_paypal_gateway_supports',
			array(
				'webhooks',
				'refunds',
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
			'ajaxurl'           => admin_url( 'admin-ajax.php' ),
			'client_id'         => $this->get_client_id(),
			'mode'              => $this->get_setting( 'paypal_mode', 'sandbox' ),
			'currency'          => $this->get_currency(),
			'nonce'             => wp_create_nonce( 'giftflow_paypal_nonce' ),
			'recurring_enabled' => (bool) $this->get_setting( 'paypal_recurring_enabled', false ),
			'messages'          => array(
				'processing'         => __( 'Processing payment...', 'giftflow' ),
				'error'              => __( 'Payment failed. Please try again.', 'giftflow' ),
				'canceled'           => __( 'Payment was canceled.', 'giftflow' ),
				'subscribe_paypal'   => __( 'Subscribe with PayPal', 'giftflow' ),
				'subscribing'        => __( 'Creating subscription...', 'giftflow' ),
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
								'Recommended PayPal events: <strong>Checkout order approved</strong>, <strong>Checkout order completed</strong>, <strong>Payment capture completed</strong>, <strong>Payment capture denied</strong>, <strong>Payment capture refunded</strong>, <strong>Billing subscription created</strong>, <strong>Billing subscription activated</strong>, <strong>Billing subscription cancelled</strong>, <strong>Payment sale completed</strong>, <strong>Payment sale denied</strong>.',
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
					'paypal_recurring_enabled' => array(
						'id' => 'giftflow_paypal_recurring_enabled',
						'type' => 'switch',
						'label' => __( 'Enable Recurring Donations', 'giftflow' ),
						'value' => isset( $payment_options['paypal']['paypal_recurring_enabled'] ) ? $payment_options['paypal']['paypal_recurring_enabled'] : false,
						'description' => __( 'Allow donors to set up recurring donations via PayPal Subscriptions. Per-campaign recurring options are in Campaign Details.', 'giftflow' ),
						'pro_only' => true,
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
			'payment-gateway/paypal-template.php',
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

		// Recurring: subscription AJAX handler.
		add_action( 'wp_ajax_giftflow_paypal_create_subscription', array( $this, 'ajax_create_subscription' ) );
		add_action( 'wp_ajax_nopriv_giftflow_paypal_create_subscription', array( $this, 'ajax_create_subscription' ) );

		// Recurring: admin product creation.
		add_action( 'admin_notices', array( $this, 'maybe_show_product_notice' ) );
		add_action( 'wp_ajax_giftflow_paypal_create_product', array( $this, 'ajax_create_product' ) );

		// Recurring: admin subscription cancellation.
		add_action( 'wp_ajax_giftflow_paypal_cancel_subscription', array( $this, 'ajax_cancel_subscription' ) );

		// Recurring: handle return from PayPal after subscription approval.
		add_action( 'template_redirect', array( $this, 'handle_subscription_return' ) );
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
		$campaign_id = isset( $data['campaign_id'] ) ? intval( $data['campaign_id'] ) : 0;
		$base_return = $campaign_id ? get_permalink( $campaign_id ) : home_url();
		if ( ! $base_return ) {
			$base_return = home_url();
		}

		$return_url = add_query_arg(
			array(
				'giftflow_paypal_return' => '1',
				'donation_id' => $donation_id,
			),
			$base_return
		);

		$cancel_url = add_query_arg(
			array(
				'giftflow_paypal_cancel' => '1',
				'donation_id' => $donation_id,
			),
			$base_return
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

		Donation_Event_History::add(
			$donation_id,
			'payment_succeeded',
			'completed',
			'',
			array(
				'transaction_id' => $transaction_id,
				'gateway' => 'paypal',
			)
		);
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
		Donation_Event_History::add(
			$donation_id,
			'payment_failed',
			'failed',
			$error_message,
			array(
				'error_code' => $error_code,
				'gateway' => 'paypal',
			)
		);

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

		// sanitize data if it is an array, else sanitize the data.
		$data = is_array( $data ) ? giftflow_sanitize_array( $data ) : sanitize_text_field( $data );

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
			Giftflow_Logger::info(
				'paypal.order.created',
				array(
					'order_id' => $order_id,
					'amount'   => $amount,
					'gateway'  => 'paypal',
				),
				'paypal'
			);
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
		$raw_post_data = $_POST;
		$data = $raw_post_data;

		// if data not an array, return error.
		if ( ! is_array( $data ) ) {
			$this->log_error( 'capture_order_error', 'Invalid data', 0, 'Invalid data' );
			wp_send_json_error(
				array(
					'message' => __( 'Invalid data', 'giftflow' ),
				)
			);
		}

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

		Donation_Event_History::add(
			$donation_id,
			'payment_succeeded',
			'completed',
			'',
			array(
				'transaction_id' => $transaction_id,
				'order_id' => $order_id,
				'gateway' => 'paypal',
			)
		);
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
		$data = giftflow_sanitize_array( $_POST );
		$donation_id = isset( $data['donation_id'] ) ? intval( $data['donation_id'] ) : null;

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
		// We verify the webhook signature later in "verify_webhook_signature" method.
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
			// get mode.
			$mode = $this->get_setting( 'paypal_mode', 'sandbox' );

			// is live mode.
			if ( 'live' === $mode ) {
				// Log warning & exit if webhook ID is not configured.
				$this->log_error( 'webhook_id_missing', 'Webhook ID not configured - signature verification skipped in live mode', 0 );
				status_header( 400 );
				exit;
			}
		}

		try {
			switch ( $event['event_type'] ) {
				// Payment completed events.
				case 'PAYMENT.SALE.COMPLETED':
				case 'PAYMENT.CAPTURE.COMPLETED':
					if ( $this->is_subscription_sale( $event['resource'] ) ) {
						$this->handle_subscription_payment_completed( $event['resource'] );
					} else {
						$this->handle_payment_completed( $event['resource'] );
					}
					break;

				// Payment denied/failed events.
				case 'PAYMENT.SALE.DENIED':
				case 'PAYMENT.CAPTURE.DENIED':
					if ( $this->is_subscription_sale( $event['resource'] ) ) {
						$this->handle_subscription_payment_denied( $event['resource'] );
					} else {
						$this->handle_payment_denied( $event['resource'] );
					}
					break;

				// Payment refunded events.
				case 'PAYMENT.SALE.REFUNDED':
				case 'PAYMENT.CAPTURE.REFUNDED':
					$this->handle_payment_refunded( $event['resource'] );
					break;

				// Subscription lifecycle events.
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

				default:
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
			Donation_Event_History::add(
				$donation_id,
				'payment_succeeded',
				'completed',
				__( 'Webhook: payment completed', 'giftflow' ),
				array(
					'transaction_id' => $transaction_id,
					'gateway' => 'paypal',
					'source' => 'webhook',
				)
			);
			Giftflow_Logger::info(
				'paypal.webhook.payment.completed',
				array(
					'donation_id'    => $donation_id,
					'transaction_id' => $transaction_id,
					'gateway'        => 'paypal',
				),
				'paypal'
			);

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
			$error_message = __( 'Payment was denied', 'giftflow' );
			if ( isset( $_resource['status_details']['reason'] ) ) {
				$error_message .= ': ' . sanitize_text_field( $_resource['status_details']['reason'] );
			}

			Donation_Event_History::add(
				$donation_id,
				'payment_failed',
				'failed',
				$error_message,
				array(
					'transaction_id' => $transaction_id,
					'gateway' => 'paypal',
					'source' => 'webhook',
				)
			);
			Giftflow_Logger::error(
				'paypal.webhook.payment.denied',
				array(
					'donation_id'    => $donation_id,
					'transaction_id' => $transaction_id,
					'error_message'  => $error_message,
					'gateway'        => 'paypal',
				),
				'paypal'
			);

			// Use centralized Donations class to update status.
			$donations_class = new Donations();
			$donations_class->update_status( $donation_id, 'failed' );

			// Store error details.
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
			$refund_id = isset( $_resource['id'] ) ? $_resource['id'] : '';
			Donation_Event_History::add(
				$donation_id,
				'payment_refunded',
				'refunded',
				__( 'Webhook: payment refunded', 'giftflow' ),
				array(
					'refund_id' => $refund_id,
					'gateway' => 'paypal',
					'source' => 'webhook',
				)
			);
			Giftflow_Logger::info(
				'paypal.webhook.payment.refunded',
				array(
					'donation_id' => $donation_id,
					'refund_id'   => $refund_id,
					'gateway'     => 'paypal',
				),
				'paypal'
			);

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

	// =========================================================================
	// Recurring: Helpers
	// =========================================================================

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

	/**
	 * Map plugin recurring interval to PayPal billing cycle parameters.
	 *
	 * @param string $interval Plugin interval (daily, weekly, monthly, quarterly, yearly).
	 * @return array { interval_unit: string, interval_count: int }
	 */
	private function map_interval_to_paypal( $interval ) {
		$map = array(
			'daily'     => array(
				'interval_unit' => 'DAY',
				'interval_count' => 1,
			),
			'weekly'    => array(
				'interval_unit' => 'WEEK',
				'interval_count' => 1,
			),
			'monthly'   => array(
				'interval_unit' => 'MONTH',
				'interval_count' => 1,
			),
			'quarterly' => array(
				'interval_unit' => 'MONTH',
				'interval_count' => 3,
			),
			'yearly'    => array(
				'interval_unit' => 'YEAR',
				'interval_count' => 1,
			),
		);

		return isset( $map[ $interval ] ) ? $map[ $interval ] : $map['monthly'];
	}

	/**
	 * Check if a PAYMENT.SALE resource is related to a subscription.
	 *
	 * @param array $_resource Resource data from webhook.
	 * @return bool
	 */
	private function is_subscription_sale( $_resource ) {
		return ! empty( $_resource['billing_agreement_id'] );
	}

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

	// =========================================================================
	// Recurring: Product (admin notice + manual creation)
	// =========================================================================

	/**
	 * Show an admin notice if the PayPal Donation Product has not been created yet.
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

	/**
	 * AJAX handler: create the PayPal Donation Product.
	 */
	public function ajax_create_product() {
		check_ajax_referer( 'giftflow_paypal_create_product', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'giftflow' ) ) );
		}

		$existing = get_option( 'giftflow_paypal_product_id', '' );
		if ( ! empty( $existing ) ) {
			wp_send_json_success(
				array(
					'message' => __( 'Product already exists.', 'giftflow' ),
					'product_id' => $existing,
				)
			);
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

	// =========================================================================
	// Recurring: Plan
	// =========================================================================

	/**
	 * Generate a human-readable option key for a PayPal plan.
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

	/**
	 * Get or create a PayPal billing plan for the given parameters.
	 *
	 * @param string $amount         Donation amount.
	 * @param string $currency       Currency code.
	 * @param string $interval       Plugin interval (daily, weekly, monthly, quarterly, yearly).
	 * @param int    $number_of_times Number of billing cycles (0 = infinite).
	 * @return string|\WP_Error PayPal Plan ID or WP_Error.
	 */
	private function get_or_create_paypal_plan( $amount, $currency, $interval, $number_of_times = 0 ) {
		$paypal_interval = $this->map_interval_to_paypal( $interval );
		$interval_unit   = $paypal_interval['interval_unit'];
		$interval_count  = $paypal_interval['interval_count'];

		$option_key = $this->generate_plan_key( $amount, $interval );

		$cached_plan_id = get_option( $option_key, '' );

		if ( ! empty( $cached_plan_id ) ) {
			return $cached_plan_id;
		}

		$lock_key = 'giftflow_paypal_plan_lock_' . sanitize_key( $option_key );
		$lock     = get_transient( $lock_key );

		if ( false !== $lock ) {
			sleep( 2 );
			$cached_plan_id = get_option( $option_key, '' );
			if ( ! empty( $cached_plan_id ) ) {
				return $cached_plan_id;
			}
			return new \WP_Error( 'paypal_plan_locked', __( 'Plan creation in progress, please try again.', 'giftflow' ) );
		}

		set_transient( $lock_key, '1', 30 );

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

	// =========================================================================
	// Recurring: Subscription
	// =========================================================================

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

		$campaign_id = isset( $data['campaign_id'] ) ? intval( $data['campaign_id'] ) : 0;
		$base_return = $campaign_id ? get_permalink( $campaign_id ) : home_url();
		if ( ! $base_return ) {
			$base_return = home_url();
		}

		// Generate a random token for verification callback URL.
		$token_verification_callback_url = 'giftflow_' . wp_generate_password( 32, false );
		update_post_meta( $donation_id, '_token_verification_callback_url', $token_verification_callback_url );

		// Create return URL with token for verification.
		$return_url = add_query_arg(
			array(
				'giftflow_paypal_subscription_return' => '1',
				'donation_id' => $donation_id,
				'token_verification' => $token_verification_callback_url,
			),
			$base_return
		);

		// Create cancel URL with token for verification.
		$cancel_url = add_query_arg(
			array(
				'giftflow_paypal_subscription_cancel' => '1',
				'donation_id' => $donation_id,
				'token_verification' => $token_verification_callback_url,
			),
			$base_return
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

	// =========================================================================
	// Recurring: AJAX handler — create subscription
	// =========================================================================

	/**
	 * AJAX handler for creating a PayPal subscription.
	 */
	public function ajax_create_subscription() {
		check_ajax_referer( 'giftflow_paypal_nonce', 'nonce' );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$data = $_POST;
		$data = is_array( $data ) ? giftflow_sanitize_array( $data ) : sanitize_text_field( $data );

		do_action( 'giftflow_donation_form_before_process_donation', $data );

		$amount = isset( $data['amount'] ) ? floatval( $data['amount'] ) : 0;
		if ( ! $amount || $amount <= 0 ) {
			wp_send_json_error( array( 'message' => __( 'Donation amount is required', 'giftflow' ) ) );
		}

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
			$interval     = $donation_data['recurring_interval'];
			$currency     = strtoupper( $this->get_currency() );
			$campaign_id  = intval( $donation_data['campaign_id'] );
			$num_of_times = 0;

			if ( $campaign_id ) {
				$num_of_times = absint( get_post_meta( $campaign_id, '_recurring_number_of_times', true ) );
			}

			$plan_id = $this->get_or_create_paypal_plan( $amount, $currency, $interval, $num_of_times );
			if ( is_wp_error( $plan_id ) ) {
				wp_send_json_error( array( 'message' => $plan_id->get_error_message() ) );
			}

			$donation_data['recurring_number_of_times'] = $num_of_times;
			$donation_id = $this->create_donation_record( $donation_data );
			if ( is_wp_error( $donation_id ) ) {
				wp_send_json_error( array( 'message' => $donation_id->get_error_message() ) );
			}

			$result = $this->create_paypal_subscription( $plan_id, $donation_data, $donation_id );
			if ( is_wp_error( $result ) ) {
				wp_send_json_error( array( 'message' => $result->get_error_message() ) );
			}

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

	// =========================================================================
	// Recurring: Return URL handler (Phase 5)
	// =========================================================================

	/**
	 * Verify the token verification callback URL.
	 *
	 * @since 1.0.6
	 *
	 * @param string $token_verification Token verification.
	 * @param int $donation_id Donation ID.
	 * @return bool True if the token verification callback URL is valid, false otherwise.
	 */
	private function verify_token_verification_callback_url( $token_verification, $donation_id ) {
		$token_verification_callback_url = get_post_meta( $donation_id, '_token_verification_callback_url', true );
		if ( empty( $token_verification_callback_url ) || $token_verification_callback_url !== $token_verification ) {
			return false;
		}

		return true;
	}

	/**
	 * Handle PayPal subscription return URL after donor approval.
	 */
	public function handle_subscription_return() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['giftflow_paypal_subscription_cancel'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$token_verification = isset( $_GET['token_verification'] ) ? sanitize_text_field( wp_unslash( $_GET['token_verification'] ) ) : '';
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$donation_id = isset( $_GET['donation_id'] ) ? absint( wp_unslash( $_GET['donation_id'] ) ) : 0;

			if ( ! $donation_id || ! $this->verify_token_verification_callback_url( $token_verification, $donation_id ) ) {
				// direct to home url.
				wp_safe_redirect( home_url() );
				exit;
			}

			$donations_class = new Donations();
			$donations_class->update_status( $donation_id, 'cancelled' );
			update_post_meta( $donation_id, '_recurring_status', 'cancelled' );

			Donation_Event_History::add(
				$donation_id,
				'recurring_subscription_cancelled_by_donor',
				'cancelled',
				__( 'Donor cancelled PayPal subscription approval', 'giftflow' ),
				array(
					'gateway' => 'paypal',
					'source' => 'cancel_url',
				)
			);

			$campaign_id  = $donation_id ? absint( get_post_meta( $donation_id, '_campaign_id', true ) ) : 0;
			$redirect_url = $campaign_id ? get_permalink( $campaign_id ) : home_url();
			if ( ! $redirect_url ) {
				$redirect_url = home_url();
			}
			wp_safe_redirect( $redirect_url );
			exit;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['giftflow_paypal_subscription_return'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$token_verification = isset( $_GET['token_verification'] ) ? sanitize_text_field( wp_unslash( $_GET['token_verification'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$donation_id        = isset( $_GET['donation_id'] ) ? absint( $_GET['donation_id'] ) : 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$subscription_id    = isset( $_GET['subscription_id'] ) ? sanitize_text_field( wp_unslash( $_GET['subscription_id'] ) ) : '';

		if ( ! $donation_id || ! $this->verify_token_verification_callback_url( $token_verification, $donation_id ) ) {
			// direct to home url.
			wp_safe_redirect( home_url() );
			exit;
		}

		if ( empty( $subscription_id ) ) {
			$subscription_id = get_post_meta( $donation_id, '_paypal_subscription_id', true );
		}

		if ( empty( $subscription_id ) ) {
			$this->log_error( 'subscription_return_no_id', 'No subscription ID found on return', $donation_id );
			return;
		}

		$subscription = $this->get_paypal_subscription( $subscription_id );

		if ( is_wp_error( $subscription ) ) {
			$this->log_error( 'subscription_return_verify_failed', $subscription->get_error_message(), $donation_id );
			return;
		}

		$status = isset( $subscription['status'] ) ? $subscription['status'] : '';

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

		// Do NOT mark donation as completed here. The PAYMENT.SALE.COMPLETED and
		// BILLING.SUBSCRIPTION.ACTIVATED webhooks handle status transitions.
		// Setting completed here would cause the first SALE webhook to think
		// it is a renewal and create a duplicate child donation.
		if ( isset( $subscription['billing_info']['next_billing_time'] ) ) {
			update_post_meta(
				$donation_id,
				'_recurring_next_payment_date',
				sanitize_text_field( $subscription['billing_info']['next_billing_time'] )
			);
		}

		Donation_Event_History::add(
			$donation_id,
			'recurring_subscription_return',
			$recurring_status,
			sprintf(
				/* translators: %s: PayPal subscription status */
				__( 'Donor returned from PayPal (subscription status: %s)', 'giftflow' ),
				$status
			),
			array(
				'subscription_id' => $subscription_id,
				'gateway'         => 'paypal',
				'source'          => 'return_url',
			)
		);

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

		// thank donor page or home url.
		$thank_donor_page = giftflow_get_thank_donor_page();
		$base_return = $thank_donor_page ? get_permalink( $thank_donor_page ) : home_url();

		$redirect_url = apply_filters(
			'giftflow_paypal_subscription_return_url',
			add_query_arg(
				array(
					'giftflow_donation_success' => '1',
					'donation_id'               => $donation_id,
				),
				$base_return
			),
			$donation_id
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}

	// =========================================================================
	// Recurring: Webhook handlers (Phase 6)
	// =========================================================================

	/**
	 * Handle BILLING.SUBSCRIPTION.CREATED webhook.
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

		Donation_Event_History::add(
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

	/**
	 * Handle BILLING.SUBSCRIPTION.ACTIVATED webhook.
	 *
	 * @param array $_resource Subscription resource from webhook.
	 */
	private function handle_subscription_activated( $_resource ) {
		$subscription_id = isset( $_resource['id'] ) ? $_resource['id'] : '';
		$donation_id     = $this->find_donation_by_subscription_id( $subscription_id );

		if ( ! $donation_id ) {
			$donation_id = isset( $_resource['custom_id'] ) ? absint( $_resource['custom_id'] ) : 0;
			if ( $donation_id ) {
				update_post_meta( $donation_id, '_paypal_subscription_id', $subscription_id );
			}
		}

		if ( ! $donation_id ) {
			$this->log_error( 'webhook_subscription_activated_no_donation', 'Parent donation not found for subscription ' . $subscription_id, 0 );
			return;
		}

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
	}

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

	/**
	 * Handle PAYMENT.SALE.COMPLETED webhook for subscription payments.
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

		$parent_txn = get_post_meta( $parent_donation_id, '_transaction_id', true );

		if ( ! empty( $existing ) || $parent_txn === $sale_id ) {
			return;
		}

		// Detect first payment by checking if parent already has a sale recorded.
		// Cannot rely on _status because the return URL handler or ACTIVATED webhook
		// may have already set it to 'completed' before this webhook arrives.
		$parent_sale_id   = get_post_meta( $parent_donation_id, '_paypal_sale_id', true );
		$is_first_payment = empty( $parent_sale_id );

		$amount   = isset( $_resource['amount']['total'] ) ? $_resource['amount']['total'] : '';
		$currency = isset( $_resource['amount']['currency'] ) ? $_resource['amount']['currency'] : '';

		if ( $is_first_payment ) {
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

			$copy_keys = array( '_amount', '_campaign_id', '_donor_id', '_payment_method', '_donation_type', '_recurring_interval' );
			foreach ( $copy_keys as $key ) {
				if ( isset( $meta[ $key ][0] ) ) {
					update_post_meta( $renewal_id, $key, $meta[ $key ][0] );
				}
			}

			if ( ! empty( $amount ) ) {
				update_post_meta( $renewal_id, '_amount', floatval( $amount ) );
			}

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

	// =========================================================================
	// Recurring: Admin cancellation (Phase 9)
	// =========================================================================

	/**
	 * AJAX handler: cancel a PayPal subscription (admin or donation owner).
	 */
	public function ajax_cancel_subscription() {
		check_ajax_referer( 'giftflow_paypal_nonce', 'nonce' );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$donation_id     = isset( $_POST['donation_id'] ) ? absint( $_POST['donation_id'] ) : 0;

		if ( ! current_user_can( 'manage_options' ) ) {
			$current_user = wp_get_current_user();
			$donor_id     = $current_user && $current_user->user_email
				? giftflow_get_donor_id_by_email( $current_user->user_email )
				: 0;
			$donation_donor_id = $donation_id ? absint( get_post_meta( $donation_id, '_donor_id', true ) ) : 0;

			if ( ! $donor_id || ! $donation_donor_id || $donor_id !== $donation_donor_id ) {
				wp_send_json_error( array( 'message' => __( 'Unauthorized', 'giftflow' ) ) );
			}
		}

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

		if ( 204 !== $code ) {
			$body      = json_decode( wp_remote_retrieve_body( $response ), true );
			$error_msg = isset( $body['message'] ) ? $body['message'] : __( 'Failed to cancel subscription', 'giftflow' );
			wp_send_json_error( array( 'message' => $error_msg ) );
		}

		update_post_meta( $donation_id, '_recurring_status', 'cancelled' );

		$cancelled_by = current_user_can( 'manage_options' ) ? 'admin' : 'donor';

		Donation_Event_History::add(
			$donation_id,
			'recurring_subscription_cancelled',
			'cancelled',
			'admin' === $cancelled_by
				? __( 'PayPal subscription cancelled by admin.', 'giftflow' )
				: __( 'PayPal subscription cancelled by donor.', 'giftflow' ),
			array(
				'subscription_id' => $subscription_id,
				'gateway'         => 'paypal',
				'cancelled_by'    => $cancelled_by,
			)
		);

		Giftflow_Logger::info(
			'paypal.subscription.cancelled_by_' . $cancelled_by,
			array(
				'donation_id'     => $donation_id,
				'subscription_id' => $subscription_id,
				'gateway'         => 'paypal',
				'cancelled_by'    => $cancelled_by,
			),
			'paypal'
		);

		wp_send_json_success( array( 'message' => __( 'PayPal subscription cancelled successfully.', 'giftflow' ) ) );
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
		Giftflow_Logger::info(
			'paypal.payment.succeeded',
			array(
				'donation_id'    => $donation_id,
				'transaction_id' => $transaction_id,
				'gateway'        => 'paypal',
			),
			'paypal'
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
			'paypal.payment.failed',
			array(
				'type'          => $type,
				'donation_id'   => $donation_id,
				'error_message' => $message,
				'error_code'    => $code,
				'gateway'       => 'paypal',
			),
			'paypal'
		);
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
