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
 * @param bool $post_type Post type.
 * @param bool $initial   Optional, default is true. Use initial calendar names.
 * @param bool $echo      Optional, default is true. Set to false for return.
 * @return string|void String when retrieving.
 */
function cptda_get_calendar( $post_type, $initial = true, $echo = true ) {
	global $wpdb, $m, $monthnum, $year, $wp_locale, $posts;

	if ( empty( $post_type ) || ! cptda_is_date_post_type( $post_type ) ) {
		return;
	}

	$post_status = cptda_get_cpt_date_archive_stati( $post_type );
	if ( ! ( is_array( $post_status ) && $post_status ) ) {
		$post_status = array( 'publish' );
	}

	$post_status_escaped = array_map( 'esc_sql', $post_status );
	$post_status_sql     = "post_status IN ('" . implode( "', '", $post_status_escaped ) . "')";

	$post_type_escaped = esc_sql( $post_type );
	$post_type_sql     = "post_type = '{$post_type_escaped}' AND {$post_status_sql}";

	$key = md5( $m . $monthnum . $year );
	$cache = wp_cache_get( 'cptda_get_calendar', 'calendar' );

	if ( $cache && is_array( $cache ) && isset( $cache[ $key ] ) ) {
		/** This filter is documented in wp-includes/general-template.php */
		$output = apply_filters( 'cptda_get_calendar', $cache[ $key ] );

		if ( $echo ) {
			echo $output;
			return;
		}

		return $output;
	}

	if ( ! is_array( $cache ) ) {
		$cache = array();
	}

	// Quick check. If we have no posts at all, abort!
	if ( ! $posts ) {
		$gotsome = $wpdb->get_var( "SELECT 1 as test FROM $wpdb->posts WHERE {$post_type_sql} LIMIT 1" );
		if ( ! $gotsome ) {
			$cache[ $key ] = '';
			wp_cache_set( 'cptda_get_calendar', $cache, 'calendar' );
			return;
		}
	}

	if ( isset( $_GET['w'] ) ) {
		$w = (int) $_GET['w'];
	}
	// week_begins = 0 stands for Sunday
	$week_begins = (int) get_option( 'start_of_week' );
	$ts = current_time( 'timestamp' );

	// Let's figure out when we are
	if ( ! empty( $monthnum ) && ! empty( $year ) ) {
		$thismonth = zeroise( intval( $monthnum ), 2 );
		$thisyear = (int) $year;
	} elseif ( ! empty( $w ) ) {
		// We need to get the month from MySQL
		$thisyear = (int) substr( $m, 0, 4 );
		//it seems MySQL's weeks disagree with PHP's
		$d = ( ( $w - 1 ) * 7 ) + 6;
		$thismonth = $wpdb->get_var( "SELECT DATE_FORMAT((DATE_ADD('{$thisyear}0101', INTERVAL $d DAY) ), '%m')" );
	} elseif ( ! empty( $m ) ) {
		$thisyear = (int) substr( $m, 0, 4 );
		if ( strlen( $m ) < 6 ) {
			$thismonth = '01';
		} else {
			$thismonth = zeroise( (int) substr( $m, 4, 2 ), 2 );
		}
	} else {
		$thisyear = gmdate( 'Y', $ts );
		$thismonth = gmdate( 'm', $ts );
	}

	$unixmonth = mktime( 0, 0 , 0, $thismonth, 1, $thisyear );
	$last_day = date( 't', $unixmonth );

	$calendar_data = array(
		'year'          => $thisyear,
		'month'         => $thismonth,
		'last_day'      => $last_day,
		'post_type'     => $post_type,
		'post_status'   => $post_status,
		'post_type_sql' => $post_type_sql,
	);

	$navigation = array(
		'previous' => array( 'year' => '', 'month' => '' ),
		'next'     => array( 'year' => '', 'month' => '' ),
	);

	/**
	 * Filter the calendar's next and previous archive links.
	 *
	 * @since  2.5.1
	 *
	 *
	 * @param array $navigation    Array with year and month data for previous and next archive link.
	 *                             Set the 'previous' or 'next' key to false to disable the archive link
	 * @param array $calendar_data Array with data for the current callendar.
	 */
	$calendar_nav = apply_filters( 'cptda_get_calendar_calendar_nav', $navigation, $calendar_data );
	$calendar_nav = array_merge( $navigation, $calendar_nav );

	$prev_year  = '';
	$prev_month = '';
	$prev       = $calendar_nav['previous'];
	if ( is_array( $prev ) ) {
		$prev_year  = isset( $prev['year'] ) ? absint( $prev['year'] ) : '';
		$prev_month = isset( $prev['month'] ) ? absint( $prev['month'] ) : '';
		if ( ! ( $prev_year && $prev_month ) ) {
			// previous month and year with at least one post
			$prev_obj = $wpdb->get_row( "SELECT MONTH(post_date) AS month, YEAR(post_date) AS year
		FROM $wpdb->posts
		WHERE post_date < '$thisyear-$thismonth-01'
		AND {$post_type_sql}
			ORDER BY post_date DESC
			LIMIT 1" );
			$prev_year  = isset( $prev_obj->year ) ? $prev_obj->year : '';
			$prev_month = isset( $prev_obj->month ) ? $prev_obj->month : '';
		}
	}

	$next_year  = '';
	$next_month = '';
	$next       = $calendar_nav['next'];
	if ( is_array( $next ) ) {
		$next_year  = isset( $next['year'] ) ? absint( $next['year'] ) : '';
		$next_month = isset( $next['month'] ) ? absint( $next['month'] ) : '';
		if ( ! ( $next_year && $next_month ) ) {
			$next_obj = $wpdb->get_row( "SELECT MONTH(post_date) AS month, YEAR(post_date) AS year
		FROM $wpdb->posts
		WHERE post_date > '$thisyear-$thismonth-{$last_day} 23:59:59'
		AND {$post_type_sql}
			ORDER BY post_date ASC
			LIMIT 1" );
			$next_year  = isset( $next_obj->year ) ? $next_obj->year : '';
			$next_month = isset( $next_obj->month ) ? $next_obj->month : '';
		}
	}

	/* translators: Calendar caption: 1: month name, 2: 4-digit year */
	$calendar_caption = _x( '%1$s %2$s', 'calendar caption' );
	$calendar_output = '<table id="wp-calendar">
	<caption>' . sprintf(
		$calendar_caption,
		$wp_locale->get_month( $thismonth ),
		date( 'Y', $unixmonth )
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

	/**
	 * Set calendar days for the current calendar.
	 *
	 * @since  2.5.1
	 *
	 * @param null|array $daywithpost   Array with numerical calendar days or null.
	 *                                  Default null (use days from current month and year).
	 * @param array      $calendar_data Array with data for the current callendar.
	 */
	$daywithpost = apply_filters( 'cptda_get_calendar_calendar_days', null, $calendar_data );

	if ( ! is_array( $daywithpost ) ) {
		$daywithpost = array();

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
	}

	$daywithpost = array_unique( array_map( 'intval', $daywithpost ) );

	// See how much we should pad in the beginning
	$pad = calendar_week_mod( date( 'w', $unixmonth ) - $week_begins );
	if ( 0 != $pad ) {
		$calendar_output .= "\n\t\t" . '<td colspan="' . esc_attr( $pad ) . '" class="pad">&nbsp;</td>';
	}

	$newrow = false;
	$daysinmonth = (int) date( 't', $unixmonth );

	for ( $day = 1; $day <= $daysinmonth; ++$day ) {
		if ( isset( $newrow ) && $newrow ) {
			$calendar_output .= "\n\t</tr>\n\t<tr>\n\t\t";
		}
		$newrow = false;

		if ( $day == gmdate( 'j', $ts ) &&
			$thismonth == gmdate( 'm', $ts ) &&
			$thisyear == gmdate( 'Y', $ts ) ) {
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

	if ( $echo ) {
		/**
		 * Filter the HTML calendar output.
		 *
		 * @since 3.0.0
		 *
		 * @param string $calendar_output HTML output of the calendar.
		 */
		echo apply_filters( 'cptda_get_calendar', $calendar_output );
		return;
	}
	/** This filter is documented in includes/calendar.php */
	return apply_filters( 'cptda_get_calendar', $calendar_output );
}
