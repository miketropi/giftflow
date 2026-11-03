<?php
/**
 * Render callback for the Donation Button block.
 *
 * @package GiftFlow
 * @subpackage Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gf_campaign_id = (int) ( $attributes['campaignId'] ?? 0 );

if ( 0 === $gf_campaign_id && isset( $block->context['postId'] ) ) {
	$gf_campaign_id = (int) $block->context['postId'];
}

if ( 0 === $gf_campaign_id ) {
	$gf_campaign_id = get_the_ID();
}

$gf_button_text   = $attributes['buttonText'] ?? __( 'Donate Now', 'giftflow' );
$gf_button_style  = $attributes['buttonStyle'] ?? 'filled';
$gf_hover_effect  = $attributes['hoverEffect'] ?? 'lift';
$gf_hover_bg      = $attributes['hoverBgColor'] ?? '';
$gf_hover_fg      = $attributes['hoverTextColor'] ?? '';
$gf_icon          = $attributes['icon'] ?? 'none';
$gf_icon_pos      = $attributes['iconPosition'] ?? 'before';
$gf_full_width    = ! empty( $attributes['fullWidth'] );
$gf_bg            = $attributes['backgroundColor'] ?? '#1e1e1e';
$gf_fg            = $attributes['textColor'] ?? '#ffffff';
$gf_padding       = $attributes['buttonPadding'] ?? '14px 32px';
$gf_radius         = isset( $attributes['borderRadius'] ) ? (int) $attributes['borderRadius'] : 8;

$gf_is_outline    = 'outline' === $gf_button_style;
$gf_is_soft       = 'soft' === $gf_button_style;
$gf_is_pill       = 'pill' === $gf_button_style;

$gf_campaign_status = get_post_status( $gf_campaign_id );
$gf_is_published    = 'publish' === $gf_campaign_status;
$gf_is_disabled     = ! $gf_is_published || 0 === $gf_campaign_id;

$gf_wrapper_classes = array( 'giftflow-donation-button' );
$gf_button_classes  = array( 'giftflow-donation-button__btn' );

if ( $gf_full_width ) {
	$gf_button_classes[] = 'giftflow-donation-button__btn--full-width';
}
if ( $gf_is_disabled ) {
	$gf_button_classes[] = 'giftflow-donation-button__btn--disabled';
}
if ( $gf_hover_bg || $gf_hover_fg ) {
	$gf_button_classes[] = 'giftflow-donation-button__btn--has-hover-color';
}

// Build CSS custom properties — never inline style, always via variables so hover cascade works.
$gf_vars = array();

if ( $gf_is_outline ) {
	$gf_vars[] = '--gf-btn-bg:transparent';
	$gf_vars[] = '--gf-btn-fg:' . esc_attr( $gf_bg );
	$gf_vars[] = '--gf-btn-border:2px solid ' . esc_attr( $gf_bg );
} elseif ( $gf_is_soft ) {
	$gf_vars[] = '--gf-btn-bg:transparent';
	$gf_vars[] = '--gf-btn-fg:' . esc_attr( $gf_bg );
	$gf_vars[] = '--gf-btn-border:2px solid transparent';
	$gf_vars[] = '--gf-btn-bg-image:linear-gradient(' . esc_attr( $gf_bg ) . '0d,' . esc_attr( $gf_bg ) . '14)';
} else {
	$gf_vars[] = '--gf-btn-bg:' . esc_attr( $gf_bg );
	$gf_vars[] = '--gf-btn-fg:' . esc_attr( $gf_fg );
	$gf_vars[] = '--gf-btn-border:none';
}

if ( $gf_is_pill ) {
	$gf_vars[] = '--gf-btn-radius:999px';
} else {
	$gf_vars[] = '--gf-btn-radius:' . $gf_radius . 'px';
}

// Padding from custom attribute.
if ( $gf_padding ) {
	$gf_vars[] = '--gf-btn-padding:' . esc_attr( $gf_padding );
}

// Hover color variables.
if ( $gf_hover_bg ) {
	$gf_vars[] = '--gf-hover-bg:' . esc_attr( $gf_hover_bg );
}
if ( $gf_hover_fg ) {
	$gf_vars[] = '--gf-hover-fg:' . esc_attr( $gf_hover_fg );
}

// Hover effect.
if ( in_array( $gf_hover_effect, array( 'lift', 'scale', 'glow' ), true ) ) {
	$gf_button_classes[] = 'giftflow-donation-button__btn--hover-' . esc_attr( $gf_hover_effect );
}

// Icons.
$gf_icons_svg = array(
	'heart'        => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>',
	'sparkle'      => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>',
	'gift'         => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13"/><path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"/></svg>',
	'arrow'        => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>',
	'ribbon-heart' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>',
);

$gf_icon_html = '';
if ( isset( $gf_icons_svg[ $gf_icon ] ) ) {
	$gf_icon_html = $gf_icons_svg[ $gf_icon ];
}

// Anchor by default: the campaign permalink stays reachable (new tab, middle-click,
// no-JS, crawlers) while the inline handler keeps opening the donation modal.
$gf_button_tag = $gf_is_disabled ? 'span' : 'a';

$gf_button_attrs = array(
	'class'               => implode( ' ', $gf_button_classes ),
	'style'               => implode( ';', $gf_vars ),
	'data-campaign-id'    => $gf_campaign_id,
	'data-campaign-title' => get_the_title( $gf_campaign_id ),
);

if ( $gf_is_disabled ) {
	// Nothing to link to — inert markup instead of a disabled control.
	$gf_button_attrs['aria-disabled'] = 'true';
} else {
	$gf_button_attrs['href']    = (string) get_permalink( $gf_campaign_id );
	$gf_button_attrs['onclick'] = 'if(window.giftflow&&window.giftflow.donationButton_Handle){window.giftflow.donationButton_Handle(this);return false;}';
}

$block_wrapper_attrs = get_block_wrapper_attributes(
	array( 'class' => implode( ' ', $gf_wrapper_classes ) )
);
?>
<div <?php echo $block_wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<<?php echo esc_html( $gf_button_tag ); ?> <?php echo giftflow_render_attributes( $gf_button_attrs ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- attribute values are escaped by the helper. ?>>
		<?php if ( 'before' === $gf_icon_pos && $gf_icon_html ) : ?>
			<span class="giftflow-donation-button__icon"><?php echo $gf_icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<?php endif; ?>
		<span class="giftflow-donation-button__label"><?php echo esc_html( $gf_button_text ); ?></span>
		<?php if ( 'after' === $gf_icon_pos && $gf_icon_html ) : ?>
			<span class="giftflow-donation-button__icon"><?php echo $gf_icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<?php endif; ?>
	</<?php echo esc_html( $gf_button_tag ); ?>>
</div>
