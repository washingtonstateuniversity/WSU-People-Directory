<?php get_header(); ?>

<main>

	<?php get_template_part( 'parts/headers' ); ?>

	<section class="row side-right gutter pad-ends">

		<div class="column one">

			<?php while ( have_posts() ) : the_post(); ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<header class="article-header">
					<hgroup>
					<?php if ( spine_get_option( 'articletitle_show' ) == 'true' ) : ?>
						<h1 class="article-title"><?php the_title(); ?></h1>
					<?php endif; ?>
					</hgroup>
				</header>

				<div class="article-body">

					<?php // Featured image.
					if ( has_post_thumbnail() ) : ?>
					<figure class="article-thumbnail"><?php the_post_thumbnail( array( 132, 132, true ) ); ?></figure>
					<?php endif; ?>

					<?php // Degree information.
					$degrees = get_post_meta( get_the_ID(), '_wsuwp_profile_degree', true );
					if ( $degrees && is_array($degrees) ) : ?>
					<ul>
						<?php foreach ( $degrees as $degree ) : ?>
						<li class="degree"><?php echo esc_html( $degree ); ?></li>
						<?php endforeach; ?>
					</ul>
					<?php endif; ?>

					<?php // Title and department info. ?>

					<?php the_content(); ?>

					<?php /*wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'spine' ), 'after' => '</div>' ) );*/ ?>
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
				<?php // If the user viewing the post can edit it, show an edit link.
				if ( current_user_can( 'edit_post', $post->ID ) ) : ?>
				<dl class="editors"><?php edit_post_link( 'Edit', '<span class="edit-link">', '</span>' ); ?></dl>
				<?php endif; ?>

			</article>

			<?php endwhile; ?>

		</div><!--/column-->

		<div class="column two">

			<aside class="contact-info">

				<h2>Contact Information</h2>

				<?php // Contact-specific "card" data also goes here. ?>

				<?php // Website.
				$website = get_post_meta( get_the_ID(), '_wsuwp_profile_website', true );
				if ( $website ) : ?>
				<p><a href="<?php echo esc_url( $website ); ?>">My Website &raquo;</a></p>
				<?php endif; ?>

				<?php // Curriculum Vitae.
				$cv = get_post_meta( get_the_ID(), '_wsuwp_profile_cv', true );
				if ( $cv ) : ?>
				<p><a href="<?php echo esc_url( wp_get_attachment_url( $cv ) ); ?>">My C.V &raquo;</a></p>
				<?php endif; ?>

			</aside>

		</div><!--/column two-->
 
	</section>
 
	<!--<footer class="main-footer">
		<section class="row halves pager prevnext gutter">
			<div class="column one">
				<?php previous_post_link(); ?> 
			</div>
			<div class="column two">
				<?php next_post_link(); ?>
			</div>
		</section>
	</footer>-->

</main><!--/#page-->

<?php get_footer(); ?>