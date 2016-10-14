// upload image with wordpress media uploader
$(document).on('click', '.cmb2-upload-button', function(event) {
	event.preventDefault();

	var wrapper = $(this).closest('.cmb-td');

	var image = wp.media({
		title: 'Upload Image',
		multiple: false
	})
	.open()
	.on('select', function(e) {
		var uploaded_image = image.state().get('selection').first().toJSON(),
			image_url = uploaded_image.url,
			image_id = uploaded_image.id,
			type = uploaded_image.type,
			icon = uploaded_image.icon;

		console.log(type);
		console.log(icon);

		if (type === 'image') {
			wrapper.find('.image-src').attr('src', image_url);
		} else {
			wrapper.find('.image-src').attr('src', icon);
		}

		wrapper.find('.image-url').val(image_url);
		wrapper.find('.image-id').val(image_id);
		wrapper.find('.cmb2-media-status').removeClass('hidden');
	});
});

// remove image
$(document).on('click', '.remvoe-image', function(event) {
	event.preventDefault();
	
	var wrapper = $(this).closest('.cmb-td');

	wrapper.find('.image-url').val('');
	wrapper.find('.image-id').val('');
	wrapper.find('.image-src').attr('src', '');
	wrapper.find('.cmb2-media-status').addClass('hidden');
});