<?php

class WSUWP_People_Classification_Taxonomy {
	/**
	 * @var WSUWP_People_Classification_Taxonomy
	 *
	 * @since 0.3.0
	 */
	private static $instance;

	/**
	 * Maintain a record of the taxonomy schema. This should be changed whenever
	 * a schema change should be initiated on any site using the taxonomy.
	 *
	 * @var string Current version of the taxonomy schema.
	 */
	public static $taxonomy_schema_version = '20180517-001';

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
		add_action( 'admin_init', array( $this, 'check_schema' ), 10 );
		add_action( 'load-edit-tags.php', array( $this, 'compare_schema' ), 10 );
		add_action( 'wsu_people_classifications_update_schema', array( $this, 'update_schema' ) );
		add_filter( 'pre_insert_term', array( $this, 'prevent_term_creation' ), 10, 2 );
		add_action( 'load-edit-tags.php', array( $this, 'display_terms' ), 11 );
		add_filter( 'parent_file', array( $this, 'parent_file' ) );
		add_filter( 'submenu_file', array( $this, 'submenu_file' ), 10, 2 );
		add_filter( 'admin_title', array( $this, 'admin_title' ), 10, 2 );
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

	/**
	 * Check the current version of the taxonomy schema on every admin page load.
	 * If it is out of date, fire a single wp-cron event to process the changes.
	 *
	 * @since 0.3.15
	 */
	public function check_schema() {
		if ( get_option( 'wsu_people_classificatons_schema', false ) !== self::$taxonomy_schema_version ) {
			wp_schedule_single_event( time() + 60, 'wsu_people_classifications_update_schema' );
		}
	}

	/**
	 * Update the taxonomy schema and version.
	 *
	 * @since 0.3.15
	 */
	public function update_schema() {
		$this->load_terms( self::$taxonomy_slug );

		update_option( 'wsu_people_classificatons_schema', self::$taxonomy_schema_version );
	}

	/**
	 * Compare the existing schema version on taxonomy page loads
	 * and run the update process if a mismatch is present.
	 *
	 * @since 0.3.15
	 */
	public function compare_schema() {
		if ( get_current_screen()->taxonomy !== self::$taxonomy_slug ) {
			return;
		}

		if ( get_option( 'wsu_people_classificatons_schema', false ) !== self::$taxonomy_schema_version ) {
			$this->update_schema();
		}
	}

	/**
	 * Maintain an array of current classifications.
	 *
	 * @since 0.3.15
	 *
	 * @return array Current classifications.
	 */
	public function get_classifications() {
		return array(
			'Adjunct',
			'Administrative Professional',
			'Affiliate',
			'Emeritus',
			'Faculty',
			'Graduate Assistant',
			'Hourly',
			'Public Affiliate',
			'Staff',
		);
	}

	/**
	 * Ensure all of the pre-configured terms for the taxonomy are loaded.
	 *
	 * @since 0.3.15
	 *
	 * @param string $taxonomy Taxonomy being loaded.
	 */
	public function load_terms( $taxonomy ) {

		$this->clear_taxonomy_cache( $taxonomy );

		// Get our current list of top level parents.
		$existing_terms = get_terms( $taxonomy, array(
			'hide_empty' => false,
			'parent' => '0',
			'fields' => 'names',
		) );

		remove_filter( 'pre_insert_term', array( $this, 'prevent_term_creation' ), 10 );

		// Look for mismatches between the static list we maintain and the existing terms list.
		foreach ( $this->get_classifications() as $term_name ) {
			if ( ! array_key_exists( $term_name, $existing_terms ) ) {
				$new_term = wp_insert_term( $term_name, $taxonomy, array(
					'parent' => '0',
				) );

				if ( ! is_wp_error( $new_term ) ) {
					$existing_terms[] = $term_name;
				}
			}
		}

		add_filter( 'pre_insert_term', array( $this, 'prevent_term_creation' ), 10 );

		$this->clear_taxonomy_cache( $taxonomy );
	}

	/**
	 * Prevent new terms being created for the classifications taxonomy.
	 *
	 * @since 0.3.15
	 *
	 * @param string $term     Term being added.
	 * @param string $taxonomy Taxonomy of the term being added.
	 *
	 * @return string|WP_Error Untouched term if not the classification taxonomy, WP_Error otherwise.
	 */
	public function prevent_term_creation( $term, $taxonomy ) {
		if ( self::$taxonomy_slug === $taxonomy ) {
			$term = new WP_Error( 'invalid_term', 'These terms cannot be modified.' );
		}

		return $term;
	}

	/**
	 * Clear all cache for a given taxonomy.
	 *
	 * @since 0.3.15
	 *
	 * @param string $taxonomy A taxonomy slug.
	 */
	private function clear_taxonomy_cache( $taxonomy ) {
		wp_cache_delete( 'all_ids', $taxonomy );
		wp_cache_delete( 'get', $taxonomy );
		_get_term_hierarchy( $taxonomy );
	}

	/**
	 * Display custom output for the classifications dashboard page.
	 *
	 * @since 0.3.15
	 */
	public function display_terms() {
		if ( get_current_screen()->taxonomy !== self::$taxonomy_slug ) {
			return;
		}

		$taxonomy = get_taxonomy( self::$taxonomy_slug );

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
				$terms = get_terms( self::$taxonomy_slug, array(
					'hide_empty' => false,
					'parent' => '0',
				) );

				foreach ( $terms as $term ) {
					$edit_link = add_query_arg( array(
						self::$taxonomy_slug => $term->slug,
						'post_type' => WSUWP_People_Post_Type::$post_type_slug,
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
	public function parent_file( $parent_file ) {
		if ( get_current_screen()->id !== 'edit-' . self::$taxonomy_slug ) {
			return $parent_file;
		}

		$parent_file .= 'edit.php?post_type=' . WSUWP_People_Post_Type::$post_type_slug;

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
	public function submenu_file( $submenu_file, $parent_file ) {
		if ( get_current_screen()->id !== 'edit-' . self::$taxonomy_slug ) {
			return $submenu_file;
		}

		$submenu_file = 'edit-tags.php?taxonomy=' . self::$taxonomy_slug . '&amp;post_type=' . WSUWP_People_Post_Type::$post_type_slug;

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
	public function admin_title( $admin_title, $title ) {
		if ( get_current_screen()->id !== 'edit-' . self::$taxonomy_slug ) {
			return $admin_title;
		}

		$admin_title = 'Classifications ' . $admin_title;

		return $admin_title;
	}
}
