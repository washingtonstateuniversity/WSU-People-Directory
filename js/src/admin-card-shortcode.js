( function( $, window, document ) {

	// Use jQuery UI Autocomplete to suggest people.
	$( document ).on( "focus", ".shortcode-ui-edit-wsuwp_person_card [name='name']", function() {
		$( this ).autocomplete( {
			delay: 500,
			minLength: 4,
			source: function( request, response ) {
				$.ajax( {
					url: window.wsupersoncard.rest_url,
					data: {
						search: request.term,
						per_page: 50
					},
					success: function( data ) {
						response( $.map( data, function( item ) {
							return {
								value: item.title.rendered,
								nid: item.nid
							};
						} ) );
					}
				} );
			},
			open: function() {
				$( this ).autocomplete( "widget" ).zIndex( 170000 );
			},
			select: function( e, ui ) {
				$( ".shortcode-ui-attribute-nid [name='nid']" ).val( ui.item.nid );
			}
		} );
	} );
}( jQuery, window, document ) );
