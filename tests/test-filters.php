<?php
/**
 * Tests default state of filters
 */
class KM_CPTDA_Tests_Filters extends CPTDA_UnitTestCase {

	/**
	 * Reset post type and filters on teardown.
	 */
	function tearDown() {
		parent::tearDown();
		$this->unregister_post_type();
		$this->boolean = null;
		remove_filter( 'cpda_add_admin_pages', array( $this, 'return_bool' ) );
		remove_filter( 'cpda_add_admin_page_cpt', array( $this, 'return_bool' ) );
		remove_filter( 'cptda_date_archives_feed', array( $this, 'return_bool' ) );
		remove_filter( 'cptda_cpt_date_archives_feed', array( $this, 'return_bool' ) );
		remove_filter( 'cptda_publish_future_posts', array( $this, 'return_bool' ) );
		remove_filter( 'cptda_publish_future_cpt', array( $this, 'return_bool' ) );
		remove_filter( 'cptda_flush_rewrite_rules', array( $this, 'return_bool' ) );
	}

	/**
	 * Test cpda_add_admin_pages filter is set to true (by default).
	 */
	function test_cpda_add_admin_pages_filter_bool() {
		add_filter( 'cpda_add_admin_pages', array( $this, 'return_bool' ) );
		$this->init();
		$admin = new CPTDA_Admin();
		$admin->cptda_admin_menu();
		$this->assertTrue( $this->boolean );
		$this->boolean = null;
	}

	/**
	 * Test cpda_add_admin_page_{$post_type} filter is set to true (by default).
	 */
	function test_cpda_add_admin_page_cpt_filter_bool() {
		add_filter( 'cpda_add_admin_page_cpt', array( $this, 'return_bool' ) );
		$this->init();
		$admin = new CPTDA_Admin();
		$admin->cptda_admin_menu();
		$this->assertTrue( $this->boolean );
		$this->boolean = null;
	}

	/**
	 * Test cptda_flush_rewrite_rules filter is set to true (by default).
	 */
	function test_cptda_flush_rewrite_rules_filter_bool() {
		add_filter( 'cptda_flush_rewrite_rules', array( $this, 'return_bool' ) );
		$this->init();
		$rewrite = new CPTDA_Rewrite();
		$rewrite->setup_archives();
		$this->assertTrue( $this->boolean );
		$this->boolean = null;
	}

	/**
	 * Test cptda_date_archives_feed filter is set to true (by default).
	 */
	function test_cptda_date_archives_feed_filter_bool() {
		global $wp_rewrite;
		add_filter( 'cptda_date_archives_feed', array( $this, 'return_bool' ) );
		$this->init();
		$rewrite = new CPTDA_Rewrite();
		$rewrite->setup_archives();
		$rewrite->generate_rewrite_rules( $wp_rewrite );
		$this->assertTrue( $this->boolean );
		$this->boolean = null;
	}

	/**
	 * Test cptda_cpt_date_archives_feed filter is set to true (by default).
	 */
	function test_cptda_cpt_date_archives_feed_filter_bool() {
		global $wp_rewrite;
		add_filter( 'cptda_cpt_date_archives_feed', array( $this, 'return_bool' ) );
		$this->init();
		$rewrite = new CPTDA_Rewrite();
		$rewrite->setup_archives();
		$rewrite->generate_rewrite_rules( $wp_rewrite );
		$this->assertTrue( $this->boolean );
		$this->boolean = null;
	}

	/**
	 * Test cptda_publish_future_posts filter is set to true (by default).
	 */
	function test_cptda_publish_future_posts_filter_bool() {
		add_filter( 'cptda_publish_future_posts', array( $this, 'return_bool' ) );
		$this->future_init();
		$plugin = cptda_date_archives();
		$plugin->post_type->setup();
		$this->assertTrue( $this->boolean );
		$this->boolean = null;
	}

	/**
	 * Test cptda_publish_future_cpt filter is set to true (by default).
	 */
	function test_cptda_publish_future_cpt_filter_bool() {

		add_filter( 'cptda_publish_future_cpt', array( $this, 'return_bool' ) );
		$this->future_init();

		$args = array(
			'post_date' => date( 'Y-m-d H:i:s', time() + YEAR_IN_SECONDS ),
			'post_type' => 'cpt',
		);

		$post_id = $this->factory->post->create( $args );

		$this->assertTrue( $this->boolean );
		$this->boolean = null;
	}

	/**
	 * Test cptda_replace_default_core_widgets filter is set to true (by default).
	 */
	function test_cptda_replace_default_core_widgets_bool() {
		add_filter( 'cptda_replace_default_core_widgets', array( $this, 'return_bool' ) );
		cptda_register_widgets();
		$this->assertTrue( $this->boolean );
		$this->boolean = null;
	}

}
