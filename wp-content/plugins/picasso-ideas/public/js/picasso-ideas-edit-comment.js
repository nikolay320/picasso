jQuery(document).ready(function($) {
	if (!jQuery().remodal) {
		return;
	}
	
	// Edit comment modal instance
	var editCommentModalInstance = $('.picasso-edit-comment-modal').remodal({
		hashTracking: false
	});

	var $modal = $('.picasso-edit-comment-modal'),
		$form = $modal.find('form'),
		$message_wrapper = $modal.find('.message-wrapper'),
		$large_loader = $modal.find('.large-loader');

	// open modal
	$(document).on('click', '.comment_popup', function(event) {
		event.preventDefault();

		var comment_id = $(this).attr('data-commentid'),
			data = {
				'action': 'pi_get_comment_form',
				'comment_id': comment_id,
			};

		$large_loader.addClass('active');

		$.ajax({
			url: picasso_ideas_params.ajaxurl,
			type: 'POST',
			dataType: 'html',
			data: data,
			success: function(response) {
				$large_loader.removeClass('active');
				$form.html(response);
			}
		});

		editCommentModalInstance.open();
	});

	// Close modal
	$(document).on('closed', '.picasso-edit-comment-modal', function(event) {
		$message_wrapper.removeClass('alert alert-danger alert-success active');
		$message_wrapper.html('');
		$form.html('');
	});

	// Update comment from modal
	$('.picasso-edit-comment-modal').on('click', '.submit_comment', function(event) {
		event.preventDefault();

		var comment_id = $modal.find('.comment_id').val(),
			comment = $modal.find('.comment').val(),
			$loader = $modal.find('.loading');

		$loader.addClass('active');
		$message_wrapper.removeClass('alert alert-danger alert-success active');
		$message_wrapper.html('');

		var data = {
			action: 'update_idea_comment',
			comment_id: comment_id,
			comment: comment,
		};

		$.ajax({
			url: picasso_ideas_params.ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: data,
			success: function(response) {
				if (response.success === 'false') {
					$message_wrapper.addClass('alert alert-danger active');
					$message_wrapper.html(response.message);
				} else if (response.success === 'true') {
					$message_wrapper.addClass('alert alert-success active');
					$message_wrapper.html(response.message);
					$('.comment_text_' + comment_id).html(response.comment_content);
				}

				$loader.removeClass('active');
			}
		});		
	});

});