<?php
class UN_Model{

	public function __construct(){
		add_action('init', array($this, 'action_init'));
		add_action('parse_query', array($this, 'action_parse_query'));
		add_filter('un_feedback_post_type_params', array($this, 'filter_un_feedback_post_type_params'));

		add_filter('un_feedback_post_type_params', array($this, '_un_feedback_post_type_params'), 11);
		add_filter('posts_orderby', array($this, 'filter_posts_orderby'), 10, 2);
		add_filter('posts_join', array($this, 'filter_posts_join'), 10, 2);
		add_action('save_post', array($this, 'action_save_post'));
		add_action('post_updated', array($this, '_post_updated'), 10, 3);
		//add_filter('un_feedback_type_taxonomy_params', array($this, '_un_feedback_type_taxonomy_params'));
		add_action('wp_set_comment_status', array($this, '_set_comment_status'), 10, 2);
		add_filter('query_vars', array($this, '_query_vars'));
	}


	public function action_init(){
		global $wp;
		if ($wp)
			$wp->add_query_var('feedback_type_id');
		$this->register_schema();
	}

	function register_schema(){
		register_taxonomy(FEEDBACK_TYPE, FEEDBACK,
			apply_filters('un_feedback_type_taxonomy_params', array(
				'public' => false,
				'show_ui' => false,
				//'rewrite' => array('slug' => 'feedback-types'),
				'label' => __('Feedback types', 'usernoise')
		)));
		register_post_type(FEEDBACK, apply_filters('un_feedback_post_type_params', array(
			'label' => _x('Feedback', 'admin', 'usernoise'),
			'labels' => array(
				'name' => _x('Feedback', 'admin', 'usernoise'),
				'singular_name' => _x('Feedback', 'admin', 'usernoise'),
				'add_new' => __('Add new', 'usernoise', 'usernoise'),
				'add_new_item' => __('Add new feedback', 'usernoise'),
				'edit_item' => __('View feedback', 'usernoise'),
				'new_item' => __('New feedback', 'usernoise'),
				'view_item' => __('View feedback', 'usernoise'),
				'search_items' => __('Search feedback', 'usernoise'),
				'not_found' => __('Feedback not found', 'usernoise'),
				'not_found_in_trash' => __('Feedback not found in Trash', 'usernoise'),
				'menu_name' => __('Usernoise', 'usernoise')
			),
			'description' => __('Feedback left by users using a form in a lightbox', 'usernoise'),
			'public' => false,
			'show_ui' => true,
			'show_in_menu' => false,
			'supports' => array('title', 'editor', 'comments'),
			'rewrite' => false,
			'show_in_nav_menus' => false
		)));
	}

	public function filter_un_feedback_post_type_params($params){
		$params['supports'] = array(null);
		return $params;
	}


	public function create_feedback($params){
		global $un_settings;
    global $current_user;
		if (isset($params['summary']) && $params['summary']) {
			$title = $params['summary'];
		} else {
			$title = wp_trim_words( $params['feedback'], 5 );
		}
		if (!isset($params['feedback']) || !trim($params['feedback'])) {
			return new WP_Error("No feedback sent");
		} else {
			$content = $params['feedback'];
		}
		$id = wp_insert_post(array(
			'post_type' => FEEDBACK,
			'post_title' => wp_kses(apply_filters('un_feedback_title', $title, $params), wp_kses_allowed_html()),
			'post_content' => wp_kses(apply_filters('un_feedback_content', $content, $params), wp_kses_allowed_html()),
			'post_status' => un_get_option(UN_PUBLISH_DIRECTLY) ? 'publish' : 'pending',
		));
		$email = isset($params['email']) ? trim($params['email']) : '';
		if ($email)
			add_post_meta($id, '_email', $email);
		if (is_user_logged_in()){
			add_post_meta($id, '_author', get_current_user_id());
		}
		if (isset($params['name']) && trim($params['name']))
			add_post_meta($id, '_name', wp_kses(trim($params['name']), wp_kses_allowed_html()));
		if (isset($params['type']))
			wp_set_post_terms($id, $params['type'], FEEDBACK_TYPE);
		if (isset($params['screenshot'])){
			add_post_meta($id, '_screenshot', $params['screenshot']);
		}
		if (isset($params['referer'])){
			add_post_meta($id, '_url', $params['referer']);
		}
		if (isset($_COOKIE['un_unique'])){
			add_post_meta($id, '_unique', $_COOKIE['un_unique']);
		}
		$extra = array();
		foreach(array_keys($params) as $extra_key){
			if (!in_array($extra_key, array('unique', 'feedback', 'summary', 'name', 'email', 'screenshot', 'referer'))){
				$extra[$extra_key] = $params[$extra_key];
			}
		}
		if ($extra){
			add_post_meta($id, '_extra', $extra);
		}
		if (isset($_SERVER['HTTP_USER_AGENT']))
			add_post_meta($id, '_user_agent', $_SERVER['HTTP_USER_AGENT']);
		if (isset($_SERVER['REMOTE_ADDR']))
			add_post_meta($id, '_ip', $_SERVER['REMOTE_ADDR']);
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			add_post_meta($id, '_x_forwarded_for', $_SERVER['HTTP_X_FORWARDED_FOR']);
		update_post_meta($id, '_status', $this->get_default_status());
		do_action('un_feedback_created', $id, $params);
		$this->send_admin_message($id, $params);
	}

