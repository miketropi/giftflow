<?php
/**
 * Thank donor block template.
 *
 * @package GiftFlow
 *
 * @var string $heading             Main heading (limited inline HTML, sanitized).
 * @var string $message             Main message HTML (sanitized + wpautop in block render).
 * @var string $account_notice      Notice HTML (sanitized + wpautop in block render).
 * @var bool   $show_account_notice Whether to show the notice.
 * @var string $button_text         CTA label.
 * @var string $button_url          CTA URL.
 * @var bool   $show_button         Whether to show the CTA.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title_id = '';
if ( ! empty( $heading ) ) {
	$title_id = wp_unique_id( 'giftflow-thank-donor-h-' );
}

?>
<section class="giftflow-thank-donor" <?php echo $title_id ? 'aria-labelledby="' . esc_attr( $title_id ) . '"' : 'aria-label="' . esc_attr__( 'Thank you for your donation', 'giftflow' ) . '"'; ?>>
	<div class="giftflow-thank-donor__card">
		<header class="giftflow-thank-donor__masthead">
			<div class="giftflow-thank-donor__mark" aria-hidden="true">
				<?php echo wp_kses( giftflow_svg_icon( 'checkmark-circle' ), giftflow_allowed_svg_tags() ); ?>
			</div>
			<?php if ( ! empty( $heading ) ) : ?>
				<h1 class="giftflow-thank-donor__title" id="<?php echo esc_attr( $title_id ); ?>">
					<?php echo wp_kses_post( $heading ); ?>
				</h1>
			<?php endif; ?>
		</header>

		<?php if ( ! empty( $message ) ) : ?>
			<div class="giftflow-thank-donor__main">
				<?php echo $message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized in render callback. ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $show_account_notice ) && ! empty( $account_notice ) ) : ?>
			<div class="giftflow-thank-donor__note" role="note">
				<?php echo $account_notice; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized in render callback. ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $show_button ) && ! empty( $button_text ) && ! empty( $button_url ) ) : ?>
			<footer class="giftflow-thank-donor__cta">
				<a class="giftflow-thank-donor__btn" href="<?php echo esc_url( $button_url ); ?>">
					<span class="giftflow-thank-donor__btn-label"><?php echo esc_html( $button_text ); ?></span>
				</a>
			</footer>
		<?php endif; ?>
	</div>
</section>
