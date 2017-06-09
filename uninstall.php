<?php
/**
 * Uninstall Custom Post Type Date Archives
 *
 * @package     Custom Post Type Date Archives
 * @subpackage  Uninstall
 * @copyright   Copyright (c) 2014, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wp_rewrite;
$wp_rewrite->flush_rules();

if ( is_multisite() ) {
	global $wpdb;
	$blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A );
	if ( $blogs ) {
		foreach ( (array) $blogs as $blog ) {
			switch_to_blog( $blog['blog_id'] );
			delete_option( 'custom_post_type_date_archives' );
		}
		restore_current_blog();
	}
} else {
	delete_option( 'custom_post_type_date_archives' );
}
