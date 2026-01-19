<?php
/**
 * Donor Account Block.
 *
 * @package GiftFlow
 */

/**
 * Register donor account block.
 *
 * @return void
 */
function giftflow_donor_account_block() {
	register_block_type(
		'giftflow/donor-account',
		array(
			'api_version'     => 3,
			'render_callback' => 'giftflow_donor_account_block_render',
			'attributes'      => array(),
		)
	);
}

/**
 * Add action to register donor account block.
 */
add_action( 'init', 'giftflow_donor_account_block' );

/**
 * Render donor account block.
 *
 * @param array $attributes Block attributes.
 * @param string $content Block content.
 * @param WP_Block $block Block object.
 * @return string Block output.
 */
function giftflow_donor_account_block_render( $attributes, $content, $block ) {
	unset( $content );
	unset( $block );

	// get current user loggin.
	$current_user = wp_get_current_user();

	ob_start();
	if ( $current_user->ID ) {

		$tabs = giftflow_donor_account_tabs();

		// load template donor account.
		giftflow_load_template(
			'block/donor-account.php',
			array(
				'current_user'            => $current_user,
				'attributes'              => $attributes,
				'tabs'                    => $tabs,
				'active_tab'              => get_query_var( 'tab', $tabs[0]['slug'] ),
				'root_donor_account_page' => get_permalink( giftflow_get_donor_account_page() ),
			)
		);
	} else {
		// load template login form.
		giftflow_load_template(
			'login-form.php',
			array(
				'attributes' => $attributes,
			)
		);
	}
	return ob_get_clean();
}

/**
 * Get donor account tabs.
 *
 * @return array The array of donor account tabs.
 */
function giftflow_donor_account_tabs() {
	$tabs = array(
		array(
			'label'    => esc_html__( 'Dashboard', 'giftflow' ),
			'slug'     => 'dashboard',
			'icon'     => giftflow_svg_icon( 'gauge' ),
			'url'      => get_permalink( giftflow_get_donor_account_page() ),
			'callback' => 'giftflow_donor_account_dashboard_callback',
		),
		array(
			'label'    => esc_html__( 'My Donations', 'giftflow' ),
			'slug'     => 'donations',
			'icon'     => giftflow_svg_icon( 'clipboard-clock' ),
			'url'      => get_permalink( giftflow_get_donor_account_page() ),
			'callback' => 'giftflow_donor_account_my_donations_callback',
		),
		// donor infomation.
		array(
			'label'    => esc_html__( 'My Account', 'giftflow' ),
			'slug'     => 'my-account',
			'icon'     => giftflow_svg_icon( 'user' ),
			'callback' => 'giftflow_donor_account_my_account_callback',
		),
	);

	/**
	 * Filter the donor account tabs.
	 *
	 * @param array $tabs The array of donor account tabs.
	 */
	return apply_filters( 'giftflow_donor_account_tabs', $tabs );
}

/**
 * Add action to register donor account page rewrite rules.
 */
add_action(
	'init',
	function () {
		$donor_account_page_id = giftflow_get_donor_account_page();
		$slug                  = get_post_field( 'post_name', $donor_account_page_id );

		add_rewrite_rule(
			'^' . $slug . '/([^/]*)/?',
			'index.php?pagename=' . $slug . '&tab=$matches[1]',
			'top'
		);

		flush_rewrite_rules();
	}
);

/**
 * Add filter to register donor account page query vars.
 */
add_filter(
	'query_vars',
	function ( $vars ) {
		$vars[] = 'tab'; // register tab query var.
		return $vars;
	}
);

/**
 * Get donor account page URL.
 *
 * @param string $tab The tab slug.
 * @return string The donor account page URL.
 */
function giftflow_donor_account_page_url( $tab ) {
	$donor_account_page_id = giftflow_get_donor_account_page();
	$slug                  = get_post_field( 'post_name', $donor_account_page_id );

	$tab = trim( $tab, '/' );
	if ( get_option( 'permalink_structure' ) ) {
		// pretty permalinks enabled.
		return home_url( '/' . $slug . '/' . $tab );
	} else {
		// plain permalinks -> query-string style.
		return add_query_arg(
			array(
				'pagename' => $slug,
				'tab'      => $tab,
			),
			home_url( '/' )
		);
	}
}

