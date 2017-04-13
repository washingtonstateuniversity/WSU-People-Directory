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

		wp_enqueue_style( 'wsuwp-people-user-profile', plugins_url( 'css/admin-user-profile.css', dirname( __FILE__ ) ), array(), WSUWP_People_Directory::$version );
		wp_enqueue_script( 'wsuwp-people-user-profile', plugins_url( 'js/admin-user-profile.min.js', dirname( __FILE__ ) ), array( 'jquery' ), WSUWP_People_Directory::$version, true );
		wp_localize_script( 'wsuwp-people-user-profile', 'wsupeople', array(
			'rest_url' => WSUWP_People_Directory::REST_URL(),
			'nid' => get_userdata( $user_id )->user_login,
			'nonce' => WSUWP_People_Directory::create_rest_nonce(),
			'uid' => wp_get_current_user()->ID,
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
					<input type="hidden" name="wsuwp_person_id" value="" />
				</td>
			</tr>
		</table>
		<?php
	}
}
