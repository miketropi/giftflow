<?php
/**
 * Template for my account
 *
 * @package GiftFlow
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// =============================================================================
// INITIALIZATION & VALIDATION
// =============================================================================
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound, WordPress.WP.GlobalVariablesOverride.Prohibited
$current_user = $current_user ?? null; // wp user object.
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$donor = $donor ?? null; // donor information.

// Return if required data is missing.
if ( ! $current_user || ! $donor ) {
	return;
}


/**
 * Render page header
 *
 * @return void
 */
function giftflow_render_page_header() {
	?>
	<h2 class="gfw-donor-account__title"><?php esc_html_e( 'My Account', 'giftflow' ); ?></h2>
	<p>
	<?php esc_html_e( 'Keep your donor account information accurate and up to date using the form below. By maintaining your current contact details, you\'ll ensure you never miss important updates, donation receipts, or opportunities to support causes you care about.', 'giftflow' ); ?>
	</p>
	<p>
	<?php esc_html_e( '🔒 Your privacy is important to us. Your information is securely stored and will never be shared without your consent.', 'giftflow' ); ?>
	</p>
	<br/>
	<?php
}

/**
 * Render success/error messages
 *
 * @param array $account_result The account result.
 * @param array $password_result The password result.
 * @return void
 */
function giftflow_render_messages( $account_result, $password_result ) {
	// Account form messages.
	if ( $account_result ) {
		if ( $account_result['success'] ) {
			giftflow_render_message( $account_result['message'], 'success' );
		} else {
			giftflow_render_message( $account_result['errors'], 'error' );
		}
	}

	// Password form messages.
	if ( $password_result ) {
		if ( $password_result['success'] ) {
			giftflow_render_message( $password_result['message'], 'success' );
		} else {
			giftflow_render_message( $password_result['errors'], 'error' );
		}
	}
}

/**
 * Render a single message
 *
 * @param string $message The message.
 * @param string $type The type of message.
 * @return void
 */
function giftflow_render_message( $message, $type ) {
	$class = 'gfw-message gfw-message--' . $type;

	if ( is_array( $message ) ) {
		echo '<div class="' . esc_attr( $class ) . '"><ul>';
		foreach ( $message as $msg ) {
			echo '<li>' . esc_html( $msg ) . '</li>';
		}
		echo '</ul></div>';
	} else {
		echo '<div class="' . esc_attr( $class ) . '">' . esc_html( $message ) . '</div>';
	}
}

/**
 * Render form field
 *
 * @param array $field_config The field configuration.
 * @return void
 */
