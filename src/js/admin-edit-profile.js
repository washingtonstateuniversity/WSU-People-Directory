/* global _ */
var wsuwp = wsuwp || {};

( function( $, window, document, wsuwp ) {
	$( document ).ready( function() {
		var $loading = $( "#publishing-action .spinner" ),
			$nid = $( "#_wsuwp_profile_ad_nid" ),
			$hash = $( "#confirm-ad-hash" ),
			$confirm = $( "#confirm-ad-data" ),
			$card = $( ".wsu-person-card" ),
			$add_repeatable_meta = $( ".wsu-person-add-repeatable-meta" ),
			repeatable_meta_template = _.template( $( ".wsu-person-repeatable-meta-template" ).html() );

		// Capture data.
		$( "#load-ad-data" ).on( "click", function( e ) {
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

						$( ".wsu-person-name" ).text( response.data.given_name + " " + response.data.surname )
							.next( "input" ).val( response.data.given_name + " " + response.data.surname );

						$( ".wsu-person-title [contenteditable='true']" ).text( response.data.title )
							.next( "input" ).val( response.data.title );

						$( ".wsu-person-email" ).text( response.data.email )
							.next( "input" ).val( response.data.email );

						$( ".wsu-person-phone" ).text( response.data.telephone_number )
							.next( "input" ).val( response.data.telephone_number );

						$( ".wsu-person-office" ).text( response.data.office )
							.next( "input" ).val( response.data.office );

						$( ".wsu-person-address" ).text( response.data.street_address )
							.next( "input" ).val( response.data.street_address );

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

		// Copy content from the editable div into its respective input.
		$card.on( "focusout", "[contenteditable='true']", function() {
			$( this ).next( "input" ).val( $( this ).text() );
		} );

		// Ignore the Enter key in editable divs.
		$card.on( "keypress", "[contenteditable='true']", function( e ) {
			if ( 13 === e.which ) {
				e.preventDefault();
			}
		} );

		// Add a repeatable meta area.
		$add_repeatable_meta.click( function() {
			$( this ).before( repeatable_meta_template( {
				type: $( this ).data( "type" ),
				value: ""
			} ) );
		} );

		// Remove a repeatable meta area.
		$card.on( "click", ".wsu-person-remove", function() {
			$( this ).closest( "div" ).remove();
		} );
	} );
}( jQuery, window, document, wsuwp ) );
