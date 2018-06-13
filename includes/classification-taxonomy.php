<?php

namespace WSUWP\People_Directory\Classification_Taxonomy;

/**
 * Returns the profile post type slug.
 *
 * @return string The profile post type slug.
 */
function profile_post_type_slug() {
	return \WSUWP\People_Directory\Profile_Post_Type\slug();
}

/**
 * Returns the classification taxonomy slug.
 *
 * @since 0.1.0
 *
 * @return string The classification taxonomy slug.
 */
function slug() {
	return 'classification';
}

/**
 * Returns the taxonomy schema version number. This should be changed whenever
 * a schema change needs to be initiated on any site using the taxonomy.
 *
 * @return string Current version of the taxonomy schema.
 */
function schema_version() {
	return '20180517-001';
}

add_action( 'init', __NAMESPACE__ . '\\register_classification_taxonomy', 11 );
add_action( 'admin_init', __NAMESPACE__ . '\\check_schema', 10 );
add_action( 'load-edit-tags.php', __NAMESPACE__ . '\\compare_schema', 10 );
add_action( 'wsu_people_classifications_update_schema', __NAMESPACE__ . '\\update_schema' );
add_filter( 'pre_insert_term', __NAMESPACE__ . '\\prevent_term_creation', 10, 2 );
add_action( 'load-edit-tags.php', __NAMESPACE__ . '\\display_terms', 11 );
add_filter( 'parent_file', __NAMESPACE__ . '\\parent_file' );
add_filter( 'submenu_file', __NAMESPACE__ . '\\submenu_file', 10, 2 );
add_filter( 'admin_title', __NAMESPACE__ . '\\admin_title', 10, 2 );
add_filter( 'wsuwp_taxonomy_metabox_disable_new_term_adding', __NAMESPACE__ . '\\disable_new_classifications' );

/**
 * Registers the classification taxonomy.
 *
 * @since 0.1.0
 */
function register_classification_taxonomy() {
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
		'query_var' => slug(),
		'show_in_rest' => true,
	);

	register_taxonomy( slug(), profile_post_type_slug(), $args );
}

/**
 * Checks the current version of the taxonomy schema on every admin page load.
 * If it is out of date, fire a single wp-cron event to process the changes.
 *
 * @since 0.3.15
 */
function check_schema() {
	if ( get_option( 'wsu_people_classificatons_schema', false ) !== schema_version() ) {
		wp_schedule_single_event( time() + 60, 'wsu_people_classifications_update_schema' );
	}
}

/**
 * Updates the taxonomy schema and version.
 *
 * @since 0.3.15
 */
function update_schema() {
	load_terms( slug() );

	update_option( 'wsu_people_classificatons_schema', schema_version() );
}

/**
 * Compares the existing schema version on taxonomy page loads
 * and run the update process if a mismatch is present.
 *
 * @since 0.3.15
 */
function compare_schema() {
	if ( get_current_screen()->taxonomy !== slug() ) {
		return;
	}

	if ( get_option( 'wsu_people_classificatons_schema', false ) !== schema_version() ) {
		update_schema();
	}
}

/**
 * Maintains an array of current classifications.
 *
 * @since 0.3.15
 *
 * @return array Current classifications.
 */
function get_classifications() {
	return array(
		'Adjunct',
		'Administrative Professional',
		'Affiliate',
		'Emeritus',
		'Faculty',
		'Graduate Assistant',
		'Hourly',
		'Affiliate',
		'Staff',
	);
}

/**
 * Ensures all of the pre-configured terms for the taxonomy are loaded.
 *
 * @since 0.3.15
 *
 * @param string $taxonomy Taxonomy being loaded.
 */
function load_terms( $taxonomy ) {

	clear_taxonomy_cache( $taxonomy );

	// Get our current list of top level parents.
	$existing_terms = get_terms( $taxonomy, array(
		'hide_empty' => false,
		'parent' => '0',
		'fields' => 'names',
	) );

	remove_filter( 'pre_insert_term', __NAMESPACE__ . '\\prevent_term_creation', 10 );

	// Look for mismatches between the static list we maintain and the existing terms list.
	foreach ( get_classifications() as $term_name ) {
		if ( ! array_key_exists( $term_name, $existing_terms ) ) {
			$new_term = wp_insert_term( $term_name, $taxonomy, array(
				'parent' => '0',
			) );

			if ( ! is_wp_error( $new_term ) ) {
				$existing_terms[] = $term_name;
			}
		}
	}

	add_filter( 'pre_insert_term', __NAMESPACE__ . '\\prevent_term_creation', 10 );

	clear_taxonomy_cache( $taxonomy );
}

