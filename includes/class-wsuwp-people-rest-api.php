<?php

class WSUWP_People_REST_API {
	/**
	 * @var WSUWP_People_REST_API
	 *
	 * @since 0.3.0
	 */
	private static $instance;

	/**
	 * A list of post meta keys to include in a REST API response from secondary sites.
	 *
	 * @since 0.3.8
	 *
	 * @var array
	 */
	public $secondary_post_meta_keys = array(
		'primary_profile_id' => '_wsuwp_profile_post_id',
		'display_photo' => '_use_photo',
		'display_title' => '_use_title',
		'display_bio' => '_use_bio',
	);

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

		// Fires before the default cookie authentication check.
		add_filter( 'rest_authentication_errors', array( $this, 'rest_verify_authentication' ), 90 );

		add_action( 'rest_api_init', array( $this, 'custom_access_control_headers' ) );
		add_action( 'rest_api_init', array( $this, 'register_api_fields' ) );

		add_filter( 'rest_prepare_' . WSUWP_People_Post_Type::$post_type_slug, array( $this, 'photos_api_field' ), 10, 2 );
		add_action( 'init', array( $this, 'show_university_taxonomies_in_rest' ), 12 );

		add_action( 'init', array( $this, 'register_wsu_nid_query_var' ) );
		add_filter( 'rest_' . WSUWP_People_Post_Type::$post_type_slug . '_query', array( $this, 'rest_query_vars' ), 10, 2 );
		add_filter( 'rest_' . WSUWP_People_Post_Type::$post_type_slug . '_collection_params', array( $this, 'rest_collection_params' ) );
		add_action( 'pre_get_posts', array( $this, 'handle_wsu_nid_query_var' ) );

		if ( false === WSUWP_People_Directory::is_main_site() ) {
			add_action( 'rest_api_init', array( $this, 'register_secondary_api_fields' ) );
		}

