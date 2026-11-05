<?php
/**
 * Canonical Stripe webhook event registry.
 *
 * The registry is the single source of truth for the events an environment's
 * endpoint must subscribe to. Both the provisioner and the admin panel read it,
 * so the UI can never advertise an event the receiver ignores.
 *
 * @package GiftFlow
 * @subpackage Stripe
 */

namespace GiftFlow\Stripe;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Required webhook events per feature set.
 */
final class StripeWebhookEventRegistry {

	/**
	 * Filter that may extend or trim the required event list.
	 *
	 * @var string
	 */
	public const FILTER_EVENTS = 'giftflow_stripe_webhook_required_events';

	/**
	 * Events required by one-time donations.
	 *
	 * @var string[]
	 */
	public const BASE_EVENTS = array(
		'payment_intent.succeeded',
		'payment_intent.payment_failed',
		'payment_intent.canceled',
		'charge.refunded',
	);

	/**
	 * Additional events required when recurring donations are enabled.
	 *
	 * @var string[]
	 */
	public const RECURRING_EVENTS = array(
		'invoice.paid',
		'invoice.payment_failed',
		'invoice_payment.paid',
		'customer.subscription.deleted',
		'customer.subscription.updated',
	);

	/**
	 * Events required for the given feature set, sorted and unique.
	 *
	 * @param bool   $recurring_enabled Whether recurring donations are enabled.
	 * @param string $environment       Environment the list is requested for (filter context only).
	 * @return string[]
	 */
	public static function required_events( bool $recurring_enabled, string $environment = '' ): array {
		$events = self::BASE_EVENTS;

		if ( $recurring_enabled ) {
			$events = array_merge( $events, self::RECURRING_EVENTS );
		}

		$context = StripeEnvironment::is_valid( $environment ) ? $environment : '';

		/**
		 * Filter the webhook events required for an environment.
		 *
		 * @since 1.1.14
		 *
		 * @param string[] $events            Required event names.
		 * @param string   $environment       Environment the list applies to, or '' when unknown.
		 * @param bool     $recurring_enabled Whether recurring donations are enabled.
		 */
		$events = apply_filters( self::FILTER_EVENTS, $events, $context, $recurring_enabled );

		return self::sorted( is_array( $events ) ? $events : array() );
	}

	/**
	 * Sort and de-duplicate an event list.
	 *
	 * @param array<mixed> $events Candidate events.
	 * @return string[]
	 */
	public static function sorted( array $events ): array {
		$normalized = StripeConnectionRepository::normalize_events( $events );
		sort( $normalized, SORT_STRING );

		return $normalized;
	}

	/**
	 * Compare two event sets.
	 *
	 * @param array<mixed> $current  Events currently configured remotely.
	 * @param array<mixed> $required Events required locally.
	 * @return array{add:string[],remove:string[]}
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
	 * Whether an event name is part of the required set.
	 *
	 * @param string $event_type        Event name.
	 * @param bool   $recurring_enabled Whether recurring donations are enabled.
	 * @return bool
	 */
	public static function is_required( string $event_type, bool $recurring_enabled ): bool {
		return in_array( $event_type, self::required_events( $recurring_enabled ), true );
	}
}
