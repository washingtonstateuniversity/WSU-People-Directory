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

		add_filter( 'enter_title_here', array( $this, 'enter_title_here' ) );
		add_action( 'edit_form_after_title', array( $this, 'edit_form_after_title' ) );
		add_action( 'edit_form_after_editor', array( $this, 'edit_form_after_editor' ) );

		add_action( 'add_meta_boxes_' . self::$post_type_slug, array( $this, 'add_meta_boxes' ) );
		add_action( 'do_meta_boxes', array( $this, 'do_meta_boxes' ), 10, 3 );

		add_filter( 'wp_insert_post_data', array( $this, 'wp_insert_post_data' ) );
		add_action( 'save_post_' . self::$post_type_slug, array( $this, 'save_post' ) );

		add_action( 'wp_ajax_wsu_people_get_data_by_nid', array( $this, 'ajax_get_data_by_nid' ) );
		add_action( 'wp_ajax_wsu_people_confirm_nid_data', array( $this, 'ajax_confirm_nid_data' ) );

		add_filter( 'wp_post_revision_meta_keys', array( $this, 'add_meta_keys_to_revision' ) );

		add_filter( 'manage_taxonomies_for_' . self::$post_type_slug . '_columns', array( $this, 'manage_people_taxonomy_columns' ) );
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

		if ( 'post-new.php' === $hook_suffix || 'post.php' === $hook_suffix ) {
			$post = get_post();
			$profile_vars = array(
				'nid_nonce' => wp_create_nonce( 'wsu-people-nid-lookup' ),
				'post_id' => $post->ID,
				'request_from' => ( apply_filters( 'wsuwp_people_display', true ) ) ? 'rest' : 'ad',
				'rest_url' => WSUWP_People_Directory::REST_URL(),
			);

			// Make a REST request for data from people.wsu.edu when editing a person.
			if ( 'post.php' === $hook_suffix && apply_filters( 'wsuwp_people_display', true ) ) {
				$profile_vars['make_request'] = true;
			}

			wp_enqueue_style( 'wsuwp-people-admin', plugins_url( 'css/admin-person.css', dirname( __FILE__ ) ), array(), WSUWP_People_Directory::$version );
			wp_enqueue_script( 'wsuwp-people-admin', plugins_url( 'js/admin-person.min.js', dirname( __FILE__ ) ), array( 'jquery-ui-tabs', 'underscore' ), WSUWP_People_Directory::$version, true );
			wp_localize_script( 'wsuwp-people-admin', 'wsupeople', $profile_vars );
		}

		// Disable autosaving on new people posts if this isn't people.wsu.edu.
		if ( 'post-new.php' === $hook_suffix && apply_filters( 'wsuwp_people_display', true ) ) {
			wp_dequeue_script( 'autosave' );
		}

		if ( 'edit.php' === $hook_suffix ) {
			wp_enqueue_style( 'wsuwp-people-admin', plugins_url( 'css/admin-people.css', dirname( __FILE__ ) ), array(), WSUWP_People_Directory::$version );
			wp_enqueue_script( 'wsuwp-people-admin', plugins_url( 'js/admin-people.min.js', dirname( __FILE__ ) ), array( 'jquery' ), WSUWP_People_Directory::$version );
		}

	}

	/**
	 * Change the "Enter title here" text for the people content type.
	 *
	 * @since 0.1.0
	 *
	 * @param string $title The placeholder text displayed in the title input field.
	 *
	 * @return string
	 */
	public function enter_title_here( $title ) {
		$screen = get_current_screen();

		if ( self::$post_type_slug === $screen->post_type ) {
			$title = 'Enter name here';
		}

		return $title;
	}

	/**
	 * Add tabs for each biography above the default editor.
	 *
	 * @since 0.1.0
	 *
	 * @since ?
	 *
	 * @param WP_Post $post Post object.
	 */
	public function edit_form_after_title( $post ) {
		if ( self::$post_type_slug !== $post->post_type ) {
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
				foreach ( self::$post_meta_keys as $key => $args ) {
					if ( ! isset( $args['render_as_wp_editor'] ) ) {
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
	 * Add panels for each biography below the default editor.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_Post $post Post object.
	 */
	public function edit_form_after_editor( $post ) {
		if ( self::$post_type_slug !== $post->post_type ) {
			return;
		}
		?>
			</div><!--wsuwp-profile-default-->

			<?php
			foreach ( self::$post_meta_keys as $key => $args ) {
				if ( ! isset( $args['render_as_wp_editor'] ) ) {
					continue;
				}

				$value = get_post_meta( $post->ID, $args['meta_key'], true );
				?>
				<div id="<?php echo esc_attr( $key ); ?>" class="wsuwp-profile-panel">
					<?php
					if ( '_wsuwp_profile_bio_university' === $args['meta_key'] && ! current_user_can( 'create_sites' ) ) {
						echo wp_kses_post( apply_filters( 'the_content', $value ) );
					} else {
						wp_editor( $value, $args['meta_key'] );
					}
					?>
				</div>
				<?php
			}
			?>

		</div><!--wsuwp-profile-tabs-->
		<?php
	}

	/**
	 * Add the meta boxes used for capturing information about a person.
	 *
	 * @since 0.1.0
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'wsuwp_profile_additional_info',
			'Additional Profile Information',
			array( $this, 'display_additional_info_meta_box' ),
			self::$post_type_slug,
			'after_title',
			'high'
		);

		add_meta_box(
			'wsuwp_profile_photos',
			'Photos',
			array( $this, 'display_photo_meta_box' ),
			self::$post_type_slug,
			'normal',
			'high'
		);

		if ( ! apply_filters( 'wsuwp_people_display', true ) ) {
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
	 * Display a meta box used to show a person's "card".
	 *
	 * @since 0.1.0
	 *
	 * @param WP_Post $post Post object.
	 */
	public function display_position_info_meta_box( $post ) {

		wp_nonce_field( 'wsuwsp_profile', 'wsuwsp_profile_nonce' );

		$nid = get_post_meta( $post->ID, '_wsuwp_profile_ad_nid', true );
		$name_first = get_post_meta( $post->ID, '_wsuwp_profile_ad_name_first', true );
		$name_last = get_post_meta( $post->ID, '_wsuwp_profile_ad_name_last', true );
		$title = get_post_meta( $post->ID, '_wsuwp_profile_ad_title', true );
		$email = get_post_meta( $post->ID, '_wsuwp_profile_ad_email', true );
		$office = get_post_meta( $post->ID, '_wsuwp_profile_ad_office', true );
		$address = get_post_meta( $post->ID, '_wsuwp_profile_ad_address', true );
		$phone = get_post_meta( $post->ID, '_wsuwp_profile_ad_phone', true );
		$phone_ext = get_post_meta( $post->ID, '_wsuwp_profile_ad_phone_ext', true );

		$classifications = wp_get_post_terms( $post->ID, WSUWP_People_Classification_Taxonomy::$taxonomy_slug, array(
			'fields' => 'names',
		) );

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

		<?php if ( ! apply_filters( 'wsuwp_people_display', true ) && $nid ) { ?>
		<p class="refresh-card">
			<span class="spinner"></span>
			<button class="button" id="refresh-ad-data">Refresh</button>
			<button class="button profile-hide-button" id="undo-ad-data-refresh">Undo</button>
			<button class="button button-primary profile-hide-button refresh" id="confirm-ad-data">Confirm</button>
			<input type="hidden" id="confirm-ad-hash" name="confirm_ad_hash" value="">
		</p>
		<?php } ?>
		<!--<p class="description">Notify <a href="#">HR</a> if any of this information is incorrect or needs updated.</p>-->
		<?php
	}

	/**
	 * Remove, move, and replace meta boxes as they are created and output.
	 *
	 * @since 0.1.0
	 *
	 * @param string  $post_type The current post type meta boxes are displayed for.
	 * @param string  $context   The context in which meta boxes are being output.
	 * @param WP_Post $post      The post object.
	 */
	public function do_meta_boxes( $post_type, $context, $post ) {
		if ( self::$post_type_slug !== $post_type ) {
			return;
		}

		$box_title = ( 'auto-draft' === $post->post_status ) ? 'Create Profile' : 'Update Profile';

		remove_meta_box( 'submitdiv', self::$post_type_slug, 'side' );
		add_meta_box( 'submitdiv', $box_title, array( $this, 'publish_meta_box' ), self::$post_type_slug, 'side', 'high' );

		add_meta_box(
			'wsuwp_profile_position_info',
			'Position and Contact Information',
			array( $this, 'display_position_info_meta_box' ),
			self::$post_type_slug,
			'side',
			'high'
		);
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
	 * Display a meta box to collect alternate contact information,
	 * working titles and degrees for a person.
	 *
	 * @since 0.1.0
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
						<input type="text" name="<%= name %>[]" value="<%= value %>" />
						<a class="wsuwp-profile-remove-repeatable-field">Remove</a>
					</label>
				</p>
			</script>

			<div class="wsuwp-profile-repeatable-field wsuwp-profile-titles">
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

			<div class="wsuwp-profile-repeatable-field wsuwp-profile-degrees">
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
					<a data-label="Degree" data-name="_wsuwp_profile_degree" href="#">+ Add another degree</a>
				</p>
			</div>
		</div>
		<div class="clear"></div>
		<?php
	}

	/**
	 * Display a meta box used to show a person's photos.
	 *
	 * @since 0.3.0
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
							<button type="button" class="wsuwp-profile-photo-remove" aria-label="Remove">
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

		<?php
		if ( apply_filters( 'wsuwp_people_display', true ) ) {
			$index_used = get_post_meta( $post->ID, '_use_photo', true );
			?>
			<input type="hidden" class="use-photo" name="_use_photo" value="<?php echo esc_attr( $index_used ); ?>" />
			<?php
		}
		?>

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
					<?php if ( apply_filters( 'wsuwp_people_display', true ) ) { ?>
					<button type="button" class="wsuwp-profile-photo-select" aria-label="Select">
						<span class="dashicons dashicons-yes"></span>
					</button>
					<?php } ?>
					<button type="button" class="wsuwp-profile-photo-remove" aria-label="Remove">
						<span class="dashicons dashicons-no"></span>
					</button>
				</div>
				<input type="hidden" class="wsuwp-profile-photo-id" name="_wsuwp_profile_photos[]" value="<%= id %>" />

			</div>
		</script>
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
	 * Save post content only if this is people.wsu.edu.
	 *
	 * @since 0.3.0
	 *
	 * @param array $data An array of slashed post data.
	 *
	 * @return array
	 */
	public function wp_insert_post_data( $data ) {
		if ( apply_filters( 'wsuwp_people_display', true ) && self::$post_type_slug === $data['post_type'] ) {
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
		if ( ! isset( $_POST['wsuwsp_profile_nonce'] ) || ! wp_verify_nonce( $_POST['wsuwsp_profile_nonce'], 'wsuwsp_profile' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Store only select meta if this isn't people.wsu.edu.
		if ( apply_filters( 'wsuwp_people_display', true ) ) {
			if ( isset( $_POST['_use_photo'] ) && '' !== $_POST['_use_photo'] ) {
				update_post_meta( $post_id, '_use_photo', absint( $_POST['_use_photo'] ) );
			} else {
				delete_post_meta( $post_id, '_use_photo' );
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
	 * Given a WSU Network ID, retrieve information about a person from people.wsu.edu.
	 *
	 * @since 0.3.0
	 *
	 * @param string $nid The user's network ID.
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
	 * Given a WSU Network ID, retrieve information from active directory about
	 * a user.
	 *
	 * @since 0.1.0
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

		$hash = md5( wp_json_encode( $return_data ) );
		$return_data['confirm_ad_hash'] = $hash;

		return $return_data;
	}

	/**
	 * Process an ajax request for information attached to a network ID. We'll return
	 * the data here for confirmation. Confirmation will be handled elsewhere.
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

		// Try to retrieve a person from people.wsu.edu first.
		// We do this in here so the above check for existing profiles can be performed.
		if ( 'rest' === $_POST['request_from'] ) {
			$return_data = self::get_rest_data( $nid );
		}

		if ( ! $return_data || 'ad' === $_POST['request_from'] ) {
			$return_data = $this->get_nid_data( $nid );
		}

		wp_send_json_success( $return_data );
	}

	/**
	 * Process an ajax request to confirm the AD information attached to a network ID. At
	 * this point we'll do the lookup again and save the information to the current profile.
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
		$confirm_data = $this->get_nid_data( $nid );

		if ( 'ad' === $_POST['request_from'] && $confirm_data['confirm_ad_hash'] !== $_POST['confirm_ad_hash'] ) {
			wp_send_json_error( 'Previously retrieved data does not match the data attached to this network ID.' );
		}

		if ( empty( absint( $_POST['post_id'] ) ) ) {
			wp_send_json_error( 'Invalid profile post ID.' );
		}

		$post_id = $_POST['post_id'];

		update_post_meta( $post_id, '_wsuwp_profile_ad_nid', $nid );

		// Only save this meta on people.wsu.edu
		if ( ! apply_filters( 'wsuwp_people_display', true ) ) {
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
}
