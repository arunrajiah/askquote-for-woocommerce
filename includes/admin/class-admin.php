<?php
/**
 * Admin-area functionality.
 *
 * @package AskQuote
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles admin enqueuing and menu registration.
 */
class Askquote_Admin {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * List of our admin screen IDs.
	 *
	 * @var array
	 */
	private $plugin_screens = array();

	/**
	 * Constructor.
	 *
	 * @param string $version Plugin version.
	 */
	public function __construct( $version ) {
		$this->version = $version;
	}

	/**
	 * Enqueue admin stylesheets on plugin-specific pages.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_styles( $hook ) {
		if ( ! $this->is_plugin_screen( $hook ) ) {
			return;
		}

		wp_enqueue_style(
			'askquote-admin',
			ASKQUOTE_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			$this->version
		);

		wp_enqueue_style( 'wp-color-picker' );
	}

	/**
	 * Enqueue admin scripts on plugin-specific pages.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {
		if ( ! $this->is_plugin_screen( $hook ) ) {
			return;
		}

		wp_enqueue_script(
			'askquote-admin',
			ASKQUOTE_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery', 'wp-color-picker' ),
			$this->version,
			true
		);

		wp_localize_script(
			'askquote-admin',
			'askquoteAdmin',
			array(
				'ajaxUrl'           => admin_url( 'admin-ajax.php' ),
				'nonce'             => wp_create_nonce( 'askquote_admin_nonce' ),
				'dismissNonce'      => wp_create_nonce( 'askquote_dismiss_notice' ),
				'confirmStatusChange' => esc_html__( 'Are you sure you want to change this quote\'s status?', 'askquote-for-woocommerce' ),
				'confirmDelete'     => esc_html__( 'Are you sure you want to delete the selected quotes? This cannot be undone.', 'askquote-for-woocommerce' ),
			)
		);
	}

	/**
	 * Register the top-level AskQuote admin menu and subpages.
	 *
	 * @return void
	 */
	public function add_menu_pages() {
		$default_items = array(
			'quotes'   => array(
				'title'      => __( 'Quotes', 'askquote-for-woocommerce' ),
				'capability' => 'manage_woocommerce',
				'callback'   => array( $this, 'render_quotes_page' ),
				'position'   => 0,
			),
			'settings' => array(
				'title'      => __( 'Settings', 'askquote-for-woocommerce' ),
				'capability' => 'manage_options',
				'callback'   => array( $this, 'render_settings_page' ),
				'position'   => 10,
			),
		);

		$items = apply_filters( 'askquote_admin_menu_items', $default_items );

		// Add top-level menu.
		$top_hook = add_menu_page(
			__( 'AskQuote', 'askquote-for-woocommerce' ),
			__( 'AskQuote', 'askquote-for-woocommerce' ),
			'manage_woocommerce',
			'askquote',
			array( $this, 'render_quotes_page' ),
			'dashicons-cart',
			56
		);
		$this->plugin_screens[] = $top_hook;

		// Add subpages.
		uasort(
			$items,
			function ( $a, $b ) {
				return ( $a['position'] ?? 0 ) - ( $b['position'] ?? 0 );
			}
		);

		foreach ( $items as $slug => $item ) {
			$hook = add_submenu_page(
				'askquote',
				esc_html( $item['title'] ),
				esc_html( $item['title'] ),
				$item['capability'],
				'askquote-' . sanitize_key( $slug ),
				$item['callback']
			);
			$this->plugin_screens[] = $hook;
		}
	}

	/**
	 * Render the quotes list page.
	 *
	 * @return void
	 */
	public function render_quotes_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'askquote-for-woocommerce' ) );
		}

		$list_table = new Askquote_Quote_List_Table();
		$list_table->prepare_items();
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Quotes', 'askquote-for-woocommerce' ); ?></h1>
			<hr class="wp-header-end">
			<?php $list_table->views(); ?>
			<form method="get">
				<input type="hidden" name="page" value="askquote">
				<?php
				$list_table->search_box( esc_html__( 'Search Quotes', 'askquote-for-woocommerce' ), 'askquote-quote' );
				$list_table->display();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'askquote-for-woocommerce' ) );
		}

		$settings_page = new Askquote_Settings_Page();
		$settings_page->render_page();
	}

	/**
	 * Check whether the current admin screen is one of the plugin's own screens.
	 *
	 * @param string $hook Current admin page hook suffix.
	 * @return bool
	 */
	private function is_plugin_screen( $hook ) {
		// Also include the edit screen for askquote_quote CPT.
		$cpt_screens = array( 'post.php', 'post-new.php', 'edit.php' );
		if ( in_array( $hook, $cpt_screens, true ) ) {
			$screen = get_current_screen();
			if ( $screen && 'askquote_quote' === $screen->post_type ) {
				return true;
			}
		}

		return in_array( $hook, $this->plugin_screens, true ) ||
			   strpos( $hook, 'askquote' ) !== false;
	}
}
