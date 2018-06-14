<?php

namespace WSUWP\People_Directory;

require_once __DIR__ . '/profile-post-type.php';
require_once __DIR__ . '/rest-api.php';
require_once __DIR__ . '/user-profile.php';

if ( false === is_primary() ) {
	require_once __DIR__ . '/directory-page-template.php';
	require_once __DIR__ . '/profile-display.php';
	require_once __DIR__ . '/person-card-shortcode.php';
}

/**
 * Returns a version number for breaking cache and triggering upgrade routines.
 *
 * @since 0.1.0
 *
 * @return string The plugin version number.
 */
function version() {
	return '0.3.15';
}

/**
 * Returns the default API path for the primary directory.
 *
 * @since 0.3.15
 *
 * @return string The default API path.
 */
function API_path() {
	$default = 'https://people.wsu.edu/wp-json/';

	return apply_filters( 'wsu_people_directory_primary_api_path', $default );
}

/**
 * Determines if the current site is the primary directory.
 *
 * @since 0.3.0
 *
 * @return bool True if the main site, false if not.
 */
function is_primary() {
	return apply_filters( 'wsuwp_people_is_main_site', false );
}

add_action( 'init', __NAMESPACE__ . '\\add_global_cache_groups', 9 );
add_action( 'init', __NAMESPACE__ . '\\maybe_flush_rewrite_rules', 99 );

/**
 * Adds a global cache group for use across a multisite install.
 *
 * @since 0.3.0
 */
function add_global_cache_groups() {
	wp_cache_add_global_groups( 'wsuwp-people' );
}

/**
 * Flushes rewrite rules if the flag is set, then deletes the flag.
 *
 * @since 0.3.0
 */
function maybe_flush_rewrite_rules() {
	if ( get_transient( 'wsuwp_people_directory_flush_rewrites' ) ) {
		flush_rewrite_rules();
		delete_transient( 'wsuwp_people_directory_flush_rewrites' );
	}
}

/**
 * Flushes rewrite rules on deactivation.
 *
 * @since 0.3.0
 */
\register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

/**
 * Generates a nonce for use with REST API requests.
 *
 * This follows the same logic used in WordPress core, but also stores the user's
 * unique token in a global cache group so that it's accessible by sites that do
 * not have access to the user's cookie.
 *
 * @since 0.3.0
 *
 * @return string A nonce for a people directory REST request.
 */
function create_rest_nonce() {
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
function verify_rest_nonce( $nonce, $user_id, $domain ) {
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
