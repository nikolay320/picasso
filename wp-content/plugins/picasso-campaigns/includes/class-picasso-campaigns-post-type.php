<?php
/**
* Register custom post type
*/
class Picasso_Campaigns_Post_Type {
	public function register_post_type() {
		// post type campaign
		$labels = array(
			'name'               => _x('Campaigns', 'post type general name', CAMPAIGNS_LOCALE),
			'singular_name'      => _x('Campaign', 'post type singular name', CAMPAIGNS_LOCALE),
			'menu_name'          => _x('Campaigns', 'admin menu', CAMPAIGNS_LOCALE),
			'name_admin_bar'     => _x('Campaign', 'add new on admin bar', CAMPAIGNS_LOCALE),
			'add_new'            => _x('Add New', 'book', CAMPAIGNS_LOCALE),
			'add_new_item'       => __('Add New Campaign', CAMPAIGNS_LOCALE),
			'new_item'           => __('New Campaign', CAMPAIGNS_LOCALE),
			'edit_item'          => __('Edit Campaign', CAMPAIGNS_LOCALE),
			'view_item'          => __('View Campaign', CAMPAIGNS_LOCALE),
			'all_items'          => __('All Campaigns', CAMPAIGNS_LOCALE),
			'search_items'       => __('Search Campaigns', CAMPAIGNS_LOCALE),
			'parent_item_colon'  => __('Parent Campaigns:', CAMPAIGNS_LOCALE),
			'not_found'          => __('No campaign found.', CAMPAIGNS_LOCALE),
			'not_found_in_trash' => __('No campaign found in Trash.', CAMPAIGNS_LOCALE),
		);
		$args = array(
			'labels'          => $labels,
			'public'          => true,
			'show_ui'         => true,
			'query_var'       => true,
			'rewrite'         => array('slug' => 'campaign'),
			'capability_type' => 'post',
			'has_archive'     => true,
			'menu_position'   => null,
			'menu_icon'       => 'dashicons-flag',
			'supports'        => array('title', 'editor', 'author', 'comments'),
		);
		register_post_type('campaign', $args);
	}

	/**
	 * Include archive template for custom post type 'campaign'
	 */
	public function include_archive_tempate($template) {
		global $post;

		if (is_post_type_archive('campaign')) {
			$template = CAMPAIGNS_TEMPLATE_PATH . 'archive-campaign.php';
		}

		return $template;
	}

	/**
	 * Include single template for custom post type 'campaign'
	 */
	public function include_single_tempate($template) {
		global $post;

		if ($post->post_type === 'campaign') {
			$template = CAMPAIGNS_TEMPLATE_PATH . 'single-campaign.php';
		}

		return $template;
	}
}