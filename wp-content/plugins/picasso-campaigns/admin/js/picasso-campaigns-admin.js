jQuery(document).ready(function($) {
	
	// Attach select2 on campaign ideas meta field
	if (jQuery().select2 && $('#_campaign_ideas').length) {
		$('#_campaign_ideas').select2();
	}

});