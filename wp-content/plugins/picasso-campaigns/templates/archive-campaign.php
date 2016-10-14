<?php
get_header();
get_template_part('page-parts/general-title-section');
get_template_part('page-parts/general-before-wrap');

if (have_posts()) {

	echo '<div class="campaigns-list">';

	while (have_posts()) {
		the_post();

		pi_render_template(CAMPAIGNS_TEMPLATE_PATH . 'partials/content-campaign.php');
	}

	echo '</div>';

	// pagination
	kleo_pagination();
} else {
	get_template_part('content', 'none');
}

get_template_part('page-parts/general-after-wrap');
get_footer();