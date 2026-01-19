<?php
/**
 * Share Block.
 *
 * @package GiftFlow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register share block.
 *
 * @return void
 */
function giftflow_share_block() {
	register_block_type(
		'giftflow/share',
		array(
			'api_version'     => 3,
			'render_callback' => 'giftflow_share_block_render',
			'attributes'      => array(
				'title'       => array(
					'type'    => 'string',
					'default' => 'Share this',
				),
				'customUrl'   => array(
					'type'    => 'string',
					'default' => '',
				),
				'showSocials' => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'showEmail'   => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'showCopyUrl' => array(
					'type'    => 'boolean',
					'default' => true,
				),
			),
		)
	);
}

add_action( 'init', 'giftflow_share_block' );

/**
 * Render share block.
 *
 * @param array $attributes Block attributes.
 * @param string $content Block content.
 * @param WP_Block $block Block object.
 * @return string Block output.
 */
function giftflow_share_block_render( $attributes, $content, $block ) {
	unset( $content );
	unset( $block );

	$title         = $attributes['title'] ?? esc_html__( 'Share this', 'giftflow' );
	$show_socials  = $attributes['showSocials'] ?? true;
	$show_email    = $attributes['showEmail'] ?? true;
	$show_copy_url = $attributes['showCopyUrl'] ?? true;
	$custom_url    = $attributes['customUrl'] ?? '';

	// Get the URL to share - if customUrl is empty, get current post/page URL.
	if ( ! empty( $custom_url ) ) {
		$share_url = $custom_url;

		// phpcs:ignore Universal.ControlStructures.DisallowLonelyIf.Found
	} else {
		// Get current post/page URL.
		if ( is_singular() ) {
			$share_url = get_permalink();
		} elseif ( is_home() || is_front_page() ) {
			$share_url = home_url( '/' );
		} elseif ( is_category() ) {
			$share_url = get_category_link( get_queried_object_id() );
		} elseif ( is_tag() ) {
			$share_url = get_tag_link( get_queried_object_id() );
		} elseif ( is_author() ) {
			$share_url = get_author_posts_url( get_queried_object_id() );
		} elseif ( is_date() ) {
			$share_url = get_pagenum_link();
		} elseif ( is_search() ) {
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.urlencode_urlencode
			$share_url = home_url( '/?s=' . urlencode( get_search_query() ) );
		} else {
			$share_url = home_url( add_query_arg( array() ) );
		}
	}

	// Get title and description for sharing.
	if ( is_singular() ) {
		$share_title       = get_the_title();
		$share_description = get_the_excerpt() ? get_the_excerpt() : get_bloginfo( 'description' );
	} elseif ( is_home() || is_front_page() ) {
		$share_title       = get_bloginfo( 'name' );
		$share_description = get_bloginfo( 'description' );
	} elseif ( is_category() ) {
		$category          = get_queried_object();
		$share_title       = single_cat_title( '', false );
		$share_description = category_description() ? category_description() : get_bloginfo( 'description' );
	} elseif ( is_tag() ) {
		$share_title       = single_tag_title( '', false );
		$share_description = tag_description() ? tag_description() : get_bloginfo( 'description' );
	} elseif ( is_author() ) {
		$author            = get_queried_object();
		$share_title       = get_the_author_meta( 'display_name', $author->ID );
		$share_description = get_the_author_meta( 'description', $author->ID ) ? get_the_author_meta( 'description', $author->ID ) : get_bloginfo( 'description' );
	} elseif ( is_date() ) {
		$share_title       = get_the_date();
		$share_description = get_bloginfo( 'description' );
	} elseif ( is_search() ) {
		/* translators: %s is the search query */
		$share_title       = sprintf( esc_html__( 'Search results for: %s', 'giftflow' ), get_search_query() );
		$share_description = get_bloginfo( 'description' );
	} else {
		$share_title       = get_bloginfo( 'name' );
		$share_description = get_bloginfo( 'description' );
	}

	// Default social platforms (Facebook, X/Twitter, LinkedIn).
	$social_platforms = array( 'twitter', 'facebook', 'linkedin' );

	ob_start();
	?>
	<div class="giftflow-share">
		<?php if ( ! empty( $title ) ) : ?>
			<strong class="giftflow-share__title"><?php echo esc_html( $title ); ?></strong>
		<?php endif; ?>

		<div class="giftflow-share__buttons">
			<?php if ( $show_socials && ! empty( $social_platforms ) ) : ?>
				<?php foreach ( $social_platforms as $platform ) : ?>
					<?php echo wp_kses_post( giftflow_render_social_share_button( $platform, $share_url, $share_title, $share_description ) ); ?>
				<?php endforeach; ?>
			<?php endif; ?>

			<?php if ( $show_email ) : ?>
				<a href="<?php echo esc_url( giftflow_get_email_share_url( $share_url, $share_title, $share_description ) ); ?>" 
					class="giftflow-share__button giftflow-share__button--email"
					title="<?php esc_attr_e( 'Share via Email', 'giftflow' ); ?>"
					target="_blank"
					rel="noopener noreferrer">
					<span class="giftflow-share__text"><?php esc_html_e( 'Email', 'giftflow' ); ?></span>
				</a>
			<?php endif; ?>

			<?php if ( $show_copy_url ) : ?>
				<a href="#"
					class="giftflow-share__button giftflow-share__button--copy-url"
					data-url="<?php echo esc_attr( $share_url ); ?>"
					title="<?php esc_attr_e( 'Copy URL to clipboard', 'giftflow' ); ?>"
					onclick="giftflowCopyUrlToClipboard('<?php echo esc_js( $share_url ); ?>', this)">
					<span class="giftflow-share__text"><?php esc_html_e( 'Copy Link', 'giftflow' ); ?></span>
				</a>
			<?php endif; ?>
		</div>

		<?php if ( $show_copy_url ) : ?>
			<div class="giftflow-share__copy-feedback" style="display: none;">
				<span class="giftflow-share__copy-message">
					<?php echo wp_kses( giftflow_svg_icon( 'checkmark-circle' ), giftflow_allowed_svg_tags() ); ?>
					<?php esc_html_e( 'URL copied to clipboard!', 'giftflow' ); ?>
				</span>
			</div>
		<?php endif; ?>
	</div>

	<script>
	function giftflowCopyUrlToClipboard(url, button) {
		if (navigator.clipboard && window.isSecureContext) {
			// Use modern clipboard API.
			navigator.clipboard.writeText(url).then(function() {
				giftflowShowCopyFeedback(button);
			}).catch(function(err) {
				console.error('Failed to copy: ', err);
				giftflowFallbackCopy(url, button);
			});
		} else {
			// Fallback for older browsers.
			giftflowFallbackCopy(url, button);
		}
	}

	function giftflowFallbackCopy(url, button) {
		const textArea = document.createElement('textarea');
		textArea.value = url;
		textArea.style.position = 'fixed';
		textArea.style.left = '-999999px';
		textArea.style.top = '-999999px';
		document.body.appendChild(textArea);
		textArea.focus();
		textArea.select();
		
		try {
			document.execCommand('copy');
			giftflowShowCopyFeedback(button);
		} catch (err) {
			console.error('Fallback copy failed: ', err);
		}
		
		document.body.removeChild(textArea);
	}

	function giftflowShowCopyFeedback(button) {
		const container = button.closest('.giftflow-share');
		const feedback = container.querySelector('.giftflow-share__copy-feedback');
		
		if (feedback) {
			feedback.style.display = 'block';
			setTimeout(function() {
				feedback.style.display = 'none';
			}, 2000);
		}
	}
	</script>
	<?php
	return ob_get_clean();
}

