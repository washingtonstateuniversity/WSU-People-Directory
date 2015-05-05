<?php

get_header();

if ( is_home() ) {
	$main_class = 'spine-main-index';
} elseif ( is_author() ) {
	$main_class = 'spine-author-index';
} elseif ( is_category() ) {
	$main_class = 'spine-category-index';
} elseif ( is_tag() ) {
	$main_class = 'spine-tag-index';
} elseif ( is_tax() ) {
	$main_class = 'spine-tax-index';
} elseif ( is_archive() ) {
	$main_class = 'spine-archive-index';
} elseif ( is_search() ) {
	$main_class = 'spine-search-index';
} else {
	$main_class = '';
}

?>

<main class="<?php echo $main_class; ?>">

<?php get_template_part('parts/headers'); ?>



	<section class="row side-right gutter pad-ends">
		<div class="column one">
			<header>
      	<?php
					if ( is_tax() ) {
						$term = get_queried_object();
						$title = $term->name;
						$child_terms = get_terms( $term->taxonomy, array( 'hide_empty' => false, 'parent' => $term->term_id ) );
						echo '<h2>' . esc_html( $title ) . '</h2>';
					} elseif ( is_search() ) {
						echo '<h2>Results for "' . get_search_query() . '"</h2>';
					}
				?>
			</header>
			<?php
				if ( $child_terms ) {
					include plugin_dir_path( __FILE__ ) . 'term-children.php';
				} else {
					include plugin_dir_path( __FILE__ ) . 'personnel.php';
				}
			?>
		</div>
		<div class="column two">
			<?php if ( is_active_sidebar( 'sidebar' ) ) { dynamic_sidebar( 'sidebar' ); } ?>
		</div>
	</section>



<?php

/* @type WP_Query $wp_query */
global $wp_query;

$big = 99164;
$args = array(
	'base'         => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
	'format'       => 'page/%#%',
	'total'        => $wp_query->max_num_pages, // Provide the number of pages this query expects to fill.
	'current'      => max( 1, get_query_var('paged') ), // Provide either 1 or the page number we're on.
);

?>
	<footer class="main-footer archive-footer pad-ends">
		<section class="row side-right pager prevnext gutter">
			<div class="column one">
				<?php echo paginate_links( $args ); ?>
			</div>
			<div class="column two">
				<!-- intentionally empty -->
			</div>
		</section><!--pager-->
	</footer>

	<?php get_template_part( 'parts/footers' ); ?>

</main>
<?php

get_footer();