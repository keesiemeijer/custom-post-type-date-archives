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

// Exit if accessed directly.
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
	 * Constructor
	 */
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

		$plugin = cptda_date_archives();

		$this->post_types = $plugin->post_type->get_post_types( 'names' );

		if ( empty( $this->post_types ) ) {
			return;
		}

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

		if ( ! is_admin() && $flush && $this->is_new_rewrite_rules() ) {
			// New cpt date archive rewrite rules found.
			$this->flush_rules();
		}
	}

	/**
	 * Adds all custom post types date archive rewrite rules to the current rewrite rules.
	 *
	 * @since 1.0
	 * @param object $wp_rewrite WP_Rewrite object.
	 * @return object WP_Rewrite object.
	 */
	public function generate_rewrite_rules( $wp_rewrite ) {
		$rules = $this->get_rewrite_rules();
		if ( is_array( $wp_rewrite->rules ) ) {
			$wp_rewrite->rules = $rules + $wp_rewrite->rules;
		}
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
			$post_type_obj = get_post_type_object( $post_type );

			if ( isset( $post_type_obj->rewrite ) && ( false !== $post_type_obj->rewrite ) ) {
				$rules = $rules + $this->get_rules( $post_type );
			}
		}
		return $rules;
	}

	/**
	 * Returns rewrite rules
	 *
	 * @since 2.3.0
	 * @param string $post_type Post type to return rewrite rules for.
	 */
	private function get_rules( $post_type ) {
		global $wp_rewrite;

		$feeds           = $this->archive_has_feed( $post_type );
		$date_permastuct = $this->get_date_permastruct( $post_type );

		if ( ! $date_permastuct ) {
			return array();
		}

		$date_rewrite = $wp_rewrite->generate_rewrite_rules( $date_permastuct , EP_DATE, true, $feeds );

		// Add post type to query vars.
		foreach ( $date_rewrite as $rule => $vars ) {
			$date_rewrite[ $rule ] = str_replace( "{$wp_rewrite->index}?", "{$wp_rewrite->index}?post_type={$post_type}&", $vars );
		}

		return $date_rewrite;
	}

	/**
	 * Returns date permastruct for a custom post type
	 *
	 * @since 2.3.0
	 *
	 * @param string $post_type Post type.
	 */
	private function get_date_permastruct( $post_type ) {
		$cpt_rewrite = new CPTDA_CPT_Rewrite( $post_type );
		return $cpt_rewrite->get_date_permastruct();
	}

	/**
	 * Check if acustom post type has date archive feeds.
	 *
	 * @since 2.3.0
	 * @param string $post_type Post type.
	 * @return bool True if post type date archive has a feed.
	 */
	private function archive_has_feed( $post_type ) {

		/**
		 * Filter whether feeds are added to date archives.
		 *
		 * @since 2.3.0
		 * @param bool $feed Add a feed for post type date archives. Default true
		 */
		$feed = (bool) apply_filters( 'cptda_date_archives_feed', true );

		if ( ! $feed ) {
			return false;
		}

		/**
		 * Filter adding rewrite rules for post type date archives feed.
		 *
		 * @param bool $feed Add a feed for a post type date archive. Default true.
		 */
		$feed = (bool) apply_filters( "cptda_{$post_type}_date_archives_feed", true );

		return $feed;
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
		$wp_rewrite = $wp_rewrite_temp;

		// Check if the rewrite rule or query exists.
		foreach ( $rules as $rule => $query ) {
			if ( ! in_array( $query, $rewrite_rules ) || ! key_exists( $rule, $rewrite_rules ) ) {
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

		$wp_rewrite->flush_rules();
	}

}

$cptda_rewrite = new CPTDA_Rewrite();
