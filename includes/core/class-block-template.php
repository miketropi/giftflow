<?php
/**
 * Block theme template loader — plugin-provided `wp_template` entries for FSE.
 *
 * @package GiftFlow
 * @subpackage Core
 */

namespace GiftFlow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers GiftFlow HTML templates with the block theme template system.
 */
class Block_Template {

	/**
	 * Constructor — hooks filters when running a block theme.
	 */
	public function __construct() {
		if ( ! function_exists( 'wp_is_block_theme' ) || ! wp_is_block_theme() ) {
			return;
		}

		add_filter( 'get_block_templates', array( $this, 'provide_templates' ), 10, 3 );
		add_filter( 'get_block_template', array( $this, 'provide_template_by_id' ), 10, 3 );
	}

	/**
	 * Map slug => file path + front-end context callback.
	 *
	 * @return array<string, array{file: string, context: callable}>
	 */
	private function get_template_map(): array {
		$base = trailingslashit( GIFTFLOW_PLUGIN_DIR );

		return array(
			'page-campaigns'        => array(
				'file'    => $base . 'block-templates/page-campaigns.html',
				'context' => static function () {
					return is_campaigns_page();
				},
			),
			'taxonomy-campaign-tax' => array(
				'file'    => $base . 'block-templates/taxonomy-campaign-tax.html',
				'context' => static function () {
					return is_tax( 'campaign-tax' );
				},
			),
			'single-campaign'       => array(
				'file'    => $base . 'block-templates/single-campaign.html',
				'context' => static function () {
					return is_singular( 'campaign' );
				},
			),
			'page-donor-account'    => array(
				'file'    => $base . 'block-templates/page-donor-account.html',
				'context' => static function () {
					return is_my_account_page();
				},
			),
			'page-thank-donor'      => array(
				'file'    => $base . 'block-templates/page-thank-donor.html',
				'context' => static function () {
					return is_thank_donor_page();
				},
			),
		);
	}

	/**
	 * Inject templates when WordPress queries by slug__in (front + editor list).
	 *
	 * @param \WP_Block_Template[] $query_result Found templates.
	 * @param array                  $query        Query args.
	 * @param string                 $template_type Template post type.
	 * @return \WP_Block_Template[]
	 */
	public function provide_templates( array $query_result, array $query, string $template_type ): array {
		if ( 'wp_template' !== $template_type ) {
			return $query_result;
		}
		if ( empty( $query['slug__in'] ) ) {
			return $query_result;
		}

		$requested_slugs = $query['slug__in'];
		$template_map    = $this->get_template_map();
		$is_editor       = is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST );

		foreach ( $template_map as $slug => $template ) {
			if ( ! in_array( $slug, $requested_slugs, true ) ) {
				continue;
			}
			if ( ! file_exists( $template['file'] ) ) {
				continue;
			}
			if ( ! $is_editor && ! $template['context']() ) {
				continue;
			}

			foreach ( $query_result as $existing ) {
				if ( $existing->slug === $slug ) {
					continue 2;
				}
			}

			$query_result[] = $this->build_template(
				get_stylesheet() . '//' . $slug,
				$slug,
				$template['file']
			);
		}

		return $query_result;
	}

	/**
	 * Inject template when the site editor resolves a template by ID.
	 *
	 * @param \WP_Block_Template|null $block_template Resolved template or null.
	 * @param string                  $id            e.g. theme//slug.
	 * @param string                  $template_type Template post type.
	 * @return \WP_Block_Template|null
	 */
	public function provide_template_by_id( $block_template, string $id, string $template_type ) {
		if ( 'wp_template' !== $template_type ) {
			return $block_template;
		}

		if ( $block_template instanceof \WP_Block_Template ) {
			return $block_template;
		}

		$parts = explode( '//', $id );
		if ( count( $parts ) !== 2 ) {
			return $block_template;
		}

		$slug         = $parts[1];
		$template_map = $this->get_template_map();

		if ( ! isset( $template_map[ $slug ] ) ) {
			return $block_template;
		}
		if ( ! file_exists( $template_map[ $slug ]['file'] ) ) {
			return $block_template;
		}

		return $this->build_template( $id, $slug, $template_map[ $slug ]['file'] );
	}

	/**
	 * Build a WP_Block_Template object from a plugin HTML file.
	 *
	 * @param string $id   Full template id (theme//slug).
	 * @param string $slug Template slug.
	 * @param string $file Absolute path to HTML.
	 */
	private function build_template( string $id, string $slug, string $file ): \WP_Block_Template {
		$template                 = new \WP_Block_Template();
		$template->id             = $id;
		$template->theme          = get_stylesheet();
		$template->slug           = $slug;
		$template->source         = 'plugin';
		$template->origin         = 'plugin';
		$template->type           = 'wp_template';
		$template->title          = ucwords( str_replace( '-', ' ', $slug ) );
		$template->status         = 'publish';
		$template->has_theme_file = false;
		$template->is_custom      = false;

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local plugin file.
		$content = file_get_contents( $file );
		$template->content = false !== $content ? $content : '';

		return $template;
	}
}
