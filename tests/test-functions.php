<?php
/**
 * Tests for public plugin functions
 */
class KM_CPTDA_Tests_Functions extends CPTDA_UnitTestCase {

	/**
	 * Reset post type on teardown.
	 */
	function tearDown() {
		parent::tearDown();
		$this->unregister_post_type();
		$this->unregister_post_type( 'cpt_2' );
		remove_filter( 'cptda_post_stati', array( $this, 'add_future_status' ), 10, 2 );
		remove_filter( 'cptda_get_archives', array( $this, 'get_objects' ), 10 , 2 );
	}

	/**
	 * Test cptda_is_cpt_date() on a custom post type date archive.
	 */
	function test_cptda_is_cpt_date() {
		$this->init();
		$posts = $this->create_posts();
		$_posts = get_posts( 'post_type=cpt&posts_per_page=-1' );

		if ( isset( $_posts[0] ) ) {
			$year  = get_the_date( 'Y', $_posts[0] );
			$this->go_to( '?post_type=cpt&year=' . $year  );
			$this->assertTrue( cptda_is_cpt_date() );
		} else {
			$this->fail( "Posts not created" );
		}
	}

	/**
	 * Test cptda_is_cpt_date() on the home page.
	 */
	function test_cptda_is_cpt_date_false() {
		$this->go_to( '/' );
		$this->assertFalse( cptda_is_cpt_date() );
	}

	/**
	 * Test cptda_is_date_post_type().
	 */
	function test_cptda_is_date_post_type() {
		$this->init();
		$this->assertTrue( cptda_is_date_post_type( 'cpt' ) );
	}

	/**
	 * Test cptda_is_date_post_type() for post type post.
	 */
	function test_cptda_is_date_post_type_false() {
		$this->assertFalse( cptda_is_date_post_type( 'post' ) );
	}

	/**
	 * Test cptda_is_date_post_type() for post type post without archive.
	 */
	function test_cptda_is_date_post_type_no_archive() {
		$args = array( 'public' => true, 'has_archive' => false );
		register_post_type( 'cpt', $args );
		$this->cpt_setup( 'cpt' );
		$this->assertFalse( cptda_is_date_post_type( 'cpt' ) );
	}

	/**
	 * Test deprecated function cptda_get_date_archive_cpt
	 *
	 * @expectedDeprecated cptda_get_date_archive_cpt
	 */
	function test_cptda_get_date_archive_cpt() {
		$this->init();
		$posts = $this->create_posts();
		$_posts = get_posts( 'post_type=cpt&posts_per_page=-1' );

		if ( isset( $_posts[0] ) ) {
			$year  = get_the_date( 'Y', $_posts[0] );
			$this->go_to( '?post_type=cpt&year=' . $year  );
			$expected   = cptda_get_queried_date_archive_post_type();
			$deprecated = cptda_get_date_archive_cpt();

			$this->assertEquals( 'cpt', $expected );
			$this->assertEquals( $expected, $deprecated );
		} else {
			$this->fail( "Posts not created" );
		}
	}

	/**
	 * Test cptda_get_queried_date_archive_post_type() current post type archive.
	 */
	function test_cptda_get_queried_date_archive_post_type() {
		$this->init();
		$posts = $this->create_posts();
		$_posts = get_posts( 'post_type=cpt&posts_per_page=-1' );

		if ( isset( $_posts[0] ) ) {
			$year  = get_the_date( 'Y', $_posts[0] );
			$this->go_to( '?post_type=cpt&year=' . $year  );
			$this->assertEquals( 'cpt', cptda_get_queried_date_archive_post_type() );
		} else {
			$this->fail( "Posts not created" );
		}
	}

	/**
	 * Test cptda_get_queried_date_archive_post_type() on normal date archive.
	 */
	function test_cptda_get_queried_date_archive_post_type_post() {
		$posts = $this->create_posts( 'post' );
		$_posts = get_posts( 'posts_per_page=-1' );

		if ( isset( $_posts[0] ) ) {
			$year  = get_the_date( 'Y', $_posts[0] );
			$this->go_to( '?year=' . $year  );
			$this->assertEmpty( cptda_get_queried_date_archive_post_type() );
		} else {
			$this->fail( "Posts not created" );
		}
	}

	/**
	 * Test cptda_get_cpt_date_archive_stati returns correct stati.
	 */
	function test_not_supported_custom_post_type_stati() {
		$this->register_post_type( 'no_date_archives' );
		$this->assertEquals( array( 'publish' ), cptda_get_cpt_date_archive_stati( 'no_date_archives' ) );
		$this->unregister_post_type( 'no_date_archives' );
	}

	/**
	 * Test cptda_get_cpt_date_archive_stati returns correct stati.
	 */
	function test_post_status_publish() {
		$this->init();
		$this->assertSame( array( 'publish' ), cptda_get_cpt_date_archive_stati( 'cpt' ) );
	}

	/**
	 * Test cptda_get_cpt_date_archive_stati returns correct stati.
	 */
	function test_post_status_publish_post_type_post() {
		$this->assertSame( array( 'publish' ), cptda_get_cpt_date_archive_stati( 'post' ) );
	}

	/**
	 * Test cptda_get_cpt_date_archive_stati returns correct stati.
	 */
	function test_post_status_future() {
		$this->future_init();
		add_filter( 'cptda_post_stati', array( $this, 'add_future_status' ), 10 , 2 );
		$this->assertEquals( array( 'publish', 'future' ), cptda_get_cpt_date_archive_stati( 'cpt' ) );
	}

