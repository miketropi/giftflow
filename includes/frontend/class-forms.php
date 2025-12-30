<?php
/**
 * Forms class for GiftFlow
 *
 * @package GiftFlow
 * @subpackage Frontend
 */

namespace GiftFlow\Frontend;

use GiftFlow\Core\Base;

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
		// get fields from fetch post data.
		$fields = json_decode( file_get_contents( 'php://input' ), true );

		// convert amout to float.
		$fields['donation_amount'] = floatval( $fields['donation_amount'] );

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
		if ( $data['donation_amount'] <= 0 ) {
			return false;
		}

		if ( empty( $data['donor_name'] ) || empty( $data['donor_email'] ) ) {
			return false;
		}

		if ( ! is_email( $data['donor_email'] ) ) {
			return false;
		}

		if ( empty( $data['payment_method'] ) ) {
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
		$payment_method = $data['payment_method'];
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
	private function get_donor_id( $email, $data ) {
		// get donor record by email.
		$donor = get_posts(
			array(
				'post_type' => 'donor',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_key' => '_email',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'meta_value' => $data['donor_email'],
				// get only one record.
				'posts_per_page' => 1,
			)
		);

		if ( $donor ) {
			// return donor id.
			return $donor[0]->ID;
		} else {
			// create new donor record.
			$donor_data = array(
				'post_title' => $data['donor_name'],
				'post_type' => 'donor',
				'post_status' => 'publish',
			);

			$donor_id = wp_insert_post( $donor_data );

			// save donor email.
			update_post_meta( $donor_id, '_email', $data['donor_email'] );
			update_post_meta( $donor_id, '_first_name', $data['donor_name'] );

			// hook after create donor record.
			do_action( 'giftflow_donor_added', $donor_id );

			return $donor_id;
		}

		return false;
	}

	/**
	 * Create donation record

	 * @param array $data Donation data.
	 * @param mixed $payment_result Payment processing result.
	 * @return int|WP_Error
	 */
	private function create_donation( $data, $payment_result = '' ) {
		$donation_data = array(
			// translators: %s: Donor name.
			'post_title' => sprintf( __( 'Donation from %s', 'giftflow' ), $data['donor_name'] ),
			'post_type' => 'donation',
			'post_status' => 'publish',
		);

		$donation_id = wp_insert_post( $donation_data );

		if ( is_wp_error( $donation_id ) ) {
				return $donation_id;
		}

		// Save donation meta.
		// Amount is required.
		update_post_meta( $donation_id, '_amount', $data['donation_amount'] );

		// Campaign ID.
		if ( ! empty( $data['campaign_id'] ) ) {
				update_post_meta( $donation_id, '_campaign_id', $data['campaign_id'] );
		}

		// Payment method.
		if ( ! empty( $data['payment_method'] ) ) {
				update_post_meta( $donation_id, '_payment_method', $data['payment_method'] );
		}

		// Donation type.
		if ( ! empty( $data['donation_type'] ) ) {
				update_post_meta( $donation_id, '_donation_type', $data['donation_type'] );
		}

		// Recurring interval.
		if ( ! empty( $data['recurring_interval'] ) ) {
				update_post_meta( $donation_id, '_recurring_interval', $data['recurring_interval'] );
		}

		// Anonymous donation.
		if ( isset( $data['anonymous_donation'] ) ) {
				update_post_meta( $donation_id, '_anonymous', $data['anonymous_donation'] );
		}

		// Donor ID.
		if ( ! empty( $data['donor_email'] ) ) {
				$donor_id = $this->get_donor_id( trim( $data['donor_email'] ), $data );
			if ( $donor_id ) {
				update_post_meta( $donation_id, '_donor_id', $donor_id );
			}
		}

		// donor_message.
		if ( ! empty( $data['donor_message'] ) ) {
				update_post_meta( $donation_id, '_donor_message', $data['donor_message'] );
		}

		// anonymous donation.
		if ( ! empty( $data['anonymous_donation'] ) ) {
				update_post_meta( $donation_id, '_anonymous_donation', 'yes' );
		} else {
				update_post_meta( $donation_id, '_anonymous_donation', 'no' );
		}

		// custom field status.
		update_post_meta( $donation_id, '_status', 'pending' );

		do_action( 'giftflow_donation_created', $donation_id );

		return $donation_id;
	}
}
