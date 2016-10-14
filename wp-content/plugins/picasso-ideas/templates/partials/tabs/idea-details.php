<?php
the_content();
$idea_id = get_the_ID();
?>

<div class="idea-attached-files">
	<!-- idea images -->
	<?php if ($idea_images = get_post_meta($idea_id, '_idea_images', true)): ?>
	<div>

		<br />
		<div class="hr-title hr-long"><abbr><?php _e('Images', 'picasso-ideas'); ?></abbr></div>
		<br />

		<div class="row idea-images">
			<?php foreach ($idea_images as $idea_image_id => $idea_image_url): ?>
				<div class="single-image">
					<div class="thumbnail">
						<a href="<?php echo $idea_image_url; ?>" rel="prettyPhoto">
							<img src="<?php echo wp_get_attachment_thumb_url($idea_image_id); ?>" />
						</a>
					</div>
				</div>
			<?php endforeach ?>
		</div>
	</div>
	<?php endif ?>

	<!-- idea files -->
	<?php if ($idea_files = get_post_meta($idea_id, '_idea_files', true)): ?>
	<div>

		<br />
		<div class="hr-title hr-long"><abbr><?php _e('Attachments', 'picasso-ideas'); ?></abbr></div>
		<br />

		<div class="row idea-files">
			<?php foreach ($idea_files as $idea_file_id => $idea_file_url): ?>
				<div class="single-file">
					<div class="thumbnail">
						<?php
						// base path, /var/www/html/picasso-dev/wp-content/plugins/ideas/
						$base = IDEAS_PLUGIN_PATH . 'assets/img/file_types/';

						// url, http://localhost/picasso-dev/wp-content/plugins/ideas/
						$url = plugins_url('ideas/assets/img/file_types/');

						// file type
						$file_type = wp_check_filetype($idea_file_url);

						if (!file_exists($base . $file_type['ext'] . '.png')) {
							$icon_url = $url . 'label.png';
						} else {
							$icon_url = $url . $file_type['ext'] . '.png';
						}
						?>
						<a href="<?php echo $idea_file_url; ?>">
							<img src="<?php echo $icon_url; ?>" title="<?php echo $idea_file_url; ?>" />
						</a>
					</div>
				</div>
			<?php endforeach ?>
		</div>
	</div>
	<?php endif ?>

	<!-- idea videos -->
	<?php if ($idea_videos = get_post_meta($idea_id, '_idea_videos', true)): ?>
		<div>

			<br />
			<div class="hr-title hr-long"><abbr><?php _e('Videos', 'picasso-ideas'); ?></abbr></div>
			<br />

			<div class="row idea-videos">
				<?php foreach ($idea_videos as $idea_video_id => $idea_video_url): ?>
					<div class="col-xs-12 col-sm-12 col-md-6">
	                    <div class="video-wrapper">
	                    	<?php
	                    	$video_type = explode('/', get_post_mime_type($idea_video_id));
	                    	echo do_shortcode('[video width="600" height="300" ' . $video_type[1] . '="' . $idea_video_url . '"]');
	                    	?>
	                    </div>
					</div>
				<?php endforeach ?>
			</div>
		</div>
	<?php endif ?>

	<!-- idea youtube video -->
	<?php if ($idea_youtube_video = get_post_meta($idea_id, '_idea_youtube', true)): ?>
		<div>

			<br />
			<div class="hr-title hr-long"><abbr><?php _e('Youtube Video', 'picasso-ideas'); ?></abbr></div>
			<br />

			<?php
			preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $idea_youtube_video, $match);
			$idea_youtube_video_id = $match[1];
			?>

			<div class="idea-youtube-video">
				<iframe width="600" height="300" src="https://www.youtube.com/embed/<?php echo $idea_youtube_video_id; ?>" frameborder="0" allowfullscreen></iframe>
			</div>
		</div>
	<?php endif ?>
</div>