<?php
/**
 * Tests for the REST API controller.
 *
 * @package AskQuote
 */

/**
 * Class Test_REST_API
 */
class Test_REST_API extends WP_Test_REST_TestCase {

	/**
	 * REST server instance.
	 *
	 * @var WP_REST_Server
	 */
	protected $server;

	/**
	 * Admin user ID.
	 *
	 * @var int
	 */
	protected $admin_id;

	/**
	 * Set up test fixtures.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		/** @var WP_REST_Server $wp_rest_server */
		global $wp_rest_server;
		$wp_rest_server = new WP_REST_Server();
		$this->server   = $wp_rest_server;
		do_action( 'rest_api_init' );

		$this->admin_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
	}

	/**
	 * Routes should be registered.
	 *
	 * @return void
	 */
	public function test_routes_are_registered() {
		$routes = $this->server->get_routes();
		$this->assertArrayHasKey( '/askquote/v1/quotes', $routes );
		$this->assertArrayHasKey( '/askquote/v1/quotes/(?P<id>[\d]+)', $routes );
		$this->assertArrayHasKey( '/askquote/v1/quotes/(?P<id>[\d]+)/status', $routes );
	}

	/**
	 * GET /quotes requires manage_woocommerce capability.
	 *
	 * @return void
	 */
	public function test_get_quotes_requires_authentication() {
		$request  = new WP_REST_Request( 'GET', '/askquote/v1/quotes' );
		$response = $this->server->dispatch( $request );
		$this->assertSame( 403, $response->get_status() );
	}

	/**
	 * Admin user can list quotes.
	 *
	 * @return void
	 */
	public function test_admin_can_get_quotes() {
		wp_set_current_user( $this->admin_id );
		$request  = new WP_REST_Request( 'GET', '/askquote/v1/quotes' );
		$response = $this->server->dispatch( $request );
		$this->assertSame( 200, $response->get_status() );
	}

	/**
	 * GET /quotes/{id} returns 404 for non-existent quote.
	 *
	 * @return void
	 */
	public function test_get_nonexistent_quote_returns_404() {
		wp_set_current_user( $this->admin_id );
		$request  = new WP_REST_Request( 'GET', '/askquote/v1/quotes/99999999' );
		$response = $this->server->dispatch( $request );
		$this->assertSame( 404, $response->get_status() );
	}

	/**
	 * Tear down REST server.
	 *
	 * @return void
	 */
	public function tear_down() {
		global $wp_rest_server;
		$wp_rest_server = null;
		parent::tear_down();
	}
}
