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

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Includes the post status 'future' for post types that support it.
 *
 * @since 1.1
 * @return void
 */
function cptda_pre_get_posts( $query ) {

	if ( !is_admin() && $query->is_main_query() && cptda_is_cpt_date() ) {
		$post_type = $query->get( 'post_type' );
		$query->set( 'post_status', cptda_get_cpt_date_archive_stati( $post_type ) );
	}
}

add_action( 'pre_get_posts', 'cptda_pre_get_posts' );