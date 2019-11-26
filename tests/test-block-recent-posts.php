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
		global $wp_locale, $wp_version;
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

		$block_class = 'wp-block-latest-posts wp-block-latest-posts__list cptda-block-latest-posts';
		$cptda_latest_posts = "<ul class=\"{$block_class}\">\n{$expected}</ul>\n";

		$this->assertEquals( strip_ws( $cptda_latest_posts ), strip_ws( $block ) );

		$args = array(
			'post_type' => 'cpt',
			'class'     => 'wp-block-latest-posts',
		);

		$recent_posts_html = cptda_get_recent_posts_html( $posts, $args );
		// $wp_latest_posts = "<ul class=\"{$block_class}\">\n{$expected}</ul>\n";
		// $block_class = $this->get_back_compat_latest_posts_class();


		$this->assertEquals( strip_ws( $cptda_latest_posts ), strip_ws( $recent_posts_html ) );
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
		$block_class = $this->get_back_compat_latest_posts_class();

		$wp_latest_posts = "<ul class=\"{$block_class}\">{$expected}</ul>\n\n";

		// Same as WP latest posts block mark up
		$this->assertEquals( strip_ws( $wp_latest_posts ), strip_ws( $block ) );

		$args = array(
			'post_type' => 'post',
		);

		$recent_posts_html = cptda_render_block_recent_posts( $args );

		$block_class = 'wp-block-latest-posts wp-block-latest-posts__list cptda-block-latest-posts';
		$cptda_latest_posts = "<ul class=\"{$block_class}\">{$expected}</ul>\n\n";

		// Same as WP latest posts block mark up (with extra newlines)
		$this->assertEquals(  preg_replace( '/\s+/', '', $cptda_latest_posts ), preg_replace( '/\s+/', '', $recent_posts_html ) );
	}

	function test_no_posts_found() {
		$this->init();

		$args = array(
			'post_type' => 'cpt',
		);

		$recent_posts_html = cptda_render_block_recent_posts( $args );
		$expected = '';
		$this->assertEquals( strip_ws( $expected ), strip_ws( $recent_posts_html ) );
	}

	function test_no_posts_found_message() {
		$this->init();

		$args = array(
			'post_type' => 'cpt',
			'message' => 'No posts found',
		);

		$recent_posts_html = cptda_render_block_recent_posts( $args );
		$expected = "<div class=\"cptda-block-latest-posts cptda-no-posts\">\n<p>No posts found</p>\n</div>";
		$this->assertEquals( strip_ws( $expected ), strip_ws( $recent_posts_html ) );
	}

}
