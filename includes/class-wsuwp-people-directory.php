<?php

class WSUWP_People_Directory {
	/**
	 * @var WSUWP_People_Directory
	 */
	private static $instance;

	/**
	 * The plugin version number, used to break caches and trigger
	 * upgrade routines.
	 *
	 * @var string
	 */
	var $version = '0.2.2';

	/**
	 * The slug used to register the "Personnel" custom content type.
	 *
	 * @var string
	 */
	var $post_type_slug = 'wsuwp_people_profile';

	/**
	 * The slugs used to register the 'Personnel" taxonomies.
	 *
	 * @var string
	 */
	var $taxonomy_slug_appointments = 'appointment';
	var $taxonomy_slug_classifications = 'classification';

	/**
	 * A list of post meta keys associated with a profile.
	 *
	 * @var array
	 */
	var $post_meta_keys = array(
		'nid' => array(
			'type' => 'ad',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_ad_nid',
		),
		'first_name' => array(
			'type' => 'ad',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_ad_name_first',
		),
		'last_name' => array(
			'type' => 'ad',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_ad_name_last',
		),
		'position_title' => array(
			'type' => 'ad',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_ad_title',
		),
		'office' => array(
			'type' => 'ad',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_ad_office',
		),
		'address' => array(
			'type' => 'ad',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_ad_address',
		),
		'phone' => array(
			'type' => 'ad',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_ad_phone',
		),
		'phone_ext' => array(
			'type' => 'ad',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_ad_phone_ext',
		),
		'email' => array(
			'type' => 'ad',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_ad_email',
		),
		'office_alt' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_alt_office',
		),
		'phone_alt' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_alt_phone',
		),
		'email_alt' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'meta_key' => '_wsuwp_profile_alt_email',
		),
		'website' => array(
			'type' => 'string',
			'description' => '',
			'sanitize_callback' => 'esc_url_raw',
			'meta_key' => '_wsuwp_profile_website',
		),
		'degree' => array(
			'type' => 'array',
			'description' => '',
			'sanitize_callback' => 'WSUWP_People_Directory::sanitize_repeatable_text_fields',
			'meta_key' => '_wsuwp_profile_degree',
		),
		'working_titles' => array(
			'type' => 'array',
			'description' => '',
			'sanitize_callback' => 'WSUWP_People_Directory::sanitize_repeatable_text_fields',
			'meta_key' => '_wsuwp_profile_title',
		),
		'bio_unit' => array(
			'type' => 'textarea',
			'description' => 'Unit Biography',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_bio_unit',
		),
		'bio_university' => array(
			'type' => 'textarea',
			'description' => 'University Biography',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_bio_university',
		),
		'photos' => array(
			'type' => 'array',
			'description' => 'A collection of photos',
			'sanitize_callback' => 'WSUWP_People_Directory::sanitize_photos',
			'meta_key' => '_wsuwp_profile_photos',
		),
		// Legacy
		'bio_college' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_bio_college',
		),
		'bio_lab' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_bio_lab',
		),
		'bio_department' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_bio_dept',
		),
		'cv_employment' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_employment',
		),
		'cv_honors' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_honors',
		),
		'cv_grants' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_grants',
		),
		'cv_publications' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_publications',
		),
		'cv_presentations' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_presentations',
		),
		'cv_teaching' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_teaching',
		),
		'cv_service' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_service',
		),
		'cv_responsibilities' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_responsibilities',
		),
		'cv_affiliations' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_societies',
		),
		'cv_experience' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'wp_kses_post',
			'meta_key' => '_wsuwp_profile_experience',
		),
		'cv_attachment' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'attachment',
			'meta_key' => '_wsuwp_profile_cv',
		),
		'profile_photo' => array(
			'type' => 'legacy',
			'sanitize_callback' => 'attachment',
			'meta_key' => '',
		),
	);

	/**
	 * Maintain and return the one instance. Initiate hooks when
	 * called the first time.
	 *
	 * @since 0.0.1
	 *
	 * @return \WSUWP_People_Directory
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSUWP_People_Directory();
			self::$instance->setup_hooks();
		}
		return self::$instance;
	}

	/**
	 * Setup hooks to include.
	 *
	 * @since 0.0.1
	 */
	public function setup_hooks() {
		// Custom content type and taxonomies.
		add_action( 'init', array( $this, 'register_personnel_content_type' ), 11 );
		add_action( 'init', array( $this, 'register_taxonomies' ), 11 );
		add_action( 'init', array( $this, 'add_taxonomies' ), 12 );
		add_action( 'init', array( $this, 'image_sizes' ) );

		// Custom meta and all that.
		add_action( 'init', array( $this, 'register_meta' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_filter( 'enter_title_here', array( $this, 'enter_title_here' ) );
		add_action( 'edit_form_after_title', array( $this, 'edit_form_after_title' ) );
		add_action( 'edit_form_after_editor',	array( $this, 'edit_form_after_editor' ) );
		add_action( "add_meta_boxes_{$this->post_type_slug}", array( $this, 'add_meta_boxes' ), 10, 1 );
		add_action( 'do_meta_boxes', array( $this, 'do_meta_boxes' ), 10, 3 );
		add_action( "save_post_{$this->post_type_slug}", array( $this, 'save_post' ), 10, 2 );
		add_filter( 'wp_post_revision_meta_keys', array( $this, 'add_meta_keys_to_revision' ) );

		// Modify taxonomy columns on "All Profiles" page.
		add_filter( "manage_taxonomies_for_{$this->post_type_slug}_columns", array( $this, 'wsuwp_people_profile_columns' ) );

		// Allow REST get_items() queries by additional data.
		add_action( 'init', array( $this, 'register_wsu_nid_query_var' ) );
		add_filter( "rest_{$this->post_type_slug}_query", array( $this, 'rest_query_vars' ), 10, 2 );
		add_action( 'pre_get_posts', array( $this, 'handle_wsu_nid_query_var' ) );

		// Register custom fields with the REST API.
		add_action( 'rest_api_init', array( $this, 'register_api_fields' ) );

		// Capabilities and related.
		add_filter( 'user_has_cap', array( $this, 'user_has_cap' ), 10, 3 );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 999 );
		add_action( 'wp_dashboard_setup', array( $this, 'wp_dashboard_setup' ) );
		add_action( 'pre_get_posts', array( $this, 'limit_media_library' ) );
		//add_action( 'views_edit-' . $this->post_type_slug, array( $this, 'edit_views' ) );
		//add_filter( 'parse_query', array ( $this, 'parse_query' ) );

		// Modify 'wsuwp_people_profile' content type query.
		add_action( 'pre_get_posts', array( $this, 'profile_archives' ) );

		// Handle ajax requests from the admin.
		add_action( 'wp_ajax_wsu_people_get_data_by_nid', array( $this, 'ajax_get_data_by_nid' ) );
		add_action( 'wp_ajax_wsu_people_confirm_nid_data', array( $this, 'ajax_confirm_nid_data' ) );
	}

	/**
	 * Register the profiles content type.
	 */
	public function register_personnel_content_type() {
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
				'author',
			),
			'taxonomies' => array(
				'post_tag',
			),
			'has_archive' => true,
			'rewrite' => array(
				'slug' => 'profile',
				'with_front' => false,
			),
			'show_in_rest' => true,
			'rest_base' => 'people',
		);

		register_post_type( $this->post_type_slug, $args );
	}

	/**
	 * Register a couple taxonomies.
	 */
	public function register_taxonomies() {

		$appointments = array(
			'labels'            => array(
				'name'          => 'Appointments',
				'singular_name' => 'Appointment',
				'search_items'  => 'Search Appointments',
				'all_items'     => 'All Appointments',
				'edit_item'     => 'Edit Appointment',
				'update_item'   => 'Update Appointment',
				'add_new_item'  => 'Add New Appointment',
				'new_item_name' => 'New Appointment Name',
				'menu_name'     => 'Appointments',
			),
			'description'  => 'Personnel Appointments',
			'public'       => true,
			'hierarchical' => true,
			'show_ui'      => true,
			'show_in_menu' => true,
			'query_var'    => $this->taxonomy_slug_appointments,
			'show_in_rest' => true,
		);
		register_taxonomy( $this->taxonomy_slug_appointments, $this->post_type_slug, $appointments );

		$classifications = array(
			'labels'        => array(
				'name'          => 'Classifications',
				'singular_name' => 'Classification',
				'search_items'  => 'Search Classifications',
				'all_items'     => 'All Classifications',
				'edit_item'     => 'Edit Classification',
				'update_item'   => 'Update Classification',
				'add_new_item'  => 'Add New Classification',
				'new_item_name' => 'New Classification Name',
				'menu_name'     => 'Classifications',
			),
			'description'   => 'Personnel Classifications',
			'public'        => true,
			'hierarchical'  => true,
			'show_ui'       => true,
			'show_in_menu'  => true,
			'query_var'     => $this->taxonomy_slug_classifications,
			'show_in_rest'  => true,
		);
		register_taxonomy( $this->taxonomy_slug_classifications, $this->post_type_slug, $classifications );

	}

	/**
	 * Add support for WSU University Taxonomies.
	 */
	public function add_taxonomies() {
		register_taxonomy_for_object_type( 'wsuwp_university_category', $this->post_type_slug );
		register_taxonomy_for_object_type( 'wsuwp_university_location', $this->post_type_slug );
		register_taxonomy_for_object_type( 'wsuwp_university_org', $this->post_type_slug );
	}

	/**
	 * Remove some images sizes.
	 */
	public function image_sizes() {
		remove_image_size( 'spine-small_size' );
		remove_image_size( 'spine-large_size' );
		remove_image_size( 'spine-xlarge_size' );
	}

	/**
	 * Register the meta keys used to store profile data.
	 */
	public function register_meta() {
		foreach ( $this->post_meta_keys as $key => $args ) {
			if ( 'legacy' === $args['type'] || 'ad' === $args['type'] ) {
				continue;
			}

			$args['single'] = true;
			register_meta( 'post', $key, $args );
		}
	}

	/**
	 * Enqueue the scripts and styles used in the admin interface.
	 *
	 * @param string $hook The current admin page.
	 */
	public function admin_enqueue_scripts( $hook ) {
		$screen = get_current_screen();

		if ( ( 'post-new.php' === $hook || 'post.php' === $hook ) && $screen->post_type === $this->post_type_slug ) {
			$post = get_post();
			$profile_vars = array(
				'nid_nonce' => wp_create_nonce( 'wsu-people-nid-lookup' ),
				'post_id' => $post->ID,
			);

			wp_enqueue_style( 'wsuwp-people-admin-style', plugins_url( 'css/admin-profile-style.css', dirname( __FILE__ ) ), array(), $this->version );
			wp_enqueue_script( 'wsuwp-people-admin-script', plugins_url( 'js/admin-profile.min.js', dirname( __FILE__ ) ), array( 'jquery-ui-tabs', 'underscore' ), $this->version, true );
			wp_localize_script( 'wsuwp-people-admin-script', 'wsupeople', $profile_vars );
		}

		if ( 'edit.php' === $hook && $screen->post_type === $this->post_type_slug ) {
			wp_enqueue_style( 'wsuwp-people-admin-style', plugins_url( 'css/admin-edit.css', dirname( __FILE__ ) ), array(), $this->version );
			wp_enqueue_script( 'wsuwp-people-admin-script', plugins_url( 'js/admin-edit.min.js', dirname( __FILE__ ) ), array( 'jquery' ), $this->version );
		}

	}

	/**
	 * Change the "Enter title here" text for the Personnel content type.
	 *
	 * @param string $title The placeholder text displayed in the title input field.
	 *
	 * @return string
	 */
	public function enter_title_here( $title ) {
		$screen = get_current_screen();

		if ( $this->post_type_slug === $screen->post_type ) {
			$title = 'Enter name here';
		}

		return $title;
	}

	/**
	 * Add tabs for each biography.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function edit_form_after_title( $post ) {
		if ( $this->post_type_slug !== $post->post_type ) {
			return;
		}
		?>
		<?php do_meta_boxes( get_current_screen(), 'after_title', $post ); ?>
		<div id="wsuwp-profile-tabs">
			<ul>
				<li class="wsuwp-profile-tab wsuwp-profile-bio-tab">
					<a href="#wsuwp-profile-default" class="nav-tab">Personal Biography</a>
				</li>
				<?php
				$keys = get_registered_meta_keys( 'post' );

				foreach ( $keys as $key => $args ) {
					if ( 'textarea' !== $args['type'] ) {
						continue;
					}

					?>
					<li class="wsuwp-profile-tab wsuwp-profile-bio-tab">
						<a href="#<?php echo esc_attr( $key ); ?>" class="nav-tab"><?php echo esc_html( $args['description'] ); ?></a>
					</li>
					<?php
				}
				?>
			</ul>
			<div id="wsuwp-profile-default" class="wsuwp-profile-panel">
		<?php
	}

	/**
	 * Add markup after the default editor of the edit screen for the Personnel content type.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function edit_form_after_editor( $post ) {
		if ( $this->post_type_slug !== $post->post_type ) {
			return;
		}
		?>
			</div><!--wsuwp-profile-default-->

			<?php
			foreach ( $this->post_meta_keys as $key => $args ) {
				if ( 'textarea' !== $args['type'] ) {
					continue;
				}

				$value = get_post_meta( $post->ID, $args['meta_key'], true );
				?>
				<div id="<?php echo esc_attr( $key ); ?>" class="wsuwp-profile-panel">
					<?php wp_editor( $value, $args['meta_key'] ); ?>
				</div>
				<?php
			}
			?>

		</div><!--wsuwp-profile-tabs-->
		<?php
	}

	/**
	 * Add the meta boxes used for the Personnel content type.
	 *
	 * @param string $post_type The slug of the current post type.
	 */
	public function add_meta_boxes( $post_type ) {
		add_meta_box(
			'wsuwp_profile_additional_info',
			'Additional Profile Information',
			array( $this, 'display_additional_info_meta_box' ),
			$this->post_type_slug,
			'after_title',
			'high'
		);

		add_meta_box(
			'wsuwp_profile_photos',
			'Photos',
			array( $this, 'display_photo_meta_box' ),
			$this->post_type_slug,
			'normal',
			'high'
		);
	}

	/**
	 * Display a meta box used to show a person's "card".
	 *
	 * @param WP_Post $post Post object.
	 */
	public function display_position_info_meta_box( $post ) {

		wp_nonce_field( 'wsuwsp_profile', 'wsuwsp_profile_nonce' );

		$name_first = get_post_meta( $post->ID, '_wsuwp_profile_ad_name_first', true );
		$name_last = get_post_meta( $post->ID, '_wsuwp_profile_ad_name_last', true );
		$title = get_post_meta( $post->ID, '_wsuwp_profile_ad_title', true );
		$email = get_post_meta( $post->ID, '_wsuwp_profile_ad_email', true );
		$office = get_post_meta( $post->ID, '_wsuwp_profile_ad_office', true );
		$address = get_post_meta( $post->ID, '_wsuwp_profile_ad_address', true );
		$phone = get_post_meta( $post->ID, '_wsuwp_profile_ad_phone', true );
		$phone_ext = get_post_meta( $post->ID, '_wsuwp_profile_ad_phone_ext', true );
		$appointments = wp_get_post_terms( $post->ID, $this->taxonomy_slug_appointments, array( 'fields' => 'names' ) );
		$classifications = wp_get_post_terms( $post->ID, $this->taxonomy_slug_classifications, array( 'fields' => 'names' ) );

		?>
		<div class="profile-card">

			<div>
				<div>Given Name:</div>
				<div class="profile-card-data" id="_wsuwp_profile_ad_name_first"><?php echo esc_html( $name_first ); ?></div>
			</div>

			<div>
				<div>Surname:</div>
				<div class="profile-card-data" id="_wsuwp_profile_ad_name_last"><?php echo esc_html( $name_last ); ?></div>
			</div>

			<div>
				<div>Title:</div>
				<div class="profile-card-data" id="_wsuwp_profile_ad_title"><?php echo esc_html( $title ); ?></div>
			</div>

			<div>
				<div>Office:</div>
				<div class="profile-card-data" id="_wsuwp_profile_ad_office"><?php echo esc_html( $office ); ?></div>
			</div>

			<div>
				<div>Street Address:</div>
				<div class="profile-card-data" id="_wsuwp_profile_ad_address"><?php echo esc_html( $address ); ?></div>
			</div>

			<div>
				<div>Phone:</div>
				<div class="profile-card-data" id="_wsuwp_profile_ad_phone"><?php echo esc_html( $phone );
				if ( $phone_ext ) { echo ' ' . esc_html( $phone_ext ); } ?></div>
			</div>

			<div>
				<div>Email:</div>
				<div class="profile-card-data" id="_wsuwp_profile_ad_email"><?php echo esc_html( $email ); ?></div>
			</div>

			<?php if ( $appointments ) : ?>
				<div>
					<div>Appointment(s)</div>
					<div>
						<ul>
							<?php foreach ( $appointments as $appointment ) { echo '<li>' . esc_html( $appointment ) . '</li>'; } ?>
						</ul>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $classifications ) : ?>
				<div>
					<div>Classification</div>
					<div>
						<ul>
							<?php foreach ( $classifications as $classification ) { echo '<li>' . esc_html( $classification ) . '</li>'; } ?>
						</ul>
					</div>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( $name_first ) { ?>
		<p class="refresh-card">
			<span class="button" id="refresh-ad-data">Refresh</span>
			<span class="button profile-hide-button" id="undo-ad-data-refresh">Undo</span>
			<span class="button button-primary profile-hide-button" id="confirm-ad-data">Confirm</span>
			<input type="hidden" id="confirm-ad-hash" name="confirm_ad_hash" value="">
		</p>
		<?php } ?>
		<!--<p class="description">Notify <a href="#">HR</a> if any of this information is incorrect or needs updated.</p>-->
		<?php
	}

	/**
	 * Remove, move, and replace meta boxes as they are created and output.
	 *
	 * @param string  $post_type The current post type meta boxes are displayed for.
	 * @param string  $context   The context in which meta boxes are being output.
	 * @param WP_Post $post      The post object.
	 */
	public function do_meta_boxes( $post_type, $context, $post ) {
		if ( $this->post_type_slug !== $post_type ) {
			return;
		}

		$box_title = ( 'auto-draft' === $post->post_status ) ? 'Create Profile' : 'Update Profile';

		remove_meta_box( 'submitdiv', $this->post_type_slug, 'side' );
		add_meta_box( 'submitdiv', $box_title, array( $this, 'publish_meta_box' ), $this->post_type_slug, 'side', 'high' );

		add_meta_box(
			'wsuwp_profile_position_info',
			'Position and Contact Information',
			array( $this, 'display_position_info_meta_box' ),
			$this->post_type_slug,
			'side',
			'high'
		);

		// Remove "Appointment" and "Classification" meta boxes.
		remove_meta_box( 'appointmentdiv', $this->post_type_slug, 'side' );
		//remove_meta_box( 'classificationdiv', $this->post_type_slug, 'side' );
	}

	/**
	 * Replace the default post publishing meta box with our own that guides the user through
	 * a slightly different process for creating and saving a person.
	 *
	 * This was originally copied from WordPress core's `post_submit_meta_box()`.
	 *
	 * @param WP_Post $post The profile being edited/created.
	 */
	public function publish_meta_box( $post ) {
		$post_type = $post->post_type;
		$post_type_object = get_post_type_object( $post_type );
		$can_publish = current_user_can( $post_type_object->cap->publish_posts );

		$nid = get_post_meta( $post->ID, '_wsuwp_profile_ad_nid', true );

		$readonly = empty( trim( $nid ) ) ? '' : 'readonly';
		?>
		<div class="submitbox" id="submitpost">

			<div id="misc-publishing-actions">
				<div class="misc-pub-section">
					<label for="_wsuwp_profile_ad_nid">Network ID</label>:
					<input type="text" id="_wsuwp_profile_ad_nid" name="_wsuwp_profile_ad_nid" value="<?php echo esc_attr( $nid ); ?>" class="widefat" <?php echo esc_attr( $readonly ); ?> />

				<?php if ( '' === $readonly ) : ?>
					<div class="load-ad-container">
						<p class="description">Enter the WSU Network ID for this user to populate data from Active Directory.</p>
					</div>
				<?php else : ?>
					<div class="load-ad-container">
						<p class="description">The WSU Network ID used to populate this profile's data from Active Directory.</p>
					</div>
				<?php endif; ?>
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
						<span class="button" id="load-ad-data">Load</span>
						<span class="button button-primary profile-hide-button" id="confirm-ad-data">Confirm</span>
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
	 * Display a meta box to collect alternate contact information as well as additional working
	 * title and degree data for the profile.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function display_additional_info_meta_box( $post ) {
		?>
		<div class="wsuwp-profile-additional">
			<p>
				<label for="_wsuwp_profile_alt_office">Office</label><br />
				<input type="text" id="_wsuwp_profile_alt_office" name="_wsuwp_profile_alt_office" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_alt_office', true ) ); ?>" class="widefat" />
			</p>
			<p>
				<label for="_wsuwp_profile_alt_phone">Phone Number <span class="description">(xxx-xxx-xxxx)</span></label><br />
				<input type="text" id="_wsuwp_profile_alt_phone" name="_wsuwp_profile_alt_phone" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_alt_phone', true ) ); ?>" class="widefat" />
			</p>
			<p>
				<label for="_wsuwp_profile_alt_email">Email Address</label><br />
				<input type="text" id="_wsuwp_profile_alt_email" name="_wsuwp_profile_alt_email" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_alt_email', true ) ); ?>" class="widefat" />
			</p>
			<p>
				<label for="_wsuwp_profile_website">Website URL</label><br />
				<input type="text" id="_wsuwp_profile_website" name="_wsuwp_profile_website" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_website', true ) ); ?>" class="widefat" />
			</p>
		</div>
		<div class="wsuwp-profile-additional">
			<?php
			$titles = get_post_meta( $post->ID, '_wsuwp_profile_title', true );
			$degrees = get_post_meta( $post->ID, '_wsuwp_profile_degree', true );
			?>

			<script type="text/template" class="wsuwp-profile-repeatable-field-template">
				<p>
					<label>
						<span><%= label %></span>
						<input type="text" name="<%= name %>[]" value="" />
						<a class="wsuwp-profile-remove-repeatable-field">Remove</a>
					</label>
				</p>
			</script>

			<div class="wsuwp-profile-repeatable-field">
				<?php
				if ( $titles && is_array( $titles ) ) {
					foreach ( $titles as $title ) {
						?>
						<p>
							<label>
								<span>Working Title</span>
								<input type="text" name="_wsuwp_profile_title[]" value="<?php echo esc_attr( $title ); ?>" />
								<a class="wsuwp-profile-remove-repeatable-field">Remove</a>
							</label>
						</p>
						<?php
					}
				} else {
					?>
					<p>
						<label>
							<span>Working Title</span>
							<input type="text" name="_wsuwp_profile_title[]" value="" />
							<a class="wsuwp-profile-remove-repeatable-field">Remove</a>
						</label>
					</p>
					<?php
				}
				?>
				<p class="wsuwp-profile-add-repeatable">
					<a data-label="Working Title" data-name="_wsuwp_profile_title" href="#">+ Add another title</a>
				</p>
			</div>

			<div class="wsuwp-profile-repeatable-field">
				<?php
				if ( $degrees && is_array( $degrees ) ) {
					foreach ( $degrees as $degree ) {
						?>
						<p>
							<label>
								<span>Degree</span>
								<input type="text" name="_wsuwp_profile_degree[]" value="<?php echo esc_attr( $degree ); ?>" />
								<a class="wsuwp-profile-remove-repeatable-field">Remove</a>
							</label>
						</p>
						<?php
					}
				} else {
					?>
					<p>
						<label>
							<span>Degree</span>
							<input type="text" name="_wsuwp_profile_degree[]" value="" />
							<a class="wsuwp-profile-remove-repeatable-field">Remove</a>
						</label>
					</p>
					<?php
				}
				?>
				<p class="wsuwp-profile-add-repeatable">
					<a data-label="Degree" data-name="_wsuwp_profile_degree"  href="#">+ Add another degree</a>
				</p>
			</div>
		</div>
		<div class="clear"></div>
		<?php
	}

	/**
	 * Display a meta box used to show a person's photos.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function display_photo_meta_box( $post ) {
		wp_enqueue_media();

		$photos = get_post_meta( $post->ID, '_wsuwp_profile_photos', true );
		?>
		<div class="wsuwp-profile-photo-collection">

			<?php
			if ( $photos ) {
				foreach ( $photos as $photo_id ) {
					$photo = wp_prepare_attachment_for_js( $photo_id );
					?>
					<div class="wsuwp-profile-photo-wrapper">

						<img class="wsuwp-profile-photo"
							 src='<?php echo esc_url( $photo['sizes']['thumbnail']['url'] ); ?>'
							 width='<?php echo esc_attr( $photo['sizes']['thumbnail']['width'] ); ?>'
							 height='<?php echo esc_attr( $photo['sizes']['thumbnail']['height'] ); ?>'
							 title='<?php echo esc_attr( $photo['title'] ); ?>'
							 alt='<?php echo esc_attr( $photo['alt'] ); ?>'
							 data-url='<?php echo esc_attr( $photo['url'] ); ?>'
							 data-height='<?php echo esc_attr( $photo['height'] ); ?>'
							 data-width='<?php echo esc_attr( $photo['width'] ); ?>'
							 data-id="<?php echo esc_attr( $photo_id ); ?>" />

						<div class="wsuwp-profile-photo-controls">
							<button class="wsuwp-profile-photo-edit" aria-label="Edit">
								<span class="dashicons dashicons-edit"></span>
							</button>
							<button class="wsuwp-profile-photo-remove" aria-label="Remove">
								<span class="dashicons dashicons-no"></span>
							</button>
						</div>

						<input type="hidden"
							   class="wsuwp-profile-photo-id"
							   name="_wsuwp_profile_photos[]"
							   value="<?php echo esc_attr( $photo_id ); ?>" />

					</div>
					<?php
				}
			}
			?>

		</div>

		<input type="button" class="wsuwp-profile-add-photo button" value="Add Photo(s)" />

		<div class="wsuwp-profile-photo-controls-tooltip" role="presentation">
			<div class="wsuwp-profile-photo-controls-tooltip-arrow"></div>
			<div class="wsuwp-profile-photo-controls-tooltip-inner"></div>
		</div>

		<script type="text/template" id="photo-template">
			<div class="wsuwp-profile-photo-wrapper">
				<img class="wsuwp-profile-photo"
					 src="<%= src %>"
					 width="<%= width %>"
					 height="<%= height %>"
					 title="<%= title %>"
					 alt="<%= alt %>"
					 data-url="<%= url %>"
					 data-height="<%= full_height %>"
					 data-width="<%= full_width %>"
					 data-id="<%= id %>" />
				<div class="wsuwp-profile-photo-controls">
					<button class="wsuwp-profile-photo-edit" aria-label="Edit">
						<span class="dashicons dashicons-edit"></span>
					</button>
					<button class="wsuwp-profile-photo-remove" aria-label="Remove">
						<span class="dashicons dashicons-no"></span>
					</button>
				</div>
				<input type="hidden" class="wsuwp-profile-photo-id" name="_wsuwp_profile_photos[]" value="<%= id %>" />
			</div>
		</script>
		<?php
	}

	/**
	 * Sanitizes repeatable text fields.
	 *
	 * @since 0.3.0
	 *
	 * @param array $values
	 *
	 * @return array
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
	 * Save data associated with a profile.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return mixed
	 */
	public function save_post( $post_id ) {

		if ( ! isset( $_POST['wsuwsp_profile_nonce'] ) || ! wp_verify_nonce( $_POST['wsuwsp_profile_nonce'], 'wsuwsp_profile' ) ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// Save "last_name first_name" data (for alpha sorting purposes).
		if ( ( isset( $_POST['_wsuwp_profile_ad_name_last'] ) && '' !== $_POST['_wsuwp_profile_ad_name_last'] ) &&
				 ( isset( $_POST['_wsuwp_profile_ad_name_first'] ) && '' !== $_POST['_wsuwp_profile_ad_name_first'] ) ) {
			update_post_meta( $post_id, '_wsuwp_profile_name', sanitize_text_field( $_POST['_wsuwp_profile_ad_name_last'] ) . ' ' . sanitize_text_field( $_POST['_wsuwp_profile_ad_name_first'] ) );
		}

		$keys = get_registered_meta_keys( 'post' );

		foreach ( $this->post_meta_keys as $key => $args ) {
			if ( isset( $_POST[ $args['meta_key'] ] ) && isset( $keys[ $key ] ) && isset( $keys[ $key ]['sanitize_callback'] ) ) {
				// Each piece of meta is registered with sanitization.
				update_post_meta( $post_id, $args['meta_key'], $_POST[ $args['meta_key'] ] );
			}
		}
	}

	/**
	 * Keys of meta fields to revision.
	 *
	 * @param array $keys Meta keys to track revisions for.
	 *
	 * @return array
	 */
	public function add_meta_keys_to_revision( $keys ) {
		foreach ( $this->post_meta_keys as $key => $args ) {
			$keys[] = $args['meta_key'];
		}

		return $keys;
	}

	/**
	 * Taxonomy columns on the "All Profiles" screen.
	 *
	 * @param array $columns Default columns on the "All Profiles" screen.
	 *
	 * @return array
	 */
	public function wsuwp_people_profile_columns( $columns ) {
		$columns[] = 'wsuwp_university_org';
		$columns[] = 'wsuwp_university_location';

		return $columns;
	}

	/**
	 * Registers the wsu_nid parameter.
	 *
	 * @since 0.2.2
	 */
	public function register_wsu_nid_query_var() {
		global $wp;
		$wp->add_query_var( 'wsu_nid' );
	}

	/**
	 * Retrieves a passed wsu_nid with a REST request and adds to the query vars.
	 *
	 * @since 0.2.2
	 *
	 * @param array           $valid_vars
	 * @param WP_REST_Request $request
	 *
	 * @return array
	 */
	public function rest_query_vars( $valid_vars, $request ) {
		$valid_vars['wsu_nid'] = $request->get_param( 'wsu_nid' );

		return $valid_vars;
	}

	/**
	 * Sets a meta query for WSU NID when the wsu_nid parameters is included as
	 * part of a query.
	 *
	 * @since 0.2.2
	 *
	 * @param WP_Query $query
	 */
	public function handle_wsu_nid_query_var( $query ) {
		if ( isset( $query->query['wsu_nid'] ) && $query->query['wsu_nid'] ) {
			$query->set( 'meta_key', '_wsuwp_profile_ad_nid' );
			$query->set( 'meta_value', sanitize_text_field( $query->query['wsu_nid'] ) );
		}
	}

	/**
	 * Register the custom meta fields attached to a REST API response containing profile data.
	 *
	 * @since 0.2.0
	 */
	public function register_api_fields() {
		$args = array(
			'get_callback' => array( $this, 'get_api_meta_data' ),
			'update_callback' => null,
			'schema' => null,
		);
		foreach ( $this->post_meta_keys as $field_name => $value ) {
			register_rest_field( $this->post_type_slug, $field_name, $args );
		}
	}

	/**
	 * Return the value of a post meta field sanitized against a whitelist with the provided method.
	 *
	 * @since 0.2.0
	 *
	 * @param array           $object     The current post being processed.
	 * @param string          $field_name Name of the field being retrieved.
	 * @param WP_Rest_Request $request    The full current REST request.
	 *
	 * @return mixed Meta data associated with the post and field name.
	 */
	public function get_api_meta_data( $object, $field_name, $request ) {
		if ( ! array_key_exists( $field_name, $this->post_meta_keys ) ) {
			return '';
		}

		if ( 'sanitize_text_field' === $this->post_meta_keys[ $field_name ]['sanitize_callback'] ) {
			return esc_html( get_post_meta( $object['id'], $this->post_meta_keys[ $field_name ]['meta_key'], true ) );
		}

		if ( 'WSUWP_People_Directory::sanitize_repeatable_text_fields' === $this->post_meta_keys[ $field_name ]['sanitize_callback'] ) {
			$data = get_post_meta( $object['id'], $this->post_meta_keys[ $field_name ]['meta_key'], true );
			if ( is_array( $data ) ) {
				$data = array_map( 'esc_html', $data );
			} else {
				$data = array();
			}

			return $data;
		}

		if ( 'esc_url_raw' === $this->post_meta_keys[ $field_name ]['sanitize_callback'] ) {
			return esc_url( get_post_meta( $object['id'], $this->post_meta_keys[ $field_name ]['meta_key'], true ) );
		}

		if ( 'wp_kses_post' === $this->post_meta_keys[ $field_name ]['sanitize_callback'] ) {
			$data = get_post_meta( $object['id'], $this->post_meta_keys[ $field_name ]['meta_key'], true );
			$data = apply_filters( 'the_content', $data );
			return wp_kses_post( $data );
		}

		if ( 'cv_attachment' === $field_name ) {
			$cv_id = get_post_meta( $object['id'], $this->post_meta_keys[ $field_name ]['meta_key'], true );
			$cv_url = wp_get_attachment_url( $cv_id );

			if ( $cv_url ) {
				return esc_url( $cv_url );
			} else {
				return false;
			}
		}

		if ( 'profile_photo' === $field_name ) {
			$thumbnail_id = get_post_thumbnail_id( $object['id'] );
			if ( $thumbnail_id ) {
				$thumbnail = wp_get_attachment_image_src( $thumbnail_id );
				if ( $thumbnail ) {
					return esc_url( $thumbnail[0] );
				} else {
					return false;
				}
			}
		}

		return '';
	}

	/**
	 * Capability modifications.
	 *
	 * @param array $allcaps All the user's capabilities
	 * @param array $caps    [0] Required capability
	 * @param array $args    [0] Requested capability
	 *                       [1] User ID
	 *                       [2] Associated object ID
	 *
	 * @return array $allcaps
	 */
	public function user_has_cap( $allcaps, $cap, $args ) {
		if ( empty( $allcaps ) ) {
			return $allcaps;
		}

		// Bail out if we're not asking about a post:
		if ( 'edit_post' !== $args[0] ) {
			return $allcaps;
		}

		// Bail for users who can already edit others posts:
		if ( $allcaps['edit_others_posts'] ) {
			return $allcaps;
		}

		// Bail for users who can't publish posts:
		if ( ! isset( $allcaps['publish_posts'] ) || ! $allcaps['publish_posts'] ) {
			return $allcaps;
		}

		// Load the post data:
		$post = get_post( $args[2] );

		// Bail if the post type isn't Personnel:
		if ( $this->post_type_slug !== $post->post_type ) {
			return $allcaps;
		}

		// Bail if the user is the post author:
		if ( $args[1] === $post->post_author ) {
			return $allcaps;
		}

		// Bail if the post isn't published:
		if ( 'publish' !== $post->post_status ) {
			return $allcaps;
		}

		// Bail if the user isn't an Organization Administrator:
		$dept = get_post_meta( $post->ID, '_wsuwp_profile_dept', true );
		$org_admin = get_user_meta( $args[1], 'wsuwp_people_organization_admin', true );
		if ( $org_admin !== $dept ) {
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
	 * Remove some items from the admin menu (should probably change permissions too).
	 */
	public function admin_menu() {

		if ( ! current_user_can( 'manage_options' ) ) {
			remove_menu_page( 'profile.php' ); // Profile
			remove_menu_page( 'edit-comments.php' ); // Comments
			remove_menu_page( 'tools.php' ); // Tools
			remove_menu_page( 'edit.php' ); // Posts
			remove_menu_page( 'upload.php' ); // Media Library
			remove_menu_page( 'edit.php?post_type=page' ); // Pages
			remove_submenu_page( 'edit.php?post_type=wsuwp_people_profile', 'post-new.php?post_type=wsuwp_people_profile' ); // Personnel -> Add New
		}

	}

	/**
	 * Remove the "Add New" menu from the toolbar.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar
	 */
	public function admin_bar_menu( $wp_admin_bar ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			$wp_admin_bar->remove_node( 'new-content' );
		}

	}

	/**
	 * Modify dashboard widgets.
	 */
	public function wp_dashboard_setup() {

		if ( ! current_user_can( 'manage_options' ) ) {

			remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
			remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
			remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
			remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );

			wp_add_dashboard_widget(
				'wsuwp_directory_dashboard_widget',
				'Welcome to the WSU Personnel Directory',
				array( $this, 'wsuwp_directory_dashboard_widget' )
			);

		}

	}

	/**
	 * Contents for the dashboard widget.
	 */
	public function wsuwp_directory_dashboard_widget() {
		echo '<p>Click the "Profiles" button to the left to get started.</p>';
		echo '<p>This is just a placeholder. This space will contain more information and documentation about the directory.</p>';
	}

	/**
	 * Show (non-Admin) users only the media they have uploaded.
	 * Theoretically, they have no need to see the rest.
	 * This doesn't change the counts on the Media Library page.
	 *
	 * @param WP_Query $query
	 */
	public function limit_media_library( $query ) {
		if ( is_admin() && isset( $_REQUEST['action'] ) ) {
			if ( ! get_current_screen() || ( 'upload' !== get_current_screen()->base && 'query-attachments' !== $_REQUEST['action'] ) ) {
				return;
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				$query->set( 'author', wp_get_current_user()->ID );
			}
		}
	}

	/**
	 * Modify "All Profiles" views for all but administrators.
	 * Probably not the most scalable.
	 *
	 * @param array $views Default table views.
	 *
	 * @return array
	 */
	public function edit_views( $views ) {

		if ( ! current_user_can( 'manage_options' ) ) {

			unset( $views['all'] );
			unset( $views['publish'] );
			unset( $views['draft'] );
			unset( $views['pending'] );
			unset( $views['trash'] );

			$current_user = wp_get_current_user();

			$all_personnel = new WP_Query( array(
				'post_type'	 => $this->post_type_slug,
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'author__not_in' => $current_user->ID,
			) );

			$posts = $all_personnel->get_posts();

			foreach ( $posts as $post ) {
				if ( current_user_can( 'edit_post', $post->ID ) ) {
					$users_editable_profiles[] = $post->ID;
				}
			}

			if ( $users_editable_profiles ) {
				$profile_ids = implode( ',', $users_editable_profiles );
				$count = count( $users_editable_profiles );
				$class = ( 'last_name' === $_GET['sortby'] ) ? ' class="current"' : '';
				$url = admin_url( 'edit.php?post_type=' . $this->post_type_slug . '&post_status=publish&sortby=last_name&profiles=' . $profile_ids );
				$views['others'] = sprintf( __( '<a href="%1$s"%2$s>Others <span class="count">(%3$d)</span></a>' ), $url, $class, $count );
			}
		}

		return $views;
	}

	/**
	 * Query parsing for "Others" view on "All Profiles" page.
	 *
	 * @param mixed $query Array or string of Query parameters.
	 */
	public function parse_query( $query ) {

		$screen = get_current_screen();

		if ( is_admin() && 'edit-' . $this->post_type_slug === $screen->id &&
				isset( $_GET['post_type'] ) && $_GET['post_type'] === $this->post_type_slug &&
				isset( $_GET['sortby'] ) && 'last_name' === $_GET['sortby'] &&
				isset( $_GET['profiles'] ) && '' !== $_GET['profiles'] ) {
			$editables = explode( ',', $_GET['profiles'] );
			set_query_var( 'meta_key', '_wsuwp_profile_ad_name_last' );
			set_query_var( 'orderby', 'meta_value' );
			// These two seem to prevent the "'views_'.$this->screen->id" hook from working properly.
			//set_query_var( 'order', 'ASC' );
			//set_query_var( 'post__in', $editables );
		}
	}

	/**
	 * Order public Personnel query results alphabetically by last name.
	 *
	 * @param WP_Query $query
	 */
	public function profile_archives( $query ) {
		if ( ( $query->is_post_type_archive( $this->post_type_slug ) || is_tax() || is_category() || is_tag() ) && $query->is_main_query() && ! is_admin() ) {
			$query->set( 'order', 'ASC' );
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'meta_key', '_wsuwp_profile_ad_name_last' );
		}
	}

	/**
	 * Given a WSU Network ID, retrieve information from active directory about
	 * a user.
	 *
	 * @param string $nid The user's network ID.
	 *
	 * @return array List of predefined information we'll expect on the other side.
	 */
	private function get_nid_data( $nid ) {
		if ( false === function_exists( 'wsuwp_get_wsu_ad_by_login' ) ) {
			return array();
		}

		// Get data from the WSUWP SSO Authentication plugin.
		$nid_data = wsuwp_get_wsu_ad_by_login( $nid );

		$return_data = array(
			'given_name' => '',
			'surname' => '',
			'title' => '',
			'office' => '',
			'street_address' => '',
			'telephone_number' => '',
			'email' => '',
			'confirm_ad_hash' => '',
		);

		if ( isset( $nid_data['givenname'][0] ) ) {
			$return_data['given_name'] = sanitize_text_field( $nid_data['givenname'][0] );
		}

		if ( isset( $nid_data['sn'][0] ) ) {
			$return_data['surname'] = sanitize_text_field( $nid_data['sn'][0] );
		}

		if ( isset( $nid_data['title'][0] ) ) {
			$return_data['title'] = sanitize_text_field( $nid_data['title'][0] );
		}

		if ( isset( $nid_data['physicaldeliveryofficename'][0] ) ) {
			$return_data['office'] = sanitize_text_field( $nid_data['physicaldeliveryofficename'][0] );
		}

		if ( isset( $nid_data['streetaddress'][0] ) ) {
			$return_data['street_address'] = sanitize_text_field( $nid_data['streetaddress'][0] );
		}

		if ( isset( $nid_data['telephonenumber'][0] ) ) {
			$return_data['telephone_number'] = sanitize_text_field( $nid_data['telephonenumber'][0] );
		}

		if ( isset( $nid_data['mail'][0] ) ) {
			$return_data['email'] = sanitize_text_field( $nid_data['mail'][0] );
		}

		$hash = md5( serialize( $return_data ) );
		$return_data['confirm_ad_hash'] = $hash;

		return $return_data;
	}

	/**
	 * Process an ajax request for AD information attached to a network ID. We'll return
	 * the data here for confirmation. Confirmation will be handled elsewhere.
	 */
	public function ajax_get_data_by_nid() {
		check_ajax_referer( 'wsu-people-nid-lookup' );

		$nid = sanitize_text_field( $_POST['network_id'] );

		if ( empty( $nid ) ) {
			wp_send_json_error( 'Invalid or empty Network ID' );
		}

		$return_data = $this->get_nid_data( $nid );

		wp_send_json_success( $return_data );
	}

	/**
	 * Process an ajax request to confirm the AD information attached to a network ID. At
	 * this point we'll do the lookup again and save the information to the current profile.
	 */
	public function ajax_confirm_nid_data() {
		check_ajax_referer( 'wsu-people-nid-lookup' );

		$nid = sanitize_text_field( $_POST['network_id'] );

		if ( empty( $nid ) ) {
			wp_send_json_error( 'Invalid or empty Network ID' );
		}

		// Data is sanitized before return.
		$confirm_data = $this->get_nid_data( $nid );

		if ( $confirm_data['confirm_ad_hash'] !== $_POST['confirm_ad_hash'] ) {
			wp_send_json_error( 'Previously retrieved data does not match the data attached to this network ID.' );
		}

		if ( empty( absint( $_POST['post_id'] ) ) ) {
			wp_send_json_error( 'Invalid profile post ID.' );
		}

		$post_id = $_POST['post_id'];

		update_post_meta( $post_id, '_wsuwp_profile_ad_nid', $nid );
		update_post_meta( $post_id, '_wsuwp_profile_ad_name_first', $confirm_data['given_name'] );
		update_post_meta( $post_id, '_wsuwp_profile_ad_name_last', $confirm_data['surname'] );
		update_post_meta( $post_id, '_wsuwp_profile_ad_title', $confirm_data['title'] );
		update_post_meta( $post_id, '_wsuwp_profile_ad_office', $confirm_data['office'] );
		update_post_meta( $post_id, '_wsuwp_profile_ad_address', $confirm_data['street_address'] );
		update_post_meta( $post_id, '_wsuwp_profile_ad_phone', $confirm_data['telephone_number'] );
		update_post_meta( $post_id, '_wsuwp_profile_ad_email', $confirm_data['email'] );

		wp_send_json_success( 'Updated' );
	}
}
