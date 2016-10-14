/*
	Javascript: Eventon Weekly Calendar
	version: 0.1
*/
jQuery(document).ready(function($){
	
	init();	
	
	/*
		acronyms:
			dim = days in month
			fw = focus week
			wim = weeks in month
			difw = days in first week
	*/
	// INITIATE script
		function init(){		
			$('.eventon_weeklyview').each(function(){

				var cal_id = $(this).closest('.ajde_evcal_calendar').attr('id');
				var evCal = $('#'+cal_id);

				indBoxWidth = resize_boxes(evCal);	
				
				
			});

			// fix ratios for resizing the calendar size
				$( window ).resize(function() {
					$('.eventon_weeklyview').each(function(){
						var cal_id = $(this).closest('.ajde_evcal_calendar').attr('id');
						var evCal = $('#'+cal_id);

						resize_boxes(evCal);
					});
				});
		}
		function resize_boxes(evCal){
			var width_section = parseInt(evCal.find('.evoWV_days').width());

			indBoxWidth = parseInt((width_section )/7);
			newStripWidth = indBoxWidth*7;
			evoWV_days = evCal.find('.evoWV_days');
			eventon_wv_days = evCal.find('.eventon_wv_days');

			//adjust width for deficit
			deficit = width_section - newStripWidth;

			// adjust the margin left
			newMarginLeft = parseInt( (evoWV_days.attr('data-focus_week'))-1) *newStripWidth;
			//newMarginLeft = newMarginLeft+deficit;
			//newMarginLeft = width_section;
			eventon_wv_days.css({'margin-left':'-'+newMarginLeft+'px'});
			evoWV_days.attr({'data-ml':newMarginLeft});

			//evoWV_days.width(newStripWidth);
			evCal.find('.evo_wv_day').width(indBoxWidth);
			eventon_wv_days.width( (indBoxWidth+20)*32);

			//console.log(indBoxWidth+' '+newStripWidth);

			return indBoxWidth;
		}

	// switch weeks 
		$('.eventon_weeklyview').on('click', '.evowv_arrow',function(){

			if(!$(this).hasClass('disable')){

				var direction = $(this).attr('data-dir');
				var cal_id = $(this).closest('.ajde_evcal_calendar').attr('id');
					var evCal = $('#'+cal_id);
					var evoData = evCal.find('.evo-data');

				var width_section = parseInt(evCal.find('.evoWV_days').width());
				var evoWV_days = $(this).siblings('.evoWV_days');
				var focus_wk = parseInt(evoData.attr('data-focus_week'));
				var wim = parseInt(evoData.attr('data-wim'));

				var ml = parseInt(evoWV_days.attr('data-ml'));
					boxWidth = evCal.find('.evo_wv_day').width();
				var weekMargin = boxWidth*7;
				//var weekMargin = width_section;
				
				if(direction=='next'){
					newWeek = (focus_wk < wim)? focus_wk+1: focus_wk;
					newMargin = (focus_wk < wim)? ml+weekMargin: ml;
				}else{
					newWeek = (focus_wk != 1)? focus_wk-1: focus_wk;
					newMargin = (focus_wk != 1)? ml-weekMargin: ml;
				}		

				// update passed on week value
				evCal.find('input[name=wv_focus_week]').attr({'value':newWeek});	

				// arrows active or disable
					if(direction=='next'){
						if(newWeek==wim){
							evCal.find('.evowv_next').addClass('disable');
						}else{evCal.find('.evowv_prev').removeClass('disable');	}
					}else{
						if(newWeek==1){
							evCal.find('.evowv_prev').addClass('disable');
						}else{evCal.find('.evowv_next').removeClass('disable');	}
					}

				evoWV_days.find('.eventon_wv_days').animate({'margin-left':'-'+newMargin+'px'});
				evoData.attr({'data-focus_week':newWeek});
				evoWV_days.attr({'data-ml':newMargin, 'data-focus_week':newWeek});

				ajax_update_week_events(cal_id, newWeek);
			}

		});
	
	// AJAX when switching week
		function ajax_update_week_events(cal_id, newWeek){
			var evCal = $('#'+cal_id);

			if(evCal.hasClass('evoWV')){
				
				var evodata = evCal.find('.evo-data');
				var cal_head = evCal.find('.calendar_header');
				var evcal_sort = cal_head.siblings('div.evcal_sort');
						
				var sort_by=evcal_sort.attr('sort_by');					
				
				var data_arg = {
					//action: 		'the_ajax_wv',
					action: 		'the_ajax_hook',
					sort_by: 		sort_by, 			
					wv_focus_week: 	newWeek,
					direction: 		'none',
					filters: 		evCal.evoGetFilters(),
					shortcode: 		evCal.evo_shortcodes(),
					evodata: 		evCal.evo_getevodata()
				};				
				
				$.ajax({
					beforeSend: function(){
						evCal.find('.eventon_events_list').slideUp('fast');
						evCal.find('#eventon_loadbar').show().css({width:'0%'}).animate({width:'100%'});
					},
					type: 'POST',
					url:the_ajax_script.ajaxurl,
					data: data_arg,
					dataType:'json',
					success:function(data){
						//alert(data);
						evCal.find('.eventon_events_list').html(data.content);
					},complete:function(){
						evCal.find('#eventon_loadbar').css({width:'100%'}).fadeOut();
						evCal.find('.eventon_events_list').delay(300).slideDown();
						evCal.evoGenmaps({'delay':400});
					}
				});
			}			
		}
	
	// click on filter sorting
		$('.eventon_filter_dropdown').on( 'click','p',function(){
			var evCal = $(this).closest('.ajde_evcal_calendar');
			if(evCal.hasClass('evoWV')){
				eventon_wv_get_new_days( evCal.attr('id'),'none','filter');
			}
		});

	// MONTH JUMPER
		$('.evo_j_dates').on('click','a',function(){
			var container = $(this).closest('.evo_j_container');
			if(container.attr('data-m')!==undefined && container.attr('data-y')!==undefined){
				
				var evCal = $(this).closest('.ajde_evcal_calendar');
				if(evCal.hasClass('evoWV'))
					eventon_wv_get_new_days(evCal.attr('id'),'','jumper');
			}
		});

	// MONTH switching		
		$('body').on('click','.evcal_btn_prev', function(){
			var evCal = $(this).closest('.ajde_evcal_calendar');
			if(evCal.hasClass('evoWV')){
				eventon_wv_get_new_days(evCal.attr('id'),'prev','switch');
			}
		});
		$('body').on('click','.evcal_btn_next',function(){
			var evCal = $(this).closest('.ajde_evcal_calendar');
			if(evCal.hasClass('evoWV')){
				eventon_wv_get_new_days(evCal.attr('id'),'next','switch');				
			}			
		});
		
	// update the weeks bar for new month or new filters
		function eventon_wv_get_new_days(cal_id, change, type){
			
			var evCal = $('#'+cal_id);			

			// run this script only on calendars with Fullcal
			if(evCal.hasClass('evoWV')){

				var cal_header = evCal.find('.calendar_header');
				var evodata = evCal.find('.evo-data');

				// get object values
				var cur_m = parseInt(evodata.attr('data-cmonth')),
					cur_y = parseInt(evodata.attr('data-cyear')),
					alwaysfirst = cal_header.find('.always_first_week').val(),
					current_week = parseInt(cal_header.find('.evoWV_other_val').val());

				// direction based values
					var new_w = current_week;
					if(change=='next'){
						var new_m = (cur_m==12)?1: cur_m+ 1 ;
						var new_y = (cur_m==12)? cur_y+1 : cur_y;
						//var new_w = current_week+1;
					}else if(change=='prev'){
						var new_m = (cur_m==1)?12:cur_m-1;
						var new_y = (cur_m==1)?cur_y-1:cur_y;
						//var new_w = current_week-1;
					}else{
					// no change						
						var new_m =cur_m;
						var new_y = cur_y;
					}

				// if always first week set
					if(alwaysfirst=='yes' && (type=='switch'|| type=='jumper'))
						new_w = 1;

				// update passed on week value
				cal_header.find('input[name=wv_focus_week]').attr({'value':new_w});	
				evodata.attr({'data-focus_week':new_w});
				evCal.find('.evoWV_days').attr({'data-focus_week':new_w});

				// AJAX data array
					var data_arg = {
						action: 	'the_ajax_wv2',
						next_m: 	new_m,	
						next_y: 	new_y,
						focus_week: new_w,
						filters: 		evCal.evoGetFilters(),
						shortcode: 		evCal.evo_shortcodes(),
						evodata: 		evCal.evo_getevodata()
					};
				
				$.ajax({
					beforeSend: function(){},
					type: 'POST',
					url:the_ajax_script.ajaxurl,
					data: data_arg,
					dataType:'json',
					success:function(data){
						if(change!='none')
							evCal.find('.eventon_wv_days').css({'margin-left':0})
							.parent().attr({'data-ml':0});

						// add content
						evCal.find('.eventon_wv_days')
							.parent().html(data.content);

						// disable going further out
						if(new_w == 1)	
							evCal.find('.evowv_prev').addClass('disable');
						if(new_w==data.evodata.wim)
							evCal.find('.evowv_next').removeClass('disable');

						resize_boxes(evCal);

						// update evodata
							evodata.attr({
								'data-focus_week': data.evodata.focus_week,
								'data-dim': data.evodata.dim,
								'data-wim': data.evodata.wim,
								'data-difw': data.evodata.difw,
							});
						
					},complete:function(){	}
				});
			}
		}


	// if mobile check
		function is_mobile(){
			return ( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) )? true: false;
		}


	
});