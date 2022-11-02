<?php

class WSUWP_People_Area_Of_Focus {


	public static function setup_hooks() {

		add_action( 'init', array( __CLASS__, 'register_area_of_focus_taxonomy' ), 11 );

	}


	public static function register_area_of_focus_taxonomy() {
		$args = array(
			'labels' => array(
				'name' => 'Area of Focus',
				'singular_name' => 'Area of Focus',
				'search_items' => 'Search Area of Focus',
				'all_items' => 'All Area of Focus',
				'edit_item' => 'Edit Area of Focus',
				'update_item' => 'Update Area of Focus',
				'add_new_item' => 'Add New Area of Focus',
				'new_item_name' => 'New Area of Focus Name',
				'menu_name' => 'Area of Focus',
			),
			'description' => 'Area of Focus',
			'public'  => true,
			'hierarchical' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_rest' => true,
		);

		register_taxonomy( 'wsuwp_focus_area', 'wsuwp_people_profile', $args );
	}
}

WSUWP_People_Area_Of_Focus::setup_hooks();
