<?php get_header(); ?>

<main>

	<?php get_template_part( 'parts/headers' ); ?>

	<section class="row side-right gutter pad-ends">

		<div class="column one">

			<?php while ( have_posts() ) : the_post(); ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<header class="article-header">
					<hgroup>
						<h1 class="article-title"><?php the_title(); ?></h1>
					</hgroup>
				</header>

				<div class="article-body">

					<?php // Degree information.
					$degrees = get_post_meta( get_the_ID(), '_wsuwp_profile_degree', true );
					if ( $degrees && is_array($degrees) ) : ?>
					<ul>
						<?php foreach ( $degrees as $degree ) : ?>
						<li class="degree"><?php echo esc_html( $degree ); ?></li>
						<?php endforeach; ?>
					</ul>
					<?php endif; ?>

					<?php // Appointment, title, and department info.
						$appt = get_post_meta( get_the_ID(), '_wsuwp_profile_ad_appointment', true );
						$title = get_post_meta( get_the_ID(), '_wsuwp_profile_ad_title', true );
						$dept = get_post_meta( get_the_ID(), '_wsuwp_profile_dept', true );

						echo '<p>';
						if ( $appt ) { echo '<strong>' . esc_attr( $ad_appt ) . ':</strong> '; }
						if ( $title ) { echo esc_attr( $title ); }
						echo '</p>';

						if ( $dept ) { echo '<p><strong>Department:</strong> <a href="#">' . esc_html( $dept ) . '</a></p>'; }
					?>

					<?php // Contact info.
						$email = get_post_meta( get_the_ID(), '_wsuwp_profile_ad_email', true );
						$phone = get_post_meta( get_the_ID(), '_wsuwp_profile_ad_phone', true );
						$office = get_post_meta( get_the_ID(), '_wsuwp_profile_ad_office', true );
						$alt_email = get_post_meta( get_the_ID(), '_wsuwp_profile_alt_email', true );
						$alt_phone = get_post_meta( get_the_ID(), '_wsuwp_profile_alt_phone', true );

						echo '<p><strong>Contact Information</strong><br />';

						// Email.
						if ( $email ) { echo '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>'; }
						if ( $email && $alt_email ) { echo ' | '; }
						if ( $alt_email ) { echo '<a href="mailto:' . esc_attr( $alt_email ) . '">' . esc_html( $alt_email ) . '</a>'; }
						echo '<br />';

						// Phone.
						if ( $phone ) { echo esc_html( $phone ); }
						if ( $phone && $alt_phone ) { echo ' | '; }
						if ( $alt_phone ) { echo esc_html( $alt_phone ); }
						echo '<br />';

						if ( $office ) { echo esc_html( $office ); }

						echo '</p>';
					?>

				</div>
<!--
				<footer class="article-footer">
				<?php
				// Display site level categories attached to the post.
				if ( has_category() ) {
					echo '<dl class="categorized">';
					echo '<dt><span class="categorized-default">Categorized</span></dt>';
					foreach( get_the_category() as $category ) {
						echo '<dd><a href="' . get_category_link( $category->cat_ID ) . '">' . $category->cat_name . '</a></dd>';
					}
					echo '</dl>';
				}

				// Display University tags attached to the post.
				if ( has_tag() ) {
					echo '<dl class="tagged">';
					echo '<dt><span class="tagged-default">Tagged</span></dt>';
					foreach( get_the_tags() as $tag ) {
						echo '<dd><a href="' . get_tag_link( $tag->term_id ) . '">' . $tag->name . '</a></dd>';
					}
					echo '</dl>';
				}
				?>
				</footer>
