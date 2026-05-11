<?php
/**
 * Plugin settings page using the WordPress Settings API.
 *
 * @package AskQuote
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders and processes the AskQuote settings page.
 */
class Askquote_Settings_Page {

	/**
	 * Option name used to store all settings.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'askquote_settings';

	/**
	 * Register settings, sections, and fields with the WordPress Settings API.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'askquote_settings_group',
			self::OPTION_NAME,
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
			)
		);

		// --- Section: General ---
		add_settings_section(
			'askquote_section_general',
			__( 'General Settings', 'askquote-for-woocommerce' ),
			'__return_false',
			'askquote_settings'
		);

		add_settings_field(
			'button_visibility',
			__( 'Button Visibility', 'askquote-for-woocommerce' ),
			array( $this, 'field_button_visibility' ),
			'askquote_settings',
			'askquote_section_general'
		);

		add_settings_field(
			'button_label',
			__( 'Button Label', 'askquote-for-woocommerce' ),
			array( $this, 'field_button_label' ),
			'askquote_settings',
			'askquote_section_general'
		);

		add_settings_field(
			'button_color',
			__( 'Button Color', 'askquote-for-woocommerce' ),
			array( $this, 'field_button_color' ),
			'askquote_settings',
			'askquote_section_general'
		);

		// --- Section: Emails ---
		add_settings_section(
			'askquote_section_emails',
			__( 'Email Settings', 'askquote-for-woocommerce' ),
			'__return_false',
			'askquote_settings'
		);

		add_settings_field(
			'admin_email',
			__( 'Admin Notification Email', 'askquote-for-woocommerce' ),
			array( $this, 'field_admin_email' ),
			'askquote_settings',
			'askquote_section_emails'
		);

		add_settings_field(
			'subject_received',
			__( 'Subject: Quote Received', 'askquote-for-woocommerce' ),
			array( $this, 'field_subject_received' ),
			'askquote_settings',
			'askquote_section_emails'
		);

		add_settings_field(
			'subject_approved',
			__( 'Subject: Quote Approved', 'askquote-for-woocommerce' ),
			array( $this, 'field_subject_approved' ),
			'askquote_settings',
			'askquote_section_emails'
		);

		// --- Section: Display ---
		add_settings_section(
			'askquote_section_display',
			__( 'Display Settings', 'askquote-for-woocommerce' ),
			'__return_false',
			'askquote_settings'
		);

		add_settings_field(
			'remove_data_on_uninstall',
			__( 'Remove Data on Uninstall', 'askquote-for-woocommerce' ),
			array( $this, 'field_remove_data_on_uninstall' ),
			'askquote_settings',
			'askquote_section_display'
		);
	}

	/**
	 * Render the full settings page.
	 *
	 * @return void
	 */
	public function render_page() {
		$this->maybe_show_advanced_notice();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'AskQuote Settings', 'askquote-for-woocommerce' ); ?></h1>
			<?php settings_errors( 'askquote_settings' ); ?>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'askquote_settings_group' );
				do_settings_sections( 'askquote_settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Show dismissible notice about advanced features (once per user).
	 *
	 * @return void
	 */
	private function maybe_show_advanced_notice() {
		$user_id   = get_current_user_id();
		$dismissed = get_user_meta( $user_id, 'askquote_advanced_notice_dismissed', true );

		if ( $dismissed ) {
			return;
		}
		?>
		<div class="notice notice-info is-dismissible askquote-advanced-notice" data-nonce="<?php echo esc_attr( wp_create_nonce( 'askquote_dismiss_notice' ) ); ?>">
			<p>
				<?php esc_html_e( 'Advanced features are available separately, including bulk quote export, custom pricing rules, and more. Visit our website to learn more.', 'askquote-for-woocommerce' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * AJAX handler to dismiss the advanced features notice.
	 *
	 * @return void
	 */
	public function ajax_dismiss_notice() {
		check_ajax_referer( 'askquote_dismiss_notice', 'nonce' );

		$user_id = get_current_user_id();
		if ( $user_id ) {
			update_user_meta( $user_id, 'askquote_advanced_notice_dismissed', '1' );
		}

		wp_send_json_success();
	}

	/**
	 * Sanitize all settings before saving.
	 *
	 * @param array $input Raw input from the settings form.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		$allowed_visibility = array( 'all_products', 'by_category', 'by_tag', 'per_product' );
		$visibility         = isset( $input['button_visibility'] ) ? sanitize_key( $input['button_visibility'] ) : 'all_products';
		$sanitized['button_visibility'] = in_array( $visibility, $allowed_visibility, true ) ? $visibility : 'all_products';

		$sanitized['button_label']   = isset( $input['button_label'] ) ? sanitize_text_field( $input['button_label'] ) : __( 'Request Quote', 'askquote-for-woocommerce' );
		$sanitized['button_color']   = isset( $input['button_color'] ) ? sanitize_hex_color( $input['button_color'] ) : '#0071a1';
		$sanitized['admin_email']    = isset( $input['admin_email'] ) ? sanitize_email( $input['admin_email'] ) : get_option( 'admin_email' );
		$sanitized['subject_received'] = isset( $input['subject_received'] ) ? sanitize_text_field( $input['subject_received'] ) : '';
		$sanitized['subject_approved'] = isset( $input['subject_approved'] ) ? sanitize_text_field( $input['subject_approved'] ) : '';
		$sanitized['remove_data_on_uninstall'] = ! empty( $input['remove_data_on_uninstall'] ) ? 'yes' : 'no';

		return $sanitized;
	}

	// --- Field render callbacks ---

	/**
	 * Render the button_visibility field.
	 *
	 * @return void
	 */
	public function field_button_visibility() {
		$value   = askquote_get_setting( 'button_visibility', 'all_products' );
		$options = array(
			'all_products' => __( 'All Products', 'askquote-for-woocommerce' ),
			'by_category'  => __( 'By Category', 'askquote-for-woocommerce' ),
			'by_tag'       => __( 'By Tag', 'askquote-for-woocommerce' ),
			'per_product'  => __( 'Per Product (manual)', 'askquote-for-woocommerce' ),
		);
		?>
		<select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[button_visibility]">
			<?php foreach ( $options as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<p class="description"><?php esc_html_e( 'Choose on which products the quote button appears.', 'askquote-for-woocommerce' ); ?></p>
		<?php
	}

	/**
	 * Render the button_label field.
	 *
	 * @return void
	 */
	public function field_button_label() {
		$value = askquote_get_setting( 'button_label', __( 'Request Quote', 'askquote-for-woocommerce' ) );
		?>
		<input type="text"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[button_label]"
			value="<?php echo esc_attr( $value ); ?>"
			class="regular-text">
		<?php
	}

	/**
	 * Render the button_color field.
	 *
	 * @return void
	 */
	public function field_button_color() {
		$value = askquote_get_setting( 'button_color', '#0071a1' );
		?>
		<input type="text"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[button_color]"
			value="<?php echo esc_attr( $value ); ?>"
			class="askquote-color-picker"
			data-default-color="#0071a1">
		<?php
	}

	/**
	 * Render the admin_email field.
	 *
	 * @return void
	 */
	public function field_admin_email() {
		$value = askquote_get_setting( 'admin_email', get_option( 'admin_email' ) );
		?>
		<input type="email"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[admin_email]"
			value="<?php echo esc_attr( $value ); ?>"
			class="regular-text">
		<p class="description"><?php esc_html_e( 'Admin receives new quote submission notifications at this address.', 'askquote-for-woocommerce' ); ?></p>
		<?php
	}

	/**
	 * Render the subject_received field.
	 *
	 * @return void
	 */
	public function field_subject_received() {
		$value = askquote_get_setting( 'subject_received', __( 'Your quote request has been received', 'askquote-for-woocommerce' ) );
		?>
		<input type="text"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[subject_received]"
			value="<?php echo esc_attr( $value ); ?>"
			class="large-text">
		<?php
	}

	/**
	 * Render the subject_approved field.
	 *
	 * @return void
	 */
	public function field_subject_approved() {
		$value = askquote_get_setting( 'subject_approved', __( 'Your quote has been approved', 'askquote-for-woocommerce' ) );
		?>
		<input type="text"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[subject_approved]"
			value="<?php echo esc_attr( $value ); ?>"
			class="large-text">
		<?php
	}

	/**
	 * Render the remove_data_on_uninstall checkbox.
	 *
	 * @return void
	 */
	public function field_remove_data_on_uninstall() {
		$value = askquote_get_setting( 'remove_data_on_uninstall', 'no' );
		?>
		<label>
			<input type="checkbox"
				name="<?php echo esc_attr( self::OPTION_NAME ); ?>[remove_data_on_uninstall]"
				value="yes"
				<?php checked( $value, 'yes' ); ?>>
			<?php esc_html_e( 'Remove all plugin data when the plugin is uninstalled.', 'askquote-for-woocommerce' ); ?>
		</label>
		<p class="description askquote-warning"><?php esc_html_e( 'Warning: This will permanently delete all quotes and settings.', 'askquote-for-woocommerce' ); ?></p>
		<?php
	}
}
