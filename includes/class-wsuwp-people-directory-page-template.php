<?php

class WSUWP_People_Directory_Page_Template {
	/**
	 * @var WSUWP_People_Directory_Page_Template
	 *
	 * @since 0.3.0
	 */
	private static $instance;

	/**
	 * The people directory page template slug and name.
	 *
	 * @since 0.3.0
	 *
	 * @var array
	 */
	public static $template = array(
		'templates/people.php' => 'People Directory',
	);

	/**
	 * A list of post meta keys associated with a directory page.
	 *
	 * @since 0.3.0
	 *
	 * @var array
	 */
	public $post_meta_keys = array(
		'_wsu_people_directory_profile_ids' => array(
			'type' => 'string',
			'description' => 'IDs of people records to display on this page',
			'sanitize_callback' => 'sanitize_text_field',
		),
		'_wsu_people_directory_layout' => array(
			'type' => 'string',
			'description' => 'The layout to use for the directory display',
			'sanitize_callback' => 'sanitize_text_field',
		),
		'_wsu_people_directory_about' => array(
			'type' => 'string',
			'description' => 'The biographical or other information to show for people on this listing',
			'sanitize_callback' => 'sanitize_text_field',
		),
		'_wsu_people_directory_link' => array(
			'type' => 'string',
			'description' => 'Link to full profiles',
			'sanitize_callback' => 'sanitize_text_field',
		),
		'_wsu_people_directory_profile' => array(
			'type' => 'string',
			'description' => 'Whether full profiles should open as a page or a lightbox',
			'sanitize_callback' => 'sanitize_text_field',
		),
		'_wsu_people_directory_show_photos' => array(
			'type' => 'boolean',
			'description' => 'Whether to show photos on this people listing',
			'sanitize_callback' => 'sanitize_text_field',
		),
		'_wsu_people_directory_filters' => array(
			'type' => 'array',
			'description' => 'Various methods by which to filter a directory',
			'sanitize_callback' => 'WSUWP_People_Directory_Page_Template::sanitize_filter_checkboxes',
		),
	);

	/**
	 * Maintain and return the one instance. Initiate hooks when called the first time.
	 *
	 * @since 0.3.0
	 *
	 * @return \WSUWP_People_Directory_Page_Template
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSUWP_People_Directory_Page_Template();
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
		add_action( 'init', array( $this, 'register_meta' ), 11 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'add_meta_boxes_page', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_page', array( $this, 'save_post' ), 10, 2 );
		add_action( 'wsuwp_people_add_or_update_profiles', array( $this, 'add_or_update_profiles' ), 10, 2 );
		add_action( 'wsuwp_people_remove_profile_from_page', array( $this, 'remove_profile_from_page' ), 10, 2 );
		add_action( 'wp_ajax_person_details', array( $this, 'person_details' ) );
		add_action( 'wp_ajax_check_inserted_profiles', array( $this, 'check_inserted_profiles' ) );

		add_filter( 'theme_page_templates', array( $this, 'add_directory_template' ) );
		add_filter( 'template_include', array( $this, 'template_include' ) );
		add_action( 'wp_footer', array( $this, 'person_lightbox_template' ) );
	}

	/**
	 * Register the meta keys used to store data about a directory page.
	 *
	 * @since 0.3.0
	 */
	public function register_meta() {
		foreach ( $this->post_meta_keys as $key => $args ) {
			$args['single'] = true;

			register_meta( 'post', $key, $args );
		}
	}

