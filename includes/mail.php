<?php
/**
 * Mail system for GiftFlow plugin.
 *
 * @package GiftFlow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Resolve the administrator address used by plugin notifications.
 *
 * The saved mail setting wins while it holds a single valid address; otherwise
 * the site administration address is used. When neither is valid the result is
 * an empty string so callers fail explicitly instead of mailing nowhere.
 *
 * @return string Valid address, or an empty string.
 */
function giftflow_get_admin_email_address() {
	$email_opts = get_option( 'giftflow_email_options' );
	$candidates = array();

	if ( is_array( $email_opts ) && isset( $email_opts['email_admin_address'] ) && is_string( $email_opts['email_admin_address'] ) ) {
		$candidates[] = $email_opts['email_admin_address'];
	}

	$site_admin = get_option( 'admin_email' );

	if ( is_string( $site_admin ) ) {
		$candidates[] = $site_admin;
	}

	foreach ( $candidates as $candidate ) {
		$candidate = trim( $candidate );

		if ( '' !== $candidate && is_email( $candidate ) ) {
			return $candidate;
		}
	}

	return '';
}

/**
 * Normalize and validate a recipient argument the way WordPress accepts it.
 *
 * Supports a single address, a comma separated list, an array of either and
 * `Name <address>` wrappers. Every non-empty entry must be a string holding one
 * valid address; an empty entry is ignored, which keeps trailing commas legal.
 *
 * @param mixed $to Recipient argument.
 * @return array{valid:bool,count:int,recipients:string[]}
 */
function giftflow_normalize_mail_recipients( $to ) {
	if ( is_array( $to ) ) {
		$entries = array_values( $to );
	} elseif ( is_string( $to ) ) {
		$entries = explode( ',', $to );
	} else {
		return array(
			'valid'      => false,
			'count'      => 0,
			'recipients' => array(),
		);
	}

	$recipients = array();
	$count      = 0;

	foreach ( $entries as $entry ) {
		if ( ! is_string( $entry ) ) {
			return array(
				'valid'      => false,
				'count'      => $count,
				'recipients' => array(),
			);
		}

		$entry = trim( $entry );

		if ( '' === $entry ) {
			continue;
		}

		++$count;

		$address = $entry;

		if ( preg_match( '/^[^<>]*<([^<>]+)>$/', $entry, $matches ) ) {
			$address = $matches[1];
		}

		if ( ! is_email( trim( $address ) ) ) {
			return array(
				'valid'      => false,
				'count'      => $count,
				'recipients' => array(),
			);
		}

		$recipients[] = $entry;
	}

	return array(
		'valid'      => $count > 0,
		'count'      => $count,
		'recipients' => $count > 0 ? $recipients : array(),
	);
}

/**
 * Report a rejected mail attempt without leaking addresses or content.
 *
 * @param string $code            WP_Error code.
 * @param string $message         Translated message.
 * @param int    $recipient_count Number of recipients considered.
 * @return void
 */
function giftflow_report_mail_failure( $code, $message, $recipient_count ) {
	do_action(
		'wp_mail_failed',
		new WP_Error(
			$code,
			$message,
			array(
				'recipient_count' => (int) $recipient_count,
			)
		)
	);
}

/**
 * Sendmail
 *
 * @param array $args Email arguments.
 * @return bool Whether the email was sent successfully.
 */
