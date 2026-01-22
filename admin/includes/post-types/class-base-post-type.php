<?php
/**
 * Base Post Type Class
 *
 * @package GiftFlow
 * @subpackage Admin
 */

namespace GiftFlow\Admin\PostTypes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base Post Type Class
 */
abstract class Base_Post_Type {
	/**
	 * Post type name
	 *
	 * @var string
	 */
	protected $post_type;

	/**
	 * Post type labels
	 *
	 * @var array
	 */
	protected $labels;

	/**
	 * Post type arguments
	 *
	 * @var array
	 */
	protected $args;

	/**
	 * Custom taxonomies
	 *
	 * @var array
	 */
	protected $taxonomies = array();

	/**
	 * Custom admin columns
	 *
	 * @var array
	 */
	protected $admin_columns = array();

	/**
	 * Sortable admin columns
	 *
	 * @var array
	 */
	protected $sortable_columns = array();

	/**
	 * Initialize the post type
	 */
	public function __construct() {
		// Initialize post type properties.
		$this->init_post_type();

		// Register the post type.
		$this->register_post_type();

		// Register the taxonomies.
		$this->register_taxonomies();

		// Register admin columns if defined.
		if ( ! empty( $this->admin_columns ) ) {
			add_filter( 'manage_' . $this->post_type . '_posts_columns', array( $this, 'set_custom_columns' ) );
			add_action( 'manage_' . $this->post_type . '_posts_custom_column', array( $this, 'render_custom_columns' ), 10, 2 );

			// Register sortable columns if defined.
			if ( ! empty( $this->sortable_columns ) ) {
				add_filter( 'manage_edit-' . $this->post_type . '_sortable_columns', array( $this, 'set_sortable_columns' ) );
				add_action( 'pre_get_posts', array( $this, 'sort_custom_columns' ) );
			}
		}
	}

	/**
	 * Initialize post type properties.
	 * This method should be implemented by child classes to set up post type properties
	 */
	abstract protected function init_post_type();

	/**
	 * Register the post type
	 */
	public function register_post_type() {
		register_post_type(
			$this->post_type,
			array_merge(
				array(
					'labels' => $this->labels,
				),
				$this->args
			)
		);
	}

	/**
	 * Register taxonomies
	 */
	public function register_taxonomies() {
		if ( empty( $this->taxonomies ) ) {
			return;
		}

		foreach ( $this->taxonomies as $taxonomy ) {
			if ( ! isset( $taxonomy['name'] ) || ! isset( $taxonomy['args'] ) ) {
				continue;
			}

			register_taxonomy(
				$taxonomy['name'],
				$this->post_type,
				$taxonomy['args']
			);
		}
	}

	/**
	 * Set custom columns for the post type
	 *
	 * @param array $columns The existing columns.
	 * @return array The modified columns.
	 */
	public function set_custom_columns( $columns ) {
		$new_columns = array();

		// Insert columns after title.
		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;

			if ( 'title' === $key ) {
				foreach ( $this->admin_columns as $column_key => $column_label ) {
					$new_columns[ $column_key ] = $column_label;
				}
			}
		}

		return $new_columns;
	}

	/**
	 * Render custom column content
	 * This method can be overridden by child classes for custom column rendering
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

		// Default display for empty values.
		if ( empty( $meta_value ) ) {
			echo '—';
			return;
		}

		// Default display for all columns.
		echo esc_html( $meta_value );
	}

	/**
	 * Set sortable columns.

	 * @param array $columns The sortable columns.
	 * @return array The modified sortable columns.
	 */
	public function set_sortable_columns( $columns ) {
		foreach ( $this->sortable_columns as $column ) {
			$columns[ $column ] = $column;
		}

		return $columns;
	}

	/**
	 * Sort custom columns
	 *
	 * @param WP_Query $query The query object.
	 */
	public function sort_custom_columns( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		// Get the orderby.
		$orderby = $query->get( 'orderby' );

		// Sort the columns if they are defined.
		if ( in_array( $orderby, $this->sortable_columns, true ) ) {
			$query->set( 'meta_key', '_' . $orderby );
			$query->set( 'orderby', 'meta_value' );
		}
	}

	/**
	 * Get post type name.
	 *
	 * @return string
	 */
	public function get_post_type() {
		return $this->post_type;
	}
}
