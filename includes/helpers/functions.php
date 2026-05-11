<?php
/**
 * Global helper functions for AskQuote.
 *
 * @package AskQuote
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get a single quote post by ID.
 *
 * @param int $quote_id Post ID.
 * @return WP_Post|null The post object or null if not found / wrong post type.
 */
function askquote_get_quote( $quote_id ) {
	$quote_id = absint( $quote_id );
	if ( ! $quote_id ) {
		return null;
	}

	$post = get_post( $quote_id );
	if ( ! $post || 'askquote_quote' !== $post->post_type ) {
		return null;
	}

	return $post;
}

/**
 * Get quotes for a specific user.
 *
 * @param int   $user_id WordPress user ID.
 * @param array $args    Additional WP_Query arguments.
 * @return WP_Post[] Array of quote post objects.
 */
function askquote_get_quotes_by_user( $user_id, $args = array() ) {
	$user_id = absint( $user_id );
	if ( ! $user_id ) {
		return array();
	}

	$default_args = array(
		'post_type'      => 'askquote_quote',
		'post_status'    => array( 'aq-pending', 'aq-replied', 'aq-approved', 'aq-closed' ),
		'posts_per_page' => 20,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'   => '_askquote_customer_user_id',
				'value' => $user_id,
				'type'  => 'NUMERIC',
			),
		),
	);

	$query_args = wp_parse_args( $args, $default_args );
	$query      = new WP_Query( $query_args );

	return $query->posts;
}

/**
 * Create a new quote.
 *
 * @param array $data {
 *     Quote data.
 *     @type string $customer_name    Customer name.
 *     @type string $customer_email   Customer email.
 *     @type string $customer_phone   Customer phone (optional).
 *     @type string $customer_company Customer company (optional).
 *     @type string $message          Customer message (optional).
 *     @type array  $items            Array of items: [product_id, variation_id, quantity].
 *     @type int    $user_id          WordPress user ID (optional, 0 for guests).
 * }
 * @return int|WP_Error Quote ID on success, WP_Error on failure.
 */
function askquote_create_quote( $data ) {
	// Apply filter to allow modification before save.
	$data = apply_filters( 'askquote_quote_data_before_save', $data );

	// Validate required fields.
	if ( empty( $data['customer_name'] ) || empty( $data['customer_email'] ) ) {
		return new WP_Error( 'missing_required_fields', __( 'Customer name and email are required.', 'askquote-for-woocommerce' ) );
	}

	if ( ! is_email( $data['customer_email'] ) ) {
		return new WP_Error( 'invalid_email', __( 'Invalid customer email address.', 'askquote-for-woocommerce' ) );
	}

	if ( empty( $data['items'] ) || ! is_array( $data['items'] ) ) {
		return new WP_Error( 'no_items', __( 'Quote must contain at least one item.', 'askquote-for-woocommerce' ) );
	}

	$customer_name  = sanitize_text_field( $data['customer_name'] );
	$customer_email = sanitize_email( $data['customer_email'] );
	$customer_phone = isset( $data['customer_phone'] ) ? sanitize_text_field( $data['customer_phone'] ) : '';
	$customer_company = isset( $data['customer_company'] ) ? sanitize_text_field( $data['customer_company'] ) : '';
	$message        = isset( $data['message'] ) ? sanitize_textarea_field( $data['message'] ) : '';
	$user_id        = isset( $data['user_id'] ) ? absint( $data['user_id'] ) : 0;

	/* translators: %s: customer name */
	$post_title = sprintf( __( 'Quote from %s', 'askquote-for-woocommerce' ), $customer_name );

	$post_id = wp_insert_post(
		array(
			'post_type'   => 'askquote_quote',
			'post_title'  => $post_title,
			'post_status' => 'aq-pending',
			'post_author' => $user_id > 0 ? $user_id : 0,
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}

	// Save customer meta.
	update_post_meta( $post_id, '_askquote_customer_name', $customer_name );
	update_post_meta( $post_id, '_askquote_customer_email', $customer_email );
	update_post_meta( $post_id, '_askquote_customer_phone', $customer_phone );
	update_post_meta( $post_id, '_askquote_customer_company', $customer_company );
	update_post_meta( $post_id, '_askquote_message', $message );
	update_post_meta( $post_id, '_askquote_customer_user_id', $user_id );

	// Insert items into custom table.
	global $wpdb;
	$table_name = $wpdb->prefix . 'askquote_quote_items';

	foreach ( $data['items'] as $item ) {
		$product_id   = absint( isset( $item['product_id'] ) ? $item['product_id'] : 0 );
		$variation_id = absint( isset( $item['variation_id'] ) ? $item['variation_id'] : 0 );
		$quantity     = absint( isset( $item['quantity'] ) ? $item['quantity'] : 1 );
		$item_meta    = isset( $item['meta'] ) ? wp_json_encode( $item['meta'] ) : null;

		if ( ! $product_id ) {
			continue;
		}

		$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$table_name,
			array(
				'quote_id'     => $post_id,
				'product_id'   => $product_id,
				'variation_id' => $variation_id,
				'quantity'     => $quantity,
				'meta'         => $item_meta,
			),
			array( '%d', '%d', '%d', '%d', '%s' )
		);
	}

	do_action( 'askquote_quote_submitted', $post_id, $data );

	return $post_id;
}

/**
 * Update the status of a quote.
 *
 * @param int    $quote_id   Quote post ID.
 * @param string $new_status New status slug (e.g. 'aq-approved').
 * @return bool True on success, false on failure.
 */
function askquote_update_quote_status( $quote_id, $new_status ) {
	$quote_id = absint( $quote_id );
	if ( ! $quote_id ) {
		return false;
	}

	if ( ! Askquote_Quote_Status::is_valid_status( $new_status ) ) {
		return false;
	}

	$quote = askquote_get_quote( $quote_id );
	if ( ! $quote ) {
		return false;
	}

	$old_status = $quote->post_status;

	$result = wp_update_post(
		array(
			'ID'          => $quote_id,
			'post_status' => $new_status,
		)
	);

	if ( $result && ! is_wp_error( $result ) ) {
		do_action( 'askquote_quote_status_changed', $quote_id, $old_status, $new_status );
		return true;
	}

	return false;
}

/**
 * Get line items for a given quote from the custom DB table.
 *
 * @param int $quote_id Quote post ID.
 * @return array Array of item row objects.
 */
function askquote_get_quote_items( $quote_id ) {
	global $wpdb;

	$quote_id   = absint( $quote_id );
	$table_name = $wpdb->prefix . 'askquote_quote_items';

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$items = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM {$table_name} WHERE quote_id = %d ORDER BY id ASC", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$quote_id
		)
	);

	return $items ? $items : array();
}

/**
 * Get a specific plugin setting by key.
 *
 * @param string $key     Setting key.
 * @param mixed  $default Default value if the key is not set.
 * @return mixed Setting value or default.
 */
function askquote_get_setting( $key, $default = '' ) {
	$settings = get_option( 'askquote_settings', array() );
	return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
}
