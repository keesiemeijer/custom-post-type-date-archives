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
		$args = $this->get_instance( $instance );

		/** This filter is documented in wp-includes/default-widgets.php */
		$title = apply_filters( 'widget_title', $args['title'], $args, $this->id_base );
		if ( $title ) {
			$title = $widget_args['before_title'] . $title . $widget_args['after_title'];
		}

		echo $widget_args['before_widget'] . "\n{$title}\n";

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
		$instance = $this->get_instance( $new_instance );

		$instance['title']     = sanitize_text_field( (string) $instance['title'] );
		$instance['post_type'] = strip_tags( trim( $instance['post_type'] ) );

		return array_merge( $old_instance, $instance );
	}

	public function form( $instance ) {
		$instance   = $this->get_instance( $instance );
		$title      = esc_attr__( strip_tags( $instance['title'] ) );
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

		include CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/partials/calendar-widget.php';
	}

	/**
	 * Gets instance settings.
	 *
	 * @since 2.7.0
	 *
	 * @param array $instance Settings for the current Recent Posts widget instance.
	 * @return @return array All Recent Posts widget instance settings with back compat applied.
	 */
	function get_instance( $instance ) {
		$defaults = array(
			'title'           => esc_attr__( 'Archives', 'custom-post-type-date-archives' ),
			'placeholder'     => esc_attr__( 'Calendar', 'custom-post-type-date-archives' ),
			'post_type'       => 'post',
		);

		return wp_parse_args( (array) $instance, $defaults );
	}

}
