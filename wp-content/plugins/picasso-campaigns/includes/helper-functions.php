<?php

/**
 * Get ideas for showing on cmb2 field
 */
if (!function_exists('pc_ideas')) {
	function pc_ideas() {
		$args = array(
			'post_type'      => 'idea',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);

		$rows = get_posts($args);
		$ideas = array();

		if ($rows) {
			foreach ($rows as $row) {
				$ideas[$row->ID] = $row->post_title;
			}
		}

		return $ideas;
	}
}

/**
 * Render template.
 * 
 * @param  string $file
 * @param  array  $params
 * 
 * @return response
 */
if (!function_exists('pc_render_template')) {
	function pc_render_template($file, $params = array()) {
		global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;

		// load the template
		if (file_exists($file)) {
			extract($params, EXTR_SKIP);
			require($file);
		}
	}
}

/**
 * Count ideas for given status
 * 
 * @param  string $status   in reivew|already reviews etc..
 * @param  array $idea_ids list of idea ids
 * @return int On success count ideas otherwise 0
 */
if (!function_exists('pc_count_ideas_for_given_status')) {
	function pc_count_ideas_for_given_status($status, $idea_ids) {
		$args = array(
			'post_type'      => 'idea',
			'post__in'       => $idea_ids,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => '_idea_status',
					'value'   => $status,
					'compare' => '='
				),
			),
		);

		$rows = get_posts($args);

		if ($rows) {
			return count($rows);
		} else {
			return 0;
		}
	}
}