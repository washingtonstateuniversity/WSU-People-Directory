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
	};

	$( document ).ready( function() {
		var $nid = $( "#_wsuwp_profile_ad_nid" );

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
					populate_from_people_directory( response[ 0 ] );
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

		// Select a working title for display on the front end.
		$( ".wsuwp-profile-titles" ).on( "click", ".select", function() {
			var $title = $( this ).closest( "p" );

			$title.toggleClass( "selected" );

			var selected_titles = $( ".wsuwp-profile-titles .selected" ).map( function() { return $( this ).index(); } ).get();

			$( ".use-title" ).val( selected_titles.join( " " ) );

			if ( $title.hasClass( "selected" ) ) {
				$title.find( ".screen-reader-text" ).text( "Deselect" );
			} else {
				$title.find( ".screen-reader-text" ).text( "Select" );
			}
		} );

		// Select a photo for display on the front end.
		$( ".wsuwp-profile-photo-collection" ).on( "click", ".wsuwp-profile-photo-select", function() {
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

		// Post data to the user's people.wsu.edu profile.
		$( "#publish" ).on( "click", function() {
			var data = {},
				name = $( "#title" ),
				office = $( "#_wsuwp_profile_alt_office" ),
				phone = $( "#_wsuwp_profile_alt_phone" ),
				email = $( "#_wsuwp_profile_alt_email" ),
				website = $( "#_wsuwp_profile_website" ),
				titles = $( "[name='_wsuwp_profile_title[]']" ),
				degrees = $( "[name='_wsuwp_profile_degree[]']" ),
				personal_bio = window.tinymce.get( "content" ),
				unit_bio = window.tinymce.get( "_wsuwp_profile_bio_unit" ),
				university_bio = window.tinymce.get( "_wsuwp_profile_bio_university" );

			// Only add changed values to the data array.
			if ( name.val() !== name.data( "original" ) ) {
				data.title = name.val();
			}

			if ( office.val() !== office.data( "original" ) ) {
				data.office_alt = office.val();
			}

			if ( phone.val() !== phone.data( "original" ) ) {
				data.phone_alt = phone.val();
			}

			if ( email.val() !== email.data( "original" ) ) {
				data.email_alt = email.val();
			}

			if ( website.val() !== website.data( "original" ) ) {
				data.website = website.val();
			}

			if ( titles ) {
				var new_titles = titles.map( function() { return $( this ).val(); } ).get();
				if ( new_titles.join( "," ) !== wsuwp.people.titles.join( "," ) ) {
					data.working_titles = new_titles;
				}
			}

			if ( degrees ) {
				var new_degrees = degrees.map( function() { return $( this ).val(); } ).get();
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
					url: window.wsuwp_people_edit_profile_secondary.rest_url + "/" + $nid.data( "post-id" ),
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

// Populate profile with data from people.wsu.edu.
function populate_from_people_directory( data ) {
	var $ = jQuery,
		repeatable_field_template = _.template( $( ".wsuwp-profile-repeatable-field-template" ).html() ),
		$working_titles = $( ".wsuwp-profile-titles .wsuwp-profile-add-repeatable" ),
		working_title_label = $working_titles.find( "a" ).data( "label" ),
		working_title_name = $working_titles.find( "a" ).data( "name" ),
		$degrees = $( ".wsuwp-profile-degrees .wsuwp-profile-add-repeatable" ),
		degree_label = $degrees.find( "a" ).data( "label" ),
		degree_name = $degrees.find( "a" ).data( "name" );

	// Populate the post id field.
	$( "#_wsuwp_profile_post_id" ).val( data.id );

	// Populate the profile title.
	$( "#title" ).focus().val( data.title.rendered ).data( "original", data.title.rendered );

	// Populate AD data.
	$( "#_wsuwp_profile_ad_name_first" ).html( data.first_name );
	$( "#_wsuwp_profile_ad_name_last" ).html( data.last_name );
	$( "#_wsuwp_profile_ad_title" ).html( data.position_title );
	$( "#_wsuwp_profile_ad_office" ).html( data.office );
	$( "#_wsuwp_profile_ad_address" ).html( data.address );
	$( "#_wsuwp_profile_ad_phone" ).html( data.phone );
	$( "#_wsuwp_profile_ad_email" ).html( data.email );

	// Populate additional/alternative fields.
	$( "#_wsuwp_profile_alt_office" ).val( data.office_alt ).data( "original", data.office_alt );
	$( "#_wsuwp_profile_alt_phone" ).val( data.phone_alt ).data( "original", data.phone_alt );
	$( "#_wsuwp_profile_alt_email" ).val( data.email_alt ).data( "original", data.email_alt );
	$( "#_wsuwp_profile_website" ).val( data.website ).data( "original", data.website );

	// Populate biographies.
	wsuwp.people.bio_content.content = data.content.rendered;
	wsuwp.people.bio_content._wsuwp_profile_bio_unit = data.bio_unit;
	wsuwp.people.bio_content._wsuwp_profile_bio_university = data.bio_university;

	var readonly_bio_unit = $( "#bio_unit .readonly" );
	var readonly_bio_university = $( "#bio_university .readonly" );

	if ( 0 !== readonly_bio_unit.length ) {
		readonly_bio_unit.html( wsuwp.people.bio_content._wsuwp_profile_bio_unit );
	}

	if ( 0 !== readonly_bio_university ) {
		readonly_bio_university.html( wsuwp.people.bio_content._wsuwp_profile_bio_university );
	}

	wsuwp.people.rest_response_complete = true;

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

	wsuwp.people.titles = data.working_titles;

	// Add the `selected` class to working titles accordingly.
	if ( "" !== $( ".use-title" ).val() ) {
		var titles = $( ".use-title" ).val().split( " " );

		$.each( titles, function( i, value ) {
			$( ".wsuwp-profile-titles p" )
			.eq( value )
			.addClass( "selected" )
			.find( ".screen-reader-text" )
			.text( "Deselect" );
		} );
	}

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

	wsuwp.people.degrees = data.degree;

	// Populate photo collection.
	if ( data._embedded && data._embedded[ "wp:photos" ] !== 0 ) {
		$.each( data._embedded[ "wp:photos" ], function( i, photo ) {
			populate_photos( photo );
		} );
	}

	// Populate featured image as part of the photo collection.
	if ( data._embedded && data._embedded[ "wp:featuredmedia" ] && data._embedded[ "wp:featuredmedia" ] !== 0 ) {
		populate_photos( data._embedded[ "wp:featuredmedia" ][ 0 ] );
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
	}

	// Populate taxonomy data.
	if ( data._embedded && data._embedded[ "wp:term" ] && data._embedded[ "wp:term" ] !== 0 ) {
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
		$( "#title" ).focus();
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
			$( "#wsuwp_profile_additional_info input" ).prop( "disabled", true );
			$( ".wsuwp-profile-button.remove" ).prop( "disabled", true );
			$( "#new-tag-post_tag" ).prop( "disabled", true );
			$( ".tabs-panel input" ).prop( "disabled", true );
			$( ".wsuwp-profile-add-photo" ).prop( "disabled", true );
			$( ".wsuwp-profile-photo-remove" ).prop( "disabled", true );

			window.tinymce.get( "content" ).setMode( "readonly" );

			if ( 0 === readonly_bio_unit.length ) {
				window.tinymce.get( "_wsuwp_profile_bio_unit" ).setMode( "readonly" );
			}

			if ( 0 === readonly_bio_university.length ) {
				window.tinymce.get( "_wsuwp_profile_bio_university" ).setMode( "readonly" );
			}
		}
	} );
}

// Populate the photo collection with data from people.wsu.edu.
function populate_photos( data ) {
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
}
