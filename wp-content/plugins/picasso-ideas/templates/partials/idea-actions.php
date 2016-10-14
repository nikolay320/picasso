<?php
$disable_expert_reviews = get_post_meta($post_id, '_idea_disable_expert_reviews', true);
$disable_user_reviews = get_post_meta($post_id, '_idea_disable_user_reviews', true);
$disable_idea_updates = get_post_meta($post_id, '_idea_disable_idea_updates', true);

// find experts
$idea_experts = pi_get_experts_for_given_idea($post_id);

// idea status
$idea_status = get_post_meta($post_id, '_idea_status', true);
?>

<div class="table idea-actions">
    <?php if ($idea_experts): ?>
        <div class="table-cell idea-experts">
            <?php
            echo '<span class="text">' . __('Experts', 'picasso-ideas') . ': ' . '</span>';

            foreach ($idea_experts as $expert_id) {
                $args = array(
                    'post_type'   => 'idea_review',
                    'post_status' => 'publish',
                    'numberposts' => -1,
                    'fields'      => 'ids',
                    'meta_query'  => array(
                        'relation' => 'AND',
                        array(
                            'key'     => '_idea_id',
                            'value'   => $post_id,
                            'compare' => '=',
                        ),
                        array(
                            'key'     => '_expert_id',
                            'value'   => $expert_id,
                            'compare' => '=',
                        ),
                    ),
                );

                $review = get_posts($args);

                echo '<div class="expert-avatar">' . get_avatar($expert_id, '32');

                if ($review && get_post_meta($review[0], '_idea_review', true)) {
                    echo '<i class="favorite-star fa-lg fa fa-star"></i>';
                }

                echo '</div>';
            }
            ?>
        </div>
    <?php endif ?>

    <?php if (pi_ideas_modifier() && $disable_expert_reviews !== 'on' && $idea_status == 'review'): ?>
        <div class="table-cell idea-modify">
            <a class="pi-idea-link" href="<?php echo get_permalink(); ?>?add-experts"><?php _e('Add experts', 'picasso-ideas'); ?></a>
        </div>
    <?php endif ?>

    <?php if (in_array($current_user_id, $idea_experts) && $disable_expert_reviews !== 'on' && $idea_status == 'review'): ?>
        <div class="table-cell idea-expert-review">
            <?php
            // check if logged in user already posted his expert review for this idea
            if (pi_get_review($post_id, $current_user_id, 'expert')) {
                $expert_review_button_title = __('Edit your expert review', 'picasso-ideas');
            } else {
                $expert_review_button_title = __('Post your expert review now!', 'picasso-ideas');
            }
            ?>
            <a class="pi-idea-link idea-edit-link" href="<?php echo get_permalink(); ?>#expert-reviews"><?php echo $expert_review_button_title; ?></a>
        </div>
    <?php endif ?>

    <?php
    if ($disable_user_reviews !== 'on' && $idea_status == 'review') {
        $enable_user_review = false;

        // check if logged is user is a modifier
        $is_logged_in_user_is_modifier = pi_ideas_modifier();

        if ($is_logged_in_user_is_modifier && $modifier_can_post_user_review == '1') {
            $enable_user_review = true;
        } elseif (!$is_logged_in_user_is_modifier && !in_array($current_user_id, $idea_experts)) {
            $enable_user_review = true;
        }

        // check if logged in user already posted his user review for this idea
        if (pi_get_review($post_id, $current_user_id, 'user')) {
            // if user review already found then we will give the
            // opportunity to this user to edit his review
            $enable_user_review = true;
            $user_review_button_title = __('Edit your review', 'picasso-ideas');
        } else {
            $user_review_button_title = __('Post your review now!', 'picasso-ideas');
        }

        if ($enable_user_review) {
            ?>
            <div class="table-cell idea-user-review">
                <a class="pi-idea-link idea-edit-link" href="<?php echo get_permalink(); ?>#user-reviews"><?php echo $user_review_button_title; ?></a>
            </div>
            <?php
        }
    }
    ?>

    <?php if ($disable_idea_updates !== 'on' && pi_check_for_idea_update_owner($post_id) && $idea_status == 'in-project'): ?>
        <?php
        // check if logged in user already posted an update for this idea
        if (pi_get_idea_update($post_id, $current_user_id)) {
            $update_button_title = __('Edit your update', 'picasso-ideas');
        } else {
            $update_button_title = __('Post idea update', 'picasso-ideas');
        }
        ?>
        <div class="table-cell idea-user-update">
            <a class="pi-idea-link" href="<?php echo get_permalink(); ?>#idea-update"><?php echo $update_button_title; ?></a>
        </div>
    <?php endif ?>
</div>
