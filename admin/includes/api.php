<?php
/**
 * API
 *
 * @package GiftFlow
 * @since v1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register REST API routes.
 */
add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'giftflow/v1',
			'/campaigns',
			array(
				'methods'             => 'GET',
				'callback'            => 'giftflow_get_campaigns',
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' ); // Editor, Author, Admin.
				},
				// params.
				'args'                => array(
					'per_page' => array(
						'type'    => 'integer',
						'default' => 3,
					),
					'page'     => array(
						'type'    => 'integer',
						'default' => 1,
					),
					'search'   => array(
						'type'    => 'string',
						'default' => '',
					),
					'order'    => array(
						'type'    => 'string',
						'default' => 'desc',
					),
					'orderby'  => array(
						'type'    => 'string',
						'default' => 'date',
					),
					// include.
					'include'  => array(
						'type'    => 'array',
						'default' => array(),
					),
					// exclude.
					// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
					'exclude'  => array(
						'type'    => 'array',
						'default' => array(),
					),
				),
			)
		);

		register_rest_route(
			'giftflow/v1',
			'/dashboard/overview',
			array(
				'methods'             => 'GET',
				'callback'            => 'giftflow_get_dashboard_overview',
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' ); // Editor, Author, Admin.
				},
			)
		);

		register_rest_route(
			'giftflow/v1',
			'/dashboard/statistics/charts',
			array(
				'methods'             => 'GET',
				'callback'            => 'giftflow_get_dashboard_statistics_charts',
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' ); // Editor, Author, Admin.
				},
				'args'                => array(
					'period' => array(
						'type'    => 'string',
						'default' => '30d',
					),
				),
			)
		);

		// register route create campaign csv.
		register_rest_route(
			'giftflow/v1',
			'/campaign/csv-export',
			array(
				'methods'             => 'GET',
				'callback'            => 'giftflow_export_campaign_csv',
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' ); // Editor, Author, Admin.
				},
				'args'                => array(
					'campaign_id' => array(
						'type'     => 'integer',
						'required' => true,
					),
					'date_from'   => array(
						'type'     => 'string',
						'required' => false,
					),
					'date_to'     => array(
						'type'     => 'string',
						'required' => false,
					),
				),
			)
		);
	}
);

/**
 * Handle GET /wp-json/giftflow/v1/campaigns.
 * Returns a list of campaigns.

 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response The response object.
 */
function giftflow_get_campaigns( $request ) {
	// Example: Fetch campaigns from a custom post type 'campaign'.
	$args  = array(
		'post_type'      => 'campaign',
		'post_status'    => 'publish',
		'posts_per_page' => $request->get_param( 'per_page' ),
		'paged'          => $request->get_param( 'page' ),
		's'              => $request->get_param( 'search' ),
		'order'          => $request->get_param( 'order' ),
		'orderby'        => $request->get_param( 'orderby' ),
		'post__in'       => $request->get_param( 'include' ),
		// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
		'post__not_in'   => $request->get_param( 'exclude' ),
	);
	$query = new WP_Query( $args );

	$campaigns = array();

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();

			$goal_amount   = (float) get_post_meta( get_the_ID(), '_goal_amount', true );
			$raised_amount = (float) giftflow_get_campaign_raised_amount( get_the_ID() );
			$percentage    = giftflow_get_campaign_progress_percentage( get_the_ID() );

			// convert to currency.
			$__goal_amount   = giftflow_render_currency_formatted_amount( $goal_amount );
			$__raised_amount = giftflow_render_currency_formatted_amount( $raised_amount );

			$campaigns[] = array(
				'id'              => strval( get_the_ID() ),
				'title'           => get_the_title(),
				'excerpt'         => get_the_excerpt(),
				'thumbnail'       => get_the_post_thumbnail_url( get_the_ID(), 'medium' ),
				'goal_amount'     => $goal_amount,
				'start_date'      => get_post_meta( get_the_ID(), '_start_date', true ),
				'end_date'        => get_post_meta( get_the_ID(), '_end_date', true ),
				'status'          => get_post_meta( get_the_ID(), '_status', true ),
				'link'            => get_the_permalink(),
				// Add more fields as needed, e.g. goal, raised, percent, etc.
				'percentage'      => $percentage,
				'raised_amount'   => $raised_amount,
				'__goal_amount'   => $__goal_amount,
				'__raised_amount' => $__raised_amount,
			);
		}
		wp_reset_postdata();
	}

	return rest_ensure_response( $campaigns );
}

/**
 * Handle GET /wp-json/giftflow/v1/dashboard/overview.
 * Returns the dashboard overview data.

 * @return WP_REST_Response The response object.
 */
function giftflow_get_dashboard_overview() {
	$total_raised = giftflow_get_total_donations_amount();
	$data         = array(
		'total_raised'           => $total_raised,
		'__total_raised'         => giftflow_render_currency_formatted_amount( $total_raised ),
		'total_active_campaigns' => giftflow_get_total_campaigns_by_status( 'active' ),
		'total_donors'           => giftflow_get_total_donors_count(),
		'recent_donations'       => giftflow_get_recent_donations(),
	);
	return rest_ensure_response( $data );
}

/**
 * Handle GET /wp-json/giftflow/v1/dashboard/statistics/charts.
 * Returns the statistics charts data.

 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response The response object.
 */
function giftflow_get_dashboard_statistics_charts( $request ) {
	$period = $request->get_param( 'period' );
	$data   = array(
		'donations_overview_chart_by_period' => giftflow_get_donations_overview_stats_by_period( $period ),
	);
	return rest_ensure_response( $data );
}

/**
 * Handle POST /wp-json/giftflow/v1/campaign/csv-export.
 * Exports the campaign CSV.

 * @param WP_REST_Request $request The request object.
 */
function giftflow_export_campaign_csv( $request ) {
	// ⚠️ Disable cache & buffer
	if ( ob_get_length() ) {
		ob_end_clean();
	}

	$campaign_id = $request->get_param( 'campaign_id' );
	$date_from   = $request->get_param( 'date_from' );
	$date_to     = $request->get_param( 'date_to' );
	$export      = new GiftFlow_Export( $campaign_id, 'all', $date_from, $date_to, true, true, 'csv' );
}
