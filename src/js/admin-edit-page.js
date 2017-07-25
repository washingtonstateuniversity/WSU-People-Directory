/* global _ */
( function( $, window, document ) {
	var $page_template_select = $( "#page_template" ),
		$directory_configuration = $( "#wsuwp-people-directory-configuration" ),
		$editor = $( "#postdivrich" ),
		$profile_ids = $( "#directory-page-profile-ids" ),
		$section_toggle = $( ".wsu-people-directory-options-header" ),
		$import_organization = $( "#wsu-people-import-organization" ),
		$import_location = $( "#wsu-people-import-location" ),
		$import_category = $( "#wsu-people-import-category" ),
		$import_classification = $( "#wsu-people-import-classification" ),
		$import_tag = $( "#wsu-people-import-tag" ),
		$layout_option = $( "#wsu-people-directory-layout" ),
		$photos_option = $( "#wsu-people-directory-show-photos" ),
		$about_option = $( "#wsu-people-directory-about" ),
		$link_option = $( "#wsu-people-directory-link" ),
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
		load_photos();

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
		$import_organization.autocomplete( {
			source: $( "#wsuwp_university_orgchecklist .selectit" ).map( function() { return $( this ).text(); } ).get()
		} );

		// Use jQuery UI Autocomplete to suggest University Locations.
		$import_location.autocomplete( {
			source: $( "#wsuwp_university_locationchecklist .selectit" ).map( function() { return $( this ).text(); } ).get()
		} );

		// Use jQuery UI Autocomplete to suggest University Categories.
		$import_category.autocomplete( {
			source: $( "#wsuwp_university_categorychecklist .selectit" ).map( function() { return $( this ).text(); } ).get()
		} );

		// Use jQuery UI Autocomplete to suggest Classifications.
		$import_classification.autocomplete( {
			delay: 500,
			minLength: 3,
			source: function( request, response ) {
				$.ajax( {
					url: window.wsuwp_people_edit_page.rest_route + "classification",
					data: {
						search: request.term,
						per_page: 50
					},
					success: function( data ) {
						response( $.map( data, function( item ) {
							return {
								label: item.name,
								value: item.slug
							};
						} ) );
					}
				} );
			}
		} );

		// Use jQuery UI Autocomplete to suggest Tags.
		$import_tag.autocomplete( {
			delay: 500,
			minLength: 3,
			source: function( request, response ) {
				$.ajax( {
					url: window.wsuwp_people_edit_page.rest_route + "tags",
					data: {
						search: request.term,
						per_page: 50
					},
					success: function( data ) {
						response( $.map( data, function( item ) {
							return {
								label: item.name,
								value: item.slug
							};
						} ) );
					}
				} );
			}
		} );

		// Get people via REST request.
		$( "#wsu-people-import" ).on( "click", function() {
			var data = { per_page: 100 };

			// Add the University Organization parameter.
			if ( "" !== $import_organization.val() ) {
				data[ "filter[wsuwp_university_org]" ] = $import_organization.val();
			}

			// Add the University Location parameter.
			if ( "" !== $import_location.val() ) {
				data[ "filter[wsuwp_university_location]" ] = $import_location.val();
			}

			// Add the University Category parameter.
			if ( "" !== $import_category.val() ) {
				data[ "filter[wsuwp_university_category]" ] = $import_category.val();
			}

			// Add the Classification parameter.
			if ( "" !== $import_classification.val() ) {
				data[ "filter[classification]" ] = $import_classification.val();
			}

			// Add the Tag parameter.
			if ( "" !== $import_tag.val() ) {
				data[ "filter[tag]" ] = $import_tag.val();
			}

			// Display a loading indicator.
			$people.html( "<span class='spinner'></span>" );

			// Make the REST request.
			$.ajax( {
				url: window.wsuwp_people_edit_page.rest_url,
				data: data
			} ).done( function( response ) {

				// Remove the loading indicator.
				$people.find( ".spinner" ).remove();

				if ( response.length !== 0 ) {
					response.sort( sort_response );
					create_person( response );
					load_photos();

					// Toggle the "Add People" options closed.
					$( ".wsu-people-directory-options.add" ).removeClass( "open" ).children( "div" ).hide();

					// Toggle the visibility of the other options.
					$( ".wsu-people-directory-options, .wsu-people-bulk-actions" ).slideDown();
				}
			} );
		} );

		// Toggle a section when its header is clicked.
		$section_toggle.on( "click", function() {
			var $section = $( this ).closest( ".wsu-people-directory-options" ),
				$options = $section.children( "div" );

			if ( $section.hasClass( "open" ) ) {
				$section.removeClass( "open" );
				$options.slideUp();
			} else {
				$section.addClass( "open" );
				$options.slideDown();
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

		// Toggle the "photos" class when the "Show Photos" option is changed.
		$photos_option.on( "change", function() {
			if ( "yes" === $( this ).val() ) {
				$people_wrapper.addClass( "photos" );
			} else {
				$people_wrapper.removeClass( "photos" );
			}
		} );

		// Update each profile's "about" content when the "about" option is changed.
		$about_option.on( "change", function() {
			var option_value = $( this ).val();

			$people.find( ".wsu-person" ).each( function() {
				var $about_option_container = $( this ).find( "div[data-key='" + option_value + "']" ),
					$about_container = $( this ).find( ".about" );

				if ( "none" === option_value || !$about_option_container.length ) {
					$about_container.html( "" );
				} else {
					$about_container.html( $about_option_container.find( ".content" ).html() );
				}
			} );
		} );

		// Update each profile when the "link" option is changed.
		$link_option.on( "change", function() {
			var option_value = $( this ).val();

			$people.find( ".wsu-person" ).each( function() {
				var $personal_bio = $( this ).find( "div[data-key='personal']" );

				if ( "no" === option_value || ( "if_bio" === option_value && !$personal_bio.length ) ) {
					$( this ).addClass( "no-link" ).removeClass( "link" );
				} else {
					$( this ).removeClass( "no-link" ).addClass( "link" );
				}
			} );
		} );

		// Use jQuery UI Sortable to allow reordering of people.
		$people.sortable( {
			cursor: "move",
			start: function( e, ui ) {
				ui.placeholder.height( ui.item.height() );
			},
			stop: function() {
				update_id_list();
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
			update_id_list();
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

			update_id_list();
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
					nonce: window.wsuwp_people_edit_page.nonce,
					page: window.wsuwp_people_edit_page.page_id,
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

			update_person_details( data, $modal );
		} );
	} );

	// Load photos asynchronously.
	function load_photos() {
		$( ".has-photo .photo img" ).each( function() {
			$( this ).attr( "src", $( this ).data( "photo" ) );
		} );
	}

	// Sort the retrieved people alphabetically by last name.
	function sort_response( a, b ) {
		if ( a.last_name < b.last_name ) {
			return -1;
		}
		if ( a.last_name > b.last_name ) {
			return 1;
		}
		return 0;
	}

	// Add a person retrieved from the REST request to the list.
	function create_person( person ) {
		$.each( person, function( i, data ) {
			var listed_ids = $people.find( ".wsu-person" ).map( function() { return $( this ).data( "profile-id" ); } ).get();

			// Don't add the person if they're already listed.
			if ( -1 !== $.inArray( data.id, listed_ids ) ) {
				return;
			}

			$profile_ids.val( function() {
				return this.value + " " + data.id;
			} );

			$people.append( $person_template( {
				nid: data.nid,
				id: data.id,
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

	// Update the list of profile IDs associated with this page.
	function update_id_list() {
		var ids = $people.find( ".wsu-person" ).map( function() { return $( this ).data( "profile-id" ); } ).get();

		$profile_ids.val( ids.join( " " ) );
	}

	// Update a person's details.
	function update_person_details( data, $modal ) {
		$.post( window.wsuwp_people_edit_page.ajax_url, data ).done( function() {
			$modal.removeClass( "active" );
			$people.sortable( "option", "disabled", false );
			$( "body" ).removeClass( "person-modal-open" );
		} );
	}
}( jQuery, window, document ) );
