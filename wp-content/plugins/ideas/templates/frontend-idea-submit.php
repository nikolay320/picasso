<?php
// no idea id given, create a new idea from frontend
$_idea_id = '';
$_idea_title = '';
$_idea_content = '';
$_idea_campaign = '';
$_idea_image = '';
$_idea_image_id = '';
$_idea_file = '';
$_idea_file_id = '';
$_idea_video = '';
$_idea_video_id = '';
$_idea_youtube_video = '';

$_idea_image_thumb_src = '';
$_idea_file_thumb_src = '';

// idea found for given id
if (isset($_POST['idea_id'])) {
	$idea = get_post($idea_id);

	if ($idea->post_type === 'ideas') {
		$_idea_id = $idea->ID;
		$_idea_title = $idea->post_title;
		$_idea_content = $idea->post_content;
		$_idea_campaign = get_post_meta($idea_id, 'idea_campaign', true);
		$_idea_image = get_post_meta($idea_id, '_idea_image', true);
		$_idea_image_id = get_post_meta($idea_id, '_idea_image_id', true);
		$_idea_file = get_post_meta($idea_id, '_idea_file', true);
		$_idea_file_id = get_post_meta($idea_id, '_idea_file_id', true);
		$_idea_video = get_post_meta($idea_id, '_idea_video', true);
		$_idea_video_id = get_post_meta($idea_id, '_idea_video_id', true);
		$_idea_youtube_video = get_post_meta($idea_id, '_idea_youtube_video', true);
	}
}

// echo '<pre>';
// print_r(wp_get_attachment_metadata($_idea_image_id));
// echo '</pre>';

// echo '<pre>';
// print_r(wp_get_attachment_metadata($_idea_file_id));
// echo '</pre>';

// echo '<pre>';
// print_r(wp_get_attachment_metadata($_idea_video_id));
// echo '</pre>';
?>

