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

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$donation_id    = $donation->ID;
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$payment_method = $donation->payment_method ?? '';
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$payment_method_label = $donation->payment_method_label ?? ucfirst( str_replace( '_', ' ', $payment_method ) );
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$donation_date  = $donation->__date_gmt ?? $donation->__date ?? '';
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$campaign_name  = $donation->campaign_name ?? '';
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$campaign_url   = $donation->campaign_url ?? '#';
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$donation_type  = $donation->donation_type ?? '';

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$amount         = $donation->__amount_formatted ?? '';
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$d_status       = $donation->status ?? '';
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$status_class   = 'status-' . esc_attr( strtolower( (string) $d_status ) );
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$status_label   = $d_status ? ucfirst( (string) $d_status ) : '—';
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$back_url       = giftflow_donor_account_page_url( 'donations' );

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$detail_data = array(
	'donor_name'           => $donation->donor_name ?? '',
	'donor_email'          => $donation->donor_email ?? '',
	'message'              => trim( $donation->message ?? '' ),
	'anonymous'            => $donation->anonymous ?? '',
	'payment_method_label' => $payment_method_label,
	'payment_status'       => $d_status,
	'date'                 => $donation_date,
);

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$detail_data = apply_filters( 'giftflow_donation_detail_data', $detail_data, $donation );

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$detail_rows = array(
	array(
		'label'  => __( 'Donor name', 'giftflow' ),
		'value'  => 'donor_name',
		'format' => 'text',
	),
	array(
		'label'    => __( 'Email', 'giftflow' ),
		'value'    => 'donor_email',
		'format'   => 'text',
		'td_class' => 'gfw-donation-detail-email',
	),
	array(
		'label'    => __( 'Message', 'giftflow' ),
		'value'    => 'message',
		'format'   => 'text',
		'td_class' => 'gfw-donation-detail-message',
	),
	array(
		'label'  => __( 'Anonymous', 'giftflow' ),
		'value'  => 'anonymous',
		'format' => 'yesno',
	),
	array(
		'label'  => __( 'Payment method', 'giftflow' ),
		'value'  => 'payment_method_label',
		'format' => 'text',
	),
	array(
		'label'  => __( 'Payment status', 'giftflow' ),
		'value'  => 'payment_status',
		'format' => 'status',
	),
	array(
		'label'    => __( 'Date', 'giftflow' ),
		'value'    => 'date',
		'format'   => 'text',
		'td_class' => 'gfw-donation-detail-date',
	),
);

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$detail_rows = apply_filters( 'giftflow_donation_detail_table_rows', $detail_rows, $donation );
?>

