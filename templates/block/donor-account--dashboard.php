<?php
/**
 * Template for dashboard
 *
 * @package GiftFlow
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// current user.
// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$current_user = wp_get_current_user();

// get name.
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$name = implode( ' ', array( $current_user->first_name, $current_user->last_name ) );
if ( empty( trim( $name ) ) ) {
  // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$name = 'donor';
}
?>
<div class="giftflow-donor-account-dashboard">
	<div class="giftflow-donor-account-dashboard__welcome">
	<h2 class="giftflow-donor-account-dashboard__title">
		<?php
		/* translators: %s: Donor's full name */
		printf( esc_html__( 'Welcome, %s!', 'giftflow' ), esc_html( $name ) );
		?>
	</h2>
	<p>
		<?php esc_html_e( 'Thank you for your generous support. Your contributions make a real difference!', 'giftflow' ); ?>
	</p>
	<p>
		<?php esc_html_e( 'From supporting urgent campaigns to helping us reach long-term goals, every donation you make empowers positive change in our community.', 'giftflow' ); ?>
	</p>
	<ul>
		<li><?php esc_html_e( 'Track your recent donations and see the impact you\'ve made.', 'giftflow' ); ?></li>
		<li><?php esc_html_e( 'Update your profile to personalize your giving experience.', 'giftflow' ); ?></li>
	</ul>
	</div>
	<div class="giftflow-donor-account-dashboard__quick-actions">
	<h3><?php esc_html_e( 'Quick Actions', 'giftflow' ); ?></h3>
	<ul>
		<li>
		<a href="<?php echo esc_url( giftflow_donor_account_page_url( 'donations' ) ); ?>" class="giftflow-dashboard-action">
			<?php echo wp_kses( giftflow_svg_icon( 'clipboard-clock' ), giftflow_allowed_svg_tags() ); ?>
			<span><?php esc_html_e( 'View My Donations', 'giftflow' ); ?></span>
		</a>
		</li>
		<li>
		<a href="<?php echo esc_url( giftflow_donor_account_page_url( 'my-account' ) ); ?>" class="giftflow-dashboard-action">
			<?php echo wp_kses( giftflow_svg_icon( 'user' ), giftflow_allowed_svg_tags() ); ?>
			<span><?php esc_html_e( 'Edit Profile', 'giftflow' ); ?></span>
		</a>
		</li>
	</ul>
	</div>
	<div class="giftflow-donor-account-dashboard__message">
	<p>
		<?php esc_html_e( 'Stay tuned for new features and updates to your donor account. We appreciate your ongoing commitment!', 'giftflow' ); ?>
	</p>
	</div>
</div>