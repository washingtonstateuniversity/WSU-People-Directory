var wsuwp = wsuwp || {};
wsuwp.people = wsuwp.people || {};

/**
 * Makes a request for and returns profile data.
 *
 * @param {string} data POST data.
 */
wsuwp.people.make_request = function( data ) {
	let options = {
		method: "POST",
		credentials: "same-origin",
		headers: { "Content-Type": "application/x-www-form-urlencoded; charset=utf-8" },
		body: data
	};

	return fetch( window.ajaxurl, options ).then( response => response.json() );
};

/**
 * Returns a repeatable input.
 *
 * @param {string} type  The type of repeatable input.
 * @param {string} value The input value.
 */
wsuwp.people.repeatable_input = function( type, value ) {
	return `<span contenteditable="true" class="${type}" data-placeholder="Enter ${type} here">${value}</span><button type="button" class="wsu-person-remove">
			<span class="screen-reader-text">Delete</span>
		</button>
		<input type="hidden" data-for="${type}" name="_wsuwp_profile_${type}[]" value="${value}" />`;
};

/**
 * Returns an image for the photo collection.
 *
 * @param {string} url The full URL path of the image.
 * @param {string} id  The attachment post ID.
 */
wsuwp.people.photo_input = function( url, id ) {
	return `<div class="wsu-person-photo-wrapper">
		<img src="${url}" />
		<button type="button" class="wsu-person-remove">
			<span class="screen-reader-text">Delete</span>
		</button>
		<input type="hidden" name="_wsuwp_profile_photos[]" value="${id}" />
	</div>`;
};

/**
 * Closes the photo collection modal.
 */
wsuwp.people.close_photo_collection = function() {
	let card_photo = document.querySelector( ".photo img" );
	let primary_photo = document.querySelector( ".wsu-person-photo-wrapper:first-of-type img" );

	if ( primary_photo ) {
		card_photo.setAttribute( "src", primary_photo.getAttribute( "src" ) );
	} else {
		if ( card_photo ) {
			card_photo.remove();
		}

		document.querySelector( ".photo" ).classList.add( "wsu-person-add-photo" );
		document.querySelector( ".photo figcaption" ).textContent = "+ Add photo(s)";
	}

	document.body.classList.remove( "wsu-person-photo-collection-open" );
	previous_focus.focus();
};

/**
 * Determines if an object has any properties.
 *
 * @param {object} data The object to check.
 */
wsuwp.people.is_empty_object = function( data ) {
	for ( var x in data ) {  // jshint ignore:line
		return false;
	}

	return true;
};

/**
 * Create an array of photo IDs already in the collection.
 */
wsuwp.people.existing_photos = function() {
	let images = Array.prototype.slice.call( document.querySelectorAll( "[name='_wsuwp_profile_photos[]']" ) );
	return images.map( image => parseInt( image.value ) );
};

/**
 * Insert buttons for handling repeatable inputs.
 */
document.addEventListener( "DOMContentLoaded", function() {
	const degrees = document.querySelectorAll( "header .degree" );
	const titles = document.querySelectorAll( ".contact .title" );
	const remove_button = "<button type='button' class='wsu-person-remove dashicons dashicons-no'><span class='screen-reader-text'>Delete</span></button>";

	// Inserts a button for adding new degrees.
	document.querySelector( ".card header" ).insertAdjacentHTML( "beforeend", "<button type='button' data-type='degree' class='wsu-person-add-repeatable-meta wsu-person-add-degree'>+ Add</button>" );

	// Inserts a button for adding new titles.
	titles[ titles.length - 1 ].insertAdjacentHTML( "afterend", "\n<button type='button' data-type='title' class='wsu-person-add-repeatable-meta wsu-person-add-title'>+ Add another title</button>" );

	// Inserts a remove button for each degree.
	degrees.forEach( function( input ) {
		input.insertAdjacentHTML( "afterend", remove_button );
	} );

	// Inserts a remove button for all but the first title.
	titles.forEach( function( input, index ) {
		if ( 0 !== index ) {
			input.insertAdjacentHTML( "afterend", remove_button );
		}
	} );
} );

/**
 * Request and populate profile data.
 */
