<?php
$idea_id = !empty($_GET['id']) ? intval($_GET['id']) : '';
$show_form = false;

if ($idea_id) {

	$idea = get_post($idea_id);
	
	if ($idea && $idea->post_type === 'idea' && $idea->post_status === 'publish' && pi_is_author_post($idea->post_author)) {

		$show_form = true;

	}

}

if ($show_form) {

	// Show flash message
	if (Picasso_Ideas_Session::exists('pi_frontend_submit_message')) {

		echo '<div class="alert alert-success">';
			echo Picasso_Ideas_Session::flash('pi_frontend_submit_message');
		echo '</div>';

	}

	pi_render_template(IDEAS_TEMPLATE_PATH . 'frontend-submisssion/submit-idea-form.php', array('idea' => $idea));

} else {

	$params = array(
		'message' => __('The Idea you are trying to access does not exist or is not available for you, or it may have been deleted. Please contact the site administrator for more information.', 'picasso-ideas'),
	);

	pi_render_template(IDEAS_TEMPLATE_PATH . 'frontend-submisssion/idea-not-found.php', $params);

}