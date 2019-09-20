<?php

/**
 * Tests for public plugin functions
 *
 * @group ff
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
	 * Test test archives output.
	 */
	function test_archive_block_in_post_content() {
	global $wp_locale;
		$this->init();
		$year = (int) date( "Y" ) - 1;

		$expected = '';
		foreach ( array( '03', '02' ) as $month ) {
			$args = array( 'post_date' => "$year-$month-20 00:00:00", 'post_type' => 'cpt' );
			$post = $this->factory->post->create( $args );
			$title = get_the_title( $post );
			$url   = get_the_permalink( $post );
			$expected .= "<li>\n<a href=\"{$url}\">{$title}</a>\n</li>\n";
		}

		$block = '<!-- wp:cptda/recent-posts {"post_type":"cpt"} /-->';
		$block = apply_filters('the_content', $block );
		//return "<ul class=\"{$class}\">\n{$recent_posts_html}\n</ul>\n</div>\n";

		$expected = "<div>\n<ul class=\"wp-block-recent-posts cptda-block-recent-posts\">\n{$expected}</ul>\n</div>\n";

		$this->assertEquals( strip_ws( $expected ), strip_ws( $block ) );

		// $archive = cptda_get_archives_html( array( 
		// 	'post_type' => 'cpt',
		// 	'echo' => false,
		// 	'class' => 'wp-block-archives'
		// 	)
		// );

		// // Check to see if cptda_get_archives_html() and cptda_render_block_archives() have same output.
		// $this->assertEquals( strip_ws( $expected ), strip_ws( $archive ) );
	}

}
