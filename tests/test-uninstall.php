<?php

/**
 * Tests for uninstall.php
 */
class KM_CPTDA_Tests_Uninstall extends WP_UnitTestCase {

	/**
	 * Tests uninstall deletes option.
	 */
	function test_uninstall() {

		update_option( 'custom_post_type_date_archives', 'fake option' );

		// Test if option exists
		$this->assertEquals( 'fake option', get_option( 'custom_post_type_date_archives' ) );

		define( 'WP_UNINSTALL_PLUGIN', CPT_DATE_ARCHIVES_PLUGIN_DIR . 'custom-post-type-date-archives.php' );
		include CPT_DATE_ARCHIVES_PLUGIN_DIR . 'uninstall.php';

		$this->assertFalse( get_option( 'custom_post_type_date_archives' ) );
	}
}
