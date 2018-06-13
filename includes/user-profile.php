<?php

namespace WSUWP\People_Directory\User_Profile;

add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\admin_enqueue_scripts' );
add_action( 'admin_init', __NAMESPACE__ . '\\biography_filter' );
add_action( 'show_user_profile', __NAMESPACE__ . '\\biography_editor' );
add_action( 'edit_user_profile', __NAMESPACE__ . '\\biography_editor' );
add_filter( 'wp_editor_settings', __NAMESPACE__ . '\\filter_default_editor_settings', 10, 2 );

/**
 * Enqueues assets for the Your Profile and Edit User page.
 *
 * @since 0.3.0
 *
 * @param string $hook_suffix
 */
function admin_enqueue_scripts( $hook_suffix ) {
	if ( ! in_array( $hook_suffix, array( 'profile.php', 'user-edit.php' ), true ) ) {
		return;
	}

	global $user_id;

	if ( 'nid' !== get_user_meta( $user_id, '_wsuwp_sso_user_type', true ) ) {
		return;
	}

	wp_enqueue_style( 'wsuwp-people-user-profile', plugins_url( 'css/admin-user-profile.css', dirname( __FILE__ ) ), array(), \WSUWP\People_Directory\version() );
	wp_enqueue_script( 'wsuwp-people-user-profile', plugins_url( 'js/admin-user-profile.min.js', dirname( __FILE__ ) ), array( 'jquery' ), \WSUWP\People_Directory\version(), true );
	wp_localize_script( 'wsuwp-people-user-profile', 'wsuwp', array(
		'people' => array(
			'rest_url' => \WSUWP\People_Directory\API_path() . 'wp/v2/people',
			'nid' => get_userdata( $user_id )->user_login,
			'nonce' => \WSUWP\People_Directory\create_rest_nonce(),
			'uid' => wp_get_current_user()->ID,
		),
	) );
}

/**
 * Affords user biographies the same HTML allowed in post content.
 *
 * @since 0.3.0
 */
function biography_filter() {
	remove_filter( 'pre_user_description', 'wp_filter_kses' );
	add_filter( 'pre_user_description', 'wp_filter_post_kses' );
}

/**
 * Replaces the Biographical Info textarea with a TinyMCE editor.
 *
 * @since 0.3.0
 *
 * @param object $user
 */
function biography_editor( $user ) {
	if ( 'nid' !== get_user_meta( $user->ID, '_wsuwp_sso_user_type', true ) ) {
		return;
	}

	wp_nonce_field( 'save-wsuwp-person-data', '_wsuwp_people_user_nonce' );
	?>
	<table class="form-table wsuwp-people-bio-wrap">
		<tr>
			<th>Biographical Info</th>
			<td>
				<?php
				$description = get_user_meta( $user->ID, 'description', true );
				$settings = array(
					'textarea_rows' => 5,
				);
				wp_editor( $description, 'description', $settings );
				?>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * Adds an init callback to the tinyMCE editor to help mitigate race conditions
 * when populating with data from the user's people.wsu.edu profile.
 *
 * @since 0.3.3
 *
 * @param array $settings
 * @param string $editor_id
 *
 * @return array
 */
function filter_default_editor_settings( $settings, $editor_id ) {
	if ( ! is_admin() ) {
		return $settings;
	}

	if ( ! in_array( get_current_screen()->id, array( 'profile', 'user-edit' ), true ) ) {
		return $settings;
	}

	if ( isset( $settings['tinymce'] ) && is_array( $settings['tinymce'] ) ) {
		$settings['tinymce']['init_instance_callback'] = 'wsuwp.people.populate_editor';
	} else {
		$settings['tinymce'] = array(
			'init_instance_callback' => 'wsuwp.people.populate_editor',
		);
	}

	return $settings;
}
