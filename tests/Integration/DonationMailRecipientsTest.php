<?php
/**
 * Donation mail recipient resolution tests.
 *
 * The reported incident was a recipient rejected before dispatch: the plugin
 * forwarded an unvalidated address from its own settings. These tests pin the
 * resolution order, the fail-closed validation and the donor-owned recipients.
 *
 * @package GiftFlow
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort -- Compact property type documentation is intentional in tests.

namespace GiftFlow\Tests\Integration;

/**
 * Class DonationMailRecipientsTest
 */
final class DonationMailRecipientsTest extends \WP_UnitTestCase {

	/**
	 * Captured `pre_wp_mail` calls.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	private $sent = array();

	/**
	 * Captured `wp_mail_failed` errors.
	 *
	 * @var array<int,\WP_Error>
	 */
	private $failures = array();

	/**
	 * Capture filters.
	 *
	 * @var array<int,callable>
	 */
	private $capture_filters = array();

	/**
	 * Site administration address used as the fallback.
	 *
	 * @var string
	 */
	const SITE_ADMIN = 'site-admin@example.org';

	/**
	 * Reset options and mail capture.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		delete_option( 'giftflow_email_options' );
		update_option( 'admin_email', self::SITE_ADMIN );

		$this->sent     = array();
		$this->failures = array();

		$capture_mail = function ( $pre, $atts ) {
			$this->sent[] = array(
				'to'      => $atts['to'],
				'subject' => $atts['subject'],
				'headers' => $atts['headers'],
			);

			return true;
		};

		$capture_failure = function ( $error ) {
			$this->failures[] = $error;
		};

		$this->capture_filters = array( $capture_mail, $capture_failure );

		add_filter( 'pre_wp_mail', $capture_mail, 10, 2 );
		add_action( 'wp_mail_failed', $capture_failure );
	}

	/**
	 * Remove capture filters.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		remove_filter( 'pre_wp_mail', $this->capture_filters[0], 10 );
		remove_action( 'wp_mail_failed', $this->capture_filters[1] );

		$this->capture_filters = array();

		parent::tearDown();
	}

	/**
	 * Create a donation with a real donor record.
	 *
	 * @param string $donor_email Donor email.
	 * @param string $status      Initial donation status.
	 * @return int Donation id.
	 */
	private function donation( $donor_email = 'donor@example.org', $status = 'completed' ) {
		$donations = new \GiftFlow\Core\Donations();

		$donation_id = $donations->create(
			array(
				'donation_amount' => 60.0,
				'donor_name'      => 'Donor',
				'donor_email'     => $donor_email,
				'payment_method'  => 'stripe',
				'campaign_id'     => 0,
				'status'          => $status,
			)
		);

		$this->assertNotWPError( $donation_id );
		$this->assertGreaterThan( 0, $donation_id );

		return (int) $donation_id;
	}

	/**
	 * Send through the plugin template sender.
	 *
	 * @param mixed $to Recipient argument.
	 * @return bool
	 */
	private function send_template( $to ) {
		return giftflow_send_mail_template(
			array(
				'to'      => $to,
				'subject' => 'Subject',
				'header'  => 'Header',
				'content' => 'Body',
			)
		);
	}

	/**
	 * Recipient of the single captured message.
	 *
	 * @return mixed
	 */
	private function only_recipient() {
		$this->assertCount( 1, $this->sent );

		return $this->sent[0]['to'];
	}

	/**
	 * Captured messages addressed to a specific recipient.
	 *
	 * @param string $recipient Expected `to` value.
	 * @return array<int,array<string,mixed>>
	 */
	private function messages_to( $recipient ) {
		return array_values(
			array_filter(
				$this->sent,
				static function ( $mail ) use ( $recipient ) {
					return $recipient === $mail['to'];
				}
			)
		);
	}

