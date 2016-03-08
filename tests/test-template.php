<?php
/**
 * Tests CPT date archive template used
 */
class KM_CPTDA_Tests_Template extends WP_UnitTestCase {

	/**
	 * Utils object to create posts with terms to test with.
	 *
	 * @var object
	 */
	private $utils;
	private $theme;

	/**
	 * Set up.
	 */
	function setUp() {

		parent::setUp();
		$this->theme = trailingslashit( get_stylesheet_directory() );

		$this->templates = array(
			'archive',
			'archive-cpt',
			'date',
			'date-cptda-archive',
			'date-cpt',
		);

		foreach ( $this->templates as $key => $template ) {
			if ( file_exists( $this->theme . "{$template}.php" ) ) {
				unset( $this->templates[$key] );
			}
		}

		$this->templates = array_values( $this->templates );

		// Use the utils class to create posts
		$this->utils = new CPTDA_Test_Utils( $this->factory );
	}


	/**
	 * Reset post type on teardown.
	 */
	function tearDown() {
		parent::tearDown();
		$this->utils->unregister_post_type();
		$this->unlink_templates();
	}


	function test_theme() {
		$my_theme = wp_get_theme();
		$this->assertEquals( 'CPTDA Dummy Test Theme', $my_theme->get( 'Name' ) );

		$templates = array(
			'archive-cpt',
			'date',
			'date-cptda-archive',
			'date-cpt',
		);

		$this->assertEquals( $templates, $this->templates );
	}


	/**
	 * Test archive-cpt.php template
	 *
	 * @depends KM_CPTDA_Tests_Template::test_theme
	 */
	function test_template_archive_cpt() {
		$this->create_templates( array( 'archive', 'archive-cpt' ) );
		$this->go_to_date_archive();
		$template = $this->get_template();
		$this->assertEquals( 'archive-cpt.php', basename( $template ) );
	}


	/**
	 * Test date.php template
	 *
	 * @depends KM_CPTDA_Tests_Template::test_theme
	 */
	function test_template_date() {
		$this->create_templates( array( 'archive', 'archive-cpt', 'date' ) );
		$this->go_to_date_archive();
		$template = $this->get_template();
		$this->assertEquals( 'date.php', basename( $template ) );
	}


	/**
	 * Test date-cptda-archive.php template
	 *
	 * @depends KM_CPTDA_Tests_Template::test_theme
	 */
	function test_template_date_cptda_archive() {
		$this->create_templates( array( 'archive', 'date', 'date-cptda-archive' ) );
		$this->go_to_date_archive();
		$template = $this->get_template();

		$this->assertEquals( 'date-cptda-archive.php', basename( $template ) );
	}


	/**
	 * Test date-cpt.php template
	 *
	 * @depends KM_CPTDA_Tests_Template::test_theme
	 */
	function test_template_date_date_cpt() {
		$this->create_templates( array( 'archive', 'date', 'date-cptda-archive', 'date-cpt' ) );
		$this->go_to_date_archive();
		$template = $this->get_template();

		$this->assertEquals( 'date-cpt.php', basename( $template ) );
	}


	/**
	 * Test archive.php template for post type post
	 *
	 * @depends KM_CPTDA_Tests_Template::test_theme
	 */
	function test_template_archive() {
		$this->create_templates( array( 'archive', 'date-cptda-archive', 'date-cpt' ) );
		$this->go_to_date_archive( 'post' );
		$template = $this->get_template();
		$this->assertEquals( 'archive.php', basename( $template ) );
	}


	function go_to_date_archive( $post_type = 'cpt' ) {
		$this->utils->init();
		$posts  = $this->utils->create_posts( $post_type );
		$_posts = get_posts( "post_type={$post_type}&posts_per_page=-1" );
		$year   = get_the_date( 'Y', $_posts[0] );
		if ( 'post' === $post_type ) {
			$this->go_to( "?year=" . $year  );
		} else {
			$this->go_to( "?post_type={$post_type}&year=" . $year  );
		}
	}


	function get_template() {
		$template = false;
		if ( is_post_type_archive()  && $template = get_post_type_archive_template() ) :
		elseif ( is_date()           && $template = get_date_template() ) :
		elseif ( is_archive()        && $template = get_archive_template() ) :
		else :
			$template = get_index_template();
		endif;
		return cptda_date_template_include( $template );
	}


	function create_templates( $templates ) {
		foreach ( $templates as $template ) {
			if ( !file_exists( $this->theme . "{$template}.php" ) ) {
				$file = fopen( $this->theme . "{$template}.php", "w" );
				fclose( $file );
			}
		}
	}


	function unlink_templates() {
		foreach ( $this->templates as $template ) {
			if ( file_exists( $this->theme . "{$template}.php" ) ) {
				unlink( $this->theme . "{$template}.php" );
			}
		}
	}

}
