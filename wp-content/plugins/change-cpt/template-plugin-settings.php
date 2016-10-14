<h2><?php _e('Change CPT', 'changecpt'); ?></h2>

<?php // Show success message ?>
<?php if (!empty($_GET['updated'])): ?>
	<div id="message" class="updated notice is-dismissible" style="margin-left: 0">
		<p><?php _e('Successfully done!', 'changecpt'); ?></p>
		<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
	</div>
<?php endif ?>

<form action="" method="post">

	<p><?php _e('Change old post metas of ideas to idea', 'changecpt'); ?></p>

	<input type="hidden" name="action" value="update_post_metas_of_ideas">
	<input type="submit" class="button" name="change_cpt" value="<?php _e('Update post metas of IDEAS', 'changecpt'); ?>">

</form>

<form action="" method="post">

	<p><?php _e('Delete old post metas of ideas', 'changecpt'); ?></p>

	<input type="hidden" name="action" value="delete_post_metas_of_ideas">
	<input type="submit" class="button" name="change_cpt" value="<?php _e('Delete post metas of IDEAS', 'changecpt'); ?>">

</form>

<form action="" method="post">

	<p><?php _e('Change old post metas of campaigns to campaign', 'changecpt'); ?></p>

	<input type="hidden" name="action" value="update_post_metas_of_campaigns">
	<input type="submit" class="button" name="change_cpt" value="<?php _e('Update post metas of CAMPAIGNS', 'changecpt'); ?>">

</form>

<form action="" method="post">

	<p><?php _e('Delete old post metas of campaigns', 'changecpt'); ?></p>

	<input type="hidden" name="action" value="delete_post_metas_of_campaigns">
	<input type="submit" class="button" name="change_cpt" value="<?php _e('Delete post metas of CAMPAIGNS', 'changecpt'); ?>">

</form>

<form action="" method="post">

	<p><?php _e('Change ideas to idea', 'changecpt'); ?></p>

	<input type="hidden" name="action" value="change_ideas_to_idea">
	<input type="submit" class="button" name="change_cpt" value="<?php _e('Change IDEAS to IDEA', 'changecpt'); ?>">

</form>

<form action="" method="post">

	<p><?php _e('Change campaigns to campaign', 'changecpt'); ?></p>

	<input type="hidden" name="action" value="change_campaigns_to_campaign">
	<input type="submit" class="button" name="change_cpt" value="<?php _e('Change CAMPAIGNS to CAMPAIGN', 'changecpt'); ?>">

</form>