/**
 * Dashboard callback.
 *
 * @return void
 */
function giftflow_donor_account_dashboard_callback() {
	// load template donor-account--dashboard.
	$current_user = wp_get_current_user();
	giftflow_load_template(
		'block/donor-account--dashboard.php',
		array(
			'current_user' => $current_user,
		)
	);
}

/**
 * My donations callback.
 *
 * @return void
 */
function giftflow_donor_account_my_donations_callback() {
	// get current user.
	$current_user = wp_get_current_user();

  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$id = isset( $_GET['_id'] ) ? intval( $_GET['_id'] ) : null;

	if ( $id ) {

		// validate post type.
		if ( 'donation' !== get_post_type( $id ) ) {
			// return template not found.
			giftflow_load_template(
				'block/donor-account--not-found.php',
				array(
					'current_user' => $current_user,
					'id'           => $id,
				)
			);
			return;
		}

		// get donation data by id.
		$donation = giftflow_get_donation_data_by_id( $id );

		// check $donation is exist.
		if ( ! $donation ) {
			// return template not found.
			giftflow_load_template(
				'block/donor-account--not-found.php',
				array(
					'current_user' => $current_user,
					'id'           => $id,
				)
			);
			return;
		}

		// check $donation donor_email is equal to current user email.
		if ( $donation->donor_email !== $current_user->user_email ) {
			// return template not allowed to view this donation.
			giftflow_load_template(
				'block/donor-account--not-allowed.php',
				array(
					'current_user' => $current_user,
					'id'           => $id,
				)
			);
			return;
		}

		// view donation template.
		giftflow_load_template(
			'block/donor-account--my-donations--detail.php',
			array(
				'current_user' => $current_user,
				'id'           => $id,
				'donation'     => $donation,
			)
		);
		return;
	}

  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$page = isset( $_GET['_page'] ) ? intval( $_GET['_page'] ) : 1;
	if ( $page < 1 ) {
		$page = 1;
	}

	// query donations by donor id.
	$donations = giftflow_get_donations_by_user( $current_user->ID, $page, 20 );

	// load template donor-account--my-donations.
	giftflow_load_template(
		'block/donor-account--my-donations.php',
		array(
			'current_user' => $current_user,
			'donations'    => $donations,
			'page'         => $page,
		)
	);
}

/**
 * My Account callback
 *
 * @since 1.0.0
 */
function giftflow_donor_account_my_account_callback() {
	// get current user.
	$current_user = wp_get_current_user();

	// =============================================================================
	// FORM PROCESSING.
	// =============================================================================

	$account_result  = null;
	$password_result = null;

	// Process account form.
	if (
	isset( $_POST['giftflow_update_account'], $_POST['giftflow_account_nonce'] ) &&
	wp_verify_nonce(
		sanitize_text_field(
			wp_unslash( $_POST['giftflow_account_nonce'] )
		),
		'giftflow_update_account'
	)
	) {
		$account_result = giftflow_process_account_form( $current_user->ID, $_POST );
	}

	// Process password form.
	if ( isset( $_POST['giftflow_update_password'], $_POST['giftflow_password_nonce'] ) &&
	wp_verify_nonce(
		sanitize_text_field(
			wp_unslash( $_POST['giftflow_password_nonce'] )
		),
		'giftflow_update_password'
	)
	) {
		$password_result = giftflow_process_password_form( $current_user, $_POST );
	}

	// =============================================================================
	// DATA PREPARATION.
	// =============================================================================

	// get donor information.
	$donor = giftflow_get_donor_user_information( $current_user->ID );

	// Get donor data for form population.
	$form_data = array(
		'first_name'  => $donor['first_name'] ?? '',
		'last_name'   => $donor['last_name'] ?? '',
		'email'       => $donor['email'] ?? '',
		'phone'       => $donor['phone'] ?? '',
		'address'     => $donor['address'] ?? '',
		'city'        => $donor['city'] ?? '',
		'state'       => $donor['state'] ?? '',
		'postal_code' => $donor['postal_code'] ?? '',
		'country'     => $donor['country'] ?? '',
	);

	// load template donor-account--my-account.
	giftflow_load_template(
		'block/donor-account--my-account.php',
		array(
			'current_user'    => $current_user,
			'donor'           => $donor,
			'account_result'  => $account_result,
			'password_result' => $password_result,
			'form_data'       => $form_data,
		)
	);
}

