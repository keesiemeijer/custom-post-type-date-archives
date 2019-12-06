<?php
/**
 * Editor blocks.
 *
 * @package     Custom_Post_Type_Date_Archives
 * @subpackage  Editor_Block
 * @copyright   Copyright (c) 2019, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.7.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_loaded', 'cptda_block_editor_init', 20 );

/**
 * Enqueue Gutenberg block assets for backend editor.
 *
 * `wp-blocks`: includes block type registration and related functions.
 * `wp-element`: includes the WordPress Element abstraction for describing the structure of your blocks.
 * `wp-i18n`: To internationalize the block's text.
 *
 * @since 2.7.0
 */
function cptda_block_editor_init() {
	if ( ! function_exists( '\register_block_type' ) ) {
		return;
	}

	// automatically load dependencies and version
	$asset_file = include CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/assets/js/blocks/index.asset.php';

	// This script is loaded when the block editor is loading on the current screen
	// See wp_enqueue_registered_block_scripts_and_styles().
	wp_register_script(
		'cptda-editor-block-script', // Handle.
		CPT_DATE_ARCHIVES_PLUGIN_URL . "includes/assets/js/blocks/index.js",
		$asset_file['dependencies']
	);

	wp_localize_script( 'cptda-editor-block-script', 'cptda_data',
		array(
			'post_type' => cptda_get_post_types( 'labels' ),
			'public'    => cptda_get_public_post_types(),
		)
	);

	if ( function_exists( 'wp_set_script_translations' ) ) {
		wp_set_script_translations( 'cptda-editor-block-script', 'custom-post-type-date-archives' );
	}

	wp_register_style(
		'cptda-editor-block-style',
		CPT_DATE_ARCHIVES_PLUGIN_URL . "includes/assets/css/blocks/editor-styles.css",
		array( )
	);

	cptda_register_blocks();
}

/**
 * Registers blocks on server.
 *
 * @since 2.7.0
 */
function cptda_register_blocks() {
	register_block_type(
		'cptda/calendar',
		array(
			'render_callback' => 'cptda_render_block_calendar',
			'editor_script'   => 'cptda-editor-block-script',
			'editor_style'    => 'cptda-editor-block-style',
			'attributes' => array(
				'align' => array(
					'type' => 'string',
					'enum' => array( 'left', 'center', 'right', 'wide', 'full' ),
				),
				'className' => array(
					'type' => 'string',
				),
				'month' => array(
					'type' => 'integer',
				),
				'year' => array(
					'type' => 'integer',
				),
				'post_type' => array(
					'type' => 'string',
				),
			),
		)
	);

	register_block_type(
		'cptda/latest-posts',
		array(
			'render_callback' => 'cptda_render_block_recent_posts',
			'editor_script'   => 'cptda-editor-block-script',
			'editor_style'    => 'cptda-editor-block-style',
			'attributes' => array(
				'align' => array(
					'type' => 'string',
					'enum' => array( 'left', 'center', 'right', 'wide', 'full' ),
				),
				'className' => array(
					'type' => 'string',
				),
				'number' => array(
					'type'    => 'number',
					'default' => 5,
				),
				'show_date' => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'include' => array(
					'type'    => 'string',
					'default' => 'all',
				),
				'message' => array(
					'type'    => 'string',
					'default' => '',
				),
				'post_type' => array(
					'type' => 'string',
				),

			),
		)
	);

	register_block_type(
		'cptda/archives',
		array(
			'render_callback' => 'cptda_render_block_archives',
			'editor_script'   => 'cptda-editor-block-script',
			'editor_style'    => 'cptda-editor-block-style',
			'attributes' => array(
				'align'             => array(
					'type' => 'string',
					'enum' => array( 'left', 'center', 'right', 'wide', 'full' ),
				),
				'className'         => array(
					'type' => 'string',
				),
				'limit' => array(
					'type'    => 'number',
					'default' => 5,
				),
				'type' => array(
					'type'    => 'string',
					'default' => 'monthly',
				),
				'format' => array(
					'type'    => 'string',
					'default' => 'html',
				),
				'order' => array(
					'type'    => 'string',
					'default' => 'DESC',
				),
				'show_post_count' => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'displayAsDropdown' => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'post_type' => array(
					'type' => 'string',
				),
			),
		)
	);
}

/**
 * Renders the `cptda/calendar` block on server.
 *
 * @since 2.7.0
 *
 * @param array $args The block arguments.
 * @return string Calendar block HTML.
 */
function cptda_render_block_calendar( $args ) {
	global $monthnum, $year;

	/**
	 * Filter the arguments for the calendar block before rendering.
	 *
	 * @since 2.7.0
	 *
	 * @param array $args Array of arguments used to retrieve the calendar.
	 */
	$args = apply_filters( 'cptda_block_calendar_args', $args );

	if ( ! isset( $args['post_type'] ) ) {
		return '';
	}

	$previous_monthnum = $monthnum;
	$previous_year     = $year;

	if ( isset( $args['month'] ) && isset( $args['year'] ) ) {
		$permalink_structure = get_option( 'permalink_structure' );
		if (
			strpos( $permalink_structure, '%monthnum%' ) !== false &&
			strpos( $permalink_structure, '%year%' ) !== false
		) {
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
			$monthnum = $args['month'];
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
			$year = $args['year'];
		}
	}

	$args['class'] = 'wp-block-calendar';
	$output = cptda_get_calendar_html( $args );

	// phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
	$monthnum = $previous_monthnum;
	// phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
	$year = $previous_year;

	return $output;
}

/**
 * Renders the `cptda/recent-posts` block on server.
 *
 * @since 2.7.0
 *
 * @param array $args The block arguments.
 * @return string Recent posts block HTML.
 */
function cptda_render_block_recent_posts( $args ) {
	$args         = cptda_validate_recent_posts_settings( $args );
	$query_args   = cptda_get_recent_posts_query( $args );

	/**
	 * Filter the arguments for the Recent Posts block before rendering.
	 *
	 * @since 2.7.0
	 *
	 * @param array $query_args Array of arguments used to retrieve the recent posts.
	 */
	$query_args   = apply_filters( 'cptda_block_latest_posts_args', $query_args );

	$recent_posts  = cptda_get_recent_posts( $query_args );
	$args['class'] = 'wp-block-latest-posts';

	return cptda_get_recent_posts_html( $recent_posts, $args );
}

/**
 * Renders the `cptda/archives` block on server.
 *
 * @since 2.7.0
 *
 * @param array $args The block arguments.
 * @return string Archives block HTML.
 */
function cptda_render_block_archives( $args ) {
	$args = cptda_validate_archive_settings( $args );

	/**
	 * Filter the arguments for the Archives block before rendering.
	 *
	 * @since 2.7.0
	 *
	 * @param array $args Array of archives block arguments.
	 */
	$args = apply_filters( 'cptda_block_archives_args', $args );

	$args['class'] = 'wp-block-archives';
	return cptda_get_archives_html( $args );
}
