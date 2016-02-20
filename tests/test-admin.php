<?php
/**
 * Tests CPT admin page
 */
class KM_CPTDA_Tests_Admin extends WP_UnitTestCase {

	/**
	 * Utils object to create posts with terms to test with.
	 *
	 * @var object
	 */
	private $utils;

	public static function setUpBeforeClass() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		update_option( 'siteurl', 'http://example.com' );
		require_once CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/admin.php';
	}


	/**
	 * Set up.
	 */
	function setUp() {
		parent::setUp();

		// Use the utils class to create posts with terms
		$this->utils = new CPTDA_Test_Utils( $this->factory );
	}

	/**
	 * Reset post type on teardown.
	 */
	function tearDown() {
		parent::tearDown();
		$this->reset_post_types();
		$this->utils->unregister_post_type();
	}


	/**
	 * Test if admin url exists.
	 */
	function test_admin_page() {
		$this->utils->init();
		$admin = new CPTDA_Admin();
		$admin->cptda_admin_menu();
		$expected = 'http://example.org/wp-admin/edit.php?post_type=cpt&#038;page=date-archives-cpt';
		$this->assertEquals( $expected, menu_page_url( 'date-archives-cpt', false ) );
	}


	/**
	 * Test merging admin page settings.
	 */
	function test_admin_merge_page_settings_add_value() {
		$this->utils->init();
		$this->utils->init( 'cpt_2' );
		$admin = new CPTDA_Admin();

		$expected =  array(
			'date_archives'        => array( 'cpt' => 1, 'cpt_2' => 1 ),
			'publish_future_posts' => array( 'cpt' => 1 ),
		);

		$settings = $expected;
		unset( $settings['date_archives']['cpt_2'] );

		$new_settings = array(
			'date_archives' => 1
		);

		$merged = $admin->merge_settings( $settings, $new_settings, 'cpt_2' );
		$this->assertEquals( $expected, $merged );
	}


	/**
	 * Test merging admin page settings. Remove a value
	 */
	function test_admin_merge_page_settings_remove_value() {
		$this->utils->init();
		$admin = new CPTDA_Admin();

		$expected =  array(
			'date_archives'        => array(),
			'publish_future_posts' => array( 'cpt' => 1 ),
		);

		$settings = $expected;
		$settings['date_archives']['cpt'] = 1;

		$new_settings = array(
			'publish_future_posts' => 1
		);

		$merged = $admin->merge_settings( $settings, $new_settings, 'cpt' );
		$this->assertEquals( $expected, $merged );
	}


	/**
	 * Test merging admin page default settings
	 */
	function test_admin_get_settings_default() {
		$this->utils->init();
		$admin = new CPTDA_Admin();

		$expected =  array(
			'date_archives'        => array(),
			'publish_future_posts' => array(),
		);

		$this->assertEquals( $expected, $admin->get_settings( 'cpt' ) );
	}


	/**
	 * Test merging admin page settings. Remove a invalid post type and key
	 */
	function test_admin_get_settings_invalid_post_type() {
		$this->utils->init();
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

		$this->assertEquals( $expected, $admin->get_settings( 'cpt' ) );
	}
}
