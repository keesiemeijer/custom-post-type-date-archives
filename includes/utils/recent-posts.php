<?php
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
 * Returns calendar HTML.
 *
 * @since 2.5.2
 * @access public
 *
 * @param array  $args    Arguments used to get the calendar.
 * @param string $feature The feature that called this function.
 * @return string Recent Posts HTML.
 */
function cptda_get_recent_posts( $args, $feature = '' ) {
	$defaults = cptda_get_recent_posts_settings();
	$args = array_merge( $defaults, $args );

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

	if ( 'widget' === $feature ) {
		$args = apply_filters( 'widget_posts_args', $query_args, $args );
	}

	$posts = get_posts( $query_args );

	return $posts;
}
