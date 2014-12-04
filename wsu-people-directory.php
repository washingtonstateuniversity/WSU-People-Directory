<?php
/*
Plugin Name: WSU People Directory
Plugin URI:	#
Description: A plugin to maintain a central directory of people.
Author:	washingtonstateuniversity, CAHNRS, philcable, danialbleile
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
	 * The fields used throughout the metaboxes (temporary).
	 */
	var $personnel_fields = array(
		'_wsuwp_profile_teaching_name',
		'_wsuwp_profile_research_name',
		'_wsuwp_profile_alt_phone',
		'_wsuwp_profile_alt_email',
		'_wsuwp_profile_website',
		'_wsuwp_profile_research_photo',
		'_wsuwp_profile_cv',
		'_wsuwp_profile_coeditor',
		'_wsuwp_profile_dept',
		'_wsuwp_profile_name_first',
		'_wsuwp_profile_name_last',
	);

	var $repeatable_fields = array(
		'_wsuwp_profile_degree',
	);

	/**
	 * Some of the data pulled from active directory needs to be captured.
	 * If keeping the fields array (which is simply for ease of saving),
	 * make a separate one for those populated by AD data so saving can be handled differently.
	 *
	 * Capturing organization data as taxonomic data would make retrieving by organization really easy...
	 */

	public function __construct() {

		// Custom content type and metaboxes.
		add_action( 'init', array( $this, 'register_personnel_content_type' ), 11 );
		// Probably should add rewrite flushing.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_filter( 'enter_title_here', array( $this, 'enter_title_here' ) );
		add_action( 'edit_form_after_title', array( $this, 'edit_form_after_title' ) );
		add_action( 'edit_form_after_editor',	array( $this, 'edit_form_after_editor' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );

		// Capabilities and related.
		add_action( 'personal_options', array( $this, 'personal_options' ) );
		add_action( 'edit_user_profile_update', array( $this, 'edit_user_profile_update' ) );
		add_action( 'personal_options_update', array( $this, 'edit_user_profile_update' ) );
		add_filter( 'user_has_cap', array( $this, 'user_has_cap' ), 10, 3 );
		//add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'pre_get_posts', array( $this, 'limit_media_library' ) );

		// Templates, scripts, styles, and filters for the front end.
		add_filter( 'template_include', array( $this, 'template_include' ), 1 );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ), 11 );
		add_action( 'pre_get_posts', array( $this, 'profile_archives' ) );

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
				'slug' => 'profile',
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

		if ( ( 'post-new.php' == $hook || 'post.php' == $hook ) && $screen->post_type == $this->personnel_content_type ) {
			wp_enqueue_style( 'wsuwp-people-admin-style', plugins_url( 'css/admin-profile-style.css', __FILE__ ) );
			wp_enqueue_script( 'wsuwp-people-admin-script', plugins_url( 'js/admin-profile.js', __FILE__ ), array( 'jquery-ui-tabs' ), '', true );
		}

	}

	/**
	 * Change the "Enter title here" text for the Personnel content type.
	 */
	public function enter_title_here( $title ) {

		$screen = get_current_screen();

		if ( $this->personnel_content_type == $screen->post_type ) {
			$title = 'Enter name here';
		}

		return $title;

	}

	/**
	 * Add stuff after the title of the edit screen for the Personnel content type.
	 */
	public function edit_form_after_title( $post ) {

		if ( $this->personnel_content_type === $post->post_type ) :
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
			<?php
			do_meta_boxes( get_current_screen(), 'after-title', $post );
		endif;

	}

	/**
	 * Add stuff after the editor of the edit screen for the Personnel content type.
	 */
	public function edit_form_after_editor( $post ) {

		if ( $this->personnel_content_type === $post->post_type ) :
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
		endif;

	}

	/**
	 * Add the meta boxes used for the Personnel content type.
	 *
	 * @param string $post_type The slug of the current post type.
	 */
	public function add_meta_boxes( $post_type ) {
		if ( $this->personnel_content_type !== $post_type ) {
			return;
		}

		add_meta_box(
			'wsuwp_profile_position_info',
			'Position and Contact Information',
			array( $this, 'display_position_info_meta_box' ),
			$this->personnel_content_type,
			'side',
			'high'
		);

		add_meta_box(
			'wsuwp_profile_contact_info',
			'Alternate Contact Information',
			array( $this, 'display_contact_info_meta_box' ),
			$this->personnel_content_type,
			'side',
			'high'
		);

		add_meta_box(
			'wsuwp_profile_research_photo',
			'Research Photo',
			array( $this, 'display_profile_research_photo_meta_box' ),
			$this->personnel_content_type,
			'side',
			'low'
		);

		add_meta_box(
			'wsuwp_profile_degree_info',
			'Degree Information',
			array( $this, 'display_degree_info_meta_box' ),
			$this->personnel_content_type,
			'after-title',
			'high'
		);

		add_meta_box(
			'wsuwp_profile_cv_upload',
			'Curriculum Vitae',
			array( $this, 'display_cv_upload_meta_box' ),
			$this->personnel_content_type,
			'after-title',
			'core'
		);

		add_meta_box(
			'wsuwp_profile_coeditor',
			'Editors',
			array( $this, 'display_profile_coeditor_meta_box' ),
			$this->personnel_content_type,
			'advanced',
			'high'
		);

	}

	/**
	 * Display a meta box used to show a person's "card".
	 */
	public function display_position_info_meta_box( $post ) {

		// Some conditions are needed here for non-WSU/AD profiles so they can edit their "card" info.
		// Leveraging "_wsuwp_sso_user_type" would work for users, not sure what to do otherwise.

		?>
		<p>(Data pulled from active directory)</p>
		<p><strong><label for="wsu_id">WSU ID</label></strong><br />
		<input type="text" id="wsu_id" name="wsu_id" value="12345678" class="widefat" disabled="disabled" /></p>
		<p><strong><label for="_wsuwp_profile_name_first">First Name</label></strong><br />
		<input type="text" id="_wsuwp_profile_name_first" name="_wsuwp_profile_name_first" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_name_first', true ) ); ?>" class="widefat" /></p>
		<p><strong><label for="_wsuwp_profile_name_last">Last Name</label></strong><br />
		<input type="text" id="_wsuwp_profile_name_last" name="_wsuwp_profile_name_last" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_name_last', true ) ); ?>" class="widefat" /></p>

		<p><strong><label for="_wsuwp_profile_dept">Department</label></strong><br />
		<input type="text" id="_wsuwp_profile_dept" name="_wsuwp_profile_dept" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_dept', true ) ); ?>" class="widefat" /></p>

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
	 * Display a metabox used to upload a persons research photo
	 */
	public function display_profile_research_photo_meta_box( $post ) {

		$research_photo = get_post_meta( $post->ID, '_wsuwp_profile_research_photo', true );

		?>
			<p class="description">If you want a different image to display on your research-specific profile, upload it here.</p>
			<div class="upload-set-wrapper">
				<input type="hidden" class="wsuwp-profile-upload" name="_wsuwp_profile_research_photo" id="_wsuwp_profile_research_photo" value="<?php echo $research_photo; ?>" />
				<p class="hide-if-no-js"><a title="research photo" data-type="Photo" href="#" class="wsuwp-profile-upload-link">
				<?php if ( $research_photo ) :
					$image = wp_get_attachment_image_src( $research_photo, 'thumbnail' );
					?>
					<img src="<?php echo $image[0]; ?>" /></a></p>
					<p class="hide-if-no-js"><a title="research photo" href="#" class="wsuwp-profile-remove-link">Remove research photo</a></p>
				<?php else : ?>
					 Upload research photo</a></p>
				<?php endif; ?>
			</div>
		<?php

	}

	/**
	 * Display a meta box used to enter a persons degree information.
	 */
	public function display_degree_info_meta_box( $post ) {

		$degrees = get_post_meta( $post->ID, '_wsuwp_profile_degree', true );

		if ( is_array( $degrees ) ) :
			foreach ( $degrees as $index => $degree ) :
			?>
			<p class="wp-profile-repeatable"><strong><label for="_wsuwp_profile_degree[<?php echo $index; ?>]">Degree</label></strong><br />
			<input type="text" id="_wsuwp_profile_degree[<?php echo $index; ?>]" name="_wsuwp_profile_degree[<?php echo $index; ?>]" value="<?php echo $degree; ?>" class="widefat" /></p>
			<?php
			endforeach;
		else :
			?>
			<p class="wp-profile-repeatable"><strong><label for="_wsuwp_profile_degree[0]">Degree</label></strong><br />
			<input type="text" id="_wsuwp_profile_degree[0]" name="_wsuwp_profile_degree[0]" value="<?php echo $degrees; ?>" class="widefat" /></p>
			<?php
		endif;
		?>
    <p class="wsuwp-profile-add-repeatable"><a href="#">+ Add Another</a></p>
    <?php

	}

	/**
	 * Display a meta box used to upload a person's C.V.
	 */
	public function display_cv_upload_meta_box( $post ) {

		$cv = get_post_meta( $post->ID, '_wsuwp_profile_cv', true );

		?>
			<div class="upload-set-wrapper">
				<input type="hidden" class="wsuwp-profile-upload" name="_wsuwp_profile_cv" id="_wsuwp_profile_cv" value="<?php echo $cv; ?>" />
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
	 * Display a meta box used to assign additional editorship of a profile.
	 */
	public function display_profile_coeditor_meta_box( $post ) {

		?>
			<p>To grant another user the ability to edit your profile, add them here.</p>
			<p><input type="text" id="_wsuwp_profile_coeditor" name="_wsuwp_profile_coeditor" value="<?php echo get_post_meta( $post->ID, '_wsuwp_profile_coeditor', true ); ?>" class="widefat" /></p>
		<?php

	}

	/**
	 * Save post meta data.
	 */
	public function save_post( $post_id ) {

		if ( ! isset( $_POST['wsuwsp_profile_nonce'] ) ) {
			return $post_id;
		}

		$nonce = $_POST['wsuwsp_profile_nonce'];

		if ( ! wp_verify_nonce( $nonce, 'wsuwsp_profile' ) ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		foreach ( $this->personnel_fields as $field ) {
			if ( isset( $_POST[ $field ] ) && '' != $_POST[ $field ] ) {
				update_post_meta( $post_id, $field, sanitize_text_field( $_POST[ $field ] ) );
			} else {
				delete_post_meta( $post_id, $field );
			}
		}

		if ( isset( $_POST['_wsuwp_profile_teaching'] ) && '' != $_POST['_wsuwp_profile_teaching'] ) {
			update_post_meta( $post_id, '_wsuwp_profile_teaching', wp_kses_post( $_POST['_wsuwp_profile_teaching'] ) );
		} else {
			delete_post_meta( $post_id, '_wsuwp_profile_teaching' );
		}

		if ( isset( $_POST['_wsuwp_profile_research'] ) && '' != $_POST['_wsuwp_profile_research'] ) {
			update_post_meta( $post_id, '_wsuwp_profile_research', wp_kses_post( $_POST['_wsuwp_profile_research'] ) );
		} else {
			delete_post_meta( $post_id, '_wsuwp_profile_research' );
		}

		foreach ( $this->repeatable_fields as $field ) {

			if ( isset( $_POST[ $field ] ) && '' != $_POST[ $field ] ) {
/*
				array_walk( $_POST[ $field ], function( &$value, $key ) {
					$value = sanitize_text_field( $value );
    		} ); 
				
				if ( isset( $_POST[ $field ] ) && '' != $_POST[ $field ] ) {
					update_post_meta( $post_id, $field, $_POST[ $field ] );
				} else {
					delete_post_meta( $post_id, $field );
				}
*/
				$array = array();

				foreach ( $_POST[ $field ] as $value ) {
					if ( isset( $value ) && '' != $value ) {
						$array[] = sanitize_text_field( $value );
					}
				}

				if ( isset( $array ) && '' != $array ) {
					update_post_meta( $post_id, $field, $array );
				} else {
					delete_post_meta( $post_id, $field );
				}

			}
		}

	}

	/**
	 * Add "Organization Administrator" field in profile.
	 */
	public function personal_options( $user ) {

		if ( IS_PROFILE_PAGE ) {
			return;
		}

		?>
		<tr>
			<th><label for="wsuwp_people_organization_admin">Organization Administrator for</label></th>
			<td><input type="text" id="wsuwp_people_organization_admin" name="wsuwp_people_organization_admin" value="<?php echo get_user_meta( $user->ID, 'wsuwp_people_organization_admin', true ); ?>" /></td>
		</tr>
		<?php
	}

	/**
	 * Store the type of authentication assigned to a user.
	 *
	 * @param int $user_id ID of the user being edited.
	 */
	public function edit_user_profile_update( $user_id ) {

		if ( ! current_user_can( 'edit_users', $user_id ) ) {
			return;
		}

		if ( isset( $_POST['wsuwp_people_organization_admin'] ) && '' != $_POST['wsuwp_people_organization_admin'] ) {
			update_user_meta( $user_id, 'wsuwp_people_organization_admin', sanitize_text_field( $_POST['wsuwp_people_organization_admin'] ) );
		} else {
			delete_user_meta( $user_id, 'wsuwp_people_organization_admin' );
		}

	}

	/**
	 * Capability modifications.
	 */
	public function user_has_cap( $allcaps, $cap, $args ) {

		// Bail out if we're not asking about a post:
		//if ( 'edit_post' != $args[0] )
		//	return $allcaps;
		// This condition results in a "You are not allowed to edit posts as this user." message upon updating.

		// Bail for users who can already edit others posts:
		if ( $allcaps['edit_others_posts'] ) {
			return $allcaps;
		}

		// Bail for users who can't publish posts:
		if ( ! isset( $allcaps['publish_posts'] ) or ! $allcaps['publish_posts'] ) {
			return $allcaps;
		}

		// Load the post data:
		$post = get_post( $args[2] );

		// Bail if the post type isn't Personnel:
		if ( $this->personnel_content_type != $post->post_type ) {
			return $allcaps;
		}

		// Bail if the user is the post author:
		if ( $args[1] == $post->post_author ) {
			return $allcaps;
		}

		// Bail if the post isn't published:
		if ( 'publish' != $post->post_status ) {
			return $allcaps;
		}

		// Bail if the user isn't an Organization Administrator or listed as a coeditor:
		$coeditor = get_post_meta( $post->ID, '_wsuwp_profile_coeditor', true );
		$dept = get_post_meta( $post->ID, '_wsuwp_profile_dept', true );
		$org_admin = get_user_meta( $args[1], 'wsuwp_people_organization_admin', true );
		if ( ( $args[1] != $coeditor ) && ( $org_admin != $dept ) ) {
			return $allcaps;
		}

		// Load the author data:
		$author = new WP_User( $post->post_author );

		// Bail if post author can edit others posts:
		if ( $author->has_cap( 'edit_others_posts' ) ) {
			return $allcaps;
		}

		$allcaps[ $cap[0] ] = true;

		return $allcaps;
	}

	/**
	 * Remove Profile menu page to avoid confusion.
	 */
	public function admin_menu() {

		if ( ! current_user_can( 'manage_options' ) ) {
			remove_menu_page( 'profile.php' );
		}

	}

	/**
	 * Show (non-Admin) users only the media they have uploaded.
	 * Theoretically, they have no need to see the rest.
	 * This doesn't change the counts on the Media Library page.
	 */
	public function limit_media_library( $query ) {

		if ( is_admin() ) {

			$screen = get_current_screen();
			$current_user = wp_get_current_user();

			if ( 'upload' != $screen->base && 'query-attachments' != $_REQUEST['action'] ) {
				return;
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				$query->set( 'author', $current_user->ID );
			}

		}

	}

	/**
	 * Add templates for the Personnel custom content type.
	 */
	public function template_include( $template ) {

		if ( $this->personnel_content_type == get_post_type() ) {

			if ( is_single() ) {
				$template = plugin_dir_path( __FILE__ ) . 'templates/single.php';
			}

			if ( is_archive() ) {
				$template = plugin_dir_path( __FILE__ ) . 'templates/archive.php';
			}

		}

		return $template;

	}

	/**
	 * Enqueue the scripts and styles used on the front end.
	 */
	public function wp_enqueue_scripts() {

		if ( $this->personnel_content_type == get_post_type() ) {
			if ( is_single() ) {
				wp_enqueue_style( 'wsuwp-people-profile-style', plugins_url( 'css/profile.css', __FILE__ ) );
			}
		}

	}

	/**
	 * Order public Personnel query results alphabetically by last name
	 */
	public function profile_archives( $query ) {

		if ( $query->is_post_type_archive( $this->personnel_content_type ) && $query->is_main_query() && ! is_admin() ) {
			$query->set( 'order', 'ASC' );
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'meta_key', '_wsuwp_profile_name_last' );
		}

	}

}

$wsuwp_people_directory = new WSUWP_People_Directory();