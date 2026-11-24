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