// =============================================================================
// FORM PROCESSING FUNCTIONS.
// =============================================================================

/**
 * Process account information form submission.
 *
 * @param int $user_id The user ID.
 * @param array $post_data The post data.
 * @return array The result of the form submission.
 */
function giftflow_process_account_form( $user_id, $post_data ) {
	$user_email = get_user_by( 'id', $user_id )->user_email;
	$donor_id   = giftflow_get_donor_id_by_email( $user_email );
	$form_data = giftflow_sanitize_account_form_data( $post_data );
	$errors    = giftflow_validate_account_form_data( $form_data );

	if ( empty( $errors ) ) {
		$result = giftflow_update_donor_account( $user_id, $form_data, $donor_id );
		if ( $result['success'] ) {
			return array(
				'success' => true,
				'message' => __( 'Your account information has been updated successfully.', 'giftflow' ),
			);
		} else {
			return array(
				'success' => false,
				'errors'  => array( $result['message'] ),
			);
		}
	}

	return array(
		'success' => false,
		'errors'  => $errors,
	);
}

/**
 * Process password change form submission
 *
 * @param WP_User $current_user The current user.
 * @param array $post_data The post data.
 * @return array The result of the form submission.
 */
function giftflow_process_password_form( $current_user, $post_data = array() ) {
	$form_data = giftflow_sanitize_password_form_data( $post_data );
	$errors    = giftflow_validate_password_form_data( $form_data, $current_user );

	if ( empty( $errors ) ) {
		$result = giftflow_update_user_password( $current_user->ID, $form_data['new_password'] );
		if ( $result ) {

			// add action after update user password.
			do_action( 'giftflow_after_update_user_password_success', $current_user->ID, $form_data['new_password'] );

			return array(
				'success' => true,
				'message' => __( 'Your password has been updated successfully.', 'giftflow' ),
			);
		} else {
			return array(
				'success' => false,
				'errors'  => array( __( 'Failed to update password. Please try again.', 'giftflow' ) ),
			);
		}
	}

	return array(
		'success' => false,
		'errors'  => $errors,
	);
}

/**
 * Sanitize account form data
 *
 * @param array $post_data The post data.
 * @return array The sanitized form data.
 */
function giftflow_sanitize_account_form_data( $post_data = array() ) {

	$form_data = array(
		'first_name'  => sanitize_text_field( wp_unslash( $post_data['first_name'] ?? '' ) ),
		'last_name'   => sanitize_text_field( wp_unslash( $post_data['last_name'] ?? '' ) ),
		'email'       => sanitize_email( wp_unslash( $post_data['email'] ?? '' ) ),
		'phone'       => sanitize_text_field( wp_unslash( $post_data['phone'] ?? '' ) ),
		'address'     => sanitize_textarea_field( wp_unslash( $post_data['address'] ?? '' ) ),
		'city'        => sanitize_text_field( wp_unslash( $post_data['city'] ?? '' ) ),
		'state'       => sanitize_text_field( wp_unslash( $post_data['state'] ?? '' ) ),
		'postal_code' => sanitize_text_field( wp_unslash( $post_data['postal_code'] ?? '' ) ),
		'country'     => sanitize_text_field( wp_unslash( $post_data['country'] ?? '' ) ),
	);

	/**
	 * Filter the sanitized account form data before it is returned.
	 *
	 * @param array $form_data The sanitized form data.
	 */
	$form_data = apply_filters( 'giftflow_sanitize_account_form_data', $form_data );

	return $form_data;
}