/**
 * Prevents new terms being created for the classifications taxonomy.
 *
 * @since 0.3.15
 *
 * @param string $term     Term being added.
 * @param string $taxonomy Taxonomy of the term being added.
 *
 * @return string|WP_Error Untouched term if not the classification taxonomy, WP_Error otherwise.
 */
function prevent_term_creation( $term, $taxonomy ) {
	if ( slug() === $taxonomy ) {
		$term = new WP_Error( 'invalid_term', 'These terms cannot be modified.' );
	}

	return $term;
}

/**
 * Clears all cache for a given taxonomy.
 *
 * @since 0.3.15
 *
 * @param string $taxonomy A taxonomy slug.
 */
function clear_taxonomy_cache( $taxonomy ) {
	wp_cache_delete( 'all_ids', $taxonomy );
	wp_cache_delete( 'get', $taxonomy );
	_get_term_hierarchy( $taxonomy );
}

/**
 * Displays custom output for the classifications dashboard page.
 *
 * @since 0.3.15
 */
function display_terms() {
	if ( get_current_screen()->taxonomy !== slug() ) {
		return;
	}

	$taxonomy = get_taxonomy( slug() );

	require_once ABSPATH . 'wp-admin/admin-header.php';

	?>
	<div class="wrap nosubsub">

		<h2><?php echo esc_html( $taxonomy->labels->name ); ?></h2>

		<table class="wp-list-table widefat striped">
			<thead>
				<tr>
					<th scope="col">Name</th>
					<th scope="col">Slug</th>
					<th scope="col">Count</th>
				</tr>
			</thead>
			<tbody>
			<?php
			$terms = get_terms( slug(), array(
				'hide_empty' => false,
				'parent' => '0',
			) );

			foreach ( $terms as $term ) {
				$edit_link = add_query_arg( array(
					slug() => $term->slug,
					'post_type' => profile_post_type_slug(),
				), get_admin_url( null, 'edit.php' ) );
				?>
				<tr>
					<td><strong><?php echo esc_html( $term->name ); ?></strong></td>
					<td><?php echo esc_html( $term->slug ); ?></td>
					<td><a href="<?php echo esc_url( $edit_link ); ?>"><?php echo esc_html( $term->count ); ?></a></td>
				</tr>
				<?php
			}
			?>
			</tbody>
			<tfoot>
				<tr>
					<th scope="col">Name</th>
					<th scope="col">Slug</th>
					<th scope="col">Count</th>
				</tr>
			</tfoot>
		</table>

	</div>

	<?php
	include ABSPATH . 'wp-admin/admin-footer.php';

	die();
}

/**
 * Sets the active parent menu item for the classifications dashboard page.
 * (Using the `load-edit-tags.php` hook prevents the default handling.)
 *
 * @since 0.3.15
 *
 * @param string $parent_file The parent file.
 *
 * @return string
 */
function parent_file( $parent_file ) {
	if ( get_current_screen()->id !== 'edit-' . slug() ) {
		return $parent_file;
	}

	$parent_file .= 'edit.php?post_type=' . profile_post_type_slug();

	return $parent_file;
}

/**
 * Sets the active menu item for the classifications dashboard page.
 * (Using the `load-edit-tags.php` hook prevents the default handling.)
 *
 * @since 0.3.15
 *
 * @param string $submenu_file The submenu file.
 * @param string $parent_file  The parent file.
 *
 * @return string
 */
function submenu_file( $submenu_file, $parent_file ) {
	if ( get_current_screen()->id !== 'edit-' . slug() ) {
		return $submenu_file;
	}

	$submenu_file = 'edit-tags.php?taxonomy=' . slug() . '&amp;post_type=' . profile_post_type_slug();

	return $submenu_file;
}

/**
 * Filters the title tag content for the classifications dashboard page.
 * (Using the `load-edit-tags.php` hook prevents the default handling.)
 *
 * @since 0.3.15
 *
 * @param string $admin_title The page title, with extra context added.
 * @param string $title  The original page title.
 *
 * @return string
 */
function admin_title( $admin_title, $title ) {
	if ( get_current_screen()->id !== 'edit-' . slug() ) {
		return $admin_title;
	}

	$admin_title = 'Classifications ' . $admin_title;

	return $admin_title;
}

/**
 * Disables the interface for adding new terms to the classifications taxonomy.
 *
 * @since 0.3.9
 *
 * @param array $taxonomies Taxonomies for which to disable the interface for adding new terms.
 *
 * @return array
 */
function disable_new_classifications( $taxonomies ) {
	$taxonomies[] = slug();

	return $taxonomies;
}
