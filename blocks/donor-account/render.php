<?php
/**
 * Render callback for the Donor Account block.
 *
 * @package GiftFlow
 * @subpackage Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_wrapper_attrs = get_block_wrapper_attributes(
	array( 'class' => 'giftflow-donor-account' )
);

if ( ! is_user_logged_in() ) {
	echo '<div ' . $block_wrapper_attrs . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	giftflow_load_template( 'login-form.php' );
	echo '</div>';
	return;
}

$gf_current_user = wp_get_current_user();
$gf_tabs         = giftflow_donor_account_tabs();
$gf_active_tab   = get_query_var( 'tab', 'dashboard' );

// Validate tab slug exists in the tabs array.
$gf_valid_slugs = wp_list_pluck( $gf_tabs, 'slug' );
if ( ! in_array( $gf_active_tab, $gf_valid_slugs, true ) ) {
	$gf_active_tab = 'dashboard';
}
?>
<div <?php echo $block_wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes returns safe HTML. ?>>
	<div class="giftflow-donor-account__sidebar">
		<?php
		/**
		 * Fires immediately before the donor account navigation.
		 *
		 * Runs inside the sidebar wrapper, so anything added here shares the
		 * navigation column instead of becoming a third flex child.
		 *
		 * @param WP_User $gf_current_user Current user.
		 */
		do_action( 'giftflow_donor_account_before_nav', $gf_current_user );
		?>
		<nav class="giftflow-donor-account__nav" role="tablist">
			<?php foreach ( $gf_tabs as $gf_tab ) : ?>
				<a
					href="<?php echo esc_url( add_query_arg( 'tab', $gf_tab['slug'], get_permalink() ) ); ?>"
					class="giftflow-donor-account__nav-item<?php echo $gf_tab['slug'] === $gf_active_tab ? ' is-active' : ''; ?>"
					role="tab"
					aria-selected="<?php echo $gf_tab['slug'] === $gf_active_tab ? 'true' : 'false'; ?>"
				>
					<?php echo esc_html( $gf_tab['label'] ); ?>
				</a>
			<?php endforeach; ?>
		</nav>
	</div>

	<div class="giftflow-donor-account__content">
		<?php
		// Find the active tab by slug and invoke its callback.
		foreach ( $gf_tabs as $gf_tab ) {
			if ( $gf_tab['slug'] === $gf_active_tab && isset( $gf_tab['callback'] ) && is_callable( $gf_tab['callback'] ) ) {
				call_user_func( $gf_tab['callback'], $gf_current_user );
				break;
			}
		}
		?>
	</div>
</div>
