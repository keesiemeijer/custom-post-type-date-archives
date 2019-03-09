<?php
/**
 * Calendar
 *
 * Customized core WordPress get_calendar() function.
 *
 * @package     Custom Post Type Date Archives
 * @subpackage  Functions/Calendar
 * @copyright   Copyright (c) 2017, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.5.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the current calendar date.
 *
 * @since 2.5.2
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
		'year'          => $thisyear,
		'month'         => $thismonth,
		'last_day'      => $last_day,
		'unixmonth'     => $unixmonth,
		'timestamp'     => $ts,
	);
}

/**
 * Get the SQL for a post type.
 *
 * @since 2.5.2
 *
 * @param string $post_type Post type.
 * @return string SQL for the post type
 */
function cptda_get_calendar_post_type_sql( $post_type ) {
	if ( empty( $post_type ) || ! cptda_is_date_post_type( $post_type ) ) {
		return '';
	}

	$post_status = cptda_get_cpt_date_archive_stati( $post_type );
	if ( ! ( is_array( $post_status ) && $post_status ) ) {
		$post_status = array( 'publish' );
	}

	$post_status_escaped = array_map( 'esc_sql', $post_status );
	$post_status_sql     = "post_status IN ('" . implode( "', '", $post_status_escaped ) . "')";

	$post_type_escaped = esc_sql( $post_type );
	return "post_type = '{$post_type_escaped}' AND {$post_status_sql}";
}

/**
 * Gets the date for an adjacent archive date
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

	$month     = zeroise( $month, 2 );
	$order     = 'DESC';
	$date_sql  = "post_date < '$year-$month-01'";

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

/**
 * Display a calendar with days that have posts as links.
 *
 * Copied from the WordPress function get_calendar().
 *
 * Use the extra `post_type` parameter to display a calendar for a custom post type.
 *
 * The calendar is cached, which will be retrieved, if it exists. If there are
 * no posts for the month, then it will not be displayed.
 *
 * @since 1.0.0
 * @see get_calendar()
 *
 * @global wpdb      $wpdb
 * @global int       $m
 * @global int       $monthnum
 * @global int       $year
 * @global WP_Locale $wp_locale
 * @global array     $posts
 *
 * @param string $post_type Post type.
 * @param bool   $initial   Optional, default is true. Use initial calendar names.
 * @param bool   $echo      Optional, default is true. Set to false for return.
 * @return string|void String when retrieving.
 */
