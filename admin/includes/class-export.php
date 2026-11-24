<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Export functionality for GiftFlow
 *
 * @package GiftFlow
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GiftFlow Export class.

 * @since 1.0.0
 */
class GiftFlow_Export {
	/**
	 * The format of the export.

	 * @var string
	 */
	public $format;
	/**
	 * The ID of the campaign.

	 * @var string
	 */
	public $campaign_id;
	/**
	 * The period of the export.

	 * @var string
	 */
	public $period;
	/**
	 * The start date of the export.

	 * @var string
	 */
	public $date_from;
	/**
	 * The end date of the export.

	 * @var string
	 */
	public $date_to;
	/**
	 * Whether to include the donor in the export.

	 * @var bool
	 */
	public $include_donor;
	/**
	 * Whether to include the campaign in the export.

	 * @var bool
	 */
	public $include_campaign;

	/**
	 * Initialize the export functionality

	 * @param string $campaign_id The ID of the campaign.
	 * @param string $period The period of the export.
	 * @param string $date_from The start date of the export.
	 * @param string $date_to The end date of the export.
	 * @param bool $include_donor Whether to include the donor in the export.
	 * @param bool $include_campaign Whether to include the campaign in the export.
	 * @param string $format The format of the export.
	 * @return void
	 */
	public function __construct(
		$campaign_id = '',
		$period = 'all',
		$date_from = '',
		$date_to = '',
		$include_donor = true,
		$include_campaign = true,
		$format = 'csv'
	) {
		$this->format           = $format;
		$this->campaign_id      = $campaign_id;
		$this->period           = $period;
		$this->date_from        = $date_from;
		$this->date_to          = $date_to;
		$this->include_donor    = $include_donor;
		$this->include_campaign = $include_campaign;

		$this->export_donations();
	}

	/**
	 * Export donations based on criteria.
	 */
	public function export_donations() {

		// Build query args.
		$args = array(
			'post_type'   => 'donation',
			'post_status' => 'publish',
			'numberposts' => -1,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query'  => array(),
		);

		// Add campaign filter.
		if ( ! empty( $this->campaign_id ) ) {
			$args['meta_query'][] = array(
				'key'     => '_campaign_id',
				'value'   => $this->campaign_id,
				'compare' => '=',
			);
		}

		// Add date filter.
		$date_query = $this->get_date_query( $this->period, $this->date_from, $this->date_to );
		if ( $date_query ) {
			$args['date_query'] = $date_query;
		}

		// Get donations.
		$donations = get_posts( $args );

		if ( empty( $donations ) ) {
			// translators: %s: Campaign name.
			wp_die( sprintf( esc_html__( 'No donations found for the selected campaign: %s', 'giftflow' ), esc_html( get_the_title( $this->campaign_id ) ) ) );
		}

		// Generate export based on format.
		switch ( $this->format ) {
			case 'csv':
				$this->export_csv( $donations, $this->include_donor, $this->include_campaign );
				break;
			default:
				wp_die( 'Invalid export format' );
		}
	}

	/**
	 * Get date query based on period.
	 */
	private function get_date_query() {
		switch ( $this->period ) {
			case 'year':
				return array(
					array(
						'after'     => '1 year ago',
						'inclusive' => true,
					),
				);
			case 'month':
				return array(
					array(
						'after'     => '1 month ago',
						'inclusive' => true,
					),
				);
			case 'week':
				return array(
					array(
						'after'     => '1 week ago',
						'inclusive' => true,
					),
				);
			case 'custom':
				if ( ! empty( $this->date_from ) && ! empty( $this->date_to ) ) {
					return array(
						array(
							'after'     => $this->date_from,
							'before'    => $this->date_to,
							'inclusive' => true,
						),
					);
				}
				break;
			case 'all':
			default:
				return null;
		}

		return null;
	}

	/**
	 * Neutralise spreadsheet formulas in a CSV cell.
	 *
	 * Excel, LibreOffice and Sheets execute a cell that starts with `=`, `+`,
	 * `-`, `@`, a tab or a carriage return. Donor names, emails and campaign
	 * titles are user input, so prefix such cells with an apostrophe.
	 *
	 * @param mixed $value Cell value.
	 * @return string
	 */
	private static function csv_safe_cell( $value ) {
		$value = (string) $value;

		if ( '' !== $value && false !== strpos( "=+-@\t\r", $value[0] ) ) {
			return "'" . $value;
		}

		return $value;
	}

	/**
	 * Export donations to CSV.

	 * @param array $donations The donations to export.
	 * @param bool $include_donor Whether to include the donor in the export.
	 * @param bool $include_campaign Whether to include the campaign in the export.
	 * @return void
	 */
	private function export_csv( $donations, $include_donor, $include_campaign ) {
		$campaign_name = get_the_title( $this->campaign_id );
		$filename      = sanitize_file_name( $campaign_name . '_' . gmdate( 'Y-m-d_H-i-s' ) . '.csv' );

		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Pragma: public' );

		$output = fopen( 'php://output', 'w' );

		// CSV headers.
		$headers = array( 'ID', 'Date', 'Amount', 'Status' );

		if ( $include_donor ) {
			$headers[] = 'Donor Name';
			$headers[] = 'Donor Email';
		}

		if ( $include_campaign ) {
			$headers[] = 'Campaign';
		}

		$headers[] = 'Payment Method';

		fputcsv( $output, $headers, ',', '"', '\\' );

		// CSV data.
		foreach ( $donations as $donation ) {
			$row = array(
				$donation->ID,
				get_the_date( 'Y-m-d H:i:s', $donation->ID ),
				get_post_meta( $donation->ID, '_amount', true ),
				get_post_meta( $donation->ID, '_status', true ),
			);

			if ( $include_donor ) {
				$donor_id = get_post_meta( $donation->ID, '_donor_id', true );
				if ( $donor_id ) {
					$donor = get_post( $donor_id );
					$row[] = $donor ? $donor->post_title : '';
					$row[] = get_post_meta( $donor_id, '_email', true );
				} else {
					$row[] = '';
					$row[] = '';
				}
			}

			if ( $include_campaign ) {
				$campaign_id = get_post_meta( $donation->ID, '_campaign_id', true );
				if ( $campaign_id ) {
					$campaign = get_post( $campaign_id );
					$row[]    = $campaign ? $campaign->post_title : '';
				} else {
					$row[] = '';
				}
			}

			$row[] = get_post_meta( $donation->ID, '_payment_method', true );

			fputcsv( $output, array_map( array( __CLASS__, 'csv_safe_cell' ), $row ), ',', '"', '\\' );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		fclose( $output );
		exit;
	}
}
