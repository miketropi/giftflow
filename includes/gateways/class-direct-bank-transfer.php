<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Direct Bank Transfer Payment Gateway for GiftFlow
 * This class implements direct bank transfer payment processing.
 * Payments are marked as pending until manually confirmed.
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
 * Direct Bank Transfer Gateway Class
 */
class Direct_Bank_Transfer_Gateway extends Gateway_Base {

	/**
	 * Initialize gateway properties
	 */
	protected function init_gateway() {
		$this->id          = 'direct_bank_transfer';
		$this->title       = __( 'Direct Bank Transfer', 'giftflow' );
		$this->description = __( 'Make a payment directly into our bank account', 'giftflow' );

		// SVG icon.
		$this->icon = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-landmark-icon lucide-landmark"><path d="M10 18v-7"/><path d="M11.12 2.198a2 2 0 0 1 1.76.006l7.866 3.847c.476.233.31.949-.22.949H3.474c-.53 0-.695-.716-.22-.949z"/><path d="M14 18v-7"/><path d="M18 18v-7"/><path d="M3 22h18"/><path d="M6 18v-7"/></svg>';

		$this->order    = 20;
		$this->supports = array();
	}

	/**
	 * Additional initialization after gateway setup
	 */
	protected function ready() {
		// Any additional setup needed.
	}

	/**
	 * Get gateway settings fields
	 *
	 * @param array $payment_fields Existing payment fields.
	 * @return array
	 */
	public function register_settings_fields( $payment_fields = array() ) {
		$payment_options                        = get_option( 'giftflow_payment_options' );
		$payment_fields['direct_bank_transfer'] = array(
			'id'                 => 'giftflow_direct_bank_transfer',
			'name'               => 'giftflow_payment_options[direct_bank_transfer]',
			'type'               => 'accordion',
			'label'              => __( 'Direct Bank Transfer', 'giftflow' ),
			'description'        => __( 'Configure direct bank transfer settings', 'giftflow' ),
			'accordion_settings' => array(
				'label'   => __( 'Direct Bank Transfer Settings', 'giftflow' ),
				'is_open' => true,
				'fields'  => array(
					'direct_bank_transfer_enabled' => array(
						'id'          => 'giftflow_direct_bank_transfer_enabled',
						'type'        => 'switch',
						'label'       => __( 'Enable Direct Bank Transfer', 'giftflow' ),
						'value'       => isset( $payment_options['direct_bank_transfer']['direct_bank_transfer_enabled'] ) ? $payment_options['direct_bank_transfer']['direct_bank_transfer_enabled'] : false,
						'description' => __( 'Enable direct bank transfer as a payment method', 'giftflow' ),
					),
					'bank_account_name'            => array(
						'id'          => 'giftflow_bank_account_name',
						'type'        => 'textfield',
						'label'       => __( 'Account Name', 'giftflow' ),
						'value'       => isset( $payment_options['direct_bank_transfer']['bank_account_name'] ) ? $payment_options['direct_bank_transfer']['bank_account_name'] : '',
						'description' => __( 'Enter the bank account name', 'giftflow' ),
					),
					'bank_account_number'          => array(
						'id'          => 'giftflow_bank_account_number',
						'type'        => 'textfield',
						'label'       => __( 'Account Number', 'giftflow' ),
						'value'       => isset( $payment_options['direct_bank_transfer']['bank_account_number'] ) ? $payment_options['direct_bank_transfer']['bank_account_number'] : '',
						'description' => __( 'Enter the bank account number', 'giftflow' ),
					),
					'bank_routing_number'          => array(
						'id'          => 'giftflow_bank_routing_number',
						'type'        => 'textfield',
						'label'       => __( 'Routing Number', 'giftflow' ),
						'value'       => isset( $payment_options['direct_bank_transfer']['bank_routing_number'] ) ? $payment_options['direct_bank_transfer']['bank_routing_number'] : '',
						'description' => __( 'Enter the bank routing number', 'giftflow' ),
					),
					'bank_name'                    => array(
						'id'          => 'giftflow_bank_name',
						'type'        => 'textfield',
						'label'       => __( 'Bank Name', 'giftflow' ),
						'value'       => isset( $payment_options['direct_bank_transfer']['bank_name'] ) ? $payment_options['direct_bank_transfer']['bank_name'] : '',
						'description' => __( 'Enter the bank name', 'giftflow' ),
					),
					'bank_iban'                    => array(
						'id'          => 'giftflow_bank_iban',
						'type'        => 'textfield',
						'label'       => __( 'IBAN', 'giftflow' ),
						'value'       => isset( $payment_options['direct_bank_transfer']['bank_iban'] ) ? $payment_options['direct_bank_transfer']['bank_iban'] : '',
						'description' => __( 'Enter the IBAN (International Bank Account Number)', 'giftflow' ),
					),
					'bank_swift'                   => array(
						'id'          => 'giftflow_bank_swift',
						'type'        => 'textfield',
						'label'       => __( 'SWIFT/BIC Code', 'giftflow' ),
						'value'       => isset( $payment_options['direct_bank_transfer']['bank_swift'] ) ? $payment_options['direct_bank_transfer']['bank_swift'] : '',
						'description' => __( 'Enter the SWIFT/BIC code', 'giftflow' ),
					),
					'instructions'                 => array(
						'id'          => 'giftflow_bank_transfer_instructions',
						'type'        => 'textarea',
						'label'       => __( 'Instructions', 'giftflow' ),
						'value'       => isset( $payment_options['direct_bank_transfer']['instructions'] ) ? $payment_options['direct_bank_transfer']['instructions'] : '',
						'description' => __( 'Instructions to display to donors on how to complete the bank transfer', 'giftflow' ),
					),
				),
			),
		);

		return $payment_fields;
	}

