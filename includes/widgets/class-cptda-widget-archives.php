<?php
/**
 * Archive Widget
 *
 * @package     Custom Post Type Date Archives
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
	public $defaults;


	public function __construct() {
		$this->plugin = cptda_date_archives();

		/* Set up defaults. */
		$this->defaults = array(
			'title'           => esc_attr__( 'Archives', 'custom-post-type-archives' ),
			'limit'           => 10,
			'post_type'       => 'post',
			'type'            => 'monthly',
			'order'           => 'DESC',
			'format'          => 'html',
			'show_post_count' => false,
		);

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

	public function widget( $sidebar, $instance ) {

		/* Set the $args for wp_get_archives() to the $instance array. */
		$args = wp_parse_args( $instance, $this->defaults );

		/* Overwrite the $echo argument and set it to false. */
		$args['echo'] = false;

		/* Output the sidebar's $before_widget wrapper. */
		echo $sidebar['before_widget'];

		/* If a title was input by the user, display it. */
		if ( ! empty( $args['title'] ) ) {
			echo $sidebar['before_title'] . apply_filters( 'widget_title',  $args['title'], $instance, $this->id_base ) . $sidebar['after_title'];
		}

		/* Get the archives list. */
		if ( cptda_is_date_post_type( $args['post_type'] ) ) {
			$archives = str_replace( array( "\r", "\n", "\t" ), '', cptda_get_archives( $args ) );
		} else {
			$archives = str_replace( array( "\r", "\n", "\t" ), '', wp_get_archives( $args ) );
		}

		/* If the archives should be shown in a <select> drop-down. */
		if ( 'option' === $args['format'] ) {

			/* Create a title for the drop-down based on the archive type. */
			if ( 'yearly' === $args['type'] ) {
				$option_title = esc_html__( 'Select Year', 'custom-post-type-date-archives' );
			} elseif ( 'monthly' === $args['type'] ) {
				$option_title = esc_html__( 'Select Month', 'custom-post-type-date-archives' );
			} elseif ( 'weekly' === $args['type'] ) {
				$option_title = esc_html__( 'Select Week', 'custom-post-type-date-archives' );
			} elseif ( 'daily' === $args['type'] ) {
				$option_title = esc_html__( 'Select Day', 'custom-post-type-date-archives' );
			} elseif ( 'postbypost' === $args['type'] || 'alpha' === $args['type'] ) {
				$option_title = esc_html__( 'Select Post', 'custom-post-type-date-archives' );
			}

			/* Output the <select> element and each <option>. */
			echo '<p><select name="archive-dropdown" onchange=\'document.location.href=this.options[this.selectedIndex].value;\'>';
			echo '<option value="">' . $option_title . '</option>';
			echo $archives;
			echo '</select></p>';
		} elseif ( 'html' === $args['format'] ) {
			echo '<ul>' . $archives . '</ul>';
		} else {
			echo $archives;
		}

		/* Close the sidebar's widget wrapper. */
		echo $sidebar['after_widget'];
	}

	public function update( $new_instance, $old_instance ) {
		/* Strip tags. */
		$instance['title']  = strip_tags( $new_instance['title'] );

		/* Whitelist options. */
		$type       = array( 'alpha', 'daily', 'monthly', 'postbypost', 'weekly', 'yearly' );
		$order      = array( 'ASC', 'DESC' );
		$format     = array( 'custom', 'html', 'option' );
		$post_types = $this->plugin->post_type->get_post_types( 'names' );
		$post_types[] = 'post';

		$instance['post_type'] = $new_instance['post_type'];
		if ( ! in_array( $new_instance['post_type'], $post_types ) ) {
			$instance['post_type'] = 'post';
		}

		$instance['type']   = in_array( $new_instance['type'], $type )     ? $new_instance['type']   : 'monthly';
		$instance['order']  = in_array( $new_instance['order'], $order )   ? $new_instance['order']  : 'DESC';
		$instance['format'] = in_array( $new_instance['format'], $format ) ? $new_instance['format'] : 'html';

		/* Integers. */
		$instance['limit'] = absint( $new_instance['limit'] );
		$instance['limit'] = $instance['limit'] ? $instance['limit'] : 10;

		/* Checkboxes. */
		$instance['show_post_count'] = isset( $new_instance['show_post_count'] ) ? 1 : 0;

		/* Return sanitized options. */
		return $instance;
	}

	public function form( $instance ) {
		/* Merge the user-selected arguments with the defaults. */
		$instance = wp_parse_args( (array) $instance, $this->defaults );

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

		$post_type = ( isset( $instance['post_type'] ) ) ? (string) $instance['post_type'] : 'post';

		$show_post_types = false;
		$post_types = $this->plugin->post_type->get_post_types( 'labels' );

		if ( ! empty( $post_types ) ) {
			$show_post_types = true;
			$post_types = array_merge( array( 'post' => __( 'Post' ) ), $post_types );
		}

		include CPT_DATE_ARCHIVES_PLUGIN_DIR . 'includes/partials/archive-widget.php';
	}
}
