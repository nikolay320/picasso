<?php
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
        array(
            'key'     => '_idea_update',
            'compare' => 'EXISTS',
        ),
    ),
);

$review = get_posts($args);

if ($review) {
    $review_id = $review[0];
    $idea_update = get_post_meta($review[0], '_idea_update', true);
} else {
    $review_id = '';
    $idea_update = '';
}
?>

<!-- back to details button -->
<a href="<?php the_permalink(); ?>" class="btn btn-primary btn-xs back-to-idea-details"><i class="fa fa-backward"></i><?php _e('Back to details', IDEAS_TEXT_DOMAIN); ?></a>

<h3><?php _e('Idea Update', IDEAS_TEXT_DOMAIN); ?></h3>
<hr />
<form action="" method="POST" class="idea-update-form">
	<textarea name="idea_update" id="comment" cols="30" rows="10" class="form-control no-hr-resize"><?php echo $idea_update; ?></textarea>

	<input type="submit" class="btn btn-primary idea-update-button" value="<?php _e('Submit', IDEAS_TEXT_DOMAIN); ?>" data-idea-id="<?php echo get_the_ID(); ?>" data-review-id="<?php echo $review_id; ?>" data-user-id="<?php echo $user_id; ?>" data-ajax-url="<?php echo admin_url('admin-ajax.php'); ?>" data-action="klc_post_idea_update" data-notify-message="<?php _e('Your update was posted successfully', IDEAS_TEXT_DOMAIN); ?>" />
	<span class="idea-loading fa fa-spinner fa-spin"></span>
</form>