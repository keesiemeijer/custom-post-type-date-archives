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

// Exit if accessed directly
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

	private $post_types;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'cptda_admin_menu' ) );
	}

	/**
	 * Adds a settings page for this plugin.
	 */
	public function cptda_admin_menu() {
		$this->post_types = cptda_get_admin_post_types();

		/**
		 * Filter whether to add admin pages to custom post type menus
		 *
		 * @since 2.1.0
		 * @param bool    $pages Add admin pages to custom post types. Default true
		 */
		$pages = apply_filters( 'cpda_add_admin_pages', true );
		if ( !$pages ) {
			return;
		}

		foreach ( $this->post_types as $post_type => $label ) {

			/**
			 * Filter whether to add an admin page to a specific custom post type
			 *
			 * @since 2.1.0
			 * @param bool    $page Add an admin page for a specific post type. Default true
			 */
			$page = apply_filters( "cpda_add_admin_page_{$post_type}", true );

			if ( !$page ) {
				continue;
			}

			add_submenu_page(
				'edit.php?post_type=' . urlencode( $post_type ),
				__( 'Custom Post Type Date Archives', 'custom-post-type-date-archives' ),
				__( 'Date Archives', 'custom-post-type-date-archives' ),
				'manage_options',
				'date-archives-' . urlencode( $post_type ),
				array( $this, 'admin_menu' )
			);
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
	 * @param string  $post_type Current admin page post type.
	 * @return array Current post type settings
	 */
	public function get_settings( $post_type = '' ) {

		$defaults = array(
			'date_archives'        => array(),
			'publish_future_posts' => array(),
		);

		$old_settings = get_option( 'custom_post_type_date_archives' );

		if ( empty( $old_settings ) || !is_array( $old_settings ) ) {
			$old_settings = $defaults;
		}

		$old_settings = array_merge( $defaults, $old_settings );

		if ( isset( $_SERVER['REQUEST_METHOD'] ) && ( 'POST' === $_SERVER['REQUEST_METHOD'] ) ) {

			check_admin_referer( "custom_post_type_date_archives_{$post_type}_nonce" );

			$_POST    = stripslashes_deep( $_POST );
			$settings = $this->merge_settings( $old_settings, (array) $_POST, $post_type );
			$message  = __( 'Settings Saved', 'custom-post-type-date-archives' );

			add_settings_error ( 'update', 'update', $message, 'updated' );
		} else {
			$settings = $old_settings;
		}

		// Remove values not in defaults
		$settings = array_intersect_key( $settings, $defaults );

		// Removes invalid post types (e.g. post types that no longer exist)
		$settings = $this->remove_invalid_post_types( $settings );

		if ( $old_settings != $settings ) {
			update_option( 'custom_post_type_date_archives', $settings );
		}

		return $settings;
	}


	/**
	 * Merge settings from a current post type admin page with the old settings
	 *
	 * @param array   $old_settings Old settings.
	 * @param array   $settings     New settings.
	 * @param string  $post_type    Current admin page post type.
	 * @return array               Settings with new settings merged.
	 */
	public function merge_settings( $old_settings, $settings, $post_type ) {

		foreach ( $old_settings as $key => $setting ) {
			unset( $old_settings[ $key ][ $post_type ] );
			if ( isset( $settings[ $key ] ) ) {
				$old_settings[ $key ][ $post_type] = 1;
			}
		}
		return $old_settings;
	}


	/**
	 * Remove invalid post types from settings.
	 * e.g. Removes post types that no longer exist or don't have an archive (anymore).
	 *
	 * @param array   $settings Settings.
	 * @return array Settings with invalid post types removed.
	 */
	private function remove_invalid_post_types( $settings ) {

		foreach ( $settings as $key => $setting ) {
			if ( !is_array( $setting ) || empty( $setting ) ) {
				continue;
			}

			foreach ( $setting as $post_type => $value ) {
				if ( !in_array( $post_type , array_keys( $this->post_types ) ) ) {
					unset( $settings[ $key ][ $post_type ] );
				}
			}
		}

		return $settings;
	}


	/**
	 * Admin page output.
	 */
	public function admin_menu() {

		echo '<div class="wrap">';
		echo '<h1>' . __( 'Date Archives', 'custom-post-type-date-archives' ) . '</h1>';

		$post_types  = $this->post_types;
		$post_type   = $this->get_current_post_type();
		$label       = isset( $post_types[ $post_type ] ) ? $post_types[ $post_type ] : $post_type;

		if ( !$post_type ) {
			$error = __( 'Could not find the post type for the current screen.', 'custom-post-type-date-archives' );
			add_settings_error ( 'post_type', 'post_type', $error, 'error' );
		}

		$settings = $this->get_settings( $post_type );
		settings_errors();

		if ( isset( $error ) ) {
			return;
		}

		include 'partials/admin-form.php';
		echo '<p>' . __( 'This page is generated by the Custom Post Type Date Archives plugin.', 'custom-post-type-date-archives' );
		echo '</div>';
	}

}
