<?php
/**
 * Search ideas for given keywork
 * 
 * @param  string $keyword
 * @return array
 */
if (!function_exists('klc_search_ideas')) {
	function klc_search_ideas($keyword) {
		$args = array(
			'post_type'      => 'ideas',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			's'              => $keyword,
		);

		$posts = get_posts($args);
		$rows = array();

		if ($posts) {
			foreach ($posts as $post) {
				$rows[$post->ID] = $post->post_title;
			}
		}

		return $rows;
	}
}

/**
 * Find similar ideas
 */
if (!function_exists('klc_search_similar_ideas')) {
	function klc_search_similar_ideas() {
		$keyword = !empty($_POST['keyword']) ? $_POST['keyword'] : '';

		$posts = klc_search_ideas($keyword);
		$html = '';

		if ($posts) {
			foreach ($posts as $post_id => $post_title) {
				$html .= '<div class="similar-idea"><a href="' . get_the_permalink($post_id) . '">' . $post_title . '</a></div>';
			}
		} else {
			$html .= '<div class="similar-idea">' . __('No similar idea found!', 'ideas_plugin') . '</div>';
		}

		echo $html;
		exit;
	}
	add_action('wp_ajax_klc_search_similar_ideas', 'klc_search_similar_ideas');
}