<?php

class WSUWP_Person_Display {
	/**
	 * @var WSUWP_Person_Display
	 *
	 * @since 0.3.0
	 */
	private static $instance;

	/**
	 * Maintain and return the one instance. Initiate hooks when called the first time.
	 *
	 * @since 0.3.0
	 *
	 * @return \WSUWP_Person_Display
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSUWP_Person_Display();
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
		if ( false === apply_filters( 'wsuwp_people_default_rewrite_slug', false ) ) {
			add_action( 'init', array( $this, 'rewrite_rules' ), 11 );
			add_filter( 'post_type_link', array( $this, 'person_permalink' ), 10, 2 );
		}

		add_filter( 'template_include', array( $this, 'template_include' ) );
	}

	/**
	 * Adds rewrite rules for person views under each directory page's path.
	 *
	 * @since 0.3.0
	 */
	public function rewrite_rules() {
		$pages = get_posts( array(
			'post_type' => 'page',
			'meta_key' => '_wp_page_template',
			'meta_value' => key( WSUWP_People_Directory_Page_Template::$template ),
		) );

		if ( $pages ) {
			foreach ( $pages as $page ) {
				add_rewrite_rule(
					'^' . get_page_uri( $page->ID ) . '/([^/]*)/?',
					'index.php?' . WSUWP_People_Post_Type::$post_type_slug . '=$matches[1]',
					'top'
				);
			}
		}
	}

	/**
	 * Changes the permalink structure for a person.
	 *
	 * @since 0.3.0
	 *
	 * @param string $url  The post URL.
	 * @param object $post The post object.
	 *
	 * @return string The modified URL.
	 */
	public function person_permalink( $url, $post ) {
		if ( WSUWP_People_Post_Type::$post_type_slug !== $post->post_type ) {
			return $url;
		}

		$directory_page_id = get_post_meta( $post->ID, 'on_page', true );

		if ( ! $directory_page_id ) {
			return $url;
		}

		$url = get_permalink( $directory_page_id ) . $post->post_name . '/';

		return $url;
	}

	/**
	 * Check if a theme is providing its own person template.
	 *
	 * @since 0.3.0
	 *
	 * @return string Path to the template file.
	 */
	public static function theme_has_template() {
		return locate_template( 'wsu-people/person.php' );
	}

	/**
	 * Determine which template to use.
	 *
	 * If using the plugin defaults, enqueue a stylesheet and filter the content.
	 *
	 * @since 0.3.0
	 *
	 * @param string $template The path of the template to include.
	 *
	 * @return string The path of the template to include.
	 */
	public function template_include( $template ) {
		if ( ! is_singular( WSUWP_People_Post_Type::$post_type_slug ) ) {
			return $template;
		}

		$photo = get_post_meta( get_the_ID(), '_use_photo', true );
		$title = get_post_meta( get_the_ID(), '_use_title', true );
		$bio = get_post_meta( get_the_ID(), '_use_bio', true );

		// Update the canonical tag if the displayed content matches the primary profile.
		if ( ( ! $photo || 0 === $photo ) && ( ! $bio || 'personal' === $bio ) && ! $title ) {
			remove_action( 'wp_head', 'rel_canonical' );
			add_action( 'wp_head', array( $this, 'rel_canonical' ) );
		}

		wp_enqueue_style( 'wsu-people-display', plugin_dir_url( dirname( __FILE__ ) ) . 'css/person.css', array(), WSUWP_People_Directory::$version );

		add_filter( 'the_content', array( $this, 'content' ) );

		return trailingslashit( get_template_directory() ) . 'single.php';
	}

	/**
	 * Adds a canonical meta tag.
	 *
	 * @since 0.3.1
	 */
	public function rel_canonical() {
		$source = get_post_meta( get_the_ID(), '_canonical_source', true );
		if ( $source ) {
			?>
			<link rel="canonical" href="<?php echo esc_url( $source ); ?>" />
			<?php
		}
	}

	/**
	 * Filters the content for a person view.
	 *
	 * @since 0.3.0
	 *
	 * @return string Modified content.
	 */
	public function content() {
		remove_filter( 'the_content', array( $this, 'content' ) );

		$local_record_id = get_post()->ID;
		$nid = get_post_meta( $local_record_id, '_wsuwp_profile_ad_nid', true );
		$person = WSUWP_People_Post_Type::get_rest_data( $nid );

		$display_options = array(
			'title' => get_post_meta( $local_record_id, '_use_title', true ),
			'use_photo' => get_post_meta( $local_record_id, '_use_photo', true ),
			'about' => get_post_meta( $local_record_id, '_use_bio', true ),
		);

		$display = self::get_data( $person, $display_options );

		ob_start();

		// If a theme has a person template, use it.
		if ( $this->theme_has_template() ) {
			include $this->theme_has_template();
		} else {
			include plugin_dir_path( dirname( __FILE__ ) ) . 'templates/person.php';
		}

		$content = ob_get_clean();

		return $content;
	}

