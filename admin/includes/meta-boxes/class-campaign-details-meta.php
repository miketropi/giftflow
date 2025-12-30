<?php
/**
 * Campaign Details Meta Box Class
 *
 * @package GiftFlow
 * @subpackage Admin
 */

namespace GiftFlow\Admin\MetaBoxes;

use GiftFlow_Field;

/**
 * Campaign Details Meta Box Class
 */
class Campaign_Details_Meta extends Base_Meta_Box {
	/**
	 * Initialize the meta box
	 */
	public function __construct() {
		$this->id        = 'campaign_details';
		$this->title     = esc_html__( 'Campaign Details', 'giftflow' );
		$this->post_type = 'campaign';
		parent::__construct();
	}

	/**
	 * Get meta box fields
	 *
	 * @return array
	 */
	protected function get_fields() {
		$preset_donation_amounts = explode( ',', giftflow_get_preset_donation_amounts() );
		$preset_donation_amounts = array_map(
			function ( $amount ) {
				return array(
					'amount' => (int) trim( $amount ),
				);
			},
			$preset_donation_amounts
		);

		return array(
			'regular'  => array(
				'goal_amount'        => array(
					'label'       => esc_html__( 'Goal Amount', 'giftflow' ),
					'type'        => 'currency',
					'step'        => '1',
					'min'         => '0',
					// description.
					'description' => esc_html__( 'Enter the goal amount for the campaign', 'giftflow' ),
				),
				'start_date'         => array(
					'label'       => esc_html__( 'Start Date', 'giftflow' ),
					'type'        => 'datetime',
					'description' => esc_html__( 'Select the start date for the campaign 📅.', 'giftflow' ),
				),
				'end_date'           => array(
					'label'       => esc_html__( 'End Date', 'giftflow' ),
					'type'        => 'datetime',
					'description' => esc_html__( 'Select the end date for the campaign 📅, If empty, the campaign will be active indefinitely ♾️', 'giftflow' ),
				),
				'status'             => array(
					'label'       => esc_html__( 'Status', 'giftflow' ),
					'type'        => 'select',
					'options'     => array(
						'active'    => esc_html__( 'Active', 'giftflow' ),
						'completed' => esc_html__( 'Completed', 'giftflow' ),
						// closed.
						'closed'    => esc_html__( 'Closed', 'giftflow' ),
						// pending.
						'pending'   => esc_html__( 'Pending', 'giftflow' ),
					),
					'description' => '
						<p>' . esc_html__( 'Select the status for the campaign donations:', 'giftflow' ) . '</p>
						<ul>
								<li><strong>' . esc_html__( 'Active', 'giftflow' ) . '</strong> 🟢: ' . esc_html__( 'Campaign is open for donations', 'giftflow' ) . ' 💰</li>
								<li><strong>' . esc_html__( 'Completed', 'giftflow' ) . '</strong> 🏆: ' . esc_html__( 'Campaign is closed and all donations are collected  ✅, but donations are still allowed.', 'giftflow' ) . '</li>
								<li><strong>' . esc_html__( 'Closed', 'giftflow' ) . '</strong> 🔒: ' . esc_html__( 'Campaign is closed and no more donations are allowed', 'giftflow' ) . ' ⛔</li>
								<li><strong>' . esc_html__( 'Pending', 'giftflow' ) . '</strong> ⏳: ' . esc_html__( 'Campaign donations are pending and auto-activated when start date is reached', 'giftflow' ) . ' 📅</li>
						</ul>',
				),
				// on / off one-time donation.
				'one_time'           => array(
					'label'       => esc_html__( 'One-Time', 'giftflow' ),
					'type'        => 'switch',
					'description' => esc_html__( 'Allow one-time donations', 'giftflow' ),
					'default'     => 1,
				),
				// on / off recurring.
				'recurring'          => array(
					'label'       => esc_html__( 'Recurring', 'giftflow' ),
					'type'        => 'switch',
					'description' => esc_html__( 'Allow recurring donations', 'giftflow' ),
					'default'     => 0,
					'pro_only'    => true,
				),
				// select recurring interval.
				'recurring_interval' => array(
					'label'       => esc_html__( 'Recurring Interval', 'giftflow' ),
					'type'        => 'select',
					'options'     => array(
						'daily'     => esc_html__( 'Daily', 'giftflow' ),
						'weekly'    => esc_html__( 'Weekly', 'giftflow' ),
						'monthly'   => esc_html__( 'Monthly', 'giftflow' ),
						'quarterly' => esc_html__( 'Quarterly', 'giftflow' ),
						'yearly'    => esc_html__( 'Yearly', 'giftflow' ),
					),
					'description' => esc_html__( 'Select the recurring interval for recurring donations', 'giftflow' ),
					'default'     => 'monthly',
					'pro_only'    => true,
				),

			),
			'advanced' => array(
				'preset_donation_amounts'       => array(
					'label'             => esc_html__( 'Preset Donation Amounts', 'giftflow' ),
					'type'              => 'repeater',
					'default'           => $preset_donation_amounts,
					'description'       => esc_html__( 'Enter the preset donation amounts for the campaign', 'giftflow' ),
					'repeater_settings' => array(
						'fields' => array(
							'amount' => array(
								'label'       => esc_html__( 'Amount', 'giftflow' ),
								'type'        => 'currency',
								'step'        => '1',
								'min'         => '0',
								'description' => esc_html__( 'Enter the amount for the donation', 'giftflow' ),
							),
						),
					),
				),

				// On / Off switch allow custom donation amounts.
				'allow_custom_donation_amounts' => array(
					'label'       => esc_html__( 'Allow Custom Donation Amounts', 'giftflow' ),
					'type'        => 'switch',
					'default'     => 1,
					'description' => esc_html__( 'Allow users to enter their own donation amounts', 'giftflow' ),
				),

				'location'                      => array(
					'label'       => esc_html__( 'Location', 'giftflow' ),
					'type'        => 'textfield',
					'default'     => 'United States',
					'description' => esc_html__( 'Enter the location for the campaign', 'giftflow' ),
				),
				'gallery'                       => array(
					'label'            => esc_html__( 'Gallery', 'giftflow' ),
					'type'             => 'gallery',
					'description'      => esc_html__( 'Upload images for the campaign', 'giftflow' ),
					'gallery_settings' => array(
						'image_size'  => 'thumbnail',
						'button_text' => esc_html__( 'Select Images', 'giftflow' ),
						'remove_text' => esc_html__( 'Remove All', 'giftflow' ),
					),
				),
			),
		);
	}

