<?php
/**
 * Donations class for GiftFlow
 *
 * Handles CRUD operations for donation custom post type
 * with comprehensive hooks and validation.
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
 * Donations Class
 *
 * Provides methods for creating, updating, deleting, and retrieving donations
 * with comprehensive hooks and validation.
 */
class Donations extends Base {
	/**
	 * Valid donation statuses
	 *
	 * @var array
	 */
	private $valid_statuses = array(
		'pending',
		'completed',
		'failed',
		'refunded',
		// 'cancelled',
	);

	/**
	 * Initialize donations
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
	 * Create a new donation
	 *
	 * @param array $data Donation data.
	 * @return int|\WP_Error Donation ID on success, WP_Error on failure.
	 */
	public function create( $data ) {
		// Validate required fields.
		$validation = $this->validate_donation_data( $data );
		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		// Allow filtering of donation data before creation.
		$data = apply_filters( 'giftflow_donation_before_create', $data );

		// Prepare post data.
		$post_data = array(
			'post_title' => $this->generate_donation_title( $data ),
			'post_type' => 'donation',
			'post_status' => 'publish',
		);

		// Allow filtering of post data.
		$post_data = apply_filters( 'giftflow_donation_post_data', $post_data, $data );

		/**
		 * Fires before a donation is created.
		 *
		 * @param array $data Donation data.
		 */
		do_action( 'giftflow_donation_before_create', $data );

		// Create the post.
		$donation_id = wp_insert_post( $post_data, true );

		if ( is_wp_error( $donation_id ) ) {
			/**
			 * Fires when donation creation fails.
			 *
			 * @param \WP_Error $donation_id Error object.
			 * @param array     $data Donation data.
			 */
			do_action( 'giftflow_donation_create_failed', $donation_id, $data );
			return $donation_id;
		}

		// Save donation meta fields.
		$this->save_donation_meta( $donation_id, $data );

		/**
		 * Fires after a donation is created.
		 *
		 * @param int   $donation_id Donation ID.
		 * @param array $data Donation data.
		 */
		do_action( 'giftflow_donation_created', $donation_id, $data );

		return $donation_id;
	}

	/**
	 * Update an existing donation
	 *
	 * @param int   $donation_id Donation ID.
	 * @param array $data Donation data to update.
	 * @return bool|\WP_Error True on success, WP_Error on failure.
	 */
	public function update( $donation_id, $data ) {
		// Verify donation exists.
		$donation = get_post( $donation_id );
		if ( ! $donation || 'donation' !== $donation->post_type ) {
			return new \WP_Error( 'invalid_donation', __( 'Invalid donation ID', 'giftflow' ) );
		}

		// Allow filtering of donation data before update.
		$data = apply_filters( 'giftflow_donation_before_update', $data, $donation_id );

		/**
		 * Fires before a donation is updated.
		 *
		 * @param int   $donation_id Donation ID.
		 * @param array $data Donation data.
		 */
		do_action( 'giftflow_donation_before_update', $donation_id, $data );

		// Prepare post data if title needs updating.
		$post_data = array( 'ID' => $donation_id );
		if ( isset( $data['donor_name'] ) ) {
			$post_data['post_title'] = $this->generate_donation_title( $data );
		}

		// Allow filtering of post data.
		$post_data = apply_filters( 'giftflow_donation_update_post_data', $post_data, $data, $donation_id );

		// Update post if needed.
		if ( count( $post_data ) > 1 ) {
			$result = wp_update_post( $post_data, true );
			if ( is_wp_error( $result ) ) {
				/**
				 * Fires when donation update fails.
				 *
				 * @param \WP_Error $result Error object.
				 * @param int       $donation_id Donation ID.
				 * @param array     $data Donation data.
				 */
				do_action( 'giftflow_donation_update_failed', $result, $donation_id, $data );
				return $result;
			}
		}

		// Update donation meta fields.
		$this->save_donation_meta( $donation_id, $data );

		/**
		 * Fires after a donation is updated.
		 *
		 * @param int   $donation_id Donation ID.
		 * @param array $data Donation data.
		 */
		do_action( 'giftflow_donation_updated', $donation_id, $data );

		return true;
	}

