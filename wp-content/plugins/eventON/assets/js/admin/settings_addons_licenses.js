/*
*	Eventon Settings tab - addons and licenses
*	@version: 2.4
*/

jQuery(document).ready(function($){

	init();

	// load addon details
		function init(){

			var obj = $('#evo_addons_list');
			var data_arg = {
				action:'eventon_get_addons_list',
			};

			$.ajax({
				beforeSend: function(){	},
				type: 'POST',
				url:the_ajax_script.ajaxurl,
				data: data_arg,
				dataType:'json',
				success:function(data){				
					obj.html(data.content);
				}
			});

			/* - remote test
				var data_arg_2 = {
					action:'eventon_remote_test',
					slug:'eventon',
				};
				$.ajax({
					beforeSend: function(){	show_pop_loading();	},
					type: 'POST',
					url:the_ajax_script.ajaxurl,
					data: data_arg_2,
					dataType:'json',
					success:function(data){	
						console.log(data);
					}
				});
				*/

				/* - remote get addons
				var content = 'text';
				var JSONurl = 'http://get.myeventon.com/addons/';
				$.getJSON(JSONurl, function(dataJ){
					// if validated remotely			
					$.each( dataJ, function( key, val ) {
						console.log(val);
					    content += val;
					});
				});
				obj.html(content);
			*/
		}

	// License Verification for EventON
		$('body').on('click','.eventon_submit_license',function(){
			
			$('.ajde_popup').find('.message').removeClass('bad good');
			
			var parent_pop_form = $(this).parent().parent();
			var license_key = parent_pop_form.find('.eventon_license_key_val').val();
			
			if(license_key==''){
				show_pop_bad_msg('License key can not be blank! Please try again.');
			}else{
				
				var slug = parent_pop_form.find('.eventon_slug').val();	
				var error = false;			
				
				// validate key format
					var data_arg = {
						action:'eventon_validate_license',
						key:license_key,
						type:'main'
					};
					$.ajax({
						beforeSend: function(){	show_pop_loading();	},
						type: 'POST',
						url:the_ajax_script.ajaxurl,
						data: data_arg,
						dataType:'json',
						success:function(data){						

							// if valid license format
							if(data.status=='good'){
								var data_arg_2 = {
									action:'eventon_get_license_api_url',
									key:license_key,
									slug:'eventon',
								};
								$.ajax({
									beforeSend: function(){},
									type: 'POST',
									url:the_ajax_script.ajaxurl,
									data: data_arg_2,
									dataType:'json',
									success:function(dataV){
										// GET json license information
										$.getJSON(dataV.json_url, function(dataJ){
											
											// Actual license present
											if(dataJ['verify-purchase'].created_at != '' ){
												// update remote validity			
												var data_arg_3 = {
													action:'eventon_remote_validity',
													remote_validity:dataJ['verify-purchase'].created_at,
													buyer: dataJ['verify-purchase'].buyer,
													slug:'eventon',
													key: license_key
												};
												$.ajax({
													beforeSend: function(){},
													type: 'POST',
													url:the_ajax_script.ajaxurl,
													data: data_arg_3,
													dataType:'json',
													success:function(data3){
														show_pop_good_msg('<span class="EVOcheckmark"></span> Excellent! Purchase key verified and saved. Thank you for activating EventON!');
														$('.ajde_popup').delay(3000).queue(function(n){
															$(this).animate({'margin-top':'70px','opacity':0}).fadeOut();
															$('#ajde_popup_bg').fadeOut();
															location.reload();
															n();
														});													
													}
												});
											}else{
												show_pop_good_msg('Envato license does not have a valid purchase information.');
											}										
										});
										
									},complete:function(){	hide_pop_loading();	}
								});
							}else{
								error =true;
								show_pop_bad_msg(data.error_msg);
								hide_pop_loading();
							}
						},complete:function(){	}
					});	
			}
		});

	// License Verification for EventON Addons
		$('body').on('click','.eventonADD_submit_license',function(){			
			$('.ajde_popup').find('.message').removeClass('bad good');
			
			var parent_pop_form = $(this).parent().parent();
			var license_key = parent_pop_form.find('.eventon_license_key_val').val();
			var email = parent_pop_form.find('.eventon_email_val').val();
			var instance = parent_pop_form.find('.eventon_index_val').val();
			
			if(license_key=='' || email==''){
				show_pop_bad_msg('All the fields must be filled, please try again!');
			}else{
				
				var slug = parent_pop_form.find('.eventon_slug').val();
				var id = parent_pop_form.find('.eventon_id').val();	
				var error = false;		
				
				// validate key format
					var data_arg = {
						action:'eventon_validate_license',
						key:license_key,
						type:'addon'
					};
					$.ajax({
						beforeSend: function(){	show_pop_loading();	},
						type: 'POST',
						url:the_ajax_script.ajaxurl,
						data: data_arg,
						dataType:'json',
						success:function(data){
							// if valid license format verify it
							if(data.status=='good'){
								var data_arg_2 = {
									action:'eventon_verify_key',
									key:license_key,
									slug:slug,
									product_id:id,
									email:email,
									instance:instance,
								};
								$.ajax({
									beforeSend: function(){},
									type: 'POST',
									url:the_ajax_script.ajaxurl,
									data: data_arg_2,
									dataType:'json',
									success:function(data){
										if(data.status=='success'){
											var box_o = parent_pop_form.find('.eventon_license_div').val();
											var box = $('#'+box_o);

											box.find('.status').html(data.content);
											
											show_pop_good_msg('Woo hoo! License key verified and saved. '+data.addition_msg);
											$('.ajde_popup').delay(4000).queue(function(n){
												$(this).animate({'margin-top':'70px','opacity':0}).fadeOut();
												$('#ajde_popup_bg').fadeOut();
												n();
											});

											box.find('.action').hide();
											box.find('.activation_text').html('Bravo! This addon is now activated!');
											box.addClass('justactivate'); // colorize the newly activated box
											
										}else{
											show_pop_bad_msg(data.error_msg);
										}
									},complete:function(){	hide_pop_loading();	}
								});
							}else{
								error =true;
								show_pop_bad_msg(data.error_msg);
								hide_pop_loading();
							}
						},complete:function(){	}
					});	
			}
		});
	
	// deactivate eventon products
		// eventon
		$('.evo_addons_page').on('click', '#evoDeactLic', function(){			
			var data_arg = {	action:'eventon_deactivate_lic',	};	
			$.ajax({
				beforeSend: function(){
					$('.evo_addons_page').find('.addon.main').css({'opacity':'0.2'});
				},
				type: 'POST',
				url:the_ajax_script.ajaxurl,
				data: data_arg,
				dataType:'json',
				success:function(data){
					//console.log(data);
					if(data.status=='success'){	location.reload();						
					}else{	alert(data.error_msg);	}	
				},complete:function(){
					$('.evo_addons_page').find('.addon.main').css({'opacity':'1'});
				}
			});
		});
		// addon
		$('body').on('click', '.evo_deact_adodn',function(){
			var addon = $(this).closest('.addon');

			var data_arg = {
				action:'eventon_deactivate_addon',
				key:addon.attr('data-key'),
				slug:addon.attr('data-slug'),
				email:addon.attr('data-email'),
				product_id:addon.attr('data-product_id'),
			};
			$.ajax({
				beforeSend: function(){},
				type: 'POST',
				url:the_ajax_script.ajaxurl,
				data: data_arg,
				dataType:'json',
				success:function(data){
					if(data.status=='success'){
						
						addon.find('.status').html(data.content);
						
						show_pop_good_msg('Successfully deactivated addon.');
						$('.ajde_popup').delay(3000).queue(function(n){
							$(this).animate({'margin-top':'70px','opacity':0}).fadeOut();
							$('#ajde_popup_bg').fadeOut();
							n();
						});
						addon.removeClass('activated'); 
						
					}else{
						show_pop_bad_msg(data.error_msg);
					}
				},complete:function(){	hide_pop_loading();	}
			});
		});
		
	// popup lightbox functions
		function show_pop_bad_msg(msg){
			$('.ajde_popup').find('.message').removeClass('bad good').addClass('bad').html(msg).fadeIn();
		}
		function show_pop_good_msg(msg){
			$('.ajde_popup').find('.message').removeClass('bad good').addClass('good').html(msg).fadeIn();
		}
		
		function show_pop_loading(){
			$('.ajde_popup_text').css({'opacity':0.3});
			$('#ajde_loading').fadeIn();
		}
		function hide_pop_loading(){
			$('.ajde_popup_text').css({'opacity':1});
			$('#ajde_loading').fadeOut(20);
		}
});