	/**
	 * Assert the last rejected call reported a code, message and count.
	 *
	 * @param string $code    Expected error code.
	 * @param string $message Expected message.
	 * @param int    $count   Expected recipient_count.
	 * @return void
	 */
	private function assert_rejected( $code, $message, $count ) {
		$this->assertCount( 0, $this->sent );
		$this->assertCount( 1, $this->failures );

		$error = $this->failures[0];

		$this->assertInstanceOf( \WP_Error::class, $error );
		$this->assertSame( $code, $error->get_error_code() );
		$this->assertSame( $message, $error->get_error_message() );
		$this->assertSame( array( 'recipient_count' => $count ), $error->get_error_data() );
	}

	/**
	 * Missing, blank, non-array and malformed settings fall back to the site
	 * administrator for the notifications that are addressed to the admin.
	 *
	 * @return void
	 */
	public function test_admin_notifications_fall_back_to_the_site_administrator(): void {
		$variants = array(
			'absent'          => null,
			'blank'           => array( 'email_admin_address' => '   ' ),
			'empty'           => array( 'email_admin_address' => '' ),
			'not a string'    => array( 'email_admin_address' => array( 'site-admin@example.org' ) ),
			'malformed'       => array( 'email_admin_address' => 'not-an-address' ),
			'option not array' => 'garbage',
		);

		foreach ( $variants as $label => $option ) {
			if ( null === $option ) {
				delete_option( 'giftflow_email_options' );
			} else {
				update_option( 'giftflow_email_options', $option );
			}

			$this->assertSame( self::SITE_ADMIN, giftflow_get_admin_email_address(), $label );

			foreach ( array( 'admin_new_donation', 'donor_thanks', 'new_user_first_time_donation' ) as $name ) {
				$this->sent = array();

				$this->assertTrue( giftflow_test_send_mail_action( $name ), $label . ' / ' . $name );
				$this->assertSame( self::SITE_ADMIN, $this->only_recipient(), $label . ' / ' . $name );

				$headers = (string) ( is_array( $this->sent[0]['headers'] ) ? implode( "\n", $this->sent[0]['headers'] ) : $this->sent[0]['headers'] );

				$this->assertStringContainsString( 'From:', $headers, $label . ' / ' . $name );
				$this->assertStringContainsString( self::SITE_ADMIN, $headers, $label . ' / ' . $name );
			}

			$this->sent = array();

			$this->assertTrue( giftflow_send_mail_notification_donation_to_admin( $this->donation() ), $label );
			$this->assertSame( self::SITE_ADMIN, $this->only_recipient(), $label );
		}
	}

	/**
	 * A valid configured address wins over the site administrator.
	 *
	 * @return void
	 */
	public function test_configured_admin_address_wins(): void {
		update_option(
			'giftflow_email_options',
			array(
				'email_admin_address' => 'donations@example.org',
				'email_from_name'     => 'Charity',
			)
		);

		$this->assertSame( 'donations@example.org', giftflow_get_admin_email_address() );

		$this->assertTrue( giftflow_test_send_mail_action( 'admin_new_donation' ) );
		$this->assertSame( 'donations@example.org', $this->only_recipient() );

		$headers = (string) ( is_array( $this->sent[0]['headers'] ) ? implode( "\n", $this->sent[0]['headers'] ) : $this->sent[0]['headers'] );

		$this->assertStringContainsString( 'Charity <donations@example.org>', $headers );
	}

	/**
	 * Both candidates unusable: no send, one declared sender error.
	 *
	 * @return void
	 */
	public function test_unusable_sender_is_rejected_before_dispatch(): void {
		update_option( 'giftflow_email_options', array( 'email_admin_address' => 'broken' ) );

		// Core refuses to store an invalid admin_email, so simulate the stored
		// value an upgrade or direct database edit can leave behind.
		$broken_admin = static function () {
			return 'also-broken';
		};

		add_filter( 'pre_option_admin_email', $broken_admin );

		try {
			$this->assertSame( '', giftflow_get_admin_email_address() );

			$this->assertFalse( $this->send_template( 'donor@example.org' ) );
			$this->assert_rejected(
				'giftflow_mail_invalid_sender',
				'A valid sender email address is required.',
				1
			);

			$this->sent     = array();
			$this->failures = array();

			// The admin notification's recipient comes from the same resolver,
			// so the empty recipient is rejected first and nothing is sent.
			$this->assertFalse( giftflow_send_mail_notification_donation_to_admin( $this->donation() ) );
			$this->assert_rejected(
				'giftflow_mail_invalid_recipient',
				'A valid recipient email address is required.',
				0
			);
		} finally {
			remove_filter( 'pre_option_admin_email', $broken_admin );
		}
	}