/**
 * Render social share button.
 *
 * @param string $platform The platform name.
 * @param string $url The URL to share.
 * @param string $title The title to share.
 * @param string $description The description to share.
 * @return string The social share button HTML.
 */
function giftflow_render_social_share_button( $platform, $url, $title, $description ) {
	unset( $description );

	$share_urls = array(
		'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $url ),
		'twitter'  => 'https://twitter.com/intent/tweet?url=' . rawurlencode( $url ) . '&text=' . rawurlencode( $title ),
		'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $url ),
	);

	$platform_names = array(
		'facebook' => __( 'Facebook', 'giftflow' ),
		'twitter'  => __( 'X', 'giftflow' ),
		'linkedin' => __( 'LinkedIn', 'giftflow' ),
	);

	$platform_icons = array(
		'facebook' => 'facebook',
		'twitter'  => 'twitter',
		'linkedin' => 'linkedin',
	);

	if ( ! isset( $share_urls[ $platform ] ) ) {
		return '';
	}

	$share_url     = $share_urls[ $platform ];
	$platform_name = $platform_names[ $platform ];
	$icon_name     = $platform_icons[ $platform ];

	ob_start();

	// translators: %s is the platform name.
	$a_title = sprintf( esc_html__( 'Share on %s', 'giftflow' ), $platform_name );
	?>
	<a href="<?php echo esc_url( $share_url ); ?>" 
		class="giftflow-share__button giftflow-share__button--<?php echo esc_attr( $platform ); ?>"
		title="<?php echo esc_attr( $a_title ); ?>"
		target="_blank"
		rel="noopener noreferrer">
		<span class="giftflow-share__text"><?php echo esc_html( $platform_name ); ?></span>
	</a>
	<?php
	return ob_get_clean();
}

/**
 * Get email share URL.
 *
 * @param string $url The URL to share.
 * @param string $title The title to share.
 * @param string $description The description to share.
 * @return string The email share URL.
 */
function giftflow_get_email_share_url( $url, $title, $description ) {
	// translators: %s is the title.
	$subject = sprintf( __( 'Check out: %s', 'giftflow' ), $title );
	// translators: 1: is the title, 2: is the description, 3: is the URL.
	$body = sprintf( __( 'I thought you might be interested in this:\n\n%1$s\n\n%2$s\n\n%3$s', 'giftflow' ), $title, $description, $url );

	// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.urlencode_urlencode
	return 'mailto:?subject=' . urlencode( $subject ) . '&body=' . urlencode( $body );
}
