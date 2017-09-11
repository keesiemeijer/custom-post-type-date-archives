<?php
/**
 * Class for managing admin settings.
 *
 * @package     Custom Post Type Date Archives
 * @subpackage  Classes/Settings
 * @copyright   Copyright (c) 2014, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get plugin settings.
 *
 * @since 1.0
 * @author keesiemeijer
 */
class CPTDA_Settings {

	private function get_default_settings() {
		return array(
			'date_archives'        => array(),
			'publish_future_posts' => array(),
		);
	}

	/**
	 * Returns the settings for the current admin page post type.
	 *
	 * @param string $post_type Current admin page post type.
	 * @return array Current post type settings
	 */
	public function get_settings() {

		$defaults = $this->get_default_settings();

		$db_settings = get_option( 'custom_post_type_date_archives' );

		if ( empty( $db_settings ) || ! is_array( $db_settings ) ) {
			$db_settings = $defaults;
		}

		$db_settings = array_merge( $defaults, $db_settings );

		return $this->sanitize_settings( $db_settings );
	}

	/**
	 * Merge settings from a post type with the old settings
	 *
	 * @param array  $old_settings Old settings.
	 * @param array  $new_settings New settings.
	 * @param string $post_type    Post type of new settings to merge with old settings.
	 * @return array               Settings with new settings merged.
	 */
	public function merge_settings( $old_settings, $new_settings, $post_type ) {

		foreach ( (array) $old_settings as $key => $setting ) {
			unset( $old_settings[ $key ][ $post_type ] );
			if ( isset( $new_settings[ $key ] ) ) {
				$old_settings[ $key ][ $post_type ] = 1;
			}
		}

		return $this->sanitize_settings( $old_settings );
	}

	/**
	 * Sanitizes admin settings.
	 *
	 * @param array $settings Admin settings.
	 * @return array Sanitized admin settings.
	 */
	private function sanitize_settings( $settings ) {
		// Remove values not in defaults.
		$settings = array_intersect_key( $settings, $this->get_default_settings() );

		// Removes invalid post types (e.g. post types that no longer exist).
		$settings = $this->remove_invalid_post_types( $settings );

		return $settings;
	}


	/**
	 * Remove invalid admin post types from settings.
	 * e.g. Removes post types that no longer exist or don't have an archive (anymore).
	 *
	 * @param array $settings Settings.
	 * @return array Settings with invalid post types removed.
	 */
	private function remove_invalid_post_types( $settings ) {

		$post_types = cptda_get_post_types( 'names', 'admin' );

		foreach ( (array) $settings as $key => $setting ) {
			if ( ! is_array( $setting ) || empty( $setting ) ) {
				continue;
			}

			foreach ( $setting as $post_type => $value ) {
				if ( ! in_array( $post_type , $post_types ) ) {
					unset( $settings[ $key ][ $post_type ] );
				}
			}
		}

		return $settings;
	}
}