	/**
	 * The recipient argument keeps every format WordPress accepts.
	 *
	 * @return void
	 */
	public function test_valid_recipient_formats_are_delivered_once(): void {
		update_option( 'giftflow_email_options', array( 'email_admin_address' => 'admin@example.org' ) );

		$string_forms = array(
			'single'          => 'one@example.org',
			'padded single'   => '  one@example.org  ',
			'name wrapper'    => 'One Person <one@example.org>',
			'comma list'      => 'one@example.org, two@example.org',
			'trailing comma'  => 'one@example.org,',
		);

		foreach ( $string_forms as $label => $to ) {
			$this->sent     = array();
			$this->failures = array();

			$this->assertTrue( $this->send_template( $to ), $label );
			$this->assertSame( 1, count( $this->sent ), $label );
			$this->assertSame( 0, count( $this->failures ), $label );
			$this->assertIsString( $this->sent[0]['to'], $label );
		}

		$array_forms = array(
			'array'            => array( 'one@example.org', 'Two Person <two@example.org>' ),
			'array with empty' => array( 'one@example.org', '' ),
		);

		foreach ( $array_forms as $label => $to ) {
			$this->sent     = array();
			$this->failures = array();

			$this->assertTrue( $this->send_template( $to ), $label );
			$this->assertSame( 1, count( $this->sent ), $label );
			$this->assertIsArray( $this->sent[0]['to'], $label );
		}

		// Trimming and list splitting happen before dispatch.
		$this->sent = array();

		$this->assertTrue( $this->send_template( ' one@example.org , two@example.org ' ) );
		$this->assertSame( 'one@example.org, two@example.org', $this->sent[0]['to'] );
	}

	/**
	 * Empty, malformed and partially invalid recipients never dispatch.
	 *
	 * @return void
	 */
	public function test_invalid_recipients_are_rejected_before_dispatch(): void {
		update_option( 'giftflow_email_options', array( 'email_admin_address' => 'admin@example.org' ) );

		$cases = array(
			'empty string'        => array( '', 0 ),
			'blank string'        => array( '   ', 0 ),
			'malformed'           => array( 'not-an-address', 1 ),
			'mixed list'          => array( 'one@example.org, broken', 2 ),
			'bad name wrapper'    => array( 'Person <broken>', 1 ),
			'empty array'         => array( array(), 0 ),
			'mixed array'         => array( array( 'one@example.org', 'broken' ), 2 ),
			'non-string entry'    => array( array( 'one@example.org', 123 ), 1 ),
			'null'                => array( null, 0 ),
			'integer'             => array( 42, 0 ),
		);

		foreach ( $cases as $label => $case ) {
			$this->sent     = array();
			$this->failures = array();

			$this->assertFalse( $this->send_template( $case[0] ), $label );
			$this->assert_rejected(
				'giftflow_mail_invalid_recipient',
				'A valid recipient email address is required.',
				$case[1]
			);
		}
	}

	/**
	 * A completed transition sends the thank-you once, and only once.
	 *
	 * @return void
	 */
	public function test_completed_transition_sends_thank_you_exactly_once(): void {
		update_option( 'giftflow_email_options', array( 'email_admin_address' => 'admin@example.org' ) );

		$donation_id = $this->donation( 'completed@example.org', 'pending' );
		$donations   = new \GiftFlow\Core\Donations();

		$this->sent = array();

		$this->assertTrue( $donations->update_status( $donation_id, 'completed' ) );
		$this->assertCount( 1, $this->messages_to( 'completed@example.org' ) );

		// Re-entering completed after another status must not resend.
		$this->assertTrue( $donations->update_status( $donation_id, 'pending' ) );
		$this->assertTrue( $donations->update_status( $donation_id, 'completed' ) );
		$this->assertCount( 1, $this->messages_to( 'completed@example.org' ) );
	}

