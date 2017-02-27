<?php

class WSUWP_People_REST_API {
	/**
	 * @var WSUWP_People_REST_API
	 *
	 * @since 0.3.0
	 */
	private static $instance;

	/**
	 * Maintain and return the one instance. Initiate hooks when called the first time.
	 *
	 * @since 0.3.0
	 *
	 * @return \WSUWP_People_REST_API
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSUWP_People_REST_API();
			self::$instance->setup_hooks();
		}
		return self::$instance;
	}

	/**
	 * Setup hooks to include.
	 *
	 * @since 0.3.0
	 */
	public function setup_hooks() {
		add_action( 'init', array( $this, 'show_people_in_rest' ), 12 );
		add_action( 'rest_api_init', array( $this, 'register_api_fields' ) );
		add_filter( 'rest_prepare_' . WSUWP_People_Post_Type::$post_type_slug, array( $this, 'photos_api_field' ), 10, 2 );

		add_action( 'init', array( $this, 'register_wsu_nid_query_var' ) );
		add_filter( 'rest_' . WSUWP_People_Post_Type::$post_type_slug . '_query', array( $this, 'rest_query_vars' ), 10, 2 );
		add_action( 'pre_get_posts', array( $this, 'handle_wsu_nid_query_var' ) );
	}

	/**
	 * Expose the people content type in the REST API.
	 *
	 * @since 0.2.0
	 */
	public function show_people_in_rest() {
		global $wp_post_types;

		$wp_post_types[ WSUWP_People_Post_Type::$post_type_slug ]->show_in_rest = true;
		$wp_post_types[ WSUWP_People_Post_Type::$post_type_slug ]->rest_base = 'people';
	}

	/**
	 * Register the custom meta fields attached to a REST API response containing people data.
	 *
	 * @since 0.2.0
	 */
	public function register_api_fields() {
		$args = array(
			'get_callback' => array( $this, 'get_api_meta_data' ),
			'update_callback' => null,
			'schema' => null,
		);
		foreach ( WSUWP_People_Post_Type::$post_meta_keys as $field_name => $value ) {
			if ( 'photos' === $field_name ) {
				continue;
			}

			register_rest_field( WSUWP_People_Post_Type::$post_type_slug, $field_name, $args );
		}
	}

	/**
	 * Return the value of a post meta field sanitized against a whitelist with the provided method.
	 *
	 * @since 0.2.0
	 *
	 * @param array           $object     The current post being processed.
	 * @param string          $field_name Name of the field being retrieved.
	 * @param WP_Rest_Request $request    The full current REST request.
	 *
	 * @return mixed Meta data associated with the post and field name.
	 */
	public function get_api_meta_data( $object, $field_name, $request ) {
		if ( ! array_key_exists( $field_name, WSUWP_People_Post_Type::$post_meta_keys ) ) {
			return '';
		}

		if ( 'sanitize_text_field' === WSUWP_People_Post_Type::$post_meta_keys[ $field_name ]['sanitize_callback'] ) {
			return esc_html( get_post_meta( $object['id'], WSUWP_People_Post_Type::$post_meta_keys[ $field_name ]['meta_key'], true ) );
		}

		if ( 'WSUWP_People_Directory::sanitize_repeatable_text_fields' === WSUWP_People_Post_Type::$post_meta_keys[ $field_name ]['sanitize_callback'] ) {
			$data = get_post_meta( $object['id'], WSUWP_People_Post_Type::$post_meta_keys[ $field_name ]['meta_key'], true );
			if ( is_array( $data ) ) {
				$data = array_map( 'esc_html', $data );
			} else {
				$data = array();
			}

			return $data;
		}

		if ( 'esc_url_raw' === WSUWP_People_Post_Type::$post_meta_keys[ $field_name ]['sanitize_callback'] ) {
			return esc_url( get_post_meta( $object['id'], WSUWP_People_Post_Type::$post_meta_keys[ $field_name ]['meta_key'], true ) );
		}

		if ( 'wp_kses_post' === WSUWP_People_Post_Type::$post_meta_keys[ $field_name ]['sanitize_callback'] ) {
			$data = get_post_meta( $object['id'], WSUWP_People_Post_Type::$post_meta_keys[ $field_name ]['meta_key'], true );
			$data = apply_filters( 'the_content', $data );
			return wp_kses_post( $data );
		}

		if ( 'cv_attachment' === $field_name ) {
			$cv_id = get_post_meta( $object['id'], WSUWP_People_Post_Type::$post_meta_keys[ $field_name ]['meta_key'], true );
			$cv_url = wp_get_attachment_url( $cv_id );

			if ( $cv_url ) {
				return esc_url( $cv_url );
			} else {
				return false;
			}
		}

		if ( 'profile_photo' === $field_name ) {
			$thumbnail_id = get_post_thumbnail_id( $object['id'] );
			if ( $thumbnail_id ) {
				$thumbnail = wp_get_attachment_image_src( $thumbnail_id );
				if ( $thumbnail ) {
					return esc_url( $thumbnail[0] );
				} else {
					return false;
				}
			}
		}

		return '';
	}

	/**
	 * Add links for attached photos to the REST API response.
	 *
	 * @since 0.3.0
	 *
	 * @param WP_REST_Response $response The current REST response object.
	 * @param WP_Post          $post     The current WP_Post object.
	 *
	 * @return WP_REST_Response
	 */
	public function photos_api_field( $response, $post ) {
		$photos = get_post_meta( $post->ID, '_wsuwp_profile_photos', true );

		foreach ( $photos as $photo_id ) {
			$response->add_link(
				'https://api.w.org/photos',
				esc_url( rest_url( '/wp/v2/media/' . $photo_id ) ),
				array( 'embeddable' => true )
			);
		}

		return $response;
	}

	/**
	 * Registers the wsu_nid parameter.
	 *
	 * @since 0.2.2
	 */
	public function register_wsu_nid_query_var() {
		global $wp;
		$wp->add_query_var( 'wsu_nid' );
	}

	/**
	 * Retrieves a passed wsu_nid with a REST request and adds to the query vars.
	 *
	 * @since 0.2.2
	 *
	 * @param array           $valid_vars
	 * @param WP_REST_Request $request
	 *
	 * @return array
	 */
	public function rest_query_vars( $valid_vars, $request ) {
		$valid_vars['wsu_nid'] = $request->get_param( 'wsu_nid' );

		return $valid_vars;
	}

	/**
	 * Sets a meta query for WSU NID when the wsu_nid parameters is included as
	 * part of a query.
	 *
	 * @since 0.2.2
	 *
	 * @param WP_Query $query
	 */
	public function handle_wsu_nid_query_var( $query ) {
		if ( isset( $query->query['wsu_nid'] ) && $query->query['wsu_nid'] ) {
			$query->set( 'meta_key', '_wsuwp_profile_ad_nid' );
			$query->set( 'meta_value', sanitize_text_field( $query->query['wsu_nid'] ) );
		}
	}
}
