<?php
class CPTDA_UnitTestCase extends WP_UnitTestCase {

	public $boolean;

	/**
	 * Creates posts with future and publish dates.
	 *
	 * @param string  $post_type      Post type.
	 * @param integer $posts_per_page How may posts to create.
	 * @return array                  Array with post ids.
	 */
	function create_posts( $post_type = 'cpt' ) {

		if ( ! post_type_exists( $post_type ) ) {
			$this->register_post_type( $post_type );
		}

		$now = time();

		// WP < 4.4.
		if ( ! defined( 'MONTH_IN_SECONDS' ) ) {
			define( 'MONTH_IN_SECONDS',  30 * DAY_IN_SECONDS );
		}

		$dates = array(

			// Future dates.
			$now + ( YEAR_IN_SECONDS * 2 ),
			$now + YEAR_IN_SECONDS,
			$now + MONTH_IN_SECONDS * 2,
			$now + MONTH_IN_SECONDS,
			$now + DAY_IN_SECONDS * 2,
			$now + DAY_IN_SECONDS,

			// Publish dates.
			$now,

			$now - DAY_IN_SECONDS,
			$now - DAY_IN_SECONDS * 2,
			$now - MONTH_IN_SECONDS,
			$now - MONTH_IN_SECONDS * 2,
			$now - YEAR_IN_SECONDS,
			$now - YEAR_IN_SECONDS * 2,
		);

		// Create posts with timestamps.
		$i = 1;
		foreach ( $dates as $date ) {
			$id = $this->factory->post->create(
				array(
					'post_date' => date( 'Y-m-d H:i:s', $date ),
					'post_type' => $post_type,
				)
			);
		}

		// Return posts by desc date.
		$posts = get_posts(
			array(
				'posts_per_page' => -1,
				'post_type'      => $post_type,
				'fields'         => 'ids',
				'order'          => 'DESC',
				'orderby'        => 'date',
			)
		);

		return $posts;
	}

	function get_back_compat_latest_posts_class() {
		$version = $GLOBALS['wp_version'];
		$class = 'wp-block-latest-posts';

		// WordPress added a className in WP 5.3
		if ( version_compare( $version , '5.2', '>' ) ) {
			$class .= ' wp-block-latest-posts__list';
		}

		return $class;
	}


	function register_post_type( $post_type = 'cpt', $rewrite = false ) {

		$args = array(
			'public' => true,
			'has_archive' => true,
			'label' => 'Custom Post Type',
		);

		if ( $rewrite ) {
			$args['rewrite'] = $rewrite;
		}

		register_post_type( $post_type, $args );
	}


	function future_init( $post_type = 'cpt', $rewrite = false ) {
		$this->init( $post_type, 'future', $rewrite );
	}


	function init( $post_type = 'cpt', $type = 'publish', $rewrite = false ) {
		_delete_all_posts();
		$this->unregister_post_type( $post_type );
		$supports = ( 'future' === $type ) ? array( 'date-archives', 'publish-future-posts' ) : array( 'date-archives' );
		$this->register_post_type( $post_type, $rewrite );
		$this->cpt_setup( $post_type, $supports );
	}


	function cpt_setup( $post_type = 'cpt', $supports = 'date-archives' ) {
		add_post_type_support( $post_type, $supports );
		$plugin = cptda_date_archives();
		$plugin->post_type->setup();
	}

	function unregister_post_type( $post_type = 'cpt' ) {

		global $wp_rewrite;

		delete_option( 'rewrite_rules' );
		$this->delete_settings();

		$plugin = cptda_date_archives();

		remove_post_type_support( $post_type, 'date-archives' );
		remove_post_type_support( $post_type, 'publish-future-posts' );
		remove_action( 'future_' . $post_type, '_future_post_hook', 5 );
		remove_action( "future_{$post_type}", array( $plugin->post_type, 'publish_future_post' ) );

		global $wp_post_types;
		if ( isset( $wp_post_types[ $post_type ] ) ) {
			unset( $wp_post_types[ $post_type ] );
		}

		unset( $wp_rewrite->extra_permastructs[ $post_type ] );

		$plugin->post_type = new CPTDA_Post_Types();
		$plugin->post_type->setup();
	}


	function return_bool( $bool ) {
		return $this->boolean = $bool;
	}

	function delete_settings() {
		delete_option( 'custom_post_type_date_archives' );
	}


	/**
	 * Utility method that resets permalinks and flushes rewrites.
	 *
	 * @since 4.4.0
	 *
	 * @global WP_Rewrite $wp_rewrite
	 *
	 * @param string $structure Optional. Permalink structure to set. Default empty.
	 */
	public function set_permalink_structure( $structure = '' ) {
		global $wp_rewrite;

		$wp_rewrite->init();
		$wp_rewrite->set_permalink_structure( $structure );
		$wp_rewrite->flush_rules();
	}
}
