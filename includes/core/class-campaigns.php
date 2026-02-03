<?php
/**
 * Campaigns class for GiftFlow
 *
 * Handles operations for campaign custom post type
 * with comprehensive hooks and query methods.
 *
 * @package GiftFlow
 * @subpackage Core
 * @since 1.0.0
 * @version 1.0.0
 */

namespace GiftFlow\Core;

use GiftFlow\Core\Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Campaigns Class
 *
 * Provides methods for retrieving and managing campaigns
 * with comprehensive hooks and query support.
 */
class Campaigns extends Base {
	/**
	 * Initialize campaigns
	 */
	public function __construct() {
		parent::__construct();
		$this->init_hooks();
	}

	/**
	 * Initialize WordPress hooks
	 *
	 * @return void
	 */
	private function init_hooks() {
		// Hook into WordPress post operations.
		add_action( 'wp_insert_post', array( $this, 'on_post_insert' ), 10, 3 );
		add_action( 'post_updated', array( $this, 'on_post_updated' ), 10, 3 );
		add_action( 'before_delete_post', array( $this, 'on_post_delete' ), 10, 1 );
		add_action( 'trash_post', array( $this, 'on_post_trash' ), 10, 1 );
		add_action( 'untrash_post', array( $this, 'on_post_untrash' ), 10, 1 );

		// Meta update hooks.
		add_action( 'updated_post_meta', array( $this, 'on_meta_updated' ), 10, 4 );
		add_action( 'added_post_meta', array( $this, 'on_meta_added' ), 10, 4 );
		add_action( 'deleted_post_meta', array( $this, 'on_meta_deleted' ), 10, 4 );
	}

	/**
	 * Get a single campaign
	 *
	 * @param int $campaign_id Campaign ID.
	 * @return array|false Campaign data on success, false on failure.
	 */
	public function get( $campaign_id ) {
		$campaign = get_post( $campaign_id );

		if ( ! $campaign || 'campaign' !== $campaign->post_type ) {
			return false;
		}

		// Get all campaign meta.
		$meta = get_post_meta( $campaign_id );

		// Build campaign data array.
		$campaign_data = array(
			'id' => $campaign_id,
			'title' => $campaign->post_title,
			'content' => $campaign->post_content,
			'excerpt' => get_the_excerpt( $campaign_id ),
			'post_status' => $campaign->post_status,
			'date' => $campaign->post_date,
			'date_gmt' => $campaign->post_date_gmt,
			'modified' => $campaign->post_modified,
			'modified_gmt' => $campaign->post_modified_gmt,
			'author' => $campaign->post_author,
			'featured_image' => get_post_thumbnail_id( $campaign_id ),
			'featured_image_url' => get_the_post_thumbnail_url( $campaign_id, 'large' ),
			'permalink' => get_permalink( $campaign_id ),
			// Campaign meta fields.
			'goal_amount' => isset( $meta['_goal_amount'][0] ) ? floatval( $meta['_goal_amount'][0] ) : 0,
			'raised_amount' => $this->get_raised_amount( $campaign_id ),
			'progress_percentage' => $this->get_progress_percentage( $campaign_id ),
			'start_date' => isset( $meta['_start_date'][0] ) ? $meta['_start_date'][0] : '',
			'end_date' => isset( $meta['_end_date'][0] ) ? $meta['_end_date'][0] : '',
			'location' => isset( $meta['_location'][0] ) ? $meta['_location'][0] : '',
			'gallery' => isset( $meta['_gallery'][0] ) ? $meta['_gallery'][0] : '',
			'one_time' => isset( $meta['_one_time'][0] ) ? filter_var( $meta['_one_time'][0], FILTER_VALIDATE_BOOLEAN ) : false,
			'recurring' => isset( $meta['_recurring'][0] ) ? filter_var( $meta['_recurring'][0], FILTER_VALIDATE_BOOLEAN ) : false,
			'recurring_interval' => isset( $meta['_recurring_interval'][0] ) ? $meta['_recurring_interval'][0] : '',
			'preset_donation_amounts' => giftflow_get_preset_donation_amounts_by_campaign( $campaign_id ),
			'allow_custom_donation_amounts' => isset( $meta['_allow_custom_donation_amounts'][0] ) ? filter_var( $meta['_allow_custom_donation_amounts'][0], FILTER_VALIDATE_BOOLEAN ) : true,
			// Taxonomies.
			'categories' => wp_get_post_terms( $campaign_id, 'campaign-tax', array( 'fields' => 'all' ) ),
			'category_ids' => wp_get_post_terms( $campaign_id, 'campaign-tax', array( 'fields' => 'ids' ) ),
		);

		// Allow filtering of campaign data.
		return apply_filters( 'giftflow_campaign_data', $campaign_data, $campaign_id );
	}

