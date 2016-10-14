/*
 * all wp-admin scripts for ajde library
 * @version 0.1
 */
jQuery(document).ready(function($){

	// self hosted tooltips
		$('body').find('.ajdethistooltip').each(function(){
			tipContent = $(this).find('.ajdeToolTip em').html();
			toolTip = $(this).find('.ajdeToolTip');
			classes = toolTip.attr('class').split('ajdeToolTip');
			toolTip.remove();
			$(this).append('<em>' +tipContent +'</em>').addClass(classes[1]);
		});

	

	// lightbox hide
		$('body').on('click',' .ajde_close_pop_trig',function(){
			var obj = $(this).parent();
			hide_popupwindowbox();
		});$('body').on('click',' .ajde_close_pop_btn',function(){
			var obj = $(this).parent();
			hide_popupwindowbox();
		});
		
		$(document).mouseup(function (e){
			var container=$('.ajde_popup');
			
			if(container.hasClass('active')){
				if (!container.is(e.target) // if the target of the click isn't the container...
				&& container.has(e.target).length === 0) // ... nor a descendant of the container
				{
					container.animate({'margin-top':'70px','opacity':0}).fadeOut().removeClass('active');
					$('#ajde_popup_bg').fadeOut();
				}
			}
		});
		function hide_popupwindowbox(){			
			var container=$('.ajde_popup');
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
				$('#ajde_popup_bg').fadeOut();			
			}
		}

	// OPEN POPUP BOX		
		// everywhere in wp-admin
			$('body').on('click','.ajde_popup_trig', function(){				
				ajde_popup_open( $(this));
			});

	// popup open
		function ajde_popup_open(obj){
			var popc = obj.data('popc');

			// check if specific lightbox requested
			LIGHTBOX = (typeof popc !== 'undefined' && popc !== false)?
				$('.ajde_popup.'+popc).eq(0):$('.ajde_popup.regular').eq(0);
			
			if(LIGHTBOX.is("visible")===true) return false;

			// append textbox id to popup if given
			if(obj.attr('data-textbox')!==''){
				$('.ajde_popup').attr({'data-textbox':obj.attr('data-textbox')});
			}

			// dynamic content within the site
				var dynamic_c = obj.attr('dynamic_c');
				if(typeof dynamic_c !== 'undefined' && dynamic_c !== false){
					
					var content_id = obj.attr('content_id');
					var content = $('#'+content_id).html();
					
					$('.ajde_popup').find('.ajde_popup_text').html( content);
				}
			
			// if content coming from a AJAX file
				var attr_ajax_url = obj.attr('ajax_url');				
				if(typeof attr_ajax_url !== 'undefined' && attr_ajax_url !== false){
					$.ajax({
						beforeSend: function(){
							show_pop_loading();
						},
						url:attr_ajax_url,
						success:function(data){
							$('.ajde_popup').find('.ajde_popup_text').html( data);
						},complete:function(){
							hide_pop_loading();
						}
					});
				}

			// change title if present		
				var poptitle = obj.attr('poptitle');
				if(typeof poptitle !== 'undefined' && poptitle !== false){
					LIGHTBOX.find('.ajde_header p').html(poptitle);
				}
						
			$('.ajde_popup').find('.message').removeClass('bad good').hide();

			// open lightbox
			LIGHTBOX.addClass('active').show().animate({'margin-top':'0px','opacity':1}).fadeIn();			
			
			$('html, body').animate({scrollTop:0}, 700);
			$('#ajde_popup_bg').fadeIn();
		}
	
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

	// yes no button		
		$('body').on('click','.ajde_yn_btn ', function(){
			var obj = $(this);
			var afterstatement = obj.attr('afterstatement');
			// yes
			if(obj.hasClass('NO')){
				obj.removeClass('NO');
				obj.siblings('input').val('yes');

				// afterstatment
				if(afterstatement!=''){
					var type = (obj.attr('as_type')=='class')? '.':'#';
					$(type+ obj.attr('afterstatement')).slideDown('fast');
				}

			}else{//no
				obj.addClass('NO');
				obj.siblings('input').val('no');
				
				if(afterstatement!=''){
					var type = (obj.attr('as_type')=='class')? '.':'#';
					$(type+obj.attr('afterstatement')).slideUp('fast');
				}
			}
		});

	// font awesome selector
		var FA = $('.ajde_fa_icons_selector');
		$('.ajde_fa_icons_selector').remove();
		$('body').append(FA);

		$('.ajde_icons').html('<em class="ajde_icon_close">X</em>');

		var fa_icon_selection = '';
		$('body').on('click','.ajde_icons', function(){
			var poss = $(this).offset();
			// /console.log(poss);
			$('.ajde_fa_icons_selector').css({'top':(poss.top-220)+'px', 'left':(poss.left-68)}).fadeIn('fast');

			fa_icon_selection = $(this);
		});

		// remove icon
			$('body').on('click','i.ajde_icons em', function(){
				$(this).parent().attr({'class':'ajde_icons default'});
				$(this).parent().siblings('input').val('');
			});

		//selection of new font icon
			$('.ajde_fa_icons_selector').on('click','li', function(){

				var icon = $(this).find('i').data('name');
				//console.log(icon);

				fa_icon_selection.attr({'class':'ajde_icons default fa '+icon});
				fa_icon_selection.siblings('input').val(icon);

				$('.ajde_fa_icons_selector').fadeOut('fast');
			});
		// close with click outside popup box when pop is shown
		$(document).mouseup(function (e){
			var container=$('.ajde_fa_icons_selector');
			
				if (!container.is(e.target) // if the target of the click isn't the container...
				&& container.has(e.target).length === 0) // ... nor a descendant of the container
				{
					$('.ajde_fa_icons_selector').fadeOut('fast');
				}
			
		});

	// color picker
		$('.colorselector').ColorPicker({
			onBeforeShow: function(){
				$(this).ColorPickerSetColor( $(this).attr('hex'));
			},	
			onChange:function(hsb, hex, rgb, el){
				CIRCLE = $('body').find('.colorpicker_on');
				CIRCLE.css({'backgroundColor': '#' + hex}).attr({'title': '#' + hex, 'hex':hex});

				obj_input = CIRCLE.siblings('input.backender_colorpicker');	
				obj_input.attr({'value':hex});
			},	
			onSubmit: function(hsb, hex, rgb, el) {
				var obj_input = $(el).siblings('input.backender_colorpicker');

				if($(el).hasClass('rgb')){
					$(el).siblings('input.rgb').attr({'value':rgb.r+','+rgb.g+','+rgb.b});
					//console.log(rgb);
				}

				obj_input.attr({'value':hex});

				$(el).css('backgroundColor', '#' + hex);
				$(el).attr({'title': '#' + hex, 'hex':hex});
				$(el).ColorPickerHide();

				$('body').find('.colorpicker_on').removeClass('colorpicker_on');
			},
			onHide: function(colpkr){
				$('body').find('.colorpicker_on').removeClass('colorpicker_on');
			}
		}).bind('click',function(){
			$(this).addClass('colorpicker_on');
		});

});