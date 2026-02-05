<?php
/**
 * PayPal Payment Gateway Template
 *
 * @package GiftFlow
 * @subpackage Templates
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$mode = isset( $mode ) ? $mode : '';
$icons = array(
	'error' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-alert-icon lucide-circle-alert"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>',
	'checked' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-badge-check-icon lucide-badge-check"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m9 12 2 2 4-4"/></svg>',
);
?>
<label class="donation-form__payment-method">
	<input type="radio" name="payment_method" value="<?php echo esc_attr( $id ); ?>" required>
	<span class="donation-form__payment-method-content">
	<?php echo wp_kses( $icon, giftflow_allowed_svg_tags() ); ?>
	<span class="donation-form__payment-method-title"><?php echo esc_html( $title ); ?></span>
	</span>
</label>
<div class="donation-form__payment-method-description donation-form__payment-method-description--paypal donation-form__fields">
	<div class="donation-form__payment-notification">
	<span class="notification-icon"><?php echo wp_kses( $icons['checked'], giftflow_allowed_svg_tags() ); ?></span>
	<div class="notification-message-entry">
		<p><?php esc_html_e( 'Pay securely with PayPal. Click the button below to complete your payment.', 'giftflow' ); ?></p>

		<?php if ( 'sandbox' === $mode ) { ?>
		<hr />
		<div role="alert">
		<p>
			<strong><?php esc_html_e( 'You are currently in PayPal Sandbox Mode.', 'giftflow' ); ?></strong>
			<?php esc_html_e( 'This is a test environment. No real payments will be processed.', 'giftflow' ); ?>
		</p>
		</div>
		<?php } ?>
	</div>
	</div>

	<?php // PayPal buttons container. ?>
	<div 
	class="donation-form__field"
	data-custom-validate="true" 
	data-custom-validate-status="false" >
	<div 
		class="donation-form__field-error custom-error-message" 
		style="margin-bottom: 10px;align-items: center; justify-content: center;">
		<?php echo wp_kses( $icons['error'], giftflow_allowed_svg_tags() ); ?>
		<span class="custom-error-message-text">
		<?php esc_html_e( 'Please complete the payment process with PayPal.', 'giftflow' ); ?>
		</span>
	</div>
	<div id="giftflow-paypal-button-container"></div>
	</div>
</div>