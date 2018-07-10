document.addEventListener( "DOMContentLoaded", function() {

	const images = [].slice.call( document.querySelectorAll( ".wsu-person.has-photo .photo img" ) );
	const profiles = [].slice.call( document.querySelectorAll( ".wsu-person" ) );
	const filters = [].slice.call( document.querySelectorAll( ".wsu-people-filter-label" ) );
	const filter_options = [].slice.call( document.querySelectorAll( ".wsu-people-filter-terms input[type='checkbox']" ) );

	/**
	 * Loads images asynchronously.
	 *
	 * This could/should eventually be updated to provide actual lazy loading.
	 */
	images.forEach( function( image ) {
		image.src = image.dataset.photo;
	} );

	/**
	 * Toggles filter options visibility.
	 */
	filters.forEach( function( filter ) {
		filter.addEventListener( "click", function( event ) {
			let expanded = ( "false" === event.target.getAttribute( "aria-expanded" ) ) ? "true" : "false";

			event.target.setAttribute( "aria-expanded", expanded );
		} );
	} );

	/**
	 * Toggles profile card visibility according to selected filter options.
	 */
	filter_options.forEach( function( filter_option ) {
		filter_option.addEventListener( "change", function() {
			let checked_options = [].slice.call( document.querySelectorAll( ".wsu-people-filter-terms input[type='checkbox']:checked" ) );
			let classes = [];

			checked_options.forEach( function( checked_option ) {
				classes.push( "." + checked_option.value );
			} );

			if ( classes.length > 0 ) {
				profiles.forEach( function( profile ) {
					if ( has_classes( profile, classes ) ) {
						profile.hidden = false;
					} else {
						profile.hidden = true;
					}
				} );
			} else {
				profiles.forEach( function( profile ) {
					profile.hidden = false;
				} );
			}
		} );
	} );

	/**
	 * Toggles profile card visibility based on text entered into the search input.
	 */
	document.querySelector( ".wsu-people-filters .search input" ).addEventListener( "input", function( event ) {
		let	search_value = event.target.value;

		if ( search_value.length > 0 ) {
			profiles.forEach( function( profile ) {
				if ( profile.textContent.toLowerCase().indexOf( search_value.toLowerCase() ) === -1 ) {
					profile.hidden = true;
				} else {
					profile.hidden = false;
				}
			} );
		} else {
			profiles.forEach( function( profile ) {
				profile.hidden = false;
			} );
		}
	} );

	/**
	 * Determines if a profile contains all the given classes.
	 *
	 * @param {object} profile The profile card to check.
	 * @param {array} classes  Classes to check the profile for.
	 */
	function has_classes( profile, classes ) {
		return classes.every( function( c ) {
			return profile.classList.contains( c );
		} );
	}

} );
