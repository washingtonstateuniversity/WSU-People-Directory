<?php
/*
Plugin Name: WSU People Directory
Plugin URI: https://web.wsu.edu/wordpress/plugins/wsu-people-directory/
Description: A plugin to maintain a central directory of people.
Author:	washingtonstateuniversity, CAHNRS, philcable, danialbleile, jeremyfelt
Version: 0.2.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// The core plugin class.
require dirname( __FILE__ ) . '/includes/class-wsuwp-people.php';

add_action( 'after_setup_theme', 'WSUWP_People' );
/**
 * Start things up.
 *
 * @return \WSUWP_People
 */
function WSUWP_People() {
	return WSUWP_People::get_instance();
}

/**
 * Retrieve the instance of the WSU People post type and meta data handler.
 *
 * @since 0.3.0
 *
 * @return WSUWP_People_Post_Type
 */
function WSUWP_People_Post_Type() {
	return WSUWP_People_Post_Type::get_instance();
}

/**
 * Retrieve the instance of the WSU People taxonomy handler.
 *
 * @since 0.3.0
 *
 * @return WSUWP_People_Taxonomies
 */
function WSUWP_People_Taxonomies() {
	return WSUWP_People_Taxonomies::get_instance();
}

/**
 * Retrieve the instance of the WSU People REST API handler.
 *
 * @since 0.3.0
 *
 * @return WSUWP_People_REST_API
 */
function WSUWP_People_REST_API() {
	return WSUWP_People_REST_API::get_instance();
}
