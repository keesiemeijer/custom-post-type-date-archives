<?php
/**
 * Tests CPT date archive template used
 */
class KM_CPTDA_Tests_Template extends CPTDA_UnitTestCase {

	private $theme;

	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		symlink( CPTDA_TEST_THEMES_DIR . '/themes/cptda-test-theme', WP_CONTENT_DIR . '/themes/cptda-test-theme' );
	}

	public static function wpTearDownAfterClass() {
		unlink( WP_CONTENT_DIR . '/themes/cptda-test-theme' );
	}

	/**
	 * Set up.
	 */
	function setUp() {
		parent::setUp();
		$this->theme = trailingslashit( get_stylesheet_directory() );
	}

	/**
	 * Reset post type on teardown.
	 */
	function tearDown() {
		parent::tearDown();
		$this->unregister_post_type();
		$this->unlink_templates();
	}

	function test_theme() {
		$my_theme = wp_get_theme();
		$this->assertEquals( 'CPTDA Dummy Test Theme', $my_theme->get( 'Name' ) );
	}

	/**
	 * Test dummy theme templates
	 *
	 * @depends KM_CPTDA_Tests_Template::test_theme
	 */
	function test_theme_templates() {
		$expected = array(
			'archive.php',
			'index.php',
			'style.css',
		);

		$files = array_map( 'basename', glob( $this->theme . '*' ) );
		sort( $expected );
		sort( $files );

		$this->assertEquals( $expected, $files );
	}

	/**
	 *
	 */
	function test_date_archives() {
		$this->go_to_date_archive( 'cpt' );
		$this->assertQueryTrue( 'is_date', 'is_archive', 'is_year', 'is_post_type_archive' );
	}

	/**
	 * Test archive-cpt.php template
	 *
	 * @depends KM_CPTDA_Tests_Template::test_theme
	 * @depends KM_CPTDA_Tests_Template::test_date_archives
	 */
	function test_template_archive_cpt() {
		$this->create_templates( array( 'archive', 'archive-cpt' ) );
		$this->go_to_date_archive( 'cpt' );
		$template = $this->get_template();
		$this->assertEquals( 'archive-cpt.php', basename( $template ) );
	}

	/**
	 * Test date.php template
	 *
	 * @depends KM_CPTDA_Tests_Template::test_theme
	 * @depends KM_CPTDA_Tests_Template::test_date_archives
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
	 * @depends KM_CPTDA_Tests_Template::test_date_archives
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
	 * @depends KM_CPTDA_Tests_Template::test_date_archives
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
	 * @depends KM_CPTDA_Tests_Template::test_date_archives
	 */
	function test_template_archive() {
		$this->create_templates( array( 'archive', 'date-cptda-archive', 'date-cpt' ) );
		$this->go_to_date_archive( 'post' );
		$template = $this->get_template();
		$this->assertEquals( 'archive.php', basename( $template ) );
	}

	function go_to_date_archive( $post_type = 'cpt' ) {
		$this->init();
		$posts  = $this->create_posts( $post_type );
		$_posts = get_posts( "post_type={$post_type}&posts_per_page=-1" );
		$year   = get_the_date( 'Y', $_posts[0] );

		if ( 'post' === $post_type ) {
			$this->go_to( "?year=" . $year  );
		} else {
			$this->go_to( "?post_type={$post_type}&year=" . $year  );
		}
	}

	function get_template() {
		if ( is_post_type_archive() && get_post_type_archive_template() ) {
			$template = get_post_type_archive_template();
		} elseif ( is_date() && get_date_template() ) {
			$template = get_date_template();
		} elseif ( is_archive() && get_archive_template() ) {
			$template = get_archive_template();
		} else {
			$template = get_index_template();
		}

		return cptda_date_template_include( $template );
	}

	function create_templates( $templates ) {
		foreach ( $templates as $template ) {
			if ( ! file_exists( $this->theme . "{$template}.php" ) ) {
				$file = fopen( $this->theme . "{$template}.php", "w" );
				fclose( $file );
			}
		}
	}

	function unlink_templates() {

		$templates = array(
			'archive-cpt',
			'date',
			'date-cptda-archive',
			'date-cpt',
		);

		foreach ( $templates as $template ) {
			if ( file_exists( $this->theme . "{$template}.php" ) ) {
				unlink( $this->theme . "{$template}.php" );
			}
		}
	}

}
