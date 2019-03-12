<?php
/**
 * Tests for the WordPress REST API in wp-rest-api.php
 *
 * @group Rest_API
 */
class CPTDA_WP_Rest_API_Recent_Posts extends CPTDA_UnitTestCase {

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
	function rest_cptda_get_recent_posts( $post_type, $args = array() ) {

		$request = new WP_REST_Request( 'GET', "/custom_post_type_date_archives/v1/{$post_type}/recent-posts" );

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
		$this->assertTrue( class_exists( 'CPTDA_Rest_API_Recent_Posts' ) );
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
		$this->assertTrue( in_array( '/custom_post_type_date_archives/v1/(?P<cptda_type>[\w-]+)/recent-posts', array_keys( $wp_rest_server->get_routes() ) ) );
		$wp_rest_server = null;
	}

	/**
	 * Test success response for rest request.
	 *
	 * @requires function WP_REST_Controller::register_routes
	 */
	function test_wp_rest_api_success_response() {
		$this->init();

		$request  = new WP_REST_Request( 'GET', '/custom_post_type_date_archives/v1/cpt/recent-posts' );
		$response = rest_do_request( $request );
		$data     = $response->get_data();
		$expected = array(
			'posts',
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
		$data = $this->rest_cptda_get_recent_posts( 'invalid' );
		$this->assertTrue( isset( $data['code'] ) );
	}

	/**
	 * Test test rendered output.
	 */
	function test_rendered_recent_posts() {
		global $wp_locale;
		$this->init();
		$year = (int) date( "Y" ) - 1;

		$expected = '';
		foreach ( array( '03', '02' ) as $month ) {
			$args = array( 'post_date' => "$year-$month-20 00:00:00", 'post_type' => 'cpt' );
			$post = $this->factory->post->create( $args );
			$title = get_the_title( $post );
			$url   = get_the_permalink( $post );
			$expected .= '<li><a href="' . $url . '">' . $title . '</a></li>';
		}

		$args = array(
			'title'        => 'Recent Posts',
			'before_title' =>  '<h2>',
			'after_title'  =>  '</h2>',
			'post_type'    => 'cpt',
		);

		$data = $this->rest_cptda_get_recent_posts( 'cpt', $args );
		$expected = "<h2>Recent Posts</h2><ul>{$expected}</ul>";
		$this->assertEquals( preg_replace( '/\s+/', '', $expected ),  preg_replace( '/\s+/', '', $data['rendered'] ) );
	}

	/**
	 * Test pagination.
	 */
	function test_recent_posts_pagination() {
		global $wp_locale;
		$this->init();
		$year = (int) date( "Y" ) - 1;

		$expected = '';
		foreach ( array( '03', '02' ) as $month ) {
			$args  = array( 'post_date' => "$year-$month-20 00:00:00", 'post_type' => 'cpt' );
			$post  = $this->factory->post->create( $args );
			$title = get_the_title( $post );
			$url   = get_the_permalink( $post );
			$expected .= '<li><a href="' . $url . '">' . $title . '</a></li>';
		}

		$posts = get_posts( 'post_type=cpt&fields=ids' );

		$args = array(
			'title'  => 'Recent Posts',
			'number' => 1,
			'page'   => 2,
		);

		$data = $this->rest_cptda_get_recent_posts( 'cpt', $args );

		$this->assertEquals( 1 , count( $data['posts'] ) );
		$this->assertEquals( $posts[1] , $data['posts'][0] );
	}
}