	/**
	 * Delete a donation
	 *
	 * @param int  $donation_id Donation ID.
	 * @param bool $force_delete Whether to bypass trash and force deletion.
	 * @return bool|\WP_Error True on success, WP_Error on failure.
	 */
	public function delete( $donation_id, $force_delete = false ) {
		// Verify donation exists.
		$donation = get_post( $donation_id );
		if ( ! $donation || 'donation' !== $donation->post_type ) {
			return new \WP_Error( 'invalid_donation', __( 'Invalid donation ID', 'giftflow' ) );
		}

		/**
		 * Fires before a donation is deleted.
		 *
		 * @param int  $donation_id Donation ID.
		 * @param bool $force_delete Whether to force delete.
		 */
		do_action( 'giftflow_donation_before_delete', $donation_id, $force_delete );

		// Get donation data before deletion.
		$donation_data = $this->get( $donation_id );

		// Delete the post.
		$result = wp_delete_post( $donation_id, $force_delete );

		if ( false === $result ) {
			return new \WP_Error( 'delete_failed', __( 'Failed to delete donation', 'giftflow' ) );
		}

		/**
		 * Fires after a donation is deleted.
		 *
		 * @param int   $donation_id Donation ID.
		 * @param array $donation_data Donation data before deletion.
		 * @param bool  $force_delete Whether it was force deleted.
		 */
		do_action( 'giftflow_donation_deleted', $donation_id, $donation_data, $force_delete );

		return true;
	}

	/**
	 * Get donation data by ID
	 *
	 * @param int $donation_id Donation ID.
	 * @return array|false Donation data array or false if not found.
	 */
	public function get( $donation_id ) {
		$donation = get_post( $donation_id );

		if ( ! $donation || 'donation' !== $donation->post_type ) {
			return false;
		}

		// Get all donation meta.
		$meta = get_post_meta( $donation_id );

		// Build donation data array.
		$donation_data = array(
			'id' => $donation_id,
			'title' => $donation->post_title,
			'post_status' => $donation->post_status,
			'date' => $donation->post_date,
			'date_gmt' => $donation->post_date_gmt,
			'modified' => $donation->post_modified,
			'modified_gmt' => $donation->post_modified_gmt,
			'amount' => isset( $meta['_amount'][0] ) ? floatval( $meta['_amount'][0] ) : 0,
			'campaign_id' => isset( $meta['_campaign_id'][0] ) ? intval( $meta['_campaign_id'][0] ) : 0,
			'donor_id' => isset( $meta['_donor_id'][0] ) ? intval( $meta['_donor_id'][0] ) : 0,
			'status' => isset( $meta['_status'][0] ) ? $meta['_status'][0] : 'pending',
			'payment_method' => isset( $meta['_payment_method'][0] ) ? $meta['_payment_method'][0] : '',
			'donation_type' => isset( $meta['_donation_type'][0] ) ? $meta['_donation_type'][0] : '',
			'recurring_interval' => isset( $meta['_recurring_interval'][0] ) ? $meta['_recurring_interval'][0] : '',
			'donor_message' => isset( $meta['_donor_message'][0] ) ? $meta['_donor_message'][0] : '',
			'anonymous_donation' => isset( $meta['_anonymous_donation'][0] ) ? $meta['_anonymous_donation'][0] : 'no',
			'anonymous' => isset( $meta['_anonymous'][0] ) ? $meta['_anonymous'][0] : '',
			'transaction_id' => isset( $meta['_transaction_id'][0] ) ? $meta['_transaction_id'][0] : '',
			'transaction_raw_data' => isset( $meta['_transaction_raw_data'][0] ) ? json_decode( $meta['_transaction_raw_data'][0], true ) : array(),
			'paypal_order_id' => isset( $meta['_paypal_order_id'][0] ) ? $meta['_paypal_order_id'][0] : '',
			'payment_status' => isset( $meta['_payment_status'][0] ) ? $meta['_payment_status'][0] : '',
			'payment_error' => isset( $meta['_payment_error'][0] ) ? $meta['_payment_error'][0] : '',
		);

		// Allow filtering of donation data.
		return apply_filters( 'giftflow_donation_data', $donation_data, $donation_id );
	}

