<?php
/**
 * Dashboard Widget Functions for GiftFlow

 * @package GiftFlow
 * @subpackage Admin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get total campaigns count.

 * @return int The number of total campaigns.
 */
function giftflow_get_total_campaigns() {
	$campaigns = get_posts(
		array(
			'post_type'   => 'campaign',
			'post_status' => 'publish',
			'numberposts' => -1,
			'fields'      => 'ids',
		)
	);
	return count( $campaigns );
}

/**
 * Get active campaigns count.

 * @param string $status The status of the campaigns.
 * @return int The number of active campaigns.
 */
function giftflow_count_campaigns_by_status( $status = 'active' ) {
	$campaigns = get_posts(
		array(
			'post_type'   => 'campaign',
			'post_status' => 'publish',
			'numberposts' => -1,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query'  => array(
				array(
					'key'     => '_status',
					'value'   => $status,
					'compare' => '=',
				),
			),
			'fields'      => 'ids',
		)
	);
	return count( $campaigns );
}

/**
 * Get total donations amount.

 * @return float The total amount of donations.
 */
function giftflow_get_total_donations_amount() {
	global $wpdb;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$total = $wpdb->get_var(
		$wpdb->prepare(
			"
			SELECT SUM(CAST(pm.meta_value AS DECIMAL(10,2)))
			FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			INNER JOIN {$wpdb->postmeta} status_pm ON status_pm.post_id = p.ID
				AND status_pm.meta_key = %s
				AND status_pm.meta_value = %s
			WHERE pm.meta_key = %s
			AND p.post_type = %s
			AND p.post_status = %s
			",
			'_status',
			'completed',
			'_amount',
			'donation',
			'publish'
		)
	);

	return $total ? $total : 0;
}

/**
 * Get total donors count.

 * @return int The number of total donors.
 */
function giftflow_get_total_donors() {
	$donors = get_posts(
		array(
			'post_type'   => 'donor',
			'post_status' => 'publish',
			'numberposts' => -1,
			'fields'      => 'ids',
		)
	);
	return count( $donors );
}

/**
 * Get highlight campaigns (top 3 by raised amount).

 * @return array The highlight campaigns.
 */
function giftflow_get_highlight_campaigns() {
	/**
	 * Filter to allow modification of highlight campaigns query args.
	 */
	$args      = array(
		'post_type'   => 'campaign',
		'post_status' => 'publish',
		'numberposts' => 5,
		'orderby'     => 'date',
		'order'       => 'DESC',
	);
	$args      = apply_filters( 'giftflow_highlight_campaigns_query_args', $args );
	$campaigns = get_posts( $args );
	$highlight_campaigns = array();
	foreach ( $campaigns as $campaign ) {
		$goal = get_post_meta( $campaign->ID, '_goal_amount', true );
		$raised = giftflow_get_campaign_raised_amount( $campaign->ID );
		$percentage = $goal > 0 ? round( ( $raised / $goal ) * 100, 1 ) : 0;
		$highlight_campaigns[] = array(
			'id'         => $campaign->ID,
			'title'      => $campaign->post_title,
			'link'       => get_edit_post_link( $campaign->ID ),
			'goal'       => $goal,
			'raised'     => $raised,
			'percentage' => $percentage,
		);
	}
	return $highlight_campaigns;
}

/**
 * Get recent donations (last 5).

 * @return array The recent donations.
 */