	/**
	 * Enqueue the scripts and styles used in the admin.
	 *
	 * @since 0.3.0
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {
		global $post;
		$screen = get_current_screen();

		if ( 'page' !== $screen->post_type || ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		wp_enqueue_style( 'wsuwp-people-display', plugins_url( 'css/people.css', dirname( __FILE__ ) ), array(), WSUWP_People_Directory::$version );
		wp_enqueue_style( 'wsuwp-people-admin', plugins_url( 'css/admin-page.css', dirname( __FILE__ ) ), array(), WSUWP_People_Directory::$version );
		wp_enqueue_script( 'wsuwp-people-edit-page', plugins_url( 'js/admin-edit-page.min.js', dirname( __FILE__ ) ), array( 'jquery', 'underscore', 'jquery-ui-autocomplete', 'jquery-ui-sortable' ), WSUWP_People_Directory::$version, true );
		wp_enqueue_script( 'wsuwp-people-sync', plugins_url( 'js/admin-people-sync.min.js', dirname( __FILE__ ) ), array( 'jquery' ), WSUWP_People_Directory::$version, true );

		wp_localize_script( 'wsuwp-people-edit-page', 'wsuwp_people_edit_page', array(
			'rest_route' => WSUWP_People_Directory::REST_route(),
			'rest_url' => WSUWP_People_Directory::REST_URL(),
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'person-details' ),
			'page_id' => $post->ID,
		) );
		wp_localize_script( 'wsuwp-people-sync', 'wsupeoplesync', array(
			'nonce' => WSUWP_People_Directory::create_rest_nonce(),
			'uid' => wp_get_current_user()->ID,
			'site_url' => get_home_url(),
		) );
	}

	/**
	 * Add the meta boxes used for capturing directory page data.
	 *
	 * @since 0.3.0
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'wsuwp-people-directory-configuration',
			'People Directory Setup',
			array( $this, 'display_people_directory_setup' ),
			'page',
			'normal',
			'high'
		);
	}

	/**
	 * Display a meta box used to configure a directory page.
	 *
	 * @since 0.3.0
	 *
	 * @param WP_Post $post Post object.
	 */
	public function display_people_directory_setup( $post ) {
		wp_nonce_field( 'directory-page-configuration', 'directory_page_nonce' );

		$directory_data = $this->directory_data( $post->ID );
		?>
		<div class="wsu-people-directory-options add<?php if ( 'raw' === $post->filter ) { echo esc_attr( ' open' ); } ?>">

			<button type="button" class="wsu-people-directory-options-header">Add People</button>

			<div>

				<p>
					<label for="wsu-people-import-organization">Organization</label>
					<input type="text" id="wsu-people-import-organization" value="" autocomplete="off" />
				</p>

				<p>
					<label for="wsu-people-import-location">Location</label>
					<input type="text" id="wsu-people-import-location" value="" autocomplete="off" />
				</p>

				<p>
					<label for="wsu-people-import-classification">Classification</label>
					<input type="text" id="wsu-people-import-classification" value="" autocomplete="off" />
				</p>

				<p>
					<label for="wsu-people-import-category">Category</label>
					<input type="text" id="wsu-people-import-category" value="" autocomplete="off" />
				</p>

				<p>
					<label for="wsu-people-import-tag">Tag</label>
					<input type="text" id="wsu-people-import-tag" value="" autocomplete="off" />
				</p>

				<button type="button" id="wsu-people-import" class="button">Add</button>

				<input type="hidden"
					   id="directory-page-profile-ids"
					   name="_wsu_people_directory_profile_ids"
					   value="<?php echo esc_attr( $directory_data['ids'] ); ?>" />

			</div>

		</div>

		<div class="wsu-people-directory-options display">

			<button type="button" class="wsu-people-directory-options-header">Display Options</button>

			<div>

				<p>
					<label for="wsu-people-directory-layout">Layout</label>
					<select id="wsu-people-directory-layout" name="_wsu_people_directory_layout">
						<option value="table"<?php selected( 'table', $directory_data['layout'] ); ?>>Table</option>
						<option value="grid"<?php selected( 'grid', $directory_data['layout'] ); ?>>Grid</option>
						<option value="custom"<?php selected( 'custom', $directory_data['layout'] ); ?>>Custom (provide your own CSS)</option>
					</select>
				</p>

				<p>
					<label for="wsu-people-directory-show-photos">Show photos</label>
					<select id="wsu-people-directory-show-photos" name="_wsu_people_directory_show_photos">
						<option value="yes"<?php selected( 'yes', $directory_data['profile_display_options']['photo'] ); ?>>Yes</option>
						<option value="no"<?php selected( 'no', $directory_data['profile_display_options']['photo'] ); ?>>No</option>
					</select>
				</p>

				<p>
					<label for="wsu-people-directory-about">About</label>
					<select id="wsu-people-directory-about" name="_wsu_people_directory_about">
						<option value="personal"<?php selected( 'bio_personal', $directory_data['profile_display_options']['about'] ); ?>>Personal Biography</option>
						<option value="bio_unit"<?php selected( 'bio_unit', $directory_data['profile_display_options']['about'] ); ?>>Unit Biography</option>
						<option value="bio_university"<?php selected( 'bio_university', $directory_data['profile_display_options']['about'] ); ?>>University Biography</option>
						<option value="tags"<?php selected( 'tags', $directory_data['profile_display_options']['about'] ); ?>>Tags</option>
						<option value="none"<?php selected( 'none', $directory_data['profile_display_options']['about'] ); ?>>None</option>
					</select>
				</p>

			</div>

		</div>

		<div class="wsu-people-directory-options interaction">

			<button type="button" class="wsu-people-directory-options-header">Interaction Options</button>

			<div>

				<p>
					<label for="wsu-people-directory-link">Link to full profiles</label>
					<select id="wsu-people-directory-link" name="_wsu_people_directory_link">
						<option value="if_bio"<?php selected( 'if_bio', $directory_data['profile_display_options']['link']['when'] ); ?>>If the person has a biography</option>
						<option value="yes"<?php selected( 'yes', $directory_data['profile_display_options']['link']['when'] ); ?>>Yes</option>
						<option value="no"<?php selected( 'no', $directory_data['profile_display_options']['link']['when'] ); ?>>No</option>
					</select>
				</p>

				<p>
					<label for="wsu-people-directory-profile">Open full profiles in a</label>
					<select id="wsu-people-directory-profile" name="_wsu_people_directory_profile">
						<option value="page"<?php selected( 'page', $directory_data['profile_display_options']['link']['profile'] ); ?>>Page</option>
						<option value="lightbox"<?php selected( 'lightbox', $directory_data['profile_display_options']['link']['profile'] ); ?>>Lightbox</option>
					</select>
				</p>

				<p>Allow filtering by</p>

				<ul>
					<li>
						<label>
							<input type="checkbox"
								   name="_wsu_people_directory_filters[]"
								   value="search"
									<?php if ( is_array( $directory_data['filters']['options'] ) && in_array( 'search', $directory_data['filters']['options'], true ) ) { echo 'checked="checked"'; } ?>>
							Search
						</label>
					</li>
					<li>
						<label>
							<input type="checkbox"
								   name="_wsu_people_directory_filters[]"
								   value="location"
									<?php if ( is_array( $directory_data['filters']['options'] ) && in_array( 'location', $directory_data['filters']['options'], true ) ) { echo 'checked="checked"'; } ?>>
							Location
						</label>
					</li>
					<li>
						<label>
							<input type="checkbox"
								   name="_wsu_people_directory_filters[]"
								   value="unit"
									<?php if ( is_array( $directory_data['filters']['options'] ) && in_array( 'unit', $directory_data['filters']['options'], true ) ) { echo 'checked="checked"'; } ?>>
							Unit
						</label>
					</li>
				</ul>

			</div>

		</div>

		<div class="wsu-people-bulk-actions">

			<button type="button" class="button toggle-select-mode">Bulk Select</button>

			<button type="button" class="button select-all-people">Select All</button>

			<button type="button" class="button button-primary delete-selected-people" disabled="disabled">Delete Selected</button>

		</div>

		<div class="wsu-people-admin-wrapper<?php if ( $directory_data['loading'] ) { ?> loading<?php } ?>">

			<?php include plugin_dir_path( dirname( __FILE__ ) ) . 'templates/people.php'; ?>

			<div class="wsu-person-controls-tooltip" role="presentation">
				<div class="wsu-person-controls-tooltip-arrow"></div>
				<div class="wsu-person-controls-tooltip-inner"></div>
			</div>

		</div>

		<script type="text/template" id="wsu-person-template">

			<article class="wsu-person<%= has_photo %>" data-nid="<%= nid %>" data-profile-id="<%= id %>">

				<div class="card">

					<h2 class="name">
						<a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?><%= slug %>"><%= name %></a>
					</h2>

					<a class="photo" href="<?php echo esc_url( get_permalink( $post->ID ) ); ?><%= slug %>">
						<img src="<?php echo esc_url( plugins_url( 'images/placeholder.png', dirname( __FILE__ ) ) ); ?>"
							 data-photo="<%= photo %>"
							 alt="<%= name %>" />
					</a>

					<div class="contact">
						<div class="title"><%= title %></div>
						<div class="email"><a href="mailto:<%= email %>"><%= email %></a></div>
						<div class="phone"><%= phone %></div>
						<div class="office"><%= office %></div>
						<div class="address"><%= address %></div>
						<div class="website"><a href="<%= website %>"><%= website %></a></div>
					</div>

				</div>

				<div class="about"><%= content %></div>

				<div class="wsu-person-controls">
					<button type="button" class="wsu-person-edit" aria-label="Edit">
						<span class="dashicons dashicons-edit"></span>
					</button>
					<button type="button" class="wsu-person-remove" aria-label="Remove">
						<span class="dashicons dashicons-no"></span>
					</button>
				</div>

				<button type="button" class="wsu-person-select button-link check">
					<span class="media-modal-icon"></span>
					<span class="screen-reader-text">Deselect</span>
				</button>

			</article>

		</script>

		<?php
	}

