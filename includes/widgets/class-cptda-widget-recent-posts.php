<?php
/**
 * Recent posts widget
 *
 * @package     Custom_Post_Type_Date_Archives
 * @subpackage  Widget
 * @copyright   Copyright (c) 2014, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.4.0
 */

/**
 * Core class used to implement a Recent Posts widget.
 *
 * @since 2.4.0
 *
 * @see WP_Widget
 */
class CPTDA_Widget_Recent_Posts extends WP_Widget {

	protected $plugin;

	/**
	 * Sets up a new Recent Posts widget instance.
	 *
	 * @since 2.4.0
	 * @access public
	 */
	public function __construct() {
		$this->plugin = cptda_date_archives();

		$widget_ops = array(
			'classname'                   => 'widget_recent_entries',
			'description'                 => __( 'Your site&#8217;s most recent Posts.' ),
			'customize_selective_refresh' => true,
		);

		if ( $this->plugin->replace_widgets ) {
			parent::__construct( 'recent-posts', __( 'Recent Posts' ), $widget_ops );
			$this->alt_option_name = 'widget_recent_entries';
		} else {
			$title = __( 'Custom Post Type Recent Posts', 'custom-post-type-date-archives' );
			$widget_ops['description'] = __( 'Your site&#8217;s most recent custom post type Posts.', 'custom-post-type-date-archives' );
			parent::__construct( 'cptda_recent-posts', $title, $widget_ops );
			$this->alt_option_name = 'cptda_widget_recent_entries';
		}
	}

	/**
	 * Outputs the content for the current Recent Posts widget instance.
	 *
	 * @since 2.4.0
	 * @access public
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current Recent Posts widget instance.
	 */
	public function widget( $widget_args, $instance ) {
		if ( ! isset( $widget_args['widget_id'] ) ) {
			$widget_args['widget_id'] = $this->id;
		}

		$args  = $this->get_instance( $instance );
		$title = ( ! empty( $args['title'] ) ) ? $args['title'] : __( 'Recent Posts' );

		/** This filter is documented in wp-includes/default-widgets.php */
		$title = apply_filters( 'widget_title', $args['title'], $args, $this->id_base );
		if ( $title ) {
			$title = $widget_args['before_title'] . $title . $widget_args['after_title'];
		}

		$query_args = cptda_get_recent_posts_query( $args );

		/** This filter is documented in wp-includes/widgets/class-wp-widget-recent-posts.php */
		$query_args = apply_filters( 'widget_posts_args', $query_args, $args );

		$recent_posts = cptda_get_recent_posts( $query_args );
		$widget       = cptda_get_recent_posts_html( $recent_posts, $args );

		if ( $widget ) {
			echo $widget_args['before_widget'] . $title . $widget . $widget_args['after_widget'];
		}
	}

	/**
	 * Handles updating the settings for the current Recent Posts widget instance.
	 *
	 * @since 2.4.0
	 * @access public
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Updated settings to save.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $this->get_instance( $new_instance );
		$instance = cptda_validate_recent_posts_settings( $instance );

		// Note: the message textarea field was sanitized with wp_kses_post().
		$instance['title'] = sanitize_text_field( (string) $instance['title'] );

		return array_merge( $old_instance, $instance );
	}

	/**
	 * Outputs the settings form for the Recent Posts widget.
	 *
	 * @since 2.4.0
	 * @access public
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {
		$instance = $this->get_instance( $instance );

		$title      = sanitize_text_field( (string) $instance['title'] );
		$message    = trim( (string) $instance['message'] );
		$post_type  = trim( (string) $instance['post_type'] );
		$number     = absint( $instance['number'] );
		$show_date  = (bool) $instance['show_date'];
		$include    = trim( (string) $instance['include'] );
		$desc       = '';
		$style      = '';
		$included   = cptda_get_recent_posts_date_query_types();
		$post_type  = $instance['post_type'] ? (string) $instance['post_type'] : 'post';
		$post_types = cptda_get_public_post_types();

		if ( ! in_array( $post_type, array_keys( $post_types ) ) ) {
			// Post type doesnt exist
			$post_types[ $post_type ] = $post_type;

			$style = ' style="border-color: red;"';
			$desc  = sprintf( __( "<strong>Note</strong>: The post type '%s' doesn't exist anymore", 'custom-post-type-date-archives' ), $post_type );
		}

		$show_post_types = true;
		if ( 1 === count( $post_types ) && in_array( 'post', array_keys( $post_types ) ) ) {
			$show_post_types = false;
		}

		include CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/partials/recent-posts-widget.php';
	}

	/**
	 * Gets instance settings.
	 *
	 * Merges instance settings with defaults and applies back compatibility.
	 *
	 * @since 2.5.0
	 *
	 * @param array $instance Settings for the current Recent Posts widget instance.
	 * @return @return array All Recent Posts widget instance settings with back compat applied.
	 */
	function get_instance( $instance ) {
		// 'status_future' was removed and replaced by 'include'
		if ( isset( $instance['status_future'] ) && ! isset( $instance['include'] ) ) {
			$instance['include'] = $instance['status_future'] ? 'future' : 'all';
		}

		unset( $instance['status_future'] );

		$default = cptda_get_recent_posts_settings();
		$default['title'] = __( 'Recent Posts', 'custom-post-type-date-archives' );

		return wp_parse_args( (array) $instance, $default );
	}
}
