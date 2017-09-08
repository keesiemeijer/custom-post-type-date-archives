<?php
/**
 * Tests for public plugin functions
 */
class KM_CPTDA_Tests_Functions extends WP_UnitTestCase {

	/**
	 * Utils object to create posts to test with.
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
		$this->utils->unregister_post_type();
		remove_filter( 'cptda_post_stati', array( $this, 'add_future_status' ), 10, 2 );
	}

	/**
	 * Test cptda_is_cpt_date() on a custom post type date archive.
	 */
	function test_cptda_is_cpt_date() {
		$this->utils->init();
		$posts = $this->utils->create_posts();
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
		$this->utils->init();
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
		$this->utils->setup( 'cpt' );
		$this->assertFalse( cptda_is_date_post_type( 'cpt' ) );
	}

	/**
	 * Test cptda_get_date_archive_cpt() current post type archive.
	 */
	function test_cptda_get_date_archive_cpt() {
		$this->utils->init();
		$posts = $this->utils->create_posts();
		$_posts = get_posts( 'post_type=cpt&posts_per_page=-1' );

		if ( isset( $_posts[0] ) ) {
			$year  = get_the_date( 'Y', $_posts[0] );
			$this->go_to( '?post_type=cpt&year=' . $year  );
			$this->assertEquals( 'cpt', cptda_get_date_archive_cpt() );
		} else {
			$this->fail( "Posts not created" );
		}
	}

	/**
	 * Test cptda_get_date_archive_cpt() on normal date archive.
	 */
	function test_cptda_get_date_archive_cpt_post() {
		$posts = $this->utils->create_posts( 'post' );
		$_posts = get_posts( 'posts_per_page=-1' );

		if ( isset( $_posts[0] ) ) {
			$year  = get_the_date( 'Y', $_posts[0] );
			$this->go_to( '?year=' . $year  );
			$this->assertEmpty( cptda_get_date_archive_cpt() );
		} else {
			$this->fail( "Posts not created" );
		}
	}

	/**
	 * Test test archives output.
	 */
	function test_cptda_get_archives() {
		global $wp_locale;
		$this->utils->init();
		$year = (int) date( "Y" ) - 1;

		$expected = '';
		foreach ( array( '03', '02' ) as $month ) {
			$args = array( 'post_date' => "$year-$month-20 00:00:00", 'post_type' => 'cpt' );
			$post = $this->factory->post->create( $args );
			$url = cptda_get_month_link( $year, $month, 'cpt' );
			$text = sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $month ), $year );
			$expected .= trim( get_archives_link( $url, $text ) ) . "\n";
		}

		$archive = cptda_get_archives( array( 'post_type' => 'cpt', 'echo' => false ) );

		$this->assertEquals( strip_ws( $expected ), strip_ws( $archive ) );
	}

	/**
	 * Test test calendar output.
	 */
	function test_cptda_get_calendar() {
		global $wp_locale;
		$this->utils->init();
		$year = (int) date( "Y" ) - 1;

		$expected = '';
		foreach ( array( '03', '01' ) as $month ) {
			$args = array( 'post_date' => "$year-$month-20 00:00:00", 'post_type' => 'cpt' );
			$post = $this->factory->post->create( $args );
		}

		$calendar = cptda_get_calendar( 'cpt', true, false );
		$this->assertContains( '>&laquo; Mar<', $calendar );
		$this->assertNotContains( '<td><a ', $calendar );

		$this->go_to( '?post_type=cpt&year=' . $year . '&monthnum=3' );
		$calendar = cptda_get_calendar( 'cpt', true, false );
		$this->assertContains( "Posts published on March 20, $year", $calendar );
		$this->assertContains( '<td><a ', $calendar );
		$this->assertContains( cptda_get_day_link( $year, 3, 20, 'cpt' ) , $calendar );
		$this->assertContains( '>&laquo; Jan<', $calendar );

		$this->go_to( '?post_type=cpt&year=' . $year . '&monthnum=2' );
		$calendar = cptda_get_calendar( 'cpt', true, false );
		$this->assertContains( '>&laquo; Jan<', $calendar );
		$this->assertContains( '>Mar &raquo;<', $calendar );
		$this->assertNotContains( '<td><a ', $calendar );

		$this->go_to( '?post_type=cpt&year=' . $year . '&monthnum=1' );
		$calendar = cptda_get_calendar( 'cpt', true, false );
		$this->assertContains( "Posts published on January 20, $year", $calendar );
		$this->assertContains( '<td><a ', $calendar );
		$this->assertContains( cptda_get_day_link( $year, 1, 20, 'cpt' ) , $calendar );
		$this->assertContains( '>Mar &raquo;<', $calendar );
	}

	/**
	 * Test cptda_get_cpt_date_archive_stati returns correct stati.
	 */
	function test_not_supported_custom_post_type_stati() {
		$this->utils->register_post_type( 'no_date_archives' );
		$this->assertEquals( array( 'publish' ), cptda_get_cpt_date_archive_stati( 'no_date_archives' ) );
		$this->utils->unregister_post_type( 'no_date_archives' );
	}

	/**
	 * Test cptda_get_cpt_date_archive_stati returns correct stati.
	 */
	function test_post_status_publish() {
		$this->utils->init();
		$this->assertEquals( array( 'publish' ), cptda_get_cpt_date_archive_stati( 'cpt' ) );
	}

	/**
	 * Test cptda_get_cpt_date_archive_stati returns correct stati.
	 */
	function test_post_status_future() {
		$this->utils->future_init();
		add_filter( 'cptda_post_stati', array( $this, 'add_future_status' ), 10 , 2 );
		$this->assertEquals( array( 'publish', 'future' ), cptda_get_cpt_date_archive_stati( 'cpt' ) );
	}

	/**
	 * Test cptda_get_admin_post_types
	 */
	function test_cptda_get_admin_post_types() {
		$this->utils->future_init();
		$this->assertEquals( array( 'cpt' => 'Custom Post Type' ), cptda_get_admin_post_types( 'cpt' ) );
	}

	/**
	 * Test cptda_get_admin_post_types for post type not publicly queryable.
	 */
	function test_cptda_get_admin_post_types_not_publicly_queryable() {
		$args = array( 'public' => true, 'has_archive' => true, 'publicly_queryable' => false );
		register_post_type( 'cpt', $args );
		$this->utils->setup( 'cpt' );
		$this->assertEmpty( cptda_get_admin_post_types( 'cpt' ) );
	}

	/**
	 * Tests for functions that should not output anything.
	 */
	function test_empty_output() {

		$this->utils->init();
		$posts = $this->utils->create_posts();
		$_posts = get_posts( 'post_type=cpt&posts_per_page=-1' );

		if ( isset( $_posts[0] ) ) {
			$year  = get_the_date( 'Y', $_posts[0] );
			$this->go_to( '?post_type=cpt&year=' . $year  );
		}

		ob_start();

		// these functions should not output anything.
		$_plugin   = cptda_date_archives();
		$is_date   = cptda_is_cpt_date();
		$is_posts  = cptda_is_date_post_type( 'cpt' );
		$post_type = cptda_get_date_archive_cpt();
		$post_type = cptda_get_admin_post_types();
		$archives  = cptda_get_archives( 'post_type=cpt&echo=0' );
		$calendar  = cptda_get_calendar( 'cpt', true, false );

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
