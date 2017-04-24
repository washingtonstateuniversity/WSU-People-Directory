<?php
if ( ! is_admin() ) {
	$page_id = get_the_ID();
	$ids = get_post_meta( $page_id, '_wsu_people_directory_profile_ids', true );
	$layout = get_post_meta( $page_id, '_wsu_people_directory_layout', true );
	$link = get_post_meta( $page_id, '_wsu_people_directory_link', true );
	$profile = get_post_meta( $page_id, '_wsu_people_directory_profile', true );
	$photos = get_post_meta( $page_id, '_wsu_people_directory_show_photos', true );
	$base_url = get_permalink();
}

$wrapper_classes = array( 'wsu-people-wrapper' );
$wrapper_classes[] = ( $layout ) ? esc_attr( $layout ) : 'table';

if ( 'yes' === $photos ) {
	$wrapper_classes[] = 'photos';
}

?>
<div class="<?php echo esc_html( implode( ' ', $wrapper_classes ) ); ?>">

	<div class="wsu-people">

	<?php
	// Loop through the local records in their display order and add an `include`
	// parameter to the request URL for each one, then leverage `orderby=include`
	// to retrieve the primary records in the desired order. Silly, but effective.
	if ( $ids ) {
		$ids = explode( ' ', $ids );

		$request_url = add_query_arg( array(
			'per_page' => count( $ids ),
			'orderby' => 'include',
		), WSUWP_People_Directory::REST_URL() );

		$people_query_args = array(
			'post_type' => WSUWP_People_Post_Type::$post_type_slug,
			'posts_per_page' => count( $ids ),
			'meta_key' => "_order_on_page_{$page_id}",
			'orderby' => 'meta_value_num',
			'order' => 'asc',
			'meta_query' => array(
				array(
					'key' => '_on_page',
					'value' => $page_id,
				),
			),
		);

		$people = new WP_Query( $people_query_args );

		if ( $people->have_posts() ) {
			while ( $people->have_posts() ) {
				$people->the_post();
				$profile_id = get_post_meta( get_the_ID(), '_wsuwp_profile_post_id', true );
				$request_url = add_query_arg( 'include[]', $profile_id, $request_url );
			}
			wp_reset_postdata();

			$response = wp_remote_get( $request_url );

			if ( ! is_wp_error( $response ) ) {
				$data = wp_remote_retrieve_body( $response );

				if ( ! empty( $data ) ) {
					$people = json_decode( $data );

					if ( ! empty( $people ) ) {
						foreach ( $people as $person ) {
							include dirname( __FILE__ ) . '/person.php';
						}
					}
				}
			}
		}
	}
	?>

	</div>

	<?php if ( is_admin() ) { ?>
	<div class="wsu-person-controls-tooltip" role="presentation">
		<div class="wsu-person-controls-tooltip-arrow"></div>
		<div class="wsu-person-controls-tooltip-inner"></div>
	</div>
	<?php } ?>

</div>
<?php
