<?php
$current_user_id = get_current_user_id();
$current_user_review = klc_get_review($idea_id, $current_user_id, $review_type);
$reviewer_type = ($review_type === 'expert') ? '_expert_id' : '_user_id';

if ($current_user_review) {
    $review_id = $current_user_review[0];
    $idea_review = get_post_meta($review_id, '_idea_review', true);
    $reviewer_id = get_post_meta($review_id, $reviewer_type, true);
} else {
    $review_id = '';
    $idea_review = '';
    $reviewer_id = $current_user_id;
}

$user_info = get_userdata($reviewer_id);
$display_name = $user_info->display_name;

if ($review_type === 'expert') {
    $button_class = 'idea-expert-review-button';
    $action = 'klc_post_expert_review_for_idea';

    // meet deadline
    $idea_deadline = get_post_meta($idea_id, '_idea_deadline', true);
    $today = current_time('Y-m-d');

    if ($idea_deadline && strtotime($today) > strtotime($idea_deadline)) {
        $enable_review = false;
        $raty_class = 'review-idea-average-rating';
    } else {
        $enable_review = true;
        $raty_class = 'review-idea-rating';
    }
} elseif ($review_type === 'user') {
	$button_class = 'idea-user-review-button';
	$action = 'klc_post_user_review_for_idea';

	// meet deadline
	$idea_deadline = get_post_meta($idea_id, '_idea_user_reviews_deadline', true);
	$today = current_time('Y-m-d');

	if ($idea_deadline && strtotime($today) > strtotime($idea_deadline)) {
	    $enable_review = false;
	    $raty_class = 'review-idea-average-rating';
	} else {
	    $enable_review = true;
	    $raty_class = 'review-idea-rating';
	}
}

if ($review_type === 'expert' && in_array($current_user_id, $idea_experts) && $current_user_review) {
	$proceed = true;
} elseif ($review_type === 'user') {
	$proceed = true;
} else {
	$proceed = false;
}

if ($review_type === 'user' && in_array($current_user_id, $idea_experts) && !$current_user_review) {
	$proceed = false;
}
?>

<?php if ($proceed): ?>
	<?php if ($enable_review): ?>
		<form action="" method="POST" class="single-idea-review">
	<?php endif ?>
	    <div class="row expert-ideas">
	    	<?php // show message if deadline is ended ?>
			<?php if ($enable_review === false): ?>
				<?php echo '<div class="col-sm-12"><div class="alert alert-info">' . __('Review deadline has been ended. You can\'t review now.', IDEAS_TEXT_DOMAIN) . '</div></div>'; ?>
			<?php endif ?>
	        <div class="col-sm-7">
	            <div class="row">
	                <div class="col-sm-2">
	                    <div class="expert-avatar"><?php echo get_avatar($reviewer_id, '32');  ?></div>
	                    <?php if ($idea_review): ?>
	                        <span class="review-posted-on visible-xs">
	                            <?php
	                            if (function_exists('bp_core_get_userlink')) {
	                                echo bp_core_get_userlink($reviewer_id) . ' ' . klc_review_posted_x_days_ago($review_id);
	                            } else {
	                                echo $display_name . ' ' . klc_review_posted_x_days_ago($review_id);
	                            }
	                            ?>
	                        </span>
	                    <?php elseif ($enable_review): ?>
	                        <?php echo '<p class="review-pending-notification visible-xs">' . __('Post your review now!', IDEAS_TEXT_DOMAIN) . '</p>'; ?>
	                    <?php endif ?>
	                </div>
	                <div class="col-sm-10">
	                    <label><b><?php _e('Rating', IDEAS_TEXT_DOMAIN); ?></b></label>
	                    <br />
	                    <br />

	                    <?php
	                    if ($review_criteria) {
	                        $count_review_criteria = count($review_criteria);
	                        $increment = 0;

	                        foreach ($review_criteria as $criteria) {
	                            $increment++;
	                            $slug = klc_slugify($criteria);
	                            ?>
	                            <div class="row">
	                                <div class="col-xs-7">
	                                    <label for="<?php echo $slug; ?>"><?php echo $criteria; ?></label>
	                                    <?php if ($increment === $count_review_criteria): ?>
	                                        <?php if ($idea_review): ?>
	                                            <span class="review-posted-on hide-xs">
	                                                <?php
	                                                if (function_exists('bp_core_get_userlink')) {
	                                                    echo bp_core_get_userlink($reviewer_id) . ' ' . klc_review_posted_x_days_ago($review_id);
	                                                } else {
	                                                    echo $display_name . ' ' . klc_review_posted_x_days_ago($review_id);
	                                                }
	                                                ?>
	                                            </span>
	                                        <?php elseif ($enable_review): ?>
	                                        	<?php echo '<p class="review-pending-notification hide-xs">' . __('Post your review now!', IDEAS_TEXT_DOMAIN) . '</p>'; ?>
	                                        <?php endif ?>
	                                    <?php endif ?>
	                                </div>
	                                <div class="col-xs-5">
	                                    <div class="pull-right <?php echo $raty_class; ?>" data-review-category="<?php echo $slug; ?>" data-score="<?php echo ($idea_review && key_exists($slug, $idea_review['rating'])) ? $idea_review['rating'][$slug] : ''; ?>"></div>
	                                </div>
	                            </div>
	                            <?php
	                        }
	                    }
	                    ?>

	                </div>
	            </div>
	        </div>
	        <div class="col-sm-5">
	            <label for="comment"><b><?php _e('Comment', IDEAS_TEXT_DOMAIN); ?></b></label>
	            <textarea name="idea_comment" id="comment" cols="30" rows="10" class="form-control no-hr-resize" <?php echo ($enable_review === false) ? 'disabled="disabled"' : ''; ?>><?php echo $idea_review ? $idea_review['comment'] : ''; ?></textarea>
	        </div>
	    </div>
	<?php if ($enable_review): ?>
		    <div class="row">
		        <div class="col-sm-12">
		            <div class="pull-right">
		                <span class="idea-loading fa fa-spinner fa-spin"></span>
		                <input type="submit" class="btn btn-primary <?php echo $button_class; ?>" value="<?php _e('Submit', IDEAS_TEXT_DOMAIN); ?>" data-idea-id="<?php echo $idea_id; ?>" data-review-id="<?php echo $review_id; ?>" data-user-id="<?php echo $reviewer_id; ?>" data-ajax-url="<?php echo admin_url('admin-ajax.php'); ?>" data-action="<?php echo $action; ?>" />
		            </div>
		        </div>
		    </div>
		</form>
	<?php endif ?>
<?php endif ?>