<?php
/**
 * Tests default state of filters
 */
class KM_CPTDA_Tests_Filters extends WP_UnitTestCase {

	/**
	 * Helper class object.
	 *
	 * @var object
	 */
	private $utils;
	private $theme;

	/**
	 * Set up.
	 */
	function setUp() {
		parent::setUp();
		$this->utils = new CPTDA_Test_Utils( $this->factory );
	}


	/**
	 * Reset post type and filters on teardown.
	 */
	function tearDown() {
		parent::tearDown();
		$this->utils->unregister_post_type();
		$this->utils->boolean = null;
		remove_filter( 'cpda_add_admin_pages', array( $this->utils, 'return_bool' ) );
		remove_filter( 'cpda_add_admin_page_cpt', array( $this->utils, 'return_bool' ) );
		remove_filter( 'cptda_publish_future_posts', array( $this->utils, 'return_bool' ) );
		remove_filter( 'cptda_publish_future_cpt', array( $this->utils, 'return_bool' ) );
		remove_filter( 'cptda_flush_rewrite_rules', array( $this->utils, 'return_bool' ) );
	}


	/**
	 * Test cpda_add_admin_pages filter is set to true (by default).
	 */
	function test_cpda_add_admin_pages_filter_bool() {
		add_filter( 'cpda_add_admin_pages', array( $this->utils, 'return_bool' ) );
		$this->utils->init();
		$admin = new CPTDA_Admin();
		$admin->cptda_admin_menu();
		$this->assertTrue( $this->utils->boolean );
		$this->utils->boolean = null;
	}


	/**
	 * Test cpda_add_admin_page_{$post_type} filter is set to true (by default).
	 */
	function test_cpda_add_admin_page_cpt_filter_bool() {
		add_filter( 'cpda_add_admin_page_cpt', array( $this->utils, 'return_bool' ) );
		$this->utils->init();
		$admin = new CPTDA_Admin();
		$admin->cptda_admin_menu();
		$this->assertTrue( $this->utils->boolean );
		$this->utils->boolean = null;
	}


	/**
	 * Test cptda_flush_rewrite_rules filter is set to true (by default).
	 */
	function test_cptda_flush_rewrite_rules_filter_bool() {
		add_filter( 'cptda_flush_rewrite_rules', array( $this->utils, 'return_bool' ) );
		$this->utils->init();
		$rewrite = new CPTDA_Rewrite();
		$rewrite->setup_archives();
		$this->assertTrue( $this->utils->boolean );
		$this->utils->boolean = null;
	}


	/**
	 * Test cptda_publish_future_posts filter is set to true (by default).
	 */
	function test_cptda_publish_future_posts_filter_bool() {
		add_filter( 'cptda_publish_future_posts', array( $this->utils, 'return_bool' ) );
		$this->utils->future_init();
		$plugin = cptda_date_archives();
		$plugin->post_type->setup();
		$this->assertTrue( $this->utils->boolean );
		$this->utils->boolean = null;
	}


	/**
	 * Test cptda_publish_future_cpt filter is set to true (by default).
	 */
	function test_cptda_publish_future_cpt_filter_bool() {

		add_filter( 'cptda_publish_future_cpt', array( $this->utils, 'return_bool' ) );
		$this->utils->future_init();

		$args = array(
			'post_date' => date( 'Y-m-d H:i:s', time() + YEAR_IN_SECONDS ),
			'post_type' => 'cpt',
		);
	
		$post_id = $this->factory->post->create( $args );
	
		$this->assertTrue( $this->utils->boolean );
		$this->utils->boolean = null;
	}

}