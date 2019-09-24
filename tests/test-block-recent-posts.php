<?php

/**
 * Tests for public plugin functions
 *
 * @group Recent_Posts
 */
class KM_CPTDA_Tests_Block_Recent_Posts extends CPTDA_UnitTestCase {

	/**
	 * Set up.
	 */
	function setUp() {
		parent::setUp();
		$this->set_permalink_structure( '/%postname%/' );
	}

	/**
	 * Test test recent posts block output.
	 */
	function test_recent_posts_block_in_post_content() {
		global $wp_locale;
		$this->init();
		$year = (int) date( "Y" ) - 1;

		$expected = '';
		$posts = array();
		foreach ( array( '03', '02' ) as $month ) {
			$args = array( 'post_date' => "$year-$month-20 00:00:00", 'post_type' => 'cpt' );
			$post = $this->factory->post->create( $args );
			$posts[] = $post;
			$title = get_the_title( $post );
			$url   = get_the_permalink( $post );
			$expected .= "<li>\n<a href=\"{$url}\">{$title}</a>\n</li>\n";
		}

		$block = '<!-- wp:cptda/latest-posts {"post_type":"cpt"} /-->';
		$block = apply_filters( 'the_content', $block );

		$expected = "<ul class=\"wp-block-latest-posts cptda-block-latest-posts\">\n{$expected}</ul>\n";

		$this->assertEquals( strip_ws( $expected ), strip_ws( $block ) );

		$args = array(
			'post_type' => 'cpt',
			'class'     => 'wp-block-latest-posts',
		);

		$recent_posts_html = cptda_get_recent_posts_html( $posts, $args );

		$this->assertEquals( strip_ws( $expected ), strip_ws( $recent_posts_html ) );
	}

	/**
	 * Test test recent posts block output.
	 */
	function test_output_of_latest_posts_block_is_equal_to_wp_block() {
		global $wp_locale;
		// $this->init();
		$year = (int) date( "Y" ) - 1;

		$expected = '';
		foreach ( array( '03', '02' ) as $month ) {
			$args = array( 'post_date' => "$year-$month-20 00:00:00", 'post_type' => 'post' );
			$post = $this->factory->post->create( $args );
			$title = get_the_title( $post );
			$url   = get_the_permalink( $post );
			$expected .= "<li><a href=\"{$url}\">{$title}</a></li>\n";
		}

		$block = '<!-- wp:latest-posts /-->';
		$block = apply_filters( 'the_content', $block );

		$expected = "<ul class=\"wp-block-latest-posts\">{$expected}</ul>\n\n";

		// Same as WP latest posts block mark up
		$this->assertEquals( strip_ws( $expected ), strip_ws( $block ) );

		$args = array(
			'post_type' => 'post',
		);

		$recent_posts_html = cptda_render_block_recent_posts( $args );
		$recent_posts_html = str_replace(' cptda-block-latest-posts', '', $recent_posts_html);

		// Same as WP latest posts block mark up (with extra newlines)
		$this->assertEquals(  preg_replace( '/\s+/', '', $expected ), preg_replace( '/\s+/', '', $recent_posts_html ) );
	}

}