	/**
	 * Sanitizes filter checkboxes.
	 *
	 * @since 0.3.0
	 *
	 * @param array $values
	 *
	 * @return array
	 */
	public static function sanitize_filter_checkboxes( $values ) {
		if ( ! is_array( $values ) || 0 === count( $values ) ) {
			return array();
		}

		$sanitized_values = array();
		$accepted_values = array( 'search', 'location', 'unit' );

		foreach ( $values as $index => $value ) {
			if ( in_array( $value, $accepted_values, true ) ) {
				$sanitized_values[] = sanitize_text_field( $value );
			}
		}

		return $sanitized_values;
	}

	/**
	 * Save data associated with a people directory page.
	 *
	 * @since 0.3.0
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_post( $post_id ) {
		if ( ! isset( $_POST['directory_page_nonce'] ) || ! wp_verify_nonce( $_POST['directory_page_nonce'], 'directory-page-configuration' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST['page_template'] ) || key( self::$template ) !== $_POST['page_template'] ) {
			return;
		}

		// We'll check against this further down.
		$previous_ids = get_post_meta( $post_id, '_wsu_people_directory_profile_ids', true );

		$keys = get_registered_meta_keys( 'post' );

		foreach ( $keys as $key => $args ) {
			if ( isset( $_POST[ $key ] ) && isset( $args['sanitize_callback'] ) ) {

				// Each piece of meta is registered with sanitization.
				update_post_meta( $post_id, $key, $_POST[ $key ] );
			} else {
				delete_post_meta( $post_id, $key );
			}
		}

		// Update associated people data.
		if ( ! isset( $_POST['_wsu_people_directory_profile_ids'] ) ) {
			return;
		}

		// Stop here if the order of people hasn't changed.
		if ( $previous_ids === $_POST['_wsu_people_directory_profile_ids'] ) {
			return;
		}

		// Set a flag to flush rewrite rules.
		if ( false === apply_filters( 'wsuwp_people_default_rewrite_slug', false ) ) {
			set_transient( 'wsuwp_people_directory_flush_rewrites', true );
		}

		// Sanitize the profile IDs associated with the page.
		$id_array = explode( ' ', $_POST['_wsu_people_directory_profile_ids'] );
		$sanitized_ids = implode( ' ', array_map( 'absint', $id_array ) );

		$scheduled_event_args = array(
			$sanitized_ids,
			$post_id,
		);

		wp_schedule_single_event( time(), 'wsuwp_people_add_or_update_profiles', $scheduled_event_args );
		wp_schedule_single_event( time(), 'wsuwp_people_remove_profile_from_page', $scheduled_event_args );
	}

	/**
	 * Add or update profiles associated with a directory page.
	 *
	 * @since 0.3.5
	 *
	 * @param array $ids     The IDs for the primary profile records.
	 * @param int   $post_id The directory page ID.
	 */
	public function add_or_update_profiles( $profile_ids, $post_id ) {
		$ids = explode( ' ', $profile_ids );

		foreach ( $ids as $index => $id ) {
			$person_query_args = array(
				'post_type' => WSUWP_People_Post_Type::$post_type_slug,
				'posts_per_page' => 1,
				'meta_key' => '_wsuwp_profile_post_id',
				'meta_value' => $id,
				'fields' => 'ids',
			);

			$people = get_posts( $person_query_args );

			if ( $people ) {
				foreach ( $people as $person ) {
					$on_page = get_post_meta( $person, '_on_page', true );
					$order_on_page = get_post_meta( $person, "_order_on_page_{$post_id}", true );
					if ( $index !== $order_on_page && $post_id !== $on_page ) {
						update_post_meta( $person, '_on_page', $post_id );
						update_post_meta( $person, "_order_on_page_{$post_id}", $index );
					}
				}
			} else {
				$person = $this->get_rest_data( $id );

				// If a matching person is found, save the profile. If not, remove from the IDs list.
				if ( $person ) {
					$this->save_person( $person, $post_id, $index );
				} else {
					// This prevents a rare scenario in which an ID is somehow saved but a REST API
					// request for its data is invalid.
					$ids = get_post_meta( $post_id, '_wsu_people_directory_profile_ids', true );
					$ids = str_replace( $id, '', $ids );
					$ids = str_replace( '  ', ' ', $ids );
					update_post_meta( $post_id, '_wsu_people_directory_profile_ids', true );
				}
			}
		}
	}

