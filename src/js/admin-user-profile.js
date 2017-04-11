( function( $, window ) {

	// Remove the default description and move the TinyMCE editor into its place.
	var $default_description = $( ".user-description-wrap" ),
		$tinymce_description = $( ".wsuwp-people-bio-wrap" );

	$default_description.closest( "tr" ).remove();
	$tinymce_description.detach().insertAfter( "h2:contains('About')" );

	// Make a REST request to retrieve the user's people.wsu.edu profile.
	$.ajax( {
		url: window.wsupeople.rest_url,
		data: {
			wsu_nid: window.wsupeople.nid
		}
	} ).done( function( response ) {
		if ( response.length !== 0 ) {
			populateProfile( response[ 0 ] );
		}
	} );

	// Populate profile with data from people.wsu.edu.
	function populateProfile( data ) {
		var $id = $( "[name='wsuwp_person_id']" ),
			$display_name = $( "#display_name" ),
			$email = $( "#email" ),
			$website = $( "#url" ),
			$biography = window.tinyMCE.activeEditor;

		$id.val( data.id );

		if ( $display_name.find( "option:selected" ).text() !== data.title.rendered ) {
			var $match = $display_name.find( "option:contains('" + data.title.rendered + "')" );
			if ( $match ) {
				$match.attr( "selected", "selected" );
			}
		}

		if ( !$email.val() ) {
			$email.val( data.email );
		}

		if ( !$website.val() ) {
			$website.val( data.website );
		}

		if ( "" === $biography.getContent() ) {
			$biography.setContent( data.content.rendered );
		}
	}
}( jQuery, window ) );
