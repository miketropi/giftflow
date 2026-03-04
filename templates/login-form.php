<?php
/**
 * Login form template
 *
 * Includes login, forgot password, and register forms with tab switching.
 *
 * @package GiftFlow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$redirect_url    = get_permalink( giftflow_get_donor_account_page() );
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$users_can_register = get_option( 'users_can_register' );

// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$initial_view    = isset( $_GET['view'] ) ? sanitize_key( $_GET['view'] ) : 'login';
if ( ! in_array( $initial_view, array( 'login', 'forgot', 'register' ), true ) || ( 'register' === $initial_view && ! $users_can_register ) ) {
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$initial_view = 'login';
}
?>
<div class="gfw-login-form" id="gfw-login-form" data-active-view="<?php echo esc_attr( $initial_view ); ?>">
	<nav class="gfw-auth-tabs" aria-label="<?php esc_attr_e( 'Authentication', 'giftflow' ); ?>">
		<button type="button" id="gfw-tab-login" class="gfw-auth-tab gfw-auth-tab--login is-active" data-gfw-view="login" aria-selected="true" aria-controls="gfw-pane-login">
			<?php esc_html_e( 'Log in', 'giftflow' ); ?>
		</button>
		<?php
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		if ( $users_can_register ) :
			?>
			<button type="button" id="gfw-tab-register" class="gfw-auth-tab gfw-auth-tab--register" data-gfw-view="register" aria-selected="false" aria-controls="gfw-pane-register">
				<?php esc_html_e( 'Register', 'giftflow' ); ?>
			</button>
		<?php endif; ?>
		<button type="button" id="gfw-tab-forgot" class="gfw-auth-tab gfw-auth-tab--forgot" data-gfw-view="forgot" aria-selected="false" aria-controls="gfw-pane-forgot">
			<?php esc_html_e( 'Forgot password', 'giftflow' ); ?>
		</button>
	</nav>

	<div class="gfw-auth-panes">
		<div class="gfw-auth-pane" id="gfw-pane-login" data-gfw-pane="login" role="tabpanel" aria-labelledby="gfw-tab-login">
			<?php
			wp_login_form(
				array(
					'redirect' => $redirect_url,
					'remember' => true,
				)
			);
			?>
		</div>

		<div class="gfw-auth-pane" id="gfw-pane-forgot" data-gfw-pane="forgot" role="tabpanel" aria-labelledby="gfw-tab-forgot" hidden>
			<form name="gfw-lostpassword-form" class="gfw-lostpassword-form" action="<?php echo esc_url( wp_lostpassword_url( $redirect_url ) ); ?>" method="post">
				<p class="gfw-form-row">
					<label for="gfw-user-login"><?php esc_html_e( 'Username or email address', 'giftflow' ); ?></label>
					<input type="text" name="user_login" id="gfw-user-login" class="input" autocomplete="username" required />
				</p>
				<p class="gfw-form-row gfw-form-row--submit">
					<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_url ); ?>" />
					<button type="submit" class="button button-primary">
						<?php esc_html_e( 'Get reset link', 'giftflow' ); ?>
					</button>
				</p>
			</form>
		</div>

		<?php if ( $users_can_register ) : ?>
			<div class="gfw-auth-pane" id="gfw-pane-register" data-gfw-pane="register" role="tabpanel" aria-labelledby="gfw-tab-register" hidden>
				<form name="gfw-register-form" class="gfw-register-form" action="<?php echo esc_url( wp_registration_url() ); ?>" method="post">
					<p class="gfw-form-row">
						<label for="gfw-register-user-login"><?php esc_html_e( 'Username', 'giftflow' ); ?></label>
						<input type="text" name="user_login" id="gfw-register-user-login" class="input" autocomplete="username" required />
					</p>
					<p class="gfw-form-row">
						<label for="gfw-register-user-email"><?php esc_html_e( 'Email address', 'giftflow' ); ?></label>
						<input type="email" name="user_email" id="gfw-register-user-email" class="input" autocomplete="email" required />
					</p>
					<p class="gfw-form-row gfw-form-row--submit">
						<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_url ); ?>" />
						<button type="submit" class="button button-primary">
							<?php esc_html_e( 'Register', 'giftflow' ); ?>
						</button>
					</p>
				</form>
			</div>
		<?php endif; ?>
	</div>
</div>
<script>
(function() {
	var form = document.getElementById('gfw-login-form');
	if (!form) return;
	var tabs = form.querySelectorAll('.gfw-auth-tab[data-gfw-view]');
	var panes = form.querySelectorAll('.gfw-auth-pane[data-gfw-pane]');
	function showView(view) {
		tabs.forEach(function(t) {
			var isActive = t.getAttribute('data-gfw-view') === view;
			t.classList.toggle('is-active', isActive);
			t.setAttribute('aria-selected', isActive ? 'true' : 'false');
		});
		panes.forEach(function(p) {
			var isActive = p.getAttribute('data-gfw-pane') === view;
			p.hidden = !isActive;
		});
		form.setAttribute('data-active-view', view);
		if (typeof window.history !== 'undefined' && window.history.replaceState) {
			var url = new URL(window.location.href);
			url.searchParams.set('view', view);
			window.history.replaceState(null, '', url.toString());
		}
	}
	tabs.forEach(function(tab) {
		tab.addEventListener('click', function() {
			showView(this.getAttribute('data-gfw-view'));
		});
	});
	var initial = form.getAttribute('data-active-view');
	if (initial) showView(initial);
})();
</script>