	/**
	 * Remove the meta associating a profile with a page.
	 *
	 * @since 0.3.5
	 *
	 * @param array $ids     The IDs for the primary profile records.
	 * @param int   $post_id The directory page ID.
	 */
	public function remove_profile_from_page( $profile_ids, $post_id ) {
		$ids = explode( ' ', $profile_ids );

		$removed_people_query_args = array(
			'post_type' => WSUWP_People_Post_Type::$post_type_slug,
			'posts_per_page' => -1,
			'meta_key' => '_wsuwp_profile_post_id',
			'meta_value' => $ids,
			'meta_compare' => 'not in',
			'fields' => 'ids',
		);

		$removed_people = get_posts( $removed_people_query_args );

		if ( $removed_people ) {
			foreach ( $removed_people as $person ) {
				delete_post_meta( $person, '_on_page', $post_id );
				delete_post_meta( $person, "_order_on_page_{$post_id}" );
			}
		}
	}

	/**
	 * Given a post ID, retrieve information about a person from people.wsu.edu.
	 *
	 * @since 0.3.0
	 *
	 * @param string $id The profile post ID.
	 *
	 * @return object|bool List of predefined information we'll expect on the other side.
	 *                     False if person is not available.
	 */
	public static function get_rest_data( $id ) {
		$request_url = trailingslashit( WSUWP_People_Directory::REST_URL() ) . $id;

		$response = wp_remote_get( $request_url );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$data = wp_remote_retrieve_body( $response );
		$data = json_decode( $data );

		return $data;
	}

