<?php
/**
 * Calendar Utils
 *
 * @package     Custom_Post_Type_Date_Archives
 * @subpackage  Utils/Calendar
 * @copyright   Copyright (c) 2017, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.6.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Get calendar HTML
 *
 * Wraps the calendar inside a div for editor block calendars.
 *
 * @since 2.7.0
 *
 * @param array $args Calendar arguments.
 * @return string Calendar HTML.
 */
function cptda_get_calendar_html( $args ) {
	if ( ! isset( $args['post_type'] ) ) {
		return '';
	}

	$calendar = cptda_get_calendar( $args['post_type'], true, false );
	$calendar = is_string( $calendar ) ? trim( $calendar ) : '';

	if ( empty( $calendar ) ) {
		return '';
	}

	$class = isset( $args['class'] ) ? $args['class'] : '';
	$class = sanitize_html_class( $class );

	if ( $class && ( 'wp-block-calendar' === $class ) ) {
		// Add extra classes from the editor block
		$class = cptda_get_block_classes( $args, $class );
		$class .= ' cptda-block-calendar';

		$calendar = sprintf( '<div class="%1$s">%2$s</div>', esc_attr( $class ), $calendar );
	}

	return $calendar;
}

/**
 * Get the current calendar date.
 *
 * @since 2.6.0
 *
 * @return array Array with date attributes.
 */
function cptda_get_calendar_date() {
	global $m, $monthnum, $year, $wpdb;

	if ( isset( $_GET['w'] ) ) {
		$w = (int) $_GET['w'];
	}

	$ts = current_time( 'timestamp' );

	// Let's figure out when we are
	if ( ! empty( $monthnum ) && ! empty( $year ) ) {
		$thismonth = (int) $monthnum;
		$thisyear = (int) $year;
	} elseif ( ! empty( $w ) ) {
		// We need to get the month from MySQL
		$thisyear = (int) substr( $m, 0, 4 );
		//it seems MySQL's weeks disagree with PHP's
		$d = ( ( $w - 1 ) * 7 ) + 6;
		$thismonth = (int) $wpdb->get_var( "SELECT DATE_FORMAT((DATE_ADD('{$thisyear}0101', INTERVAL $d DAY) ), '%m')" );
	} elseif ( ! empty( $m ) ) {
		$thisyear = (int) substr( $m, 0, 4 );
		if ( strlen( $m ) < 6 ) {
			$thismonth = 1;
		} else {
			$thismonth = (int) substr( $m, 4, 2 );
		}
	} else {
		$thisyear = (int) gmdate( 'Y', $ts );
		$thismonth = (int) gmdate( 'm', $ts );
	}

	$unixmonth = (int) mktime( 0, 0 , 0, $thismonth, 1, $thisyear );
	$last_day = (int) date( 't', $unixmonth );

	return array(
		'year'      => $thisyear,
		'month'     => $thismonth,
		'last_day'  => $last_day,
		'unixmonth' => $unixmonth,
		'timestamp' => $ts,
	);
}

/**
 * Get the SQL for a post type.
 *
 * @since 2.6.0
 *
 * @param string $post_type Post type.
 * @return string SQL for the post type
 */
function cptda_get_calendar_post_type_sql( $post_type ) {
	$types   = cptda_get_post_types();
	$types[] = 'post';

	if ( ! $post_type || ! in_array( $post_type, $types ) ) {
		return '';
	}

	$post_status         = cptda_get_cpt_date_archive_stati( $post_type );
	$post_status_escaped = array_map( 'esc_sql', (array) $post_status );
	$post_status_sql     = "post_status IN ('" . implode( "', '", $post_status_escaped ) . "')";

	$post_type_escaped = esc_sql( $post_type );
	return "post_type = '{$post_type_escaped}' AND {$post_status_sql}";
}

/**
 * Gets the date for an adjacent archive date
 *
 * @since  2.6.0
 *
 * @param string $post_type     Post type.
 * @param array  $calendar_date Array with date attributes. See cptda_get_calendar_date();
 * @param string $type          Previous or next archive date. Accepts 'previous' or 'next'.
 * @return array Array with previous or next year and month.
 */
function cptda_get_adjacent_archive_date( $post_type, $calendar_date, $type = 'previous' ) {
	global $wpdb;

	$post_type_sql = cptda_get_calendar_post_type_sql( $post_type );
	$year          = isset( $calendar_date['year'] ) ? absint( $calendar_date['year'] ) : 0;
	$month         = isset( $calendar_date['month'] ) ? absint( $calendar_date['month'] ) : 0;

	$date  = array(
		'year'  => '',
		'month' => '',
	);

	if ( ! ( $post_type_sql && $month && $year ) ) {
		return $date;
	}

	$month    = zeroise( $month, 2 );
	$order    = 'DESC';
	$date_sql = "post_date < '$year-$month-01'";

	if ( isset( $calendar_date['last_day'] ) && ( 'next' === $type ) ) {
		$order    = 'ASC';
		$last_day = absint( $calendar_date['last_day'] );
		$last_day = $last_day ? zeroise( $last_day, 2 ) : '';
		$date_sql = $last_day ? "post_date > '$year-$month-{$last_day} 23:59:59'" : '';
	}

	if ( ! $date_sql ) {
		return $date;
	}

	// previous month and year with at least one post
	$date_obj = $wpdb->get_row( "SELECT MONTH(post_date) AS month, YEAR(post_date) AS year
		FROM $wpdb->posts
		WHERE {$date_sql}
		AND {$post_type_sql}
		ORDER BY post_date {$order}
		LIMIT 1" );
	$date['year']  = isset( $date_obj->year ) ? (int) $date_obj->year : '';
	$date['month'] = isset( $date_obj->month ) ? (int) $date_obj->month : '';

	return $date;
}
