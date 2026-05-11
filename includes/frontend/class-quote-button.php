<?php
/**
 * Quote button for product pages and shop loop.
 *
 * @package AskQuote
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles rendering the "Request Quote" button on products.
 */
class Askquote_Quote_Button {

	/**
	 * Enqueue frontend assets.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		wp_enqueue_style(
			'askquote-frontend',
			ASKQUOTE_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			ASKQUOTE_VERSION
		);

		wp_enqueue_script(
			'askquote-frontend',
			ASKQUOTE_PLUGIN_URL . 'assets/js/frontend.js',
			array( 'jquery' ),
			ASKQUOTE_VERSION,
			true
		);

		wp_localize_script(
			'askquote-frontend',
			'askquoteFrontend',
			array(
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'nonce'     => wp_create_nonce( 'askquote_frontend_nonce' ),
				'addedText' => esc_html__( 'Added to quote!', 'askquote-for-woocommerce' ),
				'errorText' => esc_html__( 'Something went wrong. Please try again.', 'askquote-for-woocommerce' ),
				'cartUrl'   => esc_url( $this->get_quote_cart_url() ),
			)
		);
	}

	/**
	 * Register the [askquote_button] shortcode.
	 *
	 * @return void
	 */
	public function register_shortcode() {
		add_shortcode( 'askquote_button', array( $this, 'shortcode_callback' ) );
	}

	/**
	 * Shortcode callback for [askquote_button product_id="123"].
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function shortcode_callback( $atts ) {
		$atts = shortcode_atts(
			array( 'product_id' => 0 ),
			$atts,
			'askquote_button'
		);

		$product_id = absint( $atts['product_id'] );
		if ( ! $product_id ) {
			return '';
		}

		ob_start();
		$this->render_button( $product_id );
		return ob_get_clean();
	}

	/**
	 * Render the button on the single product page.
	 *
	 * @return void
	 */
	public function render_button_single() {
		global $product;
		if ( ! $product ) {
			return;
		}
		$this->render_button( $product->get_id() );
	}

	/**
	 * Render the button in the shop loop.
	 *
	 * @return void
	 */
	public function render_button_loop() {
		global $product;
		if ( ! $product ) {
			return;
		}
		$this->render_button( $product->get_id() );
	}

	/**
	 * Check visibility and render button for a product.
	 *
	 * @param int $product_id Product ID.
	 * @return void
	 */
	public function render_button( $product_id ) {
		$product_id = absint( $product_id );
		if ( ! $this->maybe_show_button( $product_id ) ) {
			return;
		}

		do_action( 'askquote_before_quote_button', $product_id );

		$label    = askquote_get_setting( 'button_label', __( 'Request Quote', 'askquote-for-woocommerce' ) );
		$cart_url = $this->get_quote_cart_url();

		$args = array(
			'product_id' => $product_id,
			'label'      => $label,
			'cart_url'   => $cart_url,
		);

		$html = $this->get_button_html( $args );
		$html = apply_filters( 'askquote_quote_button_html', $html, $product_id );

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML escaped inside template.

		do_action( 'askquote_after_quote_button', $product_id );
	}

	/**
	 * Get the button HTML from template.
	 *
	 * @param array $args Template args.
	 * @return string
	 */
	private function get_button_html( $args ) {
		ob_start();
		$template = ASKQUOTE_PLUGIN_DIR . 'templates/frontend/quote-button.php';
		if ( file_exists( $template ) ) {
			include $template;
		}
		return ob_get_clean();
	}

	/**
	 * Determine whether the button should be shown for the given product.
	 *
	 * @param int $product_id Product ID.
	 * @return bool
	 */
	public function maybe_show_button( $product_id ) {
		$product_id = absint( $product_id );
		$product    = wc_get_product( $product_id );

		if ( ! $product || ! $product->is_visible() ) {
			$visible = false;
			return apply_filters( 'askquote_quote_button_visible', false, $product_id );
		}

		$visibility = askquote_get_setting( 'button_visibility', 'all_products' );
		$visible    = false;

		switch ( $visibility ) {
			case 'all_products':
				$visible = true;
				break;

			case 'per_product':
				$visible = 'yes' === get_post_meta( $product_id, '_askquote_enable_button', true );
				break;

			case 'by_category':
				$category_ids = get_option( 'askquote_button_categories', array() );
				if ( ! empty( $category_ids ) && has_term( $category_ids, 'product_cat', $product_id ) ) {
					$visible = true;
				}
				break;

			case 'by_tag':
				$tag_ids = get_option( 'askquote_button_tags', array() );
				if ( ! empty( $tag_ids ) && has_term( $tag_ids, 'product_tag', $product_id ) ) {
					$visible = true;
				}
				break;
		}

		return apply_filters( 'askquote_quote_button_visible', $visible, $product_id );
	}

	/**
	 * Get the URL to the quote cart page.
	 *
	 * @return string
	 */
	private function get_quote_cart_url() {
		$page_id = get_option( 'askquote_cart_page_id', 0 );
		if ( $page_id ) {
			return get_permalink( $page_id );
		}
		return home_url( '/' );
	}
}
