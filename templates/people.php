<?php
$page_id = get_the_ID();
$nids = get_post_meta( $page_id, '_wsu_people_directory_nids', true );
$link = get_post_meta( $page_id, '_wsu_people_directory_link', true );
$profile = get_post_meta( $page_id, '_wsu_people_directory_profile', true );
$base_url = get_permalink();
$wrapper_classes = array( 'wsu-people-wrapper' );
$wrapper_classes[] = ( $layout = get_post_meta( $page_id, '_wsu_people_directory_layout', true ) ) ? esc_attr( $layout ) : 'table';

if ( 'yes' === get_post_meta( $page_id, '_wsu_people_directory_show_photos', true ) ) {
	$wrapper_classes[] = 'photos';
}

?>
<div class="<?php echo esc_html( implode( ' ', $wrapper_classes ) ); ?>">

	<div class="wsu-people">

	<?php
	if ( $nids ) {
		$nids = explode( ' ', $nids );

		$people_query_args = array(
			'post_type' => WSUWP_People_Post_Type::$post_type_slug,
			'posts_per_page' => count( $nids ),
			'meta_key' => "order_on_page_{$page_id}",
			'orderby' => 'meta_value_num',
			'order' => 'asc',
			'meta_query' => array(
				array(
					'meta_key' => '_wsuwp_profile_ad_nid',
					'meta_value' => $nids,
					'meta_compare' => 'in',
				),
			),
		);

		$people = new WP_Query( $people_query_args );

		if ( $people->have_posts() ) {
			while ( $people->have_posts() ) {
				$people->the_post();
				include dirname( __FILE__ ) . '/person.php';
			}
			wp_reset_postdata();
		}
	}
	?>

	</div>

</div>
<?php
