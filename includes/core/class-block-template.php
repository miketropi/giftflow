<?php
/**
 * Block Template Handler
 *
 * @package GiftFlow
 */

namespace GiftFlow\Core;

/**
 * Class Block_Template
 *
 * Handles registration of block templates for the theme.
 */
class Block_Template {

	/**
	 * Initialize the class
	 */
	public function __construct() {
		// $this->register_block_templates();
	}

	/**
	 * Register block templates
	 */
	public function register_block_templates() {

		// Register templates for pages.
		$templates = array(
			'campaigns-page'        => array(
				'title'       => esc_html__( 'Campaigns Page', 'giftflow' ),
				'description' => esc_html__( 'A template for the campaigns.', 'giftflow' ),
				'post_types'  => array( 'page' ),
				'template'    => 'campaigns-page',
			),
			'archive-campaign'      => array(
				'title'       => esc_html__( 'Campaign Archive', 'giftflow' ),
				'description' => esc_html__( 'A template for the campaign archive page.', 'giftflow' ),
				'template'    => 'archive-campaign',
			),
			'taxonomy-campaign-tax' => array(
				'title'       => esc_html__( 'Category Campaign Archive', 'giftflow' ),
				'description' => esc_html__( 'A template for the category campaign archive page.', 'giftflow' ),
				'template'    => 'category-campaign-archive',
			),
			'single-campaign'       => array(
				'title'       => esc_html__( 'Single Campaign', 'giftflow' ),
				'description' => esc_html__( 'A template for the single campaign page.', 'giftflow' ),
				'post_types'  => array( 'campaign' ),
				'template'    => 'single-campaign',
			),
			'donor-account'         => array(
				'title'       => esc_html__( 'Donor Account', 'giftflow' ),
				'description' => esc_html__( 'A template for the donor account page.', 'giftflow' ),
				'post_types'  => array( 'page' ),
				'template'    => 'donor-account',
			),
			'thank-donor'           => array(
				'title'       => esc_html__( 'Thank Donor', 'giftflow' ),
				'description' => esc_html__( 'A template for the thank donor page.', 'giftflow' ),
				'post_types'  => array( 'page' ),
				'template'    => 'thank-donor',
			),
		);

		foreach ( $templates as $slug => $template ) {

			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$content             = file_get_contents( GIFTFLOW_PLUGIN_DIR . 'block-templates/' . $template['template'] . '.html' );
			$template['content'] = apply_filters( 'giftflow_block_template_content', $content, $template );

			register_block_template(
				'giftflow//' . $slug,
				$template
			);
		}
	}
}
