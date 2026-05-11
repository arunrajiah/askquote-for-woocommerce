<?php
/**
 * REST API controller for quotes.
 *
 * @package AskQuote
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles the askquote/v1 REST API namespace.
 */
class Askquote_REST_API extends WP_REST_Controller {

	/**
	 * API namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'askquote/v1';

	/**
	 * Resource base.
	 *
	 * @var string
	 */
	protected $rest_base = 'quotes';

	/**
	 * Register REST API routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_create_item_schema(),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(
						'per_page' => array(
							'default'           => 20,
							'sanitize_callback' => 'absint',
						),
						'page'     => array(
							'default'           => 1,
							'sanitize_callback' => 'absint',
						),
						'status'   => array(
							'default'           => '',
							'sanitize_callback' => 'sanitize_key',
						),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => array(
						'id' => array(
							'required'          => true,
							'sanitize_callback' => 'absint',
						),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/status',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_status' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => array(
						'id'     => array(
							'required'          => true,
							'sanitize_callback' => 'absint',
						),
						'status' => array(
							'required'          => true,
							'sanitize_callback' => 'sanitize_key',
							'validate_callback' => array( $this, 'validate_status' ),
						),
					),
				),
			)
		);
	}

	/**
	 * Create a quote via REST API.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {
		$data = array(
			'customer_name'    => sanitize_text_field( $request->get_param( 'customer_name' ) ),
			'customer_email'   => sanitize_email( $request->get_param( 'customer_email' ) ),
			'customer_phone'   => sanitize_text_field( (string) $request->get_param( 'customer_phone' ) ),
			'customer_company' => sanitize_text_field( (string) $request->get_param( 'customer_company' ) ),
			'message'          => sanitize_textarea_field( (string) $request->get_param( 'message' ) ),
			'user_id'          => get_current_user_id(),
			'items'            => $this->sanitize_items( (array) $request->get_param( 'items' ) ),
		);

		$quote_id = askquote_create_quote( $data );

		if ( is_wp_error( $quote_id ) ) {
			return $quote_id;
		}

		// Send emails.
		Askquote_Email_Manager::send_customer_quote_received( $quote_id );
		Askquote_Email_Manager::send_admin_quote_submitted( $quote_id );

		$quote = askquote_get_quote( $quote_id );
		return new WP_REST_Response( $this->prepare_quote_for_response( $quote ), 201 );
	}

	/**
	 * Get a list of quotes.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response
	 */
	public function get_items( $request ) {
		$per_page = absint( $request->get_param( 'per_page' ) );
		$page     = absint( $request->get_param( 'page' ) );
		$status   = sanitize_key( (string) $request->get_param( 'status' ) );

		$query_args = array(
			'post_type'      => 'askquote_quote',
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'post_status'    => $status ? array( $status ) : array( 'aq-pending', 'aq-replied', 'aq-approved', 'aq-closed' ),
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		$query  = new WP_Query( $query_args );
		$quotes = array_map( array( $this, 'prepare_quote_for_response' ), $query->posts );

		$response = new WP_REST_Response( $quotes, 200 );
		$response->header( 'X-WP-Total', $query->found_posts );
		$response->header( 'X-WP-TotalPages', $query->max_num_pages );

		return $response;
	}

	/**
	 * Get a single quote by ID.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_item( $request ) {
		$quote_id = absint( $request->get_param( 'id' ) );
		$quote    = askquote_get_quote( $quote_id );

		if ( ! $quote ) {
			return new WP_Error( 'quote_not_found', __( 'Quote not found.', 'askquote-for-woocommerce' ), array( 'status' => 404 ) );
		}

		return new WP_REST_Response( $this->prepare_quote_for_response( $quote ), 200 );
	}

	/**
	 * Update a quote's status.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_status( $request ) {
		$quote_id   = absint( $request->get_param( 'id' ) );
		$new_status = sanitize_key( $request->get_param( 'status' ) );

		$quote = askquote_get_quote( $quote_id );
		if ( ! $quote ) {
			return new WP_Error( 'quote_not_found', __( 'Quote not found.', 'askquote-for-woocommerce' ), array( 'status' => 404 ) );
		}

		$updated = askquote_update_quote_status( $quote_id, $new_status );
		if ( ! $updated ) {
			return new WP_Error( 'status_update_failed', __( 'Could not update quote status.', 'askquote-for-woocommerce' ), array( 'status' => 500 ) );
		}

		// Approval email is sent via the askquote_quote_status_changed action
		// in Askquote_Email_Manager::on_status_changed() — no direct call needed here.

		$quote = askquote_get_quote( $quote_id );
		return new WP_REST_Response( $this->prepare_quote_for_response( $quote ), 200 );
	}

	/**
	 * Check permission for listing quotes (admin only).
	 *
	 * @param WP_REST_Request $request Incoming request.
	 * @return bool
	 */
	public function get_items_permissions_check( $request ) {
		return current_user_can( 'manage_woocommerce' );
	}

	/**
	 * Check permission to view a single quote (admin or quote owner).
	 *
	 * @param WP_REST_Request $request Incoming request.
	 * @return bool
	 */
	public function get_item_permissions_check( $request ) {
		if ( current_user_can( 'manage_woocommerce' ) ) {
			return true;
		}

		$quote_id = absint( $request->get_param( 'id' ) );
		$quote    = askquote_get_quote( $quote_id );
		if ( ! $quote ) {
			return false;
		}

		$owner_id = (int) get_post_meta( $quote_id, '_askquote_customer_user_id', true );
		return $owner_id > 0 && $owner_id === get_current_user_id();
	}

	/**
	 * Check permission to update a quote (admin only).
	 *
	 * @param WP_REST_Request $request Incoming request.
	 * @return bool
	 */
	public function update_item_permissions_check( $request ) {
		return current_user_can( 'manage_woocommerce' );
	}

	/**
	 * Validate that a status value is allowed.
	 *
	 * @param string          $value   Status slug to validate.
	 * @param WP_REST_Request $request Full request.
	 * @param string          $param   Parameter name.
	 * @return bool|WP_Error
	 */
	public function validate_status( $value, $request, $param ) {
		if ( ! Askquote_Quote_Status::is_valid_status( $value ) ) {
			return new WP_Error(
				'rest_invalid_param',
				/* translators: %s: invalid status value */
				sprintf( __( 'Invalid status: %s', 'askquote-for-woocommerce' ), $value ),
				array( 'status' => 400 )
			);
		}
		return true;
	}

	/**
	 * Prepare a quote post for the REST response.
	 *
	 * @param WP_Post $quote Quote post object.
	 * @return array
	 */
	protected function prepare_quote_for_response( $quote ) {
		return array(
			'id'               => $quote->ID,
			'status'           => $quote->post_status,
			'status_label'     => Askquote_Quote_Status::get_status_label( $quote->post_status ),
			'date'             => $quote->post_date,
			'date_gmt'         => $quote->post_date_gmt,
			'customer_name'    => get_post_meta( $quote->ID, '_askquote_customer_name', true ),
			'customer_email'   => get_post_meta( $quote->ID, '_askquote_customer_email', true ),
			'customer_phone'   => get_post_meta( $quote->ID, '_askquote_customer_phone', true ),
			'customer_company' => get_post_meta( $quote->ID, '_askquote_customer_company', true ),
			'message'          => get_post_meta( $quote->ID, '_askquote_message', true ),
			'admin_reply'      => get_post_meta( $quote->ID, '_askquote_admin_reply', true ),
			'items'            => askquote_get_quote_items( $quote->ID ),
		);
	}

	/**
	 * Sanitize the items array from the request.
	 *
	 * @param array $items Raw items array.
	 * @return array Sanitized items.
	 */
	private function sanitize_items( $items ) {
		$sanitized = array();
		foreach ( $items as $item ) {
			if ( ! is_array( $item ) || empty( $item['product_id'] ) ) {
				continue;
			}
			$sanitized[] = array(
				'product_id'   => absint( $item['product_id'] ),
				'variation_id' => absint( isset( $item['variation_id'] ) ? $item['variation_id'] : 0 ),
				'quantity'     => max( 1, absint( isset( $item['quantity'] ) ? $item['quantity'] : 1 ) ),
			);
		}
		return $sanitized;
	}

	/**
	 * Get schema for creating a quote.
	 *
	 * @return array
	 */
	private function get_create_item_schema() {
		return array(
			'customer_name'    => array(
				'required'          => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'customer_email'   => array(
				'required'          => true,
				'type'              => 'string',
				'format'            => 'email',
				'sanitize_callback' => 'sanitize_email',
			),
			'customer_phone'   => array(
				'required'          => false,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'customer_company' => array(
				'required'          => false,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'message'          => array(
				'required'          => false,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_textarea_field',
			),
			'items'            => array(
				'required' => true,
				'type'     => 'array',
				'items'    => array(
					'type'       => 'object',
					'properties' => array(
						'product_id'   => array( 'type' => 'integer', 'required' => true ),
						'variation_id' => array( 'type' => 'integer', 'required' => false ),
						'quantity'     => array( 'type' => 'integer', 'required' => false ),
					),
				),
			),
		);
	}

	/**
	 * Get the item schema for REST API documentation.
	 *
	 * @return array
	 */
	public function get_public_item_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'askquote_quote',
			'type'       => 'object',
			'properties' => array(
				'id'               => array( 'type' => 'integer', 'readonly' => true ),
				'status'           => array( 'type' => 'string' ),
				'status_label'     => array( 'type' => 'string', 'readonly' => true ),
				'date'             => array( 'type' => 'string', 'format' => 'date-time', 'readonly' => true ),
				'customer_name'    => array( 'type' => 'string' ),
				'customer_email'   => array( 'type' => 'string', 'format' => 'email' ),
				'customer_phone'   => array( 'type' => 'string' ),
				'customer_company' => array( 'type' => 'string' ),
				'message'          => array( 'type' => 'string' ),
				'admin_reply'      => array( 'type' => 'string' ),
				'items'            => array( 'type' => 'array' ),
			),
		);
	}
}
