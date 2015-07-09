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
						$address    = get_post_meta( get_the_ID(), '_wsuwp_profile_ad_address', true );
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
						if ( $classifications && ! is_wp_error( $classifications ) ) {
							echo '<p class="classifications">';
							foreach ( $classifications as $classification ) {
								echo esc_html( $classification );
							}
							echo '</p>';
						}

						// Title(s).
						if ( $title || $titles ) {
							echo '<p>';
							if ( $titles ) {
            		foreach ( $titles as $additional_title ) :
              		echo esc_html( $additional_title );
									if ( $additional_title !== end( $titles ) ) {
										echo '<br />';
									}
            		endforeach;
							} else {
								if ( $title ) { echo esc_html( $title ); }
							}
							echo '</p>';
            }

						// Department(s).
						if ( $departments && ! is_wp_error( $departments ) ) {
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
							if ( $address ) { echo '<br />' . esc_html( $address ); }
							if ( $locations && ! is_wp_error( $locations ) ) {
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
		$bio           = get_the_content();
		$short_bio     = get_post_meta( get_the_ID(), '_wsuwp_profile_bio_short', true );
		$marketing_bio = get_post_meta( get_the_ID(), '_wsuwp_profile_bio_marketing', true );
		$u_cats        = wp_get_post_terms( get_the_ID(), 'wsuwp_university_category' );
		$topics        = wp_get_post_terms( get_the_ID(), 'topic' );
		// CV meta.
		$cv_employment       = get_post_meta( get_the_ID(), '_wsuwp_profile_employment', true );
		$cv_honors           = get_post_meta( get_the_ID(), '_wsuwp_profile_honors', true );
		$cv_grants           = get_post_meta( get_the_ID(), '_wsuwp_profile_grants', true );
		$cv_publications     = get_post_meta( get_the_ID(), '_wsuwp_profile_publications', true );
		$cv_presentations    = get_post_meta( get_the_ID(), '_wsuwp_profile_presentations', true );
		$cv_teaching         = get_post_meta( get_the_ID(), '_wsuwp_profile_teaching', true );
		$cv_service          = get_post_meta( get_the_ID(), '_wsuwp_profile_service', true );
		$cv_responsibilities = get_post_meta( get_the_ID(), '_wsuwp_profile_responsibilities', true );
		$cv_societies        = get_post_meta( get_the_ID(), '_wsuwp_profile_societies', true );
		$cv_professional_dev = get_post_meta( get_the_ID(), '_wsuwp_profile_experience', true );
	?>

	<?php if ( $bio || $u_cats || $topics || has_tag() ) : ?>

	<section class="row single pad-bottom">

		<div class="column one">

			<div id="profile-bio">
				<?php the_content(); ?>
			</div>

			<?php if ( $u_cats || $topics || has_tag() || $cv_employment || $cv_honors || $cv_grants || $cv_publications || $cv_presentations || $cv_teaching || $cv_service || $cv_responsibilities || $cv_societies || $cv_professional_dev ) : ?>

			<div id="profile-accordion">

				<?php if ( $u_cats || $topics || has_tag() ) : ?>
      			<dl>
					<dt>
						<h4>Expertise</h4>
					</dt>
					<dd>
					<?php
						if ( $u_cats && ! is_wp_error( $u_cats ) ) {
							echo '<dl class="categorized">';
							//echo '<dt><span class="categorized-default">Categorized</span></dt>';
							foreach ( $u_cats as $cat ) {
								$cat = sanitize_term( $cat, 'wsuwp_university_category' );
								echo '<dd><a href="' . esc_attr( get_term_link( $cat, 'wsuwp_university_category' ) ) . '">' . esc_html( $cat->name ) . '</a></dd>';
							}
							//echo '</dl>';
						}
						if ( $topics && ! is_wp_error( $topics ) ) {
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
					?>
					</dd>
				</dl>
				<?php endif; ?>

				<?php if ( $cv_employment || $cv_honors || $cv_grants || $cv_publications || $cv_presentations || $cv_teaching || $cv_service || $cv_responsibilities || $cv_societies || $cv_professional_dev ) : ?>
				<dl>
					<dt>
						<h4>Curriculum Vitae</h4>
					</dt>
					<dd>
						<?php
							if ( $cv_employment ) {
								echo '<h2>Employment</h2>';
								echo wpautop( wp_kses_post( $cv_employment ) );
							}
							if ( $cv_honors ) {
								echo '<h2>Honors and Awards</h2>';
								echo wpautop( wp_kses_post( $cv_honors ) );
							}
							if ( $cv_grants ) {
								echo '<h2>Grants, Contracts, and Fund Generation</h2>';
								echo wpautop( wp_kses_post( $cv_grants ) );
								echo '<p class="key">Key to indicators or description of contributions to Grants, Contracts and Fund Generation: 1 = Provided the initial idea; 2 = Developed research/program design and hypotheses; 3 = Authored or co-authored grant application; 4 = Developed and/or managed budget; 5 = Managed personnel, partnerships, and project activities.</p>';
							}
							if ( $cv_publications ) {
								echo '<h2>Publications and Creative Work</h2>';
								echo wpautop( wp_kses_post( $cv_publications ) );
								echo '<p class="key">Key to indicators or description of contributions to Publications and Creative Work: 1 = Developed the initial idea; 2 = Obtained or provided funds or other resources; 3 = Collected data; 4 = Analyzed data; 5 = Wrote/created product; 6 = Edited product.</p>';
							}
							if ( $cv_presentations ) {
								echo '<h2>Presentations</h2>';
								echo wpautop( wp_kses_post( $cv_presentations ) );
							}
							if ( $cv_teaching ) {
								echo '<h2>University Instruction</h2>';
								echo wpautop( wp_kses_post( $cv_teaching ) );
							}
							if ( $cv_service ) {
								echo '<h2>Professional Service</h2>';
								echo wpautop( wp_kses_post( $cv_service ) );
							}
							if ( $cv_responsibilities ) {
								echo '<h2>Administrative Responsibility</h2>';
								echo wpautop( wp_kses_post( $cv_responsibilities ) );
							}
							if ( $cv_societies ) {
								echo '<h2>Professional and Scholarly Organization Affiliations</h2>';
								echo wpautop( wp_kses_post( $cv_societies ) );
							}
							if ( $cv_professional_dev ) {
								echo '<h2>Professional Developlment</h2>';
								echo wpautop( wp_kses_post( $cv_professional_dev ) );
							}
						?>
					</dd>
				</dl>
				<?php endif; ?>

			</div>

			<?php endif; ?>

		</div>

	</section>

	<?php endif; ?>

</main>

<?php get_footer();