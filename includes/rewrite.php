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

	/**
	 * Custom post types with 'date-archives-feed' support.
	 *
	 * @since 2.2.0
	 * @var array
	 */
	private $feeds;

	/**
	 * Plugin object.
	 *
	 * @since 2.3.0
	 * @var object
	 */
	private $plugin;


	public function __construct() {
		// Setup the rewrite class after the post types are set up
		// Priority 15 is after setting up post types.
		add_action( 'wp_loaded', array( $this, 'setup_archives' ), 15 );
	}


	/**
	 * Sets up custom post type date archives.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function setup_archives() {

		$this->plugin = cptda_date_archives();

		if ( !( $this->plugin && $this->plugin instanceof Custom_Post_Type_Date_Archives ) ) {
			return;
		}

		$this->post_types = $this->plugin->post_type->get_date_archive_post_types( 'names' );

		if ( empty( $this->post_types ) ) {
			return;
		}

		/**
		 * Filter whether feeds are added to date archives.
		 *
		 * @since 2.3.0
		 * @param bool    $feeds Add a feed for post type date archives. Default true
		 */
		$this->feeds = apply_filters( "cptda_date_archives_feed", true );

		$this->setup_archive_rewrite_rules();
	}


	/**
	 * Set up date archive rewrite rules
	 *
	 * @since 2.3.0
	 * @return void
	 */
	private function setup_archive_rewrite_rules() {

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

		foreach ( (array) $this->post_types as $post_type ) {
			$rules = $rules + $this->get_cpt_rewrite_rules( $post_type );
		}
		return $rules;
	}


	/**
	 * Returns date archive rewrite rules for a custom post type.
	 *
	 * @since 1.0
	 * @param string  $cpt Custom post type name.
	 * @return array Array with custom post type date archive rewrite rules.
	 */
	private function get_cpt_rewrite_rules( $post_type ) {
		$rewrite_rules = array();

		foreach ( $this->get_archive_types() as $archive ) {
			$rules = $this->get_rules( $post_type, $archive );
			$rewrite_rules = array_merge( $rewrite_rules, $rules  );
		}

		return $rewrite_rules;
	}


	/**
	 * Returns rewrite rules
	 *
	 * @since 2.3.0
	 * @param string  $post_type Post type to return rewrite rules for
	 * @param array   $archive   Type of archive to get the rules for (year, month day).
	 */
	private function get_rules( $post_type, $archive ) {
		global $wp_rewrite;

		$rules = array();
		$slug  = $this->plugin->post_type->get_post_type_base_slug( $post_type );

		if ( empty( $slug ) ) {
			return $rules;
		}

		$rule  = $slug . '/' . $archive['rule'];
		$query = $this->get_query_part( $post_type, $archive['vars'] );
		$index = count( $archive['vars'] ) +1;

		if (  $this->archive_has_feed(  $post_type, $archive['vars'] ) ) {
			$rules[ $rule . "/feed/(feed|rdf|rss|rss2|atom)/?$" ] = $query . "&feed=" . $wp_rewrite->preg_index( $index );
			$rules[ $rule . "/(feed|rdf|rss|rss2|atom)/?$" ]      = $query . "&feed=" . $wp_rewrite->preg_index( $index );
		}

		$rules[ $rule . "/page/([0-9]{1,})/?$" ] = $query . "&paged=" . $wp_rewrite->preg_index( $index );
		$rules[ $rule . "/?$" ] = $query;

		return $rules;
	}


	/**
	 * Get rewrite rule query part
	 *
	 * @since 2.3.0
	 * @param string  $post_type Post type to get the query part for.
	 * @param archive $archive   Archive query vars.
	 * @return string            Query part used in rewrite rule.
	 */
	private function get_query_part( $post_type, $archive ) {
		global $wp_rewrite;

		$query = 'index.php?post_type=' . $post_type;

		for ( $i=0; $i < count( $archive ) ; $i++ ) {
			$query .= '&' . $archive[ $i ] . '=' . $wp_rewrite->preg_index( $i + 1 );
		}

		return $query;
	}


	/**
	 * Returns rewrite rules and query vars for all archives.
	 *
	 * @since 2.3.0
	 * @return array Rewrite rules and query vars.
	 */
	public function get_archive_types() {
		return array(
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
	}


	/**
	 * Check if acustom post type has date archive feeds.
	 *
	 * @since 2.3.0
	 * @param string  $post_type Post type.
	 * @param array   $archive   Date archive vars.
	 * @return bool True if post type date archive has a feed.
	 */
	private function archive_has_feed( $post_type, $archive ) {

		if ( !$this->feeds ) {
			return false;
		}

		/**
		 * Filter adding rewrite rules for post type date archives feed.
		 *
		 * @param bool    $add_feed Add a feed for a post type date archive. Default true.
		 */
		$add_feed = apply_filters( "cptda_{$post_type}_date_archives_feed", true );

		if ( $add_feed ) {

			end( $archive );
			$key = key( $archive );
			$date_type = str_replace( 'num', '', $archive[ $key ] );

			/**
			 * Filter adding feed for date type day, month or year archive feed.
			 *
			 * @param bool    $add_feed Add a feed for the {$date_type} date archive. Default true.
			 */
			$add_feed = apply_filters( "cptda_{$post_type}_{$date_type}_archives_feed", true );
		}

		return $add_feed;
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
