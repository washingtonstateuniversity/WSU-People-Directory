( function( $, window ) {
	var $default_description = $( ".user-description-wrap" ),
		$tinymce_description = $( ".wsuwp-people-bio-wrap" ),
		$id = $( "[name='wsuwp_person_id']" ),
		$name = $( "#display_name" ),
		$email = $( "#email" ),
		$website = $( "#url" );

	// Remove the default description and move the TinyMCE editor into its place.
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
		$id.val( data.id );

		if ( $name.find( "option:selected" ).text() !== data.title.rendered ) {
			var $match = $name.find( "option:contains('" + data.title.rendered + "')" );
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

		if ( "" === window.tinyMCE.activeEditor.getContent() ) {
			window.tinyMCE.activeEditor.setContent( data.content.rendered );
		}
	}

	// Post data to the user's people.wsu.edu profile.
	$( "#submit" ).on( "click", function() {
		$.ajax( {
			url: window.wsupeople.rest_url + "/" + $id.val(),
			method: "POST",
			beforeSend: function( xhr ) {
				xhr.setRequestHeader( "X-WP-Nonce", window.wsupeople.nonce );
				xhr.setRequestHeader( "X-WSUWP-UID", window.wsupeople.uid );
			},
			data:{
				"title": $name.val(),
				"email": $email.val(),
				"website": $website.val(),
				"content": window.tinyMCE.activeEditor.getContent()
			}
		} );
	} );
}( jQuery, window ) );
