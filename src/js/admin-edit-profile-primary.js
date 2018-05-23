( function( $, window, document ) {
	var get_selected = function( selector ) {
		return $( selector + "  option:selected" ).map( function() {
			return this.text;
		} ).get();
	};

	var terms_match = function( original, current ) {
		return JSON.stringify( original.sort() ) === JSON.stringify( current.sort() );
	};

	$( document ).ready( function() {
		var original = {
			"classification": get_selected( "#classification-select" ),
			"wsuwp_university_org": get_selected( "#wsuwp_university_org-select" ),
			"wsuwp_university_location": get_selected( "#wsuwp_university_location-select" ),
			"wsuwp_university_category": get_selected( "#wsuwp_university_category-select" )
		};

		// Post data to secondary instances of the profile.
		$( "#publish" ).on( "click", function() {
			var instances = $( "#wsuwp-profile-listing a" );

			// Bail if there are no other instances of this profile to update.
			if ( instances.length === 0 ) {
				return;
			}

			var data = {},
				classifications = get_selected( "#classification-select" ),
				organizations = get_selected( "#wsuwp_university_org-select" ),
				locations = get_selected( "#wsuwp_university_location-select" ),
				u_categories = get_selected( "#wsuwp_university_category-select" );

			if ( !terms_match( original.classification, classifications ) ) {
				data.classification = ( 0 === classifications.length ) ? [ "wsuwp_people_empty_terms" ] : classifications;
			}

			if ( !terms_match( original.wsuwp_university_org, organizations ) ) {
				data.wsuwp_university_org = ( 0 === organizations.length ) ? [ "wsuwp_people_empty_terms" ] : organizations;
			}

			if ( !terms_match( original.wsuwp_university_location, locations ) ) {
				data.wsuwp_university_location = ( 0 === locations.length ) ? [ "wsuwp_people_empty_terms" ] : locations;
			}

			if ( !terms_match( original.wsuwp_university_category, u_categories ) ) {
				data.wsuwp_university_category = ( 0 === u_categories.length ) ? [ "wsuwp_people_empty_terms" ] : u_categories;
			}

			// Bail if there are no term changes.
			if ( $.isEmptyObject( data ) ) {
				return;
			}

			var nid = $( "#_wsuwp_profile_ad_nid" ).val();

			instances.each( function() {
				var instance_url = $( this ).attr( "href" );

				$.ajax( {
					url: instance_url + "/wp-json/wsuwp-people/v1/sync/" + nid,
					method: "POST",
					beforeSend: function( xhr ) {
						xhr.setRequestHeader( "X-WP-Nonce", window.wsupeoplesync.nonce );
						xhr.setRequestHeader( "X-WSUWP-UID", window.wsupeoplesync.uid );
					},
					data: {
						"taxonomy_terms": data
					}
				} );
			} );
		} );
	} );
}( jQuery, window, document ) );
