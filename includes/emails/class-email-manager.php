<?php
/**
 * Email manager — registers email classes with WooCommerce.
 *
 * @package AskQuote
 */

defined( 'ABSPATH' ) || exit;

/**
 * Manages all AskQuote email classes and trigger helpers.
 */
class Askquote_Email_Manager {

	/**
	 * Add AskQuote email classes to WooCommerce's email class map.
	 *
	 * @param array $email_classes Existing WC email classes.
	 * @return array Modified email classes.
	 */
	public function add_emails( $email_classes ) {
		$email_classes['Askquote_Email_Customer_Quote_Received'] = new Askquote_Email_Customer_Quote_Received();
		$email_classes['Askquote_Email_Admin_Quote_Submitted']   = new Askquote_Email_Admin_Quote_Submitted();
		$email_classes['Askquote_Email_Customer_Quote_Approved'] = new Askquote_Email_Customer_Quote_Approved();
		return $email_classes;
	}

	/**
	 * Send the "customer quote received" email.
	 *
	 * @param int $quote_id Quote post ID.
	 * @return void
	 */
	public static function send_customer_quote_received( $quote_id ) {
		$emails = WC()->mailer()->get_emails();
		if ( isset( $emails['Askquote_Email_Customer_Quote_Received'] ) ) {
			$emails['Askquote_Email_Customer_Quote_Received']->trigger( $quote_id );
		}
	}

	/**
	 * Send the "admin quote submitted" email.
	 *
	 * @param int $quote_id Quote post ID.
	 * @return void
	 */
	public static function send_admin_quote_submitted( $quote_id ) {
		$emails = WC()->mailer()->get_emails();
		if ( isset( $emails['Askquote_Email_Admin_Quote_Submitted'] ) ) {
			$emails['Askquote_Email_Admin_Quote_Submitted']->trigger( $quote_id );
		}
	}

	/**
	 * Send the "customer quote approved" email.
	 *
	 * @param int $quote_id Quote post ID.
	 * @return void
	 */
	public static function send_customer_quote_approved( $quote_id ) {
		$emails = WC()->mailer()->get_emails();
		if ( isset( $emails['Askquote_Email_Customer_Quote_Approved'] ) ) {
			$emails['Askquote_Email_Customer_Quote_Approved']->trigger( $quote_id );
		}
	}
}
