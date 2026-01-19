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
	 * @return string
	 */
	public function template_html() {
		ob_start();

		$icons = array(
			'checked' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-badge-check-icon lucide-badge-check"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m9 12 2 2 4-4"/></svg>',
		);

		$bank_account_name   = $this->get_setting( 'bank_account_name' );
		$bank_account_number = $this->get_setting( 'bank_account_number' );
		$bank_routing_number = $this->get_setting( 'bank_routing_number' );
		$bank_name           = $this->get_setting( 'bank_name' );
		$bank_iban           = $this->get_setting( 'bank_iban' );
		$bank_swift          = $this->get_setting( 'bank_swift' );
		$instructions        = $this->get_setting( 'instructions' );

		?>
		<label class="donation-form__payment-method">
		<input type="radio" name="payment_method" value="<?php echo esc_attr( $this->id ); ?>" required>
		<span class="donation-form__payment-method-content">
			<?php echo wp_kses( $this->icon, giftflow_allowed_svg_tags() ); ?>
			<span class="donation-form__payment-method-title"><?php echo esc_html( $this->title ); ?></span>
		</span>
		</label>
		<div class="donation-form__payment-method-description donation-form__payment-method-description--direct-bank-transfer donation-form__fields">
		<div class="donation-form__payment-notification">
			<span class="notification-icon"><?php echo wp_kses( $icons['checked'], giftflow_allowed_svg_tags() ); ?></span>
			<div class="notification-message-entry">
			<p><?php esc_html_e( 'Make your donation directly into our bank account.', 'giftflow' ); ?></p>
			<hr />
			<?php
			// Try to get reference number from request (for thank you page, user, etc.).
			$reference_number = $this->generate_reference_number();
			?>
			<p>
				<strong><?php esc_html_e( 'Important:', 'giftflow' ); ?></strong>
				<?php
					printf(
						wp_kses(
							// translators: %s is the reference number for bank transfer.
							'Please include your Reference Number (<strong class="gfw-monofont">%s</strong>) in the payment description so we can correctly identify your donation.',
							array(
								'code'   => array( 'class' => true ),
								'strong' => array( 'class' => true ),
							)
						),
						$reference_number ? esc_html( $reference_number ) : esc_html__( 'your reference number', 'giftflow' )
					);
				?>
			</p>
			</div>
			<input type="hidden" name="reference_number" value="<?php echo esc_attr( $reference_number ); ?>" />
		</div>

		<?php if ( ! empty( $instructions ) ) : ?>
			<div class="donation-form__field">
			<div class="donation-form__bank-instructions">
				<?php echo wp_kses_post( wpautop( $instructions ) ); ?>
			</div>
			</div>
		<?php endif; ?>

		<div class="donation-form__bank-details gfw-monofont">
			<?php if ( ! empty( $bank_account_name ) ) : ?>
			<div class="donation-form__bank-detail">
				<strong><?php esc_html_e( 'Account Name:', 'giftflow' ); ?></strong>
				<span><?php echo esc_html( $bank_account_name ); ?></span>
			</div>
			<?php endif; ?>

			<?php if ( ! empty( $bank_account_number ) ) : ?>
			<div class="donation-form__bank-detail">
				<strong><?php esc_html_e( 'Account Number:', 'giftflow' ); ?></strong>
				<span><?php echo esc_html( $bank_account_number ); ?></span>
			</div>
			<?php endif; ?>

			<?php if ( ! empty( $bank_routing_number ) ) : ?>
			<div class="donation-form__bank-detail">
				<strong><?php esc_html_e( 'Routing Number:', 'giftflow' ); ?></strong>
				<span><?php echo esc_html( $bank_routing_number ); ?></span>
			</div>
			<?php endif; ?>

			<?php if ( ! empty( $bank_name ) ) : ?>
			<div class="donation-form__bank-detail">
				<strong><?php esc_html_e( 'Bank Name:', 'giftflow' ); ?></strong>
				<span><?php echo esc_html( $bank_name ); ?></span>
			</div>
			<?php endif; ?>

			<?php if ( ! empty( $bank_iban ) ) : ?>
			<div class="donation-form__bank-detail">
				<strong><?php esc_html_e( 'IBAN:', 'giftflow' ); ?></strong>
				<span><?php echo esc_html( $bank_iban ); ?></span>
			</div>
			<?php endif; ?>

			<?php if ( ! empty( $bank_swift ) ) : ?>
			<div class="donation-form__bank-detail">
				<strong><?php esc_html_e( 'SWIFT/BIC:', 'giftflow' ); ?></strong>
				<span><?php echo esc_html( $bank_swift ); ?></span>
			</div>
			<?php endif; ?>
		</div>

		</div>
		<?php
		return ob_get_clean();
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
			$this->log_pending_payment( $donation_id, $reference_number );

			// Fire action for pending payment.
			do_action( 'giftflow_bank_transfer_payment_pending', $donation_id, $reference_number, $data );

			return true;

		} catch ( \Exception $e ) {
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
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}

		$log_data = array(
			'action'           => 'bank_transfer_pending',
			'donation_id'      => $donation_id,
			'reference_number' => $reference_number,
			'timestamp'        => current_time( 'mysql' ),
		);

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( '[GiftFlow Bank Transfer Pending] ' . wp_json_encode( $log_data ) );
	}

	/**
	 * Log error
	 *
	 * @param string $type Type of error.
	 * @param string $message Message of error.
	 * @param int    $donation_id Donation ID.
	 */
	private function log_error( $type, $message, $donation_id ) {
		$log_data = array(
			'action'        => 'bank_transfer_error',
			'type'          => $type,
			'donation_id'   => $donation_id,
			'error_message' => $message,
			'timestamp'     => current_time( 'mysql' ),
		);

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( '[GiftFlow Bank Transfer Error] ' . wp_json_encode( $log_data ) );
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
