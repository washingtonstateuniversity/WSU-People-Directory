<?php
if ( ! is_admin() ) {
	$page_id = get_the_ID();
	$ids = get_post_meta( $page_id, '_wsu_people_directory_profile_ids', true );
	$layout = get_post_meta( $page_id, '_wsu_people_directory_layout', true );
	$link = get_post_meta( $page_id, '_wsu_people_directory_link', true );
	$profile = get_post_meta( $page_id, '_wsu_people_directory_profile', true );
	$show_photo = get_post_meta( $page_id, '_wsu_people_directory_show_photos', true );
	$base_url = get_permalink();
}

$wrapper_classes = array( 'wsu-people-wrapper' );
$wrapper_classes[] = ( $layout ) ? esc_attr( $layout ) : 'table';

if ( 'yes' === $show_photo ) {
	$wrapper_classes[] = 'photos';
}

$lazy_load_photos = true;

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

			if ( is_wp_error( $response ) ) {
				return;
			}

			$data = wp_remote_retrieve_body( $response );

			if ( empty( $data ) ) {
				return;
			}

			$people = json_decode( $data );

			if ( empty( $people ) ) {
				return;
			}

			$template = ( WSUWP_Person_Display::theme_has_template() ) ? WSUWP_Person_Display::theme_has_template() : dirname( __FILE__ ) . '/person.php';

			foreach ( $people as $person ) {

				$local_record = get_posts( array(
					'post_type' => WSUWP_People_Post_Type::$post_type_slug,
					'posts_per_page' => 1,
					'meta_key' => '_wsuwp_profile_post_id',
					'meta_value' => $person->id,
				) );

				if ( $local_record ) {
					$local_record_id = $local_record[0]->ID;
					$local_data = WSUWP_Person_Display::get_local_single_view_data( $local_record_id );
					$listing_data = get_post_meta( $local_record_id, "_display_on_page_{$page_id}", true );
					$show_header = true;
					$link = trailingslashit( $base_url . $local_record[0]->post_name );
					$use_title = ( isset( $listing_data['title'] ) ) ? $listing_data['title'] : $local_data['use_title'];
					$use_photo = ( isset( $listing_data['photo'] ) ) ? $listing_data['photo'] : $local_data['use_photo'];
				}

				include $template;
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
