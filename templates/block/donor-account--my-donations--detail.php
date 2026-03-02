<?php
/**
 * Template for single donation detail (donor account).
 *
 * @package GiftFlow
 * @since 1.0.0
 *
 * Variables: $donation (object from giftflow_get_donation_data_by_id), $current_user, $id.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$donation_id    = $donation->ID;
$payment_method = $donation->payment_method ?? '';
$payment_method_label = $donation->payment_method_label ?? ucfirst( str_replace( '_', ' ', $payment_method ) );
$donation_date  = $donation->__date_gmt ?? $donation->__date ?? '';
$campaign_name  = $donation->campaign_name ?? '';
$campaign_url   = $donation->campaign_url ?? '#';
$donation_type  = $donation->donation_type ?? '';

$amount       = $donation->__amount_formatted ?? '';
$d_status       = $donation->status ?? '';
$status_class = 'status-' . esc_attr( strtolower( $d_status ) );
$status_label = $d_status ? ucfirst( $d_status ) : '—';
$back_url     = giftflow_donor_account_page_url( 'donations' );

// Single-row data for the details table. Keys match row definitions below.
$detail_data = array(
	'donor_name'    => $donation->donor_name ?? '',
	'donor_email'   => $donation->donor_email ?? '',
	'message'       => $donation->message ?? '',
	'anonymous'     => $donation->anonymous ?? '',
	'payment_method_label' => $payment_method_label,
	'payment_status' => $d_status,
	'date'          => $donation_date,
);

$detail_data = apply_filters( 'giftflow_donation_detail_data', $detail_data, $donation );

/**
 * Table rows.
 *
 * @var array $detail_rows Table rows.
 */
$detail_rows = array(
	array(
		'label' => __( 'Donor name', 'giftflow' ),
		'value' => 'donor_name',
		'format' => 'text',
	),
	array(
		'label' => __( 'Email', 'giftflow' ),
		'value' => 'donor_email',
		'format' => 'text',
		'td_class' => 'gfw-donation-detail-email',
	),
	array(
		'label' => __( 'Message', 'giftflow' ),
		'value' => 'message',
		'format' => 'text',
		'td_class' => 'gfw-donation-detail-message',
	),
	array(
		'label' => __( 'Anonymous', 'giftflow' ),
		'value' => 'anonymous',
		'format' => 'yesno',
	),
	array(
		'label' => __( 'Payment method', 'giftflow' ),
		'value' => 'payment_method_label',
		'format' => 'text',
	),
	array(
		'label' => __( 'Payment status', 'giftflow' ),
		'value' => 'payment_status',
		'format' => 'status',
	),
	array(
		'label' => __( 'Date', 'giftflow' ),
		'value' => 'date',
		'format' => 'text',
		'td_class' => 'gfw-donation-detail-date',
	),
);
$detail_rows = apply_filters( 'giftflow_donation_detail_table_rows', $detail_rows, $donation );
?>