	/**
	 * Save a person who has been added to a directory page.
	 *
	 * @since 0.3.0
	 *
	 * @param object $person  The person as returned from the REST API.
	 * @param int    $page_id ID of the page this person is associated with.
	 * @param int    $order   The person's order on the page.
	 */
	private function save_person( $person, $page_id, $order ) {
		$tags = array();
		$taxonomy_data = array();

		foreach ( $person->taxonomy_terms as $taxonomy => $terms ) {
			foreach ( $terms as $term ) {
				if ( 'post_tag' === $taxonomy ) {
					$tags[] = $term->slug;
				} else {
					$local_term = get_term_by( 'slug', $term->slug, $taxonomy );
					$taxonomy_data[ $taxonomy ][] = $local_term->term_id;
				}
			}
		}

		$person_data = array(
			'post_title' => wp_strip_all_tags( $person->title->rendered ),
			'post_content' => '',
			'post_status' => 'publish',
			'post_type' => WSUWP_People_Post_Type::$post_type_slug,
			'meta_input' => array(
				'_wsuwp_profile_post_id' => $person->id,
				'_wsuwp_profile_ad_nid' => $person->nid,
				'_on_page' => $page_id,
				"_order_on_page_{$page_id}" => absint( $order ),
			),
			'tags_input' => $tags,
		);

		$new_post_id = wp_insert_post( $person_data );

		if ( ! is_wp_error( $new_post_id ) ) {
			foreach ( $taxonomy_data as $tax => $terms ) {
				wp_set_object_terms( $new_post_id, $terms, $tax );
			}
		}
	}

