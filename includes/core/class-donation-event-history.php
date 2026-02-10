<?php
/**
 * Donation Event History class for GiftFlow
 *
 * Tracks donation payment lifecycle events in a custom table.
 * Add a record on each process payment (e.g. payment_succeeded, completed).
 *
 * @package GiftFlow
 * @subpackage Core
 */

namespace GiftFlow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Donation Event History – records per donation payment process.
 *
 * Usage:
 *   Donation_Event_History::add( $donation_id, 'payment_succeeded', 'completed', 'Stripe charge captured', [ 'transaction_id' => 'pi_xxx' ] );
 */
class Donation_Event_History {

	/**
	 * Get the history table name.
	 *
	 * @return string
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'giftflow_donation_event_history';
	}

	/**
	 * Create the giftflow_donation_event_history table. Uses dbDelta.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function create_table() {
		global $wpdb;

		$table_name      = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "
		CREATE TABLE {$table_name} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			donation_id BIGINT UNSIGNED NOT NULL,
			event VARCHAR(50) NOT NULL,
			status VARCHAR(30) NOT NULL,
			note TEXT NULL,
			meta LONGTEXT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			KEY idx_donation (donation_id),
			KEY idx_event (event),
			KEY idx_created (created_at)
		) {$charset_collate};
		";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		return true;
	}

	/**
	 * Add a donation payment event record.
	 *
	 * @param int         $donation_id Donation post ID (CPT).
	 * @param string      $event       Event name (e.g. 'payment_succeeded', 'payment_failed', 'payment_pending').
	 * @param string      $status      Status (e.g. 'completed', 'failed', 'pending').
	 * @param string      $note        Optional note. Default empty.
	 * @param array|mixed $meta        Optional meta (will be JSON-encoded). Default empty array.
	 * @return int|false Insert id on success, false on failure.
	 */
	public static function add( $donation_id, $event, $status, $note = '', $meta = array() ) {
		global $wpdb;

		$donation_id = absint( $donation_id );
		if ( ! $donation_id ) {
			return false;
		}

		$table_name  = self::get_table_name();
		$event       = substr( sanitize_text_field( $event ), 0, 50 );
		$status      = substr( sanitize_text_field( $status ), 0, 30 );
		$note        = ! empty( $note ) ? sanitize_textarea_field( $note ) : null;
		$meta_json   = ! empty( $meta ) ? wp_json_encode( $meta ) : null;
		$created_at  = current_time( 'mysql' );

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->insert(
			$table_name,
			array(
				'donation_id' => $donation_id,
				'event'       => $event,
				'status'      => $status,
				'note'        => $note,
				'meta'        => $meta_json,
				'created_at'  => $created_at,
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s' )
		);

		return $result ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Get event history for a donation.
	 *
	 * @param int   $donation_id Donation post ID.
	 * @param array $args       Optional. Order, orderby, limit. Default latest first.
	 * @return array Array of row objects.
	 */
	public static function get_by_donation( $donation_id, $args = array() ) {
		global $wpdb;

		$donation_id = absint( $donation_id );
		if ( ! $donation_id ) {
			return array();
		}

		$table_name = self::get_table_name();
		$order      = isset( $args['order'] ) && strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';
		$orderby    = isset( $args['orderby'] ) && in_array( $args['orderby'], array( 'id', 'event', 'status', 'created_at' ), true ) ? $args['orderby'] : 'created_at';
		$limit      = isset( $args['limit'] ) ? absint( $args['limit'] ) : 0;

		$sql = $wpdb->prepare(
			'SELECT * FROM %s WHERE donation_id = %d ORDER BY %s %s',
			$table_name,
			$donation_id,
			$orderby,
			$order
		);
		if ( $limit > 0 ) {
			$sql .= $wpdb->prepare( ' LIMIT %d', $limit );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_results( $sql );
	}

	/**
	 * Register the event history meta box on donation edit screen (sidebar).
	 */
	public static function register_meta_box() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ) );
	}

	/**
	 * Add the meta box to donation CPT.
	 */
	public static function add_meta_box() {
		add_meta_box(
			'giftflow_donation_event_history',
			__( 'Event History', 'giftflow' ),
			array( __CLASS__, 'render_meta_box' ),
			'donation',
			'side',
			'default'
		);
	}

	/**
	 * Render the event history meta box content.
	 *
	 * @param \WP_Post $post Donation post.
	 */
	public static function render_meta_box( $post ) {
		$donation_id = (int) $post->ID;
		$rows        = self::get_by_donation( $donation_id, array( 'limit' => 50 ) );
		$events      = array();

		foreach ( $rows as $row ) {
			$meta = ! empty( $row->meta ) ? json_decode( $row->meta, true ) : array();
			$events[] = array(
				'event_label'  => self::format_event_label( $row->event ),
				'status'       => $row->status,
				'status_label' => self::format_status_label( $row->status ),
				'gateway'      => isset( $meta['gateway'] ) ? self::format_gateway_label( $meta['gateway'] ) : '',
				'note'         => $row->note ? $row->note : '',
				'date'         => $row->created_at ? wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $row->created_at ) ) : '',
			);
		}

		giftflow_load_template( 'admin/donation-metabox-event-history.php', array( 'events' => $events ) );
	}

	/**
	 * Human-readable label for event slug.
	 *
	 * @param string $event Event slug.
	 * @return string
	 */
	public static function format_event_label( $event ) {
		$labels = array(
			'payment_succeeded'      => __( 'Payment succeeded', 'giftflow' ),
			'payment_failed'         => __( 'Payment failed', 'giftflow' ),
			'payment_pending'        => __( 'Payment pending', 'giftflow' ),
			'payment_processing'    => __( 'Payment processing', 'giftflow' ),
			'payment_requires_action' => __( 'Requires action', 'giftflow' ),
			'payment_canceled'       => __( 'Payment canceled', 'giftflow' ),
			'payment_refunded'       => __( 'Payment refunded', 'giftflow' ),
			'admin_status_updated'  => __( 'Status updated by admin', 'giftflow' ),
		);
		return isset( $labels[ $event ] ) ? $labels[ $event ] : str_replace( '_', ' ', $event );
	}

	/**
	 * Human-readable label for status slug.
	 *
	 * @param string $status Status slug.
	 * @return string
	 */
	public static function format_status_label( $status ) {
		$labels = array(
			'completed' => __( 'Completed', 'giftflow' ),
			'failed'    => __( 'Failed', 'giftflow' ),
			'pending'   => __( 'Pending', 'giftflow' ),
			'processing' => __( 'Processing', 'giftflow' ),
			'cancelled' => __( 'Cancelled', 'giftflow' ),
			'refunded'  => __( 'Refunded', 'giftflow' ),
		);
		return isset( $labels[ $status ] ) ? $labels[ $status ] : $status;
	}

	/**
	 * Human-readable label for gateway id.
	 *
	 * @param string $gateway Gateway id.
	 * @return string
	 */
	public static function format_gateway_label( $gateway ) {
		$options = giftflow_get_payment_methods_options();
		return isset( $options[ $gateway ] ) ? $options[ $gateway ] : $gateway;
	}
}
