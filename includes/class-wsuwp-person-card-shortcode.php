<?php

class WSUWP_Person_Card_Shortcode {
	/**
	 * @var WSUWP_Person_Card_Shortcode
	 *
	 * @since 0.3.0
	 */
	private static $instance;

	/**
	 * Maintain and return the one instance. Initiate hooks when called the first time.
	 *
	 * @since 0.3.0
	 *
	 * @return \WSUWP_Person_Card_Shortcode
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSUWP_Person_Card_Shortcode();
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
		add_shortcode( 'wsuwp_person_card', array( $this, 'display_wsuwp_person_card' ) );
		add_action( 'register_shortcode_ui', array( $this, 'person_card_shortcode_ui' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Check if a theme is providing its own template.
	 *
	 * @since 0.3.0
	 *
	 * @return string Path to the template file.
	 */
	public function theme_has_template() {
		return locate_template( 'wsu-people/person-card-shortcode.php' );
	}

	/**
	 * Displays a person's card.
	 *
	 * @since 0.3.0
	 */
	public function display_wsuwp_person_card( $atts ) {
		$defaults = array(
			'name' => '',
			'nid' => '',
			'cache_bust' => '',
		);

		$atts = shortcode_atts( $defaults, $atts );

		if ( empty( $atts['nid'] ) ) {
			return '';
		}

		wp_enqueue_style( 'wsu-person-card', plugin_dir_url( dirname( __FILE__ ) ) . 'css/person-card.css', array(), WSUWP_People_Directory::$version );

		$cache_key = md5( wp_json_encode( $atts ) );

		$cached_content = wp_cache_get( $cache_key, 'wsuwp_person_card' );

		if ( $cached_content ) {
			return $cached_content;
		}

		$nid = sanitize_text_field( $atts['nid'] );

		$person = WSUWP_People_Post_Type::get_rest_data( $nid );

		if ( ! $person ) {
			return '';
		}

		$template = ( $this->theme_has_template() ) ? $this->theme_has_template() : plugin_dir_path( dirname( __FILE__ ) ) . 'templates/person-card-shortcode.php';

		$display_options = array(
			'header' => true,
			'link_url' => ( $person->website ) ? $person->website : $person->link,
		);

		$display = WSUWP_Person_Display::get_data( $person, $display_options );

		ob_start();

		require_once $template;

		$content = ob_get_clean();

		wp_cache_set( $cache_key, $content, 'wsuwp_person_card', 1800 );

		return $content;
	}

	/**
	 * Adds Shortcode UI support for the person card shortcode.
	 *
	 * @since 0.3.0
	 */
	public function person_card_shortcode_ui() {
		$args = array(
			'label' => 'Person Card',
			'listItemImage' => 'dashicons-admin-users',
			'post_type' => array( 'post', 'page' ),
			'attrs' => array(
				array(
					'label' => 'Search by name',
					'attr' => 'name',
					'type' => 'text',
					'description' => 'Start typing to search for a person.',
				),
				array(
					'label' => 'Or enter NID',
					'attr' => 'nid',
					'type' => 'text',
					'description' => "If you know the person's nid, you can enter it directly.",
				),
			),
		);

		shortcode_ui_register_for_shortcode( 'wsuwp_person_card', $args );
	}

	/**
	 * Enqueues the scripts used in the admin interface.
	 *
	 * @since 0.3.0
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_script( 'wsuwp-person-card-shortcode', plugins_url( 'js/admin-card-shortcode.min.js', dirname( __FILE__ ) ), array( 'jquery-ui-autocomplete' ), WSUWP_People_Directory::$version, true );
		wp_localize_script( 'wsuwp-person-card-shortcode', 'wsupersoncard', array(
			'rest_url' => WSUWP_People_Directory::REST_URL(),
		) );
	}
}
