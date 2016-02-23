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

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CPTDA_Post_Types.
 *
 * @since 1.0
 * @author keesiemeijer
 */
class CPTDA_Post_Types {

	private $post_types      = array();
	private $post_type_names = array();
	private $future_status   = array();

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
			'public'       => true,
			'_builtin'     => false,
			'has_archive'  => true,
		);

		$this->post_types = get_post_types( $args, 'objects', 'and' );

		foreach ( (array) $this->post_types as $name => $post_type ) {

			if ( post_type_supports( $name, 'publish-future-posts' ) ) {
				$this->future_status[] = $name;
			}

			if ( post_type_supports( $name, 'date-archives' ) ) {
				$this->post_type_names[] = $name;
			} else {
				unset( $this->post_types[ $name ] );
			}
		}

		if ( !empty( $this->future_status ) ) {

			/**
			 * Publish posts with future dates as normal.
			 * 
			 * @param bool $publish Default true.
			 */
			$publish = apply_filters( 'cptda_publish_future_posts', true );

			if ( (bool) $publish ) {
				foreach ( $this->future_status as $name ) {
					remove_action( "future_{$name}", '_future_post_hook' );
					add_action( "future_{$name}", array( $this, 'publish_future_post' ) );
				}
			}
		}
	}


	/**
	 * Reset post type properties.
	 *
	 * @since 2.1.0
	 * @return void
	 */
	function reset_post_types() {
		$this->post_types      = array();
		$this->post_type_names = array();
		$this->future_status   = array();
	}


	/**
	 * Add support to post types from admin page settings.
	 *
	 * @since 2.1.0
	 * @return void
	 */
	function setup_admin_post_types() {
		$archives = get_option( 'custom_post_type_date_archives' );

		if ( empty( $archives ) ) {
			return;
		}

		foreach ( array( 'date_archives', 'publish_future_posts' ) as $support ) {
			if ( isset( $archives[ $support ] ) && !empty( $archives[ $support ] ) ) {
				$post_types = is_array( $archives[ $support ] ) ? $archives[ $support ] : array();
				$this->add_admin_post_types_support( array_keys( $post_types ), $support );
			}
		}
	}


	/**
	 * Add support to post type from admin settings
	 *
	 * @since 2.1.0
	 * @param array   $archives Array with date archive post types
	 * @param string  $support  Type of support. 'date_archives' or 'publish_future_posts'
	 * @return void
	 */
	function add_admin_post_types_support( $archives, $support = 'date-archives' ) {

		if ( empty( $archives ) || !is_array( $archives ) ) {
			return;
		}

		$post_types = cptda_get_admin_post_types();

		foreach ( $post_types as $post_type => $value ) {
			if ( in_array( $post_type, $archives ) ) {
				add_post_type_support( $post_type, str_replace( '_', '-', $support ) );
			} else {
				remove_post_type_support( $post_type, str_replace( '_', '-', $support ) );
			}
		}
	}


	/**
	 * Set new post's post_status to "publish" if the post is sceduled.
	 *
	 * @since 1.2
	 * @param int     $post_id Post ID.
	 * @return void
	 */
	public function publish_future_post( $post_id ) {

		$post = get_post( $post_id );

		/**
		 * Publish posts with future dates from a specific post type as normal.
		 * 
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
	 * @param string  $type Type of return array.
	 * @return string Array of post types that support post types.
	 */
	public function get_date_archive_post_types( $type = 'names' ) {

		$post_types = $this->post_type_names;

		if ( 'objects' === $type ) {
			$post_types = $this->post_types;
		}

		if ( 'labels' === $type ) {
			$post_types = array();
			foreach ( $this->post_types as $key => $value ) {
				$post_types[$key] = esc_attr( $value->labels->menu_name );
			}
		}

		if ( 'future_status' === $type ) {
			$post_types = $this->future_status;
		}

		return $post_types;
	}


	/**
	 * Returns the base of a custom post type depending on the rewrite parameter.
	 * Uses the post type's rewrite parameter 'with_front' and 'slug'.
	 *
	 * @since 1.0
	 * @param string  $post_type Post type.
	 * @return array  Array with front and slug from the custom post type.
	 */
	public function get_post_type_base( $post_type = '' ) {
		global $wp_rewrite;

		if ( !isset( $this->post_types[ $post_type ] ) ) {
			return array( 'front' => '', 'slug' => '' );
		}

		$post_type = $this->post_types[ $post_type ];

		$front = isset( $post_type->rewrite['with_front'] ) ? (bool) $post_type->rewrite['with_front'] : 1;
		$front = $front ? $wp_rewrite->front : $wp_rewrite->root;

		// Check if rewrite slug is set for the post type.
		$slug = isset( $post_type->rewrite['slug'] ) ? $post_type->rewrite['slug'] : '';
		$slug = !empty( $slug ) ? $slug : $post_type->name;

		return compact( 'front', 'slug' );

	}


	/**
	 * Gets the base slug for the post type depending on the post type's parameters.
	 *
	 * @since 1.0
	 * @uses get_post_type_base()
	 * @param string  $post_type Post type.
	 * @return string Post type base (front + slug).
	 */
	public function get_post_type_base_slug( $post_type = '' ) {
		$base = $this->get_post_type_base( $post_type );
		return ltrim( trailingslashit( $base['front'] ) . $base['slug'] , '/' );
	}

}
