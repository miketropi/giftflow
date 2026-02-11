<?php
/**
 * Donation Transaction Meta Box Class
 *
 * @package GiftFlow
 * @subpackage Admin
 */

namespace GiftFlow\Admin\MetaBoxes;

use GiftFlow\Core\Donation_Event_History;

/**
 * Donation Transaction Meta Box Class
 */
class Donation_Transaction_Meta extends Base_Meta_Box {
	/**
	 * Initialize the meta box
	 */
	public function __construct() {
		$this->id        = 'donation_transaction_details';
		$this->title     = __( 'Transaction Details', 'giftflow' );
		$this->post_type = 'donation';
		parent::__construct();
	}

	/**
	 * Get meta box fields
	 *
	 * @return array
	 */
	protected function get_fields() {
		return array(
			'amount'               => array(
				'label'       => __( 'Amount', 'giftflow' ),
				'type'        => 'currency',
				'description' => __( 'Enter the amount of the donation', 'giftflow' ),
			),
			'payment_method'       => array(
				'label'       => __( 'Payment Method', 'giftflow' ),
				'type'        => 'select',
				'options'     => giftflow_get_payment_methods_options(),
				'description' => __( 'Select the payment method used for the donation', 'giftflow' ),
			),
			'status'               => array(
				'label'       => __( 'Status', 'giftflow' ),
				'type'        => 'select',
				'options'     => array(
					'pending'   => __( 'Pending', 'giftflow' ),
					'completed' => __( 'Completed', 'giftflow' ),
					'failed'    => __( 'Failed', 'giftflow' ),
					'refunded'  => __( 'Refunded', 'giftflow' ),
				),
				'description' => __( 'Select the status of the donation', 'giftflow' ),
			),

			'donor_id'             => array(
				'label'       => __( 'Donor', 'giftflow' ),
				'type'        => 'select',
				'options'     => $this->get_donors(),
				'description' => __( 'Select the donor of the donation', 'giftflow' ),
			),
			'donor_message'        => array(
				'label'       => __( 'Donor Message', 'giftflow' ),
				'type'        => 'textarea',
				'description' => __( 'Enter a message from the donor', 'giftflow' ),
			),
			'anonymous_donation'   => array(
				'label'       => __( 'Anonymous', 'giftflow' ),
				'type'        => 'select',
				'options'     => array(
					'no'  => __( 'No', 'giftflow' ),
					'yes' => __( 'Yes', 'giftflow' ),
				),
				'description' => __( 'Check if the donor wants to remain anonymous', 'giftflow' ),
			),
			'campaign_id'          => array(
				'label'       => __( 'Campaign', 'giftflow' ),
				'type'        => 'select',
				'options'     => $this->get_campaigns(),
				'description' => __( 'Select the campaign of the donation', 'giftflow' ),
			),
			'donation_type'        => array(
				'label'       => __( 'Donation Type', 'giftflow' ),
				'type'        => 'select',
				'options'     => array(
					'one-time'  => __( 'One-Time', 'giftflow' ),
					'recurring' => __( 'Recurring', 'giftflow' ),
				),
				'description' => __( 'Select the type of donation', 'giftflow' ),
			),
			'recurring_interval'   => array(
				'label'       => __( 'Recurring Interval', 'giftflow' ),
				'type'        => 'select',
				'options'     => array(
					'daily'     => __( 'Daily', 'giftflow' ),
					'weekly'    => __( 'Weekly', 'giftflow' ),
					'monthly'   => __( 'Monthly', 'giftflow' ),
					'quarterly' => __( 'Quarterly', 'giftflow' ),
					'yearly'    => __( 'Yearly', 'giftflow' ),
				),
				'description' => __( 'Select the recurring interval of the donation', 'giftflow' ),
			),
			'transaction_id'       => array(
				'label'       => __( 'Transaction ID', 'giftflow' ),
				'type'        => 'textfield',
				'description' => __( 'Enter the transaction ID of the donation', 'giftflow' ),
			),
			'transaction_raw_data' => array(
				'label'       => __( 'Transaction Raw Data', 'giftflow' ),
				'type'        => 'textarea',
				'description' => __( 'Raw data of the transaction, useful for debugging', 'giftflow' ),
			),
			// _payment_reference
			'reference_number'     => array(
				'label'       => __( 'Reference Number', 'giftflow' ),
				'type'        => 'textfield',
				'description' => __( 'Enter the reference number for the donation to be used for bank transfer', 'giftflow' ),
			),
		);
	}

	/**
	 * Render the meta box
	 *
	 * @param \WP_Post $post Post object.
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'donation_transaction_details', 'donation_transaction_details_nonce' );

		$fields = $this->get_fields();
		foreach ( $fields as $field_id => $field_args ) {
			$value = get_post_meta( $post->ID, '_' . $field_id, true );

			// Create and render the field.
			$field_instance = new \GiftFlow_Field(
				$field_id,
				$field_id,
				$field_args['type'],
				array_merge(
					$field_args,
					array(
						'value' => $value,
					)
				)
			);

			/**
			 * Filter the donation transaction meta field render output.
			 *
			 * @param string         $render The rendered field HTML.
			 * @param GiftFlow_Field $field_instance The field instance.
			 */
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo apply_filters( 'giftflow_donation_transaction_meta_field_render', $field_instance->render(), $field_instance );
		}
	}

	/**
	 * Save the meta box.
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_meta_box( $post_id ) {
		if ( ! $this->verify_nonce( 'donation_transaction_details_nonce', 'donation_transaction_details' ) ) {
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
				$new_value = sanitize_text_field( wp_unslash( $_POST[ $field_id ] ) );
				if ( 'status' === $field_id ) {
					$old_status = get_post_meta( $post_id, '_status', true );
					if ( $old_status !== $new_value ) {
						update_post_meta( $post_id, '_' . $field_id, $new_value );
						Donation_Event_History::add(
							$post_id,
							'admin_status_updated',
							$new_value,
							__( 'Status changed by admin', 'giftflow' ),
							array(
								'previous_status' => $old_status,
								'source'          => 'admin',
							)
						);
						continue;
					}
				}
				update_post_meta( $post_id, '_' . $field_id, $new_value );
			}
		}
	}

	/**
	 * Get donors for select field
	 *
	 * @return array
	 */
	private function get_donors() {
		$donors = array();
		$posts  = get_posts(
			array(
				'post_type'      => 'donor',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		foreach ( $posts as $post ) {
			$donors[ $post->ID ] = $post->post_title;
		}

		return $donors;
	}

	/**
	 * Get campaigns for select field
	 *
	 * @return array
	 */
	private function get_campaigns() {
		$campaigns = array();
		$posts     = get_posts(
			array(
				'post_type'      => 'campaign',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		foreach ( $posts as $post ) {
			$campaigns[ $post->ID ] = $post->post_title;
		}

		return $campaigns;
	}
}
