<?php
/**
 * Campaign Single Content Block.
 *
 * @package GiftFlow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register campaign single content block.
 *
 * @return void
 */
function giftflow_campaign_single_content_block() {
	register_block_type(
		'giftflow/campaign-single-content',
		array(
			'api_version' => 3,
			'render_callback' => 'giftflow_campaign_single_content_block_render',
		)
	);
}

/**
 * Add action to register campaign single content block.
 */
add_action( 'init', 'giftflow_campaign_single_content_block' );

/**
 * Render campaign single content block.
 *
 * @param array $attributes Block attributes.
 * @param string $content Block content.
 * @param WP_Block $block Block object.
 * @return string Block output.
 */
function giftflow_campaign_single_content_block_render( $attributes, $content, $block ) {
	unset( $content );
	unset( $block );
	$post_id = get_the_ID();

	// Check if it is a WP json api request.
	if ( wp_is_serving_rest_request() ) {
		// We can assume it is a server side render callback from Gutenberg.
		if ( isset( $attributes['__editorPostId'] ) ) {
			// Value from JS can be a float, we need integer.
			$attributes['__editorPostId'] = (int) $attributes['__editorPostId'];
		}
		$post_id = $attributes['__editorPostId'] ?? $post_id;
	}

	// build icons array.
	$icons = array(
		'campaign' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text-icon lucide-file-text"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>',
		'donations' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users-icon lucide-users"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
		'comments' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-circle-icon lucide-message-circle"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>',
	);

	// build tabs array.
	$tabs = array(
		'campaign' => array(
			'id' => 'campaign',
			'label' => apply_filters( 'giftflow_campaign_single_content_tab_campaign_label', esc_html__( 'Campaign', 'giftflow' ) ),
			'callback' => 'giftflow_campaign_single_content_tab_campaign',
			'icon' => apply_filters( 'giftflow_campaign_single_content_tab_campaign_icon', $icons['campaign'] ),
			'is_active' => true,
		),
		'donations' => array(
			'id' => 'donations',
			'label' => apply_filters( 'giftflow_campaign_single_content_tab_donations_label', esc_html__( 'Donations', 'giftflow' ) ),
			'callback' => 'giftflow_campaign_single_content_tab_donations',
			'icon' => apply_filters( 'giftflow_campaign_single_content_tab_donations_icon', $icons['donations'] ),
		),
		'comments' => array(
			'id' => 'comments',
			'label' => apply_filters( 'giftflow_campaign_single_content_tab_comments_label', esc_html__( 'Comments', 'giftflow' ) ),
			'callback' => 'giftflow_campaign_single_content_tab_comments',
			'icon' => apply_filters( 'giftflow_campaign_single_content_tab_comments_icon', $icons['comments'] ),
		),
	);

	// Allow filtering of tabs.
	$tabs = apply_filters( 'giftflow_campaign_single_content_tabs', $tabs, $post_id );

	ob_start();
	?>
	<div class="giftflow-campaign-single-content">
		<!-- Tab Widget -->
		<div class="giftflow-tab-widget">
			<div class="giftflow-tab-widget-tabs">
				<?php foreach ( $tabs as $tab ) : ?>
					<div class="giftflow-tab-widget-tab-item <?php echo isset( $tab['is_active'] ) && true === $tab['is_active'] ? 'active' : ''; ?>" data-tab-id="<?php echo esc_attr( $tab['id'] ); ?>">
						<span class="giftflow-tab-widget-tab-item-icon">
							<?php echo wp_kses( $tab['icon'], giftflow_allowed_svg_tags() ); ?>
						</span>
						<span class="giftflow-tab-widget-tab-item-label">
							<?php echo esc_html( $tab['label'] ); ?>
						</span> 
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<!-- Tab Content -->
		<div class="giftflow-tab-widget-content">
			<?php foreach ( $tabs as $tab ) : ?>
				<div class="giftflow-tab-widget-content-item <?php echo isset( $tab['is_active'] ) && true === $tab['is_active'] ? 'active' : ''; ?>" data-tab-id="<?php echo esc_attr( $tab['id'] ); ?>">
					<?php
					if ( isset( $tab['callback'] ) && is_callable( $tab['callback'] ) ) {
						call_user_func( $tab['callback'], $post_id );
					} else {
						?>
						<p><?php esc_html_e( 'No content available.', 'giftflow' ); ?></p>
						<?php
					}
					?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Filter campaign single content tab campaign.
 *
 * @param int $post_id The ID of the campaign.
 * @return void
 */
function giftflow_campaign_single_content_tab_campaign( $post_id ) {
	?>
	<?php
		/**
		 * Hook before campaign post content in single campaign content tab.
		 *
		 * @param int $post_id The ID of the campaign.
		 */
		do_action( 'giftflow_campaign_single_content_tab_campaign_before', $post_id );
	?>
	<div class="campaign-post-content">
		<!-- Campaign post content by id. -->
		<?php echo wp_kses_post( get_the_content( $post_id ) ); ?>
	</div>
	<?php
		/**
		 * Hook after campaign post content in single campaign content tab.
		 *
		 * @param int $post_id The ID of the campaign.
		 */
		do_action( 'giftflow_campaign_single_content_tab_campaign_after', $post_id );
	?>
	<?php
}

/**
 * Filter campaign single content tab donations.
 *
 * @param int $post_id The ID of the campaign.
 * @return void
 */
function giftflow_campaign_single_content_tab_donations( $post_id ) {
	?>
	<?php
		/**
		 * Hook before campaign donations list in single campaign content tab.
		 *
		 * @param int $post_id The ID of the campaign.
		 */
		do_action( 'giftflow_campaign_single_content_tab_donations_before', $post_id );
	?>
	<div class="campaign-post-donations">
		<!-- description -->
		<strong><?php esc_html_e( 'Below are the donations for this campaign.', 'giftflow' ); ?></strong>

		<div class="__campaign-post-donations-list __donations-list-by-campaign-<?php echo esc_attr( $post_id ); ?>">
			<?php
				// get all donations for the campaign.
				// meta query status = completed.
				$args = array(
					// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'meta_query' => array(
						array(
							'key' => '_status',
							'value' => 'completed',
							'compare' => '=',
						),
					),
				);
				$_paged = 1;
				$results = giftflow_get_campaign_donations( $post_id, $args, $_paged );
				giftflow_load_template(
					'donation-list-of-campaign.php',
					array(
						'donations' => $results,
						'paged' => $_paged,
						'campaign_id' => $post_id,
					)
				);
			?>
		</div>
	</div>
	<?php
		/**
		 * Hook after campaign donations list in single campaign content tab.
		 *
		 * @param int $post_id The ID of the campaign.
		 */
		do_action( 'giftflow_campaign_single_content_tab_donations_after', $post_id );
	?>
	<?php
}


/**
 * Filter campaign single content tab comments.
 *
 * @param int $post_id The ID of the campaign.
 * @return void
 */
function giftflow_campaign_single_content_tab_comments( $post_id ) {
	?>
	<?php
		/**
		 * Hook before campaign comments list in single campaign content tab.
		 *
		 * @param int $post_id The ID of the campaign.
		 */
		do_action( 'giftflow_campaign_single_content_tab_comments_before', $post_id );
	?>
	<div class="campaign-post-comments">
		<!-- description -->
		<strong><?php esc_html_e( 'Below are the comments for this campaign.', 'giftflow' ); ?></strong>

		<div class="campaign-post-comments-content">
			<?php
				// load comments template.
				giftflow_load_template(
					'campaign-comment.php',
					array(
						'post_id' => $post_id,
					)
				);
			?>
		</div>
	</div>
	<?php
		/**
		 * Hook after campaign comments list in single campaign content tab.
		 *
		 * @param int $post_id The ID of the campaign.
		 */
		do_action( 'giftflow_campaign_single_content_tab_comments_after', $post_id );
	?>
	<?php
}
