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

/**
 * Registers the `cptda/calendar` block on server.
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
			'render_callback' => 'cptda_render_block_latest_posts',
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

add_action( 'init', 'cptda_register_blocks' );


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


function cptda_render_block_latest_posts(){
 	return 'hello';
}

/**
 * Renders the `core/archives` block on server.
 *
 * @see WP_Widget_Archives
 *
 * @param array $attributes The block attributes.
 *
 * @return string Returns the post content with archives added.
 */
function cptda_render_block_archives( $attributes ) {
	$show_post_count = ! empty( $attributes['showPostCounts'] );

	$class = 'wp-block-archives';

	if ( isset( $attributes['align'] ) ) {
		$class .= " align{$attributes['align']}";
	}

	if ( isset( $attributes['className'] ) ) {
		$class .= " {$attributes['className']}";
	}

	if ( ! empty( $attributes['displayAsDropdown'] ) ) {

		$class .= ' wp-block-archives-dropdown';

		$dropdown_id = esc_attr( uniqid( 'wp-block-archives-' ) );
		$title       = __( 'Archives' );

		/** This filter is documented in wp-includes/widgets/class-wp-widget-archives.php */
		$dropdown_args = apply_filters(
			'widget_archives_dropdown_args',
			array(
				'type'            => 'monthly',
				'format'          => 'option',
				'show_post_count' => $show_post_count,
			)
		);

		$dropdown_args['echo'] = 0;

		$archives = wp_get_archives( $dropdown_args );

		switch ( $dropdown_args['type'] ) {
			case 'yearly':
				$label = __( 'Select Year' );
				break;
			case 'monthly':
				$label = __( 'Select Month' );
				break;
			case 'daily':
				$label = __( 'Select Day' );
				break;
			case 'weekly':
				$label = __( 'Select Week' );
				break;
			default:
				$label = __( 'Select Post' );
				break;
		}

		$label = esc_attr( $label );

		$block_content = '<label class="screen-reader-text" for="' . $dropdown_id . '">' . $title . '</label>
	<select id="' . $dropdown_id . '" name="archive-dropdown" onchange="document.location.href=this.options[this.selectedIndex].value;">
	<option value="">' . $label . '</option>' . $archives . '</select>';

		return sprintf(
			'<div class="%1$s">%2$s</div>',
			esc_attr( $class ),
			$block_content
		);
	}

	$class .= ' wp-block-archives-list';

	/** This filter is documented in wp-includes/widgets/class-wp-widget-archives.php */
	$archives_args = apply_filters(
		'widget_archives_args',
		array(
			'type'            => 'monthly',
			'show_post_count' => $show_post_count,
		)
	);

	$archives_args['echo'] = 0;

	$archives = wp_get_archives( $archives_args );

	$classnames = esc_attr( $class );

	if ( empty( $archives ) ) {

		return sprintf(
			'<div class="%1$s">%2$s</div>',
			$classnames,
			__( 'No archives to show.' )
		);
	}

	return sprintf(
		'<ul class="%1$s">%2$s</ul>',
		$classnames,
		$archives
	);
}
