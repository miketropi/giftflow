<?php
/**
 * Base Meta Box Class
 *
 * @package GiftFlow
 * @subpackage Admin
 */

namespace GiftFlow\Admin\MetaBoxes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base Meta Box Class
 */
abstract class Base_Meta_Box {
	/**
	 * Meta box ID
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Meta box title
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Post type
	 *
	 * @var string
	 */
	protected $post_type;

	/**
	 * Context
	 *
	 * @var string
	 */
	protected $context = 'normal';

	/**
	 * Priority
	 *
	 * @var string
	 */
	protected $priority = 'high';

	/**
	 * Initialize the meta box
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ) );
	}

	/**
	 * Add the meta box
	 */
	public function add_meta_box() {
		add_meta_box(
			$this->id,
			$this->title,
			array( $this, 'render_meta_box' ),
			$this->post_type,
			$this->context,
			$this->priority
		);
	}

	/**
	 * Render the meta box
	 *
	 * @param \WP_Post $post Post object.
	 */
	abstract public function render_meta_box( $post );

	/**
	 * Save the meta box
	 *
	 * @param int $post_id Post ID.
	 */
	abstract public function save_meta_box( $post_id );

	/**
	 * Verify nonce
	 *
	 * @param string $nonce_name Nonce name.
	 * @param string $nonce_action Nonce action.
	 * @return bool
	 */
	protected function verify_nonce( $nonce_name, $nonce_action ) {
		if ( ! isset( $_POST[ $nonce_name ] ) ) {
			return false;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_POST[ $nonce_name ] ), $nonce_action ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get meta box fields
	 *
	 * @return array
	 */
	abstract protected function get_fields();
}
