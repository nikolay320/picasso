<?php

$campaign_ideas = get_post_meta($campaign_id, '_campaign_ideas', false);
$campaign_deadline = get_post_meta($campaign_id, '_campaign_deadline', true);

if (!$campaign_ideas) {
	$campaign_ideas = array(0);
}

// check if campaign has ended
$interval = $campaign_deadline ? ($campaign_deadline - current_time('timestamp')) : 0;

$total_ideas = 0;
$total_comments = 0;
$total_likes = 0;

$args = array(
	'post_type'      => 'idea',
	'posts_per_page' => -1,
	'post_status'    => 'publish',
	'post__in'       => $campaign_ideas,
);

$ideas_in_campaign = get_posts($args);

foreach ($ideas_in_campaign as $idea) {
	$idea_id = $idea->ID;
	$total_comments = $total_comments + wp_count_comments($idea_id)->total_comments;
	$total_likes = $total_likes + get_post_meta($idea_id, '_liked', true);
	$total_ideas++;
}

// Show campaign countdown box
$params = array(
	'campaign_id'       => $campaign_id,
	'interval'          => $interval,
	'campaign_deadline' => $campaign_deadline,
	'total_ideas'       => $total_ideas,
	'total_comments'    => $total_comments,
	'total_likes'       => $total_likes,
);

pi_render_template(CAMPAIGNS_TEMPLATE_PATH . 'partials/campaign-countdown.php', $params);