		add_action( 'rest_api_init', array( $this, 'profile_propagation' ) );
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
	 * Authenticates a cross domain request to edit a person record.
	 *
	 * @since 0.3.0
	 *
	 * @global wp $wp
	 *
	 * @param WP_Error|mixed $result Error from another authentication handler. null if we
	 *                               should handle it, another value if not.
	 *
	 * @return WP_Error|null|bool WP_Error if authentication error.
	 *                            null if authentication wasn't used.
	 *                            true if authentication succeeded.
	 */
	public function rest_verify_authentication( $result ) {
		global $wp;

		if ( ! empty( $result ) ) {
			return $result;
		}

		// Only perform custom authentication on the people endpoint.
		if ( 0 !== strpos( $wp->request, 'wp-json/wp/v2/people' ) && 0 !== strpos( $wp->request, 'wp-json/wsuwp-people/v1' ) ) {
			return null;
		}

		// Determine if there is a nonce.
		$nonce = null;

		if ( isset( $_REQUEST['_wpnonce'] ) ) { // @codingStandardsIgnoreLine
			$nonce = $_REQUEST['_wpnonce']; // @codingStandardsIgnoreLine
		} elseif ( isset( $_SERVER['HTTP_X_WP_NONCE'] ) ) {
			$nonce = $_SERVER['HTTP_X_WP_NONCE'];
		}

		$uid = null;

		if ( isset( $_SERVER['HTTP_X_WSUWP_UID'] ) ) {
			$uid = absint( $_SERVER['HTTP_X_WSUWP_UID'] );
		}

		$domain = null;

		if ( isset( $_SERVER['HTTP_ORIGIN'] ) ) {
			$domain = wp_parse_url( $_SERVER['HTTP_ORIGIN'], PHP_URL_HOST );
		}

		if ( null === $nonce || ! $uid || empty( $domain ) ) {
			// No nonce at all, so act as if it's an unauthenticated request.
			wp_set_current_user( 0 );
			return true;
		}

		// Check the nonce.
		$result = WSUWP_People_Directory::verify_rest_nonce( $nonce, $uid, $domain );

		if ( ! $result ) {
			return new WP_Error( 'rest_cookie_invalid_nonce', __( 'Cookie nonce is invalid' ), array(
				'status' => 403,
			) );
		}

		wp_set_current_user( $uid );

		return true;
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
	 * Adds `X-WP-Nonce` and `X-WSUWP-UID` to the CORS headers supplied by default
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

		register_rest_field( WSUWP_People_Post_Type::$post_type_slug, 'taxonomy_terms', array(
			'get_callback' => array( $this, 'get_api_taxonomy_data' ),
			'update_callback' => array( $this, 'update_api_taxonomy_data' ),
			'schema' => array(
				'description' => 'Taxonomy terms associated with the profile',
				'type' => 'object',
				'context' => array( 'view', 'edit' ),
				'items' => array(
					'type' => 'array',
				),
			),
		) );
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
	 * Return an array of term names and slugs keyed by taxonomy name.
	 *
	 * @since 0.3.0
	 *
	 * @param array           $object     The current post being processed.
	 * @param string          $field_name Name of the field being retrieved.
	 * @param WP_Rest_Request $request    The full current REST request.
	 *
	 * @return mixed Taxonomy data associated with the post.
	 */
	public function get_api_taxonomy_data( $object, $field_name, $request ) {
		if ( 'taxonomy_terms' !== $field_name ) {
			return null;
		}

		$data = array();

		$taxonomies = get_object_taxonomies( WSUWP_People_Post_Type::$post_type_slug );

		foreach ( $taxonomies as $taxonomy ) {
			$terms = get_the_terms( $object['id'], $taxonomy );

			if ( is_array( $terms ) ) {
				foreach ( $terms as $term ) {
					$data[ $taxonomy ][] = array(
						'name' => $term->name,
						'slug' => $term->slug,
					);
				}
			}
		}

		return $data;
	}

	/**
	 * Update the taxonomy terms associated with a post.
	 *
	 * @since 0.3.15
	 *
	 * @param mixed  $value      The updated taxonomy data.
	 * @param object $object     The object from the response.
	 * @param string $field_name Name of the current field.
	 *
	 * @return mixed
	 */
	public function update_api_taxonomy_data( $value, $object, $field_name ) {
		if ( ! $field_name && ! $value ) {
			return;
		}

		if ( 'taxonomy_terms' !== $field_name ) {
			return;
		}

		foreach ( $value as $taxonomy => $terms ) {
			// Categories and tags are ignored for now to avoid term contamination.
			if ( 'category' === $taxonomy || 'post_tag' === $taxonomy ) {
				continue;
			}

			if ( array( 'wsuwp_people_empty_terms' ) === $terms ) {
				$new_terms = '';
			} else {
				$new_terms = array();

				foreach ( $terms as $term_name ) {
					$term = get_term_by( 'name', sanitize_text_field( $term_name ), $taxonomy );

					if ( $term ) {
						$new_terms[] = absint( $term->term_id );
					}
				}
			}

			wp_set_object_terms( $object->ID, $new_terms, $taxonomy );
		}

		return '';
	}

	/**
	 * Expose the University taxonomies in the REST API.
	 *
	 * @since 0.3.0
	 */
	public function show_university_taxonomies_in_rest() {
		global $wp_taxonomies;

		if ( taxonomy_exists( 'wsuwp_university_category' ) ) {
			$wp_taxonomies['wsuwp_university_category']->show_in_rest = true;
			$wp_taxonomies['wsuwp_university_category']->rest_base = 'university_category';
		}

		if ( taxonomy_exists( 'wsuwp_university_location' ) ) {
			$wp_taxonomies['wsuwp_university_location']->show_in_rest = true;
			$wp_taxonomies['wsuwp_university_location']->rest_base = 'location';
		}

		if ( taxonomy_exists( 'wsuwp_university_org' ) ) {
			$wp_taxonomies['wsuwp_university_org']->show_in_rest = true;
			$wp_taxonomies['wsuwp_university_org']->rest_base = 'organization';
		}
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
	 * Increases the per page maximum for request responses to 200.
	 *
	 * @since 0.3.14
	 *
	 * @param array $query_params JSON Schema-formatted collection parameters.
	 *
	 * @return array
	 */
	public function rest_collection_params( $query_params ) {
		$query_params['per_page']['maximum'] = 200;

		return $query_params;
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

	/**
	 * Register secondary custom meta fields attached to a REST API response containing people data.
	 *
	 * @since 0.3.8
	 */
	public function register_secondary_api_fields() {
		$args = array(
			'get_callback' => array( $this, 'get_secondary_api_meta_data' ),
		);

		foreach ( $this->secondary_post_meta_keys as $rest_key => $field_name ) {
			register_rest_field( WSUWP_People_Post_Type::$post_type_slug, $rest_key, $args );
		}
	}

	/**
	 * Return the value of a post meta field sanitized against a whitelist with the provided method.
	 *
	 * @since 0.3.8
	 *
	 * @param array  $object   The current post being processed.
	 * @param string $rest_key Name of the field being retrieved.
	 *
	 * @return mixed Meta data associated with the post and field name.
	 */
	public function get_secondary_api_meta_data( $object, $rest_key ) {
		if ( ! array_key_exists( $rest_key, $this->secondary_post_meta_keys ) ) {
			return '';
		}

		return esc_html( get_post_meta( $object['id'], $this->secondary_post_meta_keys[ $rest_key ], true ) );
	}

	/**
	 * Registers a REST route for updating the `listed_on` meta for multiple
	 * profiles in a single call.
	 *
	 * @since 0.3.15
	 */
	public function profile_propagation() {
		register_rest_route( 'wsuwp-people/v1', '/sync', array(
			'methods' => WP_REST_Server::EDITABLE,
			'callback' => array( $this, 'update_primary_profile_listing_data' ),
			'args' => array(
				'ids' => array(
					'validate_callback' => function( $param, $request, $key ) {
						return is_array( $param );
					},
					'sanitize_callback' => function( $param, $request, $key ) {
						return array_map( 'absint', $param );
					},
				),
				'site_url' => array(
					'validate_callback' => function( $param, $request, $key ) {
						return $param;
					},
					'sanitize_callback' => function( $param, $request, $key ) {
						return esc_url_raw( $param );
					},
				),
			),
		) );
	}

	/**
	 * Updates the `listed_on` meta for each post passed in the `ids` parameter.
	 *
	 * @since 0.3.15
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Request
	 */
	public function update_primary_profile_listing_data( $request ) {
		$post_ids = $request->get_param( 'ids' );
		$site_url = $request->get_param( 'site_url' );

		if ( ! $post_ids || ! $site_url ) {
			return new WP_Error( 'invalid_parameters', __( 'Invalid or empty parameters' ), array(
				'status' => 403,
			) );
		}

		$data = array();

		foreach ( $post_ids as $post_id ) {
			$listings = get_post_meta( $post_id, '_wsuwp_profile_listed_on', true );

			if ( $listings ) {
				if ( ! in_array( $site_url, $listings, true ) ) {
					$listings[] = $site_url;
					$update = update_post_meta( $post_id, '_wsuwp_profile_listed_on', $listings );
				} else {
					$update = 'no update required';
				}
			} else {
				$update = update_post_meta( $post_id, '_wsuwp_profile_listed_on', array( $site_url ) );
			}

			$data[ $post_id ] = $update;
		}

		return new WP_REST_Response( $data, 200 );
	}
}
