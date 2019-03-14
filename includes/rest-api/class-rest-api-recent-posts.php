<?php
/**
 * Rest API Recent Posts endpoint.
 *
 * @package     Custom_Post_Type_Date_Archives
 * @subpackage  Rest_API/Recent_Posts
 * @copyright   Copyright (c) 2019, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.6.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds a WordPress REST API endpoint to get recent posts.
 *
 * Registered endpoint: /wp-json/custom-post-type-date-archives/v1/{post_type}/recent-posts
 *
 * @since 2.6.0
 */
class CPTDA_Rest_API_Recent_Posts extends WP_REST_Controller {

	/**
	 * Register routes on rest_api_init.
	 *
	 * @since 2.6.0
	 */
	public function init() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @since 2.6.0
	 */
	public function register_routes() {
		$version = '1';
		$namespace = 'custom_post_type_date_archives/v' . $version;
		$base = 'recent-posts';

		register_rest_route( $namespace, '/(?P<cptda_type>[\w-]+)/' . $base, array(
				'args' => array(
					'cptda_type' => array(
						'description' => __( 'An alphanumeric identifier for the post type.' ),
						'type'        => 'string',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => array(
						'context' => $this->get_context_param( array( 'default' => 'view' ) ),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Get one item from the collection.
	 *
	 * @since 2.6.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$args  = $request->get_params();
		$error = new WP_Error( 'rest_invalid_args', __( 'Invalid post type', 'custom-post-type-date-archives' ), array( 'status' => 404 ) );
		$data  = array();

		$post_type = isset( $args['cptda_type'] ) ? $args['cptda_type'] : '';
		$types     = cptda_get_post_types();
		$types[]   = 'post';

		if ( ! $post_type || ! in_array( $post_type, $types ) ) {
			return $error;
		}

		$defaults = cptda_get_recent_posts_settings();
		$args     = wp_parse_args( $args, $defaults );

		$args['post_type'] = $post_type;
		unset( $args['cptda_type'] );

		$args = cptda_sanitize_recent_posts_settings( $args );
		$args = $this->prepare_item_for_response( $args, $request );

		return rest_ensure_response( $args );
	}

	/**
	 * Check if a given request has access to get items
	 *
	 * @since 2.6.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {
		/**
		 * Whether users are allowed to view recent posts Rest API items.
		 *
		 * @since 2.6.0
		 *
		 * @param bool $allowed Default true.
		 */
		return apply_filters( 'cptda_rest_api_recent_posts', true, $request );
	}

	/**
	 * Check if a given request has access to get a specific item
	 *
	 * @since 2.6.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_item_permissions_check( $request ) {
		return $this->get_items_permissions_check( $request );
	}

	/**
	 * Prepare the item for the REST response
	 *
	 * @since 2.6.0
	 * @access public
	 *
	 * @param array           $args    WP Rest API arguments of the item.
	 * @param WP_REST_Request $request Request object.
	 * @return mixed
	 */
	public function prepare_item_for_response( $args, $request ) {
		// Default recent posts number is 5
		$number = $args['number'] ? $args['number'] : 5;

		// Don't allow queries over 100 posts
		$args['number'] = ( 100 >= $number ) ? $number : 100;

		// Don't allow title and message arguments.
		$blacklisted = array(
			'title',
			'before_title',
			'after_title',
			'message',
		);

		foreach ( $blacklisted as $value ) {
			$args[ $value ] = '';
		}

		$query_args   = cptda_get_recent_posts_query( $args );
		$recent_posts = get_posts( $query_args );
		$rendered     = cptda_get_recent_posts_html( $recent_posts, $args );

		$data = array(
			'posts'    => $recent_posts,
			'rendered' => $rendered,
		);

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		return $response;
	}

	/**
	 * Retrieves the recent posts schema, conforming to JSON Schema.
	 *
	 * @since 2.6.0
	 * @access public
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/schema#',
			'title'      => 'custom_post_type_date_archives_recent_posts',
			'type'       => 'object',
			'properties' => array(
				'posts' => array(
					'description' => __( 'Recent Posts post type.', 'custom-post-type-date-archives' ),
					'type'        => 'array',
					'items'       => array(
						'type'    => 'object|integer|string',
					),
					'context'     => array( 'view' ),
				),
				'rendered' => array(
					'description' => __( 'Rendered recent posts HTML', 'custom-post-type-date-archives' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}
}
