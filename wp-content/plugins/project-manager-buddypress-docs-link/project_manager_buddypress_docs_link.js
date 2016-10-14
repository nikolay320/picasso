jQuery(document).ready(function() 
{
	if ( jQuery('html').attr('lang') == 'fr-FR' ) {
		var ProjectString = 'Projet';
	} else {
		var ProjectString = 'Project';
	}
	jQuery('td.project-cell').each(function( index ) {
		if ( $(this).text() ) {
			$(this).parent().find('.activity-avatar').css('display', 'none');
			$(this).parent().find('td.author-cell').append('<span>'+ProjectString+'</span>');
			
		}
	});
});