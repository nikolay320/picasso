<?php
class UNPRO_Feedback_Types extends UN_Admin_Base{
	function __construct(){
		add_filter('un_feedback_type_taxonomy_params', array($this, '_un_feedback_type_taxonomy_params'));
		add_action('un_add_submenu', array($this, '_un_register_submenu'));
		add_action(FEEDBACK_TYPE . '_add_form_fields', array($this, '_add_form'));
		add_action('admin_print_styles-edit-tags.php', array($this, '_print_styles'));
		add_action('admin_enqueue_scripts', array($this, '_enqueue_scripts'));
		add_action('create_' . FEEDBACK_TYPE, array($this, '_edit_feedback_type'));
		add_action('edit_' . FEEDBACK_TYPE, array($this, '_edit_feedback_type'));
		add_action(FEEDBACK_TYPE . "_edit_form_fields", array($this, '_edit_form'), 10, 2);
	}

	function _edit_feedback_type($term_id){
		un_update_term_meta($term_id, 'icon', stripslashes($_POST['icon']));
		un_update_term_meta($term_id, 'position', stripslashes($_POST['position']));
		un_update_term_meta($term_id, 'plural', stripslashes($_POST['plural']));
	}

	function _print_styles(){
		wp_enqueue_style('un-font-awesome', usernoise_url('/vendor/font-awesome/css/font-awesome.css'));
		wp_enqueue_style('un-chosen', usernoise_url('/vendor/chosen/chosen.css'));
		wp_enqueue_style('unpro-admin', usernoise_url('/css/admin.css'));
	}

	function _enqueue_scripts($type){
		global $submenu_file;
		if ($submenu_file == 'edit-tags.php?taxonomy=feedback_type'){
			wp_enqueue_script('un-chosen', usernoise_url('/vendor/chosen/chosen.jquery.js'));
			wp_enqueue_script('un-feedback-types', usernoise_url('/js/feedback-types.js'));
		}
	}

	function _un_feedback_type_taxonomy_params($params){
		$params['show_ui'] = true;
		$params['labels'] = array(
			'name' => __('Feedback types', 'usernoise-pro'),
			'singular_name' => __('Feedback type', 'usernoise-pro'),
			'all_items' => __('All feedback types', 'usernoise-pro'),
			'edit_item' => __('Edit feedback type', 'usernoise-pro'),
			'view_item' => __('View feedback type', 'usernoise-pro'),
			'update_item' => __('Update feedback type', 'usernoise-pro'),
			'add_new_item' => __('Add new feedback type', 'usernoise-pro'),
			'new_item_name' => __('New feedback type name', 'usernoise-pro')
		);
		return $params;
	}

	function _un_register_submenu($slug){
		add_submenu_page($slug, __('Feedback Types', 'usernoise-pro'), __('Feedback Types', 'usernoise-pro'),
			'manage_options', 'edit-tags.php?taxonomy=feedback_type');
	}

	function _add_form($taxonomy){
		global $un_h;
		?>
		<!--<div class="form-field">
			<label for="tag-icon"><?php _e('Icon', 'usernoise-pro'); ?></label>
			<?php $un_h->select('icon', $un_h->collection2options(un_get_icons(), 'icon', 'label', null, array('data-icon'))) ?>
			<p><?php _e('Icon used for this feedback type', 'usernoise-pro'); ?></p>
		</div>-->
		<div class="form-field">
			<label for="tag-position"><?php _e('Position', 'usernoise-pro'); ?></label>
			<input name="position" id="tag-position" type="text" value="" size="40" />
			<p><?php _e('Type position among other feedback types', 'usernoise-pro'); ?></p>
		</div>
		<div class="form-field">
			<label for="tag-plural"><?php _e('Name in plural form', 'usernoise-pro'); ?></label>
			<input name="plural" id="tag-position" type="text" value="" size="40" />
			<p><?php _e('Plural form used by some of the UI', 'usernoise-pro'); ?></p>
		</div>

		<?php
	}

	function _edit_form($tag, $taxonomy){
		global $un_h;
		?>
		<!--<tr class="form-field">
			<th scope="row" valign="top"><label for="description"><?php _e('Icon', 'usernoise-pro'); ?></label></th>
			<td><?php $un_h->select('icon', $un_h->collection2options(un_get_icons(), 'icon', 'label', un_get_term_meta($tag->term_id, 'icon'), array('data-icon'))) ?>
			<p><?php _e('Icon used for this feedback type', 'usernoise-pro'); ?></p>
		</tr>-->
		<tr class="form-field">
			<th scope="row" valign="top"><label for="description"><?php _e('Position', 'usernoise-pro'); ?></label></th>
			<td><?php $un_h->text_field('position', un_get_term_meta($tag->term_id, 'position'))?><br>
			<p><?php _e('Type position among other feedback types', 'usernoise-pro'); ?></p>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="plural"><?php _e('Name in plural form', 'usernoise-pro'); ?></label></th>
			<td><?php $un_h->text_field('plural', un_get_term_meta($tag->term_id, 'plural'))?><br>
			<p><?php _e('Plural form used by some of the UI', 'usernoise-pro'); ?></p>
		</tr>

		<?php
	}

}

new UNPRO_Feedback_Types;
