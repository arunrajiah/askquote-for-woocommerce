<?php
/**
 * Customer quote received — plain text email template.
 *
 * @package AskQuote
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables.

echo '= ' . esc_html( $email_heading ) . " =\n\n";

printf(
	/* translators: %s: customer name */
	esc_html__( 'Hi %s,', 'askquote-for-woocommerce' ),
	esc_html( get_post_meta( $quote->ID, '_askquote_customer_name', true ) )
);
echo "\n\n";
echo esc_html__( 'Thank you for your quote request. We have received it and will get back to you shortly.', 'askquote-for-woocommerce' );
echo "\n\n";
echo "= " . esc_html__( 'Your Quote Details', 'askquote-for-woocommerce' ) . " =\n\n";

foreach ( $items as $item ) {
	$product_id = absint( $item->variation_id ? $item->variation_id : $item->product_id );
	$product    = wc_get_product( $product_id );
	$name       = $product ? $product->get_name() : '#' . absint( $item->product_id );
	printf( "- %s x %d\n", esc_html( $name ), absint( $item->quantity ) );
}

$message = get_post_meta( $quote->ID, '_askquote_message', true );
if ( $message ) {
	echo "\n= " . esc_html__( 'Your Message', 'askquote-for-woocommerce' ) . " =\n\n";
	echo esc_html( $message ) . "\n";
}

echo "\n";
printf(
	/* translators: %s: quote ID */
	esc_html__( 'Quote reference: #%s', 'askquote-for-woocommerce' ),
	absint( $quote->ID )
);
echo "\n\n";
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound,WordPress.Security.EscapeOutput.OutputNotEscaped
echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
