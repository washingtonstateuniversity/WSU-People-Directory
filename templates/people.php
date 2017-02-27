<?php
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
	$people_query_args = array(
		'post_type' => array( WSUWP_People_Post_Type::$post_type_slug ),
		'posts_per_page' => -1,
		'order'     => 'ASC',
		'orderby'   => 'meta_value',
		'meta_key'  => '_wsuwp_profile_ad_name_last',
	);

	$people = new WP_Query( $people_query_args );

	if ( $people->have_posts() ) {
		while ( $people->have_posts() ) {
			$people->the_post();
			include dirname( __FILE__ ) . '/person.php';
		}
		wp_reset_postdata();
	}
	?>

	</div>

</div>
<?php
