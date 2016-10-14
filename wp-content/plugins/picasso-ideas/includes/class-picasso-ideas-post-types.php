<?php
/**
* Class Picasso_Ideas_Post_Types
*/
class Picasso_Ideas_Post_Types {
	// Register custom post types
	public function register_post_types() {
		// post type idea
		$labels = array(
			'name'               => _x('Ideas', 'post type general name', IDEAS_LOCALE),
			'singular_name'      => _x('Idea', 'post type singular name', IDEAS_LOCALE),
			'menu_name'          => _x('Ideas', 'admin menu', IDEAS_LOCALE),
			'name_admin_bar'     => _x('Idea', 'add new on admin bar', IDEAS_LOCALE),
			'add_new'            => _x('Add New', 'book', IDEAS_LOCALE),
			'add_new_item'       => __('Add New Idea', IDEAS_LOCALE),
			'new_item'           => __('New Idea', IDEAS_LOCALE),
			'edit_item'          => __('Edit Idea', IDEAS_LOCALE),
			'view_item'          => __('View Idea', IDEAS_LOCALE),
			'all_items'          => __('All Ideas', IDEAS_LOCALE),
			'search_items'       => __('Search Ideas', IDEAS_LOCALE),
			'parent_item_colon'  => __('Parent Ideas:', IDEAS_LOCALE),
			'not_found'          => __('No idea found.', IDEAS_LOCALE),
			'not_found_in_trash' => __('No idea found in Trash.', IDEAS_LOCALE),
		);
		$args = array(
			'labels'          => $labels,
			'public'          => true,
			'show_ui'         => true,
			'query_var'       => true,
			'rewrite'         => array('slug' => 'idea'),
			'capability_type' => 'post',
			'has_archive'     => true,
			'menu_position'   => null,
			'menu_icon'       => 'dashicons-format-status',
			'supports'        => array('title', 'editor', 'author', 'comments'),
		);
		register_post_type('idea', $args);

		// Todo: resister category and tag for post type idea

		// post type review
		$labels = array(
			'name'               => _x( 'Reviews', 'post type general name', IDEAS_LOCALE ),
			'singular_name'      => _x( 'Review', 'post type singular name', IDEAS_LOCALE ),
			'menu_name'          => _x( 'Reviews', 'admin menu', IDEAS_LOCALE ),
			'name_admin_bar'     => _x( 'Review', 'add new on admin bar', IDEAS_LOCALE ),
			'add_new'            => _x( 'Add New', 'review', IDEAS_LOCALE ),
			'add_new_item'       => __( 'Add New Review', IDEAS_LOCALE ),
			'new_item'           => __( 'New Review', IDEAS_LOCALE ),
			'edit_item'          => __( 'Edit Review', IDEAS_LOCALE ),
			'view_item'          => __( 'View Review', IDEAS_LOCALE ),
			'all_items'          => __( 'All Reviews', IDEAS_LOCALE ),
			'search_items'       => __( 'Search Reviews', IDEAS_LOCALE ),
			'parent_item_colon'  => __( 'Parent Reviews:', IDEAS_LOCALE ),
			'not_found'          => __( 'No review found.', IDEAS_LOCALE ),
			'not_found_in_trash' => __( 'No review found in Trash.', IDEAS_LOCALE )
		);

		$args = array(
			'labels'          => $labels,
			'show_in_menu'    => 'edit.php?post_type=idea',
			'public'          => true,
			'query_var'       => true,
			'rewrite'         => array( 'slug' => 'idea-review' ),
			'capability_type' => 'post',
			'has_archive'     => false,
			'hierarchical'    => false,
			'menu_position'   => null,
			'supports'        => array( 'title', 'author' ),
		);

		register_post_type( 'idea_review', $args );
	}

	/**
	 * Add custom column 'campaign' to idea posts table
	 * 
	 * @param array $columns
	 *
	 * @return array
	 */
	public function add_campaign_custom_column($columns) {
		$new_columns = array(
			'idea_campaign' => __('Campaign', IDEAS_LOCALE),
		);

		return array_merge($columns, $new_columns);
	}

	/**
	 * Set data to custom campaign column
	 * 
	 * @param array $column
	 * @param int $post_id
	 *
	 * @return string
	 */
	public function set_data_to_custom_column_campaign($column, $post_id) {
		switch ($column) {
			case 'idea_campaign':
				$campaign_id = get_post_meta($post_id, '_idea_campaign', true);

				if ($campaign_id) {
					echo get_the_title($campaign_id);
				} else {
					_e('No campaign', IDEAS_LOCALE);
				}

				break;
		}
	}

	/**
	 * Make custom campaign column sortable
	 * 
	 * @param  array $columns
	 * 
	 * @return array
	 */
	public function make_custom_campaign_column_sortable($columns) {
		$columns['idea_campaign'] = 'idea_campaign';

		return $columns;
	}

	/**
	 * Sort ideas on idea posts table by campaign id
	 * 
	 * @param  object $query
	 */
	public function sort_ideas_by_campaign_id($query) {
		if (!is_admin()) {
			return;
		}

		$orderby = $query->get('orderby');

		if ($orderby === 'idea_campaign') {
			$query->set('meta_key', '_idea_campaign');
			$query->set('orderby', 'meta_value');
		}
	}

	/**
	 * Include archive template for custom post type 'idea'
	 */
	public function include_archive_tempate($template) {
		global $post;

		if (is_post_type_archive('idea')) {
			$template = IDEAS_TEMPLATE_PATH . 'archive-idea.php';
		}

		return $template;
	}

