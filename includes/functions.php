<?php
/**
 * Functions
 *
 * @package     Custom_Post_Type_Date_Archives
 * @subpackage  Functions
 * @copyright   Copyright (c) 2014, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns custom post types depending on format and context.
 *
 * Use context 'date_archive' to get custom post types that have date archives support (Default).
 * Use context 'admin' to get custom post types that are registered to appear in the admin menu.
 * Use context 'publish_future' to get custom post types that publish future posts.
 *
 * @since 2.5.0
 * @param string $format  Accepts 'names', 'labels' or 'objects' Default 'names'.
 * @param string $context Accepts 'date_archive', 'admin' and 'publish_future'. Default 'date_archive'.
 *
 * @return array|object Array with post types depending on format and context.
 */
function cptda_get_post_types( $format = 'names', $context = 'date_archive' ) {
	$instance = cptda_date_archives();
	return $instance->post_type->get_post_types( $format, $context );
}


/**
 * Checks if a custom post type supports date archives.
 *
 * @param string $post_type Custom post type name.
 * @return bool True when the custom post type supports date archives.
 */
function cptda_is_date_post_type( $post_type = '' ) {
	if ( in_array( (string) $post_type, cptda_get_post_types( 'names' ) ) ) {
		return cptda_is_valid_post_type( $post_type );
	}

	return false;
}

/**
 * Check if a custom post type can support date archives.
 *
 * Does not check if the post type has support for date archives.
 * Use cptda_is_date_post_type() to check if a post type supports date archives.
 *
 * @since 2.3.0
 * @param string $post_type Post type name.
 * @return boolean True if it's a valid post type.
 */
function cptda_is_valid_post_type( $post_type ) {
	$post_type = get_post_type_object( trim( (string) $post_type ) );

	if ( ! $post_type ) {
		return false;
	}

	$args = array(
		'public'             => true,
		'publicly_queryable' => true,
		'has_archive'        => true,
		'_builtin'           => false,
	);

	$valid = wp_list_filter( array( $post_type ), $args, 'AND' );
	return ! empty( $valid );
}

/**
 * Is the query for a custom post type date archive?
 *
 * @see WP_Query::is_date()
 * @since 1.0
 * @return bool True on custom post type date archives.
 */
function cptda_is_cpt_date() {
	if ( is_date() && is_post_type_archive() ) {

		$post_type = get_query_var( 'post_type' );
		if ( is_array( $post_type ) ) {
			$post_type = reset( $post_type );
		}

		return cptda_is_date_post_type( $post_type );
	}

	return false;
}

/**
 * Get the queried date archive custom post type name.
 *
 * @since 2.5.0
 * @return string Post type name if the current query is for a custom post type date archive. Else empty string.
 */
function cptda_get_queried_date_archive_post_type() {
	if ( cptda_is_cpt_date() ) {
		$post_type = get_query_var( 'post_type' );
		if ( is_array( $post_type ) ) {
			$post_type = reset( $post_type );
		}
		return $post_type;
	}

	return '';
}

/**
 * Get custom post type date archive post stati for a specific post type.
 *
 * Used in the query (and widgets) for a custom post type date archive.
 * The post stati can be filtered with the 'cptda_post_stati' filter.
 *
 * @since 1.1
 * @param string $post_type Post type.
 * @return array Array with post stati for the post type. Default array( 'publish' ).
 */
function cptda_get_cpt_date_archive_stati( $post_type = '' ) {
	$post_status = array( 'publish' );

	if ( empty( $post_type ) || ! cptda_is_date_post_type( $post_type ) ) {
		return $post_status;
	}

	/**
	 * Filter post stati for a custom post type with date archives
	 *
	 * @since 1.1
	 * @param array $post_status Array with post stati for a custom post type with date archives
	 */
	$stati = apply_filters( 'cptda_post_stati', $post_status, $post_type );

	return is_array( $stati ) && ! empty( $stati ) ? $stati : $post_status;
}

/**
 * Gets the post type base slug.
 *
 * @since 2.3.0
 * @param string $post_type Post type.
 * @return string Post type base (front + slug).
 */
function cptda_get_post_type_base( $post_type = '' ) {
	if ( ! cptda_is_date_post_type( $post_type ) ) {
		return '';
	}

	$rewrite = new CPTDA_CPT_Rewrite( $post_type );
	return $rewrite->get_base_permastruct();
}

/**
 * Get all public post types including post type 'post'.
 *
 * Note: the 'attachment' and 'page' post type are not included.
 *
 * @since 2.7.0
 *
 * @return array Array with public post types.
 */
function cptda_get_public_post_types() {
	$args = array(
		'public'             => true,
		'publicly_queryable' => true, // excludes pages
	);

	$post_types = get_post_types( $args, 'objects', 'and' );

	foreach ( (array) $post_types as $key => $value ) {
		$post_types[ $key ] = esc_attr( $value->labels->menu_name );
	}

	unset( $post_types['attachment'] );

	return $post_types;
}

/**
 * Get classes for the editor blocks.
 *
 * @since 2.7.0
 *
 * @param array  $args    Block arguments.
 * @param string $default Default block class.
 * @return string String with block class names.
 */
function cptda_get_block_classes( $args, $default = '' ) {
	$class = '';
	if ( isset( $args['align'] ) && $args['align'] ) {
		$class .= " align{$args['align']}";
	}

	if ( isset( $args['className'] ) && $args['className'] ) {
		$class .= " {$args['className']}";
	}

	$default = sanitize_html_class( $default );
	return esc_attr( trim( $default . $class ) );
}
