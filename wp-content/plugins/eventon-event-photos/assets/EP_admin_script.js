/**
 * JS for photos admin section 
 * @version  0.1
 */
jQuery(document).ready(function($){
	
	// Upload image
		var file_frame,
			BOX;
	  
	    $('body').on('click','#evoep_select_images',function(event) {
	    	var obj = jQuery(this);
	    	BOX = obj.parent().siblings('.evoep_images');

	    	event.preventDefault();

			// If the media frame already exists, reopen it.
			if ( file_frame ) {
				file_frame.open();
				return;
			}
			// Create the media frame.
			file_frame = wp.media.frames.downloadable_file = wp.media({
				title: 'Choose an Image',
				button: {text: 'Use Image',},
				multiple: true
			});

			// When an image is selected, run a callback.
			file_frame.on( 'select', function() {

				var selection = file_frame.state().get('selection');
		        selection.map( function( attachment ) {
		            attachment = attachment.toJSON();
		            loadselectimage(attachment, BOX);
		           
	            });

				//attachment = file_frame.state().get('selection').first().toJSON();
				//loadselectimage(attachment, BOX);
			});

			// Finally, open the modal.
			file_frame.open();
	    }); 

		function loadselectimage(attachment, BOX){
			thumbURL = attachment.sizes.thumbnail.url;
			imgURL = (thumbURL !== undefined)? thumbURL: attachment.url;

			caption = (attachment.caption!== undefined)? attachment.caption: 'na';

			imgEL = "<span data-imgid='"+attachment.id+"'><b class='remove_evoep'>X</b><img title='"+caption+"' data-imgid='"+attachment.id+"' src='"+imgURL+"'></span>";

			BOX.find('.evpep_img_holder').append(imgEL);
			update_image_ids(BOX);
		}

	    // remove image from gallery
		    $('body').on('click', '.remove_evoep', function(){
		    	BOX = $(this).closest('.evoep_images');
		    	$(this).parent().remove();
		    	update_image_ids(BOX);
		    });

		// update the image ids 
		    function update_image_ids(obj){
		    	var imgIDs,
		    		IDholder = obj.find('input.evpep_img_ids');

		    	obj.find('img').each(function(index){
		    		imgid = $(this).attr('data-imgid');
		    		if(imgid){
		    			imgIDs = (imgIDs? imgIDs:'') + imgid+',';
		    		}

		    		//console.log(imgIDs);
		    	});
		    	IDholder.val(imgIDs);
		    }
	// drggable and sorting image order
		$('.evpep_img_holder').sortable({
			update: function(e, ul){
				BOX = $(this).closest('.evoep_images');
				update_image_ids(BOX);
			}
		});
});