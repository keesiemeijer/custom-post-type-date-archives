<?php
/**
 * Custom Post Type Date Archives Rewrite class.
 *
 * @package     Custom Post Type Date Archives
 * @subpackage  Classes/CPT_Rewrite
 * @copyright   Copyright (c) 2014, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add custom post type date archives rewrite rules.
 *
 * @since 2.3.0
 * @author keesiemeijer
 */
class CPTDA_CPT_Rewrite {

	/**
	 * Post type base permalink.
	 *
	 * @since 2.3.0
	 * @var array
	 */
	private $front;

	/**
	 * Current permalink structure.
	 *
	 * @since 2.3.0
	 * @var array
	 */
	private $permalink_structure;

	/**
	 * Custom post type date permalink structure.
	 *
	 * @since 2.3.0
	 * @var object
	 */
	private $date_structure;

	/**
	 * Constructor
	 *
	 * @param string $post_type Post Type.
	 */
	public function __construct( $post_type = '' ) {
		$this->init( trim( (string) $post_type ) );
	}

	/**
	 * Set's up the class properties
	 *
	 * @since 2.3.0
	 * @param string $post_type Post type name.
	 * @return void
	 */
	private function init( $post_type ) {
		global $wp_rewrite;

		// Reset values.
		$this->reset_permastruct();

		if ( empty( $wp_rewrite->permalink_structure ) || ! cptda_is_valid_post_type( $post_type ) ) {
			return;
		}

		$this->permalink_structure = $wp_rewrite->permalink_structure;

		$this->front = $this->get_cpt_base_permastruct( $post_type );
	}

	/**
	 * Reset the permastructs
	 */
	public function reset_permastruct() {
		$this->permalink_structure = '';
		$this->front               = '';
		$this->date_structure      = '';
	}

	/**
	 * Public function to get the base permalink structure
	 */
	public function get_base_permastruct() {
		return $this->front;
	}

	/**
	 * Returns the base permalink structure of a custom post type.
	 *
	 * @param string $post_type Post Type.
	 * @return string            Post type permalink structure base.
	 */
	private function get_cpt_base_permastruct( $post_type ) {
		global $wp_rewrite;

		$base        = '';
		$permastruct = $wp_rewrite->get_extra_permastruct( $post_type );

		if ( $permastruct ) {
			$base = str_replace( "%{$post_type}%", '', (string) $permastruct );
			$base = trim( $base, '/' );
		} else {
			$this->reset_permastruct();
		}

		return $base;
	}

	/**
	 * Retrieves date permalink structure, with year, month, and day.
	 *
	 * The permalink structure for the date, if not set already depends on the
	 * permalink structure. It can be one of three formats. The first is year,
	 * month, day; the second is day, month, year; and the last format is month,
	 * day, year. These are matched against the permalink structure for which
	 * one is used. If none matches, then the default will be used, which is
	 * year, month, day.
	 *
	 * Prevents post ID and date permalinks from overlapping. In the case of
	 * post_id, the date permalink will be prepended with front permalink with
	 * 'date/' before the actual permalink to form the complete date permalink
	 * structure.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return string|false False on no permalink structure. Date permalink structure.
	 */
	public function get_date_permastruct() {

		if ( empty( $this->permalink_structure ) || empty( $this->front ) ) {
			$this->reset_permastruct();
			return false;
		}

		// The date permalink must have year, month, and day separated by slashes.
		$endians = array( '%year%/%monthnum%/%day%', '%day%/%monthnum%/%year%', '%monthnum%/%day%/%year%' );

		$this->date_structure = '';
		$date_endian = '';

		foreach ( $endians as $endian ) {
			if ( false !== strpos( $this->permalink_structure, $endian ) ) {
				$date_endian = $endian;
				break;
			}
		}

		if ( empty( $date_endian ) ) {
			$date_endian = '%year%/%monthnum%/%day%';
		}

		/*
		 * Do not allow the date tags and %post_id% to overlap in the permalink
		 * structure. If they do, move the date tags to $front/date/.
		 */
		$front = trailingslashit( $this->front );
		preg_match_all( '/%.+?%/', $this->permalink_structure, $tokens );
		$tok_index = 1;
		foreach ( (array) $tokens[0] as $token ) {
			if ( '%post_id%' === $token && ( $tok_index <= 3 ) ) {
				$front = $front . 'date/';
				break;
			}
			$tok_index++;
		}

		$this->date_structure = $front . $date_endian;

		return $this->date_structure;
	}


	/**
	 * Retrieves the year permalink structure without month and day.
	 *
	 * Gets the date permalink structure and strips out the month and day
	 * permalink structures.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return false|string False on failure. Year structure on success.
	 */
	public function get_year_permastruct() {
		$structure = $this->get_date_permastruct();

		if ( empty( $structure ) ) {
			return false;
		}

		$structure = str_replace( '%monthnum%', '', $structure );
		$structure = str_replace( '%day%', '', $structure );
		$structure = preg_replace( '#/+#', '/', $structure );

		return $structure;
	}


	/**
	 * Retrieves the month permalink structure without day and with year.
	 *
	 * Gets the date permalink structure and strips out the day permalink
	 * structures. Keeps the year permalink structure.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return false|string False on failure. Year/Month structure on success.
	 */
	public function get_month_permastruct() {
		$structure = $this->get_date_permastruct();

		if ( empty( $structure ) ) {
			return false;
		}

		$structure = str_replace( '%day%', '', $structure );
		$structure = preg_replace( '#/+#', '/', $structure );

		return $structure;
	}


	/**
	 * Retrieves the day permalink structure with month and year.
	 *
	 * Keeps date permalink structure with all year, month, and day.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return string|false False on failure. Year/Month/Day structure on success.
	 */
	public function get_day_permastruct() {
		return $this->get_date_permastruct();
	}

}
