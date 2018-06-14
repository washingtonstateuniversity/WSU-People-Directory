/* global ajaxurl, inlineEditPost */

/* Sourced from https://codex.wordpress.org/Plugin_API/Action_Reference/bulk_edit_custom_box */
( function( $, window, document ) {
	$( document ).ready( function() {

		// Create a copy of the WP inline edit post function.
		var $wp_inline_edit = inlineEditPost.edit;

		// Overwrite the function.
		inlineEditPost.edit = function( id ) {

			// "Call" the original WP edit function.
			$wp_inline_edit.apply( this, arguments );

			// Get the post ID.
			var $post_id = 0;

			if ( "object" === typeof( id ) ) {
				$post_id = parseInt( this.getId( id ) );
			}

			if ( $post_id > 0 ) {
				var $edit_row = $( "#edit-" + $post_id ),
					$post_row = $( "#post-" + $post_id ),
					$bio = $( ".column-use_bio span", $post_row ).data( "bio" );

				// Mark the active option.
				$( "[name='_use_bio'] option[value='" + $bio + "']", $edit_row ).attr( "selected", "selected" );
			}
		};

		// Save the new option.
		$( document ).on( "click", "#bulk_edit", function() {
			var $bulk_row = $( "#bulk-edit" ),
				$post_ids = [],
				$bio = $bulk_row.find( "[name='_use_bio']" ).val();

			$bulk_row.find( "#bulk-titles" ).children().each( function() {
				$post_ids.push( $( this ).attr( "id" ).replace( /^(ttle)/i, "" ) );
			} );

			$.ajax( {
				url: ajaxurl,
				type: "POST",
				async: false,
				cache: false,
				data: {
					action: "save_bio_edit",
					nonce: window.wsupeople.nonce,
					post_ids: $post_ids,
					use_bio: $bio
				}
			} );
		} );
	} );
}( jQuery, window, document ) );
