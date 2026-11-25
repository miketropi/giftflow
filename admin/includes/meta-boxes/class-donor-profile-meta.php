<?php
/**
 * Donor Public Profile Meta Box Class
 *
 * @package GiftFlow
 * @subpackage Admin
 */

namespace GiftFlow\Admin\MetaBoxes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Donor Profile Meta Box Class.
 *
 * Surfaces the public profile fields edited in My Account so administrators can
 * review (and correct) the bio, social links and the custom avatar.
 */
class Donor_Profile_Meta extends Base_Meta_Box {

	/**
	 * Social networks stored as `_social_<key>` donor meta.
	 *
	 * @return array<string,string>
	 */
	private function get_social_labels() {
		return array(
			'facebook'  => __( 'Facebook', 'giftflow' ),
			'x'         => __( 'X (Twitter)', 'giftflow' ),
			'threads'   => __( 'Threads', 'giftflow' ),
			'linkedin'  => __( 'LinkedIn', 'giftflow' ),
			'instagram' => __( 'Instagram', 'giftflow' ),
			'website'   => __( 'Website', 'giftflow' ),
		);
	}

	/**
	 * Initialize the meta box.
	 */
	public function __construct() {
		$this->id        = 'donor_public_profile';
		$this->title     = __( 'Public Profile', 'giftflow' );
		$this->post_type = 'donor';
		parent::__construct();
	}

	/**
	 * Get meta box fields.
	 *
	 * @return array
	 */
	protected function get_fields() {
		$fields = array(
			'bio' => array(
				'label'       => __( 'Bio', 'giftflow' ),
				'type'        => 'textarea',
				'description' => __( 'Public biography shown on the donor account (max 500 characters).', 'giftflow' ),
			),
		);

		foreach ( $this->get_social_labels() as $social_key => $social_label ) {
			$fields[ 'social_' . $social_key ] = array(
				'label'       => $social_label,
				'type'        => 'textfield',
				'description' => __( 'Full URL, including https://', 'giftflow' ),
			);
		}

		return $fields;
	}

	/**
	 * Render the meta box.
	 *
	 * @param \WP_Post $post Post object.
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'donor_public_profile', 'donor_public_profile_nonce' );

		$profile_error = get_transient( 'giftflow_donor_profile_error_' . $post->ID );

		if ( $profile_error ) {
			delete_transient( 'giftflow_donor_profile_error_' . $post->ID );
			echo '<div class="notice notice-warning inline"><p>' . esc_html( $profile_error ) . '</p></div>';
		}

		$this->render_avatar( $post );

		$fields = $this->get_fields();

		foreach ( $fields as $field_id => $field ) {
			$value = get_post_meta( $post->ID, '_' . $field_id, true );

			$field_instance = new \GiftFlow_Field(
				$field_id,
				$field_id,
				$field['type'],
				array(
					'value'           => $value,
					'label'           => $field['label'],
					'description'     => $field['description'],
					'wrapper_classes' => array( 'giftflow-field-wrapper' ),
					'classes'         => array( 'giftflow-field-input' ),
					'options'         => array(),
					'attributes'      => array(
						'id'   => $field_id,
						'name' => $field_id,
					),
				)
			);

			$field_instance->render();
		}
	}

	/**
	 * Render the avatar preview (read-only; the donor uploads it from My Account).
	 *
	 * @param \WP_Post $post Post object.
	 * @return void
	 */
	private function render_avatar( $post ) {
		$avatar_id    = (int) get_post_meta( $post->ID, '_avatar_id', true );
		$donor_email  = (string) get_post_meta( $post->ID, '_email', true );
		$is_custom    = $avatar_id > 0;
		$avatar_url   = $is_custom ? wp_get_attachment_image_url( $avatar_id, array( 96, 96 ) ) : '';

		if ( ! $avatar_url && $donor_email ) {
			$avatar_url = get_avatar_url( $donor_email, array( 'size' => 96 ) );
		}
		?>
		<div class="giftflow-field-wrapper giftflow-donor-avatar-admin">
			<label><?php esc_html_e( 'Profile Photo', 'giftflow' ); ?></label>
			<div style="display:flex;align-items:center;gap:12px;margin:6px 0 4px;">
				<?php if ( $avatar_url ) : ?>
					<img src="<?php echo esc_url( $avatar_url ); ?>" alt="" width="64" height="64" style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:1px solid #dcdcde;background:#f6f7f7;" />
				<?php endif; ?>
				<p class="description" style="margin:0;">
					<?php
					if ( $is_custom ) {
						esc_html_e( 'Custom photo uploaded by the donor (Gravatar is overridden).', 'giftflow' );
					} else {
						esc_html_e( 'No custom photo; Gravatar is used. The donor can upload one from My Account.', 'giftflow' );
					}
					?>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Save the meta box.
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_meta_box( $post_id ) {
		if ( ! $this->verify_nonce( 'donor_public_profile_nonce', 'donor_public_profile' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$social_labels = $this->get_social_labels();
		$invalid_socials = array();

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified above.
		if ( isset( $_POST['bio'] ) ) {
			update_post_meta(
				$post_id,
				'_bio',
				// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified above.
				mb_substr( sanitize_textarea_field( wp_unslash( $_POST['bio'] ) ), 0, 500 )
			);
		}

		foreach ( $social_labels as $social_key => $social_label ) {
			$field_name = 'social_' . $social_key;

			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified above.
			if ( ! isset( $_POST[ $field_name ] ) ) {
				continue;
			}

			$social_url = trim( (string) wp_unslash( $_POST[ $field_name ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- nonce verified above; escaped below.

			if ( '' !== $social_url && ! preg_match( '#^[a-z][a-z0-9+.-]*:#i', $social_url ) ) {
				$social_url = 'https://' . ltrim( $social_url, '/' );
			}

			// Invalid links are reported instead of being silently rewritten.
			if ( '' !== $social_url && false === filter_var( $social_url, FILTER_VALIDATE_URL ) ) {
				$invalid_socials[] = $social_label;
				continue;
			}

			update_post_meta( $post_id, '_' . $field_name, esc_url_raw( $social_url ) );
		}

		if ( ! empty( $invalid_socials ) ) {
			set_transient(
				'giftflow_donor_profile_error_' . $post_id,
				sprintf(
					/* translators: %s: comma-separated social network labels. */
					__( 'These profile links were not saved because they are not valid URLs: %s', 'giftflow' ),
					implode( ', ', $invalid_socials )
				),
				60
			);
		}
	}
}
