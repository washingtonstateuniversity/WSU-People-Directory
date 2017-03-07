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
	var $template = array( 'templates/people.php' => 'People Directory' );

	/**
	 * A list of post meta keys associated with a directory page.
	 *
	 * @since 0.3.0
	 *
	 * @var array
	 */
	var $post_meta_keys = array(
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
			'type' => 'string',
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

		if ( 'page' !== $screen->post_type && ( 'post-new.php' !== $hook_suffix || 'post.php' !== $hook_suffix ) ) {
			return;
		}

		wp_enqueue_style( 'wsuwp-people', plugins_url( 'css/people.css', dirname( __FILE__ ) ), array(), WSUWP_People_Directory::$version );
		wp_enqueue_style( 'wsuwp-people-admin', plugins_url( 'css/admin-page.css', dirname( __FILE__ ) ), array(), WSUWP_People_Directory::$version );
		wp_enqueue_script( 'wsuwp-people-admin', plugins_url( 'js/admin-page.min.js', dirname( __FILE__ ) ), array( 'jquery', 'underscore' ), WSUWP_People_Directory::$version, true );
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
				<input type="text"
					   id="wsu-people-import"
					   name="_wsu_people_directory_nids"
					   value="<?php echo esc_attr( $nids ); ?>" />
			</p>

			<p>
				<label for="wsu-people-directory-layout">Layout</label>
				<select id="wsu-people-directory-layout" name="_wsu_people_directory_layout">
					<option value="table">Table</option>
					<option value="grid">Grid</option>
					<option value="custom">Custom (provide your own CSS)</option>
				</select>
			</p>

			<p>
				<label for="wsu-people-directory-link">Link to full profiles</label>
				<select id="wsu-people-directory-link" name="_wsu_people_directory_link">
					<option value="if_bio">If the person has a biography</option>
					<option value="yes">Yes</option>
					<option value="no">No</option>
				</select>
			</p>

			<p>
				<label for="wsu-people-directory-profile">Open full profiles in a</label>
				<select id="wsu-people-directory-profile" name="_wsu_people_directory_profile">
					<option value="page">Page</option>
					<option value="lightbox">Lightbox</option>
				</select>
			</p>

			<p>
				<label for="wsu-people-directory-show-photos">Show photos</label>
				<input type="checkbox" id="wsu-people-directory-show-photos" name="_wsu_people_directory_show_photos" value="1">
			</p>

			<script type="text/template" id="wsu-person-template">

				<article class="wsu-person<%= has_photo %>">

					<div class="wsu-person-controls">

						<label>
							<span class="screen-reader-text">Select for bulk action</span>
							<input type="checkbox">
						</label>

						<a class="wsu-person-edit">Edit</a>

						<a class="wsu-person-remove">Remove</a>

					</div>

					<div class="card">

						<h2 class="name">
							<a href="<%= link %>"><%= name %></a>
						</h2>

						<a class="photo" href="<%= link %>">
							<img src="https://people.wsu.edu/wp-content/uploads/sites/908/2015/07/HeadShot_Template2.jpg"
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
			<div class="<?php echo esc_html( implode( ' ', $wrapper_classes ) ); ?>">

				<div class="wsu-people">
					<?php
						// Draggable/droppable people listing
					?>
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

		$keys = get_registered_meta_keys( 'post' );

		foreach ( $keys as $key => $args ) {
			if ( isset( $_POST[ $key ] ) && isset( $args['sanitize_callback'] ) ) {

				// Each piece of meta is registered with sanitization.
				update_post_meta( $post_id, $key, $_POST[ $key ] );
			}
		}
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
		$posts_templates = array_merge( $posts_templates, $this->template );

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

		if ( key( $this->template ) !== get_page_template_slug( $post->ID ) ) {
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
