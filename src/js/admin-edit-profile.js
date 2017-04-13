/* global _, populate_from_people_directory */
( function( $, window, document ) {
	$( document ).ready( function() {
		var repeatable_field_template = _.template( $( ".wsuwp-profile-repeatable-field-template" ).html() ),
			photo_template = _.template( $( "#photo-template" ).html() ),
			media_frame,
			$tooltip = $( ".wsuwp-profile-photo-controls-tooltip" ),
			$publishing_spinner = $( "#publishing-action .spinner" ),
			$refresh_spinner = $( ".refresh-card .spinner" ),
			$post_title = $( "#title" ),
			$nid = $( "#_wsuwp_profile_ad_nid" ),
			$all_card_data = $( ".profile-card-data" ),
			$hash = $( "#confirm-ad-hash" ),
			$collection = $( ".wsuwp-profile-photo-collection" ),
			$confirm = $( "#confirm-ad-data" ),
			$refresh = $( "#refresh-ad-data" ),
			$undo = $( "#undo-ad-data-refresh" );

		// Initialize tabs.
		$( "#wsuwp-profile-about-wrapper" ).tabs( {
			active: 0
		} );

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
		$( ".wsuwp-profile-repeatable-field" ).on( "click", ".remove", function() {
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
				"_ajax_nonce": window.wsuwp_people_edit_profile.nid_nonce,
				"network_id": $nid.val(),
				"request_from": window.wsuwp_people_edit_profile.request_from,
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
						$( "#_wsuwp_profile_ad_name_first" ).html( response.data.given_name );
						$( "#_wsuwp_profile_ad_name_last" ).html( response.data.surname );
						$( "#_wsuwp_profile_ad_title" ).html( response.data.title );
						$( "#_wsuwp_profile_ad_office" ).html( response.data.office );
						$( "#_wsuwp_profile_ad_address" ).html( response.data.street_address );
						$( "#_wsuwp_profile_ad_phone" ).html( response.data.telephone_number );
						$( "#_wsuwp_profile_ad_email" ).html( response.data.email );
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
				$( "#load-ad-data" ).addClass( "profile-hide-button" );
			}

			var data = {
				"action": "wsu_people_confirm_nid_data",
				"_ajax_nonce": window.wsuwp_people_edit_profile.nid_nonce,
				"network_id": $nid.val(),
				"confirm_ad_hash": $hash.val(),
				"post_id": $( "#post_ID" ).val(),
				"request_from": window.wsuwp_people_edit_profile.request_from
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

		// Handle photo adding.
		$( ".wsuwp-profile-add-photo" ).on( "click", function( e ) {
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
					uploadedTo: window.wsuwp_people_edit_profile.post_id
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
			var $button = $( this ),
				$photo = $button.closest( ".wsuwp-profile-photo-wrapper" ),
				$input = $( ".use-photo" );

			$photo.toggleClass( "selected" ).siblings().removeClass( "selected" );

			if ( $photo.hasClass( "selected" ) ) {
				$input.val( $( ".wsuwp-profile-photo-wrapper" ).index( $photo ) );
				$button.attr( "aria-label", "Deselect" );
				$photo.siblings().removeClass( "selected" ).find( ".wsuwp-profile-photo-select" ).attr( "aria-label", "Select" );
			} else {
				$input.val( "" );
				$button.attr( "aria-label", "Select" );
			}
		} );
	} );

}( jQuery, window, document ) );

// Create an array of photo IDs already in the collection.
function existing_photos() {
	var $ = jQuery,
		photos = $( ".wsuwp-profile-photo-id" ).map( function() { return parseInt( $( this ).val() ); } ).get();

	return photos;
}
