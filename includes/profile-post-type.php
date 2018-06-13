<?php

namespace WSUWP\People_Directory\Profile_Post_Type;

/**
 * Returns the plugin version number for breaking cache.
 *
 * @return string The plugin version number.
 */
function plugin_version() {
	return \WSUWP\People_Directory\version();
}

/**
 * Returns the plugin version number for breaking cache.
 *
 * @return string The plugin version number.
 */
function is_primary_directory() {
	return \WSUWP\People_Directory\is_primary();
}

/**
 * Returns the profile post type slug.
 *
 * @since 0.1.0
 *
 * @return string The profile post type slug.
 */
function slug() {
	return 'wsuwp_people_profile';
}

/**
 * Returns a list of meta keys associated the profile post type.
 *
 * @since 0.3.0
 *
 * @return array Meta keys associated with the profile post type.
 */
function meta_keys() {
	$meta_keys = array(
		'nid' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_ad_nid',
		),
		'first_name' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_ad_name_first',
		),
		'last_name' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_ad_name_last',
		),
		'position_title' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_ad_title',
		),
		'office' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_ad_office',
		),
		'address' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_ad_address',
		),
		'phone' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_ad_phone',
		),
		'phone_ext' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_ad_phone_ext',
		),
		'email' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_ad_email',
		),
		'office_alt' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_alt_office',
			'register_as_meta' => true,
			'updatable_via_rest' => true,
		),
		'phone_alt' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_alt_phone',
			'register_as_meta' => true,
			'updatable_via_rest' => true,
		),
		'email_alt' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_alt_email',
			'register_as_meta' => true,
			'updatable_via_rest' => true,
		),
		'address_alt' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_alt_address',
			'register_as_meta' => true,
			'updatable_via_rest' => true,
		),
		'website' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'esc_url_raw',
			'meta_key' => '_wsuwp_profile_website',
			'register_as_meta' => true,
			'updatable_via_rest' => true,
		),
		'degree' => array(
			'type' => 'array',
			'items' => array(
				'type' => 'string',
			),
			'description' => '',
			'sanitize_callback' => __NAMESPACE__ . '\\sanitize_repeatable_text_fields',
			'meta_key' => '_wsuwp_profile_degree',
			'register_as_meta' => true,
			'updatable_via_rest' => true,
		),
		'working_titles' => array(
			'type' => 'array',
			'items' => array(
				'type' => 'string',
			),
			'description' => '',
			'sanitize_callback' => __NAMESPACE__ . '\\sanitize_repeatable_text_fields',
			'meta_key' => '_wsuwp_profile_title',
			'register_as_meta' => true,
			'updatable_via_rest' => true,
		),
		'bio_unit' => array(
			'type' => 'string',
			'description' => 'Unit Biography',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_bio_unit',
			'register_as_meta' => true,
			'updatable_via_rest' => true,
			'render_as_wp_editor' => true,
		),
		'bio_university' => array(
			'type' => 'string',
			'description' => 'University Biography',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_bio_university',
			'register_as_meta' => true,
			'updatable_via_rest' => true,
			'render_as_wp_editor' => true,
		),
		'photos' => array(
			'type' => 'array',
			'description' => 'A collection of photos',
			'sanitize_callback' => __NAMESPACE__ . '\\sanitize_photos',
			'meta_key' => '_wsuwp_profile_photos',
			'register_as_meta' => true,
		),
		'listed_on' => array(
			'type' => 'array',
			'items' => array(
				'type' => 'string',
			),
			'description' => '',
			'sanitize_callback' => __NAMESPACE__ . '\\sanitize_repeatable_text_fields',
			'meta_key' => '_wsuwp_profile_listed_on',
			'updatable_via_rest' => true,
		),
		// Legacy
		'bio_college' => array(
			'type' => 'string',
			'description' => 'College Biography',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_bio_college',
			'updatable_via_rest' => true,
			'legacy' => true,
		),
		'bio_lab' => array(
			'type' => 'string',
			'description' => 'Lab Biography',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_bio_lab',
			'updatable_via_rest' => true,
			'legacy' => true,
		),
		'bio_department' => array(
			'type' => 'string',
			'description' => 'Department Biography',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_bio_dept',
			'updatable_via_rest' => true,
			'legacy' => true,
		),
		'cv_employment' => array(
			'type' => 'string',
			'description' => 'C.V. - Employment',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_employment',
			'updatable_via_rest' => true,
			'legacy' => true,
		),
		'cv_honors' => array(
			'type' => 'string',
			'description' => 'C.V. - Honors',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_honors',
			'updatable_via_rest' => true,
			'legacy' => true,
		),
		'cv_grants' => array(
			'type' => 'string',
			'description' => 'C.V. - Grants',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_grants',
			'updatable_via_rest' => true,
			'legacy' => true,
		),
		'cv_publications' => array(
			'type' => 'string',
			'description' => 'C.V. - Publications',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_publications',
			'updatable_via_rest' => true,
			'legacy' => true,
		),
		'cv_presentations' => array(
			'type' => 'string',
			'description' => 'C.V. - Presentations',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_presentations',
			'updatable_via_rest' => true,
			'legacy' => true,
		),
		'cv_teaching' => array(
			'type' => 'string',
			'description' => 'C.V. - Teaching',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_teaching',
			'updatable_via_rest' => true,
			'legacy' => true,
		),
		'cv_service' => array(
			'type' => 'string',
			'description' => 'C.V. - Service',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_service',
			'updatable_via_rest' => true,
			'legacy' => true,
		),
		'cv_responsibilities' => array(
			'type' => 'string',
			'description' => 'C.V. - Responsibilities',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_responsibilities',
			'updatable_via_rest' => true,
			'legacy' => true,
		),
		'cv_affiliations' => array(
			'type' => 'string',
			'description' => 'C.V. - Affiliations',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_societies',
			'updatable_via_rest' => true,
			'legacy' => true,
		),
		'cv_experience' => array(
			'type' => 'string',
			'description' => 'C.V. - Experience',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_experience',
			'updatable_via_rest' => true,
			'legacy' => true,
		),
		'cv_attachment' => array(
			'type' => 'string',
			'description' => 'C.V. - Upload',
			'sanitize_callback' => 'attachment',
			'meta_key' => '_wsuwp_profile_cv',
			'updatable_via_rest' => true,
			'legacy' => true,
		),
		'profile_photo' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'attachment',
			'meta_key' => '',
			'updatable_via_rest' => true,
		),
	);

	return $meta_keys;
}

