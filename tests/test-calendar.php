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

		remove_filter( 'cptda_calendar_data', array( $this, 'set_next_month_to_june' ), 10 );
		remove_filter( 'cptda_calendar_data', array( $this, 'set_next_month_navigation_to_false' ), 10 );
		remove_filter( 'cptda_calendar_data', array( $this, 'set_date_to_march_18' ), 10 );
		remove_filter( 'cptda_calendar_data', array( $this, 'no_dates' ), 10 );
	}

	/**
	 * Test test calendar output.
	 *
	 * @depends KM_CPTDA_Tests_Testcase::test_init
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
	 *
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
	 *
	 * @depends KM_CPTDA_Tests_Testcase::test_init
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

		$this->assertSame( $queries_before + 3, $queries_after );

		$calendar       = cptda_get_calendar( 'cpt', true, false );
		$no_queries     = get_num_queries();

		$this->assertSame( $queries_after, $no_queries );
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
			'year'          => 2018,
			'month'         => '03',
			'last_day'      => '31',
			'next_year'     => '2018',
			'prev_year'     => '2018',
			'next_month'    => '5',
			'prev_month'    => '1',
			'calendar_days' => array( 20 ),
		);

		unset( $calendar_data['timestamp'], $calendar_data['unixmonth'] );

		$this->assertSame( $expected, $calendar_data );
	}

	/**
	 * Test calendar output with navigation to the month of June.
	 *
	 * @depends KM_CPTDA_Tests_Testcase::test_init
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
}
