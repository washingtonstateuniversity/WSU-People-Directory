/* global _ */
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
	 * Contains a list of a person's taxonomy terms.
	 *
	 * @type {{taxonomy: [array]}}
	 */
	wsuwp.people.taxonomy_terms = {
		"classification": [],
		"wsuwp_university_category": [],
		"wsuwp_university_location": [],
		"wsuwp_university_org": []
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
			title = ( data.working_titles.length ) ? data.working_titles : [ data.position_title ],
			email = ( data.email_alt ) ? data.email_alt : data.email,
			phone = ( data.phone_alt ) ? data.phone_alt : data.phone,
			office = ( data.office_alt ) ? data.office_alt : data.office,
			address = ( data.address_alt ) ? data.address_alt : data.address,
			repeatable_meta_template = _.template( $( ".wsu-person-repeatable-meta-template" ).html() ),
			$add_title_button = $( ".wsu-person-add-title" ),
			$add_degree_button = $( ".wsu-person-add-degree" );

		// Populate the NID.
		$( ".wsu-person" ).attr( "data-nid", data.nid );

		// Populate the primary record id.
		$( "#_wsuwp_profile_post_id" ).val( data.id );

		// Populate the primary record URL.
		$( "#_wsuwp_profile_canonical_source" ).val( data.link );

		// Populate card data.
		$( ".wsu-person .name" ).text( data.title.rendered ).data( "original", data.title.rendered );
		$( "[data-for='name']" ).val( data.title.rendered );

		$( ".wsu-person .email" ).text( email ).data( "original", email );
		$( "[data-for='email']" ).val( email );

		$( ".wsu-person .phone" ).text( phone ).data( "original", phone );
		$( "[data-for='phone']" ).val( phone );

		$( ".wsu-person .office" ).text( office ).data( "original", office );
		$( "[data-for='office']" ).val( office );

		$( ".wsu-person .address" ).text( address ).data( "original", address );
		$( "[data-for='address']" ).val( address );

		$( ".wsu-person .website" ).text( data.website ).data( "original", data.website );
		$( "[data-for='website']" ).val( data.website );

		// Populate biographies.
		wsuwp.people.bio_content.content = data.content.rendered;
		wsuwp.people.bio_content._wsuwp_profile_bio_unit = data.bio_unit;
		wsuwp.people.bio_content._wsuwp_profile_bio_university = data.bio_university;

		// Populate biography textareas when Text mode is the default editor state.
		if ( !window.tinymce.get( "content" ) ) {
			$( "#content" ).val( data.content.rendered );
			$( "#_wsuwp_profile_bio_unit" ).val( data.bio_unit );
			$( "#_wsuwp_profile_bio_university" ).val( data.bio_university );
		}

		// Populate the read-only biographies.
		$( "#bio_unit .readonly" ).html( data.bio_unit );
		$( "#bio_university .readonly" ).html( data.bio_university );

		wsuwp.people.rest_response_complete = true;

		// Populate title(s).
		$.each( title, function( i, value ) {
			var $field = $( ".contact .title" )[ i ],
				$display_select = $( "#local-display-title" );

			if ( $field ) {
				$( $field ).text( value );
				$( "[data-for='title']" ).val( value );
			} else {
				$add_title_button.before( repeatable_meta_template( {
					type: $add_title_button.data( "type" ),
					value: value
				} ) );
			}

			// Fill in the display options.
			$display_select.append( "<option value='" + i + "'>" + value + "</option>" );

			if ( data.working_titles.length - 1 === i ) {
				var selected = $display_select.data( "selected" ).toString();

				// Set the size of the select box so that all titles are visible without scrolling.
				$display_select.attr( "size", i + 1 );

				// Set selected titles.
				if ( 1 === selected.length ) {
					$display_select.find( "option[value='" + selected + "']" ).prop( "selected", "selected" );
				} else if ( 2 < selected.length ) {
					$.each( selected.split( "," ), function( index, value ) {
						$display_select.find( "option[value='" + value + "']" ).prop( "selected", "selected" );
					} );
				}
			}
		} );

		wsuwp.people.titles = data.working_titles;

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

		// Populate photo collection.
		if ( data._embedded && data._embedded[ "wp:photos" ] !== 0 ) {
			$.each( data._embedded[ "wp:photos" ], function( i, photo ) {
				if ( "id" in photo ) {
					wsuwp.people.populate_photos( photo, i );
				}
			} );
		}

		// Populate featured image as part of the photo collection.
		if ( data._embedded && data._embedded[ "wp:featuredmedia" ] && data._embedded[ "wp:featuredmedia" ] !== 0 ) {
			wsuwp.people.populate_photos( data._embedded[ "wp:featuredmedia" ][ 0 ] );
		}

		// Populate taxonomy data.
		if ( data.taxonomy_terms ) {
			$.each( data.taxonomy_terms, function( taxonomy, terms ) {

				// Categories and tags are skipped for now to avoid term contamination.
				if ( "category" === taxonomy || "post_tag" === taxonomy ) {
					return;
				}

				if ( terms ) {
					var original_terms = [];

					$.each( terms, function( i, term ) {
						$( "#" + taxonomy + "-select option" ).filter( function() { return $( this ).text() === term.name; } ).attr( "selected", "selected" );
						$( "#" + taxonomy + "-select" ).trigger( "change" );

						original_terms.push( term.name );
					} );

					wsuwp.people.taxonomy_terms[ taxonomy ] = original_terms;
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
	wsuwp.people.populate_photos = function( data, index ) {
		var $ = jQuery,
			photo_template = _.template( $( ".wsu-person-photo-template" ).html() ),
			photo = data.media_details,
			url = photo.sizes ? photo.sizes.thumbnail.source_url : data.source_url,
			$display_photo = $( "#local-display-photo" ),
			checked = ( index === $display_photo.data( "selected" ) ) ? " checked='checked'" : "";

		// Avoid inserting duplicate images.
		if ( -1 === $.inArray( data.id, wsuwp.existing_photos() ) ) {
			$( "button.wsu-person-add-photo" ).before( photo_template( {
				src: url,
				id: data.id
			} ) );

			if ( 0 === $( ".wsu-person .photo img" ).length ) {
				$( ".photo" ).removeClass( "wsu-person-add-photo" )
					.prepend( "<img src='" + url + "'>" );
				$( ".photo figcaption" ).text( "Manage photo collection" );
			}

			// Set the display options.
			$display_photo.append( "<label><input type='radio' name='_use_photo' value='" + index + "'" + checked + "/><img src='" + url + "' /></label>" );
		}
	};

	$( document ).ready( function() {
		var $nid = $( "#_wsuwp_profile_ad_nid" ),
			post_id = $( "#_wsuwp_profile_post_id" ).val();

		// Make a REST request for profile data from people.wsu.edu.
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
				university_bio = window.tinymce.get( "_wsuwp_profile_bio_university" ),
				classifications = $( "#classification-select option:selected" ).map( function() { return this.text; } ).get(),
				organizations = $( "#wsuwp_university_org-select option:selected" ).map( function() { return this.text; } ).get(),
				locations = $( "#wsuwp_university_location-select option:selected" ).map( function() { return this.text; } ).get(),
				u_categories = $( "#wsuwp_university_category-select option:selected" ).map( function() { return this.text; } ).get();

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

			data.taxonomy_terms = {};

			if ( !wsuwp.people.terms_match( wsuwp.people.taxonomy_terms.classification, classifications ) ) {
				data.taxonomy_terms.classification = ( 0 === classifications.length ) ? [ "wsuwp_people_empty_terms" ] : classifications;
			}

			if ( !wsuwp.people.terms_match( wsuwp.people.taxonomy_terms.wsuwp_university_org, organizations ) ) {
				data.taxonomy_terms.wsuwp_university_org = ( 0 === organizations.length ) ? [ "wsuwp_people_empty_terms" ] : organizations;
			}

			if ( !wsuwp.people.terms_match( wsuwp.people.taxonomy_terms.wsuwp_university_location, locations ) ) {
				data.taxonomy_terms.wsuwp_university_location = ( 0 === locations.length ) ? [ "wsuwp_people_empty_terms" ] : locations;
			}

			if ( !wsuwp.people.terms_match( wsuwp.people.taxonomy_terms.wsuwp_university_category, u_categories ) ) {
				data.taxonomy_terms.wsuwp_university_category = ( 0 === u_categories.length ) ? [ "wsuwp_people_empty_terms" ] : u_categories;
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

		wsuwp.people.terms_match = function( original, current ) {
			return JSON.stringify( original.sort() ) === JSON.stringify( current.sort() );
		};

	} );
}( jQuery, window, document, wsuwp ) );