	/**
	 * Returns profile data from people.wsu.edu.
	 *
	 * @since 0.3.0
	 *
	 * @param object $person
	 *
	 * @return array
	 */
	public static function get_wsu_person( $person ) {
		$data = array(
			'post_id' => $person->id,
			'nid' => $person->nid,
			'first_name' => $person->first_name,
			'last_name' => $person->last_name,
			'name' => $person->title->rendered,
			'ad_title' => $person->position_title,
			'ad_office' => $person->office,
			'ad_address' => $person->address,
			'ad_phone' => $person->phone,
			'ad_phone_extension' => $person->phone_ext,
			'ad_email' => $person->email,
			'titles' => $person->working_titles,
			'office' => $person->office_alt,
			'address' => $person->address_alt,
			'phone' => $person->phone_alt,
			'email' => $person->email_alt,
			'degrees' => $person->degree,
			'website' => $person->website,
			'photos' => $person->photos,
			'listed_on' => $person->listed_on,
			'bio_personal' => $person->content->rendered,
			'bio_unit' => $person->bio_unit,
			'bio_university' => $person->bio_university,
		);

		return $data;
	}

	/**
	 * Returns profile data with any local variations.
	 *
	 * @since 0.3.0
	 *
	 * @param object $person
	 * @param array  $options
	 *
	 * @return array
	 */
	public static function get_data( $person, $options = array() ) {
		$primary_data = self::get_wsu_person( $person );

		$local_post = get_posts( array(
			'post_type' => WSUWP_People_Post_Type::$post_type_slug,
			'posts_per_page' => 1,
			'meta_key' => '_wsuwp_profile_post_id',
			'meta_value' => $primary_data['post_id'],
		) );

		// Retrieve local content display options.
		if ( $local_post ) {
			$local_post_id = $local_post[0]->ID;
			$display_title = get_post_meta( $local_post_id, '_use_title', true );
			$display_photo = get_post_meta( $local_post_id, '_use_photo', true );
			$display_bio = get_post_meta( $local_post_id, '_use_bio', true );
		}

		// Set up contact information.
		$office = ( ! empty( $primary_data['office'] ) ) ? $primary_data['office'] : $primary_data['ad_office'];
		$address = ( ! empty( $primary_data['address'] ) ) ? $primary_data['address'] : $primary_data['ad_address'];
		$ad_phone = ( ! empty( $primary_data['ad_phone_extension'] ) ) ? $primary_data['ad_phone'] . ' ext ' . $primary_data['ad_phone_extension'] : $primary_data['ad_phone'];
		$phone = ( ! empty( $primary_data['phone'] ) ) ? $primary_data['phone'] : $ad_phone;
		$email = ( ! empty( $primary_data['email'] ) ) ? $primary_data['email'] : $primary_data['ad_email'];

		// Set up titles.
		$titles = ( ! empty( $primary_data['titles'] ) ) ? $primary_data['titles'] : array( $primary_data['ad_title'] );

		if ( ! empty( $display_title ) ) {
			$title_indexes = explode( ',', $display_title );
			$titles = array();

			foreach ( $title_indexes as $index ) {
				if ( ! empty( $primary_data['titles'][ $index ] ) ) {
					$titles[] = $primary_data['titles'][ $index ];
				}
			}
		}

		// Set up the photo.
		$photo = ( isset( $primary_data['photos'][0] ) ) ? $primary_data['photos'][0] : false;

		if ( ! empty( $display_photo ) && isset( $primary_data['photos'][ $display_photo ] ) ) {
			$photo = $primary_data['photos'][ $display_photo ];
		}

		// Set up the biography.
		$bio = $primary_data['bio_personal'];

		if ( $display_bio && 'personal' !== $display_bio ) {
			$bio = $primary_data[ $display_bio ];
		}

		$data = array(
			'primary_id' => $primary_data['post_id'],
			'nid' => $primary_data['nid'],
			'name' => $primary_data['name'],
			'titles' => $titles,
			'office' => $office,
			'address' => $address,
			'phone' => $phone,
			'email' => $email,
			'degrees' => $primary_data['degrees'],
			'website' => $primary_data['website'],
			'photo' => $photo,
			'about' => $bio,
		);

		// Retrieve directory page-specific content display options.
		if ( ! empty( $options['page_id'] ) && $local_post ) {
			$data = array(
				'primary_data' => $primary_data,
				'single_view_data' => $data,
				'local_post' => $local_post[0],
			);
		}

		return $data;
	}