<div class="cmb-wrapper">
	<div class="cmb-row">
		<div class="cmb-td">
			<div class="error-message alert alert-danger"></div>
		</div>
	</div>

	<div class="cmb-row">
		<div class="cmb-th">
			<label for="_idea_title"><?php _e('Idea Title', IDEAS_TEXT_DOMAIN); ?></label>
		</div>
		<div class="cmb-td">
			<input type="text" class="regular-text" name="_idea_title" id="_idea_title" value="<?php echo $_idea_title; ?>">
		</div>
	</div>

	<div class="cmb-row">
		<div class="cmb-th">
			<label for="_idea_content"><?php _e('Idea Content', IDEAS_TEXT_DOMAIN); ?></label>
		</div>
		<div class="cmb-td">
			<textarea name="_idea_content" id="_idea_content" class="regular-textarea" cols="30" rows="10"><?php echo $_idea_content; ?></textarea>
		</div>
	</div>

	<div class="cmb-row">
		<div class="cmb-th">
			<label for="_idea_campaign"><?php _e('Campaign', IDEAS_TEXT_DOMAIN); ?></label>
		</div>
		<div class="cmb-td">
			<select name="_idea_campaign" id="_idea_campaign" class="regular-text">
				<?php
				$campaigns = klc_get_campaigns();

				if ($campaigns) {
					foreach ($campaigns as $campaign_id => $campaign_title) {
						$campaign_end_date = get_post_meta($campaign_id, 'campaign_end_date', true);
						$selected = ($_idea_campaign == $campaign_id) ? 'selected="selected"' : '';

						echo '<option value="' . $campaign_id . '" ' . $selected . '>' . $campaign_title . '</option>';
					}
				}
				?>
			</select>
		</div>
	</div>

	<div class="cmb-row">
		<div class="cmb-th">
			<label for="_idea_image"><?php _e('Image', IDEAS_TEXT_DOMAIN); ?></label>
		</div>
		<div class="cmb-td">
			<div class="upload-wrapper">
			    <input type="text" name="_idea_image" class="regular-text image-url cmb2-upload-file" value="<?php echo $_idea_image; ?>">
			    <input type="hidden" name="_idea_image_id" class="image-id" value="<?php echo $_idea_image_id; ?>">
			    <input type="button" class="cmb2-upload-button" value="<?php _e('Add or Upload Image', IDEAS_TEXT_DOMAIN); ?>">
			</div>
			<div class="cmb2-media-status <?php echo !$_idea_image_id ? 'hidden' : ''; ?>">
			    <div class="img-status">
			    	<?php
			    	$mime_type = $_idea_image_id ? get_post_mime_type($_idea_image_id) : '';
			    	$image_src = ($mime_type == 'image/jpeg' || $mime_type == 'image/png') ? wp_get_attachment_thumb_url($_idea_image_id) : wp_mime_type_icon($mime_type);
			    	?>
			        <img src="<?php echo $image_src; ?>" class="image-src">
			        <a href="#" class="remvoe-image"><span class="fa fa-close"></span></a>
			    </div>
			</div>
		</div>
	</div>

	<div class="cmb-row">
		<div class="cmb-th">
			<label for="_idea_file"><?php _e('Attachment', IDEAS_TEXT_DOMAIN); ?></label>
		</div>
		<div class="cmb-td">
			<div class="upload-wrapper">
			    <input type="text" name="_idea_file" class="regular-text image-url cmb2-upload-file" value="<?php echo $_idea_file; ?>">
			    <input type="hidden" name="_idea_file_id" class="image-id" value="<?php echo $_idea_file_id; ?>">
			    <input type="button" class="cmb2-upload-button" value="<?php _e('Add or Upload Attachment', IDEAS_TEXT_DOMAIN); ?>">
			</div>
			<div class="cmb2-media-status <?php echo !$_idea_file_id ? 'hidden' : ''; ?>">
			    <div class="img-status">
			    	<?php
			    	$mime_type = $_idea_file_id ? get_post_mime_type($_idea_file_id) : '';
			    	$image_src = ($mime_type == 'image/jpeg' || $mime_type == 'image/png') ? wp_get_attachment_thumb_url($_idea_file_id) : wp_mime_type_icon($mime_type);
			    	?>
			        <img src="<?php echo $image_src; ?>" class="image-src">
			        <a href="#" class="remvoe-image"><span class="fa fa-close"></span></a>
			    </div>
			</div>
		</div>
	</div>

	<div class="cmb-row">
		<div class="cmb-th">
			<label for="_idea_video"><?php _e('Video', IDEAS_TEXT_DOMAIN); ?></label>
		</div>
		<div class="cmb-td">
			<div class="upload-wrapper">
			    <input type="text" name="_idea_video" class="regular-text image-url cmb2-upload-file" value="<?php echo $_idea_video; ?>">
			    <input type="hidden" name="_idea_video_id" class="image-id" value="<?php echo $_idea_video_id; ?>">
			    <input type="button" class="cmb2-upload-button" value="<?php _e('Add or Upload Video', IDEAS_TEXT_DOMAIN); ?>">
			</div>
			<div class="cmb2-media-status <?php echo !$_idea_video_id ? 'hidden' : ''; ?>">
			    <div class="img-status">
			    	<?php
			    	$mime_type = $_idea_video_id ? get_post_mime_type($_idea_video_id) : '';
			    	$image_src = ($mime_type == 'image/jpeg' || $mime_type == 'image/png') ? wp_get_attachment_thumb_url($_idea_video_id) : wp_mime_type_icon($mime_type);
			    	?>
			        <img src="<?php echo $image_src; ?>" class="image-src">
			        <a href="#" class="remvoe-image"><span class="fa fa-close"></span></a>
			    </div>
			</div>
		</div>
	</div>

	<div class="cmb-row">
		<div class="cmb-th">
			<label for="_idea_youtube_video"><?php _e('Youtube Video', IDEAS_TEXT_DOMAIN); ?></label>
		</div>
		<div class="cmb-td">
		    <input type="text" name="_idea_youtube_video" class="regular-text" value="<?php echo $_idea_youtube_video; ?>">
		    <small><?php _e('Enter youtube video link.', IDEAS_TEXT_DOMAIN); ?></small>
		</div>
	</div>

	<div class="cmb-row">
		<div class="cmb-td">
		    <div class="button-group">
		    	<input type="hidden" name="_idea_id" value="<?php echo $_idea_id; ?>">
		    	<input type="hidden" name="ajax_url" value="<?php echo admin_url('admin-ajax.php'); ?>">
		    	<input type="hidden" name="action" value="klc_submit_idea_data_from_frontend">
		    	<input type="button" class="close-frontend-idea-submit-modal idea-button" value="<?php _e('Close', IDEAS_TEXT_DOMAIN); ?>" data-remodal-action="close">
		    	<input type="button" class="button-primary idea-button idea-frontend-submit" value="<?php _e('Submit', IDEAS_TEXT_DOMAIN); ?>">
		    	<span class="fa fa-spinner fa-spin loader"></span>
		    </div>
		</div>
	</div>
</div>