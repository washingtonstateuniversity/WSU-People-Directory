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
	 * Add rewrite rules for person views.
	 *
	 * @since 0.3.0
	 */
	public function rewrite_rules() {
		$pages = get_posts( array(
			'post_type' => 'page',
			'meta_key' => '_wp_page_template',
			'meta_value' => key( WSUWP_People_Directory_Page_Template::$template ),
		) );

		if ( $pages ) {
			foreach ( $pages as $page ) {
				$slug = str_replace( trailingslashit( get_home_url() ), '', get_permalink( $page->ID ) );

				add_rewrite_rule(
					'^' . $slug . '([^/]*)/?',
					'index.php?' . WSUWP_People_Post_Type::$post_type_slug . '=$matches[1]',
					'top'
				);
			}
		}
	}

	/**
	 * Change the permalink structure for a person.
	 *
	 * @since 0.3.0
	 *
	 * @param string $url  The post URL.
	 * @param object $post The post object.
	 */
	public function person_permalink( $url, $post ) {
		if ( get_post_type( $post ) !== WSUWP_People_Post_Type::$post_type_slug ) {
			return $url;
		}

		$directory_page_id = get_post_meta( $post->ID, 'on_page', true );

		$url = get_permalink( $directory_page_id ) . $post->post_name . '/';

		return $url;
	}

	/**
	 * Check if a theme is providing its own person template.
	 *
	 * @since 0.3.0
	 *
	 * @return string Path to the template file.
	 */
	public function theme_has_template() {
		return locate_template( 'wsu-people-templates/person.php' );
	}

	/**
	 * Determine which template to use.
	 *
	 * If using the plugin defaults, enqueue a stylesheet and filter the content.
	 *
	 * @since 0.3.0
	 *
	 * @param string $template The path of the template to include.
	 *
	 * @return string The path of the template to include.
	 */
	public function template_include( $template ) {
		if ( ! is_singular( WSUWP_People_Post_Type::$post_type_slug ) ) {
			return $template;
		}

		// If a theme has a person template, use it.
		if ( $this->theme_has_template() ) {
			return $this->theme_has_template();
		}

		wp_enqueue_style( 'wsu-people-display', plugin_dir_url( dirname( __FILE__ ) ) . 'css/person.css', array(), WSUWP_People_Directory::$version );

		add_filter( 'the_content', array( $this, 'content' ) );

		return trailingslashit( get_template_directory() ) . 'templates/single.php';
	}

	/**
	 * Filter the content for a person view.
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
