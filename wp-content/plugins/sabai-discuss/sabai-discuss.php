<?php
/*
Plugin Name: Sabai Discuss
Plugin URI: http://sabaidiscuss.com/
Description: Questions and Answers plugin for WordPress.
Author: onokazu
Author URI: http://codecanyon.net/user/onokazu/portfolio?ref=onokazu
Text Domain: sabai-discuss
Domain Path: /languages
Version: 1.3.28
*/
define('SABAI_PACKAGE_DISCUSS_PATH', dirname(__FILE__));

function sabai_wordpress_discuss_init()
{
    include_once SABAI_PACKAGE_DISCUSS_PATH . '/include/shortcodes.php';
}
add_action('init', 'sabai_wordpress_discuss_init');

function sabai_wordpress_discuss_addon_path($paths)
{
    $paths[] = array(SABAI_PACKAGE_DISCUSS_PATH . '/lib', '1.3.28');
    return $paths;
}
add_filter('sabai_sabai_addon_paths', 'sabai_wordpress_discuss_addon_path');

if (is_admin()) {
    function sabai_wordpress_discuss_activation_hook()
    {
        if (!function_exists('get_sabai_platform')) die('The Sabai plugin needs to be activated first before activating this plugin!');
        get_sabai_platform()->activatePlugin('sabai-discuss', array('Questions' => array()));
    }
    register_activation_hook(__FILE__, 'sabai_wordpress_discuss_activation_hook');
    
    function sabai_wordpress_discuss_plugin_row_meta($links, $file)
    {
        if ($file === plugin_basename(__FILE__)) {
            $links[] = '<a href="http://codecanyon.net/item/sabai-discuss-plugin-for-wordpress/3455723/support" target="_blank">Support</a>';  
        }
        return $links; 
    } 
    add_filter('plugin_row_meta', 'sabai_wordpress_discuss_plugin_row_meta', 10, 2);
}

function is_sabai_discuss_question()
{
    return isset($GLOBALS['sabai_entity'])
        && $GLOBALS['sabai_entity']->getBundleType() === 'questions';
}

function is_sabai_discuss_category($slug = null)
{
    if (!isset($GLOBALS['sabai_entity'])
        || $GLOBALS['sabai_entity']->getBundleType() !== 'questions_categories'
    ) return false;
    
    return isset($slug) ? $GLOBALS['sabai_entity']->getSlug() === $slug : true;
}

function is_sabai_discuss_tag($slug = null)
{
    if (!isset($GLOBALS['sabai_entity'])
        || $GLOBALS['sabai_entity']->getBundleType() !== 'questions_tags'
    ) return false;
    
    return isset($slug) ? $GLOBALS['sabai_entity']->getSlug() === $slug : true;
}
