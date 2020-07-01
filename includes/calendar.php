<?php
/**
 * Calendar
 *
 * Customized core WordPress get_calendar() function.
 *
 * @package     Custom_Post_Type_Date_Archives
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
		$output = is_string( $output ) ? $output : '';

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
	$gotsome = $wpdb->get_var( "SELECT 1 as test FROM $wpdb->posts WHERE {$post_type_sql} LIMIT 1" );
	if ( ! $gotsome ) {
		$cache[ $key ] = '';
		wp_cache_set( 'cptda_get_calendar', $cache, 'calendar' );
		$cache_data[ $key ] = '';
		wp_cache_set( 'cptda_get_calendar_data', $cache_data, 'calendar_data' );
		return '';
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

	$date = cptda_get_calendar_date();
	$date = array_merge( $date, $default_data );

	/**
	 * Filter calendar data for the current date.
	 *
	 * This filter is called before the database query.
	 *
	 * @since 2.6.0
	 *
	 * @param array  $date      Array with calendar data.
	 * @param string $post_type Post type
	 */
	$calendar = apply_filters( 'cptda_calendar_data', $date, $post_type );
	$calendar = array_merge( $date, $calendar );

	if ( ( '' === $calendar['prev_year'] ) && ( '' === $calendar['prev_month'] ) ) {
		$prev       = cptda_get_adjacent_archive_date( $post_type, $calendar );
		$prev_year  = absint( $prev['year'] );
		$prev_month = absint( $prev['month'] );
	} else {
		$prev_year  = absint( $calendar['prev_year'] );
		$prev_month = absint( $calendar['prev_month'] );
	}

	if ( ( '' === $calendar['next_year'] ) && ( '' === $calendar['next_month'] ) ) {
		$next       = cptda_get_adjacent_archive_date( $post_type, $calendar, 'next' );
		$next_year  = absint( $next['year'] );
		$next_month = absint( $next['month'] );
	} else {
		$next_year  = absint( $calendar['next_year'] );
		$next_month = absint( $calendar['next_month'] );
	}

	// Add dates to calendar data for filters below.
	$calendar['next_year'] = $next_year;
	$calendar['next_month'] = $next_month;
	$calendar['prev_year'] = $prev_year;
	$calendar['prev_month'] = $prev_month;

	$thisyear = $calendar['year'];
	$thismonth = zeroise( absint( $calendar['month'] ), 2 );
	$last_day = $calendar['last_day'];

	/* translators: Calendar caption: 1: Month name, 2: 4-digit year. */
	$calendar_caption = _x( '%1$s %2$s', 'calendar caption' );
	$output  = '<table id="wp-calendar" class="wp-calendar-table">
	<caption>' . sprintf(
		$calendar_caption,
		$wp_locale->get_month( $thismonth ),
		gmdate( 'Y', $calendar['unixmonth'] )
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
		$output .= "\n\t\t<th scope=\"col\" title=\"$wd\">$day_name</th>";
	}

	$output .= '
	</tr>
	</thead>
	<tbody>
	<tr>';

	$daywithpost = array();
	if ( is_array( $calendar['calendar_days'] ) && empty( $calendar['calendar_days'] ) ) {
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
		$daywithpost = is_array( $calendar['calendar_days'] ) ? $calendar['calendar_days'] : array();
	}

	$daywithpost = array_unique( array_map( 'intval', $daywithpost ) );
	$calendar['calendar_days'] = $daywithpost;

	// See how much we should pad in the beginning
	$pad = calendar_week_mod( date( 'w', $calendar['unixmonth'] ) - $week_begins );
	if ( 0 != $pad ) {
		$output .= "\n\t\t" . '<td colspan="' . esc_attr( $pad ) . '" class="pad">&nbsp;</td>';
	}

	$newrow = false;
	$daysinmonth = (int) gmdate( 't', $calendar['unixmonth'] );

	for ( $day = 1; $day <= $daysinmonth; ++$day ) {
		if ( isset( $newrow ) && $newrow ) {
			$output .= "\n\t</tr>\n\t<tr>\n\t\t";
		}
		$newrow = false;

		if ( $day == gmdate( 'j', $calendar['timestamp'] ) &&
			$thismonth == gmdate( 'm', $calendar['timestamp'] ) &&
			$thisyear == gmdate( 'Y', $calendar['timestamp'] ) ) {
			$output .= '<td id="today">';
		} else {
			$output .= '<td>';
		}

		if ( in_array( $day, $daywithpost ) ) {
			// any posts today?
			$date_format = date( _x( 'F j, Y', 'daily archives date format' ), strtotime( "{$thisyear}-{$thismonth}-{$day}" ) );
			$label = sprintf( __( 'Posts published on %s' ), $date_format );
			$output .= sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				cptda_get_day_archive_link( $thisyear, $thismonth, $day, $post_type ),
				esc_attr( $label ),
				$day
			);
		} else {
			$output .= $day;
		}
		$output .= '</td>';

		if ( 6 == calendar_week_mod( date( 'w', mktime( 0, 0 , 0, $thismonth, $day, $thisyear ) ) - $week_begins ) ) {
			$newrow = true;
		}
	}

	$pad = 7 - calendar_week_mod( date( 'w', mktime( 0, 0 , 0, $thismonth, $day, $thisyear ) ) - $week_begins );
	if ( $pad != 0 && $pad != 7 ) {
		$output .= "\n\t\t" . '<td class="pad" colspan="' . esc_attr( $pad ) . '">&nbsp;</td>';
	}

	$output .= "\n\t</tr>\n\t</tbody>";
	$output .= "\n\t</table>";

	$output .= '<nav aria-label="' . __( 'Previous and next months' ) . '" class="wp-calendar-nav">';

	if ( $prev_year && $prev_month ) {
		$output .= "\n\t\t" . '<span class="wp-calendar-nav-prev"><a href="' . cptda_get_month_archive_link( $prev_year, $prev_month, $post_type ) . '">&laquo; ' .
			$wp_locale->get_month_abbrev( $wp_locale->get_month( $prev_month ) ) .
			'</a></span>';
	} else {
		$output .= "\n\t\t" . '<span class="wp-calendar-nav-prev">&nbsp;</span>';
	}

	$output .= "\n\t\t" . '<span class="pad">&nbsp;</span>';

	if ( $next_year && $next_month ) {
		$output .= "\n\t\t" . '<span class="wp-calendar-nav-next"><a href="' . cptda_get_month_archive_link( $next_year, $next_month, $post_type ) . '">' .
			$wp_locale->get_month_abbrev( $wp_locale->get_month( $next_month ) ) .
			' &raquo;</a></span>';
	} else {
		$output .= "\n\t\t" . '<span class="wp-calendar-nav-next">&nbsp;</span>';
	}

	$output .= '
	</nav>';


	$cache[ $key ] = $output;
	wp_cache_set( 'cptda_get_calendar', $cache, 'calendar' );

	$cache_data[ $key ] = $calendar;
	wp_cache_set( 'cptda_get_calendar_data', $cache_data, 'calendar_data' );

	if ( $echo ) {
		/**
		 * Filter the HTML calendar output.
		 *
		 * @since 2.6.0
		 *
		 * @param string $output   HTML output of the calendar.
		 * @param array  $calendar Array with arguments for the current calendar.
		 */
		echo apply_filters( 'cptda_get_calendar', $output, $calendar );
		return;
	}
	/** This filter is documented in includes/calendar.php */
	return apply_filters( 'cptda_get_calendar', $output, $calendar );
}
