<?php
/**
 * Tests for the WordPress REST API in wp-rest-api.php
 *
 * @group Rest_API
 */
class CPTDA_Tests_Rest_API_Archives extends CPTDA_UnitTestCase {

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
	function rest_cptda_get_archives( $post_type, $args = array() ) {

		$request = new WP_REST_Request( 'GET', "/custom_post_type_date_archives/v1/{$post_type}/archives" );

		$args    = is_array( $args ) ? $args : array( $args );
		foreach ( $args as $key => $value ) {
			$request->set_param( $key, $value );
		}

		$response = rest_do_request( $request );
		$data     = $response->get_data();

		return $data;
	}

	/**
	 * Test if the CPTDA_Rest_API_Archives class is loaded
	 *
	 */
	function test_wp_rest_api_class_is_loaded() {
		$this->assertTrue( class_exists( 'CPTDA_Rest_API_Archives' ) );
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
		$this->assertTrue( in_array( '/custom_post_type_date_archives/v1/(?P<cptda_type>[\w-]+)/archives', array_keys( $wp_rest_server->get_routes() ) ) );
		$wp_rest_server = null;
	}

	/**
	 * Test success response for rest request.
	 *
	 *
	 * @requires function WP_REST_Controller::register_routes
	 */
	function test_wp_rest_api_success_response() {
		$this->init();

		$request  = new WP_REST_Request( 'GET', '/custom_post_type_date_archives/v1/cpt/archives' );
		$response = rest_do_request( $request );
		$data     = $response->get_data();
		$expected = array(
			'archives',
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
		$data = $this->rest_cptda_get_archives( 'invalid' );
		// WP Error
		$this->assertTrue( isset( $data['code'] ) );
	}

	/**
	 * Test test rendered output.
	 */
	function test_rendered_archives() {
		global $wp_locale;
		$this->init();
		$year = (int) date( "Y" ) - 1;

		$expected = '';
		foreach ( array( '03', '02' ) as $month ) {
			$args = array( 'post_date' => "$year-$month-20 00:00:00", 'post_type' => 'cpt' );
			$post = $this->factory->post->create( $args );
			$url  = cptda_get_month_link( $year, $month, 'cpt' );
			$text = sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $month ), $year );
			$expected .=  trim( get_archives_link( $url, $text ) );
		}

		$args = array(
			'title'        => 'Archives',
			'before_title' => '<h2>',
			'after_title'  => '</h2>',
			'post_type'    => 'cpt',
			'type'         => 'monthly',
		);