function giftflow_send_mail_template( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'to'        => '',
			'subject'   => '',
			'header'    => '',
			'content'   => '',
			'footer'    => '',
			'from_name' => '',
		)
	);

	$recipients = giftflow_normalize_mail_recipients( $args['to'] );

	if ( ! $recipients['valid'] ) {
		giftflow_report_mail_failure(
			'giftflow_mail_invalid_recipient',
			__( 'A valid recipient email address is required.', 'giftflow' ),
			$recipients['count']
		);

		return false;
	}

	$email_opts      = get_option( 'giftflow_email_options' );
	$email_from_name = '';

	if ( is_array( $email_opts ) && isset( $email_opts['email_from_name'] ) && is_string( $email_opts['email_from_name'] ) ) {
		$email_from_name = trim( $email_opts['email_from_name'] );
	}

	if ( '' === $email_from_name ) {
		$email_from_name = (string) get_bloginfo( 'name' );
	}

	// An extension may request its own display name. It never controls the
	// envelope address, and header injection characters are stripped.
	$requested_name = is_string( $args['from_name'] ) ? trim( str_replace( array( "\r", "\n" ), '', $args['from_name'] ) ) : '';

	if ( '' !== $requested_name ) {
		$email_from_name = $requested_name;
	}

	$email_admin_address = giftflow_get_admin_email_address();

	if ( '' === $email_admin_address ) {
		giftflow_report_mail_failure(
			'giftflow_mail_invalid_sender',
			__( 'A valid sender email address is required.', 'giftflow' ),
			$recipients['count']
		);

		return false;
	}

	$headers = array(
		'From: ' . $email_from_name . ' <' . $email_admin_address . '>',
		'Content-Type: text/html; charset=UTF-8',
	);

	// get html.
	ob_start();
	giftflow_load_template(
		'email/template-default.php',
		array(
			'header'  => $args['header'],
			'content' => $args['content'],
			'footer'  => $args['footer'],
		)
	);
	$__html = ob_get_clean();

	return wp_mail( is_array( $args['to'] ) ? $recipients['recipients'] : implode( ', ', $recipients['recipients'] ), $args['subject'], $__html, $headers );
}

// test mail ajax.
add_action( 'wp_ajax_giftflow_test_send_mail', 'giftflow_test_send_mail_ajax' );

/**
 * Test send mail ajax.
 */
function giftflow_test_send_mail_ajax() {
	// check nonce.
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'giftflow_admin_nonce' ) ) {
		wp_send_json_error( 'Invalid nonce' );
	}

	// name.
	$name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';

	// do action test mail.
	do_action( 'giftflow_test_send_mail', $name );
	die();
}

// test send mail action.
add_action( 'giftflow_test_send_mail', 'giftflow_test_send_mail_action' );

/**
 * Test send mail action.
 *
 * @param string $name Name of the test email.
 * @return bool Whether the email was sent successfully.
 */
function giftflow_test_send_mail_action( $name ) {

	$admin_email = giftflow_get_admin_email_address();

	switch ( $name ) {

		case 'admin_new_donation':
			ob_start();
			giftflow_load_template(
				'email/new-donation-admin.php',
				array(
					'campaign_name'  => '<Campaign Name>',
					'campaign_url'   => '#',
					'donor_name'     => '<Donor Name>',
					'donor_email'    => '<Donor Email>',
					'amount'         => '<Amount>',
					'date'           => '<Date>',
					'status'         => '<Status>',
					'payment_method' => '<Payment Method>',
				)
			);
			$content = ob_get_clean();

			return giftflow_send_mail_template(
				array(
					'to'      => $admin_email,
					'subject' => 'New Donation: <Campaign Name>',
					'header'  => 'New donation received',
					'content' => $content,
				)
			);

		case 'donor_thanks':
			ob_start();
			giftflow_load_template(
				'email/thanks-donor.php',
				array(
					'campaign_name'       => '<Campaign Name>',
					'campaign_url'        => '#',
					'donor_name'          => '<Donor Name>',
					'donor_email'         => '<Donor Email>',
					'amount'              => '<Amount>',
					'date'                => '<Date>',
					'donor_dashboard_url' => '#',
				)
			);
			$content = ob_get_clean();

			return giftflow_send_mail_template(
				array(
					'to'      => $admin_email,
					'subject' => 'Hi <Donor Name>, Thanks for Your Donation',
					'header'  => 'Thanks for Your Donation',
					'content' => $content,
				)
			);

		case 'new_user_first_time_donation':
			ob_start();
			giftflow_load_template(
				'email/new-user.php',
				array(
					'name'      => '<Name>',
					'username'  => '<Username>',
					'password'  => '<Password>',
					'login_url' => '<Login URL>',
				)
			);
			$content = ob_get_clean();
			return giftflow_send_mail_template(
				array(
					'to'      => $admin_email,
					'subject' => 'New User Registered',
					'header'  => 'New User Registered',
					'content' => $content,
				)
			);
	}
}

// Notify the admin once the donation is confirmed, or when a bank transfer is recorded as pending.
add_action( 'giftflow_donation_status_completed', 'giftflow_send_admin_new_donation_email_on_completed', 10, 1 );
add_action( 'giftflow_bank_transfer_payment_pending', 'giftflow_send_admin_new_donation_email_on_bank_transfer_pending', 10, 1 );

/**
 * Send the admin new donation notification when a donation reaches completed.
 *
 * @param int $donation_id The donation ID.
 * @return bool Whether the email was sent.
 */