function cptda_get_calendar( $post_type, $initial = true, $echo = true ) {
	global $wpdb, $m, $monthnum, $year, $wp_locale, $posts;

	$post_type_sql = cptda_get_calendar_post_type_sql( $post_type );

	if ( empty( $post_type ) || empty( $post_type_sql ) ) {
		return '';
	}

	$key           = md5( $post_type . $m . $monthnum . $year );
	$cache         = wp_cache_get( 'cptda_get_calendar', 'calendar' );
	$is_cache      = $cache && is_array( $cache ) && isset( $cache[ $key ] );
	$cache_data    = wp_cache_get( 'cptda_get_calendar_data', 'calendar_data' );
	$is_cache_data = $cache_data && is_array( $cache_data ) && isset( $cache_data[ $key ] );

	if ( $is_cache && $is_cache_data ) {
		/** This filter is documented in includes/calendar.php */
		$output = apply_filters( 'cptda_get_calendar', $cache[ $key ], $cache_data[ $key ] );

		if ( $echo ) {
			echo $output;
			return;
		}

		return $output;
	}

	if ( ! is_array( $cache ) || ! is_array( $cache_data ) ) {
		$cache = array();
		$cache_data = array();
	}

	// Quick check. If we have no posts at all, abort!
	if ( ! $posts ) {
		$gotsome = $wpdb->get_var( "SELECT 1 as test FROM $wpdb->posts WHERE {$post_type_sql} LIMIT 1" );
		if ( ! $gotsome ) {
			$cache[ $key ] = '';
			wp_cache_set( 'cptda_get_calendar', $cache, 'calendar' );
			$cache_data[ $key ] = '';
			wp_cache_set( 'cptda_get_calendar_data', $cache_data, 'calendar_data' );
			return;
		}
	}

	// week_begins = 0 stands for Sunday
	$week_begins = (int) get_option( 'start_of_week' );

	$default_data = array (
		'next_year'     => '',
		'prev_year'     => '',
		'next_month'    => '',
		'prev_month'    => '',
		'calendar_days' => array(),
	);

	$calendar_date = cptda_get_calendar_date();
	$calendar_date = array_merge( $calendar_date, $default_data );

	/**
	 * Filter calendar data for the current date.
	 *
	 * This filter is called before the database query.
	 *
	 * @since 2.5.2
	 *
	 * @param array  $calendar_data Array with calendar data.
	 * @param string $post_type     Post type
	 */
	$calendar_data = apply_filters( 'cptda_calendar_data', $calendar_date, $post_type );
	$calendar_data = array_merge( $calendar_date, $calendar_data );


	if ( ( '' === $calendar_data['prev_year'] ) && ( '' === $calendar_data['prev_month'] ) ) {
		$prev       = cptda_get_adjacent_archive_date( $post_type, $calendar_data );
		$prev_year  = isset( $prev['year'] ) ? $prev['year'] : '';
		$prev_month = isset( $prev['month'] ) ? $prev['month'] : '';
	} else {
		$prev_year = absint( $calendar_data['prev_year'] );
		$prev_month = absint( $calendar_data['prev_month'] );
	}

	if ( ( '' === $calendar_data['next_year'] ) && ( '' === $calendar_data['next_month'] ) ) {
		$next       = cptda_get_adjacent_archive_date( $post_type, $calendar_data, 'next' );
		$next_year  = isset( $next['year'] ) ? $next['year'] : '';
		$next_month = isset( $next['month'] ) ? $next['month'] : '';
	} else {
		$next_year = absint( $calendar_data['next_year'] );
		$next_month = absint( $calendar_data['next_month'] );
	}

	$calendar_data['next_year'] = $next_year;
	$calendar_data['next_month'] = $next_month;
	$calendar_data['prev_year'] = $prev_year;
	$calendar_data['prev_month'] = $prev_month;

	$thisyear = $calendar_data['year'];
	$thismonth = zeroise( absint( $calendar_data['month'] ), 2 );
	$last_day = $calendar_data['last_day'];

	/* translators: Calendar caption: 1: month name, 2: 4-digit year */
	$calendar_caption = _x( '%1$s %2$s', 'calendar caption' );
	$calendar_output = '<table id="wp-calendar">
	<caption>' . sprintf(
		$calendar_caption,
		$wp_locale->get_month( $thismonth ),
		date( 'Y', $calendar_data['unixmonth'] )
	) . '</caption>
	<thead>
	<tr>';

	$myweek = array();

	for ( $wdcount = 0; $wdcount <= 6; $wdcount++ ) {
		$myweek[] = $wp_locale->get_weekday( ( $wdcount + $week_begins ) % 7 );
	}

	foreach ( $myweek as $wd ) {
		$day_name = $initial ? $wp_locale->get_weekday_initial( $wd ) : $wp_locale->get_weekday_abbrev( $wd );
		$wd = esc_attr( $wd );
		$calendar_output .= "\n\t\t<th scope=\"col\" title=\"$wd\">$day_name</th>";
	}

	$calendar_output .= '
	</tr>
	</thead>

	<tfoot>
	<tr>';

	if ( $prev_year && $prev_month ) {
		$calendar_output .= "\n\t\t" . '<td colspan="3" id="prev"><a href="' . cptda_get_month_link( $prev_year, $prev_month, $post_type ) . '">&laquo; ' .
			$wp_locale->get_month_abbrev( $wp_locale->get_month( $prev_month ) ) .
			'</a></td>';
	} else {
		$calendar_output .= "\n\t\t" . '<td colspan="3" id="prev" class="pad">&nbsp;</td>';
	}

	$calendar_output .= "\n\t\t" . '<td class="pad">&nbsp;</td>';

	if ( $next_year && $next_month ) {
		$calendar_output .= "\n\t\t" . '<td colspan="3" id="next"><a href="' . cptda_get_month_link( $next_year, $next_month, $post_type ) . '">' .
			$wp_locale->get_month_abbrev( $wp_locale->get_month( $next_month ) ) .
			' &raquo;</a></td>';
	} else {
		$calendar_output .= "\n\t\t" . '<td colspan="3" id="next" class="pad">&nbsp;</td>';
	}

	$calendar_output .= '
	</tr>
	</tfoot>

	<tbody>
	<tr>';

	$daywithpost = array();
	if ( is_array( $calendar_data['calendar_days'] ) && empty( $calendar_data['calendar_days'] ) ) {
		// Get days with posts
		$dayswithposts = $wpdb->get_results( "SELECT DISTINCT DAYOFMONTH(post_date)
			FROM $wpdb->posts WHERE post_date >= '{$thisyear}-{$thismonth}-01 00:00:00'
			AND {$post_type_sql}
			AND post_date <= '{$thisyear}-{$thismonth}-{$last_day} 23:59:59'", ARRAY_N );
		if ( $dayswithposts ) {
			foreach ( (array) $dayswithposts as $daywith ) {
				$daywithpost[] = $daywith[0];
			}
		}
	} else {
		$daywithpost = is_array( $calendar_data['calendar_days'] ) ? $calendar_data['calendar_days'] : array();
	}

	$daywithpost = array_unique( array_map( 'intval', $daywithpost ) );
	$calendar_data['calendar_days'] = $daywithpost;

	// See how much we should pad in the beginning
	$pad = calendar_week_mod( date( 'w', $calendar_data['unixmonth'] ) - $week_begins );
	if ( 0 != $pad ) {
		$calendar_output .= "\n\t\t" . '<td colspan="' . esc_attr( $pad ) . '" class="pad">&nbsp;</td>';
	}

	$newrow = false;
	$daysinmonth = (int) date( 't', $calendar_data['unixmonth'] );

	for ( $day = 1; $day <= $daysinmonth; ++$day ) {
		if ( isset( $newrow ) && $newrow ) {
			$calendar_output .= "\n\t</tr>\n\t<tr>\n\t\t";
		}
		$newrow = false;

		if ( $day == gmdate( 'j', $calendar_data['timestamp'] ) &&
			$thismonth == gmdate( 'm', $calendar_data['timestamp'] ) &&
			$thisyear == gmdate( 'Y', $calendar_data['timestamp'] ) ) {
			$calendar_output .= '<td id="today">';
		} else {
			$calendar_output .= '<td>';
		}

		if ( in_array( $day, $daywithpost ) ) {
			// any posts today?
			$date_format = date( _x( 'F j, Y', 'daily archives date format' ), strtotime( "{$thisyear}-{$thismonth}-{$day}" ) );
			$label = sprintf( __( 'Posts published on %s' ), $date_format );
			$calendar_output .= sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				cptda_get_day_link( $thisyear, $thismonth, $day, $post_type ),
				esc_attr( $label ),
				$day
			);
		} else {
			$calendar_output .= $day;
		}
		$calendar_output .= '</td>';

		if ( 6 == calendar_week_mod( date( 'w', mktime( 0, 0 , 0, $thismonth, $day, $thisyear ) ) - $week_begins ) ) {
			$newrow = true;
		}
	}

	$pad = 7 - calendar_week_mod( date( 'w', mktime( 0, 0 , 0, $thismonth, $day, $thisyear ) ) - $week_begins );
	if ( $pad != 0 && $pad != 7 ) {
		$calendar_output .= "\n\t\t" . '<td class="pad" colspan="' . esc_attr( $pad ) . '">&nbsp;</td>';
	}
	$calendar_output .= "\n\t</tr>\n\t</tbody>\n\t</table>";

	$cache[ $key ] = $calendar_output;
	wp_cache_set( 'cptda_get_calendar', $cache, 'calendar' );

	$cache_data[ $key ] = $calendar_data;
	wp_cache_set( 'cptda_get_calendar_data', $cache_data, 'calendar_data' );

	if ( $echo ) {
		/**
		 * Filter the HTML calendar output.
		 *
		 * @since 2.5.2
		 *
		 * @param string $calendar_output HTML output of the calendar.
		 * @param array  $calendar_data   Array with arguments for the current calendar.
		 */
		echo apply_filters( 'cptda_get_calendar', $calendar_output, $calendar_data );
		return;
	}
	/** This filter is documented in includes/calendar.php */
	return apply_filters( 'cptda_get_calendar', $calendar_output, $calendar_data );
}
