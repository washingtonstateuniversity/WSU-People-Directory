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
		'_wsu_people_directory_nids' => array(
			'type' => 'string',
			'description' => 'A list of people to display on this page',
			'sanitize_callback' => 'sanitize_text_field',
		),
		'_wsu_people_directory_layout' => array(
			'type' => 'string',
			'description' => 'The layout to use for the directory display',
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

		add_filter( 'theme_page_templates', array( $this, 'add_directory_template' ) );
		add_filter( 'template_include', array( $this, 'template_include' ) );
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
		$screen = get_current_screen();

		if ( 'page' !== $screen->post_type || ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		wp_enqueue_style( 'wsuwp-people-display', plugins_url( 'css/people.css', dirname( __FILE__ ) ), array(), WSUWP_People_Directory::$version );
		wp_enqueue_style( 'wsuwp-people-admin', plugins_url( 'css/admin-page.css', dirname( __FILE__ ) ), array(), WSUWP_People_Directory::$version );
		wp_enqueue_script( 'wsuwp-people-admin', plugins_url( 'js/admin-page.min.js', dirname( __FILE__ ) ), array( 'jquery', 'underscore', 'jquery-ui-autocomplete', 'jquery-ui-sortable' ), WSUWP_People_Directory::$version, true );
		wp_enqueue_script( 'wsuwp-people-sync', plugins_url( 'js/admin-people-sync.min.js', dirname( __FILE__ ) ), array( 'jquery' ), WSUWP_People_Directory::$version, true );

		wp_localize_script( 'wsuwp-people-admin', 'wsupeople', array(
			'rest_url' => WSUWP_People_Directory::REST_URL(),
		) );
		wp_localize_script( 'wsuwp-people-sync', 'wsupeoplesync', array(
			'nonce' => wp_create_nonce( 'wp_rest' ),
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

		$nids = get_post_meta( $post->ID, '_wsu_people_directory_nids', true );
		$layout = get_post_meta( $post->ID, '_wsu_people_directory_layout', true );
		$link = get_post_meta( $post->ID, '_wsu_people_directory_link', true );
		$profile = get_post_meta( $post->ID, '_wsu_people_directory_profile', true );
		$photos = get_post_meta( $post->ID, '_wsu_people_directory_show_photos', true );

		?>
			<p>
				<label for="wsu-people-import">Import/add people</label>
				<input type="text" id="wsu-people-import" value="" />
				<input type="hidden"
					   id="directory-page-nids"
					   name="_wsu_people_directory_nids"
					   value="<?php echo esc_attr( $nids ); ?>" />
			</p>

			<p>
				<label for="wsu-people-directory-layout">Layout</label>
				<select id="wsu-people-directory-layout" name="_wsu_people_directory_layout">
					<option value="table"<?php selected( 'table', $layout ); ?>>Table</option>
					<option value="grid"<?php selected( 'grid', $layout ); ?>>Grid</option>
					<option value="custom"<?php selected( 'custom', $layout ); ?>>Custom (provide your own CSS)</option>
				</select>
			</p>

			<p>
				<label for="wsu-people-directory-link">Link to full profiles</label>
				<select id="wsu-people-directory-link" name="_wsu_people_directory_link">
					<option value="if_bio"<?php selected( 'if_bio', $link ); ?>>If the person has a biography</option>
					<option value="yes"<?php selected( 'yes', $link ); ?>>Yes</option>
					<option value="no"<?php selected( 'no', $link ); ?>>No</option>
				</select>
			</p>

			<p>
				<label for="wsu-people-directory-profile">Open full profiles in a</label>
				<select id="wsu-people-directory-profile" name="_wsu_people_directory_profile">
					<option value="page"<?php selected( 'page', $profile ); ?>>Page</option>
					<option value="lightbox"<?php selected( 'lightbox', $profile ); ?>>Lightbox</option>
				</select>
			</p>

			<p>
				<label for="wsu-people-directory-show-photos">Show photos</label>
				<select id="wsu-people-directory-show-photos" name="_wsu_people_directory_show_photos">
					<option value="yes"<?php selected( 'yes', $photos ); ?>>Yes</option>
					<option value="no"<?php selected( 'no', $photos ); ?>>No</option>
				</select>

			</p>

			<script type="text/template" id="wsu-person-template">

				<article class="wsu-person<%= has_photo %>" data-nid="<%= nid %>">

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
						<button class="wsu-person-edit" aria-label="Edit">
							<span class="dashicons dashicons-edit"></span>
						</button>
						<button class="wsu-person-remove" aria-label="Remove">
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
			$base_url = get_permalink( $post->ID );
			$wrapper_classes = array( 'wsu-people-wrapper' );
			$wrapper_classes[] = ( $layout ) ? esc_attr( $layout ) : 'table';

			if ( $photos ) {
				$wrapper_classes[] = 'photos';
			}

			?>

			<div class="wsu-people-bulk-actions">

				<button type="button" class="button toggle-select-mode">Bulk Select</button>

				<button type="button" class="button select-all-people">Select All</button>

				<button type="button" class="button button-primary delete-selected-people" disabled="disabled">Delete Selected</button>

			</div>

			<div class="<?php echo esc_html( implode( ' ', $wrapper_classes ) ); ?>">

				<div class="wsu-people">

					<?php
					if ( $nids ) {
						$nids = explode( ' ', $nids );

						$people_query_args = array(
							'post_type' => WSUWP_People_Post_Type::$post_type_slug,
							'posts_per_page' => count( $nids ),
							'meta_key' => "_order_on_page_{$post->ID}",
							'orderby' => 'meta_value_num',
							'order' => 'asc',
							'meta_query' => array(
								array(
									'key' => '_on_page',
									'value' => $post->ID,
								),
							),
						);

						$people = new WP_Query( $people_query_args );

						if ( $people->have_posts() ) {
							while ( $people->have_posts() ) {
								$people->the_post();
								include plugin_dir_path( dirname( __FILE__ ) ) . 'templates/person.php';
							}
							wp_reset_postdata();
						}
					}
					?>

				</div>

				<div class="wsu-person-controls-tooltip" role="presentation">
					<div class="wsu-person-controls-tooltip-arrow"></div>
					<div class="wsu-person-controls-tooltip-inner"></div>
				</div>

			</div>
		<?php
	}

	/**
	 * Save data associated with a people directory page.
	 *
	 * @since 0.3.0
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return mixed
	 */
	public function save_post( $post_id ) {
		if ( ! isset( $_POST['directory_page_nonce'] ) || ! wp_verify_nonce( $_POST['directory_page_nonce'], 'directory-page-configuration' ) ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		if ( ! isset( $_POST['page_template'] ) || key( self::$template ) !== $_POST['page_template'] ) {
			return $post_id;
		}

		// We'll check against this further down.
		$previous_nids = get_post_meta( $post_id, '_wsu_people_directory_nids', true );

		$keys = get_registered_meta_keys( 'post' );

		foreach ( $keys as $key => $args ) {
			if ( isset( $_POST[ $key ] ) && isset( $args['sanitize_callback'] ) ) {

				// Each piece of meta is registered with sanitization.
				update_post_meta( $post_id, $key, $_POST[ $key ] );
			}
		}

		// Set a flag to flush rewrite rules.
		set_transient( 'wsuwp_people_directory_flush_rewrites', true );

		// Update associated people data.
		if ( ! isset( $_POST['_wsu_people_directory_nids'] ) ) {
			return $post_id;
		}

		if ( $previous_nids === $_POST['_wsu_people_directory_nids'] ) {
			return $post_id;
		}

		// Save order data of the associated people.
		$nids = explode( ' ', $_POST['_wsu_people_directory_nids'] );

		foreach ( $nids as $index => $nid ) {
			$person_query_args = array(
				'post_type' => WSUWP_People_Post_Type::$post_type_slug,
				'posts_per_page' => 1,
				'meta_key' => '_wsuwp_profile_ad_nid',
				'meta_value' => $nid,
				'fields' => 'ids',
			);

			$person = get_posts( $person_query_args );

			if ( $person ) {
				foreach ( $person as $person ) {
					$on_page = get_post_meta( $person, '_on_page', true );
					$order_on_page = get_post_meta( $person, "_order_on_page_{$post_id}", true );

					if ( $index !== $order_on_page && $post_id !== $on_page ) {
						update_post_meta( $person, '_on_page', $post_id );
						update_post_meta( $person, "_order_on_page_{$post_id}", $index );
					}
				}
			} else {
				// If no matching NID is found, save the profile.
				$this->save_person( $nid, $post_id, $index );
			}
		}

		// Delete order data for people removed from this page.
		$removed_people_query_args = array(
			'post_type' => WSUWP_People_Post_Type::$post_type_slug,
			'posts_per_page' => -1,
			'meta_key' => '_wsuwp_profile_ad_nid',
			'meta_value' => $nids,
			'meta_compare' => 'not in',
			'fields' => 'ids',
		);

		$removed_people = get_posts( $removed_people_query_args );

		if ( $removed_people ) {
			foreach ( $removed_people as $person ) {
				delete_post_meta( $person, '_on_page', $post_id, $post_id );
				delete_post_meta( $person, "_order_on_page_{$post_id}" );
			}
		}
	}

	/**
	 * Save a person who has been added to a directory page.
	 *
	 * @param string $nid     The person's netID.
	 * @param int    $page_id ID of the page this person is associated with.
	 * @param order  $order   The person's order on the page.
	 */
	private function save_person( $nid, $page_id, $order ) {
		$person = WSUWP_People_Post_Type::get_rest_data( $nid );

		if ( ! $person ) {
			return;
		}

		$tags = array();
		$taxonomy_data = array();

		foreach ( $person->_embedded->{'wp:term'} as $taxonomy ) {
			if ( ! $taxonomy ) {
				continue;
			}

			foreach ( $taxonomy as $term ) {
				if ( 'post_tag' === $term->taxonomy ) {
					$tags[] = $term->slug;
				} else {
					// Slugs and names don't seem to work, so find the equivalent local term ID.
					$local_term = get_term_by( 'slug', $term->slug, $term->taxonomy );
					$taxonomy_data[ $term->taxonomy ][] = $local_term->term_id;
				}
			}
		}

		$person_data = array(
			'post_title' => wp_strip_all_tags( $person->title->rendered ),
			'post_content' => '',
			'post_status' => 'publish',
			'post_type' => WSUWP_People_Post_Type::$post_type_slug,
			'meta_input' => array(
				'_wsuwp_profile_ad_nid' => $nid,
				'_on_page' => $page_id,
				"_order_on_page_{$page_id}" => absint( $order ),
			),
			'tags_input' => $tags,
			'tax_input' => $taxonomy_data,
		);

		wp_insert_post( $person_data );
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
	public function theme_has_template() {
		return locate_template( 'wsu-people-templates/people.php' );
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

		if ( key( self::$template ) !== get_page_template_slug( $post->ID ) ) {
			return $template;
		}

		// If a theme has a directory template, use it.
		if ( $this->theme_has_template() ) {
			return $this->theme_has_template();
		}

		// Enqueue styles and scripts if appropriate.
		if ( 'custom' !== get_post_meta( $post->ID, '_wsu_people_directory_layout', true ) ) {
			wp_enqueue_style( 'wsu-people', plugin_dir_url( dirname( __FILE__ ) ) . 'css/people.css', array(), WSUWP_People_Directory::$version );
			wp_enqueue_script( 'wsu-people', plugin_dir_url( dirname( __FILE__ ) ) . 'js/people.min.js', array( 'jquery' ), WSUWP_People_Directory::$version );
		}

		add_filter( 'the_content', array( $this, 'directory_content' ) );

		return trailingslashit( get_template_directory() ) . 'templates/single.php';
	}

	/**
	 * Display the list of people as defined by the directory page.
	 *
	 * @since 0.3.0
	 *
	 * @param string $content Current post content.
	 *
	 * @return string Modified content.
	 */
	public function directory_content( $content ) {
		remove_filter( 'the_content', array( $this, 'directory_content' ) );

		ob_start();

		include plugin_dir_path( dirname( __FILE__ ) ) . 'templates/people.php';

		$content = ob_get_clean();

		return $content;
	}
}
