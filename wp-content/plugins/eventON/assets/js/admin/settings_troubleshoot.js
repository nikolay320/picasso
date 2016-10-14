/*
*	Eventon Settings tab - troubleshoot
*	@version: 0.1
*	@updated: 2016-3
*/

jQuery(document).ready(function($){

	$('#troubleshoot_videos').on('click', function(){
		$(this).siblings('.troubleshoot_videos').toggle();
	});

	$('.evotrouble_left').on('click','h5',function(){
		$(this).next('p').toggle();
	});

});