<?php
/**
 * Link Functions
 *
 * @package     Custom Post Type Date Archives
 * @subpackage  Functions/Links
 * @copyright   Copyright (c) 2014, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieve the permalink for custom post type year archives.
 *
 * @since 1.0
 *
 * @param int|bool $year      False for current year or year for permalink.
 * @param string   $post_type Post type.
 * @return string
 */
function cptda_get_year_link( $year, $post_type = '' ) {
	global $wp_rewrite;

	if ( ! cptda_is_date_post_type( $post_type ) ) {
		return '';
	}

	if ( ! $year ) {
		$year = gmdate( 'Y', current_time( 'timestamp' ) );
	}

	$cpt_rewrite = new CPTDA_CPT_Rewrite( $post_type );
	$yearlink = $cpt_rewrite->get_year_permastruct();

	if ( ! empty( $yearlink ) ) {
		$yearlink = str_replace( '%year%', $year, $yearlink );
		$yearlink = home_url( user_trailingslashit( $yearlink, 'year' ) );
	} else {
		$yearlink = home_url( '?m=' . $year );
		$yearlink = add_query_arg( 'post_type', $post_type, $yearlink );
	}

	/**
	 * Filter the year archive permalink.
	 *
	 * @since 1.0
	 *
	 * @param string $yearlink Permalink for the year archive.
	 * @param int    $year     Year for the archive.
	 */
	return apply_filters( 'cptda_get_year_link', $yearlink, $year );
}


/**
 * Retrieve the permalink for custom post type month archives.
 *
 * @since 1.0
 *
 * @param bool|int $year      False for current year. Integer of year.
 * @param bool|int $month     False for current month. Integer of month.
 * @param string   $post_type Post type.
 * @return string
 */
function cptda_get_month_link( $year, $month, $post_type = '' ) {
	global $wp_rewrite;

	if ( ! cptda_is_date_post_type( $post_type ) ) {
		return '';
	}

	if ( ! $year ) {
		$year = gmdate( 'Y', current_time( 'timestamp' ) );
	}

	if ( ! $month ) {
		$month = gmdate( 'm', current_time( 'timestamp' ) );
	}

	$cpt_rewrite = new CPTDA_CPT_Rewrite( $post_type );
	$monthlink = $cpt_rewrite->get_month_permastruct();

	if ( ! empty( $monthlink ) ) {
		$monthlink = str_replace( '%year%', $year, $monthlink );
		$monthlink = str_replace( '%monthnum%', zeroise( intval( $month ), 2 ), $monthlink );
		$monthlink = home_url( user_trailingslashit( $monthlink, 'month' ) );
	} else {
		$monthlink = home_url( '?m=' . $year . zeroise( $month, 2 ) );
		$monthlink = add_query_arg( 'post_type', $post_type, $monthlink );
	}

	/**
	 * Filter the month archive permalink.
	 *
	 * @since 1.0
	 *
	 * @param string $monthlink Permalink for the month archive.
	 * @param int    $year      Year for the archive.
	 * @param int    $month     The month for the archive.
	 */
	return apply_filters( 'cptda_get_month_link', $monthlink, $year, $month );
}


/**
 * Retrieve the permalink for custom post type day archives.
 *
 * @since 1.0
 *
 * @param bool|int $year      False for current year. Integer of year.
 * @param bool|int $month     False for current month. Integer of month.
 * @param bool|int $day       False for current day. Integer of day.
 * @param string   $post_type Post type.
 * @return string
 */
function cptda_get_day_link( $year, $month, $day, $post_type = '' ) {
	global $wp_rewrite;

	if ( ! cptda_is_date_post_type( $post_type ) ) {
		return '';
	}

	if ( ! $year ) {
		$year = gmdate( 'Y', current_time( 'timestamp' ) );
	}

	if ( ! $month ) {
		$month = gmdate( 'm', current_time( 'timestamp' ) );
	}

	if ( ! $day ) {
		$day = gmdate( 'j', current_time( 'timestamp' ) );
	}

	$cpt_rewrite = new CPTDA_CPT_Rewrite( $post_type );
	$daylink = $cpt_rewrite->get_day_permastruct();

	if ( ! empty( $daylink ) ) {
		$daylink = str_replace( '%year%', $year, $daylink );
		$daylink = str_replace( '%monthnum%', zeroise( intval( $month ), 2 ), $daylink );
		$daylink = str_replace( '%day%', zeroise( intval( $day ), 2 ), $daylink );
		$daylink = home_url( user_trailingslashit( $daylink, 'day' ) );
	} else {
		$daylink = home_url( '?m=' . $year . zeroise( $month, 2 ) . zeroise( $day, 2 ) );
		$daylink = add_query_arg( 'post_type', $post_type, $daylink );
	}

	/**
	 * Filter the day archive permalink.
	 *
	 * @since 1.0
	 *
	 * @param string $daylink Permalink for the day archive.
	 * @param int    $year    Year for the archive.
	 * @param int    $month   Month for the archive.
	 * @param int    $day     The day for the archive.
	 */
	return apply_filters( 'cptda_get_day_link', $daylink, $year, $month, $day );
}
