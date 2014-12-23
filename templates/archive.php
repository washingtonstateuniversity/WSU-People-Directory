<?php get_header(); ?>

<main class="spine-archive-index">

<?php get_template_part('parts/headers'); ?>

<section class="row side-right gutter pad-ends">

	<div class="column one">

		<?php while ( have_posts() ) : the_post(); ?>

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<header class="article-header">
				<hgroup>
					<h2 class="article-title">
						<a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
					</h2>
				</hgroup>
			</header>

			<div class="article-summary">

				<?php // Featured image.
				if ( has_post_thumbnail() ) : ?>
				<figure class="article-thumbnail"><?php the_post_thumbnail( array( 132, 132, true ) ); ?></figure>
				<?php endif; ?>

				<p>
				<?php // Title.
					$title = get_post_meta( get_the_ID(), '_wsuwp_profile_ad_title', true );
					if ( $title ) { echo esc_attr( $title ) . '<br />'; }
				?>

				<?php // Department.
				$dept = get_post_meta( get_the_ID(), '_wsuwp_profile_dept', true );
				if ( $dept ) {
					echo esc_html( $dept ) . '<br />';
				}
				?>

				<?php // Contact Info.
					$email = get_post_meta( get_the_ID(), '_wsuwp_profile_ad_email', true );
					$phone = get_post_meta( get_the_ID(), '_wsuwp_profile_ad_phone', true );
					$office = get_post_meta( get_the_ID(), '_wsuwp_profile_ad_office', true );

					if ( $email ) { echo '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a><br />'; }
					if ( $phone ) { echo esc_html( $phone ) . '<br />'; }
					if ( $office ) { echo esc_html( $office ); }
				?>

				</p>

			</div>

		</article>

		<?php endwhile; // end of the loop. ?>

	</div><!--/column-->

	<div class="column two">

		<?php get_sidebar(); ?>

	</div><!--/column two-->

</section>
<?php
/* @type WP_Query $wp_query */
global $wp_query;

$big = 99164;
$args = array(
	'base'				 => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
	'format'			 => 'page/%#%',
	'total'				=> $wp_query->max_num_pages, // Provide the number of pages this query expects to fill.
	'current'			=> max( 1, get_query_var('paged') ), // Provide either 1 or the page number we're on.
);
?>
	<footer class="main-footer archive-footer">
		<section class="row side-right pager prevnext gutter">
			<div class="column one">
				<?php echo paginate_links( $args ); ?>
			</div>
			<div class="column two">
				<!-- intentionally empty -->
			</div>
		</section><!--pager-->
	</footer>
</main>
<?php

get_footer();