<?php
/*
Plugin Name: Sabai
Description: Sabai is a web application framework for WordPress.
Author: onokazu
Author URI: http://codecanyon.net/user/onokazu/portfolio?ref=onokazu
Text Domain: sabai
Domain Path: /languages
Version: 1.3.28
*/
function get_sabai_platform()
{
    if (!class_exists('Sabai_Platform_WordPress', false)) {
        if (defined('SABAI_WORDPRESS_PATH_APPEND') && SABAI_WORDPRESS_PATH_APPEND) {
            set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . '/lib');
        } else {
            set_include_path(dirname(__FILE__) . '/lib' . PATH_SEPARATOR . get_include_path());
        }
        // Define custom session path if session not yet started
        if (defined('SABAI_WORDPRESS_SESSION_PATH') && !session_id()) {
            session_save_path(SABAI_WORDPRESS_SESSION_PATH);
        }
        require 'Sabai/Platform/WordPress.php';
    }
    return Sabai_Platform_WordPress::getInstance();
}

function get_sabai($loadAddons = true, $reload = false)
{
    return get_sabai_platform()->getSabai($loadAddons, $reload);
}

function is_sabai()
{
    return is_page()
        && isset($GLOBALS['post'])
        && ($slugs = get_option('sabai_sabai_page_slugs', false))
        && is_array($slugs[2])
        && array_search($GLOBALS['post']->ID, $slugs[2]);
}

function sabai_wordpress_run()
{
    get_sabai_platform()->run();
}
add_action('plugins_loaded', 'sabai_wordpress_run');

if (is_admin()) {
    function sabai_wordpress_activation_hook()
    {
        get_sabai_platform()->activate();
    }
    register_activation_hook(__FILE__, 'sabai_wordpress_activation_hook');
    
    function sabai_wordpress_uninstall_hook()
    {
        try {
            get_sabai(true, true)->Uninstall();
            wp_clear_scheduled_hook('sabai_cron');
        } catch (Sabai_NotInstalledException $e) {
            return;
        }
    }
    register_uninstall_hook(__FILE__, 'sabai_wordpress_uninstall_hook');
}
