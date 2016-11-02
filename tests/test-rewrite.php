<?php
/**
 * Tests CPT date archive queries
 */
class KM_CPTDA_Tests_Rewrite extends WP_UnitTestCase {

	/**
	 * Utils object to create posts with terms to test with.
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
		$this->utils->set_permalink_structure( 'blog/%postname%/' );
	}


	/**
	 * Reset post type on teardown.
	 */
	function tearDown() {
		parent::tearDown();
		$this->utils->unregister_post_type();
		remove_filter( "cptda_cpt_date_archives_feed", '__return_false' );
	}


	/**
	 * Test created rewrite rules.
	 */
	function test_rules() {
		global $wp_rewrite, $wp_version;

		$this->utils->init();
		$posts = $this->utils->create_posts();


		$cptda_rewrite = new CPTDA_Rewrite();
		$cptda_rewrite->setup_archives();

		$wp_rewrite->rules = array();
		$rules = $cptda_rewrite->generate_rewrite_rules( $wp_rewrite );
		$array_embed = array(
			'blog/cpt/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/embed/?$' => 'index.php?post_type=cpt&year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&embed=true',
			'blog/cpt/([0-9]{4})/([0-9]{1,2})/embed/?$' => 'index.php?post_type=cpt&year=$matches[1]&monthnum=$matches[2]&embed=true',
			'blog/cpt/([0-9]{4})/embed/?$' => 'index.php?post_type=cpt&year=$matches[1]&embed=true',
		);

		$expected = array (
			'blog/cpt/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$' => 'index.php?post_type=cpt&year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]',
			'blog/cpt/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$' => 'index.php?post_type=cpt&year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]',
			'blog/cpt/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/page/?([0-9]{1,})/?$' => 'index.php?post_type=cpt&year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&paged=$matches[4]',
			'blog/cpt/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/?$' => 'index.php?post_type=cpt&year=$matches[1]&monthnum=$matches[2]&day=$matches[3]',
			'blog/cpt/([0-9]{4})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$' => 'index.php?post_type=cpt&year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]',
			'blog/cpt/([0-9]{4})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$' => 'index.php?post_type=cpt&year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]',
			'blog/cpt/([0-9]{4})/([0-9]{1,2})/page/?([0-9]{1,})/?$' => 'index.php?post_type=cpt&year=$matches[1]&monthnum=$matches[2]&paged=$matches[3]',
			'blog/cpt/([0-9]{4})/([0-9]{1,2})/?$' => 'index.php?post_type=cpt&year=$matches[1]&monthnum=$matches[2]',
			'blog/cpt/([0-9]{4})/feed/(feed|rdf|rss|rss2|atom)/?$' => 'index.php?post_type=cpt&year=$matches[1]&feed=$matches[2]',
			'blog/cpt/([0-9]{4})/(feed|rdf|rss|rss2|atom)/?$' => 'index.php?post_type=cpt&year=$matches[1]&feed=$matches[2]',
			'blog/cpt/([0-9]{4})/page/?([0-9]{1,})/?$' => 'index.php?post_type=cpt&year=$matches[1]&paged=$matches[2]',
			'blog/cpt/([0-9]{4})/?$' => 'index.php?post_type=cpt&year=$matches[1]',
		);

		if ( version_compare( $wp_version, "4.5", ">=" ) ) {
			$expected = array_merge( $expected, $array_embed );
		}

		$this->assertEquals( $expected, $rules->rules );
	}


	/**
	 * Test created rewrite rules without a feed
	 */
	function test_rules_without_feed() {
		global $wp_rewrite;

		$this->utils->init();
		$posts = $this->utils->create_posts();

		add_filter( "cptda_cpt_date_archives_feed", '__return_false' );
		$cptda_rewrite = new CPTDA_Rewrite();
		$cptda_rewrite->setup_archives();
		$wp_rewrite->rules = array();
		$rules = $cptda_rewrite->generate_rewrite_rules( $wp_rewrite );

		$expected = array (
			'blog/cpt/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/page/?([0-9]{1,})/?$' => 'index.php?post_type=cpt&year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&paged=$matches[4]',
			'blog/cpt/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/?$' => 'index.php?post_type=cpt&year=$matches[1]&monthnum=$matches[2]&day=$matches[3]',
			'blog/cpt/([0-9]{4})/([0-9]{1,2})/page/?([0-9]{1,})/?$' => 'index.php?post_type=cpt&year=$matches[1]&monthnum=$matches[2]&paged=$matches[3]',
			'blog/cpt/([0-9]{4})/([0-9]{1,2})/?$' => 'index.php?post_type=cpt&year=$matches[1]&monthnum=$matches[2]',
			'blog/cpt/([0-9]{4})/page/?([0-9]{1,})/?$' => 'index.php?post_type=cpt&year=$matches[1]&paged=$matches[2]',
			'blog/cpt/([0-9]{4})/?$' => 'index.php?post_type=cpt&year=$matches[1]',
		);

		$this->assertEquals( $expected, $rules->rules );
	}


	/**
	 * Test created rewrite with rewrite set to false
	 */
	function test_rules_rewrite_false() {
		global $wp_rewrite;

		$args = array( 'public' => true, 'has_archive' => true, 'rewrite' => false );

		register_post_type( 'cpt', $args );
		$this->utils->setup( 'cpt' );

		$cptda_rewrite = new CPTDA_Rewrite();
		$cptda_rewrite->setup_archives();
		$wp_rewrite->rules = array();

		$rules = $cptda_rewrite->generate_rewrite_rules( $wp_rewrite );
		$this->assertEmpty( $rules->rules );
	}
}
