<?php
/**
 * Plugin Name: AskQuote for WooCommerce
 * Plugin URI: https://hub.arunrajiah.com/askquote
 * Description: Request-a-quote system for WooCommerce stores. Customers can build a quote cart and submit quote requests.
 * Version: 0.1.0
 * Author: Arun Rajiah
 * Author URI: https://hub.arunrajiah.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: askquote-for-woocommerce
 * Domain Path: /languages
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * WC requires at least: 7.0
 * WC tested up to: 8.5
 *
 * @package AskQuote
 */

defined( 'ABSPATH' ) || exit;

// Plugin constants.
define( 'ASKQUOTE_VERSION', '0.1.0' );
define( 'ASKQUOTE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ASKQUOTE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ASKQUOTE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Declare HPOS compatibility.
 */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

/**
 * Check if WooCommerce is active.
 *
 * @return bool
 */
function askquote_is_woocommerce_active() {
	return class_exists( 'WooCommerce' );
}

/**
 * Show admin notice if WooCommerce is not active.
 */
function askquote_woocommerce_missing_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				/* translators: %s: WooCommerce plugin name */
				esc_html__( 'AskQuote for WooCommerce requires %s to be installed and active.', 'askquote-for-woocommerce' ),
				'<strong>WooCommerce</strong>'
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Main plugin init function — returns the singleton Askquote instance.
 *
 * @return Askquote|null
 */
function askquote_for_woocommerce() {
	if ( ! askquote_is_woocommerce_active() ) {
		add_action( 'admin_notices', 'askquote_woocommerce_missing_notice' );
		return null;
	}

	require_once ASKQUOTE_PLUGIN_DIR . 'includes/class-askquote.php';

	return Askquote::get_instance();
}

add_action( 'plugins_loaded', 'askquote_for_woocommerce' );

/**
 * Plugin activation hook.
 */
function askquote_activate() {
	require_once ASKQUOTE_PLUGIN_DIR . 'includes/class-askquote-activator.php';
	Askquote_Activator::activate();
}
register_activation_hook( __FILE__, 'askquote_activate' );

/**
 * Plugin deactivation hook.
 */
function askquote_deactivate() {
	require_once ASKQUOTE_PLUGIN_DIR . 'includes/class-askquote-deactivator.php';
	Askquote_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'askquote_deactivate' );
