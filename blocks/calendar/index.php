<?php
/**
 * Server-side rendering of the `cptda/calendar` block.
 *
 * @package Custom_Post_Type_Date_Archives
 */

add_action( 'enqueue_block_editor_assets', 'cptda_block_editor_assets' );

/**
 * Enqueue Gutenberg block assets for backend editor.
 *
 * `wp-blocks`: includes block type registration and related functions.
 * `wp-element`: includes the WordPress Element abstraction for describing the structure of your blocks.
 * `wp-i18n`: To internationalize the block's text.
 *
 * @since 2.6.2
 */
function cptda_block_editor_assets() {
	wp_enqueue_script(
		'cptda-editor-block', // Handle.
		CPT_DATE_ARCHIVES_PLUGIN_URL . "includes/assets/js/calendar/index.js",
		array( 'wp-blocks', 'wp-i18n', 'wp-url', 'wp-element', 'wp-data', 'wp-api-fetch', 'wp-editor', 'wp-components' )
	);

	wp_localize_script( 'cptda-editor-block', 'cptda_data',
		array(
			'post_type' => cptda_get_post_types( 'labels' ),
			'post'      => __( 'Post', '' ),
		)
	);
}


/**
 * Renders the `cptda/calendar` block on server.
 *
 * @since 2.6.2
 *
 * @param array $attributes The block attributes.
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
 *
 * @since 2.6.2
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
				'post_type' => array(
					'type' => 'string',
				),
			),
			'render_callback' => 'cptda_render_block_calendar',
		)
	);
}

add_action( 'init', 'cptda_register_block_calendar' );
