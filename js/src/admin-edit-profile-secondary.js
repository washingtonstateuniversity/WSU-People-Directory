var wsuwp = wsuwp || {};
wsuwp.people = wsuwp.people || {};

/**
 * Tracks whether a REST request for profile data has completed.
 *
 * @type {boolean}
 */
wsuwp.people.rest_response_complete = false;

/**
 * Tracks whether a user has permissions to edit the current profile.
 *
 * @type {boolean}
 */
wsuwp.people.user_can_edit_profile = "";

/**
 * Contains a list of a person's working titles.
 *
 * @type {string}
 */
wsuwp.people.working_titles = "";

/**
 * Contains a list of a person's degrees.
 *
 * @type {string}
 */
wsuwp.people.degree = "";

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
 * The NID associated with the current profile.
 *
 * @type {string}
 */
wsuwp.people.nid = document.getElementById( "_wsuwp_profile_ad_nid" );

/**
 * Populate the profile with data from the primary directory.
 *
 * @param {object} data Profile data from the primary directory.
 */
wsuwp.people.populate_profile_from_primary_directory = function( data ) {

	// Populate the NID associated with the profile.
	document.querySelector( ".wsu-person" ).dataset.nid = data.nid;

	// Populate the primary profile id.
	document.getElementById( "_wsuwp_profile_post_id" ).value = data.id;

	// Populate the primary profile URL.
	document.getElementById( "_wsuwp_profile_canonical_source" ).value = data.link;

	// Populate card data.
	new Map( [
		[ "name", data.title.rendered ],
		[ "email", ( data.email_alt ) ? data.email_alt : data.email ],
		[ "phone", ( data.phone_alt ) ? data.phone_alt : data.phone ],
		[ "office", ( data.office_alt ) ? data.office_alt : data.office ],
		[ "address", ( data.address_alt ) ? data.address_alt : data.address ],
		[ "website", data.website ]
	] )
	.forEach( function( value, input ) {
		document.querySelector( ".wsu-person ." + input ).textContent = value;
		document.querySelector( ".wsu-person ." + input ).dataset.original = value;
		document.querySelector( "[data-for='" + input + "']" ).value = value;
	} );

	wsuwp.people.populate_profile_titles( ( data.working_titles ) ? data.working_titles : [ data.position_title ] );

	wsuwp.people.populate_profile_degrees( data.degree );

	wsuwp.people.populate_profile_photos( data.photos );

	wsuwp.people.populate_profile_biographies( data.content.rendered, data.bio_unit, data.bio_university );

	wsuwp.people.check_permissions( data.id );
};

/**
 * Populate the profile title(s).
 *
 * @param {array} titles Profile titles.
 */
wsuwp.people.populate_profile_titles = function( titles ) {
	wsuwp.people.working_titles = titles.join( "," );

	let add_title_button = document.querySelector( ".wsu-person-add-title" );
	let display_select = document.getElementById( "local-display-title" );

	titles.forEach( function( value, index ) {
		let input = document.querySelectorAll( ".contact .title" )[ index ];

		if ( input ) {
			input.textContent = value;
			document.querySelector( "[data-for='title']" ).value = value;
		} else {

			// `wsuwp.people.repeatable_input` is defined in admin-edit-profile.js.
			add_title_button.insertAdjacentHTML( "beforebegin", wsuwp.people.repeatable_input( "title", value ) );
		}

		// Fill in the display options.
		display_select.insertAdjacentHTML( "beforeend", "<option value='" + index + "'>" + value + "</option>" );

		if ( titles.length - 1 === index ) {
			let selected = display_select.dataset.selected.toString();

			// Set the size of the select box so that all titles are visible without scrolling.
			display_select.size = index + 1;

			// Set selected titles.
			if ( 1 === selected.length ) {
				display_select.querySelector( "option[value='" + selected + "']" ).selected = true;
			} else if ( 2 < selected.length ) {
				selected.split( "," ).forEach( function( option_value ) {
					display_select.querySelector( "option[value='" + option_value + "']" ).selected = true;
				} );
			}
		}
	} );
};

/**
 * Populate the profile degrees.
 *
 * @param {array} degrees Degrees retrieved from the primary profile.
 */
wsuwp.people.populate_profile_degrees = function( degrees ) {
	wsuwp.people.degree = degrees.join( "," );

	let add_degree_button = document.querySelector( ".wsu-person-add-degree" );

	degrees.forEach( function( value, index ) {
		let input = document.querySelectorAll( ".contact .degree" )[ index ];

		if ( input ) {
			input.textContent = value;
			input.nextSibling.value = value;
		} else {

			// `wsuwp.people.repeatable_input` is defined in admin-edit-profile.js.
			add_degree_button.insertAdjacentHTML( "beforebegin", wsuwp.people.repeatable_input( "degree", value ) );
		}
	} );
};

/**
 * Populate the profile photo collection.
 *
 * @param {array} photos Photos retrieved from the primary profile.
 */
