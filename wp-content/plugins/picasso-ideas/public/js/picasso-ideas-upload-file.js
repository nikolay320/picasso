jQuery(document).ready(function($) {
	$('.picasso-upload-wrapper').on('change', ':file', function() {
		var input = $(this),
			numFiles = input.get(0).files ? input.get(0).files.length : 1,
			label = input.val().replace(/\\/g, '/').replace(/.*\//, '');

		input.trigger('fileselect', [numFiles, label]);
	});

	$(':file').on('fileselect', function(event, numFiles, label) {
		var input = $(this).parents('.input-group').find('.file-name-holder'),
			log = numFiles > 1 ? numFiles + ' files selected' : label;

		if (input.length) {
			input.val(log);

			// Start uploading immidiately after selecting file
			var upload_wrapper = $(this).parents('.picasso-upload-wrapper');
			upload_wrapper.find('.pi-upload-file').trigger('click');
		}
	});

	$('.pi-upload-file').on('click', function(event) {
		event.preventDefault();

		var that = $(this).parent().parent().parent(),
			wrapper = that.parent(),
			error_wrapper = wrapper.find('.upload-errors'),
			post_max_size = that.find('[name="post_max_size"]').val(),
			exceeded_message = that.find('[name="file_size_message"]').val(),
			meta_field_name = that.find('[name="meta_field_name"]').val(),
			progress_wrapper = wrapper.find('.progress'),
			progress_bar = wrapper.find('.progress-bar'),
			files_wrapper = wrapper.find('.files-list'),
			form_data = new FormData(),
			filepicker = that.find('input[type="file"]'),
			filenameholder = that.find('.file-name-holder'),
			file = filepicker[0].files[0],
			supposed_file_type = that.find('[name="file_type"]').val(),
			message;

		form_data.append('file', file);
		form_data.append('action', 'pi_upload_file');
		form_data.append('supposed_file_type', supposed_file_type);
		form_data.append('meta_field_name', meta_field_name);

		error_wrapper.addClass('hidden');
		error_wrapper.html('');

		progress_wrapper.addClass('hidden');
		progress_bar.css('width', 0);
		progress_bar.text('');

		if (typeof file == 'object') {
			var file_size = file.size;

			if (file_size > post_max_size) {
				message = $('<div>' + exceeded_message + '</div>');
				$(message).appendTo(error_wrapper);
				
				// show errors
				error_wrapper.removeClass('hidden');

				// reset
				filepicker.val('');
				filenameholder.val('');
			} else {

				$.ajax({
					url: picasso_ideas_params.ajaxurl,
					type: 'POST',
					data: form_data,
					dataType: 'json',
					contentType: false,
					processData: false,
					xhr: function() {
						var xhr = new window.XMLHttpRequest();

						xhr.upload.addEventListener('progress', function(event) {
							if (event.lengthComputable) {
								var percentComplete = Math.round((event.loaded / event.total) * 100);
								
								progress_wrapper.removeClass('hidden');
								progress_bar.attr('aria-valuenow', percentComplete ? percentComplete : 0);
								progress_bar.css('width', percentComplete ? percentComplete + '%' : 0);
								progress_bar.text(percentComplete ? percentComplete + '%' : '');
							}
						}, false);

						return xhr;
					},
					success: function(response) {
						var message;

						if (response.errors.length) {
							for (var i = 0; i < response.errors.length; i++) {
								message = $('<div>' + response.errors[i] + '</div>');
								$(message).appendTo(error_wrapper);
							}

							// show errors
							error_wrapper.removeClass('hidden');
						}

						else if (typeof response.output == 'object') {
							var response = response.output;

							if (response.upload_error.length) {
								message = $('<div>' + response.upload_error + '</div>');
								$(message).appendTo(error_wrapper);
				
								// show errors
								error_wrapper.removeClass('hidden');
							}

							else if (response.html_markup.length) {
								$(response.html_markup).appendTo(files_wrapper);

								// Reinitialize magnificPopup
								$('a[rel^="prettyPhoto"]').magnificPopup({
									type: 'image',
									mainClass: 'mfp-img-pop',
									gallery: {
										enabled: true,
									},
								});
							}
						}

						progress_wrapper.addClass('hidden');
						progress_bar.css('width', 0);
						progress_bar.text('');

						// reset
						filepicker.val('');
						filenameholder.val('');
					},
				});

			}
		}

	});

	// remove attachment
	$('.picasso-upload-wrapper').on('click', '.files-list .remove', function(event) {
		$(this).parent().parent().remove();
	});

});