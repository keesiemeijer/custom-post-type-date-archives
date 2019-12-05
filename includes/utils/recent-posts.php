<?php
/**
 * Recent Posts Utils
 *
 * @package     Custom_Post_Type_Date_Archives
 * @subpackage  Utils/Recent_Posts
 * @copyright   Copyright (c) 2017, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.6.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the default settings for the recent posts feature.
 *
 * @since 2.6.0
 *
 * @return array Default recent posts settings.
 */
function cptda_get_recent_posts_settings() {
	return array(
		'message'       => '',
		'number'        => 5,
		'show_date'     => false,
		'include'       => 'all',
		'post_type'     => 'post',
	);
}

/**
 * Get date query types
 *
 * @since 2.7.0
 *
 * @return array Array with all date query types and their labels.
 */
function cptda_get_recent_posts_date_query_types() {
	return array(
		'all'    => __( 'all posts', 'custom-post-type-date-archives' ),
		'future' => __( 'posts with future dates only', 'custom-post-type-date-archives' ),
		'year'   => __( 'posts from the current year', 'custom-post-type-date-archives' ),
		'month'  => __( 'posts from the current month', 'custom-post-type-date-archives' ),
		'day'    => __( 'posts from today', 'custom-post-type-date-archives' ),
	);
}

/**
 * Sanitize recent posts feature settings.
 *
 * @since 2.6.0
 *
 * @param array $args Array with recent posts settings.
 * @return array Array with sanitized recent post settings.
 */
function cptda_sanitize_recent_posts_settings( $args ) {
	$defaults = cptda_get_recent_posts_settings();
	$args     = array_merge( $defaults, $args );

	$args['message']   = wp_kses_post( (string) $args['message'] );
	$args['number']    = absint( $args['number'] );
	$args['show_date'] = wp_validate_boolean( $args['show_date'] );
	$args['include']   = strip_tags( trim( (string) $args['include'] ) );
	$args['post_type'] = sanitize_key( strip_tags( (string) $args['post_type'] ) );

	return $args;
}

/**
 * Validates recent posts settings.
 *
 * @since 2.7.0
 *
 * @param array $args Recent posts arguments.
 * @return array Validated recent posts arguments.
 */
function cptda_validate_recent_posts_settings( $args ) {
	$args   = cptda_sanitize_recent_posts_settings( $args );

	$args['number'] = $args['number'] ? $args['number'] : 5;

	$types = cptda_get_recent_posts_date_query_types();
	if ( ! in_array( $args['include'], array_keys( $types ) ) ) {
		$args['include'] = 'all';
	}

	return $args;
}

/**
 * Get recent posts.
 *
 * @since 2.7.0
 *
 * @param array $args Query args for get_posts().
 * @return array Array with post objects.
 */
function cptda_get_recent_posts( $args ) {
	if ( ! isset( $args['post_type'] ) ) {
		return array();
	}

	$post_types = cptda_get_public_post_types();
	if ( in_array( $args['post_type'], array_keys( $post_types ) ) ) {
		return get_posts( $args );
	}

	return array();
}

/**
 * Get te recent posts feature HTML.
 *
 * @since 2.6.0
 *
 * @param array $recent_posts Array with post IDs.
 * @param array $args         Recent posts arguments
 * @return string Recent posts HTML.
 */
function cptda_get_recent_posts_html( $recent_posts, $args ) {
	$is_block = false;
	$html     = '';
	$args     = cptda_validate_recent_posts_settings( $args );
	$class    = isset( $args['class'] ) ? $args['class'] : '';
	$class    = sanitize_html_class( $class );

	/** This filter is documented in wp-includes/post_template.php */
	$message  = $args['message'] ? apply_filters( 'the_content', $args['message'] ) : '';

	if ( $class && ( 'wp-block-latest-posts' === $class ) ) {
		$is_block = true;
		$block_class = 'cptda-block-latest-posts';

		// Add extra classes from the editor block
		$class = cptda_get_block_classes( $args, $class );
		$class .= " wp-block-latest-posts__list {$block_class}";
		$class .= $args['show_date'] ? ' has-dates' : '';

		$no_posts_class = "{$block_class} cptda-no-posts";
		$message = $message ? "<div class=\"{$no_posts_class}\">\n{$message}\n</div>\n" : '';
	}

	if ( empty( $recent_posts ) ) {
		return $message;
	}

	ob_start();
	if ( $is_block ) {
		include CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/partials/latest-posts-block-display.php';
	} else {
		include CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/partials/recent-posts-display.php';
	}
	$recent_posts_html = ob_get_clean();
	$recent_posts_html = trim( $recent_posts_html );

	if ( ! $recent_posts_html ) {
		return $message;
	}

	$class = $is_block ? " class=\"{$class}\"" : '';
	return "<ul{$class}>\n{$recent_posts_html}\n</ul>";
}

/**
 * Create the recent posts query used by the widget and rest API.
 *
 * @since 2.6.0
 *
 * @param array $args Arguments used for the recent posts feature.
 * @return array Recent posts query.
 */
function cptda_get_recent_posts_query( $args ) {
	$args = cptda_validate_recent_posts_settings( $args );

	$query_args = array(
		'post_type'           => $args['post_type'],
		'fields'              => 'ids',
		'post_status'         => cptda_get_cpt_date_archive_stati( $args['post_type'] ),
		'posts_per_page'      => $args['number'],
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
		'cptda_recent_posts'  => true, // To check if it's a query from this plugin
	);

	$paged = isset( $args['page'] ) ? absint( $args['page'] ) : '';
	$paged = ( 1 < $paged ) ? $paged : '';
	if ( $paged ) {
		$query_args['offset'] = ( ( $paged - 1 ) * $args['number'] );
	}

	$today = getdate();
	$date  = array(
		'year'  => $today['year'],
		'month' => $today['mon'],
		'day'   => $today['mday'],
	);

	$date_query = array();
	$include    = $args['include'] ? $args['include'] : 'all';

	switch ( $include ) {
		case 'future':
			$date_query = array( 'after' => $date );
			break;
		case 'year':
			unset( $date['month'], $date['day'] );
			$date_query = array( $date );
			break;
		case 'month':
			unset( $date['day'] );
			$date_query = array( $date );
			break;
		case 'day':
			$date_query = array( $date );
			break;
	}

	if ( ( 'all' !== $include ) && $date_query ) {
		$query_args['date_query']  = array( $date_query );
	}

	return $query_args;
}
