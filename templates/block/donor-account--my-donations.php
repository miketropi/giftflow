<?php
/**
 * Template for my donations (minimal list / cards — no table).
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

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$cache_process_bar = array();

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$donations_data = array();
if ( $donations instanceof WP_Query && $donations->have_posts() ) {
	while ( $donations->have_posts() ) {
		$donations->the_post();
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		$donation_id = get_the_ID();
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		$d           = giftflow_get_donation_data_by_id( $donation_id );
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		if ( ! $d ) {
			continue;
		}

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		$campaign_id = get_post_meta( $donation_id, '_campaign_id', true );
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		if ( ! isset( $cache_process_bar[ $campaign_id ] ) && $campaign_id ) {
			ob_start();
			giftflow_process_bar_of_campaign_donations( $campaign_id );
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
			$cache_process_bar[ $campaign_id ] = ob_get_clean();
		}
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		$donations_data[] = array(
			'donation_id'      => $donation_id,
			'campaign_id'      => $campaign_id,
			'campaign_title'   => $d->campaign_name,
			'campaign_url'     => $d->campaign_url,
			'amount_formatted' => $d->__amount_formatted,
			'payment_status'   => $d->status,
			'payment_method'   => $d->payment_method,
			'donation_type'    => $d->donation_type,
			'payment_template' => giftflow_donation_payment_template_tags( $d ),
			'date'             => $d->__date,
			'date_gmt'         => $d->__date_gmt,
			'date_ago'         => '<span class="gfw-donation-date-ago" title="' . esc_attr( $d->__date_gmt ) . '">' . giftflow_render_time_ago( $d->__date_gmt ) . '</span>',
			'detail_url'       => giftflow_donor_account_page_url( 'donations?_id=' . $donation_id ),
			'process_bar_html' => isset( $cache_process_bar[ $campaign_id ] ) ? $cache_process_bar[ $campaign_id ] : '',
			'sub_donations'    => giftflow_get_donations_by_parent_id( $donation_id ),
		);
	}
	wp_reset_postdata();
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$donations_data = apply_filters( 'giftflow_my_donations_table_data', $donations_data, $page );

/*
 * Legacy column config (still filterable). Card layout uses fixed fields; use
 * giftflow_my_donation_card_after_body to inject extra content per row.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$table_columns = apply_filters(
	'giftflow_my_donations_table_columns',
	array(
		array(
			'label'  => '',
			'value'  => 'donation_id',
			'format' => 'id',
		),
		array(
			'label'  => __( 'Campaign', 'giftflow' ),
			'value'  => 'campaign_title',
			'format' => 'campaign',
		),
		array(
			'label'  => __( 'Amount', 'giftflow' ),
			'value'  => 'amount_formatted',
			'format' => 'html',
		),
		array(
			'label'  => __( 'Payment', 'giftflow' ),
			'value'  => 'payment_template',
			'format' => 'html',
		),
		array(
			'label'  => __( 'Date', 'giftflow' ),
			'value'  => 'date_ago',
			'format' => 'html',
		),
		array(
			'label'  => __( 'Status', 'giftflow' ),
			'value'  => 'payment_status',
			'format' => 'status',
		),
		array(
			'label'  => '',
			'value'  => 'detail_url',
			'format' => 'detail',
		),
	)
);
/**
 * Fired after legacy column filter runs (card UI does not render columns).
 *
 * @param array $table_columns Column definitions.
 */
do_action( 'giftflow_my_donations_list_columns_resolved', $table_columns );

?>
<div class="gfw-my-donations">
	<header class="gfw-my-donations-header">
		<h2 class="gfw-donor-account__title gfw-my-donations-header__title"><?php esc_html_e( 'My Donations', 'giftflow' ); ?></h2>
		<p class="gfw-my-donations-header__intro">
			<?php esc_html_e( 'A concise history of your giving. Open any entry for the full receipt and breakdown.', 'giftflow' ); ?>
		</p>
	</header>
