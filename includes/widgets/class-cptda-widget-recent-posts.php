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
	protected $defaults;

	/**
	 * Sets up a new Recent Posts widget instance.
	 *
	 * @since 2.8.0
	 * @access public
	 */
	public function __construct() {
		$this->plugin = cptda_date_archives();

		/* Set up defaults. */
		$this->defaults = array(
			'title'         => '',
			'message'       => '',
			'number'        => 5,
			'show_date'     => false,
			'status_future' => false,
			'post_type'     => 'post',
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
	 * @param array   $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array   $instance Settings for the current Recent Posts widget instance.
	 */
	public function widget( $args, $instance ) {
		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		$instance = wp_parse_args( $instance, $this->defaults );
		$title    = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Recent Posts' );

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		if ( $title ) {
			$title = $args['before_title'] . $title . $args['after_title'];
		}

		$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
		if ( ! $number ) {
			$number = 5;
		}

		$post_type     = trim( (string) $instance['post_type'] );
		$message       = trim( (string) $instance['message'] ) ;
		$show_date     = (bool) $instance['show_date'];
		$status_future = (bool) $instance['status_future'];
		$widget_start  = $args['before_widget'] . $title;

		$query_args = array(
			'post_type'           => $post_type,
			'post_status'         => cptda_get_cpt_date_archive_stati( $post_type ),
			'posts_per_page'      => $number,
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true
		);

		if ( $status_future ) {
			$today = getdate();
			$query_args['date_query']  = array(
				array(
					'after' => array(
						'year'  => $today['year'],
						'month' => $today['mon'],
						'day'   => $today['mday'],
					),
					'inclusive' => true,
				) );
		}

		/**
		 * Filters the arguments for the Recent Posts widget.
		 *
		 * @since 3.4.0
		 *
		 * @see WP_Query::get_posts()
		 *
		 * @param array $query_args An array of arguments used to retrieve the recent posts.
		 */
		$r = new WP_Query( apply_filters( 'widget_posts_args', $query_args ) );

		if ( $r->have_posts() ) {
			echo $widget_start;
			include CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/partials/recent-posts-display.php';
			echo $args['after_widget'];
		} else {
			if ( $message ) {
				echo $widget_start;
				echo apply_filters( 'the_content', $message );
				echo $args['after_widget'];
			}
		}

		// Reset the global $the_post as the query might have stomped on it.
		wp_reset_postdata();
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
		$instance = $old_instance;
		$instance['title']         = sanitize_text_field( (string) $new_instance['title'] );
		$instance['number']        = (int) $new_instance['number'];
		$instance['show_date']     = isset( $new_instance['show_date'] ) ? (bool) $new_instance['show_date'] : false;
		$instance['status_future'] = isset( $new_instance['status_future'] ) ? (bool) $new_instance['status_future'] : false;

		if ( current_user_can( 'unfiltered_html' ) ) {
			$instance['message'] = (string) $new_instance['message'];
		} else {
			$instance['message'] = wp_kses_post( (string) $new_instance['message'] );
		}

		$post_types = $this->plugin->post_type->get_date_archive_post_types( 'names' );
		$post_types[] = 'post';

		$instance['post_type'] = $new_instance['post_type'];
		if ( !in_array( $new_instance['post_type'], $post_types ) ) {
			$instance['post_type'] = 'post';
		}

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
		$instance        = wp_parse_args( (array) $instance, $this->defaults );
		$title           = sanitize_text_field( (string) $instance['title'] );
		$message         = trim( (string) $instance['message'] );
		$post_type       = trim( (string) $instance['post_type'] );
		$number          = absint( $instance['number'] );
		$show_date       = (bool) $instance['show_date'];
		$status          = (bool) $instance['status_future'];
		$show_post_types = false;
		$post_types      = $this->plugin->post_type->get_date_archive_post_types( 'labels' );
		if ( !empty( $post_types ) ) {
			$show_post_types = true;
			$post_types = array_merge( array( 'post' => __( 'Post' ) ), $post_types );
		}

		include CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/partials/recent-posts-widget.php';
	}
}
