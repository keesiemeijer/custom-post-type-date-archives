<?php
/**
 * Admin Pages.
 *
 * @package     Custom Post Type Date Archives
 * @subpackage  Classes/Admin
 * @copyright   Copyright (c) 2016, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates admin pages to add date archives for custom post types.
 *
 * @since 2.1.0
 * @author keesiemeijer
 */
class CPTDA_Admin {

	/**
	 * Custom Post Types.
	 *
	 * @var array
	 */
	private $post_types;

	/**
	 * Flush the rewrite rules when old settings are updated.
	 *
	 * @var bool
	 */
	private $flush_rewrite;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'cptda_admin_menu' ) );
		add_action( 'shutdown', array( $this, 'shutdown' ) );
	}

	/**
	 * Adds a settings page for this plugin.
	 */
	public function cptda_admin_menu() {
		$this->post_types = cptda_get_post_types( 'labels', 'admin' );

		/**
		 * Filter whether to add admin pages to custom post type menus
		 *
		 * @since 2.1.0
		 * @param bool $pages Add admin pages to custom post types. Default true
		 */
		$pages = apply_filters( 'cpda_add_admin_pages', true );
		if ( ! $pages ) {
			return;
		}

		foreach ( $this->post_types as $post_type => $label ) {

			/**
			 * Filter whether to add an admin page to a specific custom post type
			 *
			 * @since 2.1.0
			 * @param bool $page Add an admin page for a specific post type. Default true
			 */
			$page = apply_filters( "cpda_add_admin_page_{$post_type}", true );

			if ( ! $page ) {
				continue;
			}

			$hook = add_submenu_page(
				'edit.php?post_type=' . $post_type,
				__( 'Custom Post Type Date Archives', 'custom-post-type-date-archives' ),
				__( 'Date Archives', 'custom-post-type-date-archives' ),
				'manage_options',
				'date-archives-' . $post_type,
				array( $this, 'admin_menu' )
			);

			// Adds a help tab when admin page loads.
			add_action( 'load-' . $hook, array( $this, 'add_help_tab' ) );
		}
	}

	/**
	 * Returns the post type for the current admin page.
	 *
	 * @return string|bool Post type or false
	 */
	private function get_current_post_type() {
		$screen = get_current_screen();

		if ( isset( $screen->parent_base ) && ( 'edit' !== $screen->parent_base ) ) {
			return false;
		}

		if ( isset( $screen->post_type ) && $screen->post_type ) {
			return $screen->post_type;
		}

		return false;
	}

	/**
	 * Returns the settings for the current admin page post type.
	 *
	 * @param string $post_type Current admin page post type.
	 * @return array Current post type settings
	 */
	public function get_settings( $post_type = '' ) {

		$settings_obj = new CPTDA_Settings();
		$old_settings = $settings_obj->get_settings();

		if ( isset( $_SERVER['REQUEST_METHOD'] ) && ( 'POST' === $_SERVER['REQUEST_METHOD'] ) ) {
			check_admin_referer( "custom_post_type_date_archives_{$post_type}_nonce" );
			$new_settings = stripslashes_deep( $_POST );

			$message  = __( 'Settings Saved', 'custom-post-type-date-archives' );
			add_settings_error( 'update', 'update', $message, 'updated' );

			$settings = $settings_obj->merge_settings( $old_settings, (array) $new_settings, $post_type );
		} else {
			$settings = $old_settings;
		}

		// Flush rewrite rules on shutdown action if date archives were removed.
		$flush = isset( $settings['date_archives'][ $post_type ] ) ? false : true;
		if ( $flush && isset( $old_settings['date_archives'][ $post_type ] ) ) {
			$this->flush_rewrite = true;
		}

		// Save new settings.
		if ( $old_settings != $settings ) {
			update_option( 'custom_post_type_date_archives', $settings );
		}

		return $settings;
	}

	/**
	 * Admin page output.
	 */
	public function admin_menu() {
		$post_types = $this->post_types;
		$post_type  = $this->get_current_post_type();
		$label      = isset( $post_types[ $post_type ] ) ? $post_types[ $post_type ] : $post_type;

		if ( ! $post_type ) {
			$error = __( 'Could not find the post type for the current screen.', 'custom-post-type-date-archives' );
			add_settings_error( 'post_type', 'post_type', $error, 'error' );
			include 'partials/admin-error.php';
			return;
		}

		$settings = $this->get_settings( $post_type );

		include 'partials/admin-form.php';
	}

	/**
	 * Adds a help section on admin pages
	 */
	public function add_help_tab() {
		global $wp_rewrite;

		$post_type = $this->get_current_post_type();
		if ( ! $post_type ) {
			return;
		}

		$label = isset( $this->post_types[ $post_type ] ) ? $this->post_types[ $post_type ] : $post_type;

		// Current date.
		$date = getdate();

		// Get date from last post.
		$post = get_posts( "post_type={$post_type}&posts_per_page=1" );
		if ( isset( $post[0]->post_date ) && $post[0]->post_date ) {
			$date = getdate( strtotime( $post[0]->post_date ) );
		}

		// Get day rewrite permastruct.
		$cpt_rewrite = new CPTDA_CPT_Rewrite( $post_type );
		$daylink = $cpt_rewrite->get_day_permastruct();

		// Create example link.
		if ( ! empty( $daylink ) ) {
			$daylink = str_replace( '%year%', $date['year'], $daylink );
			$daylink = str_replace( '%monthnum%', zeroise( intval( $date['mon'] ), 2 ), $daylink );
			$daylink = str_replace( '%day%', zeroise( intval( $date['mday'] ), 2 ), $daylink );
			$sample_day_link = home_url( user_trailingslashit( $daylink, 'day' ) );
		} else {
			$daylink = home_url( '?m=' . $date['year'] . zeroise( $date['mon'], 2 ) . zeroise( $date['mday'], 2 ) );
			$sample_day_link = add_query_arg( 'post_type', $post_type, $daylink );
		}

		ob_start();
		include 'partials/admin-help.php';
		$content = ob_get_clean();

		$screen = get_current_screen();

		// Add help tab.
		$screen->add_help_tab(
			array(
				'id' => 'cptda_date_archive',
				'title' => __( 'Date Archives' ),
				'content' => $content,
			)
		);
	}

	/**
	 * Flush rules if date archives are removed from a post type
	 *
	 * @since 2.2.1
	 * @return void
	 */
	public function shutdown() {
		global $wp_rewrite;
		if ( true === $this->flush_rewrite ) {
			$wp_rewrite->flush_rules();
		}
	}

}
