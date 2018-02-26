<?php

class WSUWP_People_Directory {
	/**
	 * @var WSUWP_People_Directory
	 */
	private static $instance;

	/**
	 * The plugin version number, used to break caches and trigger
	 * upgrade routines.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	public static $version = '0.3.13';

	/**
	 * Maintain and return the one instance. Initiate hooks when called the first time.
	 *
	 * @since 0.3.0
	 *
	 * @return \WSUWP_People_Directory
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSUWP_People_Directory();
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
		require_once dirname( __FILE__ ) . '/class-wsuwp-people-post-type.php';
		require_once dirname( __FILE__ ) . '/class-wsuwp-people-classification-taxonomy.php';
		require_once dirname( __FILE__ ) . '/class-wsuwp-people-user-profile.php';
		require_once dirname( __FILE__ ) . '/class-wsuwp-people-rest-api.php';

		add_action( 'init', 'WSUWP_People_Post_Type' );
		add_action( 'init', 'WSUWP_People_Classification_Taxonomy' );
		add_action( 'init', 'WSUWP_People_User_Profile' );
		add_action( 'init', 'WSUWP_People_REST_API' );

		add_action( 'init', array( $this, 'add_global_cache_groups' ), 9 );

		if ( false === self::is_main_site() ) {
			require_once dirname( __FILE__ ) . '/class-wsuwp-people-directory-page-template.php';
			require_once dirname( __FILE__ ) . '/class-wsuwp-person-display.php';
			require_once dirname( __FILE__ ) . '/class-wsuwp-person-card-shortcode.php';

			add_action( 'init', 'WSUWP_People_Directory_Page_Template' );
			add_action( 'init', 'WSUWP_Person_Display' );
			add_action( 'init', 'WSUWP_Person_Card_Shortcode' );
		}

		add_action( 'init', array( $this, 'maybe_flush_rewrite_rules' ), 99 );
	}

	/**
	 * Adds a global cache group for use with the people directory
	 * across a multisite install.
	 *
	 * @since 0.3.0
	 */
	public function add_global_cache_groups() {
		wp_cache_add_global_groups( 'wsuwp-people' );
	}

	/**
	 * Determines if the current site is the main people directory.
	 *
	 * @return bool True if the main site. False if not.
	 */
	public static function is_main_site() {
		return apply_filters( 'wsuwp_people_is_main_site', false );
	}

	/**
	 * The default REST route for the main people directory.
	 *
	 * @since 0.3.2
	 *
	 * @return string
	 */
	public static function REST_Route() {

		$default = 'https://people.wsu.edu/wp-json/wp/v2/';

		return apply_filters( 'wsu_people_directory_rest_route', $default );
	}

	/**
	 * The REST request URL.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public static function REST_URL() {

		$default = 'https://people.wsu.edu/wp-json/wp/v2/people';

		return apply_filters( 'wsu_people_directory_rest_url', $default );
	}

	/**
	 * Generates a nonce for use with people directory REST API requests.
	 *
	 * This follows the same logic used in WordPress core, but also stores the user's
	 * unique token in a global cache group so that it's accessible by sites that do
	 * not have access to the user's cookie.
	 *
	 * @since 0.3.0
	 *
	 * @return string A nonce for a people directory REST request.
	 */
	public static function create_rest_nonce() {
		$user = wp_get_current_user();
		$uid = (int) $user->ID;

		$token = wp_get_session_token();
		$key = $uid . ':' . get_site()->domain;

		// Store the token in a global cache group.
		wp_cache_set( $key, $token, 'wsuwp-people' );

		$i = wp_nonce_tick();

		return substr( wp_hash( $i . '|wsuwp_people|' . $uid . '|' . $token, 'nonce' ), -12, 10 );
	}

	/**
	 * Verifies a nonce generated for use with the people directory REST API endpoint.
	 *
	 * This follows the same logic used in WordPress core, but retrieves the user's unique
	 * token from a global cache group rather than the user's cookies, which are not
	 * accessible during a CORS request.
	 *
	 * @since 0.3.0
	 *
	 * @param string $nonce   The one time use token.
	 * @param int    $user_id The requesting user's ID.
	 * @param string $domain  The origin domain.
	 *
	 * @return bool|int False if the nonce is invalid. 1 if generated 0-12 hours ago. 2 if 12-24 hours ago.
	 */
	public static function verify_rest_nonce( $nonce, $user_id, $domain ) {
		$nonce = (string) $nonce;
		$uid = (int) $user_id;
		$domain = (string) $domain;
		$action = 'wsuwp_people';

		if ( empty( $nonce ) || empty( $user_id ) || empty( $domain ) ) {
			return false;
		}

		$token = wp_cache_get( $uid . ':' . $domain, 'wsuwp-people' );
		$i = wp_nonce_tick();

		// Nonce generated 0-12 hours ago
		$expected = substr( wp_hash( $i . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );
		if ( hash_equals( $expected, $nonce ) ) {
			return 1;
		}

		// Nonce generated 12-24 hours ago
		$expected = substr( wp_hash( ( $i - 1 ) . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );
		if ( hash_equals( $expected, $nonce ) ) {
			return 2;
		}

		// Invalid nonce
		return false;
	}

	/**
	 * If the flag for flushing rewrite rules is set, flush them and delete the flag.
	 *
	 * @since 0.3.0
	 */
	public function maybe_flush_rewrite_rules() {
		if ( get_transient( 'wsuwp_people_directory_flush_rewrites' ) ) {
			flush_rewrite_rules();
			delete_transient( 'wsuwp_people_directory_flush_rewrites' );
		}
	}
}
