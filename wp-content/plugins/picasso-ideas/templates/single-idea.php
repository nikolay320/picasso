<?php
get_header();
get_template_part('page-parts/general-title-section');
get_template_part('page-parts/general-before-wrap');

// flash messages
pi_render_template(IDEAS_TEMPLATE_PATH . 'partials/flash-messages.php');

while (have_posts()) {
	the_post();

	pi_render_template(IDEAS_TEMPLATE_PATH . 'partials/content-single-idea.php');
}

get_template_part('page-parts/general-after-wrap');
get_footer();