<?php
class UN_Controller {
	function __construct(){
		add_action('wp_ajax_un_config_get', array($this, 'config_get'));
		add_action('wp_ajax_nopriv_un_config_get', array($this, 'config_get'));
		add_action('wp_ajax_un_feedback_post', array($this, 'feedback_post'));
		add_action('wp_ajax_nopriv_un_feedback_post', array($this, 'feedback_post'));
		add_action('wp_ajax_un_feedback_get', array($this, '_un_feedback_get'));
		add_action('wp_ajax_nopriv_un_feedback_get', array($this, '_un_feedback_get'));
		add_action('wp_ajax_un_feedback_get_id', array($this, '_un_feedback_get_id'));
		add_action('wp_ajax_nopriv_un_feedback_get_id', array($this, '_un_feedback_get_id'));
		add_action('wp_ajax_un_feedback_like', array($this, '_feedback_like'));
		add_action('wp_ajax_nopriv_un_feedback_like', array($this, '_feedback_like'));
		add_action('wp_ajax_un_comment_post', array($this, '_comment_post'));
		add_action('wp_ajax_nopriv_un_comment_post', array($this, '_comment_post'));
		add_action('wp_set_comment_status', array($this, '_wp_set_comment_status'), 10, 2);
	}

	function json($data) {
		header('Content-type: text/json');
		echo json_encode($data);
		exit;
	}


	function config_get() {
		$this->json(un_config(array('external' => false)));
	}



	public function feedback_post(){
		global $un_model;
		if (get_option(UN_ONLY_REGISTERED) && !is_user_logged_in()) {
			wp_die('Hacking, huh?');
		}
		$data = json_decode(file_get_contents('php://input'), true);
		$data['referer'] = $_SERVER['HTTP_REFERER'];
		$un_model->create_feedback($data);
		exit;
	}

	public function _wp_set_comment_status($comment_id, $status){
		$comment = get_comment($comment_id);
		$post = get_post($comment->comment_post_ID);
		if ($post->post_type != FEEDBACK || $post->post_author != 0){
			return;
		}
		global  $un_model;
		 $un_model->notify_feedback_author_on_comment($comment_id);
	}

	public function _feedback_like(){
		$id = (int)$_REQUEST['id'];
		global  $un_model;
		$likes = $un_model->extract_likes();
		$dislikes = $un_model->extract_dislikes();
		if (!in_array($id, $likes)){
			$likes []= $id;
			 $un_model->add_like($id);
			if (in_array($id, $dislikes)){
				$dislikes = array_diff($dislikes, array($id));
				 $un_model->remove_dislike($id);
			}
		}
		setcookie('likes', join(',', $likes), time() + 86400 * 365 * 10, '/');
		setcookie('dislikes', join(',', $dislikes), time() + 86400 * 365 * 10, '/');
		header('Content-type: text/json');
		echo json_encode(array('likes' =>  $un_model->get_likes($id), 'dislikes' =>  $un_model->get_dislikes($id)));
		exit;
	}


	public function _un_feedback_get(){
		global $post,  $un_model;
		$type_slug = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;
		$page = (int)$_REQUEST['page'];
		$params = array('post_type' => FEEDBACK, 'post_status' => 'publish',
			'paged' => $page, 'posts_per_page' => 10,
			'orderby' => 'likes', 'order' => 'DESC');
		if ($type_slug)
			$params['feedback_type_slug'] = $type_slug;
		if (isset($_REQUEST['isMyFeedback']) && $_REQUEST['isMyFeedback'] != 'false'){
			$params['post_status'] = "any";
            $params['author'] = get_current_user_id();
		}
		if ($_REQUEST['orderby'])
			$params['orderby'] = $_REQUEST['orderby'];
		if ($_REQUEST['order'])
			$params['order'] = $_REQUEST['order'];
		$query = new WP_Query(apply_filters('un_feedback_list_query_params', $params));
		$feedback = array();
		header("Content-type: text/json");
		global $post;
		while($query->have_posts()){
			$query->the_post();
			$comment_stats = get_comment_count(get_the_ID());
			$row = array(
				'id' => get_the_ID(),
				'status_slug' => _un_get_the_feedback_status_slug(),
				'status' => _un_get_the_feedback_status(),
				'title' => get_the_title(),
				'time_ago' => _un_human_time_diff(get_the_time('U')) . " " . __('ago', 'usernoise'),
				'liked' => in_array(get_the_ID(), preg_split('/,/', isset($_COOKIE['likes']) ? $_COOKIE['likes'] : '')),
				'disliked' => in_array(get_the_ID(), preg_split('/,/', isset($_COOKIE['dislikes']) ? $_COOKIE['dislikes'] : '')),
				'likes' => (int)_un_get_the_feedback_likes(),
				'comments' => $comment_stats['approved'],
				'avatar' => get_avatar_url($un_model->author_email(get_the_ID()), 24, usernoise_url("/images/default-avatar.gif")),
				'author' => un_feedback_author_name(get_the_ID()),
				'text' => $this->get_excerpt($post)
			);
			$feedback []= $row;
		}
		echo json_encode(array(
			'feedback' => $feedback,
			'pages' => $query->max_num_pages
		));
		exit;
	}

