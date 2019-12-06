<?php
/**
 * Tests for the WordPress REST API in wp-rest-api.php
 *
 * @group Rest_API
 */
class CPTDA_WP_Rest_API_Calendar extends CPTDA_UnitTestCase {

	function tearDown() {
		$this->unregister_post_type();
	}

	/**
	 * Returns related posts with the WordPress REST API.
	 *
	 * @param int          $post_id    The post id to get related posts for.
	 * @param array|string $taxonomies The taxonomies to retrieve related posts from.
	 * @param array|string $args       Optional. Change what is returned.
	 * @return array|string            Empty array if no related posts found. Array with post objects, or error code returned by the request.
	 */
	function rest_cptda_get_calendar( $post_type, $args = array() ) {

		$request = new WP_REST_Request( 'GET', "/custom_post_type_date_archives/v1/{$post_type}/calendar" );

		$args    = is_array( $args ) ? $args : array( $args );
		foreach ( $args as $key => $value ) {
			$request->set_param( $key, $value );
		}

		$response = rest_do_request( $request );
		$data     = $response->get_data();

		return $data;
	}

	/**
	 * Test if the Related_Posts_By_Taxonomy_Rest_API class is loaded
	 *
	 */
	function test_wp_rest_api_class_is_loaded() {
		$this->assertTrue( class_exists( 'CPTDA_Rest_API_Calendar' ) );
	}

	/**
	 * Test the route is registered
	 *
	 * @requires function WP_REST_Controller::register_routes
	 */
	function test_wp_rest_api_route_is_registered() {
		global $wp_rest_server;
		$wp_rest_server = new Spy_REST_Server;
		do_action( 'rest_api_init' );
		$this->assertTrue( in_array( '/custom_post_type_date_archives/v1/(?P<cptda_type>[\w-]+)/calendar', array_keys( $wp_rest_server->get_routes() ) ) );
		$wp_rest_server = null;
	}

	/**
	 * Test success response for rest request.
	 *
	 * @requires function WP_REST_Controller::register_routes
	 */
	function test_wp_rest_api_success_response() {
		$this->init();
		$post_year = (int) date( "Y" ) - 1;

		$expected = '';
		// create posts for month
		foreach ( array( '03', '01' ) as $post_month ) {
			$args = array( 'post_date' => "$post_year-03-20 00:00:00", 'post_type' => 'cpt' );
			$post = $this->factory->post->create( $args );
		}

		$request = new WP_REST_Request( 'GET', '/custom_post_type_date_archives/v1/cpt/calendar' );
		$request->set_param( 'year', $post_year );
		$request->set_param( 'month', 3 );

		$response = rest_do_request( $request );
		$data     = $response->get_data();
		$expected = array(
			'post_type',
			'year',
			'month',
			'date',
			'rendered',
		);

		$data = array_keys( $data );

		sort( $expected );
		sort( $data );

		$this->assertEquals( $expected, $data );
	}
	/**
	 * Test success response for rest request.
	 *
	 * @requires function WP_REST_Controller::register_routes
	 */
	function test_invalid_post_type() {
		$data = $this->rest_cptda_get_calendar( 'invalid' );
		// WP Error
		$this->assertTrue( isset( $data['code'] ) );
	}

	/**
	 * Test test calendar output.
	 */
	function test_rendered_calendar() {
		global $wp_locale;
		$this->init();
		$year = (int) date( "Y" ) - 1;

		$expected = '';
		foreach ( array( '03', '01' ) as $month ) {
			$args = array( 'post_date' => "$year-$month-20 00:00:00", 'post_type' => 'cpt' );
			$post = $this->factory->post->create( $args );
		}

		$date = array(
			'year' =>  $year,
			'month' => 3,
		);

		$data = $this->rest_cptda_get_calendar( 'cpt', $date );
		$this->assertContains( "Posts published on March 20, $year", $data['rendered'] );
		$this->assertContains( '<td><a ', $data['rendered'] );
		$this->assertContains( cptda_get_day_link( $year, 3, 20, 'cpt' ) , $data['rendered'] );
		$this->assertContains( '>&laquo; Jan<', $data['rendered'] );

		// editor block wrapper div
		$this->assertNotContains( '<div', $data['rendered'] );

		$date['month'] = 2;
		$data = $this->rest_cptda_get_calendar( 'cpt', $date );
		$this->assertContains( '>&laquo; Jan<', $data['rendered'] );
		$this->assertContains( '>Mar &raquo;<', $data['rendered'] );
		$this->assertNotContains( '<td><a ', $data['rendered'] );

		$date['month'] = 1;
		$data = $this->rest_cptda_get_calendar( 'cpt', $date );
		$this->assertContains( "Posts published on January 20, $year", $data['rendered'] );
		$this->assertContains( '<td><a ', $data['rendered'] );
		$this->assertContains( cptda_get_day_link( $year, 1, 20, 'cpt' ) , $data['rendered'] );
		$this->assertContains( '>Mar &raquo;<', $data['rendered'] );
	}

	/**
	 * Test block calendar output.
	 */
	function test_rendered_block_calendar() {
		global $wp_locale;
		$this->init();
		$year = (int) date( "Y" ) - 1;

		$expected = '';
		foreach ( array( '03', '01' ) as $month ) {
			$args = array( 'post_date' => "$year-$month-20 00:00:00", 'post_type' => 'cpt' );
			$post = $this->factory->post->create( $args );
		}

		$date = array(
			'year'  =>  $year,
			'month' => 3,
			'class' => 'wp-block-calendar',
		);

		$data = $this->rest_cptda_get_calendar( 'cpt', $date );
		$this->assertContains( '<div class="wp-block-calendar cptda-block-calendar">', $data['rendered'] );
	}
}
