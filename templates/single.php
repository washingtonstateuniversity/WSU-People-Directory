<?php get_header(); ?>

<main>

	<?php get_template_part( 'parts/headers' ); ?>

	<section class="row halves gutter pad-ends">

  	<div class="column one">

			<?php while ( have_posts() ) : the_post(); ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<?php if ( spine_get_option( 'articletitle_show' ) == 'true' ) : ?>
				<header class="article-header">
					<h1 class="article-title"><?php the_title(); ?></h1>
				</header>
				<?php endif; ?>

				<div class="article-body">

					<?php
          	// Meta data (excluding the content areas).
						$degrees    = get_post_meta( get_the_ID(), '_wsuwp_profile_degree', true );
						$title      = get_post_meta( get_the_ID(), '_wsuwp_profile_ad_title', true );
						$titles     = get_post_meta( get_the_ID(), '_wsuwp_profile_title', true );
						$phone      = get_post_meta( get_the_ID(), '_wsuwp_profile_ad_phone', true );
						$phone_ext  = get_post_meta( get_the_ID(), '_wsuwp_profile_ad_phone_ext', true );
						$alt_phone  = get_post_meta( get_the_ID(), '_wsuwp_profile_alt_phone', true );
						$office     = get_post_meta( get_the_ID(), '_wsuwp_profile_ad_office', true );
						$alt_office = get_post_meta( get_the_ID(), '_wsuwp_profile_alt_office', true );
						$email      = get_post_meta( get_the_ID(), '_wsuwp_profile_ad_email', true );
						$alt_email  = get_post_meta( get_the_ID(), '_wsuwp_profile_alt_email', true );
						$cv         = get_post_meta( get_the_ID(), '_wsuwp_profile_cv', true );
						$website    = get_post_meta( get_the_ID(), '_wsuwp_profile_website', true );

						// Taxonomy data.
						$departments     = wp_get_post_terms( get_the_ID(), 'cahnrs_unit' );
						$appointments    = wp_get_post_terms( get_the_ID(), 'appointment', array( 'fields' => 'names' ) );
						$classifications = wp_get_post_terms( get_the_ID(), 'classification', array( 'fields' => 'names' ) );
						$locations       = wp_get_post_terms( get_the_ID(), 'wsuwp_university_location', array( 'fields' => 'names' ) );

						// Degrees.
						if ( $degrees && is_array( $degrees ) ) {
							echo '<ul>';
							foreach ( $degrees as $degree ) {
								echo '<li class="degree">' . esc_html( $degree ) . '</li>';
							}
							echo '</ul>';
						}

						// Classification(s).
						if ( $classifications ) {
							echo '<p class="classifications">';
							foreach ( $classifications as $classification ) {
								echo '<span class="classification">' . esc_html( $classification ) . '</a></span>';
							}
							echo '</p>';
						}

						// Title(s).
						if ( $title || $titles ) {
							echo '<p>';
							if ( $title ) { echo esc_html( $title ); }
							if ( $titles ) {
            		foreach ( $titles as $additional_title ) :
              		echo "/\n<br />" . esc_html( $additional_title );
            		endforeach;
							}
							echo '</p>';
            }

						// Department(s).
						if ( $departments ) {
							echo '<p class="departments">';
							foreach ( $departments as $department ) {
								$dept = sanitize_term( $department, 'cahnrs_unit' );
								echo '<span class="department"><a href="' . esc_attr( get_term_link( $dept, 'cahnrs_unit' ) ) . '">' . esc_html( $dept->name ) . '</a></span>';
							}
							echo '</p>';
						}
						
						// Email.
						if ( $email || $alt_email ) {
							echo '<p class="contact email"><span class="dashicons dashicons-email"></span>';
							if ( $email ) { echo '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>'; }
							if ( $email && $alt_email ) { echo ' | '; }
							if ( $alt_email ) { echo '<a href="mailto:' . esc_attr( $alt_email ) . '">' . esc_html( $alt_email ) . '</a>'; }
							echo '</p>';
						}

						// Phone.
						if ( $phone || $alt_phone ) {
							echo '<p class="contact phone"><span class="dashicons dashicons-phone"></span>';
							if ( $phone ) { echo esc_html( $phone ); }
							if ( $phone && $alt_phone ) { echo ' | '; }
							if ( $alt_phone ) { echo esc_html( $alt_phone ); }
							echo '</p>';
						}

						// Office and location(s).
						if ( $office || $alt_office || $locations ) {
							echo '<p class="contact location"><span class="dashicons dashicons-location"></span>';
							if ( $office ) { echo esc_html( $office ); }
							if ( $office && $alt_office ) { echo ' | '; }
							if ( $alt_office ) { echo esc_html( $alt_office ); }
							if ( $locations ) {
								foreach ( $locations as $location ) {
									echo "<br />\n" . $location;
								}
							}
							echo '</p>';
						}

						// Curriculum Vitae.
						if ( $cv ) {
							echo '<p class="contact cv"><span class="dashicons dashicons-download"></span><a href="' . esc_url( wp_get_attachment_url( $cv ) ) . '">Curriculum Vitae</a></p>';
						}

						// Website.
						if ( $website ) {
							echo '<p class="contact website"><span class="dashicons dashicons-external"></span><a href="' . esc_url( $website ) . '">Website</a></p>';
						}

					?>

				</div>

			</article>

			<?php endwhile; ?>

		</div><!--/column-->

		<div class="column two">

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="profile-photo"><?php the_post_thumbnail( 'medium' ); /* Perhaps we should define a new image size for this purpose. */ ?></figure>
			<?php endif; ?>

		</div><!--/column two-->

	</section>

	<?php // Content areas meta.
		$about      = get_the_content();
		$experience = get_post_meta( get_the_ID(), '_wsuwp_profile_experience', true );
		$honors     = get_post_meta( get_the_ID(), '_wsuwp_profile_honors', true );
		$research   = get_post_meta( get_the_ID(), '_wsuwp_profile_research', true );
		$grants     = get_post_meta( get_the_ID(), '_wsuwp_profile_grants', true );
		$teaching   = get_post_meta( get_the_ID(), '_wsuwp_profile_teaching', true );
		$service    = get_post_meta( get_the_ID(), '_wsuwp_profile_service', true );
		$extension  = get_post_meta( get_the_ID(), '_wsuwp_profile_extension', true );
		$pubs       = get_post_meta( get_the_ID(), '_wsuwp_profile_publications', true );
		$u_cats     = wp_get_post_terms( get_the_ID(), 'wsuwp_university_category' );
		$topics     = wp_get_post_terms( get_the_ID(), 'topic' );
	?>

	<?php if ( $about || $experience || $honors || $u_cats || $topics || has_tag() || $research || $grants || $teaching || $service || $extension || $pubs ) : ?>

	<section class="row single gutter pad-ends">

		<div class="column one" id="profile-tabbed-content">

			<ul id="profile-tabs">
			<?php
				if ( $about || $u_cats || $topics || has_tag() || $experience || $honors ) echo '<li><a href="#about">About Me</a></li>';
				if ( $research || $grants ) echo '<li><a href="#research">Research</a></li>';
				if ( $teaching ) echo '<li><a href="#teaching">Teaching</a></li>';
				if ( $service ) echo '<li><a href="#service">Service</a></li>';
				if ( $extension ) echo '<li><a href="#extension">Extension</a></li>';
				if ( $pubs ) echo '<li><a href="#publications">Publications</a></li>';
			?>
			</ul>

			<?php // About Me panel.
				if ( $about || $u_cats || $topics || has_tag() || $experience || $honors ) {
					echo '<div id="about">';

					// Content.
					the_content();

					// Expertise. ish.
					if ( $u_cats || $topics || has_tag() ) {
						echo '<h2>Expertise</h2>';
					}
					if ( $u_cats ) {
						echo '<dl class="categorized">';
						//echo '<dt><span class="categorized-default">Categorized</span></dt>';
						foreach ( $u_cats as $cat ) {
							$cat = sanitize_term( $cat, 'wsuwp_university_category' );
							echo '<dd><a href="' . esc_attr( get_term_link( $cat, 'wsuwp_university_category' ) ) . '">' . esc_html( $cat->name ) . '</a></dd>';
						}
						//echo '</dl>';
					}
					if ( $topics ) {
						echo '<dl class="topics">';
						foreach ( $topics as $topic ) {
							$topic = sanitize_term( $topic, 'topic' );
							echo '<dd><a href="' . esc_attr( get_term_link( $topic, 'topic' ) ) . '">' . esc_html( $topic->name ) . '</a></dd>';
						}
						echo '</dl>';
					}
					if ( has_tag() ) {
						echo '<dl class="tagged">';
						//echo '<dt><span class="tagged-default">Tagged</span></dt>';
						foreach( get_the_tags() as $tag ) {
							echo '<dd><a href="' . esc_attr( get_tag_link( $tag->term_id ) ) . '">' . esc_html( $tag->name ) . '</a></dd>';
						}
						echo '</dl>';
					}

					// Experience.
					if ( $experience ) {
						echo '<h2>Professional Experience</h2>';
						echo wpautop( wp_kses_post( $experience ) );
					}

					// Honors.
					if ( $honors ) {
						echo '<h2>Honors and Awards</h2>';
						echo wpautop( wp_kses_post( $honors ) );
					}

					echo '</div>';
				}
			?>

			<?php // Research panel.
				if ( $research || $grants ) :
					echo '<div id="research">';

					// Research.
					if ( $research ) {
						//echo '<h2>Research Interests</h2>';
						echo wpautop( wp_kses_post( $research ) );
					}

					// Funding.
					if ( $grants ) {
						echo '<h2>Grants, Contracts, and Fund Generation</h2>';
						echo wpautop( wp_kses_post( $grants ) );
					}

					echo '</div>';
				endif;
			?>

			<?php // Teaching panel.
				if ( $teaching ) {
					echo '<div id="teaching">' . wpautop( wp_kses_post( $teaching ) ) . '</div>';
				}
			?>

			<?php // Service panel.
				if ( $service ) {
					echo '<div id="service">' . wpautop( wp_kses_post( $service ) ) . '</div>';
				}
			?>

			<?php // Service panel.
				if ( $extension ) {
					echo '<div id="extension">' . wpautop( wp_kses_post( $extension ) ) . '</div>';
				}
			?>

			<?php // Publications panel.
				if ( $pubs ) {
					echo '<div id="publications">' . wpautop( wp_kses_post( $pubs ) ) . '</div>';
				}
			?>

		</div>

	</section>

	<?php endif; ?>

	<footer class="main-footer">
		<section class="row single gutter">
			<div class="column one">
				<?php // If the user viewing the post can edit it, show an edit link.
				if ( current_user_can( 'edit_post', $post->ID ) ) : ?>
				<dl class="editors"><?php edit_post_link( 'Edit', '<span class="edit-link">', '</span>' ); ?></dl>
				<?php endif; ?>
			</div>
		</section>
	</footer>

</main>

<?php get_footer(); ?>