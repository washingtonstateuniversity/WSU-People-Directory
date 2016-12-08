<?php
/*
Plugin Name: WSU People Directory
Plugin URI: https://web.wsu.edu/wordpress/plugins/wsu-people-directory/
Description: A plugin to maintain a central directory of people.
Author:	washingtonstateuniversity, CAHNRS, philcable, danialbleile, jeremyfelt
Version: 0.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

class WSUWP_People_Directory {

	/**
	 * The plugin version number, used to break caches and trigger
	 * upgrade routines.
	 *
	 * @var string
	 */
	var $personnel_plugin_version = '0.2.1';

	/**
	 * The slug used to register the "Personnel" custom content type.
	 *
	 * @var string
	 */
	var $personnel_content_type = 'wsuwp_people_profile';

	/**
	 * The slugs used to register the 'Personnel" taxonomies.
	 *
	 * @var string
	 */
	var $personnel_appointments = 'appointment';
	var $personnel_classifications = 'classification';

	/**
	 * Fields used to store Active Directory data as meta for a person.
	 *
	 * @var array
	 */
	var $ad_fields = array(
		'_wsuwp_profile_ad_nid',
		'_wsuwp_profile_ad_name_first',
		'_wsuwp_profile_ad_name_last',
		'_wsuwp_profile_ad_title',
		'_wsuwp_profile_ad_office',
		'_wsuwp_profile_ad_address',
		'_wsuwp_profile_ad_phone',
		'_wsuwp_profile_ad_phone_ext',
		'_wsuwp_profile_ad_email',
	);

	/**
	 * Fields used to store additional profile information as meta for a person.
	 *
	 * @var array
	 */
	var $basic_fields = array(
		'_wsuwp_profile_alt_office',
		'_wsuwp_profile_alt_phone',
		'_wsuwp_profile_alt_email',
		'_wsuwp_profile_website',
		'_wsuwp_profile_cv',
	);

	/**
	 * Fields use to store data with multiple values as meta for a person.
	 *
	 * @var array
	 */
	var $repeatable_fields = array(
		'_wsuwp_profile_degree',
		'_wsuwp_profile_title',
	);

	/**
	 * WP editors for biographies.
	 */
	var $wp_bio_editors = array(
		'_wsuwp_profile_bio_college',
		'_wsuwp_profile_bio_dept',
		'_wsuwp_profile_bio_lab',
	);

	/**
	 * WP editors for C.V.
	 */
	var $wp_cv_editors = array(
		'_wsuwp_profile_employment',
		'_wsuwp_profile_honors',
		'_wsuwp_profile_grants',
		'_wsuwp_profile_publications',
		'_wsuwp_profile_presentations',
		'_wsuwp_profile_teaching',
		'_wsuwp_profile_service',
		'_wsuwp_profile_responsibilities',
		'_wsuwp_profile_societies',
		'_wsuwp_profile_experience',
		/*'_wsuwp_profile_research',
		'_wsuwp_profile_extension',*/
	);

	/**
	 * Additional fields that we add to REST API responses requesting people directory
	 * information. Each key includes the meta key used to store the data in post meta
	 * and the sanitization method used in the `get_callback` when we register the
	 * field with the API.
	 *
	 * @since 0.2.0
	 *
	 * @var array
	 */
	var $rest_response_fields = array(
		'nid' => array(
			'meta_key' => '_wsuwp_profile_ad_nid',
			'sanitize' => 'esc_html',
		),
		'first_name' => array(
			'meta_key' => '_wsuwp_profile_ad_name_first',
			'sanitize' => 'esc_html',
		),
		'last_name' => array(
			'meta_key' => '_wsuwp_profile_ad_name_last',
			'sanitize' => 'esc_html',
		),
		'position_title' => array(
			'meta_key' => '_wsuwp_profile_ad_title',
			'sanitize' => 'esc_html',
		),
		'office' => array(
			'meta_key' => '_wsuwp_profile_ad_office',
			'sanitize' => 'esc_html',
		),
		'address' => array(
			'meta_key' => '_wsuwp_profile_ad_address',
			'sanitize' => 'esc_html',
		),
		'phone' => array(
			'meta_key' => '_wsuwp_profile_ad_phone',
			'sanitize' => 'esc_html',
		),
		'phone_ext' => array(
			'meta_key' => '_wsuwp_profile_ad_phone_ext',
			'sanitize' => 'esc_html',
		),
		'email' => array(
			'meta_key' => '_wsuwp_profile_ad_email',
			'sanitize' => 'esc_html',
		),
		'office_alt' => array(
			'meta_key' => '_wsuwp_profile_alt_office',
			'sanitize' => 'esc_html',
		),
		'phone_alt' => array(
			'meta_key' => '_wsuwp_profile_alt_phone',
			'sanitize' => 'esc_html',
		),
		'email_alt' => array(
			'meta_key' => '_wsuwp_profile_alt_email',
			'sanitize' => 'esc_html',
		),
		'website' => array(
			'meta_key' => '_wsuwp_profile_website',
			'sanitize' => 'esc_url',
		),
		'bio_college' => array(
			'meta_key' => '_wsuwp_profile_bio_college',
			'sanitize' => 'the_content',
		),
		'bio_lab' => array(
			'meta_key' => '_wsuwp_profile_bio_lab',
			'sanitize' => 'the_content',
		),
		'bio_department' => array(
			'meta_key' => '_wsuwp_profile_bio_dept',
			'sanitize' => 'the_content',
		),
		'cv_employment' => array(
			'meta_key' => '_wsuwp_profile_employment',
			'sanitize' => 'the_content',
		),
		'cv_honors' => array(
			'meta_key' => '_wsuwp_profile_honors',
			'sanitize' => 'the_content',
		),
		'cv_grants' => array(
			'meta_key' => '_wsuwp_profile_grants',
			'sanitize' => 'the_content',
		),
		'cv_publications' => array(
			'meta_key' => '_wsuwp_profile_publications',
			'sanitize' => 'the_content',
		),
		'cv_presentations' => array(
			'meta_key' => '_wsuwp_profile_presentations',
			'sanitize' => 'the_content',
		),
		'cv_teaching' => array(
			'meta_key' => '_wsuwp_profile_teaching',
			'sanitize' => 'the_content',
		),
		'cv_service' => array(
			'meta_key' => '_wsuwp_profile_service',
			'sanitize' => 'the_content',
		),
		'cv_responsibilities' => array(
			'meta_key' => '_wsuwp_profile_responsibilities',
			'sanitize' => 'the_content',
		),
		'cv_affiliations' => array(
			'meta_key' => '_wsuwp_profile_societies',
			'sanitize' => 'the_content',
		),
		'cv_experience' => array(
			'meta_key' => '_wsuwp_profile_experience',
			'sanitize' => 'the_content',
		),
		'working_titles' => array(
			'meta_key' => '_wsuwp_profile_title',
			'sanitize' => 'esc_html_map',
		),
		'degrees' => array(
			'meta_key' => '_wsuwp_profile_degree',
			'sanitize' => 'esc_html_map',
		),
		'cv_attachment' => array(
			'meta_key' => '_wsuwp_profile_cv',
			'sanitize' => 'custom',
		),
		'profile_photo' => array(
			'meta_key' => '',
			'sanitize' => 'custom',
		),
	);

	/**
	 * Start the plugin and apply associated hooks.
	 */
	public function __construct() {
		// Custom content type and taxonomies.
		add_action( 'init', array( $this, 'register_personnel_content_type' ), 11 );
		add_action( 'init', array( $this, 'register_taxonomies' ), 11 );
		add_action( 'init', array( $this, 'add_taxonomies' ), 12 );
		add_action( 'init', array( $this, 'image_sizes' ) );

		// Custom meta and all that.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_filter( 'enter_title_here', array( $this, 'enter_title_here' ) );
		add_action( 'edit_form_after_title', array( $this, 'edit_form_after_title' ) );
		add_action( 'edit_form_after_editor',	array( $this, 'edit_form_after_editor' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 1 );
		add_action( 'do_meta_boxes', array( $this, 'do_meta_boxes' ), 10, 3 );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
		add_filter( 'wp_post_revision_meta_keys', array( $this, 'add_meta_keys_to_revision' ) );

		// Modify taxonomy columns on "All Profiles" page.
		add_filter( 'manage_taxonomies_for_wsuwp_people_profile_columns', array( $this, 'wsuwp_people_profile_columns' ) );

		// Allow REST get_items() queries by additional data.
		add_action( 'init', array( $this, 'register_wsu_nid_query_var' ) );
		add_filter( "rest_{$this->personnel_content_type}_query", array( $this, 'rest_query_vars' ), 10, 2 );
		add_action( 'pre_get_posts', array( $this, 'handle_wsu_nid_query_var' ) );

		// Register custom fields with the REST API.
		add_action( 'rest_api_init', array( $this, 'register_api_fields' ) );

		// Capabilities and related.
		add_filter( 'user_has_cap', array( $this, 'user_has_cap' ), 10, 3 );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 999 );
		add_action( 'wp_dashboard_setup', array( $this, 'wp_dashboard_setup' ) );
		add_action( 'pre_get_posts', array( $this, 'limit_media_library' ) );
		//add_action( 'views_edit-' . $this->personnel_content_type, array( $this, 'edit_views' ) );
		//add_filter( 'parse_query', array ( $this, 'parse_query' ) );

		// Modify 'wsuwp_people_profile' content type query.
		add_action( 'pre_get_posts', array( $this, 'profile_archives' ) );

		// Handle ajax requests from the admin.
		add_action( 'wp_ajax_wsu_people_get_data_by_nid', array( $this, 'ajax_get_data_by_nid' ) );
		add_action( 'wp_ajax_wsu_people_confirm_nid_data', array( $this, 'ajax_confirm_nid_data' ) );
	}

	/**
	 * Register the profiles content type.
	 */
	public function register_personnel_content_type() {
		$args = array(
			'labels' => array(
				'name' => 'Profiles',
				'singular_name' => 'Profile',
				'all_items' => 'All Profiles',
				'view_item' => 'View Profile',
				'add_new_item' => 'Add New Profile',
				'add_new' => 'Add New',
				'edit_item' => 'Edit Profile',
				'update_item' => 'Update Profile',
				'search_items' => 'Search Profiles',
				'not_found' => 'Not found',
				'not_found_in_trash' => 'Not found in Trash',
			),
			'description' => 'WSU people directory listings',
			'public' => true,
			'hierarchical' => false,
			'menu_position' => 5,
			'menu_icon' => 'dashicons-groups',
			'supports' => array(
				'title',
				'editor',
				'thumbnail',
				'revisions',
				'author',
			),
			'taxonomies' => array(
				'post_tag',
			),
			'has_archive' => true,
			'rewrite' => array(
				'slug' => 'profile',
				'with_front' => false
			),
			'show_in_rest' => true,
			'rest_base' => 'people',
		);

		register_post_type( $this->personnel_content_type, $args );
	}

	/**
	 * Register a couple taxonomies.
	 */
	public function register_taxonomies() {

		$appointments = array(
			'labels'            => array(
				'name'          => 'Appointments',
				'singular_name' => 'Appointment',
				'search_items'  => 'Search Appointments',
				'all_items'     => 'All Appointments',
				'edit_item'     => 'Edit Appointment',
				'update_item'   => 'Update Appointment',
				'add_new_item'  => 'Add New Appointment',
				'new_item_name' => 'New Appointment Name',
				'menu_name'     => 'Appointments',
			),
			'description'  => 'Personnel Appointments',
			'public'       => true,
			'hierarchical' => true,
			'show_ui'      => true,
			'show_in_menu' => true,
			'query_var'    => $this->personnel_appointments,
			'show_in_rest' => true,
		);
		register_taxonomy( $this->personnel_appointments, $this->personnel_content_type, $appointments );

		$classifications = array(
			'labels'        => array(
				'name'          => 'Classifications',
				'singular_name' => 'Classification',
				'search_items'  => 'Search Classifications',
				'all_items'     => 'All Classifications',
				'edit_item'     => 'Edit Classification',
				'update_item'   => 'Update Classification',
				'add_new_item'  => 'Add New Classification',
				'new_item_name' => 'New Classification Name',
				'menu_name'     => 'Classifications',
			),
			'description'   => 'Personnel Classifications',
			'public'        => true,
			'hierarchical'  => true,
			'show_ui'       => true,
			'show_in_menu'  => true,
			'query_var'     => $this->personnel_classifications,
			'show_in_rest'  => true,
		);
		register_taxonomy( $this->personnel_classifications, $this->personnel_content_type, $classifications );

	}

	/**
	 * Add support for WSU University Taxonomies.
	 */
	public function add_taxonomies() {
		register_taxonomy_for_object_type( 'wsuwp_university_category', $this->personnel_content_type );
		register_taxonomy_for_object_type( 'wsuwp_university_location', $this->personnel_content_type );
		register_taxonomy_for_object_type( 'wsuwp_university_org', $this->personnel_content_type );
	}

	/**
	 * Remove some images sizes.
	 */
	public function image_sizes() {
		remove_image_size( 'spine-small_size' );
		remove_image_size( 'spine-large_size' );
		remove_image_size( 'spine-xlarge_size' );
	}

	/**
	 * Enqueue the scripts and styles used in the admin interface.
	 */
	public function admin_enqueue_scripts( $hook ) {
		$screen = get_current_screen();

		if ( ( 'post-new.php' == $hook || 'post.php' == $hook ) && $screen->post_type == $this->personnel_content_type ) {
			$ajax_nonce = wp_create_nonce( 'wsu-people-nid-lookup' );

			wp_enqueue_style( 'wsuwp-people-admin-style', plugins_url( 'css/admin-profile-style.css', __FILE__ ) );
			wp_enqueue_script( 'wsuwp-people-admin-script', plugins_url( 'js/admin-profile.js', __FILE__ ), array( 'jquery-ui-tabs' ), '', true );
			wp_localize_script( 'wsuwp-people-admin-script', 'wsupeople_nid_nonce', $ajax_nonce );
		}

		if ( 'edit.php' == $hook && $screen->post_type == $this->personnel_content_type ) {
			wp_enqueue_style( 'wsuwp-people-admin-style', plugins_url( 'css/admin-edit.css', __FILE__ ) );
			wp_enqueue_script( 'wsuwp-people-admin-script', plugins_url( 'js/admin-edit.js', __FILE__ ) );
		}

	}

	/**
	 * Change the "Enter title here" text for the Personnel content type.
	 *
	 * @param string $title The placeholder text displayed in the title input field.
	 *
	 * @return string
	 */
	public function enter_title_here( $title ) {
		$screen = get_current_screen();

		if ( $this->personnel_content_type === $screen->post_type ) {
			$title = 'Enter name here';
		}

		return $title;
	}

	/**
	 * Add markup after the title of the edit screen for the Personnel content type.
	 *
	 * @param WP_Post $post
	 */
	public function edit_form_after_title( $post ) {
		if ( $this->personnel_content_type === $post->post_type ) :
			?>
			<?php do_meta_boxes( get_current_screen(), 'after_title', $post ); ?>
			<div id="wsuwp-profile-tabs">
				<ul>
					<li class="wsuwp-profile-tab wsuwp-profile-bio-tab"><a href="#wsuwp-profile-default" class="nav-tab">Official Biography</a></li>
					<?php
						// Add tabs for saved biographies, and build an array to check against.
						$profile_bios = array();
						foreach ( $this->wp_bio_editors as $bio ) {
							$meta = get_post_meta( $post->ID, $bio, true );
							$profile_bios[] = $meta;
							if ( $meta ) {
								?>
								<li class="wsuwp-profile-tab wsuwp-profile-bio-tab">
									<a href="#<?php echo substr( $bio, 1 ); ?>" class="nav-tab"><?php echo ucfirst( substr( strrchr( $bio, '_' ), 1 ) ); ?> Biography</a>
								</li>
								<?php
							}
						}

						// Build an array of CV field values to check against.
						$profile_cv_data = array();
						foreach ( $this->wp_cv_editors as $cv_meta_field ) {
							$cv_data = get_post_meta( $post->ID, $cv_meta_field, true );
							if ( $cv_data ) {
								$profile_cv_data[] = $cv_data;
							}
						}

						// Display CV tab if any of the fields have been saved.
						if ( $profile_cv_data ) {
							echo '<li class="wsuwp-profile-tab"><a href="#wsuwp-profile-cv" class="nav-tab">C.V.</a></li>';
						}
						
						// Display "+ Add Bio" link if any biographies are still empty.
						if ( array_search( '', $profile_bios ) !== false ) {
							echo '<li><a id="add-bio">+ Add Biography</a></li>';
						}

						//  Display "+ Add Bio" link if none of the fields have value.
						if ( empty( $profile_cv_data ) ) {
							echo '<li><a id="add-cv">+ Add C.V.</a></li>';
						}

					?>
				</ul>
				<div id="wsuwp-profile-default" class="wsuwp-profile-panel">
			<?php
		endif;

	}

	/**
	 * Add markup after the default editor of the edit screen for the Personnel content type.
	 *
	 * @param WP_Post $post
	 */
	public function edit_form_after_editor( $post ) {

		if ( $this->personnel_content_type === $post->post_type ) :
			
			?>
			</div><!--wsuwp-profile-default-->

			<?php 
				foreach ( $this->wp_bio_editors as $bio_meta_field ) {
					$bio = get_post_meta( $post->ID, $bio_meta_field, true );
					if ( $bio ) {
						?>
						<div id="<?php echo substr( $bio_meta_field, 1 ); ?>" class="wsuwp-profile-panel">
							<?php wp_editor( $bio, $bio_meta_field ); ?>
							<!--<p>
								Assign profile photo
                	<select class="wsuwp-profile-bio-photo">
										<option></option>
										<option value="one">1</option>
										<option value="two">2</option>
										<option value="three">3</option>
									</select>
								to this biography.
							</p>-->
						</div>
						<?php
					}
				}
			?>

			<div id="wsuwp-profile-bio-template" class="wsuwp-profile-panel">
				<p>
					This is my
					<select class="wsuwp-profile-bio-type">
						<option></option>
						<?php foreach ( $this->wp_bio_editors as $bio ) : ?>
							<?php if ( ! get_post_meta( $post->ID, $bio, true ) ) : /* Somehow check for ones added without saving */ ?>
							<option value="<?php echo substr( $bio, 1 ); ?>"><?php echo ucfirst( substr( strrchr( $bio, '_' ), 1 ) ); ?></option>
							<?php endif; ?>
						<?php endforeach; ?>
					</select> biography.
				</p>
				<div class="wsuwp-profile-bio-details-container">
					<textarea class="wsuwp-profile-new-bio"></textarea>
					<p>Assign profile photo
						<select class="wsuwp-profile-bio-photo">
							<option></option>
							<option>1</option>
							<option>2</option>
							<option>3</option>
						</select>
					to this biography.</p>
				</div>
			</div><!--wsuwp-profile-bio-template-->
			

			<div id="wsuwp-profile-cv" class="wsuwp-profile-panel" style="display:none;">

				<p class="description">All sections are optional - headings for sections left blank will not be displayed.<br />
        Click the <i class="mce-ico mce-i-wp_help wsuwp-profile-help"></i>for section notes and formatting examples.</p>

				<?php
				add_thickbox();

				$wsuwp_profile_cv_settings = array(
					'media_buttons' => false,
					'textarea_rows' => 5,
				);

				?>

				<div id="wsuwp-profile-employment-lb" class="wsuwp-profile-lb">
					<h4>Include</h4>
					<ul>
						<li>University Related</li>
						<li>Other</li>
					</ul>
					<h4>Formatting</h4>
					<p><strong>University Related</strong></p>
					<ul>
						<li><em>2005-Present</em><br />
             WSU Extension Community Economic Development Specialist. Serving rural communities through economic development education and applied research with an emphasis in access to capital options for small business financing, small business development, and creating entrepreneurial ecosystems in communities.</li>
						<li><em>2001-2004</em><br />
             WSU Extension County Director, Sage County. Responsible for administrative leadership for 3 faculty and 2 staff with program oversight for youth, family, natural resources programs. Conducted the community economic development program. Annual office budget, 450,000.</li>
					</ul>
					<p><strong>Other</strong></p>
					<ul>
						<li><em>2000-1997</em><br />
             Financial Manager for Neighborhood Action Partners, Springdale, MO. Supervised five staff and ten volunteers. Managed budgets for six programs with annual budget of $750,000.</li>
					</ul>
				</div>
				<h3 class="wsuwp-profile-label">Employment <a href="#TB_inline?width=600&height=700&inlineId=wsuwp-profile-employment-lb" class="thickbox wsuwp-profile-help-link" title="Employment"><i class="mce-ico mce-i-wp_help wsuwp-profile-help"></i></a></h3>
				<?php wp_editor( get_post_meta( $post->ID, '_wsuwp_profile_employment', true ), '_wsuwp_profile_employment', $wsuwp_profile_cv_settings ); ?>

				<div id="wsuwp-profile-honors-lb" class="wsuwp-profile-lb">
					<h4>Formatting</h4>
					<ul>
						<li><em>2014</em><br />
             Faculty Excellence in Extension Award, Washington State University</li>
						<li><em>2004</em><br />
             Gold Award for Digitally Curriculum, "Training Local Entrepreneurs,” Natural Resource Extension Professionals (ANREP)</li>
					</ul>
				</div>
				<h3 class="wsuwp-profile-label">Honors and Awards <a href="#TB_inline?width=600&height=700&inlineId=wsuwp-profile-honors-lb" class="thickbox wsuwp-profile-help-link" title="Honors and Awards"><i class="mce-ico mce-i-wp_help wsuwp-profile-help"></i></a></h3>
				<?php wp_editor( get_post_meta( $post->ID, '_wsuwp_profile_honors', true ), '_wsuwp_profile_honors', $wsuwp_profile_cv_settings ); ?>

				<div id="wsuwp-profile-funds-lb" class="wsuwp-profile-lb">
					<h4>Include</h4>
					<ul>
						<li>Grants and Contracts</li>
						<li>Gifts and Awards</li>
						<li>Program Revenue Generation and Sponsorships</li>
						<li>MOA’s and funding secured from Public, Non-profit, and Private entities</li>
						<li>Unfunded Grant Proposals – Summarized by Year</li>
					</ul>
				  <p>Using the rubric below, indicate your contribution(s) to each grant (e.g., principal investigator, co-PI, cooperator), as well as the title, funding entity, total amount, amount of funds for which you had responsibility and authors as described in the example.</p>
					<ol>
						<li>Provided the initial idea</li>
						<li>Developed research/program design and hypotheses</li>
						<li>Authored or co-authored grant application</li>
						<li>Developed and/or managed budget</li>
						<li>Managed personnel, partnerships, and project activities</li>
					</ol>
				  <h4>Formatting</h4>
					<ul>
						<li>Assessing the effects of global warming on nitrogen cycling in eastern Washington. Agriculture and Food Research Initiative, US Department of Agriculture. $428,802. P.I.: <strong>J. A. Smith</strong> and Co-P.I.: R. N. Jones. (3/08 – 3/12) <strong>(1, 3, 4, $300,000).</strong></li>
					</ul>
					<h4>Notes</h4>
					<ul>
						<li><em>Briefly explain why you are listed on a grant if none of the indicators above explain your contribution.</em></li>
						<li><em>The following key will be included on your profile if content is inserted in this section:<br />
            Key to indicators or description of contributions to Grants, Contracts and Fund Generation: 1 = Provided the initial idea; 2 = Developed research/program design and hypotheses; 3 = Authored or co-authored grant application; 4 = Developed and/or managed budget; 5 = Managed personnel, partnerships, and project activities.</em></li>
					</ul>
				</div>
				<h3 class="wsuwp-profile-label">Grants, Contracts, and Fund Generation <a href="#TB_inline?width=600&height=700&inlineId=wsuwp-profile-funds-lb" class="thickbox wsuwp-profile-help-link" title="Grants, Contracts, and Fund Generation"><i class="mce-ico mce-i-wp_help wsuwp-profile-help"></i></a></h3>
				<?php wp_editor( get_post_meta( $post->ID, '_wsuwp_profile_grants', true ), '_wsuwp_profile_grants', $wsuwp_profile_cv_settings ); ?>

				<div id="wsuwp-profile-pubs-lb" class="wsuwp-profile-lb">
					<h4>Include</h4>
					<ul>
						<li>Peer-reviewed Journal Articles. (The journal must have a professional organization or corporate entity with an editor that manages a blind, peer review process.)</li>
						<li>Peer-reviewed Extension Publications. (The publication must have a formal publishing organization such as WSU Extension that manages a blind, peer review process.)</li>
						<li>Peer-reviewed Curricula and Training Manuals (Published curricula and training manuals that have been formally peer reviewed by WSU Extension or another institution.)</li>
						<li>Published Books, Book Chapters, or Monographs (Specify when an entry was peer reviewed according to the criteria described in A or B above.)</li>
						<li>Creative Scholarship in Juried Events. (Abstracts, Posters, and Published Papers in Proceedings of a Professional Meeting or Conference. (Note: These are generally not peer reviewed but they may be peer approved or selected through a process.)</li>
						<li>Educational Digital Media (Videos, computer programs, mobile aps, dynamic web-pages, social media, blogs, online modules, decision aids, email list-serves, etc.) (Designate products that received formal peer-review with an * and indicate the entity managing the review.)</li>
						<li>Other Publications and Creative Works (Those products that did not receive formal peer review, and include popular press articles, newsletters, and other written works)</li>
					</ul>
					<p>Using the rubric below, indicate your contribution(s) to each scholarly product in parentheses as shown in the example:</p>
					<ol>
						<li>Developed the initial idea</li>
						<li>Obtained or provided funds or other resources</li>
						<li>Collected data</li>
						<li>Analyzed data</li>
						<li>Wrote/created product</li>
						<li>Edited product</li>
					</ol>
					<h4>Formatting</h4>
					<p><strong>Smith, J. A.</strong>, and R. N. Jones. 2010. The effects of global warming on nitrogen cycling in eastern Washington. Northwestern Ecosystems 45:300-308. <strong>(1, 2, 4, 5)</strong></p>
					<h4>Notes</h4>
					<ul>
						<li><em>Briefly explain why you are listed on a publication if none of the indicators above explain your contribution.</em></li>
						<li><em>The following key will be included on your profile if content is inserted in this section:<br />
             Key to indicators or description of contributions to Publications and Creative Work: 1 = Developed the initial idea; 2 = Obtained or provided funds or other resources; 3 = Collected data; 4 = Analyzed data; 5 = Wrote/created product; 6 = Edited product.</em></li>
					</ul>
				</div>
				<h3 class="wsuwp-profile-label">Publications and Creative Work <a href="#TB_inline?width=600&height=550&inlineId=wsuwp-profile-pubs-lb" class="thickbox wsuwp-profile-help-link" title="Publications and Creative Work"><i class="mce-ico mce-i-wp_help wsuwp-profile-help"></i></a></h3>
				<?php wp_editor( get_post_meta( $post->ID, '_wsuwp_profile_publications', true ), '_wsuwp_profile_publications', $wsuwp_profile_cv_settings ); ?>

				<div id="wsuwp-profile-presentations-lb" class="wsuwp-profile-lb">
					<p>This section is limited to verbally delivered presentations to audiences. Posters and asynchronous electronic products should be included in the appropriate categories of curriculum, videos, or other creative works in the preceding section for publications and creative works. Do not duplicate single entries across multiple categories. For example, if you developed a poster for a professional meeting do not include it under both “Publications and Creative Work” and “Presentations” categories. Indicate which presentations were specifically invited and keynote addresses.</p>
					<h4>Include</h4>
					<ul>
						<li>International</li>
						<li>National</li>
						<li>State</li>
						<li>Local</li>
					</ul>
					<h4>Formatting</h4>
					<ul>
						<li><strong>Smith, J. <em>2013</em></strong>. <em>Thrips Management in Onions. Pacific Northwest Insect Management Conference, Portland, OR.</em> <strong>Invited Presentation</strong></li>
					</ul>
				</div>
				<h3 class="wsuwp-profile-label">Presentations <a href="#TB_inline?width=600&height=550&inlineId=wsuwp-profile-presentations-lb" class="thickbox wsuwp-profile-help-link" title="Presentations"><i class="mce-ico mce-i-wp_help wsuwp-profile-help"></i></a></h3>
				<?php wp_editor( get_post_meta( $post->ID, '_wsuwp_profile_presentations', true ), '_wsuwp_profile_presentations', $wsuwp_profile_cv_settings ); ?>

				<div id="wsuwp-profile-teaching-lb" class="wsuwp-profile-lb">
					<h4>Include</h4>
					<ul>
						<li>Credit Courses Taught</li>
						<li>Additional Teaching</li>
						<li>Advising (Graduate Students and Student Interns)</li>
					</ul>
					<h4>Formatting</h4>
					<p><strong>Credit Courses Taught</strong></p>
					<ul>
						<li><em>2013</em><br />
             Micro Economics and Local Development, graduate course. School of Economics, Everstate College – Spokane, WA</li>
					</ul>
					<p><strong>Additional Teaching</strong></p>
					<ul>
						<li><em>2012</em><br />
             Micro Economics and Local Investing, undergraduate course. School of Economics, Everstate College – Spokane, WA (guest lecturer)</li>
					</ul>
					<p><strong>Advising</strong></p>
					<ul>
						<li><em>2011</em><br />
             Derek Ohlgren, MS, Civil and Environmental Engineering, Washington State University, (thesis committee)</li>
					</ul>
				</div>
				<h3 class="wsuwp-profile-label">University Instruction <a href="#TB_inline?width=600&height=700&inlineId=wsuwp-profile-teaching-lb" class="thickbox wsuwp-profile-help-link" title="University Instruction"><i class="mce-ico mce-i-wp_help wsuwp-profile-help"></i></a></h3>
				<?php wp_editor( get_post_meta( $post->ID, '_wsuwp_profile_teaching', true ), '_wsuwp_profile_teaching', $wsuwp_profile_cv_settings ); ?>

				<div id="wsuwp-profile-service-lb" class="wsuwp-profile-lb">
					<h4>Include</h4>
					<ul>
						<li>University</li>
						<li>Professional Society</li>
						<li>Community</li>
						<li>Review Activities (journal article reviews and editorial service)</li>
					</ul>
					<h4>Formatting</h4>
					<p><strong>University</strong></p>
					<ul>
						<li><em>2013</em><br />
             Washington State University Strategic Plan Taskforce – Chair<br />
             WSU Extension Director Search Committee – member</li>
						<li><em>2010</em><br />
             Washington State University Faculty Senate – Senator</li>
					</ul>
					<p><strong>Professional Society</strong></p>
					<ul>
						<li><em>2008</em><br />
             National Associate of Economic Developers – Western Regional Chair</li>
						<li><em>2007</em><br />
             Extension Professionals Society – Conference Planning Chair</li>
					</ul>
					<p><strong>Community</strong></p>
					<ul> 
						<li><em>2012</em><br />
             Washington Local Investment Coalition – President</li>
						<li><em>2011 - Present</em><br />
             Washington Banking Association Advisory Board - member</li>
					</ul>
					<p><strong>Review Activities</strong></p>
					<ul>
						<li><em>2010-Present</em><br />
             Doe, J., C. Ray, D. Mee, (editors) Journal of Metropolitan Extension; a journal of the Society for Urban Extension. <a href="#">Read the Journal at W. Coyote</a> [Online Library]</li>
					</ul>
				</div>
				<h3 class="wsuwp-profile-label">Professional Service <a href="#TB_inline?width=600&height=700&inlineId=wsuwp-profile-service-lb" class="thickbox wsuwp-profile-help-link" title="Professional Service"><i class="mce-ico mce-i-wp_help wsuwp-profile-help"></i></a></h3>
				<?php wp_editor( get_post_meta( $post->ID, '_wsuwp_profile_service', true ), '_wsuwp_profile_service', $wsuwp_profile_cv_settings ); ?>

				<div id="wsuwp-profile-responsibilities-lb" class="wsuwp-profile-lb">
					<p>A brief listing of administrative duties and responsibilities.</p>
					<h4>Formatting</h4>
					<ul>
						<li><em>2001-2004</em><br />
             WSU Extension County Director, Sage County. Responsible for administrative leadership of faculty /staff, budget development/ management, local government liaison and representing WSU to the public for youth, family and natural resources programs. Conducted the community economic development program. Annual office budget, $450,000.</li>
					</ul>
				</div>
				<h3 class="wsuwp-profile-label">Administrative Responsibility <a href="#TB_inline?width=600&height=700&inlineId=wsuwp-profile-responsibilities-lb" class="thickbox wsuwp-profile-help-link" title="Administrative Responsibility"><i class="mce-ico mce-i-wp_help wsuwp-profile-help"></i></a></h3>
				<?php wp_editor( get_post_meta( $post->ID, '_wsuwp_profile_responsibilities', true ), '_wsuwp_profile_responsibilities', $wsuwp_profile_cv_settings ); ?>

				<div id="wsuwp-profile-societies-lb" class="wsuwp-profile-lb">
					<h4>Formatting</h4>
					<ul>
						<li>Society for Indigenous Asset Building – member</li>
						<li>National Association of Community Development Extension Professionals - Chair, 2011</li>
					</ul>
				</div>
				<h3 class="wsuwp-profile-label">Professional and Scholarly Organization Affiliations <a href="#TB_inline?width=600&height=700&inlineId=wsuwp-profile-societies-lb" class="thickbox wsuwp-profile-help-link" title="Professional and Scholarly Organization Affiliations"><i class="mce-ico mce-i-wp_help wsuwp-profile-help"></i></a></h3>
				<?php wp_editor( get_post_meta( $post->ID, '_wsuwp_profile_societies', true ), '_wsuwp_profile_societies', $wsuwp_profile_cv_settings ); ?>

				<div id="wsuwp-profile-experience-lb" class="wsuwp-profile-lb">
					<p>Include additional courses for credit, in-service training, and other major professional development activities.</p>
					<h4>Formatting</h4>
					<ul>
						<li><em>2006-2007</em><br />
             <em>Spanish for Professionals</em>, Gonzaga University Certificate Summer Program</li>
						<li><em>2004</em><br />
             <em>Indigenous Economic Development Course</em>, International Economic Development Council</li>
					</ul>
				</div>
				<h3 class="wsuwp-profile-label">Professional Development <a href="#TB_inline?width=600&height=700&inlineId=wsuwp-profile-experience-lb" class="thickbox wsuwp-profile-help-link" title="Professional Experience"><i class="mce-ico mce-i-wp_help wsuwp-profile-help"></i></a></h3>
				<?php wp_editor( get_post_meta( $post->ID, '_wsuwp_profile_experience', true ), '_wsuwp_profile_experience', $wsuwp_profile_cv_settings ); ?>

			</div>

		</div><!--wsuwp-profile-tabs-->
			<?php
		endif;

	}

	/**
	 * Add the meta boxes used for the Personnel content type.
	 *
	 * @param string $post_type The slug of the current post type.
	 */
	public function add_meta_boxes( $post_type ) {
		if ( $this->personnel_content_type !== $post_type ) {
			return;
		}

		add_meta_box(
			'wsuwp_profile_additional_info',
			'Additional Profile Information',
			array( $this, 'display_additional_info_meta_box' ),
			$this->personnel_content_type,
			'after_title',
			'high'
		);
	}

	/**
	 * Display a meta box used to show a person's "card".
	 *
	 * @param WP_Post $post
	 */
	public function display_position_info_meta_box( $post ) {

		wp_nonce_field( 'wsuwsp_profile', 'wsuwsp_profile_nonce' );

		$name_first = get_post_meta( $post->ID, '_wsuwp_profile_ad_name_first', true );
		$name_last  = get_post_meta( $post->ID, '_wsuwp_profile_ad_name_last', true );
		$title      = get_post_meta( $post->ID, '_wsuwp_profile_ad_title', true );
		$email      = get_post_meta( $post->ID, '_wsuwp_profile_ad_email', true );
		$office     = get_post_meta( $post->ID, '_wsuwp_profile_ad_office', true );
		$address    = get_post_meta( $post->ID, '_wsuwp_profile_ad_address', true );
		$phone      = get_post_meta( $post->ID, '_wsuwp_profile_ad_phone', true );
		$phone_ext  = get_post_meta( $post->ID, '_wsuwp_profile_ad_phone_ext', true );
		

		$appointments = wp_get_post_terms( $post->ID, $this->personnel_appointments, array( 'fields' => 'names' ) );
		$classifications = wp_get_post_terms( $post->ID, $this->personnel_classifications, array( 'fields' => 'names' ) );

		?>
		<div class="profile-card">

			<div>
				<div>Given Name:</div>
				<div id="_wsuwp_profile_ad_name_first"><?php echo esc_html( $name_first ); ?></div>
			</div>

			<div>
				<div>Surname:</div>
				<div id="_wsuwp_profile_ad_name_last"><?php echo esc_html( $name_last ); ?></div>
			</div>

			<div>
				<div>Title:</div>
				<div id="_wsuwp_profile_ad_title"><?php echo esc_html( $title ); ?></div>
			</div>

			<div>
				<div>Office:</div>
				<div id="_wsuwp_profile_ad_office"><?php echo esc_html( $office ); ?></div>
			</div>

			<div>
				<div>Street Address:</div>
				<div id="_wsuwp_profile_ad_address"><?php echo esc_html( $address ); ?></div>
			</div>

			<div>
				<div>Phone:</div>
				<div id="_wsuwp_profile_ad_phone"><?php echo esc_html( $phone ); if ( $phone_ext ) { echo ' ' . esc_html( $phone_ext ); } ?></div>
			</div>

			<div>
				<div>Email:</div>
				<div id="_wsuwp_profile_ad_email"><?php echo esc_html( $email ); ?></div>
			</div>

			<?php if ( $appointments ) : ?>
				<div>
					<div>Appointment(s)</div>
					<div>
						<ul>
							<?php foreach ( $appointments as $appointment ) { echo '<li>' . $appointment . '</li>'; } ?>
						</ul>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $classifications ) : ?>
				<div>
					<div>Classification</div>
					<div>
						<ul>
							<?php foreach ( $classifications as $classification ) { echo '<li>' . $classification . '</li>'; } ?>
						</ul>
					</div>
				</div>
			<?php endif; ?>
		</div>

		<!--<p class="description">Notify <a href="#">HR</a> if any of this information is incorrect or needs updated.</p>-->
		<?php
	}

	/**
	 * Display a meta box used to upload a person's C.V.
	 *
	 * @param WP_Post $post
	 */
	public function display_cv_upload_meta_box( $post ) {

		$cv = get_post_meta( $post->ID, '_wsuwp_profile_cv', true );

		?>
			<div class="upload-set-wrapper">
				<input type="hidden" class="wsuwp-profile-upload" name="_wsuwp_profile_cv" id="_wsuwp_profile_cv" value="<?php echo esc_attr( $cv ); ?>" />
				<p class="hide-if-no-js"><a title="C.V." data-type="File" href="#" class="wsuwp-profile-upload-link">
				<?php if ( $cv ) : ?>
					<img src="<?php echo esc_url( home_url( '/wp-includes/images/media/document.png' ) ); ?>" /></a></p>
					<p class="hide-if-no-js"><a title="C.V." href="#" class="wsuwp-profile-remove-link">Remove C.V.</a></p>
				<?php else : ?>
					 Set C.V.</a></p>
				<?php endif; ?>
			</div>
		<?php

	}

	/**
	 * Remove, move, and replace meta boxes as they are created and output.
	 *
	 * @param string  $post_type The current post type meta boxes are displayed for.
	 * @param string  $context   The context in which meta boxes are being output.
	 * @param WP_Post $post      The post object.
	 */
	public function do_meta_boxes( $post_type, $context, $post ) {
		if ( $this->personnel_content_type !== $post_type ) {
			return;
		}

		$box_title = ( 'auto-draft' === $post->post_status ) ? 'Create Profile' : 'Update Profile';

		remove_meta_box( 'submitdiv', $this->personnel_content_type, 'side' );
		add_meta_box( 'submitdiv', $box_title, array( $this, 'publish_meta_box' ), $this->personnel_content_type, 'side', 'high' );

		add_meta_box(
			'wsuwp_profile_position_info',
			'Position and Contact Information',
			array( $this, 'display_position_info_meta_box' ),
			$this->personnel_content_type,
			'side',
			'high'
		);
		
		// Move and re-label the Featured Image meta box.
		remove_meta_box( 'postimagediv', $this->personnel_content_type, 'side' );
		add_meta_box( 'postimagediv', 'Profile Photo', 'post_thumbnail_meta_box', $this->personnel_content_type, 'side', 'high' );

		add_meta_box(
			'wsuwp_profile_cv_upload',
			'Curriculum Vitae',
			array( $this, 'display_cv_upload_meta_box' ),
			$this->personnel_content_type,
			'side',
			'high'
		);

		// Remove "Appointment" and "Classification" meta boxes.
		remove_meta_box( 'appointmentdiv', $this->personnel_content_type, 'side' );
		//remove_meta_box( 'classificationdiv', $this->personnel_content_type, 'side' );
	}

	/**
	 * Replace the default post publishing meta box with our own that guides the user through
	 * a slightly different process for creating and saving a person.
	 *
	 * This was originally copied from WordPress core's `post_submit_meta_box()`.
	 *
	 * @param WP_Post $post The profile being edited/created.
	 */
	public function publish_meta_box( $post ) {
		$post_type = $post->post_type;
		$post_type_object = get_post_type_object( $post_type );
		$can_publish = current_user_can( $post_type_object->cap->publish_posts );

		$nid = get_post_meta( $post->ID, '_wsuwp_profile_ad_nid', true );

		$readonly = empty( trim( $nid ) ) ? '' : 'readonly';
		?>
		<div class="submitbox" id="submitpost">

			<div id="misc-publishing-actions">
				<div class="misc-pub-section">
					<label for="_wsuwp_profile_ad_nid">Network ID</label>:
					<input type="text" id="_wsuwp_profile_ad_nid" name="_wsuwp_profile_ad_nid" value="<?php echo esc_attr( $nid ); ?>" class="widefat" <?php echo $readonly; ?> />

				<?php if ( '' === $readonly ) : ?>
					<div class="load-ad-container">
						<p class="description">Enter the WSU Network ID for this user to populate data from Active Directory.</p>
					</div>
				<?php else : ?>
					<div class="load-ad-container">
						<p class="description">The WSU Network ID used to populate this profile's data from Active Directory.</p>
					</div>
				<?php endif; ?>
				</div>
			</div>
			<div id="major-publishing-actions">

				<div id="delete-action">
					<?php
					if ( 'auto-draft' !== $post->post_status && current_user_can( 'delete_post', $post->ID ) ) {
						if ( ! EMPTY_TRASH_DAYS ) {
							$delete_text = __('Delete Permanently');
						} else {
							$delete_text = __( 'Move to Trash' );
						} ?>
						<a class="submitdelete deletion" href="<?php echo get_delete_post_link( $post->ID ); ?>"><?php echo $delete_text; ?></a><?php
					}
					?>
				</div>

				<div id="publishing-action">
					<span class="spinner"></span>
					<?php
					if ( $can_publish && ( ! in_array( $post->post_status, array( 'publish', 'future', 'private' ) ) || 0 == $post->ID ) ) { ?>
						<span class="button" id="load-ad-data">Load</span>
						<span class="button button-primary profile-hide-button" id="confirm-ad-data">Confirm</span>
						<input type="hidden" id="confirm-ad-hash" name="confirm_ad_hash" value="" />
						<input name="original_publish" type="hidden" id="original_publish"
							   value="<?php esc_attr_e( 'Publish' ); ?>"/>
						<?php submit_button( __( 'Publish' ), 'primary button-large profile-hide-button', 'publish', false );
					} else { ?>
						<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Update' ); ?>" />
						<input name="save" type="submit" class="button button-primary button-large" id="publish" value="<?php esc_attr_e( 'Update' ); ?>" />
					<?php
					} ?>
				</div>
				<div class="clear"></div>
			</div>
		</div>

	<?php
	}

	/**
	 * Display a meta box to collect alternate contact information as well as additional working
	 * title and degree data for the profile.
	 *
	 * @param WP_Post $post
	 */
	public function display_additional_info_meta_box( $post ) {
		?>
		<div class="wsuwp-profile-additional">
			<p>
				<label for="_wsuwp_profile_alt_office">Office</label><br />
				<input type="text" id="_wsuwp_profile_alt_office" name="_wsuwp_profile_alt_office" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_alt_office', true ) ); ?>" class="widefat" />
			</p>
			<p>
				<label for="_wsuwp_profile_alt_phone">Phone Number <span class="description">(xxx-xxx-xxxx)</span></label><br />
				<input type="text" id="_wsuwp_profile_alt_phone" name="_wsuwp_profile_alt_phone" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_alt_phone', true ) ); ?>" class="widefat" />
			</p>
			<p>
				<label for="_wsuwp_profile_alt_email">Email Address</label><br />
				<input type="text" id="_wsuwp_profile_alt_email" name="_wsuwp_profile_alt_email" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_alt_email', true ) ); ?>" class="widefat" />
			</p>
			<p>
				<label for="_wsuwp_profile_website">Website URL</label><br />
				<input type="text" id="_wsuwp_profile_website" name="_wsuwp_profile_website" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_website', true ) ); ?>" class="widefat" />
			</p>
		</div>
		<div class="wsuwp-profile-additional">
			<?php
      $titles = get_post_meta( $post->ID, '_wsuwp_profile_title', true );
      $degrees = get_post_meta( $post->ID, '_wsuwp_profile_degree', true );
      ?>
      <div>
        <?php
        if ( $titles && is_array( $titles ) ) {
          foreach ( $titles as $index => $title ) {
            ?>
            <p class="wp-profile-repeatable">
              <label for="_wsuwp_profile_title[<?php echo esc_attr( $index ); ?>]">Working Title</label><br />
              <input type="text" id="_wsuwp_profile_title[<?php echo esc_attr( $index ); ?>]" name="_wsuwp_profile_title[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $title ); ?>" class="widefat" />
            </p>
            <?php
          }
        } else {
          ?>
          <p class="wp-profile-repeatable">
            <label for="_wsuwp_profile_title[0]">Working Title</label><br />
            <input type="text" id="_wsuwp_profile_title[0]" name="_wsuwp_profile_title[0]" value="<?php echo esc_attr( $titles ); ?>" class="widefat" />
          </p>
          <?php
        }
        ?>
        <p class="wsuwp-profile-add-repeatable"><a href="#">+ Add another title</a></p>
      </div>
  
      <div>
        <?php
        if ( $degrees && is_array( $degrees ) ) {
          foreach ( $degrees as $index => $degree ) {
            ?>
						<p class="wp-profile-repeatable">
							<label for="_wsuwp_profile_degree[<?php echo esc_attr( $index ); ?>]">Degree</label><br />
							<input type="text" id="_wsuwp_profile_degree[<?php echo esc_attr( $index ); ?>]" name="_wsuwp_profile_degree[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $degree ); ?>" class="widefat" />
						</p>
            <?php
          }
        } else {
          ?>
					<p class="wp-profile-repeatable">
						<label for="_wsuwp_profile_degree[0]">Degree</label><br />
						<input type="text" id="_wsuwp_profile_degree[0]" name="_wsuwp_profile_degree[0]" value="<?php echo esc_attr( $degrees ); ?>" class="widefat" />
					</p>
          <?php
        }
        ?>
				<p class="wsuwp-profile-add-repeatable"><a href="#">+ Add another degree</a></p>
			</div>
		</div>
		<div class="clear"></div>
	<?php
	}

	/**
	 * Save data associated with a profile.
	 *
	 * @param int $post_id
	 *
	 * @return mixed
	 */
	public function save_post( $post_id ) {

		if ( ! isset( $_POST['wsuwsp_profile_nonce'] ) ) {
			return $post_id;
		}

		$nonce = $_POST['wsuwsp_profile_nonce'];

		if ( ! wp_verify_nonce( $nonce, 'wsuwsp_profile' ) ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// Save "last_name first_name" data (for alpha sorting purposes).
		if ( ( isset( $_POST['_wsuwp_profile_ad_name_last'] ) && '' != $_POST['_wsuwp_profile_ad_name_last'] ) &&
				 ( isset( $_POST['_wsuwp_profile_ad_name_first'] ) && '' != $_POST['_wsuwp_profile_ad_name_first'] ) ) {
			update_post_meta( $post_id, '_wsuwp_profile_name', sanitize_text_field( $_POST['_wsuwp_profile_ad_name_last'] ) . ' ' . sanitize_text_field( $_POST['_wsuwp_profile_ad_name_first'] ) );
		}

		// Sanitize and save basic fields.
		foreach ( $this->basic_fields as $field ) {
			if ( isset( $_POST[ $field ] ) && '' != $_POST[ $field ] ) {
				update_post_meta( $post_id, $field, sanitize_text_field( $_POST[ $field ] ) );
			} else {
				delete_post_meta( $post_id, $field );
			}
		}

		// Sanitize and save repeatable fields.
		foreach ( $this->repeatable_fields as $field ) {
			if ( isset( $_POST[ $field ] ) && '' != $_POST[ $field ] ) {
				$array = array();
				foreach ( $_POST[ $field ] as $value ) {
					if ( isset( $value ) && '' != $value ) {
						$array[] = sanitize_text_field( $value );
					}
				}
				if ( $array ) {
					update_post_meta( $post_id, $field, $array );
				} else {
					delete_post_meta( $post_id, $field );
				}

			}

		}

		// Sanitize and save wp_editors.
		$wp_editors = array_merge( $this->wp_bio_editors, $this->wp_cv_editors );
		foreach ( $wp_editors as $field ) {
			if ( isset( $_POST[ $field ] ) && '' != $_POST[ $field ] ) {
				update_post_meta( $post_id, $field, wp_kses_post( $_POST[ $field ] ) );
			} else {
				delete_post_meta( $post_id, $field );
			}
		}

	}

	/**
	 * Keys of meta fields to revision.
	 */
	public function add_meta_keys_to_revision( $keys ) {
		$revisioned_fields = array_merge( $this->basic_fields, $this->repeatable_fields, $this->wp_bio_editors, $this->wp_cv_editors );

		foreach ( $revisioned_fields as $field ) {
			$keys[] = $field;
		}

		return $keys;
	}

	/**
	 * Taxonomy columns on the "All Profiles" screen.
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function wsuwp_people_profile_columns( $columns ) {
		$columns[] = 'wsuwp_university_org';
		$columns[] = 'wsuwp_university_location';

		return $columns;
	}

	/**
	 * Registers the wsu_nid parameter.
	 *
	 * @since 0.2.2
	 */
	public function register_wsu_nid_query_var() {
		global $wp;
		$wp->add_query_var( 'wsu_nid' );
	}

	/**
	 * Retrieves a passed wsu_nid with a REST request and adds to the query vars.
	 *
	 * @since 0.2.2
	 *
	 * @param array           $valid_vars
	 * @param WP_REST_Request $request
	 *
	 * @return array
	 */
	public function rest_query_vars( $valid_vars, $request ) {
		$valid_vars['wsu_nid'] = $request->get_param( 'wsu_nid' );

		return $valid_vars;
	}

	/**
	 * Sets a meta query for WSU NID when the wsu_nid parameters is included as
	 * part of a query.
	 *
	 * @since 0.2.2
	 *
	 * @param WP_Query $query
	 */
	public function handle_wsu_nid_query_var( $query ) {
		if ( isset( $query->query['wsu_nid'] ) && $query->query['wsu_nid'] ) {
			$query->set( 'meta_key', '_wsuwp_profile_ad_nid' );
			$query->set( 'meta_value', sanitize_text_field( $query->query['wsu_nid'] ) );
		}
	}

	/**
	 * Register the custom meta fields attached to a REST API response containing profile data.
	 *
	 * @since 0.2.0
	 */
	public function register_api_fields() {
		$args = array(
			'get_callback' => array( $this, 'get_api_meta_data' ),
			'update_callback' => null,
			'schema' => null,
		);
		foreach( $this->rest_response_fields as $field_name => $value ) {
			register_rest_field( $this->personnel_content_type, $field_name, $args );
		}
	}

	/**
	 * Return the value of a post meta field sanitized against a whitelist with the provided method.
	 *
	 * @since 0.2.0
	 *
	 * @param array           $object     The current post being processed.
	 * @param string          $field_name Name of the field being retrieved.
	 * @param WP_Rest_Request $request    The full current REST request.
	 *
	 * @return mixed Meta data associated with the post and field name.
	 */
	public function get_api_meta_data( $object, $field_name, $request ) {
		if ( ! array_key_exists( $field_name, $this->rest_response_fields ) ) {
			return '';
		}

		if ( 'esc_html' === $this->rest_response_fields[ $field_name ]['sanitize'] ) {
			return esc_html( get_post_meta( $object['id'], $this->rest_response_fields[ $field_name ]['meta_key'], true ) );
		}

		if ( 'esc_html_map' === $this->rest_response_fields[ $field_name ]['sanitize'] ) {
			$data = get_post_meta( $object['id'], $this->rest_response_fields[ $field_name ]['meta_key'], true );
			if ( is_array( $data ) ) {
				$data = array_map( 'esc_html', $data );
			} else {
				$data = array();
			}

			return $data;
		}

		if ( 'esc_url' === $this->rest_response_fields[ $field_name ]['sanitize'] ) {
			return esc_url( get_post_meta( $object['id'], $this->rest_response_fields[ $field_name ]['meta_key'], true ) );
		}

		if ( 'the_content' === $this->rest_response_fields[ $field_name ]['sanitize'] ) {
			$data = get_post_meta( $object['id'], $this->rest_response_fields[ $field_name ]['meta_key'], true );
			$data = apply_filters( 'the_content', $data );
			return wp_kses_post( $data );
		}

		if ( 'cv_attachment' === $field_name ) {
			$cv_id = get_post_meta( $object['id'], $this->rest_response_fields[ $field_name ]['meta_key'], true );
			$cv_url = wp_get_attachment_url( $cv_id );

			if ( $cv_url ) {
				return esc_url( $cv_url );
			} else {
				return false;
			}
		}

		if ( 'profile_photo' === $field_name ) {
			$thumbnail_id = get_post_thumbnail_id( $object['id'] );
			if ( $thumbnail_id ) {
				$thumbnail = wp_get_attachment_image_src( $thumbnail_id );
				if ( $thumbnail ) {
					return esc_url( $thumbnail[0] );
				} else {
					return false;
				}
			}
		}

		return '';
	}

	/**
	 * Capability modifications.
	 */
	public function user_has_cap( $allcaps, $cap, $args ) {
		if ( empty( $allcaps ) ) {
			return $allcaps;
		}

		// Bail for users who can already edit others posts:
		if ( $allcaps['edit_others_posts'] ) {
			return $allcaps;
		}

		// Bail for users who can't publish posts:
		if ( ! isset( $allcaps['publish_posts'] ) or ! $allcaps['publish_posts'] ) {
			return $allcaps;
		}

		// Load the post data:
		$post = get_post( $args[2] );

		// Bail if the post type isn't Personnel:
		if ( $this->personnel_content_type != $post->post_type ) {
			return $allcaps;
		}

		// Bail if the user is the post author:
		if ( $args[1] == $post->post_author ) {
			return $allcaps;
		}

		// Bail if the post isn't published:
		if ( 'publish' != $post->post_status ) {
			return $allcaps;
		}

		// Bail if the user isn't an Organization Administrator:
		$dept = get_post_meta( $post->ID, '_wsuwp_profile_dept', true );
		$org_admin = get_user_meta( $args[1], 'wsuwp_people_organization_admin', true );
		if ( $org_admin != $dept ) {
			return $allcaps;
		}

		// Load the author data:
		$author = new WP_User( $post->post_author );

		// Bail if post author can edit others posts:
		if ( $author->has_cap( 'edit_others_posts' ) ) {
			return $allcaps;
		}

		$allcaps[ $cap[0] ] = true;

		return $allcaps;
	}

	/**
	 * Remove some items from the admin menu (should probably change permissions too).
	 */
	public function admin_menu() {

		if ( ! current_user_can( 'manage_options' ) ) {
			remove_menu_page( 'profile.php' ); // Profile
			remove_menu_page( 'edit-comments.php' ); // Comments
			remove_menu_page( 'tools.php' ); // Tools
			remove_menu_page( 'edit.php' ); // Posts
			remove_menu_page( 'upload.php' ); // Media Library
			remove_menu_page( 'edit.php?post_type=page' ); // Pages
			remove_submenu_page( 'edit.php?post_type=wsuwp_people_profile', 'post-new.php?post_type=wsuwp_people_profile' ); // Personnel -> Add New
		}

	}

	/**
	 * Remove the "Add New" menu from the toolbar.
	 */
	public function admin_bar_menu( $wp_admin_bar ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			$wp_admin_bar->remove_node( 'new-content' );
		}

	}

	/**
	 * Modify dashboard widgets.
	 */
	public function wp_dashboard_setup() {

		if ( ! current_user_can( 'manage_options' ) ) {

			remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
			remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
			remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
			remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );

			wp_add_dashboard_widget(
				'wsuwp_directory_dashboard_widget',
				'Welcome to the WSU Personnel Directory',
				array( $this, 'wsuwp_directory_dashboard_widget' )
      );

		}

	}

	/**
	 * Contents for the dashboard widget.
	 */
	public function wsuwp_directory_dashboard_widget() {
		echo '<p>Click the "Profiles" button to the left to get started.</p>';
		echo '<p>This is just a placeholder. This space will contain more information and documentation about the directory.</p>';
	}

	/**
	 * Show (non-Admin) users only the media they have uploaded.
	 * Theoretically, they have no need to see the rest.
	 * This doesn't change the counts on the Media Library page.
	 */
	public function limit_media_library( $query ) {
		if ( is_admin() && isset( $_REQUEST['action'] ) ) {
			if ( ! get_current_screen() || ( 'upload' !== get_current_screen()->base && 'query-attachments' !== $_REQUEST['action'] ) ) {
				return;
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				$query->set( 'author', wp_get_current_user()->ID );
			}
		}
	}

	/**
	 * Modify "All Profiles" views for all but administrators.
	 * Probably not the most scalable.
	 */
	public function edit_views( $views ) {

		if ( ! current_user_can( 'manage_options' ) ) {

			unset( $views['all'] );
			unset( $views['publish'] );
			unset( $views['draft'] );
			unset( $views['pending'] );
			unset( $views['trash'] );

			$current_user = wp_get_current_user();

			$all_personnel = new WP_Query( array(
				'post_type'	 => $this->personnel_content_type,
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'author__not_in' => $current_user->ID,
			) );

			$posts = $all_personnel->get_posts();

			foreach( $posts as $post ) {
				if ( current_user_can( 'edit_post', $post->ID ) ) {
					$users_editable_profiles[] = $post->ID;
				}
			}

			if ( $users_editable_profiles ) {

				$profile_ids = implode( ',', $users_editable_profiles );
				$count = count( $users_editable_profiles );

				$class = ( $_GET['sortby'] == 'last_name' ) ? ' class="current"' : '';
				$url = admin_url('edit.php?post_type=' . $this->personnel_content_type . '&post_status=publish&sortby=last_name&profiles=' . $profile_ids );
				$views['others'] = sprintf(__( '<a href="%s"'. $class .'>Others <span class="count">(%d)</span></a>' ), $url, $count );

			}

		}

		return $views;

	}

	/**
	 * Query parsing for "Others" view on "All Profiles" page.
	 */
	public function parse_query( $query ) {

		$screen = get_current_screen();

		if ( is_admin() && 'edit-' . $this->personnel_content_type == $screen->id &&
				isset( $_GET['post_type'] ) && $_GET['post_type'] == $this->personnel_content_type &&
				isset( $_GET['sortby'] ) && $_GET['sortby'] == 'last_name' &&
				isset( $_GET['profiles'] ) && $_GET['profiles'] != '' )
		{
			$editables = explode( ',', $_GET['profiles'] );
			set_query_var( 'meta_key', '_wsuwp_profile_ad_name_last' );
			set_query_var( 'orderby', 'meta_value' );
			// These two seem to prevent the "'views_'.$this->screen->id" hook from working properly.
			//set_query_var( 'order', 'ASC' );
			//set_query_var( 'post__in', $editables );
		}

	}

	/**
	 * Order public Personnel query results alphabetically by last name.
	 *
	 * @param WP_Query $query
	 */
	public function profile_archives( $query ) {
		if ( ( $query->is_post_type_archive( $this->personnel_content_type ) || is_tax() || is_category() || is_tag() ) && $query->is_main_query() && ! is_admin() ) {
			$query->set( 'order', 'ASC' );
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'meta_key', '_wsuwp_profile_ad_name_last' );
		}
	}

	/**
	 * Given a WSU Network ID, retrieve information from active directory about
	 * a user.
	 *
	 * @param string $nid The user's network ID.
	 *
	 * @return array List of predefined information we'll expect on the other side.
	 */
	private function get_nid_data( $nid ) {
		if ( false === function_exists( 'wsuwp_get_wsu_ad_by_login' ) ) {
			return array();
		}

		// Get data from the WSUWP SSO Authentication plugin.
		$nid_data = wsuwp_get_wsu_ad_by_login( $nid );

		$return_data = array(
			'given_name' => '',
			'surname' => '',
			'title' => '',
			'office' => '',
			'street_address' => '',
			'telephone_number' => '',
			'email' => '',
			'confirm_ad_hash' => '',
		);

		if ( isset( $nid_data['givenname'][0] ) ) {
			$return_data['given_name'] = sanitize_text_field( $nid_data['givenname'][0] );
		}

		if ( isset( $nid_data['sn'][0] ) ) {
			$return_data['surname'] = sanitize_text_field( $nid_data['sn'][0] );
		}

		if ( isset( $nid_data['title'][0] ) ) {
			$return_data['title'] = sanitize_text_field( $nid_data['title'][0] );
		}

		if ( isset( $nid_data['physicaldeliveryofficename'][0] ) ) {
			$return_data['office'] = sanitize_text_field( $nid_data['physicaldeliveryofficename'][0] );
		}

		if ( isset( $nid_data['streetaddress'][0] ) ) {
			$return_data['street_address'] = sanitize_text_field( $nid_data['streetaddress'][0] );
		}

		if ( isset( $nid_data['telephonenumber'][0] ) ) {
			$return_data['telephone_number'] = sanitize_text_field( $nid_data['telephonenumber'][0] );
		}

		if ( isset( $nid_data['mail'][0] ) ) {
			$return_data['email'] = sanitize_text_field( $nid_data['mail'][0] );
		}

		$hash = md5( serialize( $return_data ) );
		$return_data['confirm_ad_hash'] = $hash;

		return $return_data;
	}

	/**
	 * Process an ajax request for AD information attached to a network ID. We'll return
	 * the data here for confirmation. Confirmation will be handled elsewhere.
	 */
	public function ajax_get_data_by_nid() {
		check_ajax_referer( 'wsu-people-nid-lookup' );

		$nid = sanitize_text_field( $_POST['network_id'] );

		if ( empty( $nid ) ) {
			wp_send_json_error( 'Invalid or empty Network ID' );
		}

		$return_data = $this->get_nid_data( $nid );

		wp_send_json_success( $return_data );
	}

	/**
	 * Process an ajax request to confirm the AD information attached to a network ID. At
	 * this point we'll do the lookup again and save the information to the current profile.
	 */
	public function ajax_confirm_nid_data() {
		check_ajax_referer( 'wsu-people-nid-lookup' );

		$nid = sanitize_text_field( $_POST['network_id'] );

		if ( empty( $nid ) ) {
			wp_send_json_error( 'Invalid or empty Network ID' );
		}

		// Data is sanitized before return.
		$confirm_data = $this->get_nid_data( $nid );

		if ( $confirm_data['confirm_ad_hash'] !== $_POST['confirm_ad_hash'] ) {
			wp_send_json_error( 'Previously retrieved data does not match the data attached to this network ID.' );
		}

		if ( empty( absint( $_POST['post_id'] ) ) ) {
			wp_send_json_error( 'Invalid profile post ID.' );
		}

		$post_id = $_POST['post_id'];

		update_post_meta( $post_id, '_wsuwp_profile_ad_nid', $nid );
		update_post_meta( $post_id, '_wsuwp_profile_ad_name_first', $confirm_data['given_name'] );
		update_post_meta( $post_id, '_wsuwp_profile_ad_name_last', $confirm_data['surname'] );
		update_post_meta( $post_id, '_wsuwp_profile_ad_title', $confirm_data['title'] );
		update_post_meta( $post_id, '_wsuwp_profile_ad_office', $confirm_data['office'] );
		update_post_meta( $post_id, '_wsuwp_profile_ad_address', $confirm_data['street_address'] );
		update_post_meta( $post_id, '_wsuwp_profile_ad_phone', $confirm_data['telephone_number'] );
		update_post_meta( $post_id, '_wsuwp_profile_ad_email', $confirm_data['email'] );

		wp_send_json_success( 'Updated' );
	}

}
$wsuwp_people_directory = new WSUWP_People_Directory();