	/**
	 * Get multiple campaigns
	 *
	 * @param array $args Query arguments.
	 * @return array Array of campaign data.
	 */
	public function get_campaigns( $args = array() ) {
		$defaults = array(
			'post_type' => 'campaign',
			'post_status' => 'publish',
			'posts_per_page' => 10,
			'orderby' => 'date',
			'order' => 'DESC',
			'paged' => 1,
			'category' => '',
			'search' => '',
		);

		$args = wp_parse_args( $args, $defaults );

		// Handle category filter.
		if ( ! empty( $args['category'] ) ) {
			$category = $args['category'];
			// Check if it's a term ID or slug.
			if ( is_numeric( $category ) ) {
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				$args['tax_query'] = array(
					array(
						'taxonomy' => 'campaign-tax',
						'field' => 'term_id',
						'terms' => intval( $category ),
					),
				);
			} else {
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				$args['tax_query'] = array(
					array(
						'taxonomy' => 'campaign-tax',
						'field' => 'slug',
						'terms' => sanitize_text_field( $category ),
					),
				);
			}
		}

		// Handle search.
		if ( ! empty( $args['search'] ) ) {
			$args['s'] = sanitize_text_field( $args['search'] );
		}

		// Remove custom args that aren't WP_Query parameters.
		unset( $args['category'] );
		unset( $args['search'] );

		// Allow filtering of query args.
		$args = apply_filters( 'giftflow_campaigns_query_args', $args );

		$query = new \WP_Query( $args );

		$campaigns = array();
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$campaign_data = $this->get( get_the_ID() );
				if ( $campaign_data ) {
					$campaigns[] = $campaign_data;
				}
			}
			wp_reset_postdata();
		}

		// Return campaigns with pagination info.
		return array(
			'campaigns' => apply_filters( 'giftflow_campaigns_data', $campaigns, $args ),
			'total' => $query->found_posts,
			'pages' => $query->max_num_pages,
			'current_page' => isset( $args['paged'] ) ? intval( $args['paged'] ) : 1,
		);
	}

	/**
	 * Get raised amount for a campaign
	 *
	 * @param int $campaign_id Campaign ID.
	 * @return float Raised amount.
	 */
	public function get_raised_amount( $campaign_id ) {
		// Use the common function if available.
		if ( function_exists( 'giftflow_get_campaign_raised_amount' ) ) {
			return giftflow_get_campaign_raised_amount( $campaign_id );
		}

		// Fallback implementation.
		$donations = get_posts(
			array(
				'post_type' => 'donation',
				'posts_per_page' => -1,
				'fields' => 'ids',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'meta_query' => array(
					array(
						'key' => '_campaign_id',
						'value' => $campaign_id,
						'compare' => '=',
					),
					array(
						'key' => '_status',
						'value' => 'completed',
						'compare' => '=',
					),
				),
			)
		);

		$total_amount = 0;
		foreach ( $donations as $id ) {
			$amount = get_post_meta( $id, '_amount', true );
			if ( $amount ) {
				$total_amount += floatval( $amount );
			}
		}

		return $total_amount;
	}

	/**
	 * Get progress percentage for a campaign
	 *
	 * @param int $campaign_id Campaign ID.
	 * @return float Progress percentage (0-100).
	 */
	public function get_progress_percentage( $campaign_id ) {
		// Use the common function if available.
		if ( function_exists( 'giftflow_get_campaign_progress_percentage' ) ) {
			return giftflow_get_campaign_progress_percentage( $campaign_id );
		}

		// Fallback implementation.
		$raised_amount = $this->get_raised_amount( $campaign_id );
		$goal_amount = get_post_meta( $campaign_id, '_goal_amount', true );

		if ( ! $goal_amount || floatval( $goal_amount ) <= 0 ) {
			return 0;
		}

		$percentage = ( $raised_amount / floatval( $goal_amount ) ) * 100;
		return min( 100, max( 0, round( $percentage, 2 ) ) );
	}

	/**
	 * Check if campaign exists
	 *
	 * @param int $campaign_id Campaign ID.
	 * @return bool True if exists, false otherwise.
	 */
	public function exists( $campaign_id ) {
		$campaign = get_post( $campaign_id );
		return $campaign && 'campaign' === $campaign->post_type;
	}

	/**
	 * Hook: Fires when a post is inserted
	 *
	 * @param int    $post_id Post ID.
	 * @param object $post Post object.
	 * @param bool   $update Whether this is an update.
	 * @return void
	 */
	public function on_post_insert( $post_id, $post, $update ) {
		if ( 'campaign' !== $post->post_type ) {
			return;
		}

		if ( $update ) {
			// This is handled by on_post_updated.
			return;
		}

		/**
		 * Fires when a campaign post is inserted (created).
		 *
		 * @param int    $post_id Post ID.
		 * @param object $post Post object.
		 */
		do_action( 'giftflow_campaign_post_inserted', $post_id, $post );
	}

	/**
	 * Hook: Fires when a post is updated
	 *
	 * @param int     $post_id Post ID.
	 * @param object  $post_after Post object after update.
	 * @param object  $post_before Post object before update.
	 * @return void
	 */
	public function on_post_updated( $post_id, $post_after, $post_before ) {
		if ( 'campaign' !== $post_after->post_type ) {
			return;
		}

		/**
		 * Fires when a campaign post is updated.
		 *
		 * @param int    $post_id Post ID.
		 * @param object $post_after Post object after update.
		 * @param object $post_before Post object before update.
		 */
		do_action( 'giftflow_campaign_post_updated', $post_id, $post_after, $post_before );
	}

	/**
	 * Hook: Fires before a post is deleted
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function on_post_delete( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post || 'campaign' !== $post->post_type ) {
			return;
		}

		/**
		 * Fires before a campaign post is deleted.
		 *
		 * @param int    $post_id Post ID.
		 * @param object $post Post object.
		 */
		do_action( 'giftflow_campaign_post_before_delete', $post_id, $post );
	}

	/**
	 * Hook: Fires when a post is trashed
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function on_post_trash( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post || 'campaign' !== $post->post_type ) {
			return;
		}

		/**
		 * Fires when a campaign post is trashed.
		 *
		 * @param int    $post_id Post ID.
		 * @param object $post Post object.
		 */
		do_action( 'giftflow_campaign_post_trashed', $post_id, $post );
	}

	/**
	 * Hook: Fires when a post is untrashed
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function on_post_untrash( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post || 'campaign' !== $post->post_type ) {
			return;
		}

		/**
		 * Fires when a campaign post is untrashed.
		 *
		 * @param int    $post_id Post ID.
		 * @param object $post Post object.
		 */
		do_action( 'giftflow_campaign_post_untrashed', $post_id, $post );
	}

	/**
	 * Hook: Fires when post meta is updated
	 *
	 * @param int    $meta_id Meta ID.
	 * @param int    $post_id Post ID.
	 * @param string $meta_key Meta key.
	 * @param mixed  $meta_value Meta value.
	 * @return void
	 */
	public function on_meta_updated( $meta_id, $post_id, $meta_key, $meta_value ) {
		$post = get_post( $post_id );
		if ( ! $post || 'campaign' !== $post->post_type ) {
			return;
		}

		/**
		 * Fires when campaign meta is updated.
		 *
		 * @param int    $meta_id Meta ID.
		 * @param int    $post_id Post ID.
		 * @param string $meta_key Meta key.
		 * @param mixed  $meta_value Meta value.
		 */
		do_action( 'giftflow_campaign_meta_updated', $meta_id, $post_id, $meta_key, $meta_value );
	}

	/**
	 * Hook: Fires when post meta is added
	 *
	 * @param int    $meta_id Meta ID.
	 * @param int    $post_id Post ID.
	 * @param string $meta_key Meta key.
	 * @param mixed  $meta_value Meta value.
	 * @return void
	 */
	public function on_meta_added( $meta_id, $post_id, $meta_key, $meta_value ) {
		$post = get_post( $post_id );
		if ( ! $post || 'campaign' !== $post->post_type ) {
			return;
		}

		/**
		 * Fires when campaign meta is added.
		 *
		 * @param int    $meta_id Meta ID.
		 * @param int    $post_id Post ID.
		 * @param string $meta_key Meta key.
		 * @param mixed  $meta_value Meta value.
		 */
		do_action( 'giftflow_campaign_meta_added', $meta_id, $post_id, $meta_key, $meta_value );
	}

	/**
	 * Hook: Fires when post meta is deleted
	 *
	 * @param int    $meta_id Meta ID.
	 * @param int    $post_id Post ID.
	 * @param string $meta_key Meta key.
	 * @param mixed  $meta_value Meta value.
	 * @return void
	 */
	public function on_meta_deleted( $meta_id, $post_id, $meta_key, $meta_value ) {
		$post = get_post( $post_id );
		if ( ! $post || 'campaign' !== $post->post_type ) {
			return;
		}

		/**
		 * Fires when campaign meta is deleted.
		 *
		 * @param int    $meta_id Meta ID.
		 * @param int    $post_id Post ID.
		 * @param string $meta_key Meta key.
		 * @param mixed  $meta_value Meta value.
		 */
		do_action( 'giftflow_campaign_meta_deleted', $meta_id, $post_id, $meta_key, $meta_value );
	}
}

// Initialize the Campaigns class.
new Campaigns();
