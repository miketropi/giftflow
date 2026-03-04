<?php
/**
 * Template for donation list of campaign
 *
 * @package GiftFlow
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// --- Input ---
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound, WordPress.WP.GlobalVariablesOverride.Prohibited
$posts      = $donations['posts'] ?? array();
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$total      = $donations['total'] ?? 0;
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$pagination = $donations['pagination'] ?? 1;
// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$paged      = $paged ?? 1;

/**
 * Table column definitions.
 * Keys: label, value (key in row data), format (text|html|time|donor), cell_class, head_cell_class (optional).
 *
 * @var array $list_columns
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$list_columns = array(
	array(
		'label'          => __( 'Donor name', 'giftflow' ),
		'value'          => 'donor',
		'format'         => 'donor',
		'cell_class'     => 'gfw-donation-list__cell--donor',
		'head_cell_class' => 'gfw-donation-list__head-cell--donor',
	),
	array(
		'label'          => __( 'Amount', 'giftflow' ),
		'value'          => 'amount_html',
		'format'         => 'html',
		'cell_class'     => 'gfw-donation-list__cell--amount',
		'head_cell_class' => 'gfw-donation-list__head-cell--amount',
	),
	array(
		'label'          => __( 'Date', 'giftflow' ),
		'value'          => 'time_ago',
		'format'         => 'time',
		'cell_class'     => 'gfw-donation-list__cell--date',
		'head_cell_class' => 'gfw-donation-list__head-cell--date',
	),
);

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$list_columns = apply_filters( 'giftflow_donation_list_columns', $list_columns );

// --- Build row data (one array per donation; keys match column value / donor fields).
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$list_rows = array();
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
foreach ( $posts as $donation ) {
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$is_anonymous   = ( 'yes' === ( $donation['is_anonymous'] ?? '' ) );
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$donor_name     = $donation['donor_meta']['name'] ?? '';
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$donor_email    = $donation['donor_meta']['email'] ?? '';
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$date_gmt       = $donation['date_gmt'] ?? '';

	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$time_ago = '';
	if ( $date_gmt ) {
		if ( function_exists( 'giftflow_render_time_ago' ) ) {
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
			$time_ago = giftflow_render_time_ago( $date_gmt );
		} else {
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
			$time_ago = date_i18n( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), strtotime( $date_gmt ) );
		}
	}

	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$donor_secondary = '';
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	if ( ! $is_anonymous ) {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		$city    = $donation['donor_meta']['city'] ?? '';
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		$country = $donation['donor_meta']['country'] ?? '';
		if ( $city && $country ) {
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
			$donor_secondary = $city . ', ' . $country;
		} else {
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
			$donor_secondary = '—';
		}
	}

	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	if ( $is_anonymous || empty( $donor_email ) ) {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		$initial     = $is_anonymous ? '?' : ( $donor_name ? mb_substr( $donor_name, 0, 1 ) : '?' );
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		$avatar_html = '<span class="gfw-donation-list__avatar-placeholder" aria-hidden="true">' . esc_html( $initial ) . '</span>';
	} else {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		$avatar_html = get_avatar( $donor_email, 40, '', $donor_name, array( 'class' => 'gfw-donation-list__avatar-img' ) );
	}

	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$list_row = array(
		'id'              => $donation['id'] ?? '',
		'donor_name'      => $donor_name,
		'donor_secondary' => $donor_secondary,
		'avatar_html'     => $avatar_html,
		'message'         => isset( $donation['message'] ) ? mb_substr( $donation['message'], 0, 300 ) . ( mb_strlen( $donation['message'] ) > 300 ? '…' : '' ) : '',
		'amount_html'     => $donation['amount_formatted'] ?? '',
		'time_ago'        => $time_ago,
		'date_gmt'        => $date_gmt,
	);

	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$list_row = apply_filters( 'giftflow_donation_list_row', $list_row, $donation );
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$list_rows[] = $list_row;
}
?>

<div class="gfw-donation-list-container">
	<header class="gfw-donation-list-header">
		<span class="gfw-donation-count">
			<?php if ( $total > 0 ) : ?>
				<?php
				/* translators: %s: number of donations */
				printf( esc_html__( '%s donations', 'giftflow' ), esc_html( number_format_i18n( $total ) ) );
				?>
			<?php else : ?>
				<?php esc_html_e( 'Be the first to donate', 'giftflow' ); ?>
			<?php endif; ?>
		</span>
	</header>

	<?php if ( ! empty( $list_rows ) ) : ?>
		<div class="gfw-donation-list">
			
			<?php
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
			foreach ( $list_rows as $row ) :
				?>
				<div class="gfw-donation-list__row gfw-donation-list__row--<?php echo esc_attr( $row['id'] ?? '' ); ?>">
					<?php
					// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
					foreach ( $list_columns as $col ) :
						?>
						<?php
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
						$key   = $col['value'] ?? '';
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
						$format = $col['format'] ?? 'text';
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
						$cell_class = 'gfw-donation-list__cell ' . ( $col['cell_class'] ?? '' );
						?>
						<div class="<?php echo esc_attr( trim( $cell_class ) ); ?>">
							<?php if ( 'donor' === $format ) : ?>
								<span class="gfw-donation-list__avatar"><?php echo $row['avatar_html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<div class="gfw-donation-list__donor-main">
									<span class="gfw-donation-list__donor-name"><?php echo esc_html( $row['donor_name'] ); ?></span>
									<?php if ( ! empty( $row['donor_secondary'] ) ) : ?>
										<span class="gfw-donation-list__donor-secondary"><?php echo esc_html( $row['donor_secondary'] ); ?></span>
									<?php endif; ?>
									<?php if ( ! empty( $row['time_ago'] ) ) : ?>
										<span class="gfw-donation-list__date-mobile"><time datetime="<?php echo esc_attr( $row['date_gmt'] ); ?>"><?php echo esc_html( $row['time_ago'] ); ?></time></span>
									<?php endif; ?>
									<?php if ( ! empty( $row['message'] ) ) : ?>
										<span class="gfw-donation-list__message"><?php echo esc_html( $row['message'] ); ?></span>
									<?php endif; ?>
								</div>
							<?php elseif ( 'time' === $format ) : ?>
								<?php if ( ! empty( $row[ $key ] ) ) : ?>
									<time datetime="<?php echo esc_attr( $row['date_gmt'] ?? '' ); ?>" title="<?php echo esc_attr( $row['date_gmt'] ?? '' ); ?>"><?php echo esc_html( $row[ $key ] ); ?></time>
								<?php endif; ?>
							<?php elseif ( 'html' === $format ) : ?>
								<?php echo wp_kses_post( $row[ $key ] ?? '' ); ?>
							<?php else : ?>
								<?php echo esc_html( $row[ $key ] ?? '' ); ?>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( $pagination > 1 ) : ?>
			<nav class="gfw-donation-list-pagination" aria-label="<?php esc_attr_e( 'Donations pagination', 'giftflow' ); ?>">
				<span class="gfw-donation-list-pagination__info">
					<?php
					/* translators: 1: current page, 2: total pages */
					printf( esc_html__( 'Page %1$d of %2$d', 'giftflow' ), (int) $paged, (int) $pagination );
					?>
				</span>
				<div class="gfw-donation-list-pagination__buttons">
					<?php if ( $paged > 1 ) : ?>
						<button type="button" class="gfw-donation-list-pagination__btn gfw-prev-btn" data-page="<?php echo esc_attr( (string) ( $paged - 1 ) ); ?>" data-campaign="<?php echo esc_attr( (string) ( $campaign_id ?? 0 ) ); ?>" onclick="window.giftflow.loadDonationListPaginationTemplate_Handle(this)"><?php esc_html_e( 'Previous', 'giftflow' ); ?></button>
					<?php endif; ?>
					<?php if ( $paged < $pagination ) : ?>
						<button type="button" class="gfw-donation-list-pagination__btn gfw-next-btn" data-page="<?php echo esc_attr( (string) ( $paged + 1 ) ); ?>" data-campaign="<?php echo esc_attr( (string) ( $campaign_id ?? 0 ) ); ?>" onclick="window.giftflow.loadDonationListPaginationTemplate_Handle(this)"><?php esc_html_e( 'Next', 'giftflow' ); ?></button>
					<?php endif; ?>
				</div>
			</nav>
		<?php endif; ?>

	<?php else : ?>
		<div class="gfw-donation-list-empty">
			<p><?php esc_html_e( 'No donations yet', 'giftflow' ); ?></p>
		</div>
	<?php endif; ?>
</div>
