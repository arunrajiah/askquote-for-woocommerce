<?php
/**
 * Session-based quote cart.
 *
 * @package AskQuote
 */

defined( 'ABSPATH' ) || exit;

/**
 * Manages the quote cart stored in the WooCommerce session.
 */
class Askquote_Quote_Cart {

	/**
	 * Session key for quote cart data.
	 *
	 * @var string
	 */
	const SESSION_KEY = 'askquote_cart';

	/**
	 * Add a product to the quote cart.
	 *
	 * @param int $product_id   Product ID.
	 * @param int $variation_id Variation ID (0 if not a variation).
	 * @param int $quantity     Quantity.
	 * @return string|false The item key on success, false on failure.
	 */
	public function add_item( $product_id, $variation_id = 0, $quantity = 1 ) {
		$product_id   = absint( $product_id );
		$variation_id = absint( $variation_id );
		$quantity     = max( 1, absint( $quantity ) );

		if ( ! $product_id || ! wc_get_product( $product_id ) ) {
			return false;
		}

		$cart     = $this->get_items();
		$item_key = md5( $product_id . '_' . $variation_id );

		if ( isset( $cart[ $item_key ] ) ) {
			$cart[ $item_key ]['quantity'] += $quantity;
		} else {
			$cart[ $item_key ] = array(
				'product_id'   => $product_id,
				'variation_id' => $variation_id,
				'quantity'     => $quantity,
			);
		}

		$this->save( $cart );
		return $item_key;
	}

	/**
	 * Remove an item from the quote cart by key.
	 *
	 * @param string $item_key Cart item key.
	 * @return bool True if removed, false if not found.
	 */
	public function remove_item( $item_key ) {
		$cart = $this->get_items();
		if ( isset( $cart[ $item_key ] ) ) {
			unset( $cart[ $item_key ] );
			$this->save( $cart );
			return true;
		}
		return false;
	}

	/**
	 * Update the quantity of a cart item.
	 *
	 * @param string $item_key Cart item key.
	 * @param int    $quantity New quantity.
	 * @return bool True on success, false if item not found.
	 */
	public function update_quantity( $item_key, $quantity ) {
		$quantity = absint( $quantity );
		$cart     = $this->get_items();

		if ( ! isset( $cart[ $item_key ] ) ) {
			return false;
		}

		if ( 0 === $quantity ) {
			return $this->remove_item( $item_key );
		}

		$cart[ $item_key ]['quantity'] = $quantity;
		$this->save( $cart );
		return true;
	}

	/**
	 * Get all items from the quote cart.
	 *
	 * @return array
	 */
	public function get_items() {
		if ( ! WC()->session ) {
			return array();
		}
		$cart = WC()->session->get( self::SESSION_KEY );
		return is_array( $cart ) ? $cart : array();
	}

	/**
	 * Clear all items from the quote cart.
	 *
	 * @return void
	 */
	public function clear() {
		if ( WC()->session ) {
			WC()->session->set( self::SESSION_KEY, array() );
		}
	}

	/**
	 * Count the number of items (lines) in the quote cart.
	 *
	 * @return int
	 */
	public function count_items() {
		return count( $this->get_items() );
	}

	/**
	 * Save the cart array to the session.
	 *
	 * @param array $cart Cart data to save.
	 * @return void
	 */
	private function save( $cart ) {
		if ( WC()->session ) {
			WC()->session->set( self::SESSION_KEY, $cart );
		}
	}

	/**
	 * AJAX: add product to quote cart.
	 *
	 * @return void
	 */
	public function ajax_add_to_quote() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'askquote_frontend_nonce' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Security check failed.', 'askquote-for-woocommerce' ) ), 403 );
		}

		$product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
		$quantity     = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1;

		if ( ! $product_id ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid product.', 'askquote-for-woocommerce' ) ), 400 );
		}

		$item_key = $this->add_item( $product_id, $variation_id, $quantity );

		if ( false === $item_key ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Could not add product to quote.', 'askquote-for-woocommerce' ) ), 400 );
		}

		wp_send_json_success(
			array(
				'item_key'   => $item_key,
				'cart_count' => $this->count_items(),
				'message'    => esc_html__( 'Product added to quote cart.', 'askquote-for-woocommerce' ),
			)
		);
	}

	/**
	 * AJAX: remove item from quote cart.
	 *
	 * @return void
	 */
	public function ajax_remove_from_quote() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'askquote_frontend_nonce' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Security check failed.', 'askquote-for-woocommerce' ) ), 403 );
		}

		$item_key = isset( $_POST['item_key'] ) ? sanitize_key( $_POST['item_key'] ) : '';

		if ( ! $item_key ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid item key.', 'askquote-for-woocommerce' ) ), 400 );
		}

		$removed = $this->remove_item( $item_key );

		if ( ! $removed ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Item not found in quote cart.', 'askquote-for-woocommerce' ) ), 404 );
		}

		wp_send_json_success(
			array(
				'cart_count' => $this->count_items(),
				'message'    => esc_html__( 'Item removed from quote cart.', 'askquote-for-woocommerce' ),
			)
		);
	}

	/**
	 * AJAX: update item quantity in quote cart.
	 *
	 * @return void
	 */
	public function ajax_update_quantity() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'askquote_frontend_nonce' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Security check failed.', 'askquote-for-woocommerce' ) ), 403 );
		}

		$item_key = isset( $_POST['item_key'] ) ? sanitize_key( $_POST['item_key'] ) : '';
		$quantity = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 0;

		if ( ! $item_key ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid item key.', 'askquote-for-woocommerce' ) ), 400 );
		}

		$updated = $this->update_quantity( $item_key, $quantity );

		if ( ! $updated ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Could not update item quantity.', 'askquote-for-woocommerce' ) ), 400 );
		}

		wp_send_json_success(
			array(
				'cart_count' => $this->count_items(),
				'message'    => esc_html__( 'Quantity updated.', 'askquote-for-woocommerce' ),
			)
		);
	}
}
