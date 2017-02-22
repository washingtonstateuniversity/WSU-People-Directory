<?php

class WSUWP_People_Classification_Taxonomy {
	/**
	 * @var WSUWP_People_Classification_Taxonomy
	 *
	 * @since 0.3.0
	 */
	private static $instance;

	/**
	 * The slugs used to register the classification taxonomy.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	public static $taxonomy_slug = 'classification';

	/**
	 * Maintain and return the one instance. Initiate hooks when called the first time.
	 *
	 * @since 0.3.0
	 *
	 * @return \WSUWP_People_Classification_Taxonomy
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSUWP_People_Classification_Taxonomy();
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
		add_action( 'init', array( $this, 'register_classification_taxonomy' ), 11 );
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
			'query_var' => self::$taxonomy_slug,
			'show_in_rest' => true,
		);

		register_taxonomy( self::$taxonomy_slug, WSUWP_People_Post_Type::$post_type_slug, $args );
	}
}
