/*
	Javascript: Eventon Daily View
	version:	0.24
*/
jQuery(document).ready(function($){

	$('.evo-search').on('click',function(){
		var section = $(this).parent().siblings('.evo_cal_above_content');
		var item = section.find('.evo_search_bar');

		item.slideToggle('2000','easeInOutCubic', function(){
			if(item.is(':visible'))
				item.find('input').focus();
		});
	});

	// Enter key detection for pc
		$.fn.enterKey = function (fnc) {
		    return this.each(function () {
		        $(this).keypress(function (ev) {
		            var keycode = (ev.keyCode ? ev.keyCode : ev.which);
		            if (keycode == '13') {
		                fnc.call(this, ev);
		            }
		        })
		    })
		}

	//submit search 
		$(".evo_search_bar_in input").enterKey(function () {
		   	var ev_cal= $(this).closest('.ajde_evcal_calendar');
		   	ev_cal.find('.cal_arguments').attr({'data-s': $(this).val()});

		   	var cal_head = ev_cal.find('.calendar_header');	
			var evodata = ev_cal.find('.evo-data');

			var evcal_sort = cal_head.siblings('div.evcal_sort');						
			var sort_by=evcal_sort.attr('sort_by');
			var evodata = ev_cal.evo_getevodata();
			var data_arg = {
				action: 		'the_ajax_hook',
				sort_by: 		sort_by, 	
				direction: 		'none',
				filters: 		ev_cal.evoGetFilters(),
				shortcode: 		ev_cal.evo_shortcodes(),
				evodata: 		evodata
			};

			data_arg = cal_head.evo_otherVals({'data_arg':data_arg});	

			$.ajax({
				beforeSend: function(){
					ev_cal.find('.eventon_events_list').slideUp('fast');
					ev_cal.find('#eventon_loadbar').show().css({width:'0%'}).animate({width:'100%'});
				},
				type: 'POST',
				url:the_ajax_script.ajaxurl,
				data: data_arg,
				dataType:'json',
				success:function(data){
					// /alert(data);
					//console.log(data);
					ev_cal.find('.eventon_events_list').html(data.content);
														
				},complete:function(){
					ev_cal.find('#eventon_loadbar').css({width:'100%'}).fadeOut();
					ev_cal.find('.eventon_events_list').delay(300).slideDown('slow');
					ev_cal.evoGenmaps({'delay':400});
				}
			});

			// for fullcal
				if(ev_cal.hasClass('evoFC')){			 	
				 	// AJAX data array
					var data_arg_2 = {
						action: 	'evo_fc',
						next_m: 	evodata.cmonth,	
						next_y: 	evodata.cyear,
						next_d: 	data_arg.fc_focus_day,
						change: 	'',
						filters: 		ev_cal.evoGetFilters(),
						shortcode: 		ev_cal.evo_shortcodes(),
					};
					$.ajax({
						beforeSend: function(){
							//this_section.slideUp('fast');
						},
						type: 'POST',
						url:the_ajax_script.ajaxurl,
						data: data_arg_2,
						dataType:'json',
						success:function(data){
							var strip = cal_head.parent().find('.evofc_months_strip');
							strip.html(data.month_grid);

							//width adjustment
							var month_width = parseInt(strip.parent().width());
							strip.find('.evofc_month').width(month_width);
						}
					});
				}

			// for dailyview
				if(ev_cal.hasClass('evoDV')){
					// AJAX data array
					var data_arg_3 = {
						action: 	'the_ajax_daily_view',
						next_m: 	evodata.cmonth,	
						next_y: 	evodata.cyear,
						next_d: 	data_arg.dv_focus_day,
						cal_id: 	ev_cal.attr('id'),
						send_unix: 	evodata.send_unix,
						filters: 		ev_cal.evoGetFilters(),
						shortcode: 		ev_cal.evo_shortcodes(),
					};
					$.ajax({
						beforeSend: function(){
							//this_section.slideUp('fast');
						},
						type: 'POST',
						url:the_ajax_script.ajaxurl,
						data: data_arg_3,
						dataType:'json',
						success:function(data){
							var this_section = cal_head.parent().find('.eventon_daily_in');
							this_section.html(data.days_list);
						}
					});
				}		
			// for weeklyview
				if(ev_cal.hasClass('evoWV')){
					// AJAX data array
					var data_arg_4 = {
						action: 	'the_ajax_wv2',
						next_m: 	evodata.cmonth,	
						next_y: 	evodata.cyear,
						focus_week: 	data_arg.wv_focus_week,
						filters: 		ev_cal.evoGetFilters(),
						shortcode: 		ev_cal.evo_shortcodes(),
					};
					$.ajax({
						beforeSend: function(){
							//this_section.slideUp('fast');
						},
						type: 'POST',
						url:the_ajax_script.ajaxurl,
						data: data_arg_4,
						dataType:'json',
						success:function(data){
							// save width data
							var width1 = ev_cal.find('.evoWV_days').width();
							var width2 = ev_cal.find('.eventon_wv_days').width();
							var width3 = ev_cal.find('.evo_wv_day').width();
							var ml1 = ev_cal.find('.eventon_wv_days').css('margin-left');

							// add content
							ev_cal.find('.eventon_wv_days')
								.parent().html(data.content);

							ev_cal.find('.evoWV_days').css({'width':width1});
							ev_cal.find('.eventon_wv_days').css({'width':width2, 'margin-left':ml1});
							ev_cal.find('.evo_wv_day').css({'width':width3});

						}
					});
				}			

		});

	
});