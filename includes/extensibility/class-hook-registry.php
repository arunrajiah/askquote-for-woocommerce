<?php
/**
 * Hook registry — documents all plugin hooks for developers.
 *
 * @package AskQuote
 */

defined( 'ABSPATH' ) || exit;

/**
 * Documents all actions and filters provided by AskQuote.
 *
 * This class is for discoverability only; the actual do_action/apply_filters
 * calls happen in the relevant classes. Developers can call the static methods
 * here to get hook names programmatically.
 */
class Askquote_Hook_Registry {

	/**
	 * Fires after the plugin has finished initialising.
	 *
	 * @return string Hook name.
	 */
	public static function loaded() {
		return 'askquote_loaded';
	}

	/**
	 * Fires after a quote has been submitted and saved.
	 *
	 * Passes $quote_id (int) and $data (array).
	 *
	 * @return string Hook name.
	 */
	public static function quote_submitted() {
		return 'askquote_quote_submitted';
	}

	/**
	 * Fires when a quote's status changes.
	 *
	 * Passes $quote_id (int), $old_status (string), $new_status (string).
	 *
	 * @return string Hook name.
	 */
	public static function quote_status_changed() {
		return 'askquote_quote_status_changed';
	}

	/**
	 * Fires immediately before the quote button HTML is output.
	 *
	 * Passes $product_id (int).
	 *
	 * @return string Hook name.
	 */
	public static function before_quote_button() {
		return 'askquote_before_quote_button';
	}

	/**
	 * Fires immediately after the quote button HTML is output.
	 *
	 * Passes $product_id (int).
	 *
	 * @return string Hook name.
	 */
	public static function after_quote_button() {
		return 'askquote_after_quote_button';
	}

	/**
	 * Filter: modify the quote button HTML.
	 *
	 * Passes $html (string), $product_id (int).
	 *
	 * @return string Filter name.
	 */
	public static function quote_button_html() {
		return 'askquote_quote_button_html';
	}

	/**
	 * Filter: control whether the quote button is visible for a product.
	 *
	 * Passes $visible (bool), $product_id (int).
	 *
	 * @return string Filter name.
	 */
	public static function quote_button_visible() {
		return 'askquote_quote_button_visible';
	}

	/**
	 * Filter: modify the quote form fields array.
	 *
	 * Passes $fields (array).
	 *
	 * @return string Filter name.
	 */
	public static function quote_form_fields() {
		return 'askquote_quote_form_fields';
	}

	/**
	 * Filter: modify the quote data array just before it is saved.
	 *
	 * Passes $data (array).
	 *
	 * @return string Filter name.
	 */
	public static function quote_data_before_save() {
		return 'askquote_quote_data_before_save';
	}

	/**
	 * Filter: modify the admin menu items array.
	 *
	 * Passes $items (array).
	 *
	 * @return string Filter name.
	 */
	public static function admin_menu_items() {
		return 'askquote_admin_menu_items';
	}

	/**
	 * Filter: modify email recipients for a given email type.
	 *
	 * Passes $recipients (string|array), $quote_id (int), $email_type (string).
	 *
	 * @return string Filter name.
	 */
	public static function email_recipients() {
		return 'askquote_email_recipients';
	}

	/**
	 * Filter: modify the registered quote statuses array.
	 *
	 * Passes $statuses (array).
	 *
	 * @return string Filter name.
	 */
	public static function quote_statuses() {
		return 'askquote_quote_statuses';
	}
}
