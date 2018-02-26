<?php

class WSUWP_People_User_Profile {
	/**
	 * @var WSUWP_People_User_Profile
	 *
	 * @since 0.3.0
	 */
	private static $instance;

	/**
	 * Maintain and return the one instance. Initiate hooks when called the first time.
	 *
	 * @since 0.3.0
	 *
	 * @return \WSUWP_People_User_Profile
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSUWP_People_User_Profile();
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
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_init', array( $this, 'biography_filter' ) );
		add_action( 'show_user_profile', array( $this, 'biography_editor' ) );
		add_action( 'edit_user_profile', array( $this, 'biography_editor' ) );
		add_filter( 'wp_editor_settings', array( $this, 'filter_default_editor_settings' ), 10, 2 );
	}

	/**
	 * Enqueues stylesheets for the Your Profile/Edit User page.
	 *
	 * @since 0.3.0
	 *
	 * @param string $hook_suffix
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {
		if ( ! in_array( $hook_suffix, array( 'profile.php', 'user-edit.php' ), true ) ) {
			return;
		}

		global $user_id;

		if ( 'nid' !== get_user_meta( $user_id, '_wsuwp_sso_user_type', true ) ) {
			return;
		}

		wp_enqueue_style( 'wsuwp-people-user-profile', plugins_url( 'css/admin-user-profile.css', dirname( __FILE__ ) ), array(), WSUWP_People_Directory::$version );
		wp_enqueue_script( 'wsuwp-people-user-profile', plugins_url( 'js/admin-user-profile.min.js', dirname( __FILE__ ) ), array( 'jquery' ), WSUWP_People_Directory::$version, true );
		wp_localize_script( 'wsuwp-people-user-profile', 'wsuwp', array(
			'people' => array(
				'rest_url' => WSUWP_People_Directory::REST_URL(),
				'nid' => get_userdata( $user_id )->user_login,
				'nonce' => WSUWP_People_Directory::create_rest_nonce(),
				'uid' => wp_get_current_user()->ID,
			),
		) );
	}

	/**
	 * Allows for more HTML in user biographies.
	 */
	public function biography_filter() {
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
	public function biography_editor( $user ) {
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
	public function filter_default_editor_settings( $settings, $editor_id ) {
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
}
