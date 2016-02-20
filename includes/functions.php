<?php
/**
 * Functions
 *
 * @package     Custom Post Type Date Archives
 * @subpackage  Functions
 * @copyright   Copyright (c) 2014, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Is the query for a custom post type date archive?
 *
 * @see WP_Query::is_date()
 * @since 1.0
 * @param unknown
 * @return bool
 */
function cptda_is_cpt_date() {

	if ( is_date() && is_post_type_archive() ) {

		$post_type = get_query_var( 'post_type' );
		if ( is_array( $post_type ) ) {
			$post_type = reset( $post_type );
		}

		if ( cptda_is_date_post_type( $post_type ) ) {
			return true;
		}
	}

	return false;
}


/**
 * Checks if the post type supports date archives.
 *
 * @param string  $post_type Post type name.
 * @return bool Returns true when the post type supports date archives.
 */
function cptda_is_date_post_type( $post_type = '' ) {

	$instance   = cptda_date_archives();
	$post_types = $instance->post_type->get_date_archive_post_types( 'names' );

	if ( in_array( (string) $post_type, $post_types ) ) {
		return true;
	}

	return false;
}


/**
 * Get the current date archive custom post type.
 *
 * @since 1.0
 * @return string Post type.
 */
function cptda_get_date_archive_cpt() {

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
 * @since 1.1
 * @return array Array with post stati for the post type.
 */
function cptda_get_cpt_date_archive_stati( $post_type = '' ) {

	if ( empty( $post_type ) || !cptda_is_date_post_type( $post_type ) ) {
		return array();
	}

	$post_status   = array( 'publish' );
	$instance      = cptda_date_archives();
	$future_status = $instance->post_type->get_date_archive_post_types( 'future_status' );

	if ( in_array( $post_type, $future_status ) ) {
		$post_status[] = 'future';
	}

	return apply_filters( 'cptda_post_stati', $post_status, $post_type );
}

/**
 * Returns public post types that have archives and are displayed in the admin menu.
 *
 * @since 2.1.0
 * @param string  $type Return type 'names' or 'objects'.
 * @return array|object Post types.
 */
function cptda_get_admin_post_types( $type = 'names' ) {

	$args = array(
		'public'             => true,
		'publicly_queryable' => true,
		'_builtin'           => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'has_archive'        => true,
	);

	$post_types = get_post_types( $args, 'objects', 'and' );

	if ( 'objects' === $type ) {
		return $post_types;
	}

	foreach ( $post_types as $key => $post_type ) {
		$post_types[ $key ] = esc_attr( $post_type->labels->menu_name );
	}

	return $post_types;
}


/**
 * Display archive links based on post type, type and format.
 * Similar to wp_get_archives() but for custom post types.
 *
 * @since 1.0
 *
 * @see get_archives_link()
 *
 * @param string|array $args {
 *     Default archive links arguments. Optional.
 *
 *     @type string     $type            Type of archive to retrieve. Accepts 'daily', 'weekly', 'monthly',
 *                                       'yearly', 'postbypost', or 'alpha'. Both 'postbypost' and 'alpha'
 *                                       display the same archive link list as well as post titles instead
 *                                       of displaying dates. The difference between the two is that 'alpha'
 *                                       will order by post title and 'postbypost' will order by post date.
 *                                       Default 'monthly'.
 *     @type string|int $limit           Number of links to limit the query to. Default empty (no limit).
 *     @type string     $format          Format each link should take using the $before and $after args.
 *                                       Accepts 'link' (`<link>` tag), 'option' (`<option>` tag), 'html'
 *                                       (`<li>` tag), or a custom format, which generates a link anchor
 *                                       with $before preceding and $after succeeding. Default 'html'.
 *     @type string     $before          Markup to prepend to the beginning of each link. Default empty.
 *     @type string     $after           Markup to append to the end of each link. Default empty.
 *     @type bool       $show_post_count Whether to display the post count alongside the link. Default false.
 *     @type bool       $echo            Whether to echo or return the links list. Default 1|true to echo.
 *     @type string     $order           Whether to use ascending or descending order. Accepts 'ASC', or 'DESC'.
 *                                       Default 'DESC'.
 * }
 * @return string|null String when retrieving, null when displaying.
 */
function cptda_get_archives( $args = '' ) {
	global $wpdb, $wp_locale;

	$defaults = array(
		'type' => 'monthly', 'limit' => '',
		'format' => 'html', 'before' => '',
		'after' => '', 'show_post_count' => false,
		'echo' => 1, 'order' => 'DESC', 'post_type' => ''
	);

	$r = wp_parse_args( $args, $defaults );

	$post_type = sanitize_key( trim( (string) $r['post_type'] ) );

	if ( !cptda_is_date_post_type( $post_type ) ) {
		unset( $r['post_type'] );
		if ( $r['echo'] ) {
			wp_get_archives( $r );
			return;
		} else {
			return wp_get_archives( $r );
		}
	}

	if ( '' == $r['type'] ) {
		$r['type'] = 'monthly';
	}

	if ( ! empty( $r['limit'] ) ) {
		$r['limit'] = absint( $r['limit'] );
		$r['limit'] = ' LIMIT ' . $r['limit'];
	}

	$order = strtoupper( $r['order'] );
	if ( $order !== 'ASC' ) {
		$order = 'DESC';
	}

	// this is what will separate dates on weekly archive links
	$archive_week_separator = '&#8211;';

	// over-ride general date format ? 0 = no: use the date format set in Options, 1 = yes: over-ride
	$archive_date_format_over_ride = 0;

	// options for daily archive (only if you over-ride the general date format)
	$archive_day_date_format = 'Y/m/d';

	// options for weekly archive (only if you over-ride the general date format)
	$archive_week_start_date_format = 'Y/m/d';
	$archive_week_end_date_format = 'Y/m/d';

	if ( ! $archive_date_format_over_ride ) {
		$archive_day_date_format = get_option( 'date_format' );
		$archive_week_start_date_format = get_option( 'date_format' );
		$archive_week_end_date_format = get_option( 'date_format' );
	}

	$post_status = cptda_get_cpt_date_archive_stati( $post_type );
	$post_status = ( is_array( $post_status ) && !empty( $post_status ) ) ? $post_status : array( 'publish' );
	$post_status = array_map( 'esc_sql', $post_status );
	$post_status = "post_status IN ('" . implode( "', '", $post_status ) . "')";

	/**
	 * Filter the SQL WHERE clause for retrieving archives.
	 *
	 * @since 1.0
	 *
	 * @param string  $sql_where Portion of SQL query containing the WHERE clause.
	 * @param array   $r         An array of default arguments.
	 */
	$where = apply_filters( 'cptda_getarchives_where', "WHERE post_type = '" . esc_sql( $post_type ) . "' AND {$post_status}", $r );

	/**
	 * Filter the SQL JOIN clause for retrieving archives.
	 *
	 * @since 1.0
	 *
	 * @param string  $sql_join Portion of SQL query containing JOIN clause.
	 * @param array   $r        An array of default arguments.
	 */
	$join = apply_filters( 'cptda_getarchives_join', '', $r );

	$output = '';

	$last_changed = wp_cache_get( 'last_changed', 'posts' );
	if ( ! $last_changed ) {
		$last_changed = microtime();
		wp_cache_set( 'last_changed', $last_changed, 'posts' );
	}

	$limit = $r['limit'];

	if ( 'monthly' == $r['type'] ) {
		$query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date $order $limit";
		$key = md5( $query );
		$key = "wp_get_archives:$key:$last_changed";
		if ( ! $results = wp_cache_get( $key, 'posts' ) ) {
			$results = $wpdb->get_results( $query );
			wp_cache_set( $key, $results, 'posts' );
		}
		if ( $results ) {
			$after = $r['after'];
			foreach ( (array) $results as $result ) {
				$url = cptda_get_month_link( $result->year, $result->month, $r['post_type'] );
				/* translators: 1: month name, 2: 4-digit year */
				$text = sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $result->month ), $result->year );
				if ( $r['show_post_count'] ) {
					$r['after'] = '&nbsp;(' . $result->posts . ')' . $after;
				}
				$output .= get_archives_link( $url, $text, $r['format'], $r['before'], $r['after'] );
			}
		}
	} elseif ( 'yearly' == $r['type'] ) {
		$query = "SELECT YEAR(post_date) AS `year`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date) ORDER BY post_date $order $limit";
		$key = md5( $query );
		$key = "wp_get_archives:$key:$last_changed";
		if ( ! $results = wp_cache_get( $key, 'posts' ) ) {
			$results = $wpdb->get_results( $query );
			wp_cache_set( $key, $results, 'posts' );
		}
		if ( $results ) {
			$after = $r['after'];
			foreach ( (array) $results as $result ) {
				$url = cptda_get_year_link( $result->year, $r['post_type'] );
				$text = sprintf( '%d', $result->year );
				if ( $r['show_post_count'] ) {
					$r['after'] = '&nbsp;(' . $result->posts . ')' . $after;
				}
				$output .= get_archives_link( $url, $text, $r['format'], $r['before'], $r['after'] );
			}
		}
	} elseif ( 'daily' == $r['type'] ) {
		$query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, DAYOFMONTH(post_date) AS `dayofmonth`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date), MONTH(post_date), DAYOFMONTH(post_date) ORDER BY post_date $order $limit";
		$key = md5( $query );
		$key = "wp_get_archives:$key:$last_changed";
		if ( ! $results = wp_cache_get( $key, 'posts' ) ) {
			$results = $wpdb->get_results( $query );
			$cache[ $key ] = $results;
			wp_cache_set( $key, $results, 'posts' );
		}
		if ( $results ) {
			$after = $r['after'];
			foreach ( (array) $results as $result ) {
				$url  = cptda_get_day_link( $result->year, $result->month, $result->dayofmonth, $r['post_type'] );
				$date = sprintf( '%1$d-%2$02d-%3$02d 00:00:00', $result->year, $result->month, $result->dayofmonth );
				$text = mysql2date( $archive_day_date_format, $date );
				if ( $r['show_post_count'] ) {
					$r['after'] = '&nbsp;(' . $result->posts . ')' . $after;
				}
				$output .= get_archives_link( $url, $text, $r['format'], $r['before'], $r['after'] );
			}
		}
	} elseif ( 'weekly' == $r['type'] ) {
		$week = _wp_mysql_week( '`post_date`' );
		$query = "SELECT DISTINCT $week AS `week`, YEAR( `post_date` ) AS `yr`, DATE_FORMAT( `post_date`, '%Y-%m-%d' ) AS `yyyymmdd`, count( `ID` ) AS `posts` FROM `$wpdb->posts` $join $where GROUP BY $week, YEAR( `post_date` ) ORDER BY `post_date` $order $limit";
		$key = md5( $query );
		$key = "wp_get_archives:$key:$last_changed";
		if ( ! $results = wp_cache_get( $key, 'posts' ) ) {
			$results = $wpdb->get_results( $query );
			wp_cache_set( $key, $results, 'posts' );
		}
		$arc_w_last = '';
		if ( $results ) {
			$after = $r['after'];
			foreach ( (array) $results as $result ) {
				if ( $result->week != $arc_w_last ) {
					$arc_year       = $result->yr;
					$arc_w_last     = $result->week;
					$arc_week       = get_weekstartend( $result->yyyymmdd, get_option( 'start_of_week' ) );
					$arc_week_start = date_i18n( $archive_week_start_date_format, $arc_week['start'] );
					$arc_week_end   = date_i18n( $archive_week_end_date_format, $arc_week['end'] );
					$url            = sprintf( '%1$s/%2$s%3$sm%4$s%5$s%6$sw%7$s%8$d', home_url(), '', '?', '=', $arc_year, '&amp;', '=', $result->week );
					$url            = add_query_arg( 'post_type', $r['post_type'], $url );
					$text           = $arc_week_start . $archive_week_separator . $arc_week_end;
					if ( $r['show_post_count'] ) {
						$r['after'] = '&nbsp;(' . $result->posts . ')' . $after;
					}
					$output .= get_archives_link( $url, $text, $r['format'], $r['before'], $r['after'] );
				}
			}
		}
	} elseif ( ( 'postbypost' == $r['type'] ) || ( 'alpha' == $r['type'] ) ) {
		$orderby = ( 'alpha' == $r['type'] ) ? 'post_title ASC ' : 'post_date DESC ';
		$query = "SELECT * FROM $wpdb->posts $join $where ORDER BY $orderby $limit";
		$key = md5( $query );
		$key = "wp_get_archives:$key:$last_changed";
		if ( ! $results = wp_cache_get( $key, 'posts' ) ) {
			$results = $wpdb->get_results( $query );
			wp_cache_set( $key, $results, 'posts' );
		}
		if ( $results ) {
			foreach ( (array) $results as $result ) {
				if ( $result->post_date != '0000-00-00 00:00:00' ) {
					$url = get_permalink( $result );
					if ( $result->post_title ) {
						/** This filter is documented in wp-includes/post-template.php */
						$text = strip_tags( apply_filters( 'the_title', $result->post_title, $result->ID ) );
					} else {
						$text = $result->ID;
					}
					$output .= get_archives_link( $url, $text, $r['format'], $r['before'], $r['after'] );
				}
			}
		}
	}
	if ( $r['echo'] ) {
		echo $output;
	} else {
		return $output;
	}
}