function giftflow_get_recent_donations() {
	/**
	 * Filter to allow modification of recent donations query args.
	 */
	$args = array(
		'post_type'   => 'donation',
		'post_status' => 'publish',
		'numberposts' => 5,
		'orderby'     => 'date',
		'order'       => 'DESC',
	);
	$args = apply_filters( 'giftflow_recent_donations_query_args', $args );
	$donations = get_posts( $args );
	$recent_donations = array();
	foreach ( $donations as $donation ) {
		$amount = get_post_meta( $donation->ID, '_amount', true );
		$donor_id = get_post_meta( $donation->ID, '_donor_id', true );
		$campaign_id = get_post_meta( $donation->ID, '_campaign_id', true );
		$donor_name = esc_html__( '??', 'giftflow' );
		$donor_link = '#not-found';
		$donor_email = '#not-found';
		if ( $donor_id ) {
			$first_name = get_post_meta( $donor_id, '_first_name', true );
			$last_name = get_post_meta( $donor_id, '_last_name', true );
			$donor_name = trim( $first_name . ' ' . $last_name );
			$donor_link = esc_url( get_edit_post_link( $donor_id ) );
			$donor_email = get_post_meta( $donor_id, '_email', true );
		}
		$campaign_title = esc_html__( '??', 'giftflow' );
		$campaign_link = '#not-found';
		$campaign_id = $campaign_id ?? '';
		if ( $campaign_id ) {
			$campaign = get_post( $campaign_id );
			if ( $campaign ) {
				$campaign_title = $campaign->post_title;
				$campaign_link = esc_url( get_edit_post_link( $campaign_id ) );
			}
		}
		$recent_donations[] = array(
			'id'             => strval( $donation->ID ),
			'donor_name'     => $donor_name,
			'donor_id'       => $donor_id,
			'amount'         => $amount,
			'__amount'       => giftflow_render_currency_formatted_amount( $amount ),
			'payment_method' => get_post_meta( $donation->ID, '_payment_method', true ),
			'status'         => get_post_meta( $donation->ID, '_status', true ),
			'campaign_id'    => $campaign_id,
			'campaign_title' => $campaign_title,
			'campaign_link'  => $campaign_link,
			'date'           => date_i18n( get_option( 'date_format', 'F j, Y' ), strtotime( $donation->post_date ) ),
			'donor_link'     => $donor_link,
			'donor_email'    => $donor_email,
		);
	}
	return $recent_donations;
}

/**
 * Get top donor this month.

 * @return array The top donors.
 */
function giftflow_get_top_donors() {
	global $wpdb;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$results = $wpdb->get_results(
		"SELECT 
		pm1.meta_value as donor_id,
		pm2.meta_value as donor_name,
		SUM(CAST(dm.meta_value AS DECIMAL(10,2))) as total_amount
		FROM {$wpdb->posts} d
		INNER JOIN {$wpdb->postmeta} dm ON d.ID = dm.post_id AND dm.meta_key = '_amount'
		LEFT JOIN {$wpdb->postmeta} pm1 ON d.ID = pm1.post_id AND pm1.meta_key = '_donor_id'
		LEFT JOIN {$wpdb->postmeta} pm2 ON pm1.meta_value = pm2.post_id AND pm2.meta_key = '_email'
		WHERE d.post_type = 'donation'
		AND d.post_status = 'publish'
		GROUP BY donor_id, donor_name
		ORDER BY total_amount DESC
		LIMIT 5"
	);
	if ( $results ) {
		$top_donors = array();
		foreach ( $results as $result ) {
			$top_donors[] = array(
				'link' => get_edit_post_link( $result->donor_id ),
				'name' => $result->donor_name ? esc_html( $result->donor_name ) : esc_html__( 'Anonymous', 'giftflow' ),
				'amount' => $result->total_amount,
			);
		}
		return $top_donors;
	}
	return array(
		array(
			'name' => esc_html__( 'N/A', 'giftflow' ),
			'amount' => 0,
		),
	);
}

/**
 * Get new donors count (last 7 days).

 * @return int The number of new donors.
 */
function giftflow_get_new_donors_count() {
	$new_donors = get_posts(
		array(
			'post_type' => 'donor',
			'post_status' => 'publish',
			'numberposts' => -1,
			'date_query' => array(
				array(
					// Machine-readable relative date: a translated string is not
					// understood by date_create() and silently falls back to 1970.
					'after' => '-7 days',
				),
			),
			'fields' => 'ids',
		)
	);
	return count( $new_donors );
}

/**
 * Get total campaigns by status.

 * @param string $status The status of the campaigns.
 * @return int The number of campaigns by status.
 */
function giftflow_get_total_campaigns_by_status( $status = 'active' ) {
	$campaigns = get_posts(
		array(
			'post_type' => 'campaign',
			'post_status' => 'publish',
			'numberposts' => -1,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query' => array(
				array(
					'key' => '_status',
					'value' => $status,
					'compare' => '=',
				),
			),
			'fields' => 'ids',
		)
	);
	return count( $campaigns );
}

/**
 * Get total donors count.

 * @return int The number of total donors.
 */
function giftflow_get_total_donors_count() {
	$donors = get_posts(
		array(
			'post_type' => 'donor',
			'post_status' => 'publish',
			'numberposts' => -1,
			'fields' => 'ids',
		)
	);
	return count( $donors );
}

