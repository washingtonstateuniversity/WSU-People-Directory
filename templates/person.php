<?php
/**
 * Person template.
 *
 * This template can be overridden by including `wsu-people-templates/person.php` in your theme.
 *
 * @var array  $display
 * @var object $person
 */
?>
<article class="<?php echo esc_attr( $display['card_classes'] ); ?>"<?php if ( is_admin() ) { ?>
		 data-nid="<?php echo esc_attr( $person->nid ); ?>"
		 data-profile-id="<?php echo esc_attr( $person->id ); ?>"
		 data-post-id="<?php echo esc_attr( $display['local_record_id'] ); ?>"
		 aria-checked="false"<?php } ?>>

	<div class="card">

		<?php if ( $display['header'] ) { ?>
		<h2 class="name">
			<?php if ( $display['link'] ) { ?><a href="<?php echo esc_url( $display['link'] ); ?>"><?php } ?>
			<?php echo esc_html( $display['name'] ); ?>
			<?php if ( $display['link'] ) { ?></a><?php } ?>
		</h2>
		<?php } ?>

		<?php if ( $display['photo'] ) { ?>
		<figure class="photo">
			<?php if ( $display['link'] ) { ?><a href="<?php echo esc_url( $display['link'] ); ?>"><?php } ?>

			<?php if ( $display['directory_view'] ) { ?>
				<img src="<?php echo esc_url( plugins_url( 'images/placeholder.png', dirname( __FILE__ ) ) ); ?>"
					 data-photo="<?php echo esc_url( $display['photo'] ); ?>"
					 alt="<?php echo esc_html( $display['name'] ); ?>" />
			<?php } else { ?>
				<img src="<?php echo esc_url( $display['photo'] ); ?>"
					 alt="<?php echo esc_html( $display['name'] ); ?>" />
			<?php } ?>

			<?php if ( $display['link'] ) { ?></a><?php } ?>
		</figure>
		<?php } ?>

		<div class="contact">
			<span class="title"><?php echo wp_kses_post( $display['title'] ); ?></span>
			<span class="email"><a href="mailto:<?php echo esc_attr( $display['email'] ); ?>">
				<?php echo esc_html( $display['email'] ); ?></a>
			</span>
			<span class="phone"><?php echo esc_html( $display['phone'] ); ?></span>
			<span class="office"><?php echo esc_html( $display['office'] ); ?></span>
			<?php if ( ! empty( $display['address'] ) ) { ?>
			<span class="address"><?php echo esc_html( $display['address'] ); ?></span>
			<?php } ?>
			<?php if ( ! empty( $display['website'] ) ) { ?>
			<span class="website">
				<a href="<?php echo esc_url( $display['website'] ); ?>">Website</a>
			</span>
			<?php } ?>
		</div>

	</div>

	<?php if ( $display['about'] ) { ?>
	<div class="about">
		<?php echo wp_kses_post( apply_filters( 'the_content', $display['about'] ) ); ?>
	</div>
	<?php } ?>

	<?php
	if ( is_admin() && false !== $display['directory_view'] ) {
		include plugin_dir_path( dirname( __FILE__ ) ) . 'templates/admin-person-options.php';
	}
	?>

</article>
