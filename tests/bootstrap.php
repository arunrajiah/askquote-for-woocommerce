<?php
/**
 * PHPUnit bootstrap file for AskQuote for WooCommerce.
 *
 * @package AskQuote
 */

// Determine WP test lib location: environment variable or common defaults.
$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo 'Could not find WordPress test library at: ' . $_tests_dir . PHP_EOL;
	echo 'Please run: bash bin/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version]' . PHP_EOL;
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	// Load WooCommerce first (must be active).
	$wc_path = dirname( WP_PLUGIN_DIR ) . '/woocommerce/woocommerce.php';
	if ( file_exists( $wc_path ) ) {
		require_once $wc_path;
	}

	// Load the plugin.
	require_once dirname( __DIR__ ) . '/askquote-for-woocommerce.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
