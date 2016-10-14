/**
 * Instant Notifications Script
 * @since 1.1
 * @version 1.0
 */
jQuery(document).ready(function($){

	var mycred_get_notice = function() {
		$.ajax({
			type : "POST",
			data : {
				action  : 'mycred-inotify',
				token   : myCRED_Notice.token
			},
			dataType : "JSON",
			url : myCRED_Notice.ajaxurl,
			// On Successful Communication
			success    : function( data ) {
				if ( data !== null && data !== undefined ) {
					// Debug
					console.log( data );
				
					$.each( data, function( index, value ){
						if ( value.stay === 'false' )
							jQuery.noticeAdd({ text: value.text, stay: false, type: value.type });
						else
							jQuery.noticeAdd({ text: value.text, stay: true, type: value.type });
					});
				}
			}
		});
	};

	window.setInterval(function(){
		console.log( 'Fire' );
		mycred_get_notice();
	}, myCRED_Notice.frequency );

});