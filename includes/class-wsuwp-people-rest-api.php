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

		add_action( 'rest_api_init', array( $this, 'custom_access_control_headers' ) );
		add_action( 'rest_api_init', array( $this, 'register_api_fields' ) );

		add_filter( 'rest_prepare_' . WSUWP_People_Post_Type::$post_type_slug, array( $this, 'photos_api_field' ), 10, 2 );
		add_action( 'init', array( $this, 'show_university_taxonomies_in_rest' ), 12 );

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
	 * Removes the default WordPress core CORS headers and adds a
	 * custom replacement.
	 *
	 * @since 0.3.0
	 */
	public function custom_access_control_headers() {
		remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
		add_filter( 'rest_pre_serve_request', array( $this, 'rest_send_cors_headers' ) );
	}

	/**
	 * Adds `X-WP-Nonce` and `X-WSUWP-NID` to the CORS headers supplied by default
	 * in WordPress core.
	 *
	 * @since 0.3.0
	 *
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public function rest_send_cors_headers( $value ) {
		$origin = get_http_origin();

		if ( $origin ) {
			header( 'Access-Control-Allow-Origin: ' . esc_url_raw( $origin ) );
			header( 'Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, PATCH, DELETE' );
			header( 'Access-Control-Allow-Credentials: true' );
			header( 'Access-Control-Allow-Headers: X-WP-Nonce, X-WSUWP-UID' );
			header( 'Vary: Origin' );
		}

		return $value;
	}

	/**
	 * Register the custom meta fields attached to a REST API response containing people data.
	 *
	 * @since 0.2.0
	 */
	public function register_api_fields() {
		$args = array(
			'get_callback' => array( $this, 'get_api_meta_data' ),
		);

		foreach ( WSUWP_People_Post_Type::$post_meta_keys as $field_name => $value ) {
			if ( isset( $value['updatable_via_rest'] ) ) {
				$args['update_callback'] = array( $this, 'update_api_meta_data' );
				$args['schema'] = $this->rest_field_schema( $value );
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

		if ( 'WSUWP_People_Post_Type::sanitize_repeatable_text_fields' === WSUWP_People_Post_Type::$post_meta_keys[ $field_name ]['sanitize_callback'] ) {
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
			$data = trim( apply_filters( 'the_content', $data ) );
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

		if ( 'photos' === $field_name ) {
			$data = get_post_meta( $object['id'], WSUWP_People_Post_Type::$post_meta_keys[ $field_name ]['meta_key'], true );
			$photos = array();

			if ( is_array( $data ) ) {
				$sizes = get_intermediate_image_sizes();
				$sizes[] = 'full';
				foreach ( $data as $index => $photo_id ) {
					foreach ( $sizes as $size ) {
						$image = wp_get_attachment_image_src( $photo_id, $size );
						if ( $image ) {
							$photos[ $index ][ $size ] = $image[0];
						}
					}
				}
			}

			return $photos;
		}

		return '';
	}

	/**
	 * Update the value of a post meta field sanitized against a whitelist with the provided method.
	 *
	 * @since 0.2.0
	 *
	 * @param  mixed  $value      Post views count
	 * @param  object $object     The object from the response
	 * @param  string $field_name Name of the current field
	 *
	 * @return mixed
	 */
	public function update_api_meta_data( $value, $object, $field_name ) {
		if ( ! array_key_exists( $field_name, WSUWP_People_Post_Type::$post_meta_keys ) ) {
			return;
		}

		if ( ! $field_name && ! $value ) {
			return;
		}

		if ( 'sanitize_text_field' === WSUWP_People_Post_Type::$post_meta_keys[ $field_name ]['sanitize_callback'] ) {
			$value = sanitize_text_field( $value );
			return update_post_meta( $object->ID, WSUWP_People_Post_Type::$post_meta_keys[ $field_name ]['meta_key'], $value );
		}

		if ( 'WSUWP_People_Post_Type::sanitize_repeatable_text_fields' === WSUWP_People_Post_Type::$post_meta_keys[ $field_name ]['sanitize_callback'] ) {

			if ( is_array( $value ) ) {
				$value = ( 'listed_on' === $field_name ) ? array_map( 'esc_url_raw', $value ) : array_map( 'sanitize_text_field', $value );
			} else {
				$value = ( 'listed_on' === $field_name ) ? esc_url_raw( $value ) : sanitize_text_field( $value );
			}

			return update_post_meta( $object->ID, WSUWP_People_Post_Type::$post_meta_keys[ $field_name ]['meta_key'], $value );
		}

		if ( 'esc_url_raw' === WSUWP_People_Post_Type::$post_meta_keys[ $field_name ]['sanitize_callback'] ) {
			$value = esc_url_raw( $value );
			return update_post_meta( $object->ID, WSUWP_People_Post_Type::$post_meta_keys[ $field_name ]['meta_key'], $value );
		}

		if ( 'wp_kses_post' === WSUWP_People_Post_Type::$post_meta_keys[ $field_name ]['sanitize_callback'] ) {
			$value = trim( apply_filters( 'the_content', $value ) );
			$value = wp_kses_post( $value );
			return update_post_meta( $object->ID, WSUWP_People_Post_Type::$post_meta_keys[ $field_name ]['meta_key'], $value );
		}

		return '';
	}

	/**
	 * Set up the schema for each registered field.
	 *
	 * @since 0.3.0
	 *
	 * @param array @args
	 *
	 * @return array
	 */
	public function rest_field_schema( $args ) {
		$schema = array(
			'description' => $args['description'],
			'type' => $args['type'],
			'context' => array( 'view', 'edit' ),
		);

		if ( 'array' === $args['type'] ) {
			$schema['items'] = $args['items'];
		}

		return $schema;
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

		if ( $photos ) {
			foreach ( $photos as $photo_id ) {
				$response->add_link(
					'https://api.w.org/photos',
					esc_url( rest_url( '/wp/v2/media/' . $photo_id ) ),
					array(
						'embeddable' => true,
					)
				);
			}
		}

		return $response;
	}

	/**
	 * Expose the University taxonomies in the REST API.
	 *
	 * @since 0.3.0
	 */
	public function show_university_taxonomies_in_rest() {
		global $wp_taxonomies;

		$wp_taxonomies['wsuwp_university_category']->show_in_rest = true;
		$wp_taxonomies['wsuwp_university_category']->rest_base = 'university_category';

		$wp_taxonomies['wsuwp_university_location']->show_in_rest = true;
		$wp_taxonomies['wsuwp_university_location']->rest_base = 'location';

		$wp_taxonomies['wsuwp_university_org']->show_in_rest = true;
		$wp_taxonomies['wsuwp_university_org']->rest_base = 'organization';
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
