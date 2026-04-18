<?php
/**
 * Campaign details template (full page: header + inner + footer).
 *
 * @package GiftFlow
 * @subpackage Templates
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header( 'giftflow' );

$giftflow_template = new \GiftFlow\Frontend\Template();
$giftflow_template->load_template( 'classic/single-campaign-inner.php' );

get_footer( 'giftflow' );
