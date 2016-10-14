<?php
get_header();
get_template_part('page-parts/general-title-section');
get_template_part('page-parts/general-before-wrap');

if (have_posts()) {
	// render search box
	pi_render_template(IDEAS_TEMPLATE_PATH . 'partials/idea-search-box.php');

	echo '<div class="ideas-list">';

	while (have_posts()) {
		the_post();

		pi_render_template(IDEAS_TEMPLATE_PATH . 'partials/content-idea.php');
	}

	echo '</div>';

	// pagination
	kleo_pagination();
} else {
	get_template_part('content', 'none');
}

get_template_part('page-parts/general-after-wrap');
get_footer();