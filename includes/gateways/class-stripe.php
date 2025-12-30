<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Stripe Payment Gateway for GiftFlow
 *
 * This class implements Stripe payment processing using Omnipay v3
 * with support for Payment Intents, 3D Secure, and webhooks.
 *
 * @package GiftFlow
 * @subpackage Gateways
 * @since 1.0.0
 * @version 1.0.0
 */

namespace GiftFlow\Gateways;

use Omnipay\Omnipay;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stripe Gateway Class
 */
class Stripe_Gateway extends Gateway_Base {
	/**
	 * Omnipay gateway instance.
	 *
	 * @var PaymentIntentsGateway
	 */
	private $gateway;

	/**
	 * API key.
	 *
	 * @var string
	 */
	private $api_key;

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
		// Initialize Omnipay gateway.
		$this->init_omnipay_gateway();

		// Add Stripe-specific assets.
		$this->add_stripe_assets();
	}

	/**
	 * Initialize Omnipay gateway
	 *
	 * @return void
	 */
	private function init_omnipay_gateway() {
		$this->api_key = $this->get_api_key();
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
				),
			),
		);

		return $payment_fields;
	}

	/**
	 * Template HTML
	 *
	 * @return string
	 */
	public function template_html() {

			// get stripe_mode.
			$mode = $this->get_setting( 'stripe_mode' );

			ob_start();
			$icons = array(
				'error' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-alert-icon lucide-circle-alert"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>',
				'checked' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-badge-check-icon lucide-badge-check"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m9 12 2 2 4-4"/></svg>',
			);

			?>
			<label class="donation-form__payment-method">
					<input type="radio" checked name="payment_method" value="<?php echo esc_attr( $this->id ); ?>" required>
					<span class="donation-form__payment-method-content">
							<?php echo wp_kses( $this->icon, giftflow_allowed_svg_tags() ); ?>
							<span class="donation-form__payment-method-title"><?php echo esc_html( $this->title ); ?></span>
					</span>
			</label>
			<div 
					class="donation-form__payment-method-description donation-form__payment-method-description--stripe donation-form__fields" 
					>
					<div class="donation-form__payment-notification">
							<span class="notification-icon"><?php echo wp_kses( $icons['checked'], giftflow_allowed_svg_tags() ); ?></span>
							<div class="notification-message-entry">
									<p><?php esc_html_e( 'We use Stripe to process payments. Your payment information is encrypted and never stored on our servers.', 'giftflow' ); ?></p>

									<?php if ( 'sandbox' === $mode ) { ?>
									<hr />
									<div role="alert">
											<p>
													<strong><?php esc_html_e( 'You are currently in Stripe Sandbox Mode.', 'giftflow' ); ?></strong>
													<?php esc_html_e( 'To test your payment, use the test card number', 'giftflow' ); ?> <code class="gfw-monofont">4242 4242 4242 4242</code>
													<?php esc_html_e( 'with any CVC and any valid future expiration date.', 'giftflow' ); ?>
													<?php esc_html_e( 'This will simulate a successful payment.', 'giftflow' ); ?>
											</p>
									</div>
									<?php } ?>
							</div>
					</div>

					<?php // name on card field. ?>
					<div class="donation-form__field">
							<label for="card_name" class="donation-form__field-label"><?php esc_html_e( 'Name on card', 'giftflow' ); ?></label>
							<input type="text" id="card_name" name="card_name" class="donation-form__field-input" data-validate="required">

							<div class="donation-form__field-error custom-error-message">
							<?php echo wp_kses( $icons['error'], giftflow_allowed_svg_tags() ); ?>
							<span class="custom-error-message-text">
									<?php esc_html_e( 'Name on card is required', 'giftflow' ); ?>
							</span>
							</div>
					</div>
					
					<?php // card element. ?>
					<div 
							class="donation-form__field" 
							data-custom-validate="true" 
							data-custom-validate-status="false" >
							<label for="card_number" class="donation-form__field-label"><?php esc_html_e( 'Card number', 'giftflow' ); ?></label>
							<div id="STRIPE-CARD-ELEMENT"></div> <?php // Render card via stripe.js. ?>

							<div class="donation-form__field-error custom-error-message">
							<?php echo wp_kses( $icons['error'], giftflow_allowed_svg_tags() ); ?>
							<span class="custom-error-message-text">
									<?php esc_html_e( 'Card information is incomplete', 'giftflow' ); ?>
							</span>
							</div>
					</div>
			</div>
			<?php
			return ob_get_clean();
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
		if ( ! empty( $this->api_key ) ) {
			$this->gateway = Omnipay::create( 'Stripe' );
			$this->gateway->setApiKey( $this->api_key );
		}

		if ( ! $this->gateway ) {
			return new \WP_Error( 'stripe_error', __( 'Stripe is not properly configured', 'giftflow' ) );
		}

		if ( ! $donation_id ) {
			return new \WP_Error( 'stripe_error', __( 'Donation ID is required', 'giftflow' ) );
		}

		try {
			$payment_data = $this->prepare_payment_data( $data, $donation_id );
			$response = $this->gateway->purchase( $payment_data )->send();

			return $this->handle_payment_response( $response, $donation_id );
		} catch ( \Exception $e ) {
			$this->log_error( 'payment_exception', $e->getMessage(), $donation_id );
			return new \WP_Error( 'stripe_error', $e->getMessage() );
		}
	}

	/**
	 * Prepare payment data for Omnipay
	 *
	 * @param array $data Payment data.
	 * @param int $donation_id Donation ID.
	 * @return array
	 */
	private function prepare_payment_data( $data, $donation_id ) {
		$statement_descriptor = $this->get_setting( 'statement_descriptor', get_bloginfo( 'name' ) );
		$statement_descriptor = substr( $statement_descriptor, 0, 22 ); // Stripe limit.

		$stripe_data = array(
			'amount' => number_format( (float) $data['donation_amount'], 2, '.', '' ),
			'currency' => strtolower( $this->get_currency() ),
			'token' => $data['stripe_token'],
			// translators: 1: donor name, 2: campaign id or name.
			'description' => sprintf( __( 'Donation from %1$s for campaign %2$s', 'giftflow' ), sanitize_text_field( $data['donor_name'] ), $data['campaign_id'] ),
			'metadata' => array(
				'donation_id' => $donation_id,
				'campaign_id' => $data['campaign_id'],
				'donor_email' => sanitize_email( $data['donor_email'] ),
				'donor_name' => sanitize_text_field( $data['donor_name'] ),
				'site_url' => home_url(),
			),
		);

		return apply_filters( 'giftflow_stripe_prepare_payment_data', $stripe_data, $data, $donation_id );
	}

	/**
	 * Handle payment response
	 *
	 * @param mixed $response Response from Stripe.
	 * @param int $donation_id Donation ID.
	 * @return array|\WP_Error
	 */
	private function handle_payment_response( $response, $donation_id ) {
		if ( $response->isSuccessful() ) {
				return $this->handle_successful_payment( $response, $donation_id );
		} elseif ( $response->isRedirect() ) {
				return $this->handle_redirect_payment( $response, $donation_id );
		} elseif ( method_exists( $response, 'requiresAction' ) && $response->requiresAction() ) {
				return $this->handle_action_required( $response, $donation_id );
		} else {
				return $this->handle_failed_payment( $response, $donation_id );
		}
	}

	/**
	 * Handle successful payment
	 *
	 * @param mixed $response Response from Stripe.
	 * @param int $donation_id Donation ID.
	 * @return array
	 */
	private function handle_successful_payment( $response, $donation_id ) {
			$transaction_id = $response->getTransactionReference();
			$all_data = $response->getData();

			// Update donation meta.
			update_post_meta( $donation_id, '_transaction_id', $transaction_id );
			update_post_meta( $donation_id, '_transaction_raw_data', wp_json_encode( $all_data ) );
			update_post_meta( $donation_id, '_payment_method', 'stripe' );
			update_post_meta( $donation_id, '_status', 'completed' );

			$this->log_success( $payment_intent_id, $donation_id );

			do_action( 'giftflow_stripe_payment_completed', $donation_id, $payment_intent_id, $all_data );

			// return true when payment is successful.
			return true;
	}

	/**
	 * Handle redirect payment (3D Secure)
	 *
	 * @param mixed $response Response from Stripe.
	 * @param int $donation_id Donation ID.
	 * @return array
	 */
	private function handle_redirect_payment( $response, $donation_id ) {
			$payment_intent_id = $response->getPaymentIntentReference();

			// Store payment intent for later verification.
			update_post_meta( $donation_id, '_stripe_payment_intent_id', $payment_intent_id );
			update_post_meta( $donation_id, '_payment_status', 'processing' );

			return array(
				'success' => false,
				'redirect' => true,
				'redirect_url' => $response->getRedirectUrl(),
				'message' => __( '3D Secure authentication required', 'giftflow' ),
			);
	}

	/**
	 * Handle action required payment
	 *
	 * @param mixed $response Response from Stripe.
	 * @param int $donation_id Donation ID.
	 * @return array
	 */
	private function handle_action_required( $response, $donation_id ) {
		$client_secret = method_exists( $response, 'getPaymentIntentClientSecret' )
			? $response->getPaymentIntentClientSecret()
			: '';

		return array(
			'success' => false,
			'requires_action' => true,
			'client_secret' => $client_secret,
			'message' => esc_html__( 'Payment requires additional authentication', 'giftflow' ),
		);
	}

	/**
	 * Handle failed payment
	 *
	 * @param mixed $response Response from Stripe.
	 * @param int $donation_id Donation ID.
	 * @return \WP_Error
	 */
	private function handle_failed_payment( $response, $donation_id ) {
		$error_message = $response->getMessage() ? $response->getMessage() : esc_html__( 'Payment failed', 'giftflow' );
		$error_code = method_exists( $response, 'getCode' ) ? $response->getCode() : '';

		$this->log_error( 'payment_failed', $error_message, $donation_id, $error_code );

		update_post_meta( $donation_id, '_payment_status', 'failed' );
		update_post_meta( $donation_id, '_payment_error', $error_message );

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

			$payload = file_get_contents( 'php://input' );
			$event = json_decode( $payload, true );

		if ( ! $event || ! isset( $event['type'] ) ) {
				status_header( 400 );
				exit;
		}

		try {
			switch ( $event['type'] ) {
				case 'payment_intent.succeeded':
					$this->handle_payment_intent_succeeded( $event['data']['object'] );
					break;
				case 'payment_intent.payment_failed':
					$this->handle_payment_intent_failed( $event['data']['object'] );
					break;
				case 'charge.refunded':
					// Handle charge refunded if needed.
					$this->handle_payment_charge_refunded( $event['data']['object'] );
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
	 * Handle return URL from 3D Secure
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
				'meta_key' => '_transaction_id',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'meta_value' => $payment_intent_id,
				'posts_per_page' => 1,
			)
		);

		if ( ! empty( $donations ) ) {
			$donation_id = $donations[0]->ID;

			// Verify payment intent status.
			try {
				$response = $this->gateway->completePurchase(
					array(
						'paymentIntentReference' => $payment_intent_id,
					)
				)->send();

				if ( $response->isSuccessful() ) {
					$this->handle_successful_payment( $response, $donation_id );
					wp_safe_redirect( esc_url( add_query_arg( 'payment_status', 'success', home_url() ) ) );
				} else {
					$this->handle_failed_payment( $response, $donation_id );
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
	 * @param array $payment_intent Payment intent data.
	 */
	private function handle_payment_intent_succeeded( $payment_intent ) {
		$donation_id = isset( $payment_intent['metadata']['donation_id'] )
			? intval( $payment_intent['metadata']['donation_id'] )
			: 0;

		if ( $donation_id ) {
			update_post_meta( $donation_id, '_status', 'completed' );
			do_action( 'giftflow_stripe_webhook_payment_completed', $donation_id, $payment_intent );
		}
	}

	/**
	 * Handle failed payment intent webhook
	 *
	 * @param array $payment_intent Payment intent data.
	 */
	private function handle_payment_intent_failed( $payment_intent ) {
			$donation_id = isset( $payment_intent['metadata']['donation_id'] )
				? intval( $payment_intent['metadata']['donation_id'] )
				: 0;

		if ( $donation_id ) {
			$error_message = isset( $payment_intent['last_payment_error']['message'] )
				? $payment_intent['last_payment_error']['message']
				: __( 'Payment failed', 'giftflow' );

			update_post_meta( $donation_id, '_status', 'failed' );
			update_post_meta( $donation_id, '_payment_error', $error_message );

			do_action( 'giftflow_stripe_webhook_payment_failed', $donation_id, $payment_intent );
		}
	}

	/**
	 * Handle payment charge refunded
	 *
	 * @param array $charge Charge data.
	 */
	private function handle_payment_charge_refunded( $charge ) {
		$donation_id = isset( $charge['metadata']['donation_id'] )
			? intval( $charge['metadata']['donation_id'] )
			: 0;

		if ( $donation_id ) {
			update_post_meta( $donation_id, '_status', 'refunded' );
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
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}

		$log_data = array(
			'action' => 'stripe_payment_success',
			'donation_id' => $donation_id,
			'transaction_id' => $transaction_id,
			'timestamp' => current_time( 'mysql' ),
		);

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( '[GiftFlow Stripe Success] ' . wp_json_encode( $log_data ) );
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
		$log_data = array(
			'action' => 'stripe_payment_error',
			'type' => $type,
			'donation_id' => $donation_id,
			'error_message' => $message,
			'error_code' => $code,
			'timestamp' => current_time( 'mysql' ),
		);

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( '[GiftFlow Stripe Error] ' . wp_json_encode( $log_data ) );
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
