<?php

class WSUWP_Person_Display {
	/**
	 * @var WSUWP_Person_Display
	 *
	 * @since 0.3.0
	 */
	private static $instance;

	/**
	 * Maintain and return the one instance. Initiate hooks when called the first time.
	 *
	 * @since 0.3.0
	 *
	 * @return \WSUWP_Person_Display
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSUWP_Person_Display();
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
		add_action( 'init', array( $this, 'rewrite_rules' ), 11 );
		add_filter( 'post_type_link', array( $this, 'person_permalink' ), 10, 2 );
		add_filter( 'template_include', array( $this, 'template_include' ) );
	}

	/**
	 * Add rewrite rules for handling people and person views.
	 *
	 * @since 0.3.0
	 */
	public function rewrite_rules() {
		add_rewrite_tag( '%wsuwp_person%', '([^/]+)', WSUWP_People_Post_Type::$post_type_slug . '=' );
		//add_permastruct( 'person', "/{$slug}/%wsuwp_person%/", false );
	}

	/**
	 * Change the permalink structure for individual people posts.
	 *
	 * @since 0.3.0
	 *
	 * @param string $url  The post URL.
	 * @param object $post The post object.
	 */
	public function person_permalink( $url, $post ) {
		if ( get_post_type( $post ) === WSUWP_People_Post_Type::$post_type_slug ) {
			$options = get_option( 'wsu_people_display' );
			$slug = ( isset( $options['slug'] ) && '' !== $options['slug'] ) ? $options['slug'] : 'people';
			$url = get_site_url() . '/' . $slug . '/' . $post->post_name . '/';
		}

		return $url;
	}

	/**
	 * Assign templates to people and person pages.
	 *
	 * @param string $template The path of the template to include.
	 *
	 * @return string The path of the template to include.
	 */
	public function template_include( $template ) {
		if ( is_singular( WSUWP_People_Post_Type::$post_type_slug ) ) {
			$template = trailingslashit( get_template_directory() ) . 'templates/single.php';

			wp_enqueue_style( 'wsu-people-display', plugin_dir_url( dirname( __FILE__ ) ) . 'css/person.css', array(), WSUWP_People_Directory::$version );

			add_filter( 'the_content', array( $this, 'content' ) );
		}

		return $template;
	}

	/**
	 * Adjust the query for people page requests.
	 *
	 * @since 0.3.0
	 *
	 * @param string $content Current post content.
	 *
	 * @return string Modified content.
	 */
	public function content( $content ) {
		remove_filter( 'the_content', array( $this, 'content' ) );

		ob_start();

		include plugin_dir_path( dirname( __FILE__ ) ) . 'templates/person.php';

		$content = ob_get_clean();

		return $content;
	}
}
