<?php
/**
 * PayPal connection panel template.
 *
 * Renders the Sandbox | Live profile tabs, the active payment environment
 * selector, the shared helper text and one panel per environment. Saved
 * credentials are never rendered: only masked identifiers are shown, and secret
 * inputs start empty so blank means "keep the saved value".
 *
 * @package GiftFlow
 * @since 1.2.0
 *
 * @var array $paypal_panel Panel view model built by PayPalConnectionPanel.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$panel      = isset( $paypal_panel ) && is_array( $paypal_panel ) ? $paypal_panel : array();
$strings    = isset( $panel['strings'] ) ? $panel['strings'] : array();
$profiles   = isset( $panel['profiles'] ) ? $panel['profiles'] : array();
$active_env = isset( $panel['active_environment'] ) ? $panel['active_environment'] : 'sandbox';
$environments = isset( $panel['environments'] ) ? $panel['environments'] : array( 'sandbox', 'live' );

/**
 * Read a translated string from the panel payload.
 *
 * @param array  $strings  Panel strings.
 * @param string $key      String key.
 * @param string $fallback Fallback text.
 * @return string
 */
$giftflow_panel_string = static function ( array $strings, string $key, string $fallback = '' ): string {
	return isset( $strings[ $key ] ) ? (string) $strings[ $key ] : $fallback;
};

/**
 * Render one inline-editable credential row.
 *
 * The saved value is only ever rendered masked; editing reveals an empty input
 * that replaces exactly this credential through the REST route.
 *
 * @param array $args Row arguments: environment, field, label, masked, role, input_type, hint.
 * @return void
 */
