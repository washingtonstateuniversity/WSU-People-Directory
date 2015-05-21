<?php
/*
Plugin Name: WSU People Directory
Plugin URI:	#
Description: A plugin to maintain a central directory of people.
Author:	washingtonstateuniversity, CAHNRS, philcable, danialbleile
Version: 0.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

class WSUWP_People_Directory {

	/**
	 * The plugin version number, used to break caches and trigger
	 * upgrade routines.
	 *
	 * @var string
	 */
	var $personnel_plugin_version = '0.1.0';

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
	 * Fields used to capture Active Directory data.
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
	 * Fields used to capture additional profile information.
	 */
	var $basic_fields = array(
		'_wsuwp_profile_alt_office',
		'_wsuwp_profile_alt_phone',
		'_wsuwp_profile_alt_email',
		'_wsuwp_profile_website',
		'_wsuwp_profile_cv',
		'_wsuwp_profile_coeditor',
	);

	/**
	 * Repeatable fields used throughout the metaboxes.
	 */
	var $repeatable_fields = array(
		'_wsuwp_profile_degree',
		'_wsuwp_profile_title',
	);

	/**
	 * WP editors used throughout the metaboxes.
	 */
	var $wp_editors = array(
		'_wsuwp_profile_experience',
		'_wsuwp_profile_honors',
		'_wsuwp_profile_teaching',
		'_wsuwp_profile_research',
		'_wsuwp_profile_grants',
		'_wsuwp_profile_publications',
		'_wsuwp_profile_service',
		'_wsuwp_profile_extension',
	);

	public function __construct() {

		// Custom content type and taxonomies.
		//add_action( 'init', array( $this, 'process_upgrade_routine' ), 12 );
		add_action( 'init', array( $this, 'register_personnel_content_type' ), 11 );
		add_action( 'init', array( $this, 'register_taxonomies' ), 11 );
		add_action( 'init', array( $this, 'add_taxonomies' ), 12 );

		// Custom meta and all that.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_filter( 'enter_title_here', array( $this, 'enter_title_here' ) );
		add_action( 'edit_form_after_title', array( $this, 'edit_form_after_title' ) );
		add_action( 'edit_form_after_editor',	array( $this, 'edit_form_after_editor' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );
		add_action( 'do_meta_boxes', array( $this, 'do_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
		add_filter( 'wp_post_revision_meta_keys', array( $this, 'add_meta_keys_to_revision' ) );

		// Modify taxonomy columns on "All Profiles" page.
		add_filter( 'manage_taxonomies_for_wsuwp_people_profile_columns', array( $this, 'wsuwp_people_profile_columns' ) );

		// JSON output.
		add_filter( 'json_prepare_post', array( $this, 'json_prepare_post' ), 10, 3 );
		add_filter( 'json_query_vars', array( $this, 'json_query_vars' ) );

		// Capabilities and related.
		add_action( 'personal_options', array( $this, 'personal_options' ) );
		add_action( 'edit_user_profile_update', array( $this, 'edit_user_profile_update' ) );
		add_action( 'personal_options_update', array( $this, 'edit_user_profile_update' ) );
		add_filter( 'user_has_cap', array( $this, 'user_has_cap' ), 10, 3 );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 999 );
		add_action( 'wp_dashboard_setup', array( $this, 'wp_dashboard_setup' ) );
		add_action( 'pre_get_posts', array( $this, 'limit_media_library' ) );
		//add_action( 'views_edit-' . $this->personnel_content_type, array( $this, 'edit_views' ) );
		//add_filter( 'parse_query', array ( $this, 'parse_query' ) );

		// Templates, scripts, styles, and filters for the front end.
		add_filter( 'template_include', array( $this, 'template_include' ), 1 );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ), 11 );
		add_action( 'pre_get_posts', array( $this, 'profile_archives' ) );

		// Handle ajax requests from the admin.
		add_action( 'wp_ajax_wsu_people_get_data_by_nid', array( $this, 'ajax_get_data_by_nid' ) );
		add_action( 'wp_ajax_wsu_people_confirm_nid_data', array( $this, 'ajax_confirm_nid_data' ) );
	}

	/**
	 * Process any upgrade routines between versions or on initial activation.
	 * (Taken verbatim from the Unversity Center plugin)
	 */
	public function process_upgrade_routine() {

		$db_version = get_option( 'wsuwp_people_version', '0.0.0' );

		// Flush rewrite rules if on an early or non existing DB version.
		if ( version_compare( $db_version, '0.1.0', '<' ) ) {
			flush_rewrite_rules();
		}

		update_option( 'wsuwp_people_version', $this->personnel_plugin_version );

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
				'author',
				'thumbnail',
				'revisions'
			),
			'taxonomies' => array(
				//'category',
				'post_tag'
			),
			'has_archive' => true,
			'rewrite' => array(
				'slug' => 'profile',
				'with_front' => false
			),
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
		);
		register_taxonomy( $this->personnel_classifications, $this->personnel_content_type, $classifications );

	}

	/**
	 * Add WSUWP University Taxonomies.
	 */
	public function add_taxonomies() {
		register_taxonomy_for_object_type( 'wsuwp_university_category', $this->personnel_content_type );
		register_taxonomy_for_object_type( 'wsuwp_university_location', $this->personnel_content_type );
		//register_taxonomy_for_object_type( 'wsuwp_university_organizations', $this->personnel_content_type );
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
	 */
	public function enter_title_here( $title ) {

		$screen = get_current_screen();

		if ( $this->personnel_content_type == $screen->post_type ) {
			$title = 'Enter name here';
		}

		return $title;

	}

	/**
	 * Add markup after the title of the edit screen for the Personnel content type.
	 */
	public function edit_form_after_title( $post ) {

		//do_meta_boxes( get_current_screen(), 'after_title', $post );

		if ( $this->personnel_content_type === $post->post_type ) :
			?>
			<?php do_meta_boxes( get_current_screen(), 'after_title', $post ); ?>
			<div id="wsuwp-profile-tabs">
				<ul>
					<li><a href="#wsuwp-profile-default" class="nav-tab">About</a></li>
					<li><a href="#wsuwp-profile-research" class="nav-tab">Research</a></li>
					<li><a href="#wsuwp-profile-teaching" class="nav-tab">Teaching</a></li>
					<li><a href="#wsuwp-profile-service" class="nav-tab">Service</a></li>
          <li><a href="#wsuwp-profile-extension" class="nav-tab">Extension</a></li>
          <li><a href="#wsuwp-profile-publications" class="nav-tab">Publications</a></li>
				</ul>
				<div id="wsuwp-profile-default" class="wsuwp-profile-panel">
					<p class="description">All fields are optional. Click the <i class="mce-ico mce-i-wp_help wsuwp-profile-help"></i>for notes and formatting examples.</p>
					<h3>Biography</h3>
			<?php
		endif;

	}

	/**
	 * Add markup after the default editor of the edit screen for the Personnel content type.
	 */
	public function edit_form_after_editor( $post ) {

		if ( $this->personnel_content_type === $post->post_type ) :
			add_thickbox();
			?>
					<div id="wsuwp-profile-experience-lb" style="display:none;">
						<p>Include additional courses for credit, in-service training, and other major professional development activities.</p>
						<p>Formatting example:</p>
						<ul>
							<li>2006-2007<br />
              Spanish for Professionals, Gonzaga University Certificate Summer Program</li>
							<li>2004<br />
              Indigenous Economic Development Course, International Economic Development Council</li>
						</ul>
					</div>
					<h3 class="wpuwp-profile-label">Professional Experience <a href="#TB_inline?width=600&height=700&inlineId=wsuwp-profile-experience-lb" class="thickbox wsuwp-profile-help-link" title="Professional Experience"><i class="mce-ico mce-i-wp_help wsuwp-profile-help"></i></a></h3>
					<?php wp_editor( get_post_meta( $post->ID, '_wsuwp_profile_experience', true ), '_wsuwp_profile_experience' ); ?>
					<div id="wsuwp-profile-honors-lb" style="display:none;">
						<p>Formatting example:</p>
						<ul>
							<li>2014<br />
              Faculty Excellence in Extension Award, Washington State University</li>
							<li>2004<br />
              Gold Award for Digitally Curriculum, "Training Local Entrepreneurs,” Natural Resource Extension Professionals (ANREP)</li>
						</ul>
					</div>
					<h3 class="wpuwp-profile-label">Honors and Awards <a href="#TB_inline?width=600&height=700&inlineId=wsuwp-profile-honors-lb" class="thickbox wsuwp-profile-help-link" title="Honors and Awards"><i class="mce-ico mce-i-wp_help wsuwp-profile-help"></i></a></h3>
					<?php wp_editor( get_post_meta( $post->ID, '_wsuwp_profile_honors', true ), '_wsuwp_profile_honors' ); ?>
				</div><!--wsuwp-profile-default-->

				<div id="wsuwp-profile-research" class="wsuwp-profile-panel">
					<p class="description">All fields are optional. Click the <i class="mce-ico mce-i-wp_help wsuwp-profile-help"></i>for notes and formatting examples.</p>
					<h3>Interests</h3>
					<?php wp_editor( get_post_meta( $post->ID, '_wsuwp_profile_research', true ), '_wsuwp_profile_research' ); ?>
					<div id="wsuwp-profile-funds-lb" style="display:none;">
						<p>Include:</p>
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
					  <p>Formatting example:</p>
					  <ul>
							<li>Assessing the effects of global warming on nitrogen cycling in eastern Washington. Agriculture and Food Research Initiative, US Department of Agriculture. $428,802. P.I.: <strong>J. A. Smith</strong> and Co-P.I.: R. N. Jones. (3/08 – 3/12) <strong>(1, 3, 4, $300,000).</strong></li>
						</ul>
						<p><em>Notes:</em></p>
					  <ul>
							<li><em>Briefly explain why you are listed on a grant if none of the indicators above explain your contribution.</em></li>
							<li><em>The following key will be included on your profile if content is included in this field:<br />
            Key to indicators or description of contributions to Grants, Contracts and Fund Generation: 1 = Provided the initial idea; 2 = Developed research/program design and hypotheses; 3 = Authored or co-authored grant application; 4 = Developed and/or managed budget; 5 = Managed personnel, partnerships, and project activities.</em></li>
						</ul>
					</div>
					<h3>Grants, Contracts, and Fund Generation <a href="#TB_inline?width=600&height=700&inlineId=wsuwp-profile-funds-lb" class="thickbox wsuwp-profile-help-link" title="Grants, Contracts, and Fund Generation"><i class="mce-ico mce-i-wp_help wsuwp-profile-help"></i></a></h3>
					<?php wp_editor( get_post_meta( $post->ID, '_wsuwp_profile_grants', true ), '_wsuwp_profile_grants' ); ?>
				</div>

				<div id="wsuwp-profile-teaching" class="wsuwp-profile-panel">
					<p class="description">All fields are optional. Click the <i class="mce-ico mce-i-wp_help wsuwp-profile-help"></i>for notes and formatting examples.</p>
					<div id="wsuwp-profile-teaching-lb" style="display:none;">
						<p>Formatting example:</p>
						<p><strong>Credit Courses Taught</strong></p>
						<ul>
							<li>2013<br />
              Micro Economics and Local Development, graduate course. School of Economics, Everstate College – Spokane, WA</li>
						</ul>
						<p><strong>Additional Teaching</strong></p>
						<ul>
							<li>2012<br />
              Micro Economics and Local Investing, undergraduate course. School of Economics, Everstate College – Spokane, WA (guest lecturer)</li>
						</ul>
						<p><strong>Advising</strong> (Graduate Students and Student Interns)</p>
						<ul>
							<li>2011<br />
              Derek Ohlgren, MS, Civil and Environmental Engineering, Washington State University, (thesis committee)</li>
						</ul>
					</div>
					<h3>Credit Courses Taught, Additional Teaching, and Advising <a href="#TB_inline?width=600&height=700&inlineId=wsuwp-profile-teaching-lb" class="thickbox wsuwp-profile-help-link" title="Credit Courses Taught, Additional Teaching, and Advising"><i class="mce-ico mce-i-wp_help wsuwp-profile-help"></i></a></h3>
					<?php wp_editor( get_post_meta( $post->ID, '_wsuwp_profile_teaching', true ), '_wsuwp_profile_teaching' ); ?>
				</div>

				<div id="wsuwp-profile-service" class="wsuwp-profile-panel">
					<p class="description">All fields are optional. Click the <i class="mce-ico mce-i-wp_help wsuwp-profile-help"></i>for notes and formatting examples.</p>
					<div id="wsuwp-profile-service-lb" style="display:none;">
						<p>Formatting example:</p>
						<p><strong>University</strong></p>
						<ul>
							<li>2013<br />
              Washington State University Strategic Plan Taskforce – Chair<br />
              WSU Extension Director Search Committee – member</li>
							<li>2010<br />
              Washington State University Faculty Senate – Senator</li>
						</ul>
						<p><strong>Professional Society</strong></p>
						<ul>
							<li>2008<br />
              National Associate of Economic Developers – Western Regional Chair</li>
							<li>2007<br />
              Extension Professionals Society – Conference Planning Chair</li>
						</ul>
						<p><strong>Community</strong></p>
						<ul> 
							<li>2012<br />
              Washington Local Investment Coalition – President</li>
							<li>2011 - Present<br />
              Washington Banking Association Advisory Board - member</li>
						</ul>
						<p><strong>Review Activities</strong></p>
						<ul>
							<li>2010-Present<br />
              Doe, J., C. Ray, D. Mee, (editors) Journal of Metropolitan Extension; a journal of the Society for Urban Extension. <a href="#">Read the Journal at W. Coyote</a> [Online Library]</li>
						</ul>
					</div>
					<h3>University, Professional Society, Community, and Review Activities <a href="#TB_inline?width=600&height=700&inlineId=wsuwp-profile-service-lb" class="thickbox wsuwp-profile-help-link" title="University, Professional Society, Community, and Review Activities"><i class="mce-ico mce-i-wp_help wsuwp-profile-help"></i></a></h3>
					<?php wp_editor( get_post_meta( $post->ID, '_wsuwp_profile_service', true ), '_wsuwp_profile_service' ); ?>
				</div>

				<div id="wsuwp-profile-extension" class="wsuwp-profile-panel">
					<?php wp_editor( get_post_meta( $post->ID, '_wsuwp_profile_extension', true ), '_wsuwp_profile_extension' ); ?>
				</div>

				<div id="wsuwp-profile-publications" class="wsuwp-profile-panel">
					<p class="description">All fields are optional. Click the <i class="mce-ico mce-i-wp_help wsuwp-profile-help"></i>for notes and formatting examples.</p>
					<div id="wsuwp-profile-pubs-lb" style="display:none;">
						<p>Include:</p>
						<ul>
							<li>Peer-reviewed Journal Articles. (The journal must have a professional organization or corporate entity with an editor that manages a blind, peer review process.)</li>
							<li>Peer-reviewed Extension Publications. (The publication must have a formal publishing organization such as WSU Extension that manages a blind, peer review process.)</li>
							<li>Peer-reviewed Curricula and Training Manuals (Published curricula and training manuals that have been formally peer reviewed by WSU Extension or another institution.)</li>
							<li>Published Books, Book Chapters, or Monographs (Specify when an entry was peer reviewed according to the criteria described in A or B above.)</li>
							<li>Creative Scholarship in Juried Events. (Abstracts, Posters, and Published Papers in Proceedings of a Professional Meeting or Conference. (Note: These are generally not peer reviewed but they may be peer approved or selected through a process.)</li>
							<li>Educational Digital Media (Videos, computer programs, mobile aps, dynamic web-pages, social media, blogs, online modules, decision aids, email list-serves, etc.) (Designate products that received formal peer-review with an * and indicate the entity managing the review.)</li>
							<li>Other Publications and Creative Works (Those products that did not receive formal peer review, and include popular press articles, newsletters, and other written works)</li>
							<li>International, National, State, and Local Presentations.</li>
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
						<p>Formatting example:</p>
						<p><strong>Smith, J. A.</strong>, and R. N. Jones. 2010. The effects of global warming on nitrogen cycling in eastern Washington. Northwestern Ecosystems 45:300-308. <strong>(1, 2, 4, 5)</strong></p>
						<p><em>Notes:</em></p>
						<ul>
							<li><em>Briefly explain why you are listed on a publication if none of the indicators above explain your contribution.</em></li>
							<li><em>The following key will be included on your profile if content is included in this field:<br />
              Key to indicators or description of contributions to Publications and Creative Work: 1 = Developed the initial idea; 2 = Obtained or provided funds or other resources; 3 = Collected data; 4 = Analyzed data; 5 = Wrote/created product; 6 = Edited product.</em></li>
						</ul>
					</div>
					<h3>Publications, Creative Work, and Presentations <a href="#TB_inline?width=600&height=550&inlineId=wsuwp-profile-pubs-lb" class="thickbox wsuwp-profile-help-link" title="Publications, Creative Work, and Presentations"><i class="mce-ico mce-i-wp_help wsuwp-profile-help"></i></a></h3>
					<?php wp_editor( get_post_meta( $post->ID, '_wsuwp_profile_publications', true ), '_wsuwp_profile_publications' ); ?>
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
			'wsuwp_profile_a_position_info',
			'Position and Contact Information',
			array( $this, 'display_position_info_meta_box' ),
			$this->personnel_content_type,
			'side',
			'high'
		);

		add_meta_box(
			'wsuwp_profile_b_cv_upload',
			'Curriculum Vitae',
			array( $this, 'display_cv_upload_meta_box' ),
			$this->personnel_content_type,
			'side',
			'high'
		);

		// Bio meta boxes.
		add_meta_box(
			'wsuwp_profile_contact_info',
			'Alternate Contact Information',
			array( $this, 'display_bio_contact_meta_box' ),
			$this->personnel_content_type,
			'after_title',
			'high'
		);

		add_meta_box(
			'wsuwp_profile_degree_info',
			'Degrees Earned',
			array( $this, 'display_degree_info_meta_box' ),
			$this->personnel_content_type,
			'after_title',
			'high'
		);

		// Co-editor meta box (should probably be handled with editorial access manager).
		/*add_meta_box(
			'wsuwp_profile_coeditor',
			'Editors',
			array( $this, 'display_profile_coeditor_meta_box' ),
			$this->personnel_content_type,
			'advanced',
			'high'
		);*/

	}

	/**
	 * Display a meta box used to show a person's "card".
	 */
	public function display_position_info_meta_box( $post ) {

		wp_nonce_field( 'wsuwsp_profile', 'wsuwsp_profile_nonce' );

		$nid        = get_post_meta( $post->ID, '_wsuwp_profile_ad_nid', true );
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

		/**
		 * Just an idea...
		 * We'll pull this data from AD for all WSU people (who will presumably have a NID),
		 * but we don't want them to edit it here. We do, however, want to allow non-WSU folk
		 * whom we're hosting a profile for to be able to add contact info.
		 * So, let's leverage the NID to offer up a different presentation for those situations.
		 */
		if ( $nid ) : ?>

		<div class="profile-card">

			<div>
				<div>Network ID</div>
				<div><?php echo esc_html( $nid ); ?></div>
			</div>

			<?php if ( $name_first || $name_last ) : ?>
			<div>
				<div>Name</div>
				<div><?php if ( $name_first ) { echo esc_html( $name_first ) . ' '; } if ( $name_last ) { echo esc_html( $name_last ); } ?></div>
      </div>
			<?php endif; ?>

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

			<?php if ( $title ) : ?>
			<div>
				<div>Title</div>
      	<div><?php echo esc_html( $title ); ?></div>
			</div>
			<?php endif; ?>

			<?php if ( $office ) : ?>
			<div>
				<div>Office</div>
      	<div><?php echo esc_html( $office ); ?></div>
			</div>
			<?php endif; ?>

			<?php if ( $address ) : ?>
			<div>
				<div>Address</div>
      	<div><?php echo esc_html( $address ); ?></div>
			</div>
			<?php endif; ?>

			<?php if ( $phone ) : ?>
			<div>
				<div>Phone</div>
      	<div><?php echo esc_html( $phone ); if ( $phone_ext ) { echo ' ' . esc_html( $phone_ext ); } ?></div>
			</div>
			<?php endif; ?>

			<?php if ( $email ) : ?>
			<div>
				<div>Email</div>
      	<div><?php echo esc_html( $email ); ?></div>
			</div>
			<?php endif; ?>

		</div>
		<p class="description">Notify <a href="#">HR</a> if any of this information is incorrect or needs updated.</p>
		<?php else : ?>
		<span class="button" id="load-ad-data">Load</span>
			<span class="button button-primary profile-hide-button" id="confirm-ad-data">Confirm</span>
			<input type="hidden" id="confirm-ad-hash" name="confirm_ad_hash" value="" />
		<p><label for="_wsuwp_profile_ad_name_first">Network ID</label><br />
		<input type="text" id="_wsuwp_profile_ad_nid" name="_wsuwp_profile_ad_nid" value="<?php echo esc_attr( $nid ); ?>" class="widefat" /></p>
		<p><label for="_wsuwp_profile_ad_name_first">First Name</label><br />
		<input type="text" id="_wsuwp_profile_ad_name_first" name="_wsuwp_profile_ad_name_first" value="<?php echo esc_attr( $name_first ); ?>" class="widefat" /></p>
		<p><label for="_wsuwp_profile_ad_name_last">Last Name</label><br />
		<input type="text" id="_wsuwp_profile_ad_name_last" name="_wsuwp_profile_ad_name_last" value="<?php echo esc_attr( $name_last ); ?>" class="widefat" /></p>
		<p><label for="_wsuwp_profile_ad_title">Title</label><br />
		<input type="text" id="_wsuwp_profile_ad_title" name="_wsuwp_profile_ad_title" value="<?php echo esc_attr( $title ); ?>" class="widefat" /></p>
		<p><label for="_wsuwp_profile_ad_office">Office Location</label><br />
		<input type="text" id="_wsuwp_profile_ad_office" name="_wsuwp_profile_ad_office" value="<?php echo esc_attr( $office ); ?>" class="widefat" /></p>
		<p><label for="_wsuwp_profile_ad_address">Physical/Mailing Address</label><br />
		<input type="text" id="_wsuwp_profile_ad_address" name="_wsuwp_profile_ad_address" value="<?php echo esc_attr( $address ); ?>" class="widefat" /></p>
		<div class="phone-fields">
			<div>
				<label for="_wsuwp_profile_ad_phone">Phone Number <span class="description">(xxx-xxx-xxxx)</span></label><br />
				<input type="text" id="_wsuwp_profile_ad_phone" name="_wsuwp_profile_ad_phone" value="<?php echo esc_attr( $phone ); ?>" class="widefat" maxlength="12" />
			</div>
			<div>
			 	<label for="_wsuwp_profile_ad_phone_ext">Ext</label><br />
				<input type="text" id="_wsuwp_profile_ad_phone_ext" name="_wsuwp_profile_ad_phone_ext" value="<?php echo esc_attr( $phone_ext ); ?>" class="widefat" />
			</div>
		</div>
		<p><label for="_wsuwp_profile_ad_email">Email Address</label><br />
		<input type="text" id="_wsuwp_profile_ad_email" name="_wsuwp_profile_ad_email" value="<?php echo esc_attr( $email ); ?>" class="widefat" /></p>

		<?php endif;

	}

	/**
	 * Display a meta box used to upload a person's C.V.
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
					<div id="wsuwp-profile-cvs-lb" style="display:none;">
						<p>Include categorized documents here or something...</p>
						<p>CAHNRS CV</p>
						<p>Extension CV</p>
					</div>
        	<p>(<em><a href="#TB_inline?width=600&height=550&inlineId=wsuwp-profile-cvs-lb" class="thickbox wsuwp-profile-help-link" title="Download C.V. Template">Download C.V. template</a></em>)</p>
			</div>
		<?php

	}

	/**
	 * Remove or move certain meta boxes.
	 */
	public function do_meta_boxes() {

		// Remove "Appointment" and "Classification" meta boxes.
		remove_meta_box( 'appointmentdiv', $this->personnel_content_type, 'side' );
		remove_meta_box( 'classificationdiv', $this->personnel_content_type, 'side' );
		
		// Move and re-label the Featured Image meta box.
		remove_meta_box( 'postimagediv', $this->personnel_content_type, 'side' );
		add_meta_box( 'postimagediv', 'Profile Photo', 'post_thumbnail_meta_box', $this->personnel_content_type, 'side', 'high' );

	}

	/**
	 * Display a meta box under the "General" tab to collect additional or alternate contact info.
	 */
	public function display_bio_contact_meta_box( $post ) {

		$titles = get_post_meta( $post->ID, '_wsuwp_profile_title', true );

		if ( $titles && is_array( $titles ) ) :
			foreach ( $titles as $index => $title ) :
			?>
			<p class="wp-profile-repeatable"><label for="_wsuwp_profile_title[<?php echo esc_attr( $index ); ?>]">Working Title</label><br />
			<input type="text" id="_wsuwp_profile_title[<?php echo esc_attr( $index ); ?>]" name="_wsuwp_profile_title[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $title ); ?>" class="widefat" /></p>
			<?php
			endforeach;
		else :
			?>
			<p class="wp-profile-repeatable"><label for="_wsuwp_profile_title[0]">Working Title</label><br />
			<input type="text" id="_wsuwp_profile_title[0]" name="_wsuwp_profile_title[0]" value="<?php echo esc_attr( $titles ); ?>" class="widefat" /></p>
			<?php
		endif;
		?>
		<p class="wsuwp-profile-add-repeatable"><a href="#">+ Add another title</a></p>

		<p><label for="_wsuwp_profile_alt_office">Office</label><br />
		<input type="text" id="_wsuwp_profile_alt_office" name="_wsuwp_profile_alt_office" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_alt_office', true ) ); ?>" class="widefat" /></p>
		<p><label for="_wsuwp_profile_alt_phone">Phone Number <span class="description">(xxx-xxx-xxxx)</span></label><br />
		<input type="text" id="_wsuwp_profile_alt_phone" name="_wsuwp_profile_alt_phone" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_alt_phone', true ) ); ?>" class="widefat" /></p>
		<p><label for="_wsuwp_profile_alt_email">Email Address</label><br />
		<input type="text" id="_wsuwp_profile_alt_email" name="_wsuwp_profile_alt_email" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_alt_email', true ) ); ?>" class="widefat" /></p>
		<p><label for="_wsuwp_profile_website">Website URL</label><br />
		<input type="text" id="_wsuwp_profile_website" name="_wsuwp_profile_website" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_website', true ) ); ?>" class="widefat" /></p>
		<?php

	}

	/**
	 * Display a meta box used to enter a persons degree information.
	 */
	public function display_degree_info_meta_box( $post ) {

		$degrees = get_post_meta( $post->ID, '_wsuwp_profile_degree', true );

		if ( $degrees && is_array( $degrees ) ) :
			foreach ( $degrees as $index => $degree ) :
			?>
			<p class="wp-profile-repeatable"><label for="_wsuwp_profile_degree[<?php echo esc_attr( $index ); ?>]">Degree</label><br />
			<input type="text" id="_wsuwp_profile_degree[<?php echo esc_attr( $index ); ?>]" name="_wsuwp_profile_degree[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $degree ); ?>" class="widefat" /></p>
			<?php
			endforeach;
		else :
			?>
			<p class="wp-profile-repeatable"><label for="_wsuwp_profile_degree[0]">Degree</label><br />
			<input type="text" id="_wsuwp_profile_degree[0]" name="_wsuwp_profile_degree[0]" value="<?php echo esc_attr( $degrees ); ?>" class="widefat" /></p>
			<?php
		endif;
		?>
		<p class="wsuwp-profile-add-repeatable"><a href="#">+ Add another degree</a></p>
		<?php

	}

	/**
	 * Display a meta box used to assign additional editorship of a profile.
	 */
	public function display_profile_coeditor_meta_box( $post ) {

		?>
			<p>To grant another user the ability to edit your profile, add them here.</p>
			<p><input type="text" id="_wsuwp_profile_coeditor" name="_wsuwp_profile_coeditor" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wsuwp_profile_coeditor', true ) ); ?>" class="widefat" /></p>
		<?php

	}

	/**
	 * Save post meta data.
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

		// Sync these with AD.
		/*foreach ( $this->ad_fields as $field ) {
			if ( isset( $_POST[ $field ] ) && '' != $_POST[ $field ] ) {
				update_post_meta( $post_id, $field, sanitize_text_field( $_POST[ $field ] ) );
			} else {
				delete_post_meta( $post_id, $field );
			}
		}*/

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
		foreach ( $this->wp_editors as $field ) {
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

		$revisioned_fields = array_merge( $this->basic_fields, $this->repeatable_fields, $this->wp_editors );

		foreach ( $revisioned_fields as $field ) {
			$keys[] = $field;
		}

		return $keys;
	}

	/**
	 * Taxonomy columns on the "All Profiles" screen.
	 */
	public function wsuwp_people_profile_columns( $columns ) {
		//unset($columns['category']);
   	unset($columns['post_tag']);
		$columns[] = $this->personnel_appointments;
		$columns[] = $this->personnel_classifications;
		$columns[] = 'wsuwp_university_location';
    return $columns;
	}

	/**
	 * Include meta in the REST API output.
	 */
	public function json_prepare_post( $post_response, $post, $context ) {

		if ( $this->personnel_content_type !== $post['post_type'] ) {
			return $post_response;
		}

		$all_fields = array_merge( $this->ad_fields, $this->basic_fields, $this->repeatable_fields, $this->wp_editors );

		foreach ( $all_fields as $field ) {
			$post_response[ $field ] = get_post_meta( $post['ID'], $field, true );
		}

		// Syndicate the CV URL instead of attachment ID.
		$cv = get_post_meta( $post['ID'], '_wsuwp_profile_cv', true );
		$post_response['_wsuwp_profile_cv'] = esc_url( wp_get_attachment_url( $cv ) );

		$post_response['_wsuwp_profile_name'] = get_post_meta( $post['ID'], '_wsuwp_profile_name', true );

		return $post_response;

	}

	/**
	 * Add 'meta_key' to the list of public variables (leverage for ordering alphabetically by last name).
	 * It seems this may have security implications, though I admit I don't understand what they would be.
	 */
	public function json_query_vars( $valid_vars ) {

		$valid_vars[] = 'meta_key';

		return $valid_vars;

	}

	/**
	 * Add "Organization Administrator" field in profile.
	 */
	public function personal_options( $user ) {

		if ( IS_PROFILE_PAGE ) {
			return;
		}

		?>
		<tr>
			<th><label for="wsuwp_people_organization_admin">Organization Administrator for</label></th>
			<td><input type="text" id="wsuwp_people_organization_admin" name="wsuwp_people_organization_admin" value="<?php echo esc_attr( get_user_meta( $user->ID, 'wsuwp_people_organization_admin', true ) ); ?>" /></td>
		</tr>
		<?php
	}

	/**
	 * Store the type of authentication assigned to a user.
	 *
	 * @param int $user_id ID of the user being edited.
	 */
	public function edit_user_profile_update( $user_id ) {

		if ( ! current_user_can( 'edit_users', $user_id ) ) {
			return;
		}

		if ( isset( $_POST['wsuwp_people_organization_admin'] ) && '' != $_POST['wsuwp_people_organization_admin'] ) {
			update_user_meta( $user_id, 'wsuwp_people_organization_admin', sanitize_text_field( $_POST['wsuwp_people_organization_admin'] ) );
		} else {
			delete_user_meta( $user_id, 'wsuwp_people_organization_admin' );
		}

	}

	/**
	 * Capability modifications.
	 */
	public function user_has_cap( $allcaps, $cap, $args ) {

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

		// Bail if the user isn't an Organization Administrator or listed as a coeditor:
		$coeditor = get_post_meta( $post->ID, '_wsuwp_profile_coeditor', true );
		$dept = get_post_meta( $post->ID, '_wsuwp_profile_dept', true );
		$org_admin = get_user_meta( $args[1], 'wsuwp_people_organization_admin', true );
		if ( ( $args[1] != $coeditor ) && ( $org_admin != $dept ) ) {
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
				'Welcome to the CAHNRS Directory',
				//$this->wsuwp_directory_dashboard_widget
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

			if ( 'upload' !== get_current_screen()->base && 'query-attachments' !== $_REQUEST['action'] ) {
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
	 * Add templates for the Personnel custom content type.
	 */
	public function template_include( $template ) {

		if ( $this->personnel_content_type == get_post_type() && is_single() ) {
			$template = plugin_dir_path( __FILE__ ) . 'templates/single.php';
		}

		if ( is_post_type_archive( $this->personnel_content_type ) || is_tax() || is_category() || is_tag() ) {
			$template = plugin_dir_path( __FILE__ ) . 'templates/archive.php';
		}

		return $template;

	}

	/**
	 * Enqueue the scripts and styles used on the front end.
	 */
	public function wp_enqueue_scripts() {

		if ( $this->personnel_content_type == get_post_type() ) {
			if ( is_single() ) {
				wp_enqueue_style( 'wsuwp-people-profile-style', plugins_url( 'css/profile.css', __FILE__ ), array( 'dashicons' ), $this->personnel_plugin_version );
				wp_enqueue_script( 'wsuwp-people-profile-script', plugins_url( 'js/profile.js', __FILE__ ), array( 'jquery-ui-tabs' ), $this->personnel_plugin_version, true );
			}
			if ( is_archive() ) {
				wp_enqueue_style( 'wsuwp-people-archive-style', plugins_url( 'css/archive.css', __FILE__ ), array(), $this->personnel_plugin_version );
			}
		}

	}

	/**
	 * Order public Personnel query results alphabetically by last name
	 */
	public function profile_archives( $query ) {

		if ( ( $query->is_post_type_archive( $this->personnel_content_type ) || is_tax() || is_category() || is_tag() ) && $query->is_main_query() && ! is_admin() ) {
			$query->set( 'order', 'ASC' );
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'meta_key', '_wsuwp_profile_ad_name_last' );
		}

	}

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
			$return_data['given_name'] = $nid_data['givenname'][0];
		}

		if ( isset( $nid_data['sn'][0] ) ) {
			$return_data['surname'] = $nid_data['sn'][0];
		}

		if ( isset( $nid_data['title'][0] ) ) {
			$return_data['title'] = $nid_data['title'][0];
		}

		if ( isset( $nid_data['physicaldeliveryofficename'][0] ) ) {
			$return_data['office'] = $nid_data['physicaldeliveryofficename'][0];
		}

		if ( isset( $nid_data['streetaddress'][0] ) ) {
			$return_data['street_address'] = $nid_data['streetaddress'][0];
		}

		if ( isset( $nid_data['telephonenumber'][0] ) ) {
			$return_data['telephone_number'] = $nid_data['telephonenumber'][0];
		}

		if ( isset( $nid_data['mail'][0] ) ) {
			$return_data['email'] = $nid_data['mail'][0];
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