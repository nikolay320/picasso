<?php
$enable_expert_reviews = get_post_meta($idea_id, '_idea_enable_expert_reviews', true);
$enable_user_reviews = get_post_meta($idea_id, '_idea_enable_user_reviews', true);
$enable_idea_updates = get_post_meta($idea_id, '_idea_enable_idea_updates', true);
?>
<div class="idea-tabs-container">
    <!-- Nav tabs -->
    <ul class="nav nav-tabs nav-justified idea-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#idea-details" aria-controls="idea-details" role="tab" data-toggle="tab"><?php _e('Idea Details', IDEAS_TEXT_DOMAIN); ?></a></li>
        <li role="presentation"><a href="#idea-comments" aria-controls="idea-comments" role="tab" data-toggle="tab"><?php _e('Comments', IDEAS_TEXT_DOMAIN); ?> (<?php echo get_comments_number(); ?>)</a></li>
        <?php if ($enable_expert_reviews === 'on' && ($idea_status == 'in review' || $idea_status == 'already reviewed')): ?>
            <li role="presentation"><a href="#expert-reviews" aria-controls="expert-reviews" role="tab" data-toggle="tab"><?php _e('Expert Reviews', IDEAS_TEXT_DOMAIN); ?> (<?php echo count($expert_reviews_and_average_ratings['reviews']); ?>)</a></li>
        <?php endif ?>

        <?php if ($enable_user_reviews === 'on' && ($idea_status == 'in review' || $idea_status == 'already reviewed')): ?>
            <li role="presentation"><a href="#user-reviews" aria-controls="user-reviews" role="tab" data-toggle="tab"><?php _e('User Reviews', IDEAS_TEXT_DOMAIN); ?> (<?php echo count($user_reviews_and_average_ratings['reviews']); ?>)</a></li>
        <?php endif ?>

        <?php if ($enable_idea_updates === 'on' && $idea_status == 'already reviewed'): ?>
            <li role="presentation"><a href="#idea-update" aria-controls="idea-update" role="tab" data-toggle="tab"><?php _e('Idea Update', IDEAS_TEXT_DOMAIN); ?> (<?php echo count($idea_updates); ?>)</a></li>
        <?php endif ?>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="idea-details">
            <?php klc_render_template('idea-details'); ?>
        </div>
        <div role="tabpanel" class="tab-pane" id="idea-comments">
            <?php comments_template( '', true ); ?>
        </div>
        <?php if ($enable_expert_reviews === 'on' && ($idea_status == 'in review' || $idea_status == 'already reviewed')): ?>
            <div role="tabpanel" class="tab-pane" id="expert-reviews">
                <?php
                $params = array(
                    'review_found'                  => $expert_reviews_and_average_ratings['review_found'],
                    'average_in_each_criteria'      => $expert_reviews_and_average_ratings['average_in_each_criteria'],
                    'average'                       => $expert_reviews_and_average_ratings['average'],
                    'reviews'                       => $expert_reviews_and_average_ratings['reviews'],
                    'idea_id'                       => $idea_id,
                    'idea_status'                   => $idea_status,
                    'idea_experts'                  => $idea_experts,
                    'review_type'                   => 'expert',
                    'modifier_can_post_user_review' => $modifier_can_post_user_review,
                );

                klc_render_template('reviews', $params);
                ?>
            </div>
        <?php endif ?>

        <?php if ($enable_user_reviews === 'on' && ($idea_status == 'in review' || $idea_status == 'already reviewed')): ?>
            <div role="tabpanel" class="tab-pane" id="user-reviews">
                <?php
                $params = array(
                    'review_found'                  => $user_reviews_and_average_ratings['review_found'],
                    'average_in_each_criteria'      => $user_reviews_and_average_ratings['average_in_each_criteria'],
                    'average'                       => $user_reviews_and_average_ratings['average'],
                    'reviews'                       => $user_reviews_and_average_ratings['reviews'],
                    'idea_id'                       => $idea_id,
                    'idea_status'                   => $idea_status,
                    'idea_experts'                  => $idea_experts,
                    'review_type'                   => 'user',
                    'modifier_can_post_user_review' => $modifier_can_post_user_review,
                );

                klc_render_template('reviews', $params);
                ?>
            </div>
        <?php endif ?>

        <?php if ($enable_idea_updates === 'on' && $idea_status == 'already reviewed'): ?>
            <div role="tabpanel" class="tab-pane" id="idea-update">
                <?php klc_render_template('idea-updates', array('idea_updates' => $idea_updates)); ?>
            </div>
        <?php endif ?>
    </div>
</div>