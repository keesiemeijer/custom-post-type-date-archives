<?php
/**
 * Deprecated functions.
 *
 * @package     Custom Post Type Date Archives
 * @subpackage  Functions/Deprecated
 * @copyright   Copyright (c) 2014, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.5.0
 */

/**
 * Returns public post types that have archives and are displayed in the admin menu.
 *
 * @since 2.1.0
 * @since 2.5.0 Deprecated.
 *
 * @param string $type Return type 'names' or 'objects'.
 * @return array Array with post types.
 */
function cptda_get_admin_post_types( $type = 'names' ) {
	_deprecated_function( __FUNCTION__, '2.5.0', 'cptda_get_post_types' );

	// Type `names` is `labels` in cptda_get_post_types.
	$type = ( 'names' === $type ) ? 'labels' : $type;

	return cptda_get_post_types( $type, 'admin' );
}

/**
 * Get the current date archive custom post type name.
 *
 * @since 1.0
 * @since 2.5.0 Deprecated.
 *
 * @return string Post type name if used in a custom post type date archive. Else empty string.
 */
function cptda_get_date_archive_cpt() {

	_deprecated_function( __FUNCTION__, '2.5.0', 'cptda_get_queried_date_archive_post_type' );

	return cptda_get_queried_date_archive_post_type();
}
