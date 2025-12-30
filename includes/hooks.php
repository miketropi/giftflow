<?php
/**
 * Hooks file.

 * @package GiftFlow
 */

add_action( 'giftflow_donation_form_after_payment_method', 'giftflow_donation_form_thank_you_section_html', 20 );
add_action( 'giftflow_donation_form_after_payment_method', 'giftflow_donation_form_error_section_html', 22 );

// new user.
add_action( 'giftflow_donation_after_payment_processed', 'giftflow_auto_create_user_on_donation', 10, 2 );

// hooks to add recaptcha field to donation form.
add_action( 'giftflow_donation_form_after_form', 'giftflow_donation_form_add_recaptcha_field', 20 );
add_action( 'giftflow_donation_form_before_process_donation', 'giftflow_donation_form_validate_recaptcha', 10, 1 );

// hooks add custom enqueue scripts.
add_action( 'wp_enqueue_scripts', 'giftflow_donation_form_enqueue_custom_scripts', 20 );