/**
 * Sanitize password form data
 *
 * @param array $post_data The post data.
 * @return array The sanitized form data.
 */
function giftflow_sanitize_password_form_data( $post_data = array() ) {
	return array(
		'current_password' => sanitize_text_field( wp_unslash( $post_data['giftflow_current_password'] ?? '' ) ),
		'new_password'     => sanitize_text_field( wp_unslash( $post_data['giftflow_new_password'] ?? '' ) ),
		'confirm_password' => sanitize_text_field( wp_unslash( $post_data['giftflow_confirm_password'] ?? '' ) ),
	);
}

/**
 * Validate account form data
 *
 * @param array $data The form data.
 * @return array The errors.
 */
function giftflow_validate_account_form_data( $data ) {
	$errors = array();

	// Required field validation.
	if ( empty( $data['first_name'] ) ) {
		$errors[] = esc_html__( 'First name is required.', 'giftflow' );
	}

	if ( empty( $data['last_name'] ) ) {
		$errors[] = esc_html__( 'Last name is required.', 'giftflow' );
	}

	return $errors;
}

/**
 * Validate password form data
 *
 * @param array $data The form data.
 * @param WP_User $current_user The current user.
 * @return array The errors.
 */
function giftflow_validate_password_form_data( $data, $current_user ) {
	$errors = array();

	// Current password validation.
	if ( empty( $data['current_password'] ) || ! wp_check_password( $data['current_password'], $current_user->user_pass, $current_user->ID ) ) {
		$errors[] = esc_html__( 'Your current password is incorrect.', 'giftflow' );
	}

	// New password validation.
	if ( empty( $data['new_password'] ) ) {
		$errors[] = esc_html__( 'Please enter a new password.', 'giftflow' );
	} elseif ( $data['new_password'] !== $data['confirm_password'] ) {
		$errors[] = esc_html__( 'New password and confirmation do not match.', 'giftflow' );
	} elseif ( strlen( $data['new_password'] ) < 6 ) {
		$errors[] = esc_html__( 'New password must be at least 6 characters.', 'giftflow' );
	}

	return $errors;
}

/**
 * Update donor account information
 *
 * @param int $user_id The user ID.
 * @param array $data The form data.
 * @param int $donor_id The donor ID.
 * @return array The result of the update.
 */
function giftflow_update_donor_account( $user_id, $data, $donor_id ) {
	// validate donor id.
	if ( ! $donor_id ) {
		return array(
			'success' => false,
			'message' => esc_html__( 'Donor ID is required.', 'giftflow' ),
		);
	}

	// Update WordPress user data.
	$user_data = array(
		'ID'           => $user_id,
		'first_name'   => $data['first_name'],
		'last_name'    => $data['last_name'],
		'display_name' => $data['first_name'] . ' ' . $data['last_name'],
	);

	$result = wp_update_user( $user_data );

	if ( is_wp_error( $result ) ) {
		return array(
			'success' => false,
			'message' => esc_html__( 'There was an error updating your account. Please try again.', 'giftflow' ),
		);
	}

	// Filter the donor meta fields to be updated.
	$meta_fields = apply_filters(
		'giftflow_donor_account_meta_fields',
		array( 'first_name', 'last_name', 'phone', 'address', 'city', 'state', 'postal_code', 'country' ),
		$user_id,
		$donor_id
	);

	foreach ( $meta_fields as $field ) {
		update_post_meta( $donor_id, '_' . $field, $data[ $field ] );
	}

	// Fires after donor account information has been updated.
	do_action( 'giftflow_after_update_donor_account', $user_id, $data, $donor_id );

	return array( 'success' => true );
}

/**
 * Update user password
 *
 * @param int $user_id The user ID.
 * @param string $new_password The new password.
 * @return bool The result of the update.
 */
function giftflow_update_user_password( $user_id, $new_password ) {
	$result = wp_update_user(
		array(
			'ID'        => $user_id,
			'user_pass' => $new_password,
		)
	);

	return ! is_wp_error( $result );
}
