<?php
/**
 * Quote cart page and My Account endpoint.
 *
 * @package AskQuote
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the [askquote_cart] shortcode and WooCommerce My Account endpoint.
 */
class Askquote_Quote_Page {

	/**
	 * Add WooCommerce rewrite endpoint for /my-account/quotes.
	 *
	 * @return void
	 */
	public function add_endpoints() {
		add_rewrite_endpoint( 'quotes', EP_ROOT | EP_PAGES );
	}

	/**
	 * Register the [askquote_cart] shortcode.
	 *
	 * @return void
	 */
	public function register_shortcode() {
		add_shortcode( 'askquote_cart', array( $this, 'shortcode_cart' ) );
	}

	/**
	 * Shortcode callback for [askquote_cart].
	 *
	 * @param array $atts Shortcode attributes (unused).
	 * @return string HTML output.
	 */
	public function shortcode_cart( $atts ) {
		$atts = shortcode_atts( array(), $atts, 'askquote_cart' );
		ob_start();
		$this->render_cart();
		return ob_get_clean();
	}

	/**
	 * Render the quote cart via template.
	 *
	 * @return void
	 */
	public function render_cart() {
		$quote_cart = new Askquote_Quote_Cart();
		$cart_items = $quote_cart->get_items();

		$template = ASKQUOTE_PLUGIN_DIR . 'templates/frontend/quote-cart.php';
		if ( file_exists( $template ) ) {
			include $template;
		}
	}

	/**
	 * Add "Quotes" to the WooCommerce My Account navigation.
	 *
	 * @param array $items Current menu items.
	 * @return array Modified menu items.
	 */
	public function add_account_menu_item( $items ) {
		// Insert before the logout link.
		$logout = isset( $items['customer-logout'] ) ? $items['customer-logout'] : null;
		unset( $items['customer-logout'] );

		$items['quotes'] = __( 'My Quotes', 'askquote-for-woocommerce' );

		if ( $logout ) {
			$items['customer-logout'] = $logout;
		}

		return $items;
	}

	/**
	 * Render the My Account quotes list.
	 *
	 * @return void
	 */
	public function my_account_quotes() {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wc_add_notice( __( 'Please log in to view your quotes.', 'askquote-for-woocommerce' ), 'error' );
			return;
		}

		$quotes   = askquote_get_quotes_by_user( $user_id );
		$template = ASKQUOTE_PLUGIN_DIR . 'templates/frontend/my-account-quotes.php';
		if ( file_exists( $template ) ) {
			include $template;
		}
	}
}
