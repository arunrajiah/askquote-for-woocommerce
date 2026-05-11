<?php
/**
 * Admin new quote submitted — plain text email template.
 *
 * @package AskQuote
 */

defined( 'ABSPATH' ) || exit;

echo '= ' . esc_html( $email_heading ) . " =\n\n";

printf(
	/* translators: %s: customer name */
	esc_html__( 'A new quote request has been submitted by %s.', 'askquote-for-woocommerce' ),
	esc_html( get_post_meta( $quote->ID, '_askquote_customer_name', true ) )
);
echo "\n\n";

echo "= " . esc_html__( 'Customer Details', 'askquote-for-woocommerce' ) . " =\n\n";
printf( esc_html__( 'Name: %s', 'askquote-for-woocommerce' ) . "\n", esc_html( get_post_meta( $quote->ID, '_askquote_customer_name', true ) ) );
printf( esc_html__( 'Email: %s', 'askquote-for-woocommerce' ) . "\n", esc_html( get_post_meta( $quote->ID, '_askquote_customer_email', true ) ) );

$phone = get_post_meta( $quote->ID, '_askquote_customer_phone', true );
if ( $phone ) {
	printf( esc_html__( 'Phone: %s', 'askquote-for-woocommerce' ) . "\n", esc_html( $phone ) );
}
$company = get_post_meta( $quote->ID, '_askquote_customer_company', true );
if ( $company ) {
	printf( esc_html__( 'Company: %s', 'askquote-for-woocommerce' ) . "\n", esc_html( $company ) );
}

echo "\n= " . esc_html__( 'Requested Products', 'askquote-for-woocommerce' ) . " =\n\n";

foreach ( $items as $item ) {
	$product_id = absint( $item->variation_id ? $item->variation_id : $item->product_id );
	$product    = wc_get_product( $product_id );
	$name       = $product ? $product->get_name() : '#' . absint( $item->product_id );
	printf( "- %s x %d\n", esc_html( $name ), absint( $item->quantity ) );
}

$message = get_post_meta( $quote->ID, '_askquote_message', true );
if ( $message ) {
	echo "\n= " . esc_html__( 'Customer Message', 'askquote-for-woocommerce' ) . " =\n\n";
	echo esc_html( $message ) . "\n";
}

echo "\n";
printf(
	/* translators: %s: admin URL */
	esc_html__( 'View quote in admin: %s', 'askquote-for-woocommerce' ),
	esc_url( admin_url( 'post.php?post=' . absint( $quote->ID ) . '&action=edit' ) )
);
echo "\n\n";
echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
