/* global _ */
( function( $, window, document ) {
	$( document ).ready( function() {

		// Tabs.
		$( "#wsuwp-profile-tabs" ).tabs( {
			active: 0
		} );

		var repeatable_field_template = _.template( $( ".wsuwp-profile-repeatable-field-template" ).html() );

		// Add a repeatable field.
		$( ".wsuwp-profile-add-repeatable" ).on( "click", "a", function( e ) {
			e.preventDefault();

			$( this ).closest( "p" ).before( repeatable_field_template( {
				label: $( this ).data( "label" ),
				name: $( this ).data( "name" )
			} ) );
		} );

		// Remove a repeatable field.
		$( ".wsuwp-profile-repeatable-field" ).on( "click", ".wsuwp-profile-remove-repeatable-field", function( e ) {
			e.preventDefault();

			$( this ).closest( "p" ).remove();
		} );

		// AD data capturing.
		$( "#load-ad-data" ).on( "click", function() {
			var $given_name = $( "#_wsuwp_profile_ad_name_first" ),
				$surname = $( "#_wsuwp_profile_ad_name_last" ),
				$title = $( "#_wsuwp_profile_ad_title" ),
				$office = $( "#_wsuwp_profile_ad_office" ),
				$address = $( "#_wsuwp_profile_ad_address" ),
				$phone = $( "#_wsuwp_profile_ad_phone" ),
				$email = $( "#_wsuwp_profile_ad_email" ),
				$hash = $( "#confirm-ad-hash" ),
				$confirm = $( "#confirm-ad-data" );

			var data = {
				"action": "wsu_people_get_data_by_nid",
				"_ajax_nonce": window.wsupeople.nid_nonce,
				"network_id": $( "#_wsuwp_profile_ad_nid" ).val()
			};

			$.post( window.ajaxurl, data, function( response ) {
				if ( response.success ) {
					$given_name.html( response.data.given_name );
					$surname.html( response.data.surname );
					$title.html( response.data.title );
					$office.html( response.data.office );
					$address.html( response.data.street_address );
					$phone.html( response.data.telephone_number );
					$email.html( response.data.email );
					$hash.val( response.data.confirm_ad_hash );

					$confirm.removeClass( "profile-hide-button" );
				}
			} );
		} );

		$( "#confirm-ad-data" ).on( "click", function() {
			var data = {
				"action": "wsu_people_confirm_nid_data",
				"_ajax_nonce": window.wsupeople_nid_nonce,
				"network_id": $( "#_wsuwp_profile_ad_nid" ).val(),
				"confirm_ad_hash": $( "#confirm-ad-hash" ).val(),
				"post_id": $( "#post_ID" ).val()
			};

			var $title = $( "#title" );

			$.post( window.ajaxurl, data, function( response ) {
				if ( response.success ) {

					// If a title has not yet been entered, use the given and surname from AD.
					if ( "" === $title.val() ) {
						$title.focus();
						$title.val( $( "#_wsuwp_profile_ad_name_first" ).html() + " " + $( "#_wsuwp_profile_ad_name_last" ).html() );
					}

					$( "#_wsuwp_profile_ad_nid" ).attr( "readonly", true );
					$( ".load-ad-container .description" ).html( "The WSU Network ID used to populate this profile's data from Active Directory." );
					$( "#load-ad-data" ).addClass( "profile-hide-button" );
					$( "#confirm-ad-data" ).addClass( "profile-hide-button" );
					$( "#publish" ).removeClass( "profile-hide-button" );
				}
			} );

		} );

		// Photo collection handling.
		var media_frame,
			details_frame,
			$add_photo = $( ".wsuwp-profile-add-photo" ),
			$collection = $( ".wsuwp-profile-photo-collection" ),
			$count = $( ".wsuwp-profile-photo-count" ),
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
				var photos = media_frame.state().get( "selection" ),
					template = _.template( $( "#photo-template" ).html() ),
					existing_photos = [];

				// Create an array of photo IDs already in the collection.
				$( ".wsuwp-profile-photo-id" ).each( function() {
					existing_photos.push( parseInt( $( this ).val() ) );
				} );

				$.each( photos.models, function( i, attachment ) {

					var index = Number( $count.val() ) + i,
						photo = attachment.toJSON(),
						has_thumbnail = photo.sizes.hasOwnProperty( "thumbnail" ),
						url = has_thumbnail ? photo.sizes.thumbnail.url : photo.url,
						width = has_thumbnail ? photo.sizes.thumbnail.width : photo.width,
						height = has_thumbnail ? photo.sizes.thumbnail.height : photo.height;

					// An image doesn't need to be added more than once.
					if ( -1 === $.inArray( photo.id, existing_photos ) ) {
						$collection.append( template( {
							src: url,
							width: width,
							height: height,
							count: index,
							id: photo.id,
							alt: photo.alt,
							url: photo.url,
							title: photo.title,
							full_width: photo.width,
							full_height: photo.height
						} ) );
					}
				} );

				$count.val( $collection.find( ".wsuwp-profile-photo-wrapper" ).length );
			} );

			media_frame.open();
		} );

		// Surface control buttons when an image is clicked.
		$( "html" ).click( function( e ) {
			if ( $( e.target ).hasClass( "wsuwp-profile-photo" ) ) {
				$( e.target ).next( ".wsuwp-profile-photo-controls" ).show();
				$( e.target ).closest( ".wsuwp-profile-photo-wrapper" ).siblings( ".wsuwp-profile-photo-wrapper" ).find( ".wsuwp-profile-photo-controls" ).hide();
			} else {
				$( ".wsuwp-profile-photo-controls" ).hide();
			}
		} );

		// Show control buttons tooltip.
		$collection.on( "mouseover", ".wsuwp-profile-photo-controls button", function() {
			var text = this.getAttribute( "aria-label" ),
				button = this.getBoundingClientRect(),
				collection = $collection[ 0 ].getBoundingClientRect();

			$tooltip.find( ".wsuwp-profile-photo-controls-tooltip-inner" ).html( text );

			$tooltip.css( {
				top: button.bottom - collection.top + "px",
				left: button.right - collection.left - $tooltip.width() / 2 - 6 + "px"
			} ).show();
		} );

		// Hide control buttons tooltip.
		$collection.on( "mouseleave", ".wsuwp-profile-photo-controls button", function() {
			$tooltip.hide();
		} );

		// Edit a photo.
		$collection.on( "click", ".wsuwp-profile-photo-edit", function( e ) {
			e.preventDefault();
			$tooltip.hide();

			var img = $( this ).closest( ".wsuwp-profile-photo-wrapper" ).find( "img" ),
				metadata = {
				attachment_id: img.data( "id" ),
				size: "full",
				caption: "",
				align: "none",
				extraClasses: "",
				link: false,
				title: img.attr( "title" ),
				url: img.data( "url" ),
				alt: img.attr( "alt" ),
				width: img.data( "width" ),
				height: img.data( "height" )
			};

			if ( details_frame ) {
				details_frame.open();
				return;
			}

			window.wp.media.events.trigger( "editor:image-edit", {
				metadata: metadata,
				image: img
			} );

			details_frame = window.wp.media( {
				frame: "image",
				state: "image-details",
				metadata: metadata
			} );

			details_frame.on( "close", function() {
				var values = details_frame.state().image.attributes;

				img.attr( "title", values.title );
				img.attr( "alt", values.alt );
				img.data( "width", values.width );
				img.data( "height", values.height );

				// @todo update image post meta to reflect changes
			} );

			details_frame.open();
		} );

		// Delete a photo.
		$collection.on( "click", ".wsuwp-profile-photo-remove", function( e ) {
			e.preventDefault();
			$tooltip.hide();

			$( this ).closest( ".wsuwp-profile-photo-wrapper" ).remove();

			var $remaining_photos = $( ".wsuwp-profile-photo-id" );

			// Reset indexes of the remaining photos in the collection.
			for ( var i = 0; i < $remaining_photos.length; i++ ) {
				$( $remaining_photos[ i ] ).attr( "name", "photos[" + i + "]" );
			}

			// Reset count value.
			$count.val( $remaining_photos.length );
		} );
	} );
}( jQuery, window, document ) );
