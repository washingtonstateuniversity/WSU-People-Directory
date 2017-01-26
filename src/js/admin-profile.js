( function( $, window, document ) {
	$( document ).ready( function() {

		// Tabs.
		var tabs = $( "#wsuwp-profile-tabs" ).tabs(),
			tab_counter = $( "#wsuwp-profile-tabs > ul li" ).length;

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

		// "Add Bio" handling.
		$( "#add-bio" ).on( "click", function( e ) {
			e.preventDefault();

			if ( tab_counter < 6 ) {
				var $bio_template = $( "#wsuwp-profile-bio-template" ).clone();
				$( ".wsuwp-profile-bio-tab" ).last().after( "\n<li class='wsuwp-profile-tab wsuwp-profile-bio-tab'><a href='#wsuwp_profile_" + tab_counter + "_bio' class='nav-tab'>New Biography</a></li>" );
				$bio_template.attr( "id", "wsuwp_profile_" + tab_counter + "_bio" );
				$( "#wsuwp-profile-bio-template" ).before( $bio_template );
				tabs.tabs( "refresh" );
				var index = $( "a[href='#wsuwp_profile_" + tab_counter + "_bio']" ).parent( "li" ).index();
				$( "#wsuwp-profile-tabs" ).tabs( "option", "active", index );
				tab_counter++;
				if ( tab_counter === 6 ) {
					$( "#add-bio" ).parent( "li" ).remove();
				}
			}
		} );

		// Biography type selection.
		$( ".wsuwp-profile-bio-type" ).live( "change", function() {
			var new_id = $( this ).val(),
				editor_id = "_" + new_id,
				new_name = $( this ).find( "option:selected" ).text(),
				$panel = $( this ).parents( ".wsuwp-profile-panel" ),
				$tab = $( "a[href='#" + $panel.attr( "id" ) + "']" ),
				$wrapper = $panel.find( ".wsuwp-profile-bio-details-container" ),
				$editor = $panel.find( ".wsuwp-profile-new-bio" ),
				$photo = $panel.find( ".wsuwp-profile-bio-photo" );
			$( this ).attr( "id", new_id + "_type_select" );
			$tab.attr( "href", "#" + new_id ).html( new_name + " Biography" );
			$panel.attr( "id", new_id );
			tabs.tabs( "refresh" );
			$editor.attr( "id", editor_id ).attr( "name", editor_id );
			window.tinyMCE.execCommand( "mceAddEditor", false, editor_id );
			$photo.attr( "name", new_id + "_photo" );
			$wrapper.show( "slow" );
			$( this ).parent( "p" ).remove();
			$( ".wsuwp-profile-bio-type option[value=" + new_id + "]" ).remove();
		} );
	} );
}( jQuery, window, document ) );
