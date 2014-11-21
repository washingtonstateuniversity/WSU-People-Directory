jQuery(document).ready(function($){

	// Tabs...
	$('#wsuwp-profile-tabs').tabs();

	// Repeatable fields
	repeatable_fields = new handle_repeatable_fields();

	// Uploaders
	user_file_upload = new file_upload();

});

// Repeatable fields handling
function handle_repeatable_fields(){

	jQuery('.wsuwp-profile-add-repeatable').on( 'click', 'a', function(e) {
		e.preventDefault();
		var click_parent = jQuery(this).parent()
		var added = click_parent.siblings('.wp-profile-repeatable').first().clone();
		added.find('input').val('');
		var attrs = 'name, id, for';
		attrs = attrs.split(',');
		click_parent.before( added );
		jQuery(this).parent().siblings('.wp-profile-repeatable').each( function( index ){
			jQuery( this ).find('input, label').each(function(){
				for( var i = 0 ; i < attrs.length; i++ ){
					if( undefined != jQuery(this).attr( attrs[i] ) ) {
						jQuery(this).attr( attrs[i] , jQuery(this).attr(attrs[i] ).replace( '0', index ) );
					}
				}
			});
		});
		
	});
	
}

// File upload handling
function file_upload(){

	var custom_uploader;

	jQuery('#post-body').on( 'click', '.wsuwp-profile-upload-link', function(e) {
		e.preventDefault();
		var upload_input = jQuery(this).parents('.upload-set-wrapper').find('.wsuwp-profile-upload'),
				upload_link  = jQuery(this).parents('.upload-set-wrapper').find('.wsuwp-profile-upload-link');
		custom_uploader = wp.media.frames.file_frame = wp.media({
			title: 'Choose ' + upload_link.attr('data-type'),
			button: {
				text: 'Choose ' + upload_link.attr('data-type')
			},
			multiple: false
		});
		custom_uploader.on('select', function() {
			attachment = custom_uploader.state().get('selection').first().toJSON();
			upload_input.val(attachment.id);
			// Show an image or icon for chosen file
			if ( upload_link.attr('data-type') == 'Photo') {
				upload_link.html('<img src="' + attachment.url + '" />');
			} else if ( upload_link.attr('data-type') == 'File') {
				upload_link.html('<img src="http://m1.wpdev.cahnrs.wsu.edu/directory/wp-includes/images/media/document.png" />');
			}
			upload_link.removeClass('button'); // Remove the "button" class from the upload link
			upload_link.after('<p class="hide-if-no-js"><a href="#" class="wsuwp-profile-remove-link">Remove ' + upload_link.attr('title') + '</a></p>'); // Add a "remove" button
		});
		custom_uploader.open();
	});

	jQuery('#post-body').on('click', '.wsuwp-profile-remove-link', function(e) {
		e.preventDefault();
		var upload_input = jQuery(this).parents('.upload-set-wrapper').find('.wsuwp-profile-upload'),
				upload_link  = jQuery(this).parents('.upload-set-wrapper').find('.wsuwp-profile-upload-link');
		upload_input.val(''); // Clear the input value
		upload_link.html('Upload ' + upload_link.attr('title')); // Replace image with link "title" value
		jQuery(this).parent('.hide-if-no-js').remove(); // Remove the "Remove..." link
	});

}