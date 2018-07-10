/**
 * Create a copy of the WP inline edit post function.
 */
let wsuwp_people_directory_wp_inline_edit = window.inlineEditPost.edit;

/**
 * Overwrites the WP inline edit post function so that the current
 * Display Biography option is properly set when quick editing.
 */
window.inlineEditPost.edit = function( id ) {

	// Call the original WP inline edit post function.
	wsuwp_people_directory_wp_inline_edit.apply( this, arguments );

	// Get the post ID.
	let post_id = 0;

	if ( "object" === typeof( id ) ) {
		post_id = parseInt( this.getId( id ) );
	}

	if ( post_id > 0 ) {
		let edit_row = document.getElementById( "edit-" + post_id );
		let post_row = document.getElementById( "post-" + post_id );
		let bio = post_row.querySelector( ".column-use_bio span" ).dataset.bio;

		// Mark the current option.
		edit_row.querySelector( "[name='_use_bio'] option[value='" + bio + "']" ).selected = true;
	}
};

/**
 * Save the selected Display Biography option for the selected profiles.
 */
document.getElementById( "bulk_edit" ).addEventListener( "click", function() {
	let bulk_row = document.getElementById( "bulk-edit" );
	let post_ids = [];
	let bio = bulk_row.querySelector( "[name='_use_bio']" ).value;

	// Get the IDs of the profile posts being edited.
	bulk_row.querySelectorAll( "#bulk-titles > div" ).forEach( function( post ) {
		post_ids.push( post.id.replace( /^(ttle)/i, "" ) );
	} );

	// POST to the AJAX function to perform the meta update.
	fetch( window.ajaxurl, {
		method: "POST",
		credentials: "same-origin",
		headers: { "Content-Type": "application/x-www-form-urlencoded; charset=utf-8" },
		body: "action=save_bio_edit&_ajax_nonce=" + window.wsupeople.nonce + "&post_ids=" + post_ids + "&use_bio=" + bio
	} );
} );
