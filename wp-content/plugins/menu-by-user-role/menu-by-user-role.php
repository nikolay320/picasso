<?php

/**
Plugin Name: Menu by User Role for WordPress
Plugin URI: http://plugins.righthere.com/menu-by-user-role/
Description: Define Custom Menus based on User Roles in WordPress. Lets you easily create menus for users when logged in and not logged in.
Version: 1.0.4.55001
Author: Alberto Lau (RightHere LLC)
Author URI: http://plugins.righthere.com
 **/

define('MUR_VERSION','1.0.4'); 
//define('MUR_ADMIN_ROLE','administrator');
define('MUR_PATH', plugin_dir_path(__FILE__) ); 
define("MUR_URL", plugin_dir_url(__FILE__) );  

global $wp_version;


if(!class_exists('plugin_mur')){
	if( $wp_version < 3.5 ){
		require_once MUR_PATH.'includes/class.plugin_mur_prewp350.php';	
	}else{
		require_once MUR_PATH.'includes/class.plugin_mur.php';
	}
}

global $mur_plugin;
$mur_plugin = new plugin_mur();
?>