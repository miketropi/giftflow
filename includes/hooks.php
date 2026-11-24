<?php
/**
 * Hooks file.

 * @package GiftFlow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'giftflow_donation_form_after_payment_method', 'giftflow_donation_form_thank_you_section_html', 20 );
add_action( 'giftflow_donation_form_after_payment_method', 'giftflow_donation_form_error_section_html', 22 );

// new user: only once the donation is confirmed as completed, never for a
// pending/requires_action/failed payment.
add_action( 'giftflow_donation_status_completed', 'giftflow_auto_create_user_on_donation', 10, 1 );

// hooks to add recaptcha field to donation form.
add_action( 'giftflow_donation_form_after_form', 'giftflow_donation_form_add_recaptcha_field', 20 );
add_action( 'giftflow_donation_form_before_process_donation', 'giftflow_donation_form_validate_recaptcha', 10, 1 );

// hooks add custom enqueue scripts.
add_action( 'wp_enqueue_scripts', 'giftflow_donation_form_enqueue_custom_scripts', 20 );


// hooks for my donations table.
add_action( 'giftflow_my_donations_table_before', 'giftflow_my_donations_filter_form', 10, 3 );

// hooks redirect gf-direct-to.
add_action( 'template_redirect', 'giftflow_redirect_gf_direct_to', 10 );

// Documented campaign lifecycle hooks (see GiftFlow\Core\HookManager).
add_action( 'transition_post_status', 'giftflow_fire_campaign_published_hook', 10, 3 );

/**
 * Fire HookManager::CAMPAIGN_PUBLISHED when a campaign first becomes public.
 *
 * @param string  $new_status New post status.
 * @param string  $old_status Previous post status.
 * @param WP_Post $post       Post object.
 * @return void
 */
function giftflow_fire_campaign_published_hook( $new_status, $old_status, $post ) {
	if ( ! $post instanceof WP_Post || 'campaign' !== $post->post_type ) {
		return;
	}

	if ( 'publish' === $new_status && 'publish' !== $old_status ) {
		do_action( \GiftFlow\Core\HookManager::CAMPAIGN_PUBLISHED, $post->ID );
	}
}

/**
 * Remember the previous campaign status before `_status` is written.
 *
 * @param mixed  $check     Short-circuit value.
 * @param int    $object_id Post ID.
 * @param string $meta_key  Meta key.
 * @return mixed
 */
function giftflow_capture_campaign_status_hook( $check, $object_id, $meta_key ) {
	if ( '_status' !== $meta_key || 'campaign' !== get_post_type( $object_id ) ) {
		return $check;
	}

	$GLOBALS['giftflow_campaign_status_previous'][ $object_id ] = (string) get_post_meta( $object_id, '_status', true );

	return $check;
}
add_filter( 'update_post_metadata', 'giftflow_capture_campaign_status_hook', 10, 3 );

/**
 * Fire HookManager::CAMPAIGN_ENDED when `_status` becomes completed/closed.
 *
 * @param int    $meta_id    Meta row ID.
 * @param int    $object_id  Post ID.
 * @param string $meta_key   Meta key.
 * @param mixed  $meta_value New value.
 * @return void
 */
function giftflow_fire_campaign_ended_hook( $meta_id, $object_id, $meta_key, $meta_value ) {
	if ( '_status' !== $meta_key || 'campaign' !== get_post_type( $object_id ) ) {
		return;
	}

	$previous = isset( $GLOBALS['giftflow_campaign_status_previous'][ $object_id ] )
		? (string) $GLOBALS['giftflow_campaign_status_previous'][ $object_id ]
		: '';

	unset( $GLOBALS['giftflow_campaign_status_previous'][ $object_id ] );

	$ended = array( 'completed', 'closed' );

	if ( in_array( (string) $meta_value, $ended, true ) && ! in_array( $previous, $ended, true ) ) {
		do_action( \GiftFlow\Core\HookManager::CAMPAIGN_ENDED, $object_id );
	}
}
add_action( 'updated_post_meta', 'giftflow_fire_campaign_ended_hook', 10, 4 );
// A first-time `_status` write goes through add_metadata(), not update_metadata().
add_action( 'added_post_meta', 'giftflow_fire_campaign_ended_hook', 10, 4 );
