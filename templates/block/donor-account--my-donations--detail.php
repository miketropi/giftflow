<?php
/**
 * Template for my donations detail
 *
 * @package GiftFlow
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Extract donation data.
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$donation_id = $donation->ID;
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$donation_title = $donation->post_title;
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$donor_name = $donation->donor_name ?? '';
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$donor_email = $donation->donor_email ?? '';
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$amount = $donation->__amount_formatted ?? '$0.00';
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound, WordPress.WP.GlobalVariablesOverride.Prohibited
$status = $donation->status ?? '';
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$payment_method = $donation->payment_method ?? '';
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$donation_date = $donation->__date_gmt ?? '';
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$campaign_name = $donation->campaign_name ?? '';
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$campaign_url = $donation->campaign_url ?? '';
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$edit_url = $donation->donation_edit_url ?? '';
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$message = $donation->message ?? '';
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$anonymous = ucfirst( $donation->anonymous ?? '' );

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$donation_type = $donation->donation_type ?? '';

// Status styling.
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$status_class = 'gfw-status-' . strtolower( $status );
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$status_label = ucfirst( $status );

// Payment method styling.
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$payment_method_label = ucfirst( str_replace( '_', ' ', $payment_method ) );
?>

<div class="gfw-donation-detail-actions">
	<a 
	href="<?php echo esc_url( giftflow_donor_account_page_url( 'donations' ) ); ?>" 
	class="gfw-view-detail-link"
	>
	&larr; <?php esc_html_e( 'Back to Donations', 'giftflow' ); ?>
	</a>
</div>

<div class="gfw-my-donations-detail">

	<!-- Header -->
	<div class="gfw-donation-detail-header">
	<div class="gfw-donation-detail-campaign gfw-donation-detail-title">
		Campaign:
		<a href="<?php echo esc_url( $campaign_url ); ?>" class="gfw-campaign-link" target="_blank" rel="noopener">
		<?php echo esc_html( $campaign_name ); ?>
		</a>
	</div>
	<div class="gfw-donation-detail-meta">
		<span class="gfw-donation-id">#<?php echo esc_html( $donation_id ); ?></span>
		<span class="gfw-donation-date"><?php echo esc_html( $donation_date ); ?></span>
	</div>
	</div>

	<!-- Donation Information -->
	<div class="gfw-donation-detail-content">
	
	<!-- Amount & Status -->
	<div class="gfw-donation-detail-amount-section">
		<div class="gfw-amount-display">
		<span class="gfw-amount"><?php echo wp_kses_post( $amount ); ?></span>
		<span class="donation-status status-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $status_label ); ?></span>
		</div>
	</div>

	<table class="gfw-donation-detail-table">
		<tbody>
		<tr>
			<th colspan="2">Donation Details</th>
		</tr>
		<tr>
			<td class="gfw-detail-label">Name</td>
			<td class="gfw-detail-value"><?php echo esc_html( $donor_name ); ?></td>
		</tr>
		<tr>
			<td class="gfw-detail-label">Email</td>
			<td class="gfw-detail-value" style="font-family: monospace;"><?php echo esc_html( $donor_email ); ?></td>
		</tr>
		<tr>
			<td class="gfw-detail-label">Message</td>
			<td class="gfw-detail-value" style="font-family: monospace;"><?php echo esc_html( $message ); ?></td>
		</tr>
		<tr>
			<td class="gfw-detail-label">Anonymous</td>
			<td class="gfw-detail-value" style="font-family: monospace;"><?php echo esc_html( $anonymous ); ?></td>
		</tr>
		<tr>
			<td class="gfw-detail-label">Payment Method</td>
			<td class="gfw-detail-value"><?php echo esc_html( $payment_method_label ); ?> (<?php echo esc_html( $donation_type ?? '__unknown__' ); ?>)</td>
		</tr>
		<tr>
			<td class="gfw-detail-label">Date</td>
			<td class="gfw-detail-value gfw-donation-date"><?php echo esc_html( $donation_date ); ?></td>
		</tr>
		</tbody>
	</table>
	</div>

	<!-- Actions -->
	<div class="gfw-donation-detail-actions">
	<a href="<?php echo esc_url( wp_get_referer() ? wp_get_referer() : home_url( '/donor-account' ) ); ?>" class="gfw-view-detail-link">
		<?php echo wp_kses( giftflow_svg_icon( 'prev' ), giftflow_allowed_svg_tags() ); ?>
		<?php esc_html_e( 'Back to Donations', 'giftflow' ); ?>
	</a>
	<?php if ( ! empty( $campaign_url ) ) : ?>
	<a href="<?php echo esc_url( $campaign_url ); ?>" class="gfw-view-detail-link" target="_blank">
		<?php esc_html_e( 'View Campaign', 'giftflow' ); ?>
		<?php echo wp_kses( giftflow_svg_icon( 'next' ), giftflow_allowed_svg_tags() ); ?>
	</a>
	<?php endif; ?>
	</div>
</div>