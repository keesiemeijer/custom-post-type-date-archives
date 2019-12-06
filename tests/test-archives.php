<?php

/**
 * Tests for public plugin functions
 *
 * @group Archive
 */
class KM_CPTDA_Tests_Archive extends CPTDA_UnitTestCase {

	protected $archive_objects = null;

	/**
	 * Test test archives output.
	 */
	function test_cptda_get_archives() {
		global $wp_locale;
		$this->init();
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
	 * Test test archives output.
	 */
	function test_cptda_get_archives_objects_with_filter() {
		global $wp_locale;
		$this->init();
		$year = (int) date( "Y" ) - 1;

		$args = array( 'post_date' => "$year-03-20 00:00:00", 'post_type' => 'cpt' );
		$post = $this->factory->post->create( $args );

		add_filter( 'cptda_get_archives', array( $this, 'get_objects' ), 10 , 2 );

		$expected  = array(
			'year' => '2018',
			'month' => '3',
			'posts' => '1',
		);

		$archive = cptda_get_archives( array( 'post_type' => 'cpt', 'echo' => false ) );

		$this->assertEquals( $expected, get_object_vars( $this->archive_objects[0] ) );
		$this->archive_objects = null;
	}

	/**
	 * Test test archives output.
	 */
	function test_cptda_get_archives_objects() {
		global $wp_locale;
		$this->init();
		$year = (int) date( "Y" ) - 1;

		$args = array( 'post_date' => "$year-03-20 00:00:00", 'post_type' => 'cpt' );
		$post = $this->factory->post->create( $args );

		$expected  = array(
			'year' => '2018',
			'month' => '3',
			'posts' => '1',
		);

		$archive = cptda_get_archives( array( 'post_type' => 'cpt', 'echo' => false, 'format' => 'object' ) );

		$this->assertEquals( $expected, get_object_vars( $archive[0] ) );
	}

	/**
	 * Test test archives post type.
	 */
	function test_cptda_get_archives_post_type() {
		global $wp_locale;
		$this->init();
		$year = (int) date( "Y" ) - 1;

		$args = array( 'post_date' => "$year-03-20 00:00:00", 'post_type' => 'post' );
		$post = $this->factory->post->create( $args );

		// No post type in args defaults to 'post'
		$archive = cptda_get_archives( array( 'echo' => false ) );
		$this->assertNotEmpty( $archive );

		// Post type in args
		$archive = cptda_get_archives( array( 'post_type' => 'post', 'echo' => false ) );
		$this->assertNotEmpty( $archive );

		// Empty post type in args (doesn't default to post)
		$archive = cptda_get_archives( array( 'post_type' => '', 'echo' => false ) );
		$this->assertEmpty( $archive );
	}

	/**
	 * Test offset.
	 */
	function test_archive_offset() {
		global $wp_locale;
		$this->init();
		$year = (int) date( "Y" ) - 1;

		$expected = '';
		foreach ( array( '03', '02' ) as $month ) {
			$args = array( 'post_date' => "$year-$month-20 00:00:00", 'post_type' => 'cpt' );
			$post = $this->factory->post->create( $args );
		}

		$posts = get_posts( 'post_type=cpt&fields=ids' );

		$args = array(
			'post_type' => 'cpt',
			'format'    => 'object',
			'echo'      => false,
			'limit'     => 1,
			'offset'    => 1,
		);

		$archive = cptda_get_archives( $args );

		$this->assertEquals( 1 , count( $archive ) );
		$this->assertEquals( '2' , $archive[0]->month );
	}

	function get_objects( $html, $objects ) {
		$this->archive_objects = $objects;
		return $html;
	}
}
