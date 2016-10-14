jQuery(document).ready(function($) {
    
    //remove warning class when click in input fields
    $(document).on( 'focus', '.bpqa-input', function(){
        $(this).removeClass('bpqa-warning');
    });
    
    //remove warning class from select dropdowns
    $(document).on( 'change', '.bpqa-form-select', function(){
        $(this).removeClass('bpqa-warning');
    });
   
    //on form submit
    $(document).on( 'click', '.bpqa-submit, .bpqa-cancel', function(e){
    	e.preventDefault();
    	
    	var thisForm  = $(this).closest('form');
    	var textArea  = thisForm.find('.bpqa-textarea');
    	var selectBox = thisForm.find('.bpqa-whats-new-post-in');
    	
    	if ( $(this).hasClass('bpqa-cancel') ) {
    		
    		textArea.val('');
    		selectBox.val('');
    		if ( $('.bpqa-popup-template-holder').is(':visible') ) {
	        	$('.bpqa-popup-template-holder').fadeToggle();
	        }
    	} else {
    		
	        if ( textArea.val() && selectBox.val() != '' ) {  
	        	if ( bpqaArgs.ajaxSubmit ) {
	        		bpqaSubmitForm( thisForm );
	        	} else {
	        		thisForm.submit();
	        	}
	        } else {
	        	if ( !textArea.val() ){
	        		textArea.addClass('bpqa-warning');
	        	}
	        	if ( selectBox.val() == '' ) {
	        		selectBox.addClass('bpqa-warning');
	        	}
	        }
    	}
    });
        
    //max characters
    var maxchars = bpqaArgs.form.max_characters;
    
    $(document).on( 'keyup', '.bpqa-textarea', function(){
    	
    	if ( maxchars == undefined || maxchars == '' ) 
    		return;
    	
        var tlength = $(this).val().length;
        $(this).val($(this).val().substring(0, maxchars));
        var tlength = $(this).val().length;
        remain = maxchars - parseInt(tlength);
        $(this).closest('form').find('.bpqa-characters-count span').text(remain);
    });
    
    // @mentions autosuggest
	if (jQuery.fn.mentions ) {                                                                          
		jQuery('.bpqa-textarea').mentions({ resultsbox : '.bpqa-suggested-results', resultsbox_position : 'prepend' });
	}
	
	// trigger the popup form
	$(document).on( 'click', '.bpqa-form-trigger', function(e){
        e.preventDefault();
        if ( $('.bpqa-popup-template-holder').is(':hidden') ) {
        	$('.bpqa-popup-template-holder').fadeToggle();
        }
        //if ( !$('.bpqa-popup-template-holder').length ) {
        	//bpqaShowPopWindow();
        //}
    });
    
	//not being used at the moment. only with form displayed using ajax
	//ajaxLoaderHolder = '<div id="bpqa-popup-ajax-loader"><div id="bpqa-popup-message-holder"><p>'+bpqaArgs.labels.form.loading+'</p></div><img src="'+bpqaArgs.imgUrl+'/ajax-loader.gif" /></div>';
	
	//ajax popup window info display function. not being used at the moment
	/*function bpqaShowPopWindow() {
		
		var popupHolder = 'bpqa-popup-template-holder-'+bpqaArgs.form.popup_template;
		
		//create new popup window
		jQuery(ajaxLoaderHolder).appendTo('body').fadeToggle('fast');
		jQuery('<div id="'+popupHolder+'" class="bpqa-screen-cover bpqa-popup-template-holder"></div>').appendTo('body').fadeToggle('fast');
		
		//run ajax
		jQuery.ajax({
			type       	: "post",
			data  		: {action:'bpqa_popup_template_display' },		
			url        	: bpqaArgs.ajaxUrl,
			success:function(data){

				//append data into the popup window
				jQuery('#'+popupHolder).append(function() {
					//hide ajax loader
					jQuery('#bpqa-popup-ajax-loader').fadeToggle();	
					
					//close popup on cancel click
					jQuery(document).on('click', '.bpqa-cancel', function(e) {
						//close button function
						jQuery('#'+popupHolder).fadeToggle('fast', function(){
							jQuery('#'+popupHolder).remove();											
						});
						jQuery('#bpqa-popup-ajax-loader').remove();
					});
					
					//hide popup on ESC keypress
					$(document).keyup(function(e) {
				        if ( e.keyCode == 27 && $('.bpqa-popup-template-holder').is(':visible') ) {
				        	jQuery('#'+popupHolder).fadeToggle('fast', function(){
								jQuery('#'+popupHolder).remove();												
							});
				        }
				    });
					
				}, data);					
			}
		});
		return false;
	} */
	
	//popup window info display function
	function bpqaSubmitForm( bpqaForm ) {
		
		jQuery('#bpqa-popup-message-holder p').html(bpqaArgs.labels.form.loading);
		
		//if ( jQuery('#bpqa-popup-ajax-loader').length ) {
			jQuery('#bpqa-popup-ajax-loader').fadeToggle();	
		//} else {
		//	jQuery(ajaxLoaderHolder).appendTo('body').fadeToggle('fast');
		//}
		
		//run ajax
		jQuery.ajax({
			type       	: "post",
			data  		: {action:'bpqa_submit_form', 'formValues': bpqaForm.serialize() },		
			url        	: bpqaArgs.ajaxUrl,
			success:function(data){		
				
				jQuery('#bpqa-popup-message-holder p').html(bpqaArgs.labels.form.updated_message);
			
				setTimeout(function() {
									
					jQuery('.bpqa-textarea').val('');
			    	jQuery('.bpqa-whats-new-post-in').val('');
					jQuery('#bpqa-popup-ajax-loader').fadeToggle('slow');
					
					if ( jQuery('.bpqa-popup-template-holder').is(':visible') ) {
						jQuery('.bpqa-popup-template-holder').fadeToggle('slow');
					}
					
					/* Not being used at the moment. will be used if form is being displayed using ajax 
					if ( jQuery(popupHolder).length ) {
						jQuery(popupHolder+', #bpqa-popup-ajax-loader').fadeToggle('slow', function(){
							jQuery(this).remove();						
						});
					} else {
						jQuery('.bpqa-textarea').val('');
				    	jQuery('.bpqa-whats-new-post-in').val('');
						jQuery('#bpqa-popup-ajax-loader').fadeToggle('slow', function() {
							jQuery(this).remove();
						});
					}
					*/
            	}, 1500);		
			}
		});
		return false;
	} 
});