/**
 * Get donations stats by period (7 days, 30 days, 6 months, 1 year).

 * @param string $period Allowed values: '7d', '30d', '6m', '1y'. Default: '7d'.
 * @return array The donations stats by period.
 */
function giftflow_get_donations_overview_stats_by_period( $period = '30d' ) {
	global $wpdb;

	$post_type  = 'donation';
	$date_field = 'post_date';

	// Determine date range and grouping.
	$today = current_time( 'Y-m-d' );
	$dates = array();

	switch ( $period ) {
		case '30d':
			$range_days = 30;
			for ( $i = $range_days - 1; $i >= 0; $i-- ) {
				$dates[] = gmdate( 'Y-m-d', strtotime( "-$i days", strtotime( $today ) ) );
			}
			break;

		case '6m':
			$range_months = 6;
			for ( $i = $range_months - 1; $i >= 0; $i-- ) {
				$dates[] = gmdate( 'Y-m', strtotime( "-$i months", strtotime( $today ) ) );
			}
			break;

		case '1y':
			$range_months = 12;
			for ( $i = $range_months - 1; $i >= 0; $i-- ) {
				$dates[] = gmdate( 'Y-m', strtotime( "-$i months", strtotime( $today ) ) );
			}
			break;

		case '7d':
		default:
			$range_days = 7;
			for ( $i = $range_days - 1; $i >= 0; $i-- ) {
				$dates[] = gmdate( 'Y-m-d', strtotime( "-$i days", strtotime( $today ) ) );
			}
			break;
	}

	// Prepare results array.
	$results = array();
	foreach ( $dates as $date ) {
		$results[ $date ] = array(
			'donation_amount' => 0,
			'donors_count'    => 0,
		);
	}

	// Define date range for SQL.
	$first_date = reset( $dates );
	$last_date = end( $dates );
	if ( strlen( $first_date ) === 7 ) {
		// Monthly grouping - use proper last day of month.
		$start_date = $first_date . '-01 00:00:00';
		$end_date = gmdate( 'Y-m-t 23:59:59', strtotime( $last_date . '-01' ) );
	} else {
		// Daily grouping.
		$start_date = $first_date . ' 00:00:00';
		$end_date = $last_date . ' 23:59:59';
	}

	// Query all donations in the range.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
	$donations = $wpdb->get_results(
		$wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"SELECT p.ID, p.{$date_field} FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id WHERE p.post_type = %s AND p.post_status = 'publish' AND pm.meta_key = %s AND pm.meta_value = %s AND p.{$date_field} BETWEEN %s AND %s",
			$post_type,
			'_status',
			'completed',
			$start_date,
			$end_date
		)
	);

	// Group donations and sum donation amounts.
	foreach ( $donations as $donation ) {
		$group_key = ( in_array( $period, array( '6m', '1y' ), true ) )
			? gmdate( 'Y-m', strtotime( $donation->post_date ) )
			: gmdate( 'Y-m-d', strtotime( $donation->post_date ) );

		if ( ! isset( $results[ $group_key ] ) ) {
			continue;
		}

		// Sum donation amount.
		$amount = (float) get_post_meta( $donation->ID, '_amount', true );
		$results[ $group_key ]['donation_amount'] += $amount;
	}

	// Count donors from donor post type by created date.
	foreach ( $results as $date => &$data ) {
		if ( strlen( $date ) === 7 ) {
			// Monthly grouping - use proper last day of month.
			$start = $date . '-01 00:00:00';
			$end   = gmdate( 'Y-m-t 23:59:59', strtotime( $date . '-01' ) );
		} else {
			// Daily grouping.
			$start = $date . ' 00:00:00';
			$end   = $date . ' 23:59:59';
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$donor_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(ID)
				FROM {$wpdb->posts}
				WHERE post_type = %s 
				AND post_status = 'publish' 
				AND post_date BETWEEN %s AND %s",
				'donor',
				$start,
				$end
			)
		);
		$data['donors_count'] = intval( $donor_count );
	}

	// Format for chart.js.
	$labels        = array_keys( $results );
	$donations_data = wp_list_pluck( $results, 'donation_amount' );
	$donors_data    = wp_list_pluck( $results, 'donors_count' );

	return array(
		'labels'        => $labels,
		'donationsData' => $donations_data,
		'donorsData'    => $donors_data,
	);
}
