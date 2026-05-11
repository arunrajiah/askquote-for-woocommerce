<?php
/**
 * Customer "Quote Received" email.
 *
 * @package AskQuote
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sent to the customer when their quote request is received.
 */
class Askquote_Email_Customer_Quote_Received extends WC_Email {

	/**
	 * Constructor — set up email properties.
	 */
	public function __construct() {
		$this->id             = 'askquote_customer_quote_received';
		$this->customer_email = true;
		$this->title          = __( 'Quote Received', 'askquote-for-woocommerce' );
		$this->description    = __( 'Sent to the customer when their quote request is received.', 'askquote-for-woocommerce' );
		$this->template_html  = 'emails/html/customer-quote-received.php';
		$this->template_plain = 'emails/plain/customer-quote-received.php';
		$this->template_base  = ASKQUOTE_PLUGIN_DIR . 'templates/';
		$this->placeholders   = array(
			'{quote_id}'       => '',
			'{customer_name}'  => '',
			'{site_title}'     => $this->get_blogname(),
		);

		// Default subject/heading.
		$this->subject = askquote_get_setting(
			'subject_received',
			__( 'Your quote request has been received — {site_title}', 'askquote-for-woocommerce' )
		);
		$this->heading = __( 'Quote Request Received', 'askquote-for-woocommerce' );

		parent::__construct();
	}

	/**
	 * Trigger sending this email for a given quote.
	 *
	 * @param int $quote_id Quote post ID.
	 * @return void
	 */
	public function trigger( $quote_id ) {
		$this->setup_locale();

		$quote_id = absint( $quote_id );
		$quote    = askquote_get_quote( $quote_id );

		if ( ! $quote ) {
			return;
		}

		$this->object  = $quote;
		$customer_email = get_post_meta( $quote_id, '_askquote_customer_email', true );
		$customer_name  = get_post_meta( $quote_id, '_askquote_customer_name', true );

		$this->recipient = apply_filters( 'askquote_email_recipients', $customer_email, $quote_id, $this->id );

		$this->placeholders['{quote_id}']      = $quote_id;
		$this->placeholders['{customer_name}'] = $customer_name;

		if ( $this->is_enabled() && $this->get_recipient() ) {
			$this->send(
				$this->get_recipient(),
				$this->get_subject(),
				$this->get_content(),
				$this->get_headers(),
				$this->get_attachments()
			);
		}

		$this->restore_locale();
	}

	/**
	 * Get email HTML content.
	 *
	 * @return string
	 */
	public function get_content_html() {
		return wc_get_template_html(
			$this->template_html,
			array(
				'quote'         => $this->object,
				'email_heading' => $this->get_heading(),
				'items'         => askquote_get_quote_items( $this->object->ID ),
				'email'         => $this,
			),
			'',
			$this->template_base
		);
	}

	/**
	 * Get email plain text content.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html(
			$this->template_plain,
			array(
				'quote'         => $this->object,
				'email_heading' => $this->get_heading(),
				'items'         => askquote_get_quote_items( $this->object->ID ),
				'email'         => $this,
			),
			'',
			$this->template_base
		);
	}

	/**
	 * Get default subject.
	 *
	 * @return string
	 */
	public function get_default_subject() {
		return __( 'Your quote request has been received — {site_title}', 'askquote-for-woocommerce' );
	}

	/**
	 * Get default heading.
	 *
	 * @return string
	 */
	public function get_default_heading() {
		return __( 'Quote Request Received', 'askquote-for-woocommerce' );
	}
}
