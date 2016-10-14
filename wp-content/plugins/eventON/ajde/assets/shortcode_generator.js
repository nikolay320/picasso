/**
 * Shortcode Generator
 * @version 0.1
 */
jQuery(document).ready(function($){

 	var CODE = 'ajde';

	ajdePOSH_go_back();

	var shortcode;
	var shortcode_vars = [];
	var shortcode_keys = new Array();
	var ss_shortcode_vars = new Array();

	// click on each main step
		$('#'+CODE+'POSH_outter').on('click','.'+CODE+'POSH_btn',function(){
			var obj = $(this);
			var section = obj.attr('step2');
			var code = obj.attr('code');
			var section_name = obj.html();			

			// no 2nd step
			if(obj.hasClass('nostep') ){
				$('#'+CODE+'POSH_code').html('['+code+']').attr({'data-curcode':code});
			}else{
				var pare = obj.parent().parent();
				pare.find('.step2').show();
				pare.find('#'+section).show();
				$('.'+CODE+'POSH_inner').animate({'margin-left':'-470px'});
				
				ajdePOSH_show_back_btn();
				
				$('#'+CODE+'POSH_code').html('['+code+']').attr({'data-curcode':code});
				$('#'+CODE+'POSH_subtitle').html(section_name).attr({'data-section':section_name});
			}
		});
	// show back button
		function ajdePOSH_show_back_btn(){
			$('#'+CODE+'POSH_back').animate({'left':'0px'});		
			$('h3.notifications').addClass('back');

		}
	// go back button on the shortcode popup
		function ajdePOSH_go_back(){
			$('#'+CODE+'POSH_back').click(function(){		
				$(this).animate({'left':'-20px'},'fast');	
				
				$('h3.notifications').removeClass('back');
			
				$('.'+CODE+'POSH_inner').animate({'margin-left':'0px'}).find('.step2_in').fadeOut();
				
				// hide step 2
				$(this).closest('#'+CODE+'POSH_outter').find('.step2').fadeOut();

				// clear varianles
				shortcode_vars=[];
				shortcode_vars.length=0;

				var code_to_show = $('#'+CODE+'POSH_code').data('defsc');
				$('#'+CODE+'POSH_code')
					.html('['+code_to_show+']')
					.attr({'data-curcode':code_to_show});

				// change subtitle
				$('#'+CODE+'POSH_subtitle').html( $('#'+CODE+'POSH_subtitle').data('bf') );
			});
		}	
	
	// yes no buttons
		$('.'+CODE+'POSH_inner').on('click','.ajde_yn_btn', function(){

			console.log('t');

			var obj = $(this);
			var codevar = $(this).attr('codevar');
			var value;
			
			if(obj.hasClass('NO')){
				//obj.removeClass('NO');	
				value = 'yes';
			}else{
				//obj.addClass('NO');	
				value = 'no';
			}
			
			ajdePOSH_update_codevars(codevar,value);
			report_select_steps_( obj, codevar );

			ajdePOSH_update_shortcode();
		});

	
	// input and select fields
	$('.'+CODE+'POSH_inner').on('change','.'+CODE+'POSH_input, .'+CODE+'POSH_select', function(){
		
		var obj = $(this);
		var value = obj.val();
		var codevar = obj.attr('codevar');
		
		if(value!='' && value!='undefined'){			
			ajdePOSH_update_codevars(codevar,value);
			ajdePOSH_update_shortcode();
		}else if(!value){
			ajdePOSH_remove_codevars(codevar);			
		}		
	});
	
	// afterstatements within shortcode gen
		$('.ajdePOSH_inner').on('click', '.trig_afterst',function(){
			$(this).next('.ajde_afterst').toggle();
		});
	
	
	// SELECT STEP within 2ns step field
		$('.'+CODE+'POSH_inner').on('change','.'+CODE+'POSH_select_step', function(){
			var value = $(this).val();
			var codevar = $(this).data('codevar');
			var this_id = '#'+value;

			$(this_id).siblings('.ajde_open_ss').hide();
			$(this_id).delay(300).show();

			// first time selecting
			if(!$(this).hasClass('touched') ){
				$(this).attr({'data-cur_sc': $('#'+CODE+'POSH_code').html() })
					.addClass('touched');
			}else{
				var send_code = $(this).data('cur_sc'); // send the code before selecting select step
				remove_select_step_vals();
				$(this).removeClass('touched');
			}

			// update the current shortcode based on selection
			if(value!='' && value!='undefined'){			
				ajdePOSH_update_codevars(codevar,value);
			}else if(!value){
				ajdePOSH_remove_codevars(codevar);			
			}
			if(value=='ss_1'){
				ajdePOSH_remove_codevars(codevar);
			}

		});

		// RECORD step codevar for each select steps
		function report_select_steps_(obj, codevar){
			// ONLY SELECT STEP
			if( obj.closest('.fieldline').hasClass('ss_in')){
				if(ss_shortcode_vars.indexOf(codevar)==-1){
					ss_shortcode_vars.push(codevar);
				}
			}		
		}
		function remove_select_step_vals(){
			if(ss_shortcode_vars.length>0){
				for (var i=0;i<ss_shortcode_vars.length;i++){
					var this_code = ss_shortcode_vars[i];
					ajdePOSH_remove_codevars(this_code);
					//delete ss_shortcode_vars[i];
				}
			}
			ss_shortcode_vars=[];			
		}
	
	// update shortcode based on new selections
		function ajdePOSH_update_shortcode(){
			
			var el = $('#'+CODE+'POSH_code');
			var string = el.data('curcode')+' ';
			
			if(shortcode_vars.length==0){
				string=string;
			}else{
				$.each( shortcode_vars, function( key, value ) {
					string += value.code+'="'+value.val+'" ';
				});			
			}
			
			// update the shortcode attr on insert button
			var stringx = '['+string+']';
			el.html(stringx).attr({'data-curcode': string});
		}
	
	// UPDATE or ADD new shortcode variable to obj
		function ajdePOSH_update_codevars(codevar,value){		
			
			if(shortcode_keys.indexOf(codevar)>-1 
				&& shortcode_vars.length>0){
				$.each( shortcode_vars, function( key, arr ) {
					if(arr && arr.code==codevar){
						shortcode_vars[key].val=value;
					}
				});
			}else{
				var obj = {'code': codevar,'val':value};
				//shortcode_vars[codevar] = obj;
				shortcode_vars.push(obj);
				shortcode_keys.push(codevar);
			}
			ajdePOSH_update_shortcode();
		}

	// REMOVE a shortcode variable to object
		function ajdePOSH_remove_codevars(codevar){
			
			// remove from main object
			$.each( shortcode_vars, function( key, arr ) {
				if(arr.code==codevar){
					shortcode_vars.splice(key, 1);
				}
			});

			//remove from keys
			var index = shortcode_keys.indexOf(codevar);
			if(index>-1){
				shortcode_keys.splice(index, 1);
			}
			ajdePOSH_update_shortcode();
		}
	
	// insert code into text editor
		$('body').on('click','.'+CODE+'POSH_insert',function(){
			var obj = $(this);
			var shortcode = obj.siblings('#'+CODE+'POSH_code').html();	

			// if shortcode insert textbox id given
			var textbox = obj.closest('.ajde_popup').attr('data-textbox');
			
			if(textbox === undefined){
				tinymce.activeEditor.execCommand('mceInsertContent', false, shortcode);	
			}else{
				$('#'+textbox).html(shortcode);
			}			
			hide_popupwindowbox();
		});

		function hide_popupwindowbox(){
			var container=$('#'+CODE+'POSH_outter').parent().parent();
			var clear_content = container.attr('clear');
			
			if(container.hasClass('active')){
				container.animate({'margin-top':'70px','opacity':0},300).fadeOut().
					removeClass('active')
					.delay(300)
					.queue(function(n){
						if(clear_content=='true')					
							$(this).find('.ajde_popup_text').html('');							
						n();
					});
				container.parent().next().fadeOut();
				popup_open = false;					
			}
		}
});