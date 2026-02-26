<?php
/**
 * Donations filter form (date, status, payment method).
 *
 * @package GiftFlow
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$form_action = giftflow_donor_account_page_url( 'donations' );

// use giftflow_sanitize_array.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$filters_raw = giftflow_sanitize_array( $_GET ?? array() );

// Current filter values from GET (param names used in form).
$filter_date_from = $filters_raw['_filter_date_from'] ?? '';
$filter_date_to   = $filters_raw['_filter_date_to'] ?? '';
$filter_status    = $filters_raw['_filter_status'] ?? '';
$filter_payment   = $filters_raw['_filter_payment_method'] ?? '';

$status_options   = giftflow_get_donation_status_options();
$payment_options  = giftflow_get_payment_methods_options();
$has_active_filter = '' !== $filter_date_from || '' !== $filter_date_to || '' !== $filter_status || '' !== $filter_payment;
?>

<form class="gfw-donations-filter-form" method="get" action="<?php echo esc_url( $form_action ); ?>" role="search" aria-label="<?php esc_attr_e( 'Filter donations', 'giftflow' ); ?>">
	<input type="hidden" name="_page" value="1" />

	<div class="gfw-donations-filter-form__fields">
		<div class="gfw-donations-filter-form__field gfw-donations-filter-form__field--date-from">
			<label for="gfw-filter-date-from" class="gfw-donations-filter-form__label"><?php esc_html_e( 'Date from', 'giftflow' ); ?></label>
			<input type="date" id="gfw-filter-date-from" name="_filter_date_from" class="gfw-donations-filter-form__input" value="<?php echo esc_attr( $filter_date_from ); ?>" />
		</div>

		<div class="gfw-donations-filter-form__field gfw-donations-filter-form__field--date-to">
			<label for="gfw-filter-date-to" class="gfw-donations-filter-form__label"><?php esc_html_e( 'Date to', 'giftflow' ); ?></label>
			<input type="date" id="gfw-filter-date-to" name="_filter_date_to" class="gfw-donations-filter-form__input" value="<?php echo esc_attr( $filter_date_to ); ?>" />
		</div>

		<div class="gfw-donations-filter-form__field gfw-donations-filter-form__field--status">
			<label for="gfw-filter-status" class="gfw-donations-filter-form__label"><?php esc_html_e( 'Status', 'giftflow' ); ?></label>
			<select id="gfw-filter-status" name="_filter_status" class="gfw-donations-filter-form__select">
				<option value=""><?php esc_html_e( 'All statuses', 'giftflow' ); ?></option>
				<?php foreach ( $status_options as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $filter_status, $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="gfw-donations-filter-form__field gfw-donations-filter-form__field--payment">
			<label for="gfw-filter-payment" class="gfw-donations-filter-form__label"><?php esc_html_e( 'Payment method', 'giftflow' ); ?></label>
			<select id="gfw-filter-payment" name="_filter_payment_method" class="gfw-donations-filter-form__select">
				<option value=""><?php esc_html_e( 'All methods', 'giftflow' ); ?></option>
				<?php foreach ( $payment_options as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $filter_payment, $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>

	<div class="gfw-donations-filter-form__actions">
		<button type="submit" class="gfw-donations-filter-form__submit">
		<?php echo wp_kses( giftflow_svg_icon( 'filter' ), giftflow_allowed_svg_tags() ); ?>
		<?php esc_html_e( 'Filter Donations', 'giftflow' ); ?>
	</button>
		<?php if ( $has_active_filter ) : ?>
			<a class="gfw-donations-filter-form__clear" href="<?php echo esc_url( $form_action ); ?>"><?php esc_html_e( 'Clear Filter', 'giftflow' ); ?></a>
		<?php endif; ?>
	</div>
</form>
