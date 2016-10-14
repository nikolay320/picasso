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
			form_msg = 	$('.evore_msg_');		
			$('.eventon_events_list').on('click','.new_review_btn',function(){
				open_review_lightbox($(this));
			});	
			$('body').on('click','.new_review_btn',function(){
				open_review_lightbox($(this));
			});
			$('body').on('click','.new_review_btn',function(){
				open_review_lightbox($(this));
			});
		}

	// Review lightbox set up
		var popupcode = "<div class='evore_popup' style='display:none'></div>";
		$('body').append(popupcode);
		var evors_inside = $('body').find('#evore_get_form').html();
		$('body').find('.evore_popup').html(evors_inside);
		$('body').find('#evore_get_form').remove();

	// open lightbox review form
		function open_review_lightbox(obj){
			var review_lightbox = $('body').find('#evore_form_section');
				form = $('#evore_form'),
				formIn = form.find('.review_submission_form'),
				submission_form = form.find('.submission_form'),
				review_row = obj.closest('.evo_metarow_review'),
				repeat_interval = review_row.attr('data-ri'),
				eventid = review_row.attr('data-event_id');

			// if event open as lightbox
				if(obj.closest('.evo_lightbox').length>0){
					obj.closest('.evo_lightbox').addClass('notfocus');
					$('body').removeClass('evo_overflow ');
				}

			// update review related data to popup
			formIn.attr({'data-event_id':eventid,'data-ri':repeat_interval, 'data-lang':review_row.data('lang') });	

			// fill the review form for logged in user
				formIn.find('input[name=name]').val(obj.attr('data-username'));
				formIn.find('input[name=email]').val(obj.attr('data-useremail'));				

			// event title
				formIn.find('.eventName').html(obj.attr('data-eventname'));

			review_lightbox.show();
			review_reposition();
			
			$('body').find('.evore_popup').fadeIn(300);
		}

	// close lightbox RSVP form
		$('body').on('click', '#evore_form_close', function(){
			$(this).closest('.evore_popup').fadeOut(function(){
				$('.submission_form').show();
				$('.submission_form').find('input').val('');
				$('.find_review_to_change').hide();
				$('.review_confirmation').hide();
				$('.evore_form_section').attr({'data-reviewid':''});
				
				reset_review_form();
			});	

			// reset eventon lightbox if opened
				if($('.evo_lightbox').hasClass('notfocus'))
					$('.evo_lightbox').removeClass('notfocus');
				if($('.evo_lightbox').hasClass('show'))
					$('body').addClass('evo_overflow ');		
		});

	// lightbox review form interactions
		// Star rating change
			$('body').on('click', '.star_rating span', function(){
				rating = $(this).data('value');
				$(this).parent().find('span').removeClass('fa-star fa-star-half-full fa-star-o');

				$(this).addClass('fa-star');
				$(this).prevAll().addClass('fa-star');
				$(this).nextAll().addClass('fa-star-o');
				$(this).siblings('input').attr('value',rating);
			});
	
	// NEW review form submissions & update existing
		$('body').on('click', '#submit_review_form', function(){			

			var obj = $(this),
				ajaxdataa = { },
				form = obj.closest('.review_submission_form'),
				formSection = obj.closest('.evore_form_section'),
				error = 0;

			// reset form error messages
				form.siblings('.notification').hide();
				$('body').find('.evore_popup').removeClass('error');

			// validation
				// run through each review field
					form.find('.input').each(function(index){
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
						var thisemail = form.find('.inputemail');
						var emailVal = thisemail.val();

						if(emailVal!=''){						
							if( !is_email( emailVal )){
								thisemail.addClass('err');
								review_error('err2'); // invalid email address
								error = 2;
							}
						}
					}	

				// validate human
					if(error==0){
						var human = review_validate_human( form.find('input.captcha') );
						if(!human){
							error=3;
							review_error('err6');
						}
					}				

			if(error==0){
				var updates = form.find('.updates input').attr('checked');
					updates = (updates=='checked')? 'yes':'no';

				ajaxdataa['action']='the_ajax_evore';
				ajaxdataa['repeat_interval']= form.attr('data-ri');
				ajaxdataa['e_id']= form.attr('data-event_id');
				ajaxdataa['lang']= form.attr('data-lang');
				ajaxdataa['postnonce']= the_ajax_script.postnonce;
								
				$.ajax({
					beforeSend: function(){	form.parent().addClass('loading');	},
					type: 'POST',
					url:the_ajax_script.ajaxurl,
					data: ajaxdataa,
					dataType:'json',
					success:function(data){
						//console.log(ajaxdataa);
						if(data.status=='0'){
							form.slideUp('fast');
							review_success(ajaxdataa, form);
						}else{
							var passedReview = (data.status)? 'err'+data.status:'err7';
							review_error(passedReview);
						}
					},complete:function(){	form.parent().removeClass('loading');	}
				});				
			}else if(error==1){	review_error('err');	}	
		});
	
	// scroll through reviews
		$('body').on('click','.review_list_control span', function(){

			var obj = $(this),
				dir = obj.data('dir'),
				count = obj.parent().data('revs'),
				list = obj.parent().siblings('.review_list'),
				currentitem = list.find('.show'),
				previtem = currentitem.prev(),
				nextitem = currentitem.next();

				list.find('p').removeClass('show');

			if(dir=='next'){
				if(nextitem.length>0){
					nextitem.addClass('show');
				}else{					
					list.find('p').eq(0).addClass('show');
				}				
			}else{
				if(previtem.length>0){
					previtem.addClass('show');
				}else{	
					cnt = ((list.find('p').length)-1);				
					list.find('p').eq( cnt).addClass('show');
				}
			}
		});
	// open additional rating data 
		$('body').on('click','.orating .extra_data',function(){
			$(this).parent().siblings('.rating_data').slideToggle();
		});

	// Supporting functions
		// show success message
			function review_success(data, formObj){
				var successform = formObj.siblings('.review_confirmation');
				successform.show();
				review_reposition();
			}
		// show error messages
			function review_error(code, type){
				var codes = JSON.parse($('body').find('.evore_msg_').html());
				var classN = (type== undefined || type=='error')? 'err':type;
				$('body').find('#evore_form .notification').addClass(classN).show().find('p').html(codes.codes[code]);
				$('body').find('.evore_popup').addClass('error');
			}
		// hide form messages
			function review_hide_notifications(){
				$('body').find('#evore_form .notification').hide();
			}		
		// validate humans
			function review_validate_human(field){
				if(field==undefined){
					return true;
				}else{
					var numbers = ['11', '3', '6', '3', '8'];
					if(numbers[field.attr('data-cal')] == field.val() ){
						return true;
					}else{ return false;}
				}				
			}

		// reposition lightbox window
			function review_reposition(){
				var cur_window_top = parseInt($(window).scrollTop()) + 50;
				$('.evore_popup').css({'margin-top':cur_window_top});
			}

		// reset form parts
			function reset_review_form(type){
				FORM = $('body').find('#evore_form');
				$('body').find('.evore_popup').removeClass('error');

				FORM.find('.notification').hide();
				FORM.find('input').val('');
				FORM.find('input[name=rating]').val('1');
				FORM.find('textarea').val('');
				FORM.find('.star_rating span').attr({'class':'fa fa-star-o'});
				FORM.find('.star_rating span').eq(0).attr({'class':'fa fa-star'});
			}
		

	function is_email(email){
		var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  		return regex.test(email);
	}
});