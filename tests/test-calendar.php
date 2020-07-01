<?php
/**
 * Tests for public plugin functions
 *
 * @group Calendar
 */
class KM_CPTDA_Tests_Calendar extends CPTDA_UnitTestCase {

	protected $calendar_data = null;

	/**
	 * Reset post type on teardown.
	 */
	function tearDown() {
		parent::tearDown();
		$this->unregister_post_type();

		remove_filter( 'cptda_calendar_data', array( $this, 'exclude_category_posts' ), 10, 2 );
		remove_filter( 'cptda_calendar_data', array( $this, 'set_next_month_to_june' ), 10 );
		remove_filter( 'cptda_calendar_data', array( $this, 'set_next_month_navigation_to_false' ), 10 );
		remove_filter( 'cptda_calendar_data', array( $this, 'set_date_to_march_18' ), 10 );
		remove_filter( 'cptda_calendar_data', array( $this, 'no_dates' ), 10 );
	}

	/**
	 * Test test calendar output.
	 */
	function test_cptda_get_calendar() {
		global $wp_locale;
		$this->init();
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
	 * Test post type post
	 */
	function test_cptda_get_calendar_post_type_post() {
		global $wp_locale, $monthnum, $year;

		$monthnum = 3;
		$year = (int) date( "Y" ) - 1;

		// create posts for month
		foreach ( array( '03', '01' ) as $month ) {
			$args = array( 'post_date' => "$year-$month-20 00:00:00", 'post_type' => 'post' );
			$post = $this->factory->post->create( $args );
		}

		$calendar = cptda_get_calendar( 'post', true, false );
		$this->assertContains( "<caption>March {$year}</caption>", $calendar );
	}

	/**
	 * Test invalid post type
	 */
	function test_cptda_get_calendar_empty_post_type() {
		global $wp_locale, $monthnum, $year;

		$monthnum = 3;
		$year = (int) date( "Y" ) - 1;

		// No post type
		$calendar = cptda_get_calendar( '', true, false );
		$this->assertEmpty( $calendar );

		// Not a post type with archives
		$calendar = cptda_get_calendar( 'noarchive', true, false );
		$this->assertEmpty( $calendar );
	}

	/**
	 * Test calendar output with date set to march 18 with filter.
	 */
	function test_cptda_filter_days_of_calendar() {
		global $wp_locale;
		$this->init();
		$year = (int) date( "Y" ) - 1;

		$expected = '';
		// create posts for month
		foreach ( array( '03', '01' ) as $month ) {
			$args = array( 'post_date' => "$year-$month-20 00:00:00", 'post_type' => 'cpt' );
			$post = $this->factory->post->create( $args );
		}

		add_filter( 'cptda_calendar_data', array( $this, 'set_date_to_march_18' ), 10 );
		$this->go_to( '?post_type=cpt&year=' . $year . '&monthnum=3' );

		$calendar = cptda_get_calendar( 'cpt', true, false );

		// Date March 18 added by the filter
		$this->assertContains( "Posts published on March 18, $year", $calendar );
	}

	/**
	 * Test calendar output with date set to march 18 with filter.
	 */
	function test_cptda_filter_days_of_calendar_no_dates() {
		global $wp_locale;
		$this->init();
		$year = (int) date( "Y" ) - 1;

		$expected = '';
		// create posts for month
		foreach ( array( '03', '01' ) as $month ) {
			$args = array( 'post_date' => "$year-$month-20 00:00:00", 'post_type' => 'cpt' );
			$post = $this->factory->post->create( $args );
		}

		add_filter( 'cptda_calendar_data', array( $this, 'no_dates' ), 10 );
		$this->go_to( '?post_type=cpt&year=' . $year . '&monthnum=3' );

		$calendar = cptda_get_calendar( 'cpt', true, false );

		// Date March 18 added by the filter
		$this->assertNotContains( "Posts published on", $calendar );
	}

	/**
	 * Test calendar cache.
	 */
	function test_cptda_calendar_cache() {
		global $wp_locale, $monthnum, $year;
		$this->init();
		$post_year = (int) date( "Y" ) - 1;

		$expected = '';
		// create posts for month
		foreach ( array( '03', '01' ) as $post_month ) {
			$args = array( 'post_date' => "$post_year-$post_month-20 00:00:00", 'post_type' => 'cpt' );
			$post = $this->factory->post->create( $args );
		}

		$monthnum = 3;
		$year = $post_year;

		$queries_before = get_num_queries();
		$calendar       = cptda_get_calendar( 'cpt', true, false );
		$queries_after  = get_num_queries();

		$this->assertSame( $queries_before + 4, $queries_after );

		$calendar       = cptda_get_calendar( 'cpt', true, false );
		$no_queries     = get_num_queries();

		$this->assertSame( $queries_after, $no_queries );
	}

	/**
	 * Test calendar cache.
	 */
	function test_cptda_calendar_cache_no_posts() {
		global $wp_locale, $monthnum, $year;
		$this->init();
		$post_year = (int) date( "Y" ) - 1;

		$monthnum = 3;
		$year = $post_year;

		$queries_before = get_num_queries();
		$calendar       = cptda_get_calendar( 'cpt', true, false );
		$queries_after  = get_num_queries();

		// Query to see if there are any 'cpt' posts
		$this->assertSame( $queries_before + 1, $queries_after );

		$calendar       = cptda_get_calendar( 'cpt', true, false );
		$no_queries     = get_num_queries();

		$this->assertSame( $queries_after, $no_queries );
	}

	/**
	 * Test example in documentation.
	 */
	function test_documentation_example() {
		global $wp_locale, $monthnum, $year;
		$this->init();

		register_taxonomy_for_object_type( 'category', 'cpt' );
		$post_year = (int) date( "Y" ) - 1;

		// create posts for month
		$args = array( 'post_date' => "$post_year-02-10 00:00:00", 'post_type' => 'cpt' );
		$post = $this->factory->post->create( $args );
		$args['post_date'] = "$post_year-03-10 00:00:00";
		$post = $this->factory->post->create( $args );
		$args['post_date'] = "$post_year-04-10 00:00:00";
		$post = $this->factory->post->create( $args );
		$args['post_date'] = "$post_year-04-20 00:00:00";
		$post = $this->factory->post->create( $args );
		$args['post_date'] = "$post_year-05-10 00:00:00";
		$post = $this->factory->post->create( $args );
		$args['post_date'] = "$post_year-06-10 00:00:00";
		$post = $this->factory->post->create( $args );

		$posts = get_posts( 'post_type=cpt&posts_per_page=-1&fields=ids' );

		$term_id = wp_create_term( 'noarchive', 'category' );
		wp_set_post_terms ( $posts[1], $term_id, 'category', true );
		wp_set_post_terms ( $posts[2], $term_id, 'category', true );
		wp_set_post_terms ( $posts[4], $term_id, 'category', true );

		$monthnum = 4;
		$year = $post_year;

		// Exclude posts with 'noarchive' term.
		add_filter( 'cptda_calendar_data', array( $this, 'exclude_category_posts' ), 10, 2 );
		$calendar = cptda_get_calendar( 'cpt', true, false );

		$this->assertContains( '>Jun &raquo;<', $calendar );
		$this->assertContains( '>&laquo; Feb<', $calendar );
		$this->assertContains( "Posts published on April 10, $post_year", $calendar );
		$this->assertNotContains( "Posts published on April 20, $post_year", $calendar );
	}

	/**
	 * Test calendar data.
	 */
	function test_cptda_calendar_data() {
		global $wp_locale, $monthnum, $year;
		$this->init();
		$post_year = (int) date( "Y" ) - 1;

		$expected = '';
		// create posts for month
		foreach ( array( '05', '03', '01' ) as $post_month ) {
			$args = array( 'post_date' => "$post_year-$post_month-20 00:00:00", 'post_type' => 'cpt' );
			$post = $this->factory->post->create( $args );
		}

		$monthnum = 3;
		$year = $post_year;

		add_filter( 'cptda_get_calendar', array( $this, 'get_calendar_data' ), 10, 2 );
		$calendar = cptda_get_calendar( 'cpt', true, false );
		$calendar_data = $this->calendar_data;
		$this->calendar_data = null;

		$expected = array (
			'year'          => $post_year,
			'month'         => 3,
			'last_day'      => 31,
			'next_year'     => $post_year,
			'prev_year'     => $post_year,
			'next_month'    => 5,
			'prev_month'    => 1,
			'calendar_days' => array( 20 ),
		);

		unset( $calendar_data['timestamp'], $calendar_data['unixmonth'] );

		$this->assertSame( $expected, $calendar_data );
	}


	/**
	 * Test calendar data.
	 */
	function test_cptda_calendar_data_no_next_prev() {
		global $wp_locale, $monthnum, $year;
		$this->init();
		$post_year = (int) date( "Y" ) - 1;

		$expected = '';
		// create posts for month
		foreach ( array( '05' ) as $post_month ) {
			$args = array( 'post_date' => "$post_year-$post_month-20 00:00:00", 'post_type' => 'cpt' );
			$post = $this->factory->post->create( $args );
		}

		$monthnum = 5;
		$year = $post_year;

		add_filter( 'cptda_get_calendar', array( $this, 'get_calendar_data' ), 10, 2 );
		$calendar = cptda_get_calendar( 'cpt', true, false );
		$calendar_data = $this->calendar_data;
		$this->calendar_data = null;

		$expected = array (
			'year'          => $post_year,
			'month'         => 5,
			'last_day'      => 31,
			'next_year'     => 0,
			'prev_year'     => 0,
			'next_month'    => 0,
			'prev_month'    => 0,
			'calendar_days' => array( 20 ),
		);

		unset( $calendar_data['timestamp'], $calendar_data['unixmonth'] );

		$this->assertSame( $expected, $calendar_data );
	}

	/**
	 * Test calendar output with navigation to the month of June.
	 */
	function test_cptda_filter_calendar_navigation() {
		global $wp_locale;
		$this->init();
		$year = (int) date( "Y" ) - 1;

		$expected = '';
		// create posts for month
		foreach ( array( '03', '01' ) as $month ) {
			$args = array( 'post_date' => "{$year}-$month-20 00:00:00", 'post_type' => 'cpt' );
			$post = $this->factory->post->create( $args );
		}

		add_filter( 'cptda_calendar_data', array( $this, 'set_next_month_to_june' ), 10 );
		$this->go_to( '?post_type=cpt&year=' . $year . '&monthnum=3' );

		$calendar = cptda_get_calendar( 'cpt', true, false );

		// Date March 18 added by the filter
		$this->assertContains( '>Jun &raquo;<', $calendar );
	}

	/**
	 * Test calendar output with next month navigation_disabled.
	 */
	function test_cptda_filter_calendar_no_next_month_navigation() {
		global $wp_locale;
		$this->init();
		$year = (int) date( "Y" ) - 1;

		$expected = '';
		// create posts for month
		foreach ( array( '03', '01' ) as $month ) {
			$args = array( 'post_date' => "{$year}-$month-20 00:00:00", 'post_type' => 'cpt' );
			$post = $this->factory->post->create( $args );
		}

		add_filter( 'cptda_calendar_data', array( $this, 'set_next_month_navigation_to_false' ), 10 );
		$this->go_to( '?post_type=cpt&year=' . $year . '&monthnum=1' );
		$calendar = cptda_get_calendar( 'cpt', true, false );

		// Does not have navigation to next month (March) archive
		// See the last assertion in the test_cptda_get_calendar() method
		$this->assertNotContains( '>Mar &raquo;<', $calendar );
	}

	function get_calendar_data( $calendar_output, $calendar_data ) {
		$this->calendar_data = $calendar_data;
		return $calendar_output;
	}

	function no_dates( $data ) {
		$data['calendar_days'] = false;
		return $data;
	}

	function set_date_to_march_18( $data ) {
		$data['calendar_days'] = array( 18 );
		return $data;
	}

	function set_next_month_to_june( $data ) {
		$year = (int) date( "Y" ) - 1;
		$data['next_month']  = 6;
		$data['next_year']  = $year;

		return $data;
	}

	function set_next_month_navigation_to_false( $data ) {
		$data['next_month'] = false;
		return $data;
	}

	/**
	 * Test example in documentation
	 */
	function exclude_category_posts( $date, $post_type ) {

		/*
		 * The parameter $date is an array with $date attributes.
		 *
		 * By default the $date['calendar_days'] is an empty array.
		 * If you provide an array with days it will be used by the calendar.
		 */
		$date['calendar_days'] = array();

		$current_date = array(
			'year'  => (int) $date['year'],
			'month' => (int) $date['month'],
		);

		// Get the post stati for the current post type
		$post_status = cptda_get_cpt_date_archive_stati( $post_type );

		// Query arguments to get posts (dates) for the current month with
		// posts with the 'noarchive' term excluded.
		$args = array(
			// Get all the posts for the current month
			'posts_per_page' => -1,

			// Get posts from post type and status.
			'post_type'   => $post_type,
			'post_status' => $post_status,

			// Get posts from current calendar month.
			'date_query' => array( $current_date ),

			// Exclude posts with `noarchive` category term from results.
			'tax_query' => array(
				array(
					'taxonomy' => 'category',
					'field'    => 'slug',
					'terms'    => array( 'noarchive' ),
					'operator' => 'NOT IN',
				),
			),
		);


		// Get the posts for the current calendar (with noarchive term excluded).
		$calendar_posts = get_posts( $args );

		if ( ! $calendar_posts ) {
			// Return false for 'calendar_days' if no posts were found.
			// This prevents the calendar querying for posts
			$date['calendar_days'] = false;
		} else {
			// Get the dates from the posts.
			$dates = wp_list_pluck( $calendar_posts, 'post_date' );

			foreach ( $dates as $day ) {
				// Get the day number from the post date.
				$date['calendar_days'][] = (int) date( 'j', strtotime( $day ) );
			}
		}

		/*
		 * The 'prev_year', 'prev_month', 'next_year' and 'next_month' values are
		 * by default an empty string.
		 *
		 * If you provide your own values it will be used by the calendar.
		 */

		// Query for the next archive month.
		$args['posts_per_page']       = 1;
		$args['date_query']           = array();
		$args['date_query']['before'] = $current_date;

		// Get post before the current calendar date (with 'noarchive term excluded').
		$post_before = get_posts( $args );

		if ( isset( $post_before[0]->post_date ) ) {
			// Get the date values from the post.
			$date['prev_year']  = (int) date( 'Y', strtotime( $post_before[0]->post_date ) );
			$date['prev_month'] = (int) date( 'n', strtotime( $post_before[0]->post_date ) );
		} else {
			// Return false for 'prev_year' or 'prev_month' if no posts are found.
			// This prevents the calendar querying for next and previous archive dates.
			$date['prev_year'] = false;
		}

		// Query for previous archive month.
		$args['date_query']          = array();
		$args['date_query']['after'] = $current_date;
		$args['order']               = 'ASC';

		// Get a post after the current calendar date (with 'noarchive term excluded').
		$post_after = get_posts( $args );

		if ( isset( $post_after[0]->post_date ) ) {
			// Get the date values from the post.
			$date['next_year']  = (int) date( 'Y', strtotime( $post_after[0]->post_date ) );
			$date['next_month'] = (int) date( 'n', strtotime( $post_after[0]->post_date ) );
		} else {
			// Return false for 'next_year' or 'next_month' if no posts are found.
			// This prevents the calendar querying for next and previous archive dates.
			$date['next_year'] = false;
		}

		return $date;
	}
}
