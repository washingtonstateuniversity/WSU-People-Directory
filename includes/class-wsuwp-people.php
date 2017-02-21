<?php

class WSUWP_People {
	/**
	 * @var WSUWP_People
	 */
	private static $instance;

	/**
	 * The plugin version number, used to break caches and trigger
	 * upgrade routines.
	 *
	 * @var string
	 */
	public static $version = '0.2.2';

	/**
	 * The slug used to register the people post type.
	 *
	 * @var string
	 */
	public static $post_type_slug = 'wsuwp_people_profile';

	/**
	 * The slugs used to register taxonomies used by the people post type.
	 *
	 * @var string
	 */
	public static $taxonomy_slug_appointments = 'appointment';
	public static $taxonomy_slug_classifications = 'classification';

	/**
	 * A list of post meta keys associated with a person.
	 *
	 * @var array
	 */
	public static $post_meta_keys = array(
		'nid' => array(
			'type' => 'ad',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_ad_nid',
		),
		'first_name' => array(
			'type' => 'ad',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_ad_name_first',
		),
		'last_name' => array(
			'type' => 'ad',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_ad_name_last',
		),
		'position_title' => array(
			'type' => 'ad',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_ad_title',
		),
		'office' => array(
			'type' => 'ad',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_ad_office',
		),
		'address' => array(
			'type' => 'ad',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_ad_address',
		),
		'phone' => array(
			'type' => 'ad',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_ad_phone',
		),
		'phone_ext' => array(
			'type' => 'ad',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_ad_phone_ext',
		),
		'email' => array(
			'type' => 'ad',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_ad_email',
		),
		'office_alt' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_alt_office',
		),
		'phone_alt' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_alt_phone',
		),
		'email_alt' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_alt_email',
		),
		'website' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'esc_url_raw',
			'meta_key' => '_wsuwp_profile_website',
		),
		'degree' => array(
			'type' => 'array',
			'description' => '',
			'sanitize_callback' => 'WSUWP_People_Post_Type::sanitize_repeatable_text_fields',
			'meta_key' => '_wsuwp_profile_degree',
		),
		'working_titles' => array(
			'type' => 'array',
			'description' => '',
			'sanitize_callback' => 'WSUWP_People_Post_Type::sanitize_repeatable_text_fields',
			'meta_key' => '_wsuwp_profile_title',
		),
		'bio_unit' => array(
			'type' => 'textarea',
			'description' => 'Unit Biography',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_bio_unit',
		),
		'bio_university' => array(
			'type' => 'textarea',
			'description' => 'University Biography',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_bio_university',
		),
		'photos' => array(
			'type' => 'array',
			'description' => 'A collection of photos',
			'sanitize_callback' => 'WSUWP_People_Post_Type::sanitize_photos',
			'meta_key' => '_wsuwp_profile_photos',
		),
		// Legacy
		'bio_college' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_bio_college',
		),
		'bio_lab' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_bio_lab',
		),
		'bio_department' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_bio_dept',
		),
		'cv_employment' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_employment',
		),
		'cv_honors' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_honors',
		),
		'cv_grants' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_grants',
		),
		'cv_publications' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_publications',
		),
		'cv_presentations' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_presentations',
		),
		'cv_teaching' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_teaching',
		),
		'cv_service' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_service',
		),
		'cv_responsibilities' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_responsibilities',
		),
		'cv_affiliations' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_societies',
		),
		'cv_experience' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_experience',
		),
		'cv_attachment' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'attachment',
			'meta_key' => '_wsuwp_profile_cv',
		),
		'profile_photo' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'attachment',
			'meta_key' => '',
		),
	);

	/**
	 * Maintain and return the one instance. Initiate hooks when called the first time.
	 *
	 * @since 0.3.0
	 *
	 * @return \WSUWP_People
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSUWP_People();
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
		require_once( dirname( __FILE__ ) . '/class-post-type.php' );
		require_once( dirname( __FILE__ ) . '/class-taxonomies.php' );
		require_once( dirname( __FILE__ ) . '/class-rest-api.php' );

		add_action( 'init', 'WSUWP_People_Post_Type' );
		add_action( 'init', 'WSUWP_People_Taxonomies' );
		add_action( 'init', 'WSUWP_People_REST_API' );
	}
}
