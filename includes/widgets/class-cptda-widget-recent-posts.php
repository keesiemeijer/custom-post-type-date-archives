<?php
/**
 * Widget API: WP_Widget_Recent_Posts class
 *
 * @package WordPress
 * @subpackage Widgets
 * @since 4.4.0
 */

/**
 * Core class used to implement a Recent Posts widget.
 *
 * @since 2.8.0
 *
 * @see WP_Widget
 */
class CPTDA_Widget_Recent_Posts extends WP_Widget {

	protected $plugin;
	protected $include;

	/**
	 * Sets up a new Recent Posts widget instance.
	 *
	 * @since 2.8.0
	 * @access public
	 */
	public function __construct() {
		$this->plugin = cptda_date_archives();

		$this->include = array(
			'all'    => __( 'all posts', 'custom-post-type-date-archives' ),
			'future' => __( 'posts with future dates only', 'custom-post-type-date-archives' ),
			'year'   => __( 'posts from the current year', 'custom-post-type-date-archives' ),
			'month'  => __( 'posts from the current month', 'custom-post-type-date-archives' ),
			'day'    => __( 'posts from today', 'custom-post-type-date-archives' ),
		);

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
	 * @since 2.8.0
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

		$args  = $this->get_instance_settings( $instance );
		$title = ( ! empty( $args['title'] ) ) ? $args['title'] : __( 'Recent Posts' );

		$args['before_title'] = $widget_args['before_title'];
		$args['after_title'] = $widget_args['after_title'];

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$args['title'] = apply_filters( 'widget_title', $title, $args, $this->id_base );

		$query_args = cptda_get_recent_posts_query( $args );

		/** This filter is documented in wp-includes/widgets/class-wp-widget-recent-posts.php */
		$query_args = apply_filters( 'widget_posts_args', $query_args, $args );

		$recent_posts = get_posts( $query_args );
		$widget       = cptda_get_recent_posts_html( $recent_posts, $args );

		if ( $widget ) {
			echo $widget_args['before_widget'] . $widget . $widget_args['after_widget'];
		}
	}

	/**
	 * Handles updating the settings for the current Recent Posts widget instance.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Updated settings to save.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance              = $old_instance;
		$new_instance          = cptda_sanitize_recent_posts_settings( $new_instance );
		$instance['title']     = sanitize_text_field( (string) $new_instance['title'] );
		$instance['number']    = $new_instance['number'] ? $new_instance['number'] : 5;
		$instance['show_date'] = (bool) $new_instance['show_date'];
		$instance['message']   = (string) $new_instance['message'];

		$post_types = $this->plugin->post_type->get_post_types( 'names' );
		$post_types[] = 'post';

		$instance['post_type'] = $new_instance['post_type'];
		if ( ! in_array( $new_instance['post_type'], $post_types ) ) {
			$instance['post_type'] = 'post';
		}

		$instance['include'] = $new_instance['include'];
		if ( ! in_array( $new_instance['include'], array_keys( $this->include ) ) ) {
			$instance['include'] = 'all';
		}

		// Back compat.
		unset( $instance['status_future'] );

		return $instance;
	}

	/**
	 * Outputs the settings form for the Recent Posts widget.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {

		$instance  = $this->get_instance_settings( $instance );

		$title     = sanitize_text_field( (string) $instance['title'] );
		$message   = trim( (string) $instance['message'] );
		$post_type = trim( (string) $instance['post_type'] );
		$number    = absint( $instance['number'] );
		$show_date = (bool) $instance['show_date'];
		$include   = trim( (string) $instance['include'] );

		$show_post_types = false;
		$post_types      = $this->plugin->post_type->get_post_types( 'labels' );
		if ( ! empty( $post_types ) ) {
			$show_post_types = true;
			$post_types = array_merge( array( 'post' => __( 'Post' ) ), $post_types );
		}

		include CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/partials/recent-posts-widget.php';
	}

	/**
	 * Gets instance settings.
	 *
	 * Merges instance settings with defaults and applies back compatibility.
	 *
	 * @param array $instance Settings for the current Recent Posts widget instance.
	 * @return @return array All Recent Posts widget instance settings with back compat applied.
	 */
	function get_instance_settings( $instance ) {

		// 'status_future' was removed and replaced by 'include'
		if ( isset( $instance['status_future'] ) && ! isset( $instance['include'] ) ) {
			$instance['include'] = $instance['status_future'] ? 'future' : 'all';
		}

		$default = cptda_get_recent_posts_settings();

		return wp_parse_args( (array) $instance, $default );
	}
}
