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
		'title'         => '',
		'before_title'  => '',
		'after_title'   => '',
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
 * @since 2.6.2
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

	$args['title']        = strip_tags( trim( (string) $args['title'] ) );
	$args['before_title'] = trim( (string) $args['before_title'] ) ;
	$args['after_title']  = trim( (string) $args['after_title'] );
	$args['message']      = wp_kses_post( (string) $args['message'] );
	$args['number']       = absint( $args['number'] );
	$args['show_date']    = wp_validate_boolean( $args['show_date'] );
	$args['include']      = strip_tags( trim( (string) $args['include'] ) );
	$args['post_type']    = sanitize_key( (string) $args['post_type'] );

	return $args;
}

/**
 * Validates recent posts settings.
 *
 * @since  2.6.2
 *
 * @param array $args Recent posts arguments.
 * @return array Validated recent posts arguments.
 */
function cptda_validate_recent_posts_settings( $args ) {
	$plugin = cptda_date_archives();
	$args   = cptda_sanitize_recent_posts_settings( $args );

	$args['number'] = $args['number'] ? $args['number'] : 5;

	$types = cptda_get_recent_posts_date_query_types();
	if ( ! in_array( $args['include'], array_keys( $types ) ) ) {
		$args['include'] = 'all';
	}

	$post_types   = $plugin->post_type->get_post_types( 'names' );
	$post_types[] = 'post';
	if ( ! in_array( $args['post_type'], $post_types ) ) {
		$args['post_type'] = 'post';
	}

	return $args;
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
	$args     = cptda_validate_recent_posts_settings( $args );
	$message  = $args['message'] ? apply_filters( 'the_content', $args['message'] ) : '';
	$class    = isset( $args['class'] ) ? $args['class'] : '';
	$is_block = false;
	$html     = '';

	$title = '';
	if ( $args['title'] ) {
		$title = $args['before_title'] . $args['title'] . $args['after_title'];
	}

	$no_posts_found = $message ? $title . $message : '';

	if ( $class && ( 'wp-block-latest-posts' === $class ) ) {
		$is_block = true;
		$title    = '';

		// Add extra classes from the editor block
		$class = esc_attr( cptda_get_block_classes( $args, $class ) );
		$class .= ' cptda-block-latest-posts';
		$class .= $args['show_date'] ? ' has-dates' : '';

		$no_posts_found = $message ? "<div class=\"{$class}\">\n{$message}\n</div>\n" : '';
	}

	if ( ! $recent_posts ) {
		return $no_posts_found;
	}

	ob_start();
	if($is_block) {
		include CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/partials/latest-posts-block-display.php';
	} else {
		include CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/partials/recent-posts-display.php';
	}
	$recent_posts_html = ob_get_clean();
	$recent_posts_html = trim($recent_posts_html);

	if ( ! $recent_posts_html ) {
		return $no_posts_found;
	}

	$title = $title ? "{$title}\n" : '';
	$class = $is_block ? " class=\"{$class}\"" : '';
	return "{$title}<ul{$class}>\n{$recent_posts_html}\n</ul>";
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
