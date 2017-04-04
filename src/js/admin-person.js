/* global _ */
( function( $, window, document ) {
	$( document ).ready( function() {

		var repeatable_field_template = _.template( $( ".wsuwp-profile-repeatable-field-template" ).html() ),
			photo_template = _.template( $( "#photo-template" ).html() ),
			$publishing_spinner = $( "#publishing-action .spinner" ),
			$refresh_spinner = $( ".refresh-card .spinner" ),
			$post_title = $( "#title" ),
			$nid = $( "#_wsuwp_profile_ad_nid" ),
			$all_card_data = $( ".profile-card-data" ),
			$given_name = $( "#_wsuwp_profile_ad_name_first" ),
			$surname = $( "#_wsuwp_profile_ad_name_last" ),
			$title = $( "#_wsuwp_profile_ad_title" ),
			$office = $( "#_wsuwp_profile_ad_office" ),
			$address = $( "#_wsuwp_profile_ad_address" ),
			$phone = $( "#_wsuwp_profile_ad_phone" ),
			$email = $( "#_wsuwp_profile_ad_email" ),
			$hash = $( "#confirm-ad-hash" ),
			$collection = $( ".wsuwp-profile-photo-collection" ),
			$load = $( "#load-ad-data" ),
			$confirm = $( "#confirm-ad-data" ),
			$refresh = $( "#refresh-ad-data" ),
			$undo = $( "#undo-ad-data-refresh" );

		// Create an array of photo IDs already in the collection.
		function existing_photos() {
			var existing_photos = $( ".wsuwp-profile-photo-id" ).map( function() {
				return parseInt( $( this ).val() );
			} ).get();

			return existing_photos;
		}

		// Populate profile with data from people.wsu.edu.
		function populate_from_people_directory( data ) {
			var $working_titles = $( ".wsuwp-profile-titles .wsuwp-profile-add-repeatable" ),
				working_title_label = $working_titles.find( "a" ).data( "label" ),
				working_title_name = $working_titles.find( "a" ).data( "name" ),
				$degrees = $( ".wsuwp-profile-degrees .wsuwp-profile-add-repeatable" ),
				degree_label = $degrees.find( "a" ).data( "label" ),
				degree_name = $degrees.find( "a" ).data( "name" );

			// Populate AD data.
			$post_title.focus().val( data.title.rendered );
			$given_name.html( data.first_name );
			$surname.html( data.last_name );
			$title.html( data.position_title );
			$office.html( data.office );
			$address.html( data.address );
			$phone.html( data.phone );
			$email.html( data.email );

			// Populate additional/alternative fields.
			$( "#_wsuwp_profile_alt_office" ).val( data.office_alt );
			$( "#_wsuwp_profile_alt_phone" ).val( data.phone_alt );
			$( "#_wsuwp_profile_alt_email" ).val( data.email_alt );
			$( "#_wsuwp_profile_website" ).val( data.website );

			// Populate biographies.
			window.tinymce.get( "content" ).setContent( data.content.rendered );
			window.tinymce.get( "_wsuwp_profile_bio_unit" ).setContent( data.bio_unit );
			window.tinymce.get( "_wsuwp_profile_bio_university" ).setContent( data.bio_university );

			// Populate working title(s).
			$.each( data.working_titles, function( i, value ) {
				var field = $( "[name='_wsuwp_profile_title[]']" )[ i ];

				if ( field ) {
					$( field ).val( value );
				} else {
					$working_titles.before( repeatable_field_template( {
						label: working_title_label,
						name: working_title_name,
						value: value
					} ) );
				}
			} );

			// Populate degree(s).
			$.each( data.degree, function( i, value ) {
				var field = $( "[name='_wsuwp_profile_degree[]']" )[ i ];

				if ( field ) {
					$( field ).val( value );
				} else {
					$degrees.before( repeatable_field_template( {
						label: degree_label,
						name: degree_name,
						value: value
					} ) );
				}
			} );

			// Populate photo collection.
			if ( data._embedded[ "wp:photos" ] !== 0 ) {
				$.each( data._embedded[ "wp:photos" ], function( i, photo ) {
					populate_photos( photo );
				} );
			}

			// Populate featured image as part of the photo collection.
			if ( data._embedded[ "wp:featuredmedia" ] && data._embedded[ "wp:featuredmedia" ] !== 0 ) {
				populate_photos( data._embedded[ "wp:featuredmedia" ][ 0 ] );
			}

			// Add the `selected` class to a photo accordingly.
			if ( "" !== $( ".use-photo" ).val() ) {
				$( ".wsuwp-profile-photo-wrapper" ).eq( $( ".use-photo" ).val() ).addClass( "selected" );
			} else {
				$( ".wsuwp-profile-photo-wrapper" ).eq( 0 ).addClass( "selected" );
			}

			// Populate taxonomy data.
			if ( data._embedded[ "wp:term" ] && data._embedded[ "wp:term" ] !== 0 ) {
				$.each( data._embedded[ "wp:term" ], function( i, taxonomy ) {
					if ( taxonomy ) {
						$.each( taxonomy, function( i, term ) {
							if ( "post_tag" === term.taxonomy ) {
								$( "#new-tag-post_tag" ).val( function( index, val ) {
									return val + term.name + ", ";
								} );
							} else {
								$( "#" + term.taxonomy + "-all" )
								.find( ".selectit:contains('" + term.name + "')" )
								.find( "input[type='checkbox']" ).prop( "checked", true );
							}
						} );
					}
				} );

				// Add the tags.
				$( ".tagadd" ).trigger( "click" );

				// Change focus to the post title field (the trigger above focuses the tag input).
				$post_title.focus();
			}
		}

		// Populate the photo collection with data from people.wsu.edu.
		function populate_photos( data ) {
			var photo = data.media_details,
				has_thumbnail = photo.sizes.thumbnail,
				url = has_thumbnail ? photo.sizes.thumbnail.source_url : data.source_url,
				width = has_thumbnail ? photo.sizes.thumbnail.width : photo.width,
				height = has_thumbnail ? photo.sizes.thumbnail.height : photo.height;

			// Avoid inserting duplicate images.
			if ( -1 === $.inArray( data.id, existing_photos() ) ) {
				$collection.append( photo_template( {
					src: url,
					width: width,
					height: height,
					id: data.id,
					alt: data.alt,
					url: data.source_url,
					title: data.title.rendered,
					full_width: photo.width,
					full_height: photo.height
				} ) );
			}
		}

		// Initialize tabs.
		$( "#wsuwp-profile-tabs" ).tabs( {
			active: 0
		} );

		// Make a REST request to populate a person with data from people.wsu.edu
		if ( window.wsupeople.make_request ) {
			jQuery.ajax( {
				url: window.wsupeople.rest_url,
				data: {
					_embed: true,
					wsu_nid: $nid.val()
				}
			} ).done( function( response ) {
				if ( response.length !== 0 ) {
					populate_from_people_directory( response[ 0 ] );
				}
			} );
		}

		// Add a repeatable field.
		$( ".wsuwp-profile-add-repeatable" ).on( "click", "a", function( e ) {
			e.preventDefault();

			$( this ).closest( "p" ).before( repeatable_field_template( {
				label: $( this ).data( "label" ),
				name: $( this ).data( "name" ),
				value: ""
			} ) );
		} );

		// Remove a repeatable field.
		$( ".wsuwp-profile-repeatable-field" ).on( "click", ".wsuwp-profile-remove-repeatable-field", function( e ) {
			e.preventDefault();

			$( this ).closest( "p" ).remove();
		} );

		// Capture data.
		$( "#load-ad-data, #refresh-ad-data" ).on( "click", function( e ) {
			e.preventDefault();
			e.target.disabled = true;

			// Don't let the user get too far without entering a NID.
			if ( "" === $nid.val() ) {
				window.alert( "Please enter a Network ID" );
				return;
			}

			// Provide an indication that data is being loaded.
			// If this is a refresh, store current information in case the user wants to undo.
			if ( $( e.target ).is( "#refresh-ad-data" ) ) {
				$refresh_spinner.css( "visibility", "visible" );
				$all_card_data.each( function() {
					$( this ).data( "original", $( this ).html() );
				} );
			} else {
				$publishing_spinner.css( "visibility", "visible" );
			}

			var data = {
				"action": "wsu_people_get_data_by_nid",
				"_ajax_nonce": window.wsupeople.nid_nonce,
				"network_id": $nid.val(),
				"request_from": window.wsupeople.request_from,
				"is_refresh": ( $( e.target ).is( "#refresh-ad-data" ) ) ? "true" : "false"
			};

			$.post( window.ajaxurl, data, function( response ) {
				$( ".spinner" ).css( "visibility", "hidden" );
				e.target.disabled = false;

				if ( response.success ) {

					// If the response has an id property, it's almost certainly from people.wsu.edu.
					if ( response.data.id ) {
						populate_from_people_directory( response.data );
					} else {
						$given_name.html( response.data.given_name );
						$surname.html( response.data.surname );
						$title.html( response.data.title );
						$office.html( response.data.office );
						$address.html( response.data.street_address );
						$phone.html( response.data.telephone_number );
						$email.html( response.data.email );
						$hash.val( response.data.confirm_ad_hash );
					}
				} else {
					window.alert( response.data );
					return;
				}

				$confirm.removeClass( "profile-hide-button" );
				$undo.removeClass( "profile-hide-button" );
				$refresh.addClass( "profile-hide-button" );
			} );
		} );

		// Confirm/save retrieved data.
		$confirm.on( "click", function( e ) {
			e.preventDefault();
			e.target.disabled = true;

			// Provide an indication that data is being loaded.
			if ( $( e.target ).hasClass( "refresh" ) ) {
				$refresh_spinner.css( "visibility", "visible" );
				$undo.addClass( "profile-hide-button" );
			} else {
				$publishing_spinner.css( "visibility", "visible" );
				$load.addClass( "profile-hide-button" );
			}

			var data = {
				"action": "wsu_people_confirm_nid_data",
				"_ajax_nonce": window.wsupeople.nid_nonce,
				"network_id": $nid.val(),
				"confirm_ad_hash": $hash.val(),
				"post_id": $( "#post_ID" ).val(),
				"request_from": window.wsupeople.request_from
			};

			var $description = $( ".load-ad-container .description" ),
				$publish = $( "#publish" );

			$.post( window.ajaxurl, data, function( response ) {
				if ( response.success ) {

					// If a title has not yet been entered, use the given and surname from AD.
					if ( "" === $post_title.val() ) {
						$post_title.focus();
						$post_title.val( $( "#_wsuwp_profile_ad_name_first" ).html() + " " + $( "#_wsuwp_profile_ad_name_last" ).html() );
					}

					$nid.attr( "readonly", true );
					$description.html( "The WSU Network ID used to populate this profile's data from Active Directory." );

					$( ".spinner" ).css( "visibility", "hidden" );

					$confirm.addClass( "profile-hide-button" );
					$publish.removeClass( "profile-hide-button" );
				}
			} );
		} );

		// Undo a refresh.
		$undo.on( "click", function( e ) {
			e.preventDefault();

			$all_card_data.each( function() {
				$( this ).html( $( this ).data( "original" ) );
			} );

			$confirm.addClass( "profile-hide-button" );
			$undo.addClass( "profile-hide-button" );
			$refresh.removeClass( "profile-hide-button" ).disabled = false;
		} );

		// Photo collection handling.
		var media_frame,
			$add_photo = $( ".wsuwp-profile-add-photo" ),
			$tooltip = $( ".wsuwp-profile-photo-controls-tooltip" );

		// Handle photo adding.
		$add_photo.on( "click", function( e ) {
			e.preventDefault();

			if ( media_frame ) {
				media_frame.open();
				return;
			}

			media_frame = window.wp.media( {
				title: "Select or Upload Your Photos",
				multiple: true,
				library: {
					type: "image",
					uploadedTo: window.wsupeople.post_id
				},
				button: {
					text: "Use photo(s)"
				}
			} );

			media_frame.on( "select", function() {
				var photos = media_frame.state().get( "selection" );

				$.each( photos.models, function( i, attachment ) {

					var photo = attachment.toJSON(),
						has_thumbnail = photo.sizes.hasOwnProperty( "thumbnail" ),
						url = has_thumbnail ? photo.sizes.thumbnail.url : photo.url,
						width = has_thumbnail ? photo.sizes.thumbnail.width : photo.width,
						height = has_thumbnail ? photo.sizes.thumbnail.height : photo.height;

					// An image doesn't need to be added more than once.
					if ( -1 === $.inArray( photo.id, existing_photos() ) ) {
						$collection.append( photo_template( {
							src: url,
							width: width,
							height: height,
							id: photo.id,
							alt: photo.alt,
							url: photo.url,
							title: photo.title,
							full_width: photo.width,
							full_height: photo.height
						} ) );
					}
				} );
			} );

			media_frame.open();
		} );

		// Show control buttons tooltip.
		$collection.on( "mouseover", ".wsuwp-profile-photo-controls button", function() {
			var text = this.getAttribute( "aria-label" ),
				button = this.getBoundingClientRect(),
				collection = $collection[ 0 ].getBoundingClientRect();

			$tooltip.css( {
				top: button.bottom - collection.top + "px",
				left: button.right - collection.left - $tooltip.width() / 2 - 6 + "px"
			} ).show().find( ".wsuwp-profile-photo-controls-tooltip-inner" ).html( text );
		} );

		// Hide control buttons tooltip.
		$collection.on( "mouseleave", ".wsuwp-profile-photo-controls button", function() {
			$tooltip.hide();
		} );

		// Delete a photo.
		$collection.on( "click", ".wsuwp-profile-photo-remove", function() {
			$tooltip.hide();

			$( this ).closest( ".wsuwp-profile-photo-wrapper" ).remove();
		} );

		// Select a photo for display on the front-end (non-people.wsu.edu sites only).
		$collection.on( "click", ".wsuwp-profile-photo-select", function() {
			var photo = $( this ).closest( ".wsuwp-profile-photo-wrapper" ),
				input = $( ".use-photo" );

			photo.toggleClass( "selected" ).siblings().removeClass( "selected" );

			if ( photo.hasClass( "selected" ) ) {
				input.val( $( ".wsuwp-profile-photo-wrapper" ).index( photo ) );
			} else {
				input.val( "" );
			}
		} );
	} );
}( jQuery, window, document ) );