function giftflow_render_form_field( $field_config ) {
	$field_id         = $field_config['id'];
	$field_name       = $field_config['name'];
	$field_type       = $field_config['type'] ?? 'text';
	$field_value      = $field_config['value'] ?? '';
	$field_label      = $field_config['label'];
	$field_required   = $field_config['required'] ?? false;
	$field_readonly   = $field_config['readonly'] ?? false;
	$field_rows       = $field_config['rows'] ?? 3;
	$field_attributes = $field_config['attributes'] ?? array();
	$field_full_width = $field_config['full_width'] ?? false;

	$attributes_string = '';
	foreach ( $field_attributes as $attr => $value ) {
		if ( is_bool( $value ) ) {
			if ( $value ) {
				$attributes_string .= ' ' . esc_attr( $attr );
			}
			continue;
		}

		$attributes_string .= ' ' . esc_attr( $attr ) . '="' . esc_attr( $value ) . '"';
	}

	if ( ! empty( $field_config['placeholder'] ) ) {
		$attributes_string .= ' placeholder="' . esc_attr( $field_config['placeholder'] ) . '"';
	}

	if ( ! empty( $field_config['maxlength'] ) ) {
		$attributes_string .= ' maxlength="' . absint( $field_config['maxlength'] ) . '"';
	}

	$required_span = $field_required ? ' <span class="required">*</span>' : '';
	$readonly_attr = $field_readonly ? ' readonly' : '';
	$required_attr = $field_required ? ' required' : '';

	?>
	<div class="gfw-form-field<?php echo $field_full_width ? ' gfw-form-field--full' : ''; ?>">
	<label for="<?php echo esc_attr( $field_id ); ?>">
		<?php echo esc_html( $field_label ); ?><?php echo wp_kses_post( $required_span ); ?>
	</label>
	
	<?php if ( 'textarea' === $field_type ) : ?>
		<textarea 
		id="<?php echo esc_attr( $field_id ); ?>" 
		name="<?php echo esc_attr( $field_name ); ?>" 
		rows="<?php echo esc_attr( $field_config['rows'] ?? 3 ); ?>"
		<?php echo $readonly_attr . $required_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static attributes. ?>
		<?php echo $attributes_string; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped per attribute above. ?>
		><?php echo esc_textarea( $field_value ); ?></textarea>
	<?php else : ?>
		<input 
		type="<?php echo esc_attr( $field_type ); ?>" 
		id="<?php echo esc_attr( $field_id ); ?>" 
		name="<?php echo esc_attr( $field_name ); ?>" 
		value="<?php echo esc_attr( $field_value ); ?>"
		<?php echo $readonly_attr . $required_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static attributes. ?>
		<?php echo $attributes_string; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped per attribute above. ?>
		>
	<?php endif; ?>
	</div>
	<?php
}

/**
 * Render form section
 *
 * @param string $title The title of the section.
 * @param string $icon The icon of the section.
 * @param array $fields The fields of the section.
 * @param string $form_class The class of the form.
 * @return void
 */
