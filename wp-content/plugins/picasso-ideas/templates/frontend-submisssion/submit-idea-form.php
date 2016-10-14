<?php
if (!empty($idea)) {
	$idea_id = $idea->ID;
	$idea_title = $idea->post_title;
	$idea_content = $idea->post_content;

	$idea_campaign = get_post_meta($idea_id, '_idea_campaign', true);
	$idea_images = get_post_meta($idea_id, '_idea_images', true);
	$idea_files = get_post_meta($idea_id, '_idea_files', true);
	$idea_videos = get_post_meta($idea_id, '_idea_videos', true);
	$idea_youtube = get_post_meta($idea_id, '_idea_youtube', true);

	$save_button_title = __('Update Idea', 'picasso-ideas');
} else {
	$idea_id = '';
	$idea_title = '';
	$idea_content = '';

	if (!empty($_GET['campaign_id'])) {
		$idea_campaign = $_GET['campaign_id'];
	} else {
		$idea_campaign = '';
	}

	$idea_images = '';
	$idea_files = '';
	$idea_videos = '';
	$idea_youtube = '';

	$save_button_title = __('Add Idea', 'picasso-ideas');
}

// wp editor settings
$editor_settings = array(
	'textarea_rows' => 12,
	'media_buttons' => false,
);
?>

<div class="row">
	<div class="col-md-12 col-sm-12">
		<?php // flash success message ?>
		<?php if (Picasso_Ideas_Session::exists('pi_frontend_submit_message')): ?>
			<div class="alert alert-success">
				<?php echo Picasso_Ideas_Session::flash('pi_frontend_submit_message'); ?>
			</div>
		<?php endif ?>

		<form action="" method="POST" enctype="multipart/form-data" class="cmb-form picasso-idea-frontend-submission">
			<div class="cmb-row idea-title">
				<div class="cmb-th">
					<label for="idea_title"><?php _e('Idea Title', 'picasso-ideas'); ?></label>
				</div>
				<div class="cmb-td">
					<input type="text" class="regular-text" name="idea_title" id="idea_title" value="<?php echo $idea_title; ?>">
				</div>
			</div><!-- .idea-title -->

			<div class="cmb-row idea-content">
				<div class="cmb-th">
					<label for="idea_content"><?php _e('Idea Content', 'picasso-ideas'); ?></label>
				</div>
				<div class="cmb-td">
					<?php wp_editor($idea_content, 'idea_content', $editor_settings); ?>
				</div>
			</div><!-- .idea-content -->

			<div class="cmb-row idea-campaign">
				<div class="cmb-th">
					<label for="_idea_campaign"><?php _e('Campaign', 'picasso-ideas'); ?></label>
				</div>
				<div class="cmb-td">
					<select name="_idea_campaign" id="_idea_campaign">
						<?php
						$campaigns = pi_get_campaigns_for_cmb2_field();

						foreach ($campaigns as $campaign_id => $campaign_title) {
							$selected = ($campaign_id == $idea_campaign) ? ' selected="selected"' : '';
							echo '<option value="' . $campaign_id . '"' . $selected . '>' . $campaign_title . '</option>';
						}
						?>
					</select>
				</div>
			</div><!-- .idea-campaign -->

			<div class="cmb-row idea-images">
				<div class="cmb-th">
					<label for="idea-images"><?php _e('Images', 'picasso-ideas'); ?></label>
				</div>
				<div class="cmb-td">
					<?php echo do_shortcode('[pi_upload_file supposed_file_type="image" button_title="' . __('Browse Image', 'picasso-ideas') . '" mime_type="image/*" meta_field_name="_idea_images" post_id="' . $idea_id . '"]'); ?>
				</div>
			</div><!-- .idea-images -->

			<div class="cmb-row idea-attachments">
				<div class="cmb-th">
					<label for="idea-attachments"><?php _e('Attachments', 'picasso-ideas'); ?></label>
				</div>
				<div class="cmb-td">
					<?php echo do_shortcode('[pi_upload_file supposed_file_type="document" button_title="' . __('Browse Attachment', 'picasso-ideas') . '" mime_type="application/*" meta_field_name="_idea_files" post_id="' . $idea_id . '"]'); ?>
				</div>
			</div><!-- .idea-attachments -->

			<div class="cmb-row idea-videos">
				<div class="cmb-th">
					<label for="idea-videos"><?php _e('Videos', 'picasso-ideas'); ?></label>
				</div>
				<div class="cmb-td">
					<?php echo do_shortcode('[pi_upload_file supposed_file_type="video" button_title="' . __('Browse Video', 'picasso-ideas') . '" mime_type="video/*" meta_field_name="_idea_videos" post_id="' . $idea_id . '"]'); ?>
				</div>
			</div><!-- .idea-videos -->

			<div class="cmb-row idea-youtube">
				<div class="cmb-th">
					<label for="_idea_youtube"><?php _e('Youtube Video', 'picasso-ideas'); ?></label>
				</div>
				<div class="cmb-td">
					<input type="text" class="regular-text" name="_idea_youtube" id="_idea_youtube" value="<?php echo $idea_youtube; ?>">
				</div>
			</div><!-- .idea-youtube -->

			<div class="form-group submit-group">
				<?php wp_nonce_field('submit_idea_nonce'); ?>
				<input type="hidden" name="idea_id" value="<?php echo $idea_id; ?>">
				<a href="<?php echo get_post_type_archive_link('idea'); ?>" class="btn btn-default"><?php _e('Cancel', 'picasso-ideas'); ?></a>
				<input type="submit" name="submit_idea" value="<?php echo $save_button_title; ?>" class="btn btn-primary">
			</div>
		</form>
	</div>
</div>