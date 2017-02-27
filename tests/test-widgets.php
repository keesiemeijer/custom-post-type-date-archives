<?php

/**
 * Tests for public plugin functions
 */
class KM_CPTDA_Tests_Widgets extends WP_UnitTestCase {

	/**
	 * Utils object to create posts to test with.
	 *
	 * @var object
	 */
	private $utils;

	/**
	 * Set up.
	 */
	function setUp() {
		parent::setUp();

		// Use the utils class to create posts with terms
		$this->utils = new CPTDA_Test_Utils( $this->factory );
	}

	/**
	 * Reset post type on teardown.
	 */
	function tearDown() {
		parent::tearDown();
		$this->utils->unregister_post_type();
	}

	/**
	 * Test if the widget exists.
	 */
	function test_widgets_exists() {
		global $wp_widget_factory;

		$this->assertArrayHasKey( 'CPTDA_Widget_Archives', $wp_widget_factory->widgets );
		$this->assertArrayHasKey( 'CPTDA_Widget_Calendar', $wp_widget_factory->widgets );
	}

	/**
	 * Test output from widget.
	 *
	 */
	function test_archives_widget_output() {

		global $wp_locale;
		$this->utils->init();
		$year = (int) date( "Y" ) -1;

		$expected = '';
		foreach ( array( '03', '02' ) as $month ) {
			$args = array( 'post_date' => "$year-$month-20 00:00:00", 'post_type' => 'cpt' );
			$post = $this->factory->post->create( $args );
			$url = cptda_get_month_link( $year, $month, 'cpt' );
			$text = sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $month ), $year );
			$expected .=  trim( get_archives_link( $url, $text ) );
		}

		$widget = new CPTDA_Widget_Archives( 'archives', __( 'Archives' ) );

		ob_start();
		$args = array(
			'before_widget' => '<section>',
			'after_widget'  => '</section>',
			'before_title'  => '<h2>',
			'after_title'   => '</h2>',
		);

		$instance = array( 'post_type' => 'cpt' );
		$widget->_set( 2 );
		$widget->widget( $args, $instance );
		$output = ob_get_clean();

		$this->assertContains( '<h2>Archives</h2>', $output );
		$this->assertContains( '<section>', $output );
		$this->assertContains( '</section>', $output );

		$expected = <<<EOF
<section><h2>Archives</h2><ul>{$expected}</ul></section>
EOF;
		$this->assertEquals( strip_ws( $expected ), strip_ws( $output ) );
	}

	/**
	 * Test calendar widget output.
	 */
	function test_calendar_widget_output() {

		global $wp_locale;
		$this->utils->init();
		$year = (int) date( "Y" ) -1;

		$expected = '';
		foreach ( array( '03', '02' ) as $month ) {
			$args = array( 'post_date' => "$year-$month-20 00:00:00", 'post_type' => 'cpt' );
			$post = $this->factory->post->create( $args );
		}

		$this->go_to( '?post_type=cpt&year='. $year . '&monthnum=3' );
		$calendar = cptda_get_calendar( 'cpt', true, false );
		$widget   = new CPTDA_Widget_Calendar( 'calendar', __( 'Calendar' ) );

		ob_start();
		$args = array(
			'before_widget' => '<section>',
			'after_widget'  => '</section>',
			'before_title'  => '<h2>',
			'after_title'   => '</h2>',
		);

		$instance = array( 'post_type' => 'cpt' );
		$widget->_set( 2 );
		$widget->widget( $args, $instance );
		$output = ob_get_clean();

		$this->assertContains( '<h2>Archives</h2>', $output );
		$this->assertContains( '<section>', $output );
		$this->assertContains( '</section>', $output );

		$expected = <<<EOF
<section><h2>Archives</h2><div id="calendar_wrap" class="calendar_wrap">{$calendar}</div></section>
EOF;
		$this->assertEquals( strip_ws( $expected ), strip_ws( $output ) );
	}

	/**
	 * Test **not** replacing WordPress core default widgets.
	 */
	function test_not_replacing_core_widgets() {

		global $wp_locale;
		$this->utils->init();
		$year = (int) date( "Y" ) -1;

		$expected = '';
		foreach ( array( '03', '02' ) as $month ) {
			$args = array( 'post_date' => "$year-$month-20 00:00:00", 'post_type' => 'cpt' );
			$post = $this->factory->post->create( $args );
		}

		$widget   = new CPTDA_Widget_Archives();
		$this->assertEquals( 'archives', $widget->id_base );

		$widget   = new CPTDA_Widget_Calendar();
		$this->assertEquals( 'calendar', $widget->id_base );


		add_filter( 'cptda_replace_default_core_widgets', '__return_false' );

		cptda_register_widgets();

		$widget   = new CPTDA_Widget_Archives();
		$this->assertEquals( 'cptda_archives', $widget->id_base );

		$widget   = new CPTDA_Widget_Calendar();
		$this->assertEquals( 'cptda_calendar', $widget->id_base );
	}
}
