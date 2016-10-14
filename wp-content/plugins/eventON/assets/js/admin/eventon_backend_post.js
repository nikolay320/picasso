/** 
 * @version  2.3.19
 */
jQuery(document).ready(function($){

	var date_format = $('#evcal_dates').attr('date_format');
	var time_format = ($('body').find('input[name=_evo_time_format]').val()=='24h')? 'H:i':'h:i:A';

	// meta box sections
	// click hide and show
		$('#evo_mb').on('click','.evomb_header',function(){			
			var box = $(this).siblings('.evomb_body');			
			if(box.hasClass('closed')){
				$(this).removeClass('closed');
				box.slideDown('fast').removeClass('closed');
			}else{
				$(this).addClass('closed');
				box.slideUp('fast').addClass('closed');
			}
			update_eventEdit_meta_boxes_values();
		});
		
		function update_eventEdit_meta_boxes_values(){
			var box_ids ='';
			
			$('#evo_mb').find('.evomb_body').each(function(){				
				if($(this).hasClass('closed'))
					box_ids+=$(this).attr('box_id')+',';
			});		
			$('#evo_collapse_meta_boxes').val(box_ids);
		}
	
	// location picker
		$('#evcal_location_field').on('change',function(){
			var option = $('option:selected', this);

			// if a legit value selected
			if($(this).val()!='' && $(this).val()!= '-'){
				$('#evcal_location_name').val( $(this).val());
				$('#evcal_location').val( option.data('address')  );
				$('#evcal_lat').val( option.data('lat')  );
				$('#evcal_lon').val( option.data('lon')  );
				$('#evo_location_tax').val( option.data('tid')  );
				$('#evcal_location_link').val( option.data('link')  );

				$('#evo_loc_img_id').val( option.data('loc_img_id')  );
				if(option.data('loc_img_src')){
					$('.evo_metafield_image .evo_loc_image_src img').attr('src', option.data('loc_img_src') ).fadeIn();
				}else{
					$('.evo_metafield_image .evo_loc_image_src img').fadeOut();
				}
			}else{
				// if select a saved location picked open empty fields
				$(this).closest('.evcal_location_data_section').find('.evoselectfield_saved_data').slideToggle();
			}

			// if select saved field selected
				if($(this).val()=='-'){
					$(this).closest('.evcal_location_data_section').find('input[type=text]').attr('value','').val('');
					$('.evo_metafield_image .evo_loc_image_src img').fadeOut();
					$('#evo_location_tax').val('');
				}
		});
		// location already entered info edit button
			$('body').on('click','.evoselectfield_data_view', function(){
				$(this).parent().parent().find('.evoselectfield_saved_data').slideToggle();
			});

	// organizer picker
		$('#evcal_organizer_field').on('change',function(){
			var option = $('option:selected', this);

			if($(this).val()!=''){
				$('#evcal_organizer_name').val( $(this).val());
				$('#evcal_org_contact').val( option.data('contact')  );
				$('#evo_org_img_id').val( option.data('img')  );	
				$('#evo_organizer_tax_id').val( option.data('tid')  );
				$('#evcal_org_address').val( option.data('address')  );

				if(option.data('imgsrc')){
					$('.evo_metafield_image .evo_org_image_src img').attr('src', option.data('imgsrc') ).fadeIn();	
				}else{
					$('.evo_metafield_image .evo_org_image_src img').fadeOut();
				}
			}
			// if select saved field selected
				if($(this).val()=='-'){
					$(this).closest('.evcal_location_data_section').find('input[type=text]').attr('value','').val('');
					$('.evo_metafield_image .evo_org_image_src img').fadeOut();
					$('#evo_organizer_tax_id').val('');
				}
		});

	//makeInputSelect("evcal_location_field");
		function makeInputSelect(id) {
		    var $sel = $("#" + id);
		    var $inp = $("#" + id + "_Other");
		    var selW = $sel.width();
		    var selH = $sel.height();
		    var selOff = $sel.offset();
		    $inp.width(selW);
		    //
		    $sel.click(function(event) {
		        if(event.which <= 1) { //left click
		            var offX = event.pageX - selOff.left;
		            var offY = event.pageY - selOff.top;
		            if(offX < $sel.width() - 22 && offY < selH) { // input
		                $sel.hide();
		                $inp.show().focus();
		            }
		        }
		    });
		    $sel.change(function() {
		        $inp.val($sel.val());
		    });
		    $inp.blur(function() {
		        // remove selected attribute
		        $sel.find("option:selected").attr("selected",false);
		        // remove old user input option
		        $sel.find("option[frominput=1]").remove();
		        // add and select a new user input option
		        $sel.append($("<option />").val($inp.val()).text($inp.val()).attr("frominput", 1).attr("selected", true));
		        $inp.hide();
		        $sel.show();        
		    });
		    //
		    $sel.after($inp);
		    $inp.hide();
		}
		
	/** COLOR picker **/	
		$('#color_selector').ColorPicker({		
			color: get_default_set_color(),
			onChange:function(hsb, hex, rgb,el){
				set_hex_values(hex,rgb);
			},onSubmit: function(hsb, hex, rgb, el) {
				set_hex_values(hex,rgb);
				$(el).ColorPickerHide();
			}		
		});
		
			function set_hex_values(hex,rgb){
				var el = $('#evColor');
				el.find('.evcal_color_hex').html(hex);
				$('#evcal_event_color').attr({'value':hex});
				el.css({'background-color':'#'+hex});		
				set_rgb_min_value(rgb,'rgb');
			}
			
			function get_default_set_color(){
				var colorraw =$('#evColor').css("background-color");
						
				var def_color =rgb2hex( colorraw);	
					//alert(def_color);
				return def_color;
			}
		
	//event color
		$('.evcal_color_box').click(function(){		
			$(this).addClass('selected');
			var new_hex = $(this).attr('color');
			var new_hex_var = '#'+new_hex;
			
			set_rgb_min_value(new_hex_var,'hex');		
			$('#evcal_event_color').val( new_hex );
			
			$('#evColor').css({'background-color':new_hex_var});
			$('.evcal_color_hex').html(new_hex);
			
		});
	
	/** convert the HEX color code to RGB and get color decimal value**/
		function set_rgb_min_value(color,type){
			
			if( type === 'hex' ) {			
				var rgba = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(color);	
				var rgb = new Array();
				 rgb['r']= parseInt(rgba[1], 16);			
				 rgb['g']= parseInt(rgba[2], 16);			
				 rgb['b']= parseInt(rgba[3], 16);	
			}else{
				var rgb = color;
			}
			
			var val = parseInt((rgb['r'] + rgb['g'] + rgb['b'])/3);
			
			$('#evcal_event_color_n').attr({'value':val});
		}
		
		function rgb2hex(rgb){
			
			if(rgb=='1'){
				return;
			}else{
				if(rgb!=='' && rgb){
					rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
					
					return "#" +
					("0" + parseInt(rgb[1],10).toString(16)).slice(-2) +
					("0" + parseInt(rgb[2],10).toString(16)).slice(-2) +
					("0" + parseInt(rgb[3],10).toString(16)).slice(-2);
				}
			}
		}
		
	/** User interaction meta field 	 **/
		// new window
		$('#evo_new_window_io').click(function(){
			var curval = $(this).hasClass('selected');
			if(curval){
				$(this).removeClass('selected');
				$('#evcal_exlink_target').val('no');
			}else{
				$(this).addClass('selected');
				$('#evcal_exlink_target').val('yes');
			}
		});
		 
		$('.evcal_db_ui').click(function(){
			var val = $(this).attr('value');
			$('#evcal_exlink_option').val(val);
			
			$('.evcal_db_ui').removeClass('selected');
			$(this).addClass('selected');
			
			var link = $(this).attr('link');		
			var linkval = $(this).attr('linkval');
			var opval = $(this).attr('value');
			
			if(link=='yes'){			
				$('#evcal_exlink').show();
				if(linkval!=''){
					$('#evcal_exlink').val(linkval);
				}
			}
			
			// slide down event card
			if(opval=='1' || opval=='3'|| opval=='X'){
				$('#evo_new_window_io').removeClass('selected');
				$('#evcal_exlink_target').val('no');
				$('#evcal_exlink').hide().attr({value:''});
				$('#evo_new_window_io').hide();
			}else{
				$('#evo_new_window_io').show();
			}
		});
		
	// repeating events UI	
		// frequency
		$('#evcal_rep_freq').change(function(){
			var field = $(this).find("option:selected").attr('field');

			// monthly
			if(field =='months'){
				$('.evo_rep_month').show();

				// show or hide day of week
				var field_x = $('#evo_rep_by').find("option:selected").attr('value');
				if(field_x=='dow'){
					$('.evo_rep_month_2').show();
				}else{
					$('.evo_rep_month_2').hide();
				}
				$('.evo_preset_repeat_settings').show();
				$('.repeat_information').hide();
			// custom repeating patterns
			}else if(field=='custom'){
				$('.evo_preset_repeat_settings').hide();
				$('.repeat_information').show();
			}else{
				$('.evo_rep_month').hide();
				$('.evo_rep_month_2').hide();
				$('.evo_preset_repeat_settings').show();
				$('.repeat_information').hide();
			}
			$('#evcal_re').html(field);
		});

		// custom repeat interval function
			$('.evo_repeat_interval_new .ristD, .evo_repeat_interval_new .rietD').datepicker({
				dateFormat: date_format,
			});
			$('.evo_repeat_interval_new .ristT, .evo_repeat_interval_new .rietT').timepicker({
				'step': 5,
				'timeFormat':time_format
			});

		// adding a new repeat interval
		// @since 2.2.24
			$('#evo_add_repeat_interval').on('click',function(){
				var obj = $('.evo_repeat_interval_new');

				// if the add new RI form is not visible
				if(!obj.is(':visible')){
					obj.slideDown();
				}else{

					if( obj.find('.ristD').val() &&
						obj.find('.ristT').val() &&
						obj.find('.rietD').val() &&
						obj.find('.rietT').val() 
					){		
						count = $('.evo_custom_repeat_list').find('li').length;		
						var html = '<li class="new"><span>from</span>'+obj.find('.ristD').val()+' '+obj.find('.ristT').val()+' <span class="e">End</span> '+obj.find('.rietD').val()+' '+obj.find('.rietT').val()+'<em alt="Delete">x</em><input type="hidden" name="repeat_intervals['+count+'][0]" value="'+obj.find('.ristD').val()+' '+obj.find('.ristT').val()+'"/><input type="hidden" name="repeat_intervals['+count+'][1]" value="'+obj.find('.rietD').val()+' '+obj.find('.rietT').val()+'"/><input type="hidden" name="repeat_intervals['+count+'][type]" value="dates"></li>';

						$('ul.evo_custom_repeat_list').append(html);
						obj.find('input').val('');
					}else{
						$('.evo_repeat_interval_button').find('span').fadeIn().html(' All fields are required!').delay(2000).fadeOut();
					}
				}
			});

		// delete a repeat interval
			$('.evo_custom_repeat_list').on('click','li em',function(){
				$(this).parent().slideUp(function(){
					$(this).remove();
				});
			});

		// show all repeat intervals
			$('.evo_repeat_interval_view_all').on('click',function(){
				if($(this).attr('data-show')=='no'){
					$('.evo_custom_repeat_list').find('li.over').slideDown();
					$(this).attr({'data-show':'yes'}).html('View Less');
				}else{
					$('.evo_custom_repeat_list').find('li.over').slideUp();
					$(this).attr({'data-show':'no'}).html('View All');
				}
			});

		// repeat by value from select field
		// show correct info based on this selection
		$('#evo_rep_by').change(function(){
			var field = $(this).find("option:selected").attr('value');
			if(field=='dow'){
				$('.evo_rep_month_2').show();
				//$('.evo_month_rep_value').html().show();
			}else{
				$('.evo_rep_month_2').hide();
			}	
		});
		
	// end time hide or not
		$('#evo_endtime').click(function(){
			// yes
			if($(this).hasClass('NO')){
				$('.evo_enddate_selection').animate({'opacity':'0.5'});
				//$('#evo_dp_to').attr({'value':''});
			}else{
				$('.evo_enddate_selection').animate({'opacity':'1'});
			}
		});
	// All day or not
		$('#evcal_allday_yn_btn').click(function(){
			// yes
			if($(this).hasClass('NO')){
				$('.evcal_time_selector').animate({'opacity':'0.5'});
				//$('#evo_dp_to').attr({'value':''});
			}else{
				$('.evcal_time_selector').show().animate({'opacity':'1'});
			}
		});
		
	//date picker on		
		$('#evo_dp_from').datepicker({ 
			dateFormat: date_format,
			numberOfMonths: 2,
			onClose: function( selectedDate , obj) {
				// update end time
				$( "#evo_dp_to" ).datepicker( "option", "minDate", selectedDate );

		        var date = $(this).datepicker('getDate');
   				var dayOfWeek = date.getUTCDay();

   				// save event year based off start event date
   				$('#evo_event_year').attr({'value':date.getUTCFullYear()});
   				$('.evo_days_list').find('input').removeAttr('checked');
   				$('.evo_days_list').find('input[value="'+dayOfWeek+'"]').attr({'checked':'checked'});

   				//alert($.datepicker.iso8601Week(new Date(selectedDate)))

   				//console.log(selectedDate);
		    }
		});
		$('#evo_dp_to').datepicker({ 
			dateFormat: date_format,
			numberOfMonths: 2,
			onClose: function( selectedDate ) {
	        	$( "#evo_dp_from" ).datepicker( "option", "maxDate", selectedDate );
	      	}
		});

	// eventbrite
		$('#evcal_eventb_btn').click(function(){
			$('#evcal_eventb_data').slideToggle();
		});
		$('#evcal_eventb_btn_2').click(function(){
			$('#evcal_eventb_msg').hide();
			var ev= $('#evcal_eventb_ev_id').val();
			if(ev==''){
				$('#evcal_eventb_msg').html('You gotta enter something other than blank space..').show();
			}else{
				
				$.ajax({
					beforeSend: function(){
						$('#evcal_eventb_msg').html('We are connecting to eventbrite..').show();
					},
					type: 'POST',
					url:the_ajax_script.ajaxurl,
					data: {	action:'the_post_ajax_hook_3',	
						event_id:ev
					},
					dataType:'json',
					success:function(data){
						//alert(data);
						if(data.status =='1'){
							$('#evcal_eventb_msg').hide();
							$('#evcal_eventb_data_tb').append(data.code);
							$('#evcal_eventb_s1').delay(400).slideDown();
							$('#evcal_eb1').html(ev);
							$('#evcal_eventb_ev_d2').val(ev);
						}else{
							$('#evcal_eventb_msg').html('Could not retrieve data at this time.').show();
						}
						
					},complete:function(){
						//ev_cal.find('.evcal_events_list').delay(300).fadeIn();
					}
				});
				
			}
		});
		$('#evcal_eventb_data_tb').on( 'click','.evcal_data_row',function(){
			
			var field = $(this).attr('var');
			var p_val = $(this).find('p.value');
			var value = p_val.html();
			var this_makd = $(this).attr('marked');
			
			if(this_makd =='yes'){
			// DESELECT
				// evcal_eb_
				// evcal_ebv_
				$(this).removeClass('evcal_checked_row');
				$(this).attr({'marked':'no'});
				
				if(field =='capacity' || field=='price' || field=='url' ){
					$('.evcal_eb_'+field).slideUp();
					$('#evcal_ebv_'+field).attr({'value':''});
				}else{
					var oldval = $('#'+field).attr('oldval');				
					$('#'+field).val(oldval);				
				}
			}else{
				// SELECT
				$(this).addClass('evcal_checked_row');
				$(this).attr({'marked':'yes'});
				
				if(field =='capacity'|| field=='price' || field=='url'){
					$('.evcal_eb_'+field).slideDown();
					$('#evcal_ebv_'+field).val(value);
				}else{
					var field_cv =$('#'+field).val();
					if(field_cv!=''){
						$('#'+field).attr({'oldval':field_cv});
					}
					$('#'+field).val(value);
				}
				if(field =='capacity' ){
					$('#evcal_eb3').html(value);
				}if(field =='price' ){
					$('#evcal_eb4').html(value);
				}
			}
			$('#evcal_eventb_ev_d1').val('yes');		
		});
		// disconnect event brite
		$('#evcal_eventb_btn_dis').click(function(){
			var val_ar = new Array('evcal_eventb_ev_d2', 'evcal_eventb_ev_d1',
				'evcal_ebv_url','evcal_ebv_capacity','evcal_eventb_tprice');
			
			for(i=0; i<val_ar.length; i++){
				$('#'+val_ar[i]).attr({'value':''});
			}
			
			$('.evcal_eb_r').slideUp();
			$('#evcal_eb5').hide();
		});	
		
	// MEETUP
		$('#evcal_meetup_btn').click(function(){
			$('#evcal_meetup_data').slideToggle();
		});
		
		$('#evcal_meetup_btn_2').click(function(){
			$('#evcal_meetup_msg').hide();
			var ev= $('#evcal_meetup_ev_id').val();
			if(ev==''){
				$('#evcal_meetup_msg').html('You gotta enter something other than blank space..').show();
			}else{
				
				$.ajax({
					beforeSend: function(){
						$('#evcal_meetup_msg').html('We are connecting to Meetup..').show();
					},
					type: 'POST',
					url:the_ajax_script.ajaxurl,
					data: {	action:'the_post_ajax_hook_2',	
						event_id:ev
					},
					dataType:'json',
					success:function(data){
						//alert(data);
						if(data.status =='1'){
							$('#evcal_meetup_msg').hide();
							$('#evcal_meetup_data_tb').append(data.code);
							$('#evcal_meetup_s1').delay(400).slideDown();
							$('#evcal_001').html(ev);
							$('#evcal_meetup_ev_d2').val(ev);
						}else{
							$('#evcal_meetup_msg').html('Could not retrieve data at this time.').show();
						}
						
					},complete:function(){
						//ev_cal.find('.evcal_events_list').delay(300).fadeIn();
					}
				});
				
			}
		});
		
		$('#evcal_meetup_data_tb').on( 'click','.evcal_data_row',function(){
			$(this).addClass('evcal_checked_row');
			
			var field = $(this).attr('var');
			var p_val = $(this).find('p.value');
			var value = p_val.html();
			var this_makd = $(this).attr('marked');
			
			if(this_makd =='yes'){
				// DESELECT
				// evcal_mu_
				// evcal_muv_
				$(this).removeClass('evcal_checked_row');
				$(this).attr({'marked':'no'});
				
				if(field=='url' ){
					//$('.evcal_mu_'+field).slideUp();
					$('#evcal_lmlink').attr({'value':''});
				}else{
					var oldval = $('#'+field).attr('oldval');				
					$('#'+field).val(oldval);				
				}
			}else{
				// SELECT
				$(this).addClass('evcal_checked_row');
				$(this).attr({'marked':'yes'});
				
				if(field=='url'){
					//$('.evcal_mu_'+field).slideDown();
					//$('#evcal_muv_'+field).val(value);
					$('#evcal_lmlink').val(value);
				}else if(field =='time' ){
					$('#evcal_start_date').val( p_val.attr('ftime') );
					$('#evcal_start_time_hour').val( p_val.attr('hr') );
					$('#evcal_start_time_min').val( p_val.attr('min') );
					$('#evcal_st_ampm').val( p_val.attr('ampm') );
				}else{
					var field_cv =$('#'+field).val();
					if(field_cv!=''){
						$('#'+field).attr({'oldval':field_cv});
					}
					$('#'+field).val(value);
				}			
			}
			$('#evcal_meetup_ev_d1').val('yes');		
		});
		
		// disconnect meetup
		$('#evcal_meetup_btn_dis').click(function(){
			// remove values from MU data set and MU id
			var val_ar = new Array('evcal_meetup_ev_d1', 'evcal_meetup_ev_d2');
			
			for(i=0; i<val_ar.length; i++){
				$('#'+val_ar[i]).attr({'value':''});
			}
			
			$('.evcal_meetup_url_field').slideUp();
			$('#evcal_mu2').hide();
		});	
	
});