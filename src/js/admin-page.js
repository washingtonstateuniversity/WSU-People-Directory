/* global _ */
( function( $, window, document ) {
	var $page_template_select = $( "#page_template" ),
		$directory_configuration = $( "#wsuwp-people-directory-configuration" ),
		$editor = $( "#postdivrich" ),
		$add_people = $( "#wsu-people-import" ),
		$page_nids = $( "#directory-page-nids" ),
		organizations = $( "#wsuwp_university_orgchecklist .selectit" ).map( function() { return $( this ).text(); } ).get(),
		$people_wrapper = $( ".wsu-people" ),
		$person_template = _.template( $( "#wsu-person-template" ).html() );

	$( document ).ready( function() {

		// Toggle default editor and People Directory Setup metabox visibility on template select.
		$page_template_select.on( "change", function() {
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

		// Use jQuery UI Autocomplete to suggest University Organizations.
		$add_people.autocomplete( {
			source: organizations,
			select: function( e, ui ) {
				makeRequest( ui );
			}
		} );

		// Toggle bulk selection mode.
		$( ".wsu-people-bulk-actions" ).on( "click", ".toggle-select-mode", function() {
			if ( "Bulk Select" === $( this ).text() ) {
				$( this ).text( "Cancel Selection" );
				$( ".delete-selected-people" ).show();
				$( ".wsu-people-wrapper" ).addClass( "bulk-select" );
			} else {
				$( this ).text( "Bulk Select" );
				$( ".delete-selected-people" ).hide();
				$( ".wsu-people-wrapper" ).removeClass( "bulk-select" );
			}
		} );

		// Use jQuery UI Sortable to allow reordering of people.
		$people_wrapper.sortable( {
			cursor: "move",
			start: function( e, ui ) {
				ui.placeholder.height( ui.item.height() );
			},
			stop: function() {
				updateNidList();
			}
		} );

		// Edit a person.
		$people_wrapper.on( "click", ".wsu-person-edit", function( e ) {
			e.preventDefault();

			// This could be where the photo and bio to display are selected,
			// so that decision can be made per-page rather than per-site.
		} );

		// Delete a person
		$people_wrapper.on( "click", ".wsu-person-remove", function( e ) {
			e.preventDefault();

			$( this ).closest( ".wsu-person" ).remove();

			updateNidList();
		} );
	} );

	// Get all the people for the given organization via a REST request.
	function makeRequest( ui ) {
		jQuery.ajax( {
			url: window.wsupeople.rest_url,
			data: {
				"filter[wsuwp_university_org]": ui.item.value,
				per_page: 100
			}
		} ).done( function( response ) {
			if ( response.length !== 0 ) {
				response.sort( sortResponse );
				createPerson( response );
			}
		} );
	}

	// Sort the retrieved people alphabetically by last name.
	function sortResponse( a, b ) {
		if ( a.last_name < b.last_name ) {
			return -1;
		}
		if ( a.last_name > b.last_name ) {
			return 1;
		}
		return 0;
	}

	// Add a person retrieved from the REST request to the list.
	function createPerson( person ) {
		$.each( person, function( i, data ) {
			var $nids_field = $( "#directory-page-nids" ),
				listed_nids = $people_wrapper.find( ".wsu-person" ).map( function() { return $( this ).data( "nid" ); } ).get();

			// Don't add the person if they're already listed.
			if ( -1 !== $.inArray( data.nid, listed_nids ) ) {
				return;
			}

			$nids_field.val( function() {
				return this.value + " " + data.nid;
			} );

			$( ".wsu-people" ).append( $person_template( {
				nid: data.nid,
				has_photo: ( 0 < data.photos.length ) ? " has-photo" : "",
				slug: data.slug,
				name: data.title.rendered,
				photo: ( 0 < data.photos.length ) ? data.photos[ 0 ].thumbnail : "",
				title: ( 0 < data.working_titles.length ) ? data.working_titles[ 0 ] : data.position_title,
				email: ( data.email_alt ) ? data.email_alt : data.email,
				phone: ( data.phone_alt ) ? data.phone_alt : data.phone + data.phone_ext,
				office: ( data.office_alt ) ? data.office_alt : data.office,
				address: data.address,
				website: data.website,
				content: data.content.rendered
			} ) );
		} );
	}

	// Update the list of NIDs associated with this page.
	function updateNidList() {
		var nids = $people_wrapper.find( ".wsu-person" ).map( function() { return $( this ).data( "nid" ); } ).get();

		$page_nids.val( nids.join( " " ) );
	}
}( jQuery, window, document ) );
