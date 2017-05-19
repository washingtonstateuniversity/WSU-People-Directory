<?php
/**
 * Directory template.
 *
 * This template can be overridden by including `wsu-people-templates/people.php` in your theme.
 *
 * @var array $directory_data
 */
?>
<div class="<?php echo esc_attr( $directory_data['wrapper_classes'] ); ?>">

	<div class="wsu-people">

	<?php
	foreach ( $directory_data['people'] as $person ) {
		$display = WSUWP_Person_Display::get_data( $person, $directory_data['profile_display_options'] );
		include $directory_data['person_card_template'];
	}
	?>

	</div>

</div>