	/**
	 * Returns profile data with any directory page-specific variations.
	 *
	 * @since
	 *
	 * @param object $person
	 * @param array  $options
	 *
	 * @return array
	 */
	public static function get_listing_data( $person, $options ) {
		$profile = self::get_data( $person, $options );
		$primary_data = $profile['primary_data'];
		$data = $profile['single_view_data'];
		$local_post = $profile['local_post'];
		$local_post_id = $local_post->ID;

		// Set up card classes.
		$card_classes = 'wsu-person';

		// Add a class for each taxonomy term.
		$taxonomies = get_object_taxonomies( WSUWP_People_Post_Type::$post_type_slug );
		$taxonomy_data = array();

		foreach ( $taxonomies as $taxonomy ) {
			$prefix = array_pop( explode( '_', $taxonomy ) );
			$terms = wp_get_post_terms( $local_post_id, $taxonomy, array(
				'fields' => 'names',
			) );

			foreach ( $terms as $term ) {
				$card_classes .= ' ' . $prefix . '-' . sanitize_title( $term );
			}
		}

		// Set up card attributes, starting with the post ID of the primary record.
		$card_attributes = ' data-profile-id="' . $data['primary_id'] . '"';
		$card_attributes .= ' data-nid="' . $person->nid . '"';

		// Add attributes to be leveraged for opening this profile in a modal.
		if ( 'lightbox' === $options['link']['profile'] && ! is_admin() ) {
			if ( ! empty( $data['titles'] ) ) {
				$card_attributes .= ' data-title="' . implode( '|', $data['titles'] ) . '"';
			}

			if ( ! empty( $data['photo'] ) ) {
				$card_attributes .= ' data-photo="' . esc_url( $data['photo']->thumbnail ) . '"';
			}

			if ( ! empty( $data['about'] ) ) {
				$card_attributes .= ' data-about="' . esc_html( $data['about'] ) . '"';
			}
		}

		// Add attributes to be leveraged when editing the directory page.
		if ( is_admin() ) {
			$card_attributes .= ' data-post-id="' . $local_post_id . '"';
			$card_attributes .= ' data-listed="' . implode( ' ', $primary_data['listed_on'] ) . '"';
			$card_attributes .= ' aria-checked="false"';
		}

		// Set up the link URL.
		if ( false !== apply_filters( 'wsuwp_people_default_rewrite_slug', false ) ) {
			$link = get_the_permalink( $local_post_id );
		} elseif ( 'if_bio' === $options['link']['when'] && '' !== $data['about'] || 'yes' === $options['link']['when'] ) {
			$link = trailingslashit( $options['link']['base_url'] . $local_post->post_name );
		}

		// Set up directory page-specific content display options.
		$directory_display = get_post_meta( $local_post_id, "_display_on_page_{$options['page_id']}", true );

		// Title(s).
		if ( ! empty( $directory_display['title'] ) ) {
			$title_indexes = explode( ',', $directory_display['title'] );
			$titles = array();

			foreach ( $title_indexes as $index ) {
				$titles[] = $primary_data['titles'][ $index ];
			}

			$data['titles'] = $titles;
		}

		// Photo.
		if ( ! empty( $directory_display['photo'] ) && isset( $primary_data['photos'][ $directory_display['photo'] ] ) ) {
			$data['photo'] = $primary_data['photos'][ $directory_display['photo'] ];
		}

		// Photo display can be set for the entire page.
		if ( isset( $options['photo'] ) && 'no' === $options['photo'] ) {
			$data['photo'] = false;
		}

		// Add a "has-photo" class if appropriate.
		if ( $data['photo'] ) {
			$card_classes .= ' has-photo';
		}

		// The "Bio/About" display can be set for the entire page.
		if ( ! empty( $options['about'] ) && 'personal' !== $options['about'] ) {
			if ( 'tags' === $options['about'] ) {
				$tags = wp_get_post_tags( $local_post_id, array(
					'fields' => 'names',
				) );
				$data['about'] = implode( ', ', $tags );
			} elseif ( 'none' === $options['about'] ) {
				$data['about'] = false;
			} else {
				$data['about'] = $primary_data[ $options['about'] ];
			}
		}

		// The "Bio/About" display can also be individually set.
		// (It shouldn't override the page setting for "None", though.)
		if ( ! empty( $directory_display['about'] ) && 'none' !== $options['about'] ) {
			if ( 'tags' === $directory_display['about'] ) {
				$tags = wp_get_post_tags( $local_post_id, array(
					'fields' => 'names',
				) );
				$data['about'] = implode( ', ', $tags );
			} else {
				$data['about'] = $primary_data[ $directory_display['about'] ];
			}
		}

		$data['local_id'] = ( isset( $local_post_id ) ) ? $local_post_id : false;
		$data['card_classes'] = $card_classes;
		$data['card_attributes'] = $card_attributes;
		$data['link'] = ( ! empty( $link ) ) ? $link : false;

		if ( is_admin() ) {
			$data['directory_view_options'] = plugin_dir_path( dirname( __FILE__ ) ) . 'templates/admin-person-options.php';
			$data['directory_view'] = ( $directory_display ) ? $directory_display : false;
		}

		return $data;
	}
}
