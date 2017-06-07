<?php

class WSUWP_People_Post_Type {
	/**
	 * @var WSUWP_People_Post_Type
	 *
	 * @since 0.3.0
	 */
	private static $instance;

	/**
	 * The slug used to register the people post type.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	public static $post_type_slug = 'wsuwp_people_profile';

	/**
	 * A list of post meta keys associated with a person.
	 *
	 * @since 0.3.0
	 *
	 * @var array
	 */
	public static $post_meta_keys = array(
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
			'sanitize_callback' => 'WSUWP_People_Post_Type::sanitize_repeatable_text_fields',
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
			'sanitize_callback' => 'WSUWP_People_Post_Type::sanitize_repeatable_text_fields',
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
			'sanitize_callback' => 'WSUWP_People_Post_Type::sanitize_photos',
			'meta_key' => '_wsuwp_profile_photos',
			'register_as_meta' => true,
		),
		'listed_on' => array(
			'type' => 'array',
			'items' => array(
				'type' => 'string',
			),
			'description' => '',
			'sanitize_callback' => 'WSUWP_People_Post_Type::sanitize_repeatable_text_fields',
			'meta_key' => '_wsuwp_profile_listed_on',
			'updatable_via_rest' => true,
		),
		// Legacy
		'bio_college' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_bio_college',
			'updatable_via_rest' => true,
		),
		'bio_lab' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_bio_lab',
			'updatable_via_rest' => true,
		),
		'bio_department' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_bio_dept',
			'updatable_via_rest' => true,
		),
		'cv_employment' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_employment',
			'updatable_via_rest' => true,
		),
		'cv_honors' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_honors',
			'updatable_via_rest' => true,
		),
		'cv_grants' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_grants',
			'updatable_via_rest' => true,
		),
		'cv_publications' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_publications',
			'updatable_via_rest' => true,
		),
		'cv_presentations' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_presentations',
			'updatable_via_rest' => true,
		),
		'cv_teaching' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_teaching',
			'updatable_via_rest' => true,
		),
		'cv_service' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_service',
			'updatable_via_rest' => true,
		),
		'cv_responsibilities' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_responsibilities',
			'updatable_via_rest' => true,
		),
		'cv_affiliations' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_societies',
			'updatable_via_rest' => true,
		),
		'cv_experience' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_experience',
			'updatable_via_rest' => true,
		),
		'cv_attachment' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'attachment',
			'meta_key' => '_wsuwp_profile_cv',
			'updatable_via_rest' => true,
		),
		'profile_photo' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'attachment',
			'meta_key' => '',
			'updatable_via_rest' => true,
		),
	);

	/**
	 * Maintain and return the one instance. Initiate hooks when called the first time.
	 *
	 * @since 0.3.0
	 *
	 * @return \WSUWP_People_Post_Type
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSUWP_People_Post_Type();
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
		add_action( 'init', array( $this, 'register_post_type' ), 11 );
		add_action( 'init', array( $this, 'register_meta' ), 11 );
		add_action( 'init', array( $this, 'register_taxonomies_for_people' ), 12 );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		add_action( 'edit_form_after_title', array( $this, 'edit_form_after_title' ) );
		add_action( 'edit_form_after_editor', array( $this, 'edit_form_after_editor' ) );

		add_action( 'add_meta_boxes_' . self::$post_type_slug, array( $this, 'person_meta_boxes' ) );

		add_filter( 'wp_insert_post_data', array( $this, 'wp_insert_post_data' ) );
		add_action( 'save_post_' . self::$post_type_slug, array( $this, 'save_post' ) );

		add_action( 'wp_ajax_wsu_people_get_data_by_nid', array( $this, 'ajax_get_data_by_nid' ) );
		add_action( 'wp_ajax_wsu_people_confirm_nid_data', array( $this, 'ajax_confirm_nid_data' ) );

		add_filter( 'wp_post_revision_meta_keys', array( $this, 'add_meta_keys_to_revision' ) );

		add_filter( 'manage_taxonomies_for_' . self::$post_type_slug . '_columns', array( $this, 'manage_people_taxonomy_columns' ) );

		if ( false === WSUWP_People_Directory::is_main_site() ) {
			add_action( 'wp_enqueue_editor', array( $this, 'admin_enqueue_secondary_scripts' ) );
			add_filter( 'wp_editor_settings', array( $this, 'filter_default_editor_settings' ), 10, 2 );
			add_filter( 'manage_' . self::$post_type_slug . '_posts_columns', array( $this, 'add_people_bio_column' ) );
			add_action( 'manage_posts_custom_column', array( $this, 'bio_column' ), 10, 2 );
			add_action( 'quick_edit_custom_box', array( $this, 'display_bio_edit' ), 10, 2 );
			add_action( 'bulk_edit_custom_box', array( $this, 'display_bio_edit' ), 10, 2 );
			add_action( 'wp_ajax_save_bio_edit', array( $this, 'save_bio_edit' ) );
		}
	}

	/**
	 * Register the people post type.
	 *
	 * @since 0.1.0
	 */
	public function register_post_type() {
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
			'description' => 'WSU people directory listings',
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
			),
			'rewrite' => false,
		);

		register_post_type( self::$post_type_slug, $args );
	}

	/**
	 * Register the meta keys used to store data about a person.
	 *
	 * @since 0.3.0
	 */
	public function register_meta() {
		foreach ( self::$post_meta_keys as $key => $args ) {
			if ( ! isset( $args['register_as_meta'] ) ) {
				continue;
			}

			$args['single'] = true;
			register_meta( 'post', $args['meta_key'], $args );
		}
	}

	/**
	 * Add support for WSU University Taxonomies to the People post type.
	 *
	 * @since 0.1.0
	 */
	public function register_taxonomies_for_people() {
		register_taxonomy_for_object_type( 'wsuwp_university_category', self::$post_type_slug );
		register_taxonomy_for_object_type( 'wsuwp_university_location', self::$post_type_slug );
		register_taxonomy_for_object_type( 'wsuwp_university_org', self::$post_type_slug );
	}

	/**
	 * Enqueue the scripts and styles used in the admin.
	 *
	 * @since 0.1.0
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {
		$screen = get_current_screen();

		if ( self::$post_type_slug !== $screen->post_type ) {
			return;
		}

		// Enqueue Edit/Add New Profile scripts and styles.
		if ( 'post-new.php' === $hook_suffix || 'post.php' === $hook_suffix ) {
			$post = get_post();
			$profile_vars = array(
				'nid_nonce' => wp_create_nonce( 'wsu-people-nid-lookup' ),
				'post_id' => $post->ID,
				'request_from' => ( WSUWP_People_Directory::is_main_site() ) ? 'ad' : 'rest',
			);

			wp_enqueue_style( 'wsuwp-people-edit-profile', plugins_url( 'css/admin-person.css', dirname( __FILE__ ) ), array(), WSUWP_People_Directory::$version );
			wp_enqueue_script( 'wsuwp-people-edit-profile', plugins_url( 'js/admin-edit-profile.min.js', dirname( __FILE__ ) ), array( 'underscore' ), WSUWP_People_Directory::$version, true );
			wp_localize_script( 'wsuwp-people-edit-profile', 'wsuwp_people_edit_profile', $profile_vars );

			// Disable autosaving on spoke sites.
			if ( false === WSUWP_People_Directory::is_main_site() ) {
				wp_dequeue_script( 'autosave' );
			}
		}

		// Enqueue All Profiles list table scripts and styles.
		if ( 'edit.php' === $hook_suffix && false === WSUWP_People_Directory::is_main_site() ) {
			wp_enqueue_style( 'wsuwp-people-admin', plugins_url( 'css/admin-people.css', dirname( __FILE__ ) ), array(), WSUWP_People_Directory::$version );
			wp_enqueue_script( 'wsuwp-people-admin', plugins_url( 'js/admin-people.min.js', dirname( __FILE__ ) ), array( 'jquery' ), WSUWP_People_Directory::$version );
			wp_localize_script( 'wsuwp-people-admin', 'wsupeople', array(
				'nonce' => wp_create_nonce( 'person-meta' ),
			) );
		}
	}

	/**
	 * Enqueue the scripts and styles used in the admin for sites other than the primary directory.
	 *
	 * @since 0.3.0
	 *
	 * @param array $to_load Contains boolean values whether TinyMCE and Quicktags are being loaded.
	 */
	public function admin_enqueue_secondary_scripts( $to_load ) {
		$screen = get_current_screen();

		if ( self::$post_type_slug !== $screen->post_type ) {
			return;
		}

		// Make sure TinyMCE is loaded before loading the script.
		if ( empty( $to_load['tinymce'] ) ) {
			return;
		}

		$profile_vars = array(
			'rest_url' => WSUWP_People_Directory::REST_URL(),
		);

		if ( 'add' !== $screen->action ) {
			$profile_vars['load_data'] = true;
			$profile_vars['nonce'] = WSUWP_People_Directory::create_rest_nonce();
			$profile_vars['uid'] = wp_get_current_user()->ID;
		}

		wp_enqueue_script( 'wsuwp-people-edit-profile-secondary', plugins_url( 'js/admin-edit-profile-secondary.min.js', dirname( __FILE__ ) ), array( 'jquery', 'underscore' ), WSUWP_People_Directory::$version, true );
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
	public function filter_default_editor_settings( $settings, $editor_id ) {
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
	 * Adds the interface for editing a profile.
	 *
	 * @since 0.3.0
	 *
	 * @param WP_Post $post Post object.
	 */
	public function edit_form_after_title( $post ) {
		if ( self::$post_type_slug !== $post->post_type ) {
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
		?>
		<div class="wsu-person" data-nid="<?php echo esc_html( $nid ); ?>">

			<script type="text/template" class="wsu-person-repeatable-meta-template">
				<span contenteditable="true" class="<%= type %>" data-placeholder="Enter <%= type %> here"><%= value %></span>
				<input type="hidden" data-for="<%= type %>" name="_wsuwp_profile_<%= type %>[]" value="<%= value %>" />
			</script>

			<input type="hidden" data-for="name" name="post_title" value="<?php echo esc_attr( $post->post_title ); ?>" />
			<input type="hidden" data-for="email" name="_wsuwp_profile_alt_email" value="<?php echo esc_attr( $email_value ); ?>" />
			<input type="hidden" data-for="phone" name="_wsuwp_profile_alt_phone" value="<?php echo esc_attr( $phone_value ); ?>" />
			<input type="hidden" data-for="office" name="_wsuwp_profile_alt_office" value="<?php echo esc_attr( $office_value ); ?>" />
			<input type="hidden" data-for="address" name="_wsuwp_profile_alt_address" value="<?php echo esc_attr( $address_value ); ?>" />
			<input type="hidden" data-for="website" name="_wsuwp_profile_website" value="<?php echo esc_attr( $website ); ?>" />

			<?php
			if ( $working_titles && is_array( $working_titles ) ) {
				foreach ( $working_titles as $working_title ) {
					?><input type="hidden" data-for="title" name="_wsuwp_profile_title[]" value="<?php echo esc_attr( $working_title ); ?>" /><?php
				}
			} else {
				?><input type="hidden" data-for="title" name="_wsuwp_profile_title[]" value="" /><?php
			} ?>

			<?php
			if ( $degrees && is_array( $degrees ) ) {
				foreach ( $degrees as $degree ) {
					?><input type="hidden" data-for="degree" name="_wsuwp_profile_degree[]" value="<?php echo esc_attr( $degree ); ?>" /><?php
				}
			}
			?>

			<?php if ( false === WSUWP_People_Directory::is_main_site() ) { ?>
			<?php $index_used = get_post_meta( $post->ID, '_use_title', true ); ?>
			<input type="hidden" class="use-title" name="_use_title" value="<?php echo esc_attr( $index_used ); ?>" />
			<?php } ?>

			<div class="card">

				<header>

					<h2 contenteditable="true"
						 class="name"
						 data-placeholder="Enter name here"><?php echo esc_html( $post->post_title ); ?></h2>

					<?php
					if ( $degrees && is_array( $degrees ) ) {
						foreach ( $degrees as $degree ) { ?>
						<span contenteditable="true"
								class="degree"
								data-placeholder="Enter degree here"><?php echo esc_html( $degree ); ?></span>
						<?php }
					}
					?>

					<button type="button"
						    data-type="degree"
						    class="wsu-person-button wsu-person-add-repeatable-meta wsu-person-add-degree">+ Add</button>

				</header>

				<figure class="photo">

					<?php
					if ( $photos && is_array( $photos ) ) {
						foreach ( $photos as $photo ) {
							echo '';
						}
					} else {
						?><figcaption>Add photo(s) here</figcaption><?php
					}
					?>

				</figure>

				<div class="contact">

					<?php
					if ( $working_titles && is_array( $working_titles ) ) {
						foreach ( $working_titles as $working_title ) { ?>
						<span contenteditable="true"
								class="title"
								data-placeholder="Enter title here"><?php echo esc_html( $working_title ); ?></span>
						<?php }
					} else { ?>
						<span contenteditable="true"
								class="title"
								data-placeholder="Enter title here"><?php echo esc_html( $title ); ?></span>
					<?php } ?>

					<button type="button"
							data-type="title"
							class="wsu-person-button wsu-person-add-repeatable-meta wsu-person-add-title">+ Add another title</button>

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
	 * Continues the interface for editing a profile.
	 * This piece includes the TinyMCE editors for biographical data entry.
	 *
	 * @since 0.3.0
	 *
	 * @param WP_Post $post Post object.
	 */
	public function edit_form_after_editor( $post ) {
		if ( self::$post_type_slug !== $post->post_type ) {
			return;
		}

		?>
		</div><!--bio_personal-->
		<?php

		foreach ( self::$post_meta_keys as $key => $args ) {
			if ( ! isset( $args['render_as_wp_editor'] ) ) {
				continue;
			}

			?>
			<div id="<?php echo esc_attr( $key );?>" class="wsu-person-bio">

				<h2><?php echo esc_html( $args['description'] ); ?></h2>

				<?php
				$value = get_post_meta( $post->ID, $args['meta_key'], true );

				if ( '_wsuwp_profile_bio_university' === $args['meta_key'] && ! current_user_can( 'create_sites' ) ) {
					echo '<div class="readonly">' . wp_kses_post( apply_filters( 'the_content', $value ) ) . '</div>';
				} else {
					wp_editor( $value, $args['meta_key'] );
				}
				?>
			</div>
			<?php
		}
		?>

		</div><!--wsuwp-person-about-->
		</div><!--wsuwp-person-->
		<?php
	}

	/**
	 * Handles the meta boxes used for capturing information about a person.
	 *
	 * @since 0.3.0
	 *
	 * @param WP_Post $post The post object.
	 */
	public function person_meta_boxes( $post ) {
		remove_meta_box( 'submitdiv', self::$post_type_slug, 'side' );
		remove_meta_box( 'wsuwp_university_orgdiv', self::$post_type_slug, 'side' );
		remove_meta_box( 'wsuwp_university_categorydiv', self::$post_type_slug, 'side' );
		remove_meta_box( 'wsuwp_university_locationdiv', self::$post_type_slug, 'side' );
		remove_meta_box( 'classificationdiv', self::$post_type_slug, 'side' );

		$box_title = ( 'auto-draft' === $post->post_status ) ? 'Create Profile' : 'Update Profile';

		add_meta_box( 'submitdiv', $box_title, array( $this, 'publish_meta_box' ), self::$post_type_slug, 'side', 'high' );

		if ( true === WSUWP_People_Directory::is_main_site() ) {
			add_meta_box(
				'wsuwp_profile_listing',
				'Listed On',
				array( $this, 'display_listing_meta_box' ),
				self::$post_type_slug,
				'normal'
			);
		}
	}

	/**
	 * Replace the default post publishing meta box with our own that guides the user through
	 * a slightly different process for creating and saving a person.
	 *
	 * This was originally copied from WordPress core's `post_submit_meta_box()`.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_Post $post The profile being edited/created.
	 */
	public function publish_meta_box( $post ) {
		$post_type = $post->post_type;
		$post_type_object = get_post_type_object( $post_type );
		$can_publish = current_user_can( $post_type_object->cap->publish_posts );

		$nid = get_post_meta( $post->ID, '_wsuwp_profile_ad_nid', true );

		$readonly = empty( trim( $nid ) ) ? '' : 'readonly';
		$data_location = ( false === WSUWP_People_Directory::is_main_site() ) ? 'people.wsu.edu' : 'Active Directory';
		?>
		<div class="submitbox" id="submitpost">

			<div id="misc-publishing-actions">
				<div class="misc-pub-section">
					<label for="_wsuwp_profile_ad_nid">Network ID</label>:
					<input type="text" id="_wsuwp_profile_ad_nid" name="_wsuwp_profile_ad_nid" value="<?php echo esc_attr( $nid ); ?>" class="widefat" <?php echo esc_attr( $readonly ); ?> />

				<?php if ( false === WSUWP_People_Directory::is_main_site() ) { ?>
					<?php $record_id = get_post_meta( $post->ID, '_wsuwp_profile_post_id', true ); ?>
					<input type="hidden" id="_wsuwp_profile_post_id" name="_wsuwp_profile_post_id" value="<?php echo esc_attr( $record_id ); ?>" />
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
					} else { ?>
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
	 * Display a meta box used to show which sites a person is being listed on.
	 *
	 * @since 0.3.0
	 *
	 * @param WP_Post $post Post object.
	 */
	public function display_listing_meta_box( $post ) {
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
	 * Sanitizes repeatable text fields.
	 *
	 * @since 0.3.0
	 *
	 * @param array $values
	 *
	 * @return array|string
	 */
	public static function sanitize_repeatable_text_fields( $values ) {
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
	 * Sanitizes a collection of photos.
	 *
	 * @since 0.3.0
	 *
	 * @param array $photos
	 *
	 * @return array
	 */
	public static function sanitize_photos( $photos ) {
		if ( ! is_array( $photos ) || 0 === count( $photos ) ) {
			return '';
		}

		$sanitized_photos = array();

		foreach ( $photos as $index => $photo_id ) {
			if ( is_numeric( $photo_id ) ) {
				$sanitized_photos[] = absint( $photo_id );
			}
		}

		return $sanitized_photos;
	}

	/**
	 * Removes post content before saving a person on a secondary site.
	 *
	 * @since 0.3.0
	 *
	 * @param array $data An array of slashed post data.
	 *
	 * @return array
	 */
	public function wp_insert_post_data( $data ) {
		if ( false === WSUWP_People_Directory::is_main_site() && self::$post_type_slug === $data['post_type'] ) {
			$data['post_content'] = '';
			$data['post_content_filtered'] = '';
		}

		return $data;
	}

	/**
	 * Save data associated with a person.
	 *
	 * @since 0.1.0
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_post( $post_id ) {
		if ( ! isset( $_POST['wsuwp_profile_nonce'] ) || ! wp_verify_nonce( $_POST['wsuwp_profile_nonce'], 'wsuwp_profile' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Store only select meta if this is a secondary site.
		if ( false === WSUWP_People_Directory::is_main_site() ) {
			if ( isset( $_POST['_wsuwp_profile_post_id'] ) && '' !== $_POST['_wsuwp_profile_post_id'] ) {
				update_post_meta( $post_id, '_wsuwp_profile_post_id', absint( $_POST['_wsuwp_profile_post_id'] ) );
			}

			if ( isset( $_POST['_use_photo'] ) && '' !== $_POST['_use_photo'] ) {
				update_post_meta( $post_id, '_use_photo', absint( $_POST['_use_photo'] ) );
			} else {
				delete_post_meta( $post_id, '_use_photo' );
			}

			if ( isset( $_POST['_use_title'] ) && '' !== $_POST['_use_title'] ) {
				update_post_meta( $post_id, '_use_title', sanitize_text_field( $_POST['_use_title'] ) );
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
			}
		}
	}

	/**
	 * Retrieves information about a person from the main site.
	 *
	 * @since 0.3.0
	 *
	 * @param string $nid The user's unique ID. At WSU, this is a NID (network ID).
	 *
	 * @return object|bool List of predefined information we'll expect on the other side.
	 *                     False if person is not available.
	 */
	public static function get_rest_data( $nid ) {
		$request_url = add_query_arg(
			array(
				'_embed' => true,
				'wsu_nid' => $nid,
			),
			WSUWP_People_Directory::REST_URL()
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
	 * Retrieves information about a person from an organizational source.
	 *
	 * @since 0.1.0
	 * @since 1.0.0 Updated for extension by other organizations.
	 *
	 * @param string $nid The user's unique ID. At WSU, this is a NID (network ID).
	 *
	 * @return array List of predefined information we'll expect on the other side.
	 */
	private function get_organization_person_data( $nid ) {
		$person_data = apply_filters( 'wsuwp_people_get_organization_person_data', false, $nid );

		if ( empty( $person_data ) ) {
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
	public function ajax_get_data_by_nid() {
		check_ajax_referer( 'wsu-people-nid-lookup' );

		$nid = sanitize_text_field( $_POST['network_id'] );

		if ( empty( $nid ) ) {
			wp_send_json_error( 'Invalid or empty Network ID' );
		}

		// If this isn't a manual refresh, make sure the profile doesn't already exist.
		if ( 'false' === $_POST['is_refresh'] ) {

			$nid_query = new WP_Query( array(
				'meta_key' => '_wsuwp_profile_ad_nid',
				'meta_value' => $nid,
				'post_type' => self::$post_type_slug,
				'posts_per_page' => -1,
			) );

			if ( 0 < $nid_query->found_posts ) {
				wp_send_json_error( 'A profile for this person already exists' );
			}
		}

		$return_data = false;

		// Try to retrieve a person from people.wsu.edu first.
		// We do this in here so the above check for existing profiles can be performed.
		if ( 'rest' === $_POST['request_from'] ) {
			$return_data = self::get_rest_data( $nid );
		}

		if ( ! $return_data || 'ad' === $_POST['request_from'] ) {
			$return_data = $this->get_organization_person_data( $nid );
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
	public function ajax_confirm_nid_data() {
		check_ajax_referer( 'wsu-people-nid-lookup' );

		$nid = sanitize_text_field( $_POST['network_id'] );

		if ( empty( $nid ) ) {
			wp_send_json_error( 'Invalid or empty Network ID' );
		}

		// Data is sanitized before return.
		$confirm_data = $this->get_organization_person_data( $nid );

		if ( 'ad' === $_POST['request_from'] && $confirm_data['confirm_ad_hash'] !== $_POST['confirm_ad_hash'] ) {
			wp_send_json_error( 'Previously retrieved data does not match the data attached to this network ID.' );
		}

		if ( empty( absint( $_POST['post_id'] ) ) ) {
			wp_send_json_error( 'Invalid profile post ID.' );
		}

		$post_id = $_POST['post_id'];

		update_post_meta( $post_id, '_wsuwp_profile_ad_nid', $nid );

		// Only save this meta on the main site.
		if ( true === WSUWP_People_Directory::is_main_site() ) {
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
	public function add_meta_keys_to_revision( $keys ) {
		foreach ( self::$post_meta_keys as $key => $args ) {
			$keys[] = $args['meta_key'];
		}

		return $keys;
	}

	/**
	 * Modify taxonomy columns on the "All Profiles" screen.
	 *
	 * @since 0.1.0
	 *
	 * @param array $columns Default columns on the "All Profiles" screen.
	 *
	 * @return array
	 */
	public function manage_people_taxonomy_columns( $columns ) {
		$columns[] = 'wsuwp_university_org';
		$columns[] = 'wsuwp_university_location';

		return $columns;
	}

	/**
	 * Add a column for biography display to the people list table.
	 *
	 * @since 0.3.0
	 *
	 * @param array $columns Default columns on the "All Profiles" screen.
	 *
	 * @return array
	 */
	public function add_people_bio_column( $columns ) {
		$bio_column = array(
			'use_bio' => 'Display Biography',
		);

		// Add the "Display Biography" column in before the "Date" column.
		$new_columns = array_slice( $columns, 0, -2, true ) + $bio_column + array_slice( $columns, -2, null, true );

		return $new_columns;
	}

	/**
	 * Add a column for biography display to the people list table.
	 *
	 * @since 0.3.0
	 *
	 * @param string $column_name The name of the column to display.
	 * @param int    $post_id     The ID of the current post.
	 */
	public function bio_column( $column_name, $post_id ) {
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
	 * Add a column for biography display to the people list table.
	 *
	 * @since 0.3.0
	 *
	 * @param string $column_name The name of the column to edit.
	 * @param string $post_type   The type of the posts.
	 */
	public function display_bio_edit( $column_name, $post_type ) {
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
	 * Saves bulk edit changes to the biography display meta.
	 *
	 * @since 0.3.0
	 */
	public function save_bio_edit() {
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
}