		$data = $this->rest_cptda_get_archives( 'cpt', $args );
		// Title is not allowed for the rest api.
		$expected = "<ul>{$expected}</ul>";
		$this->assertEquals( preg_replace( '/\s+/', '', $expected ),  preg_replace( '/\s+/', '', $data['rendered'] ) );
	}

	/**
	 * Test test rendered output.
	 */
	function test_rendered_block_archives() {
		global $wp_locale;
		$this->init();
		$year = (int) date( "Y" ) - 1;

		$expected = '';
		foreach ( array( '03', '02' ) as $month ) {
			$args = array( 'post_date' => "$year-$month-20 00:00:00", 'post_type' => 'cpt' );
			$post = $this->factory->post->create( $args );
			$url  = cptda_get_month_link( $year, $month, 'cpt' );
			$text = sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $month ), $year );
			$expected .=  trim( get_archives_link( $url, $text ) );
		}

		$args = array(
			'title'        => 'Archives',
			'before_title' => '<h2>',
			'after_title'  => '</h2>',
			'post_type'    => 'cpt',
			'type'         => 'monthly',
			'class'        => 'wp-block-archives',
		);

		$data = $this->rest_cptda_get_archives( 'cpt', $args );
		// Title is not allowed for the rest api.
		$expected = "<ulclass=\"wp-block-archivescptda-block-archiveswp-block-archives-list\">{$expected}</ul>";
		$this->assertEquals( preg_replace( '/\s+/', '', $expected ),  preg_replace( '/\s+/', '', $data['rendered'] ) );
	}

	/**
	 * Test test rendered output.
	 *
	 */
	function test_rendered_archives_wp_kses() {
		global $wp_locale;
		$this->init();
		$year = (int) date( "Y" ) - 1;

		$expected = '';
		$expected_with_script = '';
		foreach ( array( '03', '02' ) as $month ) {
			$args = array( 'post_date' => "$year-$month-20 00:00:00", 'post_type' => 'cpt' );
			$post = $this->factory->post->create( $args );
			$url  = cptda_get_month_link( $year, $month, 'cpt' );
			$text = sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $month ), $year );
			$expected .=  trim( get_archives_link( $url, $text, 'html', 'alert("hello");' ) ) . "\n";
		}

		$args = array(
			'post_type'    => 'cpt',
			'type'         => 'monthly',
			'before'       => '<script type="text/javascript">alert("hello");</script>'
		);

		$data = $this->rest_cptda_get_archives( 'cpt', $args );
		// Script tags are removed from output
		$expected = "<ul>\n{$expected}</ul>";
		$this->assertEquals( strip_ws( $expected ),  strip_ws( $data['rendered'] ) );
	}

	/**
	 * Test test rendered output.
	 */
	function test_rendered_archives_dropdown() {
		global $wp_locale;
		$this->init();
		$year = (int) date( "Y" ) - 1;

		$expected = '';
		$expected_with_script = '';
		foreach ( array( '03', '02' ) as $month ) {
			$args = array( 'post_date' => "$year-$month-20 00:00:00", 'post_type' => 'cpt' );
			$post = $this->factory->post->create( $args );
			$url  = cptda_get_month_link( $year, $month, 'cpt' );
			$text = sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $month ), $year );
			$expected .=  trim( get_archives_link( $url, $text, 'option' ) ) . "\n";
		}

		$select = '<label class="screen-reader-text" for="wp-block-archives-">Archives</label>';
		$select .= '<select id="wp-block-archives-" name="archive-dropdown"';
		$select .= ' onchange="document.location.href=this.options[this.selectedIndex].value;">';
		$select .= '<option value="">Select Month</option>';
		$expected = $select . "\t" . $expected . '</select>';
		$args = array(
			'format'    => 'option',
			'post_type' => 'cpt',
			'type'      => 'monthly',
		);

		$data = $this->rest_cptda_get_archives( 'cpt', $args );

		// Remove id number
		$data = preg_replace("#id=\"wp-block-archives\-(.*?)\"#", 'id="wp-block-archives-"',  $data['rendered'] );
		$data = preg_replace("#for=\"wp-block-archives\-(.*?)\"#", 'for="wp-block-archives-"',  $data );

		$this->assertEquals( strip_ws( $expected ),  strip_ws( $data ) );
	}

	/**
	 * Test pagination.
	 */
	function test_archive_pagination() {
		global $wp_locale;
		$this->init();
		$year = (int) date( "Y" ) - 1;

		$expected = '';
		foreach ( array( '03', '02' ) as $month ) {
			$args = array( 'post_date' => "$year-$month-20 00:00:00", 'post_type' => 'cpt' );
			$post = $this->factory->post->create( $args );
			$url  = cptda_get_month_link( $year, $month, 'cpt' );
			$text = sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $month ), $year );
			if ( '02' === $month ) {
				$expected .= get_archives_link( $url, $text );
			}
		}

		$posts = get_posts( 'post_type=cpt&fields=ids' );

		$args = array(
			'limit' => 1,
			'page'  => 2,
		);

		$data     = $this->rest_cptda_get_archives( 'cpt', $args );
		$expected = "<ul>\n{$expected}\n</ul>";

		$this->assertEquals( 1 , count( $data['archives'] ) );
		$this->assertEquals( strip_ws( $expected ) , strip_ws( $data['rendered'] ) );
	}
}