document.getElementById( "publishing-action" ).addEventListener( "click", function( event ) {

	// Bail if the click isn't on the Load or Confirm button.
	if ( !event.target || ( !event.target.matches( "#load-ad-data" ) && !event.target.matches( "#confirm-ad-data" ) ) ) {
		return;
	}

	const loading_indicator = document.querySelector( "#publishing-action .spinner" );
	const nid = document.getElementById( "_wsuwp_profile_ad_nid" );
	const hash = document.getElementById( "confirm-ad-hash" );
	const load_button = document.getElementById( "load-ad-data" );
	const confirm_button = document.getElementById( "confirm-ad-data" );

	event.preventDefault();
	event.target.disabled = true;

	// Prevent any further progress if no NID has been entered.
	if ( "" === nid.value ) {
		window.alert( "Please enter a Network ID" );
		event.target.disabled = false;
		return;
	}

	// Indicate that data is loading.
	loading_indicator.style.visibility = "visible";

	let data_nonce = "&_ajax_nonce=" + window.wsuwp_people_edit_profile.nid_nonce;
	let data_request_from = "&request_from=" + window.wsuwp_people_edit_profile.request_from;
	let data_network_id = "&network_id=" + nid.value;

	let request;

	// Make a request to load institutional profile data for review.
	if ( event.target.matches( "#load-ad-data" ) ) {
		let data = "action=wsu_people_get_data_by_nid" + data_nonce + data_request_from + data_network_id;
		request = wsuwp.people.make_request( data );
	}

	// Make a request to confirm the loaded profile data.
	if ( event.target.matches( "#confirm-ad-data" ) ) {
		let data = "action=wsu_people_confirm_nid_data" + data_nonce + data_request_from + data_network_id;
		data += "&confirm_ad_hash=" + hash.value;
		data += "&post_id=" + document.getElementById( "post_ID" ).value;
		request = wsuwp.people.make_request( data );
	}

	request.then( response => {
		event.target.disabled = false;
		loading_indicator.style.visibility = "hidden";

		// Alert the user if the request was unsuccessful.
		if ( !response.success ) {
			window.alert( response.data );
			return;
		}

		// Alert the user if the request returned an empty response.
		if ( wsuwp.people.is_empty_object( response.data ) ) {
			window.alert( "Sorry, a profile for " + nid.value + " could not be found." );
			return;
		}

		// Populate the institutional profile data.
		if ( event.target.matches( "#load-ad-data" ) ) {
			if ( response.data.id ) {

				// This function is defined in admin-edit-profile-secondary.js.
				wsuwp.people.populate_profile_from_primary_directory( response.data );
			} else {
				document.querySelector( ".wsu-person" ).dataset.nid = nid.value;

				// Populate card data.
				new Map( [
					[ "name", response.data.given_name + " " + response.data.surname ],
					[ "title", response.data.title ],
					[ "email", response.data.email ],
					[ "phone", response.data.telephone_number ],
					[ "office", response.data.office ],
					[ "address", response.data.street_address ]
				] )
				.forEach( function( value, input ) {
					document.querySelector( ".wsu-person ." + input ).textContent = value;
					document.querySelector( "[data-for='" + input + "']" ).value = value;
				} );

				hash.value = response.data.confirm_ad_hash;
			}

			confirm_button.classList.remove( "profile-hide-button" );

			document.querySelectorAll( "#post-body > div > div > div" ).forEach( function( item ) {
				item.classList.add( "show" );
			} );
		}

		// Confirm the loaded profile data.
		if ( event.target.matches( "#confirm-ad-data" ) ) {
			const description = document.querySelector( ".load-ad-container .description" );
			const publish_button = document.getElementById( "publish" );

			nid.setAttribute( "readonly", true );
			description.textContent = "The WSU Network ID used to populate this profile's data from " + description.dataset.location + ".";

			load_button.classList.add( "profile-hide-button" );
			confirm_button.classList.add( "profile-hide-button" );
			publish_button.classList.remove( "profile-hide-button" );
		}
	} );
} );

/**
 * Profile card handling
 */
const card = document.querySelector( ".wsu-person .card" );

/**
 * Handles `focusout` events occuring within a profile card.
 */
card.addEventListener( "focusout", function( event ) {
	if ( !event.target ) {
		return;
	}

	// Copy content from `contenteditable` elements into their respective inputs.
	if ( event.target.matches( "[contenteditable='true']" ) ) {
		let field = event.target.classList;
		let index = [].indexOf.call( document.querySelectorAll( "." + field ), event.target );
		let value = event.target.textContent;

		document.querySelectorAll( "[data-for='" + field + "']" )[ index ].value = value;
	}

	// Hide any visible repeatable area remove buttons.
	if (
		event.target.matches( ".title" ) ||
		event.target.matches( ".degree" ) ||
		event.target.matches( ".wsu-person-remove" )
	) {
		let new_target = event.relatedTarget;

		if (
			!new_target ||
			new_target.matches( ".wsu-person-remove" )
		) {
			return;
		}

		if (
			new_target.matches( ".title" ) ||
			new_target.matches( ".degree" )
		) {
			let index = [].indexOf.call( document.querySelectorAll( ".wsu-person-remove" ), new_target.nextSibling );

			document.querySelectorAll( ".wsu-person-remove" ).forEach( function( item, i ) {
				if ( i === index ) {
					return;
				}

				item.style.display = "none";
			} );
		} else {
			document.querySelector( ".wsu-person-remove" ).style.display = "none";
		}
	}
} );

/**
 * Ignore the Enter key in editable elements.
 */
card.addEventListener( "keypress", function( event ) {
	if ( event.target && event.target.matches( "[contenteditable='true']" ) ) {
		if ( 13 === event.which ) {
			event.preventDefault();
		}
	}
} );

/**
 * Click event handling for repeatable meta.
 */
