/* global _ */
( function( $, window, document ) {
	$( document ).ready( function() {

		var $directory_configuration = $( "#wsuwp-people-directory-configuration" ),
			$editor = $( "#postdivrich" ),
			$person_template = _.template( $( "#wsu-person-template" ).html() );

		// Toggle default editor and People Directory Setup metabox on template select.
		$( "#page_template" ).on( "change", function() {
			if ( "templates/people.php" === $( this ).val() ) {
				$directory_configuration.show();
				$editor.hide();
			} else {
				$directory_configuration.hide();

				if ( "template-builder.php" !== $( this ).val() ) {
					$editor.show();
				}
			}
		} );
	} );
}( jQuery, window, document ) );