	public function send_admin_message($id, $params){
		if (!un_get_option(UN_ADMIN_NOTIFY_ON_FEEDBACK))
			return;
		$type = isset($params['type']) && $params['type'] ? $params['type'] : __('feedback');
		$message = sprintf(__('A new %s has been submitted. View it: %s.', 'usernoise'),
			$type,
			admin_url('post.php?action=edit&post=' . $id)
		);
		$message = apply_filters('un_feedback_received_message', $message, $id, $params);
		$message = apply_filters('un_admin_notification_message', $message, $id, $params);
		$message .= "\r\n\r\n";
		$email = un_feedback_author_email($id);
		if ($email)
			$message .= __('From:', 'usernoise-pro') . " " . $email . "\r\n\r\n";
		if ($author_name = un_feedback_author_name($id)){
			$message .= __('Name', 'usernoise-pro') . ': '. $author_name . "\r\n";
		}
		if (isset($params['type'])){
			$term = get_term_by('slug', $params['type'], FEEDBACK_TYPE);
			$message .= __('Type', 'usernoise-pro') . ": " . $term->name . "\r\n";
		}

		if (!empty($params['title']))
			$message .= __('Summary:', 'usernoise-pro') . " " . $params['title'] . "\r\n\r\n";
		$post = get_post($id);
		$message .= "\r\n\r\n" . $post->post_content;
		$subject = apply_filters('un_admin_notification_subject',
			sprintf(__('New %s submitted at %s'), __($type, 'usernoise'), html_entity_decode(get_bloginfo('name'), ENT_QUOTES | ENT_HTML5)));
		$headers = array();
		if ($email = un_feedback_author_email($id)){
			$headers []= "Reply-To: $email";
		}
		wp_mail(
			$to = get_option(UNPRO_ADMIN_NOTIFICATION_EMAIL, get_option('admin_email')),
			$subject,
			$message,
			$headers
			);
		do_action('un_admin_notification_sent', $id, $params, $message);
	}

	public function action_parse_query($q){
		if (isset($q->query_vars['feedback_type_id']) &&
			$q->query_vars['feedback_type_id']){
			if (empty($q->query_vars['tax_query'])){
				$q->query_vars['tax_query'] = array();
			}
			$q->query_vars['tax_query'] []= array(
				'taxonomy' => FEEDBACK_TYPE,
				'field' => 'id',
				'terms' => (int)$q->query_vars['feedback_type_id']
				);
		}
		if (isset($q->query_vars['feedback_type_slug']) &&
			$q->query_vars['feedback_type_slug']){
			if (empty($q->query_vars['tax_query'])){
				$q->query_vars['tax_query'] = array();
			}
			$q->query_vars['tax_query'] []= array(
				'taxonomy' => FEEDBACK_TYPE,
				'field' => 'slug',
				'terms' => $q->query_vars['feedback_type_slug']
				);
		}
	}

	public function get_pending_feedback_count(){
		global $wpdb;
		$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(1) FROM $wpdb->posts WHERE post_type = %s AND post_status = 'pending'", FEEDBACK));
		if (!$count)
			$count = 0;
		return $count;
	}

	public function get_comments($post){
		$result = array();
		foreach(get_comments(array(
			'post_id' => $post->ID,
			'status' => 'approve'
		)) as $comment_data){
			$result []= array(
				'id' => $comment_data->comment_ID,
				'author' => $comment_data->comment_author,
				'approved' => $comment_data->comment_approved,
				'avatar' => get_avatar_url($comment_data, 24, usernoise_url("/images/default-avatar.gif")),
				'time_ago' => _un_human_time_diff($comment_data->comment_date),
				'text' => apply_filters('comments_text', get_comment_text($comment_data->comment_ID))
			);
		}
		return $result;
	}

	public function get_pending_feedback($args = array()){
		$defaults = array('post_type' => FEEDBACK, 'numberposts' => -1, 'post_status' => 'pending');
		$args = wp_parse_args($args, $defaults);
		return get_posts($args);
	}