add_action( 'init', __NAMESPACE__ . '\\register_post_type', 11 );
add_action( 'init', __NAMESPACE__ . '\\register_meta', 11 );
add_action( 'init', __NAMESPACE__ . '\\register_taxonomies_for_people', 12 );

add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\admin_enqueue_scripts' );

add_action( 'edit_form_after_title', __NAMESPACE__ . '\\edit_form_after_title' );
add_action( 'edit_form_after_editor', __NAMESPACE__ . '\\edit_form_after_editor' );

add_action( 'add_meta_boxes_' . slug(), __NAMESPACE__ . '\\profile_meta_boxes' );
add_filter( 'wsuwp_taxonomy_metabox_post_types', __NAMESPACE__ . '\\taxonomy_meta_box' );

add_action( 'save_post_' . slug(), __NAMESPACE__ . '\\save_post' );

add_action( 'wp_ajax_wsu_people_get_data_by_nid', __NAMESPACE__ . '\\ajax_get_data_by_nid' );
add_action( 'wp_ajax_wsu_people_confirm_nid_data', __NAMESPACE__ . '\\ajax_confirm_nid_data' );

add_filter( 'wp_post_revision_meta_keys', __NAMESPACE__ . '\\add_meta_keys_to_revision' );

add_filter( 'manage_taxonomies_for_' . slug() . '_columns', __NAMESPACE__ . '\\manage_people_taxonomy_columns' );

add_filter( 'wp_ajax_wsu_people_delete_legacy_meta', __NAMESPACE__ . '\\ajax_delete_legacy_meta' );

if ( false === is_primary_directory() ) {
	add_action( 'wp_enqueue_editor', __NAMESPACE__ . '\\admin_enqueue_secondary_scripts' );
	add_filter( 'wp_editor_settings', __NAMESPACE__ . '\\filter_default_editor_settings', 10, 2 );

	add_filter( 'wp_insert_post_data', __NAMESPACE__ . '\\wp_insert_post_data' );

	add_filter( 'manage_' . slug() . '_posts_columns', __NAMESPACE__ . '\\add_bio_column' );
	add_action( 'manage_' . slug() . '_posts_custom_column', __NAMESPACE__ . '\\bio_column', 10, 2 );
	add_action( 'quick_edit_custom_box', __NAMESPACE__ . '\\display_bio_edit', 10, 2 );
	add_action( 'bulk_edit_custom_box', __NAMESPACE__ . '\\display_bio_edit', 10, 2 );
	add_action( 'wp_ajax_save_bio_edit', __NAMESPACE__ . '\\save_bio_edit' );
}

/**
 * Registers the profile post type.
 *
 * @since 0.1.0
 */
