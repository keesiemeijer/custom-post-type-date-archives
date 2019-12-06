<?php
/**
 * Rest API Recent Posts endpoint.
 *
 * @package     Custom_Post_Type_Date_Archives
 * @subpackage  Rest_API/Archives
 * @copyright   Copyright (c) 2019, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.6.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds a WordPress REST API endpoint to get custom post type archives.
 *
 * Registered endpoint: /wp-json/custom-post-type-date-archives/v1/{post_type}/archives
 *
 * @since 2.6.0
 */
class CPTDA_Rest_API_Archives extends WP_REST_Controller {

	/**
	 * Archive objects.
	 *
	 * @since 2.6.0
	 * @var array
	 */
	public $archives;

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
		$version   = '1';
		$namespace = 'custom_post_type_date_archives/v' . $version;
		$base      = 'archives';

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
		$error = new WP_Error( 'rest_invalid_args', __( 'Invalid post type', 'custom-post-type-date-archives' ), array( 'status' => 400 ) );

		$post_type = isset( $args['cptda_type'] ) ? $args['cptda_type'] : '';
		$types     = cptda_get_post_types();
		$types[]   = 'post';

		if ( ! $post_type || ! in_array( $post_type, $types ) ) {
			return $error;
		}

		unset( $args['cptda_type'] );

		$args = cptda_sanitize_archive_settings( $args );
		$args['post_type'] = $post_type;

		$args = $this->prepare_item_for_response( $args, $request );

		return rest_ensure_response( $args );
	}

	/**
	 * Check if a given request has access to get items
	 *
	 * @since 2.6.0
	 * @access public
	 *
	 * @return WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {
		/**
		 * Whether users are allowed to view archive Rest API items.
		 *
		 * @since 2.6.0
		 *
		 * @param bool $allowed Default true.
		 */
		return apply_filters( 'cptda_rest_api_archives', true, $request );
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
		$post_type = $args['post_type'];

		$defaults = cptda_get_archive_settings();
		$args     = array_merge( $defaults, $args );

		// Don't allow large queries.
		$args['limit'] = ( 100 >= $args['limit'] ) ? $args['limit'] : 100;

		/**
		 * Filter archive Rest API request arguments
		 *
		 * @since 2.7.0
		 *
		 * @param array $args    Sanitized Rest API request arguments.
		 * @param array $request Rest API request.
		 */
		$args = apply_filters( 'cptda_rest_api_archives_args', $args, $request );
		$args = cptda_validate_archive_settings( $args );

		// Unfiltarable argument
		$args['post_type'] = $post_type;

		$rendered = $this->get_archives( $args );

		$tags = wp_kses_allowed_html( 'post' );

		// Needed for dropdown
		if ( 'option' === $args['format'] ) {
			$allowed_tags = array(
				'select' => array(
					'id'       => true,
					'name'     => true,
					'onchange' => true,
				),
				'option' => array(
					'value' => true,
				),
			);

			$tags = array_merge( $tags, $allowed_tags );
		}

		// Archive arguments could contain HTML or Javascript.
		$rendered = wp_kses( $rendered, $tags );
		$rendered = $rendered ? $rendered : '';

		$data = array(
			'archives' => $this->archives,
			'rendered' => $rendered,
		);

		// Reset archives property
		$this->archives = array();

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		return $response;
	}

	/**
	 * Retrieves the archives schema, conforming to JSON Schema.
	 *
	 * @since 2.6.0
	 * @access public
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/schema#',
			'title'      => 'custom_post_type_date_archives',
			'type'       => 'object',
			'properties' => array(
				'archives' => array(
					'description' => __( 'Archive date objects.', 'custom-post-type-date-archives' ),
					'type'        => 'array',
					'items'       => array(
						'type'    => 'object',
					),
					'context'     => array( 'view' ),
				),
				'rendered' => array(
					'description' => __( 'Rendered archive HTML', 'custom-post-type-date-archives' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	public function archive_filter_callback( $output, $results, $r ) {
		$this->archives = $results;
		return $output;
	}

	private function get_archives( $args ) {
		$this->archives = array();

		add_filter( 'cptda_get_archives', array( $this, 'archive_filter_callback' ), 10, 3 );
		$archives = cptda_get_archives_html( $args );
		remove_filter( 'cptda_get_archives', array( $this, 'archive_filter_callback' ), 10, 3 );

		return $archives;
	}
}
