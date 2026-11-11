<?php
/**
 * Template for donor account
 *
 * @package GiftFlow
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound, WordPress.WP.GlobalVariablesOverride.Prohibited
$tabs = $tabs ?? array();
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound, WordPress.WP.GlobalVariablesOverride.Prohibited
$current_user = $current_user ?? null;
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$attributes = $attributes ?? array();
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$active_tab = $active_tab ?? $tabs[0]['slug'];
?>

<div class="giftflow-donor-account" role="tablist" aria-label="<?php esc_attr_e( 'Donor Account Navigation', 'giftflow' ); ?>">
	<div class="giftflow-donor-account__sidebar">
	<?php
	/**
	 * Fires immediately before the donor account navigation.
	 *
	 * Runs inside the sidebar wrapper, so anything added here shares the
	 * navigation column instead of becoming a third flex child.
	 *
	 * @param WP_User $current_user Current user.
	 */
	do_action( 'giftflow_donor_account_before_nav', $current_user );
	?>
	<div class="giftflow-donor-account__tabs" role="tablist">
	<?php
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound, WordPress.WP.GlobalVariablesOverride.Prohibited
	foreach ( $tabs as $index => $tab ) :
		?>
		<a 
		href="<?php echo esc_url( giftflow_donor_account_page_url( $tab['slug'] ) ); ?>" 
		class="giftflow-donor-account__tab<?php echo $tab['slug'] === $active_tab ? ' active' : ''; ?>"
		role="tab"
		id="tab-<?php echo esc_attr( $tab['slug'] ); ?>"
		>
		<span class="giftflow-donor-account__tab-icon" aria-hidden="true"><?php echo wp_kses( $tab['icon'], giftflow_allowed_svg_tags() ); ?></span>
		<span class="giftflow-donor-account__tab-label"><?php echo esc_html( $tab['label'] ); ?></span>
		</a>
	<?php endforeach; ?>
	</div>
	</div>
  
	<div class="giftflow-donor-account__content">
	<?php
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound, WordPress.WP.GlobalVariablesOverride.Prohibited
	foreach ( $tabs as $index => $tab ) :
		// if not active tab is continue.
		if ( $tab['slug'] !== $active_tab ) {
			continue;
		}
		?>
		<div 
		class="giftflow-donor-account__tab-content" 
		role="tabpanel"
		id="<?php echo esc_attr( $tab['slug'] ); ?>-panel"
		aria-labelledby="tab-<?php echo esc_attr( $tab['slug'] ); ?>"
		>
		<?php
		// Execute callback function if it exists.
		if ( is_callable( $tab['callback'] ) ) {
			call_user_func( $tab['callback'], $current_user, $attributes );

		} elseif ( function_exists( $tab['callback'] ) ) {
			call_user_func( $tab['callback'], $current_user, $attributes );

		} else {
			?>
			<div class="donor-account-empty">
			<div class="empty-icon"><?php echo wp_kses( giftflow_svg_icon( 'folder-code' ), giftflow_allowed_svg_tags() ); ?></div>
			<div class="empty-message">
				<h4><?php echo esc_html( $tab['label'] ); ?></h4>
				<p>
				<?php esc_html_e( 'This section is currently under development.', 'giftflow' ); ?>
				<br>
				<?php esc_html_e( 'We\'re working hard to bring you new features and improvements. Please check back soon for updates!', 'giftflow' ); ?>
				</p>
				<ul>
				<li><?php esc_html_e( 'Stay tuned for enhanced donor tools and insights.', 'giftflow' ); ?></li>
				<li><?php esc_html_e( 'Your feedback is valuable—let us know what you\'d like to see!', 'giftflow' ); ?></li>
				<li><?php esc_html_e( 'Thank you for supporting our mission.', 'giftflow' ); ?></li>
				</ul>
			</div>
			</div> 
			<?php
		}
		?>
		</div>
	<?php endforeach; ?>
	</div>
</div>