	/**
	 * Test cptda_get_post_types
	 */
	function test_cptda_get_post_types() {
		$this->init();
		$this->assertEquals( array( 'cpt' => 'Custom Post Type' ), cptda_get_post_types( 'labels' ) );
	}

	/**
	 * Test cptda_get_post_types
	 */
	function test_cptda_get_post_types_future() {
		$this->future_init();
		$this->assertEquals( array( 'cpt' => 'Custom Post Type' ), cptda_get_post_types( 'labels', 'publish_future' ) );
	}

	/**
	 * Test cptda_get_post_types for post type not publicly queryable.
	 */
	function test_cptda_get_post_types_not_publicly_queryable() {
		$args = array( 'public' => true, 'has_archive' => true, 'publicly_queryable' => false );
		register_post_type( 'cpt', $args );
		$this->cpt_setup( 'cpt' );
		$this->assertEmpty( cptda_get_post_types() );
	}

	/**
	 * Test cptda_get_admin_post_types
	 * This is a deprecated function
	 *
	 * @expectedDeprecated cptda_get_admin_post_types
	 */
	function test_cptda_get_admin_post_types_deprecated() {
		$this->init();
		$expected   = cptda_get_post_types( 'labels', 'admin' );
		$deprecated = cptda_get_admin_post_types( 'names' );
		$this->assertEquals( $expected, $deprecated );
	}

	/**
	 * Test cptda_get_post_types for admin post types.
	 */
	function test_cptda_get_admin_post_types() {
		$this->init();
		$args = array( 'public' => true, 'has_archive' => true );

		// Disable in menu
		$args['show_in_menu'] = false;
		register_post_type( 'cpt_2', $args );
		$this->cpt_setup( 'cpt_2' );

		$this->assertEquals( array( 'cpt' ), cptda_get_post_types( 'names', 'admin' ) );

		$this->assertEquals( array( 'cpt', 'cpt_2' ), cptda_get_post_types( 'names' ) );
	}

	/**
	 * Test cptda_is_valid_post_type.
	 */
	function test_cptda_is_valid_post_type() {
		$args = array( 'public' => true, 'has_archive' => true, );
		register_post_type( 'cpt', $args );
		$this->cpt_setup( 'cpt' );
		$this->assertTrue( cptda_is_valid_post_type( 'cpt' ) );
	}

	/**
	 * Test cptda_is_valid_post_type for post type not publicly queryable.
	 */
	function test_cptda_is_valid_post_type_not_publicly_queryable() {
		$args = array( 'public' => true, 'has_archive' => true, 'publicly_queryable' => false );
		register_post_type( 'cpt', $args );
		$this->cpt_setup( 'cpt' );
		$this->assertFalse( cptda_is_valid_post_type( 'cpt' ) );
	}

	/**
	 * Tests for functions that should not output anything.
	 *
	 * @expectedDeprecated cptda_get_admin_post_types
	 * @expectedDeprecated cptda_get_date_archive_cpt
	 */
	function test_empty_output() {

		$this->init();
		$posts = $this->create_posts();
		$_posts = get_posts( 'post_type=cpt&posts_per_page=-1' );

		$year  = get_the_date( 'Y', $_posts[0] );
		$month = get_the_date( 'n', $_posts[0] );
		$day   = get_the_date( 'j', $_posts[0] );

		$this->go_to( '?post_type=cpt&year=' . $year  );

		if ( ! defined( 'WP_DEBUG' ) ) {
			define( 'WP_DEBUG', true );
		}

		ob_start();

		// these functions should not output anything.
		$_plugin    = cptda_date_archives();
		$post_type  = cptda_get_post_types();
		$is_posts   = cptda_is_date_post_type( 'cpt' );
		$is_valid   = cptda_is_valid_post_type( 'cpt' );
		$is_date    = cptda_is_cpt_date();
		$post_type  = cptda_get_queried_date_archive_post_type();
		$stati      = cptda_get_cpt_date_archive_stati( 'cpt' );
		$deprecated = cptda_get_date_archive_cpt();
		$deprecated = cptda_get_admin_post_types();
		$archives   = cptda_get_archives( 'post_type=cpt&echo=0' );
		$date       = cptda_get_calendar_date();
		$adjacent   = cptda_get_adjacent_archive_date( 'cpt', $date );
		$sql        = cptda_get_calendar_post_type_sql( 'cpt' );
		$calendar   = cptda_get_calendar( 'cpt', true, false );
		$_year      = cptda_get_year_link( $year, 'cpt' );
		$_month     = cptda_get_month_link( $year, $month, 'cpt' );
		$_day       = cptda_get_month_link( $year, $month, $day, 'cpt' );
		$_year      = cptda_get_year_archive_link( $year, 'cpt' );
		$_month     = cptda_get_month_archive_link( $year, $month, 'cpt' );
		$_day       = cptda_get_day_archive_link( $year, $month, $day, 'cpt' );
		$arch_args  = cptda_get_archive_settings();
		$sanitize   = cptda_sanitize_archive_settings( $arch_args );
		$validate   = cptda_validate_archive_settings( $arch_args );
		$arch_html  = cptda_get_archives_html( $arch_args );
		$rp_args    = cptda_get_recent_posts_settings();
		$sanitize   = cptda_sanitize_recent_posts_settings( $rp_args );
		$html       = cptda_get_recent_posts_html( $posts, $rp_args );
		$query      = cptda_get_recent_posts_query( $rp_args );

		$out = ob_get_clean();

		$this->assertEmpty( $out );
	}

	function add_future_status( $status, $post_type ) {

		if ( 'cpt' === $post_type ) {
			$status[] = 'future';
		}

		return $status;
	}
}
