<?php
$post = get_post();
$is_card_shortcode = ( isset( $card_nid ) ) ? $card_nid : false;
$nid = ( $is_card_shortcode ) ? $is_card_shortcode : get_post_meta( $post->ID, '_wsuwp_profile_ad_nid', true );
$set_photo = get_post_meta( $post->ID, '_use_photo', true );
$set_title = get_post_meta( $post->ID, '_use_title', true );
$set_about = get_post_meta( $post->ID, '_use_bio', true );
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

// Title(s).
$title = ( ! empty( $person->working_titles ) ) ? implode( '<br />', $person->working_titles ) : $person->position_title;

if ( $set_title ) {
	$titles = explode( ' ', $set_title );

	foreach ( $titles as $title_index ) {
		if ( isset( $person->working_titles[ $title_index ] ) ) {
			$set_titles[] = $person->working_titles[ $title_index ];
		}
	}

	$title = implode( '<br />', $set_titles );
}


// Other card info.
$email = ( ! empty( $person->email_alt ) ) ? $person->email_alt : $person->email;
$ad_phone = ( ! empty( $person->phone_ext ) ) ? $person->phone . ' ext ' . $person->phone_ext : $person->phone;
$phone = ( ! empty( $person->phone_alt ) ) ? $person->phone_alt : $ad_phone;
$office = ( ! empty( $person->office_alt ) ) ? $person->office_alt : $person->office;
$address = $person->address;
$degrees = ( ! empty( $person->degrees ) ) ? implode( '<br />', $person->degrees ) : false;
$website = $person->website;

// Photo.
$photo_index = ( $set_photo ) ? $set_photo : 0;
$photo = ( $person->photos ) ? $person->photos[ $photo_index ]->thumbnail : false;

// About.
if ( ! $set_about || 'personal' === $set_about ) {
	$about = $person->content->rendered;
} else {
	$about = $person->$set_about;
}


// Taxonomy info.
$tags = wp_get_post_tags( $post->ID, array(
	'fields' => 'names',
) );

// Directory page-specific info.
if ( ! $profile && ! $is_card_shortcode ) {
	$directory_page = get_post_meta( $post->ID, "_display_on_page_{$page_id}", true );

	$name = get_the_title();

	if ( isset( $directory_page['title'] ) ) {
		$titles = array();

		foreach ( $directory_page['title'] as $title_index ) {
			if ( 'ad' === $title_index ) {
				$titles[] = $person->position_title;
			} else {
				$titles[] = $person->working_titles[ $title_index ];
			}
		}

		$title = implode( '<br />', $titles );
	}

	if ( isset( $directory_page['photo'] ) ) {
		$index = $directory_page['photo'];
		$photo = $person->photos[ $index ]->thumbnail;
	}

	if ( isset( $directory_page['about'] ) && 'content' !== $directory_page['about'] ) {
		if ( 'bio_unit' === $directory_page['about'] ) {
			$about = $person->bio_unit;
		} elseif ( 'bio_university' === $directory_page['about'] ) {
			$about = $person->bio_university;
		} elseif ( 'tags' === $directory_page['about'] ) {
			$about = implode( ', ', $tags );
		}
	}

	$link = trailingslashit( $base_url . $post->post_name );
}

// Card shortcode info.
if ( $is_card_shortcode ) {
	$name = $person->title->rendered;
	$link = ( $person->website ) ? $person->website : $person->link;
}