wsuwp.people.populate_profile_photos = function( photos ) {
	if ( photos ) {
		let card_photo = document.querySelector( ".wsu-person .photo" );
		let add_photo_button = document.querySelector( "button.wsu-person-add-photo" );
		let display_select = document.getElementById( "local-display-photo" );

		photos.forEach( function( value, index ) {
			let url = value.thumbnail;
			let checked = ( index === display_select.dataset.selected ) ? " checked='checked'" : "";

			// Add each photo to the collection.
			// `wsuwp.people.photo_input` is defined in admin-edit-profile.js.
			add_photo_button.insertAdjacentHTML( "beforebegin", wsuwp.people.photo_input( url, "" ) );

			// Add each photo as an option for local display.
			display_select.insertAdjacentHTML( "beforeend", "<label><input type='radio' name='_use_photo' value='" + index + "'" + checked + "/><img src='" + url + "' /></label>" );

			// Set a card photo.
			if ( card_photo.classList.contains( "wsu-person-add-photo" ) ) {
				card_photo.classList.remove( "wsu-person-add-photo" );
				card_photo.insertAdjacentHTML( "afterbegin", "<img src='" + url + "'>" );
				document.querySelector( ".photo figcaption" ).textContent = "Manage photo collection";
			}
		} );
	}
};

/**
 * Populate the TinyMCE text mode and/or read only profile biographies.
 *
 * @param {string} personal   The personal biography retrieved from the primary profile.
 * @param {string} unit       The unit biography retrieved from the primary profile.
 * @param {string} university The university biography retrieved from the primary profile.
 */
wsuwp.people.populate_profile_biographies = function( personal, unit, university ) {
	if (
		false === wsuwp.people.rest_response_complete ||
		"" === wsuwp.people.user_can_edit_profile
	) {
		setTimeout( wsuwp.people.populate_profile_biographies.bind( null, personal, unit, university ), 200 );
		return;
	}

	new Map( [
		[ "content", personal ],
		[ "_wsuwp_profile_bio_unit", unit ],
		[ "_wsuwp_profile_bio_university", university ]
	] )
	.forEach( function( biography, editor ) {
		wsuwp.people.bio_content[ editor ] = biography; // Store initial value for later comparison.

		// Disable Text mode editors if the user doesn't have sufficient permissions.
		if ( !wsuwp.people.user_can_edit_profile && document.getElementById( editor ) ) {
			document.getElementById( editor ).disabled = true;
			document.querySelectorAll( ".quicktags-toolbar [type='button']" ).forEach( function( quicktag_button ) {
				quicktag_button.disabled = true;
			} );
		}

		if ( "read-only" === wsuwp.people.check_editor_mode( editor ) ) {
			document.querySelector( ".wsu-person-bio." + editor + " .readonly" ).insertAdjacentHTML( "beforeend", unit ); // Security
		} else {
			document.getElementById( editor ).value = biography;
		}
	} );
};

/**
 * Populate biography editors once data from the REST API has been received.
 *
 * This is registered as a callback when TinyMCE inits the editor.
 *
 * @param editor
 */
wsuwp.people.populate_editor = function( editor ) {
	if (
		false === wsuwp.people.rest_response_complete ||
		"" === wsuwp.people.user_can_edit_profile
	) {
		setTimeout( wsuwp.people.populate_editor.bind( null, editor ), 200 );
		return;
	}

	if ( "visual" !== wsuwp.people.check_editor_mode( editor.id ) ) {
		return;
	}

	window.tinymce.get( editor.id ).setContent( wsuwp.people.bio_content[ editor.id ] );

	if ( !wsuwp.people.user_can_edit_profile ) {
		window.tinymce.get( editor.id ).setMode( "readonly" );
	}
};

/**
 * Make a POST request to the primary profile to determine if
 * the current user has sufficient permissions for editing it.
 *
 * The "rest_response_complete" flag is also set here so it can
 * be appropriately leveraged when populating the WP Editors.
 *
 * @param {string} id Post ID of the primary profile.
 */
wsuwp.people.check_permissions = function( id ) {
	let permissions_url = window.wsuwp_people_edit_profile_secondary.rest_url + "/" + id;
	let permissions_options = {
		method: "POST",
		credentials: "same-origin",
		headers: {
			"X-WP-Nonce": window.wsuwp_people_edit_profile_secondary.nonce,
			"X-WSUWP-UID": window.wsuwp_people_edit_profile_secondary.uid
		}
	};

	fetch( permissions_url, permissions_options )
	.then( wsuwp.people.set_permissions )
	.then( data => window.console.log( data ) )
	.catch( error => window.console.log( error ) );

	wsuwp.people.rest_response_complete = true;
};

/**
 * Handle editability and set the permissions flag based on
 * the user's ability to edit the profile's primary record.
 *
 * @param {object} response The response from the primary profile request.
 */