	/**
	 * A gateway result that is not a confirmation sends no admin notification.
	 *
	 * @return void
	 */
	public function test_unconfirmed_payment_sends_no_admin_notification(): void {
		update_option( 'giftflow_email_options', array( 'email_admin_address' => 'admin@example.org' ) );

		$donation_id = $this->donation( 'pending@example.org', 'pending' );

		$this->sent = array();

		do_action(
			'giftflow_donation_after_payment_processed',
			$donation_id,
			array(
				'success' => false,
				'status'  => 'requires_action',
			)
		);

		$this->assertCount( 0, $this->messages_to( 'admin@example.org' ) );
	}

	/**
	 * The admin notification follows the completed status, exactly once.
	 *
	 * @return void
	 */
	public function test_admin_notified_once_on_completed_transition(): void {
		update_option( 'giftflow_email_options', array( 'email_admin_address' => 'admin@example.org' ) );

		$donation_id = $this->donation( 'settled@example.org', 'pending' );
		$donations   = new \GiftFlow\Core\Donations();

		$this->sent = array();

		$this->assertTrue( $donations->update_status( $donation_id, 'completed' ) );
		$this->assertCount( 1, $this->messages_to( 'admin@example.org' ) );

		// Leaving and re-entering completed must not resend.
		$this->assertTrue( $donations->update_status( $donation_id, 'pending' ) );
		$this->assertTrue( $donations->update_status( $donation_id, 'completed' ) );
		$this->assertCount( 1, $this->messages_to( 'admin@example.org' ) );
	}

	/**
	 * A pending bank transfer notifies the admin once; later completion does not.
	 *
	 * @return void
	 */
	public function test_bank_transfer_pending_notifies_admin_once(): void {
		update_option( 'giftflow_email_options', array( 'email_admin_address' => 'admin@example.org' ) );

		$donation_id = $this->donation( 'bank-transfer@example.org', 'pending' );

		update_post_meta( $donation_id, '_payment_method', 'direct_bank_transfer' );

		$this->sent = array();

		do_action( 'giftflow_bank_transfer_payment_pending', $donation_id, 'REF-1', array() );
		$this->assertCount( 1, $this->messages_to( 'admin@example.org' ) );
		$this->assertCount( 0, $this->messages_to( 'bank-transfer@example.org' ) );

		$this->assertTrue( ( new \GiftFlow\Core\Donations() )->update_status( $donation_id, 'completed' ) );
		$this->assertCount( 1, $this->messages_to( 'admin@example.org' ) );
		$this->assertCount( 1, $this->messages_to( 'bank-transfer@example.org' ) );
	}

	/**
	 * The direct admin notification call still sends, once per donation.
	 *
	 * @return void
	 */
	public function test_direct_admin_notification_call_sends_once(): void {
		update_option( 'giftflow_email_options', array( 'email_admin_address' => 'admin@example.org' ) );

		$donation_id = $this->donation();

		$this->sent = array();

		$this->assertTrue( giftflow_send_mail_notification_donation_to_admin( $donation_id ) );
		$this->assertSame( 'admin@example.org', $this->only_recipient() );

		$this->sent = array();
		$this->assertFalse( giftflow_send_mail_notification_donation_to_admin( $donation_id ) );
		$this->assertCount( 0, $this->sent );
	}

	/**
	 * Unconfirmed payments never send the thank-you.
	 *
	 * @return void
	 */
	public function test_unconfirmed_payments_send_no_thank_you(): void {
		update_option( 'giftflow_email_options', array( 'email_admin_address' => 'admin@example.org' ) );

		$donation_id = $this->donation( 'unconfirmed@example.org', 'pending' );
		$donations   = new \GiftFlow\Core\Donations();

		$this->sent = array();

		$this->assertTrue( $donations->update_status( $donation_id, 'failed' ) );
		$this->assertCount( 0, $this->sent );

		update_post_meta( $donation_id, '_payment_status', 'processing' );
		$this->assertFalse( giftflow_send_mail_thank_you_to_donor_payment_successful( $donation_id, true ) );
		$this->assertCount( 0, $this->sent );
	}

