<?php
/**
 * The core plugin class.
 *
 * @package AskQuote
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class — singleton.
 */
class Askquote {

	/**
	 * The loader instance.
	 *
	 * @var Askquote_Loader
	 */
	protected $loader;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * Singleton instance.
	 *
	 * @var Askquote|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Askquote
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor — private to enforce singleton.
	 */
	private function __construct() {
		$this->version = ASKQUOTE_VERSION;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_frontend_hooks();
		$this->define_api_hooks();
	}

	/**
	 * Load all required class files.
	 *
	 * @return void
	 */
	private function load_dependencies() {
		require_once ASKQUOTE_PLUGIN_DIR . 'includes/class-askquote-loader.php';
		require_once ASKQUOTE_PLUGIN_DIR . 'includes/class-askquote-i18n.php';
		require_once ASKQUOTE_PLUGIN_DIR . 'includes/class-askquote-activator.php';
		require_once ASKQUOTE_PLUGIN_DIR . 'includes/class-askquote-deactivator.php';

		// Helpers.
		require_once ASKQUOTE_PLUGIN_DIR . 'includes/helpers/class-quote-status.php';
		require_once ASKQUOTE_PLUGIN_DIR . 'includes/helpers/functions.php';

		// Post types.
		require_once ASKQUOTE_PLUGIN_DIR . 'includes/post-types/class-quote-cpt.php';

		// Extensibility.
		require_once ASKQUOTE_PLUGIN_DIR . 'includes/extensibility/class-hook-registry.php';

		// Admin.
		require_once ASKQUOTE_PLUGIN_DIR . 'includes/admin/class-admin.php';
		require_once ASKQUOTE_PLUGIN_DIR . 'includes/admin/class-settings-page.php';
		require_once ASKQUOTE_PLUGIN_DIR . 'includes/admin/class-quote-list-table.php';
		require_once ASKQUOTE_PLUGIN_DIR . 'includes/admin/class-quote-meta-box.php';

		// Frontend.
		require_once ASKQUOTE_PLUGIN_DIR . 'includes/frontend/class-quote-button.php';
		require_once ASKQUOTE_PLUGIN_DIR . 'includes/frontend/class-quote-cart.php';
		require_once ASKQUOTE_PLUGIN_DIR . 'includes/frontend/class-quote-form.php';
		require_once ASKQUOTE_PLUGIN_DIR . 'includes/frontend/class-quote-page.php';

		// Emails.
		require_once ASKQUOTE_PLUGIN_DIR . 'includes/emails/class-email-manager.php';
		require_once ASKQUOTE_PLUGIN_DIR . 'includes/emails/class-customer-quote-received.php';
		require_once ASKQUOTE_PLUGIN_DIR . 'includes/emails/class-admin-quote-submitted.php';
		require_once ASKQUOTE_PLUGIN_DIR . 'includes/emails/class-customer-quote-approved.php';

		// REST API.
		require_once ASKQUOTE_PLUGIN_DIR . 'includes/api/class-rest-api.php';

		$this->loader = new Askquote_Loader();
	}

	/**
	 * Set the plugin locale for i18n.
	 *
	 * @return void
	 */
	private function set_locale() {
		$plugin_i18n = new Askquote_I18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register admin-area hooks.
	 *
	 * @return void
	 */
	private function define_admin_hooks() {
		$admin = new Askquote_Admin( $this->version );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $admin, 'add_menu_pages' );

		// CPT & statuses.
		$cpt = new Askquote_Quote_CPT();
		$this->loader->add_action( 'init', $cpt, 'register' );
		$this->loader->add_action( 'init', $cpt, 'register_statuses' );

		// Settings page.
		$settings = new Askquote_Settings_Page();
		$this->loader->add_action( 'admin_init', $settings, 'register_settings' );
		$this->loader->add_action( 'wp_ajax_askquote_dismiss_notice', $settings, 'ajax_dismiss_notice' );

		// Meta box.
		$meta_box = new Askquote_Quote_Meta_Box();
		$this->loader->add_action( 'add_meta_boxes', $meta_box, 'add_meta_boxes' );
		$this->loader->add_action( 'save_post_askquote_quote', $meta_box, 'save_meta_box', 10, 2 );

		// Emails.
		$email_manager = new Askquote_Email_Manager();
		$this->loader->add_filter( 'woocommerce_email_classes', $email_manager, 'add_emails' );
	}

	/**
	 * Register frontend hooks.
	 *
	 * @return void
	 */
	private function define_frontend_hooks() {
		// Quote button.
		$quote_button = new Askquote_Quote_Button();
		$this->loader->add_action( 'init', $quote_button, 'register_shortcode' );
		$this->loader->add_action( 'woocommerce_after_add_to_cart_button', $quote_button, 'render_button_single' );
		$this->loader->add_action( 'woocommerce_after_shop_loop_item', $quote_button, 'render_button_loop', 15 );
		$this->loader->add_action( 'wp_enqueue_scripts', $quote_button, 'enqueue_assets' );

		// Quote cart.
		$quote_cart = new Askquote_Quote_Cart();
		$this->loader->add_action( 'wp_ajax_askquote_add_to_quote', $quote_cart, 'ajax_add_to_quote' );
		$this->loader->add_action( 'wp_ajax_nopriv_askquote_add_to_quote', $quote_cart, 'ajax_add_to_quote' );
		$this->loader->add_action( 'wp_ajax_askquote_remove_from_quote', $quote_cart, 'ajax_remove_from_quote' );
		$this->loader->add_action( 'wp_ajax_nopriv_askquote_remove_from_quote', $quote_cart, 'ajax_remove_from_quote' );
		$this->loader->add_action( 'wp_ajax_askquote_update_quote_qty', $quote_cart, 'ajax_update_quantity' );
		$this->loader->add_action( 'wp_ajax_nopriv_askquote_update_quote_qty', $quote_cart, 'ajax_update_quantity' );

		// Quote form.
		$quote_form = new Askquote_Quote_Form();
		$this->loader->add_action( 'init', $quote_form, 'register_shortcode' );
		$this->loader->add_action( 'template_redirect', $quote_form, 'handle_submission' );

		// Quote page / My Account.
		$quote_page = new Askquote_Quote_Page();
		$this->loader->add_action( 'init', $quote_page, 'add_endpoints' );
		$this->loader->add_action( 'init', $quote_page, 'register_shortcode' );
		$this->loader->add_filter( 'woocommerce_account_menu_items', $quote_page, 'add_account_menu_item' );
		$this->loader->add_action( 'woocommerce_account_quotes_endpoint', $quote_page, 'my_account_quotes' );
	}

	/**
	 * Register REST API hooks.
	 *
	 * @return void
	 */
	private function define_api_hooks() {
		$rest_api = new Askquote_REST_API();
		$this->loader->add_action( 'rest_api_init', $rest_api, 'register_routes' );
	}

	/**
	 * Run the plugin — registers all hooks with WordPress.
	 *
	 * @return void
	 */
	public function run() {
		$this->loader->run();
		do_action( 'askquote_loaded' );
	}

	/**
	 * Get the plugin version.
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Get the loader instance.
	 *
	 * @return Askquote_Loader
	 */
	public function get_loader() {
		return $this->loader;
	}
}
