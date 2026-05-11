<?php
/**
 * Customer quote approved — HTML email template.
 *
 * @package AskQuote
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p>
	<?php
	printf(
		/* translators: %s: customer name */
		esc_html__( 'Hi %s,', 'askquote-for-woocommerce' ),
		esc_html( get_post_meta( $quote->ID, '_askquote_customer_name', true ) )
	);
	?>
</p>
<p><?php esc_html_e( 'Great news! Your quote request has been approved. Please see the details below.', 'askquote-for-woocommerce' ); ?></p>

<h2><?php esc_html_e( 'Approved Quote Details', 'askquote-for-woocommerce' ); ?></h2>

<table cellspacing="0" cellpadding="6" style="width:100%;border-collapse:collapse;" border="1">
	<thead>
		<tr>
			<th style="text-align:left;padding:8px;"><?php esc_html_e( 'Product', 'askquote-for-woocommerce' ); ?></th>
			<th style="text-align:center;padding:8px;"><?php esc_html_e( 'Qty', 'askquote-for-woocommerce' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $items as $item ) : ?>
		<tr>
			<td style="padding:8px;">
				<?php
				$product_id = absint( $item->variation_id ? $item->variation_id : $item->product_id );
				$product    = wc_get_product( $product_id );
				echo $product ? esc_html( $product->get_name() ) : esc_html( '#' . $item->product_id );
				?>
			</td>
			<td style="text-align:center;padding:8px;"><?php echo absint( $item->quantity ); ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<?php if ( ! empty( $admin_reply ) ) : ?>
<h3><?php esc_html_e( 'Message from Us', 'askquote-for-woocommerce' ); ?></h3>
<p><?php echo nl2br( esc_html( $admin_reply ) ); ?></p>
<?php endif; ?>

<p>
	<?php
	printf(
		/* translators: %s: quote ID */
		esc_html__( 'Quote reference: #%s', 'askquote-for-woocommerce' ),
		absint( $quote->ID )
	);
	?>
</p>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
