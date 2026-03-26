<?php
/**
 * HTML field template
 *
 * @package GiftFlow
 * @since 1.0.0
 *
 * @var string $html Custom HTML content.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="giftflow-html-field"><?php echo wp_kses_post( $html ); ?></div>
