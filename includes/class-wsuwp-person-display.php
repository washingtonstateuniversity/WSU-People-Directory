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
	 * Adds rewrite rules for person views under each directory page's path.
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
				add_rewrite_rule(
					'^' . $page->post_name . '/([^/]*)/?',
					'index.php?' . WSUWP_People_Post_Type::$post_type_slug . '=$matches[1]',
					'top'
				);
			}
		}
	}

	/**
	 * Changes the permalink structure for a person.
	 *
	 * @since 0.3.0
	 *
	 * @param string $url  The post URL.
	 * @param object $post The post object.
	 *
	 * @return string The modified URL.
	 */
	public function person_permalink( $url, $post ) {
		if ( WSUWP_People_Post_Type::$post_type_slug !== $post->post_type ) {
			return $url;
		}

		$directory_page_id = get_post_meta( $post->ID, 'on_page', true );

		if ( ! $directory_page_id ) {
			return $url;
		}

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
	public static function theme_has_template() {
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

		wp_enqueue_style( 'wsu-people-display', plugin_dir_url( dirname( __FILE__ ) ) . 'css/person.css', array(), WSUWP_People_Directory::$version );

		add_filter( 'the_content', array( $this, 'content' ) );

		return trailingslashit( get_template_directory() ) . 'templates/single.php';
	}

	/**
	 * Filters the content for a person view.
	 *
	 * @since 0.3.0
	 *
	 * @return string Modified content.
	 */
	public function content() {
		remove_filter( 'the_content', array( $this, 'content' ) );

		$local_record_id = get_post()->ID;
		$nid = get_post_meta( $local_record_id, '_wsuwp_profile_ad_nid', true );
		$person = WSUWP_People_Post_Type::get_rest_data( $nid );
		$local_data = $this->get_local_single_view_data( $local_record_id );
		$show_header = false;
		$show_photo = true;
		$lazy_load_photos = false;
		$link = false;
		$use_title = $local_data['use_title'];
		$use_photo = $local_data['use_photo'];
		$use_about = $local_data['use_about'];

		ob_start();

		// If a theme has a person template, use it.
		if ( $this->theme_has_template() ) {
			include $this->theme_has_template();
		} else {
			include plugin_dir_path( dirname( __FILE__ ) ) . 'templates/person.php';
		}

		$content = ob_get_clean();

		return $content;
	}

	/**
	 * Returns a set of data for displaying a person.
	 *
	 * @param string $post_id
	 *
	 * @since 0.3.0
	 *
	 * @return array
	 */
	public static function get_local_single_view_data( $post_id ) {
		$local_data = array(
			'use_photo' => get_post_meta( $post_id, '_use_photo', true ),
			'use_title' => get_post_meta( $post_id, '_use_title', true ),
			'use_about' => get_post_meta( $post_id, '_use_bio', true ),
		);

		return $local_data;
	}
}
