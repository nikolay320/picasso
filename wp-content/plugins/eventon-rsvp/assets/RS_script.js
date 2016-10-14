/**
 * Javascript: RSVP Events Calendar
 * @version  1.4
 */
jQuery(document).ready(function($){
	
	init();	
	
	var submit_open = false;
	var form_msg;
	
	// INITIATE script
		function init(){	
			form_msg = 	$('.evors_msg_');		
			$('.eventon_events_list').on('click','.evoRS_status_option_selection span',function(){
				open_rsvp_lightbox($(this));
			});	

			$('body').on('click','.evoRS_status_option_selection span',function(){
				open_rsvp_lightbox($(this));
			});

			$('body').on('click','.eventon_rsvp_rsvplist a.update_rsvp',function(){
				open_rsvp_lightbox($(this));
			});

			// change RSVP
			$('body').on('click','.evors_change_rsvp .change',function(){
				open_rsvp_lightbox($(this));
			});
		}

	
	// RSVP lightbox set up
		var popupcode = "<div class='evors_popup' style='display:none'></div>";
		$('body').append(popupcode);
		var evors_inside = $('body').find('#evors_get_form').html();
		$('body').find('.evors_popup').html(evors_inside);
		$('body').find('#evors_get_form').remove();

	// open lightbox rsvp form
		function open_rsvp_lightbox(obj){
			var rsvp_lightbox = $('body').find('#evors_form_section'),
				form = $('#evorsvp_form'),
				submission_form = form.find('.submission_form'),
				status_option_selection = obj.closest('.evoRS_status_option_selection'),
				repeat_interval = status_option_selection.attr('data-ri'),
				cap = status_option_selection.attr('data-cap'),
				status = obj.attr('data-val');

			rsvp_lightbox.show();

			// if event open as lightbox
				if(obj.closest('.evo_lightbox').length>0){
					obj.closest('.evo_lightbox').addClass('notfocus');
					$('body').removeClass('evo_overflow ');
				}

			// status of the RSVP
			rsvp_lightbox.find('.rsvp_status span').removeClass('set');
			rsvp_lightbox.find('.rsvp_status span[data-val='+status+']').addClass('set');

			rsvp_reposition(); // scroll to top 
			prefill_form(); // prefill forms
			
			// showing correct RSVP lightbox section
			if(status=='ch'){ // when changing rsvp
				rsvp_lightbox.find('.form_section').hide();
				rsvp_lightbox.find('.find_rsvp_to_change').show();
				rsvp_lightbox.find('.find_rsvp_to_change').find('input').val('');				
			
			// change rsvp for loggedin users
			}else if(status=='chu'){
				rsvp_lightbox.find('.form_section').hide();
				RSVP = obj.parent();
				
				var ajaxdataa = { };
				ajaxdataa['action']='the_ajax_evors_a8';
				ajaxdataa['uid']= RSVP.data('uid');
				ajaxdataa['eid']= RSVP.data('eid');
				ajaxdataa['ri']= RSVP.data('ri');

				var output = false;

				$.ajax({
					beforeSend: function(){	form.addClass('loading');},
					type: 'POST',
					url:evors_ajax_script.ajaxurl,
					data: ajaxdataa,
					dataType:'json',
					success:function(data){				
						if(data.status=='0'){ //good
							submission_form.slideDown();

							// all inputs
								$.each(data.content, function(i, val){
									// rsvp status
									if(i=='rsvp'){
										submission_form.find('.rsvp_status span[data-val='+val+']').addClass('set');

										// if rsvp status no
										hide_not_needs(val);

									// receive updates
									}else if(i=='updates' && val=='yes'){
										submission_form.find('.updates input').attr({'checked':'checked'});	
									}else if(i=='count'){
										submission_form.find('input[name=count]').attr({'data-pastval':val,'value':val});
									}else{	
										ITEM = submission_form.find('.input[name='+i+']');
										NOD = ITEM.prop('nodeName');
										if(NOD == 'SELECT'){
											ITEM.find('option[value='+val+']').attr('selected','selected');
										}else{
											ITEM.attr({'value':val});	
										}										
									}	
								});	

							// update rsvp ID to form parent
							form.parent().attr({'data-rsvpid':data.rsvpid, 'data-uid':RSVP.data('uid'), 'data-ri':RSVP.data('ri')});

							// make email uneditable
							submission_form.find('input[name=email]').attr('readonly', true);

						}else{	rsvp_error('err5');	}
					},complete:function(){	form.removeClass('loading');	}
				});
			}else{
				rsvp_lightbox.find('.form_section').hide();
				submission_form.show();
				hide_not_needs(status);
			}

			// show correct event name on the lightbox
				status_option_selection = (status_option_selection.length==0)?  obj.parent(): status_option_selection;

				var etitle = status_option_selection.attr('data-etitle');

				rsvp_lightbox.attr({'data-etitle': etitle, 'data-ri':repeat_interval });
				rsvp_lightbox.find('h3.form_header span').html(etitle);


			// pass the cal id to lightbox
				var cal_id = obj.closest('.ajde_evcal_calendar').attr('id');
				rsvp_lightbox.attr({'data-cal_id':cal_id, 
					'data-eid':status_option_selection.attr('data-eid'),
					'data-cap':cap,
					'data-precap':status_option_selection.attr('data-precap')
				});

			$('body').find('.evors_popup').fadeIn(300);
			//$('body').find('.evors_popbg').fadeIn(300);
		}

	// close lightbox RSVP form
		$('body').on('click', '#evors_form_close', function(){
			$(this).closest('.evors_popup').fadeOut(function(){
				$('.submission_form').show();
				$('.submission_form').find('input').val('');
				$('.find_rsvp_to_change').hide();
				$('.rsvp_confirmation').hide();
				$('.evors_form_section').attr({'data-rsvpid':''});
				reset_rsvp_form('full');
			});	
			
			// reset eventon lightbox if opened
				if($('.evo_lightbox').hasClass('notfocus'))
					$('.evo_lightbox').removeClass('notfocus');
				if($('.evo_lightbox').hasClass('show'))
					$('body').addClass('evo_overflow ');			
		});

	// lightbox RSVP form interactions
		// change RSVP status within the form
			$('body').on('click', '.evors_popup .rsvp_status span', function(){
				$(this).siblings().removeClass('set');
				$(this).addClass('set');
				var status = $(this).attr('data-val');
				hide_not_needs(status);
			});

		// hide /show not needed fields
			function hide_not_needs(status){
				if(status=='n'){
					$('body').find('.evors_popup .hide_no').hide();
					$('body').find('.additional_note').show();
				}else{
					$('body').find('.evors_popup .hide_no').show();
					$('body').find('.additional_note').hide();
				}
			}

		// look up RSVP for changing

	// RSVP from eventtop
		$('body').on('click', '.evors_eventtop_rsvp span', function(event){
			event.preventDefault();

			var obj = $(this),
				rsvp = obj.parent(),
				ajaxdataa = {};

			ajaxdataa['rsvp']= obj.data('val');				
			ajaxdataa['lang']= rsvp.data('lang'); 
			ajaxdataa['uid']= rsvp.data('uid'); 
			ajaxdataa['updates']= 'no';	
			ajaxdataa['action']='the_ajax_evors_a7';
			ajaxdataa['repeat_interval']=rsvp.data('ri');
			ajaxdataa['e_id']= rsvp.data('eid');
			
			$.ajax({
				beforeSend: function(){	rsvp.addClass('loading');	},
				type: 'POST',
				url:evors_ajax_script.ajaxurl,
				data: ajaxdataa,
				dataType:'json',
				success:function(data){
					if(data.status=='0'){
						rsvp.html(data.message).addClass('success');
					}else{
						rsvp.append('<span class="error">'+data.message+'</span>');
					}
				},complete:function(){
					rsvp.removeClass('loading');
				}
			});	
		});

	// RSVP form submissions & update existing
		$('body').on('click', '#submit_rsvp_form', function(){			

			var obj = $(this),
				ajaxdataa = { },
				form = obj.closest('.submission_form'),
				formSection = form.closest('.evors_form_section'),
				error = 0,
				formType = 'submit';

			// reset form error messages
				reset_rsvp_form();
				form.siblings('.notification').hide();
			// UPDATING existing rsvp form type
				var rsvpid = formSection.attr('data-rsvpid');
				if(typeof rsvpid !== 'undefined' && rsvpid!=''){
					ajaxdataa['rsvpid']=rsvpid;
					ajaxdataa['userid']=formSection.attr('data-uid');
					formType = 'update';
				}

			// validation
				// run through each rsvp field
					form.find('.input').each(function(index){
						// for drop down field
						ajaxdataa[ $(this).attr('name') ] = encodeURIComponent( $(this).val() );
						
						$(this).removeClass('err');

						// check required fields filled
						if( $(this).hasClass('req') && $(this).val()=='' && $(this).is(":visible")){
							error = 1;
							$(this).addClass('err');
						}
					});
				// validate email
					if(error==0){
						var thisemail = form.find('input[name=email]');
						if(!is_email(thisemail.val())){
							thisemail.addClass('err');
							rsvp_error('err2'); // invalid email address
							error = 2;
						}
					}	
				// capacity check
					if(error==0){
						if(formType=='update'){
							pastVal = parseInt(form.find('input[name=count]').attr('data-pastval'));
							newVal = parseInt(ajaxdataa['count']);
							compareCount = (pastVal>newVal)? 0: newVal-pastVal;
						}else{							
							compareCount =  parseInt(ajaxdataa['count']);
						}
						//console.log(newVal+' '+pastVal+' '+compareCount+' '+ parseInt(formSection.attr('data-cap')));
						if(form.find('.rsvp_status span.set').attr('data-val')!='n' 
							&& formSection.attr('data-cap')!='na' 
							&& compareCount > parseInt(formSection.attr('data-cap')) ){
							error=4;
							form.find('input[name=count]').addClass('err');
							rsvp_error('err9');
						}
						// max count limit
						if( formSection.attr('data-precap')!='na' && 
							(parseInt(form.find('input[name=count]').val()) > parseInt(formSection.attr('data-precap')) ) 
						){
							error=4;
							form.find('input[name=count]').addClass('err');
							rsvp_error('err10');
						}
					}			
				// validate human
					if(error==0){
						var human = rsvp_validate_human( form.find('input.captcha') );
						if(!human){
							error=3;
							rsvp_error('err6');
						}
					}				

			if(error==0){
				var updates = form.find('.updates input').attr('checked');
					updates = (updates=='checked')? 'yes':'no';

				ajaxdataa['rsvp']= form.find('.rsvp_status span.set').attr('data-val');				
				ajaxdataa['lang']= 'L1'; // pass current lang
				ajaxdataa['updates']= updates;// revieve updates
				ajaxdataa['action']='the_ajax_evors';
				ajaxdataa['repeat_interval']=formSection.attr('data-ri');
				ajaxdataa['e_id']= formSection.attr('data-eid');
				ajaxdataa['postnonce']= evors_ajax_script.postnonce;
								
				$.ajax({
					beforeSend: function(){	form.parent().addClass('loading');	},
					type: 'POST',
					url:evors_ajax_script.ajaxurl,
					data: ajaxdataa,
					dataType:'json',
					success:function(data){
						//console.log(ajaxdataa);
						if(data.status=='0'){
							form.slideUp('fast');
							var passedRsvppd = (data.message)? data.message: rsvpid ;
							rsvp_success(ajaxdataa, form,passedRsvppd ,formType);

							if(formType=='update') $('body').trigger('evors_update_rsvp');
						}else{
							var passedRsvppd = (data.status)? 'err'+data.status:'err7';
							rsvp_error(passedRsvppd, '', data.message);
						}
					},complete:function(){	form.parent().removeClass('loading');	}
				});				
			}else if(error==1){	rsvp_error('err');	}	
		});
	
	// CHANGE RSVP
		// Change RSVP from success message
			$('body').on('click','#call_change_rsvp_form',function(){
				var form = $(this).closest('.rsvp_confirmation');
				var rsvpid = form.attr('data-rsvpid');
				rsvp_change_form(form, rsvpid);	
			});
		// Find RSVP
			$('body').on('click', '#change_rsvp_form', function(){
				var obj = $(this);			
				var form = obj.closest('.find_rsvp_to_change');
				var error = 0;

				// run through each rsvp field
					form.find('.input').each(function(index){
						// check required fields filled
						if( $(this).hasClass('req') && $(this).val()=='' ){
							error = 1;
						}
					});							
				if(error=='1'){
					rsvp_error('err');
				}else{
					var rsvpid = form.find('input[name=rsvp_id]').val();
					rsvp_change_form(form, rsvpid);							
				}				
			});

	// rsvp manager
		$('body').on('evors_update_rsvp', function(){
			rsvplist = $('.eventon_rsvp_rsvplist');

			if(rsvplist.length == 0) return false;

			var ajaxdataa = { };
				ajaxdataa['action']='the_ajax_evors_a10';
				ajaxdataa['uid']= rsvplist.data('uid');

			$.ajax({
				beforeSend: function(){	rsvplist.addClass('loading');	},
				type: 'POST',
				url:evors_ajax_script.ajaxurl,
				data: ajaxdataa,
				dataType:'json',
				success:function(data){
					rsvplist.html(data.content);
				},complete:function(){	rsvplist.removeClass('loading');	}
			});	
		});

	// hover over guests list icons
		$('body').on('mouseover','.evors_whos_coming span', function(){
			name = $(this).attr('data-name');
			html = $(this).html();
			$(this).html(name).attr('data-intials', html).addClass('hover');
		});
		$('body').on('mouseout','.evors_whos_coming span', function(){
			$(this).html( $(this).attr('data-intials')).removeClass('hover');
		});

	// Supporting functions
		// show success message
			function rsvp_success(data, formObj, rsvpID, formType){
				var successform = formObj.siblings('.rsvp_confirmation');

				successform.find('.name').html( decodeURIComponent(data.first_name)+' '+ decodeURIComponent(data.last_name));

				// Update Vs submit
				successform.find('h3').hide();
				if(formType=='update'){
					successform.find('h3.update').show();
				}else{
					successform.find('h3.submit').show();
				}

				// not coming
				if(data.rsvp!='y'){
					successform.find('.coming').hide();
				}else{
					successform.find('.coming').show();
					var spots = (data.count!='')? data.count: 1;
					
					successform.find('.spots').html(spots);
					successform.find('p .eventName').html( successform.find('span.eventName').html() );
					successform.find('.email').html( decodeURIComponent(data.email));					
				}
				successform.show();
				successform.attr({'data-rsvpid':rsvpID});
				formObj.closest('.evors_form_section').attr({'data-rsvpid':rsvpID});
				rsvp_reposition();
			}
		// show error messages
			function rsvp_error(code, type, message){
				if(message == '' || message === undefined){
					var codes = JSON.parse($('body').find('.evors_msg_').html());
					var classN = (type== undefined || type=='error')? 'err':type;
					message = codes.codes[code]
				}
				//console.log(code+' '+message+' '+type+' '+classN);
				
				$('body').find('#evorsvp_form .notification').addClass(classN).show().find('p').html(message);
				$('body').find('.evors_popup').addClass('error');
			}
		// hide form messages
			function rsvp_hide_notifications(){
				$('body').find('#evorsvp_form .notification').hide();
			}		
		// validate humans
			function rsvp_validate_human(field){
				if(field==undefined){
					return true;
				}else{
					var numbers = ['11', '3', '6', '3', '8'];
					if(numbers[field.attr('data-cal')] == field.val() ){
						return true;
					}else{ return false;}
				}				
			}
		// bring up change RSVP form
			function rsvp_change_form(form, rsvpid){
				rsvp_hide_notifications();
				var ajaxdataa = { };
				ajaxdataa['action']='the_ajax_evors_fnd';
				ajaxdataa['e_id']= form.closest('.evors_form_section').attr('data-eid');
				ajaxdataa['rsvpid']= rsvpid;
				ajaxdataa['postnonce']= evors_ajax_script.postnonce;

				var output = false;

				$.ajax({
					beforeSend: function(){
						form.parent().addClass('loading');
					},
					type: 'POST',
					url:evors_ajax_script.ajaxurl,
					data: ajaxdataa,
					dataType:'json',
					success:function(data){				
						if(data.status=='0'){ //good
							form.slideUp(function(){
							form.siblings('.submission_form').slideDown();
								// fill in saved information
								var new_form = form.siblings('.submission_form');
								$('.evors_popup').removeClass('error');

								// all inputs
								$.each(data.content, function(i, val){
									// rsvp status
									if(i=='rsvp'){
										new_form.find('.rsvp_status span[data-val='+val+']').addClass('set');
									// receive updates
									}else if(i=='updates' && val=='yes'){
										new_form.find('.updates input').attr({'checked':'checked'});
									}else if(i=='count'){
										new_form.find('input[name=count]').attr({'data-pastval':val,'value':val});
									}else{
										ITEM = new_form.find('.input[name='+i+']');
										NOD = ITEM.prop('nodeName');
										if(NOD == 'SELECT'){
											ITEM.find('option[value='+val+']').attr('selected','selected');
										}else{
											ITEM.attr({'value':val});	
										}
									}									
								});	

								// update rsvp ID to form parent
								form.closest('.evors_form_section').attr({'data-rsvpid':rsvpid});
								
							});					
						}else{
							rsvp_error('err5');
						}
					},complete:function(){
						form.parent().removeClass('loading');
					}
				});
			}

		// reposition lightbox window
			function rsvp_reposition(){
				var cur_window_top = parseInt($(window).scrollTop()) + 50;
				$('.evors_popup').css({'margin-top':cur_window_top});
			}

		// reset form parts
			function reset_rsvp_form(type){
				FORM = $('body').find('#evorsvp_form');
				$('body').find('.evors_popup').removeClass('error');

				FORM.find('input').removeAttr('readonly');
				FORM.find('.notification').hide();
				FORM.find('input[name=count]').removeAttr('data-pastval');
				if(type=='full') FORM.find('select.input option').removeAttr('selected');
			}
		// prefill form fields
			function prefill_form(){
				FORM = $('body').find('#evorsvp_form');
				PREFILLBLOCK = FORM.parent().data('prefillblock');
				PREFILLBLOCK = PREFILLBLOCK=='yes'?true:false;

				userdata = FORM.find('.evorsvp_loggedin_user_data');
				if(typeof userdata.attr('data-uid') !== 'undefined' && userdata.attr('data-uid')!=''){				
					FORM.find('input[name=first_name]').val(userdata.attr('data-fname'));
						if(userdata.attr('data-fname')!='' && PREFILLBLOCK) FORM.find('input[name=first_name]').attr('readonly',true);
					FORM.find('input[name=last_name]').val(userdata.attr('data-lname'));
						if(userdata.attr('data-lname')!='' && PREFILLBLOCK) FORM.find('input[name=last_name]').attr('readonly',true);
					FORM.find('input[name=email]').val(userdata.attr('data-email'));
						if(userdata.attr('data-email')!='' && PREFILLBLOCK) FORM.find('input[name=email]').attr('readonly',true);
				}
			}

	function is_email(email){
		var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  		return regex.test(email);
	}
});