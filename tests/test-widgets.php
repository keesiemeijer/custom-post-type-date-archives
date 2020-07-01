<?php

/**
 * Tests for public plugin functions
 *
 * @group Widget
 */
class KM_CPTDA_Tests_Widgets extends CPTDA_UnitTestCase {

	/**
	 * Reset post type on teardown.
	 */
	function tearDown() {
		parent::tearDown();
		$this->unregister_post_type();
	}

	/**
	 * Test if the widget exists.
	 */
	function test_widgets_exists() {
		global $wp_widget_factory;

		$this->assertArrayHasKey( 'CPTDA_Widget_Archives', $wp_widget_factory->widgets );
		$this->assertArrayHasKey( 'CPTDA_Widget_Calendar', $wp_widget_factory->widgets );
		$this->assertArrayHasKey( 'CPTDA_Widget_Recent_Posts', $wp_widget_factory->widgets );
	}

	/**
	 * Test output from widget.
	 */
	function test_archives_widget_output() {

		global $wp_locale;
		$this->init();
		$year = (int) date( "Y" ) - 1;

		$expected = '';
		foreach ( array( '03', '02' ) as $month ) {
			$args = array( 'post_date' => "$year-$month-20 00:00:00", 'post_type' => 'cpt' );
			$post = $this->factory->post->create( $args );
			$url = cptda_get_month_link( $year, $month, 'cpt' );
			$text = sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $month ), $year );
			$expected .=  trim( get_archives_link( $url, $text ) ) . "\n";
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
		$widget->widget( $args, $instance );
		$output = ob_get_clean();

		$this->assertContains( '<h2>Archives</h2>', $output );
		$this->assertContains( '<section>', $output );
		$this->assertContains( '</section>', $output );

