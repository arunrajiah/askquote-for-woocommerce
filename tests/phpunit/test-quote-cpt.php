<?php
/**
 * Tests for the Quote CPT and status helpers.
 *
 * @package AskQuote
 */

/**
 * Class Test_Quote_CPT
 */
class Test_Quote_CPT extends WP_UnitTestCase {

	/**
	 * Instance of the CPT class.
	 *
	 * @var Askquote_Quote_CPT
	 */
	private $cpt;

	/**
	 * Set up test fixtures.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
		$this->cpt = new Askquote_Quote_CPT();
		$this->cpt->register();
		$this->cpt->register_statuses();
	}

	/**
	 * CPT should be registered.
	 *
	 * @return void
	 */
	public function test_cpt_is_registered() {
		$this->assertTrue( post_type_exists( 'askquote_quote' ) );
	}

	/**
	 * CPT should not be publicly queryable.
	 *
	 * @return void
	 */
	public function test_cpt_is_not_public() {
		$cpt = get_post_type_object( 'askquote_quote' );
		$this->assertFalse( $cpt->publicly_queryable );
	}

	/**
	 * All four custom statuses should be registered.
	 *
	 * @return void
	 */
	public function test_custom_statuses_registered() {
		$expected = array( 'aq-pending', 'aq-replied', 'aq-approved', 'aq-closed' );
		foreach ( $expected as $status ) {
			$this->assertNotNull( get_post_status_object( $status ), "Status '{$status}' should be registered." );
		}
	}

	/**
	 * Askquote_Quote_Status::get_all_statuses() should return four statuses.
	 *
	 * @return void
	 */
	public function test_get_all_statuses_returns_four_entries() {
		$statuses = Askquote_Quote_Status::get_all_statuses();
		$this->assertCount( 4, $statuses );
	}

	/**
	 * is_valid_status() returns true for valid slugs.
	 *
	 * @return void
	 */
	public function test_is_valid_status_returns_true_for_valid() {
		$this->assertTrue( Askquote_Quote_Status::is_valid_status( 'aq-pending' ) );
		$this->assertTrue( Askquote_Quote_Status::is_valid_status( 'aq-approved' ) );
	}

	/**
	 * is_valid_status() returns false for invalid slugs.
	 *
	 * @return void
	 */
	public function test_is_valid_status_returns_false_for_invalid() {
		$this->assertFalse( Askquote_Quote_Status::is_valid_status( 'publish' ) );
		$this->assertFalse( Askquote_Quote_Status::is_valid_status( 'invalid-status' ) );
	}

	/**
	 * get_status_label() returns human-readable label.
	 *
	 * @return void
	 */
	public function test_get_status_label() {
		$label = Askquote_Quote_Status::get_status_label( 'aq-pending' );
		$this->assertStringContainsString( 'Pending', $label );
	}

	/**
	 * get_allowed_transitions() returns array for known status.
	 *
	 * @return void
	 */
	public function test_get_allowed_transitions_from_pending() {
		$transitions = Askquote_Quote_Status::get_allowed_transitions( 'aq-pending' );
		$this->assertIsArray( $transitions );
		$this->assertNotEmpty( $transitions );
	}
}
