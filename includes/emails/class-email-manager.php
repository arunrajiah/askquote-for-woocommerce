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
		// Load email classes here so WC_Email is already defined when the files are included.
		require_once ASKQUOTE_PLUGIN_DIR . 'includes/emails/class-customer-quote-received.php';
		require_once ASKQUOTE_PLUGIN_DIR . 'includes/emails/class-admin-quote-submitted.php';
		require_once ASKQUOTE_PLUGIN_DIR . 'includes/emails/class-customer-quote-approved.php';

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

	/**
	 * Listen to status transitions and fire the approval email when a quote is approved.
	 * Hooked to askquote_quote_status_changed so any code path (admin meta box, REST API,
	 * WP-CLI, etc.) triggers the email — not just the REST API.
	 *
	 * @param int    $quote_id   Quote post ID.
	 * @param string $old_status Previous status slug.
	 * @param string $new_status New status slug.
	 * @return void
	 */
	public static function on_status_changed( $quote_id, $old_status, $new_status ) {
		if ( 'aq-approved' === $new_status && $old_status !== $new_status ) {
			self::send_customer_quote_approved( $quote_id );
		}
	}
}
