<?php
/**
 * Quote button template.
 *
 * @package AskQuote
 * @var array $args Template arguments.
 */

defined( 'ABSPATH' ) || exit;
?>
<a href="<?php echo esc_url( $args['cart_url'] ); ?>"
   class="askquote-btn button"
   data-product-id="<?php echo esc_attr( $args['product_id'] ); ?>"
   data-nonce="<?php echo esc_attr( wp_create_nonce( 'askquote_frontend_nonce' ) ); ?>">
	<?php echo esc_html( $args['label'] ); ?>
</a>