		$expected = <<<EOF
<section><h2>Archives</h2><ul>\n{$expected}</ul>\n</section>
EOF;
		$this->assertSame( strip_ws( $expected ), strip_ws( $output ) );
	}

	/**
	 * Test calendar widget output.
	 */
	function test_calendar_widget_output() {

		global $wp_locale;
		$this->init();
		$year = (int) date( "Y" ) - 1;

		$expected = '';
		foreach ( array( '03', '02' ) as $month ) {
			$args = array( 'post_date' => "$year-$month-20 00:00:00", 'post_type' => 'cpt' );
			$post = $this->factory->post->create( $args );
		}

		$this->go_to( '?post_type=cpt&year=' . $year . '&monthnum=3' );
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
		$widget->widget( $args, $instance );
		$output = ob_get_clean();

		$this->assertContains( '<h2>Archives</h2>', $output );
		$this->assertContains( '<section>', $output );
		$this->assertContains( '</section>', $output );

		$expected = <<<EOF
<section>\n<h2>Archives</h2>\n<div id="calendar_wrap" class="calendar_wrap">{$calendar}</div></section>
EOF;
		$this->assertSame( strip_ws( $expected ), strip_ws( $output ) );
	}

	/**
	 * Test output from recent posts widget.
	 */
	function test_recent_posts_widget_output() {

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

		$widget = new CPTDA_Widget_Recent_Posts();

		ob_start();
		$args = array(
			'before_widget' => '<section>',
			'after_widget'  => '</section>',
			'before_title'  => '<h2>',
			'after_title'   => '</h2>',
		);

		$instance = array( 'post_type' => 'cpt' );
		$widget->widget( $args, $instance );
		$output = ob_get_clean();

		$this->assertContains( '<h2>Recent Posts</h2>', $output );
		$this->assertContains( '<section>', $output );
		$this->assertContains( '</section>', $output );
		$this->assertContains( $url, $output );

		$expected = <<<EOF
<section><h2>Recent Posts</h2><ul>{$expected}</ul></section>
EOF;
		$this->assertSame( preg_replace( '/\s+/', '', $expected ),  preg_replace( '/\s+/', '', $output ) );
	}

	/**
	 * Test output from recent posts widget.
	 */
	function test_recent_posts_future_posts_only() {

		global $wp_locale;
		$this->init();
		$year = (int) date( "Y" );

		$args = array( 'post_date' => "$year-02-20 00:00:00", 'post_type' => 'cpt' );
		$post_1 = $this->factory->post->create( $args );

		$args = array( 'post_date' => ( $year + 1 ) . "-02-20 00:00:00", 'post_type' => 'cpt' );
		$post_2 = $this->factory->post->create( $args );

		$args = array(
			'before_widget' => '<section>',
			'after_widget'  => '</section>',
			'before_title'  => '<h2>',
			'after_title'   => '</h2>',
		);

		$instance = array( 'post_type' => 'cpt', 'status_future' => true );

		$widget = new CPTDA_Widget_Recent_Posts();
		ob_start();
		$widget->widget( $args, $instance );
		$output = ob_get_clean();

		$this->assertEmpty( $output );

		wp_publish_post( $post_2 );

		$widget_2 = new CPTDA_Widget_Recent_Posts();
		ob_start();
		$widget_2->widget( $args, $instance );
		$output = ob_get_clean();

		$title = get_the_title( $post_2 );
		$url   = get_the_permalink( $post_2 );
		$expected = '<li><a href="' . $url . '">' . $title . '</a></li>';
		$expected = <<<EOF
<section><h2>Recent Posts</h2><ul>{$expected}</ul></section>
EOF;
		$this->assertSame( preg_replace( '/\s+/', '', $expected ),  preg_replace( '/\s+/', '', $output ) );
	}

	/**
	 * Test output from recent posts widget.
	 */
	function test_recent_posts_no_posts_found() {

		global $wp_locale;
		$this->init();

		$args = array(
			'before_widget' => '<section>',
			'after_widget'  => '</section>',
			'before_title'  => '<h2>',
			'after_title'   => '</h2>',
		);

		$instance = array( 'post_type' => 'cpt' );

		$widget = new CPTDA_Widget_Recent_Posts();
		ob_start();
		$widget->widget( $args, $instance );
		$output = ob_get_clean();

		// No posts created
		$this->assertEmpty( $output );

		$instance['message'] = 'no posts found';
		$widget_2 = new CPTDA_Widget_Recent_Posts();
		ob_start();
		$widget_2->widget( $args, $instance );
		$output = ob_get_clean();

		$expected = <<<EOF
<section><h2>Recent Posts</h2><p>no posts found</p></section>
EOF;
		// No posts found message is displayed.
		$this->assertSame( preg_replace( '/\s+/', '', $expected ),  preg_replace( '/\s+/', '', $output ) );
	}

	/**
	 * Test **not** replacing WordPress core default widgets.
	 *
	 */
	function test_not_replacing_core_widgets() {

		global $wp_locale;
		$this->init();
		$year = (int) date( "Y" ) - 1;

		$expected = '';
		foreach ( array( '03', '02' ) as $month ) {
			$args = array( 'post_date' => "$year-$month-20 00:00:00", 'post_type' => 'cpt' );
			$post = $this->factory->post->create( $args );
		}

		$widget   = new CPTDA_Widget_Archives();
		$this->assertSame( 'archives', $widget->id_base );

		$widget   = new CPTDA_Widget_Calendar();
		$this->assertSame( 'calendar', $widget->id_base );

		$widget = new CPTDA_Widget_Recent_Posts();
		$this->assertSame( 'recent-posts', $widget->id_base );

		add_filter( 'cptda_replace_default_core_widgets', '__return_false' );

		cptda_register_widgets();

		$widget   = new CPTDA_Widget_Archives();
		$this->assertSame( 'cptda_archives', $widget->id_base );

		$widget   = new CPTDA_Widget_Calendar();
		$this->assertSame( 'cptda_calendar', $widget->id_base );

		$widget = new CPTDA_Widget_Recent_Posts();
		$this->assertSame( 'cptda_recent-posts', $widget->id_base );
	}
}