	/**
	 * Get multiple donations
	 *
	 * @param array $args Query arguments.
	 * @return array Array of donation data.
	 */
	public function get_donations( $args = array() ) {
		$defaults = array(
			'post_type' => 'donation',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'orderby' => 'date',
			'order' => 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'giftflow_donations_query_args', $args );

		$query = new \WP_Query( $args );

		$donations = array();
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$donation_data = $this->get( get_the_ID() );
				if ( $donation_data ) {
					$donations[] = $donation_data;
				}
			}
			wp_reset_postdata();
		}

		return apply_filters( 'giftflow_donations_data', $donations, $args );
	}

	/**
	 * Update donation status
	 *
	 * @param int    $donation_id Donation ID.
	 * @param string $status New status.
	 * @return bool|\WP_Error True on success, WP_Error on failure.
	 */
	public function update_status( $donation_id, $status ) {
		// Validate status.
		if ( ! in_array( $status, $this->valid_statuses, true ) ) {
			return new \WP_Error( 'invalid_status', __( 'Invalid donation status', 'giftflow' ) );
		}

		// Get current status.
		$old_status = get_post_meta( $donation_id, '_status', true );

		// If status hasn't changed, return true.
		if ( $old_status === $status ) {
			return true;
		}

		/**
		 * Fires before donation status is updated.
		 *
		 * @param int    $donation_id Donation ID.
		 * @param string $status New status.
		 * @param string $old_status Old status.
		 */
		do_action( 'giftflow_donation_before_status_update', $donation_id, $status, $old_status );

		// Update status.
		$result = update_post_meta( $donation_id, '_status', $status );

		if ( $result ) {
			/**
			 * Fires after donation status is updated.
			 *
			 * @param int    $donation_id Donation ID.
			 * @param string $status New status.
			 * @param string $old_status Old status.
			 */
			do_action( 'giftflow_donation_status_updated', $donation_id, $status, $old_status );

			// Fire status-specific hooks.
			do_action( "giftflow_donation_status_{$status}", $donation_id, $old_status );
		}

		return $result;
	}

	/**
	 * Validate donation data
	 *
	 * @param array $data Donation data.
	 * @return true|\WP_Error True if valid, WP_Error if invalid.
	 */
	private function validate_donation_data( $data ) {
		// Amount is required.
		if ( empty( $data['donation_amount'] ) || floatval( $data['donation_amount'] ) <= 0 ) {
			return new \WP_Error( 'invalid_amount', __( 'Donation amount is required and must be greater than 0', 'giftflow' ) );
		}

		// Donor name is required.
		if ( empty( $data['donor_name'] ) ) {
			return new \WP_Error( 'invalid_donor_name', __( 'Donor name is required', 'giftflow' ) );
		}

		// Donor email is required.
		if ( empty( $data['donor_email'] ) || ! is_email( $data['donor_email'] ) ) {
			return new \WP_Error( 'invalid_donor_email', __( 'Valid donor email is required', 'giftflow' ) );
		}

		// Validate status if provided.
		if ( isset( $data['status'] ) && ! in_array( $data['status'], $this->valid_statuses, true ) ) {
			return new \WP_Error( 'invalid_status', __( 'Invalid donation status', 'giftflow' ) );
		}

		// Allow custom validation.
		$validation = apply_filters( 'giftflow_donation_validate', true, $data );
		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		return true;
	}

	/**
	 * Save donation meta fields
	 *
	 * @param int   $donation_id Donation ID.
	 * @param array $data Donation data.
	 * @return void
	 */
	private function save_donation_meta( $donation_id, $data ) {
		// Amount (required).
		if ( isset( $data['donation_amount'] ) ) {
			update_post_meta( $donation_id, '_amount', floatval( $data['donation_amount'] ) );
		}

		// Campaign ID.
		if ( isset( $data['campaign_id'] ) ) {
			if ( ! empty( $data['campaign_id'] ) ) {
				update_post_meta( $donation_id, '_campaign_id', intval( $data['campaign_id'] ) );
			} else {
				delete_post_meta( $donation_id, '_campaign_id' );
			}
		}

		// Payment method.
		if ( isset( $data['payment_method'] ) ) {
			if ( ! empty( $data['payment_method'] ) ) {
				update_post_meta( $donation_id, '_payment_method', sanitize_text_field( $data['payment_method'] ) );
			} else {
				delete_post_meta( $donation_id, '_payment_method' );
			}
		}

		// Donation type.
		if ( isset( $data['donation_type'] ) ) {
			if ( ! empty( $data['donation_type'] ) ) {
				update_post_meta( $donation_id, '_donation_type', sanitize_text_field( $data['donation_type'] ) );
			} else {
				delete_post_meta( $donation_id, '_donation_type' );
			}
		}

		// Recurring interval.
		if ( isset( $data['recurring_interval'] ) ) {
			if ( ! empty( $data['recurring_interval'] ) ) {
				update_post_meta( $donation_id, '_recurring_interval', sanitize_text_field( $data['recurring_interval'] ) );
			} else {
				delete_post_meta( $donation_id, '_recurring_interval' );
			}
		}

		// Donor ID (will be set if donor_email is provided).
		if ( isset( $data['donor_email'] ) && ! empty( $data['donor_email'] ) ) {
			$donor_id = $this->get_or_create_donor( $data['donor_email'], $data );
			if ( $donor_id ) {
				update_post_meta( $donation_id, '_donor_id', $donor_id );
			}
		} elseif ( isset( $data['donor_id'] ) ) {
			if ( ! empty( $data['donor_id'] ) ) {
				update_post_meta( $donation_id, '_donor_id', intval( $data['donor_id'] ) );
			} else {
				delete_post_meta( $donation_id, '_donor_id' );
			}
		}

		// Donor message.
		if ( isset( $data['donor_message'] ) ) {
			if ( ! empty( $data['donor_message'] ) ) {
				update_post_meta( $donation_id, '_donor_message', sanitize_textarea_field( $data['donor_message'] ) );
			} else {
				delete_post_meta( $donation_id, '_donor_message' );
			}
		}

		// Anonymous donation.
		if ( isset( $data['anonymous_donation'] ) ) {
			$is_anonymous = ( 'yes' === $data['anonymous_donation'] || true === $data['anonymous_donation'] || '1' === $data['anonymous_donation'] );
			update_post_meta( $donation_id, '_anonymous_donation', $is_anonymous ? 'yes' : 'no' );
			update_post_meta( $donation_id, '_anonymous', $is_anonymous ? 'yes' : 'no' );
		}

		// Status.
		if ( isset( $data['status'] ) ) {
			if ( in_array( $data['status'], $this->valid_statuses, true ) ) {
				update_post_meta( $donation_id, '_status', $data['status'] );
			}
		} elseif ( ! get_post_meta( $donation_id, '_status', true ) ) {
			// Set default status if not provided.
			update_post_meta( $donation_id, '_status', 'pending' );
		}

		// Transaction ID.
		if ( isset( $data['transaction_id'] ) ) {
			if ( ! empty( $data['transaction_id'] ) ) {
				update_post_meta( $donation_id, '_transaction_id', sanitize_text_field( $data['transaction_id'] ) );
			} else {
				delete_post_meta( $donation_id, '_transaction_id' );
			}
		}

		// Transaction raw data.
		if ( isset( $data['transaction_raw_data'] ) ) {
			if ( ! empty( $data['transaction_raw_data'] ) ) {
				$raw_data = is_array( $data['transaction_raw_data'] ) ? $data['transaction_raw_data'] : json_decode( $data['transaction_raw_data'], true );
				update_post_meta( $donation_id, '_transaction_raw_data', wp_json_encode( $raw_data ) );
			} else {
				delete_post_meta( $donation_id, '_transaction_raw_data' );
			}
		}

		// PayPal order ID.
		if ( isset( $data['paypal_order_id'] ) ) {
			if ( ! empty( $data['paypal_order_id'] ) ) {
				update_post_meta( $donation_id, '_paypal_order_id', sanitize_text_field( $data['paypal_order_id'] ) );
			} else {
				delete_post_meta( $donation_id, '_paypal_order_id' );
			}
		}

		// Payment status.
		if ( isset( $data['payment_status'] ) ) {
			if ( ! empty( $data['payment_status'] ) ) {
				update_post_meta( $donation_id, '_payment_status', sanitize_text_field( $data['payment_status'] ) );
			} else {
				delete_post_meta( $donation_id, '_payment_status' );
			}
		}

		// Payment error.
		if ( isset( $data['payment_error'] ) ) {
			if ( ! empty( $data['payment_error'] ) ) {
				update_post_meta( $donation_id, '_payment_error', sanitize_text_field( $data['payment_error'] ) );
			} else {
				delete_post_meta( $donation_id, '_payment_error' );
			}
		}

		/**
		 * Fires after donation meta is saved.
		 *
		 * @param int   $donation_id Donation ID.
		 * @param array $data Donation data.
		 */
		do_action( 'giftflow_donation_meta_saved', $donation_id, $data );
	}

	/**
	 * Generate donation title
	 *
	 * @param array $data Donation data.
	 * @return string Donation title.
	 */
	private function generate_donation_title( $data ) {
		$donor_name = isset( $data['donor_name'] ) ? $data['donor_name'] : __( 'Anonymous', 'giftflow' );
		// translators: %s: Donor name.
		return sprintf( __( 'Donation from %s', 'giftflow' ), $donor_name );
	}

	/**
	 * Get or create donor record
	 *
	 * @param string $email Donor email.
	 * @param array  $data Donation data.
	 * @return int|false Donor ID or false on failure.
	 */
	private function get_or_create_donor( $email, $data ) {
		// Get donor record by email.
		$donors = get_posts(
			array(
				'post_type' => 'donor',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_key' => '_email',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'meta_value' => $email,
				'posts_per_page' => 1,
			)
		);

		if ( ! empty( $donors ) ) {
			return $donors[0]->ID;
		}

		// Create new donor record.
		$donor_data = array(
			'post_title' => isset( $data['donor_name'] ) ? $data['donor_name'] : '',
			'post_type' => 'donor',
			'post_status' => 'publish',
		);

		$donor_id = wp_insert_post( $donor_data );

		if ( is_wp_error( $donor_id ) ) {
			return false;
		}

		// Save donor email and name.
		update_post_meta( $donor_id, '_email', sanitize_email( $email ) );
		if ( isset( $data['donor_name'] ) ) {
			update_post_meta( $donor_id, '_first_name', sanitize_text_field( $data['donor_name'] ) );
		}

		/**
		 * Fires after a donor is created.
		 *
		 * @param int   $donor_id Donor ID.
		 * @param array $data Donation data.
		 */
		do_action( 'giftflow_donor_added', $donor_id, $data );

		return $donor_id;
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
		if ( 'donation' !== $post->post_type ) {
			return;
		}

		if ( $update ) {
			// This is handled by on_post_updated.
			return;
		}

		/**
		 * Fires when a donation post is inserted (created).
		 *
		 * @param int    $post_id Post ID.
		 * @param object $post Post object.
		 */
		do_action( 'giftflow_donation_post_inserted', $post_id, $post );
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
		if ( 'donation' !== $post_after->post_type ) {
			return;
		}

		/**
		 * Fires when a donation post is updated.
		 *
		 * @param int    $post_id Post ID.
		 * @param object $post_after Post object after update.
		 * @param object $post_before Post object before update.
		 */
		do_action( 'giftflow_donation_post_updated', $post_id, $post_after, $post_before );
	}

	/**
	 * Hook: Fires before a post is deleted
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function on_post_delete( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post || 'donation' !== $post->post_type ) {
			return;
		}

		/**
		 * Fires before a donation post is deleted.
		 *
		 * @param int    $post_id Post ID.
		 * @param object $post Post object.
		 */
		do_action( 'giftflow_donation_post_before_delete', $post_id, $post );
	}

	/**
	 * Hook: Fires when a post is trashed
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function on_post_trash( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post || 'donation' !== $post->post_type ) {
			return;
		}

		/**
		 * Fires when a donation post is trashed.
		 *
		 * @param int    $post_id Post ID.
		 * @param object $post Post object.
		 */
		do_action( 'giftflow_donation_post_trashed', $post_id, $post );
	}

	/**
	 * Hook: Fires when a post is untrashed
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function on_post_untrash( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post || 'donation' !== $post->post_type ) {
			return;
		}

		/**
		 * Fires when a donation post is untrashed.
		 *
		 * @param int    $post_id Post ID.
		 * @param object $post Post object.
		 */
		do_action( 'giftflow_donation_post_untrashed', $post_id, $post );
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
		if ( ! $post || 'donation' !== $post->post_type ) {
			return;
		}

		/**
		 * Fires when donation meta is updated.
		 *
		 * @param int    $meta_id Meta ID.
		 * @param int    $post_id Post ID.
		 * @param string $meta_key Meta key.
		 * @param mixed  $meta_value Meta value.
		 */
		do_action( 'giftflow_donation_meta_updated', $meta_id, $post_id, $meta_key, $meta_value );

		// Special handling for status changes.
		if ( '_status' === $meta_key ) {
			$old_value = get_post_meta( $post_id, '_status', true );
			// Note: The old value here might not be accurate due to timing.
			// Use giftflow_donation_status_updated hook for accurate status change tracking.
		}
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
		if ( ! $post || 'donation' !== $post->post_type ) {
			return;
		}

		/**
		 * Fires when donation meta is added.
		 *
		 * @param int    $meta_id Meta ID.
		 * @param int    $post_id Post ID.
		 * @param string $meta_key Meta key.
		 * @param mixed  $meta_value Meta value.
		 */
		do_action( 'giftflow_donation_meta_added', $meta_id, $post_id, $meta_key, $meta_value );
	}

	/**
	 * Hook: Fires when post meta is deleted
	 *
	 * @param array  $meta_ids Meta IDs.
	 * @param int    $post_id Post ID.
	 * @param string $meta_key Meta key.
	 * @param mixed  $meta_value Meta value.
	 * @return void
	 */
	public function on_meta_deleted( $meta_ids, $post_id, $meta_key, $meta_value ) {
		$post = get_post( $post_id );
		if ( ! $post || 'donation' !== $post->post_type ) {
			return;
		}

		/**
		 * Fires when donation meta is deleted.
		 *
		 * @param array  $meta_ids Meta IDs.
		 * @param int    $post_id Post ID.
		 * @param string $meta_key Meta key.
		 * @param mixed  $meta_value Meta value.
		 */
		do_action( 'giftflow_donation_meta_deleted', $meta_ids, $post_id, $meta_key, $meta_value );
	}

	/**
	 * Get valid donation statuses
	 *
	 * @return array Array of valid statuses.
	 */
	public function get_valid_statuses() {
		return apply_filters( 'giftflow_donation_valid_statuses', $this->valid_statuses );
	}

	/**
	 * Check if donation exists
	 *
	 * @param int $donation_id Donation ID.
	 * @return bool True if exists, false otherwise.
	 */
	public function exists( $donation_id ) {
		$donation = get_post( $donation_id );
		return ( $donation && 'donation' === $donation->post_type );
	}

	/**
	 * Get donations by campaign
	 *
	 * @param int   $campaign_id Campaign ID.
	 * @param array $args Additional query arguments.
	 * @return array Array of donation data.
	 */
	public function get_by_campaign( $campaign_id, $args = array() ) {
		$defaults = array(
			'post_type' => 'donation',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'orderby' => 'date',
			'order' => 'DESC',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_key' => '_campaign_id',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'meta_value' => $campaign_id,
		);

		$args = wp_parse_args( $args, $defaults );
		return $this->get_donations( $args );
	}

	/**
	 * Get donations by donor
	 *
	 * @param int   $donor_id Donor ID.
	 * @param array $args Additional query arguments.
	 * @return array Array of donation data.
	 */
	public function get_by_donor( $donor_id, $args = array() ) {
		$defaults = array(
			'post_type' => 'donation',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'orderby' => 'date',
			'order' => 'DESC',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_key' => '_donor_id',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'meta_value' => $donor_id,
		);

		$args = wp_parse_args( $args, $defaults );
		return $this->get_donations( $args );
	}

	/**
	 * Get donations by status
	 *
	 * @param string $status Donation status.
	 * @param array  $args Additional query arguments.
	 * @return array Array of donation data.
	 */
	public function get_by_status( $status, $args = array() ) {
		$defaults = array(
			'post_type' => 'donation',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'orderby' => 'date',
			'order' => 'DESC',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_key' => '_status',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'meta_value' => $status,
		);

		$args = wp_parse_args( $args, $defaults );
		return $this->get_donations( $args );
	}
}

// Initialize Donations class.
new \GiftFlow\Core\Donations();