	/**
	 * Get payment form HTML
	 *
	 * @return void
	 */
	public function template_html() {

		giftflow_load_template(
			'payment-gateway/direct-bank-transfer-template.php',
			array(
				'id' => $this->id,
				'title' => $this->title,
				'icon' => $this->icon,
				'instructions' => $this->get_setting( 'instructions' ),
				'bank_account_name' => $this->get_setting( 'bank_account_name' ),
				'bank_account_number' => $this->get_setting( 'bank_account_number' ),
				'bank_routing_number' => $this->get_setting( 'bank_routing_number' ),
				'bank_name' => $this->get_setting( 'bank_name' ),
				'bank_iban' => $this->get_setting( 'bank_iban' ),
				'bank_swift' => $this->get_setting( 'bank_swift' ),
				'reference_number' => $this->generate_reference_number(),
			)
		);
	}

	/**
	 * Process payment
	 *
	 * @param array $data Payment data.
	 * @param int   $donation_id Donation ID.
	 * @return mixed
	 */
	public function process_payment( $data, $donation_id = 0 ) {
		if ( ! $donation_id ) {
			return new \WP_Error( 'bank_transfer_error', __( 'Donation ID is required', 'giftflow' ) );
		}

		try {
			// Mark donation as pending - payment will be confirmed manually.
			update_post_meta( $donation_id, '_payment_method', 'direct_bank_transfer' );

			// Use centralized Donations class to update status.
			$donations_class = new Donations();
			$donations_class->update_status( $donation_id, 'pending' );

			update_post_meta( $donation_id, '_bank_transfer_pending', 'yes' );

			// Store payment data.
			update_post_meta( $donation_id, '_donation_amount', floatval( $data['donation_amount'] ) );

			// Generate a unique reference number for this donation.
			$reference_number = isset( $data['reference_number'] ) ? $data['reference_number'] : '';
			update_post_meta( $donation_id, '_reference_number', $reference_number );

			// Log the pending payment.
			Donation_Event_History::add(
				$donation_id,
				'payment_pending',
				'pending',
				'',
				array(
					'reference_number' => $reference_number,
					'gateway' => 'direct_bank_transfer',
				)
			);
			$this->log_pending_payment( $donation_id, $reference_number );

			// Fire action for pending payment.
			do_action( 'giftflow_bank_transfer_payment_pending', $donation_id, $reference_number, $data );

			return true;

		} catch ( \Exception $e ) {
			Donation_Event_History::add( $donation_id, 'payment_failed', 'failed', $e->getMessage(), array( 'gateway' => 'direct_bank_transfer' ) );
			$this->log_error( 'payment_exception', $e->getMessage(), $donation_id );
			return new \WP_Error( 'bank_transfer_error', $e->getMessage() );
		}
	}

	/**
	 * Generate a unique reference number for the donation
	 *
	 * @return string
	 */
	private function generate_reference_number() {
		// Generate a unique reference: {RANDOM}-{TIMESTAMP}.
		$random = wp_generate_password( 2, false );
		return sprintf( '%s-%s', strtoupper( $random ), time() );
	}

	/**
	 * Log pending payment
	 *
	 * @param int    $donation_id Donation ID.
	 * @param string $reference_number Reference number.
	 */
	private function log_pending_payment( $donation_id, $reference_number ) {
		Giftflow_Logger::info(
			'direct_bank_transfer.payment.pending',
			array(
				'donation_id'      => $donation_id,
				'reference_number' => $reference_number,
				'gateway'          => 'direct_bank_transfer',
			),
			'direct_bank_transfer'
		);
	}

	/**
	 * Log error
	 *
	 * @param string $type Type of error.
	 * @param string $message Message of error.
	 * @param int    $donation_id Donation ID.
	 */
	private function log_error( $type, $message, $donation_id ) {
		Giftflow_Logger::error(
			'direct_bank_transfer.payment.failed',
			array(
				'type'          => $type,
				'donation_id'   => $donation_id,
				'error_message' => $message,
				'gateway'       => 'direct_bank_transfer',
			),
			'direct_bank_transfer'
		);
	}
}

// register direct bank transfer gateway.
add_action(
	'giftflow_register_gateways',
	function () {
		new \GiftFlow\Gateways\Direct_Bank_Transfer_Gateway();
	}
);

/**
 * Helper function to get Direct Bank Transfer Gateway instance
 *
 * @return Direct_Bank_Transfer_Gateway|null
 */
// phpcs:ignore Universal.Files.SeparateFunctionsFromOO.Mixed, Squiz.Commenting.FunctionComment.Missing
function giftflow_get_direct_bank_transfer_gateway() {
	return Gateway_Base::get_gateway( 'direct_bank_transfer' );
}
