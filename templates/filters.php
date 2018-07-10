<?php
/**
 * Provides different methods by which to filter directory listings.
 *
 * @var array $directory_data
 */
?>
<div class="wsu-people-filters">

	<?php if ( in_array( 'search', $directory_data['filters']['options'], true ) ) { ?>
	<div class="wsu-people-filter search">
		<label>
			<span class="screen-reader-text">Start typing to search</span>
			<input type="search"
				   value=""
				   placeholder="<?php echo esc_attr( apply_filters( 'wsuwp_people_search_filter_label', 'Type to search' ) ); ?>"
				   autocomplete="off" />
		</span>
	</div>
	<?php } ?>

	<?php foreach ( $directory_data['filters']['options'] as $option ) { ?>
		<?php
		if ( 'search' === $option ) {
			continue;
		}

		$label = ( 'org' === $option ) ? 'unit' : $option;
		$label = apply_filters( "wsuwp_people_{$option}_filter_label", 'Filter by ' . $label );
		?>
		<div class="wsu-people-filter <?php echo esc_attr( $option ); ?>">
			<button type="button"
					class="wsu-people-filter-label"
					aria-expanded="false"><?php echo esc_html( $label ); ?></button>
			<ul class="wsu-people-filter-terms" id="wsu-people-<?php echo esc_attr( $option ); ?>">
				<?php foreach ( $directory_data['filters'][ $option ] as $i => $term ) { ?>
				<li>
					<label>
						<input type="checkbox" value="<?php echo esc_attr( $option ); ?>-<?php echo esc_attr( sanitize_title( $term ) ); ?>">
						<span><?php echo esc_attr( $term ); ?></span>
					</label>
				</li>
				<?php } ?>
			</ul>
		</div>
	<?php } ?>

</div>
