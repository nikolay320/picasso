<?php
$show_form = empty($_GET['idea_submitted']) ? true : false;
?>

<div class="row">
	<div class="col-md-2 col-sm-3">
	    <div class="idea-steps">
	    	<div class="panel<?php echo ($show_form === true) ? ' panel-active' : ''; ?>">
	    		<div class="panel-body"><?php _e('Add Idea', 'ideas_plugin'); ?></div>
	    	</div>
	    	<div class="panel<?php echo ($show_form === false) ? ' panel-active' : ''; ?>">
	    		<div class="panel-body"><?php _e('Publish Idea', 'ideas_plugin'); ?></div>
	    	</div>
	    </div>
	</div>

	<div class="col-md-8 col-sm-6">
	    <?php if ($show_form === true): ?>
	    	<?php echo do_shortcode('[idea_submission_form]');  ?>
	    <?php else: ?>
	    	<?php $idea = get_post('2343'); ?>
	    	<!-- idea title -->
	    	<p>
	    		<?php echo '<strong>' . __('Idea Title', 'ideas_plugin') . '</strong>:'; ?>
	    	</p>

	    	<p><?php echo $idea->post_title; ?></p>

	    	<!-- idea content -->
	    	<p>
	    		<?php echo '<strong>' . __('Idea Content', 'ideas_plugin') . '</strong>:'; ?>
	    	</p>

	    	<p><?php echo $idea->post_content; ?></p>

	    	<!-- idea images -->
	    	<?php if ($idea_images = get_post_meta($idea->ID, '_idea_images', true)): ?>
	    	<div>
	    		<p><?php echo '<strong>' . __('Idea Images', 'ideas_plugin') . '</strong>:'; ?></p>

	    		<div class="row idea-images">
	    			<?php foreach ($idea_images as $idea_image_id => $idea_image_url): ?>
	    				<div class="col-xs-6 col-sm-6 col-md-3 image">
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
	    	<?php if ($idea_files = get_post_meta($idea->ID, '_idea_files', true)): ?>
	    	<div>
	    		<p><?php echo '<strong>' . __('Idea Files', 'ideas_plugin') . '</strong>:'; ?></p>

	    		<div class="row idea-files">
	    			<?php foreach ($idea_files as $idea_file_id => $idea_file_url): ?>
	    				<div class="col-xs-6 col-sm-6 col-md-3 image">
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
	        <?php if ($idea_videos = get_post_meta($idea->ID, '_idea_videos', true)): ?>
	        	<div>
	        		<p><?php echo '<strong>' . __('Idea Videos', 'ideas_plugin') . '</strong>:'; ?></p>

	        		<div class="row idea-videos">
	        			<?php foreach ($idea_videos as $idea_video_id => $idea_video_url): ?>
	        				<div class="col-xs-12 col-sm-12 col-md-6 video">
	    	    				<?php
	    	    				$video_type = explode('/', get_post_mime_type($idea_video_id));
	    	    				echo do_shortcode('[video width="600" height="300" ' . $video_type[1] . '="' . $idea_video_url . '"]');
	    	    				?>
	        				</div>
	        			<?php endforeach ?>
	        		</div>
	        	</div>
	        <?php endif ?>

	        <!-- idea youtube video -->
	        <?php if ($idea_youtube_video = get_post_meta($idea->ID, '_idea_youtube', true)): ?>
	        	<div>
	        		<p><?php echo '<strong>' . __('Idea Youtube Video', 'ideas_plugin') . '</strong>:'; ?></p>

	        		<?php
	        		preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $idea_youtube_video, $match);
	        		$idea_youtube_video_id = $match[1];
	        		?>

	        		<div class="idea-youtube-video">
	        			<iframe width="600" height="300" src="https://www.youtube.com/embed/<?php echo $idea_youtube_video_id; ?>" frameborder="0" allowfullscreen></iframe>
	        		</div>
	        	</div>
	        <?php endif ?>
	    <?php endif ?>
	</div>

	<div class="col-md-2 col-sm-3">
	    <div class="similar-ideas">
	        <h3><?php _e('Similar Ideas', 'ideas_plugin'); ?></h3>
	        <div class="similar-ideas-wrapper">
	        	<?php if ($show_form === false): ?>
		        	<?php $similar_ideas = klc_search_ideas($idea->post_title); ?>
	        		<?php unset($similar_ideas[$idea->ID]); ?>
		        	<?php if ($similar_ideas): ?>
		        		<?php foreach ($similar_ideas as $idea_id => $idea_title): ?>
		        			<div class="similar-idea">
		        				<a href="<?php echo get_the_permalink($idea_id); ?>"><?php echo $idea_title; ?></a>
		        			</div>
		        		<?php endforeach ?>
		        	<?php else: ?>
		        		<?php echo __('No similar idea found!', 'ideas_plugin'); ?>
		        	<?php endif ?>
	        	<?php endif ?>
	        </div>
	    </div>
	</div>
</div>