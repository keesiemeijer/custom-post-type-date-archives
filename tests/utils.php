<?php
class CPTDA_Test_Utils {

	private $factory;

	function __construct( $factory = null ) {
		$this->factory = $factory;
	}

	/**
	 * Creates posts with future and publish dates.
	 *
	 * @param string  $post_type      Post type.
	 * @param integer $posts_per_page How may posts to create.
	 * @return array                  Array with post ids.
	 */
	function create_posts( $post_type = 'cpt' ) {

		if ( !post_type_exists( $post_type ) ) {
			$this->register_post_type( $post_type );
		}

		$now = time();

		$dates = array(

			// future dates
			$now + ( YEAR_IN_SECONDS * 2 ),
			$now + YEAR_IN_SECONDS,
			$now + MONTH_IN_SECONDS * 2,
			$now + MONTH_IN_SECONDS,
			$now + DAY_IN_SECONDS * 2,
			$now + DAY_IN_SECONDS ,

			// publish dates
			$now,

			$now - DAY_IN_SECONDS ,
			$now - DAY_IN_SECONDS * 2,
			$now - MONTH_IN_SECONDS,
			$now - MONTH_IN_SECONDS * 2,
			$now - YEAR_IN_SECONDS,
			$now - YEAR_IN_SECONDS * 2,
		);


		// create posts with timestamps
		$i = 1;
		foreach ( $dates as $date ) {
			$id = $this->factory->post->create(
				array(
					'post_date' => date( 'Y-m-d H:i:s', $date ),
					'post_type' => $post_type,
				) );
		}

		// Return posts by desc date.
		$posts = get_posts(
			array(
				'posts_per_page' => -1,
				'post_type'      => $post_type,
				'fields'         => 'ids',
				'order'          => 'DESC',
				'orderby'        => 'date',
			) );

		return $posts;
	}


	function register_post_type( $post_type = 'cpt', $rewrite = false ) {

		$args = array( 'public'      => true, 'has_archive' => true );
		if($rewrite) {
			$args['rewrite'] = $rewrite;
		}
		register_post_type( $post_type, $args );
	}


	function future_init( $post_type = 'cpt', $rewrite = false ) {
		$this->init( $post_type, 'future', $rewrite );
	}


	function init( $post_type = 'cpt', $type = 'publish', $rewrite = false ) {
		$this->unregister_post_type( $post_type );
		$date_archives = ( 'future' === $type ) ? array( 'date-archives', 'publish-future-posts' ) : array( 'date-archives' );
		$this->register_post_type( $post_type, $rewrite );
		add_post_type_support( $post_type, $date_archives );
		$plugin = cptda_date_archives();
		$plugin->post_type->setup();
	}


	function unregister_post_type( $post_type = 'cpt' ) {

		global $wp_post_types;
		if ( isset( $wp_post_types[ $post_type ] ) ) {
			unset( $wp_post_types[ $post_type ] );
		}

		remove_post_type_support ( $post_type, 'date-archives' );
		remove_post_type_support ( $post_type, 'publish-future-posts' );

		$plugin = cptda_date_archives();
		$plugin->post_type = new CPTDA_Post_Types();
	}
}