	public function get_feedback_type($feedback){
		if (is_object($feedback))
			$feedback = $feedback->ID;
		$terms = wp_get_post_terms($feedback, FEEDBACK_TYPE);
		if (count($terms))
			return $terms[0];
		return null;
	}

	public function get_plural_feedback_type_label($type){
		$term = get_term_by('slug', $type, FEEDBACK_TYPE);
		return un_get_term_meta($term->term_id, 'plural');
	}

	public function get_feedback_types(){
		$tags = get_terms(FEEDBACK_TYPE, array('un_orderby_meta' => 'position', 'hide_empty' => false));
		$result = array();
		foreach($tags as $tag){
			$result[] = array(
				'slug' => $tag->slug,
				'singular' => $tag->name,
				'plural' => un_get_term_meta($tag->term_id, 'plural'),
				'icon' => un_get_term_meta($tag->term_id, 'icon')
				);
		}
		return $result;
	}


	public function _query_vars($query_vars){
		$query_vars []= 'un_status';
		return $query_vars;
	}

	public function _set_comment_status($comment_id, $new_status){
		$comment = get_comment($comment_id);
		if (!$comment) {
			return;
		}
		$post = get_post($comment->comment_post_ID);
		if ($post->post_type != FEEDBACK){
			return;
		}
		if (!($new_status == '1' || $new_status == 'approve')){
			return;
		}
		$this->notify_feedback_author_on_comment($comment_id);
	}



	public function _un_feedback_post_type_params($params){
		$params['supports'] = array('title', 'editor', 'comments');
		$params['public'] = false;
		$params['show_in_nav_menus'] = true;
		return $params;
	}

	public function action_save_post($id){
		global  $un_model;
		$post = get_post($id);
		if ($post->post_type != FEEDBACK)
			return;
		if (isset($_REQUEST['un_status']))
			$this->set_feedback_status($id, stripslashes($_REQUEST['un_status']));
	}

	public function _post_updated($post_ID, $after, $before){
		$post = get_post($post_ID);
		if ($after->post_status == 'publish' && $before->post_status != 'publish' && $this->author_email($post_ID)){
			$this->send_feedback_published_notification($post);
		}
	}

	public function send_feedback_published_notification($post){
		$message = sprintf(__('Your feedback "%s" has been published by admin. Thanks for your feedback!', 'usernoise') . "\r\n", $post->post_title);
		$message .= sprintf(__('You can view it at %s', 'usernoise'), un_get_option(UNPRO_NOTIFICATIONS_SITE));
		$subject = sprintf(__('Feedback approved at %s', 'usernoise'), un_get_option(UNPRO_NOTIFICATIONS_SITE));
		@wp_mail($this->author_email($post->ID), $subject, $message);
	}


	public function filter_posts_orderby($orderby, $query){
		if ($this->is_order_by_likes($query)){
			return ' CAST(posts_likes.meta_value AS SIGNED INTEGER) DESC ';
		}
		return $orderby;
	}

	public function filter_posts_join($join, $query){
		global $wpdb;
		if ($this->is_order_by_likes($query)){
			 $join .= " LEFT OUTER JOIN $wpdb->postmeta posts_likes ON $wpdb->posts.ID = posts_likes.post_id AND posts_likes.meta_key = '_likes' ";
		}
		if ($this->is_status_query($query)){
			$join .= $wpdb->prepare(" INNER JOIN $wpdb->postmeta un_posts_statuses ON $wpdb->posts.ID = un_posts_statuses.post_id AND un_posts_statuses.meta_key = '_status' AND un_posts_statuses.meta_value = %s ", stripslashes($query->query_vars['un_status']));
		}
		return $join;
	}

	private function is_status_query($query){
		return isset($query->query_vars['un_status']) && trim($query->query_vars['un_status']);
	}

	private function is_order_by_likes($query){
		return isset($query->query_vars['orderby']) && $query->query_vars['orderby'] == 'likes';
	}

	public function add_like($id){
		$post = get_post($id);
		if ($post->post_type != FEEDBACK){
			wp_die(__('Hacking, huh?'));
		}
		if (get_post_meta($id, '_likes', true) !== false){
			global $wpdb;
			$likes = get_post_meta($id, '_likes', true);
			$likes++;
			update_post_meta($id, '_likes', $likes);
			return $likes;
		} else {
			add_post_meta($id, '_likes', 1);
			return 1;
		}
	}

	public function remove_like($id){
		$post = get_post($id);
		if ($post->post_type != FEEDBACK){
			wp_die(__('Hacking, huh?'));
		}
		if (get_post_meta($id, '_likes', true) !== false){
			global $wpdb;
			$likes = get_post_meta($id, '_likes', true);
			$likes--;
			if ($likes < 0)
				$likes = 0;
			update_post_meta($id, '_likes', $likes);
			return $likes;
		}
		return 0;
	}

