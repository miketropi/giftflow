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

use GiftFlow\PayPal\PayPalApiClient;
use GiftFlow\PayPal\PayPalConnectionPanel;
use GiftFlow\PayPal\PayPalConnectionRepository;
use GiftFlow\PayPal\PayPalDonationBinding;
use GiftFlow\PayPal\PayPalEnvironment;
use GiftFlow\PayPal\PayPalWebhookDispatcher;
use GiftFlow\PayPal\PayPalWebhookEventRegistry;
use GiftFlow\PayPal\PayPalWebhookProvisioner;
use GiftFlow\PayPal\PayPalWebhookReceiver;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PayPal Gateway Class
 */
class PayPal_Gateway extends Gateway_Base {
	/**
	 * Connection repository.
	 *
	 * @var PayPalConnectionRepository|null
	 */
	private $repository;

	/**
	 * Donation binding helper.
	 *
	 * @var PayPalDonationBinding|null
	 */
	private $binding;

	/**
	 * Shared webhook receiver.
	 *
	 * @var PayPalWebhookReceiver|null
	 */
	private $webhook_receiver;
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
		$this->add_paypal_assets();
	}

	/**
	 * Connection repository.
	 *
	 * @return PayPalConnectionRepository
	 */
	public function get_repository(): PayPalConnectionRepository {
		if ( null === $this->repository ) {
			$this->repository = new PayPalConnectionRepository();
		}

		return $this->repository;
	}

	/**
	 * Active payment environment.
	 *
	 * @return string
	 */
	public function get_active_environment(): string {
		return $this->get_repository()->get_active_environment();
	}

	/**
	 * Create an API client for one environment.
	 *
	 * @param string $environment Environment id.
	 * @return PayPalApiClient|null
	 */
	public function client_for_environment( string $environment ): ?PayPalApiClient {
		$environment = PayPalEnvironment::assert_valid( $environment );
		$credentials = $this->get_repository()->get_credentials( $environment );

		if ( '' === $credentials['client_id'] || '' === $credentials['client_secret'] ) {
			return null;
		}

		return new PayPalApiClient( $environment, $credentials['client_id'], $credentials['client_secret'], $this->get_repository() );
	}

	/**
	 * Resolve a donation's PayPal environment.
	 *
	 * @param int $donation_id Donation post id.
	 * @return string
	 */
	public function resolve_donation_environment( int $donation_id ): string {
		return $this->binding()->resolve_environment( $donation_id );
	}

	/**
	 * Donation binding helper.
	 *
	 * @return PayPalDonationBinding
	 */
	public function binding(): PayPalDonationBinding {
		if ( null === $this->binding ) {
			$this->binding = new PayPalDonationBinding( $this->get_repository() );
		}

		return $this->binding;
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
		$environment = $this->get_active_environment();
		$credentials = $this->get_repository()->get_credentials( $environment );
		$client_id   = $credentials['client_id'];

		if ( empty( $client_id ) ) {
			return;
		}

		$sdk_url = add_query_arg(
			array(
				'client-id'  => $client_id,
				'currency'   => strtoupper( $this->get_currency() ),
				'intent'     => 'capture',
				'components' => 'buttons,marks',
			),
			PayPalEnvironment::sdk_base( $environment )
		);

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
		$environment = $this->get_active_environment();
		$credentials = $this->get_repository()->get_credentials( $environment );

		return array(
			'ajaxurl'           => admin_url( 'admin-ajax.php' ),
			'client_id'         => $credentials['client_id'],
			'mode'              => $environment,
			'currency'          => $this->get_currency(),
			'nonce'             => wp_create_nonce( 'giftflow_paypal_nonce' ),
			'recurring_enabled' => (bool) $this->get_setting( 'paypal_recurring_enabled', false ),
			'messages'          => array(
				'processing'       => __( 'Processing payment...', 'giftflow' ),
				'error'            => __( 'Payment failed. Please try again.', 'giftflow' ),
				'canceled'         => __( 'Payment was canceled.', 'giftflow' ),
				'subscribe_paypal' => __( 'Subscribe with PayPal', 'giftflow' ),
				'subscribing'      => __( 'Creating subscription...', 'giftflow' ),
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
		$paypal_options  = isset( $payment_options['paypal'] ) && is_array( $payment_options['paypal'] ) ? $payment_options['paypal'] : array();
		$events          = PayPalWebhookEventRegistry::required_events( $this->get_repository()->is_recurring_enabled() );

		$payment_fields['paypal'] = array(
			'id' => 'giftflow_paypal',
			'name' => 'giftflow_payment_options[paypal]',
			'type' => 'accordion',
			'label' => __( 'PayPal', 'giftflow' ),
			'description' => __( 'Configure PayPal payment settings', 'giftflow' ),
			'content' => array( $this, 'render_connection_settings' ),
			'accordion_settings' => array(
				'label' => __( 'PayPal Settings', 'giftflow' ),
				'is_open' => true,
				'fields' => array(
					'paypal_enabled' => array(
						'id' => 'giftflow_paypal_enabled',
						'type' => 'switch',
						'label' => __( 'Enable PayPal', 'giftflow' ),
						'value' => isset( $paypal_options['paypal_enabled'] ) ? $paypal_options['paypal_enabled'] : false,
						'description' => __( 'Enable PayPal as a payment method', 'giftflow' ),
					),
					'paypal_webhook_enabled' => array(
						'id' => 'giftflow_paypal_webhook_enabled',
						'type' => 'switch',
						'label' => __( 'Enable Webhook', 'giftflow' ),
						'value' => isset( $paypal_options['paypal_webhook_enabled'] ) ? $paypal_options['paypal_webhook_enabled'] : false,
						'description' => sprintf(
							// translators: 1: webhook URL, 2: comma separated event names.
							__( 'Enable webhooks for payment status updates. Webhook URL: %1$s<br>Recommended PayPal events: %2$s', 'giftflow' ),
							'<code>' . esc_html( $this->get_webhook_url() ) . '</code>',
							esc_html( implode( ', ', $events ) )
						),
					),
					'paypal_recurring_enabled' => array(
						'id' => 'giftflow_paypal_recurring_enabled',
						'type' => 'switch',
						'label' => __( 'Enable Recurring Donations', 'giftflow' ),
						'value' => isset( $paypal_options['paypal_recurring_enabled'] ) ? $paypal_options['paypal_recurring_enabled'] : false,
						'description' => __( 'Allow donors to set up recurring donations via PayPal Subscriptions. Per-campaign recurring options are in Campaign Details.', 'giftflow' ),
						'pro_only' => true,
					),
				),
			),
		);

		return $payment_fields;
	}

	/** Render connection settings. @return void */
	public function render_connection_settings(): void {
		$panel = new PayPalConnectionPanel( $this->get_repository(), $this->get_active_environment(), $this->get_webhook_url() );
		$panel->render();
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
				'mode' => $this->get_active_environment(),
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
		$environment = $this->get_active_environment();
		$client = $this->client_for_environment( $environment );
		if ( ! $client ) {
			throw new \Exception( esc_html__( 'PayPal is not properly configured', 'giftflow' ) );
		}
		$order_data = array(
			'intent' => 'CAPTURE',
			'purchase_units' => array(
				array(
					'amount' => array(
						'currency_code' => strtoupper( $this->get_currency() ),
						'value' => number_format( (float) $donation_data['donation_amount'], 2, '.', '' ),
					),
					'description' => sprintf(
						// translators: 1: donor name, 2: campaign id or name.
						__( 'Donation from %1$s for campaign %2$s', 'giftflow' ),
						$donation_data['donor_name'],
						$donation_data['campaign_id']
					),
				),
			),
			'application_context' => array(
				'brand_name' => get_bloginfo( 'name' ),
				'landing_page' => 'NO_PREFERENCE',
				'user_action' => 'PAY_NOW',
				'return_url' => home_url(),
				'cancel_url' => home_url(),
			),
		);
		$response = $client->create_order( $order_data );
		if ( ! $response['ok'] || empty( $response['body']['id'] ) ) {
			throw new \Exception( esc_html__( 'Failed to create PayPal order', 'giftflow' ) );
		}
		$order_id = $response['body']['id'];
		$profile = $this->get_repository()->get_profile( $environment );
		set_transient(
			'giftflow_paypal_order_' . $order_id,
			array(
				'donation_data' => $donation_data,
				'environment' => $environment,
				'paypal_app_id' => $client->app_identity(),
				'binding_fingerprint' => $profile['binding_fingerprint'],
			),
			HOUR_IN_SECONDS
		);
		return $order_id;
	}


	/**
	 * Capture PayPal order using PayPal Orders API v2
	 *
	 * @param string $order_id PayPal order ID.
	 * @return array|\WP_Error
	 */
	private function capture_paypal_order( $order_id ) {
		$transient_key = 'giftflow_paypal_order_' . $order_id;
		$snapshot = get_transient( $transient_key );
		$valid_snapshot = is_array( $snapshot ) && isset( $snapshot['donation_data'], $snapshot['environment'], $snapshot['paypal_app_id'], $snapshot['binding_fingerprint'] );
		$valid_snapshot = $valid_snapshot && is_array( $snapshot['donation_data'] ) && PayPalEnvironment::is_valid( $snapshot['environment'] );
		if ( ! $valid_snapshot ) {
			return new \WP_Error( 'paypal_error', esc_html__( 'Donation data not found. Please try again.', 'giftflow' ) );
		}
		$environment = $snapshot['environment'];
		$profile = $this->get_repository()->get_profile( $environment );
		if ( ! hash_equals( (string) $profile['binding_fingerprint'], (string) $snapshot['binding_fingerprint'] ) ) {
			return new \WP_Error( 'paypal_error', esc_html__( 'PayPal account binding has changed. Please try again.', 'giftflow' ) );
		}
		$client = $this->client_for_environment( $environment );
		if ( ! $client ) {
			return new \WP_Error( 'paypal_error', esc_html__( 'PayPal is not properly configured', 'giftflow' ) );
		}
		$response = $client->capture_order( $order_id );
		if ( ! $response['ok'] ) {
			delete_transient( $transient_key );
			return new \WP_Error( 'paypal_error', esc_html__( 'Failed to capture PayPal order', 'giftflow' ) );
		}
		$response_body = $response['body'];
		$donation_id = $this->create_donation_record( $snapshot['donation_data'] );
		if ( is_wp_error( $donation_id ) ) {
			delete_transient( $transient_key );
			return new \WP_Error( 'paypal_error', esc_html__( 'Payment successful but failed to create donation record', 'giftflow' ) );
		}
		$transaction_id = isset( $response_body['purchase_units'][0]['payments']['captures'][0]['id'] ) ? $response_body['purchase_units'][0]['payments']['captures'][0]['id'] : '';
		update_post_meta( $donation_id, '_transaction_id', $transaction_id );
		update_post_meta( $donation_id, '_transaction_raw_data', wp_json_encode( $response_body ) );
		update_post_meta( $donation_id, '_paypal_order_id', $order_id );
		update_post_meta( $donation_id, '_payment_method', 'paypal' );
		$this->binding()->persist( (int) $donation_id, $environment, $snapshot['paypal_app_id'] );
		$donations_class = new Donations();
		$donations_class->update_status( $donation_id, 'completed' );
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
				'environment' => $environment,
			)
		);
		$this->log_success( $transaction_id, $donation_id );
		do_action( 'giftflow_paypal_payment_completed', $donation_id, $transaction_id, $response_body );
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
		$this->webhook_receiver()->handle_request();
	}

	/**
	 * Shared webhook receiver instance.
	 *
	 * @return PayPalWebhookReceiver
	 */
	private function webhook_receiver(): PayPalWebhookReceiver {
		if ( null === $this->webhook_receiver ) {
			$repository = $this->get_repository();
			$this->webhook_receiver = new PayPalWebhookReceiver( $repository, new PayPalWebhookDispatcher( $repository ) );
		}

		return $this->webhook_receiver;
	}

	// =========================================================================
	// Recurring: Helpers
	// =========================================================================


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

		$product_id = get_option( $this->get_repository()->product_option_key( $this->get_active_environment() ), '' );
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
		$environment = $this->get_active_environment();
		$option_key = $this->get_repository()->product_option_key( $environment );
		$existing = get_option( $option_key, '' );
		if ( ! empty( $existing ) ) {
			wp_send_json_success(
				array(
					'message' => __( 'Product already exists.', 'giftflow' ),
					'product_id' => $existing,
				)
			);
		}
		$client = $this->client_for_environment( $environment );
		if ( ! $client ) {
			wp_send_json_error( array( 'message' => __( 'Failed to authenticate with PayPal', 'giftflow' ) ) );
		}
		$response = $client->create_product(
			array(
				'name' => __( 'Giftflow Donation', 'giftflow' ),
				'type' => 'SERVICE',
			)
		);
		if ( ! $response['ok'] || empty( $response['body']['id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Failed to create PayPal product', 'giftflow' ) ) );
		}
		update_option( $option_key, $response['body']['id'], false );
		wp_send_json_success(
			array(
				'message' => __( 'PayPal Donation Product created successfully!', 'giftflow' ),
				'product_id' => $response['body']['id'],
			)
		);
	}

	/**
	 * Get the stored PayPal Product ID.
	 *
	 * @param string         $environment Environment id.
	 * @param PayPalApiClient $client     Environment-bound API client.
	 * @return string|\WP_Error Product ID or WP_Error if not yet created.
	 */
	private function get_paypal_product_id( $environment, $client ) {
		$option_key = $this->get_repository()->product_option_key( $environment );
		$product_id = get_option( $option_key, '' );
		if ( ! empty( $product_id ) ) {
			return $product_id;
		}
		$legacy_id = get_option( $this->get_repository()->legacy_product_option(), '' );
		if ( ! empty( $legacy_id ) ) {
			$response = $client->get_product( $legacy_id );
			if ( $response['ok'] ) {
				update_option( $option_key, $legacy_id, false );
				return $legacy_id;
			}
		}
		return new \WP_Error( 'paypal_product_missing', __( 'PayPal Donation Product has not been created yet. Please create it from the WordPress admin.', 'giftflow' ) );
	}

	// =========================================================================
	// Recurring: Plan
	// =========================================================================


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
		$environment = $this->get_active_environment();
		$client = $this->client_for_environment( $environment );
		if ( ! $client ) {
			return new \WP_Error( 'paypal_token_error', __( 'Failed to authenticate with PayPal', 'giftflow' ) );
		}
		$paypal_interval = $this->map_interval_to_paypal( $interval );
		$amount_minor = (int) round( (float) $amount * 100 );
		$option_key = $this->get_repository()->plan_option_key( $environment, $currency, $amount_minor, $interval, (int) $number_of_times );
		$cached_plan_id = get_option( $option_key, '' );
		if ( ! empty( $cached_plan_id ) ) {
			return $cached_plan_id;
		}
		$legacy_key = $this->get_repository()->legacy_plan_option( $currency, $amount_minor, $interval );
		$legacy_id = get_option( $legacy_key, '' );
		if ( ! empty( $legacy_id ) ) {
			$legacy_response = $client->get_plan( $legacy_id );
			if ( $legacy_response['ok'] ) {
				update_option( $option_key, $legacy_id, false );
				return $legacy_id;
			}
		}
		$product_id = $this->get_paypal_product_id( $environment, $client );
		if ( is_wp_error( $product_id ) ) {
			return $product_id;
		}
		$plan_data = array(
			'product_id' => $product_id,
			'name' => sprintf(
				// translators: 1: formatted amount, 2: currency code, 3: billing interval unit.
				__( 'Donation %1$s %2$s / %3$s', 'giftflow' ),
				number_format( (float) $amount, 2, '.', '' ),
				strtoupper( $currency ),
				strtolower( $paypal_interval['interval_unit'] )
			),
			'status' => 'ACTIVE',
			'billing_cycles' => array(
				array(
					'frequency' => $paypal_interval,
					'tenure_type' => 'REGULAR',
					'sequence' => 1,
					'total_cycles' => (int) $number_of_times,
					'pricing_scheme' => array(
						'fixed_price' => array(
							'value' => number_format( (float) $amount, 2, '.', '' ),
							'currency_code' => strtoupper( $currency ),
						),
					),
				),
			),
			'payment_preferences' => array(
				'auto_bill_outstanding' => true,
				'payment_failure_threshold' => 3,
			),
		);
		$response = $client->create_plan( $plan_data );
		if ( ! $response['ok'] || empty( $response['body']['id'] ) ) {
			return new \WP_Error( 'paypal_plan_error', __( 'Failed to create PayPal plan', 'giftflow' ) );
		}
		update_option( $option_key, $response['body']['id'], false );
		return $response['body']['id'];
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
		$environment = $this->resolve_donation_environment( (int) $donation_id );
		$client = $this->client_for_environment( $environment );
		if ( ! $client ) {
			return new \WP_Error( 'paypal_token_error', __( 'Failed to authenticate with PayPal', 'giftflow' ) );
		}
		$campaign_id = isset( $data['campaign_id'] ) ? intval( $data['campaign_id'] ) : 0;
		$base_return = $campaign_id ? get_permalink( $campaign_id ) : home_url();
		$base_return = $base_return ? $base_return : home_url();
		$token = 'giftflow_' . wp_generate_password( 32, false );
		update_post_meta( $donation_id, '_token_verification_callback_url', $token );
		$return_url = add_query_arg(
			array(
				'giftflow_paypal_subscription_return' => '1',
				'donation_id' => $donation_id,
				'token_verification' => $token,
			),
			$base_return
		);
		$cancel_url = add_query_arg(
			array(
				'giftflow_paypal_subscription_cancel' => '1',
				'donation_id' => $donation_id,
				'token_verification' => $token,
			),
			$base_return
		);
		$subscription_data = array(
			'plan_id' => $plan_id,
			'subscriber' => array(
				'name' => array( 'given_name' => sanitize_text_field( $data['donor_name'] ) ),
				'email_address' => sanitize_email( $data['donor_email'] ),
			),
			'application_context' => array(
				'brand_name' => get_bloginfo( 'name' ),
				'locale' => 'en-US',
				'user_action' => 'SUBSCRIBE_NOW',
				'payment_method' => array(
					'payer_selected' => 'PAYPAL',
					'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED',
				),
				'return_url' => $return_url,
				'cancel_url' => $cancel_url,
			),
			'custom_id' => (string) $donation_id,
		);
		$response = $client->create_subscription( $subscription_data );
		if ( ! $response['ok'] || empty( $response['body']['id'] ) ) {
			return new \WP_Error( 'paypal_subscription_error', __( 'Failed to create PayPal subscription', 'giftflow' ) );
		}
		$body = $response['body'];
		$approval_url = '';
		foreach ( isset( $body['links'] ) && is_array( $body['links'] ) ? $body['links'] : array() as $link ) {
			if ( isset( $link['rel'], $link['href'] ) && 'approve' === $link['rel'] ) {
				$approval_url = $link['href'];
				break;
			}
		}
		if ( '' === $approval_url ) {
			return new \WP_Error( 'paypal_subscription_error', __( 'PayPal did not return an approval URL', 'giftflow' ) );
		}
		return array(
			'subscription_id' => $body['id'],
			'approval_url' => $approval_url,
			'raw_response' => $body,
		);
	}

	/**
	 * Get PayPal subscription details.
	 *
	 * @param string $subscription_id PayPal Subscription ID.
	 * @param int    $donation_id     Donation post id used to resolve the environment.
	 * @return array|\WP_Error Subscription data or WP_Error.
	 */
	private function get_paypal_subscription( $subscription_id, $donation_id = 0 ) {
		$environment = $donation_id ? $this->resolve_donation_environment( (int) $donation_id ) : $this->get_active_environment();
		$client = $this->client_for_environment( $environment );
		if ( ! $client ) {
			return new \WP_Error( 'paypal_token_error', __( 'Failed to authenticate with PayPal', 'giftflow' ) );
		}
		$response = $client->get_subscription( $subscription_id );
		return $response['ok'] ? $response['body'] : new \WP_Error( 'paypal_subscription_error', __( 'Failed to retrieve subscription', 'giftflow' ) );
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
			$environment = $this->get_active_environment();
			$client      = $this->client_for_environment( $environment );

			if ( ! $client ) {
				wp_send_json_error( array( 'message' => __( 'PayPal is not properly configured.', 'giftflow' ) ) );
			}

			$this->binding()->persist( (int) $donation_id, $environment, $client->app_identity() );

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
	 * @param int    $donation_id Donation ID.
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
			delete_post_meta( $donation_id, '_token_verification_callback_url' );

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

			// direct to home url.
			wp_safe_redirect( home_url() );
			exit;
		}

		$subscription = $this->get_paypal_subscription( $subscription_id, $donation_id );

		if ( is_wp_error( $subscription ) ) {
			$this->log_error( 'subscription_return_verify_failed', $subscription->get_error_message(), $donation_id );

			// direct to home url.
			wp_safe_redirect( home_url() );
			exit;
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

		delete_post_meta( $donation_id, '_token_verification_callback_url' );

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

		$environment = $this->resolve_donation_environment( (int) $donation_id );
		$client = $this->client_for_environment( $environment );
		if ( ! $client ) {
			wp_send_json_error( array( 'message' => __( 'Failed to authenticate with PayPal', 'giftflow' ) ) );
		}
		$response = $client->cancel_subscription( $subscription_id, __( 'Cancelled by admin via GiftFlow', 'giftflow' ) );
		if ( ! $response['ok'] ) {
			wp_send_json_error( array( 'message' => __( 'Failed to cancel subscription', 'giftflow' ) ) );
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
		return PayPalWebhookProvisioner::normalize_url( admin_url( 'admin-ajax.php?action=giftflow_paypal_webhook' ) );
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