	/**
	 * Include single template for custom post type 'idea'
	 */
	public function include_single_tempate($template) {
		global $post;

		if ($post->post_type === 'idea') {
			$template = IDEAS_TEMPLATE_PATH . 'single-idea.php';
		}

		return $template;
	}

	/**
	 * Increment Idea views count
	 */
	public function increment_idea_views($template) {
		global $post;

		if ($post->post_type === 'idea') {
			$idea_id = $post->ID;

			$views_count = get_post_meta($idea_id, '_views_count', true);

			if (!isset($views_count)) {
				add_post_meta($idea_id, '_views_count', 0);
			} else {
				update_post_meta($idea_id, '_views_count', $views_count + 1);
			}
		}

		return $template;
	}

	/**
	 * Put idea in favorites via ajax call
	 */
	public function put_idea_in_favorites() {
		$idea_id = isset($_POST['idea_id']) ? intval($_POST['idea_id']) : '';
		$status = isset($_POST['status']) ? intval($_POST['status']) : '';

		if (!$idea_id) {
			return;
		}

		$idea_favorites = get_post_meta($idea_id, '_idea_favorites');
		$user_id = get_current_user_id();

		if ($idea_favorites) {
			if (!in_array($user_id, $idea_favorites) && $status) {
				add_post_meta($idea_id, '_idea_favorites', $user_id);
			}

			if (in_array($user_id, $idea_favorites) && !$status) {
				delete_post_meta($idea_id, '_idea_favorites', $user_id);
			}
		} else {
			add_post_meta($idea_id, '_idea_favorites', $user_id);
		}

		echo json_encode(array(
			'success' => 'true',
		));

		exit;
	}

	/**
	 * Add comment from modal
	 */
	public function add_comment_from_modal() {
		global $current_user;

		$post_id = !empty($_POST['post_id']) ? intval($_POST['post_id']) : '';
		$comment = !empty($_POST['comment']) ? sanitize_text_field($_POST['comment']) : '';
		$message = '';
		$success = 'false';

		if (!is_user_logged_in()) {
			$message = __('You must be logged in to comment', 'picasso-ideas');
		}

		elseif (!$post_id) {
			$message = __('Post id is required', 'picasso-ideas');
		}

		elseif (!$comment) {
			$message = __('Comment is required', 'picasso-ideas');
		}

		else {
			$comment_data = array(
				'comment_post_ID'      => $post_id,
				'comment_content'      => $comment,
				'user_id'              => $current_user->ID,
				'comment_author'       => $current_user->user_nicename,
				'comment_author_email' => $current_user->user_email,
				'comment_author_url'   => site_url(),
			);

			// Add new comment
			$comment_id = wp_new_comment($comment_data);

			if ($comment_id) {
				$post_link = get_the_permalink($post_id) . '#comment-' . $comment_id;
				$message = sprintf(__('You comment has been posted successfully! %s', 'picasso-ideas'), '<a href="' . $post_link . '">' . __('See your comment') . '</a>');
				$success = 'true';
			} else {
				$message = __('Something went wrong.', 'picasso-ideas');
			}
		}

		echo json_encode(array(
			'message' => $message,
			'success' => $success,
		));

		exit;
	}

	/**
	 * Update comment from modal
	 */
	public function update_comment_from_modal() {
		$comment_id = !empty($_POST['comment_id']) ? intval($_POST['comment_id']) : '';
		$comment = !empty($_POST['comment']) ? wp_kses($_POST['comment'], wp_kses_allowed_html('post')) : '';
		$message = '';
		$success = 'false';
		$comment_content = '';

		if (!$comment_id) {
			$message = __('Comment id is required', 'picasso-ideas');
		}

		elseif (!$comment) {
			$message = __('Comment is required', 'picasso-ideas');
		}

		else {
			$comment_data = array(
				'comment_ID'      => $comment_id,
				'comment_content' => $comment,
			);

			// Update comment
			$updated = wp_update_comment($comment_data);

			if ($updated) {
				$message = sprintf(__('You comment has been updated successfully!', 'picasso-ideas'));
				$comment_content = apply_filters('comment_text', $comment);
				$success = 'true';
			} else {
				$message = __('Something went wrong.', 'picasso-ideas');
			}
		}

		echo json_encode(array(
			'message'         => $message,
			'success'         => $success,
			'comment_content' => $comment_content,
		));

		exit;
	}

	/**
	 * Sort ideas
	 */
	public function sort_ideas($query) {
		if (is_admin() || !$query->is_main_query()) {
			return;
		}

		if (is_post_type_archive('idea') && !empty($_GET['order_by'])) {
			$order_by = $_GET['order_by'];

			switch ($order_by) {
				case 'newest':
					$query->set('orderby', 'date');
					break;

				case 'oldest':
					$query->set('orderby', 'date');
					$query->set('order', 'ASC');
					break;

				case 'recently_active':
					$query->set('orderby', 'modified');
					$query->set('order', 'DESC');
					break;

				case 'most_voted':
					$query->set('orderby', 'meta_value_num');
					$query->set('meta_key', '_liked');
					break;

				case 'most_answers':
					$query->set('orderby', 'comment_count');
					break;

				case 'my_ideas':
					$query->set('author', get_current_user_id());
					break;
			}
		}

		return $query;
	}
}