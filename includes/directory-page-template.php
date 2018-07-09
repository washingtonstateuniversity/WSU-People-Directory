<?php

namespace WSUWP\People_Directory\Directory_Page_Template;

/**
 * Returns the plugin version number for breaking cache.
 *
 * @return string The plugin version number.
 */
function plugin_version() {
	return \WSUWP\People_Directory\version();
}

/**
 * Returns the profile post type slug.
 *
 * @return string The profile post type slug.
 */
function primary_directory_api_path() {
	return \WSUWP\People_Directory\API_path();
}

/**
 * Returns the profile post type slug.
 *
 * @return string The profile post type slug.
 */
function profile_post_type_slug() {
	return \WSUWP\People_Directory\Profile_Post_Type\slug();
}

/**
 * Returns the people directory page template slug and name.
 *
 * @since 0.3.0
 *
 * @return array People directory page template slug and name.
 */
function template() {
	return array(
		'templates/people.php' => 'People Directory',
	);
}

/**
 * Returns a list of post meta keys associated with a directory page.
 *
 * @since 0.3.0
 *
 * @return array Post meta keys associated with a directory page.
 */
function meta_keys() {
	$meta_keys = array(
		'_wsu_people_directory_profile_ids' => array(
			'type' => 'string',
			'description' => 'IDs of profile records to display on this page',
			'sanitize_callback' => 'sanitize_text_field',
		),
		'_wsu_people_directory_layout' => array(
			'type' => 'string',
			'description' => 'The layout to use for the directory display',
			'sanitize_callback' => 'sanitize_text_field',
		),
		'_wsu_people_directory_about' => array(
			'type' => 'string',
			'description' => 'The biographical or other information to show for profiles on this listing',
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
			'type' => 'string',
			'description' => 'Whether to show photos on this people listing',
			'sanitize_callback' => 'sanitize_text_field',
		),
		'_wsu_people_directory_filters' => array(
			'type' => 'array',
			'description' => 'Various methods by which to filter a directory',
			'sanitize_callback' => __NAMESPACE__ . '\\sanitize_filter_checkboxes',
		),
	);

	return $meta_keys;
}

add_action( 'init', __NAMESPACE__ . '\\register_meta', 11 );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\admin_enqueue_scripts' );
add_action( 'add_meta_boxes_page', __NAMESPACE__ . '\\add_meta_boxes' );
add_action( 'save_post_page', __NAMESPACE__ . '\\save_post', 10, 2 );
add_action( 'wsuwp_people_add_or_update_profiles', __NAMESPACE__ . '\\add_or_update_profiles', 10, 2 );
add_action( 'wsuwp_people_remove_profile_from_page', __NAMESPACE__ . '\\remove_profile_from_page', 10, 2 );
add_action( 'wp_ajax_person_details', __NAMESPACE__ . '\\person_details' );
add_action( 'wp_ajax_check_inserted_profiles', __NAMESPACE__ . '\\check_inserted_profiles' );
add_filter( 'theme_page_templates', __NAMESPACE__ . '\\add_directory_template' );
add_filter( 'template_include', __NAMESPACE__ . '\\template_include' );
add_action( 'wp_footer', __NAMESPACE__ . '\\person_lightbox_template' );

/**
 * Registers the meta keys used to store data about a directory page.
 *
 * @since 0.3.0
 */
function register_meta() {
	foreach ( meta_keys() as $key => $args ) {
		$args['single'] = true;

		\register_meta( 'post', $key, $args );
	}
}

/**
 * Enqueues the assets used on the Add New and Edit Page pages.
 *
 * @since 0.3.0
 *
 * @param string $hook_suffix The current admin page.
 */
