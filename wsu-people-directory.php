<?php
/*
Plugin Name: WSU People Directory
Plugin URI: https://web.wsu.edu/wordpress/plugins/wsu-people-directory/
Description: A plugin to maintain a central directory of people.
Author:	washingtonstateuniversity, philcable, jeremyfelt
Version: 0.3.15
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// This plugin uses namespaces and requires PHP 5.3 or greater.
if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
	add_action( 'admin_notices', function() {
		echo '<div class="error"><p>WSU People Directory requires PHP 5.3 to function properly. Please upgrade PHP or deactivate the plugin.</p></div>';
	} );

	return;
} else {
	add_action( 'after_setup_theme', 'WSUWP_People_Directory' );

	/**
	 * Start things up.
	 */
	function WSUWP_People_Directory() {
		include_once __DIR__ . '/includes/wsuwp-people-directory.php';
	}
}
