<?php
/**
 * My Account — quotes list template.
 *
 * @package AskQuote
 * @var WP_Post[] $quotes Array of quote post objects.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="askquote-my-account-quotes">
	<?php if ( empty( $quotes ) ) : ?>
		<p><?php esc_html_e( 'You have not submitted any quote requests yet.', 'askquote-for-woocommerce' ); ?></p>
	<?php else : ?>
		<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Quote #', 'askquote-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Date', 'askquote-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Status', 'askquote-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Items', 'askquote-for-woocommerce' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $quotes as $quote ) : ?>
				<?php
				$items       = askquote_get_quote_items( $quote->ID );
				$item_count  = count( $items );
				$status      = $quote->post_status;
				$status_label = Askquote_Quote_Status::get_status_label( $status );
				?>
				<tr>
					<td>
						<strong>#<?php echo absint( $quote->ID ); ?></strong>
					</td>
					<td><?php echo esc_html( get_the_date( get_option( 'date_format' ), $quote ) ); ?></td>
					<td>
						<span class="askquote-status askquote-status-<?php echo esc_attr( $status ); ?>">
							<?php echo esc_html( $status_label ); ?>
						</span>
					</td>
					<td>
						<?php
						echo esc_html(
							sprintf(
								/* translators: %d: number of items */
								_n( '%d item', '%d items', $item_count, 'askquote-for-woocommerce' ),
								$item_count
							)
						);
						?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
