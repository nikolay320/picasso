<?php
$modifier = pi_ideas_modifier();

$current_user_id = get_current_user_id();

if ($review_type === 'expert') {
	$review_id = pi_get_review($idea_id, $current_user_id, $review_type, false);
} else {
	$review_id = pi_get_review($idea_id, $current_user_id, $review_type);
}

$reviewer_type = ($review_type === 'expert') ? '_expert_id' : '_user_id';

if ($review_id) {
    $idea_review = get_post_meta($review_id, '_idea_review', true);
    $reviewer_id = get_post_meta($review_id, $reviewer_type, true);
} else {
    $idea_review = '';
    $reviewer_id = $current_user_id;
}

$user_info = get_userdata($reviewer_id);
$display_name = $user_info->display_name;

if ($review_type === 'expert') {
    // meet deadline
    $idea_deadline = get_post_meta($idea_id, '_idea_expert_reviews_deadline', true);
    $today = current_time('Y-m-d');

    if (!$modifier && $idea_deadline && strtotime($today) > strtotime($idea_deadline)) {
        $enable_review = false;
        $raty_class = 'review-idea-readonly-rating';
    } else {
        $enable_review = true;
        $raty_class = 'review-idea-rating';
    }
} elseif ($review_type === 'user') {
	// meet deadline
	$idea_deadline = get_post_meta($idea_id, '_idea_user_reviews_deadline', true);
	$today = current_time('Y-m-d');

	if (!$modifier && $idea_deadline && strtotime($today) > strtotime($idea_deadline)) {
	    $enable_review = false;
	    $raty_class = 'review-idea-readonly-rating';
	} else {
	    $enable_review = true;
	    $raty_class = 'review-idea-rating';
	}
}

$proceed = true;

// Normally expert can't post user review
// but if an expert has already posted his user review then he can modify his review
if ($review_type === 'user' && in_array($current_user_id, $idea_experts) && !$review_id) {
	$proceed = false;
}

// check if logged in user is an idea expert
// otherwise don't show the review form
if ($review_type === 'expert' && !in_array($current_user_id, $idea_experts)) {
	$proceed = false;
}
?>

<?php if ($proceed): ?>
	<form action="" method="POST" class="add-review-form">

		<div class="row">
			<div class="col-sm-7">
				<div class="row">
					<div class="col-sm-2">
						<div class="expert-avatar"><?php echo get_avatar($reviewer_id, '32');  ?></div>
						<?php if ($idea_review): ?>
						    <span class="review-posted-on visible-xs">
						        <?php
						        if (function_exists('bp_core_get_userlink')) {
						            echo bp_core_get_userlink($reviewer_id) . ' ' . pi_posted_on($review_id);
						        } else {
						            echo $display_name . ' ' . pi_posted_on($review_id);
						        }
						        ?>
						    </span>
						<?php elseif ($enable_review): ?>
						    <?php echo '<p class="review-pending-notification visible-xs">' . __('Post your review now!', 'picasso-ideas') . '</p>'; ?>
						<?php endif ?>
					</div>

					<div class="col-sm-10">
					    <label><b><?php _e('Rating', 'picasso-ideas'); ?></b></label>
					    <br />
					    <br />

					    <?php
					    if ($review_criteria) {
					        $count_review_criteria = count($review_criteria);
					        $increment = 0;

					        foreach ($review_criteria as $criteria) {
					            $increment++;
					            $slug = pi_slugify($criteria);
					            ?>
					            <div class="row">
					                <div class="col-xs-7">
					                    <label for="<?php echo $slug; ?>"><?php echo $criteria; ?></label>
					                    <?php if ($increment === $count_review_criteria): ?>
					                        <?php if ($idea_review): ?>
					                            <span class="review-posted-on hide-xs">
					                                <?php
					                                if (function_exists('bp_core_get_userlink')) {
					                                    echo bp_core_get_userlink($reviewer_id) . ' ' . pi_posted_on($review_id);
					                                } else {
					                                    echo $display_name . ' ' . pi_posted_on($review_id);
					                                }
					                                ?>
					                            </span>
					                        <?php elseif ($enable_review): ?>
					                        	<?php echo '<p class="review-pending-notification hide-xs">' . __('Post your review now!', 'picasso-ideas') . '</p>'; ?>
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
				<label for="comment"><b><?php _e('Comment', 'picasso-ideas'); ?></b></label>
				<br />
				<br />
				<textarea name="comment" cols="30" rows="5" class="form-control no-hr-resize" <?php echo ($enable_review === false) ? 'disabled="disabled"' : ''; ?>><?php echo $idea_review ? $idea_review['comment'] : ''; ?></textarea>
			</div>
		</div>

		<?php if ($enable_review): ?>
			<div class="row">
				<div class="col-sm-12">
					<div class="pull-right">
						<?php wp_nonce_field('idea_review_nonce'); ?>

						<input type="hidden" name="idea_id" value="<?php echo $idea_id; ?>">
						<input type="hidden" name="review_type" value="<?php echo $review_type; ?>">
						<input type="hidden" name="review_id" value="<?php echo $review_id; ?>">
						<input type="hidden" name="reviewer_id" value="<?php echo $reviewer_id; ?>">

						<input type="submit" name="post_idea_review" class="btn btn-primary" value="<?php _e('Submit', 'picasso-ideas'); ?>" />
					</div>
				</div>
			</div>
		<?php endif ?>

	</form>
<?php endif ?>
