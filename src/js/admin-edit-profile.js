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
			repeatable_meta_template = _.template( $( ".wsu-person-repeatable-meta-template" ).html() );

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
				"request_from": window.wsuwp_people_edit_profile.request_from,
				"is_refresh": ( $( e.target ).is( "#refresh-ad-data" ) ) ? "true" : "false"
			};

			$.post( window.ajaxurl, data, function( response ) {
				e.target.disabled = false;
				$loading.css( "visibility", "hidden" );

				if ( response.success ) {

					// If the response has an id property, it's almost certainly from people.wsu.edu.
					if ( response.data.id ) {
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
				$( ".wsu-person-remove:not(:focus)" ).hide( 50 );
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
	} );

	// Initialize Select2.
	$( ".taxonomy-select2" ).select2( {
		placeholder: "+ Add",
		closeOnSelect: false,
		templateResult: function( data, container ) {
			if ( data.element ) {
				$( container ).addClass( $( data.element ).attr( "class" ) );
			}
			return data.text;
		}
	} );
}( jQuery, window, document, wsuwp ) );
