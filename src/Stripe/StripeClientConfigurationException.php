<?php
/**
 * Exception thrown when a Stripe client cannot be constructed safely.
 *
 * The message never contains credential material.
 *
 * @package GiftFlow
 * @subpackage Stripe
 */

namespace GiftFlow\Stripe;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Invalid Stripe client configuration.
 */
class StripeClientConfigurationException extends \InvalidArgumentException {
}
