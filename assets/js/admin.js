/**
 * AskQuote for WooCommerce — Admin JavaScript
 *
 * @package AskQuote
 */

/* global askquoteAdmin, jQuery, wp */

( function ( $ ) {
	'use strict';

	var AskQuoteAdmin = {

		/**
		 * Initialise all admin behaviours.
		 */
		init: function () {
			this.initColorPicker();
			this.bindStatusChangeConfirm();
			this.bindBulkActionConfirm();
			this.bindDismissNotice();
		},

		/**
		 * Initialise WordPress color picker on color fields.
		 */
		initColorPicker: function () {
			$( '.askquote-color-picker' ).wpColorPicker();
		},

		/**
		 * Confirm before changing a quote status via the meta box dropdown.
		 */
		bindStatusChangeConfirm: function () {
			$( '#askquote_quote_status' ).on( 'change', function () {
				if ( ! window.confirm( askquoteAdmin.confirmStatusChange ) ) {
					// Revert to previous value.
					$( this ).val( $( this ).data( 'original-value' ) );
				}
			} ).each( function () {
				$( this ).data( 'original-value', $( this ).val() );
			} );
		},

		/**
		 * Confirm before performing destructive bulk actions.
		 */
		bindBulkActionConfirm: function () {
			$( '#doaction, #doaction2' ).on( 'click', function ( e ) {
				var action = $( this ).prev( 'select' ).val();
				if ( 'delete' === action ) {
					if ( ! window.confirm( askquoteAdmin.confirmDelete ) ) {
						e.preventDefault();
					}
				}
			} );
		},

		/**
		 * AJAX dismiss the "advanced features" notice.
		 */
		bindDismissNotice: function () {
			$( document ).on( 'click', '.askquote-advanced-notice .notice-dismiss', function () {
				var nonce = $( this ).closest( '.askquote-advanced-notice' ).data( 'nonce' );
				$.ajax( {
					url:    askquoteAdmin.ajaxUrl,
					method: 'POST',
					data: {
						action: 'askquote_dismiss_notice',
						nonce:  nonce
					}
				} );
			} );
		}
	};

	$( function () {
		AskQuoteAdmin.init();
	} );

}( jQuery ) );
