<?php
/**
 * Post Types.
 *
 * @package     Custom Post Type Date Archives
 * @subpackage  Classes/Post_Types
 * @copyright   Copyright (c) 2014, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
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

	/**
	 * Public post type objects.
	 *
	 * @var array
	 */
	private $post_types = array();

	public function __construct() {
		add_action( 'wp_loaded',   array( $this, 'setup' ) );
	}

	/**
	 * Sets up properties with custom post types that support date archives.
	 * Checks if 'date-archives' support was added to custom post types.
	 * see: http://codex.wordpress.org/Function_Reference/add_post_type_support
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function setup() {
		$this->post_types = $this->get_public_post_types();
		$this->setup_admin_post_types();
		$this->publish_scheduled_posts();
	}

	/**
	 * Setup post types.
	 *
	 * @since 2.5.0
	 */
	private function get_public_post_types() {
		$args = array(
			'public'             => true,
			'publicly_queryable' => true,
			'has_archive'        => true,
			'_builtin'           => false,
		);

		return get_post_types( $args, 'objects', 'and' );
	}

	/**
	 * Setup post types from admin page settings.
	 *
	 * @since 2.1.0
	 * @return void
	 */
	private function setup_admin_post_types() {

		$settings = new CPTDA_Settings();
		$admin_settings = $settings->get_settings();

		if ( empty( $admin_settings ) ) {
			return;
		}

		$this->setup_admin_post_type_support( $admin_settings );
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

			if ( ! ( isset( $archives[ $support ] ) && $archives[ $support ] ) ) {
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

		$post_types = $this->get_post_types( 'names', 'admin' );

		foreach ( $post_types as $post_type ) {
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

		$future_types = $this->get_post_types( 'names', 'publish_future' );
		if ( empty( $future_types ) ) {
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

		foreach ( $future_types as $name ) {
			remove_action( "future_{$name}", '_future_post_hook' );
			add_action( "future_{$name}", array( $this, '_future_post_hook' ) );
		}
	}

	/**
	 * Set new post's post_status to "publish" if the post is sceduled.
	 *
	 * @since 1.2.0
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function _future_post_hook( $post_id ) {
		$post = get_post( $post_id );

		/**
		 * Filter whether to publish posts with future dates from a specific post type.
		 *
		 * @since 1.2.0
		 * @param bool $publish Default true.
		 */
		$publish = apply_filters( "cptda_publish_future_{$post->post_type}", true );
		if ( (bool) $publish ) {
			wp_publish_post( $post_id );
		}
	}

	/**
	 * Returns custom post types depending on format and context.
	 *
	 * Use context 'date_archive' to get custom post types that have date archives support (Default).
	 * Use context 'admin' to get custom post types that are registered to appear in the admin menu.
	 * Use context 'publish_future' to get custom post types that publish future posts.
	 *
	 * @since 2.5.0
	 * @param string $format  Accepts 'names', 'labels' or 'objects' Default 'names'.
	 * @param string $context Accepts 'date_archive', 'admin' and 'publish_future'.
	 *                        Default 'date_archive'. If no context is provided the default is used.
	 *
	 * @return array Array with post types depending on format and context.
	 */
	public function get_post_types( $format = 'names', $context = 'date_archive' ) {

		$post_types = $this->get_post_types_by_context( $context );
		if ( ! $post_types ) {
			return array();
		}

		if ( 'labels' === $format ) {
			foreach ( (array) $post_types as $key => $value ) {
				$post_types[ $key ] = esc_attr( $value->labels->menu_name );
			}
		}

		if ( 'names' === $format ) {
			$post_types = wp_list_pluck( $post_types, 'name', null );
			$post_types = array_values( $post_types );
		}

		return $post_types;
	}

	/**
	 * Returns custom post type objects depending on context.
	 *
	 * Use context 'date_archive' to get custom post types that have date archives support (Default).
	 * Use context 'admin' to get custom post types that are registered to appear in the admin menu.
	 * Use context 'publish_future' to get custom post types that publish future posts.
	 *
	 * @since 2.5.0
	 * @param string $context Accepts 'date_archive', 'admin' and 'publish_future'.
	 *                        Default 'date_archive'. If no context is provided the default is used.
	 * @return array Array with post type objects depending on context.
	 */
	public function get_post_types_by_context( $context = 'date_archive' ) {
		switch ( $context ) {
			case 'admin':
				$args = array(
					'show_ui'      => true,
					'show_in_menu' => true,
				);
				$post_types = wp_list_filter( $this->post_types, $args, 'AND' );
				break;
			case 'publish_future':
				$post_types = $this->filter_by_support( $this->post_types, 'publish-future-posts' );
				break;
			default:
				$post_types = $this->filter_by_support( $this->post_types, 'date-archives' );
				break;
		}

		return $post_types;
	}

	/**
	 * Filters array of post types by support.
	 *
	 * @since 2.5.0
	 *
	 * @param array  $post_types Array with post type objects.
	 * @param string $support    Support to filter by.
	 * @return array Array with post types filtered by support.
	 */
	private function filter_by_support( $post_types, $support ) {
		if ( ! is_array( $post_types ) ) {
			return array();
		}

		foreach ( $post_types as $name => $post_type ) {
			if ( ! post_type_supports( $name, $support ) ) {
				unset( $post_types[ $name ] );
			}
		}
		return $post_types;
	}

}
