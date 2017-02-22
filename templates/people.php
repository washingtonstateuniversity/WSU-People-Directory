<?php
// Retrieve people from people.wsu.edu.
$request_url = 'https://people.wsu.edu/wp-json/wp/v2/people/';

//$request_url = add_query_arg( array( 'filter[wsuwp_university_org]' => $terms ), $request_url );

$response = wp_remote_get( $request_url );

if ( is_wp_error( $response ) ) {
	return '<!-- ' . sanitize_text_field( $response->get_error_message() ) . ' -->';
}

$data = wp_remote_retrieve_body( $response );

if ( empty( $data ) ) {
	return '<!-- empty -->';
}

$people = json_decode( $data );

if ( empty( $people ) ) {
	return '<!-- empty -->';
}

$options = get_option( 'wsu_people_display' );

$wrapper_classes = array( 'wsu-people-wrapper' );

// Layout options to come - table, grid, etc.
if ( isset( $options['layout'] ) && '' !== $options['layout'] ) {
	$wrapper_classes[] = esc_html( $options['layout'] );
} else {
	$wrapper_classes[] = 'table';
}

// This option also needs to be set up.
if ( isset( $options['show_photos'] ) && 'yes' === $options['show_photos'] ) {
	$wrapper_classes[] = 'photos';
} else {
	$wrapper_classes[] = 'photos';
}

// $base_url is used to create the link to individual profiles in templates/person.php
$slug = ( isset( $options['slug'] ) && '' !== $options['slug'] ) ? $options['slug'] : 'people';
$base_url = trailingslashit( trailingslashit( get_home_url() ) . $slug );
?>
<div class="<?php echo esc_html( implode( ' ', $wrapper_classes ) ); ?>">

	<div class="wsu-people">
	<?php
	foreach ( $people as $person ) {
		include dirname( __FILE__ ) . '/person.php';
	}
	?>
	</div>

</div>
<?php