wsuwp.people.set_permissions = function( response ) {
	return response.json()
	.then( json => {
		if ( response.ok ) {
			wsuwp.people.user_can_edit_profile = true;

			return json;
		} else {
			wsuwp.people.user_can_edit_profile = false;

			document.querySelectorAll( ".wsu-person [contenteditable='true']" ).forEach( function( input ) {
				input.contentEditable = false;
			} );

			document.querySelectorAll( ".wsu-person-remove" ).forEach( function( input ) {
				input.remove();
			} );

			document.querySelectorAll( ".wsu-person-add-repeatable-meta" ).forEach( function( input ) {
				input.remove();
			} );

			// Should move all the bio editor permissions stuff down here.
			if ( window.tinymce.get( "content" ) ) {
				window.tinymce.get( "content" ).setMode( "readonly" );
			}

			return Promise.reject( Object.assign( {}, json, {
				status: response.status,
				statusText: response.statusText
			} ) );
		}
	} );
};

/**
 * Update the primary profile with any changed meta data.
 */
wsuwp.people.update_primary_profile = function() {
	let data = {};

	// Collect all card data that differs from the original.
	new Map( [
		[ "title", document.querySelector( ".wsu-person .name" ) ],
		[ "email_alt", document.querySelector( ".wsu-person .email" ) ],
		[ "phone_alt", document.querySelector( ".wsu-person .phone" ) ],
		[ "office_alt", document.querySelector( ".wsu-person .office" ) ],
		[ "address_alt", document.querySelector( ".wsu-person .address" ) ],
		[ "website", document.querySelector( ".wsu-person .website" ) ]
	] )
	.forEach( function( input, rest_key ) {
		if ( input.textContent !== input.dataset.original ) {
			data[ rest_key ] = input.textContent;
		}
	} );

	// Collect all repeatable input data that differs from the original.
	new Map( [
		[ "working_titles", document.querySelectorAll( ".wsu-person .title" ) ],
		[ "degree", document.querySelectorAll( ".wsu-person .degree" ) ]
	] )
	.forEach( function( inputs, rest_key ) {
		if ( inputs ) {
			let current_values = Array.prototype.slice.call( inputs ).map( input => input.textContent ).join( "," );

			if ( current_values !== wsuwp.people[ rest_key ] ) {
				data[ rest_key ] = current_values;
			}
		}
	} );

	// Collect any biography data that differs from the original.
	new Map( [
		[ "content", "content" ],
		[ "bio_unit", "_wsuwp_profile_bio_unit" ],
		[ "bio_university", "_wsuwp_profile_bio_university" ]
	] )
	.forEach( function( editor, rest_key ) {
		if ( "read-only" !== wsuwp.people.check_editor_mode( editor ) ) {
			let biography = document.getElementById( editor ).value;

			// This comparison isn't great, as switching the editor mode can easily throw it off.
			if ( biography !== wsuwp.people.bio_content[ editor ] ) {
				data[ rest_key ] = biography;
			}
		}
	} );

	// Bail if no data has been changed.
	// `wsuwp.people.is_empty_object` is defined in admin-edit-profile.js.
	if ( wsuwp.people.is_empty_object( data ) ) {
		return;
	}

	let primary_profile_post_id = document.getElementById( "_wsuwp_profile_post_id" ).value;
	let primary_profile_rest_url = window.wsuwp_people_edit_profile_secondary.rest_url + "/" + primary_profile_post_id;
	let primary_profile_post_options = {
		method: "POST",
		credentials: "same-origin",
		body: data,
		headers: {
			"Content-Type": "application/x-www-form-urlencoded; charset=utf-8",
			"X-WP-Nonce": window.wsuwp_people_edit_profile_secondary.nonce,
			"X-WSUWP-UID": window.wsuwp_people_edit_profile_secondary.uid
		}
	};

	fetch( primary_profile_rest_url, primary_profile_post_options );
};

/**
 * Determines if a given editor is in Visual or Text mode.
 *
 * If neither, the content is most likely being displayed in a div.
 *
 * @param {string} editor The ID for the given editor.
 */
wsuwp.people.check_editor_mode = function( editor ) {
	let editor_wrapper = document.getElementById( "wp-" + editor + "-wrap" );

	if ( editor_wrapper ) {
		return ( editor_wrapper.classList.contains( "html-active" ) ) ? "text" : "visual";
	} else {
		return "read-only";
	}
};

/**
 * Only run this code when editing an existing profile.
 */
if ( window.wsuwp_people_edit_profile_secondary.load_data && wsuwp.people.nid.value ) {

	// Make a REST request for profile data from the primary directory.
	fetch( window.wsuwp_people_edit_profile_secondary.rest_url + "?wsu_nid=" + wsuwp.people.nid.value )
	.then( response => response.json() )
	.then( response => {
		wsuwp.people.populate_profile_from_primary_directory( response[ 0 ] );
	} );

	// Post updated data to the primary profile.
	document.getElementById( "publish" ).addEventListener( "click", function() {
		if ( wsuwp.people.user_can_edit_profile ) {
			wsuwp.people.update_primary_profile();
		}
	} );
}
