<?php
/**
 * Tests CPT date archive queries
 */
class KM_CPTDA_Tests_Query extends WP_UnitTestCase {

	/**
	 * Utils object to create posts with terms to test with.
	 *
	 * @var object
	 */
	private $utils;


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
	 * Test non existant date archive .
	 */
	function test_404() {
		$this->utils->init();
		$posts = $this->utils->create_posts();
		$_posts = get_posts( 'post_type=cpt&posts_per_page=-1' );
		if ( isset( $_posts[0] ) ) {
			$year  = get_the_date( 'Y', $_posts[0] );
			$this->go_to( '?post_type=cpt&year='. ( $year + 1 ) );
			$this->assertQueryTrue( 'is_404' );
		} else {
			$this->fail( "Posts not created" );
		}
	}


	/**
	 * Test date archives for published custom post type posts.
	 */
	function test_date_archive() {
		$this->utils->init();
		$posts = $this->utils->create_posts();
		$_posts = get_posts( 'post_type=cpt&posts_per_page=-1' );

		if ( isset( $_posts[0] ) ) {
			$year  = get_the_date( 'Y', $_posts[0] );
			$this->go_to( '?post_type=cpt&year=' . $year  );
			$this->assertQueryTrue( 'is_date', 'is_archive', 'is_year', 'is_post_type_archive' );
		} else {
			$this->fail( "Posts not created" );
		}
	}


	/**
	 * Test future date archives for future custom post type posts.
	 */
	function test_future_date_archive() {
		$this->utils->future_init();
		$posts = $this->utils->create_posts();
		$future_posts = get_posts( 'post_type=cpt&posts_per_page=-1' );
		if ( isset( $future_posts[0] ) ) {
			$post_year  = get_the_date( 'Y', $future_posts[0] );
			$current_year = date( 'Y' );
			$this->assertTrue( ( $post_year > $current_year ) );
			$this->go_to( '?post_type=cpt&year=' . $post_year );
			$this->assertQueryTrue( 'is_date', 'is_archive', 'is_year', 'is_post_type_archive' );
		} else {
			$this->fail( "Posts not created" );
		}
	}

}
