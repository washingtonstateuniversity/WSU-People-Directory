<?php foreach ( $child_terms as $child ) : ?>

<?php
	$child = sanitize_term( $child, $child->taxonomy );
	$child_link = get_term_link( $child, $child->taxonomy );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

  <header class="article-header">
    <hgroup>
      <h3 class="article-title">
        <a href="<?php echo esc_url( $child_link ); ?>" rel="bookmark"><?php echo esc_html( $child->name ); ?></a>
      </h3>
    </hgroup>
  </header>

  <div class="article-body">
    <?php /*echo wp_kses_post( $child->description );*/ ?>
  </div>

</article>

<?php endforeach; ?>