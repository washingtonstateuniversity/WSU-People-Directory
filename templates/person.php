<?php
$profile = get_query_var( WSUWP_People_Post_Type::$post_type_slug );

// @todo Implement some kind of validation that the profile should indeed be accessible on this site.

$photo = false;
// Array madness to sort out
$title = ( $alt_title = get_post_meta( get_the_ID(), '_wsuwp_profile_title', true ) ) ? $alt_title[0] : get_post_meta( get_the_ID(), '_wsuwp_profile_ad_title', true );
$email = ( $alt_email = get_post_meta( get_the_ID(), '_wsuwp_profile_email', true ) ) ? $alt_email : get_post_meta( get_the_ID(), '_wsuwp_profile_ad_email', true );
$office = ( $alt_office = get_post_meta( get_the_ID(), '_wsuwp_profile_office', true ) ) ? $alt_office : get_post_meta( get_the_ID(), '_wsuwp_profile_ad_office', true );
$address = ( $alt_address = get_post_meta( get_the_ID(), '_wsuwp_profile_email', true ) ) ? $alt_address : get_post_meta( get_the_ID(), '_wsuwp_profile_ad_address', true );
$phone = ( $alt_phone = get_post_meta( get_the_ID(), '_wsuwp_profile_email', true ) ) ? $alt_phone : get_post_meta( get_the_ID(), '_wsuwp_profile_ad_phone', true );
$phone_ext = get_post_meta( get_the_ID(), '_wsuwp_profile_ad_phone_ext', true );
$classifications = wp_get_post_terms( get_the_ID(), WSUWP_People_Classification_Taxonomy::$taxonomy_slug, array( 'fields' => 'names' ) );

?>
<article class="wsu-person<?php if ( $photo ) { echo ' has-photo'; } ?>">

	<div class="card">

		<?php if ( $profile ) { ?>
		<h1 class="name"><?php the_title(); ?></h1>
		<?php } else { ?>
		<h2 class="name">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>
		<?php } ?>

		<?php if ( ! $profile && in_array( 'photos', $wrapper_classes, true ) && $photo ) { ?>
		<a class="photo" href="#">
			<img src="https://people.wsu.edu/wp-content/uploads/sites/908/2015/07/HeadShot_Template2.jpg"
				 data-photo="<?php echo esc_url( $photo ); ?>"
				 alt="<?php echo esc_attr( $person->title->rendered ); ?>" />
		</a>
		<?php } ?>

		<div class="contact">
			<div class="title"><?php echo esc_html( $title ); ?></div>
			<div class="email"><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></div>
			<div class="phone"><?php echo esc_html( $phone ); ?></div>
			<div class="office"><?php echo esc_html( $office ); ?></div>
			<?php if ( ! empty( $address ) ) { echo '<div class="address">' . esc_html( $address ) . '</div>'; } ?>
			<?php if ( ! empty( $website ) ) { echo '<div class="website"><a href="' . esc_url( $website ) . '">' . esc_html( $website ) . '</a></div>'; } ?>
		</div>

	</div>

	<div class="about">
		<?php the_content(); ?>
	</div>

</article>
<?php
