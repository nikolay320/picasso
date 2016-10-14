<?php

class UN_Admin_Editor_Page{

	public function __construct(){
		add_action('admin_print_styles-post.php', array($this, 'action_print_styles'));
		add_action('add_meta_boxes_un_feedback', array($this, 'action_add_meta_boxes'));
		add_action('post_updated_messages', array($this, 'filter_post_updated_messages'));
		add_action('admin_enqueue_scripts', array($this, 'action_admin_enqueue_scripts'));
		add_filter('redirect_post_location', array($this, '_redirect_post_location'), 10, 2);
		add_action('save_post', array($this, '_save_post'));
	}

	public function _redirect_post_location($location, $post_id){
		$post = get_post($post_id);
		if ($post->post_type != FEEDBACK) return $location;
		if (isset($_REQUEST['un_redirect_back']) && $_REQUEST['un_redirect_backp'])
			$location = $_REQUEST['un_redirect_back'];
		else
			$location = admin_url('edit.php?order=desc&post_type=un_feedback&post_status=pending');
		return $location;
	}

	public function _save_post($id){
		global $un_model;
		$post = get_post($id);
		if ($post->post_type != FEEDBACK)
			return;
		if (isset($_REQUEST['un_status']))
			$un_model->set_feedback_status($id, stripslashes($_REQUEST['un_status']));
	}


	public function action_add_meta_boxes($post){
		global $post_new_file;
		if (isset($post_new_file)){
			$post_new_file = null;
		}
		remove_meta_box('submitdiv', FEEDBACK, 'side');
		add_meta_box('submitdiv', __('Publish'), array($this, 'post_submit_meta_box'),
			FEEDBACK, 'side', 'default');

		if (get_post_meta($post->ID, '_screenshot', true))
			add_meta_box('screenshot', __('Screenshot', 'usernoise'), array($this, 'screenshot'), FEEDBACK, 'side');

		add_meta_box('details', __('Details', 'usernoise'), array($this, '_details'), FEEDBACK, 'side');
		if ( ( 'publish' != get_post_status( $post ) && 'private' != get_post_status( $post ) ))
			add_meta_box('commentsdiv', __('Comments'), 'post_comment_meta_box', null, 'normal', 'core');
	}

	function _details($post){
		?>
			<?php if (un_feedback_has_author($post->ID) || true): ?>
				<div class="un-admin-section un-admin-section-first"><strong>
					<?php echo __('Author') . ': ' ?><?php un_feedback_author_link($post->ID) ?></strong>
				</div>
			<?php endif ?>
			<?php if ($url = get_post_meta($post->ID, '_url', true)): ?>
				<div class="un-admin-section un-admin-section-first">
					<strong><?php echo __('Page', 'usernoise') . ': ' ?></strong>
					<a href="<?php echo esc_attr($url) ?>" target="_blank"><?php echo esc_html($url)?> </a>
				</div>
			<?php endif ?>
			<?php if ($agent = get_post_meta($post->ID, '_user_agent', true)): ?>
				<div class="un-admin-section un-admin-section-first">
					<strong><?php echo __('Browser', 'usernoise') . ': ' ?></strong>
					<?php echo esc_html($agent)?>
				</div>
			<?php endif ?>
			<?php if($extra = get_post_meta($post->ID, '_extra', true)): ?>
				<?php foreach($extra as $key => $value): ?>
					<div class="un-admin-section un-admin-section-first">
						<strong><?php echo esc_html(ucfirst($key)) . ': ' ?></strong>
						<?php echo esc_html($value) ?>
				</div>
				<?php endforeach ?>
			<?php endif ?>
		<?php
	}

	function screenshot($post){
		$screenshot = get_post_meta($post->ID, '_screenshot', true);
		?><a href="<?php echo $screenshot ?>" target="_blank"><img src="<?php echo  $screenshot ?>" style="width: 100%"></a><?php
	}


	public function action_admin_enqueue_scripts($hook){
		global $post_type;
		if (!($post_type == FEEDBACK && $hook == 'post.php'))
			return;
		wp_enqueue_script('quicktags');
		wp_enqueue_script('un-editor-page', usernoise_url('/js/editor-page.js'));
	}

	public function filter_post_updated_messages($messages){
		$messages[FEEDBACK][6] = __('Feedback was marked as reviewed', 'usernoise');
		return $messages;
	}

	public function action_print_styles(){
		global $post_type;
		if ($post_type == FEEDBACK) {
				wp_enqueue_style('un-admin', usernoise_url('/css/admin.css'));
				wp_enqueue_style('un-admin-font-awesome', usernoise_url('/vendor/font-awesome/font-awesome-embedded.css' . "?version=" . UN_VERSION));
		}
	}

	public function post_submit_meta_box($post) {
		global $action;
		$post_type = $post->post_type;
		$post_type_object = get_post_type_object($post_type);
		$can_publish = current_user_can($post_type_object->cap->publish_posts);
		require(usernoise_path('/html/publish-meta-box.php'));
	}
}

$un_admin_editor_page = new UN_Admin_Editor_Page;
