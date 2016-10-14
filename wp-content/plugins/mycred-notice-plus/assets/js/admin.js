/**
 * Notifications Admin Scripts
 * @since 1.0
 * @version 1.1
 */
jQuery(document).ready(function($){
	// Load wp color picker
	$( '.wp-color-picker-field' ).wpColorPicker();

	// Toggle Instant Notifications
	$( 'input#myCRED-notifications-instant-use' ).click(function(){
		if ( $(this).is(':checked') ) {
			$( 'div#instant-notifications' ).show();
		}
		else {
			$( 'div#instant-notifications' ).hide();
		}
	});

	// Toggle Negative Colors
	$( 'input#myCRED-notifications-colors-negative-use' ).click(function(){
		if ( $(this).is(':checked') ) {
			$( 'div#negative-colors' ).show();
		}
		else {
			$( 'div#negative-colors' ).hide();
		}
	});
});