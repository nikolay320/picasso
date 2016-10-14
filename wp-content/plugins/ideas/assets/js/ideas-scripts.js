jQuery(document).ready(function($){   
	// save idea fields
	$('.save-idea-fields-button').on('click', function(event) {
		event.preventDefault();
		
		var expert_id = $('#expert_id').val(),
			deadline = $('#idea_deadline').val(),
			campaign_id = $(this).attr('data-campaign-id'),
			idea_id = $(this).attr('data-idea-id'),
			url = $(this).attr('data-ajax-url'),
			action = $(this).attr('data-action'),
			notify_message = $(this).attr('data-notify-message'),
			$loader = $(this).closest('.idea-buttons-wrapper').find('.idea-loading'),
			$table = $('.idea-experts-table');

		$loader.addClass('active');

		$.ajax({
			url: url,
			type: 'POST',
			dataType: 'json',
			data: {
				idea_id: idea_id,
				expert_id: expert_id,
				deadline: deadline,
				campaign_id: campaign_id,
				action: action,
			},
			success: function(response) {
				if (response.reload_table == 'true') {
					$table.html(response.table_content);
				}

				$loader.removeClass('active');

				if (response.show_notification == 'true' && $.isFunction($.notify)) {
					$.notify({
						message: notify_message
					}, {
						type: 'success',
						z_index: 10000,
					});
				}
			}
		});
	});

	// remove expert
	$(document).on('click', '.remove-expert', function(event) {
		event.preventDefault();

		var expert_id = $(this).attr('data-expert-id'),
			idea_id = $(this).attr('data-idea-id'),
			url = $(this).attr('data-ajax-url'),
			action = $(this).attr('data-action'),
			notify_message = $(this).attr('data-notify-message'),
			$loader = $(this).closest('td').find('.idea-loading'),
			$tr = $(this).closest('tr');

		$loader.addClass('active');

		$.ajax({
			url: url,
			type: 'POST',
			dataType: 'json',
			data: {
				idea_id: idea_id,
				expert_id: expert_id,
				notify_message: notify_message,
				action: action,
			},
			success: function(response) {
				$tr.hide('fast', function() {
					$tr.remove();
				});

				if (response.success == 'true' && $.isFunction($.notify)) {
					$.notify({
						message: notify_message
					}, {
						type: 'success',
						z_index: 10000,
					});
				}
			}
		});		
	});

	// ratings
	if ($('.review-idea-rating').length && jQuery().raty) {
		$('.review-idea-rating').raty({
			cancel: true,
			half: true,
			starType: 'i',
			score: function() {
				return $(this).attr('data-score');
			}
		});
	}

	// ratings
	if ($('.review-idea-average-rating').length && jQuery().raty) {
		$('.review-idea-average-rating').raty({
			readOnly: true,
			starType: 'i',
			score: function() {
				return $(this).attr('data-score');
			}
		});
	}

	// post idea expert review
	$('.idea-expert-review-button').on('click', function(event) {
		event.preventDefault();

		var $form = $(this).closest('.single-idea-review');
			comment = $form.find('[name="idea_comment"]').val(),
			$loader = $form.find('.idea-loading'),
			review_id = $(this).attr('data-review-id'),
			url = $(this).attr('data-ajax-url'),
			action = $(this).attr('data-action'),
			data = {
				review_id: review_id,
				comment: comment,
				action: action,
			};

		$form.find('[data-review-category]').each(function() {
			var that = $(this),
				name = that.attr('data-review-category'),
				score = that.find('[name="score"]').val();
			
			data[name] = score;
		});

		$loader.addClass('active');

		$.ajax({
			url: url,
			type: 'POST',
			dataType: 'json',
			data: data,
			success: function(response) {
				$loader.removeClass('active');

				if (response.success == 'true') {
					window.location.hash = "#expert-reviews"
					location.reload();
				}
			}
		});
	});

	// post idea user review
	$('.idea-user-review-button').on('click', function(event) {
		event.preventDefault();

		var $form = $(this).closest('.single-idea-review');
			comment = $form.find('[name="idea_comment"]').val(),
			$loader = $form.find('.idea-loading'),
			idea_id = $(this).attr('data-idea-id'),
			review_id = $(this).attr('data-review-id'),
			user_id = $(this).attr('data-user-id'),
			url = $(this).attr('data-ajax-url'),
			action = $(this).attr('data-action'),
			data = {
				idea_id: idea_id,
				review_id: review_id,
				user_id: user_id,
				comment: comment,
				action: action,
			};

		$form.find('[data-review-category]').each(function() {
			var that = $(this),
				name = that.attr('data-review-category'),
				score = that.find('[name="score"]').val();
			
			data[name] = score;
		});

		$loader.addClass('active');

		$.ajax({
			url: url,
			type: 'POST',
			dataType: 'json',
			data: data,
			success: function(response) {
				$loader.removeClass('active');

				if (response.success == 'true') {
					window.location.hash = "#user-reviews"
					location.reload();
				}
			}
		});
	});

	// post idea update
	$('.idea-update-button').on('click', function(event) {
		event.preventDefault();

		var $form = $(this).closest('.idea-update-form');
			idea_update = $form.find('[name="idea_update"]').val(),
			$loader = $form.find('.idea-loading'),
			idea_id = $(this).attr('data-idea-id'),
			review_id = $(this).attr('data-review-id'),
			user_id = $(this).attr('data-user-id'),
			url = $(this).attr('data-ajax-url'),
			action = $(this).attr('data-action'),
			notify_message = $(this).attr('data-notify-message'),
			data = {
				idea_id: idea_id,
				review_id: review_id,
				user_id: user_id,
				idea_update: idea_update,
				action: action,
			};

		$loader.addClass('active');

		$.ajax({
			url: url,
			type: 'POST',
			dataType: 'json',
			data: data,
			success: function(response) {
				$loader.removeClass('active');

				if (response.success == 'true' && $.isFunction($.notify)) {
					$.notify({
						message: notify_message
					}, {
						type: 'success',
						z_index: 10000,
					});
				}
			}
		});
	});

	// Tooltip
	if (jQuery().tooltip) {
		$('[data-toggle="idea-tooltip"]').tooltip({
			// trigger: 'click',
			template: '<div class="tooltip idea-tooltip" role="tooltip"><div class="tooltip-arrow idea-tooltip-arrow"></div><div class="tooltip-inner idea-tooltip-inner"></div></div>',
		});
	}

	// create idea from frontend modal
	$('.create-idea-modal-link').on('click', function(event) {
	    event.preventDefault();
	    
	    var $loader = $(this).parent().find('.loader'),
	        url = $(this).attr('data-ajax-url'),
	        data = {
	            'action': 'klc_get_idea_modal_content',
	        },
	        modal = $('#frontend-idea-submit');

		$loader.addClass('active');

	    $.ajax({
	        url: url,
	        type: 'POST',
	        dataType: 'html',
	        data: data,
	        success: function(response) {
	        	$loader.removeClass('active');

	            modal.html(response);

	            // remove and add tinymce instance
	            tinymce.EditorManager.execCommand('mceRemoveEditor',true, '_idea_content');
	            tinymce.EditorManager.execCommand('mceAddEditor',true, '_idea_content');

	            var rremodal = $('#frontend-idea-modal').remodal({
	            	hashTracking: false,
	            });

	            rremodal.open();

	            rremodal.open();

	            // modal.reveal({
	            //     dismissmodalclass: 'close-frontend-idea-submit-modal',
	            // });
	        }
	    });
	    
	});

	// edit idea from frontend modal
	$('.edit-idea-modal-link').on('click', function(event) {
	    event.preventDefault();
	    
	    var idea_id = $(this).attr('data-idea-id'),
	    	$loader = $(this).parent().find('.idea-edit-modal-open-loader'),
	        url = $(this).attr('data-ajax-url'),
	        data = {
	            'idea_id': idea_id,
	            'action': 'klc_get_idea_modal_content',
	        },
	        modal = $('#frontend-idea-submit');

		$loader.addClass('active');

	    $.ajax({
	        url: url,
	        type: 'POST',
	        dataType: 'html',
	        data: data,
	        success: function(response) {
	        	$loader.removeClass('active');

	            modal.html(response);

	            // remove and add tinymce instance
	            tinymce.EditorManager.execCommand('mceRemoveEditor',true, '_idea_content');
	            tinymce.EditorManager.execCommand('mceAddEditor',true, '_idea_content');

	            var rremodal = $('#frontend-idea-modal').remodal({
	            	hashTracking: false,
	            });

	            rremodal.open();

	            // modal.reveal({
	            //     dismissmodalclass: 'close-frontend-idea-submit-modal',
	            // });
	        }
	    });
	    
	});

	// submit idea
	$(document).on('click', '.idea-frontend-submit', function(event) {
	    event.preventDefault();
	    
	    var $form = $(this).closest('.cmb-wrapper'),
	        $loader = $form.find('.loader'),
	        $error = $form.find('.error-message'),
	        ajax_url = $form.find('[name="ajax_url"]').val(),
	        action = $form.find('[name="action"]').val(),
	        tinymce_editor_content = tinymce.editors['_idea_content'].getContent(),
	        data =  {
	            '_idea_id': $form.find('[name="_idea_id"]').val(),
	            '_idea_title': $form.find('[name="_idea_title"]').val(),
	            '_idea_content': tinymce_editor_content,
	            '_idea_campaign': $form.find('[name="_idea_campaign"]').val(),
	            '_idea_image': $form.find('[name="_idea_image"]').val(),
	            '_idea_image_id': $form.find('[name="_idea_image_id"]').val(),
	            '_idea_file': $form.find('[name="_idea_file"]').val(),
	            '_idea_file_id': $form.find('[name="_idea_file_id"]').val(),
	            '_idea_video': $form.find('[name="_idea_video"]').val(),
	            '_idea_video_id': $form.find('[name="_idea_video_id"]').val(),
	            '_idea_youtube_video': $form.find('[name="_idea_youtube_video"]').val(),
	            'action': action,
	        };

	    $loader.addClass('active');
	    $error.html('');
	    $error.removeClass('active');

	    $.ajax({
	        url: ajax_url,
	        type: 'POST',
	        dataType: 'json',
	        data: data,
	        success: function(response) {
	            $loader.removeClass('active');

	            if (response.success === 'false' && response.error.length) {
	                $error.html(response.error);
	                $error.addClass('active');
	            }

	            else {
	                location.reload();
	            }                    
	        }
	    });
	});

	// fucntion
	var delay = (function() {
		var timer = 0;
		return function(callback, ms) {
			clearTimeout(timer);
			timer = setTimeout(callback, ms);
		};
	})();

	// Find similar ideas
	if ($('.cmb2-id-post-title #post_title').length) {
		$('.cmb2-id-post-title #post_title').keyup(function(event) {
			var keyword = $(this).val(),
				wrapper = $('.similar-ideas-wrapper');
			
			if (keyword.length > 3) {
				delay(function() {
					var myExp = new RegExp(keyword, 'i'),
						data = {
							'action': 'klc_search_similar_ideas',
							'keyword': keyword,
						};

					$.ajax({
						url: klc_params.ajaxurl,
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
