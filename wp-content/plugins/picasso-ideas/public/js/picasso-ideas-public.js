jQuery(document).ready(function($) {
	// Sort Ideas
	$(document).on('change', '.idea_sort', function(event) {
		event.preventDefault();

		// find form
		var form = $(this).parent().parent();

		// submit form
		form[0].submit();
	});

	// Tooltip
	if (jQuery().tooltip) {
		$('[data-toggle="idea-tooltip"]').tooltip({
			// trigger: 'click',
			template: '<div class="tooltip idea-tooltip" role="tooltip"><div class="tooltip-arrow idea-tooltip-arrow"></div><div class="tooltip-inner idea-tooltip-inner"></div></div>',
		});
	}

	// Select2
	if (jQuery().select2) {
	    function format(state) {
	        var originalOption = state.element,
	            member_type = '';

	        if ($(originalOption).data('member-type')) {
	            member_type = ' (' + $(originalOption).data('member-type') + ')';
	        }
	        return '<span><img src="' + $(originalOption).data('avatar') + '" width="32" height="32" /> ' + state.text + member_type + '</span>';
	    }

	    $('#expert_id').select2({
	        placeholder: $(this).attr('data-placeholder'),
	        formatResult: format,
	        formatSelection: format,
	    });
	}

	// Zebra_DatePicker
	if (jQuery().Zebra_DatePicker) {
	    $('#idea_deadline').Zebra_DatePicker({
	        default_position: 'below'
	    });
	}

	// StickyTabs
	if (jQuery().stickyTabs) {
		$('.idea-tabs').stickyTabs();
	}

	// Jump to comment area after page loaded
	var hash = window.location.hash,
		matches = hash.match(/^#comment-([0-9]+)$/);

	if (hash && matches) {
		$('.idea-tabs a[href="#idea-comments"]').tab('show');
	}

	// editable ratings
	if ($('.review-idea-rating').length && jQuery().raty) {
		$('.review-idea-rating').raty({
			cancel: true,
			half: true,
			starType: 'i',
			scoreName: function() {
				return $(this).attr('data-review-category');
			},
			score: function() {
				return $(this).attr('data-score');
			},
		});
	}

	// non editable ratings
	if ($('.review-idea-readonly-rating').length && jQuery().raty) {
		$('.review-idea-readonly-rating').raty({
			readOnly: true,
			starType: 'i',
			scoreName: function() {
				return $(this).attr('data-review-category');
			},
			score: function() {
				return $(this).attr('data-score');
			},
		});
	}

	// put idea in favorites
	$(document).on('click', '.put-in-favorite', function(event) {
		event.preventDefault();
		
		var icon = $(this).find('.favorite-star'),
			counter_wrapper = $(this).find('.favorites-count'),
			count_favorites = $(this).find('.favorites-count').text(),
			idea_id = $(this).attr('data-post-id'),
			status;

		if ($(icon).hasClass('fa-star-o')) {
			status = 1;
		} else {
			status = 0;
		}

		var data = {
			action: 'idea_favorites',
			status: status,
			idea_id: idea_id,
		};

		$.ajax({
			url: picasso_ideas_params.ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: data,
			success: function(response) {
				if (response.success === 'true') {
					if (status) {
						icon.removeClass('fa-star-o');
						icon.addClass('fa-star');
						counter_wrapper.text(parseInt(count_favorites) + 1);
					} else {
						icon.addClass('fa-star-o');
						icon.removeClass('fa-star');
						counter_wrapper.text(parseInt(count_favorites) - 1);
					}
				}
			}
		});
	});

	// delay fucntion
	var delay = (function() {
		var timer = 0;
		return function(callback, ms) {
			clearTimeout(timer);
			timer = setTimeout(callback, ms);
		};
	})();

	// Find similar ideas
	if ($('.picasso-idea-frontend-submission #idea_title').length) {
		$('.picasso-idea-frontend-submission #idea_title').keyup(function(event) {
			var keyword = $(this).val(),
				wrapper = $('.similar-ideas-wrapper');
			
			if (keyword.length > 3) {
				delay(function() {
					var myExp = new RegExp(keyword, 'i'),
						data = {
							'action': 'pi_search_similar_ideas',
							'keyword': keyword,
						};

					$.ajax({
						url: picasso_ideas_params.ajaxurl,
						type: 'POST',
						dataType: 'html',
						data: data,
						success: function(response) {
							$(wrapper).html(response);
						}
					});
				}, 1000);
			}
		});
	}

});