<?php if ($idea_updates): ?>

	<?php
	// check if logged in user already posted an idea update
	// if found then don't show this update here
	// $found_logged_in_user_idea_update = pi_get_idea_update($idea_id, $current_user_id);

	// if ($found_logged_in_user_idea_update) {
	// 	if (($key = array_search($found_logged_in_user_idea_update, $idea_updates)) !== false) {
	// 		unset($idea_updates[$key]);
	// 	}
	// }
	?>

	<?php foreach ($idea_updates as $idea_update_id): ?>
		<?php
		$idea_update = get_post_meta($idea_update_id, '_idea_update', true);
		$user_id = get_post_meta($idea_update_id, '_user_id', true);
		$user_info = get_userdata($user_id);
		$display_name = $user_info->display_name;
		?>
		<div class="single-idea-update">
			<div class="row">
		        <div class="col-sm-2 col-md-1">
		            <div class="expert-avatar"><?php echo get_avatar($user_id, '32');  ?></div>
		            <span class="review-posted-on visible-xs"><?php echo $display_name . ' ' . pi_posted_on($idea_update_id); ?></span>
		        </div>
		        <div class="col-sm-10 col-md-11">
		        	<?php echo nl2br($idea_update); ?>
		            
		            <span class="review-posted-on hide-xs">
		                <?php
		                if (function_exists('bp_core_get_userlink')) {
		                    echo bp_core_get_userlink($user_id) . ' ' . pi_posted_on($idea_update_id);
		                } else {
		                    echo $display_name . ' ' . pi_posted_on($idea_update_id);
		                }
		                ?>
		            </span>
		        </div>
			</div>
		</div>
	<?php endforeach ?>
<?php else: ?>
	<p><?php _e('No update found!', 'picasso-ideas'); ?></p>
<?php endif ?>

<?php
if (pi_check_for_idea_update_owner($idea_id)) {
	$params = array(
		'idea_id'         => $idea_id,
		'current_user_id' => $current_user_id,
	);

	pi_render_template(IDEAS_TEMPLATE_PATH . 'partials/idea-post-update.php', $params);
}