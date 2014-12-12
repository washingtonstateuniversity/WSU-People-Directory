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
	 * The plugin version number, used to break caches and trigger
	 * upgrade routines.
	 *
	 * @var string
	 */
	var $personnel_plugin_version = '0.1.0';

	/**
	 * The slug used to register the "Personnel" custom content type.
	 *
	 * @var string
	 */
	var $personnel_content_type = 'wsuwp_people_profile';

	/**
	 * Fields used to capture Active Directory data.
	 */
	var $ad_fields = array(
		'_wsuwp_profile_ad_nid',
		'_wsuwp_profile_ad_name_first',
		'_wsuwp_profile_ad_name_last',
		'_wsuwp_profile_ad_dept',
		'_wsuwp_profile_ad_title',
		'_wsuwp_profile_ad_appointment',// Not sure if this information is in AD.
		'_wsuwp_profile_ad_classification',// Ditto.
		'_wsuwp_profile_ad_office',
		'_wsuwp_profile_ad_phone',
		'_wsuwp_profile_ad_email',
	);

	/**
	 * Fields used to capture additional profile information.
	 */
	var $basic_fields = array(
		'_wsuwp_profile_teaching_name',
		'_wsuwp_profile_research_name',
		'_wsuwp_profile_alt_phone',
		'_wsuwp_profile_alt_email',
		'_wsuwp_profile_website',
		'_wsuwp_profile_research_photo',
		'_wsuwp_profile_cv',
		'_wsuwp_profile_coeditor',
	);

	/**
	 * Repeatable fields used throughout the metaboxes.
	 */
	var $repeatable_fields = array(
		'_wsuwp_profile_degree',
	);

	/**
	 * WP editors used throughout the metaboxes.
	 */
	var $wp_editors = array(
		'_wsuwp_profile_teaching',
		'_wsuwp_profile_research',
		'_wsuwp_profile_extension',
		'_wsuwp_profile_publications',
	);

	public function __construct() {

		// Custom content type and taxonomies.
		//add_action( 'init', array( $this, 'process_upgrade_routine' ), 12 );
		add_action( 'init', array( $this, 'register_personnel_content_type' ), 11 );
		add_action( 'init', array( $this, 'add_taxonomies' ), 12 );

		// Custom meta and all that.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_filter( 'enter_title_here', array( $this, 'enter_title_here' ) );
		add_action( 'edit_form_after_title', array( $this, 'edit_form_after_title' ) );
		add_action( 'edit_form_after_editor',	array( $this, 'edit_form_after_editor' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );
		add_action( 'do_meta_boxes', array( $this, 'featured_image_box' ) ); 
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
		add_filter( 'wp_post_revision_meta_keys', array( $this, 'add_meta_keys_to_revision' ) );

		// JSON output.
		add_filter( 'json_prepare_post', array( $this, 'json_prepare_post' ), 10, 3 );
		add_filter( 'json_query_vars', array( $this, 'json_query_vars' ) );

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
	 * Process any upgrade routines between versions or on initial activation.
	 * (Taken verbatim from the Unversity Center plugin)
	 */
	public function process_upgrade_routine() {

		$db_version = get_option( 'wsuwp_people_version', '0.0.0' );

		// Flush rewrite rules if on an early or non existing DB version.
		if ( version_compare( $db_version, '0.1.0', '<' ) ) {
			flush_rewrite_rules();
		}

		update_option( 'wsuwp_people_version', $this->personnel_plugin_version );

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
	 * Add WSUWP University Taxonomies.
	 */
	public function add_taxonomies() {
		register_taxonomy_for_object_type( 'wsuwp_university_category', $this->personnel_content_type );
		register_taxonomy_for_object_type( 'wsuwp_university_location', $this->personnel_content_type );
		//register_taxonomy_for_object_type( 'wsuwp_university_organizations', $this->personnel_content_type );
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

		do_meta_boxes( get_current_screen(), 'after_title', $post );

		if ( $this->personnel_content_type === $post->post_type ) :
			?>
			<div id="wsuwp-profile-tabs">
				<ul>
					<li><a href="#wsuwp-profile-default" class="nav-tab">Bio</a></li>
					<li><a href="#wsuwp-profile-teaching" class="nav-tab">Teaching</a></li>
					<li><a href="#wsuwp-profile-research" class="nav-tab">Research</a></li>
          <li><a href="#wsuwp-profile-extension" class="nav-tab">Extension</a></li>
					<li><a href="#wsuwp-profile-publications" class="nav-tab">Publications</a></li>
				</ul>
				<div id="wsuwp-profile-default">
					<p class="description">Consider including professional experience, previous employment, awards, honors, memberships, or other information you wish to share about yourself here.</p>
			<?php
			do_meta_boxes( get_current_screen(), 'bio_above_editor', $post ); // Metaboxes added this way don't show up for my Super Admin account - hoping it's a unique case...
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
					<input type="text" id="_wsuwp_profile_teaching_name" name="_wsuwp_profile_teaching_name" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_teaching_name', true ) ); ?>" class="widefat wsuwp-profile-namefield" /></p>
					<?php do_meta_boxes( get_current_screen(), 'teaching_above_editor', $post ); ?>
					<?php wp_editor( get_post_meta( $post->ID, '_wsuwp_profile_teaching', true ), '_wsuwp_profile_teaching' ); ?>
				</div>

				<div id="wsuwp-profile-research">
					<p>(Could be cool to conditionally show this tab if user has a research appointment.)</p>
					<p class="description">Information about your research interests, recent funding/funded projects/grant submissions, grad students/program personnel/research team, research facilities, collaborators, patents, etc.</p>
					<h3 class="wpuwp-profile-label"><label for="_wsuwp_profile_research_name">Research Profile Display Name</label></h3>
					<p class="description">(if different than default)</p>
					<input type="text" id="_wsuwp_profile_research_name" name="_wsuwp_profile_research_name" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_research_name', true ) ); ?>" class="widefat wsuwp-profile-namefield" /></p>
          <?php do_meta_boxes( get_current_screen(), 'research_above_editor', $post ); ?>
					<?php wp_editor( get_post_meta( $post->ID, '_wsuwp_profile_research', true ), '_wsuwp_profile_research' ); ?>
				</div>

				<div id="wsuwp-profile-extension">
					<p>(Could be cool to conditionally show this tab if user has an Extension appointment.)</p>
					<p class="description">Information about your Extension duties.</p>
					<?php do_meta_boxes( get_current_screen(), 'extension_above_editor', $post ); ?>
					<?php wp_editor( get_post_meta( $post->ID, '_wsuwp_profile_extension', true ), '_wsuwp_profile_extension' ); ?>
				</div>

				<div id="wsuwp-profile-publications">
					<p>(This section would ideally include a way for users to dynamically pull in a feed from the pubs store, and a wp_editor for manually inputting book chapters, professional articles, peer-reviewed exhibitions, juried artistic works, and other publications that wouldn't be in the pubs store.)</p>
					<?php do_meta_boxes( get_current_screen(), 'publications_above_editor', $post ); ?>
					<?php wp_editor( get_post_meta( $post->ID, '_wsuwp_profile_publications', true ), '_wsuwp_profile_publications' ); ?>
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
			'research_above_editor',
			'low'
		);

		add_meta_box(
			'wsuwp_profile_degree_info',
			'Degree Information',
			array( $this, 'display_degree_info_meta_box' ),
			$this->personnel_content_type,
			'bio_above_editor',
			'high'
		);

		add_meta_box(
			'wsuwp_profile_cv_upload',
			'Curriculum Vitae',
			array( $this, 'display_cv_upload_meta_box' ),
			$this->personnel_content_type,
			'bio_above_editor',
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

		$nid = get_post_meta( $post->ID, '_wsuwp_profile_ad_nid', true );

		?>

		<!-- Just for testing. Capture the NID during post creation/save. -->
    <input type="text" id="_wsuwp_profile_ad_nid" name="_wsuwp_profile_ad_nid" value="<?php echo esc_attr( $nid ); ?>" />

		<?php
		/**
		 * Just an idea...
		 * We'll pull this data from AD for all WSU people (who will presumably have a NID),
		 * but we don't want them to edit it here. We do, however, want to allow non-WSU folk
		 * whom we're hosting a profile for to be able to add contact info.
		 * So, let's leverage the NID to offer up a different presentation for those situations.
		 */
		if ( $nid ) : ?>

		<p><?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_ad_name_first', true ) ) . ' ' . esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_ad_name_last', true ) ); ?></p>
		<p><?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_ad_dept', true ) ); ?></p>
		<p><?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_ad_appointment', true ) ); ?></p>
		<p><?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_ad_classification', true ) ); ?></p>
		<p><?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_ad_title', true ) ); ?></p>
		<p><?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_ad_email', true ) ); ?></p>
		<p><?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_ad_phone', true ) ); ?></p>
		<p><?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_ad_office', true ) ); ?></p>
		<p class="description">Notify <a href="#">HR</a> if any of this information is incorrect or needs updated.</p>

		<?php else : ?>

		<p><label for="_wsuwp_profile_ad_name_first">First Name</label><br />
    <input type="text" id="_wsuwp_profile_ad_name_first" name="_wsuwp_profile_ad_name_first" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_ad_name_first', true ) ); ?>" class="widefat" /></p>
    <p><label for="_wsuwp_profile_ad_name_last">Last Name</label><br />
    <input type="text" id="_wsuwp_profile_ad_name_last" name="_wsuwp_profile_ad_name_last" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_ad_name_last', true ) ); ?>" class="widefat" /></p>
    <p><label for="_wsuwp_profile_ad_title">Title</label><br />
		<input type="text" id="_wsuwp_profile_ad_title" name="_wsuwp_profile_ad_title" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_ad_title', true ) ); ?>" class="widefat" /></p>
		<p><label for="_wsuwp_profile_ad_office">Location</label><br />
		<input type="text" id="_wsuwp_profile_ad_office" name="_wsuwp_profile_ad_office" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_ad_office', true ) ); ?>" class="widefat" /></p>
		<p><label for="_wsuwp_profile_ad_phone">Phone Number <span class="description">(xxx-xxx-xxxx)</span></label><br />
		<input type="text" id="_wsuwp_profile_ad_phone" name="_wsuwp_profile_ad_phone" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_ad_phone', true ) ); ?>" class="widefat" /></p>
		<p><label for="_wsuwp_profile_ad_email">Email Address</label><br />
		<input type="text" id="_wsuwp_profile_ad_email" name="_wsuwp_profile_ad_email" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_ad_email', true ) ); ?>" class="widefat" /></p>

		<?php endif;

	}

	/**
	 * Display a meta box used to collect any additional or alternate contact info.
	 */
	public function display_contact_info_meta_box( $post ) {

		wp_nonce_field( 'wsuwsp_profile', 'wsuwsp_profile_nonce' );

		?>
		<p><label for="_wsuwp_profile_alt_phone">Phone Number</label><br />
		<input type="text" id="_wsuwp_profile_alt_phone" name="_wsuwp_profile_alt_phone" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_alt_phone', true ) ); ?>" class="widefat" /></p>
		<p><label for="_wsuwp_profile_alt_email">Email Address</label><br />
		<input type="text" id="_wsuwp_profile_alt_email" name="_wsuwp_profile_alt_email" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_alt_email', true ) ); ?>" class="widefat" /></p>
		<p><label for="_wsuwp_profile_website">Website URL</label><br />
		<input type="text" id="_wsuwp_profile_website" name="_wsuwp_profile_website" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_website', true ) ); ?>" class="widefat" /></p>
		<?php

	}

	/**
	 * Display a metabox used to upload a persons research photo
	 */
	public function display_profile_research_photo_meta_box( $post ) {

		$research_photo = get_post_meta( $post->ID, '_wsuwp_profile_research_photo', true );

		?>
			<p class="description">If you would like to associate a different image with your research information, upload it here.</p>
			<div class="upload-set-wrapper">
				<input type="hidden" class="wsuwp-profile-upload" name="_wsuwp_profile_research_photo" id="_wsuwp_profile_research_photo" value="<?php echo esc_attr( $research_photo ); ?>" />
				<p class="hide-if-no-js"><a title="research photo" data-type="Photo" href="#" class="wsuwp-profile-upload-link">
				<?php if ( $research_photo ) :
					$image = wp_get_attachment_image_src( $research_photo, 'thumbnail' );
					?>
					<img src="<?php echo esc_url( $image[0] ); ?>" /></a></p>
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
			<p class="wp-profile-repeatable"><label for="_wsuwp_profile_degree[<?php echo esc_attr( $index ); ?>]">Degree</label><br />
			<input type="text" id="_wsuwp_profile_degree[<?php echo esc_attr( $index ); ?>]" name="_wsuwp_profile_degree[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $degree ); ?>" class="widefat" /></p>
			<?php
			endforeach;
		else :
			?>
			<p class="wp-profile-repeatable"><label for="_wsuwp_profile_degree[0]">Degree</label><br />
			<input type="text" id="_wsuwp_profile_degree[0]" name="_wsuwp_profile_degree[0]" value="<?php echo esc_attr( $degrees ); ?>" class="widefat" /></p>
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
				<input type="hidden" class="wsuwp-profile-upload" name="_wsuwp_profile_cv" id="_wsuwp_profile_cv" value="<?php echo esc_attr( $cv ); ?>" />
				<p class="hide-if-no-js"><a title="C.V." data-type="File" href="#" class="wsuwp-profile-upload-link">
				<?php if ( $cv ) : ?>
					<img src="<?php echo esc_url( home_url( '/wp-includes/images/media/document.png' ) ); ?>" /></a></p>
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
			<p><input type="text" id="_wsuwp_profile_coeditor" name="_wsuwp_profile_coeditor" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_coeditor', true ) ); ?>" class="widefat" /></p>
		<?php

	}

	/**
	 * Move and relabel the Featured Image metabox.
	 */
	public function featured_image_box() {  
    remove_meta_box( 'postimagediv', $this->personnel_content_type, 'side' );  
    add_meta_box( 'postimagediv', __('Profile Photo'), 'post_thumbnail_meta_box', $this->personnel_content_type, 'bio_above_editor', 'low' );  
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

		// Sanitize and save AD fields. Or not! Unique handling will be required in this case.
		foreach ( $this->ad_fields as $field ) {
			if ( isset( $_POST[ $field ] ) && '' != $_POST[ $field ] ) {
				update_post_meta( $post_id, $field, sanitize_text_field( $_POST[ $field ] ) );
			} else {
				delete_post_meta( $post_id, $field );
			}
		}

		// Sanitize and save basic fields.
		foreach ( $this->basic_fields as $field ) {
			if ( isset( $_POST[ $field ] ) && '' != $_POST[ $field ] ) {
				update_post_meta( $post_id, $field, sanitize_text_field( $_POST[ $field ] ) );
			} else {
				delete_post_meta( $post_id, $field );
			}
		}

		// Sanitize and save repeatable fields.
		foreach ( $this->repeatable_fields as $field ) {
			if ( isset( $_POST[ $field ] ) && '' != $_POST[ $field ] ) {
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

		// Sanitize and save wp_editors.
		foreach ( $this->wp_editors as $field ) {
			if ( isset( $_POST[ $field ] ) && '' != $_POST[ $field ] ) {
				update_post_meta( $post_id, $field, wp_kses_post( $_POST[ $field ] ) );
			} else {
				delete_post_meta( $post_id, $field );
			}
		}

	}

	/**
	 * Keys of meta fields to revision.
	 */
	public function add_meta_keys_to_revision( $keys ) {

		$revisioned_fields = array_merge( $this->basic_fields, $this->repeatable_fields, $this->wp_editors );

		foreach ( $revisioned_fields as $field ) {
			$keys[] = $field;
		}

    return $keys;
	}

	/**
	 * Include meta in the REST API output.
	 */
	public function json_prepare_post( $post_response, $post, $context ) {

		if ( $this->personnel_content_type !== $post['post_type'] ) {
			return $post_response;
		}

		$all_fields = array_merge( $this->ad_fields, $this->basic_fields, $this->repeatable_fields, $this->wp_editors );

		foreach ( $all_fields as $field ) {
			$post_response[ $field ] = get_post_meta( $post['ID'], $field, true );
		}

    return $post_response;

	}

	/**
	 * Add 'meta_key' to the list of public variables (leverage for ordering alphabetically by last name).
	 * It seems this may have security implications, though I admit I don't understand what they would be.
	 */
	public function json_query_vars( $valid_vars ) {

		$valid_vars[] = 'meta_key';

		return $valid_vars;

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
			<td><input type="text" id="wsuwp_people_organization_admin" name="wsuwp_people_organization_admin" value="<?php echo esc_attr( get_user_meta( $user->ID, 'wsuwp_people_organization_admin', true ) ); ?>" /></td>
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
				wp_enqueue_style( 'wsuwp-people-profile-style', plugins_url( 'css/profile.css', __FILE__ ), array(), $this->personnel_plugin_version );
				wp_enqueue_script( 'wsuwp-people-profile-script', plugins_url( 'js/profile.js', __FILE__ ), array( 'jquery-ui-tabs' ), $this->personnel_plugin_version, true );
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