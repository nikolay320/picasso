<?php if ($idea_updates): ?>
	<?php foreach ($idea_updates as $idea_update_id): ?>
		<?php
		$idea_update = get_post_meta($idea_update_id, '_idea_update', true);
		$user_id = get_post_meta($idea_update_id, '_user_id', true);
		$user_info = get_userdata($user_id);
		$display_name = $user_info->display_name;
		?>
		<div class="row single-idea-update">
	        <div class="col-sm-2 col-md-1">
	            <div class="expert-avatar"><?php echo get_avatar($user_id, '32');  ?></div>
	            <span class="review-posted-on visible-xs"><?php echo $display_name . ' ' . klc_review_posted_x_days_ago($idea_update_id); ?></span>
	        </div>
	        <div class="col-sm-10 col-md-11">
	        	<?php echo nl2br($idea_update); ?>
	            
	            <span class="review-posted-on hide-xs">
	                <?php
	                if (function_exists('bp_core_get_userlink')) {
	                    echo bp_core_get_userlink($user_id) . ' ' . klc_review_posted_x_days_ago($idea_update_id);
	                } else {
	                    echo $display_name . ' ' . klc_review_posted_x_days_ago($idea_update_id);
	                }
	                ?>
	            </span>
	        </div>
		</div>
	<?php endforeach ?>
<?php else: ?>
	<p><?php _e('No update found!', IDEAS_TEXT_DOMAIN); ?></p>
<?php endif ?>