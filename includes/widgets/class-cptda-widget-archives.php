<?php
/**
 * Archive Widget
 *
 * @package     Custom_Post_Type_Date_Archives
 * @subpackage  Widget
 * @copyright   Copyright (c) 2014, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Archives widget class
 *
 * @since 1.0
 */
class CPTDA_Widget_Archives extends WP_Widget {

	public $plugin;

	public function __construct() {
		$this->plugin = cptda_date_archives();

		$widget_ops = array(
			'classname' => 'widget_archive',
			'description' => __( 'A monthly archive of your site&#8217;s Posts.' ),
			'customize_selective_refresh' => true,
		);

		if ( $this->plugin->replace_widgets ) {
			parent::__construct( 'archives', __( 'Archives' ), $widget_ops );
		} else {
			$title = __( 'Custom Post Type Archives', 'custom-post-type-date-archives' );
			$widget_ops['description'] = __( 'A monthly archive of your site&#8217;s custom post type Posts.' );
			parent::__construct( 'cptda_archives', $title, $widget_ops );
		}
	}

	public function widget( $widget_args, $instance ) {
		$args = $this->get_instance( $instance );

		/** This filter is documented in wp-includes/default-widgets.php */
		$title = apply_filters( 'widget_title', $args['title'], $args, $this->id_base );
		if ( $title ) {
			$title = $widget_args['before_title'] . $title . $widget_args['after_title'];
		}

		$args['cpda_widget_archives'] =  true;

		if ( 'option' === $args['format'] ) {
			/** This filter is documented in wp-includes/widgets/class-wp-widget-archives.php */
			$args = apply_filters( 'widget_archives_dropdown_args', $args, $instance );
		} else {
			/** This filter is documented in wp-includes/widgets/class-wp-widget-archives.php */
			$args = apply_filters( 'widget_archives_args', $args, $instance );
		}

		$args     = cptda_validate_archive_settings( $args );
		$archives = cptda_get_archives_html( $args );

		if ( $archives ) {
			echo $widget_args['before_widget'] . $title . $archives . $widget_args['after_widget'];
		}
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $this->get_instance( $new_instance );
		$instance  = cptda_validate_archive_settings( $instance );

		$instance['title'] = sanitize_text_field( (string) $instance['title'] );
		$instance['limit'] = $instance['limit'] ? $instance['limit'] : 5;

		return array_merge( $old_instance, $instance );
	}

	public function form( $instance ) {
		/* Merge the user-selected arguments with the defaults. */
		$instance = $this->get_instance( $instance );

		/* Create an array of archive types. */
		$type = array(
			'alpha'      => esc_attr__( 'Alphabetical', 'custom-post-type-date-archives' ),
			'daily'      => esc_attr__( 'Daily',        'custom-post-type-date-archives' ),
			'monthly'    => esc_attr__( 'Monthly',      'custom-post-type-date-archives' ),
			'postbypost' => esc_attr__( 'Post By Post', 'custom-post-type-date-archives' ),
			'weekly'     => esc_attr__( 'Weekly',       'custom-post-type-date-archives' ),
			'yearly'     => esc_attr__( 'Yearly',       'custom-post-type-date-archives' ),
		);

		/* Create an array of order options. */
		$order = array(
			'ASC'  => esc_attr__( 'Ascending',  'custom-post-type-date-archives' ),
			'DESC' => esc_attr__( 'Descending', 'custom-post-type-date-archives' ),
		);

		/* Create an array of archive formats. */
		$format = array(
			'custom' => esc_attr__( 'Custom', 'custom-post-type-date-archives' ),
			'html'   => esc_attr__( 'HTML',   'custom-post-type-date-archives' ),
			'option' => esc_attr__( 'Option', 'custom-post-type-date-archives' ),
		);

		$desc       = '';
		$style      = '';
		$post_type  = $instance['post_type'] ? (string) $instance['post_type'] : 'post';
		$post_types = $this->plugin->post_type->get_post_types( 'labels' );
		$post_types = array_merge( array( 'post' => __( 'Post' ) ), $post_types );

		if ( ! in_array( $post_type, array_keys( $post_types ) ) ) {
			// Post type doesnt exist or has no date archives
			$post_types[ $post_type ] = $post_type;
			$style = ' style="border-color: red;"';
			if ( ! post_type_exists( $post_type ) ) {
				$desc = sprintf( __( "<strong>Note</strong>: The post type '%s' doesn't exist anymore", 'custom-post-type-date-archives' ), $post_type );
			} else {
				$desc = sprintf( __( "<strong>Note</strong>: The post type '%s' doesn't have date archives", 'custom-post-type-date-archives' ), $post_type );
			}
		}

		$show_post_types = true;
		if ( 1 === count( $post_types ) && in_array( 'post', array_keys( $post_types ) ) ) {
			$show_post_types = false;
		}

		include CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/partials/archive-widget.php';
	}

	/**
	 * Gets instance settings.
	 *
	 * @param array $instance Settings for the current Recent Posts widget instance.
	 * @return @return array All Recent Posts widget instance settings with back compat applied.
	 */
	function get_instance( $instance ) {
		$defaults = cptda_get_archive_settings();
		$defaults['title'] = esc_attr__( 'Archives', 'custom-post-type-archives' );

		return wp_parse_args( (array) $instance, $defaults );
	}
}