<?php if ( ! empty( $donations_data ) ) : ?>
	<?php do_action( 'giftflow_my_donations_table_before', $current_user, $donations, $page ); ?>
	<ul class="gfw-donations-list" role="list">
		<?php foreach ( $donations_data as $row ) : ?>
			<?php
			$donation_id   = isset( $row['donation_id'] ) ? absint( $row['donation_id'] ) : 0;
			$sub_donations = $row['sub_donations'] ?? array();
			$status        = isset( $row['payment_status'] ) ? (string) $row['payment_status'] : '';
			?>
		<li class="gfw-donations-list__item" data-donation-id="<?php echo esc_attr( (string) $donation_id ); ?>">
			<article class="gfw-donation-card" aria-labelledby="gfw-donation-title-<?php echo esc_attr( (string) $donation_id ); ?>">
				<div class="gfw-donation-card__top">
					<div class="gfw-donation-card__amount-wrap">
						<span class="screen-reader-text"><?php esc_html_e( 'Amount', 'giftflow' ); ?></span>
						<span class="gfw-donation-card__amount"><?php echo wp_kses_post( $row['amount_formatted'] ?? '' ); ?></span>
					</div>
					<span class="donation-status status-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( ucfirst( $status ) ); ?></span>
				</div>

				<div class="gfw-donation-card__campaign" id="gfw-donation-title-<?php echo esc_attr( (string) $donation_id ); ?>">
					<?php if ( ! empty( $row['campaign_id'] ) ) : ?>
						<?php if ( ! empty( $row['process_bar_html'] ) ) : ?>
					<div class="gfw-donation-card__progress">
							<?php echo wp_kses_post( $row['process_bar_html'] ); ?>
					</div>
						<?php endif; ?>
					<div class="gfw-donation-card__campaign-line">
						<span class="gfw-donation-card__campaign-name"><?php echo esc_html( $row['campaign_title'] ?? '' ); ?></span>
						<a class="gfw-donation-card__campaign-link" href="<?php echo esc_url( $row['campaign_url'] ?? '' ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Open campaign in new tab', 'giftflow' ); ?>">
							<?php echo wp_kses( giftflow_svg_icon( 'arrow-up-right' ), giftflow_allowed_svg_tags() ); ?>
						</a>
					</div>
					<?php else : ?>
					<p class="gfw-donation-card__campaign-name"><?php echo esc_html( $row['campaign_title'] ?? '' ); ?></p>
					<?php endif; ?>
				</div>

				<div class="gfw-donation-card__meta" role="group" aria-label="<?php esc_attr_e( 'Donation details', 'giftflow' ); ?>">
					<span class="gfw-donation-card__meta-item gfw-donation-card__meta-item--payment">
						<span class="gfw-donation-card__meta-key"><?php esc_html_e( 'Payment', 'giftflow' ); ?></span>
						<span class="gfw-donation-card__meta-val"><?php echo wp_kses_post( $row['payment_template'] ?? '' ); ?></span>
					</span>
					<span class="gfw-donation-card__meta-item gfw-donation-card__meta-item--when">
						<span class="gfw-donation-card__meta-key"><?php esc_html_e( 'When', 'giftflow' ); ?></span>
						<span class="gfw-donation-card__meta-val"><?php echo wp_kses_post( $row['date_ago'] ?? '' ); ?></span>
					</span>
					<span class="gfw-donation-card__meta-item gfw-donation-card__meta-item--ref">
						<span class="gfw-donation-card__meta-key"><?php esc_html_e( 'Ref.', 'giftflow' ); ?></span>
						<span class="gfw-donation-card__meta-val gfw-donation-card__ref">#<?php echo esc_html( (string) $donation_id ); ?></span>
					</span>
				</div>

				<?php do_action( 'giftflow_my_donation_card_after_meta', $row ); ?>

				<div class="gfw-donation-card__foot">
					<a class="gfw-donation-card__detail" href="<?php echo esc_url( $row['detail_url'] ?? '' ); ?>">
						<span><?php esc_html_e( 'View details', 'giftflow' ); ?></span>
						<?php echo wp_kses( giftflow_svg_icon( 'arrow-up-right' ), giftflow_allowed_svg_tags() ); ?>
					</a>
				</div>

				<?php if ( ! empty( $sub_donations ) && is_array( $sub_donations ) ) : ?>
				<details class="gfw-donation-card__subs">
					<summary class="gfw-donation-card__subs-summary">
						<span><?php esc_html_e( 'Recurring payments', 'giftflow' ); ?></span>
						<span class="gfw-donation-card__subs-badge"><?php echo esc_html( (string) count( $sub_donations ) ); ?></span>
					</summary>
					<ul class="gfw-donation-card__subs-list" role="list">
						<?php
						foreach ( $sub_donations as $sub_post ) :
							$sub_id = $sub_post->ID;
							$sub_d  = giftflow_get_donation_data_by_id( $sub_id );
							if ( ! $sub_d ) {
								continue;
							}
							$sub_detail_url = giftflow_donor_account_page_url( 'donations?_id=' . $sub_id );
							$sub_date_ago   = '<span class="gfw-donation-date-ago" title="' . esc_attr( $sub_d->__date_gmt ) . '">' . giftflow_render_time_ago( $sub_d->__date_gmt ) . '</span>';
							$sub_status     = (string) ( $sub_d->status ?? '' );
							?>
						<li class="gfw-donation-card__subs-item">
							<span class="gfw-donation-card__subs-line">
								<span class="gfw-donation-card__subs-amount"><?php echo wp_kses_post( $sub_d->__amount_formatted ?? '' ); ?></span>
								<span class="donation-status status-<?php echo esc_attr( $sub_status ); ?>"><?php echo esc_html( ucfirst( $sub_status ) ); ?></span>
							</span>
							<span class="gfw-donation-card__subs-meta">
								<span class="gfw-donation-card__subs-ref">#<?php echo esc_html( (string) $sub_id ); ?></span>
								<span class="gfw-donation-card__subs-when"><?php echo wp_kses_post( $sub_date_ago ); ?></span>
							</span>
							<a class="gfw-donation-card__subs-link" href="<?php echo esc_url( $sub_detail_url ); ?>"><?php esc_html_e( 'Details', 'giftflow' ); ?></a>
						</li>
						<?php endforeach; ?>
					</ul>
				</details>
				<?php endif; ?>

				<?php do_action( 'giftflow_my_donation_card_after_body', $row ); ?>
			</article>
		</li>
		<?php endforeach; ?>
	</ul>

	<?php
	if ( isset( $donations->max_num_pages ) && $donations->max_num_pages > 1 ) :
		$big          = 999999999;
		$current_page = max( 1, $page );
		?>
	<nav class="gfw-pagination" aria-label="<?php esc_attr_e( 'Donations list pagination', 'giftflow' ); ?>">
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
					'type'      => 'list',
				)
			)
		);
		?>
	</nav>
	<?php endif; ?>
<?php else : ?>
	<div class="gfw-no-donations" role="status">
		<p class="gfw-no-donations__title"><?php esc_html_e( 'No donations yet', 'giftflow' ); ?></p>
		<p class="gfw-no-donations__hint"><?php esc_html_e( 'When you support a campaign, your receipts and history will appear here.', 'giftflow' ); ?></p>
	</div>
<?php endif; ?>
</div>
