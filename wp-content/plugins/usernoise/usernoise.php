<?php
/*
Plugin Name: Usernoise Pro
Description: Usernoise is a modal contact / feedback form with smooth interface.
Version: 4.5.0.7
Author: Nikolay Karev
*/

require_once(ABSPATH . "/wp-admin/includes/plugin.php");
$usernoise_plugin_data = get_plugin_data(__FILE__);
define('UN_VERSION', $usernoise_plugin_data['Version']);
add_action('plugins_loaded', 'un_load_plugin_textdomain');

function un_load_plugin_textdomain() {
	load_plugin_textdomain('usernoise', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

define('FEEDBACK', 'un_feedback');
define('FEEDBACK_TYPE', 'feedback_type');
define('USERNOISE', 'usernoise');
define('USERNOISE_DIR', dirname(plugin_basename(__FILE__)));
define('USERNOISE_MAIN', __FILE__);
define('USERNOISE_PATH', dirname(__FILE__));

define('UN_FEEDBACK_FORM_TITLE', 'feedback_form_title');
define('UN_USE_FONT', 'use_font');
define('UN_FEEDBACK_FORM_TEXT', 'feedback_form_text');
define('UN_FEEDBACK_BUTTON_TEXT', 'feedback_button_text');
define('UN_FEEDBACK_BUTTON_ICON', 'feedback_button_icon');
define('UN_FEEDBACK_BUTTON_COLOR', 'feedback_button_color');
define('UN_FEEDBACK_BUTTON_POSITION', 'feedback_button_position');
define('UN_FEEDBACK_BUTTON_TEXT_COLOR', 'feedback_button_text_color');
define('UN_FEEDBACK_BUTTON_SHOW_BORDER', 'feedback_button_show_border');
define('UN_SUBMIT_FEEDBACK_BUTTON_TEXT', 'submit_feedback_button_text');
define('UN_FEEDBACK_TEXTAREA_PLACEHOLDER', 'feedback_textarea_placeholder');
define('UN_FEEDBACK_SUMMARY_PLACEHOLDER', 'feedback_summary_placeholder');
define('UN_FEEDBACK_EMAIL_PLACEHOLDER', 'feedback_email_placeholder');
define('UN_FEEDBACK_FORM_SHOW_SUMMARY', 'feedback_form_show_summary');
define('UN_FEEDBACK_FORM_SHOW_TYPE', 'feedback_form_show_type');
define('UN_FEEDBACK_FORM_SHOW_EMAIL', 'feedback_form_show_email');
define('UN_FEEDBACK_FORM_SHOW_NAME', 'feedback_form_show_name');
define('UN_FEEDBACK_NAME_PLACEHOLDER', 'feedback_form_name_placeholder');
define('UN_FEEDBACK_FORM_SCREENSHOT_ENABLE', 'feedback_form_screenshot_enable');
define('UN_FEEDBACK_FORM_SCREENSHOT_FORMAT', 'feedback_form_screenshot_format');
define('UN_ONLY_REGISTERED', 'only_registered');
define('UN_FEEDBACK_FORM_SCREENSHOT_QUALITY', 'feedback_form_screenshot_quality');
define('UN_SHOW_FEEDBACK_BUTTON', 'unpro_show_feedback_button');
define('UN_COMMENTS_ENABLE', 'un_comments_enable');
define('UN_SHOW_ALL_FEEDBACKS_LINK', 'show_all_feedbacks_link');

define('UNPRO_ENABLE_FEEDS', 'unpro_enable_feeds');

define('UN_PUBLISH_DIRECTLY', 'publish_directly');

define('UN_ADMIN_NOTIFY_ON_FEEDBACK', 'admin_notify_on_feedback');
define('UN_THANKYOU_TITLE', 'thankyou_title');
define('UN_THANKYOU_TEXT', 'thankyou_text');
define('UN_DISABLE_ON_MOBILES', 'disable_on_mobiles');
define('UN_LOAD_IN_FOOTER', 'load_in_footer');

define('UNPRO_ADMIN_NOTIFICATION_EMAIL', 'unpro_admin_notification_email');
define('UNPRO_ENABLE_DISCUSSIONS', 'unpro_enable_discussions');
define('UNPRO_CUSTOM_BUTTON_ID', 'unpro_custom_button_id');
define('UNPRO_CUSTOM_BUTTON_CSS', 'unpro_custom_button_css');
define('UNPRO_FORM_CSS', 'unpro_form_css');
define('UNPRO_EXTERNAL_CODE', 'unpro_external_code');
define('UNPRO_DISABLE_BUTTON_ON_LOGIN', 'unpro_disable_button_on_login');
define('UNPRO_NOTIFICATIONS_SITE', 'unpro_notifications_site');

require(USERNOISE_PATH .'/vendor/plugin-options-framework/plugin-options-framework.php');
$un_h = new HTML_Helpers_0_4;
require(USERNOISE_PATH .'/inc/template.php');

require(USERNOISE_PATH . '/inc/termmeta-api.php');
require(usernoise_path('/admin/settings.php'));
require(usernoise_path('/inc/model.php'));
require(usernoise_path('/inc/db-upgrade.php'));
require(usernoise_path('/inc/migrations.php'));
require(usernoise_path('/inc/shortcodes.php'));
require(usernoise_path('/inc/widgets.php'));

if (is_admin()){
  require(usernoise_path('/admin/admin-base.php'));
  require(usernoise_path('/admin/editor-page.php'));
  require(usernoise_path('/admin/menu.php'));
  require(usernoise_path('/admin/feedback-list.php'));
  require(usernoise_path('/admin/feedback-types.php'));
  require(usernoise_path('/admin/dashboard.php'));
  if (defined('DOING_AJAX')){
    require(usernoise_path('/inc/controller.php'));
  }
} else {
  require(usernoise_path('/admin/admin-bar.php'));
  require(usernoise_path('/inc/integration.php'));
  require(usernoise_path('/inc/controller.php'));
}


function un_get_feedback_capabilities(){
  return array('edit_un_feedback_items', 'edit_un_feedback',
    'delete_un_feedback', 'publish_un_feedback', 'publish_un_feedback_items',
    'edit_others_un_feedback_items', 'edit_published_un_feedback');
}

function un_get_capable_roles(){
  return array('administrator', 'editor');
}

function un_activation_hook(){

}

if (is_admin()){
  add_action('init', 'un_disable_pro');
}

function un_disable_pro(){
	require_once( ABSPATH .   'wp-admin/includes/plugin.php' );
	deactivate_plugins(plugin_basename(dirname(dirname(__FILE__)) . "/usernoise-pro/usernoise-pro.php"), true);
}

function un_deactivation_hook(){
	delete_option('un_version');
	global $wp_roles;
	flush_rewrite_rules();
}

register_deactivation_hook(__FILE__, 'un_deactivation_hook');
register_activation_hook(__FILE__, 'un_activation_hook');
