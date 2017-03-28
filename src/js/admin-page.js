/* global _ */
( function( $, window, document ) {
	var $page_template_select = $( "#page_template" ),
		$directory_configuration = $( "#wsuwp-people-directory-configuration" ),
		$editor = $( "#postdivrich" ),
		$add_people = $( "#wsu-people-import" ),
		$page_nids = $( "#directory-page-nids" ),
		$layout_option = $( "#wsu-people-directory-layout" ),
		$photos_option = $( "#wsu-people-directory-show-photos" ),
		organizations = $( "#wsuwp_university_orgchecklist .selectit" ).map( function() { return $( this ).text(); } ).get(),
		$bulk_select = $( ".toggle-select-mode" ),
		$select_all = $( ".select-all-people" ),
		$delete_selection = $( ".delete-selected-people" ),
		$people_wrapper = $( ".wsu-people-wrapper" ),
		$people = $( ".wsu-people" ),
		$person_modal = $( ".person-modal" ),
		$tooltip = $( ".wsu-person-controls-tooltip" ),
		$person_template = _.template( $( "#wsu-person-template" ).html() );

	$( document ).ready( function() {

		// Load photos asynchronously.
		loadPhotos();

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

		// Toggle the "photos" class when the "Show Photos" option is changed.
		$photos_option.on( "change", function() {
			if ( "yes" === $( this ).val() ) {
				$people_wrapper.addClass( "photos" );
			} else {
				$people_wrapper.removeClass( "photos" );
			}
		} );

		// Update layout class when the "Layout" option is changed.
		$layout_option.on( "change", function() {
			var $layout = $( this ).val();
			$layout_option.find( "option" ).not( ":selected" ).each( function() {
				$people_wrapper.removeClass( $( this ).val() );
			} );
			$people_wrapper.addClass( $layout );
		} );

		// Use jQuery UI Sortable to allow reordering of people.
		$people.sortable( {
			cursor: "move",
			start: function( e, ui ) {
				ui.placeholder.height( ui.item.height() );
			},
			stop: function() {
				updateNidList();
			}
		} );

		// Toggle bulk selection mode.
		$bulk_select.on( "click", function() {
			if ( "Bulk Select" === $( this ).text() ) {
				$( this ).text( "Cancel Selection" );
				$select_all.show();
				$delete_selection.show().prop( "disabled", true );
				$people_wrapper.addClass( "bulk-select" );
				$people.sortable( "option", "disabled", true );
			} else {
				$( this ).text( "Bulk Select" );
				$select_all.hide();
				$delete_selection.hide().prop( "disabled", true );
				$people_wrapper.removeClass( "bulk-select" );
				$people.find( ".wsu-person" ).removeClass( "selected" ).attr( "aria-checked", "false" );
				$people.sortable( "option", "disabled", false );
			}
		} );

		// Select/deselect all people.
		$select_all.on( "click", function() {
			if ( "Select All" === $( this ).text() ) {
				$( this ).text( "Deselect All" );
				$people.find( ".wsu-person" ).addClass( "selected" ).attr( "aria-checked", "true" );
				$delete_selection.prop( "disabled", false );
			} else {
				$( this ).text( "Select All" );
				$people.find( ".wsu-person" ).removeClass( "selected" ).attr( "aria-checked", "false" );
				$delete_selection.prop( "disabled", true );
			}
		} );

		// Select individual people in bulk selection mode.
		$people.on( "click", ".wsu-person", function() {
			if ( $people_wrapper.is( ".bulk-select" ) ) {
				$( this ).toggleClass( "selected" );
				if ( $( this ).hasClass( "selected" ) ) {
					$( this ).attr( "aria-checked", "true" );
				} else {
					$( this ).attr( "aria-checked", "false" );
				}

				if ( 0 < $people.find( ".selected" ).length ) {
					$delete_selection.prop( "disabled", false );
				} else {
					$select_all.text( "Select All" );
					$delete_selection.prop( "disabled", true );
				}

				if ( $people.find( ".wsu-person" ).length === $people.find( ".selected" ).length ) {
					$select_all.text( "Deselect All" );
				}
			}
		} );

		// Delete bulk selected people.
		$delete_selection.on( "click", function() {
			$people.find( ".selected" ).remove();
			updateNidList();
		} );

		// Show control buttons tooltip.
		$people.on( "mouseover", ".wsu-person-controls button", function() {
			var text = this.getAttribute( "aria-label" ),
				button = this.getBoundingClientRect(),
				people = $people[ 0 ].getBoundingClientRect();

			$tooltip.find( ".wsu-person-controls-tooltip-inner" ).html( text );

			$tooltip.css( {
				top: ( button.bottom - people.top ) + "px",
				left: ( button.right - people.left - $tooltip.width() / 2 - button.width / 2 ) + "px"
			} ).show();
		} );

		// Hide control buttons tooltip.
		$people.on( "mouseleave", ".wsu-person-controls button", function() {
			$tooltip.hide();
		} );

		// Open the details modal for a person.
		$people.on( "click", ".wsu-person-edit", function() {
			$( this ).closest( ".wsu-person" ).find( ".person-modal-wrapper" ).addClass( "active" );
			$( "body" ).addClass( "person-modal-open" );
			$people.sortable( "option", "disabled", true );
		} );

		// Delete a person
		$people.on( "click", ".wsu-person-remove", function() {
			$( this ).closest( ".wsu-person" ).remove();

			updateNidList();
		} );

		// Close a person without updating.
		$people.on( "click", ".close", function( e ) {
			if ( e.target === this ) {
				$( this ).closest( ".person-modal-wrapper" ).removeClass( "active" );
				$( "body" ).removeClass( "person-modal-open" );
				$people.sortable( "option", "disabled", false );
			}
		} );

		// Edit a person.
		$person_modal.on( "click", ".choose > div", function() {
			$( this ).toggleClass( "selected" );

			if ( !$( this ).closest( ".choose" ).hasClass( "multiple" ) ) {
				$( this ).siblings().removeClass( "selected" );
			}
		} );

		// Update a person.
		$person_modal.on( "click", ".person-update", function() {
			var $person = $( this ).closest( ".wsu-person" ),
				$modal = $( this ).closest( ".person-modal-wrapper" ),
				data = {
					action: "person_details",
					nonce: window.wsupeople.nonce,
					page: window.wsupeople.page_id,
					post: $person.data( "post-id" )
				};

			if ( $modal.find( ".person-photos .selected" ) ) {
				var $photo = $modal.find( ".person-photos .selected" );

				data.photo = $photo.data( "index" );
				$person.find( ".photo img" ).attr( "src",  $photo.find( "img" ).attr( "src" ) );
			}

			if ( $modal.find( ".person-titles .selected" ) ) {
				var new_title = "",
					title_indexes = [],
					selected_titles = $modal.find( ".person-titles .selected .content" ),
					count = selected_titles.length;

				selected_titles.each( function( i ) {
					title_indexes.push( $( this ).closest( ".selected" ).data( "index" ) );
					new_title += $( this ).html();

					if ( i !== count - 1 ) {
						new_title += "<br />";
					}
				} );

				data.title = title_indexes.join( " " );
				$person.find( ".card .title" ).html( new_title );
			}

			if ( $modal.find( ".person-content .selected" ) ) {
				var $about = $modal.find( ".person-content .selected" );

				data.about = $about.data( "key" );
				$person.find( ".about" ).html(  $about.find( ".content" ).html() );
			}

			updatePersonDetails( data, $modal );
		} );
	} );

	// Load photos asynchronously.
	function loadPhotos() {
		$( ".has-photo .photo img" ).each( function() {
			$( this ).attr( "src", $( this ).data( "photo" ) );
		} );
	}

	// Get all the people for the given organization via a REST request.
	function makeRequest( ui ) {
		$.ajax( {
			url: window.wsupeople.rest_url,
			data: {
				"filter[wsuwp_university_org]": ui.item.value,
				per_page: 100
			}
		} ).done( function( response ) {
			if ( response.length !== 0 ) {
				response.sort( sortResponse );
				createPerson( response );
				loadPhotos();
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
				listed_nids = $people.find( ".wsu-person" ).map( function() { return $( this ).data( "nid" ); } ).get();

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
		var nids = $people.find( ".wsu-person" ).map( function() { return $( this ).data( "nid" ); } ).get();

		$page_nids.val( nids.join( " " ) );
	}

	// Update a person's details.
	function updatePersonDetails( data, $modal ) {
		window.console.log( data );
		$.post( window.wsupeople.ajax_url, data ).done( function() {
			$modal.removeClass( "active" );
			$people.sortable( "option", "disabled", false );
			$( "body" ).removeClass( "person-modal-open" );
		} );
	}
}( jQuery, window, document ) );
