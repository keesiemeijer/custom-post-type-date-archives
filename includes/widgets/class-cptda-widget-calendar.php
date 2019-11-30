<?php
/**
 * Calendar Widget.
 *
 * @package     Custom_Post_Type_Date_Archives
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

	public function widget( $widget_args, $instance ) {

		/* Set the $widget_args for wp_get_archives() to the $instance array. */
		$args = wp_parse_args( $instance, $this->defaults );

		/** This filter is documented in wp-includes/default-widgets.php */
		$title = apply_filters( 'widget_title', empty( $args['title'] ) ? '' : $args['title'], $args, $this->id_base );

		echo $widget_args['before_widget'];
		if ( $title ) {
			echo $widget_args['before_title'] . $title . $widget_args['after_title'];
		}

		if ( 0 === self::$instance ) {
			echo '<div id="calendar_wrap" class="calendar_wrap">';
		} else {
			echo '<div class="calendar_wrap">';
		}

		cptda_get_calendar( $args['post_type'] );

		echo '</div>';
		echo $widget_args['after_widget'];

		self::$instance++;
	}

	public function update( $new_instance, $old_instance ) {
		$instance     = $old_instance;
		$new_instance = array_merge( $this->defaults, $new_instance );

		$instance['title']     = strip_tags( $new_instance['title'] );
		$instance['post_type'] = strip_tags( $new_instance['post_type'] );

		return $instance;
	}

	public function form( $instance ) {
		/* Merge the user-selected arguments with the defaults. */
		$instance    = wp_parse_args( (array) $instance, $this->defaults );
		$title       = esc_attr__( strip_tags( $instance['title'] ) );

		$desc            = '';
		$style           = '';
		$show_post_types = true;
		$post_type       = $instance['post_type'] ? (string) $instance['post_type'] : 'post';
		$post_types      = $this->plugin->post_type->get_post_types( 'labels' );
		$post_types      = array_merge( array( 'post' => __( 'Post' ) ), $post_types );

		if ( ! in_array( $post_type, array_keys( $post_types ) ) ) {
			// Post type doesnt exist or has no date archives
			$post_types[ $post_type ] = $post_type;
			$style = ' style="border-color: red;"';
			if ( ! post_type_exists( $post_type ) ) {
				$desc = __( "<strong>Note</strong>: The selected post type doesn't exist anymore", 'custom-post-type-date-archives' );
			} else {
				$desc = __( "<strong>Note</strong>: The selected post type doesn't have date archives", 'custom-post-type-date-archives' );
			}
		}

		if ( 1 === count( $post_types ) && in_array( 'post', $post_types ) ) {
			$show_post_types = false;
		}

		include CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/partials/calendar-widget.php';
	}
}
