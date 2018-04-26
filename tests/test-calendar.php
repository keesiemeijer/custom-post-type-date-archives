<?php
/**
 * Tests for public plugin functions
 */
class KM_CPTDA_Tests_Calendar extends CPTDA_UnitTestCase {

	/**
	 * Reset post type on teardown.
	 */
	function tearDown() {
		parent::tearDown();
		$this->unregister_post_type();
		remove_filter( 'cptda_get_calendar_calendar_days', array( $this, 'set_date_to_march_18' ), 10, 2 );
		remove_filter( 'cptda_get_calendar_calendar_nav', array( $this, 'set_next_month_to_june' ), 10, 2 );
		remove_filter( 'cptda_get_calendar_calendar_nav', array( $this, 'set_next_month_navigation_to_false' ), 10, 2 );

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
	 *
	 * @depends KM_CPTDA_Tests_Testcase::test_init
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

		add_filter( 'cptda_get_calendar_calendar_days', array( $this, 'set_date_to_march_18' ), 10, 2 );
		$this->go_to( '?post_type=cpt&year=' . $year . '&monthnum=3' );

		$calendar = cptda_get_calendar( 'cpt', true, false );

		// Date March 18 added by the filter
		$this->assertContains( "Posts published on March 18, $year", $calendar );
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

		add_filter( 'cptda_get_calendar_calendar_nav', array( $this, 'set_next_month_to_june' ), 10, 2 );
		$this->go_to( '?post_type=cpt&year=' . $year . '&monthnum=3' );

		$calendar = cptda_get_calendar( 'cpt', true, false );

		// Date March 18 added by the filter
		$this->assertContains( '>Jun &raquo;<', $calendar );
	}

	/**
	 * Test calendar output with next month navigation_disabled.
	 *
	 * @depends KM_CPTDA_Tests_Testcase::test_init
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

		add_filter( 'cptda_get_calendar_calendar_nav', array( $this, 'set_next_month_navigation_to_false' ), 10, 2 );
		$this->go_to( '?post_type=cpt&year=' . $year . '&monthnum=1' );
		$calendar = cptda_get_calendar( 'cpt', true, false );

		// Does not have navigation to next month (March) archive
		// See the last assertion in the test_cptda_get_calendar() method
		$this->assertNotContains( '>Mar &raquo;<', $calendar );
	}

	function set_date_to_march_18( $days, $data ) {
		return array( 18 );
	}

	function set_next_month_to_june( $nav, $data ) {
		$nav['next']['year']  = $data['year'];
		$nav['next']['month'] = 6;

		return $nav;
	}

	function set_next_month_navigation_to_false( $nav, $data ) {
		$nav['next'] = false;
		return $nav;
	}
}
