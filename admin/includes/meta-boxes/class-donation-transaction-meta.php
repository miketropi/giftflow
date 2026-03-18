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
				'options'     => giftflow_get_donation_status_options(),
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
				'options'     => apply_filters(
					'giftflow_donation_type_options',
					array(
						'one-time'  => __( 'One-Time', 'giftflow' ),
					)
				),
				'description' => __( 'Select the type of donation', 'giftflow' ),
			),
			'recurring_interval'   => array(
				'label'       => __( 'Recurring Interval', 'giftflow' ),
				'type'        => 'select',
				'options'     => apply_filters(
					'giftflow_recurring_interval_options',
					array()
				),
				'description' => __( 'Select the recurring interval of the donation', 'giftflow' ),
				'pro_only'    => true,
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

			// render field.
			$field_instance->render();
		}

		// Stripe recurring: show subscription details and cancel button when this is a subscription parent.
		$is_subscription_parent = get_post_meta( $post->ID, '_is_subscription_parent', true );
		$subscription_id       = get_post_meta( $post->ID, '_stripe_subscription_id', true );
		if ( $is_subscription_parent && ! empty( $subscription_id ) ) {
			$recurring_status   = get_post_meta( $post->ID, '_recurring_status', true );
			$next_payment       = get_post_meta( $post->ID, '_recurring_next_payment_date', true );
			$recurring_interval = get_post_meta( $post->ID, '_recurring_interval', true );
			$stripe_dashboard   = 'https://dashboard.stripe.com/subscriptions/' . esc_attr( $subscription_id );
			?>
			<div class="giftflow-recurring-details" style="margin-top:1em;padding:1em;background:#f0f0f1;border-left:4px solid #2271b1;">
				<p><strong><?php esc_html_e( 'Recurring (Stripe)', 'giftflow' ); ?></strong></p>
				<p>
					<?php esc_html_e( 'Subscription ID:', 'giftflow' ); ?>
					<a href="<?php echo esc_url( $stripe_dashboard ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $subscription_id ); ?></a>
				</p>
				<p><?php esc_html_e( 'Status:', 'giftflow' ); ?> <?php echo esc_html( $recurring_status ?? '—' ); ?></p>
				<p><?php esc_html_e( 'Interval:', 'giftflow' ); ?> <?php echo esc_html( $recurring_interval ?? '—' ); ?></p>
				<?php if ( $next_payment ) : ?>
					<p><?php esc_html_e( 'Next payment:', 'giftflow' ); ?> <?php echo esc_html( gmdate( get_option( 'date_format' ), strtotime( $next_payment ) ) ); ?></p>
				<?php endif; ?>
				<?php if ( $recurring_status && 'cancelled' !== $recurring_status ) : ?>
					<p>
						<button type="button" class="button giftflow-cancel-subscription" data-donation-id="<?php echo esc_attr( (string) $post->ID ); ?>">
							<?php esc_html_e( 'Cancel subscription', 'giftflow' ); ?>
						</button>
						<span class="giftflow-cancel-result" style="margin-left:8px;"></span>
					</p>
					<script>
					jQuery( function( $ ) {
						$( '.giftflow-cancel-subscription' ).on( 'click', function() {
							var btn = $( this ), id = btn.data( 'donation-id' ), result = btn.siblings( '.giftflow-cancel-result' );
							btn.prop( 'disabled', true );
							result.text( '<?php echo esc_js( __( 'Cancelling…', 'giftflow' ) ); ?>' );
							$.post( ajaxurl, {
								action: 'giftflow_stripe_cancel_subscription',
								nonce: '<?php echo esc_js( wp_create_nonce( 'giftflow_stripe_nonce' ) ); ?>',
								donation_id: id
							} ).done( function( r ) {
								if ( r.success ) {
									result.text( r.data && r.data.message ? r.data.message : '<?php echo esc_js( __( 'Cancelled.', 'giftflow' ) ); ?>' );
									location.reload();
								} else {
									result.text( r.data && r.data.message ? r.data.message : '<?php echo esc_js( __( 'Error.', 'giftflow' ) ); ?>' );
									btn.prop( 'disabled', false );
								}
							} ).fail( function() {
								result.text( '<?php echo esc_js( __( 'Request failed.', 'giftflow' ) ); ?>' );
								btn.prop( 'disabled', false );
							} );
						} );
					} );
					</script>
				<?php endif; ?>
			</div>
			<?php
		}

		// PayPal recurring: show subscription details when this is a subscription parent.
		$paypal_subscription_id = get_post_meta( $post->ID, '_paypal_subscription_id', true );
		if ( $is_subscription_parent && ! empty( $paypal_subscription_id ) && 'paypal' === get_post_meta( $post->ID, '_payment_method', true ) ) {
			$recurring_status   = get_post_meta( $post->ID, '_recurring_status', true );
			$next_payment       = get_post_meta( $post->ID, '_recurring_next_payment_date', true );
			$recurring_interval = get_post_meta( $post->ID, '_recurring_interval', true );
			$paypal_plan_id     = get_post_meta( $post->ID, '_paypal_plan_id', true );
			?>
			<div class="giftflow-recurring-details" style="margin-top:1em;padding:1em;background:#f0f0f1;border-left:4px solid #0070ba;">
				<p><strong><?php esc_html_e( 'Recurring (PayPal)', 'giftflow' ); ?></strong></p>
				<p>
					<?php esc_html_e( 'Subscription ID:', 'giftflow' ); ?>
					<code><?php echo esc_html( $paypal_subscription_id ); ?></code>
				</p>
				<?php if ( $paypal_plan_id ) : ?>
				<p>
					<?php esc_html_e( 'Plan ID:', 'giftflow' ); ?>
					<code><?php echo esc_html( $paypal_plan_id ); ?></code>
				</p>
				<?php endif; ?>
				<p><?php esc_html_e( 'Status:', 'giftflow' ); ?> <?php echo esc_html( $recurring_status ?? '—' ); ?></p>
				<p><?php esc_html_e( 'Interval:', 'giftflow' ); ?> <?php echo esc_html( $recurring_interval ?? '—' ); ?></p>
				<?php if ( $next_payment ) : ?>
					<p><?php esc_html_e( 'Next payment:', 'giftflow' ); ?> <?php echo esc_html( gmdate( get_option( 'date_format' ), strtotime( $next_payment ) ) ); ?></p>
				<?php endif; ?>
				<?php if ( $recurring_status && ! in_array( $recurring_status, array( 'cancelled', 'expired' ), true ) ) : ?>
					<p>
						<button type="button" class="button giftflow-cancel-paypal-subscription" data-donation-id="<?php echo esc_attr( (string) $post->ID ); ?>">
							<?php esc_html_e( 'Cancel subscription', 'giftflow' ); ?>
						</button>
						<span class="giftflow-paypal-cancel-result" style="margin-left:8px;"></span>
					</p>
					<script>
					jQuery( function( $ ) {
						$( '.giftflow-cancel-paypal-subscription' ).on( 'click', function() {
							var btn = $( this ), id = btn.data( 'donation-id' ), result = btn.siblings( '.giftflow-paypal-cancel-result' );
							if ( ! confirm( '<?php echo esc_js( __( 'Are you sure you want to cancel this PayPal subscription?', 'giftflow' ) ); ?>' ) ) return;
							btn.prop( 'disabled', true );
							result.text( '<?php echo esc_js( __( 'Cancelling…', 'giftflow' ) ); ?>' );
							$.post( ajaxurl, {
								action: 'giftflow_paypal_cancel_subscription',
								nonce: '<?php echo esc_js( wp_create_nonce( 'giftflow_paypal_nonce' ) ); ?>',
								donation_id: id
							} ).done( function( r ) {
								if ( r.success ) {
									result.text( r.data && r.data.message ? r.data.message : '<?php echo esc_js( __( 'Cancelled.', 'giftflow' ) ); ?>' );
									location.reload();
								} else {
									result.text( r.data && r.data.message ? r.data.message : '<?php echo esc_js( __( 'Error.', 'giftflow' ) ); ?>' );
									btn.prop( 'disabled', false );
								}
							} ).fail( function() {
								result.text( '<?php echo esc_js( __( 'Request failed.', 'giftflow' ) ); ?>' );
								btn.prop( 'disabled', false );
							} );
						} );
					} );
					</script>
				<?php endif; ?>
			</div>
			<?php
		}

		// When this is a renewal, link to parent.
		$parent_id = get_post_meta( $post->ID, '_parent_donation_id', true );
		if ( ! empty( $parent_id ) && get_post_meta( $post->ID, '_is_subscription_renewal', true ) ) {
			$parent_edit = admin_url( 'post.php?post=' . (int) $parent_id . '&action=edit' );
			?>
			<p style="margin-top:1em;">
				<?php esc_html_e( 'Recurring renewal of:', 'giftflow' ); ?>
				<a href="<?php echo esc_url( $parent_edit ); ?>">#<?php echo esc_html( (string) $parent_id ); ?></a>
			</p>
			<?php
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