	public function remove_dislike($id){
		$post = get_post($id);
		if ($post->post_type != FEEDBACK){
			wp_die(__('Hacking, huh?'));
		}
		if (get_post_meta($id, '_dislikes', true)){
			global $wpdb;
			$likes = get_post_meta($id, '_dislikes', true);
			$likes--;
			if ($likes < 0)
				$likes = 0;
			update_post_meta($id, '_dislikes', $likes);
			return $likes;
		}
		return 0;
	}

	public function get_likes($id){
		if ($likes = get_post_meta($id, '_likes', true))
			return $likes;
		return 0;
	}

	public function get_dislikes($id){
		if ($dislikes = get_post_meta($id, '_dislikes', true)){
			return $dislikes;
		}
		return 0;
	}

	function extract_likes(){
		$likes = isset($_COOKIE['likes']) ? $_COOKIE['likes'] : null;
		if (is_string($likes))
			$likes = array_map('intval', explode(',', $likes));
		else
			$likes = array();
		return $likes;
	}

	function extract_dislikes(){
		$dislikes = isset($_COOKIE['dislikes']) ? $_COOKIE['dislikes'] : null;
		if (is_string($dislikes))
			$dislikes = explode(',', $dislikes);
		else
			$dislikes = array();
		return $dislikes;
	}


	public function get_statuses(){
		return apply_filters('un_statuses', array(
			'new' => __('New', 'usernoise'),
			'rejected' => __('Rejected', 'usernoise'),
			'planned' => __('Planned', 'usernoise'),
			'in_progress' => __('In progress', 'usernoise'),
			'done' => __('Done', 'usernoise')
		));
	}
	public function get_default_status(){
		$statuses = $this->get_statuses();
		$keys = array_keys($statuses);
		return apply_filters('un_default_status', $keys[0]);
	}

	public function set_feedback_status($feedback, $status){
		if (is_object($feedback))
			$feedback = $feedback->ID;
		$old_status = get_post_meta($feedback, '_status', true);
		if ($old_status && $status && $old_status != $status && $this->author_email($feedback))
			$this->send_status_change_notification($feedback, $status);
		update_post_meta($feedback, '_status', $status);
	}

	public function send_status_change_notification($id, $status){
		$post = get_post($id);
		$statuses = $this->get_statuses();
		$old_status = get_post_meta($id, '_status', true);
		$message = sprintf(__('Your feedback "%s" was changed from %s to %s.', 'usernoise'), $post->post_title, strtolower($statuses[$old_status]), strtolower($statuses[$status])) . "\r\n";
		$message .= sprintf(__('You can view your feedback at %s', 'usernoise'), un_get_option(UNPRO_NOTIFICATIONS_SITE)) . "\r\n\r\n";
		$message .= __('Original feedback was:', 'usernoise');
		$message .= wptexturize($post->post_content);
		@wp_mail($this->author_email($id), sprintf(__('Feedback status changed at %s', 'usernoise'), un_get_option(UNPRO_NOTIFICATIONS_SITE)), $message);
	}

	public function get_feedback_status($feedback){
		if (is_object($feedback))
			$feedback = $feedback->ID;
		if (!($status = get_post_meta($feedback, '_status', true)))
			$status = $this->get_default_status();
		return $status;
	}

	public function get_feedback_status_name($feedback){
		$statuses = $this->get_statuses();
		return $statuses[$this->get_feedback_status($feedback)];
	}

	public function author_email($id){
		$email = get_post_meta($id, '_email', true);
		$user = get_post_meta($id, '_author', true);
		if ($user){
			$user = get_userdata($user)->user_email;
		}
		if ($user) return $user;
		return $email;
	}


	public function notify_feedback_author_on_comment($comment_id){
		$comment = get_comment($comment_id);
		$post = get_post($comment->comment_post_ID);
		$email = $this->author_email($comment->comment_post_ID);
		$subject = sprintf( __('[%1$s] Comment: "%2$s"'), un_get_option(UNPRO_NOTIFICATIONS_SITE), $post->post_title );
		$notify_message  = sprintf( __( 'New comment on your feedback "%s" at %s', 'usernoise' ),
			$post->post_title, un_get_option(UNPRO_NOTIFICATIONS_SITE) ) . "\r\n";
		/* translators: 1: comment author, 2: author IP, 3: author domain */
		//$notify_message .= sprintf( __('E-mail : %s'), $comment->comment_author_email ) . "\r\n";
		$notify_message .= __('Comment: ') . "\r\n" . $comment->comment_content . "\r\n\r\n";
		$notify_message .= "View at the site: " . site_url("/#feedback-" . $post->ID);
		@wp_mail($email, $subject, $notify_message );
	}
}

$GLOBALS['un_model'] = new UN_Model;
