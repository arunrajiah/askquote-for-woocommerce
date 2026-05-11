<?php
/**
 * Quote status helper class.
 *
 * @package AskQuote
 */

defined( 'ABSPATH' ) || exit;

/**
 * Provides constants and helper methods for quote statuses.
 */
class Askquote_Quote_Status {

	const PENDING  = 'aq-pending';
	const REPLIED  = 'aq-replied';
	const APPROVED = 'aq-approved';
	const CLOSED   = 'aq-closed';

	/**
	 * Get all registered statuses with metadata.
	 *
	 * @return array Associative array keyed by status slug.
	 */
	public static function get_all_statuses() {
		$statuses = array(
			self::PENDING  => array(
				'label'       => __( 'Pending', 'askquote-for-woocommerce' ),
				'description' => __( 'Quote request received, awaiting response.', 'askquote-for-woocommerce' ),
				'color'       => '#f0ad4e',
			),
			self::REPLIED  => array(
				'label'       => __( 'Replied', 'askquote-for-woocommerce' ),
				'description' => __( 'Admin has replied to the quote request.', 'askquote-for-woocommerce' ),
				'color'       => '#5bc0de',
			),
			self::APPROVED => array(
				'label'       => __( 'Approved', 'askquote-for-woocommerce' ),
				'description' => __( 'Quote has been approved.', 'askquote-for-woocommerce' ),
				'color'       => '#5cb85c',
			),
			self::CLOSED   => array(
				'label'       => __( 'Closed', 'askquote-for-woocommerce' ),
				'description' => __( 'Quote has been closed without approval.', 'askquote-for-woocommerce' ),
				'color'       => '#d9534f',
			),
		);

		return apply_filters( 'askquote_quote_statuses', $statuses );
	}

	/**
	 * Get the human-readable label for a given status slug.
	 *
	 * @param string $status Status slug.
	 * @return string Human-readable label, or the slug itself if not found.
	 */
	public static function get_status_label( $status ) {
		$statuses = self::get_all_statuses();
		return isset( $statuses[ $status ] ) ? $statuses[ $status ]['label'] : $status;
	}

	/**
	 * Check whether a given status slug is valid.
	 *
	 * @param string $status Status slug to check.
	 * @return bool
	 */
	public static function is_valid_status( $status ) {
		return array_key_exists( $status, self::get_all_statuses() );
	}

	/**
	 * Get the allowed next statuses from the current one.
	 *
	 * @param string $current Current status slug.
	 * @return array Array of allowed next status slugs.
	 */
	public static function get_allowed_transitions( $current ) {
		$transitions = array(
			self::PENDING  => array( self::REPLIED, self::APPROVED, self::CLOSED ),
			self::REPLIED  => array( self::APPROVED, self::CLOSED, self::PENDING ),
			self::APPROVED => array( self::CLOSED ),
			self::CLOSED   => array( self::PENDING ),
		);

		return isset( $transitions[ $current ] ) ? $transitions[ $current ] : array();
	}
}
