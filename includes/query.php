<?php
/**
 * Query
 *
 * @package     Custom Post Type Date Archives
 * @subpackage  Functions/Query
 * @copyright   Copyright (c) 2014, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'pre_get_posts', 'cptda_pre_get_posts', 9 );

/**
 * Include the post status 'future' for post types that support it.
 *
 * @since 1.1
 * @param WP_Query $query The WP_Query instance (passed by reference).
 */
function cptda_pre_get_posts( $query ) {

	if ( ! is_admin() && $query->is_main_query() && cptda_is_cpt_date() ) {
		$post_type = $query->get( 'post_type' );
		$query->set( 'post_status', cptda_get_cpt_date_archive_stati( $post_type ) );
	}
}


add_action( 'wp', 'cptda_handle_404' );

/**
 * Set 404 if no posts are found on a custom post type date archive
 *
 * @since 2.0.0
 * @return void
 */
function cptda_handle_404() {
	global $wp_query;

	if ( ! is_admin() && ! is_paged() && cptda_is_cpt_date() && ! $wp_query->posts  ) {
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
}
