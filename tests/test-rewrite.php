<?php
/**
 * Tests CPT date archive queries
 */
class KM_CPTDA_Tests_Rewrite extends CPTDA_UnitTestCase {

	/**
	 * Set up.
	 */
	function setUp() {
		parent::setUp();
		$this->set_permalink_structure( 'blog/%postname%/' );
	}


	/**
	 * Reset post type on teardown.
	 */
	function tearDown() {
		parent::tearDown();
		$this->unregister_post_type();
		remove_filter( "cptda_cpt_date_archives_feed", '__return_false' );
	}


	/**
	 * Test date permastruct with_front false
	 */
	function test_rewrite_date_permastruct_with_front_false() {
		$this->init( 'cpt', 'publish', array( 'with_front' => false ) );

		$rewrite = new CPTDA_CPT_Rewrite( 'cpt' );
		$this->assertSame( 'cpt/%year%/%monthnum%/%day%', $rewrite->get_date_permastruct() );
	}


	/**
	 * Test date permastruct %day%/%monthnum%/%year%/ rewrite
	 */
	function test_rewrite_date_permastruct() {
		$this->set_permalink_structure( 'blog/%day%/%monthnum%/%year%/' );
		$this->init( 'cpt', 'publish' );

		$rewrite = new CPTDA_CPT_Rewrite( 'cpt' );
		$this->assertSame( 'blog/cpt/%day%/%monthnum%/%year%', $rewrite->get_date_permastruct() );
	}


	/**
	 * Test date permastruct with /%post_id%/ as third token
	 */
	function test_rewrite_date_permastruct_post_id() {
		$this->set_permalink_structure( 'blog/%category%/%postname%/%post_id%/' );
		$this->init( 'cpt', 'publish' );

		$rewrite = new CPTDA_CPT_Rewrite( 'cpt' );
		$this->assertSame( 'blog/cpt/date/%year%/%monthnum%/%day%', $rewrite->get_date_permastruct() );
	}


	/**
	 * Test date permastruct with slug my-cpt
	 */
	function test_rewrite_date_permastruct_rewrite_slug() {
		$this->init( 'cpt', 'publish', array( 'slug' => 'my-cpt' ) );

		$rewrite = new CPTDA_CPT_Rewrite( 'cpt' );
		$this->assertSame( 'blog/my-cpt/%year%/%monthnum%/%day%', $rewrite->get_date_permastruct() );
	}


	/**
	 * Test date permastruct with rewrite set to false
	 */
	function test_rewrite_date_permastruct_rewrite_false() {
		$args = array( 'public' => true, 'has_archive' => true, 'rewrite'  => false );

		register_post_type( 'cpt', $args );
		$this->cpt_setup( 'cpt' );
		$rewrite = new CPTDA_CPT_Rewrite( 'cpt' );

		$this->assertEmpty( $rewrite->get_date_permastruct() );
	}


	/**
	 * Test created rewrite rules.
	 */
	function test_rules() {
		global $wp_rewrite, $wp_version;

		$this->init();
		$posts = $this->create_posts();


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

		// Has all the key values but not in the right order.
		// We cannot use assertSame in this instance.
		$this->assertEquals( $expected, $rules->rules );
	}


	/**
	 * Test created rewrite rules without a feed.
	 */
	function test_rules_without_feed() {
		global $wp_rewrite;

		$this->init();
		$posts = $this->create_posts();

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

		$this->assertSame( $expected, $rules->rules );
	}


	/**
	 * Test created rewrite with rewrite set to false
	 */
	function test_rules_rewrite_false() {
		global $wp_rewrite;

		$args = array( 'public' => true, 'has_archive' => true, 'rewrite' => false );

		register_post_type( 'cpt', $args );
		$this->cpt_setup( 'cpt' );

		$cptda_rewrite = new CPTDA_Rewrite();
		$cptda_rewrite->setup_archives();
		$wp_rewrite->rules = array();

		$rules = $cptda_rewrite->generate_rewrite_rules( $wp_rewrite );
		$this->assertEmpty( $rules->rules );
	}
}
