<?php
/**
 * Campaign Post Type Class
 *
 * @package GiftFlow
 * @subpackage Admin
 */

namespace GiftFlow\Admin\PostTypes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Campaign Post Type Class.
 */
class Campaign extends Base_Post_Type {
	/**
	 * Initialize the campaign post type.
	 *
	 * @return void
	 */
	// phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found, Squiz.Commenting.FunctionComment.Missing
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Initialize post type properties.
	 */
	protected function init_post_type() {
		$this->post_type = 'campaign';
		$this->labels    = array(
			'name'               => _x( 'Campaigns', 'Post type general name', 'giftflow' ),
			'singular_name'      => _x( 'Campaign', 'Post type singular name', 'giftflow' ),
			'menu_name'          => _x( 'Campaigns', 'Admin Menu text', 'giftflow' ),
			'name_admin_bar'     => _x( 'Campaign', 'Add New on Toolbar', 'giftflow' ),
			'add_new'            => __( 'Add New', 'giftflow' ),
			'add_new_item'       => __( 'Add New Campaign', 'giftflow' ),
			'new_item'           => __( 'New Campaign', 'giftflow' ),
			'edit_item'          => __( 'Edit Campaign', 'giftflow' ),
			'view_item'          => __( 'View Campaign', 'giftflow' ),
			'all_items'          => __( 'All Campaigns', 'giftflow' ),
			'search_items'       => __( 'Search Campaigns', 'giftflow' ),
			'not_found'          => __( 'No campaigns found.', 'giftflow' ),
			'not_found_in_trash' => __( 'No campaigns found in Trash.', 'giftflow' ),
		);

		$this->args = array(
			'labels'             => $this->labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => 'giftflow-dashboard',
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'campaign' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments' ),
			'menu_icon'          => 'dashicons-megaphone',
			'show_in_rest'       => true,
		);

		// Define custom taxonomies.
		$this->taxonomies = array(
			array(
				'name' => 'campaign-tax',
				'args' => array(
					'hierarchical'      => true,
					'labels'            => array(
						'name'              => _x( 'Campaign Categories', 'taxonomy general name', 'giftflow' ),
						'singular_name'     => _x( 'Campaign Category', 'taxonomy singular name', 'giftflow' ),
						'search_items'      => __( 'Search Campaign Categories', 'giftflow' ),
						'all_items'         => __( 'All Campaign Categories', 'giftflow' ),
						'parent_item'       => __( 'Parent Campaign Category', 'giftflow' ),
						'parent_item_colon' => __( 'Parent Campaign Category:', 'giftflow' ),
						'edit_item'         => __( 'Edit Campaign Category', 'giftflow' ),
						'update_item'       => __( 'Update Campaign Category', 'giftflow' ),
						'add_new_item'      => __( 'Add New Campaign Category', 'giftflow' ),
						'new_item_name'     => __( 'New Campaign Category Name', 'giftflow' ),
						'menu_name'         => __( 'Campaign Categories', 'giftflow' ),
					),
					'show_ui'           => true,
					'show_admin_column' => true,
					'query_var'         => true,
					'rewrite'           => array( 'slug' => 'campaign-tax' ),
					'show_in_rest'      => true,
				),
			),
		);

		// Define custom admin columns.
		$this->admin_columns = array(
			'goal_amount'   => __( 'Goal Amount', 'giftflow' ),
			'raised_amount' => __( 'Raised Amount', 'giftflow' ),
			'start_date'    => __( 'Start Date', 'giftflow' ),
			'end_date'      => __( 'End Date', 'giftflow' ),
			'status'        => __( 'Status', 'giftflow' ),
		);

		// Define sortable columns.
		$this->sortable_columns = array(
			'goal_amount',
			'raised_amount',
			'start_date',
			'end_date',
			'status',
		);

		// Add filter to highlight parent menu when on taxonomy page.
		add_filter( 'parent_file', array( $this, 'highlight_parent_menu' ) );
		add_filter( 'submenu_file', array( $this, 'highlight_submenu' ) );

		// Register submenu page for campaign taxonomy.
		add_action( 'admin_menu', array( $this, 'register_campaign_taxonomy_submenu' ) );

		// Add filters.
		add_action( 'restrict_manage_posts', array( $this, 'add_status_filter' ) );
		add_action( 'restrict_manage_posts', array( $this, 'add_category_filter' ) );
		add_filter( 'parse_query', array( $this, 'filter_campaigns' ) );
	}

