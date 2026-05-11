<?php
/**
 * Quote cart template.
 *
 * @package AskQuote
 * @var array $cart_items Quote cart items from Askquote_Quote_Cart::get_items().
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="askquote-cart-wrapper">
	<?php if ( empty( $cart_items ) ) : ?>
		<p class="askquote-cart-empty"><?php esc_html_e( 'Your quote cart is empty.', 'askquote-for-woocommerce' ); ?></p>
	<?php else : ?>
		<table class="askquote-cart-table shop_table">
			<thead>
				<tr>
					<th class="product-name"><?php esc_html_e( 'Product', 'askquote-for-woocommerce' ); ?></th>
					<th class="product-quantity"><?php esc_html_e( 'Quantity', 'askquote-for-woocommerce' ); ?></th>
					<th class="product-remove"><?php esc_html_e( 'Remove', 'askquote-for-woocommerce' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $cart_items as $item_key => $item ) : ?>
				<?php
				$product_id   = absint( $item['product_id'] );
				$variation_id = absint( $item['variation_id'] );
				$quantity     = absint( $item['quantity'] );
				$product      = wc_get_product( $variation_id ? $variation_id : $product_id );
				if ( ! $product ) {
					continue;
				}
				?>
				<tr class="askquote-cart-item" data-item-key="<?php echo esc_attr( $item_key ); ?>">
					<td class="product-name">
						<?php echo esc_html( $product->get_name() ); ?>
					</td>
					<td class="product-quantity">
						<div class="quantity">
							<label for="askquote-qty-<?php echo esc_attr( $item_key ); ?>" class="screen-reader-text">
								<?php esc_html_e( 'Quantity', 'askquote-for-woocommerce' ); ?>
							</label>
							<input
								type="number"
								id="askquote-qty-<?php echo esc_attr( $item_key ); ?>"
								class="askquote-qty-input input-text qty text"
								value="<?php echo absint( $quantity ); ?>"
								min="1"
								step="1"
								data-item-key="<?php echo esc_attr( $item_key ); ?>"
								data-nonce="<?php echo esc_attr( wp_create_nonce( 'askquote_frontend_nonce' ) ); ?>">
						</div>
					</td>
					<td class="product-remove">
						<button type="button"
							class="askquote-remove-item button"
							data-item-key="<?php echo esc_attr( $item_key ); ?>"
							data-nonce="<?php echo esc_attr( wp_create_nonce( 'askquote_frontend_nonce' ) ); ?>"
							aria-label="<?php esc_attr_e( 'Remove item', 'askquote-for-woocommerce' ); ?>">
							&times;
						</button>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<div class="askquote-cart-actions">
			<?php
			$form_page_id = get_option( 'askquote_form_page_id', 0 );
			if ( $form_page_id ) :
			?>
			<a href="<?php echo esc_url( get_permalink( $form_page_id ) ); ?>" class="button askquote-submit-btn">
				<?php esc_html_e( 'Submit Quote Request', 'askquote-for-woocommerce' ); ?>
			</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