card.addEventListener( "click", function( event ) {
	if ( !event.target ) {
		return;
	}

	// Add a repeatable meta input.
	if ( event.target.matches( ".wsu-person-add-repeatable-meta" ) ) {
		let type = event.target.dataset.type;
		let value = "";

		event.target.insertAdjacentHTML( "beforebegin", wsuwp.people.repeatable_input( type, value ) );
	}

	// Remove a repeatable meta input and its associated input.
	if ( event.target.matches( ".wsu-person-remove" ) ) {
		let span = event.target.previousElementSibling;
		let field = span.classList[ 0 ];
		let index = [].indexOf.call( document.querySelectorAll( "." + field ), span );

		span.remove();
		document.querySelectorAll( "[data-for='" + field + "']" )[ index ].remove();
		event.target.remove();
	}
} );

/**
 * Surface a repeatable input's remove button.
 * (This is a little buggy when the remove button for an empty input is clicked.)
 */
card.addEventListener( "focusin", function( event ) {
	if ( !event.target ) {
		return;
	}

	if ( event.target.matches( ".title" ) ||
		 event.target.matches( ".degree" )
	) {
		event.target.nextElementSibling.style.display = "inline-block";
	}
} );

/**
 * Photo collection handling.
 */
const card_photo_container = document.querySelector( ".wsu-person .photo" );
const photo_collection = document.querySelector( ".wsu-person-photo-collection" );
let previous_focus;

/**
 * Click event handling for the photo collection modal.
 */
document.getElementById( "post-body-content" ).addEventListener( "click", function( event ) {
	if ( !event.target ) {
		return;
	}

	// Surface the photo collection modal.
	if (
		event.target.matches( ".wsu-person .photo:not(.wsu-person-add-photo)" ) ||
		(
			event.target.parentNode &&
			event.target.parentNode.matches( ".wsu-person .photo:not(.wsu-person-add-photo)" )
		)
	) {
		window.scroll( 0, 0 );

		let position = card_photo_container.getBoundingClientRect();

		photo_collection.style.top = position.top + "px";
		photo_collection.style.left = position.left + "px";
		previous_focus = document.activeElement;
		document.body.classList.add( "wsu-person-photo-collection-open" );

		document.querySelector( ".wsu-person-add-photo" ).focus();
	}

	// Close the photo collection modal
	if ( event.target.matches( ".wsu-person-photo-collection-close" ) ) {
		wsuwp.people.close_photo_collection();
	}

	// Surface the Add Media modal.
	if (
		event.target.matches( ".wsu-person-add-photo" ) ||
		(
			event.target.parentNode &&
			event.target.parentNode.matches( ".wsu-person-add-photo" )
		)
	) {
		let media_frame;

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
			let photos = media_frame.state().get( "selection" );

			photos.models.forEach( function( attachment ) {
				let photo = attachment.toJSON();
				let has_thumbnail = photo.sizes.hasOwnProperty( "thumbnail" );
				let url = has_thumbnail ? photo.sizes.thumbnail.url : photo.url;
				let id = photo.id;

				// Only add photos that aren't already in the collection.
				if ( !wsuwp.people.existing_photos().includes( id ) ) {
					document.querySelector( "button.wsu-person-add-photo" ).insertAdjacentHTML( "beforebegin", wsuwp.people.photo_input( url, id ) );

					// If this is the first photo added to the profile, add it to the card.
					if ( card_photo_container.classList.contains( "wsu-person-add-photo" ) ) {
						card_photo_container.classList.remove( "wsu-person-add-photo" );
						card_photo_container.insertAdjacentHTML( "afterbegin", "<img src='" + url + "'>" );
						card_photo_container.querySelector( "figcaption" ).textContent = "Manage photo collection";
					}
				}
			} );
		} );

		media_frame.open();
	}

	// Remove a photo from the collection.
	if ( event.target.matches( ".wsu-person-photo-wrapper .wsu-person-remove" ) ) {
		event.target.parentNode.remove();
	}
} );

/**
 * Keydown event handling for the photo collection modal.
 */
document.addEventListener( "keydown", function( event ) {
	if ( !document.body.classList.contains( "wsu-person-photo-collection-open" ) ) {
		return;
	}

	// Trap focus within the photo collection modal.
	if ( 9 === event.which ) {
		let focusable_elements = photo_collection.querySelectorAll( "button:not(.wsu-person-remove)" );
		let focused_element_index = [].indexOf.call( focusable_elements, document.activeElement );

		if ( event.shiftKey && 0 === focused_element_index ) {
			focusable_elements[ focusable_elements.length - 1 ].focus();
			event.preventDefault();
		} else if ( !event.shiftKey && focused_element_index === focusable_elements.length - 1 ) {
			focusable_elements[ 0 ].focus();
			event.preventDefault();
		}
	}

	// Close the photo collection modal if the Escape key is pushed.
	if ( 27 === event.which ) {
		wsuwp.people.close_photo_collection();
	}
} );

/**
 * Use jQuery UI Sortable to allow reordering of photos.
 */
jQuery( ".wsu-person-photo-collection" ).sortable( {
	cursor: "move",
	placeholder: "wsu-person-photo-wrapper placeholder",
	items: "> .wsu-person-photo-wrapper",
	start: function( e, ui ) {
		ui.helper.height( "" ).width( "" );
	}
} );
