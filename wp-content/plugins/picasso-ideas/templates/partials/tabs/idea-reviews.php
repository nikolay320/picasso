<?php
global $picasso_ideas;

// check if modifier can post user review
$modifier_can_post_user_review = ($picasso_ideas['modifier_can_post_user_review'] == '0') ? false : true;

$modifier_can_edit_reviews = pi_modifier_can_edit_reviews();
$raty_class = $modifier_can_edit_reviews ? 'review-idea-rating' : 'review-idea-readonly-rating';
?>

<?php // average rating ?>
<?php if ($review_found): ?>
    <div class="average-rating">

        <div class="row">
            <div class="col-sm-3">
                <?php
                if ($review_type === 'expert') {
                    $average_title = __('Average Expert Rating', 'picasso-ideas');
                } else {
                    $average_title = __('Average User Rating', 'picasso-ideas');
                }
                ?>
                <h4 class="average-rating-title"><?php echo $average_title; ?></h4>
                <div class="picasso-idea-init-chart average-rating-in-number" <?php echo pi_get_data_for_chart($idea_id, $review_type, $review_criteria); ?>>
                    <span class="fa fa-bar-chart"></span>
                    <?php echo $average; ?>
                </div>
            </div>
            <div class="col-sm-9">
                <?php foreach ($average_in_each_criteria as $title => $score): ?>
                    <?php $slug = pi_slugify($title); ?>
                    <div class="row">
                        <div class="col-xs-5">
                            <label for="<?php echo $slug; ?>"><?php echo $title; ?></label>
                        </div>
                        <div class="col-xs-7">
                            <div class="review-idea-readonly-rating" data-score="<?php echo $score; ?>"></div>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        </div>

    </div>
<?php else: ?>
    <div class="average-rating">
        <p><?php _e('No review found!', 'picasso-ideas'); ?></p>
    </div>
<?php endif ?>

<?php
// check if logged in user already posted review
$found_modifier_review = pi_get_review($idea_id, $current_user_id, $review_type);
?>

<?php // only admin and modifiers can see all reviews ?>
<?php if (pi_ideas_modifier()): ?>
    <?php if ($review_found): ?>
        <?php $reviewer_type = ($review_type === 'expert') ? '_expert_id' : '_user_id'; ?>

        <?php
        if ($found_modifier_review) {
            if (($key = array_search($found_modifier_review, $reviews)) !== false) {
                unset($reviews[$key]);
            }
        }
        ?>

        <?php foreach ($reviews as $review_id): ?>
            <?php
            $idea_review = get_post_meta($review_id, '_idea_review', true);
            $reviewer_id = get_post_meta($review_id, $reviewer_type, true);
            $user_info = get_userdata($reviewer_id);
            $display_name = $user_info->display_name;
            ?>

            <?php if ($idea_review): ?>
                <form action="" method="POST" class="add-review-form single-idea-review">

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
                            <textarea name="comment" cols="30" rows="5" class="form-control no-hr-resize" <?php echo ($modifier_can_edit_reviews === false) ? 'disabled="disabled"' : ''; ?>><?php echo $idea_review ? $idea_review['comment'] : ''; ?></textarea>
                        </div>
                    </div>

                    <?php if ($modifier_can_edit_reviews): ?>
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
        <?php endforeach ?>
    <?php endif ?>
<?php endif ?>

<?php
if ($idea_status === 'review') {
    $proceed = true;

    // check if logged in user is an idea modifier
    // and have the permission to post user review
    // if no permission then don't show user review form
    // but if user review already found then show it
    if (pi_ideas_modifier() && $review_type === 'user' && $modifier_can_post_user_review === false && !$found_modifier_review) {
        $proceed = false;
    }

    if ($proceed) {
        $params = array(
            'idea_id'         => $idea_id,
            'campaign_id'     => $campaign_id,
            'review_type'     => $review_type,
            'idea_experts'    => $idea_experts,
            'review_criteria' => $review_criteria,
        );

        pi_render_template(IDEAS_TEMPLATE_PATH . 'partials/idea-post-review.php', $params);
    }
}
