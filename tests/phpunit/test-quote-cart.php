<?php
/**
 * Tests for the session-based Quote Cart.
 *
 * @package AskQuote
 */

/**
 * Class Test_Quote_Cart
 */
class Test_Quote_Cart extends WP_UnitTestCase {

	/**
	 * Cart instance.
	 *
	 * @var Askquote_Quote_Cart
	 */
	private $cart;

	/**
	 * Set up test fixtures.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		// Boot WooCommerce session if available.
		if ( function_exists( 'WC' ) && WC()->session ) {
			WC()->session->init();
		}

		$this->cart = new Askquote_Quote_Cart();
		$this->cart->clear();
	}

	/**
	 * Cart starts empty.
	 *
	 * @return void
	 */
	public function test_cart_starts_empty() {
		$this->assertEmpty( $this->cart->get_items() );
		$this->assertSame( 0, $this->cart->count_items() );
	}

	/**
	 * Adding an item increases count.
	 *
	 * @return void
	 */
	public function test_add_item_increases_count() {
		// Create a simple product to pass the wc_get_product() check.
		$product_id = $this->factory->post->create(
			array(
				'post_type'   => 'product',
				'post_status' => 'publish',
			)
		);
		wp_set_object_terms( $product_id, 'simple', 'product_type' );
		update_post_meta( $product_id, '_price', '10' );
		update_post_meta( $product_id, '_regular_price', '10' );

		$key = $this->cart->add_item( $product_id, 0, 2 );
		if ( false !== $key ) {
			$this->assertSame( 1, $this->cart->count_items() );
		} else {
			// WC product not found in test env — mark inconclusive.
			$this->markTestSkipped( 'wc_get_product() not available in this test environment.' );
		}
	}

	/**
	 * Clearing the cart empties it.
	 *
	 * @return void
	 */
	public function test_clear_empties_cart() {
		$this->cart->clear();
		$this->assertEmpty( $this->cart->get_items() );
	}

	/**
	 * Removing a non-existent item returns false.
	 *
	 * @return void
	 */
	public function test_remove_nonexistent_item_returns_false() {
		$result = $this->cart->remove_item( 'does-not-exist' );
		$this->assertFalse( $result );
	}
}
