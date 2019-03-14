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
 * Get te recent posts feature HTML.
 *
 * @since 2.6.0
 *
 * @param array $recent_posts Array with post IDs.
 * @param array $args         Recent posts arguments
 * @return string Recent posts HTML.
 */
function cptda_get_recent_posts_html( $recent_posts, $args ) {
	$defaults = cptda_get_recent_posts_settings();
	$args     = array_merge( $defaults, $args );
	$message  = $args['message'] ? apply_filters( 'the_content', $args['message'] ) : '';
	$html     = '';
	$title    = '';

	if ( $args['title'] ) {
		$title = $args['before_title'] . $args['title'] . $args['after_title'];
	}

	if ( ! $recent_posts ) {
		return $message ? $title . $message : '';
	}

	ob_start();
	include CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/partials/recent-posts-display.php';
	$recent_posts_html = ob_get_clean();

	if ( ! $recent_posts_html ) {
		return $message ? $title . $message : '';
	}

	return "{$title}\n<ul>\n{$recent_posts_html}</ul>";
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
	$defaults = cptda_get_recent_posts_settings();
	$args     = array_merge( $defaults, $args );

	// Default recent posts is 5
	$number = $args['number'] ? $args['number'] : 5;

	$query_args = array(
		'post_type'           => $args['post_type'],
		'fields'              => 'ids',
		'post_status'         => cptda_get_cpt_date_archive_stati( $args['post_type'] ),
		'posts_per_page'      => $number,
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
		'cptda_recent_posts'  => true, // To check if it's a query from this plugin
	);

	$paged = isset( $args['page'] ) ? absint( $args['page'] ) : '';
	$paged = ( 1 < $paged ) ? $paged : '';
	if ( $paged ) {
		$query_args['offset'] = ( ( $paged - 1 ) * $number );
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