function admin_enqueue_scripts( $hook_suffix ) {
	$screen = get_current_screen();

	if ( 'page' !== $screen->post_type || ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	global $post;

	wp_enqueue_style( 'wsuwp-people-display', plugins_url( 'css/people.css', dirname( __FILE__ ) ), array(), plugin_version() );
	wp_enqueue_style( 'wsuwp-people-admin', plugins_url( 'css/admin-edit-page.css', dirname( __FILE__ ) ), array(), plugin_version() );
	wp_enqueue_script( 'wsuwp-people-edit-page', plugins_url( 'js/admin-edit-page.min.js', dirname( __FILE__ ) ), array( 'jquery', 'underscore', 'jquery-ui-autocomplete', 'jquery-ui-sortable' ), plugin_version(), true );

	wp_localize_script( 'wsuwp-people-edit-page', 'wsuwp_people_edit_page', array(
		'rest_route' => primary_directory_api_path() . 'wp/v2/',
		'rest_url' => primary_directory_api_path() . 'wp/v2/people',
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'nonce' => wp_create_nonce( 'person-details' ),
		'page_id' => $post->ID,
	) );
	wp_localize_script( 'wsuwp-people-sync', 'wsupeoplesync', array(
		'endpoint' => primary_directory_api_path() . 'wsuwp-people/v1/sync',
		'nonce' => \WSUWP\People_Directory\create_rest_nonce(),
		'uid' => wp_get_current_user()->ID,
		'site_url' => get_home_url(),
	) );

	$template = get_post_meta( $post->ID, '_wp_page_template', true );

	if ( $template && 'templates/people.php' === $template ) {
		add_filter( 'admin_body_class', __NAMESPACE__ . '\\add_body_class' );
	}
}

/**
 * Adds a body class if the page is using the people directory template.
 *
 * @since 0.3.6
 *
 * @return string
 */
function add_body_class( $classes ) {
	$classes .= ' wsuwp-people-directory-template ';

	return $classes;
}

/**
 * Adds the meta boxes used for capturing directory page data.
 *
 * @since 0.3.0
 */
function add_meta_boxes() {
	add_meta_box( 'wsuwp-people-directory-configuration', 'People Directory Setup', __NAMESPACE__ . '\\display_people_directory_setup', 'page', 'normal', 'high' );
}

/**
 * Displays a meta box used to configure a directory page.
 *
 * @since 0.3.0
 *
 * @param WP_Post $post Post object.
 */
function display_people_directory_setup( $post ) {
	wp_nonce_field( 'directory-page-configuration', 'directory_page_nonce' );

	$directory_data = directory_data( $post->ID );
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
				<label for="wsu-people-import-wsu-category">University Category</label>
				<input type="text" id="wsu-people-import-wsu-category" value="" autocomplete="off" />
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
					<option value="thumbnail"<?php selected( 'thumbnail', $directory_data['profile_display_options']['photo'] ); ?>>Thumbnail</option>
					<option value="medium"<?php selected( 'medium', $directory_data['profile_display_options']['photo'] ); ?>>Medium</option>
					<option value="large"<?php selected( 'large', $directory_data['profile_display_options']['photo'] ); ?>>Large</option>
					<option value="no"<?php selected( 'no', $directory_data['profile_display_options']['photo'] ); ?>>None</option>
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
							   value="org"
							   <?php if ( is_array( $directory_data['filters']['options'] ) && in_array( 'org', $directory_data['filters']['options'], true ) ) { echo 'checked="checked"'; } ?>>
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
 * Sanitizes filter option checkboxes.
 *
 * @since 0.3.0
 *
 * @param array $values Unsanitized values.
 *
 * @return array The sanitized values.
 */
function sanitize_filter_checkboxes( $values ) {
	if ( ! is_array( $values ) || 0 === count( $values ) ) {
		return array();
	}

	$sanitized_values = array();
	$accepted_values = array( 'search', 'location', 'org' );

	foreach ( $values as $index => $value ) {
		if ( in_array( $value, $accepted_values, true ) ) {
			$sanitized_values[] = sanitize_text_field( $value );
		}
	}

	return $sanitized_values;
}

/**
 * Saves data associated with a directory page.
 *
 * @since 0.3.0
 *
 * @param int $post_id Post ID.
 */
function save_post( $post_id ) {
	if ( ! isset( $_POST['directory_page_nonce'] ) || ! wp_verify_nonce( $_POST['directory_page_nonce'], 'directory-page-configuration' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( ! isset( $_POST['page_template'] ) || key( template() ) !== $_POST['page_template'] ) {
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

	// Stop here if the order of profiles hasn't changed.
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
 * Adds or update profiles associated with a directory page.
 *
 * @since 0.3.5
 *
 * @param array $ids     The IDs for the primary profile records.
 * @param int   $post_id The directory page ID.
 */
function add_or_update_profiles( $profile_ids, $post_id ) {
	$ids = explode( ' ', $profile_ids );

	foreach ( $ids as $index => $id ) {
		$profile_query_args = array(
			'post_type' => profile_post_type_slug(),
			'posts_per_page' => 1,
			'meta_key' => '_wsuwp_profile_post_id',
			'meta_value' => $id,
			'fields' => 'ids',
		);

		$profiles = get_posts( $profile_query_args );

		if ( $profiles ) {
			foreach ( $profiles as $profile ) {
				if ( get_post_meta( $profile, '_on_page', true ) !== $post_id ) {
					update_post_meta( $profile, '_on_page', $post_id );
				}
			}
		} else {
			$profile = get_rest_data( $id );

			// If a matching profile is found, save it. If not, remove from the IDs list.
			if ( $profile ) {
				save_profile( $profile, $post_id, $index );
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
 * Removes the meta associating a profile with a page.
 *
 * @since 0.3.5
 *
 * @param array $ids     The IDs for the primary profile records.
 * @param int   $post_id The directory page ID.
 */
function remove_profile_from_page( $profile_ids, $post_id ) {
	$ids = explode( ' ', $profile_ids );

	$removed_profiles_query_args = array(
		'post_type' => profile_post_type_slug(),
		'posts_per_page' => -1,
		'meta_key' => '_wsuwp_profile_post_id',
		'meta_value' => $ids,
		'meta_compare' => 'not in',
		'fields' => 'ids',
	);

	$removed_profiles = get_posts( $removed_profiles_query_args );

	if ( $removed_profiles ) {
		foreach ( $removed_profiles as $profile ) {
			delete_post_meta( $profile, '_on_page', $post_id );
		}
	}
}

/**
 * Retrieves a profile with the given post ID from the primary directory.
 *
 * @since 0.3.0
 *
 * @param string $id The profile post ID.
 *
 * @return object|bool List of predefined information we'll expect on the other side.
 *                     False if profile is not available.
 */
function get_rest_data( $id ) {
	$request_url = trailingslashit( primary_directory_api_path() . 'wp/v2/people' ) . $id;

	$response = wp_remote_get( $request_url );

	if ( is_wp_error( $response ) ) {
		return false;
	}

	$data = wp_remote_retrieve_body( $response );
	$data = json_decode( $data );

	return $data;
}

/**
 * Saves a profile that has been added to a directory page.
 *
 * @since 0.3.0
 *
 * @param object $profile  The profile as returned from the REST API.
 * @param int    $page_id ID of the page this profile is associated with.
 * @param int    $order   The profile's order on the page.
 */
function save_profile( $profile, $page_id, $order ) {
	$tags = array();
	$taxonomy_data = array();

	foreach ( $profile->taxonomy_terms as $taxonomy => $terms ) {
		foreach ( $terms as $term ) {
			if ( 'post_tag' !== $taxonomy && 'category' !== $taxonomy ) {
				$local_term = get_term_by( 'slug', $term->slug, $taxonomy );
				if ( $local_term ) {
					$taxonomy_data[ $taxonomy ][] = $local_term->term_id;
				}
			}
		}
	}

	$profile_data = array(
		'post_title' => wp_strip_all_tags( $profile->title->rendered ),
		'post_content' => '',
		'post_status' => 'publish',
		'post_type' => profile_post_type_slug(),
		'meta_input' => array(
			'_wsuwp_profile_post_id' => $profile->id,
			'_wsuwp_profile_ad_nid' => $profile->nid,
			'_on_page' => $page_id,
		),
		'tags_input' => $tags,
	);

	$new_post_id = wp_insert_post( $profile_data );

	if ( ! is_wp_error( $new_post_id ) ) {
		foreach ( $taxonomy_data as $tax => $terms ) {
			wp_set_object_terms( $new_post_id, $terms, $tax );
		}
	}
}

/**
 * Updates a profile's display information for this page.
 *
 * @since 0.3.0
 */
function person_details() {
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
function check_inserted_profiles() {
	check_ajax_referer( 'person-details', 'nonce' );

	$post_id = absint( $_GET['page'] );
	$ids = get_post_meta( $post_id, '_wsu_people_directory_profile_ids', true );
	$id_array = explode( ' ', $ids );
	$id_count = count( $id_array );
	$profiles = false;

	$profiles_query_args = array(
		'post_type' => profile_post_type_slug(),
		'posts_per_page' => $id_count,
		'meta_query' => array(
			array(
				'key' => '_on_page',
				'value' => $post_id,
			),
		),
	);

	$profiles_query = new \WP_Query( $profiles_query_args );

	if ( $id_count === $profiles_query->post_count ) {
		$directory_data = directory_data( $post_id );
		$profiles = $directory_data['people'];
	}

	echo wp_json_encode( $profiles );

	exit();
}

/**
 * Adds a "People Directory" option to the page template drop-down.
 *
 * @since 0.3.0
 *
 * @param array $posts_templates Page templates.
 *
 * @return array
 */
function add_directory_template( $posts_templates ) {
	$posts_templates = array_merge( $posts_templates, template() );

	return $posts_templates;
}

/**
 * Returns the path to the directory template.
 *
 * Allows themes to provide an override.
 *
 * @since 0.3.0
 *
 * @return string Path to the directory template file.
 */
function directory_template() {
	if ( locate_template( 'wsu-people/people.php' ) ) {
		return locate_template( 'wsu-people/people.php' );
	}

	return plugin_dir_path( dirname( __FILE__ ) ) . 'templates/people.php';
}

/**
 * Returns the path to the profile listing template.
 *
 * Allows themes to provide an override.
 *
 * @since 0.3.5
 *
 * @return string Path to the profile listing template file.
 */
function profile_listing_template() {
	if ( locate_template( 'wsu-people/person-listing.php' ) ) {
		return locate_template( 'wsu-people/person-listing.php' );
	}

	return plugin_dir_path( dirname( __FILE__ ) ) . 'templates/person-listing.php';
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
function directory_data( $post_id ) {
	$ids = get_post_meta( $post_id, '_wsu_people_directory_profile_ids', true );
	$filters = get_post_meta( $post_id, '_wsu_people_directory_filters', true );
	$layout = get_post_meta( $post_id, '_wsu_people_directory_layout', true );
	$show_photos = get_post_meta( $post_id, '_wsu_people_directory_show_photos', true );
	$open_profiles_as = get_post_meta( $post_id, '_wsu_people_directory_profile', true );

	// Set up the wrapper classes.
	$wrapper_classes = 'wsu-people-wrapper';
	$wrapper_classes .= ( $layout ) ? ' ' . esc_attr( $layout ) : ' table';

	if ( 'no' !== $show_photos ) {
		$wrapper_classes .= ' photos';
	}

	if ( 'lightbox' === $open_profiles_as ) {
		$wrapper_classes .= ' lightbox';
	}

	// Set up the page content.
	$elements = array(
		'people' => array(),
		'locations' => array(),
		'orgs' => array(),
	);
	$loading = false;

	if ( $ids ) {
		$cache_key = md5( get_the_modified_date( 'Y-m-d H:i:s' ) );
		$cached_elements = wp_cache_get( $cache_key, 'directory_page_elements' );

		if ( $cached_elements ) {
			$elements = $cached_elements;
		} else {
			$id_array = explode( ' ', $ids );
			$count = count( $id_array );

			// Get post count and taxonomy terms from the local people records.
			$local_people_data = get_local_profiles( $post_id, $count, $filters );
			$elements['locations'] = $local_people_data['locations'];
			$elements['orgs'] = $local_people_data['orgs'];

			// Get the people via REST request from the primary site.
			if ( 100 >= $count ) {
				$elements['people'] = get_profiles( $id_array, $count );
			} else {
				$id_groups = array_chunk( $id_array, 100 );

				foreach ( $id_groups as $id_group ) {
					$id_group_count = count( $id_group );
					$people_group = get_profiles( $id_group, $id_group_count );
					$elements['people'] = array_merge( $elements['people'], $people_group );
				}
			}

			// If the number of IDs doesn't match the number of local profiles,
			// they're probably still being inserted, so set a flag to display
			// a notification telling the user as much.
			// This also checks that the number of profiles returned by the REST
			// request matches the number of IDs.
			// Otherwise, cache the more expensive content for this page.
			if ( $local_people_data['count'] !== $count || count( $elements['people'] ) !== $count ) {
				$loading = true;
				$elements['people'] = array();
			} else {
				wp_cache_set( $cache_key, $elements, 'directory_page_elements', 3600 );
			}
		}
	}

	// Assemble the data used for displaying the page.
	$data = array(
		'wrapper_classes' => $wrapper_classes,
		'ids' => $ids,
		'layout' => $layout,
		'people' => $elements['people'],
		'filters' => array(
			'options' => $filters,
			'template' => plugin_dir_path( dirname( __FILE__ ) ) . 'templates/filters.php',
			'location' => $elements['locations'],
			'org' => $elements['orgs'],
		),
		'person_card_template' => profile_listing_template(),
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
 * Returns number of local profiles and, if needed,
 * the terms for populating filters on a directory page.
 *
 * @since 0.3.6
 *
 * @param int   $post_id The ID of the directory page.
 * @param int   $count   The number of profiles associated with the directory page.
 * @param array $filters The filter options to display on the directory page.
 *
 * @return array
 */
function get_local_profiles( $post_id, $count, $filters ) {
	$local_data = array(
		'count' => 0,
		'locations' => array(),
		'orgs' => array(),
	);

	$people_query_args = array(
		'post_type' => profile_post_type_slug(),
		'posts_per_page' => $count,
		'meta_query' => array(
			array(
				'key' => '_on_page',
				'value' => $post_id,
			),
		),
	);

	$people = new \WP_Query( $people_query_args );

	$local_data['count'] = $people->post_count;

	// Stop here if no taxonomy filters are being displayed.
	if ( ! $filters || empty( array_diff( $filters, array( 'search' ) ) ) ) {
		return $local_data;
	}

	if ( $people->have_posts() ) {
		while ( $people->have_posts() ) {
			$people->the_post();
			$get_terms_args = array(
				'fields' => 'names',
			);

			if ( in_array( 'location', $filters, true ) ) {
				$locations = wp_get_post_terms( get_the_ID(), 'wsuwp_university_location', $get_terms_args );

				$local_data['locations'] = array_unique( array_merge( $local_data['locations'], $locations ) );
			}

			if ( in_array( 'org', $filters, true ) ) {
				$orgs = wp_get_post_terms( get_the_ID(), 'wsuwp_university_org', $get_terms_args );
				$local_data['orgs'] = array_unique( array_merge( $local_data['orgs'], $orgs ) );
			}
		}
		wp_reset_postdata();
	}

	return $local_data;
}

/**
 * Returns a group of profiles.
 *
 * @since 0.3.5
 *
 * @param array $id_array The IDs of the primary profiles to retrieve.
 * @param int   $per_page The number of profiles to request.
 *
 * @return array Profiles retrieved from the primary directory.
 */
function get_profiles( $id_array, $per_page ) {
	$query_args = array(
		'per_page' => $per_page,
		'orderby' => 'include',
	);

	$request_url = add_query_arg( $query_args, primary_directory_api_path() . 'wp/v2/people' );

	foreach ( $id_array as $id ) {
		$request_url = add_query_arg( 'include[]', absint( $id ), $request_url );
	}

	$response = wp_remote_get( $request_url );

	if ( is_wp_error( $response ) ) {
		return array();
	}

	$data = wp_remote_retrieve_body( $response );

	if ( empty( $data ) ) {
		return array();
	}

	$people = json_decode( $data );

	if ( empty( $people ) ) {
		return array();
	}

	return $people;
}

/**
 * Determines which template to use and enqueues any necessary assets.
 *
 * @since 0.3.0
 *
 * @param string $template The path of the template to include.
 *
 * @return string The path of the template to include.
 */
function template_include( $template ) {
	$post = get_post();

	if ( ! $post ) {
		return $template;
	}

	if ( key( template() ) !== get_page_template_slug( $post->ID ) ) {
		return $template;
	}

	// Enqueue styles if appropriate.
	if ( 'custom' !== get_post_meta( $post->ID, '_wsu_people_directory_layout', true ) ) {
		wp_enqueue_style( 'wsu-people', plugin_dir_url( dirname( __FILE__ ) ) . 'css/people.css', array(), plugin_version() );
	}

	// Enqueue scripts.
	wp_enqueue_script( 'wsu-people', plugin_dir_url( dirname( __FILE__ ) ) . 'js/people.min.js', array( 'jquery', 'underscore' ), plugin_version(), true );

	if ( 'lightbox' === get_post_meta( $post->ID, '_wsu_people_directory_profile', true ) ) {
		wp_localize_script( 'wsu-people', 'wsu_people_rest_url', trailingslashit( primary_directory_api_path() . 'wp/v2/people' ) );
	}

	add_filter( 'the_content', __NAMESPACE__ . '\\directory_content' );

	return trailingslashit( get_template_directory() ) . 'templates/single.php';
}

/**
 * Displays the list of profiles as defined by the directory page.
 *
 * @since 0.3.0
 *
 * @return string Modified content.
 */
function directory_content() {
	remove_filter( 'the_content', __NAMESPACE__ . '\\directory_content' );

	$directory_data = directory_data( get_post()->ID );

	ob_start();

	include directory_template();

	$content = ob_get_clean();

	return $content;
}

/**
 * Outputs the underscore template for lightbox profiles.
 *
 * @since 0.3.0
 */
function person_lightbox_template() {
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
						<%= title %>
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
