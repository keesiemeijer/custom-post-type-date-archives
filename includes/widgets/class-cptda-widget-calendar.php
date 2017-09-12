<?php
/**
 * Calendar Widget.
 *
 * @package     Custom Post Type Date Archives
 * @subpackage  Widget
 * @copyright   Copyright (c) 2014, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

/**
 * Calendar widget class.
 *
 * @since 1.1
 */
class CPTDA_Widget_Calendar extends WP_Widget {

	protected $plugin;
	protected $defaults;

	/**
	 * Ensure that the ID attribute only appears in the markup once
	 *
	 * @since 2.3.2
	 *
	 * @static
	 * @access private
	 * @var int
	 */
	private static $instance = 0;

	public function __construct() {
		$this->plugin = cptda_date_archives();

		/* Set up defaults. */
		$this->defaults = array(
			'title'           => esc_attr__( 'Archives', 'custom-post-type-date-archives' ),
			'placeholder'     => esc_attr__( 'Calendar', 'custom-post-type-date-archives' ),
			'post_type'       => 'post',
		);

		$widget_ops = array(
			'classname' => 'widget_calendar',
			'description' => __( 'A calendar of your site&#8217;s Posts.' ),
			'customize_selective_refresh' => true,
		);

		if ( $this->plugin->replace_widgets ) {
			parent::__construct( 'calendar', __( 'Calendar' ), $widget_ops );
		} else {
			$title = __( 'Custom Post Type Calendar', 'custom-post-type-date-archives' );
			$widget_ops['description'] = __( 'A calendar of your site&#8217;s custom post type Posts.' );
			parent::__construct( 'cptda_calendar', $title, $widget_ops );
		}
	}

	public function widget( $args, $instance ) {

		/* Set the $args for wp_get_archives() to the $instance array. */
		$instance = wp_parse_args( $instance, $this->defaults );

		/** This filter is documented in wp-includes/default-widgets.php */
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		if ( 0 === self::$instance ) {
			echo '<div id="calendar_wrap" class="calendar_wrap">';
		} else {
			echo '<div class="calendar_wrap">';
		}

		/* Get the archives list. */
		if ( cptda_is_date_post_type( $instance['post_type'] ) ) {
			cptda_get_calendar( $instance['post_type'] );
		} else {
			get_calendar();
		}

		echo '</div>';
		echo $args['after_widget'];

		self::$instance++;
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );

		$post_types = $this->plugin->post_type->get_post_types( 'names' );
		$post_types[] = 'post';

		$instance['post_type'] = $new_instance['post_type'];
		if ( ! in_array( $new_instance['post_type'], $post_types ) ) {
			$instance['post_type'] = 'post';
		}

		return $instance;
	}

	public function form( $instance ) {
		/* Merge the user-selected arguments with the defaults. */
		$instance    = wp_parse_args( (array) $instance, $this->defaults );
		$title       = esc_attr__( strip_tags( $instance['title'] ) );
		$post_types  = $this->plugin->post_type->get_post_types( 'labels' );
		$post_type   = isset( $instance['post_type'] ) ? (string) $instance['post_type'] : 'post';

		$show_post_types = false;
		if ( ! empty( $post_types ) ) {
			$show_post_types = true;
			$post_types = array_merge( array( 'post' => __( 'Post' ) ), $post_types );
		}

		include CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/partials/calendar-widget.php';
	}
}
