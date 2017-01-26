( function( $, window, document ) {
	$( document ).ready( function() {

		// Tabs.
		$( "#wsuwp-profile-tabs" ).tabs( {
			active: 0
		} );

		// Repeatable fields handling.
		$( ".wsuwp-profile-add-repeatable" ).on( "click", "a", function( e ) {

			e.preventDefault();

			var click_parent = $( this ).parent(),
					added = click_parent.siblings( ".wp-profile-repeatable" ).first().clone(),
					attrs = "name,id,for";

			added.find( "input" ).val( "" );
			click_parent.before( added );
			attrs = attrs.split( "," );

			$( this ).parent().siblings( ".wp-profile-repeatable" ).each( function( index ) {
				$( this ).find( "input, label" ).each( function() {
					for ( var i = 0; i < attrs.length; i++ ) {
						if ( undefined !== $( this ).attr( attrs[ i ] ) ) {
							$( this ).attr( attrs[ i ], $( this ).attr( attrs[ i ] ).replace( "0", index ) );
						}
					}
				} );
			} );
		} );

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
				"_ajax_nonce": window.wsupeople_nid_nonce,
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
	} );
}( jQuery, window, document ) );
