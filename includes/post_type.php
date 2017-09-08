<?php
/**
 * Post Types.
 *
 * @package     Custom Post Type Date Archives
 * @subpackage  Classes/Post_Types
 * @copyright   Copyright (c) 2014, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Setup of post types that support date archives.
 *
 * @since 1.0
 * @author keesiemeijer
 */
class CPTDA_Post_Types {

	private $date_post_types = array();
	private $publish_future  = array();

	public function __construct() {
		add_action( 'wp_loaded',   array( $this, 'setup' ) );
	}

	/**
	 * Sets up properties with custom post types that support date archives.
	 * Checks if 'date-archives' support was added to custom post types.
	 * see: http://codex.wordpress.org/Function_Reference/add_post_type_support
	 *
	 * @since 1.0
	 * @return void
	 */
	public function setup() {

		$this->reset_post_types();
		$this->setup_admin_post_types();

		$args = array(
			'public'             => true,
			'publicly_queryable' => true,
			'has_archive'        => true,
			'_builtin'           => false,
		);

		$this->date_post_types = get_post_types( $args, 'objects', 'and' );

		foreach ( (array) $this->date_post_types as $name => $post_type ) {

			if ( post_type_supports( $name, 'publish-future-posts' ) ) {
				$this->publish_future[] = $name;
			}

			if ( ! post_type_supports( $name, 'date-archives' ) ) {
				unset( $this->date_post_types[ $name ] );
			}
		}

		$this->publish_scheduled_posts();
	}

	/**
	 * Reset post type properties.
	 *
	 * @since 2.1.0
	 * @return void
	 */
	function reset_post_types() {
		$this->date_post_types = array();
		$this->publish_future  = array();
	}

	/**
	 * Setup post types from admin page settings.
	 *
	 * @since 2.1.0
	 * @return void
	 */
	private function setup_admin_post_types() {
		$archives = get_option( 'custom_post_type_date_archives' );
		if ( empty( $archives ) ) {
			return;
		}

		$this->setup_admin_post_type_support( $archives );
	}

	/**
	 * Set up admin settings post type support.
	 *
	 * @since 2.3.0
	 * @param array $archives Admin archives settings.
	 * @return void
	 */
	private function setup_admin_post_type_support( $archives ) {
		$supports = array( 'date_archives', 'publish_future_posts' );

		foreach ( $supports  as $support ) {

			if ( ! ( isset( $archives[ $support ] ) && ! empty( $archives[ $support ] ) ) ) {
				continue;
			}

			$post_types = is_array( $archives[ $support ] ) ? $archives[ $support ] : array();
			$support    = str_replace( '_', '-', $support );
			$this->add_admin_post_type_support( array_keys( $post_types ), $support );
		}
	}

	/**
	 * Add support to post type from admin settings
	 *
	 * @since 2.1.0
	 * @param array  $archives Array with date archive post types.
	 * @param string $support  Type of support. 'date_archives' or 'publish_future_posts'.
	 * @return void
	 */
	private function add_admin_post_type_support( $archives, $support = 'date-archives' ) {

		if ( empty( $archives ) || ! is_array( $archives ) ) {
			return;
		}

		$post_types = cptda_get_admin_post_types();

		foreach ( $post_types as $post_type => $value ) {
			if ( in_array( $post_type, $archives ) ) {
				add_post_type_support( $post_type, $support );
			} else {
				remove_post_type_support( $post_type, $support );
			}
		}
	}

	/**
	 * Sets up post types were scheduled posts are published.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	private function publish_scheduled_posts() {

		if ( empty( $this->publish_future ) ) {
			return;
		}

		/**
		 * Filter whether to publish posts with future dates as normal posts.
		 *
		 * @since 1.0
		 * @param bool $publish Default true.
		 */
		$publish = (bool) apply_filters( 'cptda_publish_future_posts', true );
		if ( ! $publish ) {
			return;
		}

		foreach ( $this->publish_future as $name ) {
			remove_action( "future_{$name}", '_future_post_hook' );
			add_action( "future_{$name}", array( $this, '_future_post_hook' ) );
		}
	}

	/**
	 * Set new post's post_status to "publish" if the post is sceduled.
	 *
	 * @since 1.2
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function _future_post_hook( $post_id ) {
		$post = get_post( $post_id );

		/**
		 * Filter whether to publish posts with future dates from a specific post type.
		 *
		 * @since 1.2
		 * @param bool $publish Default true.
		 */
		$publish = apply_filters( "cptda_publish_future_{$post->post_type}", true );
		if ( (bool) $publish ) {
			wp_publish_post( $post_id );
		}
	}

	/**
	 * Returns post types that support date archives.
	 *
	 * @since 1.0
	 * @param string $type Type of return array.
	 * @return string Array of post types that support post types.
	 */
	public function get_date_archive_post_types( $type = 'names' ) {

		$post_types = array();

		if ( 'objects' === $type ) {
			$post_types = $this->date_post_types;
		}

		if ( 'labels' === $type ) {
			$post_types = array();
			foreach ( $this->date_post_types as $key => $value ) {
				$post_types[ $key ] = esc_attr( $value->labels->menu_name );
			}
		}

		if ( 'publish_future' === $type ) {
			$post_types = $this->publish_future;
		}

		if ( ! empty( $this->date_post_types ) && ( 'names' === $type ) ) {
			$post_types = wp_list_pluck( $this->date_post_types, 'name' );
		}

		return $post_types;
	}

}
