<?php
class UN_Feedback_List {
	function __construct(){
	//	add_action('restrict_manage_posts', array($this, 'action_restrict_manage_posts'));
	//	add_action('admin_init', array($this, 'action_admin_init'));
	//	add_action('manage_un_feedback_posts_custom_column',
	//		array($this, 'action_manage_feedback_posts_custom_column'), 10, 2);
	//	add_filter('manage_un_feedback_posts_columns', array($this, 'filter_manage_feedback_posts_columns'));
	//	add_filter('un_feedback_columns', array($this, '_un_feedback_columns'));
	///	add_action('restrict_manage_posts', array($this, '_restrict_manage_posts'));
	}

	public function action_admin_init(){
		add_action('admin_enqueue_scripts', array($this, 'action_admin_enqueue_scripts'));
		add_action('admin_print_styles-edit.php', array($this, 'action_admin_print_styles'));
	}


	public function _un_feedback_columns($columns){
		$columns['un-status'] = __('Status', 'usernoise');
		return $columns;
	}

	public function _restrict_manage_posts(){
		global $post_type;
		global $wp;
		global $un_h;
		global $un_model;
		if ($post_type != FEEDBACK)
			return;
		$statuses = $un_model->get_statuses();
		$un_h->select('un_status', $un_h->hash2options($statuses, __('All feedback statuses', 'usernoise-pro')),
			stripslashes(isset($_REQUEST['un_status']) ? $_REQUEST['un_status'] : ''));
	}

	public function action_admin_print_styles(){
		wp_enqueue_style('un-admin', usernoise_url('/css/admin.css' . "?version=" . UN_VERSION));
		wp_enqueue_style('un-admin-font-awesome', usernoise_url('/vendor/font-awesome/font-awesome-embedded.css' . "?version=" . UN_VERSION));
	}

	public function action_admin_enqueue_scripts($type){
		global $post_type;
		if ($type == 'edit.php' && $post_type == FEEDBACK){
			wp_enqueue_script('un-feedback-list', usernoise_url('/js/feedback-list.js'));
		}
	}
	public function filter_manage_feedback_posts_columns($columns){
		if (!un_get_option(UN_FEEDBACK_FORM_SHOW_TYPE))
			return $columns;
		return array_merge(
				array_merge(
					array('cb' => $columns['cb']),
					apply_filters('un_feedback_columns', array('un-type' => __('Type', 'usernoise')))),
				array_slice($columns, 1));
	}

	public function action_manage_feedback_posts_custom_column($column_name, $post_id){
		global $un_h;
		if ($column_name == 'un-type'){
			$terms = wp_get_post_terms($post_id, FEEDBACK_TYPE);
			if (!empty($terms)){
				echo "<span class='type'>";
				$un_h->tag('i', array('class' => un_get_term_meta($terms[0]->term_id, 'icon')));
				echo $terms[0]->name;
				echo "</span>";
			}
		}
		if ($column_name == 'un-status') {
			echo get_post_meta($post_id, '_status', true);
		}
	}

	public function action_restrict_manage_posts(){
		global $post_type;
		global $wp;
		if ($post_type != FEEDBACK)
			return;
		wp_dropdown_categories(array(
			'taxonomy' => FEEDBACK_TYPE,
			'show_option_all' => __('All feedback types', 'usernoise'),
			'show_count' => false,
			'name' => 'feedback_type_id',
			'selected' => (isset($_REQUEST['feedback_type_id']) ? $_REQUEST['feedback_type_id'] : null)
		));
	}
}

$un_feedback_list_class = apply_filters('un_feedback_list_class', 'UN_Feedback_List');
$un_feedback_list = new $un_feedback_list_class;