-->
			</article>

			<?php endwhile; ?>

		</div><!--/column-->

		<div class="column two">

			<aside>

				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="profile-photo"><?php the_post_thumbnail( 'medium' ); /* Perhaps we should define a new image size for this purpose. */ ?></figure>
				<?php endif; ?>

				<?php // Website.
				$website = get_post_meta( get_the_ID(), '_wsuwp_profile_website', true );
				if ( $website ) : ?>
					<p><a href="<?php echo esc_url( $website ); ?>">My Website <span class="dashicons dashicons-external"></span></a></p>
				<?php endif; ?>

				<?php // Curriculum Vitae.
				$cv = get_post_meta( get_the_ID(), '_wsuwp_profile_cv', true );
				if ( $cv ) : ?>
					<p><a href="<?php echo esc_url( wp_get_attachment_url( $cv ) ); ?>">My C.V <span class="dashicons dashicons-download"></span></a></p>
				<?php endif; ?>

			</aside>

		</div><!--/column two-->

	</section>

	<section class="row single gutter pad-ends">

		<div class="column one" id="profile-tabbed-content">

			<?php
				$about      = get_the_content();
				$experience = get_post_meta( get_the_ID(), '_wsuwp_profile_experience', true );
				$honors     = get_post_meta( get_the_ID(), '_wsuwp_profile_honors', true );
				$research   = get_post_meta( get_the_ID(), '_wsuwp_profile_research', true );
				$grants     = get_post_meta( get_the_ID(), '_wsuwp_profile_grants', true );
				$pubs       = get_post_meta( get_the_ID(), '_wsuwp_profile_publications', true );
				$teaching   = get_post_meta( get_the_ID(), '_wsuwp_profile_teaching', true );
				$service    = get_post_meta( get_the_ID(), '_wsuwp_profile_service', true );
				
			?>

			<ul id="profile-tabs">
			<?php
				if ( $about || $experience || $honors ) echo '<li><a href="#about">About Me</a></li>';
				if ( $research || $grants || $pubs ) echo '<li><a href="#research">Research</a></li>';
				if ( $teaching ) echo '<li><a href="#teaching">Teaching</a></li>';
				if ( $service ) echo '<li><a href="#service">Service</a></li>';
			?>
			</ul>

			<?php
				if ( $about || $experience || $honors ) :
					echo '<div id="about">';
					the_content();
					if ( $experience ) {
						echo '<h2>Professional Experience</h2>';
						echo wpautop( wp_kses_post( $experience ) );
					}
					if ( $honors ) {
						echo '<h2>Honors and Awards</h2>';
						echo wpautop( wp_kses_post( $honors ) );
					}
					echo '</div>';
				endif;
			?>

			<?php
				if ( $research || $grants || $pubs ) :
					echo '<div id="research">';
					if ( $research ) {
						//echo '<h2>Research Interests</h2>';
						echo wpautop( wp_kses_post( $research ) );
					}
					if ( $grants ) {
						echo '<h2>Grants, Contracts, and Fund Generation</h2>';
						echo wpautop( wp_kses_post( $grants ) );
					}
					if ( $pubs ) {
						echo '<h2>Publications, Creative Work, and Presentations</h2>';
						echo wpautop( wp_kses_post( $pubs ) );
					}
					echo '</div>';
				endif;
			?>

			<?php
				if ( $teaching ) :
					echo '<div id="teaching">' . wpautop( wp_kses_post( $teaching ) ) . '</div>';
				endif;
			?>

			<?php
				if ( $service ) :
					echo '<div id="service">' . wpautop( wp_kses_post( $service ) ) . '</div>';
				endif;
			?>

		</div>

	</section>

	<footer class="main-footer">
		<section class="row halves pager prevnext gutter">
			<div class="column one">

				<?php // If the user viewing the post can edit it, show an edit link.
				if ( current_user_can( 'edit_post', $post->ID ) ) : ?>
				<dl class="editors"><?php edit_post_link( 'Edit', '<span class="edit-link">', '</span>' ); ?></dl>
				<?php endif; ?>

				<!--<?php previous_post_link(); ?> 
			</div>
			<div class="column two">
				<?php next_post_link(); ?>-->
			</div>
		</section>
	</footer>

</main><!--/#page-->

<?php get_footer(); ?>