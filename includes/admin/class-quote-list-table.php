<?php
/**
 * WP_List_Table implementation for quotes.
 *
 * @package AskQuote
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Displays the list of quote posts in the admin.
 */
class Askquote_Quote_List_Table extends WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Quote', 'askquote-for-woocommerce' ),
				'plural'   => __( 'Quotes', 'askquote-for-woocommerce' ),
				'ajax'     => false,
			)
		);
	}

	/**
	 * Define the table columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'       => '<input type="checkbox">',
			'id'       => __( 'ID', 'askquote-for-woocommerce' ),
			'customer' => __( 'Customer', 'askquote-for-woocommerce' ),
			'email'    => __( 'Email', 'askquote-for-woocommerce' ),
			'products' => __( 'Products', 'askquote-for-woocommerce' ),
			'status'   => __( 'Status', 'askquote-for-woocommerce' ),
			'date'     => __( 'Date', 'askquote-for-woocommerce' ),
		);
	}

	/**
	 * Define sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'id'   => array( 'ID', false ),
			'date' => array( 'date', true ),
		);
	}

	/**
	 * Define bulk actions.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array(
			'mark-replied' => __( 'Mark as Replied', 'askquote-for-woocommerce' ),
			'mark-closed'  => __( 'Mark as Closed', 'askquote-for-woocommerce' ),
			'delete'       => __( 'Delete', 'askquote-for-woocommerce' ),
		);
	}

	/**
	 * Get status view links (tabs).
	 *
	 * @return array
	 */
	public function get_views() {
		$all_statuses = Askquote_Quote_Status::get_all_statuses();
		$current      = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$views = array();

		// All.
		$all_count  = $this->get_quote_count();
		$all_class  = '' === $current ? ' class="current"' : '';
		$views['all'] = '<a href="' . esc_url( admin_url( 'admin.php?page=askquote' ) ) . '"' . $all_class . '>' .
			esc_html__( 'All', 'askquote-for-woocommerce' ) . ' <span class="count">(' . absint( $all_count ) . ')</span></a>';

		foreach ( $all_statuses as $slug => $data ) {
			$count = $this->get_quote_count( $slug );
			$url   = admin_url( 'admin.php?page=askquote&status=' . rawurlencode( $slug ) );
			$class = ( $current === $slug ) ? ' class="current"' : '';
			$views[ $slug ] = '<a href="' . esc_url( $url ) . '"' . $class . '>' .
				esc_html( $data['label'] ) . ' <span class="count">(' . absint( $count ) . ')</span></a>';
		}

		return $views;
	}

	/**
	 * Count quotes, optionally filtered by status.
	 *
	 * @param string $status Optional status slug.
	 * @return int
	 */
	private function get_quote_count( $status = '' ) {
		$args = array(
			'post_type'      => 'askquote_quote',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'post_status'    => $status ? array( $status ) : array( 'aq-pending', 'aq-replied', 'aq-approved', 'aq-closed' ),
		);
		$query = new WP_Query( $args );
		return $query->found_posts;
	}

	/**
	 * Prepare items for display.
	 *
	 * @return void
	 */
	public function prepare_items() {
		$this->process_bulk_action();

		$per_page     = 20;
		$current_page = $this->get_pagenum();

		$status = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$orderby = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'date'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order   = isset( $_GET['order'] ) && 'asc' === strtolower( sanitize_key( wp_unslash( $_GET['order'] ) ) ) ? 'ASC' : 'DESC'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$query_args = array(
			'post_type'      => 'askquote_quote',
			'posts_per_page' => $per_page,
			'paged'          => $current_page,
			'post_status'    => $status ? array( $status ) : array( 'aq-pending', 'aq-replied', 'aq-approved', 'aq-closed' ),
			'orderby'        => $orderby,
			'order'          => $order,
		);

		if ( $search ) {
			$query_args['s'] = $search;
		}

		$query       = new WP_Query( $query_args );
		$total_items = $query->found_posts;

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);

		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
		$this->items           = $query->posts;
	}

	/**
	 * Process bulk action submissions.
	 *
	 * @return void
	 */
	private function process_bulk_action() {
		$action = $this->current_action();
		if ( ! $action ) {
			return;
		}

		check_admin_referer( 'bulk-quotes' );

		$quote_ids = isset( $_POST['quote_ids'] ) ? array_map( 'absint', (array) $_POST['quote_ids'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( empty( $quote_ids ) ) {
			return;
		}

		switch ( $action ) {
			case 'mark-replied':
				foreach ( $quote_ids as $id ) {
					askquote_update_quote_status( $id, 'aq-replied' );
				}
				break;

			case 'mark-closed':
				foreach ( $quote_ids as $id ) {
					askquote_update_quote_status( $id, 'aq-closed' );
				}
				break;

			case 'delete':
				foreach ( $quote_ids as $id ) {
					wp_delete_post( $id, true );
				}
				break;
		}
	}

	/**
	 * Render the checkbox column.
	 *
	 * @param WP_Post $item Current row item.
	 * @return string
	 */
	public function column_cb( $item ) {
		return '<input type="checkbox" name="quote_ids[]" value="' . absint( $item->ID ) . '">';
	}

	/**
	 * Render the ID column.
	 *
	 * @param WP_Post $item Current row item.
	 * @return string
	 */
	public function column_id( $item ) {
		$edit_url = admin_url( 'post.php?post=' . absint( $item->ID ) . '&action=edit' );
		$actions  = array(
			'edit'   => '<a href="' . esc_url( $edit_url ) . '">' . esc_html__( 'View / Edit', 'askquote-for-woocommerce' ) . '</a>',
			'delete' => '<a href="' . esc_url(
				wp_nonce_url(
					admin_url( 'post.php?post=' . absint( $item->ID ) . '&action=delete' ),
					'delete-post_' . absint( $item->ID )
				)
			) . '" onclick="return confirm(\'' . esc_js( __( 'Delete this quote?', 'askquote-for-woocommerce' ) ) . '\')">' . esc_html__( 'Delete', 'askquote-for-woocommerce' ) . '</a>',
		);

		return '<a href="' . esc_url( $edit_url ) . '"><strong>#' . absint( $item->ID ) . '</strong></a>' .
			$this->row_actions( $actions );
	}

	/**
	 * Render the customer column.
	 *
	 * @param WP_Post $item Current row item.
	 * @return string
	 */
	public function column_customer( $item ) {
		$name = get_post_meta( $item->ID, '_askquote_customer_name', true );
		return esc_html( $name ? $name : __( '(Unknown)', 'askquote-for-woocommerce' ) );
	}

	/**
	 * Render the email column.
	 *
	 * @param WP_Post $item Current row item.
	 * @return string
	 */
	public function column_email( $item ) {
		$email = get_post_meta( $item->ID, '_askquote_customer_email', true );
		return $email ? '<a href="' . esc_url( 'mailto:' . antispambot( $email ) ) . '">' . esc_html( antispambot( $email ) ) . '</a>' : '&mdash;';
	}

	/**
	 * Render the products column.
	 *
	 * @param WP_Post $item Current row item.
	 * @return string
	 */
	public function column_products( $item ) {
		$items    = askquote_get_quote_items( $item->ID );
		$count    = count( $items );
		/* translators: %d: number of items */
		return esc_html( sprintf( _n( '%d item', '%d items', $count, 'askquote-for-woocommerce' ), $count ) );
	}

	/**
	 * Render the status column.
	 *
	 * @param WP_Post $item Current row item.
	 * @return string
	 */
	public function column_status( $item ) {
		$status    = $item->post_status;
		$label     = Askquote_Quote_Status::get_status_label( $status );
		$statuses  = Askquote_Quote_Status::get_all_statuses();
		$color     = isset( $statuses[ $status ]['color'] ) ? $statuses[ $status ]['color'] : '#ccc';
		return '<span class="askquote-status-badge" style="background:' . esc_attr( $color ) . '">' . esc_html( $label ) . '</span>';
	}

	/**
	 * Render the date column.
	 *
	 * @param WP_Post $item Current row item.
	 * @return string
	 */
	public function column_date( $item ) {
		return esc_html( get_the_date( get_option( 'date_format' ), $item ) );
	}

	/**
	 * Default column handler.
	 *
	 * @param WP_Post $item        Current row item.
	 * @param string  $column_name Column slug.
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		return '&mdash;';
	}
}
