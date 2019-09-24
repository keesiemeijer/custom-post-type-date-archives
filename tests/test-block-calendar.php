<?php

/**
 * Tests for public plugin functions
 *
 * @group Calendar
 */
class KM_CPTDA_Tests_Block_Calendar extends CPTDA_UnitTestCase {


	/**
	 * Set up.
	 */
	function setUp() {
		parent::setUp();
		$this->set_permalink_structure( '/%postname%/' );
	}

	/**
	 * Reset post type on teardown.
	 */
	function tearDown() {
		parent::tearDown();
		$this->unregister_post_type();
	}

	/**
	 * Test test calendar block output.
	 */
	function test_calendar_block_in_post_content() {
		global $monthnum, $year;

		$previous_monthnum = $monthnum;
		$previous_year     = $year;

		// Set globals
		$year = (int) date( "Y" ) - 1;
		$monthnum = 3;

		$this->init();

		$expected = '';
		foreach ( array( '03', '01' ) as $month ) {
			$args = array( 'post_date' => "$year-$month-20 00:00:00", 'post_type' => 'cpt' );
			$post = $this->factory->post->create( $args );
		}

		$block = '<!-- wp:cptda/calendar {"post_type":"cpt"} /-->';
		$block = apply_filters( 'the_content', $block );

		$expected = cptda_get_calendar( 'cpt', true, false );
		$expected = "<div class=\"wp-block-calendar cptda-block-calendar\">{$expected}</div>";

		$monthnum = $previous_monthnum;
		$year = $previous_year;

		$this->assertEquals( strip_ws( $expected ), strip_ws( $block ) );
	}

	/**
	 * Test test calendar block output.
	 */
	function test_calendar_block_is_equal_to_wp_calendar_block() {
		global $monthnum, $year;

		$previous_monthnum = $monthnum;
		$previous_year     = $year;

		// Set globals
		$year = (int) date( "Y" ) - 1;
		$monthnum = 3;

		//$this->init();

		$expected = '';
		foreach ( array( '03', '01' ) as $month ) {
			$args = array( 'post_date' => "$year-$month-20 00:00:00", 'post_type' => 'post' );
			$post = $this->factory->post->create( $args );
		}

		$block = '<!-- wp:calendar /-->';
		$block = apply_filters( 'the_content', $block );

		$expected = cptda_render_block_calendar( array('post_type' => 'post') );
		$expected = str_replace(' cptda-block-calendar', '', $expected);

		$monthnum = $previous_monthnum;
		$year = $previous_year;

		// Same as WP calendar
		$this->assertEquals( strip_ws( $expected ), strip_ws( $block ) );
	}

}
