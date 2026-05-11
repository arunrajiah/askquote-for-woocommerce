<?php
/**
 * Customer quote approved — plain text email template.
 *
 * @package AskQuote
 */

defined( 'ABSPATH' ) || exit;

echo '= ' . esc_html( $email_heading ) . " =\n\n";

printf(
	/* translators: %s: customer name */
	esc_html__( 'Hi %s,', 'askquote-for-woocommerce' ),
	esc_html( get_post_meta( $quote->ID, '_askquote_customer_name', true ) )
);
echo "\n\n";
echo esc_html__( 'Great news! Your quote request has been approved.', 'askquote-for-woocommerce' );
echo "\n\n";
echo "= " . esc_html__( 'Approved Products', 'askquote-for-woocommerce' ) . " =\n\n";

foreach ( $items as $item ) {
	$product_id = absint( $item->variation_id ? $item->variation_id : $item->product_id );
	$product    = wc_get_product( $product_id );
	$name       = $product ? $product->get_name() : '#' . absint( $item->product_id );
	printf( "- %s x %d\n", esc_html( $name ), absint( $item->quantity ) );
}

if ( ! empty( $admin_reply ) ) {
	echo "\n= " . esc_html__( 'Message from Us', 'askquote-for-woocommerce' ) . " =\n\n";
	echo esc_html( $admin_reply ) . "\n";
}

echo "\n";
printf(
	/* translators: %s: quote ID */
	esc_html__( 'Quote reference: #%s', 'askquote-for-woocommerce' ),
	absint( $quote->ID )
);
echo "\n\n";
echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
