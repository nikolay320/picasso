/*
	Javascript: Eventon Active User
*/
jQuery(document).ready(function($){

	// add new users to permissions list
	$('.ajde_popup_text').on('click','.evoau_user_list_item',function(){
		var uid = ($(this).val());
		var uname = $(this).attr('uname');	

		if(uid =='all'){
			$(this).parent().siblings('p').find('input').prop('checked',false);
			$('.evoau_users_data').find('input.evoau_user_list_item').removeAttr('checked');	
		}else{
			$(this).parent().siblings('p').find('input[value="all"]').prop('checked',false);
			$('.evoau_users_data').find('input.evoau_user_list_item').removeAttr('checked');	
		}
			
		if( $(this).is(':checked') ){		
			$('.evoau_users_data').find('#evoau_'+uid).attr('checked','checked');
		}else{
			$('.evoau_users_data').find('#evoau_'+uid).removeAttr('checked');			
		}
	});

	// add new userroles to permissions list
	$('body').on('click','.ajde_popup_text .evoau_user_role_list_item',function(){
		var Role = $(this).val();
		//console.log(Role);
			
		if( $(this).is(':checked') ){		
			$('#evoau_role_list').find('#evoau_role_'+Role).attr('checked','checked');
		}else{
			$('#evoau_role_list').find('#evoau_role_'+Role).removeAttr('checked');			
		}
	});

	
});