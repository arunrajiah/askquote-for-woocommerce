<?php
/**
 * Define the internationalization functionality.
 *
 * @package AskQuote
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles loading the text domain for translations.
 */
class Askquote_I18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'askquote-for-woocommerce',
			false,
			ASKQUOTE_PLUGIN_DIR . 'languages'
		);
	}
}
