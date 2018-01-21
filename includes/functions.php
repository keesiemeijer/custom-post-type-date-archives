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

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns custom post types depending on format and context.
 *
 * Use context 'date_archive' to get custom post types that have date archives support (Default).
 * Use context 'admin' to get custom post types that are registered to appear in the admin menu.
 * Use context 'publish_future' to get custom post types that publish future posts.
 *
 * @since 2.5.0
 * @param string $format  Accepts 'names', 'labels' or 'objects' Default 'names'.
 * @param string $context Accepts 'date_archive', 'admin' and 'publish_future'. Default 'date_archive'.
 *
 * @return array|object Array with post types depending on format and context.
 */
function cptda_get_post_types( $format = 'names', $context = 'date_archive' ) {
	$instance = cptda_date_archives();
	return $instance->post_type->get_post_types( $format, $context );
}


/**
 * Checks if a custom post type supports date archives.
 *
 * @param string $post_type Custom post type name.
 * @return bool True when the custom post type supports date archives.
 */
function cptda_is_date_post_type( $post_type = '' ) {
	if ( in_array( (string) $post_type, cptda_get_post_types( 'names' ) ) ) {
		return cptda_is_valid_post_type( $post_type );
	}

	return false;
}

/**
 * Check if a custom post type can support date archives.
 *
 * Does not check if the post type has support for date archives.
 * Use cptda_is_date_post_type() to check if a post type supports date archives.
 *
 * @since 2.3.0
 * @param string $post_type Post type name.
 * @return boolean True if it's a valid post type.
 */
function cptda_is_valid_post_type( $post_type ) {

	$post_type = get_post_type_object( trim( (string) $post_type ) );

	if ( ! $post_type ) {
		return false;
	}

	$args = array(
		'public'             => true,
		'publicly_queryable' => true,
		'has_archive'        => true,
		'_builtin'           => false,
	);

	$valid = wp_list_filter( array( $post_type ), $args, 'AND' );
	return ! empty( $valid );
}

/**
 * Is the query for a custom post type date archive?
 *
 * @see WP_Query::is_date()
 * @since 1.0
 * @return bool True on custom post type date archives.
 */
function cptda_is_cpt_date() {

	if ( is_date() && is_post_type_archive() ) {

		$post_type = get_query_var( 'post_type' );
		if ( is_array( $post_type ) ) {
			$post_type = reset( $post_type );
		}

		return cptda_is_date_post_type( $post_type );
	}

	return false;
}

/**
 * Get the queried date archive custom post type name.
 *
 * @since 2.5.0
 * @return string Post type name if the current query is for a custom post type date archive. Else empty string.
 */
function cptda_get_queried_date_archive_post_type() {

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
 * Used in the query (and widgets) for a custom post type date archive.
 * The post stati can be filtered with the 'cptda_post_stati' filter.
 *
 * @since 1.1
 * @param string $post_type Post type.
 * @return array Array with post stati for the post type. Default array( 'publish' ).
 */
function cptda_get_cpt_date_archive_stati( $post_type = '' ) {

	$post_status = array( 'publish' );

	if ( empty( $post_type ) || ! cptda_is_date_post_type( $post_type ) ) {
		return $post_status;
	}

	/**
	 * Filter post stati for a custom post type with date archives
	 *
	 * @since 1.1
	 * @param array $post_status Array with post stati for a custom post type with date archives
	 */
	$post_status = apply_filters( 'cptda_post_stati', $post_status, $post_type );
	return $post_status;
}

/**
 * Gets the post type base slug.
 *
 * @since 2.3.0
 * @param string $post_type Post type.
 * @return string Post type base (front + slug).
 */
function cptda_get_post_type_base( $post_type = '' ) {

	if ( ! cptda_is_date_post_type( $post_type ) ) {
		return '';
	}

	$rewrite = new CPTDA_CPT_Rewrite( $post_type );
	return $rewrite->get_base_permastruct();
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

	if ( ! cptda_is_date_post_type( $post_type ) ) {
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
	$post_status = ( is_array( $post_status ) && ! empty( $post_status ) ) ? $post_status : array( 'publish' );
	$post_status = array_map( 'esc_sql', $post_status );
	$post_status = "post_status IN ('" . implode( "', '", $post_status ) . "')";

	/**
	 * Filter the SQL WHERE clause for retrieving archives.
	 *
	 * @since 1.0
	 *
	 * @param string $sql_where Portion of SQL query containing the WHERE clause.
	 * @param array  $r         An array of default arguments.
	 */
	$where = apply_filters( 'cptda_getarchives_where', "WHERE post_type = '" . esc_sql( $post_type ) . "' AND {$post_status}", $r );

	/**
	 * Filter the SQL JOIN clause for retrieving archives.
	 *
	 * @since 1.0
	 *
	 * @param string $sql_join Portion of SQL query containing JOIN clause.
	 * @param array  $r        An array of default arguments.
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
