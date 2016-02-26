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

			if ( !post_type_supports( $name, 'date-archives' ) ) {
				unset( $this->date_post_types[ $name ] );
			}
		}

		if ( !empty( $this->publish_future ) ) {

			/**
			 * Filter whether to publish posts with future dates as normal posts.
			 *
			 * @since 1.0
			 * @param bool    $publish Default true.
			 */
			$publish = apply_filters( 'cptda_publish_future_posts', true );

			if ( (bool) $publish ) {
				foreach ( $this->publish_future as $name ) {
					remove_action( "future_{$name}", '_future_post_hook' );
					add_action( "future_{$name}", array( $this, 'publish_future_post' ) );
				}
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
		 * Filter whether to publish posts with future dates from a specific post type.
		 *
		 * @since 1.2
		 * @param bool    $publish Default true.
		 */
		$publish = apply_filters( "cptda_publish_future_{$post->post_type}", true );
		if ( (bool) $publish ) {
			wp_publish_post( $post_id );
		}
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
	 * Returns post types that support date archives.
	 *
	 * @since 1.0
	 * @param string  $type Type of return array.
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
				$post_types[$key] = esc_attr( $value->labels->menu_name );
			}
		}

		if ( 'publish_future' === $type ) {
			$post_types = $this->publish_future;
		}

		if ( !empty( $this->date_post_types ) && ( 'names' === $type ) ) {
			$post_types = wp_list_pluck( $this->date_post_types, 'name' );
		}

		return $post_types;
	}


	/**
	 * Check if a post type is valid to be used as date archive post type
	 *
	 * @since 2.1.0
	 * @param string  $post_type Post type name
	 * @return boolean            True if it's a valid post type
	 */
	function is_valid_post_type( $post_type ) {

		$post_type = get_post_type_object ( trim( (string) $post_type ) );

		if ( !( isset( $post_type->public ) && $post_type->public ) ) {
			return false;
		}

		if ( !( isset( $post_type->publicly_queryable ) && $post_type->publicly_queryable ) ) {
			return false;
		}

		if ( !( isset( $post_type->has_archive ) && $post_type->has_archive ) ) {
			return false;
		}

		if ( !isset( $post_type->_builtin ) ) {
			return false;
		}

		return $post_type->_builtin ? false : true;
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

		if ( !$this->is_valid_post_type( $post_type ) ) {
			return array( 'front' => '', 'slug' => '' );
		}

		$post_type = get_post_type_object ( trim( (string) $post_type ) );

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
