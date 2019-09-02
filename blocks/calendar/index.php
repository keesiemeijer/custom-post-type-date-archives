<?php
/**
 * Server-side rendering of the `cptda/calendar` block.
 *
 * @package WordPress
 */

add_action( 'enqueue_block_editor_assets', 'cptda_block_editor_assets' );

/**
 * Enqueue Gutenberg block assets for backend editor.
 *
 * `wp-blocks`: includes block type registration and related functions.
 * `wp-element`: includes the WordPress Element abstraction for describing the structure of your blocks.
 * `wp-i18n`: To internationalize the block's text.
 *
 * @since 2.4.2
 */
function cptda_block_editor_assets() {

	// // if ( ! cptda_has_editor_block_support() ) {
	// // 	return;
	// // }

	// // $plugin = cptda_plugin();

	// // Use un-minified Javascript when in debug mode.
	// $debug = $plugin && $plugin->plugin_supports( 'debug' ) ? '' : '.min';

	// Scripts.
	// 
	wp_enqueue_script(
		'cptda-related-posts-block', // Handle.
		CPT_DATE_ARCHIVES_PLUGIN_URL . "includes/assets/js/calendar/index.js",
		array( 'wp-blocks', 'wp-i18n', 'wp-url', 'wp-element', 'wp-data', 'wp-api-fetch', 'wp-editor', 'wp-components' )
	);

	// // Styles.
	// wp_enqueue_style(
	// 	'rpbt-related-posts-block-css', // Handle.
	// 	CPT_DATE_ARCHIVES_PLUGIN_URL . 'includes/assets/css/editor.css',
	// 	array( 'wp-edit-blocks' )
	// );

	// $order = array(
	// 	'DESC' => __( 'Most terms in common', 'related-posts-by-taxonomy' ),
	// 	'ASC'  => __( 'Least terms in common', 'related-posts-by-taxonomy' ),
	// 	'RAND' => __( 'Randomly', 'related-posts-by-taxonomy' ),
	// );

	// wp_localize_script( 'rpbt-related-posts-block', 'cptda_plugin_data',
	// 	array(
	// 		'post_types'       => $plugin->post_types,
	// 		'taxonomies'       => $plugin->taxonomies,
	// 		'default_tax'      => $plugin->default_tax,
	// 		'all_tax'          => 'cptda_all_tax',
	// 		'formats'          => $plugin->formats,
	// 		'image_sizes'      => $plugin->image_sizes,
	// 		'order'            => $order,
	// 		'preview'          => (bool) $plugin->plugin_supports( 'editor_block_preview' ),
	// 		'html5_gallery'    => (bool) current_theme_supports( 'html5', 'gallery' ),
	// 		'default_category' => absint( get_option( 'default_category' ) ),
	// 	)
	// );
}


/**
 * Renders the `cptda/calendar` block on server.
 *
 * @param array $attributes The block attributes.
 *
 * @return string Returns the block content.
 */
function cptda_render_block_calendar( $attributes ) {
	global $monthnum, $year;

	$previous_monthnum = $monthnum;
	$previous_year     = $year;

	if ( isset( $attributes['month'] ) && isset( $attributes['year'] ) ) {
		$permalink_structure = get_option( 'permalink_structure' );
		if (
			strpos( $permalink_structure, '%monthnum%' ) !== false &&
			strpos( $permalink_structure, '%year%' ) !== false
		) {
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
			$monthnum = $attributes['month'];
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
			$year = $attributes['year'];
		}
	}

	$custom_class_name = empty( $attributes['className'] ) ? '' : ' ' . $attributes['className'];
	$align_class_name  = empty( $attributes['align'] ) ? '' : ' ' . "align{$attributes['align']}";

	$output = sprintf(
		'<div class="%1$s">%2$s</div>',
		esc_attr( 'wp-block-calendar' . $custom_class_name . $align_class_name ),
		get_calendar( true, false )
	);

	// phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
	$monthnum = $previous_monthnum;
	// phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
	$year = $previous_year;

	return $output;
}

/**
 * Registers the `cptda/calendar` block on server.
 */
function cptda_register_block_calendar() {
	register_block_type(
		'cptda/calendar',
		array(
			'attributes'      => array(
				'align'     => array(
					'type' => 'string',
					'enum' => array( 'left', 'center', 'right', 'wide', 'full' ),
				),
				'className' => array(
					'type' => 'string',
				),
				'month'     => array(
					'type' => 'integer',
				),
				'year'      => array(
					'type' => 'integer',
				),
			),
			'render_callback' => 'cptda_render_block_calendar',
		)
	);
}

add_action( 'init', 'cptda_register_block_calendar' );
