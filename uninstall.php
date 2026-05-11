<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package AskQuote
 */

// Exit if not called by WordPress uninstall routine.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Only remove data if the user has opted in via settings.
$settings = get_option( 'askquote_settings', array() );
$remove   = isset( $settings['remove_data_on_uninstall'] ) ? $settings['remove_data_on_uninstall'] : 'no';

if ( 'yes' !== $remove ) {
	return;
}

global $wpdb;

// Delete all quote posts and their meta.
$quote_ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'askquote_quote'"
);

if ( $quote_ids ) {
	foreach ( $quote_ids as $quote_id ) {
		wp_delete_post( absint( $quote_id ), true );
	}
}

// Drop custom table.
$table_name      = $wpdb->prefix . 'askquote_quote_items';
$safe_table_name = esc_sql( $table_name );
$wpdb->query( "DROP TABLE IF EXISTS `{$safe_table_name}`" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

// Delete plugin options.
delete_option( 'askquote_settings' );
delete_option( 'askquote_db_version' );
delete_option( 'askquote_cart_page_id' );
delete_option( 'askquote_form_page_id' );
delete_option( 'askquote_button_categories' );
delete_option( 'askquote_button_tags' );

// Delete user meta for dismissed notices.
$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
	$wpdb->usermeta,
	array( 'meta_key' => 'askquote_advanced_notice_dismissed' ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
	array( '%s' )
);
