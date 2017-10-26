/* global _ */
var wsuwp = wsuwp || {};

( function( $, window, document, wsuwp ) {
	$( document ).ready( function() {
		var $load = $( "#load-ad-data" ),
			$loading = $( "#publishing-action .spinner" ),
			$nid = $( "#_wsuwp_profile_ad_nid" ),
			$hash = $( "#confirm-ad-hash" ),
			$confirm = $( "#confirm-ad-data" ),
			$card = $( ".wsu-person .card" ),
			$photo_collection = $( ".wsu-person-photo-collection" ),
			repeatable_meta_template = _.template( $( ".wsu-person-repeatable-meta-template" ).html() ),
			photo_template = _.template( $( ".wsu-person-photo-template" ).html() ),
			previous_focus,
			media_frame;

		// Insert the repeatable meta area buttons.
		$( ".card header" ).append( "<button type='button' data-type='degree' class='wsu-person-add-repeatable-meta wsu-person-add-degree'>+ Add</button>" );
		$( ".contact .title" ).last().after( "\n<button type='button' data-type='title' class='wsu-person-add-repeatable-meta wsu-person-add-title'>+ Add another title</button>" );
		$.each( $( ".contact .title" ).slice( 1 ), function() {
			$( this ).after( "<button type='button' class='wsu-person-remove dashicons dashicons-no'><span class='screen-reader-text'>Delete</span></button>" );
		} );
		$.each( $( "header .degree" ), function() {
			$( this ).after( "<button type='button' class='wsu-person-remove dashicons dashicons-no'><span class='screen-reader-text'>Delete</span></button>" );
		} );

		// Capture data.
		$load.on( "click", function( e ) {
			e.preventDefault();
			e.target.disabled = true;

			// Don't let the user get too far without entering a NID.
			if ( "" === $nid.val() ) {
				window.alert( "Please enter a Network ID" );
				return;
			}

			// Show that data is loading.
			$loading.css( "visibility", "visible" );

			var data = {
				"action": "wsu_people_get_data_by_nid",
				"_ajax_nonce": window.wsuwp_people_edit_profile.nid_nonce,
				"network_id": $nid.val(),
				"request_from": window.wsuwp_people_edit_profile.request_from
			};

			$.post( window.ajaxurl, data, function( response ) {
				e.target.disabled = false;
				$loading.css( "visibility", "hidden" );

				if ( response.success ) {
					if ( $.isEmptyObject( response.data ) ) {
						window.alert( "Sorry, a profile for " + $nid.val() + " could not be found." );
						return;
					} else if ( response.data.id ) {
						wsuwp.people.populate_person_from_people_directory( response.data );
					} else {
						$( ".wsu-person" ).attr( "data-nid", $nid.val() );

						$( ".wsu-person .name" ).text( response.data.given_name + " " + response.data.surname );
						$( "[name='post_title']" ).val( response.data.given_name + " " + response.data.surname );

						$( ".wsu-person .title" ).text( response.data.title );
						$( "[name='_wsuwp_profile_title[]']" ).val( response.data.title );

						$( ".wsu-person .email" ).text( response.data.email );
						$( "[name='_wsuwp_profile_alt_email']" ).val( response.data.email );

						$( ".wsu-person .phone" ).text( response.data.telephone_number );
						$( "[name='_wsuwp_profile_alt_phone']" ).val( response.data.telephone_number );

						$( ".wsu-person .office" ).text( response.data.office );
						$( "[name='_wsuwp_profile_alt_office']" ).val( response.data.office );

						$( ".wsu-person .address" ).text( response.data.street_address );
						$( "[name='_wsuwp_profile_alt_address']" ).val( response.data.street_address );

						$hash.val( response.data.confirm_ad_hash );
					}

					$( "#wsuwp-university-taxonomies" ).addClass( "show" );
					$( "#wsuwp-profile-listing" ).addClass( "show" );
					$( "#wsuwp-profile-local-display" ).addClass( "show" );
				} else {
					window.alert( response.data );
					return;
				}

				$confirm.removeClass( "profile-hide-button" );
			} );
		} );

		// Confirm/save retrieved data.
		$confirm.on( "click", function( e ) {
			e.preventDefault();
			e.target.disabled = true;
			$loading.css( "visibility", "visible" );
			$( "#load-ad-data" ).addClass( "profile-hide-button" );

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

					$nid.attr( "readonly", true );
					$description.html( "The WSU Network ID used to populate this profile's data from " + $description.data( "location" ) + "." );

					$( ".spinner" ).css( "visibility", "hidden" );

					$confirm.addClass( "profile-hide-button" );
					$publish.removeClass( "profile-hide-button" );
				}
			} );
		} );

		// Copy content from `contenteditable` elements into their respective inputs.
		$card.on( "focusout", "[contenteditable='true']", function() {
			var field = $( this ).attr( "class" ),
				index = $( this ).index( "." + field ),
				value = $( this ).text();

			$( "[data-for='" + field + "']" ).eq( index ).val( value );
		} );

		// Ignore the Enter key in editable divs.
		$card.on( "keypress", "[contenteditable='true']", function( e ) {
			if ( 13 === e.which ) {
				e.preventDefault();
			}
		} );

		// Add a repeatable meta area.
		$card.on( "click", ".wsu-person-add-repeatable-meta", function() {
			$( this ).before( repeatable_meta_template( {
				type: $( this ).data( "type" ),
				value: ""
			} ) );
		} );

		// Surface a repeatable meta area remove button.
		$card.on( "focus", ".title, .degree", function() {
			$( this ).next( "button:not(.wsu-person-add-repeatable-meta)" ).show( 50 );
		} );

		// Hide any visible repeatable area remove buttons.
		$card.on( "focusout", ".title, .degree, .wsu-person-remove", function( e ) {
			var $new_target = $( e.relatedTarget );

			if ( $new_target.hasClass( "wsu-person-remove" ) ) {
				return;
			} else if ( $new_target.hasClass( "title" ) || $new_target.hasClass( "title" ) ) {
				var index = $new_target.next( ".wsu-person-remove" ).index( ".wsu-person-remove" );

				$( ".wsu-person-remove" ).not( ":eq(" + index + ")" ).hide( 50 );
			} else {
				$( ".wsu-person-remove" ).hide( 50 );
			}
		} );

		// Remove a repeatable meta area and its associated input.
		$card.on( "click", ".wsu-person-remove", function() {
			var $span = $( this ).prev( "span" ),
				field = $span.attr( "class" ),
				index = $span.index( "." + field );

			$span.remove();
			$( "[data-for='" + field + "']" ).eq( index ).remove();
			$( this ).remove();
		} );

		// Surface photo collection.
		$card.on( "click", ".photo", function() {
			if ( $( this ).hasClass( "wsu-person-add-photo" ) ) {
				return;
			}

			var position = $( this ).offset();
			previous_focus = document.activeElement;

			$( window ).scrollTop( 0 );
			$( "body" ).addClass( "wsu-person-photo-collection-open" );

			$photo_collection.css( {
				"top": position.top,
				"left": position.left
			} );

			$( ".wsu-person-add-photo" ).focus();
		} );

		// Handle keyboard interactions with the photo collection.
		/**
		 * Based on ideas from https://github.com/gdkraus/accessible-modal-dialog.
		 * The license is as follows:
		 *
		 * This license is governed by United States copyright law, and with respect to matters
		 * of tort, contract, and other causes of action it is governed by North Carolina law,
		 * without regard to North Carolina choice of law provisions.  The forum for any dispute
		 * resolution shall be in Wake County, North Carolina.
		 *
		 * Redistribution and use in source and binary forms, with or without modification, are
		 * permitted provided that the following conditions are met:
		 *
		 * 1. Redistributions of source code must retain the above copyright notice, this list
		 * of conditions and the following disclaimer.
		 *
		 * 2. Redistributions in binary form must reproduce the above copyright notice, this
		 * list of conditions and the following disclaimer in the documentation and/or other
		 * materials provided with the distribution.

		 * 3. The name of the author may not be used to endorse or promote products derived from
		 * this software without specific prior written permission.

		 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR "AS IS" AND ANY EXPRESS OR IMPLIED
		 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
		 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE AUTHOR BE
		 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
		 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
		 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
		 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
		 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
		 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
		 */
		$( document ).keydown( function( e ) {
			if ( !$( "body" ).hasClass( "wsu-person-photo-collection-open" ) ) {
				return;
			}

			// Trap Tab key navigation within the photo collection.
			if ( e.which === 9 ) {
				var focusable_elements = $photo_collection.find( "button" ),
					focused_element_index = focusable_elements.index( $( ":focus" ) );

				if ( e.shiftKey && 0 === focused_element_index ) {
					focusable_elements[ focusable_elements.length - 1 ].focus();
					e.preventDefault();
				} else if ( !event.shiftKey && focused_element_index === focusable_elements.length - 1 ) {
					focusable_elements[ 0 ].focus();
					e.preventDefault();
				}
			}

			// Close the photo collection when the Escape key is pushed.
			if ( e.which === 27 ) {
				wsuwp.close_photo_collection();
			}
		} );

		// Close photo collection.
		$( document ).on( "click", ".wsu-person-photo-collection-close", function( e ) {
			if ( e.target === this ) {
				wsuwp.close_photo_collection();
			}
		} );

		// Add photos to a collection.
		$( document ).on( "click", ".wsu-person-add-photo", function() {
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
						photo_added = false;

					// Only add photos that aren't already in the collection.
					if ( -1 === $.inArray( photo.id, wsuwp.existing_photos() ) ) {
						$( "button.wsu-person-add-photo" ).before( photo_template( {
							src: url,
							id: photo.id
						} ) );

						// If this is the first photo added to the profile, add it to the card.
						if ( !photo_added && $( ".photo" ).hasClass( "wsu-person-add-photo" ) ) {
							$( ".photo" ).removeClass( "wsu-person-add-photo" )
								.prepend( "<img src='" + url + "'>" );
							$( ".photo figcaption" ).text( "Manage photo collection" );
							photo_added = true;
						}
					} else {
						window.alert( photo.url + " is already in your collection." );
					}
				} );
			} );

			media_frame.open();
		} );

		// Remove a photo from the collection.
		$photo_collection.on( "click", ".wsu-person-remove", function() {
			$( this ).parent( ".wsu-person-photo-wrapper" ).remove();
		} );

		// Use jQuery UI Sortable to allow reordering of photos.
		$photo_collection.sortable( {
			cursor: "move",
			placeholder: "wsu-person-photo-wrapper placeholder",
			items: "> .wsu-person-photo-wrapper",
			start: function( e, ui ) {
				ui.helper.height( "" ).width( "" );
			}
		} );

		// Close the photo collection and update the card photo.
		wsuwp.close_photo_collection = function() {
			var $card_photo = $( ".photo img" ),
				$primary_photo = $( ".wsu-person-photo-wrapper:first-of-type img" );

			if ( $primary_photo.length ) {
				$card_photo.attr( "src", $primary_photo.attr( "src" ) );
			} else {
				$card_photo.remove();
				$( ".photo" ).addClass( "wsu-person-add-photo" )
					.find( "figcaption" ).text( "+ Add photo(s)" );
			}

			$( "body" ).removeClass( "wsu-person-photo-collection-open" );
			previous_focus.focus();
		};
	} );

	// Create an array of photo IDs already in the collection.
	wsuwp.existing_photos = function() {
		return $( "[name='_wsuwp_profile_photos[]']" ).map( function() { return parseInt( $( this ).val() ); } ).get();
	};
}( jQuery, window, document, wsuwp ) );