	/**
	 * Update a person's display information for this page.
	 *
	 * @since 0.3.0
	 */
	public function person_details() {
		check_ajax_referer( 'person-details', 'nonce' );

		$post_id = absint( $_POST['post'] );
		$page_id = absint( $_POST['page'] );
		$meta = array();

		if ( isset( $_POST['title'] ) ) {
			$titles = explode( ' ', $_POST['title'] );
			$sanitized_titles = array();
			foreach ( $titles as $title ) {
				if ( 'ad' === $title ) {
					$sanitized_titles[] = 'ad';
				} else {
					$sanitized_titles[] = absint( $title );
				}
			}
			$meta['title'] = implode( ',', $sanitized_titles );
		}

		if ( isset( $_POST['photo'] ) ) {
			$meta['photo'] = absint( $_POST['photo'] );

		}

		if ( $_POST['about'] ) {
			$meta['about'] = sanitize_text_field( $_POST['about'] );
		}

		if ( ! empty( $meta ) ) {
			update_post_meta( $post_id, "_display_on_page_{$page_id}", $meta );
		}

		exit();
	}

	/**
	 * Provides the profiles for a directory page after they've all been inserted.
	 *
	 * @since 0.3.5
	 */
	public function check_inserted_profiles() {
		check_ajax_referer( 'person-details', 'nonce' );

		$post_id = absint( $_GET['page'] );
		$ids = get_post_meta( $post_id, '_wsu_people_directory_profile_ids', true );
		$id_array = explode( ' ', $ids );
		$id_count = count( $id_array );
		$people = false;

		$people_query_args = array(
			'post_type' => WSUWP_People_Post_Type::$post_type_slug,
			'posts_per_page' => $id_count,
			'meta_key' => "_order_on_page_{$post_id}",
			'orderby' => 'meta_value_num',
			'order' => 'asc',
			'meta_query' => array(
				array(
					'key' => '_on_page',
					'value' => $post_id,
				),
			),
		);

		$people_query = new WP_Query( $people_query_args );

		if ( $id_count === $people_query->post_count ) {
			$directory_data = $this->directory_data( $post_id );
			$people = $directory_data['people'];
		}

		echo wp_json_encode( $people );

		exit();
	}

	/**
	 * Add a "People Directory" option to the page template drop-down.
	 *
	 * @since 0.3.0
	 *
	 * @param array $posts_templates Page templates.
	 *
	 * @return array
	 */
	public function add_directory_template( $posts_templates ) {
		$posts_templates = array_merge( $posts_templates, self::$template );

		return $posts_templates;
	}

	/**
	 * Check if a theme is providing its own directory template.
	 *
	 * @since 0.3.0
	 *
	 * @return string Path to the template file.
	 */
	public function theme_has_directory_template() {
		return locate_template( 'wsu-people/people.php' );
	}

	/**
	 * Check if a theme is providing its own profile listing template.
	 *
	 * @since 0.3.5
	 *
	 * @return string Path to the template file.
	 */
	public function theme_has_listing_template() {
		return locate_template( 'wsu-people/person-listing.php' );
	}