	/**
	 * Render the meta box
	 *
	 * @param \WP_Post $post Post object.
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'campaign_details', 'campaign_details_nonce' );

		$fields = $this->get_fields();

		ob_start();
		?>
		<div class="campaign-details-tabs">
			<div class="nav-tab-wrapper">
				<a href="#general-tab" class="nav-tab nav-tab-active"><?php esc_html_e( 'General', 'giftflow' ); ?></a>
				<a href="#advanced-tab" class="nav-tab"><?php esc_html_e( 'Advanced', 'giftflow' ); ?></a>
			</div>
			
			<div id="general-tab" class="tab-content active">
				<?php
				foreach ( $fields['regular'] as $field_id => $field_args ) {
					if ( metadata_exists( 'post', $post->ID, '_' . $field_id ) ) {
						$value = get_post_meta( $post->ID, '_' . $field_id, true );
					} else {
						$value = $field_args['default'] ?? '';
					}

					$field = new GiftFlow_Field(
						$field_id,
						$field_id,
						$field_args['type'],
						array_merge(
							$field_args,
							array(
								'value'           => $value,
								'wrapper_classes' => array( 'campaign-details-field' ),
							)
						)
					);

                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $field->render();
				}
				?>
			</div>
			
			<div id="advanced-tab" class="tab-content">
				<?php
				foreach ( $fields['advanced'] as $field_id => $field_args ) {
					if ( metadata_exists( 'post', $post->ID, '_' . $field_id ) ) {
						$value = get_post_meta( $post->ID, '_' . $field_id, true );

						if ( 'repeater' === $field_args['type'] && $value ) {
							$value = maybe_unserialize( $value );
						}
					} else {
						$value = $field_args['default'] ?? '';
					}

					$field = new GiftFlow_Field(
						$field_id,
						$field_id,
						$field_args['type'],
						array_merge(
							$field_args,
							array(
								'value'           => $value,
								'wrapper_classes' => array( 'campaign-details-field', 'advanced-field' ),
							)
						)
					);

                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $field->render();
				}
				?>
			</div>
		</div>

		<style>
			.campaign-details-tabs {
				margin-top: 10px;
			}
			.tab-content {
				display: none;
				padding: 20px 0;
			}
			.tab-content.active {
				display: block;
			}
			.nav-tab-wrapper {
				margin-bottom: 20px;
			}
			.nav-tab {
				padding: 8px 12px;
				text-decoration: none;
				border: 1px solid #ccc;
				background: #f1f1f1;
				margin-right: 5px;
				transform: translateY(1px);
				-webkit-transform: translateY(1px);
			}
			.nav-tab-active {
				background: #fff;
				border-bottom: 1px solid #fff;
			}
		</style>

		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$('.nav-tab').on('click', function(e) {
					e.preventDefault();
					
					// Remove active class from all tabs and content
					$('.nav-tab').removeClass('nav-tab-active');
					$('.tab-content').removeClass('active');
					
					// Add active class to clicked tab
					$(this).addClass('nav-tab-active');
					
					// Show corresponding content
					var target = $(this).attr('href');
					$(target).addClass('active');
				});
			});
		</script>
		<?php
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo ob_get_clean();
	}

	/**
	 * Save the meta box
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_meta_box( $post_id ) {
		if ( ! $this->verify_nonce( 'campaign_details_nonce', 'campaign_details' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = $this->get_fields();

		// Save regular fields.
		foreach ( $fields['regular'] as $field_id => $field ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( isset( $_POST[ $field_id ] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				$value = sanitize_text_field( wp_unslash( $_POST[ $field_id ] ) );

				update_post_meta(
					$post_id,
					'_' . $field_id,
					$value
				);
			} else {
				update_post_meta(
					$post_id,
					'_' . $field_id,
					''
				);
			}
		}

		// Save advanced fields.
		foreach ( $fields['advanced'] as $field_id => $field ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( isset( $_POST[ $field_id ] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
				$value = $_POST[ $field_id ];

				// if value is an array, then we need to save each value as a separate post meta.
				if ( is_array( $value ) ) {
					// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
					$value = serialize( $value );
				} else {
					// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					$value = sanitize_text_field( wp_unslash( $_POST[ $field_id ] ) );
				}

				update_post_meta(
					$post_id,
					'_' . $field_id,
					$value
				);
			} else {
				update_post_meta(
					$post_id,
					'_' . $field_id,
					''
				);
			}
		}
	}
}