	/**
	 * A direct bank transfer confirmed through Donation Management sends once.
	 *
	 * The management screen writes `_status` directly, so it does not fire the
	 * Donations status hooks.
	 *
	 * @return void
	 */
	public function test_management_confirmation_sends_thank_you_once(): void {
		update_option( 'giftflow_email_options', array( 'email_admin_address' => 'admin@example.org' ) );

		$donation_id = $this->donation( 'bank@example.org', 'pending' );

		update_post_meta( $donation_id, '_payment_method', 'direct_bank_transfer' );
		update_post_meta( $donation_id, '_status', 'completed' );

		$this->sent = array();

		do_action( 'giftflow_donation_management_saved', $donation_id, 'pending', 'completed', 1 );
		$this->assertSame( 'bank@example.org', $this->only_recipient() );

		do_action( 'giftflow_donation_management_saved', $donation_id, 'pending', 'completed', 1 );
		$this->assertCount( 1, $this->sent );
	}

	/**
	 * The donor thank-you stays addressed to the donor, never to the admin.
	 *
	 * @return void
	 */
	public function test_donor_thank_you_is_addressed_to_the_donor(): void {
		update_option( 'giftflow_email_options', array( 'email_admin_address' => 'admin@example.org' ) );

		$donation_id = $this->donation( 'thanked@example.org' );

		$this->assertTrue( giftflow_send_mail_thank_you_to_donor_payment_successful( $donation_id, true ) );
		$this->assertSame( 'thanked@example.org', $this->only_recipient() );

		// The same donation cannot be thanked twice.
		$this->sent = array();
		$this->assertFalse( giftflow_send_mail_thank_you_to_donor_payment_successful( $donation_id, true ) );
		$this->assertCount( 0, $this->sent );

		// No payment success, no mail.
		$this->assertNull( giftflow_send_mail_thank_you_to_donor_payment_successful( $donation_id, false ) );
		$this->assertCount( 0, $this->sent );
	}

	/**
	 * A donation whose donation record vanished sends nothing.
	 *
	 * @return void
	 */
	public function test_missing_donation_record_sends_no_thank_you(): void {
		update_option( 'giftflow_email_options', array( 'email_admin_address' => 'admin@example.org' ) );

		$this->assertFalse( giftflow_send_mail_thank_you_to_donor_payment_successful( 999999, true ) );
		$this->assertCount( 0, $this->sent );
	}

	/**
	 * The welcome mail for a first-time donor goes to the donor.
	 *
	 * @return void
	 */
	public function test_first_donation_welcome_is_addressed_to_the_donor(): void {
		update_option( 'giftflow_email_options', array( 'email_admin_address' => 'admin@example.org' ) );

		$donation_id = $this->donation( 'new-donor@example.org' );

		giftflow_auto_create_user_on_donation( $donation_id, true );

		$user = get_user_by( 'email', 'new-donor@example.org' );

		$this->assertInstanceOf( \WP_User::class, $user );
		$this->assertSame( 'new-donor@example.org', $this->only_recipient() );
	}

	/**
	 * A donation without a usable donor creates no account and sends no mail.
	 *
	 * @return void
	 */
	public function test_missing_donor_creates_no_user_and_sends_nothing(): void {
		update_option( 'giftflow_email_options', array( 'email_admin_address' => 'admin@example.org' ) );

		$donation_id = $this->donation( 'orphan@example.org' );

		update_post_meta( $donation_id, '_donor_id', 999999 );

		giftflow_auto_create_user_on_donation( $donation_id, true );

		$this->assertFalse( get_user_by( 'email', 'orphan@example.org' ) );
		$this->assertCount( 0, $this->sent );
	}
}
