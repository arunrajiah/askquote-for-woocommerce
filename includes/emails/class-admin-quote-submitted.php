<?php
/**
 * Admin "New Quote Submitted" email.
 *
 * @package AskQuote
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sent to the admin when a new quote request is submitted.
 */
class Askquote_Email_Admin_Quote_Submitted extends WC_Email {

	/**
	 * Constructor — set up email properties.
	 */
	public function __construct() {
		$this->id             = 'askquote_admin_quote_submitted';
		$this->title          = __( 'New Quote Submitted (Admin)', 'askquote-for-woocommerce' );
		$this->description    = __( 'Sent to the admin when a new quote request is received.', 'askquote-for-woocommerce' );
		$this->template_html  = 'emails/html/admin-quote-submitted.php';
		$this->template_plain = 'emails/plain/admin-quote-submitted.php';
		$this->template_base  = ASKQUOTE_PLUGIN_DIR . 'templates/';
		$this->placeholders   = array(
			'{quote_id}'      => '',
			'{customer_name}' => '',
			'{site_title}'    => $this->get_blogname(),
		);

		$this->subject = __( 'New quote request received — {site_title}', 'askquote-for-woocommerce' );
		$this->heading = __( 'New Quote Request', 'askquote-for-woocommerce' );

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
		$admin_email   = askquote_get_setting( 'admin_email', get_option( 'admin_email' ) );

		$this->recipient = apply_filters( 'askquote_email_recipients', $admin_email, $quote_id, $this->id );

		$customer_name = get_post_meta( $quote_id, '_askquote_customer_name', true );
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
		return __( 'New quote request received — {site_title}', 'askquote-for-woocommerce' );
	}

	/**
	 * Get default heading.
	 *
	 * @return string
	 */
	public function get_default_heading() {
		return __( 'New Quote Request', 'askquote-for-woocommerce' );
	}
}
