<?php
/**
 * GiftFlow Options
 *
 * @package GiftFlow
 * @subpackage Admin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// add custom nav item under plugin title in WordPress admin plugin list.
add_filter( 'plugin_action_links_' . GIFTFLOW_PLUGIN_BASENAME, 'giftflow_add_settings_link' );

/**
 * Add settings link to plugin action links.

 * @param array $links The plugin action links.
 * @return array The plugin action links.
 */
function giftflow_add_settings_link( $links ) {
	$settings_link = '<a href="admin.php?page=giftflow-settings">' . __( 'Settings', 'giftflow' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}

// add submenu page to giftflow-dashboard menu.
add_action( 'admin_menu', 'giftflow_add_settings_page', 30 );

/**
 * Add settings page to giftflow-dashboard menu.

 * @return void
 */
function giftflow_add_settings_page() {
	add_submenu_page(
		'giftflow-dashboard',
		'GiftFlow Settings',
		'Settings',
		'manage_options',
		'giftflow-settings',
		'giftflow_settings_page'
	);
}

/**
 * Display the settings page.
 *
 * @return void
 */
function giftflow_settings_page() {
	// Check user capabilities.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Get active tab.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';

	// build array of tabs.
	$tabs = apply_filters(
		'giftflow_settings_tabs',
		array(
			'general' => __( 'General', 'giftflow' ),
			'payment' => __( 'Payment', 'giftflow' ),
			'email'   => __( 'Email', 'giftflow' ),
			'options_with_api_keys' => __( 'Options with API Keys', 'giftflow' ),
		)
	);
	?>
	<div class="giftflow_page_giftflow-settings">
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<h2 class="nav-tab-wrapper">
				<?php foreach ( $tabs as $tab_key => $tab_label ) : ?>
					<a href="?page=giftflow-settings&tab=<?php echo esc_attr( $tab_key ); ?>" class="nav-tab <?php echo $active_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
						<?php echo esc_html( $tab_label ); ?>
					</a>
				<?php endforeach; ?>
			</h2>
			<form method="post" action="options.php">
				<?php
				switch ( $active_tab ) {
					case 'general':
						settings_fields( 'giftflow_general_options' );
						do_settings_sections( 'giftflow-dashboard' );
						break;
					case 'payment':
						settings_fields( 'giftflow_payment_options' );
						do_settings_sections( 'giftflow-payment' );
						break;
					case 'email':
						settings_fields( 'giftflow_email_options' );
						do_settings_sections( 'giftflow-email' );
						break;
					case 'options_with_api_keys':
						settings_fields( 'giftflow_options_with_api_keys_options' );
						do_settings_sections( 'giftflow-options-with-api-keys' );
						break;
				}
				do_action( 'giftflow_settings_tabs', $active_tab );
				submit_button( __( 'Save Settings', 'giftflow' ) );
				?>
			</form>
		</div>
	</div>
	<?php
}

/**
 * Initialize plugin settings.

 * @return void
 */
function giftflow_initialize_settings() {
	// Get current options.
	$general_options = get_option( 'giftflow_general_options' );
	$payment_options = get_option( 'giftflow_payment_options' );
	$email_options   = get_option( 'giftflow_email_options' );
	$design_options  = get_option( 'giftflow_design_options' );
	$options_with_api_keys_options = get_option( 'giftflow_options_with_api_keys_options' );

	// Define settings structure.
	$settings = array(
		'general'         => array(
			'option_name'      => 'giftflow_general_options',
			'page'             => 'giftflow-dashboard',
			'section'          => 'giftflow_general_settings',
			'section_title'    => __( 'General Settings', 'giftflow' ),
			'section_callback' => 'giftflow_general_settings_callback',
			'fields'           => array(
				'currency'                => array(
					'id'          => 'giftflow_currency',
					'name'        => 'giftflow_general_options[currency]',
					'type'        => 'select',
					'label'       => __( 'Default Currency', 'giftflow' ),
					'value'       => giftflow_get_current_currency(),
					'options'     => array_combine(
						array_column( giftflow_get_common_currency(), 'code' ),
						array_map(
							function ( $currency ) {
								// countries.
								return $currency['countries'][0] . ' (' . $currency['code'] . ' ' . $currency['symbol'] . ')';
							},
							giftflow_get_common_currency()
						)
					),
					'description' => __( 'Select the default currency for donations', 'giftflow' ),
				),
				// currency template.
				'currency_template'       => array(
					'id'          => 'giftflow_currency_template',
					'name'        => 'giftflow_general_options[currency_template]',
					'type'        => 'text',
					'label'       => __( 'Currency Template', 'giftflow' ),
					'value'       => giftflow_get_currency_template(),
					'description' => __( 'Enter the template for the currency formatted amount (default: {{currency_symbol}} {{amount}})', 'giftflow' ),
				),
				// preset donation amounts.
				'preset_donation_amounts' => array(
					'id'          => 'giftflow_preset_donation_amounts',
					'name'        => 'giftflow_general_options[preset_donation_amounts]',
					'type'        => 'text',
					'label'       => __( 'Preset Donation Amounts', 'giftflow' ),
					'value'       => giftflow_get_preset_donation_amounts(),
					'description' => __( 'Enter the preset donation amounts for the campaign (default: 10, 25, 35)', 'giftflow' ),
				),
				'min_amount'              => array(
					'id'          => 'giftflow_min_amount',
					'name'        => 'giftflow_general_options[min_amount]',
					'type'        => 'currency',
					'label'       => __( 'Minimum Donation Amount', 'giftflow' ),
					'value'       => isset( $general_options['min_amount'] ) ? $general_options['min_amount'] : '1',
					'min'         => '0',
					'step'        => '1',
					'description' => __( 'Set the minimum amount that can be donated', 'giftflow' ),
				),
				// max amount.
				'max_amount'              => array(
					'id'          => 'giftflow_max_amount',
					'name'        => 'giftflow_general_options[max_amount]',
					'type'        => 'currency',
					'label'       => __( 'Maximum Donation Amount', 'giftflow' ),
					'value'       => isset( $general_options['max_amount'] ) ? $general_options['max_amount'] : '1000',
					'min'         => '0',
					'step'        => '1',
					'description' => __( 'Set the maximum amount that can be donated', 'giftflow' ),
				),
				// donor account page.
				'donor_account_page'      => array(
					'id'          => 'giftflow_donor_account_page',
					'name'        => 'giftflow_general_options[donor_account_page]',
					'type'        => 'select',
					'options'     => giftflow_get_pages(),
					'value'       => giftflow_get_donor_account_page(),
					'label'       => __( 'Donor Account Page', 'giftflow' ),
					'description' => __( 'Select the donor account page', 'giftflow' ),
				),
				// thank donor page.
				'thank_donor_page'        => array(
					'id'          => 'giftflow_thank_donor_page',
					'name'        => 'giftflow_general_options[thank_donor_page]',
					'type'        => 'select',
					'options'     => giftflow_get_pages(),
					'value'       => giftflow_get_thank_donor_page(),
					'label'       => __( 'Thank Donor Page', 'giftflow' ),
					'description' => __( 'Select the thank donor page', 'giftflow' ),
				),
			),
		),
		'payment_methods' => giftflow_payment_methods_options(), // payment methods options.
		'email'           => array(
			'option_name'      => 'giftflow_email_options',
			'page'             => 'giftflow-email',
			'section'          => 'giftflow_email_settings',
			'section_title'    => __( 'Email', 'giftflow' ),
			'section_callback' => 'giftflow_email_settings_callback',
			'fields'           => array(
				'email_from_name'     => array(
					'id'          => 'giftflow_email_from_name',
					'name'        => 'giftflow_email_options[email_from_name]',
					'type'        => 'textfield',
					'label'       => __( 'Email From Name', 'giftflow' ),
					'value'       => isset( $email_options['email_from_name'] ) ? $email_options['email_from_name'] : get_bloginfo( 'name' ),
					'description' => __( 'The name that appears in the From field of emails', 'giftflow' ),
				),
				'email_admin_address' => array(
					'id'          => 'giftflow_email_admin_address',
					'name'        => 'giftflow_email_options[email_admin_address]',
					'type'        => 'textfield',
					'label'       => __( 'Email Admin Address', 'giftflow' ),
					'value'       => isset( $email_options['email_admin_address'] ) ? $email_options['email_admin_address'] : get_bloginfo( 'admin_email' ),
					'input_type'  => 'email',
					'description' => __( 'Admin notification email address', 'giftflow' ),
				),
				'html_1'              => array(
					'id'    => 'giftflow_html_1',
					'name'  => 'giftflow_email_options[html_1]',
					'type'  => 'html',
					'html'  => giftflow_test_mail_html(),
					'label' => __( 'Test Email Template', 'giftflow' ),
				),
			),
		),
		'options_with_api_keys' => giftflow_options_with_api_keys_options(), // options with API keys options.
	);

	// apply filter for settings.
	$settings = apply_filters( 'giftflow_general_settings', $settings );

	// Register settings and add sections/fields.
	foreach ( $settings as $section_key => $section ) {
		// Register setting.
		// phpcs:ignore PluginCheck.CodeAnalysis.SettingSanitization.register_settingMissing
		register_setting( $section['option_name'], $section['option_name'] );

		// Add section.
		add_settings_section(
			$section['section'],
			$section['section_title'],
			$section['section_callback'],
			$section['page']
		);

		// Add fields.
		foreach ( $section['fields'] as $field_key => $field ) {
			$field_instance = new GiftFlow_Field(
				$field['id'],
				$field['name'],
				$field['type'],
				array_merge(
					isset( $field['value'] ) ? array( 'value' => $field['value'] ) : array(),
					isset( $field['options'] ) ? array( 'options' => $field['options'] ) : array(),
					isset( $field['description'] ) ? array( 'description' => $field['description'] ) : array(),
					isset( $field['min'] ) ? array( 'min' => $field['min'] ) : array(),
					isset( $field['step'] ) ? array( 'step' => $field['step'] ) : array(),
					isset( $field['rows'] ) ? array( 'rows' => $field['rows'] ) : array(),
					isset( $field['input_type'] ) ? array( 'type' => $field['input_type'] ) : array(),
					isset( $field['accordion_settings'] ) ? array( 'accordion_settings' => $field['accordion_settings'] ) : array(),
					isset( $field['content'] ) ? array( 'content' => $field['content'] ) : array(),
					isset( $field['html'] ) ? array( 'html' => $field['html'] ) : array(),
				)
			);

			add_settings_field(
				$field['id'],
				$field['label'],
				function () use ( $field_instance ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $field_instance->render();
				},
				$section['page'],
				$section['section']
			);
		}
	}
}
add_action( 'admin_init', 'giftflow_initialize_settings' );

/**
 * General settings callback.
 *
 * @return void
 */
function giftflow_general_settings_callback() {
	echo '<p>' . esc_html__( 'Configure general settings for GiftFlow.', 'giftflow' ) . '</p>';
}

/**
 * Payment settings callback.
 *
 * @return void
 */
function giftflow_payment_settings_callback() {
	echo '<p>' . esc_html__( 'Configure payment gateway settings.', 'giftflow' ) . '</p>';
}

/**
 * Email settings callback.
 *
 * @return void
 */
function giftflow_email_settings_callback() {
	echo '<p>' . esc_html__( 'Configure email for notifications.', 'giftflow' ) . '</p>';
}

/**
 * Design settings callback.
 *
 * @return void
 */
function giftflow_design_settings_callback() {
	echo '<p>' . esc_html__( 'Customize the appearance of donation forms.', 'giftflow' ) . '</p>';
}

/**
 * Payment methods options.
 *
 * @return array
 */
function giftflow_payment_methods_options() {
	$payment_options = get_option( 'giftflow_payment_options' );

	/**
	 * Allow developers to customize the payment methods settings.
	 *
	 * @hook giftflow_payment_methods_settings : giftflow_payment_methods_options_stripe:8
	 * @hook giftflow_payment_methods_settings : giftflow_payment_methods_options_paypal:10
	 */
	$payment_fields = apply_filters( 'giftflow_payment_methods_settings', array() );

	return array(
		'option_name'      => 'giftflow_payment_options',
		'page'             => 'giftflow-payment',
		'section'          => 'giftflow_payment_settings',
		'section_title'    => __( 'Payment Methods', 'giftflow' ),
		'section_callback' => 'giftflow_payment_settings_callback',
		'fields'           => $payment_fields,
	);
}

/**
 * Test mail HTML.
 *
 * @return string
 */
function giftflow_test_mail_html() {
	// get mail options.
	$email_opts  = get_option( 'giftflow_email_options' );
	$admin_email = $email_opts['email_admin_address'] ?? get_bloginfo( 'admin_email' );

	// Define the default notification buttons.
	$notification_buttons = array(
		array(
			'id'    => 'admin_new_donation',
			'label' => esc_html__( 'Send: Notification New Donation to Admin', 'giftflow' ),
		),
		array(
			'id'    => 'donor_thanks',
			'label' => esc_html__( 'Send: Thanks Mail to Donor (Donation Successful)', 'giftflow' ),
		),
		array(
			'id'    => 'new_user_first_time_donation',
			'label' => esc_html__( 'Send: New User Account to Donor (First Time Donation)', 'giftflow' ),
		),
	);

	/**
	 * Allow developers to customize or extend the notification buttons.
	 *
	 * @param array $notification_buttons
	 */
	$notification_buttons = apply_filters( 'giftflow_test_email_notification_buttons', $notification_buttons );

	// Render the buttons.
	ob_start();
	echo '<div class="giftflow-email-notification-buttons" style="display: flex; gap: 10px; flex-wrap: wrap;">';
	foreach ( $notification_buttons as $button ) {
		printf(
			'<button 
                type="button" 
                class="button giftflow-email-btn button-primary" 
                id="%s" 
                onclick="window.giftflow.testSendMail_Handle(\'%s\')">%s</button> ',
			esc_attr( $button['id'] ),
			esc_attr( $button['id'] ),
			esc_html( $button['label'] )
		);
	}

	echo '</div>';
	// Add a message to indicate the recipient of the test email.
	echo '<div style="margin-top:10px; color:#666; font-size:13px;">';
	esc_html_e( 'Test emails will be sent to the admin email address above: ', 'giftflow' );
	echo '<u>' . wp_kses_post( $admin_email ) . '</u>';
	echo '</div>';
	return ob_get_clean();
}

/**
 * Get pages.
 *
 * @return array
 */
function giftflow_get_pages() {
	$pages = get_pages();

	// return [key => value].
	return array_combine(
		array_column( $pages, 'ID' ),
		array_map(
			function ( $page ) {
				return $page->post_title . ' (#' . $page->ID . ')';
			},
			$pages
		)
	);
}

/**
 * Options with API keys settings callback.
 *
 * @return void
 */
function giftflow_options_with_api_keys_settings_callback() {
	echo '<p>' . esc_html__( 'Configure options that require API keys for integration with external services. For example, enable features like Google reCAPTCHA, Google Maps, etc. By providing the necessary API keys here.', 'giftflow' ) . '</p>';
}

/**
 * Options with API keys options.
 *
 * @return array
 */
function giftflow_options_with_api_keys_options() {
	$options_with_api_keys_options = get_option( 'giftflow_options_with_api_keys_options' );

	$fields = array(
		'google_recaptcha' => array(
			'id'    => 'giftflow_google_recaptcha',
			'name'  => 'giftflow_options_with_api_keys_options[google_recaptcha]',
			'type'  => 'accordion',
			'label' => __( 'Google reCAPTCHA (v3)', 'giftflow' ),
			'description' => __( 'Configure Google reCAPTCHA (v3) for enhanced spam protection across all forms in this plugin. To get your API keys: visit https://www.google.com/recaptcha/admin/create, sign in with your Google account, register your site, and choose reCAPTCHA v3. Copy your generated Site Key and Secret Key into the fields below.', 'giftflow' ),
			'accordion_settings' => array(
				'label'   => __( 'Google reCAPTCHA Settings', 'giftflow' ),
				'is_open' => true,
				'fields'  => array(
					'google_recaptcha_enabled' => array(
						'id'          => 'giftflow_google_recaptcha_enabled',
						'type'        => 'switch',
						'label'       => __( 'Enable Google reCAPTCHA', 'giftflow' ),
						'value'       => isset( $options_with_api_keys_options['google_recaptcha']['google_recaptcha_enabled'] ) ? $options_with_api_keys_options['google_recaptcha']['google_recaptcha_enabled'] : false,
						'description' => __( 'Turn on to enable Google reCAPTCHA protection on your forms.', 'giftflow' ),
					),
					'google_recaptcha_site_key' => array(
						'id'          => 'giftflow_google_recaptcha_site_key',
						'type'        => 'text',
						'label'       => __( 'Site Key', 'giftflow' ),
						'value'       => isset( $options_with_api_keys_options['google_recaptcha']['google_recaptcha_site_key'] ) ? $options_with_api_keys_options['google_recaptcha']['google_recaptcha_site_key'] : '',
						'description' => __( 'Enter your Google reCAPTCHA Site Key.', 'giftflow' ),
					),
					'google_recaptcha_secret_key' => array(
						'id'          => 'giftflow_google_recaptcha_secret_key',
						'type'        => 'text',
						'label'       => __( 'Secret Key', 'giftflow' ),
						'value'       => isset( $options_with_api_keys_options['google_recaptcha']['google_recaptcha_secret_key'] ) ? $options_with_api_keys_options['google_recaptcha']['google_recaptcha_secret_key'] : '',
						'description' => __( 'Enter your Google reCAPTCHA Secret Key.', 'giftflow' ),
					),
				),
			),
		),
		'google_maps' => array(
			'id'    => 'giftflow_google_maps',
			'name'  => 'giftflow_options_with_api_keys_options[google_maps]',
			'type'  => 'accordion',
			'label' => __( 'Google Maps', 'giftflow' ),
			'description' => __( 'To enable map features in the plugin, you need to create a Google Maps API key: Go to https://console.cloud.google.com/apis/credentials, sign in with your Google account, create a new project if needed, and generate an API key for "Maps JavaScript API". Then copy and paste your API key here.', 'giftflow' ),
			'accordion_settings' => array(
				'label'   => __( 'Google Maps API Settings', 'giftflow' ),
				'is_open' => true,
				'fields'  => array(
					'google_maps_api_key' => array(
						'id'          => 'giftflow_google_maps_api_key',
						'type'        => 'text',
						'label'       => __( 'Google Maps API Key', 'giftflow' ),
						'value'       => isset( $options_with_api_keys_options['google_maps']['google_maps_api_key'] ) ? $options_with_api_keys_options['google_maps']['google_maps_api_key'] : '',
						'description' => __( 'Enter your Google Maps API key.', 'giftflow' ),
					),
				),
			),
		),
	);

	return array(
		'option_name'      => 'giftflow_options_with_api_keys_options',
		'page'             => 'giftflow-options-with-api-keys',
		'section'          => 'giftflow_options_with_api_keys_settings',
		'section_title'    => __( 'Options with API Keys', 'giftflow' ),
		'section_callback' => 'giftflow_options_with_api_keys_settings_callback',
		'fields'           => apply_filters( 'giftflow_options_with_api_keys_settings_fields', $fields, $options_with_api_keys_options ),
	);
}