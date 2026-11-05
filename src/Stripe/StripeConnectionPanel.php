<?php
/**
 * View model and renderer for the Stripe connection panel.
 *
 * The panel is rendered inside the Stripe settings accordion. It only ever
 * receives masked credentials, so saved secrets never reach page markup.
 *
 * @package GiftFlow
 * @subpackage Stripe
 */

namespace GiftFlow\Stripe;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the environment-scoped Stripe setup panel.
 */
final class StripeConnectionPanel {

	/**
	 * Repository.
	 *
	 * @var StripeConnectionRepository
	 */
	private StripeConnectionRepository $repository;

	/**
	 * Active payment environment.
	 *
	 * @var string
	 */
	private string $active_environment;

	/**
	 * Canonical webhook URL.
	 *
	 * @var string
	 */
	private string $webhook_url;

	/**
	 * Constructor.
	 *
	 * @param StripeConnectionRepository $repository         Repository.
	 * @param string                     $active_environment Active payment environment.
	 * @param string                     $webhook_url        Canonical webhook URL.
	 */
	public function __construct( StripeConnectionRepository $repository, string $active_environment, string $webhook_url ) {
		$this->repository         = $repository;
		$this->active_environment = StripeEnvironment::is_valid( $active_environment )
			? $active_environment
			: StripeEnvironment::SANDBOX;
		$this->webhook_url        = $webhook_url;
	}

	/**
	 * Render the panel.
	 *
	 * @return void
	 */
	public function render(): void {
		$panel = $this->build_data();

		giftflow_load_template( 'admin/stripe/connection-panel.php', array( 'stripe_panel' => $panel ) );
	}

	/**
	 * Build the panel view model.
	 *
	 * @return array<string, mixed>
	 */
	public function build_data(): array {
		$recurring = $this->repository->is_recurring_enabled();
		$profiles  = array();

		foreach ( StripeEnvironment::all() as $environment ) {
			$masked   = $this->repository->get_masked_profile( $environment );
			$profiles[ $environment ] = array_merge(
				$masked,
				array(
					'events_required' => StripeWebhookEventRegistry::required_events( $recurring, $environment ),
				)
			);
		}

		$migration = $this->repository->get_migration_state();

		return array(
			'active_environment' => $this->active_environment,
			'webhook_url'        => $this->webhook_url,
			'environments'       => StripeEnvironment::all(),
			'profiles'           => $profiles,
			'webhook_enabled'    => $this->repository->is_webhook_enabled(),
			'recurring_enabled'  => $recurring,
			'migration_error'    => StripeErrorCodes::sanitize( $migration['error'] ?? '' ),
			'url_is_public'      => StripeWebhookProvisioner::is_public_url( $this->webhook_url ),
			'rest'               => array(
				'root'  => esc_url_raw( rest_url( 'giftflow/v2/stripe' ) ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
			),
			'strings'            => $this->strings(),
		);
	}

	/**
	 * Translated UI strings used by markup and the admin module.
	 *
	 * @return array<string, string>
	 */
	private function strings(): array {
		return array(
			'connect'            => __( 'Connect & Auto Configure', 'giftflow' ),
			'connect_running'    => __( 'Configuring…', 'giftflow' ),
			'check'              => __( 'Check Configuration', 'giftflow' ),
			'check_running'      => __( 'Checking…', 'giftflow' ),
			'repair'             => __( 'Repair', 'giftflow' ),
			'repair_running'     => __( 'Repairing…', 'giftflow' ),
			'rebind'             => __( 'Confirm account change', 'giftflow' ),
			'open_config'        => __( 'Connect Stripe Config', 'giftflow' ),
			'modal_title'        => __( 'Stripe configuration', 'giftflow' ),
			'close'              => __( 'Close', 'giftflow' ),
			'cancel'             => __( 'Cancel', 'giftflow' ),
			'keys_helper'        => __( 'Keys stay on this WordPress server and are used directly with Stripe.', 'giftflow' ),
			'connect_hint'       => __( 'Enter both keys for this profile below, then click Connect & Auto Configure to finish the setup.', 'giftflow' ),
			'edit'               => __( 'Edit', 'giftflow' ),
			'click_to_edit'      => __( 'Click to edit', 'giftflow' ),
			'update'             => __( 'Update', 'giftflow' ),
			'value_required'     => __( 'Enter a value before updating.', 'giftflow' ),
			'credential_hint'    => __( 'Only this value is replaced; the saved value is never shown again.', 'giftflow' ),
			'show'               => __( 'Show', 'giftflow' ),
			'hide'               => __( 'Hide', 'giftflow' ),
			'show_details'       => __( 'Show details', 'giftflow' ),
			'hide_details'       => __( 'Hide details', 'giftflow' ),
			'account'            => __( 'Stripe account', 'giftflow' ),
			'endpoint_url'       => __( 'Endpoint URL', 'giftflow' ),
			'endpoint_id'        => __( 'Endpoint ID', 'giftflow' ),
			'events'             => __( 'Events', 'giftflow' ),
			'last_checked'       => __( 'Last checked', 'giftflow' ),
			'last_verified'      => __( 'Last valid webhook', 'giftflow' ),
			'never'              => __( 'Never', 'giftflow' ),
			'configured'         => __( 'Configured', 'giftflow' ),
			'not_configured'     => __( 'Not configured', 'giftflow' ),
			'ownership'          => __( 'Managed by', 'giftflow' ),
			'owner_plugin'       => __( 'GiftFlow', 'giftflow' ),
			'owner_manual'       => __( 'Manual setup', 'giftflow' ),
			'owner_unknown'      => __( 'Not recorded', 'giftflow' ),
			'secret_missing'     => __( 'Not saved', 'giftflow' ),
			'diff_title'         => __( 'Exact changes that will be applied', 'giftflow' ),
			'field_status'       => __( 'Endpoint status', 'giftflow' ),
			'field_url'          => __( 'Endpoint URL', 'giftflow' ),
			'field_events'       => __( 'Subscribed events', 'giftflow' ),
			'field_account'      => __( 'Stripe account', 'giftflow' ),
			'confirm_applied'    => __( 'Confirm and apply', 'giftflow' ),
			'notice_manual_hint' => __( 'Paste the signing secret from the Stripe dashboard when the endpoint already exists.', 'giftflow' ),
			// translators: %s: webhook URL reported by this site.
			'notice_local_url'   => __( 'Stripe needs a public HTTPS webhook URL, but this site reports %s. Automatic configuration is unavailable: replace the keys with the pencil buttons and paste the signing secret from Stripe.', 'giftflow' ),
			'notice_webhook_off' => __( 'The webhook setting in this accordion is currently off, so Stripe deliveries are acknowledged without processing.', 'giftflow' ),
			'notice_migration'   => StripeErrorCodes::message( StripeErrorCodes::MIGRATION_ERROR ),
			'recovery_pending'   => __( 'A previous request needs reconciliation. Run Check Configuration, then Connect again.', 'giftflow' ),
			'request_failed'     => __( 'The request could not be completed.', 'giftflow' ),
			'network_failed'     => __( 'The request could not reach this server. Reload the page and try again.', 'giftflow' ),
		);
	}
}
