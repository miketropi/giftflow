<?php
/**
 * Donation form template
 *
 * @package GiftFlow
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<form class="donation-form" id="donation-form-<?php echo esc_attr( $campaign_id ); ?>">
	<?php

		// do action to allow other plugins to add custom content before the form.
		do_action( 'giftflow_donation_form_before_form', $args );
	?>

	<!-- Campaign Header -->
	<div class="donation-form__header">
		<div class="donation-form__campaign">
			<div class="donation-form__campaign-thumbnail">
				<?php echo get_the_post_thumbnail( $campaign_id, 'thumbnail' ); ?>
			</div>
			<div class="donation-form__campaign-info">
				<h4 class="donation-form__campaign-title"><?php esc_html_e( 'Donate to', 'giftflow' ); ?>: <?php echo esc_html( $campaign_title ); ?></h4>
				<div class="donation-form__campaign-progress">
					<?php
					// Translators: %1$s is the amount raised, %2$s is the goal amount.
					echo wp_kses_post( sprintf( __( '%1$s raised from %2$s goal', 'giftflow' ), giftflow_render_currency_formatted_amount( $raised_amount ), giftflow_render_currency_formatted_amount( $goal_amount ) ) );
					?>
				</div>
			</div>
		</div>
	</div>

	<!-- Form Steps -->
	<div class="donation-form__steps">
		<!-- Step Navigation -->
		<nav class="donation-form__step-nav">
			<ol class="donation-form__step-list">
				<li class="donation-form__step-item nav-step-1">
					<a href="#donation-information" class="donation-form__step-link is-active" data-step="donation-information">
						<span class="donation-form__step-number">1</span>
						<span class="donation-form__step-text"><?php esc_html_e( 'Donation Information', 'giftflow' ); ?></span>
					</a>
				</li>
				<li class="donation-form__step-separator"></li>
				<li class="donation-form__step-item nav-step-2">
					<a href="#payment-method" class="donation-form__step-link" data-step="payment-method">
						<span class="donation-form__step-number">2</span>
						<span class="donation-form__step-text"><?php esc_html_e( 'Payment Method', 'giftflow' ); ?></span>
					</a>
				</li>
			</ol>
		</nav>

		<!-- Step Content -->
		<div class="donation-form__step-content">
			<!-- Step 1: Donation Information -->
			<section id="donation-information" class="donation-form__step-panel step-1 is-active">
				<div class="donation-form__step-panel-content">
					<!-- Donation Type -->
					<fieldset class="
					<?php
					echo implode(
						' ',
						array(
							'donation-form__fieldset',
							'__count-donation-types-' . count( $donation_types ),
							count( $donation_types ) === 1 ? '__hidden-fieldset' : '',
						)
					);
					?>
						">
						<legend class="donation-form__legend"><?php esc_html_e( 'Select donation type, one-time or monthly', 'giftflow' ); ?></legend>
						<div class="donation-form__radio-group donation-form__radio-group--donation-type">
							<?php
							// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
							foreach ( $donation_types as $index => $donation_type ) :
								// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
								$is_checked = 0 === $index ? 'checked' : '';
								?>
								<label>
									<input 
										type="radio" 
										name="donation_type" 
										value="<?php echo esc_attr( $donation_type['name'] ); ?>"   
										id="donation_type_<?php echo esc_attr( $donation_type['name'] ); ?>" 
										<?php echo esc_attr( $is_checked ); ?>>
									<div class="donation-form__radio-label" for="donation_type_<?php echo esc_attr( $donation_type['name'] ); ?>">
										<span class="donation-form__radio-content">
											<span class="donation-form__radio-title">
												<?php echo wp_kses( $donation_type['icon'], giftflow_allowed_svg_tags() ); ?>	
												<?php echo esc_html( $donation_type['label'] ); ?>
											</span>
											<span class="donation-form__radio-description"><?php echo esc_html( $donation_type['description'] ); ?></span>
										</span>
									</div>
								</label>
							<?php endforeach; ?>
						</div>
					</fieldset>

					<!-- Donation Amount -->
					<fieldset class="donation-form__fieldset">
						<legend class="donation-form__legend"><?php esc_html_e( 'Donation Amount', 'giftflow' ); ?></legend>
						
						<?php // notification message. ?>
						<div class="donation-form__amount-notification">
							<span class="notification-message-icon"><?php echo wp_kses( giftflow_svg_icon( 'info' ), giftflow_allowed_svg_tags() ); ?></span>
							<div class="notification-message-entry">
								<span class="notification-message-required">
									<?php esc_html_e( 'Please enter your donation amount.', 'giftflow' ); ?>
								</span>
								<span class="notification-message-min">
									<?php esc_html_e( 'The minimum amount you can donate is', 'giftflow' ); ?>
									<strong class="gfw-monofont"><?php echo wp_kses_post( giftflow_render_currency_formatted_amount( $min_amount ) ); ?></strong>.
								</span>
								<span class="notification-message-max">
									<?php esc_html_e( 'The maximum amount you can donate is', 'giftflow' ); ?>
									<strong class="gfw-monofont">
										<?php
										if ( $max_amount ) {
											echo wp_kses_post( giftflow_render_currency_formatted_amount( $max_amount ) );
										} else {
											esc_html_e( 'unlimited', 'giftflow' );
										}
										?>
									</strong>.
								</span>
								<span class="notification-message-between">
									<?php esc_html_e( 'Enter an amount between the minimum and maximum allowed values.', 'giftflow' ); ?>
								</span>
							</div>
						</div>
						
						<div class="donation-form__amount">
							<?php
							// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
							$validate_attr_value = array( 'required', 'number', 'min' );
							// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
							$max_attr            = $max_amount ? 'max="' . esc_attr( $max_amount ) . '"' : '';
							// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
							$extra_data          = array(
								'min' => $min_amount ? $min_amount : 1,
							);

							if ( $max_attr ) {
								// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
								$validate_attr_value[] = 'max';
								// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
								$extra_data['max']     = $max_amount;
							}
							?>
							<div class="donation-form__amount-input">
								<span class="donation-form__currency"><?php echo esc_html( $currency_symbol ); ?></span>
								
								<input 
									type="number" 
									name="donation_amount" 
									value="<?php echo esc_attr( $default_amount ); ?>" 
									min="<?php echo esc_attr( $min_amount ); ?>" 
									<?php echo esc_attr( $max_attr ); ?>
									step="<?php echo esc_attr( apply_filters( 'giftflow_donation_form_amount_step', 1 ) ); ?>" 
									required 
									data-validate="<?php echo esc_attr( implode( ',', $validate_attr_value ) ); ?>"
									data-extra-data='<?php echo esc_attr( wp_json_encode( $extra_data ) ); ?>'
									<?php if ( ! $allow_custom_donation_amounts ) : ?>
										disabled
									<?php endif; ?>
									>
							</div>
							<div class="donation-form__preset-amounts">
								<?php
                                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
								foreach ( $preset_donation_amounts as $amount ) :
									?>
									<button 
										type="button" 
										class="donation-form__preset-amount" 
										data-amount="<?php echo esc_attr( $amount['amount'] ); ?>">
										<?php echo wp_kses_post( giftflow_render_currency_formatted_amount( $amount['amount'] ) ); ?>
									</button>
								<?php endforeach; ?>
							</div>
						</div>
					</fieldset>

					<!-- Donor Information -->
					<fieldset class="donation-form__fieldset">
						<legend class="donation-form__legend"><?php esc_html_e( 'Your Information', 'giftflow' ); ?></legend>
						<div class="donation-form__fields">

							<?php do_action( 'giftflow_donation_form_before_donor_information' ); ?>

							<div class="donation-form__field">
								<label for="donor_name"><?php esc_html_e( 'Full Name', 'giftflow' ); ?></label>
								<input type="text" id="donor_name" name="donor_name" value="<?php echo esc_attr( $user_fullname ); ?>" required data-validate="required" <?php echo $user_info_readonly ? 'readonly' : ''; ?>>

								<?php // error message. ?>
								<div class="donation-form__field-error">
									<?php echo wp_kses( giftflow_svg_icon( 'error' ), giftflow_allowed_svg_tags() ); ?>
									<?php esc_html_e( 'This field is required, please enter your name', 'giftflow' ); ?>
								</div> 
							</div>
							<div class="donation-form__field">
								<label for="donor_email"><?php esc_html_e( 'Email Address', 'giftflow' ); ?></label>
								<input type="email" id="donor_email" name="donor_email" value="<?php echo esc_attr( $user_email ); ?>" required data-validate="email" <?php echo $user_info_readonly ? 'readonly' : ''; ?>>

								<?php // error message. ?>
								<div class="donation-form__field-error">
									<?php echo wp_kses( giftflow_svg_icon( 'error' ), giftflow_allowed_svg_tags() ); ?>
									<?php esc_html_e( 'This field is required, please enter your email', 'giftflow' ); ?>
								</div> 
							</div>
							<div class="donation-form__field">
								<label for="donor_message"><?php esc_html_e( 'Message (Optional)', 'giftflow' ); ?></label>
								<textarea id="donor_message" name="donor_message"></textarea>
							</div>

							<?php do_action( 'giftflow_donation_form_after_donor_information' ); ?>

							<div class="donation-form__field">
								<label class="donation-form__checkbox-label">
									<input type="checkbox" name="anonymous_donation">
									<span><?php esc_html_e( 'Make this donation anonymous', 'giftflow' ); ?></span>
								</label>
							</div>
						</div>
					</fieldset>
				</div>
				<div class="donation-form__step-actions">
					<button type="button" class="donation-form__button donation-form__button--next" data-next-step="payment-method">
						<?php esc_html_e( 'Continue to Payment', 'giftflow' ); ?>
						<?php echo wp_kses( giftflow_svg_icon( 'next' ), giftflow_allowed_svg_tags() ); ?>
					</button>
				</div>
			</section>

			<!-- Step 2: Payment Method -->
			<section id="payment-method" class="donation-form__step-panel step-2">
				<div class="donation-form__step-panel-content">
					<!-- Payment Methods -->
					<fieldset class="donation-form__fieldset">
						<legend class="donation-form__legend"><?php esc_html_e( 'Select Payment Method', 'giftflow' ); ?></legend>
						<div class="donation-form__payment-methods">
							<?php
							if ( ! empty( $gateways ) ) {
								// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
								foreach ( $gateways as $method ) {
									if ( $method->is_enabled() ) {
										echo '<div class="donation-form__payment-method-item payment-method-' . esc_attr( $method->get_id() ) . '">';
										// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										echo $method->template_html();
										echo '</div>';
									}
								}
							}
							?>
						</div>
					</fieldset>

					<!-- Donation Summary -->
					<div class="donation-form__summary">
						<h3 class="donation-form__summary-title"><?php esc_html_e( 'Donation Summary', 'giftflow' ); ?></h3>
						<dl class="donation-form__summary-list">
							<div class="donation-form__summary-item">
								<dt><?php esc_html_e( 'Amount', 'giftflow' ); ?></dt>
								<dd class="donation-form__summary-amount gfw-monofont" data-output="donation_amount" data-format-template="<?php echo esc_attr( $currency_format_template ); ?>"></dd>
							</div>
							<div class="donation-form__summary-item">
								<dt><?php esc_html_e( 'Email', 'giftflow' ); ?></dt>
								<dd class="donation-form__summary-email" data-output="donor_email">
									<?php echo esc_html( $user_email ); ?>
								</dd>
							</div>
							<div class="donation-form__summary-item">
								<dt><?php esc_html_e( 'Campaign', 'giftflow' ); ?></dt>
								<dd><?php echo esc_html( $campaign_title ); ?></dd>
							</div>
						</dl>
					</div>
				</div>

				<?php // notice scurity payment check. ?>
				<div class="donation-form__security-notice">
					<span class="donation-form__security-notice-icon"><?php echo wp_kses( giftflow_svg_icon( 'shield-check' ), giftflow_allowed_svg_tags() ); ?></span>
					<p><?php esc_html_e( 'We use methods of payment that are secure and trusted. Your payment information is encrypted and never stored on our servers.', 'giftflow' ); ?></p>
				</div>

				<div class="donation-form__step-actions">
					<?php // hidden fields campaign id, wp nonce, form nonce, recurring interval. ?>
					<input type="hidden" name="campaign_id" value="<?php echo esc_attr( $campaign_id ); ?>">
					<input type="hidden" name="wp_nonce" value="<?php echo esc_attr( wp_create_nonce( 'giftflow_donation_form' ) ); ?>">
					<input type="hidden" name="recurring_interval" value="<?php echo esc_attr( $recurring_interval ); ?>">

					<button type="button" class="donation-form__button donation-form__button--back" data-prev-step="donation-information">
						<?php echo wp_kses( giftflow_svg_icon( 'prev' ), giftflow_allowed_svg_tags() ); ?>
						<?php esc_html_e( 'Back', 'giftflow' ); ?>
					</button>
										
					<?php // support class loading show icon loading when submit. ?>
					<button type="submit" class="donation-form__button donation-form__button--submit">
						<?php esc_html_e( 'Complete Donation', 'giftflow' ); ?>
						<?php echo wp_kses( giftflow_svg_icon( 'next' ), giftflow_allowed_svg_tags() ); ?>
					</button>
				</div>
			</section>
			
			<?php
			// do action to allow other plugins to add custom content after the payment method step.
			/**
			 * Action hook: giftflow_donation_form_after_payment_method.
			 *
			 * @see giftflow_donation_form_thank_you_section_html - 20.
			 * @see giftflow_donation_form_error_section_html - 22.
			 */
			do_action( 'giftflow_donation_form_after_payment_method', $args );
			?>
		</div>
	</div>

	<?php
		// do action to allow other plugins to add custom content after the form.
		do_action( 'giftflow_donation_form_after_form', $args );
	?>
</form>