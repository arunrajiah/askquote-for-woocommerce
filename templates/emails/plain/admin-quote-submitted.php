<?php
/**
 * Admin new quote submitted — plain text email template.
 *
 * @package AskQuote
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables are intentionally short-named.

echo '= ' . esc_html( $email_heading ) . " =\n\n";

printf(
	/* translators: %s: customer name */
	esc_html__( 'A new quote request has been submitted by %s.', 'askquote-for-woocommerce' ),
	esc_html( get_post_meta( $quote->ID, '_askquote_customer_name', true ) )
);
echo "\n\n";

echo "= " . esc_html__( 'Customer Details', 'askquote-for-woocommerce' ) . " =\n\n";
/* translators: %s: customer full name */
printf( esc_html__( 'Name: %s', 'askquote-for-woocommerce' ) . "\n", esc_html( get_post_meta( $quote->ID, '_askquote_customer_name', true ) ) );
/* translators: %s: customer email address */
printf( esc_html__( 'Email: %s', 'askquote-for-woocommerce' ) . "\n", esc_html( get_post_meta( $quote->ID, '_askquote_customer_email', true ) ) );

$askquote_phone = get_post_meta( $quote->ID, '_askquote_customer_phone', true );
if ( $askquote_phone ) {
	/* translators: %s: customer phone number */
	printf( esc_html__( 'Phone: %s', 'askquote-for-woocommerce' ) . "\n", esc_html( $askquote_phone ) );
}
$askquote_company = get_post_meta( $quote->ID, '_askquote_customer_company', true );
if ( $askquote_company ) {
	/* translators: %s: company name */
	printf( esc_html__( 'Company: %s', 'askquote-for-woocommerce' ) . "\n", esc_html( $askquote_company ) );
}

echo "\n= " . esc_html__( 'Requested Products', 'askquote-for-woocommerce' ) . " =\n\n";

foreach ( $items as $askquote_item ) {
	$askquote_product_id = absint( $askquote_item->variation_id ? $askquote_item->variation_id : $askquote_item->product_id );
	$askquote_product    = wc_get_product( $askquote_product_id );
	$askquote_name       = $askquote_product ? $askquote_product->get_name() : '#' . absint( $askquote_item->product_id );
	printf( "- %s x %d\n", esc_html( $askquote_name ), absint( $askquote_item->quantity ) );
}

$askquote_message = get_post_meta( $quote->ID, '_askquote_message', true );
if ( $askquote_message ) {
	echo "\n= " . esc_html__( 'Customer Message', 'askquote-for-woocommerce' ) . " =\n\n";
	echo esc_html( $askquote_message ) . "\n";
}

echo "\n";
printf(
	/* translators: %s: URL to the quote in WP admin */
	esc_html__( 'View quote in admin: %s', 'askquote-for-woocommerce' ),
	esc_url( admin_url( 'post.php?post=' . absint( $quote->ID ) . '&action=edit' ) )
);
echo "\n\n";
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound,WordPress.Security.EscapeOutput.OutputNotEscaped
echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
