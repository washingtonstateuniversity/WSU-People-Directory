/* global _, existing_photos */
var wsuwp = wsuwp || {};
wsuwp.people = wsuwp.people || {};

( function( $, window, document, wsuwp ) {
	/**
	 * Tracks whether a REST request for profile data has
	 * completed.
	 *
	 * @type {boolean}
	 */
	wsuwp.people.rest_response_complete = false;

	/**
	 * Contains a list of a person's working titles.
	 *
	 * @type {string}
	 */
	wsuwp.people.titles = "";

	/**
	 * Contains a list of a person's degrees.
	 *
	 * @type {string}
	 */
	wsuwp.people.degrees = "";

	/**
	 * Contains multiple biographies for a person.
	 *
	 * @type {{content: string, _wsuwp_profile_bio_unit: string, _wsuwp_profile_bio_university: string}}
	 */
	wsuwp.people.bio_content = {
		"content": "",
		"_wsuwp_profile_bio_unit": "",
		"_wsuwp_profile_bio_university": ""
	};

	/**
	 * Populate biography editors with existing data from the REST API
	 * after that data has been received.
	 *
	 * This is registered as a callback when TinyMCE inits the editor.
	 *
	 * @param editor
	 */
	wsuwp.people.populate_editor = function( editor ) {
		if ( false === wsuwp.people.rest_response_complete ) {
			setTimeout( wsuwp.people.populate_editor.bind( null, editor ), 200 );
			return;
		}

		window.tinymce.get( editor.id ).setContent( wsuwp.people.bio_content[ editor.id ] );

		if ( 0 !== $( "#bio_unit .readonly" ).length ) {
			window.tinymce.get( "_wsuwp_profile_bio_unit" ).setMode( "readonly" );
		}

		if ( 0 !== $( "#bio_university .readonly" ).length ) {
			window.tinymce.get( "_wsuwp_profile_bio_university" ).setMode( "readonly" );
		}
	};

	/**
	 * Populates a profile with data retrieved from the main site via the REST API.
	 *
	 * @param data
	 */
	wsuwp.people.populate_person_from_people_directory = function( data ) {
		var $ = jQuery,
			repeatable_meta_template = _.template( $( ".wsu-person-repeatable-meta-template" ).html() ),
			$add_title_button = $( ".wsu-person-add-title" ),
			$add_degree_button = $( ".wsu-person-add-degree" );

		// Populate the NID.
		$( ".wsu-person" ).attr( "data-nid", data.nid );

		// Populate the primary record id.
		$( "#_wsuwp_profile_post_id" ).val( data.id );

		// Populate card data.
		$( ".wsu-person .name" ).text( data.title.rendered ).data( "original", data.title.rendered );
		$( "[data-for='name']" ).val( data.title.rendered );

		$( ".wsu-person .email" ).text( data.email_alt ).data( "original", data.email_alt );
		$( "[data-for='email']" ).val( data.email_alt );

		$( ".wsu-person .phone" ).text( data.phone_alt ).data( "original", data.phone_alt );
		$( "[data-for='phone']" ).val( data.phone_alt );

		$( ".wsu-person .office" ).text( data.office_alt ).data( "original", data.office_alt );
		$( "[data-for='office']" ).val( data.office_alt );

		$( ".wsu-person .address" ).text( data.address_alt ).data( "original", data.address_alt );
		$( "[data-for='address']" ).val( data.address_alt );

		$( ".wsu-person .website" ).text( data.website ).data( "original", data.website );
		$( "[data-for='website']" ).val( data.website );

		// Populate biographies.
		wsuwp.people.bio_content.content = data.content.rendered;
		wsuwp.people.bio_content._wsuwp_profile_bio_unit = data.bio_unit;
		wsuwp.people.bio_content._wsuwp_profile_bio_university = data.bio_university;

		// Populate the read-only biographies.
		$( "#bio_unit .readonly" ).html( data.bio_unit );
		$( "#bio_university .readonly" ).html( data.bio_university );

		wsuwp.people.rest_response_complete = true;

		// Populate title(s).
		$.each( data.working_titles, function( i, value ) {
			var $field = $( ".contact .title" )[ i ];

			if ( $field ) {
				$( $field ).text( value );
				$( "[data-for='title']" ).val( value );
			} else {
				$add_title_button.before( repeatable_meta_template( {
					type: $add_title_button.data( "type" ),
					value: value
				} ) );
			}
		} );

		wsuwp.people.titles = data.working_titles;

		// Add the `selected` class to titles accordingly.
		if ( "" !== $( ".use-title" ).val() ) {
			var titles = $( ".use-title" ).val().split( " " );

			$.each( titles, function( i, value ) {
				$( ".contact .title" )
					.eq( value )
					.addClass( "selected" )
					.find( ".screen-reader-text" )
					.text( "Deselect" );
			} );
		}

		// Populate degree(s).
		$.each( data.degree, function( i, value ) {
			var $field = $( ".contact .degree" )[ i ];

			if ( $field ) {
				$( $field ).text( value ).next( "input" ).val( value );
			} else {
				$add_degree_button.before( repeatable_meta_template( {
					type: $add_degree_button.data( "type" ),
					value: value
				} ) );
			}
		} );

		wsuwp.people.degrees = data.degree;

		/* Populate photo collection.
		if ( data._embedded && data._embedded[ "wp:photos" ] !== 0 ) {
			$.each( data._embedded[ "wp:photos" ], function( i, photo ) {
				wsuwp.people.populate_photos( photo );
			} );
		}

		// Populate featured image as part of the photo collection.
		if ( data._embedded && data._embedded[ "wp:featuredmedia" ] && data._embedded[ "wp:featuredmedia" ] !== 0 ) {
			wsuwp.people.populate_photos( data._embedded[ "wp:featuredmedia" ][ 0 ] );
		}

		// Add the `selected` class to a photo accordingly.
		if ( "" !== $( ".use-photo" ).val() ) {
			$( ".wsuwp-profile-photo-wrapper" )
				.eq( $( ".use-photo" ).val() )
				.addClass( "selected" )
				.find( ".wsuwp-profile-photo-select" )
				.attr( "aria-label", "Deselect" );
		} else {
			$( ".wsuwp-profile-photo-wrapper" )
				.eq( 0 )
				.addClass( "selected" )
				.find( ".wsuwp-profile-photo-select" )
				.attr( "aria-label", "Deselect" );
		}*/

		// Populate taxonomy data.
		if ( data._embedded && data._embedded[ "wp:term" ] && data._embedded[ "wp:term" ] !== 0 ) {
			$.each( data._embedded[ "wp:term" ], function( i, taxonomy ) {
				if ( taxonomy ) {
					$.each( taxonomy, function( i, term ) {
						if ( "post_tag" === term.taxonomy ) {
							$( "#post_tag" ).val( term.name ).trigger( "change" );
						} else {
							$( "#" + term.taxonomy ).find( "option:contains(" + term.name + ")" ).attr( "selected", "selected" );
							$( "#" + term.taxonomy ).trigger( "change" );
						}
					} );
				}
			} );
		}

		// Disable inputs if a user doesn't have adequate permissions to edit the profile.
		$.ajax( {
			url: window.wsuwp_people_edit_profile_secondary.rest_url + "/" + data.id,
			type: "POST",
			beforeSend: function( xhr ) {
				xhr.setRequestHeader( "X-WP-Nonce", window.wsuwp_people_edit_profile_secondary.nonce );
				xhr.setRequestHeader( "X-WSUWP-UID", window.wsuwp_people_edit_profile_secondary.uid );
			},
			error: function() {
				$( ".wsu-person-card [contenteditable='true']" ).prop( "contenteditable", false );
				$( ".wsu-person-remove" ).remove();
				$( ".wsu-person-add-repeatable-meta" ).remove();
				$( "#new-tag-post_tag" ).prop( "disabled", true );

				window.tinymce.get( "content" ).setMode( "readonly" );
			}
		} );
	};

	/**
	 * Populate the photo collection with data from the main site.
	 *
	 * @param data
	 */
	wsuwp.people.populate_photos = function( data ) {
		var $ = jQuery,
			photo_template = _.template( $( "#photo-template" ).html() ),
			photo = data.media_details,
			has_thumbnail = photo.sizes.thumbnail,
			url = has_thumbnail ? photo.sizes.thumbnail.source_url : data.source_url,
			width = has_thumbnail ? photo.sizes.thumbnail.width : photo.width,
			height = has_thumbnail ? photo.sizes.thumbnail.height : photo.height;

		// Avoid inserting duplicate images.
		if ( -1 === $.inArray( data.id, existing_photos() ) ) {
			$( ".wsuwp-profile-photo-collection" ).append( photo_template( {
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
	};

	$( document ).ready( function() {
		var $nid = $( "#_wsuwp_profile_ad_nid" ),
			post_id = $( "#_wsuwp_profile_post_id" ).val();

		// Make a REST request to for profile data from people.wsu.edu.
		if ( window.wsuwp_people_edit_profile_secondary.load_data ) {
			$.ajax( {
				url: window.wsuwp_people_edit_profile_secondary.rest_url,
				data: {
					_embed: true,
					wsu_nid: $nid.val()
				}
			} ).done( function( response ) {
				if ( response.length !== 0 ) {
					wsuwp.people.populate_person_from_people_directory( response[ 0 ] );
				}
			} );
		}

		// Select a bio for display on the front end.
		$( ".wsuwp-profile-about-tabs" ).on( "click", ".select", function() {
			var $tab = $( this ).closest( "li" ),
				$input = $( ".use-bio" );

			$tab.toggleClass( "selected" );

			if ( $tab.hasClass( "selected" ) ) {
				$input.val( $tab.data( "bio" ) );
				$tab.find( ".screen-reader-text" ).text( "Deselect" );
				$tab.siblings().removeClass( "selected" ).find( ".screen-reader-text" ).text( "Select" );
			} else {
				$input.val( "" );
				$tab.find( ".screen-reader-text" ).text( "Select" );
			}
		} );

		// Select a title for display on the front end.
		$( ".wsu-person-title" ).on( "click", ".wsu-person-select", function() {
			var $title = $( this ).closest( ".wsu-person-repeatable-meta-entry" );

			$title.toggleClass( "selected" );

			var selected_titles = $( ".wsu-person-title .selected" ).map( function() { return $( this ).index(); } ).get();

			$( ".use-title" ).val( selected_titles.join( " " ) );

			if ( $title.hasClass( "selected" ) ) {
				$title.find( ".screen-reader-text" ).text( "Deselect" );
			} else {
				$title.find( ".screen-reader-text" ).text( "Select" );
			}
		} );

		// Select a photo for display on the front end.
		/*$( ".wsuwp-profile-photo-collection" ).on( "click", ".wsuwp-profile-photo-select", function() {
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
		} );*/

		// Post data to the user's people.wsu.edu profile.
		$( "#publish" ).on( "click", function() {
			var data = {},
				name = $( ".wsu-person .name" ),
				email = $( ".wsu-person .email" ),
				phone = $( ".wsu-person .phone" ),
				office = $( ".wsu-person .office" ),
				address = $( ".wsu-person .address" ),
				website = $( ".wsu-person .website" ),
				titles = $( ".wsu-person .title" ),
				degrees = $( ".wsu-person .degree" ),
				personal_bio = window.tinymce.get( "content" ),
				unit_bio = window.tinymce.get( "_wsuwp_profile_bio_unit" ),
				university_bio = window.tinymce.get( "_wsuwp_profile_bio_university" );

			// Only add changed values to the data array.
			if ( name.text() !== name.data( "original" ) ) {
				data.title = name.text();
			}

			if ( email.text() !== email.data( "original" ) ) {
				data.email_alt = email.text();
			}

			if ( phone.text() !== phone.data( "original" ) ) {
				data.phone_alt = phone.text();
			}

			if ( office.text() !== office.data( "original" ) ) {
				data.office_alt = office.text();
			}

			if ( address.text() !== address.data( "original" ) ) {
				data.address_alt = address.text();
			}

			if ( website.text() !== website.data( "original" ) ) {
				data.website = website.text();
			}

			if ( titles ) {
				var new_titles = titles.map( function() { return $( this ).text(); } ).get();
				if ( new_titles.join( "," ) !== wsuwp.people.titles.join( "," ) ) {
					data.working_titles = new_titles;
				}
			}

			if ( degrees ) {
				var new_degrees = degrees.map( function() { return $( this ).text(); } ).get();
				if ( new_degrees.join( "," ) !== wsuwp.people.degrees.join( "," ) ) {
					data.degree = new_degrees;
				}
			}

			if ( personal_bio && ( personal_bio.getContent() !== wsuwp.people.bio_content.content ) ) {
				data.content = personal_bio.getContent();
			}

			if ( unit_bio && ( unit_bio.getContent() !== wsuwp.people.bio_content._wsuwp_profile_bio_unit ) ) {
				data.bio_unit = unit_bio.getContent();
			}

			if ( university_bio && ( university_bio.getContent() !== wsuwp.people.bio_content._wsuwp_profile_bio_university ) ) {
				data.bio_university = university_bio.getContent();
			}

			// Only push data if values have changed.
			if ( !$.isEmptyObject( data ) ) {
				$.ajax( {
					url: window.wsuwp_people_edit_profile_secondary.rest_url + "/" + post_id,
					method: "POST",
					beforeSend: function( xhr ) {
						xhr.setRequestHeader( "X-WP-Nonce", window.wsuwp_people_edit_profile_secondary.nonce );
						xhr.setRequestHeader( "X-WSUWP-UID", window.wsuwp_people_edit_profile_secondary.uid );
					},
					data: data
				} );
			}
		} );

	} );
}( jQuery, window, document, wsuwp ) );
