jQuery(document).ready(function($){	
	$("#campaign_ideas").select2();
  $('#campaign_end_date').datetimepicker({
     minDate:0
  });
});

function showMediaUploader(input,inputHidden,image){
    var mediaUploader;

    // If the uploader object has already been created, reopen the dialog
     if (mediaUploader) {
      mediaUploader.open();
      return;
    }

    // Extend the wp.media object
    mediaUploader = wp.media.frames.file_frame = wp.media({
      title: 'Choose Image',
      button: {
      text: 'Choose Image'
    }, multiple: false });

    // When a file is selected, grab the URL and set it as the text field's value
    mediaUploader.on('select', function() {
      attachment = mediaUploader.state().get('selection').first().toJSON();
      input.val(attachment.url);
      inputHidden.val(attachment.id);
      if(image!=false){
        image.attr('src',attachment.url)
      }
    });

    // Open the uploader dialog
    mediaUploader.open();
}