function register_post_type() {
	$args = array(
		'labels' => array(
			'name' => 'Profiles',
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
		'description' => 'WSU people directory profiles',
		'public' => true,
		'hierarchical' => false,
		'menu_position' => 5,
		'menu_icon' => 'dashicons-groups',
		'supports' => array(
			'title',
			'editor',
			'revisions',
		),
		'taxonomies' => array(
			'post_tag',
			'category',
		),
		'rewrite' => apply_filters( 'wsuwp_people_default_rewrite_slug', false ),
		'show_in_rest' => true,
		'rest_base' => 'people',
	);

	\register_post_type( slug(), $args );
}

/**
 * Registers the meta keys used to store additional profile data.
 *
 * @since 0.3.0
 */
function register_meta() {
	foreach ( meta_keys() as $key => $args ) {
		if ( ! isset( $args['register_as_meta'] ) ) {
			continue;
		}

		$args['single'] = true;

		\register_meta( 'post', $args['meta_key'], $args );
	}
}

/**
 * Adds support for WSU University Taxonomies to the profile post type.
 *
 * @since 0.1.0
 */
function register_taxonomies_for_people() {
	register_taxonomy_for_object_type( 'wsuwp_university_category', slug() );
	register_taxonomy_for_object_type( 'wsuwp_university_location', slug() );
	register_taxonomy_for_object_type( 'wsuwp_university_org', slug() );
}

/**
 * Enqueues the assets used for profile post type admin pages.
 *
 * @since 0.1.0
 *
 * @param string $hook_suffix The current admin page.
 */
function admin_enqueue_scripts( $hook_suffix ) {
	$screen = get_current_screen();

	if ( slug() !== $screen->post_type ) {
		return;
	}

	// Enqueue assets for the Edit and Add New Profile pages.
	if ( 'post-new.php' === $hook_suffix || 'post.php' === $hook_suffix ) {
		$post = get_post();

		$profile_vars = array(
			'nid_nonce' => wp_create_nonce( 'wsu-people-nid-lookup' ),
			'post_id' => $post->ID,
			'request_from' => ( is_primary_directory() ) ? 'ad' : 'rest',
		);

		wp_enqueue_style( 'wsuwp-people-edit-profile', plugins_url( 'css/admin-person.css', dirname( __FILE__ ) ), array(), plugin_version() );
		wp_enqueue_script( 'wsuwp-people-edit-profile', plugins_url( 'js/admin-edit-profile.min.js', dirname( __FILE__ ) ), array( 'underscore', 'jquery-ui-sortable' ), plugin_version(), true );
		wp_localize_script( 'wsuwp-people-edit-profile', 'wsuwp_people_edit_profile', $profile_vars );

		// Disable autosaving on spoke sites.
		if ( false === is_primary_directory() ) {
			wp_dequeue_script( 'autosave' );
		}

		// Enqueue assets for the Edit and Add New Profile pages on the primary directory.
		if ( true === is_primary_directory() ) {
			wp_enqueue_script( 'wsuwp-people-edit-profile-primary', plugins_url( 'js/admin-edit-profile-primary.min.js', dirname( __FILE__ ) ), array( 'jquery' ), plugin_version(), true );

			wp_localize_script( 'wsuwp-people-edit-profile-primary', 'wsupeoplesync', array(
				'nonce' => \WSUWP\People_Directory\create_rest_nonce(),
				'uid' => wp_get_current_user()->ID,
			) );
		}
	}

	// Enqueue assets for the All Profiles page on spoke sites.
	if ( 'edit.php' === $hook_suffix && false === is_primary_directory() ) {
		wp_enqueue_style( 'wsuwp-people-admin', plugins_url( 'css/admin-people.css', dirname( __FILE__ ) ), array(), plugin_version() );
		wp_enqueue_script( 'wsuwp-people-admin', plugins_url( 'js/admin-people.min.js', dirname( __FILE__ ) ), array( 'jquery' ), plugin_version() );
		wp_localize_script( 'wsuwp-people-admin', 'wsupeople', array(
			'nonce' => wp_create_nonce( 'person-meta' ),
		) );
	}
}

/**
 * Outputs the interface for editing profile data.
 *
 * @since 0.3.0
 *
 * @param WP_Post $post Post object.
 */
function edit_form_after_title( $post ) {
	if ( slug() !== $post->post_type ) {
		return;
	}

	wp_nonce_field( 'wsuwp_profile', 'wsuwp_profile_nonce' );

	// AD data
	$nid = get_post_meta( $post->ID, '_wsuwp_profile_ad_nid', true );
	$name_first = get_post_meta( $post->ID, '_wsuwp_profile_ad_name_first', true );
	$name_last = get_post_meta( $post->ID, '_wsuwp_profile_ad_name_last', true );
	$title = get_post_meta( $post->ID, '_wsuwp_profile_ad_title', true );
	$email = get_post_meta( $post->ID, '_wsuwp_profile_ad_email', true );
	$phone = get_post_meta( $post->ID, '_wsuwp_profile_ad_phone', true );
	$phone_ext = get_post_meta( $post->ID, '_wsuwp_profile_ad_phone_ext', true );
	$office = get_post_meta( $post->ID, '_wsuwp_profile_ad_office', true );
	$address = get_post_meta( $post->ID, '_wsuwp_profile_ad_address', true );

	// Override data.
	$working_titles = get_post_meta( $post->ID, '_wsuwp_profile_title', true );
	$email_alt = get_post_meta( $post->ID, '_wsuwp_profile_alt_email', true );
	$phone_alt = get_post_meta( $post->ID, '_wsuwp_profile_alt_phone', true );
	$office_alt = get_post_meta( $post->ID, '_wsuwp_profile_alt_office', true );
	$address_alt = get_post_meta( $post->ID, '_wsuwp_profile_alt_address', true );

	// Additional data.
	$website = get_post_meta( $post->ID, '_wsuwp_profile_website', true );
	$degrees = get_post_meta( $post->ID, '_wsuwp_profile_degree', true );
	$photos = get_post_meta( $post->ID, '_wsuwp_profile_photos', true );

	// Show the override data if it exists, otherwise show the AD data.
	$email_value = ( $email_alt ) ? $email_alt : $email;
	$office_value = ( $office_alt ) ? $office_alt : $office;
	$phone_value = ( $phone_alt ) ? $phone_alt : $phone;
	$address_value = ( $address_alt ) ? $address_alt : $address;

	// Define the URL of the primary photo.
	$photo_url = false;
	if ( $photos && is_array( $photos ) ) {
		foreach ( $photos as $i => $photo_id ) {
			if ( is_string( get_post_status( $photo_id ) ) ) {
				$photo_url = wp_get_attachment_image_src( $photos[ $i ] )[0];
				break;
			}
		}
	} elseif ( has_post_thumbnail() ) {
		$photos = array(); // An array is expected further down.
		$photo_url = get_the_post_thumbnail_url();
	}
	?>
	<script type="text/template" class="wsu-person-repeatable-meta-template">
		<span contenteditable="true" class="<%= type %>" data-placeholder="Enter <%= type %> here"><%= value %></span><button type="button" class="wsu-person-remove">
				<span class="screen-reader-text">Delete</span>
		</button>
		<input type="hidden" data-for="<%= type %>" name="_wsuwp_profile_<%= type %>[]" value="<%= value %>" />
	</script>

	<script type="text/template" class="wsu-person-photo-template">
		<div class="wsu-person-photo-wrapper">
			<img src="<%= src %>" alt="<?php echo esc_attr( $post->post_title ); ?>" />
			<button type="button" class="wsu-person-remove">
				<span class="screen-reader-text">Delete</span>
			</button>
			<input type="hidden" name="_wsuwp_profile_photos[]" value="<%= id %>" />
		</div>
	</script>

	<input type="hidden" data-for="name" name="post_title" value="<?php echo esc_attr( $post->post_title ); ?>" />
	<input type="hidden" data-for="email" name="_wsuwp_profile_alt_email" value="<?php echo esc_attr( $email_value ); ?>" />
	<input type="hidden" data-for="phone" name="_wsuwp_profile_alt_phone" value="<?php echo esc_attr( $phone_value ); ?>" />
	<input type="hidden" data-for="office" name="_wsuwp_profile_alt_office" value="<?php echo esc_attr( $office_value ); ?>" />
	<input type="hidden" data-for="address" name="_wsuwp_profile_alt_address" value="<?php echo esc_attr( $address_value ); ?>" />
	<input type="hidden" data-for="website" name="_wsuwp_profile_website" value="<?php echo esc_attr( $website ); ?>" />

	<?php if ( $working_titles && is_array( $working_titles ) ) { ?>
		<?php foreach ( $working_titles as $working_title ) { ?>
		<input type="hidden" data-for="title" name="_wsuwp_profile_title[]" value="<?php echo esc_attr( $working_title ); ?>" />
		<?php } ?>
	<?php } else { ?>
		<input type="hidden" data-for="title" name="_wsuwp_profile_title[]" value="" />
	<?php } ?>

	<?php if ( $degrees && is_array( $degrees ) ) { ?>
		<?php foreach ( $degrees as $degree ) { ?>
		<input type="hidden" data-for="degree" name="_wsuwp_profile_degree[]" value="<?php echo esc_attr( $degree ); ?>" />
		<?php } ?>
	<?php } ?>

	<?php if ( false === is_primary_directory() ) { ?>
		<?php $index_used = get_post_meta( $post->ID, '_use_title', true ); ?>
		<input type="hidden" class="use-title" name="_use_title" value="<?php echo esc_attr( $index_used ); ?>" />
	<?php } ?>

	<div class="wsu-person-photo-collection-backdrop wsu-person-photo-collection-close">
		<div class="wsu-person-photo-collection">
			<?php
			// Add the featured image to the photos array.
			if ( has_post_thumbnail() ) {
				$photos[] = get_post_thumbnail_id();
			}

			if ( $photos && is_array( $photos ) ) {
				foreach ( $photos as $photo_id ) {
					if ( is_string( get_post_status( $photo_id ) ) ) {
						?>
						<div class="wsu-person-photo-wrapper">
							<img src="<?php echo esc_url( wp_get_attachment_image_src( $photo_id )[0] ); ?>"
								 alt="<?php echo esc_attr( $post->post_title ); ?>" />
							<button type="button" class="wsu-person-remove">
								<span class="screen-reader-text">Delete</span>
							</button>
							<input type="hidden" name="_wsuwp_profile_photos[]" value="<?php echo esc_attr( $photo_id ); ?>" />
						</div>
						<?php
					}
				}
			}
			?>
			<button type="button" class="wsu-person-add-photo">+ Add another photo</button>

			<div class="wsu-person-photo-collection-toolbar">
				<button type="button" class="wsu-person-photo-collection-close button button-primary button-large">Update</button>
			</div>
		</div>

	</div>

	<div class="wsu-person" data-nid="<?php echo esc_html( $nid ); ?>">

		<div class="card">

			<header>

				<h2 contenteditable="true"
					class="name"
					data-placeholder="Enter name here"><?php echo esc_html( $post->post_title ); ?></h2>

				<?php if ( $degrees && is_array( $degrees ) ) { ?>
					<?php foreach ( $degrees as $degree ) { ?>
					<span contenteditable="true"
						  class="degree"
						  data-placeholder="Enter degree here"><?php echo esc_html( $degree ); ?></span>
					<?php } ?>
				<?php } ?>

			</header>

			<figure class="photo<?php if ( ! $photo_url ) { echo ' wsu-person-add-photo'; } ?>">

				<?php if ( $photo_url ) { ?>
				<img src="<?php echo esc_url( $photo_url ); ?>" alt="<?php echo esc_attr( $post->post_title ); ?>" />
				<figcaption>Manage photo collection</figcaption>
				<?php } else { ?>
				<figcaption>+ Add photo(s)</figcaption>
				<?php } ?>

			</figure>

			<div class="contact">

				<?php if ( $working_titles && is_array( $working_titles ) ) { ?>
					<?php foreach ( $working_titles as $working_title ) { ?>
					<span contenteditable="true"
						  class="title"
						  data-placeholder="Enter title here"><?php echo esc_html( $working_title ); ?></span>
					<?php } ?>
				<?php } else { ?>
					<span contenteditable="true"
						  class="title"
						  data-placeholder="Enter title here"><?php echo esc_html( $title ); ?></span>
				<?php } ?>

				<span contenteditable="true"
					  class="email"
					  data-placeholder="Enter email address here"><?php echo esc_html( $email_value ); ?></span>

				<span contenteditable="true"
					  class="phone"
					  data-placeholder="Enter phone number here (***-***-****)"><?php echo esc_html( $phone_value ); ?></span>

				<span contenteditable="true"
					  class="office"
					  data-placeholder="Enter office here"><?php echo esc_html( $office_value ); ?></span>

				<span contenteditable="true"
					  class="address"
					  data-placeholder="Enter mailing address here"><?php echo esc_html( $address_value ); ?></span>

				<span contenteditable="true"
					  class="website"
					  data-placeholder="Enter website URL here"><?php echo esc_html( $website ); ?></span>

			</div>

		</div>

		<div class="about">

			<div id="bio_personal" class="wsu-person-bio">

				<h2>Personal Biography</h2>
	<?php
}

/**
 * Continues the profile data editing interface output.
 * This includes TinyMCE editors for capturing additional biographical data.
 *
 * @since 0.3.0
 *
 * @param WP_Post $post Post object.
 */
function edit_form_after_editor( $post ) {
	if ( slug() !== $post->post_type ) {
		return;
	}

	?>
	</div><!--bio_personal-->
	<?php

	$user = wp_get_current_user();
	$profile_owner = in_array( 'wsuwp_people_profile_owner', (array) $user->roles, true );
	$global_admin = wsuwp_is_global_admin( $user->data->ID );

	foreach ( meta_keys() as $key => $args ) {
		if ( ! isset( $args['render_as_wp_editor'] ) ) {
			continue;
		}

		?>
		<div id="<?php echo esc_attr( $key ); ?>" class="wsu-person-bio">

			<h2><?php echo esc_html( $args['description'] ); ?></h2>

			<?php
			$value = get_post_meta( $post->ID, $args['meta_key'], true );
			$unit_bio = '_wsuwp_profile_bio_unit' === $args['meta_key'];
			$university_bio = '_wsuwp_profile_bio_university' === $args['meta_key'];

			if ( ( is_primary_directory() && $profile_owner && $unit_bio ) ||
					( $university_bio && ! $global_admin ) ) {
				echo '<div class="readonly">' . wp_kses_post( apply_filters( 'the_content', $value ) ) . '</div>';
			} else {
				wp_editor( $value, $args['meta_key'] );
			}
			?>
		</div>
		<?php
	}

	// Legacy inputs - temporary.
	if ( is_primary_directory() && 'add' !== get_current_screen()->action ) {
		$legacy_notice = false;

		foreach ( meta_keys() as $key => $args ) {
			if ( ! isset( $args['legacy'] ) ) {
				continue;
			}

			$value = get_post_meta( $post->ID, $args['meta_key'], true );

			if ( $value ) {

				if ( false === $legacy_notice ) {
					?><p class="description legacy-notice"><strong>Attention:</strong> the following fields have been deprecated. It is recommended that the information therein be moved into one of the above biography fields.</p><?php
					$legacy_notice = true;
				}

				?>
				<div id="<?php echo esc_attr( $key ); ?>" class="wsu-person-bio">

					<button type="button"
							class="legacy-meta-delete"
							data-name="<?php echo esc_attr( $args['description'] ); ?>"
							data-metakey="<?php echo esc_attr( $args['meta_key'] ); ?>">Delete this field</button>

					<h2><?php echo esc_html( $args['description'] ); ?></h2>

					<div class="readonly"><?php
					if ( 'cv_attachment' === $key ) {
						$cv_attachment_url = wp_get_attachment_url( $value );
						echo '<a href="' . esc_url( $cv_attachment_url ) . '">' . esc_url( $cv_attachment_url ) . '</a>';
					} else {
						echo wp_kses_post( apply_filters( 'the_content', $value ) );
					}

					?></div>

				</div>
				<?php
			}
		}
	}
	?>

	</div><!--wsuwp-person-about-->
	</div><!--wsuwp-person-->
	<?php
}

/**
 * Handles the meta boxes used for capturing and displaying profile data.
 *
 * @since 0.3.0
 *
 * @param WP_Post $post The post object.
 */
function profile_meta_boxes( $post ) {
	remove_meta_box( 'submitdiv', slug(), 'side' );

	$box_title = ( 'auto-draft' === $post->post_status ) ? 'Create Profile' : 'Update Profile';

	add_meta_box( 'submitdiv', $box_title, __NAMESPACE__ . '\\publish_meta_box', slug(), 'side', 'high' );

	if ( true === is_primary_directory() ) {
		add_meta_box( 'wsuwp-profile-listing', 'Listed On', __NAMESPACE__ . '\\display_listing_meta_box', slug(), 'normal' );
	}

	if ( false === is_primary_directory() ) {
		add_meta_box( 'wsuwp-profile-local-display', 'Display Options', __NAMESPACE__ . '\\display_local_display_meta_box', slug(), 'side', 'low' );
	}
}

/**
 * Replaces the default post publishing meta box with our own that guides the user through
 * a slightly different process for creating and saving a profile.
 *
 * This was originally copied from WordPress core's `post_submit_meta_box()`.
 *
 * @since 0.1.0
 *
 * @param WP_Post $post The profile being edited/created.
 */
function publish_meta_box( $post ) {
	$post_type = $post->post_type;
	$post_type_object = get_post_type_object( $post_type );
	$can_publish = current_user_can( $post_type_object->cap->publish_posts );

	$nid = get_post_meta( $post->ID, '_wsuwp_profile_ad_nid', true );

	$readonly = empty( trim( $nid ) ) ? '' : 'readonly';
	$data_location = ( false === is_primary_directory() ) ? 'people.wsu.edu' : 'Active Directory';
	?>
	<div class="submitbox" id="submitpost">

		<div id="misc-publishing-actions">
			<div class="misc-pub-section">
				<label for="_wsuwp_profile_ad_nid">Network ID</label>:
				<input type="text" id="_wsuwp_profile_ad_nid" name="_wsuwp_profile_ad_nid" value="<?php echo esc_attr( $nid ); ?>" class="widefat" <?php echo esc_attr( $readonly ); ?> />

			<?php if ( false === is_primary_directory() ) { ?>
				<?php
				$record_id = get_post_meta( $post->ID, '_wsuwp_profile_post_id', true );
				$source = get_post_meta( $post->ID, '_canonical_source', true );
				?>
				<input type="hidden" id="_wsuwp_profile_post_id" name="_wsuwp_profile_post_id" value="<?php echo esc_attr( $record_id ); ?>" />

				<input type="hidden" id="_wsuwp_profile_canonical_source" name="_canonical_source" value="<?php echo esc_url( $source ); ?>" />
			<?php } ?>

			<div class="load-ad-container">
				<p class="description" data-location="<?php echo esc_attr( $data_location ); ?>"><?php
				if ( '' === $readonly ) { ?>
					Enter the WSU Network ID for this user to populate data from <?php echo esc_html( $data_location ); ?>.
				<?php } else { ?>
					The WSU Network ID used to populate this profile's data from <?php echo esc_html( $data_location ); ?>.
				<?php } ?></p>
			</div>

			</div>
		</div>
		<div id="major-publishing-actions">

			<div id="delete-action">
				<?php
				if ( 'auto-draft' !== $post->post_status && current_user_can( 'delete_post', $post->ID ) ) {
					if ( ! EMPTY_TRASH_DAYS ) {
						$delete_text = __( 'Delete Permanently' );
					} else {
						$delete_text = __( 'Move to Trash' );
					} ?>
					<a class="submitdelete deletion" href="<?php echo get_delete_post_link( $post->ID ); ?>"><?php echo esc_html( $delete_text ); ?></a><?php
				}
				?>
			</div>

			<div id="publishing-action">
				<span class="spinner"></span>
				<?php
				if ( $can_publish && ( ! in_array( $post->post_status, array( 'publish', 'future', 'private' ), true ) || 0 === $post->ID ) ) { ?>
					<button class="button" id="load-ad-data">Load</button>
					<button class="button button-primary profile-hide-button" id="confirm-ad-data">Confirm</button>
					<input type="hidden" id="confirm-ad-hash" name="confirm_ad_hash" value="" />
					<input name="original_publish" type="hidden" id="original_publish"
							value="<?php esc_attr_e( 'Publish' ); ?>"/>
					<?php submit_button( __( 'Publish' ), 'primary button-large profile-hide-button', 'publish', false );
				} else {
					?>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Update' ); ?>" />
					<input name="save" type="submit" class="button button-primary button-large" id="publish" value="<?php esc_attr_e( 'Update' ); ?>" />
					<?php
				} ?>
			</div>
			<div class="clear"></div>
		</div>
	</div>

	<?php
}

/**
 * Displays a meta box for selecting taxonomy terms.
 *
 * @since 0.3.9
 *
 * @param array $post_types Post types and their associated taxonomies.
 *
 * @return array Post types and their associated taxonomies.
 */
function taxonomy_meta_box( $post_types ) {
	// Reversed because that seems to better match the order of importance.
	$post_types[ slug() ] = array_reverse( get_object_taxonomies( slug() ) );

	return $post_types;
}

/**
 * Displays a meta box used to show which sites a profile is listed on.
 *
 * @since 0.3.0
 *
 * @param WP_Post $post Post object.
 */
function display_listing_meta_box( $post ) {
	$listings = get_post_meta( $post->ID, '_wsuwp_profile_listed_on', true );

	if ( $listings ) {
		?>
		<ul>
		<?php foreach ( $listings as $listing ) { ?>
			<li><a href="<?php echo esc_url( $listing ); ?>"><?php echo esc_url( $listing ); ?></a></li>
		<?php } ?>
		</ul>
		<?php
	}
}

/**
 * Displays a meta box used to adjust the display of a profile.
 *
 * @since 0.3.2
 *
 * @param WP_Post $post Post object.
 */
function display_local_display_meta_box( $post ) {
	$photo = get_post_meta( $post->ID, '_use_photo', true );
	$title = get_post_meta( $post->ID, '_use_title', true );
	$bio = get_post_meta( $post->ID, '_use_bio', true );
	?>
	<p class="description">Select content to display for the public view of this profile.</p>

	<p class="post-attributes-label-wrapper">
		<label class="post-attributes-label">Photo</label>
	</p>
	<div id="local-display-photo" data-selected="<?php echo esc_attr( $photo ); ?>"></div>

	<p class="post-attributes-label-wrapper">
		<label class="post-attributes-label" for="local-display-title">Title</label>
	</p>
	<select id="local-display-title"
			name="_use_title[]"
			multiple="multiple"
			size="1"
			class="widefat"
			data-selected="<?php echo esc_attr( $title ); ?>">
	</select>

	<p class="post-attributes-label-wrapper">
		<label class="post-attributes-label" for="local-display-bio">Biography</label>
	</p>
	<select id="local-display-bio" name="_use_bio" class="widefat">
		<option value="personal"<?php selected( $bio, 'personal' ); ?>>Personal</option>
		<option value="bio_unit"<?php selected( $bio, 'bio_unit' ); ?>>Unit</option>
		<option value="bio_university"<?php selected( $bio, 'bio_university' ); ?>>University</option>
	</select>

	<?php
}

/**
 * Sanitizes values for repeatable text fields.
 *
 * @since 0.3.0
 *
 * @param array $values Unsanitzed repeatable text field values.
 *
 * @return array|string Sanitized values.
 */
function sanitize_repeatable_text_fields( $values ) {
	if ( ! is_array( $values ) || 0 === count( $values ) ) {
		return '';
	}

	$sanitized_values = array();

	foreach ( $values as $index => $value ) {
		if ( '' !== $value ) {
			$sanitized_values[] = sanitize_text_field( $value );
		}
	}

	return $sanitized_values;
}

/**
 * Sanitizes photo collection values.
 *
 * @since 0.3.0
 *
 * @param array $photos Unsanitized values.
 *
 * @return array Sanitized values.
 */
function sanitize_photos( $photos ) {
	if ( ! is_array( $photos ) || 0 === count( $photos ) ) {
		return '';
	}

	$sanitized_photos = array();

	foreach ( $photos as $index => $photo_id ) {
		// The attachment must have a numeric ID and still exist in order to be added.
		if ( is_numeric( $photo_id ) && is_string( get_post_status( $photo_id ) ) ) {
			$sanitized_photos[] = absint( $photo_id );
		}
	}

	return $sanitized_photos;
}

/**
 * Saves data associated with a profile.
 *
 * @since 0.1.0
 *
 * @param int $post_id Post ID.
 */
function save_post( $post_id ) {
	if ( ! isset( $_POST['wsuwp_profile_nonce'] ) || ! wp_verify_nonce( $_POST['wsuwp_profile_nonce'], 'wsuwp_profile' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Make sure taxonomy terms are properly removed when none are selected.
	$taxonomies = get_post_taxonomies( $post_id );

	foreach ( $taxonomies as $taxonomy ) {
		if ( ! isset( $_POST['tax_input'][ $taxonomy ] ) ) {
			wp_set_object_terms( $post_id, '', $taxonomy );
		}
	}

	// Store only select meta if this is not the primary directory.
	if ( false === is_primary_directory() ) {
		if ( isset( $_POST['_wsuwp_profile_post_id'] ) && '' !== $_POST['_wsuwp_profile_post_id'] ) {
			update_post_meta( $post_id, '_wsuwp_profile_post_id', absint( $_POST['_wsuwp_profile_post_id'] ) );
		}

		if ( isset( $_POST['_canonical_source'] ) && '' !== $_POST['_canonical_source'] ) {
			update_post_meta( $post_id, '_canonical_source', esc_url_raw( $_POST['_canonical_source'] ) );
		}

		if ( isset( $_POST['_use_photo'] ) && '' !== $_POST['_use_photo'] ) {
			update_post_meta( $post_id, '_use_photo', absint( $_POST['_use_photo'] ) );
		} else {
			delete_post_meta( $post_id, '_use_photo' );
		}

		if ( isset( $_POST['_use_title'] ) && '' !== $_POST['_use_title'] ) {
			$titles = implode( ',', $_POST['_use_title'] );
			update_post_meta( $post_id, '_use_title', sanitize_text_field( $titles ) );
		} else {
			delete_post_meta( $post_id, '_use_title' );
		}

		if ( isset( $_POST['_use_bio'] ) && '' !== $_POST['_use_bio'] ) {
			update_post_meta( $post_id, '_use_bio', sanitize_text_field( $_POST['_use_bio'] ) );
		} else {
			delete_post_meta( $post_id, '_use_bio' );
		}

		return;
	}

	$keys = get_registered_meta_keys( 'post' );

	foreach ( $keys as $key => $args ) {
		if ( isset( $_POST[ $key ] ) && isset( $args['sanitize_callback'] ) ) {
			// Each piece of meta is registered with sanitization.
			update_post_meta( $post_id, $key, $_POST[ $key ] );
		} elseif ( '_wsuwp_profile_degree' === $key || '_wsuwp_profile_photos' === $key ) {
			delete_post_meta( $post_id, $key );
		}
	}
}

/**
 * Retrieves profile data from the primary directory.
 *
 * @since 0.3.0
 *
 * @param string $nid The user's unique ID. At WSU, this is a NID (network ID).
 *
 * @return object|bool List of predefined information we'll expect on the other side.
 *                     False if person is not available.
 */
function get_rest_data( $nid ) {
	$request_url = add_query_arg(
		array(
			'_embed' => true,
			'wsu_nid' => $nid,
		),
		\WSUWP\People_Directory\API_path() . 'wp/v2/people'
	);

	$response = wp_remote_get( $request_url );

	if ( is_wp_error( $response ) ) {
		return false;
	}

	$data = wp_remote_retrieve_body( $response );
	$data = json_decode( $data );

	return $data[0];
}

/**
 * Retrieves profile data from an organizational source.
 *
 * @since 0.1.0
 * @since 1.0.0 Updated for extension by other organizations.
 *
 * @param string $nid The user's unique ID. At WSU, this is a NID (network ID).
 *
 * @return array List of predefined information we'll expect on the other side.
 */
function get_organization_person_data( $nid ) {
	$person_data = apply_filters( 'wsuwp_people_get_organization_person_data', false, $nid );

	if ( ! $person_data || ! is_array( $person_data ) ) {
		return array();
	}

	if ( empty( array_filter( $person_data ) ) ) {
		return array();
	}

	$return_data = array(
		'given_name' => '',
		'surname' => '',
		'title' => '',
		'office' => '',
		'street_address' => '',
		'telephone_number' => '',
		'email' => '',
	);

	foreach ( $return_data as $key => $value ) {
		if ( isset( $person_data[ $key ] ) ) {
			$return_data[ $key ] = sanitize_text_field( $person_data[ $key ] );
		}
	}

	$hash = md5( wp_json_encode( $return_data ) );
	$return_data['confirm_ad_hash'] = $hash;

	return $return_data;
}

/**
 * Processes an AJAX request for information attached to a person's unique ID.
 * We'll return the data here for confirmation. Confirmation will be handled elsewhere.
 *
 * @since 0.1.0
 */
function ajax_get_data_by_nid() {
	check_ajax_referer( 'wsu-people-nid-lookup' );

	$nid = sanitize_text_field( $_POST['network_id'] );

	if ( empty( $nid ) ) {
		wp_send_json_error( 'Invalid or empty Network ID' );
	}

	$nid_query = new WP_Query( array(
		'meta_key' => '_wsuwp_profile_ad_nid',
		'meta_value' => $nid,
		'post_type' => slug(),
		'posts_per_page' => -1,
	) );

	if ( 0 < $nid_query->found_posts ) {
		wp_send_json_error( "A profile for $nid already exists." );
	}

	$return_data = false;

	// Try to retrieve a person from the primary directory first.
	// We do this in here so the above check for existing profiles can be performed.
	if ( 'rest' === $_POST['request_from'] ) {
		$return_data = get_rest_data( $nid );
	}

	if ( ! $return_data || 'ad' === $_POST['request_from'] ) {
		$return_data = get_organization_person_data( $nid );
	}

	wp_send_json_success( $return_data );
}

/**
 * Processes an AJAX request to confirm the information attached to a person's unique ID
 * that has been pulled from a central source. At this point we'll do the lookup again
 * and save the information to the current profile.
 *
 * @since 0.1.0
 */
function ajax_confirm_nid_data() {
	check_ajax_referer( 'wsu-people-nid-lookup' );

	$nid = sanitize_text_field( $_POST['network_id'] );

	if ( empty( $nid ) ) {
		wp_send_json_error( 'Invalid or empty Network ID' );
	}

	// Data is sanitized before return.
	$confirm_data = get_organization_person_data( $nid );

	if ( 'ad' === $_POST['request_from'] && $confirm_data['confirm_ad_hash'] !== $_POST['confirm_ad_hash'] ) {
		wp_send_json_error( 'Previously retrieved data does not match the data attached to this network ID.' );
	}

	if ( empty( absint( $_POST['post_id'] ) ) ) {
		wp_send_json_error( 'Invalid profile post ID.' );
	}

	$post_id = $_POST['post_id'];

	update_post_meta( $post_id, '_wsuwp_profile_ad_nid', $nid );

	// Only save this meta on the main site.
	if ( true === is_primary_directory() ) {
		update_post_meta( $post_id, '_wsuwp_profile_ad_name_first', $confirm_data['given_name'] );
		update_post_meta( $post_id, '_wsuwp_profile_ad_name_last', $confirm_data['surname'] );
		update_post_meta( $post_id, '_wsuwp_profile_ad_title', $confirm_data['title'] );
		update_post_meta( $post_id, '_wsuwp_profile_ad_office', $confirm_data['office'] );
		update_post_meta( $post_id, '_wsuwp_profile_ad_address', $confirm_data['street_address'] );
		update_post_meta( $post_id, '_wsuwp_profile_ad_phone', $confirm_data['telephone_number'] );
		update_post_meta( $post_id, '_wsuwp_profile_ad_email', $confirm_data['email'] );
	}

	wp_send_json_success( 'Updated' );
}

/**
 * Keys of meta fields to revision.
 *
 * @since 0.1.0
 *
 * @param array $keys Meta keys to track revisions for.
 *
 * @return array
 */
function add_meta_keys_to_revision( $keys ) {
	foreach ( meta_keys() as $key => $args ) {
		$keys[] = $args['meta_key'];
	}

	return $keys;
}

/**
 * Modifies taxonomy columns on the "All Profiles" screen.
 *
 * @since 0.1.0
 *
 * @param array $columns Default columns on the "All Profiles" screen.
 *
 * @return array
 */
function manage_people_taxonomy_columns( $columns ) {
	$columns[] = 'wsuwp_university_org';
	$columns[] = 'wsuwp_university_location';

	return $columns;
}

/**
 * Handles legacy meta data removal.
 *
 * @since 0.3.0
 */
function ajax_delete_legacy_meta() {
	check_ajax_referer( 'wsu-people-nid-lookup' );

	$post_id = absint( $_POST['post_id'] );
	$meta_key = sanitize_text_field( $_POST['meta_key'] );

	if ( empty( $post_id ) || empty( $meta_key ) ) {
		wp_send_json_error( 'Invalid or empty Network ID' );
	}

	delete_post_meta( $post_id, $meta_key );

	wp_send_json_success();
}

/**
 * Enqueues the assets used for profile post type admin pages on secondary sites.
 *
 * @since 0.3.0
 *
 * @param array $to_load Contains boolean values whether TinyMCE and Quicktags are being loaded.
 */
function admin_enqueue_secondary_scripts( $to_load ) {
	if ( ! is_admin() ) {
		return;
	}

	$screen = get_current_screen();

	if ( slug() !== $screen->post_type ) {
		return;
	}

	// Make sure TinyMCE is loaded before loading the script.
	if ( empty( $to_load['tinymce'] ) ) {
		return;
	}

	$profile_vars = array(
		'rest_url' => \WSUWP\People_Directory\API_path() . 'wp/v2/people',
	);

	if ( 'add' !== $screen->action ) {
		$profile_vars['load_data'] = true;
		$profile_vars['nonce'] = \WSUWP\People_Directory\create_rest_nonce();
		$profile_vars['uid'] = wp_get_current_user()->ID;
	}

	wp_enqueue_script( 'wsuwp-people-edit-profile-secondary', plugins_url( 'js/admin-edit-profile-secondary.min.js', dirname( __FILE__ ) ), array( 'jquery', 'underscore' ), plugin_version(), true );
	wp_localize_script( 'wsuwp-people-edit-profile-secondary', 'wsuwp_people_edit_profile_secondary', $profile_vars );
}

/**
 * Adds an init callback to any tinyMCE editor created on a secondary site
 * profile page. This will help mitigate race conditions when populating
 * with data from the main site via REST API.
 *
 * @since 1.0.0
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

	if ( 'wsuwp_people_profile' !== get_current_screen()->id ) {
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

/**
 * Removes post content before saving a profile on a secondary site.
 *
 * @since 0.3.0
 *
 * @param array $data An array of slashed post data.
 *
 * @return array
 */
function wp_insert_post_data( $data ) {
	if ( false === is_primary_directory() && slug() === $data['post_type'] ) {
		$data['post_content'] = '';
		$data['post_content_filtered'] = '';
	}

	return $data;
}

/**
 * Adds a column for biography display to the All Profiles page on secondary sites.
 *
 * @since 0.3.0
 *
 * @param array $columns Default columns on the "All Profiles" screen.
 *
 * @return array Modified columns.
 */
function add_bio_column( $columns ) {
	$bio_column = array(
		'use_bio' => 'Display Biography',
	);

	// Add the "Display Biography" column in before the "Date" column.
	$new_columns = array_slice( $columns, 0, -2, true ) + $bio_column + array_slice( $columns, -2, null, true );

	return $new_columns;
}

/**
 * Populates the biography display column on the All Profiles page on secondary sites.
 *
 * @since 0.3.0
 *
 * @param string $column_name The name of the column to display.
 * @param int    $post_id     The ID of the current post.
 */
function bio_column( $column_name, $post_id ) {
	if ( 'use_bio' === $column_name ) {
		$bio = esc_html( get_post_meta( $post_id, '_use_bio', true ) );

		if ( 'personal' === $bio ) {
			?><span data-bio="personal">Personal</span><?php
		} elseif ( 'bio_unit' === $bio ) {
			?><span data-bio="bio_unit">Unit</span><?php
		} elseif ( 'bio_university' === "$bio" ) {
			?><span data-bio="bio_university">University</span><?php
		}
	}
}

/**
 * Outputs controls for quick or bulk editing the biography display for profiles.
 *
 * @since 0.3.0
 *
 * @param string $column_name The name of the column to edit.
 * @param string $post_type   The type of the posts.
 */
function display_bio_edit( $column_name, $post_type ) {
	static $nonce = true;

	if ( $nonce ) {
		$nonce = true;

		wp_nonce_field( 'wsuwp_profile', 'wsuwp_profile_nonce' );
	}

	if ( 'use_bio' === $column_name ) {
		?>
		<fieldset class="inline-edit-col-right inline-edit-book">
			<div class="inline-edit-col column-use-bio">
				<label class="inline-edit-group">
					<span class="title">Biography to display</span>
					<select name="_use_bio">
						<option value="personal">Personal</option>
						<option value="bio_unit">Unit</option>
						<option value="bio_university">University</option>
					</select>
				</label>
			</div>
		</fieldset>
		<?php
	}
}

/**
 * Saves quick or bulk edit changes to a profile's biography display.
 *
 * @since 0.3.0
 */
function save_bio_edit() {
	check_ajax_referer( 'person-meta', 'nonce' );

	$post_ids = ( ! empty( $_POST['post_ids'] ) ) ? $_POST['post_ids'] : array();
	$use_bio = ( ! empty( $_POST['use_bio'] ) ) ? $_POST['use_bio'] : null;

	if ( ! empty( $post_ids ) && is_array( $post_ids ) ) {
		foreach ( $post_ids as $post_id ) {
			update_post_meta( $post_id, '_use_bio', $use_bio );
		}
	}

	exit();
}