	/**
	 * Returns a set of meta data for displaying a directory page.
	 *
	 * @since 0.3.0
	 *
	 * @param int $post_id
	 *
	 * @return array
	 */
	public function directory_data( $post_id ) {
		$ids = get_post_meta( $post_id, '_wsu_people_directory_profile_ids', true );
		$filters = get_post_meta( $post_id, '_wsu_people_directory_filters', true );
		$layout = get_post_meta( $post_id, '_wsu_people_directory_layout', true );
		$show_photos = get_post_meta( $post_id, '_wsu_people_directory_show_photos', true );
		$open_profiles_as = get_post_meta( $post_id, '_wsu_people_directory_profile', true );
		$wrapper_classes = 'wsu-people-wrapper';
		$wrapper_classes .= ( $layout ) ? ' ' . esc_attr( $layout ) : ' table';
		$theme_template = $this->theme_has_listing_template();
		$elements = array(
			'people' => array(),
			'locations' => array(),
			'units' => array(),
		);
		$loading = false;

		// Loop through the local records in their display order and add an `include`
		// parameter to the request URL for each one, then leverage `orderby=include`
		// to retrieve the primary records in the desired order. Silly, but effective.
		if ( $ids ) {
			$cache_key = md5( get_the_modified_date( 'Y-m-d H:i:s' ) );
			$cached_elements = wp_cache_get( $cache_key, 'directory_page_elements' );

			if ( $cached_elements ) {
				$elements = $cached_elements;
			} else {
				$id_array = explode( ' ', $ids );
				$count = count( $id_array );

				if ( 100 >= $count ) {
					$elements = $this->get_people( $post_id, $id_array, $count, $filters, 0 );
				} else {
					$groups = array_chunk( $id_array, 100 );
					$offset = 0;

					foreach ( $groups as $group ) {
						$group_count = count( $group );
						$chunk = $this->get_people( $post_id, $group, $group_count, $filters, $offset );

						$elements['people'] = array_merge( $elements['people'], $chunk['people'] );
						$elements['locations'] = array_merge( $elements['locations'], $chunk['locations'] );
						$elements['units'] = array_merge( $elements['units'], $chunk['units'] );

						$offset = $offset + $group_count;
					}
				}

				// Give a notification if profiles are still being loaded.
				// Otherwise, cache the people data for this page.
				if ( count( $elements['people'] ) !== $count ) {
					$loading = true;
					$elements['people'] = array();
				} else {
					wp_cache_set( $cache_key, $elements, 'directory_page_elements', 3600 );
				}
			}
		}

		if ( 'yes' === $show_photos ) {
			$wrapper_classes .= ' photos';
		}

		if ( 'lightbox' === $open_profiles_as ) {
			$wrapper_classes .= ' lightbox';
		}

		$data = array(
			'wrapper_classes' => $wrapper_classes,
			'ids' => $ids,
			'layout' => $layout,
			'people' => $elements['people'],
			'filters' => array(
				'options' => $filters,
				'template' => plugin_dir_path( dirname( __FILE__ ) ) . 'templates/filters.php',
				'location' => array_unique( $elements['locations'] ),
				'unit' => array_unique( $elements['units'] ),
			),
			'person_card_template' => ( $theme_template ) ? $theme_template : plugin_dir_path( dirname( __FILE__ ) ) . 'templates/person-listing.php',
			'profile_display_options' => array(
				'about' => get_post_meta( $post_id, '_wsu_people_directory_about', true ),
				'link' => array(
					'when' => get_post_meta( $post_id, '_wsu_people_directory_link', true ),
					'base_url' => get_permalink( $post_id ),
					'profile' => $open_profiles_as,
				),
				'photo' => $show_photos,
				'page_id' => $post_id,
			),
			'loading' => $loading,
		);

		return $data;
	}

	/**
	 * Return a group of people.
	 *
	 * @since 0.3.4
	 *
	 * @param int   $post_id
	 * @param array $id_array
	 * @param int   $per_page
	 * @param mixed $filters
	 *
	 * @return array
	 */
	public function get_people( $post_id, $id_array, $per_page, $filters, $offset ) {
		$elements = array(
			'people' => array(),
			'locations' => array(),
			'units' => array(),
		);

		$people_query_args = array(
			'post_type' => WSUWP_People_Post_Type::$post_type_slug,
			'posts_per_page' => count( $id_array ),
			'meta_key' => "_order_on_page_{$post_id}",
			'orderby' => 'meta_value_num',
			'order' => 'asc',
			'meta_query' => array(
				array(
					'key' => '_on_page',
					'value' => $post_id,
				),
			),
		);

		if ( 0 !== $offset ) {
			$people_query_args['offset'] = $offset;
		}

		$people = new WP_Query( $people_query_args );

		if ( $people->have_posts() ) {
			$request_url = add_query_arg( array(
				'per_page' => $per_page,
				'orderby' => 'include',
			), WSUWP_People_Directory::REST_URL() );

			while ( $people->have_posts() ) {
				$people->the_post();
				$profile_id = get_post_meta( get_the_ID(), '_wsuwp_profile_post_id', true );
				$request_url = add_query_arg( 'include[]', $profile_id, $request_url );

				if ( is_array( $filters ) ) {
					$get_terms_args = array(
						'fields' => 'names',
					);

					if ( in_array( 'location', $filters, true ) ) {
						$profile_locations = wp_get_post_terms( get_the_ID(), 'wsuwp_university_location', $get_terms_args );
						$elements['locations'] = array_unique( array_merge( $elements['locations'], $profile_locations ) );
					}

					if ( in_array( 'unit', $filters, true ) ) {
						$profile_units = wp_get_post_terms( get_the_ID(), 'wsuwp_university_org', $get_terms_args );
						$elements['units'] = array_unique( array_merge( $elements['units'], $profile_units ) );
					}
				}
			}
			wp_reset_postdata();

			$response = wp_remote_get( $request_url );

			if ( ! is_wp_error( $response ) ) {
				$data = wp_remote_retrieve_body( $response );

				if ( ! empty( $data ) ) {
					$people = json_decode( $data );

					if ( ! empty( $people ) ) {
						$elements['people'] = $people;
					}
				}
			}
		}

		return $elements;
	}

