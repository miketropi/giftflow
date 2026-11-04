<?php
/**
 * View model and renderer for the PayPal connection panel.
 *
 * The panel is rendered inside the PayPal settings accordion. It only ever
 * receives masked credentials, so saved secrets never reach page markup.
 *
 * @package GiftFlow
 * @subpackage PayPal
 */

namespace GiftFlow\PayPal;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the environment-scoped PayPal setup panel.
 */
final class PayPalConnectionPanel {

	/**
	 * Repository.
	 *
	 * @var PayPalConnectionRepository
	 */
	private PayPalConnectionRepository $repository;

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
	 * @param PayPalConnectionRepository $repository         Repository.
	 * @param string                     $active_environment Active payment environment.
	 * @param string                     $webhook_url        Canonical webhook URL.
	 */
	public function __construct( PayPalConnectionRepository $repository, string $active_environment, string $webhook_url ) {
		$this->repository         = $repository;
		$this->active_environment = PayPalEnvironment::is_valid( $active_environment )
			? $active_environment
			: PayPalEnvironment::SANDBOX;
		$this->webhook_url        = $webhook_url;
	}

	/**
	 * Render the panel.
	 *
	 * @return void
	 */
	public function render(): void {
		$panel = $this->build_data();

		giftflow_load_template( 'admin/paypal/connection-panel.php', array( 'paypal_panel' => $panel ) );
	}

	/**
	 * Build the panel view model.
	 *
	 * @return array<string, mixed>
	 */
	public function build_data(): array {
		$recurring = $this->repository->is_recurring_enabled();
		$profiles  = array();

		foreach ( PayPalEnvironment::all() as $environment ) {
			$masked   = $this->repository->get_masked_profile( $environment );
			$profiles[ $environment ] = array_merge(
				$masked,
				array(
					'events_required' => PayPalWebhookEventRegistry::required_events( $recurring, $environment ),
				)
			);
		}

		$migration = $this->repository->get_migration_state();

		return array(
			'active_environment' => $this->active_environment,
			'webhook_url'        => $this->webhook_url,
			'environments'       => PayPalEnvironment::all(),
			'profiles'           => $profiles,
			'webhook_enabled'    => $this->repository->is_webhook_enabled(),
			'recurring_enabled'  => $recurring,
			'migration_error'    => PayPalErrorCodes::sanitize( $migration['error'] ?? '' ),
			'url_is_public'      => PayPalWebhookProvisioner::is_public_url( $this->webhook_url ),
			'rest'               => array(
				'root'  => esc_url_raw( rest_url( 'giftflow/v2/paypal' ) ),
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
			'rebind'             => __( 'Confirm app change', 'giftflow' ),
			'open_config'        => __( 'Connect PayPal Config', 'giftflow' ),
			'modal_title'        => __( 'PayPal configuration', 'giftflow' ),
			'close'              => __( 'Close', 'giftflow' ),
			'cancel'             => __( 'Cancel', 'giftflow' ),
			'keys_helper'        => __( 'Keys stay on this WordPress server and are used directly with PayPal.', 'giftflow' ),
			'connect_hint'       => __( 'Enter the client ID and client secret for this profile below, then click Connect & Auto Configure to finish the setup.', 'giftflow' ),
			'edit'               => __( 'Edit', 'giftflow' ),
			'click_to_edit'      => __( 'Click to edit', 'giftflow' ),
			'update'             => __( 'Update', 'giftflow' ),
			'value_required'     => __( 'Enter a value before updating.', 'giftflow' ),
			'credential_hint'    => __( 'Only this value is replaced; the saved value is never shown again.', 'giftflow' ),
			'show'               => __( 'Show', 'giftflow' ),
			'hide'               => __( 'Hide', 'giftflow' ),
			'show_details'       => __( 'Show details', 'giftflow' ),
			'hide_details'       => __( 'Hide details', 'giftflow' ),
			'app_id'             => __( 'PayPal app ID', 'giftflow' ),
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
			'field_app'          => __( 'PayPal app', 'giftflow' ),
			'field_abandoned'    => __( 'Abandoned product/plan caches', 'giftflow' ),
			'confirm_applied'    => __( 'Confirm and apply', 'giftflow' ),
			// translators: %s: webhook URL reported by this site.
			'notice_local_url'   => __(
				'PayPal needs a public HTTPS webhook URL, but this site reports %s. Automatic configuration is unavailable; credentials can still be saved here for manual use.',
				'giftflow'
			),
			'notice_webhook_off' => __( 'The webhook setting in this accordion is currently off, so PayPal deliveries are acknowledged without processing.', 'giftflow' ),
			'notice_migration'   => PayPalErrorCodes::message( PayPalErrorCodes::MIGRATION_ERROR ),
			'recovery_pending'   => __( 'A previous request needs reconciliation. Run Check Configuration, then Connect again.', 'giftflow' ),
			'request_failed'     => __( 'The request could not be completed.', 'giftflow' ),
			'network_failed'     => __( 'The request could not reach this server. Reload the page and try again.', 'giftflow' ),
		);
	}
}
