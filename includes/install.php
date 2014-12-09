<?php
/**
 * Install
 *
 * @package     Custom Post Type Date Archives
 * @subpackage  Functions/Install
 * @copyright   Copyright (c) 2014, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fired during plugin deactivation.
 * Removes the rewrite rules for custom post type date archives.
 *
 * @since 1.0
 * @return void
 */
function cptda_deactivate_plugin() {
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
}

register_deactivation_hook( CPT_DATE_ARCHIVES_PLUGIN_FILE, 'cptda_deactivate_plugin' );


/**
 * Includes the date template file on custom post type date archives.
 *
 * @since 1.0
 * @param string  $template Template file.
 * @return string Template file.
 */
function cptda_date_template_include( $template ) {

	if ( cptda_is_cpt_date() ) {

		$_template = get_date_template();
		$_template = !empty( $_template ) ? $_template : get_post_type_archive_template();

		if ( !empty( $_template ) ) {
			return apply_filters( 'cptda_date_template_include', $_template );
		}
	}

	return $template;
}

add_filter( 'template_include', 'cptda_date_template_include' );