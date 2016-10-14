jQuery(document).ready(function($) {
	if (!jQuery().remodal) {
		return;
	}

	// Comment modal instance
	var commentModalInstance = $('.picasso-idea-comment-modal').remodal({
		hashTracking: false
	});

	var $modal = $('.picasso-idea-comment-modal'),
		$post_id_holder = $modal.find('.post_id'),
		$message_wrapper = $modal.find('.message-wrapper'),
		$loader = $modal.find('.loading');

	// Open comment modal
	$('.idea-comment-popup').on('click', '.idea-comment-popup-link', function(event) {
		event.preventDefault();

		var post_id = $(this).attr('data-post-id');

		$post_id_holder.val(post_id);

		commentModalInstance.open();
	});

	// Add comment from modal
	$('.picasso-idea-comment-modal').on('click', '.submit_comment', function(event) {
		event.preventDefault();

		var post_id = $post_id_holder.val(),
			comment = $modal.find('.comment').val();

		$loader.addClass('active');
		$message_wrapper.removeClass('alert alert-danger alert-success active');
		$message_wrapper.html('');

		var data = {
			action: 'add_idea_comment',
			post_id: post_id,
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
					$modal.find('.comment').val('');
					$message_wrapper.html(response.message);

					var $commented_post = $('[data-post-id="' + post_id + '"]'),
						$count_comments_holder = $commented_post.find('.count-comments');
						count_comments = $count_comments_holder.text();

					$commented_post.addClass('comment-found');
					$count_comments_holder.text(parseInt(count_comments) + 1);
				}

				$loader.removeClass('active');
			}
		});		
	});

	// Close comment modal
	$(document).on('closed', '.picasso-idea-comment-modal', function(event) {
		$message_wrapper.removeClass('alert alert-danger alert-success active');
		$message_wrapper.html('');
		$post_id_holder.val('');
	});

});