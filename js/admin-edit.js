jQuery(document).ready(function( $ ) {

		// Maybe heavy-handed, but these labels have no unique attributes to leverage.
		$( '.inline-edit-categories-label:contains("Appointments")' ).remove();
		$( '.inline-edit-categories-label:contains("Classifications")' ).remove();

});