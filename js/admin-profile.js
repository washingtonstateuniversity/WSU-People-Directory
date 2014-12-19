jQuery(document).ready(function( $ ) {

	// Tabs.
	$('#wsuwp-profile-tabs').tabs();

	// Repeatable fields handling.
	$( '.wsuwp-profile-add-repeatable' ).on( 'click', 'a', function( e ) {

		e.preventDefault();

		var click_parent = $(this).parent(),
				added = click_parent.siblings( '.wp-profile-repeatable' ).first().clone(),
				attrs = 'name,id,for';

		added.find( 'input' ).val( '' );
		click_parent.before( added );
		attrs = attrs.split( ',' );

		$(this).parent().siblings( '.wp-profile-repeatable' ).each(function( index ) {
			$(this).find( 'input, label' ).each(function() {
				for ( var i = 0; i < attrs.length; i++ ) {
					if ( undefined != $(this).attr( attrs[i] ) ) {
						$(this).attr( attrs[i], $(this).attr( attrs[i] ).replace( '0', index ) );
					}
				}
			});
		});
	});

	// Upload handling.
	var custom_uploader;

	$( '#post-body' ).on( 'click', '.wsuwp-profile-upload-link', function( e ) {

		e.preventDefault();

		var container    = $(this).parents( '.upload-set-wrapper' ),
				upload_input = container.find( '.wsuwp-profile-upload' ),
				upload_link  = container.find( '.wsuwp-profile-upload-link' ),
				remove_link  = container.find( '.wsuwp-profile-remove-link' );

		custom_uploader = wp.media.frames.file_frame = wp.media({
			title: 'Choose ' + upload_link.attr( 'data-type' ),
			button: {
				text: 'Choose ' + upload_link.attr( 'data-type' )
			},
			multiple: false
		});

		custom_uploader.on( 'select', function() {

			attachment = custom_uploader.state().get( 'selection' ).first().toJSON();

			upload_input.val(attachment.id);

			// Show an image or icon for chosen file
			if ( upload_link.attr( 'data-type' ) == 'Photo' ) {
				if ( attachment.sizes.hasOwnProperty( 'thumbnail' ) ) {
					upload_link.html( '<img src="' + attachment.sizes.thumbnail.url + '" />' );
				} else {
					upload_link.addClass( 'small-image-notice' );
					upload_link.html( '<img src="' + attachment.url + '" /><span>This image is smaller than the recommended ___&#215;___ pixel minimum.<br />Please consider uploading a larger image.</span>' );
				}
			} else if ( upload_link.attr( 'data-type' ) == 'File' ) {
				upload_link.html( '<img src="http://' + location.host + '/wp-includes/images/media/document.png" />' );
			}

			// Add a "Remove" link
			if ( remove_link.length === 0 ) {
				upload_link.after( '<p class="hide-if-no-js"><a href="#" class="wsuwp-profile-remove-link">Remove ' + upload_link.attr('title') + '</a></p>' );
			}

		});

		custom_uploader.open();

	});

	// Upload "Remove" handling.
	$( '#post-body' ).on( 'click', '.wsuwp-profile-remove-link', function(e) {

		e.preventDefault();

		var upload_input = $(this).parents( '.upload-set-wrapper' ).find( '.wsuwp-profile-upload' ),
				upload_link  = $(this).parents( '.upload-set-wrapper' ).find( '.wsuwp-profile-upload-link' );

		// Clear the input value.
		upload_input.val( '' );

		// Remove notice.
		upload_link.removeClass( 'small-image-notice' );

		// Replace image with link title value.
		upload_link.html( 'Set ' + upload_link.attr( 'title' ));

		// Remove the "Remove" link.
		$(this).parent( '.hide-if-no-js' ).remove();

	});

});