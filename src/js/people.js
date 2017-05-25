/* global _ */
( function( $, document, window ) {

	// Loads images asynchronously.
	$( document ).ready( function() {
		$( ".has-photo .photo img" ).each( function() {
			$( this ).attr( "src", $( this ).data( "photo" ) );
		} );
	} );

	// Opens a profile in a modal.
	var lightbox_template = _.template( $( "#wsu-person-lightbox-template" ).html() ),
		focused_before_modal;

	$( ".lightbox" ).on( "click", ".name a, .card figure a", function( e ) {
		e.preventDefault();

		var $person = $( this ).closest( ".wsu-person" ),
			profile_id = $person.data( "profile-id" );

		$.ajax( {
			url: window.wsu_people_rest_url + profile_id
		} )
		.done( function( response ) {
			var email = ( response.email_alt ) ? response.email_alt : response.email,
				phone = ( response.phone_alt ) ? response.phone_alt : response.phone,
				office = ( response.office_alt ) ? response.office_alt : response.office,
				photo = false,
				title = response.position_title,
				about = response.content.rendered,
				photo_index = $person.data( "photo" ),
				title_index = $person.data( "title" ),
				about_index = $person.data( "about" );

			// Grabs the locally set photo, taking care to avoid any undefined properties.
			if ( photo_index && response.photos[ photo_index ] ) {
				photo = ( response.photos[ photo_index ].thumbnail ) ? response.photos[ photo_index ].thumbnail : response.photos[ photo_index ].full;
			} else if ( response.photos[ 0 ] ) {
				photo = ( response.photos[ 0 ].thumbnail ) ? response.photos[ 0 ].thumbnail : response.photos[ 0 ].full;
			}

			// Grabs the locally set title.
			if ( title_index && response.working_titles.length ) {
				if ( String( title_index ).indexOf( " " ) > 0 ) {
					var titles = [];

					$.each( title_index.split( " " ), function( i ) {
						if ( response.working_titles[ i ] ) {
							titles.push( response.working_titles[ i ] );
						}
					} );

					title = titles.join( "<br />" );
				} else if ( response.working_titles[ title_index ] ) {
					title = response.working_titles[ title_index ];
				}
			} else if ( response.working_titles.length ) {
				title = response.working_titles.join( "<br />" );
			}

			// Grabs the locally set "about" content.
			if ( about_index && "" !== response[ about_index ] ) {
				about = response[ about_index ];
			}

			focused_before_modal = document.activeElement;

			$( "body > a, body > div" ).attr( "aria-hidden", "true" );

			$( "body" ).addClass( "wsu-people-lightbox-open" ).append( lightbox_template( {
				name: $person.find( ".name" ).text(), // Seems like a good idea to grab this locally.
				photo: photo,
				title: title,
				email: email,
				phone: phone,
				office: office,
				address: response.address,
				website: response.website,
				about: about
			} ) );

			$( "#wsu-person-name" ).focus();
		} );
	} );

	// Traps focus in a modal if one is open.
	$( document ).keydown( function( e ) {
		if ( e.which === 9 && $( ".wsu-people-lightbox" ).length ) {
			var focusable_elements = $( ".wsu-people-lightbox" ).find( "a, button, [tabindex]:not([tabindex^='-'])" ),
				focused_element_index = focusable_elements.index( $( ":focus" ) );

			if ( e.shiftKey && 0 === focused_element_index ) {
				focusable_elements[ focusable_elements.length - 1 ].focus();
				e.preventDefault();
			} else if ( !event.shiftKey && focused_element_index === focusable_elements.length - 1 ) {
				focusable_elements[ 0 ].focus();
				e.preventDefault();
			}
		}
	} );

	// Closes the modal when the close button or overlay is clicked.
	$( "body" ).on( "click", ".wsu-people-lightbox-close", function( e ) {
		if ( e.target === this ) {
			close_people_modal();
		}
	} );

	// Closes the modal when the escape key is pushed.
	$( document ).keydown( function( e ) {
		if ( e.which === 27 ) {
			close_people_modal();
		}
	} );

	// Shows/hides profiles according to text entered into the search input.
	$( ".wsu-people-filters .search" ).on( "keyup", "input", function() {
		var	search_value = $( this ).val(),
			profiles = $( this ).closest( ".wsu-people-wrapper" ).find( ".wsu-person" );

		if ( search_value.length > 0 ) {
			profiles.each( function() {
				var person = $( this );
				if ( person.text().toLowerCase().indexOf( search_value.toLowerCase() ) === -1 ) {
					person.hide( "fast" ).attr( "aria-hidden", "true" );
				} else {
					person.show( "fast" ).removeAttr( "aria-hidden" );
				}
			} );
		} else {
			profiles.show( "fast" );
		}
	} );

	// Toggles filter options visibility.
	$( ".wsu-people-filter-label" ).on( "click", function() {
		var expanded = ( "false" === $( this ).attr( "aria-expanded" ) ) ? "true" : "false";

		$( this ).attr( "aria-expanded", expanded ).next( "ul" ).slideToggle( 250 );
	} );

	// Shows/hides profiles according to selected filter options.
	$( ".wsu-people-filter-terms" ).on( "change", "input:checkbox", function() {
		var classes = [],
			$profiles = $( this ).closest( ".wsu-people-wrapper" ).find( ".wsu-person" );

		$( ".wsu-people-filter-terms input:checkbox:checked" ).each( function() {
			classes.push( "." + $( this ).val() );
		} );

		if ( classes.length > 0 ) {
			$profiles.not( classes.join( "," ) ).hide( 250 ).attr( "aria-hidden", "true" );
			$profiles.filter( classes.join( "," ) ).show( 250 ).removeAttr( "aria-hidden" );
		} else {
			$profiles.show( 250 ).removeAttr( "aria-hidden" );
		}
	} );

	// Closes the modal.
	function close_people_modal() {
		$( ".wsu-people-lightbox" ).remove();

		$( "body" ).removeClass( "wsu-people-lightbox-open" );

		$( "body > a, body > div" ).removeAttr( "aria-hidden" );

		if ( focused_before_modal ) {
			focused_before_modal.focus();
		}
	}
}( jQuery, document, window ) );
