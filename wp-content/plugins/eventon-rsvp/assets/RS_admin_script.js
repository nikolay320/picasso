/**
 * JS for RSVP admin section 
 * @version  1.5
 */
jQuery(document).ready(function($){
	
	init();	
		
	// INITIATE script
	function init(){}

	// GET attendee list
		$('#evors_VA').on('click',function(){

			RIACTIVE = $(this).data('riactive');
			if(RIACTIVE =='no'){
				var data_arg = {
					action: 		'the_ajax_evors_a1',
					e_id:			$(this).data('e_id'),
					postnonce: evors_admin_ajax_script.postnonce, 
				};
				console.log(data_arg);			
				$.ajax({
					beforeSend: function(){},
					type: 'POST',
					url:evors_admin_ajax_script.ajaxurl,
					data: data_arg,
					dataType:'json',
					success:function(data){
						//alert(data);
						if(data.status=='0'){
							$('.evors_lightbox ').find('.ajde_popup_text').html(data.content);
							$('.evors_lightbox').find('.evors_list span.checkin').click(function(){
								var obj = $(this);
								checkin_attendee(obj);
							});
						}else{
							$('.evors_lightbox ').find('.ajde_popup_text').html('Could not load attendee list');
						}
					},complete:function(){}
				});
			}else{
				$('body').find('#evors_view_attendees_list').html('').hide();
			}
		});
		$('body').on('click','#evors_VA_submit',function(){
			var data_arg = {
				action: 		'the_ajax_evors_a1',
				e_id:			$(this).data('e_id'),
				ri: $('#evors_event_repeatInstance').val(),
				postnonce: evors_admin_ajax_script.postnonce, 
			};
			//console.log(data_arg);			
			$.ajax({
				beforeSend: function(){ $('#evors_view_attendees').addClass('loading'); },
				type: 'POST',
				url:evors_admin_ajax_script.ajaxurl,
				data: data_arg,
				dataType:'json',
				success:function(data){
					//alert(data);
					if(data.status=='0'){
						$('body').find('#evors_view_attendees_list').html(data.content).slideDown();
						$('.evors_lightbox').find('.evors_list span.checkin').click(function(){
							var obj = $(this);
							checkin_attendee(obj);
						});
					}else{
						$('.evors_lightbox ').find('.ajde_popup_text').html('Could not load attendee list');
					}
				},complete:function(){ $('#evors_view_attendees').removeClass('loading');}
			});	
		});
	// show rest of attendees
		$('body').on('click','.evors_repeats_showrest',function(){
			$(this).parent().find('p').removeClass('hidden');
		});
	// Sync attendees count
		$('#evors_SY').on('click',function(){

			var obj = $(this);
			var data_arg = {
				action: 		'the_ajax_evors_a2',
				e_id:			$(this).data('e_id'),
				postnonce: evors_admin_ajax_script.postnonce, 
			};
			//console.log(data_arg);			
			$.ajax({
				beforeSend: function(){
					$('#evors_message').html('Loading..').show();
				},
				type: 'POST',
				url:evors_admin_ajax_script.ajaxurl,
				data: data_arg,
				dataType:'json',
				success:function(data){
					//alert(data);
					if(data.status=='0'){
						obj.closest('.evors_details').find('.evors_stats').html(data.content);
						$('#evors_message').fadeOut();
					}else{
						$('#evors_message').html('Could not sync attendance at this moment. Please try again later').show();
						$('#evors_message').delay(3000).fadeOut();
					}
				},complete:function(){}
			});

		});

	// Emailing for RSVP
		$('body').on('change','#evors_emailing_options',function(){
			VAL = $(this).find(":selected").attr('value');
			if(VAL!='someone'){
				$('#evors_emailing').find('p.text').hide();
			}else{
				$('#evors_emailing').find('p.text').show();
			}
		});
		$('body').on('click','#evors_email_submit', function(){
			var obj = $(this);
			var data_arg = {
				action: 		'the_ajax_evors_a9',
				eid:			$(this).attr('data-eid'),
				type:			$('#evors_emailing_options').val(),
				emails:			$('#evors_emailing .text input').val(),
				subject:		$('#evors_emailing .subject input').val(),
				message:		$('#evors_emailing .textarea textarea').val(),
				repeat_interval:$('#evors_emailing_repeat_interval').val(),
			};	

			if(data_arg.subject == '' ){
				obj.closest('.ajde_lightbox_outter').siblings('.message').addClass('bad').html('Required Fields Missing').show();
			}else{
				obj.closest('.ajde_lightbox_outter').siblings('.message').hide();
				$.ajax({
					beforeSend: function(){
						$('#evors_emailing').addClass('loading');
					},
					type: 'POST',
					url:evors_admin_ajax_script.ajaxurl,
					data: data_arg,
					dataType:'json',
					success:function(data){
						//console.log(data);
						if(data.status=='0'){
							obj.closest('.ajde_lightbox_outter').siblings('.message').addClass('good').html('Emails Sent').show();
						}else{
							obj.closest('.ajde_lightbox_outter').siblings('.message').addClass('bad').html('Could not send emails. Try again later.').show();
						}
					},complete:function(){$('#evors_emailing').removeClass('loading');}
				});
			}	
		});

	// Default build in checkin
	// CHECK in attendees
		function checkin_attendee(obj){

			var status = obj.attr('data-status');

			status = (status=='' || status=='check-in')? 'checked':'check-in';

			var data_arg = {
				action: 'the_ajax_evors_a4',
				rsvp_id: obj.data('id'),
				status:  status
			};
			$.ajax({
				beforeSend: function(){
					obj.parent().animate({'opacity':'0.3'});
				},
				type: 'POST',
				url:evors_admin_ajax_script.ajaxurl,
				data: data_arg,
				dataType:'json',
				success:function(data){
					//alert(data);
					if(data.status=='0'){
						obj.attr({'data-status':status}).html(status).removeAttr('class').addClass(status+' checkin');
					}
				},complete:function(){
					obj.parent().animate({'opacity':'1'});
				}
			});
		}
	// check in attendee from rsvp edit page
		$('#evors_rsvp_tb').on('click','.rsvp_ch_st',function(){
			var obj = $(this);
			var status = obj.attr('data-status');

			status = (status=='' || status=='check-in')? 'checked':'check-in';

			var data_arg = {
				action: 'the_ajax_evors_a4X',
				rsvp_id: obj.attr('data-rsvpid'),
				status:  status
			};
			$.ajax({
				beforeSend: function(){
					obj.html( obj.html()+'...');
				},
				type: 'POST',
				url:evors_admin_ajax_script.ajaxurl,
				data: data_arg,
				dataType:'json',
				success:function(data){
					//alert(data);
					obj.attr({'data-status':status}).html(data.new_status_lang).removeAttr('class').addClass(status+' rsvp_ch_st');
				}
			});
		});

	// resend confirmation email
		$('#evoRS_resend_email').on('click',function(){
			var obj = $(this);
			
			var data_arg = {
				action: 'the_ajax_evors_a5',
				rsvp_id: obj.data('rsvpid'),
			};
			$.ajax({
				beforeSend: function(){
					obj.closest('.evoRS_resend_conf').addClass('loading');
				},
				type: 'POST',
				url:evors_admin_ajax_script.ajaxurl,
				data: data_arg,
				dataType:'json',
				success:function(data){
					//alert(data);
					if(data.status=='0'){
						obj.siblings('.message').fadeIn().delay(5000).fadeOut();
					}
				},complete:function(){
					obj.closest('.evoRS_resend_conf').removeClass('loading');
				}
			});
		});

		$('#evoRS_custom_email').on('click',function(){
			var obj = $(this);
			var _email = obj.parent().find('input').val();
			
			var data_arg = {
				action: 'the_ajax_evors_a6',
				email: _email,
				rsvp_id: obj.data('rsvpid'),
			};

			if( _email===undefined || _email=='' ){
				obj.siblings('.message').addClass('error').html( obj.data('empty')).show().delay(5000).fadeOut(function(){
					$(this).removeClass('error');
				});
			}else{
				$.ajax({
					beforeSend: function(){
						obj.closest('.evoRS_resend_conf').addClass('loading');
					},
					type: 'POST',
					url:evors_admin_ajax_script.ajaxurl,
					data: data_arg,
					dataType:'json',
					success:function(data){
						//alert(data);
						if(data.status=='0'){
							obj.siblings('.message').fadeIn().delay(5000).fadeOut();
						}

					},complete:function(){
						obj.closest('.evoRS_resend_conf').removeClass('loading');
					}
				});
			}
		});
	function is_email(email){
		var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  		return regex.test(email);
	}
});