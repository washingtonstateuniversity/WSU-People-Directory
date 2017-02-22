( function( $, document ) {

	// Cache selectors...?

	// Load images asynchronously.
	$( document ).ready( function() {
		$( ".has-photo .photo img" ).each( function() {
			$( this ).attr( "src", $( this ).data( "photo" ) );
		} );
	} );

	// Toggle filter options.
	$( ".wsu-people-filter-label" ).on( "click", function() {
		$( this ).toggleClass( "open" );
	} );

	// Show/hide profiles according to selected filter options.
	$( ".wsu-people-filter-terms" ).on( "change", "input:checkbox", function() {

		var sort_class = [],
			profiles = $( this ).parents( ".wsu-people" ).find( ".wsu-person" );

		$( ".wsuwp-people-filter-terms input:checkbox:checked" ).each( function() {
			sort_class.push( "." + $( this ).data( "id" ) );
		} );

		if ( "" !== sort_class ) {
			profiles.not( sort_class.join( "," ) ).hide( "fast" );
			profiles.filter( sort_class.join( "," ) ).show( "fast" );
		} else {
			profiles.show( "fast" );
		}

	} );

	// Search.
	$( ".wsu-people-actions .search" ).on( "keyup", "input", function() {

		var	search_value = $( this ).val(),
			profiles = $( this ).parents( ".wsu-people" ).find( ".wsu-person" );

		if ( search_value.length > 0 ) {
			profiles.each( function() {
				var person = $( this );
				if ( person.text().toLowerCase().indexOf( search_value.toLowerCase() ) === -1 ) {
					person.hide( "fast" );
				} else {
					person.show( "fast" );
				}
			} );
		} else {
			profiles.show( "fast" );
		}

	} );

}( jQuery, document ) );
