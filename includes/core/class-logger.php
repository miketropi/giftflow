<?php
/**
 * Logger class for GiftFlow
 *
 * @package GiftFlow
 * @subpackage Core
 */

namespace GiftFlow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Logger class that writes to custom giftflow_logs table.
 *
 * Usage:
 *   Giftflow_Logger::info('payment.succeeded', ['amount' => 500, 'currency' => 'USD']);
 *   Giftflow_Logger::error('payment.failed', ['reason' => 'card_declined']);
 */
class Logger {

	/**
	 * Log levels.
	 *
	 * @var string[]
	 */
	const LEVELS = array( 'debug', 'info', 'warning', 'error' );

	/**
	 * Retention in days per level. Older logs are deleted by cleanup().
	 *
	 * @var int[] Level => days to keep.
	 */
	const RETENTION_DAYS = array(
		'debug'   => 7,
		'info'    => 30,
		'warning' => 30,
		'error'   => 90,
	);

	/**
	 * Get the logs table name.
	 *
	 * @return string
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'giftflow_logs';
	}

	/**
	 * Create the giftflow_logs table. Uses dbDelta.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function create_table() {
		global $wpdb;

		$table_name = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "
		CREATE TABLE {$table_name} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			level VARCHAR(20) NOT NULL,
			category VARCHAR(50) NOT NULL,
			event VARCHAR(100) NOT NULL,
			context LONGTEXT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			KEY idx_created_at (created_at),
			KEY idx_level (level),
			KEY idx_event (event)
		) {$charset_collate};
		";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		return true;
	}

	/**
	 * Write a log entry to the table.
	 *
	 * @param string $level   Log level: debug, info, warning, error.
	 * @param string $event   Event name (e.g. 'payment.succeeded').
	 * @param array  $context Optional context data (will be JSON-encoded).
	 * @param string $category Optional category (default 'app').
	 * @return int|false Insert id on success, false on failure.
	 */
	public static function log( $level, $event, $context = array(), $category = 'app' ) {
		global $wpdb;

		$level = strtolower( $level );
		if ( ! in_array( $level, self::LEVELS, true ) ) {
			$level = 'info';
		}

		$table_name = self::get_table_name();
		$context_json = ! empty( $context ) ? wp_json_encode( $context ) : null;
		$created_at  = current_time( 'mysql' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->insert(
			$table_name,
			array(
				'level'      => $level,
				'category'   => substr( sanitize_text_field( $category ), 0, 50 ),
				'event'      => substr( sanitize_text_field( $event ), 0, 100 ),
				'context'    => $context_json,
				'created_at' => $created_at,
			),
			array( '%s', '%s', '%s', '%s', '%s' )
		);

		return $result ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Log at debug level.
	 *
	 * @param string $event   Event name.
	 * @param array  $context Optional context data.
	 * @param string $category Optional category.
	 * @return int|false
	 */
	public static function debug( $event, $context = array(), $category = 'app' ) {
		return self::log( 'debug', $event, $context, $category );
	}

	/**
	 * Log at info level.
	 *
	 * @param string $event   Event name.
	 * @param array  $context Optional context data.
	 * @param string $category Optional category.
	 * @return int|false
	 */
	public static function info( $event, $context = array(), $category = 'app' ) {
		return self::log( 'info', $event, $context, $category );
	}

	/**
	 * Log at warning level.
	 *
	 * @param string $event   Event name.
	 * @param array  $context Optional context data.
	 * @param string $category Optional category.
	 * @return int|false
	 */
	public static function warning( $event, $context = array(), $category = 'app' ) {
		return self::log( 'warning', $event, $context, $category );
	}

	/**
	 * Log at error level.
	 *
	 * @param string $event   Event name.
	 * @param array  $context Optional context data.
	 * @param string $category Optional category.
	 * @return int|false
	 */
	public static function error( $event, $context = array(), $category = 'app' ) {
		return self::log( 'error', $event, $context, $category );
	}

	/**
	 * Cleanup old logs by level retention rules.
	 *
	 * Rules: DEBUG 7 days, INFO 30 days, WARNING 30 days, ERROR 90 days.
	 * Deletes rows where created_at is older than the retention period for that level.
	 *
	 * @return array{ deleted: int, by_level: int[] } Total rows deleted and count per level.
	 */
	public static function cleanup() {
		global $wpdb;

		$table_name = self::get_table_name();
		$now_ts     = current_time( 'mysql' );
		$deleted    = 0;
		$by_level   = array();

		foreach ( self::RETENTION_DAYS as $level => $days ) {
			$cutoff = wp_date( 'Y-m-d H:i:s', $now_ts - ( $days * DAY_IN_SECONDS ) );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$count = $wpdb->query(
				$wpdb->prepare(
					'DELETE FROM %s WHERE level = %s AND created_at < %s',
					$table_name,
					$level,
					$cutoff
				)
			);

			if ( false !== $count ) {
				$by_level[ $level ] = (int) $count;
				$deleted           += (int) $count;
			} else {
				$by_level[ $level ] = 0;
			}
		}

		return array(
			'deleted'   => $deleted,
			'by_level'  => $by_level,
		);
	}
}

// Alias so Giftflow_Logger::info() works as in the example.
class_alias( Logger::class, 'Giftflow_Logger' );
