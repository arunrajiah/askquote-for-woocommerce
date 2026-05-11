<?php
/**
 * Meta box for the quote detail/edit screen.
 *
 * @package AskQuote
 */

defined( 'ABSPATH' ) || exit;

/**
 * Adds and saves a meta box on the askquote_quote post edit screen.
 */
class Askquote_Quote_Meta_Box {

	/**
	 * Register meta boxes.
	 *
	 * @return void
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'askquote-quote-details',
			__( 'Quote Details', 'askquote-for-woocommerce' ),
			array( $this, 'render_meta_box' ),
			'askquote_quote',
			'normal',
			'high'
		);
	}

	/**
	 * Render the quote detail meta box HTML.
	 *
	 * @param WP_Post $post The current post object.
	 * @return void
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'askquote_meta_box_save', 'askquote_meta_box_nonce' );

		$customer_name    = get_post_meta( $post->ID, '_askquote_customer_name', true );
		$customer_email   = get_post_meta( $post->ID, '_askquote_customer_email', true );
		$customer_phone   = get_post_meta( $post->ID, '_askquote_customer_phone', true );
		$customer_company = get_post_meta( $post->ID, '_askquote_customer_company', true );
		$message          = get_post_meta( $post->ID, '_askquote_message', true );
		$admin_reply      = get_post_meta( $post->ID, '_askquote_admin_reply', true );
		$current_status   = $post->post_status;
		$all_statuses     = Askquote_Quote_Status::get_all_statuses();
		$items            = askquote_get_quote_items( $post->ID );
		?>
		<div class="askquote-meta-box">

			<h3><?php esc_html_e( 'Customer Information', 'askquote-for-woocommerce' ); ?></h3>
			<table class="form-table">
				<tr>
					<th><label for="askquote_customer_name"><?php esc_html_e( 'Name', 'askquote-for-woocommerce' ); ?></label></th>
					<td>
						<input type="text" id="askquote_customer_name" name="askquote_customer_name"
							value="<?php echo esc_attr( $customer_name ); ?>" class="regular-text">
					</td>
				</tr>
				<tr>
					<th><label for="askquote_customer_email"><?php esc_html_e( 'Email', 'askquote-for-woocommerce' ); ?></label></th>
					<td>
						<input type="email" id="askquote_customer_email" name="askquote_customer_email"
							value="<?php echo esc_attr( $customer_email ); ?>" class="regular-text">
					</td>
				</tr>
				<tr>
					<th><label for="askquote_customer_phone"><?php esc_html_e( 'Phone', 'askquote-for-woocommerce' ); ?></label></th>
					<td>
						<input type="text" id="askquote_customer_phone" name="askquote_customer_phone"
							value="<?php echo esc_attr( $customer_phone ); ?>" class="regular-text">
					</td>
				</tr>
				<tr>
					<th><label for="askquote_customer_company"><?php esc_html_e( 'Company', 'askquote-for-woocommerce' ); ?></label></th>
					<td>
						<input type="text" id="askquote_customer_company" name="askquote_customer_company"
							value="<?php echo esc_attr( $customer_company ); ?>" class="regular-text">
					</td>
				</tr>
			</table>

			<h3><?php esc_html_e( 'Customer Message', 'askquote-for-woocommerce' ); ?></h3>
			<p><?php echo nl2br( esc_html( $message ? $message : __( '(No message)', 'askquote-for-woocommerce' ) ) ); ?></p>

			<h3><?php esc_html_e( 'Requested Products', 'askquote-for-woocommerce' ); ?></h3>
			<?php if ( $items ) : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Product', 'askquote-for-woocommerce' ); ?></th>
						<th><?php esc_html_e( 'SKU', 'askquote-for-woocommerce' ); ?></th>
						<th><?php esc_html_e( 'Qty', 'askquote-for-woocommerce' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $items as $item ) : ?>
					<tr>
						<td>
							<?php
							$product_id   = absint( $item->variation_id ? $item->variation_id : $item->product_id );
							$product      = wc_get_product( $product_id );
							if ( $product ) {
								echo '<a href="' . esc_url( get_edit_post_link( $item->product_id ) ) . '">' . esc_html( $product->get_name() ) . '</a>';
							} else {
								/* translators: %d: product ID */
								echo esc_html( sprintf( __( 'Product #%d (deleted)', 'askquote-for-woocommerce' ), absint( $item->product_id ) ) );
							}
							?>
						</td>
						<td><?php echo $product ? esc_html( $product->get_sku() ) : '&mdash;'; ?></td>
						<td><?php echo absint( $item->quantity ); ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php else : ?>
				<p><?php esc_html_e( 'No items found.', 'askquote-for-woocommerce' ); ?></p>
			<?php endif; ?>

			<h3><?php esc_html_e( 'Quote Status', 'askquote-for-woocommerce' ); ?></h3>
			<select name="askquote_quote_status" id="askquote_quote_status">
				<?php foreach ( $all_statuses as $slug => $data ) : ?>
					<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $current_status, $slug ); ?>>
						<?php echo esc_html( $data['label'] ); ?>
					</option>
				<?php endforeach; ?>
			</select>

			<h3><?php esc_html_e( 'Admin Reply', 'askquote-for-woocommerce' ); ?></h3>
			<textarea name="askquote_admin_reply" id="askquote_admin_reply" rows="5" class="large-text"><?php echo esc_textarea( $admin_reply ); ?></textarea>
			<p class="description"><?php esc_html_e( 'This reply may be included in the email sent to the customer.', 'askquote-for-woocommerce' ); ?></p>

		</div>
		<?php
	}

	/**
	 * Save the meta box fields when the post is saved.
	 *
	 * @param int     $post_id Post ID being saved.
	 * @param WP_Post $post    Post object.
	 * @return void
	 */
	public function save_meta_box( $post_id, $post ) {
		// Nonce check.
		if ( ! isset( $_POST['askquote_meta_box_nonce'] ) ||
			! wp_verify_nonce( sanitize_key( $_POST['askquote_meta_box_nonce'] ), 'askquote_meta_box_save' ) ) {
			return;
		}

		// Bail on autosave / ajax / non-publish capability.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save customer fields.
		if ( isset( $_POST['askquote_customer_name'] ) ) {
			update_post_meta( $post_id, '_askquote_customer_name', sanitize_text_field( wp_unslash( $_POST['askquote_customer_name'] ) ) );
		}
		if ( isset( $_POST['askquote_customer_email'] ) ) {
			update_post_meta( $post_id, '_askquote_customer_email', sanitize_email( wp_unslash( $_POST['askquote_customer_email'] ) ) );
		}
		if ( isset( $_POST['askquote_customer_phone'] ) ) {
			update_post_meta( $post_id, '_askquote_customer_phone', sanitize_text_field( wp_unslash( $_POST['askquote_customer_phone'] ) ) );
		}
		if ( isset( $_POST['askquote_customer_company'] ) ) {
			update_post_meta( $post_id, '_askquote_customer_company', sanitize_text_field( wp_unslash( $_POST['askquote_customer_company'] ) ) );
		}

		// Save admin reply.
		if ( isset( $_POST['askquote_admin_reply'] ) ) {
			update_post_meta( $post_id, '_askquote_admin_reply', sanitize_textarea_field( wp_unslash( $_POST['askquote_admin_reply'] ) ) );
		}

		// Update quote status.
		if ( isset( $_POST['askquote_quote_status'] ) ) {
			$new_status = sanitize_key( wp_unslash( $_POST['askquote_quote_status'] ) );
			if ( Askquote_Quote_Status::is_valid_status( $new_status ) && $post->post_status !== $new_status ) {
				// Unhook to avoid recursion.
				remove_action( 'save_post_askquote_quote', array( $this, 'save_meta_box' ), 10 );
				askquote_update_quote_status( $post_id, $new_status );
				add_action( 'save_post_askquote_quote', array( $this, 'save_meta_box' ), 10, 2 );
			}
		}
	}
}
