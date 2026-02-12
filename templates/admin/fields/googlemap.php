<?php
/**
 * Google Maps field template
 *
 * @package GiftFlow
 * @since 1.0.0
 *
 * @var string $id Field ID.
 * @var string $name Field name.
 * @var string $map_id Unique map ID.
 * @var string $value JSON-encoded value.
 * @var string $address Address string.
 * @var string $lat Latitude.
 * @var string $lng Longitude.
 * @var string $api_key Google Maps API key.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div 
	class="giftflow-googlemap-field" 
	id="<?php echo esc_attr( $map_id ); ?>" 
	data-api-key="<?php echo esc_attr( $api_key ); ?>"
	data-lat="<?php echo esc_attr( $lat ); ?>"
	data-lng="<?php echo esc_attr( $lng ); ?>">
	<!-- Hidden input to store location data -->
	<input type="hidden" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $value ); ?>" />
	
	<!-- Address input field -->
	<div class="giftflow-googlemap-address">
		<input type="text" class="giftflow-googlemap-address-input" placeholder="<?php esc_attr_e( 'Enter an address', 'giftflow' ); ?>" value="<?php echo esc_attr( $address ); ?>" />
		<button type="button" class="button giftflow-googlemap-search"><?php esc_html_e( 'Search', 'giftflow' ); ?></button>
	</div>
	
	<!-- Map container -->
	<div class="giftflow-googlemap-container" style="height: 300px; margin-top: 10px;"></div>
	
	<!-- Coordinates display -->
	<div class="giftflow-googlemap-coordinates">
		<p>
			<strong><?php esc_html_e( 'Latitude:', 'giftflow' ); ?></strong> <span class="giftflow-googlemap-lat"><?php echo esc_html( $lat ); ?></span>
			<strong><?php esc_html_e( 'Longitude:', 'giftflow' ); ?></strong> <span class="giftflow-googlemap-lng"><?php echo esc_html( $lng ); ?></span>
		</p>
	</div>
</div><!-- End Google Maps field -->
