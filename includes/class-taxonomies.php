<?php

class WSUWP_People_Taxonomies {
	/**
	 * @var WSUWP_People_Taxonomies
	 *
	 * @since 0.3.0
	 */
	private static $instance;

	/**
	 * Maintain and return the one instance. Initiate hooks when called the first time.
	 *
	 * @since 0.3.0
	 *
	 * @return \WSUWP_People_Taxonomies
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSUWP_People_Taxonomies();
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
		add_action( 'init', array( $this, 'register_appointment_taxonomy' ), 11 );
		add_action( 'init', array( $this, 'register_classification_taxonomy' ), 11 );
		add_action( 'init', array( $this, 'register_taxonomies_for_people' ), 12 );

		add_filter( 'manage_taxonomies_for_' . WSUWP_People::$post_type_slug . '_columns', array( $this, 'manage_people_taxonomy_columns' ) );
	}

	/**
	 * Register the Appointment taxonomy.
	 *
	 * @since 0.1.0
	 */
	public function register_appointment_taxonomy() {
		$args = array(
			'labels' => array(
				'name' => 'Appointments',
				'singular_name' => 'Appointment',
				'search_items' => 'Search Appointments',
				'all_items' => 'All Appointments',
				'edit_item' => 'Edit Appointment',
				'update_item' => 'Update Appointment',
				'add_new_item' => 'Add New Appointment',
				'new_item_name' => 'New Appointment Name',
				'menu_name' => 'Appointments',
			),
			'description' => 'Personnel Appointments',
			'public' => true,
			'hierarchical' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'query_var' => WSUWP_People::$taxonomy_slug_appointments,
			'show_in_rest' => true,
		);

		register_taxonomy( WSUWP_People::$taxonomy_slug_appointments, WSUWP_People::$post_type_slug, $args );
	}

	/**
	 * Register the Classification taxonomy.
	 *
	 * @since 0.1.0
	 */
	public function register_classification_taxonomy() {
		$args = array(
			'labels' => array(
				'name' => 'Classifications',
				'singular_name' => 'Classification',
				'search_items' => 'Search Classifications',
				'all_items' => 'All Classifications',
				'edit_item' => 'Edit Classification',
				'update_item' => 'Update Classification',
				'add_new_item' => 'Add New Classification',
				'new_item_name' => 'New Classification Name',
				'menu_name' => 'Classifications',
			),
			'description' => 'Personnel Classifications',
			'public'  => true,
			'hierarchical' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'query_var' => WSUWP_People::$taxonomy_slug_classifications,
			'show_in_rest' => true,
		);

		register_taxonomy( WSUWP_People::$taxonomy_slug_classifications, WSUWP_People::$post_type_slug, $args );
	}

	/**
	 * Add support for WSU University Taxonomies to the People post type.
	 *
	 * @since 0.1.0
	 */
	public function register_taxonomies_for_people() {
		register_taxonomy_for_object_type( 'wsuwp_university_category', WSUWP_People::$post_type_slug );
		register_taxonomy_for_object_type( 'wsuwp_university_location', WSUWP_People::$post_type_slug );
		register_taxonomy_for_object_type( 'wsuwp_university_org', WSUWP_People::$post_type_slug );
	}

	/**
	 * Modify taxonomy columns on the "All Profiles" screen.
	 *
	 * @since 0.1.0
	 *
	 * @param array $columns Default columns on the "All Profiles" screen.
	 *
	 * @return array
	 */
	public function manage_people_taxonomy_columns( $columns ) {
		$columns[] = 'wsuwp_university_org';
		$columns[] = 'wsuwp_university_location';

		return $columns;
	}
}
