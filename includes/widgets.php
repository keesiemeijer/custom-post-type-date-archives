<?php
/**
 * Widgets.
 *
 * @package     Custom Post Type Date Archives
 * @subpackage  Classes/Widgets
 * @copyright   Copyright (c) 2014, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load widget classes.
require_once CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/widgets/class-cptda-widget-archives.php';
require_once CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/widgets/class-cptda-widget-calendar.php';
require_once CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/widgets/class-cptda-widget-recent-posts.php';

add_action( 'widgets_init',  'cptda_register_widgets' );

/**
 * Register Widgets.
 * Unregisters the default WP core widgets when the
 * cptda_replace_default_core_widgets filter is set to true.
 *
 * @since  1.0
 * @access public
 * @return void
 */
function cptda_register_widgets() {

	/**
	 * Wheter to replace WordPress core default widgets with plugin widgets.
	 *
	 * @since 2.3.2
	 * @param bool $replace_widgets Replace WordPress core default widgets if true. Default true
	 */
	$replace_widgets = apply_filters( 'cptda_replace_default_core_widgets', true );

	if ( $replace_widgets ) {
		/* Unregister the default WordPress widgets. */
		unregister_widget( 'WP_Widget_Archives' );
		unregister_widget( 'WP_Widget_Calendar' );
		unregister_widget( 'WP_Widget_Recent_Posts' );
	} else {
		$plugin = cptda_date_archives();
		$plugin->replace_widgets = false;
	}

	/* Register the archives widget. */
	register_widget( 'CPTDA_Widget_Archives' );

	/* Register the calendar widget. */
	register_widget( 'CPTDA_Widget_Calendar' );

	/* Register the calendar widget. */
	register_widget( 'CPTDA_Widget_Recent_Posts' );
}
