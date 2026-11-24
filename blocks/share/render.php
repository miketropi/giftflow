<?php
/**
 * Render callback for the Share block.
 *
 * @package GiftFlow
 * @subpackage Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gf_title          = $attributes['title'] ?? __( 'Share this', 'giftflow' );
$gf_show_socials   = $attributes['showSocials'] ?? true;
$gf_show_email     = $attributes['showEmail'] ?? true;
$gf_show_copy      = $attributes['showCopyUrl'] ?? true;
$gf_custom_url     = $attributes['customUrl'] ?? '';

$gf_share_url   = '';
$gf_share_title = get_bloginfo( 'name' );
$gf_share_desc  = get_bloginfo( 'description' );

if ( '' !== trim( (string) $gf_custom_url ) ) {
	$gf_share_url = esc_url_raw( $gf_custom_url );
} elseif ( is_singular() ) {
	$gf_share_url   = get_permalink();
	$gf_share_title = get_the_title();
	$gf_share_desc  = wp_trim_words( get_the_excerpt(), 30 );
} elseif ( is_home() || is_front_page() ) {
	$gf_share_url = home_url( '/' );
} elseif ( is_category() || is_tag() || is_tax() ) {
	$gf_share_url = get_term_link( get_queried_object_id() );
} else {
	$gf_share_url = home_url( add_query_arg( array() ) );
}

$gf_social_links = array(
	'facebook' => array(
		'url'   => 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $gf_share_url ),
		'label' => __( 'Facebook', 'giftflow' ),
	),
	'x'        => array(
		'url'   => 'https://x.com/intent/tweet?url=' . rawurlencode( $gf_share_url ) . '&text=' . rawurlencode( $gf_share_title ),
		'label' => 'X',
	),
	'linkedin' => array(
		'url'   => 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $gf_share_url ),
		'label' => __( 'LinkedIn', 'giftflow' ),
	),
);

$gf_email_url = 'mailto:?subject=' . rawurlencode( $gf_share_title ) . '&body=' . rawurlencode( $gf_share_desc . "\n\n" . $gf_share_url );

$block_wrapper_attrs = get_block_wrapper_attributes(
	array( 'class' => 'giftflow-share' )
);
?>
<div <?php echo $block_wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( ! empty( $gf_title ) ) : ?>
		<span class="giftflow-share__title"><?php echo esc_html( $gf_title ); ?></span>
	<?php endif; ?>

	<div class="giftflow-share__btns">
		<?php if ( $gf_show_socials ) : ?>
			<?php foreach ( $gf_social_links as $gf_platform => $gf_link ) : ?>
				<a
					href="<?php echo esc_url( $gf_link['url'] ); ?>"
					class="giftflow-share__btn giftflow-share__button"
					target="_blank"
					rel="noopener noreferrer"
					title="<?php echo esc_attr( $gf_link['label'] ); ?>"
					data-network="<?php echo esc_attr( $gf_platform ); ?>"
					data-url="<?php echo esc_url( $gf_share_url ); ?>"
				>
					<?php echo esc_html( $gf_link['label'] ); ?>
				</a>
			<?php endforeach; ?>
		<?php endif; ?>

		<?php if ( $gf_show_email ) : ?>
			<a href="<?php echo esc_url( $gf_email_url ); ?>" class="giftflow-share__btn giftflow-share__button" title="<?php esc_attr_e( 'Share via email', 'giftflow' ); ?>" data-network="email" data-url="<?php echo esc_url( $gf_share_url ); ?>">
				<?php esc_html_e( 'Email', 'giftflow' ); ?>
			</a>
		<?php endif; ?>

		<?php if ( $gf_show_copy ) : ?>
			<button
				type="button"
				class="giftflow-share__btn giftflow-share__button giftflow-share__button--copy-url"
				data-url="<?php echo esc_url( $gf_share_url ); ?>"
				onclick="var b=this,u=b.dataset.url;if(!u)return;var o=b.textContent;navigator.clipboard.writeText(u).then(function(){b.textContent='<?php echo esc_js( __( 'Copied', 'giftflow' ) ); ?>';setTimeout(function(){b.textContent=o},1000)}).catch(function(){b.textContent='<?php echo esc_js( __( 'Error', 'giftflow' ) ); ?>';setTimeout(function(){b.textContent=o},600)});"
				title="<?php esc_attr_e( 'Copy link', 'giftflow' ); ?>"
			>
				<?php esc_html_e( 'Copy Link', 'giftflow' ); ?>
			</button>
		<?php endif; ?>
	</div>
</div>
