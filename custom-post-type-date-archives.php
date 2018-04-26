<?php
/*
Plugin Name: Custom Post Type Date Archives
Version:  2.5.1
Plugin URI: https://wordpress.org/plugins/custom-post-type-date-archives
Description: This plugin allows you to add date archives to custom post types. It also adds extra options to the archive, calendar and recent posts widget.
Author: keesiemijer
Author URI: https://keesiemeijer.wordpress.com
Text Domain: custom-post-type-date-archives
Domain Path: languages
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

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Custom_Post_Type_Date_Archives' ) ) :

	/**
	 * Main Custom_Post_Type_Date_Archives Class
	 *
	 * @since 1.0
	 */
	final class Custom_Post_Type_Date_Archives {

	/**
	 * Plugin instance.
	 *
	 * @var Plugin instance
	 * @since 1.0
	 */
	private static $instance;

	/**
	 * Post type.
	 *
	 * @var Post type object
	 * @since 1.0
	 */
	public $post_type;

	/**
	 * Rewrite object
	 *
	 * @var Rewrite object
	 * @since 1.0
	 */
	public $rewrite;

	/**
	 * Replace default WP core widgets.
	 *
	 * @since 2.3.2
	 * @var bool
	 */
	public $replace_widgets = true;

	/**
	 * Main Custom_Post_Type_Date_Archives Instance
	 *
	 * Insures that only one instance of CS_Custom_Post_Type_Date_Archives exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 1.0
	 * @uses Custom_Post_Type_Date_Archives::setup_constants() Setup the constants needed
	 * @uses Custom_Post_Type_Date_Archives::includes() Include the required files
	 * @uses Custom_Post_Type_Date_Archives::load_textdomain() load the language files
	 * @return Custom_Post_Type_Date_Archives instance.
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Custom_Post_Type_Date_Archives ) ) {
			self::$instance = new Custom_Post_Type_Date_Archives;
			self::$instance->setup_constants();
			self::$instance->includes();
			self::$instance->load_textdomain();
			self::$instance->post_type = new CPTDA_Post_Types();

			if ( is_admin() ) {
				new CPTDA_Admin();
			}
		}

		return self::$instance;
	}


	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 1.0
	 * @access protected
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'content-shortcuts' ), '1.0' );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @since 1.0
	 * @access protected
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'content-shortcuts' ), '1.0' );
	}

	/**
	 * Setup plugin constants
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function setup_constants() {

		// Plugin version
		if ( ! defined( 'CPT_DATE_ARCHIVES_VERSION' ) ) {
			define( 'CPT_DATE_ARCHIVES_VERSION', '2.5.1' );
		}

		// Plugin Folder Path
		if ( ! defined( 'CPT_DATE_ARCHIVES_PLUGIN_DIR' ) ) {
			define( 'CPT_DATE_ARCHIVES_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL
		if ( ! defined( 'CPT_DATE_ARCHIVES_PLUGIN_URL' ) ) {
			define( 'CPT_DATE_ARCHIVES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Root File
		if ( ! defined( 'CPT_DATE_ARCHIVES_PLUGIN_FILE' ) ) {
			define( 'CPT_DATE_ARCHIVES_PLUGIN_FILE', __FILE__ );
		}
	}

	/**
	 * Include required files
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function includes() {
		require_once CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/cpt-rewrite.php';
		require_once CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/functions.php';
		require_once CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/calendar.php';
		require_once CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/deprecated.php';
		require_once CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/link-template.php';
		require_once CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/post_type.php';
		require_once CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/widgets.php';
		require_once CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/settings.php';


		if ( ! is_admin() ) {
			require_once CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/rewrite.php';
			require_once CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/query.php';
		} else {
			require_once CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/admin.php';
		}
		require_once CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/install.php';
	}

	/**
	 * Loads the plugin language files
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function load_textdomain() {
		$dir = dirname( plugin_basename( CPT_DATE_ARCHIVES_PLUGIN_FILE ) ) . '/languages/';
		load_plugin_textdomain( 'custom-post-type-date-archives', '', $dir );
	}


}
endif;

/**
 * Returns the Custom_Post_Type_Date_Archives instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $instance = cptda_date_archives(); ?>
 *
 * @since 1.0
 * @return object Custom_Post_Type_Date_Archives Instance.
 */
function cptda_date_archives() {
	return Custom_Post_Type_Date_Archives::instance();
}

// Instantiate plugin
cptda_date_archives();
