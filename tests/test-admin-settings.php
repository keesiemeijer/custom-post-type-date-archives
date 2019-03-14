<?php
/**
 * Tests CPT admin page
 *
 * @group Settings
 */
class KM_CPTDA_Tests_Settings extends CPTDA_UnitTestCase {

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
		$this->unregister_post_type('cpt_2');
	}

	/**
	 * Test if settings always returns default settings
	 */
	function test_get_settings_defaults() {
		$settings_obj = new CPTDA_Admin_Settings();

		$expected = array(
			'date_archives'        => array(),
			'publish_future_posts' => array(),
		);

		$this->delete_settings();

		$this->assertEquals( $expected, $settings_obj->get_settings() );
	}

	/**
	 * Test merging admin page settings.
	 */
	function test_get_settings_in_database() {
		$this->init();
		$this->future_init();

		$settings_obj = new CPTDA_Admin_Settings();

		$expected =  array(
			'date_archives'        => array( 'cpt' => 1 ),
			'publish_future_posts' => array( 'cpt' => 1 ),
		);

		$option = array(
			'date_archives'        => array( 'cpt' => 1, 'movie' => 1 ),
			'publish_future_posts' => array( 'cpt' => 1, 'movie' => 1 ),
		);

		update_option( 'custom_post_type_date_archives', $option );

		$this->assertEquals( $expected, $settings_obj->get_settings() );
	}

	/**
	 * Test merging admin page settings.
	 */
	function test_admin_merge_page_settings_add_value() {
		$this->init();
		$this->init( 'cpt_2' );
		$settings_obj = new CPTDA_Admin_Settings();

		$expected =  array(
			'date_archives'        => array( 'cpt' => 1, 'cpt_2' => 1 ),
			'publish_future_posts' => array( 'cpt' => 1 ),
		);

		$settings = $expected;
		unset( $settings['date_archives']['cpt_2'] );

		$new_settings = array(
			'date_archives' => 1,
		);

		$merged = $settings_obj->merge_settings( $settings, $new_settings, 'cpt_2' );
		$this->assertEquals( $expected, $merged );
	}

	/**
	 * Test merging admin page settings. Remove a value
	 */
	function test_admin_merge_page_settings_remove_value() {
		$this->init();
		$settings_obj = new CPTDA_Admin_Settings();

		$expected = array(
			'date_archives'        => array(),
			'publish_future_posts' => array( 'cpt' => 1 ),
		);

		$settings = $expected;
		$settings['date_archives']['cpt'] = 1;

		$new_settings = array(
			'publish_future_posts' => 1,
		);

		$merged = $settings_obj->merge_settings( $settings, $new_settings, 'cpt' );
		$this->assertEquals( $expected, $merged );
	}

}
