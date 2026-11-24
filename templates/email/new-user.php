<?php
/**
 * Template for new user email
 *
 * @package GiftFlow
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound   
$name = $name ?? '';
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$username = $username ?? '';
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$password = $password ?? '';
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$reset_url = $reset_url ?? '';
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$login_url = $login_url ?? '';

// Get site information.
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$site_name = get_bloginfo( 'name' );
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$site_url = home_url();
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$admin_email = get_option( 'admin_email' );
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$accent_color = '#0b57d0';
?>

<h2 style="font-size: 1.2rem; font-weight: 600; margin: 0 0 1rem 0; color: black;">
	<?php esc_html_e( 'Welcome to', 'giftflow' ); ?> <?php echo esc_html( $site_name ); ?>!
</h2>

<p style="margin: 0 0 1.5rem 0; line-height: 1.6; opacity: .8; font-size: .9rem;">
	<?php
	if ( ! empty( $name ) ) {
		printf(
			/* translators: %s: Username */
			esc_html__( 'Hello %s,', 'giftflow' ),
			esc_html( $name )
		);
	} else {
		esc_html_e( 'Hello,', 'giftflow' );
	}
	?>
</p>

<p style="margin: 0 0 1.5rem 0; line-height: 1.6; opacity: .8; font-size: .9rem;">
	<?php esc_html_e( 'Thank you for making your first donation! We have created an account for you so you can easily view and manage your donations anytime.', 'giftflow' ); ?>
</p>

<table id="giftflow-email-table" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="width: 100%; border-collapse: collapse; margin: 1.8rem 0 0; background: #ffffff; border: 1px solid #e9ecef; border-radius: 0.3rem; overflow: hidden;">
	
	<!-- Account Details Header -->
	<tr>
		<td style="padding: .8rem 0; border-bottom: 1px solid #e9ecef;">
			<h3 style="margin: 0; font-size: 1rem; font-weight: 600; color: black;">
				<?php esc_html_e( 'Your Account Details', 'giftflow' ); ?>
			</h3>
		</td>
	</tr>
	
	<!-- Username -->
	<tr>
		<td style="padding: .8rem 0; border-bottom: 1px solid #f1f3f4;">
			<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
				<tr>
					<td style="width: 30%; padding: 0; vertical-align: top;">
						<p style="font-size: 0.9rem; margin: 0;">
							<?php esc_html_e( 'Username:', 'giftflow' ); ?>
						</p>
					</td>
					<td style="width: 70%; padding: 0; vertical-align: top;">
						<span style="color: <?php echo esc_attr( $accent_color ); ?>; font-weight: 600;">
							<?php echo esc_html( $username ); ?>
						</span>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	
	<!-- Password (if provided) -->
	<?php if ( ! empty( $password ) ) : ?>
	<tr>
		<td style="padding: .8rem 0; border-bottom: 1px solid #f1f3f4;">
			<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
				<tr>
					<td style="width: 30%; padding: 0; vertical-align: top;">
						<p style="font-size: 0.9rem; margin: 0;">
							<?php esc_html_e( 'Password:', 'giftflow' ); ?>
						</p>
					</td>
					<td style="width: 70%; padding: 0; vertical-align: top;">
						<span style="">
							<?php echo esc_html( $password ); ?>
						</span>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<?php endif; ?>
	
	<!-- Set password (a credential is never emailed) -->
	<?php if ( empty( $password ) && ! empty( $reset_url ) ) : ?>
	<tr>
		<td style="padding: .8rem 0; border-bottom: 1px solid #f1f3f4;">
			<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
				<tr>
					<td style="width: 30%; padding: 0; vertical-align: top;">
						<p style="font-size: 0.9rem; margin: 0;">
							<?php esc_html_e( 'Set your password:', 'giftflow' ); ?>
						</p>
					</td>
					<td style="width: 70%; padding: 0; vertical-align: top;">
						<a href="<?php echo esc_url( $reset_url ); ?>" style="color: <?php echo esc_attr( $accent_color ); ?>; text-decoration: none; font-size: 0.9rem;">
							<?php esc_html_e( 'Choose a password', 'giftflow' ); ?>
						</a>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<?php endif; ?>
	
	<!-- Login URL -->
	<tr>
		<td style="padding: .8rem 0;">
			<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
				<tr>
					<td style="width: 30%; padding: 0; vertical-align: top;">
						<p style="font-size: 0.9rem; margin: 0;">
							<?php esc_html_e( 'Login URL:', 'giftflow' ); ?>
						</p>
					</td>
					<td style="width: 70%; padding: 0; vertical-align: top;">
						<?php if ( ! empty( $login_url ) ) : ?>
							<a href="<?php echo esc_url( $login_url ); ?>" style="color: <?php echo esc_attr( $accent_color ); ?>; text-decoration: none; font-size: 0.9rem;">
								<?php echo esc_html( $login_url ); ?>
							</a>
						<?php else : ?>
							<a href="<?php echo esc_url( wp_login_url() ); ?>" style="color: <?php echo esc_attr( $accent_color ); ?>; text-decoration: none; font-size: 0.9rem;">
								<?php echo esc_url( wp_login_url() ); ?>
							</a>
						<?php endif; ?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	
</table>

<!-- Getting Started Section -->
<div style="background: #f8f9fa; padding: 1.5rem; margin: 1.5rem 0; border-radius: .3rem; border: solid 1px #eee;">
	<h4 style="margin: 0 0 0.5rem 0; font-size: 1rem; font-weight: 600;">
		<?php esc_html_e( 'Getting Started', 'giftflow' ); ?>
	</h4>
	<p style="margin: 0 0 1rem 0; opacity: .8; font-size: 0.9rem; line-height: 1.5;">
		<?php esc_html_e( 'Now that your account is set up, here\'s what you can do:', 'giftflow' ); ?>
	</p>
	<ul style="margin: 0; padding-left: 1.2rem; opacity: .8; font-size: 0.9rem; line-height: 1.5;">
		<li><?php esc_html_e( 'Log in to your account using the credentials above', 'giftflow' ); ?></li>
		<li><?php esc_html_e( 'Update your profile information', 'giftflow' ); ?></li>
		<li><?php esc_html_e( 'Explore our donation campaigns', 'giftflow' ); ?></li>
		<li><?php esc_html_e( 'Make your donation', 'giftflow' ); ?></li>
	</ul>
</div>

<!-- Action Buttons -->
<div style="text-align: center; margin: 2rem 0;">
	<table cellpadding="0" cellspacing="0" role="presentation" style="margin: 0 auto; width: 100%;">
		<tr>
			<td style="padding: 0 0.5rem;">
				<a 
					href="<?php echo esc_url( ! empty( $login_url ) ? $login_url : wp_login_url() ); ?>" 
					style="border-bottom: solid 2px; font-weight: 600; text-decoration: none;">
					<?php esc_html_e( '🔑 Log In Now', 'giftflow' ); ?>
				</a>
			</td>
			<td style="padding: 0 0.5rem;">
				<a 
					href="<?php echo esc_url( $site_url ); ?>" 
					style="border-bottom: solid 2px; font-weight: 600; text-decoration: none;">
					<?php esc_html_e( '🌎 Visit Our Website', 'giftflow' ); ?>
				</a>
			</td>
		</tr>
	</table>
</div>

<!-- Welcome Message -->
<p style="margin: 1.5rem 0 0 0; color: #555; font-size: 0.9rem; line-height: 1.6; text-align: center;">
	<?php esc_html_e( 'We\'re excited to have you as part of our community!', 'giftflow' ); ?>
</p>