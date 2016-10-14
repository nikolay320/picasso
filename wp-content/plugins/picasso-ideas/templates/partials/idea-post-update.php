<?php
$review_id = pi_get_idea_update($idea_id, $current_user_id);
$idea_update = $review_id ? get_post_meta($review_id, '_idea_update', true) : '';

$title = $review_id ? __('Edit Idea Update', 'picasso-ideas') : __('Post Idea Update', 'picasso-ideas');
$button_title = $review_id ? __('Update', 'picasso-ideas') : __('Submit', 'picasso-ideas');
?>

<form action="" method="POST" class="post-idea-update">
    <h4><?php echo $title; ?></h4>

	<textarea name="idea_update" id="comment" cols="15" rows="3" class="form-control no-hr-resize"><?php echo $idea_update; ?></textarea>

    <?php wp_nonce_field('post_idea_update_nonce'); ?>

    <input type="hidden" name="user_id" value="<?php echo $current_user_id; ?>">
    <input type="hidden" name="idea_id" value="<?php echo $idea_id; ?>">
    <input type="hidden" name="review_id" value="<?php echo $review_id; ?>">

	<input type="submit" class="btn btn-primary" name="post_idea_update" value="<?php echo $button_title; ?>" />
</form>