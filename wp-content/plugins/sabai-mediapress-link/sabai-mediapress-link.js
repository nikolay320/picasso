jQuery(document).ready(function() 
{
	jQuery('.sabai-field-name-field-videofile .fa.fa-file-o').hide();
	jQuery('.sabai-field-name-field-videofile a').each(function() {
		var videofile = jQuery(this).attr("href");
		var html = '<div style="width: 100%; " class="flowplayer functional play-button"><video controls="">'
		+'<source type="video/webm" src="'+ videofile +'">'
		+'<source type="video/mp4" src="'+ videofile +'">'
		+'</video></div>';
		
		jQuery(html).insertAfter(jQuery(this));
		jQuery(this).hide();
		
	});
	
	//jQuery('.flowplayer').hide()
	jQuery('.sabai_mediapress_video').each(function() {
		var videofile = jQuery(this).find(">:first-child").attr("class");
		
		var html = '<div style="width: 90%; " class="flowplayer functional play-button"><video controls="">'
		+'<source type="video/webm" src="'+ videofile +'">'
		+'<source type="video/mp4" src="'+ videofile +'">'
		+'</video></div>';
		
		jQuery(html).insertAfter(jQuery(this).find(">:first-child"));
		
	});

	jQuery('.sabai-idea-youtube-video').each(function() {
		var youtubeid = jQuery(this).find(">:first-child").attr("class");
		
		var youtubeiframe = '<iframe src="https://www.youtube.com/embed/'+youtubeid+'" frameborder="0" allowfullscreen></iframe>';
		
		jQuery(this).find(">:first-child").html(youtubeiframe);
		
	});
});