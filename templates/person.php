<?php
$post = get_post();
$nid = get_post_meta( $post->ID, '_wsuwp_profile_ad_nid', true );
$set_photo = get_post_meta( $post->ID, '_use_photo', true );
$profile = get_query_var( 'wsuwp_people_profile' );

$request_url = add_query_arg(
	array(
		'_embed' => true,
		'wsu_nid' => $nid,
	),
	WSUWP_People_Directory::REST_URL()
);

$response = wp_remote_get( $request_url );

if ( is_wp_error( $response ) ) {
	return '';
}

$data = wp_remote_retrieve_body( $response );

if ( empty( $data ) ) {
	return '';
}

$person = json_decode( $data );

if ( empty( $person ) ) {
	return '';
}

$person = $person[0];
$name = $person->title->rendered;

// Card info.
$title = ( isset( $person->working_titles[0] ) ) ? $person->working_titles[0] : $person->position_title;
$email = ( ! empty( $person->email_alt ) ) ? $person->email_alt : $person->email;
$phone = ( ! empty( $person->phone_alt ) ) ? $person->phone_alt : $person->phone; // We provide a field for phone extension, but I'm not sure if it's used.
$office = ( ! empty( $person->office_alt ) ) ? $person->office_alt : $person->office;
$address = $person->address;
$degrees = ( ! empty( $person->degrees ) ) ? $person->degrees : false;
$website = $person->website;

// Photo.
$photo_index = ( $set_photo ) ? $set_photo : 0;
$photo = ( $person->photos ) ? $person->photos[ $photo_index ]->thumbnail : false;
?>
<article class="wsu-person<?php if ( $photo ) { echo ' has-photo'; } ?>"<?php if ( is_admin() ) { ?> data-nid="<?php echo esc_attr( $nid ); ?>"<?php } ?>>

	<div class="card">

		<?php if ( ! $profile ) { ?>
		<?php $link = trailingslashit( $base_url . $post->post_name ); ?>
		<h2 class="name">
			<a href="<?php echo esc_url( $link ); ?>"><?php the_title(); ?></a>
		</h2>
		<?php } ?>

		<?php if ( $photo ) {

			// Markup for directory page view.
			if ( ! $profile && in_array( 'photos', $wrapper_classes, true ) ) {
				?>
				<figure class="photo">
					<a href="<?php echo esc_url( $link ); ?>">
						<img src="https://people.wsu.edu/wp-content/uploads/sites/908/2015/07/HeadShot_Template2.jpg"
							 data-photo="<?php echo esc_url( $photo ); ?>"
							 alt="<?php echo esc_attr( $person->title->rendered ); ?>" />
					</a>
				</figure>
				<?php
			}

			// Markup for individual person view.
			if ( $profile && $photo ) {
				?>
				<figure class="photo">
					<img src="<?php echo esc_url( $photo ); ?>"
						 alt="<?php echo esc_attr( $person->title->rendered ); ?>" />
				</figure>
				<?php
			}
		} ?>

		<div class="contact">
			<span class="title"><?php echo esc_html( $title ); ?></span>
			<span class="email"><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></span>
			<span class="phone"><?php echo esc_html( $phone ); ?></span>
			<span class="office"><?php echo esc_html( $office ); ?></span>
			<?php if ( ! empty( $address ) ) { echo '<span class="address">' . esc_html( $address ) . '</span>'; } ?>
			<?php if ( ! empty( $website ) ) { echo '<span class="website"><a href="' . esc_url( $website ) . '">' . esc_html( $website ) . '</a></span>'; } ?>
		</div>

	</div>

	<div class="about">
		<?php echo apply_filters( 'the_content', $person->content->rendered ); ?>
	</div>

	<?php if ( is_admin() ) { ?>
	<div class="wsu-person-controls">
		<button class="wsu-person-edit" aria-label="Edit" data-id="<?php echo esc_attr( $person->id ); ?>" data-listed="<?php echo esc_attr( implode( ' ', $person->listed_on ) ); ?>">
			<span class="dashicons dashicons-edit"></span>
		</button>
		<button class="wsu-person-remove" aria-label="Remove">
			<span class="dashicons dashicons-no"></span>
		</button>
	</div>
	<?php } ?>

</article>
