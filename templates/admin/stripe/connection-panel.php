<?php
/**
 * Stripe connection panel template.
 *
 * Renders the Sandbox | Live profile tabs, the active payment environment
 * selector, the shared helper text and one panel per environment. Saved
 * credentials are never rendered: only masked identifiers are shown, and secret
 * inputs start empty so blank means "keep the saved value".
 *
 * @package GiftFlow
 * @since 1.2.0
 *
 * @var array $stripe_panel Panel view model built by StripeConnectionPanel.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$panel      = isset( $stripe_panel ) && is_array( $stripe_panel ) ? $stripe_panel : array();
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
	$input_id    = 'giftflow-stripe-' . $environment . '-' . str_replace( '_', '-', $field );
	$edit_id     = $input_id . '-edit';
	?>
	<tr class="giftflow-stripe-credential"
		data-credential="<?php echo esc_attr( $field ); ?>"
		data-environment="<?php echo esc_attr( $environment ); ?>">
		<th scope="row"><?php echo esc_html( $label ); ?></th>
		<td>
			<span class="giftflow-stripe-credential-view" data-role="credential-view">
				<span class="giftflow-stripe-credential-value" data-role="<?php echo esc_attr( $role ); ?>"><?php echo '' !== $masked ? esc_html( $masked ) : esc_html( $giftflow_panel_string( $strings, 'secret_missing' ) ); ?></span>
				<button type="button"
					class="giftflow-stripe-icon-button giftflow-stripe-action"
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

			<span class="giftflow-stripe-credential-edit" id="<?php echo esc_attr( $edit_id ); ?>" data-role="credential-edit" hidden>
				<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>">
					<?php echo esc_html( $label ); ?>
				</label>
				<span class="giftflow-stripe-input-group">
					<input type="<?php echo esc_attr( $input_type ); ?>"
						id="<?php echo esc_attr( $input_id ); ?>"
						value=""
						autocomplete="new-password"
						spellcheck="false"
						data-credential="<?php echo esc_attr( $field ); ?>" />
					<?php if ( 'password' === $input_type ) : ?>
						<button type="button" class="button giftflow-stripe-reveal" data-target="<?php echo esc_attr( $input_id ); ?>" disabled aria-pressed="false">
							<span data-role="reveal-label"><?php echo esc_html( $giftflow_panel_string( $strings, 'show' ) ); ?></span>
						</button>
					<?php endif; ?>
					<button type="button"
						class="button button-primary giftflow-stripe-action"
						data-action="update-credential"
						data-environment="<?php echo esc_attr( $environment ); ?>"
						data-credential="<?php echo esc_attr( $field ); ?>"
						disabled>
						<?php echo esc_html( $giftflow_panel_string( $strings, 'update' ) ); ?>
					</button>
					<button type="button"
						class="button giftflow-stripe-action"
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
<div class="giftflow-stripe-panel"
	data-active-environment="<?php echo esc_attr( $active_env ); ?>"
	id="giftflow-stripe-panel">

	<div class="giftflow-stripe-summary-bar">
		<button type="button"
			class="button button-primary giftflow-stripe-open"
			data-action="open-config"
			aria-haspopup="dialog"
			aria-controls="giftflow-stripe-modal">
			<?php echo esc_html( $giftflow_panel_string( $strings, 'open_config' ) ); ?>
		</button>

		<span class="giftflow-stripe-summary-states">
			<?php foreach ( $environments as $giftflow_summary_environment ) : ?>
				<?php $giftflow_summary_profile = isset( $profiles[ $giftflow_summary_environment ] ) ? $profiles[ $giftflow_summary_environment ] : array(); ?>
				<span class="giftflow-stripe-badge"
					data-role="summary-badge-<?php echo esc_attr( $giftflow_summary_environment ); ?>"
					data-tone="<?php echo esc_attr( isset( $giftflow_summary_profile['state_tone'] ) ? $giftflow_summary_profile['state_tone'] : 'neutral' ); ?>"
					data-state="<?php echo esc_attr( isset( $giftflow_summary_profile['state'] ) ? $giftflow_summary_profile['state'] : 'not_configured' ); ?>">
					<?php
					echo esc_html(
						sprintf(
							/* translators: 1: environment label, 2: connection state label */
							'%1$s · %2$s',
							\GiftFlow\Stripe\StripeEnvironment::label( $giftflow_summary_environment ),
							isset( $giftflow_summary_profile['state_label'] ) ? $giftflow_summary_profile['state_label'] : ''
						)
					);
					?>
				</span>
			<?php endforeach; ?>
		</span>
	</div>

	<p class="giftflow-stripe-helper"><?php echo esc_html( $giftflow_panel_string( $strings, 'keys_helper' ) ); ?></p>

	<?php if ( ! empty( $panel['migration_error'] ) ) : ?>
		<div class="notice notice-error inline giftflow-stripe-notice">
			<p><?php echo esc_html( $giftflow_panel_string( $strings, 'notice_migration' ) ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( empty( $panel['url_is_public'] ) ) : ?>
		<div class="notice notice-warning inline giftflow-stripe-notice">
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
		<div class="notice notice-warning inline giftflow-stripe-notice">
			<p><?php echo esc_html( $giftflow_panel_string( $strings, 'notice_webhook_off' ) ); ?></p>
		</div>
	<?php endif; ?>

	<?php
	// Rendered through GiftFlow_Field so the row matches the other settings fields.
	$giftflow_environment_choices = array();

	foreach ( $environments as $giftflow_environment_choice ) {
		$giftflow_environment_choices[ $giftflow_environment_choice ] = \GiftFlow\Stripe\StripeEnvironment::label( $giftflow_environment_choice );
	}

	$giftflow_environment_field = new GiftFlow_Field(
		'giftflow_stripe_mode',
		'giftflow_payment_options[stripe][stripe_mode]',
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

	<dialog class="giftflow-stripe-modal"
		id="giftflow-stripe-modal"
		aria-labelledby="giftflow-stripe-modal-title">

		<div class="giftflow-stripe-modal-header">
			<h2 id="giftflow-stripe-modal-title"><?php echo esc_html( $giftflow_panel_string( $strings, 'modal_title' ) ); ?></h2>
			<button type="button" class="button giftflow-stripe-modal-close" data-action="close-config">
				<?php echo esc_html( $giftflow_panel_string( $strings, 'close' ) ); ?>
			</button>
		</div>

		<div class="giftflow-stripe-modal-body">

			<div class="notice notice-info inline giftflow-stripe-notice">
				<p><?php echo esc_html( $giftflow_panel_string( $strings, 'connect_hint' ) ); ?></p>
			</div>

			<div class="giftflow-stripe-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Stripe configuration profile', 'giftflow' ); ?>">
				<?php foreach ( $environments as $index => $environment ) : ?>
					<?php
					$tab_id   = 'giftflow-stripe-tab-' . $environment;
					$panel_id = 'giftflow-stripe-tabpanel-' . $environment;
					$selected = $active_env === $environment;
					?>
			<button type="button"
				class="giftflow-stripe-tab<?php echo $selected ? ' is-active' : ''; ?>"
				id="<?php echo esc_attr( $tab_id ); ?>"
				role="tab"
				aria-selected="<?php echo $selected ? 'true' : 'false'; ?>"
				aria-controls="<?php echo esc_attr( $panel_id ); ?>"
				tabindex="<?php echo $selected ? '0' : '-1'; ?>"
				data-environment="<?php echo esc_attr( $environment ); ?>">
					<?php echo esc_html( \GiftFlow\Stripe\StripeEnvironment::label( $environment ) ); ?>
			</button>
		<?php endforeach; ?>
	</div>

	<?php foreach ( $environments as $environment ) : ?>
		<?php
		$profile  = isset( $profiles[ $environment ] ) ? $profiles[ $environment ] : array();
		$panel_id = 'giftflow-stripe-tabpanel-' . $environment;
		$tab_id   = 'giftflow-stripe-tab-' . $environment;
		?>
		<div class="giftflow-stripe-tabpanel"
			id="<?php echo esc_attr( $panel_id ); ?>"
			role="tabpanel"
			aria-labelledby="<?php echo esc_attr( $tab_id ); ?>"
			data-environment="<?php echo esc_attr( $environment ); ?>"
			<?php echo $active_env === $environment ? '' : 'hidden'; ?>>

			<p class="giftflow-stripe-status-line">
				<span class="giftflow-stripe-badge"
					data-role="status-badge"
					data-tone="<?php echo esc_attr( isset( $profile['state_tone'] ) ? $profile['state_tone'] : 'neutral' ); ?>"
					data-state="<?php echo esc_attr( isset( $profile['state'] ) ? $profile['state'] : 'not_configured' ); ?>">
					<?php echo esc_html( isset( $profile['state_label'] ) ? $profile['state_label'] : '' ); ?>
				</span>
				<span class="giftflow-stripe-status-text" data-role="status-message">
					<?php echo esc_html( isset( $profile['last_error_message'] ) ? $profile['last_error_message'] : '' ); ?>
				</span>
			</p>

			<p class="description" data-role="recovery-note" <?php echo empty( $profile['recovery_pending'] ) ? 'hidden' : ''; ?>>
				<?php echo esc_html( $giftflow_panel_string( $strings, 'recovery_pending' ) ); ?>
			</p>

			<table class="giftflow-stripe-summary">
				<tbody>
					<?php
					$giftflow_credential_row(
						array(
							'environment' => $environment,
							'field'       => 'publishable_key',
							'label'       => __( 'Publishable key', 'giftflow' ),
							'masked'      => (string) $profile['publishable_key_masked'],
							'role'        => 'publishable-masked',
							'input_type'  => 'text',
						)
					);

					$giftflow_credential_row(
						array(
							'environment' => $environment,
							'field'       => 'secret_key',
							'label'       => __( 'Secret key', 'giftflow' ),
							'masked'      => (string) $profile['secret_key_masked'],
							'role'        => 'secret-masked',
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

			<p class="giftflow-stripe-details-toggle">
				<button type="button"
					class="button-link giftflow-stripe-action giftflow-stripe-details-toggle-button"
					data-action="toggle-details"
					data-environment="<?php echo esc_attr( $environment ); ?>"
					aria-expanded="false"
					aria-controls="giftflow-stripe-details-<?php echo esc_attr( $environment ); ?>">
					<span class="giftflow-stripe-details-caret" aria-hidden="true"></span>
					<span data-role="details-label"><?php echo esc_html( $giftflow_panel_string( $strings, 'show_details' ) ); ?></span>
				</button>
			</p>

			<div class="giftflow-stripe-details"
				id="giftflow-stripe-details-<?php echo esc_attr( $environment ); ?>"
				data-role="details"
				hidden>

				<table class="giftflow-stripe-summary">
					<tbody>
						<tr>
							<th scope="row"><?php echo esc_html( $giftflow_panel_string( $strings, 'account' ) ); ?></th>
							<td data-role="account">
								<?php echo '' !== (string) $profile['stripe_account_id'] ? esc_html( $profile['stripe_account_id'] ) : esc_html( $giftflow_panel_string( $strings, 'owner_unknown' ) ); ?>
							</td>
						</tr>
						<?php
						$giftflow_credential_row(
							array(
								'environment' => $environment,
								'field'       => 'webhook_secret',
								'label'       => __( 'Signing secret', 'giftflow' ),
								'masked'      => (string) $profile['webhook_secret_masked'],
								'role'        => 'webhook-secret-masked',
								'input_type'  => 'password',
								'hint'        => $giftflow_panel_string( $strings, 'notice_manual_hint' ),
							)
						);
						?>
						<tr>
							<th scope="row"><?php echo esc_html( $giftflow_panel_string( $strings, 'endpoint_id' ) ); ?></th>
							<td data-role="webhook-id"><?php echo '' !== (string) $profile['webhook_id'] ? '<code>' . esc_html( $profile['webhook_id'] ) . '</code>' : esc_html( $giftflow_panel_string( $strings, 'owner_unknown' ) ); ?></td>
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
								<span data-role="event-count"><?php echo esc_html( sprintf( /* translators: %d: event count */ _n( '%d event', '%d events', count( $events_to_show ), 'giftflow' ), count( $events_to_show ) ) ); ?></span>
								<ul class="giftflow-stripe-events">
									<?php foreach ( $events_to_show as $event_name ) : ?>
										<li><code><?php echo esc_html( (string) $event_name ); ?></code></li>
									<?php endforeach; ?>
								</ul>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php echo esc_html( $giftflow_panel_string( $strings, 'last_checked' ) ); ?></th>
							<td data-role="last-checked"><?php echo '' !== (string) $profile['last_checked_display'] ? esc_html( $profile['last_checked_display'] ) : esc_html( $giftflow_panel_string( $strings, 'never' ) ); ?></td>
						</tr>
						<tr>
							<th scope="row"><?php echo esc_html( $giftflow_panel_string( $strings, 'last_verified' ) ); ?></th>
							<td data-role="last-verified"><?php echo '' !== (string) $profile['last_verified_display'] ? esc_html( $profile['last_verified_display'] ) : esc_html( $giftflow_panel_string( $strings, 'never' ) ); ?></td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="giftflow-stripe-actions">
				<button type="button" class="button button-primary giftflow-stripe-action" data-action="connect" data-environment="<?php echo esc_attr( $environment ); ?>">
					<?php echo esc_html( $giftflow_panel_string( $strings, 'connect' ) ); ?>
				</button>
				<button type="button" class="button giftflow-stripe-action" data-action="check" data-environment="<?php echo esc_attr( $environment ); ?>">
					<?php echo esc_html( $giftflow_panel_string( $strings, 'check' ) ); ?>
				</button>
				<button type="button" class="button giftflow-stripe-action" data-action="repair" data-environment="<?php echo esc_attr( $environment ); ?>" disabled>
					<?php echo esc_html( $giftflow_panel_string( $strings, 'repair' ) ); ?>
				</button>
			</div>

			<div class="giftflow-stripe-diff" data-role="diff" aria-live="polite" hidden>
				<h4><?php echo esc_html( $giftflow_panel_string( $strings, 'diff_title' ) ); ?></h4>
				<ul class="giftflow-stripe-diff-list" data-role="diff-list"></ul>
				<p class="giftflow-stripe-actions">
					<button type="button" class="button button-primary giftflow-stripe-action" data-action="repair-confirm" data-environment="<?php echo esc_attr( $environment ); ?>">
						<?php echo esc_html( $giftflow_panel_string( $strings, 'confirm_applied' ) ); ?>
					</button>
					<button type="button" class="button button-primary giftflow-stripe-action" data-action="rebind-confirm" data-environment="<?php echo esc_attr( $environment ); ?>" hidden>
						<?php echo esc_html( $giftflow_panel_string( $strings, 'rebind' ) ); ?>
					</button>
					<button type="button" class="button giftflow-stripe-action" data-action="diff-cancel" data-environment="<?php echo esc_attr( $environment ); ?>">
						<?php echo esc_html( $giftflow_panel_string( $strings, 'cancel' ) ); ?>
					</button>
				</p>
			</div>

		</div>
	<?php endforeach; ?>

			<p class="giftflow-stripe-operation-status" data-role="operation-status" role="status" aria-live="polite"></p>

		</div>
	</dialog>

	<script type="application/json" id="giftflow-stripe-config">
	<?php
	// JSON_HEX_TAG/AMP keep the payload inert inside a script element.
	echo wp_json_encode( $panel, JSON_HEX_TAG | JSON_HEX_AMP ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	?>
	</script>
</div>
