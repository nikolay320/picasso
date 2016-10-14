<?php
$modifier_can_edit_reviews = klc_modifier_can_edit_reviews();

global $klc_ideas;
$review_criteria = $klc_ideas['review_criteria'];

$raty_class = $modifier_can_edit_reviews ? 'review-idea-rating' : 'review-idea-average-rating';

// average rating
if ($review_found) {
    ?>
    <div class="row average-rating">
        <div class="col-sm-3">
            <?php
            if ($review_type === 'expert') {
                $average_title = __('Average Expert Rating', IDEAS_TEXT_DOMAIN);
            } else {
                $average_title = __('Average User Rating', IDEAS_TEXT_DOMAIN);
            }
            ?>
            <h4 class="average-rating-title"><?php echo $average_title; ?></h4>
            <span class="average-rating-in-number">
                <?php
                if ($review_type === 'user') {
                    echo '<span class="fa fa-bar-chart"></span>';
                }
                echo $average;
                ?>
            </span>
        </div>
        <div class="col-sm-9">
            <?php foreach ($average_in_each_criteria as $title => $score): ?>
                <?php $slug = klc_slugify($title); ?>
                <div class="row">
                    <div class="col-xs-5">
                        <label for="<?php echo $slug; ?>"><?php echo $title; ?></label>
                    </div>
                    <div class="col-xs-7">
                        <div class="review-idea-average-rating" data-score="<?php echo $score; ?>"></div>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    </div>
    <?php
}
// no review found
else {
    ?>
    <div class="average-rating">
        <p><?php _e('No review found!', IDEAS_TEXT_DOMAIN); ?></p>
    </div>
    <?php
}

// idea modifier/admin
if (klc_ideas_modifier()) {
    if ($review_found) {
        $reviewer_type = ($review_type === 'expert') ? '_expert_id' : '_user_id';

        if ($review_type === 'expert') {
            $button_class = 'idea-expert-review-button';
            $action = 'klc_post_expert_review_for_idea';
        } elseif ($review_type === 'user') {
            $button_class = 'idea-user-review-button';
            $action = 'klc_post_user_review_for_idea';
        }

        foreach ($reviews as $review_id) {
            $idea_review = get_post_meta($review_id, '_idea_review', true);
            $reviewer_id = get_post_meta($review_id, $reviewer_type, true);
            $user_info = get_userdata($reviewer_id);
            $display_name = $user_info->display_name;

            if ($idea_review) {
                ?>
                <?php if ($modifier_can_edit_reviews): ?>
                    <form action="" method="POST" class="single-idea-review">
                <?php endif ?>
                <div class="row expert-ideas">
                    <div class="col-sm-7">
                        <div class="row">
                            <div class="col-sm-2">
                                <div class="expert-avatar"><?php echo get_avatar($reviewer_id, '32');  ?></div>
                                <span class="review-posted-on visible-xs">
                                    <?php
                                    if (function_exists('bp_core_get_userlink')) {
                                        echo bp_core_get_userlink($reviewer_id) . ' ' . klc_review_posted_x_days_ago($review_id);
                                    } else {
                                        echo $display_name . ' ' . klc_review_posted_x_days_ago($review_id);
                                    }
                                    ?>
                                </span>
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
                                                    <span class="review-posted-on hide-xs">
                                                        <?php
                                                        if (function_exists('bp_core_get_userlink')) {
                                                            echo bp_core_get_userlink($reviewer_id) . ' ' . klc_review_posted_x_days_ago($review_id);
                                                        } else {
                                                            echo $display_name . ' ' . klc_review_posted_x_days_ago($review_id);
                                                        }
                                                        ?>
                                                    </span>
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
                        <textarea name="idea_comment" id="comment" cols="30" rows="10" class="form-control no-hr-resize" <?php echo ($modifier_can_edit_reviews === false) ? 'disabled="disabled"' : ''; ?>><?php echo $idea_review ? $idea_review['comment'] : ''; ?></textarea>
                    </div>
                </div>
                <?php if ($modifier_can_edit_reviews): ?>
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
                <?php
            } // single idea review
        }
    }
}

// experts review or users review
if (klc_ideas_modifier() && $review_type === 'user' && $modifier_can_post_user_review == '0') {
    $render_post_review = false;
} else {
    $render_post_review = true;
}

if ($render_post_review) {
    $params = array(
        'idea_id'         => $idea_id,
        'review_type'     => $review_type,
        'review_criteria' => $review_criteria,
        'idea_experts'    => $idea_experts,
    );

    klc_render_template('post-review', $params);
}