<?php
/**
 * Tests CPT date archive queries
 */
class KM_CPTDA_Tests_Query extends CPTDA_UnitTestCase {

	/**
	 * Reset post type on teardown.
	 */
	function tearDown() {
		parent::tearDown();
		$this->unregister_post_type();
	}


	/**
	 * Test non existant date archive.
	 *
	 * @depends KM_CPTDA_Tests_Testcase::test_create_posts_init
	 */
	function test_404() {
		$this->init();
		$posts = $this->create_posts();
		$_posts = get_posts( 'post_type=cpt&posts_per_page=-1' );
		if ( isset( $_posts[0] ) ) {
			$year  = get_the_date( 'Y', $_posts[0] );
			$this->go_to( '?post_type=cpt&year=' . ( $year + 1 ) );
			$this->assertQueryTrue( 'is_404' );
		} else {
			$this->fail( "Posts not created" );
		}
	}


	/**
	 * Test date archives for published custom post type posts.
	 *
	 * @depends KM_CPTDA_Tests_Testcase::test_create_posts_init
	 */
	function test_date_archive() {
		$this->init();
		$posts = $this->create_posts();
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
	 *
	 * @depends KM_CPTDA_Tests_Testcase::test_create_posts_future_init
	 */
	function test_future_date_archive() {
		$this->future_init();
		$posts = $this->create_posts();
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
