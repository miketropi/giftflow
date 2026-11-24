<?php
/**
 * Render callback for the Thank Donor block.
 *
 * @package GiftFlow
 * @subpackage Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gf_heading      = $attributes['heading'] ?? __( 'Thank You!', 'giftflow' );
$gf_message      = $attributes['message'] ?? __( 'Your donation has been received.', 'giftflow' );
$gf_account_note = $attributes['accountNotice'] ?? '';
$gf_show_account = $attributes['showAccountNotice'] ?? true;
$gf_button_text  = $attributes['buttonText'] ?? __( 'View My Donations', 'giftflow' );
$gf_button_url   = $attributes['buttonUrl'] ?? '';
$gf_show_button  = $attributes['showButton'] ?? true;

if ( empty( $gf_button_url ) && $gf_show_button ) {
	$gf_button_url = giftflow_thank_donor_block_default_button_url();
}

$gf_message      = wp_kses_post( wpautop( $gf_message ) );
$gf_account_note = wp_kses_post( wpautop( $gf_account_note ) );

$block_wrapper_attrs = get_block_wrapper_attributes(
	array( 'class' => 'giftflow-thank-donor' )
);
?>
<div <?php echo $block_wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes returns safe HTML. ?> aria-labelledby="giftflow-thank-donor-heading">
	<div class="giftflow-thank-donor__icon" aria-hidden="true">
		<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
			<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
			<polyline points="22 4 12 14.01 9 11.01" />
		</svg>
	</div>

	<h2 id="giftflow-thank-donor-heading" class="giftflow-thank-donor__heading">
		<?php echo esc_html( $gf_heading ); ?>
	</h2>

	<div class="giftflow-thank-donor__message">
		<?php echo $gf_message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped via wp_kses_post. ?>
	</div>

	<?php if ( $gf_show_account && ! empty( $gf_account_note ) ) : ?>
		<div class="giftflow-thank-donor__notice" role="note">
			<?php echo $gf_account_note; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped via wp_kses_post. ?>
		</div>
	<?php endif; ?>

	<?php if ( $gf_show_button && ! empty( $gf_button_text ) ) : ?>
		<div class="giftflow-thank-donor__action">
			<a href="<?php echo esc_url( $gf_button_url ); ?>" class="giftflow-thank-donor__btn">
				<?php echo esc_html( $gf_button_text ); ?>
			</a>
		</div>
	<?php endif; ?>
</div>