<div class="gfw-donation-detail-page">

	<nav class="gfw-donation-detail-nav" aria-label="<?php esc_attr_e( 'Breadcrumb', 'giftflow' ); ?>">
		<a class="gfw-donation-detail-back" href="<?php echo esc_url( $back_url ); ?>">
			<?php echo wp_kses( giftflow_svg_icon( 'prev' ), giftflow_allowed_svg_tags() ); ?>
			<?php esc_html_e( 'Back to Donations', 'giftflow' ); ?>
		</a>
	</nav>

	<header class="gfw-donation-detail-header">
		<h1 class="gfw-donation-detail-title">
			<?php esc_html_e( 'Donation', 'giftflow' ); ?>
			<span class="gfw-donation-detail-id">#<?php echo esc_html( (string) $donation_id ); ?></span>
		</h1>
		<?php if ( $campaign_name ) : ?>
		<p class="gfw-donation-detail-campaign">
			<?php esc_html_e( 'Campaign:', 'giftflow' ); ?>
			<?php if ( $campaign_url && '#' !== $campaign_url ) : ?>
				<a class="gfw-donation-detail-campaign-link" href="<?php echo esc_url( $campaign_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $campaign_name ); ?></a>
			<?php else : ?>
				<span><?php echo esc_html( $campaign_name ); ?></span>
			<?php endif; ?>
		</p>
		<?php endif; ?>
		<p class="gfw-donation-detail-meta">
			<time class="gfw-donation-detail-date" datetime="<?php echo esc_attr( $donation->__date_gmt ?? '' ); ?>"><?php echo esc_html( $donation_date ); ?></time>
		</p>
	</header>

	<section class="gfw-donation-detail-summary" aria-label="<?php esc_attr_e( 'Donation summary', 'giftflow' ); ?>">
		<div class="gfw-donation-detail-amount-row">
			<span class="gfw-donation-detail-amount"><?php echo wp_kses_post( $amount ); ?></span>
			<span class="gfw-donation-detail-status donation-status <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_label ); ?></span>
		</div>
		<?php if ( $payment_method_label || $donation_date ) : ?>
		<div class="gfw-donation-detail-summary-meta">
			<?php if ( $payment_method_label ) : ?>
				<span><?php echo esc_html( $payment_method_label ); ?></span>
			<?php endif; ?>
			<?php if ( $donation_type ) : ?>
				<span class="gfw-donation-detail-type"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $donation_type ) ) ); ?></span>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</section>

	<?php
	/**
	 * Action hook: giftflow_donor_account_before_donation_detail_table
	 *
	 * Allows other plugins or themes to add custom content before the donation detail table.
	 *
	 * @param array $donation The donation object/array.
	 */
	do_action( 'giftflow_donor_account_before_donation_detail_table', $donation );
	?>

	<section class="gfw-donation-detail-section" aria-labelledby="gfw-donation-details-heading">
		<h2 id="gfw-donation-details-heading" class="gfw-donation-detail-section-title"><?php esc_html_e( 'Donation details', 'giftflow' ); ?></h2>
		<table class="gfw-donation-detail-table giftflow-table">
			<tbody>
				<?php foreach ( $detail_rows as $row ) : ?>
					<?php
					$key  = $row['value'] ?? '';
					$val  = isset( $detail_data[ $key ] ) ? $detail_data[ $key ] : '';
					if ( isset( $row['show_if_empty'] ) && false === $row['show_if_empty'] && '' === (string) $val ) {
						continue;
					}
					$format   = $row['format'] ?? 'text';
					$td_class = 'gfw-donation-detail-value' . ( ! empty( $row['td_class'] ) ? ' ' . $row['td_class'] : '' );
					if ( 'yesno' === $format ) {
						$display = $val ? __( 'Yes', 'giftflow' ) : __( 'No', 'giftflow' );
					} elseif ( 'html' === $format ) {
						$display = wp_kses_post( $val );
					} elseif ( 'status' === $format ) {
						$display = '' !== $val && null !== $val
						? '<span class="donation-status status-' . esc_attr( strtolower( (string) $val ) ) . '">' . esc_html( ucfirst( (string) $val ) ) . '</span>'
						: '—';
					} else {
						$display = '' !== $val && null !== $val ? $val : '—';
					}
					?>
				<tr>
					<th scope="row" class="gfw-donation-detail-label"><?php echo esc_html( $row['label'] ); ?></th>
					<td class="<?php echo esc_attr( $td_class ); ?>"><?php echo ( 'html' === $format || 'status' === $format ) ? wp_kses_post( $display ) : esc_html( $display ); ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</section>

	<?php
	/**
	 * Action hook: giftflow_donor_account_after_donation_detail_table
	 *
	 * Allows other plugins or themes to add custom content after the donation detail table.
	 *
	 * @param array $donation The donation object/array.
	 */
	do_action( 'giftflow_donor_account_after_donation_detail_table', $donation );
	?>

	<footer class="gfw-donation-detail-actions">
		<a class="gfw-donation-detail-btn gfw-donation-detail-btn-back" href="<?php echo esc_url( $back_url ); ?>">
			<?php echo wp_kses( giftflow_svg_icon( 'prev' ), giftflow_allowed_svg_tags() ); ?>
			<?php esc_html_e( 'Back to Donations', 'giftflow' ); ?>
		</a>
		<?php if ( $campaign_url && '#' !== $campaign_url ) : ?>
		<a class="gfw-donation-detail-btn gfw-donation-detail-btn-primary" href="<?php echo esc_url( $campaign_url ); ?>" target="_blank" rel="noopener noreferrer">
			<?php esc_html_e( 'View campaign', 'giftflow' ); ?>
			<?php echo wp_kses( giftflow_svg_icon( 'next' ), giftflow_allowed_svg_tags() ); ?>
		</a>
		<?php endif; ?>
	</footer>

</div>
