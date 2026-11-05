<?php
/**
 * Canonical PayPal webhook event registry.
 *
 * @package GiftFlow
 * @subpackage PayPal
 */

namespace GiftFlow\PayPal;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Required PayPal webhook events per feature set.
 */
final class PayPalWebhookEventRegistry {

	/** Filter used to customize required events. */
	public const FILTER_EVENTS = 'giftflow_paypal_webhook_required_events';

	/** Events required for one-time payments. */
	public const BASE_EVENTS = array(
		'CHECKOUT.ORDER.APPROVED',
		'CHECKOUT.ORDER.COMPLETED',
		'PAYMENT.CAPTURE.COMPLETED',
		'PAYMENT.CAPTURE.DENIED',
		'PAYMENT.CAPTURE.REFUNDED',
	);

	/** Events required for recurring payments. */
	public const RECURRING_EVENTS = array(
		'BILLING.SUBSCRIPTION.CREATED',
		'BILLING.SUBSCRIPTION.ACTIVATED',
		'BILLING.SUBSCRIPTION.CANCELLED',
		'BILLING.SUBSCRIPTION.SUSPENDED',
		'BILLING.SUBSCRIPTION.EXPIRED',
		'PAYMENT.SALE.COMPLETED',
		'PAYMENT.SALE.DENIED',
	);

	/**
	 * Return required events.
	 *
	 * @param bool   $recurring_enabled Whether recurring payments are enabled.
	 * @param string $environment       Optional environment filter context.
	 * @return string[]
	 */
	public static function required_events( bool $recurring_enabled, string $environment = '' ): array {
		$events = self::BASE_EVENTS;
		if ( $recurring_enabled ) {
			$events = array_merge( $events, self::RECURRING_EVENTS );
		}
		$context = PayPalEnvironment::is_valid( $environment ) ? $environment : '';
		$events  = apply_filters( self::FILTER_EVENTS, $events, $context, $recurring_enabled );
		return self::sorted( is_array( $events ) ? $events : array() );
	}

	/**
	 * Normalize, de-duplicate, and sort events.
	 *
	 * @param array $events Candidate events.
	 * @return string[]
	 */
	public static function sorted( array $events ): array {
		$normalized = array();
		foreach ( $events as $event ) {
			$event = is_scalar( $event ) ? trim( (string) $event ) : '';
			if ( '' !== $event ) {
				$normalized[] = $event;
			}
		}
		$normalized = array_values( array_unique( $normalized ) );
		sort( $normalized, SORT_STRING );
		return $normalized;
	}

	/**
	 * Compare configured and required events.
	 *
	 * @param array $current  Configured events.
	 * @param array $required Required events.
	 * @return array
	 */
	public static function diff( array $current, array $required ): array {
		$current  = self::sorted( $current );
		$required = self::sorted( $required );
		return array(
			'add'    => array_values( array_diff( $required, $current ) ),
			'remove' => array_values( array_diff( $current, $required ) ),
		);
	}

	/**
	 * Determine whether an event is required.
	 *
	 * @param string $event_type        Event type.
	 * @param bool   $recurring_enabled Whether recurring payments are enabled.
	 * @return bool
	 */
	public static function is_required( string $event_type, bool $recurring_enabled ): bool {
		return in_array( $event_type, self::required_events( $recurring_enabled ), true );
	}

	/**
	 * Determine whether the dispatcher handles an event.
	 *
	 * @param string $event_type        Event type.
	 * @param bool   $recurring_enabled Whether recurring payments are enabled.
	 * @return bool
	 */
	public static function is_dispatched( string $event_type, bool $recurring_enabled ): bool {
		$events = self::BASE_EVENTS;
		if ( $recurring_enabled ) {
			$events = array_merge( $events, self::RECURRING_EVENTS );
		}
		return in_array( $event_type, $events, true );
	}
}