	/**
	 * Determine which template to use and enqueue dependencies if needed.
	 *
	 * @since 0.3.0
	 *
	 * @param string $template The path of the template to include.
	 *
	 * @return string
	 */
	public function template_include( $template ) {
		$post = get_post();

		if ( ! $post ) {
			return $template;
		}

		if ( key( self::$template ) !== get_page_template_slug( $post->ID ) ) {
			return $template;
		}

		// Enqueue styles and scripts if appropriate.
		if ( 'custom' !== get_post_meta( $post->ID, '_wsu_people_directory_layout', true ) ) {
			wp_enqueue_style( 'wsu-people', plugin_dir_url( dirname( __FILE__ ) ) . 'css/people.css', array(), WSUWP_People_Directory::$version );
			wp_enqueue_script( 'wsu-people', plugin_dir_url( dirname( __FILE__ ) ) . 'js/people.min.js', array( 'jquery', 'underscore' ), WSUWP_People_Directory::$version, true );

			if ( 'lightbox' === get_post_meta( $post->ID, '_wsu_people_directory_profile', true ) ) {
				wp_localize_script( 'wsu-people', 'wsu_people_rest_url', trailingslashit( WSUWP_People_Directory::REST_URL() ) );
			}
		}

		add_filter( 'the_content', array( $this, 'directory_content' ) );

		return trailingslashit( get_template_directory() ) . 'templates/single.php';
	}

	/**
	 * Display the list of people as defined by the directory page.
	 *
	 * @since 0.3.0
	 *
	 * @return string Modified content.
	 */
	public function directory_content() {
		remove_filter( 'the_content', array( $this, 'directory_content' ) );

		$directory_data = $this->directory_data( get_post()->ID );

		ob_start();

		// If a theme has a directory template, use it.
		if ( $this->theme_has_directory_template() ) {
			include $this->theme_has_directory_template();
		} else {
			include plugin_dir_path( dirname( __FILE__ ) ) . 'templates/people.php';
		}

		$content = ob_get_clean();

		return $content;
	}

	/**
	 * Output the underscore template for lightbox profiles.
	 *
	 * @since 0.3.0
	 */
	public function person_lightbox_template() {
		$post = get_post();

		if ( ! $post ) {
			return;
		}

		if ( 'lightbox' !== get_post_meta( $post->ID, '_wsu_people_directory_profile', true ) ) {
			return;
		}

		?>
		<script type="text/template" id="wsu-person-lightbox-template">

			<div class="wsu-people-lightbox wsu-people-lightbox-close" tabindex="-1">

				<article class="wsu-person" role="dialog" aria-labelledby="wsu-person-name">

					<div class="card">

						<h2 class="name" id="wsu-person-name" tabindex="0"><%= name %></h2>

						<% if ( photo ) { %>
						<figure class="photo">
							<img src="<%= photo %>" alt="<%= name %>" />
						</figure>
						<% } %>

						<div class="contact">
							<div class="title"><%= title %></div>
							<div class="email"><a href="mailto:<%= email %>"><%= email %></a></div>
							<div class="phone"><%= phone %></div>
							<div class="office"><%= office %></div>
							<div class="address"><%= address %></div>
							<% if ( website ) { %>
							<div class="website"><a href="<%= website %>"><%= website %></a></div>
							<% } %>
						</div>

					</div>

					<div class="about"><%= about %></div>

					<button type="button" class="wsu-people-lightbox-close" aria-label="Close this dialog window">&times;</button>

				</article>

			</div>

		</script>
		<?php
	}
}
