jQuery(document).ready( function($){

	// Tabs.
	$( '#profile-tabbed-content' ).tabs();

	// Tabs on smaller devices.
	$( '#profile-tabbed-content' ).on( 'click', '#profile-tabs .ui-tabs-active a', function(event){
		$(this).closest( 'ul' ).toggleClass( 'open' );
		event.preventDefault();
	});

});