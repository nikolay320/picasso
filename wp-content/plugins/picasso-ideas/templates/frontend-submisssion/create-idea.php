<?php
$idea_id = !empty($_GET['id']) ? intval($_GET['id']) : '';
$show_form = false;
$show_submitted_idea = false;

if (!$idea_id) {
	$show_form = true;
}

if ($idea_id) {
	$idea = get_post($idea_id);
	
	if ($idea && $idea->post_type === 'idea' && $idea->post_status === 'publish' && pi_is_author_post($idea->post_author)) {
		$show_submitted_idea = true;
	}
}
?>

<?php if ($show_form === false && $show_submitted_idea === false): ?>

	<?php
	$params = array(
		'message' => __('The Idea you are trying to access does not exist or is not available for you, or it may have been deleted. Please contact the site administrator for more information.', 'picasso-ideas'),
	);

	pi_render_template(IDEAS_TEMPLATE_PATH . 'frontend-submisssion/idea-not-found.php', $params);
	?>

<?php else: ?>

	<div class="row">

		<div class="col-sm-9">

			<?php // flash success message ?>
			<?php if (Picasso_Ideas_Session::exists('pi_frontend_submit_message')): ?>
				<div class="alert alert-success">
					<?php echo Picasso_Ideas_Session::flash('pi_frontend_submit_message'); ?>
				</div>
			<?php endif ?>

			<?php
			if ($show_form) {

				pi_render_template(IDEAS_TEMPLATE_PATH . 'frontend-submisssion/submit-idea-form.php');

			} else {

				pi_render_template(IDEAS_TEMPLATE_PATH . 'frontend-submisssion/sumitted-idea.php', array('idea' => $idea));

			}
			?>

		</div><!-- .col-sm-9 -->

		<div class="col-sm-3">

		    <div class="similar-ideas">
		        <h3><?php _e('Similar Ideas', 'picasso-ideas'); ?></h3>

		        <div class="similar-ideas-wrapper">

		        	<?php
		        	if ($show_form === false) {

		        		$similar_ideas = pi_search_ideas($idea->post_title);

		        		// hide the idea that is currently showing
		        		unset($similar_ideas[$idea->ID]);

		        		if ($similar_ideas) {

		        			foreach ($similar_ideas as $idea_id => $idea_title) {

		        				echo '<div class="similar-idea">';
		        					echo '<a href="' . get_the_permalink($idea_id) . '" target="_blank">' . $idea_title . '</a>';
		        				echo '</div>';

		        			}

		        		} else {

		        			_e('No similar idea found!', 'picasso-ideas');

		        		}
		        	} else {

		        		_e('Waiting for your input', 'picasso-ideas');

		        	}
		        	?>

		        </div><!-- .similar-ideas-wrapper -->
		    </div><!-- .similar-ideas -->

		</div><!-- .col-sm-3 -->

	</div><!-- .row -->

<?php endif ?>