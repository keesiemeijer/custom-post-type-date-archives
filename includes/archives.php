<?php
/**
 * Archives
 *
 * Customized core WordPress wp_get_archives() function.
 *
 * @package     Custom_Post_Type_Date_Archives
 * @subpackage  Functions/Archives
 * @copyright   Copyright (c) 2017, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.6.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Display archive links based on post type, type and format.
 *
 * Copied from the WordPress function wp_get_archives().
 *
 * Use the extra `post_type` parameter in `$args` to display archive links for a custom post type.
 *
 * @since 1.0
 *
 * @see wp_get_archives()
 *
 * @param string|array $args {
 *     Default archive links arguments. Optional.
 *
 *     @type string     $post_type       Post type to get archives for. Default 'post'
 *     @type string     $type            Type of archive to retrieve. Accepts 'daily', 'weekly', 'monthly',
 *                                       'yearly', 'postbypost', or 'alpha'. Both 'postbypost' and 'alpha'
 *                                       display the same archive link list as well as post titles instead
 *                                       of displaying dates. The difference between the two is that 'alpha'
 *                                       will order by post title and 'postbypost' will order by post date.
 *                                       Default 'monthly'.
 *     @type string|int $limit           Number of links to limit the query to. Default empty (no limit).
 *     @type string|int $offset          Offset the query. Limit must be set for the offset to be used.
 *                                       Default empty (no offset).
 *     @type string     $format          Format each link should take using the $before and $after args.
 *                                       Accepts 'link' (`<link>` tag), 'option' (`<option>` tag), 'html'
 *                                       (`<li>` tag), a custom format, which generates a link anchor or
 *                                       'object' date objects from the database query.
 *                                       with $before preceding and $after succeeding. Default 'html'.
 *     @type string     $before          Markup to prepend to the beginning of each link. Default empty.
 *     @type string     $after           Markup to append to the end of each link. Default empty.
 *     @type bool       $show_post_count Whether to display the post count alongside the link. Default false.
 *     @type bool       $echo            Whether to echo or return the links list. Default 1|true to echo.
 *     @type string     $order           Whether to use ascending or descending order. Accepts 'ASC', or 'DESC'.
 *                                       Default 'DESC'.
 *     @type string     $year            Year. Default current year.
 *     @type string     $monthnum        Month number. Default current month number.
 *     @type string     $day             Day. Default current day.
 *     @type string     $w               Week. Default current week.
 * }
 * @return string|null String when retrieving, null when displaying.
 */
