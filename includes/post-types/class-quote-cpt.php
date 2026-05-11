<?php
/**
 * Quote custom post type registration.
 *
 * @package AskQuote
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the askquote_quote CPT and its custom statuses.
 */
class Askquote_Quote_CPT {

	/**
	 * Register the askquote_quote post type.
	 *
	 * @return void
	 */
	public function register() {
		$labels = array(
			'name'                  => _x( 'Quotes', 'post type general name', 'askquote-for-woocommerce' ),
			'singular_name'         => _x( 'Quote', 'post type singular name', 'askquote-for-woocommerce' ),
			'menu_name'             => _x( 'Quotes', 'admin menu', 'askquote-for-woocommerce' ),
			'name_admin_bar'        => _x( 'Quote', 'add new on admin bar', 'askquote-for-woocommerce' ),
			'add_new'               => _x( 'Add New', 'quote', 'askquote-for-woocommerce' ),
			'add_new_item'          => __( 'Add New Quote', 'askquote-for-woocommerce' ),
			'new_item'              => __( 'New Quote', 'askquote-for-woocommerce' ),
			'edit_item'             => __( 'Edit Quote', 'askquote-for-woocommerce' ),
			'view_item'             => __( 'View Quote', 'askquote-for-woocommerce' ),
			'all_items'             => __( 'All Quotes', 'askquote-for-woocommerce' ),
			'search_items'          => __( 'Search Quotes', 'askquote-for-woocommerce' ),
			'parent_item_colon'     => __( 'Parent Quotes:', 'askquote-for-woocommerce' ),
			'not_found'             => __( 'No quotes found.', 'askquote-for-woocommerce' ),
			'not_found_in_trash'    => __( 'No quotes found in Trash.', 'askquote-for-woocommerce' ),
			'featured_image'        => __( 'Quote Image', 'askquote-for-woocommerce' ),
			'set_featured_image'    => __( 'Set quote image', 'askquote-for-woocommerce' ),
			'remove_featured_image' => __( 'Remove quote image', 'askquote-for-woocommerce' ),
			'use_featured_image'    => __( 'Use as quote image', 'askquote-for-woocommerce' ),
			'archives'              => __( 'Quote archives', 'askquote-for-woocommerce' ),
			'attributes'            => __( 'Quote attributes', 'askquote-for-woocommerce' ),
			'insert_into_item'      => __( 'Insert into quote', 'askquote-for-woocommerce' ),
			'uploaded_to_this_item' => __( 'Uploaded to this quote', 'askquote-for-woocommerce' ),
			'items_list'            => __( 'Quotes list', 'askquote-for-woocommerce' ),
			'items_list_navigation' => __( 'Quotes list navigation', 'askquote-for-woocommerce' ),
			'filter_items_list'     => __( 'Filter quotes list', 'askquote-for-woocommerce' ),
		);

		$args = array(
			'labels'              => $labels,
			'description'         => __( 'Quote requests submitted by customers.', 'askquote-for-woocommerce' ),
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => false,
			'query_var'           => false,
			'rewrite'             => false,
			'capability_type'     => 'post',
			'has_archive'         => false,
			'hierarchical'        => false,
			'menu_position'       => null,
			'supports'            => array( 'title', 'custom-fields' ),
			'show_in_rest'        => false,
		);

		register_post_type( 'askquote_quote', $args );
	}

	/**
	 * Register custom post statuses for the quote CPT.
	 *
	 * @return void
	 */
	public function register_statuses() {
		register_post_status(
			'aq-pending',
			array(
				'label'                     => _x( 'Pending', 'quote status', 'askquote-for-woocommerce' ),
				'public'                    => false,
				'exclude_from_search'       => true,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: count of quotes */
				'label_count'               => _n_noop( 'Pending <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>', 'askquote-for-woocommerce' ),
			)
		);

		register_post_status(
			'aq-replied',
			array(
				'label'                     => _x( 'Replied', 'quote status', 'askquote-for-woocommerce' ),
				'public'                    => false,
				'exclude_from_search'       => true,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: count of quotes */
				'label_count'               => _n_noop( 'Replied <span class="count">(%s)</span>', 'Replied <span class="count">(%s)</span>', 'askquote-for-woocommerce' ),
			)
		);

		register_post_status(
			'aq-approved',
			array(
				'label'                     => _x( 'Approved', 'quote status', 'askquote-for-woocommerce' ),
				'public'                    => false,
				'exclude_from_search'       => true,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: count of quotes */
				'label_count'               => _n_noop( 'Approved <span class="count">(%s)</span>', 'Approved <span class="count">(%s)</span>', 'askquote-for-woocommerce' ),
			)
		);

		register_post_status(
			'aq-closed',
			array(
				'label'                     => _x( 'Closed', 'quote status', 'askquote-for-woocommerce' ),
				'public'                    => false,
				'exclude_from_search'       => true,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: count of quotes */
				'label_count'               => _n_noop( 'Closed <span class="count">(%s)</span>', 'Closed <span class="count">(%s)</span>', 'askquote-for-woocommerce' ),
			)
		);
	}
}
