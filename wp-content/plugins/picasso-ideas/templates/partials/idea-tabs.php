<div class="idea-tabs-container">
    <!-- Nav tabs -->
    <ul class="nav nav-tabs nav-justified idea-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#idea-details" aria-controls="idea-details" role="tab" data-toggle="tab"><?php _e('Idea Details', 'picasso-ideas'); ?></a></li>
        <li role="presentation"><a href="#idea-comments" aria-controls="idea-comments" role="tab" data-toggle="tab"><?php _e('Comments', 'picasso-ideas'); ?> (<?php echo get_comments_number(); ?>)</a></li>
        <?php if ($disable_expert_reviews !== 'on' && ($idea_status == 'review' || $idea_status == 'in-project')): ?>
            <li role="presentation"><a href="#expert-reviews" aria-controls="expert-reviews" role="tab" data-toggle="tab"><?php _e('Expert Reviews', 'picasso-ideas'); ?> (<?php echo count($expert_reviews_and_average_ratings['reviews']); ?>)</a></li>
        <?php endif ?>

        <?php if ($disable_user_reviews !== 'on' && ($idea_status == 'review' || $idea_status == 'in-project')): ?>
            <li role="presentation"><a href="#user-reviews" aria-controls="user-reviews" role="tab" data-toggle="tab"><?php _e('User Reviews', 'picasso-ideas'); ?> (<?php echo count($user_reviews_and_average_ratings['reviews']); ?>)</a></li>
        <?php endif ?>

        <?php if ($disable_idea_updates !== 'on' && $idea_status == 'in-project'): ?>
            <li role="presentation"><a href="#idea-update" aria-controls="idea-update" role="tab" data-toggle="tab"><?php _e('Idea Update', 'picasso-ideas'); ?> (<?php echo count($idea_updates); ?>)</a></li>
        <?php endif ?>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="idea-details">
            <?php pi_render_template(IDEAS_TEMPLATE_PATH . 'partials/tabs/idea-details.php'); ?>
        </div>
        <div role="tabpanel" class="tab-pane" id="idea-comments">
            <?php comments_template( '', true ); ?>
        </div>
        <?php if ($disable_expert_reviews !== 'on' && ($idea_status == 'review' || $idea_status == 'in-project')): ?>
            <div role="tabpanel" class="tab-pane" id="expert-reviews">
                <?php
                $params = array(
                    'review_found'             => $expert_reviews_and_average_ratings['review_found'],
                    'average_in_each_criteria' => $expert_reviews_and_average_ratings['average_in_each_criteria'],
                    'average'                  => $expert_reviews_and_average_ratings['average'],
                    'reviews'                  => $expert_reviews_and_average_ratings['reviews'],
                    'idea_id'                  => $idea_id,
                    'campaign_id'              => $campaign_id,
                    'idea_status'              => $idea_status,
                    'idea_experts'             => $idea_experts,
                    'review_type'              => 'expert',
                    'current_user_id'          => $current_user_id,
                    'review_criteria'          => $review_criteria,
                );

                pi_render_template(IDEAS_TEMPLATE_PATH . 'partials/tabs/idea-reviews.php', $params);
                ?>
            </div>
        <?php endif ?>

        <?php if ($disable_user_reviews !== 'on' && ($idea_status == 'review' || $idea_status == 'in-project')): ?>
            <div role="tabpanel" class="tab-pane" id="user-reviews">
                <?php
                $params = array(
                    'review_found'             => $user_reviews_and_average_ratings['review_found'],
                    'average_in_each_criteria' => $user_reviews_and_average_ratings['average_in_each_criteria'],
                    'average'                  => $user_reviews_and_average_ratings['average'],
                    'reviews'                  => $user_reviews_and_average_ratings['reviews'],
                    'idea_id'                  => $idea_id,
                    'campaign_id'              => $campaign_id,
                    'idea_status'              => $idea_status,
                    'idea_experts'             => $idea_experts,
                    'review_type'              => 'user',
                    'current_user_id'          => $current_user_id,
                    'review_criteria'          => $review_criteria,
                );

                pi_render_template(IDEAS_TEMPLATE_PATH . 'partials/tabs/idea-reviews.php', $params);
                ?>
            </div>
        <?php endif ?>

        <?php if ($disable_idea_updates !== 'on' && $idea_status == 'in-project'): ?>
            <div role="tabpanel" class="tab-pane" id="idea-update">
                <?php
                $params = array(
                    'idea_updates'    => $idea_updates,
                    'idea_id'         => $idea_id,
                    'current_user_id' => $current_user_id,
                );

                pi_render_template(IDEAS_TEMPLATE_PATH . 'partials/tabs/idea-updates.php', $params); ?>
            </div>
        <?php endif ?>
    </div>
</div>
