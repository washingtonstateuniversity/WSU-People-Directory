<?php
/**
 * Person template.
 *
 * This template can be overridden by including `wsu-people/person.php` in your theme.
 *
 * @var array  $display
 * @var object $person
 */
?>
<div class="card">

	<?php if ( $display['photo'] ) { ?>
	<figure class="photo">
		<img src="<?php echo esc_url( $display['photo']->thumbnail ); ?>"
			 alt="<?php echo esc_html( $display['name'] ); ?>" />
	</figure>
	<?php } ?>

	<div class="contact">
		<?php
		if ( $display['titles'] ) {
			foreach ( $display['titles'] as $title ) {
			?><span class="title"><?php echo esc_html( $title ); ?></span><?php
			}
		}
		?>
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
			<a href="<?php echo esc_url( $display['website'] ); ?>"><?php echo esc_url( $display['website'] ); ?></a>
		</span>
		<?php } ?>
	</div>

</div>

<?php if ( $display['about'] ) { ?>
<div class="about">
	<?php echo wp_kses_post( apply_filters( 'the_content', $display['about'] ) ); ?>
</div>
<?php } ?>
