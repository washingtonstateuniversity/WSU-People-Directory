<?php
/**
 * Directory template.
 *
 * This template can be overridden by including `wsu-people/people.php` in your theme.
 *
 * @var array $directory_data
 */
?>
<div class="<?php echo esc_attr( $directory_data['wrapper_classes'] ); ?>">

	<?php
	if ( ! empty( $directory_data['filters']['options'] ) ) {
		include $directory_data['filters']['template'];
	}
	?>

	<div class="wsu-people">

		<?php
		foreach ( $directory_data['people'] as $person ) {
			$display = WSUWP\People_Directory\Profile_Display\get_listing_data( $person, $directory_data['profile_display_options'] );
			include $directory_data['person_card_template'];
		}
		?>

	</div>

</div>
