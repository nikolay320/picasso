/*
 * This file is helper to popup events. 
 * This file may need to edit after new release of eventon
 * author: Mahibul
 * */

jQuery(document).ready(function($){
				
	function fullheight_img_reset_(){
		$('.evo_metarow_fimg').each(function(){					
			feature_image_expansion_($(this));					
		});				
	}
	
// featured image height processing
	function feature_image_expansion_(image, type){
		img = image;
		
		var img_status = img.attr('data-status');
		var img_style = img.attr('data-imgstyle');
		
		// if image already expanded
		if(img_status=='open' ){
			img.attr({'data-status':'close'}).css({'height':''});			
		}else{	
			var img_full_height = parseInt(img.attr('data-imgheight'));
			var cal_width = parseInt(img.closest('.ajde_evcal_calendar').width());
				cal_width = (cal_width)? cal_width: img.width();
			var img_full_width = parseInt(img.attr('data-imgwidth'));


			// show at minimized height
			if(img_style=='100per'){
				relative_height = img_full_height;
			}else if(img_style=='full'){
				relative_height = parseInt(img_full_height * (cal_width/img_full_width)) ;
			}else{
				// minimized version
				if(type=='click'){
					relative_height = parseInt(img_full_height * (cal_width/img_full_width));
					relative_height = (relative_height)? relative_height: img_full_height;
					//console.log(relative_height+ ' '+img_full_height+' '+type);
				}else{
					relative_height = img.attr('data-minheight');
				}					
			}
			
			// when to set the status as open for images
			if(img_status=='' && img_style=='minmized'){
				img.attr({'data-status':'close'});
			}else{
				img.attr({'data-status':'open'});
			}
			img.css({'height':relative_height});
		}			
	}				
					
	
	$('body').on('click', '.bb_event-activity_header', function(event){
		event.preventDefault();
		var event_id = '#activity_event_' + $(this).attr('id');						
			
		
		fullheight_img_reset_();    // added first reset

		$('.evo_lightbox_body').html('');

		
		var event_list = $(event_id);
		var obj = event_list;
							
		
		var content = obj.closest('.eventon_list_event').find('.event_description').html();
		var content_front = obj.html();
		var eventid = obj.closest('.eventon_list_event').data('event_id');
		
		var _content = $(content).not('.evcal_close');		
		
		fullheight_img_reset_();    // added first reset
				
		
		// RTL
		if(event_list.hasClass('evortl')){	
			$('.evo_popin').addClass('evortl');	
			$('.evo_lightbox').addClass('evortl');
		}
	
		$('.evo_lightbox_body').append('<div class="evopop_top">'+content_front+'</div>').append(_content);
		$('.evo_lightbox_body').addClass('event_'+eventid);
		
		var this_map = $('.evo_lightbox_body').find('.evcal_gmaps');
		var idd = this_map.attr('id');
		this_map.attr({'id':idd+'_evop'});
		
		$('.evo_lightbox').addClass('eventcard eventon_events_list show');
		$('body').addClass('evo_overflow');
		$('html').addClass('evo_overflow');

		obj.evoGenmaps({	
			'_action':'lightbox',
			'cal':cal,
			'mapSpotId':idd+'_evop'
		});

		fullheight_img_reset_();  	
		
		// update border color
		bgcolor = $('.evo_pop_body').find('.evcal_cblock').attr('data-bgcolor');
		$('.evo_pop_body').find('.evopop_top').css({'border-left':'3px solid '+bgcolor});
	
		return false;					
		
										
	});			
});		
