<?php
/**
 * Quote submission form.
 *
 * @package AskQuote
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders and processes the quote submission form.
 */
class Askquote_Quote_Form {

	/**
	 * Register the [askquote_form] shortcode.
	 *
	 * @return void
	 */
	public function register_shortcode() {
		add_shortcode( 'askquote_form', array( $this, 'shortcode_callback' ) );
	}

	/**
	 * Shortcode callback for [askquote_form].
	 *
	 * @param array $atts Shortcode attributes (unused currently).
	 * @return string HTML output.
	 */
	public function shortcode_callback( $atts ) {
		$atts = shortcode_atts( array(), $atts, 'askquote_form' );
		ob_start();
		$this->render_form();
		return ob_get_clean();
	}

	/**
	 * Get the default form fields.
	 *
	 * @return array
	 */
	public function get_form_fields() {
		$fields = array(
			'customer_name'    => array(
				'label'       => __( 'Your Name', 'askquote-for-woocommerce' ),
				'type'        => 'text',
				'required'    => true,
				'placeholder' => __( 'John Smith', 'askquote-for-woocommerce' ),
			),
			'customer_email'   => array(
				'label'       => __( 'Email Address', 'askquote-for-woocommerce' ),
				'type'        => 'email',
				'required'    => true,
				'placeholder' => __( 'john@example.com', 'askquote-for-woocommerce' ),
			),
			'customer_phone'   => array(
				'label'       => __( 'Phone Number', 'askquote-for-woocommerce' ),
				'type'        => 'tel',
				'required'    => false,
				'placeholder' => '',
			),
			'customer_company' => array(
				'label'       => __( 'Company', 'askquote-for-woocommerce' ),
				'type'        => 'text',
				'required'    => false,
				'placeholder' => '',
			),
			'message'          => array(
				'label'       => __( 'Message', 'askquote-for-woocommerce' ),
				'type'        => 'textarea',
				'required'    => false,
				'placeholder' => __( 'Tell us more about your requirements...', 'askquote-for-woocommerce' ),
			),
		);

		return apply_filters( 'askquote_quote_form_fields', $fields );
	}

	/**
	 * Render the form HTML via template.
	 *
	 * @return void
	 */
	public function render_form() {
		// Check for success message.
		if ( isset( $_GET['askquote_submitted'] ) && '1' === $_GET['askquote_submitted'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			echo '<div class="askquote-success woocommerce-message">' . esc_html__( 'Your quote request has been submitted. We will be in touch shortly.', 'askquote-for-woocommerce' ) . '</div>';
		}

		$quote_cart = new Askquote_Quote_Cart();
		$cart_items = $quote_cart->get_items();
		$fields     = $this->get_form_fields();

		// Pre-populate from logged-in user.
		$user        = wp_get_current_user();
		$user_values = array();
		if ( $user->ID ) {
			$user_values = array(
				'customer_name'  => trim( $user->first_name . ' ' . $user->last_name ),
				'customer_email' => $user->user_email,
			);
		}

		$template = ASKQUOTE_PLUGIN_DIR . 'templates/frontend/quote-form.php';
		if ( file_exists( $template ) ) {
			include $template;
		}
	}

	/**
	 * Handle form submission on template_redirect.
	 *
	 * @return void
	 */
	public function handle_submission() {
		if ( ! isset( $_POST['askquote_submit'] ) ) {
			return;
		}

		if ( ! isset( $_POST['askquote_form_nonce'] ) ||
			! wp_verify_nonce( sanitize_key( $_POST['askquote_form_nonce'] ), 'askquote_submit_quote' ) ) {
			wc_add_notice( __( 'Security check failed. Please try again.', 'askquote-for-woocommerce' ), 'error' );
			return;
		}

		$quote_cart = new Askquote_Quote_Cart();
		$cart_items = $quote_cart->get_items();

		if ( empty( $cart_items ) ) {
			wc_add_notice( __( 'Your quote cart is empty. Please add products first.', 'askquote-for-woocommerce' ), 'error' );
			return;
		}

		$data = array(
			'customer_name'    => isset( $_POST['customer_name'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_name'] ) ) : '',
			'customer_email'   => isset( $_POST['customer_email'] ) ? sanitize_email( wp_unslash( $_POST['customer_email'] ) ) : '',
			'customer_phone'   => isset( $_POST['customer_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_phone'] ) ) : '',
			'customer_company' => isset( $_POST['customer_company'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_company'] ) ) : '',
			'message'          => isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '',
			'user_id'          => get_current_user_id(),
			'items'            => array(),
		);

		// Map cart items to the format expected by askquote_create_quote.
		foreach ( $cart_items as $item ) {
			$data['items'][] = array(
				'product_id'   => absint( $item['product_id'] ),
				'variation_id' => absint( $item['variation_id'] ),
				'quantity'     => absint( $item['quantity'] ),
			);
		}

		$quote_id = askquote_create_quote( $data );

		if ( is_wp_error( $quote_id ) ) {
			wc_add_notice( $quote_id->get_error_message(), 'error' );
			return;
		}

		// Clear the cart.
		$quote_cart->clear();

		// Send emails.
		Askquote_Email_Manager::send_customer_quote_received( $quote_id );
		Askquote_Email_Manager::send_admin_quote_submitted( $quote_id );

		// Redirect with success.
		$redirect = add_query_arg( 'askquote_submitted', '1', wp_get_referer() ? wp_get_referer() : home_url( '/' ) );
		wp_safe_redirect( $redirect );
		exit;
	}
}
