<?php while ( have_posts() ) : the_post(); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php if ( spine_has_featured_image() ) {
		$featured_image_src = spine_get_featured_image_src(); ?>
	<figure class="profile-image" style="background-image: url('<?php echo $featured_image_src ?>');">
			<?php spine_the_featured_image( 'thumbnail' ); ?>
	</figure>
	<?php } ?>

  <header class="profile-header">
    <hgroup>
      <h3 class="profile-title">
        <a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
      </h3>
    </hgroup>
  </header>

  <div class="profile-body">
    <?php
    	$title = get_post_meta( get_the_ID(), '_wsuwp_profile_ad_title', true );
			$phone = get_post_meta( get_the_ID(), '_wsuwp_profile_ad_phone', true );
			$phone_ext = get_post_meta( get_the_ID(), '_wsuwp_profile_ad_phone_ext', true );
			$office = get_post_meta( get_the_ID(), '_wsuwp_profile_ad_office', true );
			$email = get_post_meta( get_the_ID(), '_wsuwp_profile_ad_email', true );
			
			if ( $title ) {
				echo '<p class="title">' . esc_html( $title ) . '</p>';
			}
			if ( ! is_tax( 'cahnrs_unit' ) ) {
				$depts = wp_get_post_terms( get_the_ID(), 'cahnrs_unit' );
				if ( $depts ) {
					echo '<p class="departments">';
					foreach ( $depts as $dept ) :
						$dept = sanitize_term( $dept, 'cahnrs_unit' );
						echo '<span class="department"><a href="' . esc_url( get_term_link( $dept, 'cahnrs_unit' ) ) . '">' . esc_html( $dept->name ) . '</a></span>';
					endforeach;
					echo '</p>';
				}
			}
			if ( $phone || $office || $email ) {
				echo '<p class="contact">';
				if ( $phone ) { echo '<span class="phone">' . esc_html( $phone ) . '</span>'; }
				if ( $office ) { echo '<span class="office">' . esc_html( $office ) . '</span>'; }
				if ( $email ) { echo '<span class="email"><a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a></span>'; }
				echo '</p>';
			}
		?>
  </div>

</article>

<?php endwhile; ?>