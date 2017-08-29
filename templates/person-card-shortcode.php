<?php
/**
 * Person Card shortcode template.
 *
 * This template can be overridden by including `wsu-people/person-card-shortcode.php` in your theme.
 *
 * @var array  $display
 * @var object $person
 */
?>
<aside class="wsu-person card-shortcode">

	<div class="card">

		<header class="name">
			<?php if ( ! empty( $display['link'] ) ) { ?><a href="<?php echo esc_url( $display['link'] ); ?>"><?php } ?>
			<?php echo esc_html( $display['name'] ); ?>
			<?php if ( ! empty( $display['link'] ) ) { ?></a><?php } ?>
		</header>

		<?php if ( $display['photo'] ) { ?>
		<figure class="photo">
			<?php if ( ! empty( $display['link'] ) ) { ?><a href="<?php echo esc_url( $display['link'] ); ?>"><?php } ?>
			<img src="<?php echo esc_url( $display['photo']->thumbnail ); ?>" alt="<?php echo esc_html( $display['name'] ); ?>" />
			<?php if ( ! empty( $display['link'] ) ) { ?></a><?php } ?>
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
				<a href="<?php echo esc_url( $display['website'] ); ?>">Website</a>
			</span>
			<?php } ?>
		</div>

	</div>

</aside>
