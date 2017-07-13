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
		add_action( 'init', array( $this, 'rewrite_rules' ), 11 );
		add_filter( 'post_type_link', array( $this, 'person_permalink' ), 10, 2 );
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
					'^' . $page->post_name . '/([^/]*)/?',
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

		remove_action( 'wp_head', 'rel_canonical' );
		add_action( 'wp_head', array( $this, 'rel_canonical' ) );

		wp_enqueue_style( 'wsu-people-display', plugin_dir_url( dirname( __FILE__ ) ) . 'css/person.css', array(), WSUWP_People_Directory::$version );

		add_filter( 'the_content', array( $this, 'content' ) );

		return trailingslashit( get_template_directory() ) . 'templates/single.php';
	}

	/**
	 * Adds a canonical meta tag.
	 *
	 * @since 0.3.2
	 */
	public function rel_canonical() {
		global $post;
		$source = get_post_meta( $post->ID, '_canonical_source', true );
		?>
		<link rel="canonical" href="<?php echo esc_url( $source ); ?>" />
		<?php
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

		$display = WSUWP_Person_Display::get_data( $person, $display_options );

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
	 * Returns a set of data for displaying a person.
	 *
	 * @param object $person
	 * @param array  $options
	 *
	 * @since 0.3.0
	 *
	 * @return array
	 */
	public static function get_data( $person, $options = array() ) {
		$card_classes = 'wsu-person';
		$card_attributes = '';
		$ad_phone = ( ! empty( $person->phone_ext ) ) ? $person->phone . ' ext ' . $person->phone_ext : $person->phone;
		$local_record_id = false;

		// Default "about" content (personal biography).
		$about = $person->content->rendered;

		// Set up any directory-specific content.
		if ( isset( $options['directory_view'] ) ) {
			$local_record = get_posts( array(
				'post_type' => WSUWP_People_Post_Type::$post_type_slug,
				'posts_per_page' => 1,
				'meta_key' => '_wsuwp_profile_post_id',
				'meta_value' => $person->id,
			) );

			if ( $local_record ) {
				$local_record_id = $local_record[0]->ID;
				$directory_display = get_post_meta( $local_record_id, "_display_on_page_{$options['page_id']}", true );
				$single_display_title = get_post_meta( $local_record_id, '_use_title', true );
				$single_display_photo = get_post_meta( $local_record_id, '_use_photo', true );
				$single_display_bio = get_post_meta( $local_record_id, '_use_bio', true );

				if ( 'if_bio' === $options['link']['when'] && '' !== $about || 'yes' === $options['link']['when'] ) {
					$options['link_url'] = trailingslashit( $options['link']['base_url'] . $local_record[0]->post_name );
				}

				$options['title'] = ( isset( $directory_display['title'] ) ) ? $directory_display['title'] : $single_display_title;
				$options['use_photo'] = ( isset( $directory_display['photo'] ) ) ? $directory_display['photo'] : $single_display_photo;

				// If the "about" option is set individually for this profile, use that instead of the global setting.
				if ( isset( $directory_display['about'] ) ) {
					$options['about'] = $directory_display['about'];
				}

				// Add classes for taxonomy terms (leveraged by filters).
				$get_terms_args = array(
					'fields' => 'names',
				);

				$locations = wp_get_post_terms( $local_record_id, 'wsuwp_university_location', $get_terms_args );

				foreach ( $locations as $location ) {
					$card_classes .= ' location-' . sanitize_title( $location );
				}

				$units = wp_get_post_terms( $local_record_id, 'wsuwp_university_org', $get_terms_args );

				foreach ( $units as $unit ) {
					$card_classes .= ' unit-' . sanitize_title( $unit );
				}

				// Add attributes to be leveraged for opening this profile in a modal.
				if ( 'lightbox' === $options['link']['profile'] ) {
					$card_attributes .= ' data-profile-id="' . $person->id . '"';

					if ( ! is_admin() ) {
						if ( $single_display_photo ) {
							$card_attributes .= ' data-photo="' . $single_display_photo . '"';
						}

						if ( $single_display_title ) {
							$card_attributes .= ' data-title="' . $single_display_title . '"';
						}

						if ( $single_display_bio ) {
							$card_attributes .= ' data-about="' . $single_display_bio . '"';
						}
					}
				}

				if ( is_admin() ) {
					$card_attributes .= ' data-nid="' . $person->nid . '"';
					$card_attributes .= ' data-post-id="' . $local_record_id . '"';
					$card_attributes .= ' data-listed="' . implode( ' ', $person->listed_on ) . '"';
					$card_attributes .= ' aria-checked="false"';
				}
			}
		}

		// Set up link URL.
		$link = ( isset( $options['link_url'] ) ) ? $options['link_url'] : false;

		// Set up what to display for the title.
		if ( isset( $options['title'] ) && '' !== $options['title'] ) {
			$title_indexes = explode( ' ', $options['title'] );
			$use_titles = array();

			foreach ( $title_indexes as $index ) {
				if ( 'ad' === $index ) {
					$use_titles[] = $person->position_title;
				} elseif ( isset( $person->working_titles[ $index ] ) ) {
					$use_titles[] = $person->working_titles[ $index ];
				}
			}

			$title = implode( '<br />', $use_titles );
		} else {
			$title = ( ! empty( $person->working_titles ) ) ? implode( '<br />', $person->working_titles ) : $person->position_title;
		}

		// Set up the photo.
		if ( isset( $options['photo'] ) && 'no' === $options['photo'] ) {
			$photo = false;
		} else {
			if ( isset( $options['use_photo'] ) && '' !== $options['use_photo'] && $person->photos[ $options['use_photo'] ] ) {
				$photo = $person->photos[ $options['use_photo'] ]->thumbnail;
			} else {
				$photo = ( $person->photos && $person->photos[0] ) ? $person->photos[0]->thumbnail : false;
			}
		}

		// Adds the "has-photo" class to the card container.
		if ( $photo ) {
			$card_classes .= ( ! empty( $person->photos ) ) ? ' has-photo' : '';
		}

		// Set up what to display for the "about" content.
		if ( isset( $options['about'] ) && 'personal' !== $options['about'] && '' !== $options['about'] ) {
			if ( 'tags' === $options['about'] ) {
				$tags = wp_get_post_tags( $local_record_id, array(
					'fields' => 'names',
				) );
				$about = implode( ', ', $tags );
			} elseif ( 'none' === $options['about'] ) {
				$about = false;
			} else {
				$about = $person->$options['about'];
			}
		}

		$data = array(
			'card_classes' => $card_classes,
			'card_attributes' => $card_attributes,
			'name' => $person->title->rendered,
			'title' => $title,
			'email' => ( ! empty( $person->email_alt ) ) ? $person->email_alt : $person->email,
			'phone' => ( ! empty( $person->phone_alt ) ) ? $person->phone_alt : $ad_phone,
			'office' => ( ! empty( $person->office_alt ) ) ? $person->office_alt : $person->office,
			'address' => $person->address,
			'website' => $person->website,
			'photo' => $photo,
			'about' => $about,
			'local_record_id' => $local_record_id,
			'header' => ( isset( $options['header'] ) ) ? true : false,
			'link' => $link,
			'directory_view' => ( isset( $options['directory_view'] ) ) ? $directory_display : false,
		);

		return $data;
	}
}
