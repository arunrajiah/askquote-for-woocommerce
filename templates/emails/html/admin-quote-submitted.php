<?php
/**
 * Admin new quote submitted — HTML email template.
 *
 * @package AskQuote
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound,WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- template variables and WC hook calls.

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p>
	<?php
	printf(
		/* translators: %s: customer name */
		esc_html__( 'A new quote request has been submitted by %s.', 'askquote-for-woocommerce' ),
		esc_html( get_post_meta( $quote->ID, '_askquote_customer_name', true ) )
	);
	?>
</p>

<h2><?php esc_html_e( 'Customer Details', 'askquote-for-woocommerce' ); ?></h2>
<table cellspacing="0" cellpadding="6" style="width:100%;border-collapse:collapse;" border="1">
	<tbody>
		<tr>
			<th style="text-align:left;padding:8px;"><?php esc_html_e( 'Name', 'askquote-for-woocommerce' ); ?></th>
			<td style="padding:8px;"><?php echo esc_html( get_post_meta( $quote->ID, '_askquote_customer_name', true ) ); ?></td>
		</tr>
		<tr>
			<th style="text-align:left;padding:8px;"><?php esc_html_e( 'Email', 'askquote-for-woocommerce' ); ?></th>
			<td style="padding:8px;"><?php echo esc_html( get_post_meta( $quote->ID, '_askquote_customer_email', true ) ); ?></td>
		</tr>
		<?php $phone = get_post_meta( $quote->ID, '_askquote_customer_phone', true ); if ( $phone ) : ?>
		<tr>
			<th style="text-align:left;padding:8px;"><?php esc_html_e( 'Phone', 'askquote-for-woocommerce' ); ?></th>
			<td style="padding:8px;"><?php echo esc_html( $phone ); ?></td>
		</tr>
		<?php endif; ?>
		<?php $company = get_post_meta( $quote->ID, '_askquote_customer_company', true ); if ( $company ) : ?>
		<tr>
			<th style="text-align:left;padding:8px;"><?php esc_html_e( 'Company', 'askquote-for-woocommerce' ); ?></th>
			<td style="padding:8px;"><?php echo esc_html( $company ); ?></td>
		</tr>
		<?php endif; ?>
	</tbody>
</table>

<h2><?php esc_html_e( 'Requested Products', 'askquote-for-woocommerce' ); ?></h2>
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

<?php $message = get_post_meta( $quote->ID, '_askquote_message', true ); if ( $message ) : ?>
<h3><?php esc_html_e( 'Customer Message', 'askquote-for-woocommerce' ); ?></h3>
<p><?php echo nl2br( esc_html( $message ) ); ?></p>
<?php endif; ?>

<p>
	<a href="<?php echo esc_url( admin_url( 'post.php?post=' . absint( $quote->ID ) . '&action=edit' ) ); ?>" style="background:#0071a1;color:#fff;padding:10px 18px;text-decoration:none;display:inline-block;">
		<?php esc_html_e( 'View Quote in Admin', 'askquote-for-woocommerce' ); ?>
	</a>
</p>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
