<?php

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
		CPT_DATE_ARCHIVES_PLUGIN_URL . "includes/assets/js/blocks/index.js",
		array( 'wp-blocks', 'wp-i18n', 'wp-url', 'wp-element', 'wp-data', 'wp-api-fetch', 'wp-editor', 'wp-components' )
	);

	wp_localize_script( 'cptda-editor-block', 'cptda_data',
		array(
			'post_type' => cptda_get_post_types( 'labels' ),
		)
	);
}

add_action( 'init', 'cptda_register_blocks' );

/**
 * Registers blocks on server.
 *
 * @since 2.6.2
 */
function cptda_register_blocks() {
	register_block_type(
		'cptda/calendar',
		array(
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
			'render_callback' => 'cptda_render_block_calendar',
		)
	);

	register_block_type(
		'cptda/recent-posts',
		array(
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
			'render_callback' => 'cptda_render_block_recent_posts',
		)
	);

	register_block_type(
		'cptda/archives',
		array(
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
				'post_type' => array(
					'type' => 'string',
				),
			),
			'render_callback' => 'cptda_render_block_archives',
		)
	);
}

/**
 * Renders the `cptda/calendar` block on server.
 *
 * @since 2.6.2
 *
 * @param array $args The block arguments.
 * @return string Returns the block content.
 */
function cptda_render_block_calendar( $args ) {
	global $monthnum, $year;

	$previous_monthnum = $monthnum;
	$previous_year     = $year;
	$post_type = $args['post_type'];

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

	$class = cptda_get_block_classes( $args, 'wp-block-calendar' );
	$class .= ' cptda-block-calendar';

	$output = sprintf(
		'<div class="%1$s">%2$s</div>',
		esc_attr( $class ),
		cptda_get_calendar( $post_type, true, false )
	);

	// phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
	$monthnum = $previous_monthnum;
	// phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
	$year = $previous_year;

	return $output;
}

/**
 * Renders the `cptda/recent-posts` block on server.
 *
 * @since 2.6.2
 *
 * @param array $args The block arguments.
 * @return string Returns the block content.
 */
function cptda_render_block_recent_posts( $args ) {
	$args = cptda_sanitize_recent_posts_settings( $args );
	$post_type = $args['post_type'];

	$query_args = cptda_get_recent_posts_query( $args );

	/** This filter is documented in wp-includes/widgets/class-wp-widget-archives.php */
	$query_args = apply_filters( 'cptda_block_recent_posts_args', $query_args );
	$query_args['post_type'] = $post_type;

	$recent_posts = get_posts( $query_args );

	// Add block classes
	$args['class'] = 'wp-block-recent-posts';

	return cptda_get_recent_posts_html( $recent_posts, $args );
}

/**
 * Renders the `cptda/archives` block on server.
 *
 * @since 2.6.2
 *
 * @see WP_Widget_Archives
 *
 * @param array $args The block arguments.
 *
 * @return string Returns the post content with archives added.
 */
function cptda_render_block_archives( $args ) {
	$args = cptda_validate_archive_settings( $args );
	$show_post_count = ! empty( $args['show_post_count'] );

	$args['class'] = 'wp-block-archives';

	if ( 'option' === $args['format'] ) {
		$args = apply_filters( 'cptda_block_archives_dropdown_args', $args );
	} else {
		$args = apply_filters( 'cptda_block_archives_args', $args );
	}

	return cptda_get_archives_html( $args );
}