function giftflow_send_admin_new_donation_email_on_completed( $donation_id ) {
	return giftflow_send_admin_new_donation_email( $donation_id );
}

/**
 * Send the admin new donation notification for a pending bank transfer.
 *
 * Direct bank transfer is confirmed manually, so the admin must be told while
 * the donation is still pending.
 *
 * @param int $donation_id The donation ID.
 * @return bool Whether the email was sent.
 */
function giftflow_send_admin_new_donation_email_on_bank_transfer_pending( $donation_id ) {
	return giftflow_send_admin_new_donation_email( $donation_id );
}

/**
 * Send the admin new donation notification at most once per donation.
 *
 * The `_admin_new_donation_email_sent` marker is claimed before dispatch so the
 * pending bank-transfer path and a later completed transition cannot both send.
 *
 * @param int $donation_id The donation ID.
 * @return bool Whether the email was sent.
 */
function giftflow_send_admin_new_donation_email( $donation_id ) {
	$donation_id = absint( $donation_id );

	if ( ! $donation_id ) {
		return false;
	}

	// get donation data.
	$donation_data = giftflow_get_donation_data_by_id( $donation_id );

	if ( ! is_object( $donation_data ) ) {
		return false;
	}

	// Claim the send before dispatch; a second claim finds the marker and bails.
	if ( ! add_post_meta( $donation_id, '_admin_new_donation_email_sent', time(), true ) ) {
		return false;
	}

	$admin_email = giftflow_get_admin_email_address();

	ob_start();
	// Allow filtering/modifying the arguments passed to the admin new donation email template.
	$admin_email_args = apply_filters(
		'giftflow_new_donation_admin_email_args',
		array(
			'donation_id'       => $donation_id,
			'donation_edit_url' => $donation_data->donation_edit_url,
			'campaign_name'     => $donation_data->campaign_name,
			'campaign_url'      => $donation_data->campaign_url,
			'donor_name'        => $donation_data->donor_name,
			'donor_email'       => $donation_data->donor_email,
			'amount'            => $donation_data->__amount_formatted,
			'date'              => $donation_data->__date,
			'status'            => $donation_data->status,
			'payment_method'    => $donation_data->payment_method,
		),
		$donation_id,
		$donation_data
	);

	giftflow_load_template( 'email/new-donation-admin.php', $admin_email_args );
	$content = ob_get_clean();

	$sent = giftflow_send_mail_template(
		array(
			'to'      => $admin_email,
			'subject' => apply_filters(
				'giftflow_new_donation_admin_email_subject',
				// translators: %s: Campaign name for the new donation notification email subject.
				sprintf( esc_html__( 'New Donation — %s', 'giftflow' ), $donation_data->campaign_name ),
				$donation_id,
				$donation_data
			),
			'header'  => apply_filters(
				'giftflow_new_donation_admin_email_header',
				esc_html__( 'New donation received', 'giftflow' ),
				$donation_id,
				$donation_data
			),
			'content' => $content,
		)
	);

	if ( ! $sent ) {
		// Release the claim so a later delivery attempt can still send.
		delete_post_meta( $donation_id, '_admin_new_donation_email_sent' );
	}

	return (bool) $sent;
}

/**
 * Send mail notification donation to admin.
 *
 * Retained as a public entry point for callers that already hold a donation
 * record. Real flows are driven by the completed-status and bank-pending hooks
 * above.
 *
 * @param int $donation_id The donation ID.
 * @return bool Whether the email was sent successfully.
 */
function giftflow_send_mail_notification_donation_to_admin( $donation_id ) {
	return giftflow_send_admin_new_donation_email( $donation_id );
}

// Send the donor thank-you only when the donation is actually confirmed as completed.
add_action( 'giftflow_donation_status_completed', 'giftflow_send_donor_thank_you_email_on_completed', 10, 1 );

// Direct bank transfer is confirmed through Donation Management, which writes the status meta directly.
add_action( 'giftflow_donation_management_saved', 'giftflow_send_donor_thank_you_email_after_management_save', 10, 4 );

/**
 * Send the donor thank-you when a donation transitions to completed.
 *
 * @param int $donation_id The donation ID.
 * @return bool Whether the email was sent.
 */
function giftflow_send_donor_thank_you_email_on_completed( $donation_id ) {
	return giftflow_send_donor_thank_you_email( $donation_id );
}

