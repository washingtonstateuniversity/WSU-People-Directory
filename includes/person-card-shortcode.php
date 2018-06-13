<?php

namespace WSUWP\People_Directory\Profile_Shortcode;

add_shortcode( 'wsuwp_person_card', __NAMESPACE__ . '\\display_wsuwp_person_card' );
add_action( 'register_shortcode_ui', __NAMESPACE__ . '\\person_card_shortcode_ui' );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\admin_enqueue_scripts' );

/**
 * Checks if a theme is providing its own template.
 *
 * @since 0.3.0
 *
 * @return string Path to the template file.
 */
function theme_has_template() {
	return locate_template( 'wsu-people/person-card-shortcode.php' );
}

/**
 * Displays a person's card.
 *
 * @since 0.3.0
 *
 * @param array $atts Attributes passed with the shortcode.
 *
 * @return string Data to output where the shortcode is used.
 */
function display_wsuwp_person_card( $atts ) {
	$defaults = array(
		'name' => '',
		'nid' => '',
		'cache_bust' => '',
	);

	$atts = shortcode_atts( $defaults, $atts );

	if ( empty( $atts['nid'] ) ) {
		return '';
	}

	wp_enqueue_style( 'wsu-person-card', plugin_dir_url( dirname( __FILE__ ) ) . 'css/person-card.css', array(), \WSUWP\People_Directory\version() );

	$cache_key = md5( wp_json_encode( $atts ) );

	$cached_content = wp_cache_get( $cache_key, 'wsuwp_person_card' );

	if ( $cached_content ) {
		return $cached_content;
	}

	$nid = sanitize_text_field( $atts['nid'] );

	$person = WSUWP\People_Directory\Profile_Post_Type\get_rest_data( $nid );

	if ( ! $person ) {
		return '';
	}

	$template = ( theme_has_template() ) ? theme_has_template() : plugin_dir_path( dirname( __FILE__ ) ) . 'templates/person-card-shortcode.php';

	$display_options = array(
		'header' => true,
		'link_url' => ( $person->website ) ? $person->website : $person->link,
	);

	$display = WSUWP\People_Directory\Profile_Display\get_local_profile( $person, $display_options );

	ob_start();

	require_once $template;

	$content = ob_get_clean();

	wp_cache_set( $cache_key, $content, 'wsuwp_person_card', 1800 );

	return $content;
}

/**
 * Adds Shortcode UI support for the person card shortcode.
 *
 * @since 0.3.0
 */
function person_card_shortcode_ui() {
	$args = array(
		'label' => 'Person Card',
		'listItemImage' => 'dashicons-admin-users',
		'post_type' => array( 'post', 'page' ),
		'attrs' => array(
			array(
				'label' => 'Search by name',
				'attr' => 'name',
				'type' => 'text',
				'description' => 'Start typing to search for a person.',
			),
			array(
				'label' => 'Or enter NID',
				'attr' => 'nid',
				'type' => 'text',
				'description' => "If you know the person's nid, you can enter it directly.",
			),
		),
	);

	shortcode_ui_register_for_shortcode( 'wsuwp_person_card', $args );
}

/**
 * Enqueues the JavaScript used in the Shortcode UI interface.
 *
 * @since 0.3.0
 */
function admin_enqueue_scripts() {
	wp_enqueue_script( 'wsuwp-person-card-shortcode', plugins_url( 'js/admin-card-shortcode.min.js', dirname( __FILE__ ) ), array( 'jquery-ui-autocomplete' ), \WSUWP\People_Directory\version(), true );
	wp_localize_script( 'wsuwp-person-card-shortcode', 'wsupersoncard', array(
		'rest_url' => \WSUWP\People_Directory\API_path() . 'wp/v2/people',
	) );
}