/**
 * Display calendar with days that have posts as links.
 *
 * The calendar is cached, which will be retrieved, if it exists. If there are
 * no posts for the month, then it will not be displayed.
 *
 * @since 1.0.0
 *
 * @global wpdb      $wpdb
 * @global int       $m
 * @global int       $monthnum
 * @global int       $year
 * @global WP_Locale $wp_locale
 * @global array     $posts
 *
 * @param bool $post_type Post type.
 * @param bool $initial Optional, default is true. Use initial calendar names.
 * @param bool $echo    Optional, default is true. Set to false for return.
 * @return string|void String when retrieving.
 */
function cptda_get_calendar( $post_type, $initial = true, $echo = true ) {
	global $wpdb, $m, $monthnum, $year, $wp_locale, $posts;

	if ( empty( $post_type ) || !cptda_is_date_post_type( $post_type ) ) {
		return;
	}

	$post_status = cptda_get_cpt_date_archive_stati( $post_type );
	$post_status = ( is_array( $post_status ) && !empty( $post_status ) ) ? $post_status : array( 'publish' );
	$post_status = array_map( 'esc_sql', $post_status );
	$post_status = "post_status IN ('" . implode( "', '", $post_status ) . "')";

	$post_type_escaped = esc_sql( $post_type );

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
		$gotsome = $wpdb->get_var( "SELECT 1 as test FROM $wpdb->posts WHERE post_type = '{$post_type_escaped}' AND {$post_status} LIMIT 1" );
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

	// Get the next and previous month and year with at least one post
	$previous = $wpdb->get_row( "SELECT MONTH(post_date) AS month, YEAR(post_date) AS year
		FROM $wpdb->posts
		WHERE post_date < '$thisyear-$thismonth-01'
		AND post_type = '{$post_type_escaped}' AND {$post_status}
			ORDER BY post_date DESC
			LIMIT 1" );
	$next = $wpdb->get_row( "SELECT MONTH(post_date) AS month, YEAR(post_date) AS year
		FROM $wpdb->posts
		WHERE post_date > '$thisyear-$thismonth-{$last_day} 23:59:59'
		AND post_type = '{$post_type_escaped}' AND {$post_status}
			ORDER BY post_date ASC
			LIMIT 1" );

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

	if ( $previous ) {
		$calendar_output .= "\n\t\t".'<td colspan="3" id="prev"><a href="' . cptda_get_month_link( $previous->year, $previous->month, $post_type ) . '">&laquo; ' .
			$wp_locale->get_month_abbrev( $wp_locale->get_month( $previous->month ) ) .
			'</a></td>';
	} else {
		$calendar_output .= "\n\t\t".'<td colspan="3" id="prev" class="pad">&nbsp;</td>';
	}

	$calendar_output .= "\n\t\t".'<td class="pad">&nbsp;</td>';

	if ( $next ) {
		$calendar_output .= "\n\t\t".'<td colspan="3" id="next"><a href="' . cptda_get_month_link( $next->year, $next->month, $post_type ) . '">' .
			$wp_locale->get_month_abbrev( $wp_locale->get_month( $next->month ) ) .
			' &raquo;</a></td>';
	} else {
		$calendar_output .= "\n\t\t".'<td colspan="3" id="next" class="pad">&nbsp;</td>';
	}

	$calendar_output .= '
	</tr>
	</tfoot>

	<tbody>
	<tr>';

	$daywithpost = array();

	// Get days with posts
	$dayswithposts = $wpdb->get_results( "SELECT DISTINCT DAYOFMONTH(post_date)
		FROM $wpdb->posts WHERE post_date >= '{$thisyear}-{$thismonth}-01 00:00:00'
		AND post_type = '{$post_type_escaped}' AND {$post_status}
		AND post_date <= '{$thisyear}-{$thismonth}-{$last_day} 23:59:59'", ARRAY_N );
	if ( $dayswithposts ) {
		foreach ( (array) $dayswithposts as $daywith ) {
			$daywithpost[] = $daywith[0];
		}
	}

	// See how much we should pad in the beginning
	$pad = calendar_week_mod( date( 'w', $unixmonth ) - $week_begins );
	if ( 0 != $pad ) {
		$calendar_output .= "\n\t\t".'<td colspan="'. esc_attr( $pad ) .'" class="pad">&nbsp;</td>';
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
		$calendar_output .= "\n\t\t".'<td class="pad" colspan="'. esc_attr( $pad ) .'">&nbsp;</td>';
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
	/** This filter is documented in wp-includes/general-template.php */
	return apply_filters( 'cptda_get_calendar', $calendar_output );
}
