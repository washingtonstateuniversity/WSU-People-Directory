<?php
$profile = get_query_var( 'person' );

// @todo Implement some kind of validation that the profile should indeed be accessible on this site.

if ( $profile ) {
	// Retrieve person from people.wsu.edu.
	$request_url = 'https://people.wsu.edu/wp-json/wp/v2/people/';
	$request_url = add_query_arg( array( 'wsu_nid' => esc_html( $profile ) ), $request_url );
	$response = wp_remote_get( $request_url );

	if ( is_wp_error( $response ) ) {
		return '<!-- ' . sanitize_text_field( $response->get_error_message() ) . ' -->';
	}

	$data = wp_remote_retrieve_body( $response );

	if ( empty( $data ) ) {
		return '<!-- empty -->';
	}

	$person = json_decode( $data );

	if ( empty( $person ) ) {
		return '<!-- empty -->';
	}

	$person = $person[0];
	$name = $person->title->rendered;
}

$title = ( isset( $person->working_titles[0] ) ) ? $person->working_titles[0] : $person->position_title;
$email = ( ! empty( $person->email_alt ) ) ? $person->email_alt : $person->email;
$phone = ( ! empty( $person->phone_alt ) ) ? $person->phone_alt : $person->phone; // We provide a field for phone extension, but I'm not sure if it's used.
$office = ( ! empty( $person->office_alt ) ) ? $person->office_alt : $person->office;
$address = $person->address;
$photo = $person->profile_photo;
$degrees = $person->degrees;
$website = $person->website;

?>
<article class="wsu-person<?php if ( $photo ) { echo ' has-photo'; } ?>">

	<div class="card">

		<?php if ( $profile ) { ?>
		<h1 class="name"><?php echo esc_html( $person->title->rendered ); ?></h1>
		<?php } else { ?>
		<h2 class="name">
			<a href="<?php echo esc_url( trailingslashit( $base_url . $person->nid ) ); ?>"><?php echo esc_html( $person->title->rendered ); ?></a>
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
			<span class="title"><?php echo esc_html( $title ); ?></span>
			<span class="email"><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></span>
			<span class="phone"><?php echo esc_html( $phone ); ?></span>
			<span class="office"><?php echo esc_html( $office ); ?></span>
			<?php if ( ! empty( $address ) ) { echo '<span class="address">' . esc_html( $address ) . '</span>'; } ?>
			<?php if ( ! empty( $website ) ) { echo '<span class="website"><a href="' . esc_url( $website ) . '">' . esc_html( $website ) . '</a></span>'; } ?>
		</div>

	</div>

	<div class="about">
		<?php echo wp_kses_post( $person->content->rendered ); ?>
	</div>

</article>
<?php
