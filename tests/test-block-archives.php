<?php

/**
 * Tests for public plugin functions
 *
 * @group Archive
 */
class KM_CPTDA_Tests_Block_Archive extends CPTDA_UnitTestCase {

	/**
	 * Set up.
	 */
	function setUp() {
		parent::setUp();
		$this->set_permalink_structure( '/%postname%/' );
	}

	/**
	 * Test test archives block output.
	 */
	function test_archive_block_in_post_content() {
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

		$block = '<!-- wp:cptda/archives {"post_type":"cpt"} /-->';
		$block = apply_filters('the_content', $block );

		$expected = "<ul class=\"wp-block-archives cptda-block-archives wp-block-archives-list\">\n{$expected}</ul>";

		$this->assertEquals( strip_ws( $expected ), strip_ws( $block ) );

		$archive = cptda_get_archives_html( array( 
			'post_type' => 'cpt',
			'echo' => false,
			'class' => 'wp-block-archives'
			)
		);

		// Check to see if cptda_get_archives_html() and cptda_render_block_archives() have same output.
		$this->assertEquals( strip_ws( $expected ), strip_ws( $archive ) );
	}

	/**
	 * Test test archives block output.
	 */
	function test_output_of_archive_block_is_equal_to_wp_block() {
		global $wp_locale;
		$this->init();
		$year = (int) date( "Y" ) - 1;

		$expected = '';
		foreach ( array( '03', '02' ) as $month ) {
			$args = array( 'post_date' => "$year-$month-20 00:00:00", 'post_type' => 'post' );
			$post = $this->factory->post->create( $args );
			$url = get_month_link( $year, $month );
			$text = sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $month ), $year );
			$expected .= trim( get_archives_link( $url, $text ) ) . "\n";
		}

		$block = '<!-- wp:archives /-->';
		$block = apply_filters('the_content', $block );

		$expected = "<ul class=\"wp-block-archives wp-block-archives-list\">\n{$expected}</ul>\n";

		// Markup same as WP archives block
		$this->assertEquals( preg_replace( '/\s+/', '', $expected ), preg_replace( '/\s+/', '', $block ) );

		$archive = cptda_render_block_archives( array(
			'post_type' => 'post',
			'echo' => true,
			)
		);

		$archive = str_replace(' cptda-block-archives ', ' ', $archive);

		// Markup same as WP archives block
		$this->assertEquals( preg_replace( '/\s+/', '', $expected ), preg_replace( '/\s+/', '', $archive ) );
	}
}
