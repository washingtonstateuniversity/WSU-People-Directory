( function( $, window ) {
	$( ".wsu-person-edit" ).on( "click", function() {
		var userID = $( this ).data( "id" ),
			userListed = $( this ).data( "listed" ).split( " " );

		if ( -1 !== $.inArray( window.wsupeoplesync.site_url, userListed ) ) {
			return;
		}

		userListed.push( window.wsupeoplesync.site_url );

		$.ajax( {
			url: window.wsupeople.rest_url + "/" + userID,
			method: "POST",
			beforeSend: function( xhr ) {
				xhr.setRequestHeader( "X-WP-Nonce", window.wsupeoplesync.nonce );
			},
			data:{
				"listed_on": userListed
			}
		} );
	} );
}( jQuery, window ) );
