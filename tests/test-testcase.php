<?php
/**
 * Tests testcase functions
 */
class KM_CPTDA_Tests_Testcase extends CPTDA_UnitTestCase {

	/**
	 * Test testcase function cpt_setup()
	 */
	function test_cpt_setup() {
		$args = array(
			'public' => true,
			'has_archive' => true,
		);
		register_post_type( 'cpt', $args );

		$this->cpt_setup( 'cpt' );
		$this->assertTrue( post_type_supports( 'cpt', 'date-archives' ) );
	}

	/**
	 * Test testcase function init()
	 */
	function test_init() {
		$this->init();
		$this->assertTrue( post_type_supports( 'cpt', 'date-archives' ) );
	}

	/**
	 * Test testcase function future_init()
	 */
	function test_future_init() {
		$this->future_init();
		$this->assertTrue( post_type_supports( 'cpt', 'publish-future-posts' ) );
	}

	/**
	 * Test create_posts.
	 */
	function test_create_posts() {
		$posts = $this->create_posts();
		$this->assertSame( 7, count( $posts ) );
	}

	/**
	 * Test if posts are created with post status publish.
	 */
	function test_create_posts_init() {
		$this->init();
		$posts = $this->create_posts();
		$this->assertSame( 7, count( $posts ) );
	}

	/**
	 * Test if posts with future dates are created with post status publish.
	 */
	function test_create_posts_future_init() {
		$this->future_init();
		$posts = $this->create_posts();
		$this->assertSame( 13, count( $posts ) );
	}

	/**
	 * Test deleting admin page settings
	 */
	function test_delete_settings() {
		$this->delete_settings();
		$this->assertFalse( get_option( 'custom_post_type_date_archives' ) );
	}
}
