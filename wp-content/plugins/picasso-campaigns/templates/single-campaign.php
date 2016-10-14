<?php
get_header();
get_template_part('page-parts/general-title-section');
get_template_part('page-parts/general-before-wrap');

$post_id = $campaign_id = get_the_ID();
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

// Todo: Use paginaiton
$args = array(
	'post_type'      => 'idea',
	'posts_per_page' => -1,
	'post_status'    => 'publish',
	'post__in'       => $campaign_ideas,
);

// find ideas by keyword
if (!empty($_GET['keyword'])) {
	$args['s'] = $_GET['keyword'];
}

// sort ideas
if (!empty($_GET['order_by'])) {
	$order_by = $_GET['order_by'];

	switch ($order_by) {
		case 'newest':
			$args['orderby'] = 'date';
			break;

		case 'oldest':
			$args['orderby'] = 'date';
			$args['order'] = 'ASC';
			break;

		case 'recently_active':
			$args['orderby'] = 'modified';
			$args['order'] = 'DESC';
			break;

		case 'most_voted':
			$args['orderby'] = 'meta_value_num';
			$args['meta_key'] = '_liked';
			break;

		case 'most_answers':
			$args['orderby'] = 'comment_count';
			break;

		case 'my_ideas':
			$args['author'] = get_current_user_id();
			break;
	}
}

$ideas = get_posts($args);

// insert ideas those have zero like
// if (!empty($_GET['order_by']) && ($_GET['order_by'] === 'most_voted' || $_GET['order_by'] === 'my_ideas')) {
// 	$args = array(
// 		'post_type'      => 'idea',
// 		'posts_per_page' => -1,
// 		'post_status'    => 'publish',
// 		'post__in'       => $campaign_ideas,
// 		'meta_query'     => array(
// 			array(
// 				'key'     => '_liked',
// 				'compare' => 'NOT EXISTS',
// 			),
// 		),
// 	);

// 	$not_liked_ideas = get_posts($args);

// 	$ideas = array_merge($ideas, $not_liked_ideas);
// }

foreach ($ideas as $idea) {
	$idea_id = $idea->ID;
	$total_comments = $total_comments + wp_count_comments($idea_id)->total_comments;
	$total_likes = $total_likes + get_post_meta($idea_id, '_liked', true);
	$total_ideas++;
}

while (have_posts()) {
	the_post();

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

	echo '<hr />';

	echo '<div class="row single-campaign-wrapper">';

		echo '<div class="col-md-5 single-campaign-content">';

			pi_render_template(CAMPAIGNS_TEMPLATE_PATH . 'partials/content-single-campaign.php');

		echo '</div>'; // .single-campaign-content

		echo '<div class="col-md-7 campaign-ideas">';

			if ($ideas) {
				$params = array(
					'main_query'  => false,
					'campaign_id' => $campaign_id,
				);

				pi_render_template(CAMPAIGNS_TEMPLATE_PATH . 'partials/idea-search-box.php', $params);

				echo '<div class="ideas-list">';

				foreach ($ideas as $post) {
					setup_postdata($post);
					pi_render_template(CAMPAIGNS_TEMPLATE_PATH . 'partials/content-idea.php');
				}

				echo '</div>';

				wp_reset_postdata();
			} else {
				_e('No idea found in this campaign.', CAMPAIGNS_LOCALE);
			}

		echo '</div>'; // .campaign-ideas

	echo '</div>'; // .single-campaign-wrapper
}

// Post navigation
if (sq_option('post_navigation', 1) == 1) {
	kleo_post_nav();
}

get_template_part('page-parts/general-after-wrap');
get_footer();