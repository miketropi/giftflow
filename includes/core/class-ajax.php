<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * AJAX Handler Class.
 * Handles AJAX requests for the GiftFlow plugin.
 *
 * @package GiftFlow
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX Handler Class
 *
 * @since 1.0.0
 */
class GiftFlow_Ajax {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Register AJAX actions.
		add_action( 'wp_ajax_giftflow_get_gallery_images', array( $this, 'get_gallery_images' ) );

		add_action( 'wp_ajax_giftflow_get_pagination_donation_list_html', array( $this, 'get_pagination_donation_list_html' ) );
		add_action( 'wp_ajax_nopriv_giftflow_get_pagination_donation_list_html', array( $this, 'get_pagination_donation_list_html' ) );

		// giftflow_get_campaign_donation_form.
		add_action( 'wp_ajax_giftflow_get_campaign_donation_form', array( $this, 'get_campaign_donation_form' ) );
		add_action( 'wp_ajax_nopriv_giftflow_get_campaign_donation_form', array( $this, 'get_campaign_donation_form' ) );

		// Donor profile avatar.
		add_action( 'wp_ajax_giftflow_upload_donor_avatar', array( $this, 'upload_donor_avatar' ) );
		add_action( 'wp_ajax_giftflow_remove_donor_avatar', array( $this, 'remove_donor_avatar' ) );
	}

	/**
	 * Get gallery images
	 */
	public function get_gallery_images() {
		// Check nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'giftflow_gallery_nonce' ) ) {
			wp_send_json_error( __( 'Security check failed', 'giftflow' ) );
		}

		// Check if IDs are provided.
		if ( ! isset( $_POST['ids'] ) || ! is_array( $_POST['ids'] ) ) {
			wp_send_json_error( __( 'No image IDs provided', 'giftflow' ) );
		}

		// Get image size.
		$size = isset( $_POST['size'] ) ? sanitize_text_field( wp_unslash( $_POST['size'] ) ) : 'thumbnail';

		// Get image IDs.
		$ids = array_map( 'intval', $_POST['ids'] );

		// Get image data.
		$images = array();
		foreach ( $ids as $id ) {
			$image_url = wp_get_attachment_image_url( $id, $size );
			if ( $image_url ) {
				$images[ $id ] = array(
					'url' => $image_url,
					'alt' => get_post_meta( $id, '_wp_attachment_image_alt', true ),
				);
			}
		}

		// Send response.
		wp_send_json_success( $images );
	}

	/**
	 * Get pagination donation list HTML.
	 */
	public function get_pagination_donation_list_html() {
		// ajax check nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'giftflow_common_nonce' ) ) {
			wp_send_json_error( __( 'Security check failed', 'giftflow' ) );
		}

		$campaign = isset( $_POST['campaign'] ) ? intval( $_POST['campaign'] ) : 0;
		$paged    = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1;

		// validate $campaign.
		if ( $campaign <= 0 ) {
			wp_send_json_error( __( 'Invalid campaign ID', 'giftflow' ) );
		}

		$args    = array(
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query' => array(
				array(
					'key'     => '_status',
					'value'   => 'completed',
					'compare' => '=',
				),
			),
		);
		$_paged  = $paged;
		$results = giftflow_get_campaign_donations( $campaign, $args, $_paged );

		ob_start();
		giftflow_load_template(
			'donation-list-of-campaign.php',
			array(
				'donations'   => $results,
				'paged'       => $_paged,
				'campaign_id' => $campaign,
			)
		);
		$html = ob_get_clean();

		wp_send_json_success(
			array(
				'__html'                     => $html,
				'__replace_content_selector' => '.__donations-list-by-campaign-' . $campaign,
			)
		);
	}

	/**
	 * Store a donor's uploaded profile photo.
	 *
	 * The browser crops the image to a square before uploading, so the stored
	 * attachment is always ready to render as an avatar.
	 *
	 * @return void
	 */
	public function upload_donor_avatar() {
		check_ajax_referer( 'giftflow_avatar', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( __( 'You must be signed in to change your photo.', 'giftflow' ), 403 );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- validated by media_handle_upload().
		$file = isset( $_FILES['avatar'] ) ? $_FILES['avatar'] : array();

		if ( empty( $file ) || ! isset( $file['error'] ) || UPLOAD_ERR_OK !== (int) $file['error'] ) {
			wp_send_json_error( __( 'Choose an image to upload.', 'giftflow' ) );
		}

		if ( isset( $file['size'] ) && (int) $file['size'] > 5 * MB_IN_BYTES ) {
			wp_send_json_error( __( 'The image must be smaller than 5 MB.', 'giftflow' ) );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$attachment_id = media_handle_upload(
			'avatar',
			0,
			array( 'post_title' => __( 'Donor profile photo', 'giftflow' ) ),
			array(
				'test_form' => false,
				'mimes'     => array(
					'jpg|jpeg' => 'image/jpeg',
					'png'      => 'image/png',
					'webp'     => 'image/webp',
				),
			)
		);

		if ( is_wp_error( $attachment_id ) ) {
			wp_send_json_error( $attachment_id->get_error_message() );
		}

		$user     = wp_get_current_user();
		$previous = (int) get_user_meta( $user->ID, 'giftflow_avatar_id', true );

		update_user_meta( $user->ID, 'giftflow_avatar_id', (int) $attachment_id );

		$donor_id = giftflow_get_donor_id_by_email( $user->user_email );

		if ( $donor_id ) {
			update_post_meta( $donor_id, '_avatar_id', (int) $attachment_id );
		}

		if ( $previous > 0 && $previous !== (int) $attachment_id ) {
			wp_delete_attachment( $previous, true );
		}

		wp_send_json_success(
			array(
				'id'  => (int) $attachment_id,
				'url' => wp_get_attachment_image_url( (int) $attachment_id, array( 192, 192 ) ),
			)
		);
	}

	/**
	 * Remove the custom avatar and fall back to Gravatar.
	 *
	 * @return void
	 */
	public function remove_donor_avatar() {
		check_ajax_referer( 'giftflow_avatar', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( __( 'You must be signed in to change your photo.', 'giftflow' ), 403 );
		}

		$user     = wp_get_current_user();
		$previous = (int) get_user_meta( $user->ID, 'giftflow_avatar_id', true );

		delete_user_meta( $user->ID, 'giftflow_avatar_id' );

		$donor_id = giftflow_get_donor_id_by_email( $user->user_email );

		if ( $donor_id ) {
			delete_post_meta( $donor_id, '_avatar_id' );
		}

		if ( $previous > 0 ) {
			wp_delete_attachment( $previous, true );
		}

		wp_send_json_success(
			array(
				'url' => get_avatar_url( $user->user_email, array( 'size' => 192 ) ),
			)
		);
	}

	/**
	 * Get campaign donation form.
	 */
	public function get_campaign_donation_form() {
		// ajax check nonce.
		if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), 'giftflow_common_nonce' ) ) {
			wp_send_json_error( __( 'Security check failed', 'giftflow' ) );
		}

		$campaign_id = isset( $_GET['campaign_id'] ) ? intval( wp_unslash( $_GET['campaign_id'] ) ) : 0;

		if ( $campaign_id <= 0 ) {
			wp_send_json_error( __( 'Invalid campaign ID', 'giftflow' ) );
		}

		$campaign = get_post( $campaign_id );

		if ( ! $campaign || 'campaign' !== $campaign->post_type || 'publish' !== $campaign->post_status ) {
			wp_send_json_error( __( 'Invalid campaign ID', 'giftflow' ) );
		}

		try {
			$shortcode = '[giftflow_donation_form campaign_id="' . $campaign_id . '"';

			// Pass a pre-selected amount when provided (e.g., from a preset amount pill).
			$preset_amount = isset( $_GET['amount'] ) ? floatval( wp_unslash( $_GET['amount'] ) ) : 0;
			if ( $preset_amount > 0 ) {
				$shortcode .= ' default_amount="' . $preset_amount . '"';
			}

			$shortcode .= ']';

			$content = do_shortcode( $shortcode );
			echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Shortcode output is already escaped by templates.
		} catch ( \Throwable $e ) {
			wp_send_json_error( $e->getMessage() );
		}

		die();
	}
}

// Initialize the AJAX handler.
new GiftFlow_Ajax();
