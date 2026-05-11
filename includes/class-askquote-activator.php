<?php
/**
 * Fired during plugin activation.
 *
 * @package AskQuote
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles everything that happens during plugin activation.
 */
class Askquote_Activator {

	/**
	 * Run activation tasks: create DB tables, set defaults, flush rewrites.
	 *
	 * @return void
	 */
	public static function activate() {
		self::create_tables();
		self::set_default_options();

		// Register CPT so rewrite flush works.
		if ( ! post_type_exists( 'askquote_quote' ) ) {
			require_once ASKQUOTE_PLUGIN_DIR . 'includes/post-types/class-quote-cpt.php';
			$cpt = new Askquote_Quote_CPT();
			$cpt->register();
		}

		flush_rewrite_rules();
	}

	/**
	 * Create the quote items custom DB table.
	 *
	 * @return void
	 */
	private static function create_tables() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'askquote_quote_items';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			quote_id BIGINT UNSIGNED NOT NULL,
			product_id BIGINT UNSIGNED NOT NULL,
			variation_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			quantity INT NOT NULL DEFAULT 1,
			meta LONGTEXT,
			PRIMARY KEY (id),
			KEY quote_id (quote_id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'askquote_db_version', ASKQUOTE_VERSION );
	}

	/**
	 * Set default plugin options on first activation.
	 *
	 * @return void
	 */
	private static function set_default_options() {
		$defaults = array(
			'button_visibility'        => 'all_products',
			'button_label'             => __( 'Request Quote', 'askquote-for-woocommerce' ),
			'button_color'             => '#0071a1',
			'admin_email'              => get_option( 'admin_email' ),
			'subject_received'         => __( 'Your quote request has been received', 'askquote-for-woocommerce' ),
			'subject_approved'         => __( 'Your quote has been approved', 'askquote-for-woocommerce' ),
			'remove_data_on_uninstall' => 'no',
		);

		if ( ! get_option( 'askquote_settings' ) ) {
			add_option( 'askquote_settings', $defaults );
		}
	}
}