	/**
	 * Render custom column content.
	 *
	 * @param string $column The column name.
	 * @param int    $post_id The post ID.
	 */
	public function render_custom_columns( $column, $post_id ) {
		if ( ! isset( $this->admin_columns[ $column ] ) ) {
			return;
		}

		// Get the meta key for this column.
		$meta_key   = '_' . $column;
		$meta_value = get_post_meta( $post_id, $meta_key, true );

		// if a raised amount column, get the raised amount.
		if ( 'raised_amount' === $column ) {
			$meta_value = giftflow_get_campaign_raised_amount( $post_id );
		}

		// Default display for empty values.
		if ( empty( $meta_value ) ) {
			echo '—';
			return;
		}

		// Special handling for different column types.
		switch ( $column ) {
			case 'goal_amount':
				echo wp_kses_post( giftflow_render_currency_formatted_amount( $meta_value ) );
				break;

			case 'raised_amount':
				echo wp_kses_post( giftflow_render_currency_formatted_amount( $meta_value ) );
				printf( ' (%s%s)', wp_kses_post( giftflow_get_campaign_progress_percentage( $post_id ) ), '%' );
				break;

			case 'start_date':
				echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $meta_value ) ) );
				break;

			case 'end_date':
				echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $meta_value ) ) );
				break;

			case 'status':
				$status_class = '';

				switch ( $meta_value ) {
					case 'active':
						$status_text  = __( 'Active', 'giftflow' );
						$status_class = 'status-active';
						break;
					case 'completed':
						$status_text  = __( 'Completed', 'giftflow' );
						$status_class = 'status-completed';
						break;
					// pending.
					case 'pending':
						$status_text  = __( 'Pending', 'giftflow' );
						$status_class = 'status-pending';
						break;
					case 'closed':
						$status_text  = __( 'Closed', 'giftflow' );
						$status_class = 'status-closed';
						break;
					default:
						$status_text  = __( 'Unknown', 'giftflow' );
						$status_class = 'status-unknown';
				}

				echo '<span class="campaign-status ' . esc_attr( $status_class ) . '">' . esc_html( $status_text ) . '</span>';
				break;

			default:
				// Default display for other columns.
				echo esc_html( $meta_value );
		}
	}

	/**
	 * Register the campaign taxonomy submenu.
	 */
	public function register_campaign_taxonomy_submenu() {
		// add submenu page for campaign taxonomy.
		add_submenu_page(
			'giftflow-dashboard',
			__( 'Campaign Categories', 'giftflow' ),
			__( 'Campaign Categories', 'giftflow' ),
			'manage_options',
			'edit-tags.php?taxonomy=campaign-tax&post_type=campaign',
			null,
			20
		);
	}

	/**
	 * Highlight the parent menu when on the campaign taxonomy page.

	 * @param string $parent_file The parent file.
	 * @return string The modified parent file.
	 */
	public function highlight_parent_menu( $parent_file ) {
		global $current_screen;

		if ( 'campaign-tax' === $current_screen->taxonomy ) {
			$parent_file = 'giftflow-dashboard';
		}

		return $parent_file;
	}

	/**
	 * Highlight the submenu when on the campaign taxonomy page.

	 * @param string $submenu_file The submenu file.
	 * @return string The modified submenu file.
	 */
	public function highlight_submenu( $submenu_file ) {
		global $current_screen;

		if ( 'campaign-tax' === $current_screen->taxonomy ) {
			$submenu_file = 'edit-tags.php?taxonomy=campaign-tax&post_type=campaign';
		}

		return $submenu_file;
	}

	/**
	 * Add status filter dropdown
	 */
	public function add_status_filter() {
		global $typenow;

		if ( 'campaign' === $typenow ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$selected = isset( $_GET['campaign_status'] ) ? sanitize_text_field( wp_unslash( $_GET['campaign_status'] ) ) : '';
			$statuses = array( 'active', 'completed', 'closed', 'pending' );

			echo '<select name="campaign_status">';
			echo '<option value="">' . esc_html__( 'All Statuses', 'giftflow' ) . '</option>';

			foreach ( $statuses as $status ) {
				$status_label  = ucfirst( $status );
				$selected_attr = selected( $selected, $status, false );
				echo '<option value="' . esc_attr( $status ) . '" ' . esc_attr( $selected_attr ) . '>' . esc_html( $status_label ) . '</option>';
			}

			echo '</select>';
		}
	}

	/**
	 * Add category filter dropdown.
	 */
	public function add_category_filter() {
		global $typenow;

		if ( 'campaign' === $typenow ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$selected = isset( $_GET['campaign_category'] ) ? sanitize_text_field( wp_unslash( $_GET['campaign_category'] ) ) : '';

			// Get all campaign categories.
			$categories = get_terms(
				array(
					'taxonomy'   => 'campaign-tax',
					'hide_empty' => false,
					'orderby'    => 'name',
					'order'      => 'ASC',
				)
			);

			if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
				echo '<select name="campaign_category">';
				echo '<option value="">' . esc_html__( 'All Categories', 'giftflow' ) . '</option>';

				foreach ( $categories as $category ) {
					$selected_attr = selected( $selected, $category->term_id, false );
					echo '<option value="' . esc_attr( $category->term_id ) . '" ' . esc_attr( $selected_attr ) . '>' . esc_html( $category->name ) . '</option>';
				}

				echo '</select>';
			}
		}
	}

	/**
	 * Filter campaigns by status and category.

	 * @param WP_Query $query The WP_Query instance.
	 */
	public function filter_campaigns( $query ) {
		global $pagenow, $typenow;

		if ( 'edit.php' === $pagenow && 'campaign' === $typenow ) {
			$meta_query = array();
			$tax_query  = array();

			// Filter by status.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( isset( $_GET['campaign_status'] ) && '' !== $_GET['campaign_status'] ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$status       = sanitize_text_field( wp_unslash( $_GET['campaign_status'] ) );
				$meta_query[] = array(
					'key'     => '_status',
					'value'   => $status,
					'compare' => '=',
				);
			}

			// Filter by category.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( isset( $_GET['campaign_category'] ) && '' !== $_GET['campaign_category'] ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$category_id = intval( wp_unslash( $_GET['campaign_category'] ) );
				$tax_query[] = array(
					'taxonomy' => 'campaign-tax',
					'field'    => 'term_id',
					'terms'    => $category_id,
					'operator' => 'IN',
				);
			}

			// Apply meta query if we have status filter.
			if ( ! empty( $meta_query ) ) {
				$query->set( 'meta_query', $meta_query );
			}

			// Apply tax query if we have category filter.
			if ( ! empty( $tax_query ) ) {
				$query->set( 'tax_query', $tax_query );
			}
		}
	}
}
