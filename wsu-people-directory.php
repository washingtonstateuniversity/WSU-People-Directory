<?php
/*
Plugin Name: WSU People Directory
Plugin URI: https://web.wsu.edu/wordpress/plugins/wsu-people-directory/
Description: A plugin to maintain a central directory of people.
Author:	washingtonstateuniversity, CAHNRS, philcable, danialbleile, jeremyfelt
Version: 0.3.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Flush rewrite rules on deactivation.
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

// The core plugin class.
require dirname( __FILE__ ) . '/includes/class-wsuwp-people-directory.php';

add_action( 'after_setup_theme', 'WSUWP_People_Directory' );
/**
 * Start things up.
 *
 * @return \WSUWP_People_Directory
 */
function WSUWP_People_Directory() {
	return WSUWP_People_Directory::get_instance();
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
 * Retrieve the instance of the WSU People Classification taxonomy.
 *
 * @since 0.3.0
 *
 * @return WSUWP_People_Classification_Taxonomy
 */
function WSUWP_People_Classification_Taxonomy() {
	return WSUWP_People_Classification_Taxonomy::get_instance();
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

/**
 * Retrieve the instance of the People Directory page template.
 *
 * @since 0.3.0
 *
 * @return WSUWP_People_Directory_Page_Template
 */
function WSUWP_People_Directory_Page_Template() {
	return WSUWP_People_Directory_Page_Template::get_instance();
}

/**
 * Retrieve the instance of the Person Display handler.
 *
 * @since 0.3.0
 *
 * @return WSUWP_Person_Display
 */
function WSUWP_Person_Display() {
	return WSUWP_Person_Display::get_instance();
}

/**
 * Retrieve the instance of the person card shortcode.
 *
 * @since 0.3.0
 *
 * @return WSUWP_Person_Card_Shortcode
 */
function WSUWP_Person_Card_Shortcode() {
	return WSUWP_Person_Card_Shortcode::get_instance();
}

/**
 * Retrieve the instance of the user profile handler.
 *
 * @since 0.3.0
 *
 * @return WSUWP_People_User_Profile
 */
function WSUWP_People_User_Profile() {
	return WSUWP_People_User_Profile::get_instance();
}
