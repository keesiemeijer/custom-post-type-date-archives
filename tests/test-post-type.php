<?php
/**
 * Tests for dependencies and various plugin functions
 */
class KM_CPTDA_Post_Type extends CPTDA_UnitTestCase {

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
	}

	/**
	 * Test slug with front (blog)
	 */
	function test_cpt_slug_with_front() {
		$this->init();
		$slug = cptda_get_post_type_base( 'cpt' );
		$this->assertSame( 'blog/cpt', $slug );
	}

	/**
	 * Test slug
	 */
	function test_cpt_slug() {
		$this->set_permalink_structure( '/%postname%/' );
		$this->init();
		$slug = cptda_get_post_type_base( 'cpt' );
		$this->assertSame( 'cpt', $slug );
	}

	/**
	 * Test rewrite slug
	 */
	function test_cpt_rewrite_slug() {
		$this->init( 'cpt', 'publish', array( 'slug' => 'rewrite', 'with_front' => true ) );
		$slug = cptda_get_post_type_base( 'cpt' );
		$this->assertSame( 'blog/rewrite', $slug );
	}

	/**
	 * Test rewrite slug without front
	 */
	function test_cpt_rewrite_slug_without_front() {
		$this->init( 'cpt', 'publish', array( 'slug' => 'rewrite', 'with_front' => false ) );
		$plugin = cptda_date_archives();
		$slug = cptda_get_post_type_base( 'cpt' );
		$this->assertSame( 'rewrite', $slug );
	}

	/**
	 * Test rewrite set to false
	 */
	function test_cpt_rewrite_slug_rewrite_false() {
		global $wp_rewrite;
		$args = array( 'public' => true, 'has_archive' => true, 'rewrite' => false );
		register_post_type( 'cpt', $args );
		$this->cpt_setup( 'cpt' );
		$post_type = get_post_type_object( 'cpt' );
		$slug = cptda_get_post_type_base( 'cpt' );
		$this->assertSame( '', $slug );
	}

}
