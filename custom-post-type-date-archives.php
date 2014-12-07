<?php
/*
Plugin Name: Custom Post Type Date Archives
Version: 1.0
Plugin URI:
Description: This plugin allows you to add date archives to custom post types in your theme's functions.php file.
Author: keesiemijer
Author URI:
License: GPL v2

Custom Post Type Date Archives
Copyright 2014  Kees Meijer  (email : keesie.meijer@gmail.com)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version. You may NOT assume that you can use any other version of the GPL.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * Class to add date archives to custom post types.
 *
 * @since 1.0
 * @author keesiemeijer
 */
class CPTDA_Custom_Post_Type_Date_Archives {

	/**
	 * Custom post types with 'date-archives' support.
	 *
	 * @since 1.0
	 * @var array
	 */
	public $post_types;

	/**
	 * Class instance.
	 *
	 * @since 1.0
	 * @var object
	 */
	private static $instance = null;


	/**
	 * Acces this plugin's working instance.
	 *
	 * @since 1.0
	 * @return object
	 */
	public static function get_instance() {
		// create a new object if it doesn't exist.
		is_null( self::$instance ) && self::$instance = new self;
		return self::$instance;
	}


	/**
	 * Sets up class properties on action hook wp_loaded.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function init() {
		add_action( 'wp_loaded', array( self::get_instance(), 'setup_archives' ) );
	}


	/**
	 * Sets up custom post type date archives.
	 *
	 * @since 1.0
	 * @return void
	 */
	function setup_archives() {
		$this->post_types = $this->get_date_archive_post_types();

		if ( !empty( $this->post_types ) ) {

			// The custom post type date archive rewrite rules are added when the rewrite rules are flushed.
			// Or when the rewrite rules are generated.

			// Add the custom post type date archive rewrite rules when the rewrite rules are generated.
			add_action( 'generate_rewrite_rules', array( $this, 'generate_rewrite_rules' ) );

			/**
			 * This filter allows you to disable the automatic flushing of rewrite rules.
			 * The rewrite rules are automaticly flushed on on the front end when
			 * the date rewrite rules for custom post types don't exist yet.
			 *
			 * If disabled you'll have to update the rewrite rules manually by
			 * going to wp-admin > Settings > Permalinks if you add date-archive support for custom post types.
			 *
			 * @since 1.0
			 * @param boolean $automatic_flush Flush rewrite rules for new cpt date archives. Default true.
			 */
			$flush = apply_filters( 'custom_post_type_date_archives_flush_rules', true );

			if ( !is_admin() && $flush && $this->is_new_rewrite_rules() ) {
				// New cpt date archive rewrite rules found.
				$this->flush_rules();
			}
		}
	}


	/**
	 * Returns custom post types to create a date archive for.
	 * Checks if 'date-archives' support was added to custom post types.
	 * see http://codex.wordpress.org/Function_Reference/add_post_type_support
	 *
	 * @since 1.0
	 * @param array|string $post_type Post type(s).
	 * @return array Post types to create date archives for.
	 */
	public function get_date_archive_post_types() {

		$archive_post_types = array();
		$custom_post_types  = get_post_types( array( 'public' => true, '_builtin' => false ), 'names', 'and' );

		foreach ( array_keys( $custom_post_types ) as $type ) {

			if ( post_type_supports( $type, 'date-archives' ) ) {
				$archive_post_types[] = $type;
			}
		}

		return $archive_post_types;
	}


	/**
	 * Adds all custom post types date archive rewrite rules to the current rewrite rules.
	 *
	 * @since 1.0
	 * @param object  $wp_rewrite WP_Rewrite object.
	 * @return object WP_Rewrite object.
	 */
	public function generate_rewrite_rules( $wp_rewrite ) {
		$rules = $this->get_rewrite_rules();
		$wp_rewrite->rules = $rules + $wp_rewrite->rules;
		return $wp_rewrite;
	}


	/**
	 * Returns date rewrite rules for all custom post types that support date archives.
	 *
	 * @since 1.0
	 * @return array Custom post types date archive rewrite rules.
	 */
	function get_rewrite_rules() {
		$rules = array();

		foreach ( (array) $this->post_types as $type ) {
			$rules = $rules + $this->date_rewrite_rules( $type );
		}
		return $rules;
	}


