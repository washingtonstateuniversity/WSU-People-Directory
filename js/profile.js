jQuery(document).ready( function($) {

	$('#profile-accordion > dl > dd').hide();

	$('#profile-accordion dt').click( function() {
		$(this).next('dd').toggle().parents('dl').toggleClass('disclosed');
	})

/*
	// Tabs.
	$( '#profile-tabbed-content' ).tabs();

	// Tabs on smaller devices.
	$( '#profile-tabbed-content' ).on( 'click', '#profile-tabs .ui-tabs-active a', function(event){
		$(this).closest( 'ul' ).toggleClass( 'open' );
		event.preventDefault();
	});
*/
});