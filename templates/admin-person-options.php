<?php
/**
 * Content display options for a profile on a directory page.
 *
 * @var array $display
 */
?>
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
			<h1><?php echo esc_html( $display['name'] ); ?> Details</h1>
		</div>

		<div class="person-modal-content">

			<?php if ( $person->photos ) { ?>
			<h2>Photos</h2>

			<p class="description">Select which photo to display.</p>

			<div class="person-photos choose">

				<?php foreach ( $person->photos as $index => $photo ) { ?>
				<div data-index="<?php echo esc_attr( $index ); ?>"<?php if ( isset( $display['directory_view']['photo'] ) && $index === $display['directory_view']['photo'] ) { echo ' class="selected"'; } ?>>
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

				<?php $set_titles = explode( ' ', $display['directory_view']['title'] ); ?>
				<div data-index="ad"<?php if ( isset( $display['directory_view']['title'] ) && in_array( 'ad', $set_titles, true ) ) { echo ' class="selected"'; } ?>>
					<span class="content"><?php echo esc_html( $person->position_title ); ?></span>
					<button type="button" class="wsu-person-select button-link check">
						<span class="media-modal-icon"></span>
						<span class="screen-reader-text">Deselect</span>
					</button>
				</div>

				<?php foreach ( $person->working_titles as $index => $title ) { ?>
				<div data-index="<?php echo esc_attr( $index ); ?>"<?php if ( isset( $display['directory_view']['title'] ) && in_array( (string) $index, $set_titles, true ) ) { echo ' class="selected"'; } ?>>
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
				<div data-key="personal">
					<div class="content"><?php echo wp_kses_post( apply_filters( 'the_content', $person->content->rendered ) ); ?></div>
					<button type="button" class="wsu-person-select button-link check">
						<span class="media-modal-icon"></span>
						<span class="screen-reader-text">Deselect</span>
					</button>
				</div>
				<?php } ?>

				<?php if ( $person->bio_unit ) { ?>
				<h3>Unit Biography</h3>
				<div data-key="bio_unit"<?php if ( isset( $display['directory_view']['about'] ) && 'bio_unit' === $display['directory_view']['about'] ) { echo ' class="selected"'; } ?>>
					<div class="content"><?php echo wp_kses_post( apply_filters( 'the_content', $person->bio_unit ) ); ?></div>
					<button type="button" class="wsu-person-select button-link check">
						<span class="media-modal-icon"></span>
						<span class="screen-reader-text">Deselect</span>
					</button>
				</div>
				<?php } ?>

				<?php if ( $person->bio_university ) { ?>
				<h3>University Biography</h3>
				<div data-key="bio_university"<?php if ( isset( $display['directory_view']['about'] ) && 'bio_university' === $display['directory_view']['about'] ) { echo ' class="selected"'; } ?>>
					<div class="content"><?php echo wp_kses_post( apply_filters( 'the_content', $person->bio_university ) ); ?></div>
					<button type="button" class="wsu-person-select button-link check">
						<span class="media-modal-icon"></span>
						<span class="screen-reader-text">Deselect</span>
					</button>
				</div>
				<?php } ?>

				<?php
				$tags = wp_get_post_tags( $display['local_record_id'], array(
					'fields' => 'names',
				) );
				?>
				<?php if ( $tags ) { ?>
				<h3>Tags</h3>
				<div data-key="tags"<?php if ( isset( $display['directory_view']['about'] ) && 'tags' === $display['directory_view']['about'] ) { echo ' class="selected"'; } ?>>
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
