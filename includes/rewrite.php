<?php
/**
 * Rewrite rules.
 *
 * @package     Custom Post Type Date Archives
 * @subpackage  Classes/Rewrite
 * @copyright   Copyright (c) 2014, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add custom post type date archives rewrite rules.
 *
 * @since 1.0
 * @author keesiemeijer
 */
class CPTDA_Rewrite {

	/**
	 * Custom post types with 'date-archives' support.
	 *
	 * @since 1.0
	 * @var array
	 */
	private $post_types;


	public function __construct() {
		// Setup the rewrite class after the post types are set up (priority 15).
		add_action( 'wp_loaded', array( $this, 'setup_archives' ), 15 );
	}


	/**
	 * Sets up the rewrite rules for custom post type date archives.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function setup_archives() {

		$instance = cptda_date_archives();
		$this->post_types = $instance->post_type->get_date_archive_post_types( 'names' );

		if ( !empty( $this->post_types ) ) {

			// The custom post type date archive rewrite rules are added when the rewrite rules are flushed.
			// Or when the rewrite rules are generated.

			// Add the custom post type date archive rewrite rules when the rewrite rules are generated.
			add_action( 'generate_rewrite_rules', array( $this, 'generate_rewrite_rules' ) );

			/**
			 * Filter whether to disable the automatic flushing of rewrite rules.
			 * Rewrite rules are automatically flushed by this plugin on the front end.
			 * They are only flushed when the date rewrite rules for custom post types don't exist yet.
			 * If disabled you'll have to update the rewrite rules manually by going to wp-admin > Settings > Permalinks.
			 *
			 * @since 1.0
			 * @param boolean $flush Flush rewrite rules for new cpt date archives. Default true.
			 */
			$flush = apply_filters( 'cptda_flush_rewrite_rules', true );

			if ( !is_admin() && $flush && $this->is_new_rewrite_rules() ) {
				// New cpt date archive rewrite rules found.
				$this->flush_rules();
			}
		}
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
	private function get_rewrite_rules() {
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
	private function date_rewrite_rules( $cpt ) {
		global $wp_rewrite;

		$rules    = array();
		$instance = cptda_date_archives();
		$slug     = $instance->post_type->get_post_type_base_slug( $cpt );

		if ( empty( $slug ) ) {
			return $rules;
		}

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
			$rule = $slug . '/' . $data['rule'];

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
	private function flush_rules() {
		global $wp_rewrite;

		// Uncomment the following to see when rules are flushed.
		// echo 'The Custom Post Type Date Archives plugin flushed the rewrite rules';

		$wp_rewrite->flush_rules();
	}

}

$cptda_rewrite = new CPTDA_Rewrite();
