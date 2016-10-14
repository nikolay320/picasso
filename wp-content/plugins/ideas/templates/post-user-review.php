<?php
wp_enqueue_style('jquery_raty-styles');
wp_enqueue_script('jquery_raty-js');
wp_enqueue_script('bootstrap_notify-js');

$user_id = get_current_user_id();

$args = array(
    'post_type'   => 'idea_review',
    'post_status' => 'publish',
    'numberposts' => -1,
    'fields'      => 'ids',
    'meta_query'  => array(
        'relation' => 'AND',
        array(
            'key'     => '_idea_id',
            'value'   => get_the_ID(),
            'compare' => '=',
        ),
        array(
            'key'     => '_user_id',
            'value'   => $user_id,
            'compare' => '=',
        ),
    ),
);

$review = get_posts($args);

if ($review) {
    $review_id = $review[0];
    $idea_review = get_post_meta($review[0], '_idea_review', true);
} else {
    $review_id = '';
    $idea_review = array();
}

// idea deadline
$idea_deadline = get_post_meta(get_the_ID(), '_idea_user_reviews_deadline', true);
$today = current_time('Y-m-d');

if ($idea_deadline && strtotime($today) > strtotime($idea_deadline)) {
    $enable_review = false;
    $raty_class = 'review-idea-average-rating';
} else {
    $enable_review = true;
    $raty_class = 'review-idea-rating';
}

global $klc_ideas;
$review_criteria = $klc_ideas['review_criteria'];
?>

<!-- back to details button -->
<!-- <a href="<?php the_permalink(); ?>" class="btn btn-primary back-to-idea-details"><i class="fa fa-backward"></i><?php _e('Back to details', IDEAS_TEXT_DOMAIN); ?></a> -->

<h3><?php _e('Add review', IDEAS_TEXT_DOMAIN); ?></h3>
<hr />
<?php if ($enable_review): ?>
    <form action="" method="POST" class="single-idea-review">
<?php endif ?>
    <div class="row">
        <div class="col-sm-6 rating">
            <label><b><?php _e('Rating', IDEAS_TEXT_DOMAIN); ?></b></label>
            <br />
            <br />

            <?php
            if ($review_criteria) {
                foreach ($review_criteria as $criteria) {
                    $slug = klc_slugify($criteria);
                    ?>
                    <div class="row">
                        <div class="col-xs-7">
                            <label for="<?php echo $slug; ?>"><?php echo $criteria; ?></label>
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
        <div class="col-sm-6 comment">
            <label for="comment"><b><?php _e('Comment', IDEAS_TEXT_DOMAIN); ?></b></label>
            <textarea name="idea_comment" id="comment" cols="30" rows="10" class="form-control no-hr-resize" <?php echo ($enable_review === false) ? 'disabled="disabled"' : ''; ?>><?php echo $idea_review ? $idea_review['comment'] : ''; ?></textarea>
        </div>
    </div>
    <?php if ($enable_review): ?>
        <div class="row">
            <div class="col-sm-12">
                <div class="pull-right">
                    <span class="idea-loading fa fa-spinner fa-spin"></span>
                    <input type="submit" class="btn btn-primary idea-user-review-button" value="<?php _e('Submit', IDEAS_TEXT_DOMAIN); ?>" data-idea-id="<?php echo get_the_ID(); ?>" data-review-id="<?php echo $review_id; ?>" data-user-id="<?php echo $user_id; ?>" data-ajax-url="<?php echo admin_url('admin-ajax.php'); ?>" data-action="klc_post_user_review_for_idea" data-notify-message="<?php _e('Your review was posted successfully', IDEAS_TEXT_DOMAIN); ?>" />
                </div>
            </div>
        </div>
    <?php endif ?>
<?php if ($enable_review): ?>
    </form>
<?php endif ?>