<?php
/**
 * Thank donor page (full document — header/footer, no block editor / do_blocks).
 *
 * Inner layout: templates/thank-donor/content-thank-donor.php
 * Hooks: giftflow_thank_donor_before_content, giftflow_thank_donor_after_content
 * Filter: giftflow_thank_donor_content_template, giftflow_thank_donor_show_page_title
 *
 * @package GiftFlow
 * @subpackage Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$page_id = giftflow_get_thank_donor_page();
$page_id = is_scalar( $page_id ) ? absint( $page_id ) : 0;
if ( $page_id <= 0 ) {
	return;
}

get_header( 'giftflow' );

// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
$__page = get_post( $page_id );
if ( ! $__page instanceof WP_Post ) {
	return;
}

/**
 * Fires at the very start of the thank donor inner template.
 *
 * @param int $page_id Thank donor page ID.
 */
do_action( 'giftflow_thank_donor_before_content', $page_id );

$template_rel = apply_filters(
	'giftflow_thank_donor_content_template',
	'thank-donor/content-thank-donor.php',
	$page_id
);
$template_rel = is_string( $template_rel ) && '' !== $template_rel
	? $template_rel
	: 'thank-donor/content-thank-donor.php';

?>
<main id="primary" class="gfw-thank-donor-page-main" role="main">
	<?php
	if ( apply_filters( 'giftflow_thank_donor_show_page_title', false, $page_id ) ) {
		?>
	<header class="gfw-thank-donor-page__header">
		<h1 class="gfw-thank-donor-page__title entry-title"><?php echo esc_html( get_the_title( $page_id ) ); ?></h1>
	</header>
		<?php
	}

	giftflow_load_template(
		$template_rel,
		array(
			'page_id' => $page_id,
			'page'    => $__page,
		)
	);
	?>
</main>
<?php

/**
 * Fires at the very end of the thank donor inner template.
 *
 * @param int $page_id Thank donor page ID.
 */
do_action( 'giftflow_thank_donor_after_content', $page_id );

get_footer( 'giftflow' );
