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
$askquote_settings = get_option( 'askquote_settings', array() );
$askquote_remove   = isset( $askquote_settings['remove_data_on_uninstall'] ) ? $askquote_settings['remove_data_on_uninstall'] : 'no';

if ( 'yes' !== $askquote_remove ) {
	return;
}

global $wpdb;

// Delete all quote posts and their meta.
$askquote_quote_ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'askquote_quote'"
);

if ( $askquote_quote_ids ) {
	foreach ( $askquote_quote_ids as $askquote_quote_id ) {
		wp_delete_post( absint( $askquote_quote_id ), true );
	}
}

// Drop custom table.
$askquote_table_name      = $wpdb->prefix . 'askquote_quote_items';
$askquote_safe_table_name = esc_sql( $askquote_table_name );
$wpdb->query( "DROP TABLE IF EXISTS `{$askquote_safe_table_name}`" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

// Delete plugin options.
delete_option( 'askquote_settings' );
delete_option( 'askquote_db_version' );
delete_option( 'askquote_cart_page_id' );
delete_option( 'askquote_form_page_id' );
delete_option( 'askquote_button_categories' );
delete_option( 'askquote_button_tags' );

// Delete user meta for dismissed notices.
$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->usermeta,
	array( 'meta_key' => 'askquote_advanced_notice_dismissed' ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
	array( '%s' )
);
