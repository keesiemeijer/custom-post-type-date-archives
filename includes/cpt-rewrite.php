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

// Exit if accessed directly
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
	 * Custom post types with 'date-archives' support.
	 *
	 * @since 1.0
	 * @var array
	 */
	private $front;

	/**
	 * Custom post types with 'date-archives-feed' support.
	 *
	 * @since 2.2.0
	 * @var array
	 */
	private $permalink_structure;

	/**
	 * Plugin object.
	 *
	 * @since 2.3.0
	 * @var object
	 */
	private $date_structure;


	public function __construct( $post_type = '' ) {
		$this->init( trim( (string) $post_type ) );
	}

	private function init( $post_type ) {
		global $wp_rewrite;

		// Reset values
		$this->permalink_structure = '';
		$this->front               = '';
		$this->date_structure      = '';

		if ( !cptda_is_date_post_type( $post_type  ) || empty( $wp_rewrite->permalink_structure ) ) {
			return;
		}

		$post_type = get_post_type_object( $post_type );

		$this->permalink_structure = $wp_rewrite->permalink_structure;

		$front = isset( $post_type->rewrite['with_front'] ) ? (bool) $post_type->rewrite['with_front'] : 1;
		$front = $front ? $wp_rewrite->front : $wp_rewrite->root;

		// Check if rewrite slug is set for the post type.
		$slug = isset( $post_type->rewrite['slug'] ) ? $post_type->rewrite['slug'] : '';
		$slug = !empty( $slug ) ? $slug : $post_type->name;

		$this->front = ltrim( trailingslashit( $front ) . $slug , '/' );
	}


	public function get_front() {
		return $this->front;
	}

	public function get_date_permastruct() {

		if ( empty( $this->permalink_structure ) ) {
			$this->date_structure = '';
			return false;
		}

		// The date permalink must have year, month, and day separated by slashes.
		$endians = array( '%year%/%monthnum%/%day%', '%day%/%monthnum%/%year%', '%monthnum%/%day%/%year%' );

		$this->date_structure = '';
		$date_endian = '';

		foreach ( $endians as $endian ) {
			if ( false !== strpos( $this->permalink_structure, $endian ) ) {
				$date_endian= $endian;
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
			if ( '%post_id%' == $token && ( $tok_index <= 3 ) ) {
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
