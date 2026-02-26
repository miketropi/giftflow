<?php
/**
 * Template for my donations
 *
 * @package GiftFlow
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$donations = $donations ?? null;
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound, WordPress.WP.GlobalVariablesOverride.Prohibited
$page = $page ?? 1;

// Cache process bar by campaign_id.
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$cache_process_bar = array();

// Build table rows from donation query using giftflow_get_donation_data_by_id().
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$donations_data = array();
if ( $donations instanceof WP_Query && $donations->have_posts() ) {
	while ( $donations->have_posts() ) {
		$donations->the_post();
		$donation_id = get_the_ID();
		$d           = giftflow_get_donation_data_by_id( $donation_id );
		if ( ! $d ) {
			continue;
		}
		$campaign_id = get_post_meta( $donation_id, '_campaign_id', true );
		if ( ! isset( $cache_process_bar[ $campaign_id ] ) && $campaign_id ) {
			ob_start();
			giftflow_process_bar_of_campaign_donations( $campaign_id );
			$cache_process_bar[ $campaign_id ] = ob_get_clean();
		}
		$donations_data[] = array(
			'donation_id'       => $donation_id,
			'campaign_id'       => $campaign_id,
			'campaign_title'    => $d->campaign_name,
			'campaign_url'      => $d->campaign_url,
			'amount_formatted'  => $d->__amount_formatted,
			'payment_status'    => $d->status,
			'payment_method'    => $d->payment_method,
			// donation type.
			'donation_type'     => $d->donation_type,
			// template for payment method + donation type.
			'payment_template'  => "<div class='gfw-payment-method gfw-payment-method-" . esc_attr( $d->payment_method_label ) . "' title='" . esc_attr__( 'Payment Method', 'giftflow' ) . "'>" . esc_html( ucfirst( $d->payment_method_label ) ) . "</div> <div class='gfw-donation-type gfw-tag-status status-closed gfw-donation-type-" . esc_attr( $d->donation_type ) . "' title='" . esc_attr__( 'Donation Type', 'giftflow' ) . "'>" . esc_html( ucfirst( $d->donation_type ) ) . '</div>',
			'date'              => $d->__date,
			'date_gmt'          => $d->__date_gmt,
			'date_ago'          => '<span class="gfw-donation-date-ago" title="' . esc_attr( $d->__date_gmt ) . '">' . giftflow_render_time_ago( $d->__date_gmt ) . '</span>',
			'detail_url'        => giftflow_donor_account_page_url( 'donations?_id=' . $donation_id ),
			'process_bar_html'  => isset( $cache_process_bar[ $campaign_id ] ) ? $cache_process_bar[ $campaign_id ] : '',
			'sub_donations'     => giftflow_get_donations_by_parent_id( $donation_id ),
		);
	}
	wp_reset_postdata();
}
$donations_data = apply_filters( 'giftflow_my_donations_table_data', $donations_data, $page );
?>
<div class="gfw-my-donations-header">
	<h2 class="gfw-donor-account__title"><?php esc_html_e( 'My Donations', 'giftflow' ); ?></h2>
	<p class="gfw-my-donations-desc">
	<?php esc_html_e( 'Here you can view a detailed record of your recent donations. Each contribution helps us create lasting impact and drive positive change in our community.', 'giftflow' ); ?>
	</p>
</div>
<?php
/*
 * Table format: array of columns. Each column is an array with:
 *   label  (string)  Header text for the column.
 *   value  (string)  Key in each row to use for this column (see $donations_data keys).
 *   format (string)  How to render: 'text' | 'html' | 'id' | 'campaign' | 'date' | 'status' | 'detail'. Default 'text'.
 *   width  (string)  Optional. e.g. '40%' for <th width="40%">.
 *   th_attrs (array) Optional. Extra attributes for <th>.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$table_columns = array(
	array(
		'label' => '',
		'value' => 'donation_id',
		'format' => 'id',
	),
	array(
		'label' => __( 'Campaign', 'giftflow' ),
		'value' => 'campaign_title',
		'format' => 'campaign',
		'width' => '30%',
	),
	array(
		'label' => __( 'Amount', 'giftflow' ),
		'value' => 'amount_formatted',
		'format' => 'html',
	),

	// payment type.
	array(
		'label' => __( 'Payment', 'giftflow' ),
		'value' => 'payment_template',
		'format' => 'html',
	),

	array(
		'label' => __( 'Date', 'giftflow' ),
		'value' => 'date_ago',
		'format' => 'html',
	),
	array(
		'label' => __( 'Status', 'giftflow' ),
		'value' => 'payment_status',
		'format' => 'status',
	),
	array(
		'label' => '',
		'value' => 'detail_url',
		'format' => 'detail',
	),
);
$table_columns = apply_filters( 'giftflow_my_donations_table_columns', $table_columns );

if ( ! empty( $donations_data ) ) :
	?>
	<?php
	// Action hook: giftflow_my_donations_table_before.
	// @since 1.0.0.
	do_action( 'giftflow_my_donations_table_before', $current_user, $donations, $page );
	?>
	<div class="gfw-my-donations-table-container">
		<table class="giftflow-table gfw-my-donations-table" style="width: 100%;">
			<thead>
				<tr>
					<?php foreach ( $table_columns as $col ) : ?>
						<?php
						$th_attrs = $col['th_attrs'] ?? array();
						if ( ! empty( $col['width'] ) ) {
							$th_attrs['width'] = $col['width'];
						}
						?>
					<th 
						<?php
						foreach ( $th_attrs as $attr => $val ) {
							echo esc_attr( $attr ) . '="' . esc_attr( $val ) . '" ';
						}
						?>
					><?php echo esc_html( $col['label'] ?? '' ); ?></th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $donations_data as $row ) : ?>
				<tr class="gfw-my-donations-row">
					<?php foreach ( $table_columns as $col ) : ?>
					<td>
						<?php
						$key    = $col['value'] ?? $col['key'] ?? '';
						$format = $col['format'] ?? $col['cell'] ?? 'text';
						$val    = isset( $row[ $key ] ) ? $row[ $key ] : '';
						switch ( $format ) {
							case 'id':
								echo '<span style="font-family: monospace;">#' . esc_html( (string) $val ) . '</span>';
								break;
							case 'campaign':
								if ( ! empty( $row['campaign_id'] ) ) {
									echo wp_kses_post( $row['process_bar_html'] ?? '' );
									// phpcs:ignore Generic.Strings.UnnecessaryStringConcat.Found
									echo '<b>' . esc_html( $row['campaign_title'] ?? '' ) . '</b>' . '<a class="gfw-campaign-title-link" href="' . esc_url( $row['campaign_url'] ?? '' ) . '" target="_blank">' . wp_kses( giftflow_svg_icon( 'arrow-up-right' ), giftflow_allowed_svg_tags() ) . '</a>';
								} else {
									echo esc_html( $row['campaign_title'] ?? '' );
								}
								break;
							case 'html':
								echo wp_kses_post( $val );
								break;
							case 'date':
								echo '<span class="gfw-donation-date">' . esc_html( $val ) . '</span>';
								break;
							case 'status':
								echo '<span class="donation-status status-' . esc_attr( $val ) . '">' . esc_html( ucfirst( $val ) ) . '</span>';
								break;
							case 'detail':
								echo '<a class="gfw-view-detail-link" href="' . esc_url( $val ) . '" style="white-space: nowrap;">';
								echo esc_html__( 'Detail →', 'giftflow' );
								echo '</a>';
								break;
							default:
								echo esc_html( $val );
						}
						?>
					</td>
					<?php endforeach; ?>
				</tr>
					<?php
					// Sub-donations: toggle show/hide via <details>.
					$sub_donations = $row['sub_donations'] ?? array();
					if ( ! empty( $sub_donations ) && is_array( $sub_donations ) ) :
						$col_count = count( $table_columns );
						$sub_count = count( $sub_donations );
						?>
				<tr class="gfw-my-donations-sub-row">
					<td colspan="<?php echo (int) $col_count; ?>" class="gfw-my-donations-sub-cell">
						<details class="gfw-my-donations-sub-details">
							<summary class="gfw-my-donations-sub-summary">
								<span class="gfw-my-donations-sub-summary-text"><?php esc_html_e( 'Recurring payments', 'giftflow' ); ?></span>
							</summary>
							<ul class="gfw-my-donations-sub-list">
								<?php foreach ( $sub_donations as $sub_post ) : ?>
									<?php
									$sub_id = $sub_post->ID;
									$sub_d  = giftflow_get_donation_data_by_id( $sub_id );
									if ( ! $sub_d ) {
										continue;
									}
									$sub_detail_url = giftflow_donor_account_page_url( 'donations?_id=' . $sub_id );
									$sub_date_ago = '<span class="gfw-donation-date-ago" title="' . esc_attr( $sub_d->__date_gmt ) . '">' . giftflow_render_time_ago( $sub_d->__date_gmt ) . '</span>';
									?>
									<li class="gfw-my-donations-sub-item">
										<span class="gfw-my-donations-sub-id">#<?php echo esc_html( (string) $sub_id ); ?></span>
										<span class="gfw-my-donations-sub-amount"><?php echo wp_kses_post( $sub_d->__amount_formatted ?? '' ); ?></span>
										<span class="gfw-my-donations-sub-date"><?php echo wp_kses_post( $sub_date_ago ?? '—' ); ?></span>
										<span class="donation-status status-<?php echo esc_attr( $sub_d->status ?? '' ); ?>"><?php echo esc_html( ucfirst( $sub_d->status ?? '' ) ); ?></span>
										<a class="gfw-view-detail-link" href="<?php echo esc_url( $sub_detail_url ); ?>"><?php esc_html_e( 'Detail →', 'giftflow' ); ?></a>
									</li>
								<?php endforeach; ?>
							</ul>
						</details>
					</td>
				</tr>
					<?php endif; ?>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<?php
	// Comment: Pagination (if needed).
	if ( isset( $donations->max_num_pages ) && $donations->max_num_pages > 1 ) :
	  // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		$big = 999999999; // need an unlikely integer.
	  // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		$current_page = max( 1, $page );

		?>
	<div class="gfw-pagination">
		<?php
		echo wp_kses_post(
			paginate_links(
				array(
					'base'      => str_replace( $big, '%#%', esc_url( giftflow_donor_account_page_url( 'donations?_page=' . $big ) ) ),
					'format'    => '?_page=%#%',
					'current'   => $current_page,
					'total'     => $donations->max_num_pages,
					'prev_text' => esc_html__( 'Previous', 'giftflow' ),
					'next_text' => esc_html__( 'Next', 'giftflow' ),
				)
			)
		);
		?>
	</div>
	<?php endif; ?>
<?php else : ?>
	<div class="gfw-no-donations">
	<?php esc_html_e( 'You have not made any donations yet.', 'giftflow' ); ?>
	</div>
<?php endif; ?>