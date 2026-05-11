<?php
/**
 * Quote submission form template.
 *
 * @package AskQuote
 * @var array  $cart_items  Items from Askquote_Quote_Cart::get_items().
 * @var array  $fields      Form fields from Askquote_Quote_Form::get_form_fields().
 * @var array  $user_values Pre-populated values for logged-in users.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="askquote-form-wrapper">
	<?php if ( empty( $cart_items ) ) : ?>
		<p class="askquote-notice"><?php esc_html_e( 'Your quote cart is empty. Please add products to your quote cart before submitting.', 'askquote-for-woocommerce' ); ?></p>
	<?php else : ?>
		<form method="post" class="askquote-form woocommerce-form">
			<?php wp_nonce_field( 'askquote_submit_quote', 'askquote_form_nonce' ); ?>

			<div class="askquote-cart-summary">
				<h3><?php esc_html_e( 'Items in Your Quote', 'askquote-for-woocommerce' ); ?></h3>
				<ul>
					<?php foreach ( $cart_items as $item ) : ?>
					<?php
					$product_id   = absint( $item['product_id'] );
					$variation_id = absint( $item['variation_id'] );
					$product      = wc_get_product( $variation_id ? $variation_id : $product_id );
					if ( ! $product ) {
						continue;
					}
					?>
					<li><?php echo esc_html( $product->get_name() ); ?> &times; <?php echo absint( $item['quantity'] ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>

			<h3><?php esc_html_e( 'Your Details', 'askquote-for-woocommerce' ); ?></h3>

			<?php foreach ( $fields as $field_key => $field ) : ?>
			<div class="form-row <?php echo $field['required'] ? 'form-row-wide validate-required' : 'form-row-wide'; ?>">
				<label for="askquote-<?php echo esc_attr( $field_key ); ?>">
					<?php echo esc_html( $field['label'] ); ?>
					<?php if ( $field['required'] ) : ?><abbr class="required" title="<?php esc_attr_e( 'required', 'askquote-for-woocommerce' ); ?>">*</abbr><?php endif; ?>
				</label>
				<?php
				$val = isset( $user_values[ $field_key ] ) ? $user_values[ $field_key ] : '';
				if ( 'textarea' === $field['type'] ) :
				?>
				<textarea
					id="askquote-<?php echo esc_attr( $field_key ); ?>"
					name="<?php echo esc_attr( $field_key ); ?>"
					placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
					class="input-text"
					rows="5"
					<?php echo $field['required'] ? 'required' : ''; ?>><?php echo esc_textarea( $val ); ?></textarea>
				<?php else : ?>
				<input
					type="<?php echo esc_attr( $field['type'] ); ?>"
					id="askquote-<?php echo esc_attr( $field_key ); ?>"
					name="<?php echo esc_attr( $field_key ); ?>"
					value="<?php echo esc_attr( $val ); ?>"
					placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
					class="input-text"
					<?php echo $field['required'] ? 'required' : ''; ?>>
				<?php endif; ?>
			</div>
			<?php endforeach; ?>

			<div class="form-row">
				<button type="submit" name="askquote_submit" value="1" class="button askquote-submit-btn">
					<?php esc_html_e( 'Submit Quote Request', 'askquote-for-woocommerce' ); ?>
				</button>
			</div>
		</form>
	<?php endif; ?>
</div>
