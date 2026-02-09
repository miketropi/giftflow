<?php
/**
 * Donor Contact Meta Box Class
 *
 * @package GiftFlow
 * @subpackage Admin
 */

namespace GiftFlow\Admin\MetaBoxes;

/**
 * Donor Contact Meta Box Class
 */
class Donor_Contact_Meta extends Base_Meta_Box {
	/**
	 * Initialize the meta box
	 */
	public function __construct() {
		$this->id        = 'donor_contact_details';
		$this->title     = __( 'Contact Information', 'giftflow' );
		$this->post_type = 'donor';
		parent::__construct();
	}

	/**
	 * Get meta box fields
	 *
	 * @return array
	 */
	protected function get_fields() {
		return array(
			// first name.
			'first_name'  => array(
				'label'       => __( 'First Name', 'giftflow' ),
				'type'        => 'textfield',
				'description' => __( 'Enter the first name of the donor', 'giftflow' ),
			),
			// last name.
			'last_name'   => array(
				'label'       => __( 'Last Name', 'giftflow' ),
				'type'        => 'textfield',
				'description' => __( 'Enter the last name of the donor', 'giftflow' ),
			),
			// email.
			'email'       => array(
				'label'       => __( 'Email', 'giftflow' ),
				'type'        => 'email',
				'description' => __( 'Enter the email of the donor', 'giftflow' ),
			),
			// phone.
			'phone'       => array(
				'label'       => __( 'Phone', 'giftflow' ),
				'type'        => 'tel',
				'description' => __( 'Enter the phone number of the donor', 'giftflow' ),
			),
			// address.
			'address'     => array(
				'label'       => __( 'Address', 'giftflow' ),
				'type'        => 'textarea',
				'description' => __( 'Enter the address of the donor', 'giftflow' ),
			),
			// city.
			'city'        => array(
				'label'       => __( 'City', 'giftflow' ),
				'type'        => 'textfield',
				'description' => __( 'Enter the city of the donor', 'giftflow' ),
			),
			// state.
			'state'       => array(
				'label'       => __( 'State/Province', 'giftflow' ),
				'type'        => 'textfield',
				'description' => __( 'Enter the state/province of the donor', 'giftflow' ),
			),
			// postal code.
			'postal_code' => array(
				'label'       => __( 'Postal Code', 'giftflow' ),
				'type'        => 'textfield',
				'description' => __( 'Enter the postal code of the donor', 'giftflow' ),
			),
			// country.
			'country'     => array(
				'label'       => __( 'Country', 'giftflow' ),
				'type'        => 'textfield',
				'description' => __( 'Enter the country of the donor', 'giftflow' ),
			),
		);
	}

	/**
	 * Render the meta box
	 *
	 * @param \WP_Post $post Post object.
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'donor_contact_details', 'donor_contact_details_nonce' );

		$fields = $this->get_fields();
		foreach ( $fields as $field_id => $field ) {
			$value   = get_post_meta( $post->ID, '_' . $field_id, true );
			$options = isset( $field['options'] ) ? $field['options'] : array();
			// Create field instance with all necessary parameters.
			$field_instance = new \GiftFlow_Field(
				$field_id,                    // id.
				$field_id,                    // name.
				$field['type'],              // type.
				array(
					'value'           => $value,
					'label'           => $field['label'],
					'description'     => $field['description'],
					'wrapper_classes' => array( 'giftflow-field-wrapper' ),
					'classes'         => array( 'giftflow-field-input' ),
					'options'         => $options,
					'attributes'      => array(
						'id'   => $field_id,
						'name' => $field_id,
					),
				)
			);

			/**
			 * Filter the donor contact meta field render output.
			 *
			 * @param string         $render The rendered field HTML.
			 * @param GiftFlow_Field $field_instance The field instance.
			 */
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo apply_filters( 'giftflow_donor_contact_meta_field_render', $field_instance->render(), $field_instance );
		}
	}

	/**
	 * Save the meta box
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_meta_box( $post_id ) {
		if ( ! $this->verify_nonce( 'donor_contact_details_nonce', 'donor_contact_details' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = $this->get_fields();
		foreach ( $fields as $field_id => $field ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( isset( $_POST[ $field_id ] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				if ( 'textarea' === $field['type'] ) {
					update_post_meta(
						$post_id,
						'_' . $field_id,
						// phpcs:ignore WordPress.Security.NonceVerification.Missing
						sanitize_textarea_field( wp_unslash( $_POST[ $field_id ] ) )
					);
				} else {
					update_post_meta(
						$post_id,
						'_' . $field_id,
						// phpcs:ignore WordPress.Security.NonceVerification.Missing
						sanitize_text_field( wp_unslash( $_POST[ $field_id ] ) )
					);
				}
			}
		}
	}

	/**
	 * Get all user names for select field.

	 * @return array
	 */
	private function get_all_usernames() {
		$__users = get_users(
			array(
				'fields' => array( 'ID', 'user_login' ),
			)
		);

		$u = array();

		foreach ( $__users as $__user ) {
			$u[ $__user->ID ] = $__user->user_login . ' (#' . $__user->ID . ')';
		}

		return $u;
	}
}
