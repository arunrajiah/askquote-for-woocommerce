<?php
/**
 * Tests for email classes.
 *
 * @package AskQuote
 */

/**
 * Class Test_Emails
 */
class Test_Emails extends WP_UnitTestCase {

	/**
	 * Email classes should instantiate correctly.
	 *
	 * @return void
	 */
	public function test_customer_quote_received_instantiates() {
		if ( ! class_exists( 'WC_Email' ) ) {
			$this->markTestSkipped( 'WooCommerce WC_Email class not available.' );
		}
		$email = new Askquote_Email_Customer_Quote_Received();
		$this->assertInstanceOf( 'WC_Email', $email );
		$this->assertSame( 'askquote_customer_quote_received', $email->id );
	}

	/**
	 * Admin quote submitted email instantiates correctly.
	 *
	 * @return void
	 */
	public function test_admin_quote_submitted_instantiates() {
		if ( ! class_exists( 'WC_Email' ) ) {
			$this->markTestSkipped( 'WooCommerce WC_Email class not available.' );
		}
		$email = new Askquote_Email_Admin_Quote_Submitted();
		$this->assertInstanceOf( 'WC_Email', $email );
		$this->assertSame( 'askquote_admin_quote_submitted', $email->id );
	}

	/**
	 * Customer quote approved email instantiates correctly.
	 *
	 * @return void
	 */
	public function test_customer_quote_approved_instantiates() {
		if ( ! class_exists( 'WC_Email' ) ) {
			$this->markTestSkipped( 'WooCommerce WC_Email class not available.' );
		}
		$email = new Askquote_Email_Customer_Quote_Approved();
		$this->assertInstanceOf( 'WC_Email', $email );
		$this->assertSame( 'askquote_customer_quote_approved', $email->id );
	}

	/**
	 * Email manager adds emails to WC email classes.
	 *
	 * @return void
	 */
	public function test_email_manager_adds_emails() {
		if ( ! class_exists( 'WC_Email' ) ) {
			$this->markTestSkipped( 'WooCommerce WC_Email class not available.' );
		}
		$manager = new Askquote_Email_Manager();
		$result  = $manager->add_emails( array() );

		$this->assertArrayHasKey( 'Askquote_Email_Customer_Quote_Received', $result );
		$this->assertArrayHasKey( 'Askquote_Email_Admin_Quote_Submitted', $result );
		$this->assertArrayHasKey( 'Askquote_Email_Customer_Quote_Approved', $result );
	}

	/**
	 * Triggering an email for a non-existent quote should not throw.
	 *
	 * @return void
	 */
	public function test_trigger_with_invalid_quote_does_not_throw() {
		if ( ! class_exists( 'WC_Email' ) ) {
			$this->markTestSkipped( 'WooCommerce WC_Email class not available.' );
		}
		$email = new Askquote_Email_Customer_Quote_Received();
		// Should not throw; trigger should bail early.
		$email->trigger( 0 );
		$this->assertTrue( true ); // Reached here without exception.
	}
}