	/**
	 * Creates rewrite rules for a custom post type that supports date archives.
	 *
	 * @since 1.0
	 * @param string  $cpt Custom post type name.
	 * @return array Array with custom post type date archive rewrite rules.
	 */
	function date_rewrite_rules( $cpt ) {
		global $wp_rewrite;
		$rules = array();

		$post_type = get_post_type_object( $cpt );
		if ( isset( $post_type->name ) && !post_type_exists( $post_type->name ) ) {
			return $rules;
		}

		if ( !$post_type->has_archive ) {
			return $rules;
		}

		// Check if with_front is set for the post type.
		$front = isset( $post_type->rewrite['with_front'] ) ? (bool) $post_type->rewrite['with_front'] : 1;
		$base = $front ? $wp_rewrite->front : $wp_rewrite->root;

		// Check if rewrite slug is set for the post type.
		$slug = isset( $post_type->rewrite['slug'] ) ? $post_type->rewrite['slug'] : '';
		$slug = !empty( $slug ) ? $slug : $post_type->name;

		// Create slug with base.
		$archive_slug = ltrim( trailingslashit( $base ) . $slug , '/' );

		$dates = array(
			array(
				'rule' => "([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})",
				'vars' => array( 'year', 'monthnum', 'day' ) ),
			array(
				'rule' => "([0-9]{4})/([0-9]{1,2})",
				'vars' => array( 'year', 'monthnum' ) ),
			array(
				'rule' => "([0-9]{4})",
				'vars' => array( 'year' ) )
		);

		foreach ( $dates as $data ) {
			$query = 'index.php?post_type='.$cpt;
			$rule = $archive_slug . '/' . $data['rule'];

			$i = 1;
			foreach ( $data['vars'] as $var ) {
				$query .= '&' . $var . '=' . $wp_rewrite->preg_index( $i );
				$i++;
			}

			$rules[ $rule . "/feed/(feed|rdf|rss|rss2|atom)/?$" ] = $query . "&feed=" . $wp_rewrite->preg_index( $i );
			$rules[ $rule . "/(feed|rdf|rss|rss2|atom)/?$" ]      = $query . "&feed=" . $wp_rewrite->preg_index( $i );
			$rules[ $rule . "/page/([0-9]{1,})/?$" ]              = $query . "&paged=" . $wp_rewrite->preg_index( $i );
			$rules[ $rule . "/?$" ] = $query;
		}

		return $rules;
	}


	/**
	 * Checks if the date rewrite rules for custom post types exist.
	 * The date rules exist if they're already in the current rewrite rules.
	 *
	 * @since 1.0
	 * @return boolean Returns true if custom post types date rewrite rules don't exist.
	 */
	private function is_new_rewrite_rules() {
		global $wp_rewrite;

		$rewrite_rules = get_option( 'rewrite_rules', $wp_rewrite->rules );


		if ( empty( $rewrite_rules ) ) {
			// Can't compare against the current rewrite rules. Lets bail.
			return false;
		}

		// Store the $wp_rewrite object in a temp variable.
		$wp_rewrite_temp = $wp_rewrite;

		// Set the 'matches' property for the preg_index() method used in $this->get_rewrite_rules().
		$wp_rewrite->matches = 'matches';

		// Get all custom post types date archive rules.
		$rules = $this->get_rewrite_rules();

		// restore the $wp_rewrite object.
		$wp_rewrite  = $wp_rewrite_temp;

		// Check if the rewrite rule or query exists.
		foreach ( $rules as $rule => $query ) {
			if ( !in_array( $query, $rewrite_rules ) || !key_exists( $rule, $rewrite_rules ) ) {
				// Doesn't exist.
				return true;
			}
		}

		return false;
	}

	/**
	 * Flush rewrite rules for new custom post type date archives.
	 * !Important. The rewrite rules are not flushed on every page load. That would be bad.
	 * See the custom_post_type_date_archives_flush_rules filter for when this method is called.
	 *
	 * @since 1.0
	 * @return void
	 */
	function flush_rules() {
		global $wp_rewrite;

		// Uncomment this to see when rules are flushed.
		// echo 'The Custom Post Type Date Archives plugin flushed the rewrite rules';

		$wp_rewrite->flush_rules();
	}

}

CPTDA_Custom_Post_Type_Date_Archives::init();
