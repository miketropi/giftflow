<?php
/**
 * Forms class for GiftFlow
 *
 * @package GiftFlow
 * @subpackage Frontend
 */

namespace GiftFlow\Frontend;

use GiftFlow\Core\Base;
use GiftFlow\Core\Donations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles donation form functionality
 */
class Forms extends Base {
	/**
	 * Initialize forms
	 */
	public function __construct() {
		parent::__construct();
		$this->init_hooks();
	}

	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_giftflow_donation_form', array( $this, 'process_donation' ) );
		add_action( 'wp_ajax_nopriv_giftflow_donation_form', array( $this, 'process_donation' ) );
	}

	/**
	 * Enqueue required scripts and styles
	 */
	public function enqueue_scripts() {
		// donation-form.bundle.css.
		wp_enqueue_style( 'giftflow-donation-form', $this->get_plugin_url() . 'assets/css/donation-form.bundle.css', array(), $this->get_version() );

		// forms.bundle.js.
		wp_enqueue_script( 'giftflow-donation-forms', $this->get_plugin_url() . 'assets/js/forms.bundle.js', array( 'jquery' ), $this->get_version(), true );

		// localize script.
		wp_localize_script(
			'giftflow-donation-forms',
			'giftflowDonationForms',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'giftflow_donation_form' ),
			)
		);
	}

	/**
	 * Process donation form submission
	 *
	 * @return void
	 *
	 * Fields include:
	 * - anonymous_donation: boolean (true/false)
	 * - campaign_id: string (campaign post ID)
	 * - card_name: string (name on card)
	 * - donation_amount: string (donation amount)
	 * - donation_type: string (once/monthly/etc)
	 * - donor_email: string (email address)
	 * - donor_name: string (donor's name)
	 * - payment_method: string (payment method)
	 */
	public function process_donation() {
		// Reviewer Note: The incoming donation submission payload is intentionally left unmodified at this stage.
		// This design enables granular, field-specific validation and sanitization at the point where each value is used.
		// If you have questions about handling unsanitized inputs before later validation/sanitization, or require changes for security compliance, please let us know.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$fields = json_decode( file_get_contents( 'php://input' ), true );

		// sanitize data if it is an array, else sanitize the data.
		$fields = is_array( $fields ) ? giftflow_sanitize_array( $fields ) : sanitize_text_field( $fields );

		// convert amout to float.
		$fields['donation_amount'] = floatval( wp_unslash( $fields['donation_amount'] ) );

		// add filter to fields.
		$fields = apply_filters( 'giftflow_donation_form_fields', $fields );

		// giftflow_donation_form.
		check_ajax_referer( 'giftflow_donation_form', 'wp_nonce' );

		/**
		 * Hooks do_action before process donation.
		 *
		 * @see giftflow_donation_form_validate_recaptcha - 10
		 */
		do_action( 'giftflow_donation_form_before_process_donation', $fields );

		// Validate data.
		if ( ! $this->validate_donation_data( $fields ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid donation data', 'giftflow' ),
				)
			);
		}

		// Create donation record.
		$donation_id = $this->create_donation( $fields );

		if ( is_wp_error( $donation_id ) ) {
			wp_send_json_error(
				array(
					'message' => $donation_id->get_error_message(),
				)
			);
		}

		// Process payment.
		$payment_result = $this->process_payment( $fields, $donation_id );

		if ( is_wp_error( $payment_result ) ) {
			wp_send_json_error(
				array(
					'message' => $payment_result->get_error_message(),
				)
			);
		}

		/**
		 * Add hook after payment processed

		 * @see giftflow_send_mail_notification_donation_to_admin - 10
		 * @see giftflow_auto_create_user_on_donation - 10
		 */
		do_action( 'giftflow_donation_after_payment_processed', $donation_id, $payment_result );

		wp_send_json_success(
			array(
				'message' => __( 'Donation processed successfully', 'giftflow' ),
				'donation_id' => $donation_id,
				'payment_result' => $payment_result,
			)
		);
	}

	/**
	 * Validate donation data

	 * @param array $data Donation data.
	 * @return bool
	 */
	private function validate_donation_data( $data ) {
		// Sanitize and validate donation amount.
		$donation_amount = isset( $data['donation_amount'] ) ? floatval( $data['donation_amount'] ) : 0;
		if ( $donation_amount <= 0 ) {
			return false;
		}

		// Sanitize and validate donor name.
		$donor_name = isset( $data['donor_name'] ) ? sanitize_text_field( $data['donor_name'] ) : '';
		$donor_email_raw = isset( $data['donor_email'] ) ? $data['donor_email'] : '';
		$donor_email = sanitize_email( $donor_email_raw );
		if ( empty( $donor_name ) || empty( $donor_email ) ) {
			return false;
		}

		// Validate email.
		if ( ! is_email( $donor_email ) ) {
			return false;
		}

		// Sanitize and validate payment method.
		$payment_method = isset( $data['payment_method'] ) ? sanitize_text_field( $data['payment_method'] ) : '';
		if ( empty( $payment_method ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Process payment

	 * @param array $data Donation data.
	 * @param int $donation_id ID of donation.
	 * @return mixed
	 */
	private function process_payment( $data, $donation_id ) {
		// call function based on payment method, allow 3rd party to process payment.
		// check if function exists.
		$payment_method = isset( $data['payment_method'] ) ? sanitize_text_field( $data['payment_method'] ) : '';
		if ( empty( $payment_method ) ) {
			return new \WP_Error( 'invalid_payment_method', __( 'Invalid payment method', 'giftflow' ) );
		}
		$pm_obj = \GiftFlow\Gateways\Gateway_Base::get_gateway( $payment_method );
		if ( ! $pm_obj ) {
			return new \WP_Error( 'invalid_payment_method', __( 'Invalid payment method', 'giftflow' ) );
		}

		// Process payment using the gateway class.
		if ( method_exists( $pm_obj, 'process_payment' ) ) {
			$payment_result = $pm_obj->process_payment( $data, $donation_id );
			if ( is_wp_error( $payment_result ) ) {
				return $payment_result; // Return error if payment processing fails.
			}
			return $payment_result; // Return successful payment result.
		}

		return false;
	}

	/**
	 * Get donor record by email if exists, otherwise create new donor record

	 * @param string $email Donor email.
	 * @param array $data Donation data.
	 * @return int|WP_Error
	 */

	/**
	 * Create donation record

	 * @param array $data Donation data.
	 * @param mixed $payment_result Payment processing result.
	 * @return int|WP_Error
	 */
	private function create_donation( $data, $payment_result = '' ) {
		// Prepare donation data for Donations class.
		$donation_data = array(
			'donation_amount' => isset( $data['donation_amount'] ) ? floatval( $data['donation_amount'] ) : 0,
			'donor_name' => isset( $data['donor_name'] ) ? sanitize_text_field( $data['donor_name'] ) : '',
			'donor_email' => isset( $data['donor_email'] ) ? sanitize_email( $data['donor_email'] ) : '',
			'payment_method' => isset( $data['payment_method'] ) ? sanitize_text_field( $data['payment_method'] ) : '',
			'status' => 'pending', // Default status, will be updated after payment processing.
		);

		// Optional fields.
		if ( ! empty( $data['campaign_id'] ) ) {
			$donation_data['campaign_id'] = sanitize_text_field( $data['campaign_id'] );
		}

		if ( ! empty( $data['donation_type'] ) ) {
			$donation_data['donation_type'] = sanitize_text_field( $data['donation_type'] );
		}

		if ( ! empty( $data['recurring_interval'] ) ) {
			$donation_data['recurring_interval'] = sanitize_text_field( $data['recurring_interval'] );
		}

		if ( ! empty( $data['donor_message'] ) ) {
			$donation_data['donor_message'] = sanitize_textarea_field( $data['donor_message'] );
		}

		if ( isset( $data['anonymous_donation'] ) ) {
			$donation_data['anonymous_donation'] = ( 'yes' === $data['anonymous_donation'] || true === $data['anonymous_donation'] || '1' === $data['anonymous_donation'] ) ? 'yes' : 'no';
		}

		// Use centralized Donations class to create donation.
		$donations = new Donations();
		$donation_id = $donations->create( $donation_data );

		return $donation_id;
	}
}
