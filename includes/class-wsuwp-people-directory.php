<?php

class WSUWP_People_Directory {
	/**
	 * @var WSUWP_People_Directory
	 */
	private static $instance;

	/**
	 * The plugin version number, used to break caches and trigger
	 * upgrade routines.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	public static $version = '0.2.2';

	/**
	 * Maintain and return the one instance. Initiate hooks when called the first time.
	 *
	 * @since 0.3.0
	 *
	 * @return \WSUWP_People_Directory
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSUWP_People_Directory();
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
		require_once( dirname( __FILE__ ) . '/class-wsuwp-people-post-type.php' );
		require_once( dirname( __FILE__ ) . '/class-wsuwp-people-classification-taxonomy.php' );

		add_action( 'init', 'WSUWP_People_Post_Type' );
		add_action( 'init', 'WSUWP_People_Classification_Taxonomy' );

		if ( apply_filters( 'wsuwp_people_show_in_rest', false ) ) {
			require_once( dirname( __FILE__ ) . '/class-wsuwp-people-rest-api.php' );

			add_action( 'init', 'WSUWP_People_REST_API' );
		}

		if ( apply_filters( 'wsuwp_people_display', true ) ) {
			require_once( dirname( __FILE__ ) . '/class-wsuwp-people-display-settings.php' );
			require_once( dirname( __FILE__ ) . '/class-wsuwp-people-display-frontend.php' );

			add_action( 'init', 'WSUWP_People_Display_Settings' );
			add_action( 'init', 'WSUWP_People_Display_Frontend' );
		}
	}
}
