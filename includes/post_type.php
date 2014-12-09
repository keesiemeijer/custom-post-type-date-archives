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

		$this->post_types  = get_post_types( array( 'public' => true, '_builtin' => false ), 'objects', 'and' );

		foreach ( (array) $this->post_types as $name => $post_type ) {

			$has_archive = $post_type->has_archive;
			$support     = post_type_supports( $name, 'date-archives' );

			if (  !empty( $has_archive ) && $support ) {
				$this->post_type_names[] = $name;
			} else {
				unset( $this->post_types[ $name ] );
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