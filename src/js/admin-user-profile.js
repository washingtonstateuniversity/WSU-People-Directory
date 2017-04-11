( function( $ ) {
	var $default_description = $( ".user-description-wrap" ),
		$tinymce_description = $( ".wsuwp-people-bio-wrap" );

	$default_description.closest( "tr" ).remove();
	$tinymce_description.detach().insertAfter( "h2:contains('About')" );
}( jQuery ) );
