<?php
/**
 * Direct Bank Transfer Payment Gateway Template
 *
 * @package GiftFlow
 * @subpackage Templates
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<label class="donation-form__payment-method">
	<input type="radio" name="payment_method" value="<?php echo esc_attr( $id ); ?>" required>
	<span class="donation-form__payment-method-content">
	<?php echo wp_kses( $icon, giftflow_allowed_svg_tags() ); ?>
	<span class="donation-form__payment-method-title"><?php echo esc_html( $title ); ?></span>
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