/**
 * Send the donor thank-you after a Donation Management save completes a donation.
 *
 * The management screen writes `_status` directly instead of going through
 * Donations::update_status(), so the status hooks do not fire for a direct bank
 * transfer confirmed by an administrator.
 *
 * @param int    $donation_id The donation ID.
 * @param string $old_status Status before the save.
 * @param string $new_status Status after the save.
 * @param int    $actor_id Acting user ID. Unused.
 * @return bool Whether the email was sent.
 */
function giftflow_send_donor_thank_you_email_after_management_save( $donation_id, $old_status, $new_status, $actor_id ) {
	unset( $actor_id );

	if ( 'completed' !== $new_status || 'completed' === $old_status ) {
		return false;
	}

	return giftflow_send_donor_thank_you_email( $donation_id );
}

/**
 * Send the donor thank-you email at most once per completed donation.
 *
 * The `_thank_you_email_sent` marker is claimed before dispatch, so paths that
 * report the same success twice (for example PayPal fires both the
 * after-payment action and the status update) cannot send duplicates.
 *
 * @param int $donation_id The donation ID.
 * @return bool Whether the email was sent.
 */
function giftflow_send_donor_thank_you_email( $donation_id ) {
	$donation_id = absint( $donation_id );

	if ( ! $donation_id ) {
		return false;
	}

	// Only a donation that is actually completed may receive the thank-you.
	if ( 'completed' !== (string) get_post_meta( $donation_id, '_status', true ) ) {
		return false;
	}

	// Claim the send before dispatch; a second claim finds the marker and bails.
	if ( ! add_post_meta( $donation_id, '_thank_you_email_sent', time(), true ) ) {
		return false;
	}

	// get giftflow_get_donor_account_page() url.
	$donor_account_url = get_permalink( giftflow_get_donor_account_page() );

	// get donation data.
	$donation_data = giftflow_get_donation_data_by_id( $donation_id );

	if ( ! is_object( $donation_data ) ) {
		delete_post_meta( $donation_id, '_thank_you_email_sent' );

		return false;
	}

	// send thanks email.
	ob_start();
	// Allow filtering/modifying the arguments passed to the donor thank you email template.
	$thanks_donor_args = apply_filters(
		'giftflow_thanks_donor_email_args',
		array(
			'donation_id'         => $donation_id,
			'campaign_name'       => $donation_data->campaign_name,
			'campaign_url'        => $donation_data->campaign_url,
			'donor_name'          => $donation_data->donor_name,
			'donor_email'         => $donation_data->donor_email,
			'amount'              => $donation_data->__amount_formatted,
			'date'                => $donation_data->__date,
			'donor_dashboard_url' => $donor_account_url,
		),
		$donation_id,
		$donation_data
	);

	giftflow_load_template( 'email/thanks-donor.php', $thanks_donor_args );
	$content = ob_get_clean();

	$sent = giftflow_send_mail_template(
		array(
			'to'      => $donation_data->donor_email,
			'subject' => apply_filters(
				'giftflow_thanks_donor_email_subject',
				// translators: %s: Campaign name for donor thank you email subject.
				sprintf( esc_html__( 'Thank You for Your Donation — %s', 'giftflow' ), $donation_data->campaign_name ),
				$donation_id,
				$donation_data
			),
			'header'  => apply_filters(
				'giftflow_thanks_donor_email_header',
				esc_html__( 'Thanks for supporting us', 'giftflow' ),
				$donation_id,
				$donation_data
			),
			'content' => $content,
		)
	);

	if ( ! $sent ) {
		// Release the claim so a later delivery attempt can still send.
		delete_post_meta( $donation_id, '_thank_you_email_sent' );
	}

	return (bool) $sent;
}

/**
 * Send mail thank you to donor payment successful.
 *
 * Retained as a public entry point for callers that already hold a definitive
 * payment result. Real payment flows no longer depend on this signature; they
 * are driven by the completed-status hooks above.
 *
 * @param int   $donation_id The donation ID.
 * @param mixed $payment_result The payment result.
 * @return bool|null Whether the email was sent, null when the result is not a success.
 */
function giftflow_send_mail_thank_you_to_donor_payment_successful( $donation_id, $payment_result ) {
	if ( true !== $payment_result ) {
		return null;
	}

	return giftflow_send_donor_thank_you_email( $donation_id );
}
