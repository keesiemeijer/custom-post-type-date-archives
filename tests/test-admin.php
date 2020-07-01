<?php
/**
 * Tests CPT admin page
 */
class KM_CPTDA_Tests_Admin extends CPTDA_UnitTestCase {

	public static function setUpBeforeClass() {
		require_once CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/admin.php';
	}

	/**
	 * Set up.
	 */
	function setUp() {
		parent::setUp();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
	}

	/**
	 * Reset post type on teardown.
	 */
	function tearDown() {
		parent::tearDown();
		$this->unregister_post_type();
	}

	/**
	 * Test if admin url exists.
	 */
	function test_admin_page() {
		$this->init();
		$admin = new CPTDA_Admin();
		$admin->cptda_admin_menu();
		$expected = admin_url( 'edit.php?post_type=cpt&#038;page=date-archives-cpt' );
		$this->assertSame( $expected, menu_page_url( 'date-archives-cpt', false ) );
	}

	/**
	 * Test merging admin page default settings
	 */
	function test_admin_get_settings_default() {
		$this->init();
		$admin = new CPTDA_Admin();

		$expected =  array(
			'date_archives'        => array(),
			'publish_future_posts' => array(),
		);

		$this->assertSame( $expected, $admin->get_settings( 'cpt' ) );
	}

	/**
	 * Test merging admin page settings. Remove a invalid post type and key
	 */
	function test_admin_get_settings_invalid_post_type() {
		$this->init();
		$admin = new CPTDA_Admin();
		$admin->cptda_admin_menu();

		$expected =  array(
			'date_archives'        => array( 'cpt' => 1 ),
			'publish_future_posts' => array(),
		);

		$option = $expected;
		$option['date_archives']['movie'] = 1;
		$option['invalid_key'] = 'hahaha';

		update_option( 'custom_post_type_date_archives', $option );

		$this->assertSame( $expected, $admin->get_settings( 'cpt' ) );
	}

	/**
	 * Test admin page settings with update
	 */
	function test_admin_get_settings_when_updating() {
		$this->init();
		$admin = new CPTDA_Admin();
		$admin->cptda_admin_menu();
		$this->delete_settings();

		// Fake the globals
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_REQUEST['_wpnonce'] = wp_create_nonce( "custom_post_type_date_archives_cpt_nonce" );

		// Fake "add date archives" selected
		$_POST = array(
			'date_archives' => 1,
		);

		$expected =  array(
			'date_archives'        => array( 'cpt' => 1 ),
			'publish_future_posts' => array(),
		);

		$this->assertSame( $expected, $admin->get_settings( 'cpt' ) );
	}
}
