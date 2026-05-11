/**
 * AskQuote for WooCommerce — Frontend JavaScript
 *
 * @package AskQuote
 */

/* global askquoteFrontend, jQuery */

( function ( $ ) {
	'use strict';

	var AskQuote = {

		/**
		 * Initialise all frontend behaviours.
		 */
		init: function () {
			this.bindAddToQuote();
			this.bindRemoveFromQuote();
			this.bindUpdateQuantity();
		},

		/**
		 * Handle "Add to Quote" button click.
		 */
		bindAddToQuote: function () {
			$( document ).on( 'click', '.askquote-btn', function ( e ) {
				e.preventDefault();

				var $btn       = $( this );
				var productId  = $btn.data( 'product-id' );

				if ( ! productId ) {
					// If no product-id, it's a link to the cart page — follow it.
					window.location.href = $btn.attr( 'href' );
					return;
				}

				if ( $btn.hasClass( 'loading' ) ) {
					return;
				}

				$btn.addClass( 'loading' );

				$.ajax( {
					url:    askquoteFrontend.ajaxUrl,
					method: 'POST',
					data: {
						action:       'askquote_add_to_quote',
						nonce:        askquoteFrontend.nonce,
						product_id:   productId,
						variation_id: $btn.data( 'variation-id' ) || 0,
						quantity:     1
					},
					success: function ( response ) {
						if ( response.success ) {
							AskQuote.updateBadge( response.data.cart_count );
							AskQuote.showMessage( $btn, askquoteFrontend.addedText );
						} else {
							AskQuote.showMessage( $btn, response.data.message || askquoteFrontend.errorText );
						}
					},
					error: function () {
						AskQuote.showMessage( $btn, askquoteFrontend.errorText );
					},
					complete: function () {
						$btn.removeClass( 'loading' );
					}
				} );
			} );
		},

		/**
		 * Handle "Remove item" click in the quote cart.
		 */
		bindRemoveFromQuote: function () {
			$( document ).on( 'click', '.askquote-remove-item', function () {
				var $btn     = $( this );
				var itemKey  = $btn.data( 'item-key' );
				var nonce    = $btn.data( 'nonce' );

				$.ajax( {
					url:    askquoteFrontend.ajaxUrl,
					method: 'POST',
					data: {
						action:   'askquote_remove_from_quote',
						nonce:    nonce,
						item_key: itemKey
					},
					success: function ( response ) {
						if ( response.success ) {
							$btn.closest( 'tr' ).fadeOut( 300, function () {
								$( this ).remove();
							} );
							AskQuote.updateBadge( response.data.cart_count );
						}
					}
				} );
			} );
		},

		/**
		 * Handle quantity change in the quote cart.
		 */
		bindUpdateQuantity: function () {
			var timer;
			$( document ).on( 'change', '.askquote-qty-input', function () {
				var $input  = $( this );
				var itemKey = $input.data( 'item-key' );
				var nonce   = $input.data( 'nonce' );
				var qty     = parseInt( $input.val(), 10 );

				clearTimeout( timer );
				timer = setTimeout( function () {
					$.ajax( {
						url:    askquoteFrontend.ajaxUrl,
						method: 'POST',
						data: {
							action:   'askquote_update_quote_qty',
							nonce:    nonce,
							item_key: itemKey,
							quantity: qty
						},
						success: function ( response ) {
							if ( response.success ) {
								AskQuote.updateBadge( response.data.cart_count );
							}
						}
					} );
				}, 500 );
			} );
		},

		/**
		 * Update all count badges on the page.
		 *
		 * @param {number} count New item count.
		 */
		updateBadge: function ( count ) {
			$( '.askquote-count-badge' ).text( count );
			if ( 0 === count ) {
				$( '.askquote-count-badge' ).hide();
			} else {
				$( '.askquote-count-badge' ).show();
			}
		},

		/**
		 * Show a brief feedback message next to the button.
		 *
		 * @param {jQuery} $btn   The button element.
		 * @param {string} text   Message text.
		 */
		showMessage: function ( $btn, text ) {
			var $msg = $( '<span class="askquote-inline-msg"></span>' ).text( text );
			$btn.after( $msg );
			setTimeout( function () {
				$msg.fadeOut( 400, function () { $( this ).remove(); } );
			}, 2500 );
		}
	};

	$( function () {
		AskQuote.init();
	} );

}( jQuery ) );
