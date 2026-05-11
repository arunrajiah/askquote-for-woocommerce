<?php
/**
 * Fired during plugin deactivation.
 *
 * @package AskQuote
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles everything that happens during plugin deactivation.
 */
class Askquote_Deactivator {

	/**
	 * Run deactivation tasks: flush rewrites, clear scheduled cron jobs.
	 *
	 * @return void
	 */
	public static function deactivate() {
		flush_rewrite_rules();

		// Clear any scheduled cron events.
		$scheduled_hooks = array(
			'askquote_cleanup_abandoned_quotes',
			'askquote_send_follow_up_emails',
		);

		foreach ( $scheduled_hooks as $hook ) {
			$timestamp = wp_next_scheduled( $hook );
			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, $hook );
			}
			wp_clear_scheduled_hook( $hook );
		}
	}
}
