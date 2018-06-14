var wsuwp = wsuwp || {};
wsuwp.people = wsuwp.people || {};

( function( $, window, wsuwp ) {
	/**
	 * Tracks whether a REST request for profile data has completed.
	 *
	 * @type {boolean}
	 */
	wsuwp.people.rest_response_complete = false;

	/**
	 * Contains the post ID of a person's main profile.
	 *
	 * @type {boolean|int}
	 */
	wsuwp.people.profile_id = false;

	/**
	 * Contains the personal biography for a person.
	 *
	 * @type {string}
	 */
	wsuwp.people.personal_bio = false;

	/**
	 * Caches selectors used throughout the script.
	 */
	var $name = $( "#display_name" ),
		$email = $( "#email" ),
		$website = $( "#url" );

	/**
	 * Removes the default description and move the TinyMCE editor into its place.
	 */
	$( ".user-description-wrap" ).closest( "tr" ).remove();
	$( ".wsuwp-people-bio-wrap" ).detach().insertAfter( "h2:contains('About')" );

	/**
	 * Makes a REST request to retrieve the user's main profile.
	 */
	$.ajax( {
		url: wsuwp.people.rest_url,
		data: {
			wsu_nid: wsuwp.people.nid
		}
	} ).done( function( response ) {
		if ( response.length !== 0 ) {
			wsuwp.people.populate_profile_fields( response[ 0 ] );
		}

		wsuwp.people.rest_response_complete = true;
	} );

	/**
	 * Populates profile fields with data from the REST API.
	 *
	 * @param data
	 */
	wsuwp.people.populate_profile_fields = function( data ) {
		wsuwp.people.profile_id = data.id;
		wsuwp.people.name = data.title.rendered;
		wsuwp.people.email = data.email;
		wsuwp.people.website = data.website;
		wsuwp.people.personal_bio = data.content.rendered;

		if ( $name.find( "option:selected" ).text() !== wsuwp.people.name ) {
			var $match = $name.find( "option:contains('" + wsuwp.people.name + "')" );
			if ( $match ) {
				$match.attr( "selected", "selected" );
			}
		}

		if ( !$email.val() ) {
			$email.val( wsuwp.people.email );
		}

		if ( !$website.val() ) {
			$website.val( wsuwp.people.website );
		}
	};

	/**
	 * Populates the biography editor with data from the REST API.
	 *
	 * This is registered as a callback when TinyMCE inits the editor.
	 *
	 * @param editor
	 */
	wsuwp.people.populate_editor = function( editor ) {
		if ( false === wsuwp.people.rest_response_complete && false === wsuwp.people.personal_bio ) {
			setTimeout( wsuwp.people.populate_editor.bind( null, editor ), 200 );
			return;
		}

		if ( wsuwp.people.personal_bio ) {
			window.tinymce.activeEditor.setContent( wsuwp.people.personal_bio );
		}
	};

	/**
	 * Posts data to the user's main profile.
	 * Commenting this out for now.
	$( "#submit" ).on( "click", function() {
		if ( false === wsuwp.people.profile_id ) {
			return;
		}

		var data = {};

		if ( $name.val() !== wsuwp.people.name ) {
			data.title = $name.val();
		}

		if ( $email.val() !== wsuwp.people.email ) {
			data.email = $email.val();
		}

		if ( $website.val() !== wsuwp.people.website ) {
			data.website = $website.val();
		}

		if ( window.tinymce.activeEditor.getContent() !== wsuwp.people.personal_bio ) {
			data.content = window.tinymce.activeEditor.getContent();
		}

		if ( $.isEmptyObject( data ) ) {
			return;
		}

		$.ajax( {
			url: wsuwp.people.rest_url + "/" + wsuwp.people.profile_id,
			method: "POST",
			beforeSend: function( xhr ) {
				xhr.setRequestHeader( "X-WP-Nonce", wsuwp.people.nonce );
				xhr.setRequestHeader( "X-WSUWP-UID", wsuwp.people.uid );
			},
			data: data
		} );
	} ); */
}( jQuery, window, wsuwp ) );
