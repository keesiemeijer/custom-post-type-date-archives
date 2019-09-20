<?php
/**
 * Archive Utils
 *
 * @package     Custom_Post_Type_Date_Archives
 * @subpackage  Utils/Archives
 * @copyright   Copyright (c) 2017, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.6.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the default settings for the archives feature.
 *
 * @since 2.6.0
 *
 * @return array Default archives settings.
 */
function cptda_get_archive_settings() {
	return array(
		'post_type'       => 'post',
		'title'           => '',
		'before_title'    => '',
		'after_title'     => '',
		'limit'           => '',
		'offset'          => '',
		'type'            => 'monthly',
		'order'           => 'DESC',
		'format'          => 'html',
		'show_post_count' => false,
		'before'          => '',
		'after'           => '',
	);
}

/**
 * Sanitize recent archive settings.
 *
 * @since 2.6.0
 *
 * @param array $args Array with archives settings.
 * @return array Array with sanitized archives settings.
 */
function cptda_sanitize_archive_settings( $args ) {
	$defaults = cptda_get_archive_settings();
	$args     = array_merge( $defaults, $args );

	$args['post_type']       = sanitize_key( trim( (string) $args['post_type'] ) );
	$args['title']           = strip_tags( trim( (string) $args['title'] ) );
	$args['before_title']    = trim( (string) $args['before_title'] ) ;
	$args['after_title']     = trim( (string) $args['after_title'] );
	$args['limit']           = absint( $args['limit'] );
	$args['offset']          = absint( $args['offset'] );
	$args['type']            = strip_tags( trim( (string) $args['type'] ) );
	$args['order']           = strtoupper( strip_tags( trim( (string) $args['order'] ) ) );
	$args['format']          = strip_tags( trim( (string) $args['format'] ) );
	$args['show_post_count'] = wp_validate_boolean( $args['show_post_count'] );
	$args['before']          = trim( (string) $args['before'] ) ;
	$args['after']           = trim( (string) $args['after'] );

	return $args;
}

/**
 * Validate recent archive settings.
 *
 * @since 2.6.0
 *
 * @param array $args Array with archives settings.
 * @return array Array with validated archives settings.
 */
function cptda_validate_archive_settings( $args ) {
	$plugin = cptda_date_archives();
	$args   = cptda_sanitize_archive_settings( $args );

	$post_types   = $plugin->post_type->get_post_types( 'names' );
	$post_types[] = 'post';
	if ( ! in_array( $args['post_type'], $post_types ) ) {
		$args['post_type'] = 'post';
	}

	$type           = array( 'alpha', 'daily', 'monthly', 'postbypost', 'weekly', 'yearly' );
	$order          = array( 'ASC', 'DESC' );
	$format         = array( 'custom', 'html', 'option' );
	$args['type']   = in_array( $args['type'], $type )     ? $args['type']   : 'monthly';
	$args['order']  = in_array( $args['order'], $order )   ? $args['order']  : 'DESC';
	$args['format'] = in_array( $args['format'], $format ) ? $args['format'] : 'html';

	return $args;
}

function cptda_get_archive_label( $type ) {
	switch ( $type ) {
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

	return $label;
}

/**
 * Get the archives feature HTML.
 *
 * @since 2.6.0
 *
 * @param array $args Archive arguments.
 * @return string Archives HTML.
 */
function cptda_get_archives_html( $args ) {
	$defaults = cptda_get_archive_settings();
	$args     = array_merge( $defaults, $args );
	$html     = '';
	$is_block = false;

	/* Override archive $args if needed. */
	$args['echo']   = false;
	$args['format'] = ( 'object' === $args['format'] ) ? 'html' : $args['format'];

	$title = $args['title'];
	if ( ! empty( $title ) ) {
		$title = $args['before_title'] . $args['title'] . $args['after_title'];
	}

	$class = isset( $args['class'] ) ? $args['class'] : '';
	if ( $class && ( 'wp-block-archives' === $class ) ) {
		$is_block = true;

		// Add extra classes from the editor block
		$archive_type = ( 'option' === $args['format'] ) ? 'dropdown' : 'list';
		$type_class = " {$class}-{$archive_type}";
		$class = cptda_get_block_classes( $args, $class );
		$class .= ' cptda-block-archives';
		$class .= $type_class;
	}

	$class = esc_attr( trim( $class ) );

	$paged = isset( $args['page'] ) ? absint( $args['page'] ) : 0;
	$paged = ( 1 < $paged ) ? $paged : 0;
	if ( $paged ) {
		$args['offset'] = $args['limit'] ? ( ( $paged - 1 ) * $args['limit'] ) : '';
	}

	/* Get the archives list. */
	$archives = cptda_get_archives( $args );

	if ( ! $archives ) {
		$no_archives = __( 'No archives to show.', 'custom-post-type-date-archives' );
		$empty_block = "<div class=\"{$class}\">\n{$title}\n{$no_archives}\n</div>\n";
		return $is_block ? $empty_block : '';
	}

	/* If the archives should be shown in a <select> drop-down. */
	if ( 'option' === $args['format'] ) {
		$label       = cptda_get_archive_label( $args['type'] );
		$title       = $args['title'] ? $args['title'] : __( 'Archives', 'custom-post-type-date-archives' );
		$dropdown_id = esc_attr( uniqid( 'wp-block-archives-' ) );

		$html .= $title;
		$html .= ( $is_block ) ? "<div class=\"{$class}\">\n" : '';
		$html .= '<label class="screen-reader-text" for="' . $dropdown_id . '">' . $title . '</label>';
		$html .= '<select id="' . $dropdown_id . '" name="archive-dropdown"';
		$html .= ' onchange="document.location.href=this.options[this.selectedIndex].value;">';
		$html .= '<option value="">' . $option_title . '</option>' . $archives . '</select>';
		$html .= ( $is_block ) ? "</div>\n" : '';

	} elseif ( 'html' === $args['format'] ) {
		$class = $class ? ' class="' . esc_attr( trim( $class ) ) . '"' : '';
		$html .= "{$title}<ul{$class}>\n{$archives}</ul>\n";
	} else {
		$html .= $archives;
	}

	return $html;
}