function cptda_get_archives( $args = '' ) {
	global $wpdb, $wp_locale;
	$defaults = cptda_get_archive_settings();

	$date_args = array(
		'year'     => get_query_var( 'year' ),
		'monthnum' => get_query_var( 'monthnum' ),
		'day'      => get_query_var( 'day' ),
		'w'        => get_query_var( 'w' ),
	);

	$defaults = array_merge( $defaults, $date_args );

	$args = wp_parse_args( $args, $defaults );
	$args = cptda_sanitize_archive_settings( $args );

	// Reset format for 'objects'
	$object_format  = ( 'object' === $args['format'] );
	$args['format'] = $object_format ? 'html' : $args['format'];

	$post_type = $args['post_type'];
	$types     = cptda_get_post_types();
	$types[]   = 'post';

	// Check if the post type has archives we can link to.
	if ( ! $post_type || ! in_array( $post_type, $types ) ) {
		return '';
	}

	$limit = '';
	if ( $args['limit'] ) {
		$offset = $args['offset'] ? $args['offset'] . ', ' : '';
		$limit  = ' LIMIT ' . $offset . $args['limit'];
	}

	if ( '' == $args['type'] ) {
		$args['type'] = 'monthly';
	}

	$order = strtoupper( $args['order'] );
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
	$post_status = ( is_array( $post_status ) && ! empty( $post_status ) ) ? $post_status : array( 'publish' );
	$post_status = array_map( 'esc_sql', $post_status );
	$post_status = "post_status IN ('" . implode( "', '", $post_status ) . "')";

	/**
	 * Filter the SQL WHERE clause for retrieving archives.
	 *
	 * @since 1.0
	 *
	 * @param string $sql_where Portion of SQL query containing the WHERE clause.
	 * @param array  $args      An array of default arguments.
	 */
	$where = apply_filters( 'cptda_getarchives_where', "WHERE post_type = '" . esc_sql( $post_type ) . "' AND {$post_status}", $args );

	/**
	 * Filter the SQL JOIN clause for retrieving archives.
	 *
	 * @since 1.0
	 *
	 * @param string $sql_join Portion of SQL query containing JOIN clause.
	 * @param array  $args     An array of default arguments.
	 */
	$join = apply_filters( 'cptda_getarchives_join', '', $args );

	$output = '';

	$last_changed = wp_cache_get( 'last_changed', 'posts' );
	if ( ! $last_changed ) {
		$last_changed = microtime();
		wp_cache_set( 'last_changed', $last_changed, 'posts' );
	}

	if ( 'monthly' == $args['type'] ) {
		$query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date $order $limit";
		$key = md5( $query );
		$key = "wp_get_archives:$key:$last_changed";
		if ( ! $results = wp_cache_get( $key, 'posts' ) ) {
			$results = $wpdb->get_results( $query );
			wp_cache_set( $key, $results, 'posts' );
		}
		if ( ! $object_format && $results ) {
			$after = $args['after'];
			foreach ( (array) $results as $result ) {
				$url = cptda_get_month_archive_link( $result->year, $result->month, $args['post_type'] );
				/* translators: 1: month name, 2: 4-digit year */
				$text = sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $result->month ), $result->year );
				if ( $args['show_post_count'] ) {
					$args['after'] = '&nbsp;(' . $result->posts . ')' . $after;
				}
				$selected = is_archive() && (string) $args['year'] === $result->year && (string) $args['monthnum'] === $result->month;
				$output .= get_archives_link( $url, $text, $args['format'], $args['before'], $args['after'], $selected );
			}
		}
	} elseif ( 'yearly' == $args['type'] ) {
		$query = "SELECT YEAR(post_date) AS `year`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date) ORDER BY post_date $order $limit";
		$key = md5( $query );
		$key = "wp_get_archives:$key:$last_changed";
		if ( ! $results = wp_cache_get( $key, 'posts' ) ) {
			$results = $wpdb->get_results( $query );
			wp_cache_set( $key, $results, 'posts' );
		}
		if ( ! $object_format && $results ) {
			$after = $args['after'];
			foreach ( (array) $results as $result ) {
				$url = cptda_get_year_archive_link( $result->year, $args['post_type'] );
				$text = sprintf( '%d', $result->year );
				if ( $args['show_post_count'] ) {
					$args['after'] = '&nbsp;(' . $result->posts . ')' . $after;
				}
				$selected = is_archive() && (string) $args['year'] === $result->year;
				$output .= get_archives_link( $url, $text, $args['format'], $args['before'], $args['after'], $selected );
			}
		}
	} elseif ( 'daily' == $args['type'] ) {
		$query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, DAYOFMONTH(post_date) AS `dayofmonth`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date), MONTH(post_date), DAYOFMONTH(post_date) ORDER BY post_date $order $limit";
		$key = md5( $query );
		$key = "wp_get_archives:$key:$last_changed";
		if ( ! $results = wp_cache_get( $key, 'posts' ) ) {
			$results = $wpdb->get_results( $query );
			$cache[ $key ] = $results;
			wp_cache_set( $key, $results, 'posts' );
		}
		if ( ! $object_format && $results ) {
			$after = $args['after'];
			foreach ( (array) $results as $result ) {
				$url  = cptda_get_day_archive_link( $result->year, $result->month, $result->dayofmonth, $args['post_type'] );
				$date = sprintf( '%1$d-%2$02d-%3$02d 00:00:00', $result->year, $result->month, $result->dayofmonth );
				$text = mysql2date( $archive_day_date_format, $date );
				if ( $args['show_post_count'] ) {
					$args['after'] = '&nbsp;(' . $result->posts . ')' . $after;
				}
				$selected = is_archive() && (string) $args['year'] === $result->year && (string) $args['monthnum'] === $result->month && (string) $args['day'] === $result->dayofmonth;
				$output .= get_archives_link( $url, $text, $args['format'], $args['before'], $args['after'], $selected );
			}
		}
	} elseif ( 'weekly' == $args['type'] ) {
		$week = _wp_mysql_week( '`post_date`' );
		$query = "SELECT DISTINCT $week AS `week`, YEAR( `post_date` ) AS `yr`, DATE_FORMAT( `post_date`, '%Y-%m-%d' ) AS `yyyymmdd`, count( `ID` ) AS `posts` FROM `$wpdb->posts` $join $where GROUP BY $week, YEAR( `post_date` ) ORDER BY `post_date` $order $limit";
		$key = md5( $query );
		$key = "wp_get_archives:$key:$last_changed";
		if ( ! $results = wp_cache_get( $key, 'posts' ) ) {
			$results = $wpdb->get_results( $query );
			wp_cache_set( $key, $results, 'posts' );
		}
		$arc_w_last = '';
		if ( ! $object_format && $results ) {
			$after = $args['after'];
			foreach ( (array) $results as $result ) {
				if ( $result->week != $arc_w_last ) {
					$arc_year       = $result->yr;
					$arc_w_last     = $result->week;
					$arc_week       = get_weekstartend( $result->yyyymmdd, get_option( 'start_of_week' ) );
					$arc_week_start = date_i18n( $archive_week_start_date_format, $arc_week['start'] );
					$arc_week_end   = date_i18n( $archive_week_end_date_format, $arc_week['end'] );
					$url            = sprintf( '%1$s/%2$s%3$sm%4$s%5$s%6$sw%7$s%8$d', home_url(), '', '?', '=', $arc_year, '&amp;', '=', $result->week );
					$url            = add_query_arg( 'post_type', $args['post_type'], $url );
					$text           = $arc_week_start . $archive_week_separator . $arc_week_end;
					if ( $args['show_post_count'] ) {
						$args['after'] = '&nbsp;(' . $result->posts . ')' . $after;
					}
					$selected = is_archive() && (string) $args['year'] === $result->yr && (string) $args['w'] === $result->week;
					$output .= get_archives_link( $url, $text, $args['format'], $args['before'], $args['after'], $selected );
				}
			}
		}
	} elseif ( ( 'postbypost' == $args['type'] ) || ( 'alpha' == $args['type'] ) ) {
		$orderby = ( 'alpha' == $args['type'] ) ? 'post_title ASC ' : 'post_date DESC ';
		$query = "SELECT * FROM $wpdb->posts $join $where ORDER BY $orderby $limit";
		$key = md5( $query );
		$key = "wp_get_archives:$key:$last_changed";
		if ( ! $results = wp_cache_get( $key, 'posts' ) ) {
			$results = $wpdb->get_results( $query );
			wp_cache_set( $key, $results, 'posts' );
		}
		if ( ! $object_format && $results ) {
			foreach ( (array) $results as $result ) {
				if ( $result->post_date != '0000-00-00 00:00:00' ) {
					$url = get_permalink( $result );
					if ( $result->post_title ) {
						/** This filter is documented in wp-includes/post-template.php */
						$text = strip_tags( apply_filters( 'the_title', $result->post_title, $result->ID ) );
					} else {
						$text = $result->ID;
					}
					$selected = $result->ID === get_the_ID();
					$output .= get_archives_link( $url, $text, $args['format'], $args['before'], $args['after'], $selected );
				}
			}
		}
	}

	$results = isset( $results ) && is_array( $results ) ? $results : array();

	/**
	 * Filter the archive HTML.
	 *
	 * @since  2.6.0
	 *
	 * @param string $output  Archive HTML.
	 * @param array  $results Array with date objects.
	 * @param array  $args    Arguments for the archive.
	 */
	$output = apply_filters( 'cptda_get_archives', $output, $results, $args );

	if ( $args['echo'] ) {
		echo $output;
	} else {
		return $object_format ? $results : $output;
	}
}