$giftflow_credential_row = static function ( array $args ) use ( $giftflow_panel_string, $strings ): void {
	$environment = (string) $args['environment'];
	$field       = (string) $args['field'];
	$label       = (string) $args['label'];
	$masked      = (string) $args['masked'];
	$role        = (string) $args['role'];
	$input_type  = isset( $args['input_type'] ) ? (string) $args['input_type'] : 'password';
	$hint        = isset( $args['hint'] ) ? (string) $args['hint'] : $giftflow_panel_string( $strings, 'credential_hint' );
	$input_id    = 'giftflow-paypal-' . $environment . '-' . str_replace( '_', '-', $field );
	$edit_id     = $input_id . '-edit';
	?>
	<tr class="giftflow-paypal-credential"
		data-credential="<?php echo esc_attr( $field ); ?>"
		data-environment="<?php echo esc_attr( $environment ); ?>">
		<th scope="row"><?php echo esc_html( $label ); ?></th>
		<td>
			<span class="giftflow-paypal-credential-view" data-role="credential-view">
				<span class="giftflow-paypal-credential-value" data-role="<?php echo esc_attr( $role ); ?>">
					<?php echo '' !== $masked ? esc_html( $masked ) : esc_html( $giftflow_panel_string( $strings, 'secret_missing' ) ); ?>
				</span>
				<button type="button"
					class="giftflow-paypal-icon-button giftflow-paypal-action"
					data-action="edit-credential"
					data-environment="<?php echo esc_attr( $environment ); ?>"
					data-credential="<?php echo esc_attr( $field ); ?>"
					aria-expanded="false"
					aria-controls="<?php echo esc_attr( $edit_id ); ?>"
					aria-label="<?php echo esc_attr( sprintf( /* translators: %s: credential label */ __( 'Edit %s', 'giftflow' ), $label ) ); ?>"
					title="<?php echo esc_attr( $giftflow_panel_string( $strings, 'edit' ) ); ?>">
					<?php echo wp_kses( giftflow_svg_icon( 'pencil' ), giftflow_allowed_svg_tags() ); ?>
				</button>
			</span>

			<span class="giftflow-paypal-credential-edit" id="<?php echo esc_attr( $edit_id ); ?>" data-role="credential-edit" hidden>
				<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>">
					<?php echo esc_html( $label ); ?>
				</label>
				<span class="giftflow-paypal-input-group">
					<input type="<?php echo esc_attr( $input_type ); ?>"
						id="<?php echo esc_attr( $input_id ); ?>"
						value=""
						autocomplete="new-password"
						spellcheck="false"
						data-credential="<?php echo esc_attr( $field ); ?>" />
					<?php if ( 'password' === $input_type ) : ?>
						<button type="button" class="button giftflow-paypal-reveal" data-target="<?php echo esc_attr( $input_id ); ?>" disabled aria-pressed="false">
							<span data-role="reveal-label"><?php echo esc_html( $giftflow_panel_string( $strings, 'show' ) ); ?></span>
						</button>
					<?php endif; ?>
					<button type="button"
						class="button button-primary giftflow-paypal-action"
						data-action="update-credential"
						data-environment="<?php echo esc_attr( $environment ); ?>"
						data-credential="<?php echo esc_attr( $field ); ?>"
						disabled>
						<?php echo esc_html( $giftflow_panel_string( $strings, 'update' ) ); ?>
					</button>
					<button type="button"
						class="button giftflow-paypal-action"
						data-action="cancel-credential"
						data-environment="<?php echo esc_attr( $environment ); ?>"
						data-credential="<?php echo esc_attr( $field ); ?>">
						<?php echo esc_html( $giftflow_panel_string( $strings, 'cancel' ) ); ?>
					</button>
				</span>
				<span class="description"><?php echo esc_html( $hint ); ?></span>
			</span>
		</td>
	</tr>
	<?php
};
?>
<div class="giftflow-paypal-panel"
	data-active-environment="<?php echo esc_attr( $active_env ); ?>"
	id="giftflow-paypal-panel">

	<div class="giftflow-paypal-summary-bar">
		<button type="button"
			class="button button-primary giftflow-paypal-open"
			data-action="open-config"
			aria-haspopup="dialog"
			aria-controls="giftflow-paypal-modal">
			<?php echo esc_html( $giftflow_panel_string( $strings, 'open_config' ) ); ?>
		</button>

		<span class="giftflow-paypal-summary-states">
			<?php foreach ( $environments as $giftflow_summary_environment ) : ?>
				<?php $giftflow_summary_profile = isset( $profiles[ $giftflow_summary_environment ] ) ? $profiles[ $giftflow_summary_environment ] : array(); ?>
				<span class="giftflow-paypal-badge"
					data-role="summary-badge-<?php echo esc_attr( $giftflow_summary_environment ); ?>"
					data-tone="<?php echo esc_attr( isset( $giftflow_summary_profile['state_tone'] ) ? $giftflow_summary_profile['state_tone'] : 'neutral' ); ?>"
					data-state="<?php echo esc_attr( isset( $giftflow_summary_profile['state'] ) ? $giftflow_summary_profile['state'] : 'not_configured' ); ?>">
					<?php
					echo esc_html(
						sprintf(
							/* translators: 1: environment label, 2: connection state label */
							'%1$s · %2$s',
							\GiftFlow\PayPal\PayPalEnvironment::label( $giftflow_summary_environment ),
							isset( $giftflow_summary_profile['state_label'] ) ? $giftflow_summary_profile['state_label'] : ''
						)
					);
					?>
				</span>
			<?php endforeach; ?>
		</span>
	</div>

	<p class="giftflow-paypal-helper"><?php echo esc_html( $giftflow_panel_string( $strings, 'keys_helper' ) ); ?></p>

	<?php if ( ! empty( $panel['migration_error'] ) ) : ?>
		<div class="notice notice-error inline giftflow-paypal-notice">
			<p><?php echo esc_html( $giftflow_panel_string( $strings, 'notice_migration' ) ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( empty( $panel['url_is_public'] ) ) : ?>
		<div class="notice notice-warning inline giftflow-paypal-notice">
			<p>
				<?php
				printf(
					// translators: %s: webhook URL reported by this site.
					esc_html( $giftflow_panel_string( $strings, 'notice_local_url' ) ),
					'<code>' . esc_html( (string) $panel['webhook_url'] ) . '</code>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped fragment.
				);
				?>
			</p>
		</div>
	<?php endif; ?>

	<?php if ( empty( $panel['webhook_enabled'] ) ) : ?>
		<div class="notice notice-warning inline giftflow-paypal-notice">
			<p><?php echo esc_html( $giftflow_panel_string( $strings, 'notice_webhook_off' ) ); ?></p>
		</div>
	<?php endif; ?>

	<?php
	// Rendered through GiftFlow_Field so the row matches the other settings fields.
	$giftflow_environment_choices = array();

	foreach ( $environments as $giftflow_environment_choice ) {
		$giftflow_environment_choices[ $giftflow_environment_choice ] = \GiftFlow\PayPal\PayPalEnvironment::label( $giftflow_environment_choice );
	}

	$giftflow_environment_field = new GiftFlow_Field(
		'giftflow_paypal_mode',
		'giftflow_payment_options[paypal][paypal_mode]',
		'select',
		array(
			'label'       => __( 'Active payment environment', 'giftflow' ),
			'value'       => $active_env,
			'options'     => $giftflow_environment_choices,
			'description' => __( 'This controls which profile processes donor payments. Switching it does not edit or migrate any profile.', 'giftflow' ),
		)
	);

	$giftflow_environment_field->render();
	?>

	<dialog class="giftflow-paypal-modal"
		id="giftflow-paypal-modal"
		aria-labelledby="giftflow-paypal-modal-title">

		<div class="giftflow-paypal-modal-header">
			<h2 id="giftflow-paypal-modal-title"><?php echo esc_html( $giftflow_panel_string( $strings, 'modal_title' ) ); ?></h2>
			<button type="button" class="button giftflow-paypal-modal-close" data-action="close-config">
				<?php echo esc_html( $giftflow_panel_string( $strings, 'close' ) ); ?>
			</button>
		</div>

		<div class="giftflow-paypal-modal-body">

			<div class="notice notice-info inline giftflow-paypal-notice">
				<p><?php echo esc_html( $giftflow_panel_string( $strings, 'connect_hint' ) ); ?></p>
			</div>

			<div class="giftflow-paypal-tabs" role="tablist" aria-label="<?php esc_attr_e( 'PayPal configuration profile', 'giftflow' ); ?>">
				<?php foreach ( $environments as $index => $environment ) : ?>
					<?php
					$tab_id   = 'giftflow-paypal-tab-' . $environment;
					$panel_id = 'giftflow-paypal-tabpanel-' . $environment;
					$selected = $active_env === $environment;
					?>
			<button type="button"
				class="giftflow-paypal-tab<?php echo $selected ? ' is-active' : ''; ?>"
				id="<?php echo esc_attr( $tab_id ); ?>"
				role="tab"
				aria-selected="<?php echo $selected ? 'true' : 'false'; ?>"
				aria-controls="<?php echo esc_attr( $panel_id ); ?>"
				tabindex="<?php echo $selected ? '0' : '-1'; ?>"
				data-environment="<?php echo esc_attr( $environment ); ?>">
					<?php echo esc_html( \GiftFlow\PayPal\PayPalEnvironment::label( $environment ) ); ?>
			</button>
		<?php endforeach; ?>
	</div>

	<?php foreach ( $environments as $environment ) : ?>
		<?php
		$profile  = isset( $profiles[ $environment ] ) ? $profiles[ $environment ] : array();
		$panel_id = 'giftflow-paypal-tabpanel-' . $environment;
		$tab_id   = 'giftflow-paypal-tab-' . $environment;
		?>
		<div class="giftflow-paypal-tabpanel"
			id="<?php echo esc_attr( $panel_id ); ?>"
			role="tabpanel"
			aria-labelledby="<?php echo esc_attr( $tab_id ); ?>"
			data-environment="<?php echo esc_attr( $environment ); ?>"
			<?php echo $active_env === $environment ? '' : 'hidden'; ?>>

			<p class="giftflow-paypal-status-line">
				<span class="giftflow-paypal-badge"
					data-role="status-badge"
					data-tone="<?php echo esc_attr( isset( $profile['state_tone'] ) ? $profile['state_tone'] : 'neutral' ); ?>"
					data-state="<?php echo esc_attr( isset( $profile['state'] ) ? $profile['state'] : 'not_configured' ); ?>">
					<?php echo esc_html( isset( $profile['state_label'] ) ? $profile['state_label'] : '' ); ?>
				</span>
				<span class="giftflow-paypal-status-text" data-role="status-message">
					<?php echo esc_html( isset( $profile['last_error_message'] ) ? $profile['last_error_message'] : '' ); ?>
				</span>
			</p>

			<p class="description" data-role="recovery-note" <?php echo empty( $profile['recovery_pending'] ) ? 'hidden' : ''; ?>>
				<?php echo esc_html( $giftflow_panel_string( $strings, 'recovery_pending' ) ); ?>
			</p>

			<table class="giftflow-paypal-summary">
				<tbody>
					<?php
					$giftflow_credential_row(
						array(
							'environment' => $environment,
							'field'       => 'client_id',
							'label'       => __( 'Client ID', 'giftflow' ),
							'masked'      => (string) $profile['client_id_masked'],
							'role'        => 'client-id-masked',
							'input_type'  => 'text',
						)
					);

					$giftflow_credential_row(
						array(
							'environment' => $environment,
							'field'       => 'client_secret',
							'label'       => __( 'Client secret', 'giftflow' ),
							'masked'      => (string) $profile['client_secret_masked'],
							'role'        => 'client-secret-masked',
							'input_type'  => 'password',
						)
					);
					?>
					<tr>
						<th scope="row"><?php echo esc_html( $giftflow_panel_string( $strings, 'endpoint_url' ) ); ?></th>
						<td data-role="webhook-url"><code><?php echo esc_html( (string) $panel['webhook_url'] ); ?></code></td>
					</tr>
				</tbody>
			</table>

			<p class="giftflow-paypal-details-toggle">
				<button type="button"
					class="button-link giftflow-paypal-action giftflow-paypal-details-toggle-button"
					data-action="toggle-details"
					data-environment="<?php echo esc_attr( $environment ); ?>"
					aria-expanded="false"
					aria-controls="giftflow-paypal-details-<?php echo esc_attr( $environment ); ?>">
					<span class="giftflow-paypal-details-caret" aria-hidden="true"></span>
					<span data-role="details-label"><?php echo esc_html( $giftflow_panel_string( $strings, 'show_details' ) ); ?></span>
				</button>
			</p>

			<div class="giftflow-paypal-details"
				id="giftflow-paypal-details-<?php echo esc_attr( $environment ); ?>"
				data-role="details"
				hidden>

				<table class="giftflow-paypal-summary">
					<tbody>
						<tr>
							<th scope="row"><?php echo esc_html( $giftflow_panel_string( $strings, 'app_id' ) ); ?></th>
							<td data-role="app-id">
								<?php
								echo '' !== (string) $profile['paypal_app_id']
									? esc_html( $profile['paypal_app_id'] )
									: esc_html( $giftflow_panel_string( $strings, 'owner_unknown' ) );
								?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php echo esc_html( $giftflow_panel_string( $strings, 'endpoint_id' ) ); ?></th>
							<td data-role="webhook-id">
								<?php
								echo '' !== (string) $profile['webhook_id']
									? '<code>' . esc_html( $profile['webhook_id'] ) . '</code>'
									: esc_html( $giftflow_panel_string( $strings, 'owner_unknown' ) );
								?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php echo esc_html( $giftflow_panel_string( $strings, 'ownership' ) ); ?></th>
							<td data-role="ownership">
								<?php
								$owner_labels = array(
									'plugin'  => $giftflow_panel_string( $strings, 'owner_plugin' ),
									'manual'  => $giftflow_panel_string( $strings, 'owner_manual' ),
									'unknown' => $giftflow_panel_string( $strings, 'owner_unknown' ),
								);
								$owner        = isset( $profile['webhook_owner'] ) ? (string) $profile['webhook_owner'] : 'unknown';
								echo esc_html( isset( $owner_labels[ $owner ] ) ? $owner_labels[ $owner ] : $owner_labels['unknown'] );
								?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php echo esc_html( $giftflow_panel_string( $strings, 'events' ) ); ?></th>
							<td data-role="events">
								<?php
								$events_configured = isset( $profile['events'] ) && is_array( $profile['events'] ) ? $profile['events'] : array();
								$events_required   = isset( $profile['events_required'] ) && is_array( $profile['events_required'] ) ? $profile['events_required'] : array();
								$events_to_show    = ! empty( $events_configured ) ? $events_configured : $events_required;
								?>
								<span data-role="event-count">
									<?php
									echo esc_html(
										sprintf(
											/* translators: %d: event count */
											_n( '%d event', '%d events', count( $events_to_show ), 'giftflow' ),
											count( $events_to_show )
										)
									);
									?>
								</span>
								<ul class="giftflow-paypal-events">
									<?php foreach ( $events_to_show as $event_name ) : ?>
										<li><code><?php echo esc_html( (string) $event_name ); ?></code></li>
									<?php endforeach; ?>
								</ul>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php echo esc_html( $giftflow_panel_string( $strings, 'last_checked' ) ); ?></th>
							<td data-role="last-checked">
								<?php
								echo '' !== (string) $profile['last_checked_display']
									? esc_html( $profile['last_checked_display'] )
									: esc_html( $giftflow_panel_string( $strings, 'never' ) );
								?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php echo esc_html( $giftflow_panel_string( $strings, 'last_verified' ) ); ?></th>
							<td data-role="last-verified">
								<?php
								echo '' !== (string) $profile['last_verified_display']
									? esc_html( $profile['last_verified_display'] )
									: esc_html( $giftflow_panel_string( $strings, 'never' ) );
								?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="giftflow-paypal-actions">
				<button type="button" class="button button-primary giftflow-paypal-action" data-action="connect" data-environment="<?php echo esc_attr( $environment ); ?>">
					<?php echo esc_html( $giftflow_panel_string( $strings, 'connect' ) ); ?>
				</button>
				<button type="button" class="button giftflow-paypal-action" data-action="check" data-environment="<?php echo esc_attr( $environment ); ?>">
					<?php echo esc_html( $giftflow_panel_string( $strings, 'check' ) ); ?>
				</button>
				<button type="button" class="button giftflow-paypal-action" data-action="repair" data-environment="<?php echo esc_attr( $environment ); ?>" disabled>
					<?php echo esc_html( $giftflow_panel_string( $strings, 'repair' ) ); ?>
				</button>
			</div>

			<div class="giftflow-paypal-diff" data-role="diff" aria-live="polite" hidden>
				<h4><?php echo esc_html( $giftflow_panel_string( $strings, 'diff_title' ) ); ?></h4>
				<ul class="giftflow-paypal-diff-list" data-role="diff-list"></ul>
				<p class="giftflow-paypal-actions">
					<button type="button" class="button button-primary giftflow-paypal-action" data-action="repair-confirm" data-environment="<?php echo esc_attr( $environment ); ?>">
						<?php echo esc_html( $giftflow_panel_string( $strings, 'confirm_applied' ) ); ?>
					</button>
					<button type="button" class="button button-primary giftflow-paypal-action" data-action="rebind-confirm" data-environment="<?php echo esc_attr( $environment ); ?>" hidden>
						<?php echo esc_html( $giftflow_panel_string( $strings, 'rebind' ) ); ?>
					</button>
					<button type="button" class="button giftflow-paypal-action" data-action="diff-cancel" data-environment="<?php echo esc_attr( $environment ); ?>">
						<?php echo esc_html( $giftflow_panel_string( $strings, 'cancel' ) ); ?>
					</button>
				</p>
			</div>

		</div>
	<?php endforeach; ?>

			<p class="giftflow-paypal-operation-status" data-role="operation-status" role="status" aria-live="polite"></p>

		</div>
	</dialog>

	<script type="application/json" id="giftflow-paypal-config">
	<?php
	// JSON_HEX_TAG/AMP keep the payload inert inside a script element.
	echo wp_json_encode( $panel, JSON_HEX_TAG | JSON_HEX_AMP ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	?>
	</script>
</div>
