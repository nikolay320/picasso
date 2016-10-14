<?php

/**
 * Slugify strings
 * 
 * @param  string $str
 * @return string
 */
if (!function_exists('pi_slugify')) {
	function pi_slugify($str) {
		$str = strtolower($str);
		$str = html_entity_decode($str);
		$str = preg_replace('/[^\w ]+/', '', $str);
		$str = preg_replace('/ +/', '-', $str);

		return $str;
	}
}

/**
 * Redirect to
 * 
 * @param  string $redirect_to
 */
if (!function_exists('pi_redirect_to')) {
	function pi_redirect_to($redirect_to) {
		header('Location: ' . $redirect_to);
		exit();
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
if (!function_exists('pi_render_template')) {
	function pi_render_template($file, $params = array()) {
		global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;

		// load the template
		if (file_exists($file)) {
			extract($params, EXTR_SKIP);
			require($file);
		}
	}
}

/**
 * Get Campaigns for showing in cmb2 field
 * 
 * @return array
 */
if (!function_exists('pi_get_campaigns_for_cmb2_field')) {
	function pi_get_campaigns_for_cmb2_field() {
		$args = array(
			'post_type'      => 'campaign',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		);

		$campaigns = get_posts($args);

		$rows = array();

		if ($campaigns) {
			// $rows[] = __('Select campaign', 'picasso-ideas');
			$rows[''] = __('No campaign', 'picasso-ideas');

			foreach ($campaigns as $key => $value) {
				$rows[$value->ID] = $value->post_title;
			}
		}

		return $rows;
	}
}

/**
 * Return an array of options, these will be used as order_by value to sort ideas
 * 
 * @return array
 */
if (!function_exists('pi_idea_sort_by')) {
	function pi_idea_sort_by() {
		return array(
			'newest'          => __('Newest First', 'picasso-ideas'),
			'oldest'          => __('Oldest First', 'picasso-ideas'),
			'recently_active' => __('Recently Active', 'picasso-ideas'),
			'most_voted'      => __('Most Votes', 'picasso-ideas'),
			'most_answers'    => __('Most Answers', 'picasso-ideas'),
			'my_ideas'        => __('My Ideas', 'picasso-ideas'),
		);
	}
}

/**
 * Return all idea status
 * 
 * @return array
 */
if (!function_exists('pi_idea_status')) {
	function pi_idea_status() {
		return array(
			'no-status'    => __('NO STATUS', 'picasso-ideas'),
			'votes'        => __('VOTES', 'picasso-ideas'),
			'selected'     => __('SELECTED', 'picasso-ideas'),
			'not-selected' => __('NOT SELECTED', 'picasso-ideas'),
			'review'       => __('REVIEW', 'picasso-ideas'),
			'no-go'        => __('NO GO', 'picasso-ideas'),
			'in-project'   => __('IN PROJECT', 'picasso-ideas'),
		);
	}
}

/**
 * Get idea status markup
 * 
 * @param  string $idea_status
 * @return string
 */
if (!function_exists('pi_get_idea_status')) {
	function pi_get_idea_status($idea_status) {
		if (!$idea_status) {
			return;
		}

		$registered_status = pi_idea_status();
		$tooltip_message = pi_tooltip_message_for_status($idea_status);

		if (key_exists($idea_status, $registered_status) && $idea_status !== 'no-status') {
			return '<div class="text-center idea-status-tag ' . $idea_status . '" title="' . $tooltip_message . '" data-placement="top" data-toggle="idea-tooltip">' . $registered_status[$idea_status] . '</div> ';
		}

	}
}

/**
 * Check if post was created by logged in user
 * 
 * @param  int $author_id
 * @return boolean
 */
if (!function_exists('pi_is_author_post')) {
	function pi_is_author_post($author_id) {
		global $current_user;

		if ($author_id == $current_user->ID) {
			return true;
		} else {
			return false;
		}
	}
}

/**
 * Get buddypress avatar
 */
if (!function_exists('pi_get_avatar')) {
	function pi_get_avatar($user_id) {
		if (function_exists('bp_core_fetch_avatar')) {
			$args = array(
			    'item_id' => $user_id,
			    'html' => false,
			);

			return bp_core_fetch_avatar($args);
		}
	}
}

/**
 * Idea create page
 * @param  boolean $permalink
 * @return string|int If permalink is false then return page id
 */
if (!function_exists('pi_idea_create_page')) {
	function pi_idea_create_page($permalink = true) {
		global $picasso_ideas;

		if (key_exists('idea_create_page', $picasso_ideas) && !empty($picasso_ideas['idea_create_page'])) {
			$page_id = $picasso_ideas['idea_create_page'];
		} else {
			$page_id = '';
		}

		if (!$page_id) {
			return;
		}

		if ($permalink) {
			return get_the_permalink($page_id);
		}

		return $page_id;
	}
}