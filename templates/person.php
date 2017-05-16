<?php

$name = $person->title->rendered;

// Position/working title(s).
$title = ( ! empty( $person->working_titles ) ) ? implode( '<br />', $person->working_titles ) : $person->position_title;

if ( $use_title ) {
	$titles = explode( ' ', $use_title );
	$use_titles = array();

	foreach ( $titles as $title_index ) {
		if ( 'ad' === $title_index ) {
			$use_titles[] = $person->position_title;
		} elseif ( isset( $person->working_titles[ $title_index ] ) ) {
			$use_titles[] = $person->working_titles[ $title_index ];
		}
	}

	$title = implode( '<br />', $use_titles );
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
$photo_index = ( $use_photo ) ? $use_photo : 0;
$photo = ( $person->photos && $person->photos[ $photo_index ] ) ? $person->photos[ $photo_index ]->thumbnail : false;

$tags = wp_get_post_tags( $local_record_id, array(
	'fields' => 'names',
) );

// About.
if ( ! $use_about || 'personal' === $use_about ) {
	$about = $person->content->rendered;
} elseif ( 'tags' === $use_about ) {
	$about = implode( ', ', $tags );
} elseif ( 'none' === $use_about ) {
	$about = false;
} else {
	$about = $person->$use_about;
}

// Person classes.
$person_classes = array( 'wsu-person' );

if ( $photo ) {
	$person_classes[] = 'has-photo';
}
?>
<article class="<?php echo esc_html( implode( ' ', $person_classes ) ); ?>"<?php if ( is_admin() ) { ?>
		 data-nid="<?php echo esc_attr( $person->nid ); ?>"
		 data-profile-id="<?php echo esc_attr( $person->id ); ?>"
		 data-post-id="<?php echo esc_attr( $local_record_id ); ?>"
		 aria-checked="false"<?php } ?>>

	<div class="card">

		<?php if ( $show_header ) { ?>
		<h2 class="name">
			<a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $name ); ?></a>
		</h2>
		<?php } ?>

		<?php if ( $show_photo ) { ?>
		<figure class="photo">
			<?php if ( $link ) { ?><a href="<?php echo esc_url( $link ); ?>"><?php } ?>

			<?php if ( $lazy_load_photos ) { ?>
				<img src="<?php echo esc_url( plugins_url( 'images/placeholder.png', dirname( __FILE__ ) ) ); ?>"
					 data-photo="<?php echo esc_url( $photo ); ?>"
					 alt="<?php echo esc_html( $name ); ?>" />
			<?php } else { ?>
				<img src="<?php echo esc_url( $photo ); ?>"
					 alt="<?php echo esc_html( $name ); ?>" />
			<?php } ?>

			<?php if ( $link ) { ?></a><?php } ?>
		</figure>
		<?php } ?>

		<div class="contact">
			<span class="title"><?php echo wp_kses_post( $title ); ?></span>
			<span class="email"><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></span>
			<span class="phone"><?php echo esc_html( $phone ); ?></span>
			<span class="office"><?php echo esc_html( $office ); ?></span>
			<?php if ( ! empty( $address ) ) { echo '<span class="address">' . esc_html( $address ) . '</span>'; } ?>
			<?php if ( ! empty( $website ) ) { echo '<span class="website"><a href="' . esc_url( $website ) . '">' . esc_html( $website ) . '</a></span>'; } ?>
		</div>

	</div>

	<?php if ( $about ) { ?>
	<div class="about">
		<?php echo wp_kses_post( apply_filters( 'the_content', $about ) ); ?>
	</div>
	<?php } ?>

	<?php if ( is_admin() && $lazy_load_photos ) { ?>
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
				<h1><?php echo esc_html( $name ); ?> Details</h1>
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