// Person classes.
$person_classes = array( 'wsu-person' );
if ( $photo ) {
	$person_classes[] = 'has-photo';
}
?>
<article class="<?php echo esc_html( implode( ' ', $person_classes ) ); ?>"<?php if ( is_admin() ) { ?>
		 data-nid="<?php echo esc_attr( $nid ); ?>"
		 data-post-id="<?php the_ID(); ?>"
		 aria-checked="false"<?php } ?>>

	<div class="card">

		<?php if ( ! $profile ) { ?>
		<h2 class="name">
			<a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $name ); ?></a>
		</h2>
		<?php } ?>

		<?php
		if ( $photo ) {

			// Markup for directory page view.
			if ( ! $profile && ! $is_card_shortcode && in_array( 'photos', $wrapper_classes, true ) ) {
				?>
				<figure class="photo">
					<a href="<?php echo esc_url( $link ); ?>">
						<img src="<?php echo esc_url( plugins_url( 'images/placeholder.png', dirname( __FILE__ ) ) ); ?>"
							 data-photo="<?php echo esc_url( $photo ); ?>"
							 alt="<?php echo esc_html( $name ); ?>" />
					</a>
				</figure>
				<?php
			}

			// Markup for individual person view.
			if ( $profile ) {
				?>
				<figure class="photo">
					<img src="<?php echo esc_url( $photo ); ?>"
						 alt="<?php echo esc_html( $name ); ?>" />
				</figure>
				<?php
			}

			// Markup for card shortcode view.
			if ( $is_card_shortcode ) {
				?>
				<figure class="photo">
					<a href="<?php echo esc_url( $link ); ?>">
						<img src="<?php echo esc_url( $photo ); ?>"
							 alt="<?php echo esc_html( $name ); ?>" />
					</a>
				</figure>
				<?php
			}
		}
		?>

		<div class="contact">
			<span class="title"><?php echo wp_kses_post( $title ); ?></span>
			<span class="email"><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></span>
			<span class="phone"><?php echo esc_html( $phone ); ?></span>
			<span class="office"><?php echo esc_html( $office ); ?></span>
			<?php if ( ! empty( $address ) ) { echo '<span class="address">' . esc_html( $address ) . '</span>'; } ?>
			<?php if ( ! empty( $website ) ) { echo '<span class="website"><a href="' . esc_url( $website ) . '">' . esc_html( $website ) . '</a></span>'; } ?>
		</div>

	</div>

	<div class="about">
		<?php echo wp_kses_post( apply_filters( 'the_content', $about ) ); ?>
	</div>

	<?php if ( is_admin() && ! $is_card_shortcode ) { ?>
	<div class="wsu-person-controls">
		<button type="button"
				class="wsu-person-edit"
				aria-label="Edit"
				data-id="<?php echo esc_attr( $person->id ); ?>"
				data-listed="<?php echo esc_attr( implode( ' ', $person->listed_on ) ); ?>">
			<span class="dashicons dashicons-edit"></span>
		</button>
		<button type="button" class="wsu-person-remove" aria-label="Remove">
			<span class="dashicons dashicons-no"></span>
		</button>
	</div>

	<button type="button" class="wsu-person-select button-link check">
		<span class="media-modal-icon"></span>
		<span class="screen-reader-text">Deselect</span>
	</button>

	<div class="person-modal-wrapper close">
		<div class="person-modal content">

			<div class="person-modal-title">
				<h1><?php the_title(); ?> Details</h1>
			</div>

			<div class="person-modal-content">

				<?php if ( $person->photos ) { ?>
				<h2>Photos</h2>

				<p class="description">Select which photo to display.</p>

				<div class="person-photos choose">

					<?php foreach ( $person->photos as $index => $photo ) { ?>
					<div data-index="<?php echo esc_attr( $index ); ?>"<?php if ( isset( $directory_page['photo'] ) && $index === $directory_page['photo'] ) { echo ' class="selected"'; } ?>>
						<img src="<?php echo esc_url( $photo->thumbnail ); ?>" />
						<button type="button" class="wsu-person-select button-link check">
							<span class="media-modal-icon"></span>
							<span class="screen-reader-text">Deselect</span>
						</button>
					</div>
					<?php } ?>

				</div>
				<?php } ?>

				<?php if ( $person->working_titles ) { ?>
				<h2>Titles</h2>

				<p class="description">Select which title(s) to display.</p>

				<div class="person-titles choose multiple">

					<div data-index="ad"<?php if ( isset( $directory_page['title'] ) && in_array( 'ad', $directory_page['title'], true ) ) { echo ' class="selected"'; } ?>>
						<span class="content"><?php echo esc_html( $person->position_title ); ?></span>
						<button type="button" class="wsu-person-select button-link check">
							<span class="media-modal-icon"></span>
							<span class="screen-reader-text">Deselect</span>
						</button>
					</div>

					<?php foreach ( $person->working_titles as $index => $title ) { ?>
					<div data-index="<?php echo esc_attr( $index ); ?>"<?php if ( isset( $directory_page['title'] ) && in_array( $index, $directory_page['title'], true ) ) { echo ' class="selected"'; } ?>>
						<span class="content"><?php echo esc_html( $title ); ?></span>
						<button type="button" class="wsu-person-select button-link check">
							<span class="media-modal-icon"></span>
							<span class="screen-reader-text">Deselect</span>
						</button>
					</div>
					<?php } ?>

				</div>
				<?php } ?>

				<?php if ( $person->content->rendered || $person->bio_unit || $person->bio_university ) { ?>
				<h2>About</h2>

				<p class="description">Select what to display for the "About" area.</p>

				<div class="person-content choose">

					<?php if ( $person->content->rendered ) { ?>
					<h3>Personal Biography</h3>
					<div data-key="content">
						<div class="content"><?php echo wp_kses_post( apply_filters( 'the_content', $person->content->rendered ) ); ?></div>
						<button type="button" class="wsu-person-select button-link check">
							<span class="media-modal-icon"></span>
							<span class="screen-reader-text">Deselect</span>
						</button>
					</div>
					<?php } ?>

					<?php if ( $person->bio_unit ) { ?>
					<h3>Unit Biography</h3>
					<div data-key="bio_unit"<?php if ( isset( $directory_page['about'] ) && 'bio_unit' === $directory_page['about'] ) { echo ' class="selected"'; } ?>>
						<div class="content"><?php echo wp_kses_post( apply_filters( 'the_content', $person->bio_unit ) ); ?></div>
						<button type="button" class="wsu-person-select button-link check">
							<span class="media-modal-icon"></span>
							<span class="screen-reader-text">Deselect</span>
						</button>
					</div>
					<?php } ?>

					<?php if ( $person->bio_university ) { ?>
					<h3>University Biography</h3>
					<div data-key="bio_university"<?php if ( isset( $directory_page['about'] ) && 'bio_university' === $directory_page['about'] ) { echo ' class="selected"'; } ?>>
						<div class="content"><?php echo wp_kses_post( apply_filters( 'the_content', $person->bio_university ) ); ?></div>
						<button type="button" class="wsu-person-select button-link check">
							<span class="media-modal-icon"></span>
							<span class="screen-reader-text">Deselect</span>
						</button>
					</div>
					<?php } ?>

					<?php if ( $tags ) { ?>
					<h3>Tags</h3>
					<div data-key="tags"<?php if ( isset( $directory_page['about'] ) && 'tags' === $directory_page['about'] ) { echo ' class="selected"'; } ?>>
						<div class="content"><?php echo esc_html( implode( ', ', $tags ) ); ?></div>
						<button type="button" class="wsu-person-select button-link check">
							<span class="media-modal-icon"></span>
							<span class="screen-reader-text">Deselect</span>
						</button>
					</div>
					<?php } ?>

				</div>
				<?php } ?>

			</div>

			<div class="person-modal-toolbar">
				<button type="button" class="button button-primary button-large person-update">Update</button>
			</div>

			<button type="button" class="button-link person-modal-button close">
				<span class="person-modal-icon close">
					<span class="screen-reader-text">Close person panel</span>
				</span>
			</button>
		</div>
	</div>
	<?php } ?>

</article>
