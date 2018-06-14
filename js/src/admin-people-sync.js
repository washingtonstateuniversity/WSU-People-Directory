( function( $, sync ) {
	$( ".wsu-person-controls button" ).on( "click", function() {
		var button = this.className,
			$person = $( this ).closest( ".wsu-person" ),
			id = $person.data( "profile-id" ),
			listed_on = $person.data( "listed" ).split( " " ),
			already_listed = $.inArray( sync.site_url, listed_on );

		/**
		 * Checks if a person's profile already includes an entry for this site.
		 * If not, and the person is being edited, the entry is added.
		 * If so, and the person is being removed, the entry is removed.
		 * If neither of the above cases are true, stop processing here.
		 */
		if ( -1 === already_listed && "wsu-person-edit" === button ) {
			listed_on.push( sync.site_url );
		} else if ( -1 !== already_listed && "wsu-person-remove" === button ) {
			listed_on.splice( listed_on.indexOf( sync.site_url ), 1 );
		} else {
			return;
		}

		// Updates the listing data on the person's primary profile.
		$.ajax( {
			url: window.wsuwp_people_edit_page.rest_url + "/" + id,
			method: "POST",
			beforeSend: function( xhr ) {
				xhr.setRequestHeader( "X-WP-Nonce", sync.nonce );
				xhr.setRequestHeader( "X-WSUWP-UID", sync.uid );
			},
			data:{
				"listed_on": listed_on
			}
		} );
	} );
}( jQuery, window.wsupeoplesync ) );
