jQuery(document).ready(function($) {

	// countdown clock
	$('.countdown-clock').each(function() {
		var that = $(this),
			date = that.attr('data-date'),
			offset = that.attr('data-offset');

		if (jQuery().countdown) {
			that.countdown({
				date: date,
				offset: offset,
			});
		}
	});

});