function giftflow_render_form_section( $title, $icon, $fields, $form_class = '' ) {
	?>
	<div class="gfw-form-section <?php echo esc_attr( $form_class ); ?>">
		<h3 class="gfw-form-section-title">
			<?php echo wp_kses( giftflow_svg_icon( $icon ), giftflow_allowed_svg_tags() ); ?>
			<?php echo esc_html( $title ); ?>
		</h3>
		
		<div class="gfw-form-fields">
			<?php foreach ( $fields as $field ) : ?>
				<?php giftflow_render_form_field( $field ); ?>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

// =============================================================================
// DATA VALIDATION
// =============================================================================

// Ensure required data is available.
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$account_result = $account_result ?? null;
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$password_result = $password_result ?? null;
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$form_data = $form_data ?? array();
?>

<div class="giftflow-donor-account-my-account">
	<?php giftflow_render_page_header(); ?>
	<?php giftflow_render_messages( $account_result, $password_result ); ?>
  
	<?php giftflow_render_account_form( $form_data ); ?>
	<?php giftflow_render_password_form(); ?>
	<?php giftflow_render_avatar_modal(); ?>
</div>

<?php
// =============================================================================
// TEMPLATE RENDERING FUNCTIONS
// =============================================================================

/**
 * Render account information form
 *
 * @param array $form_data The form data.
 * @return void
 */
function giftflow_render_account_form( $form_data ) {
	// Personal information fields.
	$personal_fields = array(
		array(
			'id'       => 'first_name',
			'name'     => 'first_name',
			'type'     => 'text',
			'label'    => __( 'First Name', 'giftflow' ),
			'value'    => $form_data['first_name'],
			'required' => true,
		),
		array(
			'id'       => 'last_name',
			'name'     => 'last_name',
			'type'     => 'text',
			'label'    => __( 'Last Name', 'giftflow' ),
			'value'    => $form_data['last_name'],
			'required' => true,
		),
		array(
			'id'       => 'email',
			'name'     => 'email',
			'type'     => 'email',
			'label'    => __( 'Email Address', 'giftflow' ),
			'value'    => $form_data['email'],
			'required' => true,
			'readonly' => true,
		),
		array(
			'id'    => 'phone',
			'name'  => 'phone',
			'type'  => 'tel',
			'label' => __( 'Phone Number', 'giftflow' ),
			'value' => $form_data['phone'],
		),
	);

	// Address information fields.
	$address_fields = array(
		array(
			'id'         => 'address',
			'name'       => 'address',
			'type'       => 'textarea',
			'label'      => __( 'Street Address', 'giftflow' ),
			'value'      => $form_data['address'],
			'full_width' => true,
			'rows'       => 3,
		),
		array(
			'id'    => 'city',
			'name'  => 'city',
			'type'  => 'text',
			'label' => __( 'City', 'giftflow' ),
			'value' => $form_data['city'],
		),
		array(
			'id'    => 'state',
			'name'  => 'state',
			'type'  => 'text',
			'label' => __( 'State/Province', 'giftflow' ),
			'value' => $form_data['state'],
		),
		array(
			'id'    => 'postal_code',
			'name'  => 'postal_code',
			'type'  => 'text',
			'label' => __( 'ZIP/Postal Code', 'giftflow' ),
			'value' => $form_data['postal_code'],
		),
		array(
			'id'    => 'country',
			'name'  => 'country',
			'type'  => 'text',
			'label' => __( 'Country', 'giftflow' ),
			'value' => $form_data['country'],
		),
	);

	?>
	<form class="gfw-account-form" method="post" action="">
	<?php wp_nonce_field( 'giftflow_update_account', 'giftflow_account_nonce' ); ?>
	
	<?php giftflow_render_form_section( __( 'Personal Information', 'giftflow' ), 'info', $personal_fields ); ?>
	<?php giftflow_render_profile_section( $form_data ); ?>
	<?php giftflow_render_form_section( __( 'Address Information', 'giftflow' ), 'map-pin', $address_fields ); ?>
	
	<div class="gfw-form-actions">
		<button type="submit" name="giftflow_update_account" class="gfw-btn gfw-btn-primary">
		<?php esc_html_e( 'Update Account', 'giftflow' ); ?>
		</button>
	</div>
	</form>
	<?php
}

/**
 * Render password change form
 *
 * @return void
 */
function giftflow_render_password_form() {
	$password_fields = array(
		array(
			'id'         => 'giftflow_current_password',
			'name'       => 'giftflow_current_password',
			'type'       => 'password',
			'label'      => esc_html__( 'Current Password', 'giftflow' ),
			'required'   => true,
			'full_width' => true,
			'attributes' => array( 'autocomplete' => 'current-password' ),
		),
		array(
			'id'         => 'giftflow_new_password',
			'name'       => 'giftflow_new_password',
			'type'       => 'password',
			'label'      => esc_html__( 'New Password', 'giftflow' ),
			'required'   => true,
			'full_width' => true,
			'attributes' => array( 'autocomplete' => 'new-password' ),
		),
		array(
			'id'         => 'giftflow_confirm_password',
			'name'       => 'giftflow_confirm_password',
			'type'       => 'password',
			'label'      => esc_html__( 'Confirm New Password', 'giftflow' ),
			'required'   => true,
			'full_width' => true,
			'attributes' => array( 'autocomplete' => 'new-password' ),
		),
	);

	?>
	<br/>
	<hr />
	<br />

	<div class="gfw-form-section">
	<h3 class="gfw-form-section-title gfw-donor-account__title">
		<?php echo wp_kses( giftflow_svg_icon( 'lock-closed' ), giftflow_allowed_svg_tags() ); ?>
		<?php esc_html_e( 'Change Password', 'giftflow' ); ?>
	</h3>

	<p class="gfw-form-desc">
		<?php esc_html_e( 'For your security, you can change your password below. Please enter your current password and choose a new password that is at least 6 characters long.', 'giftflow' ); ?>
	</p>

	<br />

	<form class="gfw-account-form" method="post" action="">
		<div class="gfw-form-fields">
		<?php foreach ( $password_fields as $field ) : ?>
			<?php giftflow_render_form_field( $field ); ?>
		<?php endforeach; ?>
		</div>
	  
		<br />
	  
		<div class="gfw-form-actions">
		<button type="submit" name="giftflow_update_password" class="gfw-btn gfw-btn-primary">
			<?php esc_html_e( 'Update Password', 'giftflow' ); ?>
		</button>
		</div>
	  
		<?php wp_nonce_field( 'giftflow_update_password', 'giftflow_password_nonce' ); ?>
	</form>
	</div>
	<?php
}

/**
 * Render the public profile section: photo, bio and social links.
 *
 * @param array $form_data The form data.
 * @return void
 */
function giftflow_render_profile_section( $form_data ) {
	$avatar_url = ! empty( $form_data['avatar_url'] ) ? $form_data['avatar_url'] : '';
	$has_custom = ! empty( $form_data['has_custom_avatar'] );
	$socials    = isset( $form_data['socials'] ) && is_array( $form_data['socials'] ) ? $form_data['socials'] : array();

	$social_labels = array(
		'facebook'  => __( 'Facebook', 'giftflow' ),
		'x'         => __( 'X (Twitter)', 'giftflow' ),
		'threads'   => __( 'Threads', 'giftflow' ),
		'linkedin'  => __( 'LinkedIn', 'giftflow' ),
		'instagram' => __( 'Instagram', 'giftflow' ),
		'website'   => __( 'Website', 'giftflow' ),
	);

	?>
	<div class="gfw-form-section">
		<h3 class="gfw-form-section-title">
			<?php echo wp_kses( giftflow_svg_icon( 'user' ), giftflow_allowed_svg_tags() ); ?>
			<?php esc_html_e( 'Public Profile', 'giftflow' ); ?>
		</h3>

		<div class="gfw-form-fields">
			<div class="gfw-form-field gfw-form-field--full gfw-avatar-field">
				<label for="gfw-avatar-upload"><?php esc_html_e( 'Profile Photo', 'giftflow' ); ?></label>
				<div class="gfw-avatar-control">
					<img id="gfw-avatar-preview" class="gfw-avatar-preview" src="<?php echo esc_url( $avatar_url ); ?>" width="96" height="96" alt="<?php esc_attr_e( 'Your profile photo', 'giftflow' ); ?>" />
					<div class="gfw-avatar-meta">
						<div class="gfw-avatar-actions">
							<input type="file" id="gfw-avatar-file" accept="image/png,image/jpeg,image/webp" hidden />
							<button type="button" class="gfw-avatar-btn gfw-avatar-btn--primary" id="gfw-avatar-upload">
								<?php esc_html_e( 'Upload Photo', 'giftflow' ); ?>
							</button>
							<button type="button" class="gfw-avatar-btn" id="gfw-avatar-reset" <?php echo $has_custom ? '' : 'hidden'; ?>>
								<?php esc_html_e( 'Use Gravatar Instead', 'giftflow' ); ?>
							</button>
						</div>
						<p class="gfw-avatar-help">
							<?php esc_html_e( 'Your Gravatar is used by default. When you upload a photo, it replaces Gravatar on this site; remove it at any time to switch back.', 'giftflow' ); ?>
						</p>
						<p class="gfw-avatar-status" id="gfw-avatar-status" role="status" aria-live="polite"></p>
					</div>
				</div>
			</div>

			<?php
			giftflow_render_form_field(
				array(
					'id'          => 'bio',
					'name'        => 'bio',
					'type'        => 'textarea',
					'label'       => __( 'Bio', 'giftflow' ),
					'value'       => $form_data['bio'] ?? '',
					'rows'        => 4,
					'full_width'  => true,
					'maxlength'   => 500,
					'placeholder' => __( 'Tell donors a little about yourself (up to 500 characters).', 'giftflow' ),
				)
			);

			foreach ( $social_labels as $social_key => $social_label ) {
				giftflow_render_form_field(
					array(
						'id'          => 'social_' . $social_key,
						'name'        => 'social_' . $social_key,
						'type'        => 'url',
						'label'       => $social_label,
						'value'       => isset( $socials[ $social_key ] ) ? $socials[ $social_key ] : '',
						'placeholder' => 'https://',
					)
				);
			}
			?>
		</div>
	</div>
	<?php
}

/**
 * Render the avatar crop modal, its styles and its script.
 *
 * @return void
 */
function giftflow_render_avatar_modal() {
	$config = array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'giftflow_avatar' ),
		'strings' => array(
			'saving'  => __( 'Saving photo…', 'giftflow' ),
			'saved'   => __( 'Your photo has been updated.', 'giftflow' ),
			'removed' => __( 'Your photo has been removed. Gravatar is used again.', 'giftflow' ),
			'failed'  => __( 'The photo could not be saved. Please try again.', 'giftflow' ),
			'invalid' => __( 'That file is not a supported image. Choose a PNG, JPEG or WebP file.', 'giftflow' ),
		),
	);
	?>
	<style>
		.gfw-avatar-control { display: flex; gap: 1rem; align-items: flex-start; }
		.gfw-avatar-preview { width: 96px; height: 96px; border-radius: 50%; object-fit: cover; background: #f1f5f9; border: 1px solid #dcdcde; flex-shrink: 0; }
		.gfw-avatar-actions { display: flex; gap: .5rem; flex-wrap: wrap; }
		.gfw-avatar-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 34px; padding: 0 .875rem; border-radius: 6px; border: 1px solid #c3c4c7; background: #fff; color: #1d2327; font-size: .8125rem; font-weight: 500; cursor: pointer; }
		.gfw-avatar-btn:hover { background: #f6f7f7; }
		.gfw-avatar-btn--primary { border-color: #0b57d0; background: #0b57d0; color: #fff; }
		.gfw-avatar-btn--primary:hover { background: #0a4cb8; }
		.gfw-avatar-btn[disabled] { opacity: .6; cursor: default; }
		.gfw-avatar-help { margin: .5rem 0 0; max-width: 56ch; font-size: .8125rem; color: #50575e; }
		.gfw-avatar-status { margin: .35rem 0 0; min-height: 1.2em; font-size: .8125rem; }
		.gfw-avatar-status.is-error { color: #b32d2e; }
		.gfw-avatar-modal { position: fixed; inset: 0; z-index: 100000; display: flex; align-items: center; justify-content: center; padding: 1rem; background: rgba(15, 23, 42, .65); }
		.gfw-avatar-modal[hidden] { display: none; }
		.gfw-avatar-modal__dialog { width: 100%; max-width: 420px; padding: 1.25rem; border-radius: 12px; background: #fff; box-shadow: 0 24px 64px rgba(0, 0, 0, .35); }
		.gfw-avatar-modal__title { margin: 0 0 .25rem; font-size: 1.0625rem; }
		.gfw-avatar-modal__hint { margin: 0 0 .75rem; font-size: .8125rem; color: #50575e; }
		.gfw-avatar-crop { position: relative; width: 100%; max-width: 320px; margin: 0 auto .75rem; aspect-ratio: 1; border-radius: 8px; overflow: hidden; background: #0f172a; }
		.gfw-avatar-crop canvas { display: block; width: 100%; height: 100%; cursor: grab; touch-action: none; }
		.gfw-avatar-crop canvas:active { cursor: grabbing; }
		.gfw-avatar-zoom { width: 100%; margin: 0 0 .75rem; }
		.gfw-avatar-modal__actions { display: flex; gap: .5rem; justify-content: flex-end; }
		.gfw-avatar-modal__status { margin: .5rem 0 0; min-height: 1.2em; font-size: .8125rem; }
		.gfw-avatar-modal__status.is-error { color: #b32d2e; }
		body.gfw-avatar-modal-open { overflow: hidden; }
	</style>

	<div class="gfw-avatar-modal" id="gfw-avatar-modal" hidden>
		<div class="gfw-avatar-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="gfw-avatar-modal-title">
			<h4 class="gfw-avatar-modal__title" id="gfw-avatar-modal-title"><?php esc_html_e( 'Crop your photo', 'giftflow' ); ?></h4>
			<p class="gfw-avatar-modal__hint"><?php esc_html_e( 'Drag the photo to reposition it and use the slider to zoom.', 'giftflow' ); ?></p>
			<div class="gfw-avatar-crop">
				<canvas id="gfw-avatar-canvas" width="320" height="320"></canvas>
			</div>
			<input type="range" class="gfw-avatar-zoom" id="gfw-avatar-zoom" min="1" max="3" step="0.01" value="1" aria-label="<?php esc_attr_e( 'Zoom', 'giftflow' ); ?>" />
			<div class="gfw-avatar-modal__actions">
				<button type="button" class="gfw-avatar-btn" id="gfw-avatar-cancel"><?php esc_html_e( 'Cancel', 'giftflow' ); ?></button>
				<button type="button" class="gfw-avatar-btn gfw-avatar-btn--primary" id="gfw-avatar-save"><?php esc_html_e( 'Save Photo', 'giftflow' ); ?></button>
			</div>
			<p class="gfw-avatar-modal__status" id="gfw-avatar-modal-status" role="status" aria-live="polite"></p>
		</div>
	</div>

	<script type="application/json" id="gfw-avatar-config"><?php echo wp_json_encode( $config ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON in a data script. ?></script>
	<script>
	(function () {
		var configEl = document.getElementById('gfw-avatar-config');
		if (!configEl) {
			return;
		}

		var config = JSON.parse(configEl.textContent);
		var fileInput = document.getElementById('gfw-avatar-file');
		var uploadBtn = document.getElementById('gfw-avatar-upload');
		var resetBtn = document.getElementById('gfw-avatar-reset');
		var preview = document.getElementById('gfw-avatar-preview');
		var pageStatus = document.getElementById('gfw-avatar-status');
		var modal = document.getElementById('gfw-avatar-modal');
		var modalStatus = document.getElementById('gfw-avatar-modal-status');
		var canvas = document.getElementById('gfw-avatar-canvas');
		var zoomInput = document.getElementById('gfw-avatar-zoom');
		var saveBtn = document.getElementById('gfw-avatar-save');
		var cancelBtn = document.getElementById('gfw-avatar-cancel');
		var ctx = canvas.getContext('2d');
		var size = canvas.width;
		var image = null;
		var baseScale = 1;
		var zoom = 1;
		var offsetX = 0;
		var offsetY = 0;
		var dragFrom = null;

		function setStatus(message, isError) {
			[pageStatus, modalStatus].forEach(function (el) {
				if (!el) {
					return;
				}
				el.textContent = message || '';
				el.classList.toggle('is-error', !!isError);
			});
		}

		function clampOffsets() {
			var scale = baseScale * zoom;
			var width = image.width * scale;
			var height = image.height * scale;
			var minX = size - width;
			var minY = size - height;

			offsetX = Math.min(0, Math.max(minX, offsetX));
			offsetY = Math.min(0, Math.max(minY, offsetY));
		}

		function draw() {
			if (!image) {
				return;
			}

			clampOffsets();
			var scale = baseScale * zoom;

			ctx.clearRect(0, 0, size, size);
			ctx.drawImage(image, offsetX, offsetY, image.width * scale, image.height * scale);
		}

		function closeModal() {
			modal.hidden = true;
			document.body.classList.remove('gfw-avatar-modal-open');
		}

		function openModal(file) {
			var objectUrl = URL.createObjectURL(file);
			var img = new Image();

			img.onload = function () {
				image = img;
				baseScale = Math.max(size / img.width, size / img.height);
				zoom = 1;
				zoomInput.value = '1';
				offsetX = (size - img.width * baseScale) / 2;
				offsetY = (size - img.height * baseScale) / 2;
				draw();

				modal.hidden = false;
				document.body.classList.add('gfw-avatar-modal-open');
				setStatus('', false);
				saveBtn.disabled = false;
			};

			img.onerror = function () {
				setStatus(config.strings.invalid, true);
			};

			img.src = objectUrl;
		}

		uploadBtn.addEventListener('click', function () {
			fileInput.click();
		});

		fileInput.addEventListener('change', function () {
			if (fileInput.files && fileInput.files[0]) {
				openModal(fileInput.files[0]);
			} else {
				setStatus('', false);
			}

			fileInput.value = '';
		});

		zoomInput.addEventListener('input', function () {
			zoom = parseFloat(zoomInput.value) || 1;
			draw();
		});

		canvas.addEventListener('pointerdown', function (event) {
			dragFrom = { x: event.clientX, y: event.clientY };
			canvas.setPointerCapture(event.pointerId);
		});

		canvas.addEventListener('pointermove', function (event) {
			if (!dragFrom) {
				return;
			}

			offsetX += event.clientX - dragFrom.x;
			offsetY += event.clientY - dragFrom.y;
			dragFrom = { x: event.clientX, y: event.clientY };
			draw();
		});

		function stopDrag() {
			dragFrom = null;
		}

		canvas.addEventListener('pointerup', stopDrag);
		canvas.addEventListener('pointercancel', stopDrag);

		cancelBtn.addEventListener('click', closeModal);
		modal.addEventListener('click', function (event) {
			if (event.target === modal) {
				closeModal();
			}
		});
		document.addEventListener('keydown', function (event) {
			if (!modal.hidden && event.key === 'Escape') {
				closeModal();
			}
		});

		saveBtn.addEventListener('click', function () {
			if (!image) {
				return;
			}

			saveBtn.disabled = true;
			setStatus(config.strings.saving, false);

			var output = document.createElement('canvas');
			output.width = 400;
			output.height = 400;
			var outputCtx = output.getContext('2d');
			var scale = baseScale * zoom;
			var ratio = output.width / size;

			outputCtx.drawImage(
				image,
				offsetX * ratio,
				offsetY * ratio,
				image.width * scale * ratio,
				image.height * scale * ratio
			);

			output.toBlob(function (blob) {
				if (!blob) {
					saveBtn.disabled = false;
					setStatus(config.strings.failed, true);
					return;
				}

				var data = new FormData();
				data.append('action', 'giftflow_upload_donor_avatar');
				data.append('nonce', config.nonce);
				data.append('avatar', blob, 'avatar.jpg');

				fetch(config.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: data })
					.then(function (response) { return response.json(); })
					.then(function (result) {
						if (!result || !result.success) {
							var message = result && typeof result.data === 'string' ? result.data : (result && result.data && result.data.message);
							throw new Error(message || config.strings.failed);
						}

						preview.src = result.data.url;
						resetBtn.hidden = false;
						saveBtn.disabled = false;
						closeModal();
						setStatus(config.strings.saved, false);
					})
					.catch(function (error) {
						saveBtn.disabled = false;
						setStatus(error.message || config.strings.failed, true);
					});
			}, 'image/jpeg', 0.92);
		});

		resetBtn.addEventListener('click', function () {
			resetBtn.disabled = true;
			setStatus(config.strings.saving, false);

			var data = new FormData();
			data.append('action', 'giftflow_remove_donor_avatar');
			data.append('nonce', config.nonce);

			fetch(config.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: data })
				.then(function (response) { return response.json(); })
				.then(function (result) {
					if (!result || !result.success) {
						throw new Error(config.strings.failed);
					}

					preview.src = result.data.url;
					resetBtn.hidden = true;
					setStatus(config.strings.removed, false);
				})
				.catch(function (error) {
					setStatus(error.message || config.strings.failed, true);
				})
				.finally(function () {
					resetBtn.disabled = false;
				});
		});
	})();
	</script>
	<?php
}