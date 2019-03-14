<?php
/**
 * Rest API Calendar endpoint.
 *
 * @package     Custom_Post_Type_Date_Archives
 * @subpackage  Rest_API/Calendar
 * @copyright   Copyright (c) 2019, Kees Meijer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.6.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds a WordPress REST API endpoint to get a custom post type calendar.
 *
 * Registered endpoint: /wp-json/custom-post-type-date-archives/v1/{post_type}/calendar
 *
 * @since 2.6.0
 */
class CPTDA_Rest_API_Calendar extends WP_REST_Controller {

	/**
	 * Register routes on rest_api_init.
	 *
	 * @since 2.6.0
	 */
	public function init() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Calendar HTML.
	 *
	 * @since 2.6.0
	 * @var string
	 */
	public $calendar;

	/**
	 * Calendar data
	 *
	 * @since 2.6.0
	 *
	 * @var array
	 */
	public $calendar_data;

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @since 2.6.0
	 */
	public function register_routes() {
		$version = '1';
		$namespace = 'custom_post_type_date_archives/v' . $version;
		$base = 'calendar';

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
		$error = new WP_Error( 'rest_invalid_args', __( 'Invalid calendar request.', 'custom-post-type-date-archives' ), array( 'status' => 404 ) );
		$data  = array();

		$post_type = isset( $args['cptda_type'] ) ? $args['cptda_type'] : '';
		$types     = cptda_get_post_types();
		$types[]   = 'post';

		if ( ! $post_type || ! in_array( $post_type, $types ) ) {
			return $error;
		}

		$year  = isset( $args['year'] ) ? absint( $args['year'] ) : '';
		$month = isset( $args['month'] ) ? absint( $args['month'] ) : '';

		if ( $year && $month ) {
			$args['year']  = $year;
			$args['month'] = $month;
		} else {
			$date = cptda_get_calendar_date();
			$args['year']  = $date['year'];
			$args['month'] = $date['month'];
		}

		if ( ! ( $args['year'] && $args['month'] ) ) {
			return $error;
		}

		$args['post_type'] = $post_type;
		unset( $args['cptda_type'] );

		$data = $this->prepare_item_for_response( $args, $request );

		return rest_ensure_response( $data );
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
		 * Whether users are allowed to view calendar Rest API items.
		 *
		 * @since 2.6.0
		 *
		 * @param bool $allowed Default true.
		 */
		return apply_filters( 'cptda_rest_api_calendar', true, $request );
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

		$calendar = $this->get_calendar( $args );

		$data = array(
			'post_type' => isset( $args['post_type'] ) ? $args['post_type'] : '',
			'year'      => isset( $args['year'] ) ? (int) $args['year'] : '',
			'month'     => isset( $args['month'] ) ? (int) $args['month'] : '',
			'date'      => $this->calendar_data,
			'rendered'  => $calendar,
		);

		// Reset filter_args.
		$this->calendar      = '';
		$this->calendar_data = array();

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		return $response;
	}

	/**
	 * Retrieves the calendar schema, conforming to JSON Schema.
	 *
	 * @since 2.6.0
	 * @access public
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/schema#',
			'title'      => 'custom_post_type_date_archives_calendar',
			'type'       => 'object',
			'properties' => array(
				'post_type'       => array(
					'description' => __( 'Calendar post type.', 'custom-post-type-date-archives' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
				'year'            => array(
					'description' => __( 'Calendar year.', 'custom-post-type-date-archives' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
				),
				'month'            => array(
					'description' => __( 'Calendar month.', 'custom-post-type-date-archives' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
				),
				'date'            => array(
					'description' => __( 'Calendar Date', 'custom-post-type-date-archives' ),
					'type'        => 'array',
					'items'       => array(
						'type'    => 'integer',
					),
					'context'     => array( 'view' ),
				),
				'rendered'            => array(
					'description' => __( 'Rendered calendar HTML', 'custom-post-type-date-archives' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Returns arguments used by the calendar.
	 *
	 * @since 2.6.0
	 * @access public
	 *
	 * @param string $calendar Calendar HTML.
	 * @param array  $date     Calendar date.
	 * @return string Calendar HTML.
	 */
	public function calendar_filter_callback( $calendar, $date ) {
		$this->calendar      = $calendar;
		$this->calendar_data = $date;
		return $calendar;
	}

	/**
	 * Returns calendar HTML.
	 *
	 * @since 2.6.0
	 * @access public
	 *
	 * @param array $args Arguments used to get the calendar.
	 * @return string Calendar HTML.
	 */
	public function get_calendar( $args ) {
		global $year, $monthnum;

		$this->calendar      = '';
		$this->calendar_data = array();
		$previous_year       = $year;
		$previous_monthnum   = $monthnum;

		if ( isset( $args['year'] ) && isset( $args['month'] ) ) {
			$monthnum = $args['month'];
			$year     = $args['year'];
		}

		add_filter( 'cptda_get_calendar', array( $this, 'calendar_filter_callback' ), 10, 2 );
		$calendar = cptda_get_calendar( $args['post_type'], true, false );
		remove_filter( 'cptda_get_calendar', array( $this, 'calendar_filter_callback' ), 10, 2 );

		$monthnum = $previous_monthnum;
		$year     = $previous_year;

		return $calendar;
	}
}