  public function _un_feedback_get_id(){
		global $post,  $un_model;
		$query = new WP_Query(array(
			'post_type' => FEEDBACK,
			'p' => (int)$_REQUEST['id'],
			'post_status' => 'publish'
		));
		if (!$query->have_posts()){
			$query = new WP_Query(array(
				'post_type' => FEEDBACK,
				'p' => (int)$_REQUEST['id'],
				'post_status' => 'any',
				'meta_key' => '_unique',
				'meta_value' => $_COOKIE['un_unique']
			));
		}
		$feedback = array();
		header("Content-type: text/json");
		global $post;
		while($query->have_posts()){
			$query->the_post();
			if ($post->post_author) {
				$avatar = get_avatar_url($post, 24, usernoise_url("/images/default-avatar.gif"));
			} else {
				$avatar = get_avatar_url($un_model->author_email(get_the_ID()), 24, usernoise_url("/images/default-avatar.gif"));
			}
			$row = array(
				'id' => get_the_ID(),
				'status_slug' => _un_get_the_feedback_status_slug(),
				'status' => _un_get_the_feedback_status(),
				'title' => get_the_title(),
				'time_ago' => _un_human_time_diff(get_the_time('U')) . " " . __('ago', 'usernoise'),
				'liked' => in_array(get_the_ID(), preg_split('/,/', isset($_COOKIE['likes']) ? $_COOKIE['likes'] : '')),
				'disliked' => in_array(get_the_ID(), preg_split('/,/', isset($_COOKIE['dislikes']) ? $_COOKIE['dislikes'] : '')),
				'likes' => (int)_un_get_the_feedback_likes(),
				'avatar' => $avatar,
				'author' => un_feedback_author_name(get_the_ID()),
				'text' => $this->get_content($post)
			);
			$feedback = $row;
		}
		echo json_encode(array(
			'feedback' => $feedback,
			'comments' => $un_model->get_comments($post)
		));
		exit;
	}

	private function get_content($post){
		return apply_filters('comment_text', get_the_content($post));
	}

	private function get_excerpt($post){
		global $post;
		if (trim($post->post_excerpt))
			$text = $post->post_excerpt;
		else
			$text = get_the_content('');
		$text = strip_shortcodes( $text );
		$text = apply_filters( 'the_content', $text );
		$text = str_replace(']]>', ']]&gt;', $text);
		$excerpt_length = apply_filters( 'excerpt_length', 55 );
		$excerpt_more = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
		$text = wp_trim_words( $text, $excerpt_length, '&hellip;');
		return $text;
	}

	public function _comment_post(){
		global  $un_model;
		if (!(un_get_option(UN_COMMENTS_ENABLE, true) && (!get_option('comment_registration', true) || get_current_user_id() != false)))
			exit;
		if (get_option(UN_ONLY_REGISTERED) && !is_user_logged_in()) {
			wp_die('Hacking, huh?');
		}
		$post_id = $_REQUEST['postId'];
		$post = get_post($post_id);
		if ($post->post_type != FEEDBACK)
			wp_die(__('Hackin, huh?'));
		if (is_user_logged_in()){
			$user = get_userdata(get_current_user_id());
			$email = $user->user_email;
			$name = $user->display_name;
		} else {
			$email = stripslashes($_REQUEST['email']);
		}
		$comment = array(
			'comment_post_ID' => $post_id,
			'comment_author' => isset($_REQUEST['name']) && stripslashes($_REQUEST['name']) == __('Name') ? '' : (isset($_REQUEST['name']) ? stripslashes($_REQUEST['name']) : ''),
			'comment_author_email' => $email,
			'comment_content' => stripslashes($_REQUEST['text']),
			'comment_author_url' => '',
			'comment_type' => null,
			'user_id' => get_current_user_id()
		);
		setcookie('un_email', $comment['comment_author_email']);
		setcookie('un_name', $comment['comment_author']);
		add_action('comment_post', array($this, '_comment_post_hook'), 10, 2);
		// A hack for WPML.
		$_POST['comment_post_ID'] = $comment['comment_post_ID'];
		$comment_id = wp_new_comment( $comment );
		remove_action('comment_post', array($this, '_comment_post_hook'), 10, 2);
		global $comment;
		$comment = get_comment($comment_id);
		$this->json(array(
			'id' => $comment_id,
			'author' => $comment->comment_author,
			'time_ago' => _un_human_time_diff($comment->comment_date),
			'text' => apply_filters('comments_text', get_comment_text($comment->comment_ID)),
			'appproved' => (bool)$comment->comment_approved,
			'avatar' => get_avatar_url($comment, 24,
				usernoise_url("/images/default-avatar.gif")),
		));
	}

	public function _comment_post_hook($comment_id, $approved){
		global  $un_model;
		if (!$approved) return;
		 $un_model->notify_feedback_author_on_comment($comment_id);
	}

	function filter_unpro_comment_feed_orderby(){
		return 'comment_date_gmt ASC';
	}
}

$un_controller = new UN_Controller;
