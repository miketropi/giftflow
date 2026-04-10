<?php
/**
 * Donor account page (full document — header/footer, no block editor / do_blocks).
 *
 * Inner layout: templates/donor-account/content-donor-account.php
 * Hooks: giftflow_donor_account_before_content, giftflow_donor_account_after_content
 * Filter: giftflow_donor_account_content_template
 *
 * @package GiftFlow
 * @subpackage Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$page_id = giftflow_get_donor_account_page();
$page_id = is_scalar( $page_id ) ? absint( $page_id ) : 0;
if ( $page_id <= 0 ) {
	return;
}

get_header( 'giftflow' );

$__page = get_post( $page_id );
if ( ! $__page instanceof WP_Post ) {
	return;
}

/**
 * Fires at the very start of the donor account inner template.
 *
 * @param int $page_id Donor account page ID.
 */
do_action( 'giftflow_donor_account_before_content', $page_id );

$template_rel = apply_filters(
	'giftflow_donor_account_content_template',
	'donor-account/content-donor-account.php',
	$page_id
);
$template_rel = is_string( $template_rel ) && '' !== $template_rel
	? $template_rel
	: 'donor-account/content-donor-account.php';

?>
<main id="primary" class="gfw-donor-account-page-main" role="main">
	<?php
	if ( apply_filters( 'giftflow_donor_account_show_page_title', true, $page_id ) ) {
		?>
	<header class="gfw-donor-account-page__header">
		<h1 class="gfw-donor-account-page__title entry-title"><?php echo esc_html( get_the_title( $page_id ) ); ?></h1>
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
 * Fires at the very end of the donor account inner template.
 *
 * @param int $page_id Donor account page ID.
 */
do_action( 'giftflow_donor_account_after_content', $page_id );

get_footer( 'giftflow' );
