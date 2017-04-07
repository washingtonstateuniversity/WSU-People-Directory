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
	}

	/**
	 * Displays a person's card.
	 *
	 * @since 0.3.0
	 */
	public function display_wsuwp_person_card( $atts ) {
		$defaults = array(
			'nid' => '',
			'cache_bust' => '',
		);

		$atts = shortcode_atts( $defaults, $atts );

		if ( empty( $atts['nid'] ) ) {
			return '';
		}

		$cache_key = md5( wp_json_encode( $atts ) );

		$cached_content = wp_cache_get( $cache_key, 'wsuwp_person_card' );

		if ( $cached_content ) {
			return $cached_content;
		}

		$nid = sanitize_text_field( $atts['nid'] );
		$profile = true;

		ob_start();

		require_once( plugin_dir_path( dirname( __FILE__ ) ) . 'templates/person.php' );

		$content = ob_get_clean();

		wp_cache_set( $cache_key, $content, 'wsuwp_person_card', 1800 );

		return $content;
	}
}
