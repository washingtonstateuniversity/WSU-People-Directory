<?php
/*
Plugin Name: WSU People Directory
Plugin URI:	#
Description: A plugin to maintain a central directory of people.
Author:	washingtonstateuniversity, CAHNRS, philcable, jeremyfelt
Version: 0.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

class WSUWP_People_Directory {

	/**
	 * The slug used to register the "Personnel" custom content type.
	 *
	 * @var string
	 */
	var $personnel_content_type = 'wsuwp_people_profile';

	/**
	 * The fields used throughout the metaboxes (temp).
	 */
	var $personnel_fields = array(
		'_wsuwp_profile_display_name',
		'_wsuwp_profile_teaching_name',
		'_wsuwp_profile_research_name',
		'_wsuwp_profile_alt_phone',
		'_wsuwp_profile_alt_email',
		'_wsuwp_profile_website',
		'_wsuwp_profile_degree',
		'_wsuwp_profile_cv'
	);

	public function __construct() {

		add_action( 'init', array( $this, 'register_personnel_content_type' ), 11 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'edit_form_after_title', array( $this, 'edit_form_after_title' ) );
		add_action( 'edit_form_after_editor',	array( $this, 'edit_form_after_editor' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );

	}

	/**
	 * Register the project content type.
	 */
	public function register_personnel_content_type() {
		$args = array(
			'labels' => array(
				'name' => 'Personnel',
				'singular_name' => 'Profile',
				'all_items' => 'All Profiles',
				'view_item' => 'View Profile',
				'add_new_item' => 'Add New Profile',
				'add_new' => 'Add New',
				'edit_item' => 'Edit Profile',
				'update_item' => 'Update Profile',
				'search_items' => 'Search Profiles',
				'not_found' => 'Not found',
				'not_found_in_trash' => 'Not found in Trash',
			),
			'description' => 'WSU people directory listings',
			'public' => true,
			'hierarchical' => false,
			'menu_position' => 5,
			'menu_icon' => 'dashicons-groups',
			'supports' => array(
				'title',
				'editor',
				'author',
				'thumbnail',
				'revisions'
			),
			'taxonomies' => array(
				'category',
				'post_tag'
			),
			'has_archive' => true,
			'rewrite' => array(
				'slug' => 'listing',
				'with_front' => false
			),
		);
		register_post_type( $this->personnel_content_type, $args );
	}

	/**
	 * Enqueue the scripts and styles used in the admin interface.
	 */
	public function admin_enqueue_scripts( $hook ) {
		$screen = get_current_screen();
		if ( ( $hook == 'post-new.php' || $hook == 'post.php' ) && $screen->post_type == $this->personnel_content_type ) {
			wp_enqueue_script( 'wsuwp-people-admin-script', plugins_url( 'js/admin-profile.js', __FILE__ ), array( 'jquery-ui-tabs' ), '', true );
			wp_enqueue_style( 'wsuwp-people-admin-style', plugins_url( 'css/admin-profile-style.css', __FILE__ ) );
		}
	}

	/**
	 * Add stuff after the title of the edit screen for the Personnel content type.
	 */
	public function edit_form_after_title( $post ) {
		if ( $post->post_type === $this->personnel_content_type ) :
			?>
			<div id="wsuwp-profile-tabs">
        <ul>
          <li><a href="#wsuwp-profile-default" class="nav-tab">Bio</a></li>
          <li><a href="#wsuwp-profile-teaching" class="nav-tab">Teaching</a></li>
          <li><a href="#wsuwp-profile-research" class="nav-tab">Research</a></li>
          <li><a href="#wsuwp-profile-publications" class="nav-tab">Publications</a></li>
        </ul>
        <div id="wsuwp-profile-default">
          <p class="description">Consider including professional experience, previous employment, awards, honors, memberships, or other information you wish to share about yourself here.</p>
          <h3 class="wpuwp-profile-label"><label for="_wsuwp_profile_display_name">Default Display Name</label></h3>
          <input type="text" id="_wsuwp_profile_display_name" name="_wsuwp_profile_display_name" value="<?php echo get_post_meta( $post->ID, '_wsuwp_profile_display_name', true ); ?>" class="widefat wsuwp-profile-namefield" />
			<?php
			do_meta_boxes( get_current_screen(), 'after-title', $post ); // maybe better to hardcode
		endif;
	}

	/**
	 * Add stuff after the editor of the edit screen for the Personnel content type.
	 */
	public function edit_form_after_editor($post ) {
		if ( $post->post_type === $this->personnel_content_type ) {
			?>
        </div><!--wsuwp-profile-default-->
  
        <div id="wsuwp-profile-teaching">
          <p>(Could be cool to conditionally show this tab if user has a teaching appointment.)</p>
          <p class="description">Your teaching responsibilities, classes you teach, etc.</p>
          <h3 class="wpuwp-profile-label"><label for="_wsuwp_profile_teaching_name">Teaching Profile Display Name</label></h3>
          <p class="description">(if different than default)</p>
          <input type="text" id="_wsuwp_profile_teaching_name" name="_wsuwp_profile_teaching_name" value="<?php echo get_post_meta( $post->ID, '_wsuwp_profile_teaching_name', true ); ?>" class="widefat wsuwp-profile-namefield" /></p>
          <?php
            wp_editor( get_post_meta( $post->ID, '_wsuwp_profile_teaching', true ), '_wsuwp_profile_teaching' );
          ?>
        </div>
  
        <div id="wsuwp-profile-research">
          <p>(Could be cool to conditionally show this tab if user has a research appointment.)</p>
          <p class="description">Information about your research interests, recent funding/funded projects/grant submissions, grad students/program personnel/research team, research facilities, collaborators, patents, etc.</p>
          <h3 class="wpuwp-profile-label"><label for="_wsuwp_profile_research_name">Research Profile Display Name</label></h3>
          <p class="description">(if different than default)</p>
          <input type="text" id="_wsuwp_profile_research_name" name="_wsuwp_profile_research_name" value="<?php echo get_post_meta( $post->ID, '_wsuwp_profile_research_name', true ); ?>" class="widefat wsuwp-profile-namefield" /></p>
          <?php
            wp_editor( get_post_meta( $post->ID, '_wsuwp_profile_research', true ), '_wsuwp_profile_research' );
          ?>
        </div>

				<div id="wsuwp-profile-publications">
          <p>(This section would ideally include a way for users to dynamically pull in a feed from the pubs store, and a wp_editor for manually inputting book chapters, professional articles, peer-reviewed exhibitions, juried artistic works, and other publications that wouldn't be in the pubs store.)</p>
        </div>

			</div><!--wsuwp-profile-tabs-->
			<?php
		}
	}

	/**
	 * Add the meta boxes used for the Personnel content type.
	 *
	 * @param string $post_type The slug of the current post type.
	 */
	public function add_meta_boxes( $post_type ) {

		add_meta_box(
			'wsuwp_people_position_info',
			'Position and Contact Information',
			array( $this, 'display_position_info_meta_box' ),
			$this->personnel_content_type,
			'side',
			'high'
		);
		
		add_meta_box(
			'wsuwp_people_contact_info',
			'Alternate Contact Information',
			array( $this, 'display_contact_info_meta_box' ),
			$this->personnel_content_type,
			'side',
			'high'
		);
		
		add_meta_box(
			'wsuwp_people_degree_info',
			'Degree Information',
			array( $this, 'display_degree_info_meta_box' ),
			$this->personnel_content_type,
			'after-title',
			'high'
		);
		
		add_meta_box(
			'wsuwp_people_cv_upload',
			'Curriculum Vitae',
			array( $this, 'display_cv_upload_meta_box' ),
			$this->personnel_content_type,
			'after-title',
			'core'
		);

	}

	/**
	 * Display a meta box used to show a persons 'card'.
	 */
	public function display_position_info_meta_box( $post ) {
		?>
		<p>(Data pulled from active directory)</p>
		<p><strong><label for="wsu_id">WSU ID</label></strong><br />
		<input type="text" id="wsu_id" name="wsu_id" value="12345678" class="widefat" disabled="disabled" /></p>
		<p><strong><label for="dept">Department</label></strong><br />
		<input type="text" id="dept" name="dept" value="ABC" class="widefat" disabled="disabled" /></p>
		<p><strong><label for="official_title">Official Title</label></strong><br />
		<input type="text" id="official_title" name="official_title" value="Widget builder" class="widefat" disabled="disabled" /></p>
		<p><strong>Appointment</strong><br />
		<label for="appointment_teaching"><input type="checkbox" id="appointment_teaching" name="appointment_teaching" checked="checked" disabled="disabled"> Teaching</label><br />
		<label for="appointment_research"><input type="checkbox" id="appointment_research" name="appointment_research" checked="checked" disabled="disabled"> Research</label><br />
		<label for="appointment_extension"><input type="checkbox" id="appointment_extension" name="appointment_extension" checked="checked" disabled="disabled"> Extension</label><br />
		<label for="appointment_other"><input type="checkbox" id="appointment_other" name="appointment_other" checked="checked" disabled="disabled"> Other</label><br />
		<p><strong>Classification</strong><br />
		<label for="classification_faculty"><input type="checkbox" id="classification_faculty" name="classification_faculty" checked="checked" disabled="disabled"> Faculty</label><br />
		<label for="classification_ap"><input type="checkbox" id="classification_ap" name="classification_ap" checked="checked" disabled="disabled"> Administrative Professional</label><br />
		<label for="classification_staff"><input type="checkbox" id="classification_staff" name="classification_staff" checked="checked" disabled="disabled"> Staff</label><br />
		<label for="classification_ga"><input type="checkbox" id="classification_ga" name="classification_ga" checked="checked" disabled="disabled"> Graduate Assistant</label><br />
		<label for="classification_hourly"><input type="checkbox" id="classification_hourly" name="classification_hourly" checked="checked" disabled="disabled"> Hourly</label><br />
		<label for="classification_pa"><input type="checkbox" id="classification_pa" name="classification_pa" checked="checked" disabled="disabled"> Public Affiliate</label><br />
		<p><strong><label for="office">Office Location</label></strong><br />
		<input type="text" id="office" name="office" value="Somewhere" disabled="disabled" /></p></strong></p>
		<p><strong><label for="phone">Office Phone Number</label></strong><br />
		<input type="text" id="phone" name="phone" value="(509) 335-5555" disabled="disabled" /></p></strong></p>
		<p><strong><label for="email">Email Address</label></strong><br />
		<input type="text" id="email" name="email" value="someone@wsu.edu" disabled="disabled" /></p></strong></p>
		<p class="description">Notify <a href="#">HR</a> if any of this information is incorrect or needs updated.</p>
		<?php
	}

	/**
	 * Display a meta box used to collect any additional or alternate contact info.
	 */
	public function display_contact_info_meta_box( $post ) {
		wp_nonce_field( 'wsuwsp_profile', 'wsuwsp_profile_nonce' );
		?>
		<p><strong><label for="_wsuwp_profile_alt_phone">Phone Number</label></strong><br />
		<input type="text" id="_wsuwp_profile_alt_phone" name="_wsuwp_profile_alt_phone" value="<?php echo get_post_meta( $post->ID, '_wsuwp_profile_alt_phone', true ); ?>" class="widefat" /></p>
		<p><strong><label for="_wsuwp_profile_alt_email">Email Address</label></strong><br />
		<input type="text" id="_wsuwp_profile_alt_email" name="_wsuwp_profile_alt_email" value="<?php echo get_post_meta( $post->ID, '_wsuwp_profile_alt_email', true ); ?>" class="widefat" /></p>
		<p><strong><label for="_wsuwp_profile_website">Website URL</label></strong><br />
		<input type="text" id="_wsuwp_profile_website" name="_wsuwp_profile_website" value="<?php echo get_post_meta( $post->ID, '_wsuwp_profile_website', true ); ?>" class="widefat" /></p>
		<?php
	}

	/**
	 * Display a meta box used to enter a persons degree information.
	 */
	public function display_degree_info_meta_box( $post ) {
		$degrees = get_post_meta( $post->ID, '_wsuwp_profile_degree', true );
		?>
		<p><strong><label for="_wsuwp_profile_degree">Degree</label></strong><br />
		<input type="text" id="_wsuwp_profile_degree" name="_wsuwp_profile_degree" value="" class="widefat" /></p>
		<p><a href="#">+ Add Another</a></p>
		<?php
	}

	/**
	 * Display a meta box used to upload a persons c.v.
	 */
	public function display_cv_upload_meta_box( $post ) {
		?>
			<div class="upload-set-wrapper">
				<input type="hidden" class="wsuwp-profile-upload" name="_wsuwp_profile_cv" id="_wsuwp_profile_cv" value="<?php echo get_user_meta( $user->ID, '_wsuwp_profile_cv', true ); ?>" />
				<p class="hide-if-no-js"><a title="C.V." data-type="File" href="#" class="wsuwp-profile-upload-link">
				<?php if ( $cv ) : ?>
					<img src="<?php echo get_bloginfo( 'url' ) . '/wp-includes/images/media/document.png'; ?>" /></a></p>
					<p class="hide-if-no-js"><a title="C.V." href="#" class="wsuwp-profile-remove-link">Remove C.V.</a></p>
				<?php else : ?>
					 Upload C.V.</a></p>
				<?php endif; ?>
			</div>
		<?php
	}

	/**
	 * Save post meta data.
	 */
	public function save_post( $post_id ) {
		if ( ! isset( $_POST['wsuwsp_profile_nonce'] ) )
			return $post_id;

		$nonce = $_POST['wsuwsp_profile_nonce'];

		if ( ! wp_verify_nonce( $nonce, 'wsuwsp_profile' ) )
			return $post_id;

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return $post_id;

		if ( ! current_user_can( 'edit_post', $post_id ) )
			return $post_id;

		foreach ( $this->personnel_fields as $field ) {
			if ( isset( $_POST[$field] ) && $_POST[$field] != '' )
				update_post_meta( $post_id, $field, sanitize_text_field( $_POST[$field] ) );
			else
				delete_post_meta( $post_id, $field );
		}

		if ( isset( $_POST['_wsuwp_profile_teaching'] ) && $_POST['_wsuwp_profile_teaching'] != '' )
			update_post_meta( $post_id, '_wsuwp_profile_teaching', wp_kses_post( $_POST['_wsuwp_profile_teaching'] ) );
		else
			delete_post_meta( $post_id, '_wsuwp_profile_teaching' );

		if ( isset( $_POST['_wsuwp_profile_research'] ) && $_POST['_wsuwp_profile_research'] != '' )
			update_post_meta( $post_id, '_wsuwp_profile_research', wp_kses_post( $_POST['_wsuwp_profile_research'] ) );
		else
			delete_post_meta( $post_id, '_wsuwp_profile_research' );


	}

}

$wsuwp_people_directory = new WSUWP_People_Directory();