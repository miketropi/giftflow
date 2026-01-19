<?php
/**
 * Login form template
 *
 * @package GiftFlow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="gfw-login-form">
<?php
	wp_login_form(
		array(
			'redirect' => get_permalink( giftflow_get_donor_account_page() ),
			'remember' => true,
		)
	);
	?>
</div>