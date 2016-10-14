/**
 * Main javascript for fullCal addon for eventon
 * @version 0.10
 * @updated 2.2.22  
 */
jQuery(document).ready(function($){	
	init();		
	
	// INITIATE script
		function init(){
			append_popup_codes();
			$('.eventon_fullcal').each(function(){
				obj = $(this);
				evCal =obj.closest('.ajde_evcal_calendar');
				var strip = obj.find('.evofc_months_strip');
				var width = parseInt(strip.width());

				var cal_width = obj.width();
				
				strip.width(width*3);				

				evofc_add_dots(obj);

				var multiplier = strip.attr('data-multiplier');
					
				if(multiplier<0){
					strip.width(cal_width*3).css({'margin-left':(multiplier*cal_width)+'px'});					
				}
				obj.find('.evofc_month').width(cal_width);

				// if grid ux set to lightbox update cal ux
					evoData =evCal.find('.evo-data');
					if(evoData.attr('data-ux_val')!=4 && evoData.attr('data-grid_ux')==2){
						evoData.attr({'data-ux_val':3});
					}
				
			});

			// fix ratios for resizing the calendar size
				$( window ).resize(function() {
					$('.eventon_fullcal').each(function(){
						var cal_width = $(this).width();
						var strip = $(this).find('.evofc_months_strip');
						var multiplier = strip.attr('data-multiplier');
						
						if(multiplier<0){
							strip.width(cal_width*3).css({'margin-left':(multiplier*cal_width)+'px'});					
						}
						$(this).find('.evofc_month').width(cal_width);
					});
				});
		}
	
	// lightbox
		function append_popup_codes(){
			var popupcode = "<div class='evofc_lightbox evo_popup evoLB' data-cal_id=''>";
				popupcode += "<div class='evo_content_in'>";
					popupcode += "<div class='evo_content_inin'>";
						popupcode += "<div class='evo_lightbox_content'>";
							popupcode += "<a class='evolbclose_fc'>X</a>";
							popupcode += "<div class='evofc_lightbox_body eventon_events_list evcal_eventcard'></div>";
						popupcode += "</div>";
					popupcode += "</div>";
				popupcode += "</div>";
			popupcode += "</div>";
			$('body').append(popupcode);

			// /var popupcode = "<div class='evoFC_popup evoLB' style='display:none' data-cal_id=''><div class='evoFC_popin'><a class='evopopclose'>X</a><div class='evoFC_pop_body  eventon_events_list evcal_eventcard'></div></div></div><div class='evoFC_popbg' style='display:none'></div>";
			// /$('body').append(popupcode);
		}		
		

		// close popup
			$('body').on('click','.evolbclose_fc', function(){				
				closing_lightbox();				
			});

			// close with click outside popup box when pop is shown
				$(document).mouseup(function (e){
					var container=$('.evofc_lightbox_body');
					
					if (!container.is(e.target) // if the target of the click isn't the container...
						&& e.pageX < ($(window).width() - 30)
					&& container.has(e.target).length === 0) // ... nor a descendant of the container
					{					
						closing_lightbox();
					}				
				});
				function closing_lightbox(){
					if(!$('.evofc_lightbox').hasClass('show')) return false;

					if($('.evofc_lightbox').hasClass('notfocus')) return false;

					$('.evofc_lightbox').removeClass('show');
					setTimeout( function(){ 
						$('body').find('.evofc_lightbox_body').html('')
							.attr('class','evofc_lightbox_body eventon_events_list evo_pop_body evcal_eventcard'); 
						$('body').removeClass('evo_overflow');
					}, 500);
				}

		// LIGHTBOX functionsx
			function prepair_popup(){
				$('.evofc_lightbox_body').html('');
			}			
			function show_popup(cal_id){
				rtl = ($('#'+cal_id).find('.evo-data').attr('data-rtl')=='yes')?'evortl':'';
				$('.evofc_lightbox').addClass('show '+rtl).attr('data-cal_id',cal_id);
				$('body').addClass('evo_overflow');
			}			
			function appendTo_popup(content){
				$('.evofc_lightbox_body').html(content);
			}

			// clicking lightbox events from eventlist lightobx on fc
				$('.evofc_lightbox_body').on('click','.desc_trig',function(){
					$('.evofc_lightbox').addClass('notfocus');
				});
				$('body').on('lightbox_event_closing',function(){
					$('.evofc_lightbox').removeClass('notfocus');
				});
	
	// go to today
		$('body').on('evo_goto_today', function(index, calid, evo_data){
			if($('#'+calid).hasClass('evoFC'))	
				eventon_fc_get_new_days($('#'+calid).find('.calendar_header'),'','','jumper');
		});

	// click on a day
		if(is_mobile()){
			if(is_android()){
				$('.evofc_months_strip').on( 'click','.eventon_fc_days .evo_fc_day',function(){
					clickon_day($(this));
				});	
			}else{
				$('.evofc_months_strip').on( 'tap','.eventon_fc_days .evo_fc_day',function(){
					clickon_day($(this));
				});	
			}			
		}else{
			$('.evofc_months_strip').on( 'click','.eventon_fc_days .evo_fc_day',function(){
				clickon_day($(this));
			});			
		}
		function clickon_day(obj){
			if( !obj.hasClass('evo_fc_empty')){
				var new_day = obj.attr('data-day');
				var nest = obj.parent();
						
				var cal_id = obj.closest('.ajde_evcal_calendar').attr('id');
				nest.find('.evo_fc_day').removeClass('on_focus');					
				nest.find('.evo_fc_day[data-day='+new_day+']').addClass('on_focus');
				
				// update the calendar according to the new date selection
				ajax_update_month_events(cal_id, new_day);
			}
		}

		// scroll down to events list
			function focus_eventslist(cal){
				var toppos = cal.find('.calendar_header').offset();
				var grid = cal.find('.eventon_fullcal').height();
				//var win = $(window).scrollTop();

				var scroll = toppos.top+grid;
				//console.log(toppos.top+' '+ win+' '+grid+' '+scroll);

				$('html, body').animate({scrollTop: scroll});
			}

	// click on a day of the week 
		$('.evofc_months_strip').on('click', '.eventon_fc_daynames .evo_fc_day',function(){
			var dow = $(this).data('dow');
			$('.evo_fc_day').removeClass('highl');
			
			$(this).addClass('highl').closest('.evofc_month ').find('.eventon_fc_days').find('p[data-dow='+dow+']')
				.addClass('highl');
			
		});
	
	// AJAX when changing date
		function ajax_update_month_events(cal_id, new_day){
			var ev_cal = $('#'+cal_id);

			if(ev_cal.hasClass('evoFC')){
				// Initial values
					var new_date_el = ev_cal.find('#evcal_head .evoFC_val'),
						new_day_ =1;
					if(!new_date_el.hasClass('mo1st')){	new_day_ = new_day;	}	

					var cal_head = ev_cal.find('.calendar_header'),
						evodata = ev_cal.find('.evo-data'),
						evcal_sort = cal_head.siblings('div.evcal_sort'),
						sort_by=evcal_sort.attr('sort_by'),
						LoadFullMonth = ev_cal.find('.cal_arguments').attr('data-load_fullmonth');

					// change load all month status temporarily
						ev_cal.find('.cal_arguments').attr('data-load_fullmonth','no');
					
					// change values to new in ATTRs
					evodata.attr({'data-cday':new_day});	
					
					var data_arg = {
						action: 		'the_ajax_hook',
						sort_by: 		sort_by, 			
						fc_focus_day: 	new_day,
						direction: 		'none',
						filters: 		ev_cal.evoGetFilters(),
						shortcode: 		ev_cal.evo_shortcodes(),
						evodata: 		ev_cal.evo_getevodata()
					};				
				
				$.ajax({
					beforeSend: function(){
						ev_cal.find('.eventon_events_list').slideUp('fast');
						ev_cal.find('#eventon_loadbar').show().css({width:'0%'}).animate({width:'100%'});
						prepair_popup();
					},
					type: 'POST',
					url:the_ajax_script.ajaxurl,
					data: data_arg,
					dataType:'json',
					success:function(data){
						
						// Open events as a lightbox from grid
						if(evodata.attr('data-grid_ux')==2) {
							appendTo_popup(data.content);

							// close open event card
							$('.evofc_lightbox_body').find('.event_description').each(function(){
								$(this).hide().removeClass('open');
							});

							show_popup(cal_id);
						}else{
							ev_cal.find('.eventon_events_list').html(data.content);
							ev_cal.find('.eventon_other_vals').val(new_day_);
						}
						
					},complete:function(){
						// reset load full month status
						ev_cal.find('.cal_arguments').attr('data-load_fullmonth',LoadFullMonth);

						if(evodata.attr('data-grid_ux')!=2) {
							ev_cal.find('.eventon_events_list').delay(300).slideDown();
						}
						ev_cal.find('#eventon_loadbar').css({width:'100%'}).fadeOut();	
						
						// Load google maps if they are to be shown on load
							if(evodata.data('evc_open')=='1'){
								ev_cal.find('.desc_trig').each(function(){
									$(this).evoGenmaps({'fnt':2,'delay':400});
								});
							}

						// focus to event list
						if(evodata.attr('data-grid_ux')==1){
							focus_eventslist(ev_cal);
						}
					}
				});
			}
			
		}
	
	// click on filter sorting
		$('.eventon_filter_dropdown').on( 'click','p',function(){
			var cal_head = $(this).closest('.eventon_sorting_section').siblings('.calendar_header');
			eventon_fc_get_new_days(cal_head,'','');
		});

	// MONTH JUMPER
		$('.evo_j_dates').on('click','a',function(){
			var container = $(this).closest('.evo_j_container');
			if(container.attr('data-m')!==undefined && container.attr('data-y')!==undefined){
				
				var cal_head = $(this).closest('.calendar_header');
				var evo_dv = cal_head.find('.eventon_other_vals').length;

				if(evo_dv>0)
					eventon_fc_get_new_days(cal_head,'','','jumper');
			}
		});

	// MONTH switching		
		//$('.evcal_btn_prev').on('swiperight', function(){
		$('body').on('click','.evcal_btn_prev', function(){
			var cal_head = $(this).parents('.calendar_header');

			if($(this).closest('.ajde_evcal_calendar').hasClass('evoFC')){
				eventon_fc_get_new_days(cal_head,'prev','');
			}
		});			
		
		$('body').on('click','.evcal_btn_next',function(){	
			var cal_head = $(this).parents('.calendar_header');
			if($(this).closest('.ajde_evcal_calendar').hasClass('evoFC')){
				eventon_fc_get_new_days(cal_head,'next','');
			}
		});
		
	// update the days list for new month
		function eventon_fc_get_new_days(cal_header, change, cday, type){
			
			var cal_id = cal_header.closest('.ajde_evcal_calendar').attr('id');
			var cal = $('#'+cal_id);
			

			// run this script only on calendars with Fullcal
			if(cal.hasClass('evoFC')){

				var cal_head = cal.find('.calendar_header');
				var evodata = cal.find('.evo-data');

				// get object values
				var cur_m = parseInt(evodata.attr('data-cmonth'));
				var cur_y = parseInt(evodata.attr('data-cyear'));
				
				
				// new dates
				var new_date_el = cal_header.find('.eventon_other_vals');
				var new_d =1;
				if(!new_date_el.hasClass('mo1st')){
					new_d = (cday=='')? new_date_el.val(): cday;	
				}
				
				
				// direction based values
				if(change=='next'){
					var new_m = (cur_m==12)?1: cur_m+ 1 ;
					var new_y = (cur_m==12)? cur_y+1 : cur_y;
				}else if(change=='prev'){
					var new_m = (cur_m==1)?12:cur_m-1;
					var new_y = (cur_m==1)?cur_y-1:cur_y;
				}else{
					var new_m =cur_m;
					var new_y = cur_y;
				}
				
				// AJAX data array
				var data_arg = {
					action: 	'evo_fc',
					next_m: 	new_m,	
					next_y: 	new_y,
					next_d: 	new_d,
					change: 	change,
					filters: 		cal.evoGetFilters(),
					shortcode: 		cal.evo_shortcodes(),
				};
				
				var this_section = cal_header.parent().find('.eventon_fc_days');
				var strip = cal_header.parent().find('.evofc_months_strip');
				
				// animation
				var cur_margin = parseInt(strip.css('marginLeft'));
				var month_width = parseInt(strip.parent().width());
				var months = strip.find('.evofc_month').length;
				var super_margin;
				var pre_elems = strip.find('.focus').prevAll().length;
				var next_elems = strip.find('.focus').nextAll().length;
				
				$.ajax({
					beforeSend: function(){
						//this_section.slideUp('fast');
					},
					type: 'POST',
					url:the_ajax_script.ajaxurl,
					data: data_arg,
					dataType:'json',
					success:function(data){						
						
						// build out month grid animation
						if(change=='next' || type=='jumper'){
							if( months ==2 && next_elems==0){
								strip.find('.evofc_month:first-child').remove();
								strip.css({'margin-left':(cur_margin+month_width)+'px'});						
								super_margin = cur_margin;
								strip.append(data.month_grid);
								
							}else if(months== 2 && next_elems==1){
								super_margin = cur_margin-month_width;
							}else{
								strip.append(data.month_grid);
								super_margin = cur_margin-month_width;
							}					
							
							strip.attr({'data-multiplier':'-1'}).find('.evofc_month').removeClass('focus');
							strip.find('.evofc_month:last-child').addClass('focus');
							
						}else if(change=='prev'){
							
							if(months==2 && pre_elems==0){		

								strip.prepend(data.month_grid);
								strip.css({'margin-left':(cur_margin-month_width)+'px'});
								
								strip.find('.evofc_month:last-child').remove();
								super_margin =0;
								
								
							}else if(months== 2 && pre_elems==1){
								super_margin =0;
							}else{
								
								strip.prepend(data.month_grid);
								strip.css({'margin-left':(cur_margin-month_width)+'px'});
								//strip.find('.evofc_month:last-child').remove();
								super_margin = 0;
								
							}
							
							strip.attr({'data-multiplier':'+1'}).find('.evofc_month').removeClass('focus');
							strip.find('.evofc_month:first-child').addClass('focus');
							
						}else{
						// no month change filter change
							
							strip.find('.focus').replaceWith(data.month_grid);
							strip.find('.evofc_month[month='+new_m+']').addClass('focus');
						}

						strip.find('.evofc_month').width(month_width);
						
						// animate the month grid
						strip.delay(200).animate({'margin-left':super_margin+'px'}, 1300, 'easeOutQuint',function(){
							strip.find('.focus').siblings().remove();
							strip.css({'margin-left':'0'});
							strip.attr({'data-multiplier':'0'})
						});	
						
					},complete:function(){
						var cal = cal_header.parent().find('.eventon_fullcal');
						evofc_add_dots(cal);
					}
				});

			}
		}
	
	// tool tips on calendar dates
		$('.evofc_months_strip').on('mouseover' , '.has_events', function(){
			var obj = $(this),
				fullCal = obj.closest('.eventon_fullcal');

			if(obj.data('events')!=''){	

				if(fullCal.data('hover')=='numname'){
				// event names
					ed = $.parseJSON(obj.attr('data-ed'));	
					
					var eventNames= addition ='',
						titletip = fullCal.find('.evofc_title_tip'),
						events = obj.attr('data-events');
					
					titletip.find('.evofc_ttle_cnt').html(events);

					// event names
					$.each(ed.et, function(key, value){
						if(key>2){
							 addition ="<li>+ "+titletip.data('txt')+"</li>";
						}else{
							if(value != 'undefined'){
								color = ed.ec[key];
								eventNames += "<li><b style='background-color:#"+color+"'></b>"+value+"</li>";
							}
						}
					});
					eventNames += addition;
					titletip.find('.evofc_ttle_events').html(eventNames);

					var offs = obj.position();
					width = $('.eventon_fullcal').width();
					var dayh = obj.closest('.evofc_month').find('.eventon_fc_daynames')
						.height();

					BOXCOUNT = obj.data('cnt');
					
					if( BOXCOUNT<5 || (7<BOXCOUNT && BOXCOUNT<12) || (14<BOXCOUNT && BOXCOUNT<19) || (21<BOXCOUNT && BOXCOUNT<26) || BOXCOUNT>28){
						titletip.removeClass('lefter');
						leftOff = offs.left + obj.width();
						rightOFF = 'initial';
					}else{
						titletip.addClass('lefter');
						leftOff = 'initial';
						rightOFF = width- offs.left ;	
					}

					titletip.css({top: (offs.top+dayh), left:leftOff, right:rightOFF}).stop(true, false).fadeIn('fast');

				}else{ // just event count number
					var popup = obj.closest('.eventon_fullcal').find('.evoFC_tip');
					var offs = obj.position();
					var leftOff ='';

					var dayh = obj.closest('.evofc_month').find('.eventon_fc_daynames')
						.height();

					if(obj.data('cnt') %7 ==0){
						popup.addClass('leftyy');
						leftOff = offs.left - 17;
					}else{
						leftOff = offs.left + obj.width()+2;
					}
					
					popup.css({top: (offs.top+dayh), left:leftOff});
					popup.html( obj.data('events') ).stop(true, false).fadeIn('fast');
				}
			}
			
		}).mouseout(function(){
			if($(this).closest('.eventon_fullcal').data('hover')=='numname'){
				$('.evofc_title_tip').removeClass('lefter');
				$('.evofc_title_tip').stop(true, false).hide();
			}else{
				var popup = $(this).closest('.eventon_fullcal').find('.evoFC_tip');
				popup.removeClass('leftyy');			
				popup.stop(true, false).hide();
			}
		});
		
	// add dots for events
		function evofc_add_dots(cal){
			var strip = cal.find('.evofc_months_strip');
			color = strip.attr('data-color');
			heat = (strip.attr('data-heat')=='yes')? true: false;
			cal.find('.has_events').each(function(){
				var event_count = $(this).data('events');
				var elements ='';

				var ed = $.parseJSON($(this).attr('data-ed'));

				if(event_count>5){
					elements = "<i></i><b>+ more</b>";
				}else{
					for(x=0; x<event_count; x++){
						elements += "<i data-et='"+ed.et[x]+"' title='"+ed.et[x]+"'></i>";
					}
				}

				/**
				 * Event color with heat style
				 * @since 0.25  
				 */
				if(heat){
					opacity = 1-(1/(event_count+1));
					$(this).css({'background-color':'#'+color, 'opacity':opacity});
				}

				$(this).append('<span>'+elements+'</span>');
			});
		}
	
	// if mobile check
		function is_mobile(){
			return ( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) )? true: false;
		}
		function is_android(){
			var ua = navigator.userAgent.toLowerCase();
			return ( ua.indexOf("android") > -1)? true: false;
		}

});