<div class="gfw-donation-detail-page">

	<nav class="gfw-donation-detail-nav" aria-label="<?php esc_attr_e( 'Donation navigation', 'giftflow' ); ?>">
		<a class="gfw-donation-detail-back" href="<?php echo esc_url( $back_url ); ?>">
			<?php echo wp_kses( giftflow_svg_icon( 'prev' ), giftflow_allowed_svg_tags() ); ?>
			<span><?php esc_html_e( 'Back to donations', 'giftflow' ); ?></span>
		</a>
	</nav>

	<article class="gfw-donation-detail-hero" aria-labelledby="gfw-donation-detail-heading">
		<header class="gfw-donation-detail-hero__head">
			<h1 id="gfw-donation-detail-heading" class="gfw-donation-detail-hero__title">
				<?php esc_html_e( 'Donation', 'giftflow' ); ?>
				<span class="gfw-donation-detail-hero__id">#<?php echo esc_html( (string) $donation_id ); ?></span>
			</h1>
			<span class="donation-status <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_label ); ?></span>
		</header>

		<?php if ( $campaign_name ) : ?>
		<div class="gfw-donation-detail-hero__campaign">
			<span class="gfw-donation-detail-hero__campaign-label"><?php esc_html_e( 'Campaign', 'giftflow' ); ?></span>
			<div class="gfw-donation-detail-hero__campaign-line">
				<?php if ( $campaign_url && '#' !== $campaign_url ) : ?>
				<a class="gfw-donation-detail-hero__campaign-link" href="<?php echo esc_url( $campaign_url ); ?>" target="_blank" rel="noopener noreferrer">
					<span><?php echo esc_html( $campaign_name ); ?></span>
					<?php echo wp_kses( giftflow_svg_icon( 'arrow-up-right' ), giftflow_allowed_svg_tags() ); ?>
				</a>
				<?php else : ?>
				<span class="gfw-donation-detail-hero__campaign-name"><?php echo esc_html( $campaign_name ); ?></span>
				<?php endif; ?>
			</div>
		</div>
		<?php endif; ?>

		<div class="gfw-donation-detail-hero__amount-block">
			<span class="screen-reader-text"><?php esc_html_e( 'Amount', 'giftflow' ); ?></span>
			<p class="gfw-donation-detail-hero__amount"><?php echo wp_kses_post( $amount ); ?></p>
		</div>

		<?php if ( $donation_date || $payment_method_label || $donation_type ) : ?>
		<div class="gfw-donation-detail-hero__meta" role="group" aria-label="<?php esc_attr_e( 'Summary', 'giftflow' ); ?>">
			<?php if ( $donation_date ) : ?>
			<span class="gfw-donation-detail-hero__meta-item">
				<span class="gfw-donation-detail-hero__meta-key"><?php esc_html_e( 'Date', 'giftflow' ); ?></span>
				<time class="gfw-donation-detail-hero__meta-val" datetime="<?php echo esc_attr( $donation->__date_gmt ?? '' ); ?>"><?php echo esc_html( $donation_date ); ?></time>
			</span>
			<?php endif; ?>
			<?php if ( $payment_method_label ) : ?>
			<span class="gfw-donation-detail-hero__meta-item">
				<span class="gfw-donation-detail-hero__meta-key"><?php esc_html_e( 'Payment', 'giftflow' ); ?></span>
				<span class="gfw-donation-detail-hero__meta-val"><?php echo esc_html( $payment_method_label ); ?></span>
			</span>
			<?php endif; ?>
			<?php if ( $donation_type ) : ?>
			<span class="gfw-donation-detail-hero__meta-item">
				<span class="gfw-donation-detail-hero__meta-key"><?php esc_html_e( 'Type', 'giftflow' ); ?></span>
				<span class="gfw-donation-detail-hero__meta-val gfw-donation-detail-hero__type"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $donation_type ) ) ); ?></span>
			</span>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</article>

	<?php do_action( 'giftflow_donor_account_before_donation_detail_table', $donation ); ?>

	<section class="gfw-donation-detail-section" aria-labelledby="gfw-donation-details-heading">
		<h2 id="gfw-donation-details-heading" class="gfw-donation-detail-section__title"><?php esc_html_e( 'Full details', 'giftflow' ); ?></h2>
		<div class="gfw-donation-detail-card">
			<dl class="gfw-donation-detail-fields">
				<?php
				foreach ( $detail_rows as $row ) :
					$key    = $row['value'] ?? '';
					$val    = isset( $detail_data[ $key ] ) ? $detail_data[ $key ] : '';
					if ( isset( $row['show_if_empty'] ) && false === $row['show_if_empty'] && '' === (string) $val ) {
						continue;
					}
					$format    = $row['format'] ?? 'text';
					$dd_class  = 'gfw-donation-detail-field__value' . ( ! empty( $row['td_class'] ) ? ' ' . $row['td_class'] : '' );
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
				<div class="gfw-donation-detail-field">
					<dt class="gfw-donation-detail-field__label"><?php echo esc_html( $row['label'] ); ?></dt>
					<dd class="<?php echo esc_attr( $dd_class ); ?>">
						<?php
						if ( 'html' === $format || 'status' === $format ) {
							echo wp_kses_post( $display );
						} else {
							echo esc_html( is_string( $display ) ? $display : (string) $display );
						}
						?>
					</dd>
				</div>
				<?php endforeach; ?>
			</dl>
		</div>
	</section>

	<?php do_action( 'giftflow_donor_account_after_donation_detail_table', $donation ); ?>

	<footer class="gfw-donation-detail-actions">
		<a class="gfw-donation-detail-action gfw-donation-detail-action--ghost" href="<?php echo esc_url( $back_url ); ?>">
			<?php echo wp_kses( giftflow_svg_icon( 'prev' ), giftflow_allowed_svg_tags() ); ?>
			<span><?php esc_html_e( 'Back to donations', 'giftflow' ); ?></span>
		</a>
		<?php if ( $campaign_url && '#' !== $campaign_url ) : ?>
		<a class="gfw-donation-detail-action gfw-donation-detail-action--primary" href="<?php echo esc_url( $campaign_url ); ?>" target="_blank" rel="noopener noreferrer">
			<span><?php esc_html_e( 'View campaign', 'giftflow' ); ?></span>
			<?php echo wp_kses( giftflow_svg_icon( 'arrow-up-right' ), giftflow_allowed_svg_tags() ); ?>
		</a>
		<?php endif; ?>
	</footer>

</div>
